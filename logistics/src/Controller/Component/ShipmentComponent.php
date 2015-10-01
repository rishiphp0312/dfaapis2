<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;

/**
 * Shipment Component
 */
class ShipmentComponent extends Component {

    public $Shipments = null;
    public $ShipmentLocations = null;
    public $FieldOptionValues = null;
    public $ShipmentPackages = null;
    public $ShipmentPackageItems = null;
    public $ShipmentLocationAttachments = null;
    
    public $components = ['Auth', 'Common', 'UserCommon', 'Administration','Package','Items', 'Subscription'];
    
    public function initialize(array $config) {
        parent::initialize($config);
        $this->Shipments = TableRegistry::get('Shipments');
        $this->ShipmentLocations = TableRegistry::get('ShipmentLocations');
        $this->ShipmentLocationAttachments = TableRegistry::get('ShipmentLocationAttachments');
        $this->FieldOptionValues = TableRegistry::get('FieldOptionValues');
        $this->ShipmentPackages = TableRegistry::get('ShipmentPackages');
        $this->ShipmentPackageItems = TableRegistry::get('ShipmentPackageItems');
    }

    /**
     * GET shipment List
     * $options is the array
     * @return array Shipment Listing
     * @labels is by default false if true will add package count also
     */
    public function getShipmentList($options = [], $labels = false) {
        $pkgCounts = 0;
        $shipmetArray = [];
        $query = $this->Shipments->find('all', $options);
        $results = $query->hydrate(false)->all()->toArray();

        if (!empty($results)) {

            $areas = $this->Administration->getAreaList('list');
            $locations = $this->Administration->getLocationList('all');

            foreach ($results as $result) {
                if (!empty($result['code'])) {
                    
                    $shipmentLocation = $this->Administration->getShipmentLocationList('all', [], ['shipment_id' => $result['id']]);
                    
                    $fromLocation = isset($locations[$result['from_location_id']]['name']) ? $locations[$result['from_location_id']]['name'] : null;
                    $toLocation = isset($locations[$result['to_location_id']]['name']) ? $locations[$result['to_location_id']]['name'] : null;
                    $statusId = !empty($shipmentLocation) ? end($shipmentLocation)['status_id'] : '' ;
                    
                    if($labels == true){
                       $pkgCounts= $this->ShipmentPackages->getCount(['shipment_id' => $result['id']]);
                    }
                    
                    $shipmetArray[] = [
                        'id' => $result['id'],
                        'shipmentCode' => $result['code'],
                        'shipDate' => $result['shipment_date'],
                        'shipFromAreaId' => $result['from_area_id'],
                        'shipFromArea' => $areas[$result['from_area_id']],
                        'shipFromId' => $result['from_location_id'],
                        'shipFrom' => $fromLocation,
                        'shipToAreaId' => $result['to_area_id'],
                        'shipToArea' => $areas[$result['to_area_id']],
                        'shipToId' => $result['to_location_id'],
                        'shipTo' => $toLocation,
                        'statusId' => $statusId,
                        'packageCount' => $pkgCounts,
                    ];
                }
            }
        }

        return $shipmetArray;
    }

    /**
     * validate Add Shipment
     * 
     * @param array $data add shipment data
     * @return boolean/array true-false/error-code
     */
    public function validateAddShipment($data) {

        $return = true;

        if (!isset($data['shipFrom']['areaId']) || empty($data['shipFrom']['areaId']))
            $return = ['error' => _ERR131];
        if (!isset($data['shipFrom']['locationId']) || empty($data['shipFrom']['locationId']))
            $return = ['error' => _ERR132];
        if (!isset($data['shipTo']['areaId']) || empty($data['shipTo']['areaId']))
            $return = ['error' => _ERR133];
        if (!isset($data['shipTo']['locationId']) || empty($data['shipTo']['locationId']))
            $return = ['error' => _ERR134];

        return $return;
    }

