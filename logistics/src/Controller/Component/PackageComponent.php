<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Configure;
use Cake\Network\Email\Email;

/**
 * Package Component
 */
class PackageComponent extends Component {

    public $Users = '';
    public $Roles = '';
    public $ShipmentPackages = '';
    public $Shipments = '';
    public $ShipmentPackageItems = '';
    public $TempPackagesObj = '';
    public $components = ['Auth', 'Common', 'UserCommon','Items'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->Users = TableRegistry::get('Users');
        $this->Roles = TableRegistry::get('Roles');
        $this->ShipmentPackages = TableRegistry::get('ShipmentPackages');
        $this->ShipmentPackageItems = TableRegistry::get('ShipmentPackageItems');
        $this->Shipments = TableRegistry::get('Shipments');
        $this->TempPackagesObj = TableRegistry::get('TempPackages');
    }

    /**

     * method to manage  package with items
     *
     * @inputArray posted array
     * 
     */
    public function savePackageDetails($inputArray = array()) {

        $case = 'INSERT';
        if (isset($inputArray['packageId']) && $inputArray['packageId'] != '') {
            $case = 'UPDATE';
        }
        $validated = $this->getValidatedPackageFields($inputArray); //validate input details 
        if (isset($validated['errCode'])) {
            return ['error' => $validated['errCode']];
        }

        if (empty($inputArray['packageId'])) {
            if (!isset($inputArray['created_user_id']))
                $inputArray['created_user_id'] = $this->Auth->User('id');
        }else {
            $inputArray['id'] = $inputArray['packageId'];
        }
        if (!isset($inputArray['modified_user_id']))
            $inputArray['modified_user_id'] = $this->Auth->User('id');

        $inputArray['shipment_id'] = $inputArray['shipmentId'];
        $inputArray['package_type_id'] = $inputArray['type'];
        $inputArray['package_weight'] = $inputArray['weight'];
        $inputArray['confirmation_id'] = '0';
        $inputArray['code'] = $inputArray['packageCode'];
        $itemsInput = $inputArray['items'];
        unset($inputArray['items']);
        unset($inputArray['shipmentCode']);

        $pkgId = $this->ShipmentPackages->addModifyPackage($inputArray); //add modify package  
        if ($pkgId > 0) {
            //manage  items 
            $this->managePackageItems($itemsInput, $inputArray['shipment_id'], $pkgId, $case);
            return true;
        } else {
            return ['errorCode' => _ERR100];      // user not modified due to database error 
        }
    }

    /**

     * method to get validated package items posted data
     * @fields posted array 

     */
    public function validatePackageItems($fields = []) {
        if (count($fields) > 0) { 
            $postedItemids = $this->checkDuplicateItems($fields);
            foreach ($fields as $value) {
                $itemQty = (isset($value['quantity'])) ? trim($value['quantity']) : '';
                $itemId = (isset($value['itemId'])) ? trim($value['itemId']) : '';
                
                if ($postedItemids['itemsIds'][$itemId] > 1) {  
                    return ['errCode' => _ERR144];  // items already exists
                }
                
                if (!empty($itemQty) && !empty($itemId)) {
                    $validlength = $this->Common->checkBoundaryLength($itemQty, _PKG_ITEMQTY_LENGTH); //5 only
                   
                    if ($validlength == false) {
                        return ['errCode' => _ERR129];               // item qty   length 99999
                    }
                }
            }
        } else {
            return ['errCode' => _ERR138]; // empty package items
        }
    }

   /**
    * 
    * @param type $dataArray
    * @return type array of count of each items ids 
    */
    public function  checkDuplicateItems($dataArray=[]) {
        $itemsIds = [];
        $cnt = 0;
        if (isset($dataArray) && !empty($dataArray)) {
            foreach ($dataArray as $value) {
                //validate subgroup val details 
                $itemsIds[$cnt] = (isset($value['itemId'])) ? trim($value['itemId']) : '';
                $cnt++;
            }
            return ['itemsIds' => array_count_values($itemsIds)];
        }
    }

