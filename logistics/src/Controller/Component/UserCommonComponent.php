<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Configure;
use Cake\Network\Email\Email;
use Cake\View\View;

/**
 * Common period Component
 */
class UserCommonComponent extends Component {

    public $Users = '';
    public $Roles = '';
    public $UserLog = '';
    public $ShipmentPackages = '';
    public $Shipments = '';
    public $ShipmentPackageItems = '';
    public $components = ['Auth', 'Common'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->Users = TableRegistry::get('Users');
        $this->Roles = TableRegistry::get('Roles');
        $this->UserLog = TableRegistry::get('UserLogs');
        $this->ShipmentPackages = TableRegistry::get('ShipmentPackages');
        $this->Shipments = TableRegistry::get('Shipments');
        $this->ShipmentPackageItems = TableRegistry::get('ShipmentPackageItems');
    }

    /**
     * updatePassword  to update the  password while activating request
     * $data array contains posted data
     */
    public function updatePassword($data = []) {

        return $this->Users->addModifyUser($data);
    }

    /**
     * getUserDetails to get the  users details on  passed conditions and fields
     * @ conditions is array
     * @ fields is array 
     */
    public function getUserDetails($fields = [], $conditions = [],$type='all',$extra=[]) {
        return $details = $this->Users->getRecords($fields, $conditions,$type,$extra);
    }

    /*
      function to  update the users last loggged in  time
     * @$fieldsArray  array of posted fields 
     */

    public function updateLastLoggedIn($fieldsArray = []) {
        $this->Users->updateLastLoggedIn($fieldsArray);
    }

    /*
      checkEmailExists to check the duplicate email
     * 
     * returns the 0 or 1 0 means does not exist 1 means already exists 
     */

    public function checkEmailExists($email = null, $userId = null) {
        return $getDetailsByEmail = $this->Users->checkEmailExists($email, $userId);
    }

    /*
      checkUsernameExists to check the duplicate username
     * 
     * returns the 0 or 1 0 means does not exist 1 means already exists 
     */

    public function checkUsernameExists($username = null, $userId = null) {
        return $getDetails = $this->Users->checkUsernameExists($username, $userId);
    }

    /**
     * addModifyUser to add or modify the users with their roles 
     * @fieldsArray array of posted data 

     */
    public function addModifyUser($fieldsArray = []) {
        $userId = $this->Users->addModifyUser($fieldsArray);  // update or insert user 
        if ($userId > 0) {
            return $userId;
        }

        return 0;
    }

    /**
     * 
     * getAutoCompleteDetails to return the autocomplete details 
     *
     */
    public function getAutoCompleteDetails() {
        return $this->Users->getAutoCompleteDetails();
    }

    /**
     * 
     * function to return the role id on basis of passed role value
     * @roleValue is  passed as roles  like 'ADMIN' or 'DATAENTRY'
     */
    public function returnRoleId($roleValue = null) {

        return $this->Roles->returnRoleId($roleValue);
    }

    /**
      sending activation link
      @params $userId is user id , $email recievers email $name recievers name
      @params $subject is for subject of email
     */
    public function sendActivationLink($userId, $email, $name, $subject) {
        $dateafter5days = date('Y-m-d', strtotime("+5 days"));
        $dateafter5days = strtotime($dateafter5days);
        $encodedstring = base64_encode(_SALTPREFIX1 ._DELEM3 . $userId ._DELEM3 . _SALTPREFIX2._DELEM3.$dateafter5days);
        $website_base_url = _WEBSITE_URL . "#/ResetPassword/$encodedstring";
        //$subject = 'DFA Data Admin Activation';
        $message = "<div>Dear " . ucfirst($name) . ",<br/><br/>
			Please 	<a href='" . $website_base_url . "'>Click here  </a> to  setup your password.<br/><br/>
			Thank you.<br/>
			Regards,<br/>
			OpenEMIS Logistics
			</div> ";

        $fromEmail = _ADMIN_EMAIL;
        $this->sendEmail($email, $fromEmail, $subject, $message, 'smtp');
    }