    /**
     * ADD shipment
     * 
     * @param array $fieldsArray Add fields values
     */
    public function addShipment($fieldsArray = []) {

        // Validate Inputs
        $validate = $this->validateAddShipment($fieldsArray);
        if ($validate != true)
            return $validate;

        // Logged-in User details
        $user = $this->Auth->user();

        // Prepare Data
        $insertData = [];
        $insertData['code'] = $fieldsArray['shipmentCode'];
        $insertData['from_area_id'] = $fieldsArray['shipFrom']['areaId'];
        $insertData['from_location_id'] = $fieldsArray['shipFrom']['locationId'];
        $insertData['to_area_id'] = $fieldsArray['finalDeliveryPoint']['shipTo']['areaId'];
        $insertData['to_location_id'] = $fieldsArray['finalDeliveryPoint']['shipTo']['locationId'];
        $insertData['modified_user_id'] = $user['id'];
        
        // INSERT Case
        if (empty($fieldsArray['id'])) {
            $insertData['shipment_date'] = date('Y-m-d', time());
            $insertData['created_user_id'] = $user['id'];
        } // MODIFY Case
        else {
            $insertData['id'] = $fieldsArray['id'];
        }

        // Save Shipment
        $shipmentId = $this->Shipments->saveRecords($insertData);

        if ($shipmentId) {
            
            // Send SMS when creating new Shipment
            if (empty($fieldsArray['id'])) {
                $shipmentMessage = 'New Shipment generated. Code - ' . $insertData['code'];
                $this->Subscription->sendSmsToSubscribers($insertData['to_location_id'], $shipmentMessage);
            }
            
            $fieldsArray['id'] = $shipmentId;

            // ADD Delivery Points
            return $this->saveDeliveryPoints($fieldsArray);
        } else {
            return ['error' => _ERR126];
        }
    }

    /**
     * GET auto-generated Shipment Code
     * 
     * @return string Auto-Generated shipment code
     */
    public function getAutoGeneratedCode() {
        $letters = $this->Common->getAlphabets();
        $autoGeneratedCode = $letters[array_rand($letters)]
                . $letters[array_rand($letters)]
                . $letters[array_rand($letters)]
                . time();

        return $autoGeneratedCode;
    }

    /**
     * SAVE delivery points
     * 
     * @param array $data Save Data
     * @return boolean true/false
     */
    public function saveDeliveryPoints($data = []) {

        if (!empty($data)) {

            /**
             * DELETE all associated Delivery Points first, then create new ones
             * Reason: We need to avoid extra coding for managing
             * ADD, UPDATE, DELETE of delivery points because
             * 1. Some new delivery points will be added during edit.
             * 2. Some old delivery points will be edited during edit.
             * 3. Some old delivery points will be deleted during edit.
             */
            //$this->ShipmentLocations->deleteAll(['shipment_id' => $data['id']]);
            $shipmentLocRecs = $this->ShipmentLocations->findByShipmentId($data['id'])->hydrate(false)->all()->toArray();
            $shipLocExistingIds = $shipLocPostedIds = [];
            if(!empty($shipmentLocRecs)) {
                foreach($shipmentLocRecs as $shipmentLocRec) {
                    $shipLocExistingIds[] = $shipmentLocRec['id'];
                }
            }
            $user = $this->Auth->user();

            $saveConfirmationValue = true;
            $isConfirmed = $this->Common->getSystemConfig('list', ['fields' => ['code', 'value'], 'conditions' => ['code' => 'confirmations']]);
            if(!empty($isConfirmed)) {
                if($isConfirmed['confirmations'] != 0)
                    $saveConfirmationValue = false;
            }    
            
            if(!empty($data['deliveryPoint'])) {
                // Prepare data for INSERT
                foreach ($data['deliveryPoint'] as $deliveryPoint) {
                    $status = isset($deliveryPoint['statusId']) ? $deliveryPoint['statusId'] : 1;
                    
                    $insertData[] = [
                        'shipment_id' => $data['id'],
                        'sequence_no' => $deliveryPoint['sequenceNumber'],
                        'from_area_id' => $deliveryPoint['shipFrom']['areaId'],
                        'from_location_id' => $deliveryPoint['shipFrom']['locationId'],
                        'to_area_id' => $deliveryPoint['shipTo']['areaId'],
                        'to_location_id' => $deliveryPoint['shipTo']['locationId'],
                        'expected_delivery_date' => $deliveryPoint['expectedDate'],
                        'courier_id' => $deliveryPoint['courierId'],
                        'confirmation_id' => $deliveryPoint['confirmationRequired'],
                        'status_id' => $status,
                        'created_user_id' => $user['id'],
                        'modified_user_id' => $user['id'],
                    ];
                    
                    // UPDATE delivery point if Id exists
                    if(isset($deliveryPoint['id'])) {
                        $insertData[count($insertData) - 1]['id'] = $deliveryPoint['id'];
                        $shipLocPostedIds[] = $deliveryPoint['id'];
                    }
                    /*if($saveConfirmationValue == true) {
                        $insertData[count($insertData) - 1]['confirmation_id'] = $deliveryPoint['confirmationRequired'];
                    }*/
                }
            }

            // Final Delivery Point
            $status = isset($data['finalDeliveryPoint']['statusId']) ? $data['finalDeliveryPoint']['statusId'] : 1;
            
            $insertData[] = [
                'shipment_id' => $data['id'],
                'sequence_no' => $data['finalDeliveryPoint']['sequenceNumber'],
                'from_area_id' => $data['finalDeliveryPoint']['shipFrom']['areaId'],
                'from_location_id' => $data['finalDeliveryPoint']['shipFrom']['locationId'],
                'to_area_id' => $data['finalDeliveryPoint']['shipTo']['areaId'],
                'to_location_id' => $data['finalDeliveryPoint']['shipTo']['locationId'],
                'expected_delivery_date' => $data['finalDeliveryPoint']['expectedDate'],
                'courier_id' => $data['finalDeliveryPoint']['courierId'],
                'confirmation_id' => $data['finalDeliveryPoint']['confirmationRequired'],
                'status_id' => $status,
                'created_user_id' => $user['id'],
                'modified_user_id' => $user['id'],
            ];
            
            // UPDATE delivery point if Id exists
            if(isset($data['finalDeliveryPoint']['id'])) {
                $insertData[count($insertData) - 1]['id'] = $data['finalDeliveryPoint']['id'];
                $shipLocPostedIds[] = $data['finalDeliveryPoint']['id'];
            }
            
            /*if($saveConfirmationValue == true) {
                $insertData[count($insertData) - 1]['confirmation_id'] = $data['finalDeliveryPoint']['confirmationRequired'];
            }*/
            
            // DELETE removed delivery points
            $this->ShipmentLocations->deleteAll(['id IN' => array_diff($shipLocExistingIds, $shipLocPostedIds)]);
            
            // INSERT delivery points
            $this->ShipmentLocations->saveBulkRecords($insertData);
        }

        return true;
    }

