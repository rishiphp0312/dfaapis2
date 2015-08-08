<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Configure;
use Cake\Network\Email\Email;

/**
 * Common period Component
 */
class UserCommonComponent extends Component {

    public $MDatabaseConnections = '';
    public $MSystemConfirgurations = '';
    public $dbcon = '';
    public $Users = '';
    public $Roles = '';
    public $RUserDatabases = '';
    public $RUserDatabasesRoles = '';
    public $components = ['Auth', 'UserAccess', 'Common'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->MDatabaseConnections = TableRegistry::get('MDatabaseConnections');
        $this->MSystemConfirgurations = TableRegistry::get('MSystemConfirgurations');
        $this->Users = TableRegistry::get('Users');
        $this->Roles = TableRegistry::get('MRoles');
        $this->RUserDatabases = TableRegistry::get('RUserDatabases');
        $this->RUserDatabasesRoles = TableRegistry::get('RUserDatabasesRoles');
        //$this->Auth->allow();
    }

    /*
      checkUserDbRelation  to check whether user is added with db or not
      @$userId is user id
      @$dbId is database id
     */
    public function checkUserDbRelation($userId, $dbId) {

        return $this->RUserDatabases->checkUserDbRelation($userId, $dbId);
    }
    
    /*
      updatePassword  to update the  password while activating request
      $data array contains posted data
     */
    public function updatePassword($data = []) {

        return $this->Users->addModifyUser($data);
    }
    
    /*
      getUserDetails to get the  users details on  passed conditions and fields
     * @ conditions is array
     * @ fields is array 
     */
    public function getUserDetails($fields = [],$conditions = [] ) {

        return $details = $this->Users->getRecords($fields,$conditions);
    }
    
    /*
      listAllRoles to get list of all Roles
     * returns array 
     */
    public function listAllRoles() {

        return $listAllRoles = $this->Roles->listAllRoles();
    }
    
    /*
     get userDatabase ID
     @userId represents user id
     @dbId   represents database id
    * returns RUD table ids 
    */
    public function getUserDatabaseId($userId, $dbId) {

        return $getidsRUD = $this->RUserDatabases->getUserDatabaseId($userId, $dbId);
    }
    
    /*
     getUserDatabasesRoles gives the roles of users
     get the roles on basis of dbId  and userId
     * 
    */
    public function getUserDatabasesRoles($userId = null, $dbId = null) {
        $rolesarray = [];
        $getidsRUD = $this->getUserDatabaseId($userId, $dbId); //rud ids 
        if ($getidsRUD) {
            $listAllRoleIDs = $this->RUserDatabasesRoles->getRoleIDsDatabase($getidsRUD); //index for rudrid  and value for  roleid
            if (!empty($listAllRoleIDs)) {
                foreach ($listAllRoleIDs as $index => $RoleId) {
                    $rolesarray[] = $this->Roles->returnRoleValue($RoleId); //gives value of role on passed role id 
                }
            }
        }
        return $rolesarray;
    }
    
    /*
      listAllUsersDb to get listing of all users with their roles related to specific databases
     * @params dbId is database id 
     */
    public function listAllUsersDb($dbId) {

        $userRoles = [];
        $data = $this->MDatabaseConnections->listAllUsersDb($dbId); // get list of all users of dbId
        if (isset($data) && !empty($data)) {
            foreach ($data as $index => $value) {
                $userId = $value[_USER_ID];
                $roleIdsDb['roles'] = $this->getUserDatabasesRoles($userId, $dbId); //get roles of users of dbId
                $getidsRUD = $this->getUserDatabaseId($userId, $dbId); //get ids of RUD table
                $getidsRUDR = $this->RUserDatabasesRoles->getRoleIDsDatabase($getidsRUD); // return array index for RUDR id and value for roleid 
                $userRoles[$index] = $value;
                $userRoles[$index]['roles'] = $roleIdsDb['roles'];               
                $getidsRUDR = array_keys($getidsRUDR);//get rudr table ids which are stored on index of array 
                $getAssignedAreas = $this->UserAccess->getAssignedAreas($getidsRUDR);    //get area ids 
				//$extra['getidsRUDR']=$getidsRUDR;
               // $getAssignedAreas = $this->UserAccess->getAreaAccessToUser($extra['getidsRUDR']);    //get area ids 
                $getAssignedIndis = $this->UserAccess->getAssignedIndicators($getidsRUDR);//get indicator gids    
                //$getAssignedIndis = $this->UserAccess->getIndicatorAccessToUser($extra['getidsRUDR']);//get indicator gids    
                //pr($getAssignedIndis);
                $userRoles[$index]['access']['area'] =      array_values($getAssignedAreas);                
                $userRoles[$index]['access']['indicator'] = array_values($getAssignedIndis);      
            }
        }
        return $userRoles;
    }
    