    /**
      sendEmail method  to  send email
      @toEmail    recievers email
      @fromEmail  senders email
      @subject    subject of email
      @message    message of email
      @type       type method used for smtp
     */
    public function sendEmail($toEmail, $fromEmail, $subject = null, $message = null, $type = 'smtp') {
        $return = false;
        try {
            if (!empty($toEmail) && !empty($fromEmail)) {
                ($type == 'smtp') ? $type = 'defaultsmtp' : $type = 'default';
                $emailClass = new Email($type);
                $result = $emailClass->emailFormat('html')->from([$fromEmail => $subject])->to($toEmail)->subject($subject)->send($message);
                if ($result) {
                    $return = true;
                }
            }
        } catch (Exception $e) {
            $return = $e;
        }

        return $return;
    }

    /**
      method to get UserDetails using Email
      @email email of user
     */
    public function getUserDetailsByEmail($email) {

        if (!empty($email)) {
            $fieldsArray = ['id', 'first_name', 'last_name', 'email', 'status_id'];
            $conditionArray = ['email' => $email];
            $userDetails = $this->getUserDetails($fieldsArray, $conditionArray);
            return $userDetails[0];
        }
    }

    /**
     * forgotPassword method sends password reset link on email 
     * @params email
     * 
     */
    public function forgotPassword($data = null) {

        if (!isset($data['userName']) || empty($data['userName'])) {
            return array('error' => _ERR101);
        }

        $userName = trim($data['userName']);

        $validlength = $this->Common->checkBoundaryLength($userName, _USERNAME_LENGTH); //128 only
        if ($validlength == false) {
            return ['error' => _ERR109];  // email  length 
        }
        /*$verifyEmail = $this->Common->validEmail($email);
        if ($verifyEmail == false) {
            return array('error' => _ERR103);
        }*/
        $chkusr = $this->checkUsernameExists($userName); //check email exists or not 1 means email exists 
        if ($chkusr == 0) {
            return array('error' => _ERR142);
        } else {

            $userData = $this->getUserDetails(['email','id','first_name','last_name'],['username'=>$userName]); //get user details using email 
            $userId = $userData[0]['id'];
            $fieldsArray = ['modified_user_id' => $userId];
            $conditions = ['id' => $userId];
            $this->Users->updateRecords($fieldsArray, $conditions); //update status for activation link 

            $status = $this->sendActivationLink($userId, $userData[0]['email'], $userData[0]['first_name'] . ' ' . $userData[0]['last_name'], _FORGOTPASSWORD_SUBJECT);
        }
    }

    /**
      method to get User details by User id
     * @ params userId 
      @ returns array of user details
     */
    public function getUserDetailsById($userId = null) {

        $data = [];
        $rolevalue = '';
        $dt = [];
        if (!empty($userId)) {

            $fieldsArray = [];
            $conditionArray = ['Users.id' => $userId];
            $extra=['contain'=>['Areas','Locations','Couriers']];
            $getDetails = $this->getUserDetails($fieldsArray, $conditionArray,'all',$extra);
            if (!empty($getDetails)) {
                $dt = $getDetails[0];
                $roleDetails = $this->Common->getRoles(['id' => $dt['role_id']]);
                if (!empty($roleDetails)) {
                    $getRoleDt = current($roleDetails);
                    $rolevalue = $getRoleDt['role'];
                    $rolenameValue = $getRoleDt['role_name'];
                }
                $data = ['firstName' => $dt['first_name'], 'lastName' => $dt['last_name'],
                    'comments' => $dt['comments'], 'role' => $rolevalue,'roleName' => $rolenameValue,
                    'login' => $dt['username'], 'id' => $dt['id'],
                    'locationId' => $dt['location_id'], 'courierId' => $dt['courier_id'],'areaId' => $dt['area_id'],
                    'locationName' => $dt['location']['name'], 'courierName' => $dt['courier']['name'],'areaName' => $dt['area']['name'],
                    'status_id' => $dt['status_id'], 'email' => $dt['email'],
                    'createdBy' => $dt['created_user_id'], 'modifiedBy' => $dt['modified_user_id'],
                ];
            }
        }
        return $data;
    }