    /**
     * DELETE shipment
     */
    public function deleteShipment($id = null) {
        if (!empty($id)) {
            $this->Shipments->deleteAll(['id' => $id]);
            $this->Shipments->ShipmentLocations->deleteAll(['shipment_id' => $id]);
            $this->Shipments->ShipmentLocationAttachments->deleteAll(['shipment_id' => $id]);
            $this->Shipments->ShipmentPackages->deleteAll(['shipment_id' => $id]);
            $this->Shipments->ShipmentPackageItems->deleteAll(['shipment_id' => $id]);
        }
    }

    /**
     * GET shipment details
     */
    public function getShipment($id = null, $code = null) {

        $return = [];

        $results = $this->Shipments->find('all', ['conditions' => ['OR' => ['id' => $id, 'code' => $code]]])->hydrate(false)->first();
        if (!empty($results)) {

            //$areas = $this->Administration->find('list', 'Areas', ['fields' => ['id', 'name'], 'conditions' => ['id IN' => array($results['from_area_id'], $results['to_area_id'])]]);
            $areas = $this->Administration->find('list', 'Areas', ['fields' => ['id', 'name']]);
            $locations = $this->Administration->find('list', 'Locations', ['fields' => ['id', 'name']]);
            
            // GET users
            $usersObj = TableRegistry::get('Users');
            $users = $usersObj->getRecords(['id', 'username'], [], 'list');
            
            $fromAreaId = isset($areas[$results['from_area_id']]) ? $results['from_area_id'] : null;
            $fromAreaName = isset($areas[$results['from_area_id']]) ? $areas[$results['from_area_id']] : null;
            $fromLocationId = isset($locations[$results['from_location_id']]) ? $results['from_location_id'] : null;
            $fromLocationName = isset($locations[$results['from_location_id']]) ? $locations[$results['from_location_id']] : null;
            
            $toAreaId = isset($areas[$results['to_area_id']]) ? $results['to_area_id'] : null;
            $toAreaName = isset($areas[$results['to_area_id']]) ? $areas[$results['to_area_id']] : null;
            $toLocationId = isset($locations[$results['to_location_id']]) ? $results['to_location_id'] : null;
            $toLocationName = isset($locations[$results['to_location_id']]) ? $locations[$results['to_location_id']] : null;
            
            $modifiedBy = isset($users[$results['modified_user_id']]) ? $users[$results['modified_user_id']] : null;

            $return = [
                'id' => $results['id'],
                'shipmentCode' => $results['code'],
                'shipmentDate' => $results['shipment_date'],
                'shipFrom' => [
                    'areaId' => $fromAreaId,
                    'areaName' => $fromAreaName,
                    'locationId' => $fromLocationId,
                    'locationName' => $fromLocationName
                ],
                'shipTo' => [
                    'areaId' => $toAreaId,
                    'areaName' => $toAreaName,
                    'locationId' => $toLocationId,
                    'locationName' => $toLocationName
                ],
                'modifiedBy' => $modifiedBy,
                'modifiedDate' => $results['modified'],
            ];

            $deliveryPoints = $this->ShipmentLocations->find('all', ['fields' => [], 'conditions' => ['shipment_id' => $results['id']], 'order' => ['sequence_no' => 'ASC']])->contain(['Couriers'])->hydrate(false)->all()->toArray();

            if (!empty($deliveryPoints)) {
                
                // Get Custom Types List
                $customTypes = $this->Common->getTypeLists(_CONFIRMATION_LIST_TYPES_CODE,  true);

                $finalDeliveryPoint = end($deliveryPoints);

                $toAreaId = isset($areas[$finalDeliveryPoint['to_area_id']]) ? $finalDeliveryPoint['to_area_id'] : null;
                $toAreaName = isset($areas[$finalDeliveryPoint['to_area_id']]) ? $areas[$finalDeliveryPoint['to_area_id']] : null;
                $toLocationId = isset($locations[$finalDeliveryPoint['to_location_id']]) ? $finalDeliveryPoint['to_location_id'] : null;
                $toLocationName = isset($locations[$finalDeliveryPoint['to_location_id']]) ? $locations[$finalDeliveryPoint['to_location_id']] : null;
                $courierId = isset($finalDeliveryPoint['courier_id']) ? $finalDeliveryPoint['courier_id'] : null;
                $courierName = isset($finalDeliveryPoint['courier']['name']) ? $finalDeliveryPoint['courier']['name'] : null;
                $confirmationText = isset($customTypes[$finalDeliveryPoint['confirmation_id']]) ? $customTypes[$finalDeliveryPoint['confirmation_id']] : null;
                
                $return['finalDeliveryPoint'] = [
                    'id' => $finalDeliveryPoint['id'],
                    'sequenceNumber' => (int) $finalDeliveryPoint['sequence_no'],
                    'shipTo' => [
                        'areaId' => $toAreaId,
                        'areaName' => $toAreaName,
                        'locationId' => $toLocationId,
                        'locationName' => $toLocationName,
                    ],
                    'courierId' => $courierId,
                    'courierName' => $courierName,
                    'expectedDate' => $finalDeliveryPoint['expected_delivery_date'],
                    //'confirmationId' => $finalDeliveryPoint['confirmation_id'],
                    //'confirmationRequired' => $finalDeliveryPoint['confirmation_id']
                    'confirmationRequired' => $finalDeliveryPoint['confirmation_id'],
                    'confirmationRequiredName' => $confirmationText
                ];

                unset($deliveryPoints[count($deliveryPoints) - 1]);

                foreach ($deliveryPoints as $deliveryPoint) {
                    
                    $toAreaId = isset($areas[$deliveryPoint['to_area_id']]) ? $deliveryPoint['to_area_id'] : null;
                    $toAreaName = isset($areas[$deliveryPoint['to_area_id']]) ? $areas[$deliveryPoint['to_area_id']] : null;
                    $toLocationId = isset($locations[$deliveryPoint['to_location_id']]) ? $deliveryPoint['to_location_id'] : null;
                    $toLocationName = isset($locations[$deliveryPoint['to_location_id']]) ? $locations[$deliveryPoint['to_location_id']] : null;
                    $courierId = isset($deliveryPoint['courier_id']) ? $deliveryPoint['courier_id'] : null;
                    $courierName = isset($deliveryPoint['courier']['name']) ? $deliveryPoint['courier']['name'] : null;
                    $confirmationText = isset($customTypes[$deliveryPoint['confirmation_id']]) ? $customTypes[$deliveryPoint['confirmation_id']] : null;
                    
                    $return['deliveryPoint'][] = [
                        'id' => $deliveryPoint['id'],
                        'sequenceNumber' => $deliveryPoint['sequence_no'],
                        'shipTo' => [
                            'areaId' => $toAreaId,
                            'areaName' => $toAreaName,
                            'locationId' => $toLocationId,
                            'locationName' => $toLocationName,
                        ],
                        'courierId' => $courierId,
                        'courierName' => $courierName,
                        'expectedDate' => $deliveryPoint['expected_delivery_date'],
                        //'confirmationId' => $deliveryPoint['confirmation_id'],
                        'confirmationRequired' => $deliveryPoint['confirmation_id'],
                        'confirmationRequiredName' => $confirmationText
                    ];
                }
            }
            
        }

        return $return;
    }
    
    
    
