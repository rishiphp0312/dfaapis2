<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;

/**
 * Courier Component
 */
class CourierComponent extends Component {

    public $Items = null;
    public $components = ['Auth', 'Common', 'UserCommon', 'Administration'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->CourierObj = TableRegistry::get('Couriers');
        $this->ShipmentLocations = TableRegistry::get('ShipmentLocations');
    }

    
    

    /** method to check Courier code
     * if 0 means does not exists
     * 
     * @param type $code
     * @param type $courierId
     * return boolean 0/1
     */

    public function checkCourierCode($code='',$courierId=''){
        $conditions=[];
        if($courierId!='')
            $conditions['id !=']=$courierId;
          if($code!='')
            $conditions['code']=$code;
          
        return $this->CourierObj->getcount($conditions);
    }
    
    /** method to check Courier Name
     * if 0 means does not exists
     * 
     * @param type $name
     * @param type $courierId
     * return boolean 0/1
     */
    public function checkCourierName($name='',$courierId=''){
        $conditions=[];
        if ($courierId != '')
            $conditions['id !='] = $courierId;
        if ($name != '')
            $conditions['name'] = $name;
        return $this->CourierObj->getcount($conditions);
    }

    /**

     * method to get validated  courier posted data
     * @fields posted array 

     */ 
      public function validateCourier($fields = []) {

          if (count($fields) > 0) {
            $code = (isset($fields['code'])) ? trim($fields['code']) : '';
            $courierId = (isset($fields['id'])) ? trim($fields['id']) : '';
            $type = (isset($fields['type'])) ? trim($fields['type']) : '';
            $name = (isset($fields['name'])) ? trim($fields['name']) : '';
            $statusId = (isset($fields['statusId'])) ? trim($fields['statusId']) : '';
            $contact = (isset($fields['contact'])) ? trim($fields['contact']) : '';
            $phone = (isset($fields['phone'])) ? trim($fields['phone']) : '';
            $fax = (isset($fields['fax'])) ? trim($fields['fax']) : '';
            $comments = (isset($fields['comments'])) ? trim($fields['comments']) : '';
            $email = (isset($fields['email'])) ? trim($fields['email']) : '';
          
            if(empty($type) ){
                 return ['errCode' => _ERR105];
            }
            
            if (empty($code)) {
                return ['errCode' => _ERR169];
            } else {
                $validlength = $this->Common->checkBoundaryLength($code, _COURIER_CODE_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR164];               // code  length
                }
                $chkCode = $this->checkCourierCode($code, $courierId); //if >0 means code exists
                if ($chkCode > 0) {
                    return ['errCode' => _ERR162];
                }
            }
            if (empty($name)) {
                return ['errCode' => _ERR170];
            } else {

                $validlength = $this->Common->checkBoundaryLength($name, _COURIER_NAME_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR163];               // courier name   length
                }
                $chkName = $this->checkCourierName($name, $courierId); //if >0 means courier name    exists
                if ($chkName > 0) {
                    return ['errCode' => _ERR168];
                }
            }            
            if (empty($contact)) {
                return ['errCode' => _ERR171];
            } else {
                $validlength = $this->Common->checkBoundaryLength($contact, _COURIER_CONTACT_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR172];               // courier name   length
                }               
            }
            
            if (empty($phone)) {
                return ['errCode' => _ERR173];
            } else {
                $validlength = $this->Common->checkBoundaryLength($phone, _COURIER_PHONE_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR174];               // courier name   length
                }               
            }
            if (empty($email)) {
                return ['errCode' => _ERR175];
            } else {
                $validlength = $this->Common->checkBoundaryLength($email, _COURIER_EMAIL_LENGTH); //100 only
                if ($validlength == false) {
                    return ['errCode' => _ERR176];               // courier email   length
                    
                }
                $verifyEmail = $this->Common->validEmail($email);
                if ($verifyEmail == false) {
                    return ['errCode' => _ERR103]; //invalid format 
                }
            }
            if (!empty($comments)) {
                $validlength = $this->Common->checkBoundaryLength($comments, _COMMENTS_LENGTH); //65535 only
                if ($validlength == false) {
                    return ['errCode' => _ERR139];               // comments   length exceeeded
                }               
            }
            
            if (!empty($fax)) {
                $validlength = $this->Common->checkBoundaryLength($fax, _COURIER_FAX_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR177];               // courier name   length
                }               
            }
            

            ///
        } else {
            return ['errCode' => _ERR105];
        }
    } 
    
    
    /**
     *  get all records of couriers 
     */
    public function getCourierList(){
           //['visible'=>_VISBLE]
            $couriersList=[]; $modifyBy='';
            $courierData = $this->CourierObj->getRecords([], [], 'all');
            if (!empty($courierData) ) {
            foreach($courierData as $index=>$value){
                
                 if($value['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($value['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }
                
                 
               
                $couriersList[]=['code'=>$value['code'],'name'=>$value['name'],'id' => $value['id'],'modifyBy'=>$modifyBy,'modified'=>$value['modified'],
                 //   'type'=>$value['field_option_value']['name']
                   // 'statusId'=>$value['status_id'],'comments'=>$value['comments']
                        ];
                $modifyBy='';
            }
                
            }
            return $couriersList;
    }
    
    
    
    /**
     * method to get courier  details 
     * $courierId is courier Id 
     */
    public function getCourierDetailsById($courierId = '') {
        ///'visible'=>_VISBLE
        $couriersList=[];
        $modifyBy = '';
        if ($courierId != '') {
            $Data = $this->CourierObj->getRecords([], [['Couriers.id' => $courierId]], 'all', ['first' => true]);
            if (!empty($Data)) {
                if($Data['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($Data['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }
                $couriersList = ['code' => $Data['code'],'name' => $Data['name'], 
                    'type'=>$Data['type_id'],
                    'id' => $Data['id'],'modifyBy' => $modifyBy, 'modified' => $Data['modified'],
                     'comments' => $Data['comments'],
                     'statusId' => $Data['status_id'],                   
                     'contact' => $Data['contact'],                   
                     'phone' => $Data['phone'],                   
                     'fax' => $Data['fax'],                   
                     'email' => $Data['email'],                   
                     'typeName' => $Data['field_option_value']['name'],                   
                   
             
                    //'TypeName'=>$itemsData['field_option_value']['name']
                    ];
            }

            return $couriersList;
        } else {
            return ['errorCode' => _ERR105];
        }
    }
    
    /** deleteCourier method to delete Courier 
     * 
     * @param type $itemId
     */
    public function deleteCourier($courierId=''){
        if ($courierId != '') {
            $conditions = ['id' => $courierId];
            $courDels = $this->CourierObj->deleteRecords($conditions);
            if ($courDels > 0) {
                $conditions = [];
                $conditions = ['courier_id' => $courierId];
                $deleteshipmentLocationIds = $this->ShipmentLocations->deleteRecords($conditions);
                
                return true;
            } else {
                return ['errorCode' => _ERR100];      // user not modified due to database error 
            }
        } else {
            return ['errorCode' => _ERR105];      // user not modified due to database error 
        }
    }
    
    /**
     * saveCourierDetails -
     * 
     * 
     *  $data
     */
    public function saveCourierDetails($data=[]) {
                $savedata=[];
                $errorCode = $this->validateCourier($data);//validate data 
                if(isset($errorCode['errCode'])){
                    return ['error'=>$errorCode['errCode']];
                }
                
                $savedata['code'] = $data['code'];
                $savedata['contact'] = $data['contact'];
                $savedata['phone'] = $data['phone'];
                $savedata['fax'] = (isset($data['fax']) && !empty($data['fax']))?$data['fax']:''; 
                $savedata['name'] = ucfirst($data['name']);
                $savedata['status_id'] = $data['statusId'];
                $savedata['comments'] = (isset($data['comments']) && !empty($data['comments']))?$data['comments']:'';               
                $savedata['type_id'] = $data['type'];
                $savedata['email'] = $data['email'];
                
                if (empty($data['id'])) {   
                    $savedata['created_user_id'] = $this->Auth->User('id');
                    $savedata['modified_user_id'] = $this->Auth->User('id');
                }else{
                    $savedata['modified_user_id'] = $this->Auth->User('id');
                }                
                if (!empty($data['id'])) {                  
                   $courierNid = $this->CourierObj->updateRecords($savedata,['id'=>$data['id']]); 
                   unset($data['id']);
                }else{
                   $courierNid = $this->CourierObj->saveCourier($savedata); 
                }
                if($courierNid>0){
                    return true;
                }else{
                  return ['errorCode' => _ERR100];      // user not modified due to database error 
                }
               
    }
    
}