    /*
     * deleteUserRolesAndDbs to delete the users 
     * $userId  array for multiple user ids 
     * $dbId    is database id 
     */
    public function deleteUserRolesAndDbs($userId = [], $dbId = null) {

        if (!empty($dbId) && $dbId > 0) {
            if (isset($userId) && !empty($userId)) {
				
				
                $getidsRUD = $this->getUserDatabaseId($userId, $dbId);    // get RUD id
                $getidsRUDR = $this->RUserDatabasesRoles->getRoleIDsDatabase($getidsRUD); //  index for rudrid and value for roleid 
                $allRUDR_ids = array_keys($getidsRUDR); // all RUDR ids 
                if ($getidsRUD) {
                    $deleteDatabase = $this->RUserDatabases->deleteUserDatabase($getidsRUD); //delete db
                    if ($deleteDatabase > 0) {
                        $deleteRoleDatabase = $this->RUserDatabasesRoles->deleteUserRolesDatabase($getidsRUD); //delete roles
                        if ($deleteRoleDatabase > 0) {
                            if (count($allRUDR_ids) > 0) {
                                $deleteAreas = $this->UserAccess->deleteUserAreaAccess($getidsRUD, $allRUDR_ids, ' IN '); //delete areas
                                $deleteIndicators = $this->UserAccess->deleteUserIndicatorAccess($getidsRUD, $allRUDR_ids, ' IN '); //delete indi
                            }
                            return $deleteRoleDatabase;
                        }
                    }
                }
            }
        }
        return 0;
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
     * deleteUserRoles is  used for deleting roles while  modifying  user
     * $type IN or NOT IN  for  deleting roles default value is IN
       $getIdsRUD is the user_database_id
     * 
     */
    public function deleteUserRoles($roledIds = [], $getIdsRUD = [], $type) {
        $deleteRoles = 0;
        $deleteRoles = $this->RUserDatabasesRoles->deleteUserRoles($getIdsRUD, $roledIds, $type); // delete these $roledIds
        return $deleteRoles;
    }
    
    /*
     *
      addModifyUser to add or modify the users with their databases and roles on   areas and indicators  respectively
     * @fieldsArray array of posted data 
     * @ dbId is database id 
     */
    public function addModifyUser($fieldsArray = [], $dbId = null) {

        if ($dbId > 0) {
			
			$userId = $this->Users->addModifyUser($fieldsArray);  // update or insert user 

            if ($userId) {

                if (isset($fieldsArray[_USER_ID]) && !empty($fieldsArray[_USER_ID])) { // case of modify
					
                    $existRoles = $this->getUserDatabasesRoles($fieldsArray[_USER_ID], $dbId); //get existing roles 
                    // getidsRUD stores the user_database_id value from r_user_databases table 
                    $getidsRUD = $this->getUserDatabaseId($fieldsArray[_USER_ID], $dbId); //get ids of RUD table
                    $getidsRUDR = $this->RUserDatabasesRoles->getRoleIDsDatabase($getidsRUD); // return array index for RUDR id and value for roleid 
                    $allRUDR_ids = array_keys($getidsRUDR); // all RUDR ids	
                    $this->UserAccess->deleteUserAreaAccess($getidsRUD, $allRUDR_ids, ' IN '); // deleting existing areas
                    $this->UserAccess->deleteUserIndicatorAccess($getidsRUD, $allRUDR_ids, ' IN '); // deleting existing indicators 					
                    //start
                    $insertRoles = $this->getRoles($existRoles, $getidsRUD,$fieldsArray['roles']); //get roles in case of modification
                    //end 
                } else {                   					
                    $insertRoles = $fieldsArray['roles']; // roles to be inserted in case of add                     
                }

                // saving in rud table  
                if (empty($fieldsArray[_USER_ID]) || empty($getidsRUD)) {
                    $lastinsertedDbId = $this->addUserDbDetails($userId,$dbId); // adding user to db when 
                } else {
                    $lastinsertedDbId = current($getidsRUD); // while modifying user 
                }

                $cnt = 0;
                // $insertRoles this will be empty if posted roles and existing roles both are same
                if (isset($insertRoles) && count($insertRoles) > 0) {
                    $rolearrayids[] = $this->addUserRoleDbDetails($lastinsertedDbId,$insertRoles);
					
                    if(!empty($rolearrayids[0]['de']))
                        $deRoleinsertedId=$rolearrayids[0]['de']; //de id 
                }
				
				
                if (!empty($fieldsArray[_USER_ID]) && !empty($getidsRUD)) {
                    //in case of modify  get details from RUDR table 
                    $getidsRUDR = $this->RUserDatabasesRoles->getRoleIDsDatabase($getidsRUD); // return array index for RUDR id and value for roleid 
                    foreach ($getidsRUDR as $index => $roleId) {
                        if ($this->checkDEAccess($roleId) == true) {
                            $deRoleinsertedId = $index;                 //get rudr id for de 
                        }
                    }
                }
				
                //check data entry id is empty or not 
                if (!empty($deRoleinsertedId)) {
                    $areaAccessFlag = $indAccessFlag = 0;				  
					
                    //check area access
					$AreaAccessAr = (isset($fieldsArray['access']['area'])) ? $fieldsArray['access']['area'] : array();
					if (!empty($AreaAccessAr) && count($AreaAccessAr) > 0) {
                        // function to save area access against to data role
						$this->addUserAreaAccess($deRoleinsertedId, $lastinsertedDbId, $AreaAccessAr);
						$areaAccessFlag = 1;
					}
					
					//check indicator access
					$IndAccessAr = (isset($fieldsArray['access']['indicator'])) ? $fieldsArray['access']['indicator'] : array();
                    if (!empty($IndAccessAr) && count($IndAccessAr) > 0) {
                        // function to save indicator access against to data role
						$this->addUserIndicatorAccess($deRoleinsertedId, $lastinsertedDbId, $IndAccessAr); //save indicators 
						$indAccessFlag = 1;
					}
                     
                    // set user access role flag
					$this->setAreaIndAccessFlag($deRoleinsertedId, $areaAccessFlag, $indAccessFlag);
                }

                return $userId;
            }
        }// end of dbId 
        return 0;
    }

	
	/*
	function to set area/indicator access to the user for a database
	@dbRoleId data entry id
	@aFlag area access flag return true or false
	@indFlag indicator access flag return true or false	
	*/
	function setAreaIndAccessFlag($dbRoleId, $aFlag=0, $indFlag=0) {
		$flagarray=[];
		if($dbRoleId) {
			$flagarray[_RUSERDBROLE_INDICATOR_ACCESS] = $indFlag;
			$flagarray[_RUSERDBROLE_AREA_ACCESS] = $aFlag;
			$flagarray[_RUSERDBROLE_ID] = $dbRoleId; 
			$flagarray[_RUSERDBROLE_MODIFIEDBY] = $this->Auth->User('id'); 
						
			// Update area and indicator access flag
			$this->RUserDatabasesRoles->addUserRoles($flagarray); 
		}
	}
    
    
    /*
     * getRoles to manipluate roles which will insert  while add or modify user     
     
     * @existRoles is array 
     * @getidsRUD is rud table ids 
     * @postedRoles posted roles 
     * returns array of  roles with their ids to be inserted 
     */   
    
    public function getRoles($existRoles, $getidsRUD, $postedRoles){
            $rolesid_array = array();
             //get common roles 
            $commonRoles = array_intersect($postedRoles, $existRoles); // get the common roles between posted and  exists roles 
                   
            if (isset($commonRoles) && count($commonRoles) > 0) {
                foreach ($commonRoles as $index => $value) {
                    // getting common role ids 					
                    $rolesid_array[] = $this->Roles->returnRoleId($value);
                }
            }
                       
            // case when posted data Roles is not found in existing  roles of user 
            $rolesNotinPost = array();
            if (empty($commonRoles) && !empty($existRoles)) {
                foreach ($existRoles as $index => $valueroles) {
                    $rolesNotinPost[] = $this->Roles->returnRoleId($valueroles);
                }
            }
            if (isset($rolesNotinPost) && count($rolesNotinPost) > 0) {
                $this->deleteUserRoles($rolesNotinPost, $getidsRUD, ' IN '); // in case of delete
            }

            if (isset($rolesid_array) && count($rolesid_array) > 0) {
                $this->deleteUserRoles($rolesid_array, $getidsRUD, ' NOT IN '); // delete roles which are not common  
            }
            return $insertRoles = array_diff($postedRoles, $existRoles); // roles to be inserted 
    }
    
     /*
     * addUserRoleDbDetails to add  user with their roles in  selected db      
     * @usrdbId is the user database id 
     * @rolesData is array 
     * returns array of  lastinserted id of RUDR table belongs to DE or other role types 
     */    
    public function addUserRoleDbDetails($usrdbId,$rolesData){
        $fieldsArrayRoles = []; $roleinsertedId=[];
        if (isset($rolesData) && count($rolesData) > 0) {
                    foreach ($rolesData as $value) {
                        // role ids which need  to be inserted  	
                        $fieldsArrayRoles[_RUSERDBROLE_USER_DB_ID] = trim($usrdbId);
                        $roleId = trim($this->Roles->returnRoleId($value));
                        $fieldsArrayRoles[_RUSERDBROLE_AREA_ACCESS] = 0;
                        $fieldsArrayRoles[_RUSERDBROLE_INDICATOR_ACCESS] = 0;
                        $fieldsArrayRoles[_RUSERDBROLE_ROLE_ID] = $roleId;
                        $fieldsArrayRoles[_RUSERDBROLE_CREATEDBY] = $this->Auth->User('id');
                        $fieldsArrayRoles[_RUSERDBROLE_MODIFIEDBY] = $this->Auth->User('id');

                        if ($this->checkDEAccess($roleId) == true) {  //check whether its DE or not 
                             $roleinsertedId['de'] = $this->RUserDatabasesRoles->addUserRoles($fieldsArrayRoles); //saving roles		
                        } else {
                             $roleinsertedId['others'][] = $this->RUserDatabasesRoles->addUserRoles($fieldsArrayRoles); //saving roles
                        }
                    }
                    return $roleinsertedId;
                }
    }
    
    /*
     * addUserDbDetails to add  user in selected db 
     * @dbId is database id 
     * @usrdbId is the user database id 
     * returns lastinserted id of RUD table 
     */    
    public function addUserDbDetails($userId,$dbId){
        $fieldsArrayDB = [];
        $fieldsArrayDB[_RUSERDB_USER_ID] = $userId;
        $fieldsArrayDB[_RUSERDB_DB_ID] = $dbId;
        $fieldsArrayDB[_RUSERDB_CREATEDBY] = $this->Auth->User('id');
        $fieldsArrayDB[_RUSERDB_MODIFIEDBY] = $this->Auth->User('id');
        return $this->RUserDatabases->addUserDatabases($fieldsArrayDB); 
    }
    
    /*
     * addUserAreaAccess to add Areas for DE
     * $deId data entry  id from RUDR table ie. RUDR id 
     * $usrdbId is the user database id 
     * $areas the areas posted array 
     */
    public function addUserAreaAccess($deId, $usrdbId, $areas) {

        foreach ($areas as $areaId) {
            if(isset($areaId['id']) && !empty($areaId['id'])) {
                $fieldsArrayAreas = [_RACCESSAREAS_AREA_ID => $areaId['id'],
                    _RACCESSAREAS_AREA_NAME => $areaId['name'],
                    _RACCESSAREAS_USER_DATABASE_ID => $usrdbId,
                    _RACCESSAREAS_USER_DATABASE_ROLE_ID => $deId
                ];
                $this->UserAccess->createRecordAreaAccess($fieldsArrayAreas);    
            }
        }        
    }

    /*
     * addUserIndicatorAccess to add indicators for DE
     * $deId data entry  id from RUDR table ie. RUDR id 
     * $usrdbId is the user database id 
     * $indicators the indicators posted array 
     */

    public function addUserIndicatorAccess($deId, $usrdbId, $indicators) {

        foreach ($indicators as $indGid) {
            if(isset($indGid['id']) && !empty($indGid['id'])) {
                $fieldsArrayInd = [_RACCESSINDICATOR_INDICATOR_GID => $indGid['id'],
                    _RACCESSINDICATOR_INDICATOR_NAME => $indGid['name'],
                    _RACCESSINDICATOR_USER_DATABASE_ID => $usrdbId,
                    _RACCESSINDICATOR_USER_DATABASE_ROLE_ID => $deId
                ];

                $this->UserAccess->createRecordIndicatorAccess($fieldsArrayInd);    
            }
        }         
    }
    
    
    /*
     * 
     * getAutoCompleteDetails to return the autocomplete details 
     *
     */

    public function getAutoCompleteDetails() {
        return $this->Users->getAutoCompleteDetails();
    }

    /*
     * 
     * function to return the role id on basis of passed role value
     * @roleValue is  passed as roles  like 'ADMIN' or 'DATAENTRY'
     */

    public function returnRoleId($roleValue = null) {

        return $this->Roles->returnRoleId($roleValue);
    }

    /*
      sending activation link
      @params $userId is user id , $email recievers email $name recievers name 
      @params $subject is for subject of email 
     */

    public function sendActivationLink($userId, $email, $name,$subject) {

        $encodedstring = base64_encode(_SALTPREFIX1 . '-' . $userId . '-' . _SALTPREFIX2);
        $website_base_url = _WEBSITE_URL . "#/UserActivation/$encodedstring";
        //$subject = 'DFA Data Admin Activation';
        $message = "<div>Dear " . ucfirst($name) . ",<br/><br/>
			Please 	<a href='" . $website_base_url . "'>Click here  </a> to activate and setup your password.<br/><br/>
			Thank you.<br/>
			Regards,<br/>
			DFA Database Admin
			</div> ";

        $fromEmail = _ADMIN_EMAIL;
        $this->sendEmail($email, $fromEmail, $subject, $message, 'smtp');
    }

    /*
      sending notification on adding user to db
     */

    public function sendDbAddNotify($email, $name, $dbName='') {


        $subject = _ASSIGNEDDB_SUBJECT;
        $message = "<div>Dear " . ucfirst($name) . ",<br/><br/>
                    Database ".((!empty($dbName)) ? '('.$dbName.') ' : '')." is assigned to you using DFA Data Admin Tool.<br/><br/>
                    Thank you.<br/>
                    Regards,<br/>
                    DFA Database Admin
                    </div> ";
        $fromEmail = _ADMIN_EMAIL;
        $this->sendEmail($email, $fromEmail, $subject, $message, 'smtp');
    }

    /*
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

    /* resetPassword sends re-activation link to user
     * 
     * @userId string user id
     */

    public function resetPassword($userId = null) {

        $return = array('status' => true, 'error' => '');

        if (!empty($userId)) {
            // get User details
            $userData = $this->getUserDetailsById($userId);

            if ($userData) {
                // update user status field as 0 (in-active)
                $fieldsArray = [_USER_STATUS => 0,_USER_MODIFIEDBY => $this->Auth->User('id')];
                $conditions = [_USER_ID => $userId];
                $this->Users->updateRecords($fieldsArray, $conditions);
                // Send mail to activate the account and setup the password
                $this->sendActivationLink($userId, $userData['email'], $userData['name'],_FORGOTPASSWORD_SUBJECT);
            }
        }

        return $return;
    }
    /*
	 method to get UserDetails using Email
	 @email email of user  
	*/
    public function getUserDetailsByEmail($email){
        
        if(!empty($email)){
            $fieldsArray = [_USER_ID, _USER_NAME,_USER_EMAIL,_USER_STATUS];
            $conditionArray = [_USER_EMAIL=>$email];
            $userDetails =  $this->getUserDetails($fieldsArray, $conditionArray);
            return $userDetails[0];
        }
    }
    
    /*
     * forgotPassword method sends password reset link on email 
     * @params email
     * 
    */
    public function forgotPassword($email=null){
        $return = array('status' => true, 'error' => '');
        $userData = $this->getUserDetailsByEmail($email); //get user details using email 
        $userId = $userData[_USER_ID];
        $fieldsArray = [_USER_STATUS => 0,_USER_MODIFIEDBY => $userId];
        $conditions  = [_USER_ID => $userId]; 
        $this->Users->updateRecords($fieldsArray, $conditions); //update status for activation link 
		
        $status = $this->sendActivationLink($userId,$userData['email'],$userData['name'],_FORGOTPASSWORD_SUBJECT);
        return $return;
    }

    /*
      function to get User details by User id 
     * @ params userId 
       @ returns array of user details  
     */

    public function getUserDetailsById($userId = null) {

        $data = [];
        if (!empty($userId)) {
            $fieldsArray = [_USER_ID,_USER_NAME,_USER_EMAIL, _USER_STATUS,_USER_ROLE_ID];
            $conditionArray = [_USER_ID => $userId];
            $dt = $this->getUserDetails($fieldsArray, $conditionArray);
            if (isset($dt[0]))
                $data = $dt[0];
        }
        return $data;
    }

    /*
     * 
     * function to return the role id on basis of passed role value
     * @roleValue is  passed as roles  like 'ADMIN' or 'DATAENTRY'
     */

    public function getDbRolesDetails($fields = [], $conditions = [], $type = 'all', $extra = []) {
        return $this->RUserDatabasesRoles->getDetails($fields, $conditions, $type, $extra);
    }

    /*
     * checkDEAccess to check whether its role is DE or not 
     * returns true of false 
     * $roleId is the role id 
     * 
     */
    public function checkDEAccess($roleId = '') {
        $data = $this->Roles->returnRoleValue($roleId);
        if ($data == _DATAENTRY_ROLE) {
            return true;
        }
    }
	
	/*
     * checkSAAccess to check whether its role is SA or not 
     * returns true of false     
     */
    public function checkSAAccess() {
		$authUserRoleId = $this->Auth->User(_USER_ROLE_ID);
        $data = $this->Roles->returnRoleValue($authUserRoleId);
        if ($data == _SUPERADMIN_ROLE) {
            return true; //if super admin 
        }
		return false; // if not sa 
    }
	
	/*
     * check user add delete modify authentication rights 
     * returns  false if user not allowed else true 
     * @dbId is the database id  
	 * @toId  the user id on whom action performed 
     * @postedRoles an array of posted roles  
     */
	public function checkAuthorizeUser($toId,$dbId,$postedRoles=[]){
		
		$authuserId        = $this->Auth->User(_USER_ID);
		$authRoleId        = $this->Auth->User(_USER_ROLE_ID);
		$fromroleValue     = $this->Roles->returnRoleValue($authRoleId); //returns Role value on basis of role id 
		
        if ($fromroleValue == _SUPERADMIN_ROLE) {
			/// case  for multiple SuperAdmin  			
            return true;    // if super admin allow all access

        }
		
		$loggedUserRoles =  $this->getUserDatabasesRoles($authuserId,$dbId);		
		$toUserRoles     =  $this->getUserDatabasesRoles($toId,$dbId);
		$returnValue     =  '';		
		$returnValue     =  $this->checkAdminRoleAccess(_ADMIN_ROLE,$loggedUserRoles,$toUserRoles,$postedRoles); // check for admin role
		if($returnValue === 'NA'){
			$returnValue = false;
		}
		
		return $returnValue;
		
	}

	
	
	
	
	
	/*
	 function returns true if user is allowed to perform action  checks for admin only
	@roleValue will be type of role //ADMIN
	@loggedUserRoles will be array of all roles of logged in user 
	@toUserRoles will be array of all roles of selected  user 
	@postedRoles will be array of roles posted while modification 
	*/	
	public function checkAdminRoleAccess($roleValue,$loggedUserRoles=[],$toUserRoles=[],$postedRoles=[]){
		if(in_array($roleValue,$loggedUserRoles)==true){
				
				if(!empty($toUserRoles) && in_array($roleValue,$toUserRoles)==true)				
					return false ;
				
				if(isset($postedRoles) && in_array($roleValue,$postedRoles)==true)
				 return false;
			 
			return true;
		}
		return 'NA'; //when not a DE or TEMP user 
	}
	
    /*

    function to manage user (add/modify/assign new dataabse)
	@dbId is the databse id 
	@inputArray posted array 
    */
    public function saveUserDetails($inputArray=array(), $dbId=null) {
        $returnData = true;      
	
        if(!isset($inputArray['dbId'])) $inputArray['dbId'] = $dbId;
		
        $validated = $this->getValidatedUserFields($inputArray);
        
        if($validated['isError']===false) {
            // no validation error
            if(empty($inputArray['id'])) {
                if(!isset($inputArray['createdby'])) $inputArray['createdby'] = $this->Auth->User('id');
                $inputArray['status'] = 0;
            }
            if(!isset($inputArray['modifiedby'])) $inputArray['modifiedby'] = $this->Auth->User('id');

            $lastIdinserted = $this->addModifyUser($inputArray, $dbId);//add modify user 
            if ($lastIdinserted > 0) {
                // success
                $returnData = true;

                if (empty($inputArray['id'])) {
                    // for new user, send an activation link
                    $this->sendActivationLink($lastIdinserted, $inputArray['email'], $inputArray['name'], _ACTIVATIONEMAIL_SUBJECT);

                }
                else if($inputArray['isModified']=='false') {
                    // get database details
                    $dbArray = $this->Common->parseDBDetailsJSONtoArray($dbId);

                    // for exisiting user, send an assigned database link
                    $this->sendDbAddNotify($inputArray['email'], $inputArray['name'], $dbArray['db_connection_name']);
                }
            }
            else {
                //$returnData = _ERR114;      // user not modified due to database error 
                $returnData = _ERR100;      // user not modified due to database error 
            }
        }
        else {
            // there is some error
            $returnData = $validated['errCode'];
        }
        
        return $returnData;
        
    }

    /*
    function to get validated user fields before saving into db 
    */
    function getValidatedUserFields($fields=[]) {
        $validated = ["isError"=>false, "errCode"=>''];
        $errCode = '';

        if(count($fields) > 0) {
            $name = (isset($fields['name'])) ? trim($fields['name']) : '';
            $email = (isset($fields['email'])) ? trim($fields['email']) : '';
            $uId = (isset($fields['id'])) ? trim($fields['id']) : 0;
            $isModified = (isset($fields['isModified'])) ? trim($fields['isModified']) : false;
            $roleArray = (isset($fields['roles'])) ? $fields['roles'] : [];

            if(!empty($name) && !empty($email)) {
                $chkEmail = $this->checkEmailExists($email, $uId);
                if ($chkEmail > 0) {   // email is unique
                    $errCode = _ERR118;   //  user not modified due to email  already exists  
                }
                else {
                    if (!empty($uId) && $isModified=='false') {
                        if(isset($fields['dbId']) && !empty($fields['dbId'])) {
                            $chkuserDbRel = $this->checkUserDbRelation($uId, $fields['dbId']); //
                            if($chkuserDbRel > 0) {
                                $errCode = _ERR119;   //  user is already added to this database
                            }    
                        }
                        else {
                            $errCode = _ERR106; //  db id is empty
                        }                        
                    }
                }
            }
            else {
                $errCode = _ERR111; //  Email or  name may be empty 
            }

            // Check for User Role
            if(count($roleArray)==0) {
                $errCode = _ERR112; //  Roles are  empty
            }
            
        }
        else {
            $errCode = '';
        }

        if(!empty($errCode)) {
            $validated['isError'] = true;
            $validated['errCode'] = $errCode;
        }

        return $validated;
        
    }
	
	
	
	/*
      listAllUsersDb to get listing of all users with their roles related to specific databases
     * @params dbId is database id 
     * @params userid is user id 		
     */
    public function listSpecificUsersdetails($userId,$dbId) {
			$userRoles =[];
			$userData = $this->getUserDetailsById($userId);
			if(!empty($userData)){
				
			
			$userRoles[]=$userData;

			$roleIdsDb['roles'] = $this->getUserDatabasesRoles($userId, $dbId); //get roles of users of dbId
			$getidsRUD = $this->getUserDatabaseId($userId, $dbId); //get ids of RUD table
			$getidsRUDR = $this->RUserDatabasesRoles->getRoleIDsDatabase($getidsRUD); // return array index for RUDR id and value for roleid 
			$userRoles[0]['roles'] = $roleIdsDb['roles'];
			$getidsRUDR = array_keys($getidsRUDR);//get rudr table ids which are stored on index of array 
			$getAssignedAreas = $this->UserAccess->getAssignedAreas($getidsRUDR);    //get area ids 
			$getAssignedIndis = $this->UserAccess->getAssignedIndicators($getidsRUDR);//get indicator gids    
			
			$userRoles[0]['access']['area'] = array_values($getAssignedAreas);                
			$userRoles[0]['access']['indicator'] = array_values($getAssignedIndis);      
			$userRoles =current($userRoles);
			//pr($userRoles);die;
			}
			return $userRoles;
    }

}