    /**
      method to get validated package posted data
     * @fields posted array 
     */
    public function getValidatedPackageFields($fields = []) {


        if (!empty($fields) && count($fields) > 0) {

            $code = (isset($fields['packageCode'])) ? trim($fields['packageCode']) : '';
            $pkgId = (isset($fields['packageId'])) ? trim($fields['packageId']) : '';
            $shipmentId = (isset($fields['shipmentId'])) ? trim($fields['shipmentId']) : '';
            $weight = (isset($fields['weight'])) ? trim($fields['weight']) : '';
            $type = (isset($fields['type'])) ? trim($fields['type']) : ''; //pkg type id

            if (empty($shipmentId) || empty($type)) {
                return ['errCode' => _ERR105]; //missing paramters 
            }
            if (empty($code)) {
                return ['errCode' => _ERR123];
            } else {
                $validlength = $this->Common->checkBoundaryLength($code, _PKG_CODE_LENGTH); //50 only
                if ($validlength == false) {
                    return ['errCode' => _ERR124];               // code  length 
                }
                $chkCode = $this->checkPkgCode($code, $pkgId); //if >0 means code exists 
                if ($chkCode > 0) {
                    return ['errCode' => _ERR125];
                }
            }
            if (empty($weight)) {
                return ['errCode' => _ERR136]; //wt is empty
            } else {
                $validlength = $this->Common->checkBoundaryLength($weight, _PKG_WGHT_LENGTH); //50 only
                if ($validlength == false) {
                    return ['errCode' => _ERR128];               // code  length 
                }
            }
            if (isset($fields['items']) && !empty($fields['items']))
                return $this->validatePackageItems($fields['items']);
            ///
        } else {
            return ['errCode' => _ERR105];
        }
    }

    /**
     * 
     * method to validate the username 
     * @username is the username 
     */
    public function validateUserName($username, $uId = '') {

        if (empty($username)) {
            return ['errCode' => _ERR101];
        } else {
            $validlength = $this->Common->checkBoundaryLength($username, _USERNAME_LENGTH); //128 only

            if ($validlength == false) {
                return ['errCode' => _ERR109];
                // username  length 
            }
            $chkusr = $this->checkUsernameExists($username, $uId); //check username exists or not 1 means username exists 

            if ($chkusr > 0) {
                return ['errCode' => _ERR112];
            }
        }
    }