    /**

      function to manage user (add/modify/assign new dataabse)
      @dbId is the databse id
      @inputArray posted array
     */
    public function saveUserDetails($inputArray = array()) {
        $roleId = '';
        $validated = $this->getValidatedUserFields($inputArray); //validate input details 
        if (isset($validated['errCode'])) {
            return ['error' => $validated['errCode']];
        }

        if (empty($inputArray['id'])) {
            if (!isset($inputArray['created_user_id']))
                $inputArray['created_user_id'] = $this->Auth->User('id');
            $inputArray['status_id'] = 0;
        }else {
            if (empty($inputArray['password'])) {
                unset($inputArray['password']);
            }
        }
        if (!isset($inputArray['modified_user_id']))
            $inputArray['modified_user_id'] = $this->Auth->User('id');

        $roleDetails = $this->Common->getRoles(['role' => $inputArray['role']]);
        if (!empty($roleDetails)) {
            $getRoleDt = current($roleDetails);
            $roleId = $getRoleDt['id'];
        }

        $inputArray['role_id']     = $roleId; //return role id 
        $inputArray['first_name']  = $inputArray['firstName'];
        $inputArray['last_name']   = $inputArray['lastName'];
        $inputArray['username']    = $inputArray['login'];
        $inputArray['area_id']     = !empty($inputArray['area'][0]['id'])?$inputArray['area'][0]['id']:'0';
        $inputArray['courier_id']  = !empty($inputArray['courierId'])?$inputArray['courierId']:'0';
        $inputArray['location_id'] = !empty($inputArray['locationId'])?$inputArray['locationId']:'0';


        unset($inputArray['login']);
        unset($inputArray['firstName']);
        unset($inputArray['lastName']);
        unset($inputArray['role']);
        unset($inputArray['area']);
       
        $lastIdinserted = $this->addModifyUser($inputArray); //add modify user 
        if ($lastIdinserted > 0) {
            // success         
            if (empty($inputArray['id'])) {
                // for new user, send an registration link
                $this->registrationDetails($inputArray['email'], $inputArray['first_name'].' '.$inputArray['last_name'], _ACTIVATIONEMAIL_SUBJECT, $inputArray['username'], $inputArray['password']);
            }else{
                $this->registrationDetails($inputArray['email'], $inputArray['first_name'].' '.$inputArray['last_name'], _MODIFYUSEREMAIL_SUBJECT, $inputArray['username'], '','UPDATE');
                
            }
            return true;
        } else {
            return ['errorCode' => _ERR100];      // user not modified due to database error 
        }
    }

