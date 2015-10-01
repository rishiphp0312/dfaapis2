<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;

/**
 * Items Component
 */
class ItemsComponent extends Component {

    public $Items = null;
    public $components = ['Auth', 'Common', 'UserCommon', 'Administration'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->Items = TableRegistry::get('Items');
        $this->ShipmentPackageItems = TableRegistry::get('ShipmentPackageItems');
    }

    /**
     * GET Items List
     */
    public function getPackageItemsList() {
        $fields = $conditions = [];
        $fields = ['id', 'name', 'code'];
        $itemsList = $this->Items->getRecords($fields, $conditions);


        return $itemsList;
    }

    /**
     * method to get the item details 
     * @itemId 
     */
    public function getItemDetails($itemId = '') {
        $fields = [];
        $conditions = ['Items.id' => $itemId];
        return $data = $this->Items->getRecords($fields, $conditions);
    }

    /** method to check item code
     * if 0 means does not exists
     * 
     * @param type $code
     * @param type $itemId
     * return boolean 0/1
     */
    public function checkItemCode($code='',$itemId=''){
        $conditions=[];
        if($itemId!='')
            $conditions['id !=']=$itemId;
          if($code!='')
            $conditions['code']=$code;
          
        return $this->Items->getcount($conditions);
    }
    /** method to check item code
     * if 0 means does not exists
     * 
     * @param type $name
     * @param type $itemId
     * return boolean 0/1
     */

    public function checkItemName($name='',$itemId=''){
        $conditions=[];
        if($itemId!='')
            $conditions['id !=']=$itemId;
          if($name!='')
            $conditions['name']=$name;
        return $this->Items->getcount($conditions);
    }

    /**

     * method to get validated package items posted data
     * @fields posted array 

     */ 
      public function validateItems($fields = []) {

          if (count($fields) > 0) {
            $itemCode = (isset($fields['itemCode'])) ? trim($fields['itemCode']) : '';
            $itemId = (isset($fields['id'])) ? trim($fields['id']) : '';
            $itemType = (isset($fields['itemType'])) ? trim($fields['itemType']) : '';
            $itemName = (isset($fields['itemName'])) ? trim($fields['itemName']) : '';
            $statusId = (isset($fields['statusId'])) ? trim($fields['statusId']) : '';
           // die;
            if(empty($itemType) ){
                 return ['errCode' => _ERR105];
            }

            if (empty($itemCode)) {
                return ['errCode' => _ERR130];
            } else {
                $validlength = $this->Common->checkBoundaryLength($itemCode, _ITEM_CODE_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR158];               // code  length
                }
                $chkCode = $this->checkItemCode($itemCode, $itemId); //if >0 means code exists
                if ($chkCode > 0) {
                    return ['errCode' => _ERR159];
                }
            }
            if (empty($itemName)) {
                return ['errCode' => _ERR130];
            } else {

                $validlength = $this->Common->checkBoundaryLength($itemName, _ITEM_NAME_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR160];               // item name   length
                }
                $chkName = $this->checkItemName($itemName, $itemId); //if >0 means item name    exists
                if ($chkName > 0) {
                    return ['errCode' => _ERR161];
                }
            }

            ///
        } else {
            return ['errCode' => _ERR105];
        }
    } 
    
    
    /**
     *  get all records of items 
     */
    public function getItemsList(){
           //['visible'=>_VISBLE]
            $itemsList=[]; $modifyBy='';
            $itemsData = $this->Items->getRecords([], [], 'all');
            if (!empty($itemsData) ) {
            foreach($itemsData as $index=>$value){
                
                 if($value['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($value['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }
                
                 
               
                $itemsList[]=['code'=>$value['code'],'name'=>$value['name'],'id' => $value['id'],'modifyBy'=>$modifyBy,'modified'=>$value['modified'],
                    'type'=>$value['field_option_value']['name']
                   // 'statusId'=>$value['status_id'],'comments'=>$value['comments']
                        ];
                $modifyBy='';
            }
                
            }
            return $itemsList;
    }
    
    
    
    /**
     * method to get area details 
     * $itemId is item id 
     */
    public function getItemDetailsById($itemId = '') {
        ///'visible'=>_VISBLE
        $itemsList=[];
        $modifyBy = '';
        if ($itemId != '') {
            $itemsData = $this->Items->getRecords([], [['Items.id' => $itemId]], 'all', ['first' => true]);
            if (!empty($itemsData)) {
                if($itemsData['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($itemsData['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }
                $itemsList = ['itemCode' => $itemsData['code'],'itemName' => $itemsData['name'], 
                    'itemType'=>$itemsData['type_id'],
                    'id' => $itemsData['id'],'modifyBy' => $modifyBy, 'modified' => $itemsData['modified'],'comments' => $itemsData['comments'],
                     'statusId' => $itemsData['status_id'],'TypeName'=>$itemsData['field_option_value']['name']
                    ];
            }

            return $itemsList;
        } else {
            return ['errorCode' => _ERR105];
        }
    }
    
    /** deleteItems method to delete items 
     * 
     * @param type $itemId
     */
    public function deleteItems($itemId=''){
        if ($itemId != '') {
            $conditions = ['id' => $itemId];
            $itemDets = $this->Items->deleteRecords($conditions);
            if ($itemDets > 0) {
                $conditions = [];
                $conditions = ['item_id'=>$itemId];
                $deleteshipmentPkgsItemsIds = $this->ShipmentPackageItems->deleteRecords($conditions);
                return true;
            } else {
                return ['errorCode' => _ERR100];      // user not modified due to database error 
            }
        } else {
            return ['errorCode' => _ERR105];      // user not modified due to database error 
        }
    }
    
    /**
     * saveItemsDetails -
     * 
     * 
     *  $data
     */
    public function saveItemsDetails($data=[]) {
                $savedata=[];
                $errorCode = $this->validateItems($data);//validate data 
                if(isset($errorCode['errCode'])){
                    return ['error'=>$errorCode['errCode']];
                }
                
                $savedata['code'] = $data['itemCode'];
                $savedata['name'] = ucfirst($data['itemName']);
                $savedata['status_id'] = $data['statusId'];
                $savedata['comments'] = (isset($data['comments']) && !empty($data['comments']))?$data['comments']:'';               
                $savedata['type_id'] = $data['itemType'];
                //$savedata['type_id'] = $data['itemType'];
                //$savedata['comm11'] = $data['comments'];
                
                if (empty($data['id'])) {   
                 $savedata['created_user_id'] = $this->Auth->User('id');
                 $savedata['modified_user_id'] = $this->Auth->User('id');
                }else{
                 $savedata['modified_user_id'] = $this->Auth->User('id');
                }
                
                if (!empty($data['id'])) {                  
                   $itemNid = $this->Items->updateRecords($savedata,['id'=>$data['id']]); 
                   unset($data['id']);
                }else{
                   $itemNid = $this->Items->saveItem($savedata); 
                }
                if($itemNid>0){
                    return true;
                }else{
                  return ['errorCode' => _ERR100];      // user not modified due to database error 
                }
               
    }
    
}