    /**
     * method to get the shipment packages details 
     * @param array $delId delivery id 
   
     */
    public function getShipmentPackageDetails($delId = '') {
        $data = $itemsData = [];

        if ($delId != '') {
            $pkgDetails = [];
            $deliveryDetails = $this->ShipmentLocations->getDeliveryPoint($delId);
            if (!empty($deliveryDetails)) {

                $data['sequenceNumber'] = $deliveryDetails['sequence_no'];
                $data['statusId'] = $deliveryDetails['status_id'];
                $data['deliveryComments'] = $deliveryDetails['delivery_comments'];
                $data['confirmationComments'] = $deliveryDetails['confirmation_comments'];
                $confListType  = $this->Common->getTypeLists(_CONFIRMATION_LIST_TYPES_CODE,true);
                $statusName = $this->Administration->getStatusList('all',['name'],['id'=>$deliveryDetails['status_id']]);
                if(!empty($statusName))
                $data['statusName'] = isset(current($statusName)['name'])?current($statusName)['name']:'';   
              
                if(!empty($confListType)){
                $data['confirmationName'] = isset($confListType[$deliveryDetails['confirmation_id']])?$confListType[$deliveryDetails['confirmation_id']]:'';   
                }
               
                $data['confirmationId'] = $deliveryDetails['confirmation_id'];
                $data['toLocationId'] = $deliveryDetails['location']['id'];
                $data['toLocation'] = $deliveryDetails['location']['name'];
                $data['shipmentCode'] = $deliveryDetails['shipment']['code'];
                $data['shipmentId'] = $deliveryDetails['shipment_id'];
                $data['latitude'] = (float) $deliveryDetails['delivery_latitude'];
                $data['longitude'] = (float) $deliveryDetails['delivery_longitude'];

                if (!empty($data['shipmentCode'])) {
                    $conditions['Shipments.code'] = $data['shipmentCode'];
                    $fields = [];
                    $extra['contain'] = true;
                    $extra['model'] = ['Shipments', 'FieldOptionValues', 'ShipmentPackageItems'];
                    $pkgDt = $this->ShipmentPackages->getRecords($fields, $conditions, 'all', $extra); //get all packages 
                    
                    if (!empty($pkgDt)) {
                        $pkgCount = 0;
                        foreach ($pkgDt as $index => $pkgValue) {
                            if (isset($pkgValue['shipment_package_items']) && count($pkgValue['shipment_package_items']) > 0) {
                                foreach ($pkgValue['shipment_package_items'] as $innerIndex => $itemvalue) {
                                        $itemsData[$pkgCount] = [
                                        'id' => $itemvalue['id'],
                                        'itemId' => $itemvalue['item_id'],
                                        'quantity' => $itemvalue['quantity'],
                                        'itemConfirmationId' => $itemvalue['confirmation_id']
                                    ];
                                    $getItemdata = $this->Items->getItemDetails($itemvalue['item_id']);
                                    if (!empty($getItemdata)) {
                                        $itemDt = current($getItemdata);
                                        $itemsData[$pkgCount]['itemName'] = $itemDt['name'];
                                        $itemsData[$pkgCount]['itemCode'] = $itemDt['code'];
                                        $itemTypeList= $this->Common->getTypeLists(_ITEM_LIST_TYPES_CODE,true);
                                         if(!empty($itemTypeList))                                             
                                        $itemsData[$pkgCount]['itemType'] =$itemTypeList[$itemDt['type_id']];
                                    }
                                   
                                    $itemsData[$pkgCount]['packageId'] = $pkgValue['id'];
                                    $itemsData[$pkgCount]['packageCode'] = $pkgValue['code'];
                                    $itemsData[$pkgCount]['shipmentId'] = $pkgValue['shipment_id'];
                                    $itemsData[$pkgCount]['shipmentCode'] = (isset($pkgValue['shipment']['code']) && !empty($pkgValue['shipment']['code'])) ? $pkgValue['shipment']['code'] : '';
                                    $itemsData[$pkgCount]['packageType'] = $pkgValue['package_type_id'];
                                    $itemsData[$pkgCount]['packageWeight'] = $pkgValue['package_weight'];
                                    $pkgtypeList= $this->Common->getTypeLists(_PACKAGE_LIST_TYPES_CODE,true);
                                    if(!empty($pkgtypeList))
                                    $itemsData[$pkgCount]['packageTypeName'] =$pkgtypeList[$pkgValue['package_type_id']] ;
                                    
                                    $pkgCount++;
                                }
                                
                            }
                           
                        }
                        //$data['packageDetails'] = $itemsData;
                        $data['packageDetails'] = array_values($itemsData);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * SAVE delivery point
     */
    public function saveDeliveryPoint($fieldsArray, $files = null) {
        
        $user = $this->Auth->user();
        
        // preapare data
        $insertDelivery = [
            'status_id' => $fieldsArray['statusId'],
            'id' => $fieldsArray['id'],
            'modified_user_id' => $user['id'],
        ];
        
        if(isset($fieldsArray['deliveryLatitude']))
            $insertDelivery['delivery_latitude'] = $fieldsArray['deliveryLatitude'];
        if(isset($fieldsArray['deliveryLongitude']))
            $insertDelivery['delivery_longitude'] = $fieldsArray['deliveryLongitude'];
        
        if(!empty($files)) {
            $imageContent = file_get_contents($files);
            @unlink($files);
            $insertAttachment = [
                'shipment_id' => $fieldsArray['shipmentId'],
                'sequence_no' => $fieldsArray['sequence'],
                'attachment' => $imageContent,
                'modified_user_id' => $user['id'],
                'created_user_id' => $user['id'],
            ];
        }
        
        if(isset($fieldsArray['deliveryComments'])) {
            $insertDelivery['delivery_comments'] = $fieldsArray['deliveryComments'];
            $insertAttachment['attachment_type'] = _ATTACHMENT_TYPE_DELIVERY;
        }
        if(isset($fieldsArray['confirmationComments'])) {
            $insertDelivery['confirmation_comments'] = $fieldsArray['confirmationComments'];
            $insertAttachment['attachment_type'] = _ATTACHMENT_TYPE_CONFIRMATION;
            
            // Check if we need to accept confirmation or NOT
            $saveConfirmationValue = true;
            $isConfirmed = $this->Common->getSystemConfig('list', ['fields' => ['code', 'value'], 'conditions' => ['code' => 'confirmations']]);
            if(!empty($isConfirmed)) {
                if($isConfirmed['confirmations'] != 1)
                    $saveConfirmationValue = false;
            }
            
            // Update Package-Item Confirmation ID
            if(isset($fieldsArray['packageDetails']) && $saveConfirmationValue == true) {
                
                $updatedPackage = [];
                
                foreach($fieldsArray['packageDetails'] as $package) {
                    
                    // Update package Items confirmation
                    $confirmationId = $package['itemConfirmationId'];
                    $savePackageItem = [
                        'id' => $package['id'],
                        'confirmation_id' => $confirmationId
                    ];
                    
                    $this->ShipmentPackageItems->saveRecords($savePackageItem);
                    
                    // Update package also, if any one item is set to YES
                    if(!in_array($package['packageId'], $updatedPackage)) {
                        $updatedPackage[] = $package['packageId'];
                        $savePackage = [
                            'id' => $package['packageId'],
                            'confirmation_id' => $confirmationId
                        ];
                        $this->ShipmentPackages->saveRecords($savePackage);
                    }
                }
            }
            
        }
        
        $shipmentLocationId = $this->ShipmentLocations->saveRecords($insertDelivery);
        
        if(is_numeric($shipmentLocationId) && !empty($shipmentLocationId)) {
            $shipmentMessage = 'Delivery status changed. Code - '.$fieldsArray['shipmentCode'].'. Status - ' . $fieldsArray['statusName'];
            $this->Subscription->sendSmsToSubscribers($fieldsArray['toLocationId'], $shipmentMessage);
        }
        
        if(!empty($files)) {
            // Delete attachment if exists as we do not have ID to update it, then add the new one
            $this->ShipmentLocationAttachments->deleteAll([
                'shipment_id' => $fieldsArray['shipmentId'], 
                'sequence_no' => $fieldsArray['sequence'], 
                'attachment_type' => $insertAttachment['attachment_type']
            ]);
            
            $this->ShipmentLocationAttachments->saveRecords($insertAttachment);
        }
        
        return true;
    }
    
    /**
     *  method for all shipment delivery list 
     * @return array shipment delivery list 
     * confirmation boolean if true will return confirmation list 
     */
    public function getDeliveryList($confirmation = false) {
        $conditions = $deliveryList = [];
        $assignedcourierId = $this->Auth->user('courier_id');
        $assignedlocationId = $this->Auth->user('location_id');
        $assignedareaId = $this->Auth->user('area_id');
        $confirmationId = '';
        $conditions =["ShipmentLocations.shipment_id IS NOT NULL "];
        if ($confirmation == true) {
            $confirmationDetails = $this->FieldOptionValues->getIdByName(_YES,_CONFIRMATION_LIST_TYPES);
            if (!empty($confirmationDetails['id'])) {
                $confirmationId = $confirmationDetails['id'];
                $conditions =array_merge($conditions, ['ShipmentLocations.confirmation_id' => $confirmationId]);
                
            }
        }
        if(!empty($assignedlocationId)){
             $conditions =array_merge($conditions, ['ShipmentLocations.to_location_id' => $assignedlocationId]);
        }
        if(!empty($assignedAreaId)){
            $conditions =array_merge($conditions, ['ShipmentLocations.to_area_id' => $assignedAreaId]);
        }
        
        if(!empty($assignedcourierId)){
            $conditions =array_merge($conditions, ['ShipmentLocations.courier_id' => $assignedcourierId]);
        }

        //$delPoints = $this->ShipmentLocations->getDeliveryList($confirmation,$confirmationId);
        $delPoints = $this->ShipmentLocations->getDeliveryDetails($conditions, ['Shipments', 'locations', 'Areas', 'Couriers']);
        if (!empty($delPoints) && count($delPoints) > 0) {
            // get all status color codes
            $statusData = $this->Administration->getStatusList('all', ['id','name','color_code'], []);
            $statusDetails = $this->Administration->convertStatusIntoArray($statusData);

            foreach ($delPoints as $index => $value) {

                $deliveryList[$index]['shipmentId'] = $value['shipment_id'];
                $deliveryList[$index]['deliveryId'] = $value['id'];
                $deliveryList[$index]['sequenceNo'] = $value['sequence_no'];
                $deliveryList[$index]['confirmationId'] = $confirmationId;
                $deliveryList[$index]['location'] = $value['location']['name'];
                $deliveryList[$index]['locationId'] = $value['to_location_id'];
                $deliveryList[$index]['areaId'] = $value['to_area_id'];
                $deliveryList[$index]['area'] = (isset($value['area']['name'])) ? $value['area']['name'] : '';
                $statusName = (isset($statusDetails[$value['status_id']])) ? $statusDetails[$value['status_id']]['name'] : '';
                
                if (isset($statusName) && !empty($statusName)) {
                    $deliveryList[$index]['statusName'] = $statusName;
                    $deliveryList[$index]['statusColorCode'] = $statusDetails[$value['status_id']]['color_code'];
                }

                $deliveryList[$index]['statusId'] = $value['status_id'];
                $deliveryList[$index]['modified'] = $value['modified'];
                $deliveryList[$index]['code'] = $value['shipment']['code'];
                $deliveryList[$index]['Courier'] = (isset($value['courier']['name'])) ? $value['courier']['name'] : '';
            }
        }

        return $deliveryList;
    }
    
    
    /**
     * method to get shipment labels
     * getShipmentList
    */

}