    /**
      method to get validated user fields before saving into db
     * $fields array 
     */
    public function getValidatedUserFields($fields = []) {

        //$validated['errCode'] = '';

        if (count($fields) > 0) {

            $username = (isset($fields['login'])) ? trim($fields['login']) : '';
            $first_name = (isset($fields['firstName'])) ? trim($fields['firstName']) : '';
            $last_name = (isset($fields['lastName'])) ? trim($fields['lastName']) : '';
            $email = (isset($fields['email'])) ? trim($fields['email']) : '';
            $uId = (isset($fields['id'])) ? trim($fields['id']) : 0;
            //$isModified = (isset($fields['isModified'])) ? trim($fields['isModified']) : false;
            $roleValue = (isset($fields['role'])) ? $fields['role'] : '';
            $password = (isset($fields['password'])) ? $fields['password'] : '';
            $confirmPassword = (isset($fields['confirmPassword'])) ? $fields['confirmPassword'] : '';
            $comments = (isset($fields['comments'])) ? $fields['comments'] : '';


            if (empty($roleValue)) {
                return ['errCode' => _ERR113];
            } else {
                $roleDetails = $this->Common->getRoles(['role' => $roleValue]);
                if (empty($roleDetails)) {
                    return ['errCode' => _ERR114];
                }
                /* $roleid = $this->returnRoleId($roleValue);
                  if ($roleid == 0) {
                  return ['errCode' => _ERR114];
                  } */
            }

            if (empty($password) || empty($confirmPassword)) {
                if ($uId == '')
                    return ['errCode' => _ERR119];             // Empty password   
            }else {
                if ($password != $confirmPassword) {
                    return ['errCode' => _ERR122];  // password   not matched with confirm                      
                }
                $validlength = $this->Common->checkBoundaryLength($password, _PASSWORD_LENGTH); //765 only
                if ($validlength == false) {
                    return ['errCode' => _ERR121];  // password   length 
                }
            }


            if (empty($first_name)) {
                return ['errCode' => _ERR107];
            } else {
                $validlength = $this->Common->checkBoundaryLength($first_name, _FIRSTNAME_LENGTH); //128 only
                if ($validlength == false) {
                    return ['errCode' => _ERR110];               // First name length
                }
            }
            if (!empty($comments)) {
                $validlength = $this->Common->checkBoundaryLength($comments, _COMMENTS_LENGTH); //128 only
                if ($validlength == false) {
                    return ['errCode' => _ERR139];               // email  length 
                }
            }

            if (empty($last_name)) {
                return ['errCode' => _ERR108];
            } else {
                $validlength = $this->Common->checkBoundaryLength($last_name, _LASTNAME_LENGTH); //128 only
                if ($validlength == false) {
                    return ['errCode' => _ERR111];      // email  length 
                }
            }

            ///
            if (empty($email)) {
                return ['errCode' => _ERR101];
            } else {

                $validlength = $this->Common->checkBoundaryLength($email, _EMAIL_LENGTH); //128 only
                if ($validlength == false) {
                    return ['errCode' => _ERR104];
                    // email  length 
                }
                $verifyEmail = $this->Common->validEmail($email);
                if ($verifyEmail == false) {
                    return ['errCode' => _ERR103];
                }
                /*
                  $chkEmail = $this->checkEmailExists($email, $uId); //if >0 means email exists
                  if ($chkEmail > 0) {
                  return ['errCode' => _ERR120];
                  } */
            }

            $usernamevalid = $this->validateUserName($username, $uId);
            if (isset($usernamevalid['errCode'])) {
                return ['errCode' => $usernamevalid['errCode']];
            }

            ///
        } else {
            return ['errCode' => _ERR105];
        }
    }

    /**
     * 
     * method to validate the username 
     * @username is the username 
     * $uId is user id 
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
     * method returns 1 if logged user have no right to delete selected users 
     * @userIds array of user ids 
     * @dbId is the database id 
     */
    public function getAuthorizationStatus($userIds, $dbId) {
        $status = 0;
        foreach ($userIds as $toId) {
            $acessStatus = $this->checkAuthorizeUser($toId, $dbId); //check authentication 
            if ($acessStatus == false) {
                $status = 1;
                break;
            }
        }
        return $status;
    }

    /**
     * 
     * method to update password on activation link
     * @data posted info 
     */
    public function accountActivation($data = []) {

        $validate = $this->validateLink($data);

        if (isset($validate['error'])) {
            return ['error' => $validate['error']];
        }

        $actkey = $data['userId'];//activation key 
        $requestdata = array();
        $encodedstring = trim($actkey);
        $decodedstring = base64_decode($encodedstring);
        $explodestring = explode(_DELEM3, $decodedstring);
        $requestdata['modified_user_id'] = $requestdata['id'] = $userId = $explodestring[1];
        $password = $requestdata['password'] = trim($data['password']);
        //$requestdata['status_id'] = _ACTIVE; // Activate user 
        $returndata = $this->updatePassword($requestdata);
        if ($returndata > 0) {
            $returnData['status'] = _SUCCESS;
        } else {
            $returnData['error'] = _ERR100;      // password not updated due to server error   
        }
    }

