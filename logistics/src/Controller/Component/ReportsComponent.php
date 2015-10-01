<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;

/**
 * Reports component
 */
class ReportsComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $Shipments = null;
    public $ShipmentLocations = null;
    public $Locations = null;
    public $ShipmentPackages = null;
    
    public $components = ['Shipment', 'Barcode.Barcode', 'Administration', 'Common', 'Items'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->Shipments = TableRegistry::get('Shipments');
        $this->ShipmentLocations = TableRegistry::get('ShipmentLocations');
        $this->ShipmentPackages = TableRegistry::get('ShipmentPackages');
        $this->Locations = TableRegistry::get('Locations');
      
    }
    
    /**
     * GET Shipment Labels
     * 
     * @return array Shipment Labels file name
     */
    public function getShipmentLabels($shipmentId = null) {
        
        $conditions = [];
        
        if(!empty($shipmentId))
            $conditions['Shipments.id'] = $shipmentId;
        
        $fields = [];
        $query = $this->Shipments->find('all', array('fields' => $fields, 'conditions' => $conditions))->contain(['ShipmentPackages', 'FromLocations', 'ToLocations']);
        $result = $query->hydrate(false)->first();
        
        if(!empty($result)) {
            
            $html = _ERR137; // A space is required to produce blank sheet
            $shipmentCode = $result['code'];
            
            // Including PDF generator lib
            require_once(ROOT . DS . 'vendor' . DS . 'dompdf-master' . DS . 'dompdf_config.inc.php');

            // PDF config
            $pdf = new \DOMPDF();
            $pdf->set_paper("A4","landscape");
            
            if(!empty($result['shipment_packages'])) {
                
                $packageCounter = 1;
                $shipmetCount = count($result['shipment_packages']);
                $weight_units = $this->Common->getSystemConfig('all', ['fields' => ['code', 'value', 'default_value'], 'conditions' => ['code' => 'weight_units']]);
                $weightUnit = $weight_units['weight_units']; 
                
                foreach($result['shipment_packages'] as $package) {
                    $packageCode = $package['code'];
                    $packageCount = $packageCounter . ' of ' . $shipmetCount;
                    $packageWeight = $package['package_weight'] . ' ' . $weightUnit;
                    
                    // Ship From
                    $shipFrom = $result['from_location']['name'] . _DELEM5 . $result['from_location']['address']; 
                    if(!empty($result['from_location']['postal_code'])) {
                        $shipFrom .= _DELEM5 . _SHIP_LABEL_POSTAL_CODE . $result['from_location']['postal_code'];
                    }
                    
                    // Ship To
                    $shipTo = $result['to_location']['name'] . _DELEM5 . $result['to_location']['address'];
                    if(!empty($result['to_location']['postal_code'])) {
                        $shipTo .= _DELEM5 . _SHIP_LABEL_POSTAL_CODE . $result['to_location']['postal_code'];
                    }
                    
                    // Generate Barcode
                    $barcode = $this->Barcode->generateBarcode($text = $result['code'] . ',' . $package['code'], $type = 'BCGcode128');
                   
                    $renderedView[] = $this->Common->getViewHtml([
                        'shipFrom' => $shipFrom,
                        'shipTo' => $shipTo,
                        'barcode' => base64_encode($barcode),
                        'shipmentCode' => $shipmentCode,
                        'packageCode' => $packageCode,
                        'packageCount' => $packageCount,
                        'packageWeight' => $packageWeight,
                    ], 'Labels', 'shipment_label');
                    
                    $packageCounter++;
                }
                
                $html = '<html><body>' . implode( '<div style="page-break-before: always;"></div>' , $renderedView ) . '</body></html>';
                
            }/* else {
                $return = ['error' => _ERR140];
            }*/
            
            // Generating New pages (i.e. page breaks)
            $pdf->load_html($html);
            $pdf->render();
            $pdf->stream('Shipment_labels_' . $shipmentCode . ".pdf");

            // Write HTML file - TESTING
            /*$htmlFileName = 'test' . time().rand(55, 998652) . '.html';
            $logfile = fopen(_TMP_PATH . DS . $htmlFileName, "w") or die("Unable to open file!");
            fwrite($logfile, $renderedView);
            fclose($logfile);*/
            exit;
            
        } else {
            $return = ['error' => _ERR137];
        }
        
        return $return;
    }
    
    
    /**
     * GET Package Labels
     * 
     * @return array Shipment Labels file name
     */
    public function getPackageLabels($shipmentId = null, $combinePacakges = false) {
        
        $conditions = [];
        
        if(!empty($shipmentId))
            $conditions['Shipments.id'] = $shipmentId;
        
        $fields = [
            'Shipments.code', 'Shipments.shipment_date', 
            'FromLocations.name', 'FromLocations.address', 'FromLocations.postal_code',
            'ToLocations.name', 'ToLocations.address', 'ToLocations.postal_code',
            ];
        $query = $this->Shipments->find('all', array('fields' => $fields, 'conditions' => $conditions))->contain(['FromLocations', 'ToLocations']);
        $result = $query->hydrate(false)->first();
        
        if(!empty($result)) {
            
            $shipmentCode = $result['code'];
            $shipmentDate = $result['shipment_date'];
            
            $shipFrom = $result['from_location']['name'] . _DELEM5 . $result['from_location']['address'];
            if(!empty($result['from_location']['postal_code'])) {
                $shipFrom .= _DELEM5 . _SHIP_LABEL_POSTAL_CODE . $result['from_location']['postal_code'];
            }
            
            $shipTo = $result['to_location']['name'] . _DELEM5 . $result['to_location']['address'];
            if(!empty($result['to_location']['postal_code'])) {
                $shipFrom .= _DELEM5 . _SHIP_LABEL_POSTAL_CODE . $result['to_location']['postal_code'];
            }
            
            $packageList = $this->getShipmentListwithPackageDetails(null, $shipmentId, false);
            $result = array_replace($result, $packageList);            
            
            // Including PDF generator lib
            require_once(ROOT . DS . 'vendor' . DS . 'dompdf-master' . DS . 'dompdf_config.inc.php');

            // PDF config
            $pdf = new \DOMPDF();
            $pdf->set_paper("A4","portrait");//$pdf->set_paper("A4","landscape");  
            
            if(!empty($result['packageList'])) {
                
                $weight_units = $this->Common->getSystemConfig('all', ['fields' => ['code', 'value', 'default_value'], 'conditions' => ['code' => 'weight_units']]);
                $weightUnit = $weight_units['weight_units'];         
                
                // Show all packages items on one page
                if($combinePacakges == true) {
                    $renderedView = $this->Common->getViewHtml([
                        'shipFrom' => $shipFrom,
                        'shipTo' => $shipTo,
                        'shipmentCode' => $shipmentCode,
                        'shipmentDate' => $shipmentDate,
                        'weightUnit' => $weightUnit,
                        'packageList' => $result['packageList'],
                    ], 'Labels', 'package_label');
                    
                } // Show different package items on different pages
                else {
                    foreach($result['packageList'] as $row) {
                        $packages[$row['packageCode']][] = $row;
                    }
                    
                    foreach($packages as $package) {
                        $renderedView[] = $this->Common->getViewHtml([
                            'shipFrom' => $shipFrom,
                            'shipTo' => $shipTo,
                            'shipmentCode' => $shipmentCode,
                            'shipmentDate' => $shipmentDate,
                            'weightUnit' => $weightUnit,
                            'packageList' => $package,
                        ], 'Labels', 'package_label');
                    }

                    // Generating New pages (i.e. page breaks)
                    $renderedView = '<html><body>' . implode( '<div style="page-break-before: always;"></div>' , $renderedView ) . '</body></html>'; //$html = $renderedView;
                }                
            } else {
                $renderedView = _ERR137; // A space is required to produce blank sheet
            }
            
            $pdf->load_html($renderedView);
            $pdf->render();
            $pdf->stream('Package_labels_' . $shipmentCode . ".pdf");
                
            // Write HTML file - TESTING
            /*$htmlFileName = 'Package_labels_' . $shipmentCode . '.html';
            $logfile = fopen(_TMP_PATH . DS . $htmlFileName, "w") or die("Unable to open file!");
            fwrite($logfile, $renderedView);
            fclose($logfile);*/
            
            exit;
        } else {
            $return = ['error' => _ERR137];
        }
        
        return $return;
    }
    
    
    /**$shipCode is the shipment code 
     * method to get shipment reports with delivery point details  
     */
    public function getShipmentListDetails($conditions = []) {

        $shipDt = $deliveryList = [];
        //$shipmentData = $this->Shipment->getShipmentList($options['conditions']=['Shipments.code'=>$shipCode]);
        $shipmentData = $this->Shipment->getShipmentList(['conditions' => $conditions]);
        if (!empty($shipmentData) && count($shipmentData) > 0) {
            foreach ($shipmentData as $index => $shipValue) {
                $shipDt[$index]['shipmentId'] = $shipValue['id'];
                $shipDt[$index]['shipmentCode'] = $shipValue['shipmentCode'];

                $shipDt[$index]['statusId'] = $shipValue['statusId'];
                $statusDt = $this->Administration->getStatusList('all', ['name', 'color_code'], ['id' => $shipValue['statusId']]);
                if (!empty($statusDt)) {
                    $shipDt[$index]['statusColor'] = isset(current($statusDt)['color_code']) ? current($statusDt)['color_code'] : '';
                    $shipDt[$index]['statusName'] = isset(current($statusDt)['name']) ? current($statusDt)['name'] : '';
                }

                $shipDt[$index]['areaId'] = $shipValue['shipToAreaId'];
                $shipDt[$index]['locationId'] = $shipValue['shipToId'];
                $shipDt[$index]['destinationName'] = $shipValue['shipTo'];

                $delPoints = $this->ShipmentLocations->getDeliveryDetails(['ShipmentLocations.shipment_id' => $shipValue['id']], ['locations', 'Couriers']);
                if (!empty($delPoints) && count($delPoints) > 0) {
                    $cnt = 1;
                    foreach ($delPoints as $innerIndex => $value) {
                        
                        $startLocation = '';
                        if ($value['sequence_no'] == 1) {

                            $deliveryList[0]['locationId'] = $value['from_location_id'];
                            $locationsDt = $this->Locations->getLocationDetails(['id' => $value['from_location_id']]);
                            if (!empty($locationsDt)) {
                                $deliveryList[0]['locationName'] = (isset($locationsDt['name'])) ? $locationsDt['name'] : '';
                                $deliveryList[0]['locationContact'] = (isset($locationsDt['contact_person'])) ? $locationsDt['contact_person'] : '';
                                $deliveryList[0]['locationCode'] = (isset($locationsDt['code'])) ? $locationsDt['code'] : '';
                                $deliveryList[0]['locationContactNo'] = (isset($locationsDt['telephone'])) ? $locationsDt['telephone'] : '';
                                $deliveryList[0]['lng'] = (isset($locationsDt['longitude'])) ? $locationsDt['longitude'] : '';
                                $deliveryList[0]['lat'] = (isset($locationsDt['latitude'])) ? $locationsDt['latitude'] : '';
                            }
                            $deliveryList[0]['estimatedDate'] = '';
                            $deliveryList[0]['deliveryId'] = $value['id'];
                            $deliveryList[0]['sequenceNo'] = 0;
                            $deliveryList[0]['statusId'] = $value['status_id'];
                            
                            $statusDt = $this->Administration->getStatusList('all', ['name', 'color_code'], ['id' => $value['status_id']]);
                            if (!empty($statusDt)) {
                                $deliveryList[0]['statusColor'] = isset(current($statusDt)['color_code']) ? current($statusDt)['color_code'] : '';
                                $deliveryList[0]['statusName'] = isset(current($statusDt)['name']) ? current($statusDt)['name'] : '';
                            }
                            $deliveryList[0]['courier'] = '';
                            $deliveryList[0]['courierContactNo'] = '';
                        }

                        $expectedDelivery = $value['expected_delivery_date'];
                        $deliveryList[$cnt]['estimatedDate'] = $expectedDelivery;
                        $deliveryList[$cnt]['deliveryId'] = $value['id'];
                        $deliveryList[$cnt]['sequenceNo'] = $value['sequence_no'];
                        $deliveryList[$cnt]['statusId'] = $value['status_id'];
                        $statusDt = $this->Administration->getStatusList('all', ['name', 'color_code'], ['id' => $value['status_id']]);
                        if (!empty($statusDt)) {
                            $deliveryList[$cnt]['statusColor'] = isset(current($statusDt)['color_code']) ? current($statusDt)['color_code'] : '';
                            $deliveryList[$cnt]['statusName'] = isset(current($statusDt)['name']) ? current($statusDt)['name'] : '';
                        }
                        $deliveryList[$cnt]['locationId'] = $value['to_location_id'];

                        $deliveryList[$cnt]['locationName'] = (isset($value['location']['name'])) ? $value['location']['name'] : '';
                        $deliveryList[$cnt]['locationContact'] = (isset($value['location']['contact_person'])) ? $value['location']['contact_person'] : '';
                        $deliveryList[$cnt]['locationCode'] = (isset($value['location']['code'])) ? $value['location']['code'] : '';
                        $deliveryList[$cnt]['locationContactNo'] = (isset($value['location']['telephone'])) ? $value['location']['telephone'] : '';
                        $deliveryList[$cnt]['lng'] = (isset($value['location']['longitude'])) ? $value['location']['longitude'] : '';
                        $deliveryList[$cnt]['lat'] = (isset($value['location']['latitude'])) ? $value['location']['latitude'] : '';
                            
                        $deliveryList[$cnt]['courier'] = (isset($value['courier']['name']) && !empty($value['courier']['name'])) ? $value['courier']['name'] : '';
                        $deliveryList[$cnt]['courierContactNo'] = (isset($value['courier']['phone']) && !empty($value['courier']['phone'])) ? $value['courier']['phone'] : '';

                        $cnt++;
                    }
                    $shipDt[$index]['expectedDelivery'] = $expectedDelivery;
                    $shipDt[$index]['deliveryPoints'] = $deliveryList;
                    unset($deliveryList);
                }
            }
        }
        return $shipDt;
    }
    
    
    /**
     * method to get shipment reports with delivery point details  and package list 
    */
    public function getShipmentListwithPackageDetails($shipCode = null, $shipmentId = null, $includeShipDetails = true) {
    
        $deliveryPointsdata = $fields = $conditions = $itemsData = [];
        
        if (!empty($shipCode) || !empty($shipmentId)) {
            if(!empty($shipCode)) {
                $conditions['Shipments.code'] = $shipCode;
            } else if(!empty($shipmentId)) {
                $conditions['Shipments.id'] = $shipmentId;
            }
            
            if($includeShipDetails == true) {
                $deliveryPointsdata = $this->getShipmentListDetails($conditions);
                $deliveryPointsdata = current($deliveryPointsdata);
            }
            
            $extra['contain'] = true;
            $extra['model'] = ['Shipments', 'FieldOptionValues','ShipmentPackageItems'];
            $pkgDt = $this->ShipmentPackages->getRecords($fields, $conditions, 'all', $extra); //get all packages 
          
            if (!empty($pkgDt)) {
                $cnt = 0;
                foreach ($pkgDt as $value) {
                    if (count($value['shipment_package_items']) > 0) {
                        foreach ($value['shipment_package_items'] as $innerIndex => $itemvalue) {
                            $itemsData[$cnt]['itemId'] = $itemvalue['item_id'];
                            $itemsData[$cnt]['quantity'] = $itemvalue['quantity'];
                            $itemTypeDet = $this->Items->getItemDetails($itemvalue['item_id']);
                            if (!empty($itemTypeDet)) {
                                $itemTypeDet = current($itemTypeDet);
                                $itemsData[$cnt]['itemtype'] = $itemTypeDet['field_option_value']['name'];
                            }
                            //$itemsData[$cnt]['itemtype'] = $itemvalue['field_option_value']['name'];//  'type' => $value['field_option_value']['name'],
                            $itemsData[$cnt]['packageCode'] = $value['code'];
                            $itemsData[$cnt]['packageWeight'] = $value['package_weight'];
                            // $itemsData[$cnt]['packageWeight'] = $value['package_weight'];
                            $itemDetails = $this->Items->getItemDetails($itemvalue['item_id']);
                            if (!empty($itemDetails)) {
                                $itemDetails = current($itemDetails);
                                $itemsData[$cnt]['itemName'] = $itemDetails['name'];
                                $itemsData[$cnt]['itemCode'] = $itemDetails['code'];
                            }
                            $cnt++;
                        }
                    }
                }
            }
            
            if (isset($itemsData) && count($itemsData) > 0)
                $deliveryPointsdata['packageList'] = $itemsData;
        }
        
        return $deliveryPointsdata;
    }
    
    /**
     * method to get shipment labels 
     * shipment details with package count
     * shipment labels list 
    */
    public function shipmentLabelsList(){
        return $this->Shipment->getShipmentList([],true);
       
    }

}