    /**
     * 
     * method to get the list of packages  
     * returns array  
     * @shipId is the shipment_id
     * @others by default false if true associated with models Shipments, FieldOptionValues,ShipmentPackageItems
     */
    public function listAllPackages($shipCode = '') {
        $data = $fields = $conditions = [];
        $modifyBy = '';

        if ($shipCode != '') {

            $conditions['Shipments.code'] = $shipCode;
            $extra['contain'] = true;
            $extra['model'] = ['Shipments', 'FieldOptionValues'];

            $pkgDt = $this->ShipmentPackages->getRecords($fields, $conditions, 'all', $extra); //get all packages 
            if (!empty($pkgDt)) {
                foreach ($pkgDt as $value) {
                    $usrdetails = $this->UserCommon->getUserDetailsById($value['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    }

                    $itemsData = [];
                    $data[] = ['id' => $value['id'], 'code' => $value['code'],
                        'shipmentId' => $value['shipment_id'], 'shipCode' => $value['shipment']['code'],
                        'type' => $value['field_option_value']['name'],
                        'weight' => $value['package_weight'], 'modifiedBy' => $modifyBy, 'modified' => $value['modified'],
                        'itemsData' => $itemsData
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * deletePackage 
     * pkgId  package id 
     * 
    */
    public function deletePackage($pkgId = '') {

        $deletepkgs = 0;
        if (isset($pkgId) && !empty($pkgId)) {
            $cnt = $deletepkgs = $this->ShipmentPackages->deleteRecords(['id' => $pkgId]);
            if ($deletepkgs > 0) {
                $deletepkgsItems = $this->ShipmentPackageItems->deleteRecords(['package_id' => $pkgId]);
                return true;
            } else {
                return ['error' => _ERR100];
            }
        } else {
            return ['error' => _ERR105];
        }
    }

    /**
      checkEmailExists to check the duplicate email
     * 
     * returns the 0 or 1 0 means does not exist 1 means already exists 
     */
    public function checkPkgCode($code = null, $pkgId = null) {

        return $getcount = $this->ShipmentPackages->checkPkgCodeExists($code, $pkgId);
    }

    /**
     * 
     * method to add /modify package  items 
     * @itemsData array of posted items 
     * @shipId  shipment id 
     * @pkgId  package id 
     * $case INSERT/UPDATE
     */
    public function managePackageItems($itemsData = [], $shipId = '', $pkgId = '', $case) {
        $userId = $this->Auth->User('id');
        if ($pkgId != '' && $shipId != '' && $case == 'UPDATE') {
            $conditions = ['package_id' => $pkgId, 'shipment_id' => $shipId];
            $this->ShipmentPackageItems->deleteRecords($conditions);
        }//pr($itemsData);
        foreach ($itemsData as $value) {
            if(!empty($value['itemId'])) {
                $data = ['item_id' => $value['itemId'], 'quantity' => $value['quantity'],
                    'shipment_id' => $shipId, 'package_id' => $pkgId,
                    'created_user_id' => $userId, 'modified_user_id' => $userId,
                    'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s'),
                ];
                $this->ShipmentPackageItems->addModifyPackageItems($data);
                unset($data);
            }
        }
    }

    /**
     * 
     * method to genrate package code 
     * @data is array posted data 
     */
    public function getPackageCode($data = []) {
        $cntVal=0;
        $shipCode = (isset($data['shipCode']) && !empty($data['shipCode'])) ? $data['shipCode'] : '';
        if ($shipCode == '') {
            return ['error' => _ERR135];
        }
        $shipId = $this->Shipments->getShipmentId($shipCode);

        if (isset($shipId['id']) && $shipId['id'] != '') {
            
            $conditions = ['ShipmentPackages.shipment_id' => $shipId['id']];
            $lastCode = $this->ShipmentPackages->getMax('ShipmentPackages.code',$conditions);
            $lastCodeExp= explode(_DELEM3,$lastCode);
            $cntVal = ((isset($lastCodeExp[1])) ? $lastCodeExp[1] : 0) +1;
            /*
             * $allCodes = $this->ShipmentPackages->getRecords(['ShipmentPackages.id','ShipmentPackages.code'],$conditions,'all');
            $allcounters =[];
            if(!empty($allCodes)){
                foreach($allCodes as $index=> $value){
                    //echo $value['code'];
                    $allcounters[$index] = ltrim(strstr($value['code'],_DELEM3),_DELEM3);    
                }
            }*/
            
            /* $cntVal = $this->ShipmentPackages->getCount($conditions);
            * 
            */
            
            //$cntVal =  $this->ShipmentPackages->getMax('id');
            return ['packageCode' => $shipCode . _DELEM3 . $cntVal, 'shipmentId' => $shipId['id']];
        } else {
            return ['error' => _ERR135];
        }
    }

    /**
     * 
     * method to delete  package details  
     * @data is array posted data 
     */
    /*  public function getPackageCode($data = []) {
      $shipCode = (isset($data['shipCode']) && !empty($data['shipCode'])) ? $data['shipCode'] : '';
      if ($shipCode == '') {
      return ['error' => _ERR135];
      }
      $conditions = [];
      } */

    /**
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    public function getCount($conditions = []) {
        return $total = $this->find()->where($conditions)->count();
        //  return $total =  $this->query()->find()->where($conditions)->count();
    }

    /**
     * get PackageDetails
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     * $pkgId is the package Id 
     */
    public function getPackageDetails($pkgCode = '') {
        $data = $fields = $conditions = [];


        if ($pkgCode != '') {

            //$conditions['Shipments.code'] = $shipCode;
            $extra['contain'] = true;
            $extra['model'] = ['Shipments', 'ShipmentPackageItems','FieldOptionValues'];
            $conditions['ShipmentPackages.code'] = $pkgCode;
            $pkgDt = $this->ShipmentPackages->getRecords($fields, $conditions, 'all', $extra); //get all packages 
            if (!empty($pkgDt)) {
                //  foreach ($pkgDt as $value) {
                $pkgDt = current($pkgDt);

                $itemsData = [];
                if (count($pkgDt['shipment_package_items']) > 0) {
                    foreach ($pkgDt['shipment_package_items'] as $innerIndex=> $value) {
                        $itemsData[$innerIndex]['itemId'] = $value['item_id'];
                        $itemsData[$innerIndex]['quantity'] = $value['quantity'];
                        $itemDetails = $this->Items->getItemDetails($value['item_id']);                    
                        if(!empty($itemDetails)){
                            $itemDetails = current($itemDetails);
                            $itemsData[$innerIndex]['itemName'] = $itemDetails['name'];
                            $itemsData[$innerIndex]['itemCode'] = $itemDetails['code'];                            
                        }
                       
                    }
                }

                $data = ['packageId' => $pkgDt['id'], 'packageCode' => $pkgDt['code'],
                    'shipmentId' => $pkgDt['shipment_id'],
                    'shipmentCode' => $pkgDt['shipment']['code'],
                    'type' => $pkgDt['package_type_id'],
                    'typeName' => $pkgDt['field_option_value']['name'],
                    'weight' => $pkgDt['package_weight'],
                    'items' => $itemsData
                ];
            }
        }
        return $data;
    }
    
    
    /**
     * 
     * method to genrate package code with addictional checks 
     * @data is array posted data 
     */
    public function getPackageCode1($data = []) {
        $cntVal=0;
        $shipCode = (isset($data['shipCode']) && !empty($data['shipCode'])) ? $data['shipCode'] : 'HCA1443175507';
        if ($shipCode == '') {
            return ['error' => _ERR135];
        }
        $shipId = $this->Shipments->getShipmentId($shipCode);

        if (isset($shipId['id']) && $shipId['id'] != '') {
            
            $conditions = ['shipment_id' => $shipId['id']];
            $lastCode = $this->ShipmentPackages->getMax('ShipmentPackages.code',$conditions);
            /*
             * $allCodes = $this->ShipmentPackages->getRecords(['ShipmentPackages.id','ShipmentPackages.code'],$conditions,'all');
            $allcounters =[];
            if(!empty($allCodes)){
                foreach($allCodes as $index=> $value){
                    $allcounters[$index] = ltrim(strstr($value['code'],_DELEM3),_DELEM3);    
                }
            }
            */
           $lastCodeExp= explode(_DELEM3,$lastCode);
           
           $cntVal = $lastCodeExp[1]+1;
            // $cntVal = (!empty($allcounters))? max($allcounters):0;
            // compare ship code and cnt in temp packages
           $tempData= $conditions = []; 
           $conditions = ['shipment_code'=>$shipCode,'cnt'=>$cntVal];
           $getRowExists = $this->TempPackagesObj->getCount($conditions);
           if($getRowExists>0){               
            // if row exists then update it with new cnt 
               $cntVal=$cntVal+1;
               $tempData=['cnt'=>$cntVal];
               $this->TempPackagesObj->updateRecords($tempData,$conditions);
               
           }else{
            // if not then add new row
               $tempData=['shipment_code'=>$shipCode,'cnt'=>$cntVal];  
               $this->TempPackagesObj->saveRecords($tempData);
           }
            
            
            // after submission of package delete from temp table 
           
            /* $cntVal = $this->ShipmentPackages->getCount($conditions);
            * 
            */
            
            return ['packageCode' => $shipCode . _DELEM3 . $cntVal, 'shipmentId' => $shipId['id']];
        } else {
            return ['error' => _ERR135];
        }
    }

}