    /**
     * method to validate activation details 
     * @data posted data 
     */
    public function validateLink($data) {
        $actkey = (isset($data['userId'])) ? $data['userId'] : '';//activation key 
        if (empty($actkey)) {
            return ['error' => _ERR118]; //checks key is empty or not
        }

        $encodedstring = trim($actkey);
        $decodedstring = base64_decode($encodedstring);
        $explodestring = explode(_DELEM3, $decodedstring);

        if (isset($explodestring[1]) && !empty($explodestring[1])) {
            $userId = $explodestring[1];
        } else {
            return ['error' => _ERR117];            //  invalid key    
        }

        if ($explodestring[0] != _SALTPREFIX1 || $explodestring[2] != _SALTPREFIX2) {
            return ['error' => _ERR117];            //  invalid key    
        }

        if(!empty($explodestring[3]) && (strtotime(date('Y-m-d')) > $explodestring[3])){
            return ['error' => _ERR143];            //  activation link expired     
        }
        /*
        $activationStatus = $this->checkActivationLink($userId);
        if ($activationStatus == 0)
            return ['error' => _ERR118];            //  Activation link already used 
        */
        if (!isset($data['password']) || empty($data['password'])) {
            return ['error' => _ERR119];             // Empty password   
        } else {
            $validlength = $this->Common->checkBoundaryLength($data['password'], _PASSWORD_LENGTH); //765 only
            if ($validlength == false) {
                return ['error' => _ERR121];  // password   length 
            }
        }
    }

    /**
     * 
     * method to add user logged in  details 
     */
    public function logindetails($loginDetails = []) {

        $this->UserLog->savedata($loginDetails);
    }

    /**
     * 
     * method to get the list of all  users 
     */
    public function listAllUsers() {
        $data = $fields = $conditions = [];
        $rolevalue = $modifyBy = '';
        $userDt = $this->Users->getRecords($fields, $conditions);
        if (!empty($userDt)) {
            foreach ($userDt as $value) {
                $roleDetails = $this->Common->getRoles(['id' => $value['role_id']]);
                if (!empty($roleDetails)) {
                    $getRoleDt = current($roleDetails);
                    $rolevalue = $getRoleDt['role_name'];
                }
                $usrdetails = $this->getUserDetailsById($value['modified_user_id']);
                if (!empty($usrdetails)) {
                    $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                }
                $data[] = ['name' => $value['first_name'] . _DELEM7 . $value['last_name'],
                    'role' => $rolevalue,
                    'modified' => $value['modified'],
                    'login' => $value['username'],
                    'id' => $value['id'],
                    'modifiedBy' => $modifyBy,
                ];
            }
        }
        return $data;
    }

    /**
     * deleteUser 
     * $userId  array for multiple user ids 
     * 
     */
    public function deleteUser($userId = '') {

        $deleteUser = 0;
        if (isset($userId) && !empty($userId)) {
            $deleteUser = $this->Users->deleteRecords(['id' => $userId]); //delete db
            if ($deleteUser > 0) {
                $this->ShipmentPackageItems->deleteRecords(['created_user_id' => $userId]); //delete db
                $this->ShipmentPackages->deleteRecords(['created_user_id' => $userId]); //delete db
                $this->Shipments->deleteRecords(['created_user_id' => $userId]); //delete db make function in model
                $this->UserLog->deleteRecords(['user_id' => $userId]); //delete db
            }
        }

        return $deleteUser;
    }

    /**
      sending login credentials
      @params $password is password , $email recievers email $name recievers name
      @params $subject is for subject of email
     */
    public function registrationDetails($email, $name, $subject, $login, $password,$case='INSERT') {

        //$encodedstring = base64_encode(_SALTPREFIX1 . '-' . $userId . '-' . _SALTPREFIX2);
        $website_base_url = _WEBSITE_URL;
      
        // Grabbing View Data
        $view = new View($this->request, $this->response, null);
        $view->set('name', $name);
        $view->set('subject', $subject);
        $view->set('login', $login);
        $view->set('password', $password);
        $view->set('case', $case);
        $view->set('website_base_url', $website_base_url);
        $view->viewPath = 'Email'; // Directory inside view directory to search for .ctp files
        $view->layout = false; //$view->layout='ajax'; // layout to use or false to disable
        $html = $view->render('register');

        $fromEmail = _ADMIN_EMAIL;
        $this->sendEmail($email, $fromEmail, $subject, $html, 'smtp');
    }
    
    
    
    
    
    
    
    
    
    /*
      function to check activation link is used or not
      @params $userId , $email
     */

    public function checkActivationLink($userId) {
        $status = $this->Users->checkActivationLink($userId);
        return $status;
    }

}
