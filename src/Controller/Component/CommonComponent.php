<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Configure;

//use Cake\Network\Email\Email;

/**
 * Common period Component
 */
class CommonComponent extends Component {

    public $MDatabaseConnections = '';
    public $MSystemConfirgurations = '';
    public $dbcon = '';
    public $Users = '';
    public $Roles = '';
    public $components = ['Auth', 'UserAccess', 'MIusValidations', 'DevInfoInterface.CommonInterface', 'UserCommon'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->MDatabaseConnections = TableRegistry::get('MDatabaseConnections');
        $this->MSystemConfirgurations = TableRegistry::get('MSystemConfirgurations');
        $this->Users = TableRegistry::get('Users');
        $this->Roles = TableRegistry::get('MRoles');
    }

    /*
      guid is function which returns gid
     */

    public function guid() {

        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            //$uuid =// chr(123)// "{"
            $uuid = substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12);
            //.chr(125);// "}"
            return $uuid;
        }
    }
    	
    /**
     * Check for valid Guid
     * 
     * @param string $guid Guid String accepts only A-Z, a-z, @, 0-9, _, -, $
     * @return boolean true/false
     */
    public function validateGuid($gid) {
        if(preg_match('/^[0-9a-zA-Z\$@\_\-]+$/', $gid) === 0) {
            return false;  // not valid 
        } else {
            return true; // when its valid 
        }
    }

    /*
     * 
     * Create database connection details
     * @$data passed as array
     */

    public function createDatabasesConnection($data = array()) {
        return $this->MDatabaseConnections->insertData($data);
    }


     /*
     * 
     * Update database connection details
     * @$data passed as array
     */

    public function updateDatabasesConnection($data = array()) {
        return $this->MDatabaseConnections->insertData($data);
    }

    /*
     * 
     * check the database connection  
     */

    public function testConnection($connectionstring = null) {

        $db_source = '';
        $db_connection_name = '';
        $db_host = '';
        $db_password = '';
        $db_login = '';
        $db_database = '';
        $db_port = '';
        $connectionstringdata = [];
        $connectionstring = json_decode($connectionstring, true);

        if (isset($connectionstring[_DATABASE_CONNECTION_DEVINFO_DB_CONN])) {

            $connectionstringData = json_decode($connectionstring[_DATABASE_CONNECTION_DEVINFO_DB_CONN], true);
            $db_source = trim($connectionstringData['db_source']);
            $db_connection_name = trim($connectionstringData['db_connection_name']);
            $db_host = trim($connectionstringData['db_host']);
            $db_login = trim($connectionstringData['db_login']);
            $db_password = trim($connectionstringData['db_password']);
            $db_port = trim($connectionstringData['db_port']);
            $db_database = trim($connectionstringData['db_database']);

            $db_source = strtolower($db_source);
        }




        $flags = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );

        if ($db_source == 'mysql') {
            try {
                $this->dbcon = new \PDO('mysql:host=' . $db_host . ';dbname=' . $db_database, $db_login, $db_password, $flags);
                return true;
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        } else {
            try {
                $this->dbcon = new \PDO(
                        "sqlsrv:server={$db_host};Database={$db_database}", $db_login, $db_password, $flags
                );
                return true;
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        }
    }

    /*
      Function getDbDetails is to get  the database information with respect to passed database id
      @$dbId is used to pass the database id
     */

    public function getDbConnectionDetails($dbId) {

        $databasedetails = array();

        $databasedetails = $this->MDatabaseConnections->getDbConnectionDetails($dbId);

        return $databasedetails;
    }

    /*
      Function getDbDetails is to get  the database information with respect to passed database id
      @$dbId is used to pass the database id
     */

    public function parseDBDetailsJSONtoArray($dbId) {

        $databasedetails = $this->getDbConnectionDetails($dbId);
        return json_decode($databasedetails, true);
    }

    /*
      Function getDbNameByID is to get  the database information with respect to passed database id
      @$dbId is used to pass the database id
     */

    public function getDbNameByID($dbId) {

        $databasedetails = array();

        $databasedetails = $this->MDatabaseConnections->getDbNameByID($dbId);

        return $databasedetails;
    }

    /*
      Get List of the Database as per the logged in  User
     *
     */

    public function getDatabases() {

        $userId = $this->Auth->User('id');
        $roleId = $this->Auth->User('role_id');

        if ($roleId == _SUPERADMINROLEID) // for super admin acces to all databases            
            $returnDatabaseDetails = $this->MDatabaseConnections->getAllDatabases();
        else
            $returnDatabaseDetails = $this->getdatabaseListOfUser($userId); //db list for logged in user 

        return $returnDatabaseDetails;
    }

    /*
     * Function deleteDatabase is used for deleting the database details
     * $dbId  database id 
     * $userId user id 
     */

    public function deleteDatabase($dbId, $userId) {

        return $databasedetails = $this->MDatabaseConnections->deleteDatabase($dbId, $userId);
    }

    /*
      getdatabaseListOfUser to get the list of all the databases associated to specific users
      $userId the user Id of user
     */

    public function getdatabaseListOfUser($userId) {
        $data = array();
        $All_databases = $this->Users->getdatabaseList($userId);
        $alldatabases = current($All_databases)['m_database_connections'];
        if (isset($alldatabases) && !empty($alldatabases)) {
            foreach ($alldatabases as $index => $valuedb) {
                $connectionObject = json_decode($valuedb[_DATABASE_CONNECTION_DEVINFO_DB_CONN], true);
                if (isset($connectionObject['db_connection_name']) && !empty($connectionObject['db_connection_name']) && $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ARCHIVED] == '0') {
                    $dbId = $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ID];
                    $data[] = [
                        'id' => $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ID],
                        'dbName' => $connectionObject['db_connection_name'],
                        'dbRoles' => $this->UserCommon->getUserDatabasesRoles($userId, $dbId)
                    ];
                }
            }
        }
        return $data;
    }

    /*
      uniqueConnection is used to check the uniqueness of database connection name
      @$dbConnectionName is used to pass the database Connection Name
     */

    public function uniqueConnection($dbConnectionName) {
        $databasedetails = $this->MDatabaseConnections->uniqueConnection($dbConnectionName);
        return $databasedetails;
    }

    /*
      function to check activation link is used or not
      @params $userId , $email
     */

    public function checkActivationLink($userId) {
        $status = $this->Users->checkActivationLink($userId);
        return $status;
    }

    /*
     * Get mime Types List
     * 
     * @param array $allowedExtensions Allowed extensions
     * @return Mime Types array
     */

    public function mimeTypes($allowedExtensions = []) {
        $mimeTypes = [
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'zip2' => 'application/octet-stream'
        ];

        $allowedExtensionsMimeTypes = array_intersect_key($mimeTypes, array_flip($allowedExtensions));

        return $allowedExtensionsMimeTypes;
    }

    /*
     * Process File uploads
     * 
     * @param array $files POST $_FILES Variable
     * @param array $extensions Valid extension allowed 
     * @return uploaded filename
     */

    public function processFileUpload($files = null, $allowedExtensions = [], $extra = []) {

        // Check Blank Calls
        if (!empty($files)) {

            foreach ($files as $fieldName => $fileDetails):
                // Check if file was uploaded via HTTP POST
                if (!is_uploaded_file($fileDetails['tmp_name'])) :
                    return ['error' => _ERROR_UNACCEPTED_METHOD];
                endif;

                if (isset($extra['dest']) && $extra['dest'] == true) {
                    $pathinfo = pathinfo($fileDetails['name']);
                    $dest = $extra['dest'] . DS . $extra['dbName'] . '_' . $extra['subModule'] . '_' . date('Y-m-d-h-i-s', time()) . '.' . $pathinfo['extension'];
                } else {
                    $dest = _XLS_PATH . DS . $fileDetails['name'];
                }

                $mimeType = $fileDetails['type'];
                if (!in_array($mimeType, $this->mimeTypes($allowedExtensions))) {
                    return ['error' => _ERROR_INVALID_FILE];
                }

                // Upload File
                if (move_uploaded_file($fileDetails['tmp_name'], $dest)) :
                    if (isset($extra['createLog']) && $extra['createLog'] == true) {
                        $pathinfo = pathinfo($fileDetails['name']);
                        $copyDest = _LOGS_PATH . DS . _IMPORTERRORLOG_FILE . '_' . $extra['subModule'] . '_' . $extra['dbName'] . '_' . date('Y-m-d-h-i-s', time()) . '.' . $pathinfo['extension'];
                        if (!@copy($dest, $copyDest)) {
                            return ['error' => _ERROR_UPLOAD_FAILED];
                        }
                        define('_LOG_FILEPATH', $copyDest);
                    }
                    $filePaths[] = $dest;   // Upload Successful

                else:
                    return ['error' => _ERROR_UPLOAD_FAILED];   // Upload Failed
                endif;

            endforeach;

            return $filePaths;
        }
        return ['error' => _ERROR_LOCATION_UNACCESSIBLE];
    }

    /*
      function to get role details
     */

    public function getRoleDetails($roleId) {

        return $this->Roles->getRoleByID($roleId);
    }

    /*
      function to json data for tree view
     */

    public function getTreeViewJSON($type = _TV_AREA, $dbId = null, $parentId = -1, $onDemand = true, $idVal = '', $icType = '', $showGroup=false) {
        $returndData = [];

        if (!empty($dbId)) {
            $dbConnection = $this->getDbConnectionDetails($dbId);
            switch (strtolower($type)) {
                case _TV_AREA:
                    $extra = [];
                    // Get Area access
                    $areaAccess = $this->UserAccess->getAreaAccessToUser(['type' => 'list']);
                    if (!empty($areaAccess)) {
                        $extra['conditions'] = [_AREA_AREA_ID . ' IN' => array_keys($areaAccess)];
                        $extra['childExists'] = false;
                        $onDemand = true;
                    }
                    // Get Area Tree Data
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getParentChild', ['Area', $parentId, $onDemand, $extra], $dbConnection);
                    break;

                case _TV_IU:
                    $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);
                    // get Subgroup Tree data
                    if ($parentId != '-1') {
                        $parentIds = explode(_DELEM1, $parentId);
                        if (empty($indicatorGidsAccessible) || in_array($parentIds[0], $indicatorGidsAccessible)) {
                            $fields = [_IUS_SUBGROUP_VAL_NID];
                            $params['fields'] = $fields;
                            $params['conditions'] = ['iGid' => $parentIds[0], 'uGid' => $parentIds[1]];
                            $params['extra'] = ['type' => 'all', 'unique' => true];
                            $returndData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getAllSubgroupsFromIUGids', $params, $dbConnection);
                        } else {
                            return ['error' => _UNAUTHORIZED_ACCESS];
                        }
                    }// get IU Tree data
                    else {
                        //$fields = [_IUS_IUSNID, _IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID];
                        $fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID];
                        $conditions = [];

                        $extra = ['type' => 'all', 'unique' => false, 'onDemand' => $onDemand, 'group' => true];
                        if ($indicatorGidsAccessible !== false && !empty($indicatorGidsAccessible)) {
                            //$conditions = [_IUS_INDICATOR_NID . ' IN' => $indicatorGidsAccessible];
                            $extra['indicatorGidsAccessible'] = $indicatorGidsAccessible;
                        }
                        $params = ['fields' => $fields, 'conditions' => $conditions, 'extra' => $extra];
                        $returndData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getAllIU', $params, $dbConnection);
                    }
                    break;

                case _TV_IUS:
                    // coming soon
                    break;

                case _TV_IC:
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getParentChild', ['IndicatorClassifications', $parentId, $onDemand, ['conditions' => [_IC_IC_TYPE => $icType]]], $dbConnection);
                    break;

                case _TV_ICIND:
                    $returndData = $this->getICINDList($type, $dbConnection, $parentId, $onDemand);
                    break;

                case _TV_IND:
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getIndicatorList', ['Indicator'], $dbConnection);
                    break;

                case _TV_UNIT:
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getUnitList', [], $dbConnection);
                    break;

                case _TV_ICIUS:
                    // coming soon
                    break;

                case _TV_TP:
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getTimePeriodTreeList', $params = [], $dbConnection);
                    break;

                case _TV_SOURCE:
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getSourceTreeList', $params = [], $dbConnection);
                    break;
				
				case _TV_SGVAL:
				
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getSubgroupValTreeList', $params = [], $dbConnection);
                    break;
				case _TV_SGTYPE:
			
				$returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getSubgroupTypeTreeList', $params = [], $dbConnection);
				break;
            }
        }
				

        $data = $this->convertDataToTVArray($type, $returndData, $onDemand, $dbId, $idVal, $showGroup);

        return $data;
    }

    /*
     * get the inidcators with classifications combination 
     * @$type will  be ICIND 
     * @$dbConnection will be dbconnection details 
     * @parentId can be -1 or blank 
     * @$onDemand value can be true or false
     */

    public function getICINDList($type, $dbConnection, $parentId, $onDemand) {

        $returnData = [];
        if (empty($parentId) || $parentId == -1) {
            $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'getParentChild', ['IndicatorClassifications', $parentId, false], $dbConnection);
        } else {

            $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'getICIndicatorList', ['IcIus', $parentId, false], $dbConnection);
        }


        if (count($returnData) > 0 && $onDemand == false && (empty($parentId) || $parentId == -1)) {
            //Display only all IC records with those indicators whose IC id is sent                 
            $returnData = $this->prepareICINDList($returnData, $type, $dbConnection, $parentId, $onDemand);
        }

        return $returnData;
    }

    /*
     * 
     * prepareICINDList to prepare data for IC with Indicators 
     * Recursively works on input array 
     * $icData array having ic data with parent child data 
     * $dbConnection is the database connection details 
     * $parentId is the  nid of IC  
     * returns data indicators appended at last node 
     */

    public function prepareICINDList($icData, $type, $dbConnection, $parentId, $onDemand) {

        $i = 0;
        // start loop through area data
        foreach ($icData as $index => $value) {

            $NId = $value['nid'];
            $ID = $value['id'];
            $name = $value['name'];

            if ($value['childExists'] === false) {

                $indicatorData = $this->CommonInterface->serviceInterface('CommonInterface', 'getICIndicatorList', ['IcIus', $NId, false]);
                if (count($indicatorData) > 0) {
                    $icData[$i]['nodes'] = $indicatorData;
                }
            } else {

                $nodes = $value['nodes'];
                $icData[$i]['nodes'] = $this->prepareICINDList($nodes, $type, $dbConnection, $parentId, $onDemand);
            }

            $i++;
        }

        return $icData;
    }

    /*
      function to convert array data into tree view array
     */

    public function convertDataToTVArray($type, $dataArray, $onDemand, $dbId, $idVal = '', $showGroup=false) {
        $returnArray = array();
        $i = 0;
        foreach ($dataArray as $dt) {

            $caseData = $this->convertDataToTVArrayCase($type, $dt, $idVal, $showGroup);

            if (isset($caseData['returnData']) && $onDemand == true) {
                $caseData['returnData']['dbId'] = $dbId;
                $caseData['returnData']['type'] = $type;
                $caseData['returnData']['onDemand'] = $onDemand;
            }

            $returnArray[$i]['id'] = $caseData['rowid'];
            $returnArray[$i]['uId'] = $caseData['uid'];
            $returnArray[$i]['fields'] = $caseData['fields'];
            $returnArray[$i]['returnData'] = $caseData['returnData'];
            $returnArray[$i]['isChildAvailable'] = $dt['childExists'];
            if (count($dt['nodes']) > 0) {
                $returnArray[$i]['nodes'] = $this->convertDataToTVArray($type, $dt['nodes'], $onDemand, $dbId, $i);
            } else {
                $returnArray[$i]['nodes'] = $dt['nodes'];
            }

            $i++;
        }

        return $returnArray;
    }

    /*
      function to get case wise data
     */

    function convertDataToTVArrayCase($type, $data, $idVal = '', $showGroup=false) {
        $retData = $fields = $returnData = array();
        $rowid = $uid = '';

        switch (strtolower($type)) {
            case _TV_AREA:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                if($showGroup && isset($data['block']) && !empty($data['block'])) {
                    // group handling
                    $fields = array('gname' => $data['name']);
                }
                else {
                    $fields = array('aname' => $data['name']);    
                }                
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal)) $returnData['idVal'] = $idVal;
                $returnData['level'] = $data['areaLvl'];

                break;
            case _TV_IU:
                // Subgroup List
                if (array_key_exists(_IUS_IUSNID, $data)) {
                    $rowid = (strtolower($idVal) == 'nid') ? $data['iusNid'] : $data['iusGid'];
                    $uid = (strtolower($idVal) == 'nid') ? $data['iusGid'] : $data['IUSNId'];

                    $fields = array('sName' => $data['sName']);
                    $returnData = array('iusGid' => $data['iusGid'], _IUS_IUSNID => $data[_IUS_IUSNID]);
                }// IU List
                else {
                    $rowGid = $data['iGid'] . _DELEM1 . $data['uGid'];
                    $rowNid = (isset($data['iNid'])) ? $data['iNid'] . _DELEM1 . $data['uNid'] : '';
                    $rowid = (strtolower($idVal) == 'nid') ? $rowNid : $rowGid;
                    $uid = (strtolower($idVal) == 'nid') ? $rowGid : $rowNid;

                    $fields = array('iName' => $data['iName'], 'uName' => $data['uName']);
                    //$returnData = array('pnid' => $data['iGid'] . '{~}' . $data['uGid'], 'iGid' => $data['iGid'], 'uGid' => $data['uGid']);
                    $returnData = array('pnid' => $data['iGid'] . _DELEM1 . $data['uGid']);
                }

                break;
            case _TV_IU_S:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['sGid'];
                $uid = (strtolower($idVal) == 'nid') ? $data['sGid'] : $data['nid'];

                $fields = array('sName' => $data['sName']);
                $returnData = array('sGid' => $data['sGid'], _IUS_IUSNID => $data[_IUS_IUSNID]);
                if (!empty($idVal)) $returnData['idVal'] = $idVal;

                break;
            case _TV_IUS:
                // coming soon
                break;
            case _TV_IC:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('icName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal)) $returnData['idVal'] = $idVal;

                break;
            case _TV_ICIND:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('icName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal)) $returnData['idVal'] = $idVal;

                break;
            case _TV_IND:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('iName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal)) $returnData['idVal'] = $idVal;

                break;
            case _TV_UNIT:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('uName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal)) $returnData['idVal'] = $idVal;

                break;
            case _TV_ICIUS:
                // coming soon
                break;
            case _TV_TP:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('tName' => $data['name']);
                $returnData = []; //array('pnid' => $data['nid']);

                break;
            case _TV_SOURCE:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('srcName' => $data['name']);
                $returnData = []; //array('pnid' => $data['nid']);

                break;
			case _TV_SGVAL:
			    $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

			    $fields = array('sName' => $data['name']);
			    $returnData = []; //array('pnid' => $data['nid']);

			break;
			
			case _TV_SGTYPE:
			    $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

			    $fields = array('sName' => $data['name']);
			    $returnData = []; //array('pnid' => $data['nid']);

			break;
        }

        return array('rowid' => $rowid, 'uid' => $uid, 'fields' => $fields, 'returnData' => $returnData);
    }

    /*
      function to add/update IUS validations
     */

    function addUpdateIUSValidations($dbId, $iusGids = [], $extra = []) {

        $status = false;

        foreach ($iusGids as $iusGid) {
            $iusGidsExploded = explode(_DELEM1, $iusGid);
            $subgroupGid[] = isset($iusGidsExploded[2]) ? $iusGidsExploded[2] : '';

            if (empty($subgroupGid[0])) {
                // find all subgroup gids from the database and fill the array 
                $subgroupGid = $this->getAllSubGrpsFromIU($dbId, $iusGidsExploded[0], $iusGidsExploded[1], 'sGid');
            }
            //pr($subgroupGid);
            foreach ($subgroupGid as $sGid) {
                if (!empty($sGid)) {

                    // insert/update into database
                    $extra['first'] = true;
                    $fields = [_MIUSVALIDATION_ID];
                    $conditions = [
                        _MIUSVALIDATION_DB_ID => $dbId,
                        _MIUSVALIDATION_INDICATOR_GID => $iusGidsExploded[0],
                        _MIUSVALIDATION_UNIT_GID => $iusGidsExploded[1],
                        _MIUSVALIDATION_SUBGROUP_GID => $sGid
                    ];
                    $validationExist = $this->MIusValidations->getRecords($fields, $conditions, 'all', $extra);

                    if (($extra['isTextual'] === true || $extra['isTextual'] == 'true')) {
                        $isTextual = 1;
                        $minimumValue = null;
                        $maximumValue = null;
                    }// isTextual is un-checked
                    else {
                        $isTextual = 0;
                        if (isset($extra['minimumValue']) && !empty($extra['minimumValue']) && (preg_match('/(\d+\.?\d*)/', $extra['minimumValue']) == 0)) {
                            $minimumValue = null;
                            return ['error' => _ERR127];
                        } else {
                            $minimumValue = $extra['minimumValue'];
                        }
                        if (isset($extra['maximumValue']) && !empty($extra['maximumValue']) && (preg_match('/(\d+\.?\d*)/', $extra['maximumValue']) == 0)) {
                            $maximumValue = null;
                            return ['error' => _ERR127];
                        } else {
                            $maximumValue = $extra['maximumValue'];
                        }
                    }

                    // Update Case
                    if (!empty($validationExist)) {
                        $conditions = [_MIUSVALIDATION_ID => $validationExist[_MIUSVALIDATION_ID]];
                        $updateArray = [
                            _MIUSVALIDATION_IS_TEXTUAL => $isTextual,
                            _MIUSVALIDATION_MIN_VALUE => $minimumValue,
                            _MIUSVALIDATION_MAX_VALUE => $maximumValue,
                            _MIUSVALIDATION_MODIFIEDBY => $this->Auth->user('id')
                        ];
                        $this->MIusValidations->updateRecord($updateArray, $conditions);
                        $status = true;
                    }
                    //Insert Case
                    else {
                        $MIusValidationsInsert[] = [
                            _MIUSVALIDATION_DB_ID => $dbId,
                            _MIUSVALIDATION_INDICATOR_GID => $iusGidsExploded[0],
                            _MIUSVALIDATION_UNIT_GID => $iusGidsExploded[1],
                            _MIUSVALIDATION_SUBGROUP_GID => $sGid,
                            _MIUSVALIDATION_IS_TEXTUAL => $isTextual,
                            _MIUSVALIDATION_MIN_VALUE => $minimumValue,
                            _MIUSVALIDATION_MAX_VALUE => $maximumValue,
                            _MIUSVALIDATION_CREATEDBY => $this->Auth->user('id')
                        ];
                    }
                }
            }

            // insert bulk
            if (isset($MIusValidationsInsert) && count($MIusValidationsInsert) > 0) {
                $this->MIusValidations->insertOrUpdateBulkData($MIusValidationsInsert);
                $status = true;
            }
        }

        return $status;
    }

    /*
      function to add/update IUS validations
     */

    function getAllSubGrpsFromIU($dbId, $iGid = null, $uGid = null, $flags = 'sGid') {

        $returnData = [];

        if (!empty($iGid) && !empty($uGid)) {
            $dbConnection = $this->getDbConnectionDetails($dbId);

            $params = [];
            $params['fields'] = [_IUS_SUBGROUP_VAL_NID];
            $params['conditions'] = ['iGid' => $iGid, 'uGid' => $uGid];
            $params['extra'] = ['type' => 'all', 'unique' => true];
            $data = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getAllSubgroupsFromIUGids', $params, $dbConnection);
            if ($data) {
                $i = 0;
                foreach ($data as $iusGid) {
                    $key = $i;
                    if ($flags == 'sGid') {
                        $sGrp = explode(_DELEM1, $iusGid['iusGid']);
                        $value = $sGrp[2];
                    } else if ($flags == 'IUSGid') {
                        $value = $iusGid['iusGid'];
                    } else if ($flags == 'IUSNId') {
                        $value = $iusGid['IUSNId'];
                    } else if ($flags == 'sgrpDetail') {
                        $sGrp = explode(_DELEM1, $iusGid['iusGid']);
                        $key = $sGrp[2];
                        $value = $iusGid['sName'];
                    }

                    if (isset($key) && !empty($value)) {
                        $returnData[$key] = $value;
                    }
                    $i++;
                }
            }
        }
        return $returnData;
    }

    /*
      function to delete IUS
     */

    function deleteIUS($dbConnection, $iusGids = []) {

        $status = false;

        foreach ($iusGids as $iusGid) {
            $iusGidsExploded = explode(_DELEM1, $iusGid);
            $subgroupGid[] = isset($iusGidsExploded[2]) ? $iusGidsExploded[2] : '';

            if (empty($subgroupGid[0])) {
                // find all subgroup gids from the database and fill the array 
                $subgroupGid = $this->getAllSubGrpsFromIU($dbId, $iusGidsExploded[0], $iusGidsExploded[1], 'sGid');
            }
            //pr($subgroupGid);
            foreach ($subgroupGid as $sGid) {
                if (!empty($sGid)) {

                    // delete IUS record
                    $params = [];
                    $params['conditions'] = ['iGid' => $parentIds[0], 'uGid' => $parentIds[1]];
                    $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'deleteRecords', $params, $dbConnection);
                }
            }
            $status = true;
        }

        return $status;
    }

    /**
     * get Data Details
     *
     * @param array $areaNid Gids Array. {DEFAULT : empty}
     * @param array $timePeriodNid Data Array From XLS/XLSX/CSV.
     * @param array $iusgidArray Fields to be inserted Array.
     * @param array $dbConnection Fields to be inserted Array.
     * @return void
     */
    public function deSearchIUSData($areaNidArray, $timePeriodNidArray, $iusgidArray, $extraParams = []) {

        $iusData = $iusList = [];
        if (!empty($extraParams))
            extract($extraParams);

        $conditions = [_MDATA_TIMEPERIODNID . ' IN ' => $timePeriodNidArray, _MDATA_AREANID . ' IN ' => $areaNidArray];
        $fields = [];
        $params['fields'] = $fields;
        $params['conditions'] = $conditions;
        $params['extra'] = $iusgidArray;
        $returnData = $this->CommonInterface->serviceInterface('Data', 'getData', $params, $dbConnection);

        // if Data Exist
        if (isset($returnData['data'])) {
            // Get FootNote list
            $footnotes = $this->CommonInterface->serviceInterface('Data', 'getFootnoteList', []);

            $iusGids = (isset($returnData['iusInfo']['iusGids'])) ? $returnData['iusInfo']['iusGids'] : [];

            foreach ($returnData['data'] as $dt) {
                if (!empty($dt['Data_NId'])) {
                    $iusGid = '';
                    if (isset($iusGids[$dt['IUSNId']])) {
                        $iusGid = $iusGids[$dt['IUSNId']]['indicator_gid'] . _DELEM1 . $iusGids[$dt['IUSNId']]['unit_gid'] . _DELEM1 . $iusGids[$dt['IUSNId']]['subgroup_gid'];
                    }

                    $iusData[] = [
                        'dNid' => $dt['Data_NId'],
                        'iusNid' => $dt['IUSNId'],
                        'iusGid' => $iusGid,
                        'tpNid' => $dt['TimePeriod_NId'],
                        'srcNid' => $dt['Source_NId'],
                        'aNid' => $dt['Area_NId'],
                        'footnote' => (isset($footnotes[$dt['FootNote_NId']])) ? $footnotes[$dt['FootNote_NId']] : '',
                        'dv' => $dt['Data_Value']
                    ];
                }
            }
            // get IUS Validation
            $iusValidations = $this->getIUSValidations($iusGids, $dbId);

            // prepare iusList
            $iusList = (isset($returnData['iusInfo']['ius'])) ? $returnData['iusInfo']['ius'] : [];
        }

        $return = ['iusData' => $iusData, 'iusValidations' => $iusValidations, 'iusList' => $iusList];
        return $return;
    }

    /**
     * function to get IUS validations
     */
    public function getIUSValidations($iusGids = [], $dbId) {
        $returnData = [];
        $gidsNidsArray = [];
        foreach ($iusGids as $key => $gids) {
            $gidsNidsArray[implode(_DELEM1, $gids)] = $key;
        }

        if ($iusGids) {

            $fields = [
                _MIUSVALIDATION_INDICATOR_GID,
                _MIUSVALIDATION_UNIT_GID,
                _MIUSVALIDATION_SUBGROUP_GID,
                _MIUSVALIDATION_IS_TEXTUAL,
                _MIUSVALIDATION_MIN_VALUE,
                _MIUSVALIDATION_MAX_VALUE
            ];
            $conditions = ['OR' => $iusGids, _MIUSVALIDATION_DB_ID => $dbId];
            $getIUSValidations = $this->MIusValidations->getRecords($fields, $conditions, 'all', $extra = []);

            foreach ($getIUSValidations as $records) {
                $isTextual = ($records[_MIUSVALIDATION_IS_TEXTUAL] == '1') ? true : false;
                $minimumValue = $records[_MIUSVALIDATION_MIN_VALUE];
                $maximumValue = $records[_MIUSVALIDATION_MAX_VALUE];
                $isMinimum = ($minimumValue === NULL || $minimumValue === '') ? false : true;
                $isMaximum = ($maximumValue === NULL || $maximumValue === '') ? false : true;
                $validationsArray = [
                    'isTextual' => $isTextual,
                    'isMinimum' => $isMinimum,
                    'isMaximum' => $isMaximum,
                    'minimumValue' => $minimumValue,
                    'maximumValue' => $maximumValue,
                ];
                $gidStr = $records[_MIUSVALIDATION_INDICATOR_GID] . _DELEM1 . $records[_MIUSVALIDATION_UNIT_GID] . _DELEM1 . $records[_MIUSVALIDATION_SUBGROUP_GID];
                $iusNId = $gidsNidsArray[$gidStr];

                $returnData = [
                    $iusNId => $validationsArray
                ];
            }
        }
        return $returnData;
    }

    /*
      function to get html data format for log file
      $params array
     */

    function getHtmlData($params = []) {

        $data = (isset($params['data'])) ? $params['data'] : '';
        $dbConnName = (isset($params['data'])) ? $params['dbConnName'] : '';
        $startTime = (isset($data['startTime'])) ? $data['startTime'] : 0;
        $endTime = (isset($data['endTime'])) ? $data['endTime'] : 0;
        $noofImportedRec = (isset($data['totalImported'])) ? $data['totalImported'] : 0;
        $noofErrors = (isset($data['totalIssues'])) ? $data['totalIssues'] : 0;
        $errMsgArr = (isset($data['issues'])) ? $data['issues'] : 0;

        $txt = "<table>
			<tr><td colspan='2'>&nbsp; </td></tr>
			<tr><td colspan='2'>&nbsp; </td></tr>
		   <tr><td colspan='2'><b><H1>Database Administration Log</H1></b> </td></tr>
			<tr><td width='150px;'>&nbsp;</td><td>&nbsp;</td></tr>
			 <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
			 <tr><td align='left' ><b>Module :</b>  </td><td align='left' >Form Data</td></tr>
			<tr><td align='left' ><b>Database Name:</b> </td><td align='left'>" . $dbConnName . "</td></tr>
			<tr><td align='left' ><b>Date:</b> </td><td align='left'>" . date('Y-m-d') . "</td></tr>
			<tr><td align='left'><b>Start Time :</b>  </td><td align='left' >" . date('H:i:s', strtotime($startTime)) . "</td></tr>
			<tr><td align='left' ><b>End Time :</b>  </td><td align='left' >" . date('H:i:s', strtotime($endTime)) . "</td></tr>
			<tr><td align='left' ><b>No. of Imported Records :</b>  </td><td align='left' >" . $noofImportedRec . "</td></tr>
			<tr><td align='left' ><b>Errors List :</b> </td><td></td></tr>";
        if (!empty($errMsgArr) && count($errMsgArr) > 0) {
            foreach ($errMsgArr as $value) {
                $txt .="<tr><td>Row " . $value['rowNo'] . "  </td><td>" . $value['msg'] . "</td></tr>";
            }
        }
        $txt .="</table>";
        return $txt;
    }

    /*
      method to create the custom log file and save on server
     */

    public function writeLogFile($data = '', $dbId = '') {

        $dbData = $this->getDbConnectionDetails($dbId); //get connection details
        $dbData = json_decode($dbData, true);
        $db_connection_name = str_replace(' ', '-', $dbData['db_connection_name']); //connection name 
        $logfilename = _CUSTOMLOG_FILE . date('l') . ',' . date('F-d-Y-H-i-s') . '-' . $db_connection_name . '.html';
        $dbConnName = $dbData['db_connection_name'];
        $params = ['data' => $data, 'dbConnName' => $dbConnName];

        $logfile = fopen(_LOGS_PATH . DS . $logfilename, "w") or die("Unable to open file!");
        $html = $this->getHtmlData($params);
        fwrite($logfile, $html);
        fclose($logfile);
        $filepath = _LOGS_PATH . DS . $logfilename;

        return ['status' => true, 'filepath' => $filepath];
    }

    /**
     * function to search in multiple dimension array
     */
    public function arraySearch($searchVal, $array = []) {
        if (is_array($array) && count($array) > 0) {
            $foundkey = array_search($searchVal, $array);
            if ($foundkey === false) {
                foreach ($array as $key => $value) {
                    if (is_array($value) && count($value) > 0) {
                        $valueStr = implode(_DELEM1, $value);
                        if ($searchVal == $valueStr) {
                            return $key;
                        } else {
                            $foundkey = array_search($searchVal, $value);
                            
                            if ($foundkey != false)
                                return $key;
                        }
                    }
                }
            }
            return $foundkey;
        }
    }

    /**
     * get Source Details (Publisher/Source/Year)
     * 
     * @param array $params Extra parameters like fields, conditons etc.
     * @param array $dbConnection Database connection details
     * @return array Publisher, Source, Year individual Lists
     */
    public function getSourceBreakupDetails($params, $dbConnection) {
        $publisher = $source = $year = [];

        $sourceDetails = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getSource', $params, $dbConnection);

        // Get Publisher
        $publihserKeys = array_filter(array_column($sourceDetails, _IC_IC_PARENT_NID), function($value) {
            return $value == '-1';
        });
        $publisherDetails = array_intersect_key($sourceDetails, $publihserKeys);

        // Get Source
        $sourceDetails = array_diff_key($sourceDetails, $publihserKeys);
        foreach ($sourceDetails as $key => $sourceDetail) {
            $sourceName = str_replace($sourceDetail[_IC_PUBLISHER] . _DELEM4, '', $sourceDetail[_IC_IC_NAME]);
            $sourceName = str_replace(_DELEM4 . $sourceDetail[_IC_DIYEAR], '', $sourceName);
            $source[] = trim($sourceName);
        }

        // Prepare Return
        $publisher = array_unique(array_filter(array_column($publisherDetails, _IC_IC_NAME)));
        $source = array_unique(array_filter($source));
        $year = array_unique(array_filter(array_column($sourceDetails, _IC_DIYEAR)));

        sort($publisher);
        sort($source);
        sort($year);

        return ['publisher' => array_values($publisher), 'source' => array_values($source), 'year' => array_values($year)];
    }
    
	

     /*

    function to manage user add/modify dbConnection Details
	@dbId is the databse id 
	@inputArray posted array 
    */
    public function saveDbConnectionDetails($inputArray=array(), $dbId) {
       
        $returnData = true;      
        if(!empty($dbId)) {           
            $inputArray[_DATABASE_CONNECTION_DEVINFO_DB_ID] = $dbId;            
        }
        if(isset($inputArray['dbId'])) unset($inputArray['dbId']);

        $validated = $this->getValidatedDbConFields($inputArray, $dbId);
        
        if($validated['isError']===false) {

                           

            $db_source = (isset($inputArray['databaseType'])) ? trim($inputArray['databaseType']) : '';
            $db_connection_name = (isset($inputArray['connectionName'])) ? trim($inputArray['connectionName']) : '';
            $db_host = (isset($inputArray['hostAddress'])) ? trim($inputArray['hostAddress']) : '';
            $db_login = (isset($inputArray['userName'])) ? trim($inputArray['userName']) : '';
            $db_port = (isset($inputArray['port'])) ? trim($inputArray['port']) : '';
            $db_database = (isset($inputArray['databaseName'])) ? $inputArray['databaseName'] : '';
            $db_password = (isset($inputArray['password'])) ? $inputArray['password'] : '';
            
             $db_con = array(
                    'db_source' => $db_source,
                    'db_connection_name' => $db_connection_name,
                    'db_host' => $db_host,
                    'db_login' => $db_login,
                    'db_password' => $db_password,
                    'db_port' => $db_port,
                    'db_database' => $db_database
                );
                $jsondata = array(
                    _DATABASE_CONNECTION_DEVINFO_DB_CONN => json_encode($db_con)
                );                       
                $jsondata = json_encode($jsondata);  

                $inputArray[_DATABASE_CONNECTION_DEVINFO_DB_CONN] = json_encode( $db_con)  ; 
            
                unset($inputArray['databaseType']);
                unset($inputArray['userName']);
                unset($inputArray['password']);
                unset($inputArray['connectionName']);
                unset($inputArray['databaseName']);
                  unset($inputArray['port']);

            // no validation error
            if(empty($dbId)) {
                $inputArray['createdby'] = $this->Auth->User('id');
                $inputArray[_DATABASE_CONNECTION_DEVINFO_DB_ARCHIVED] = 1;
            }
            //print_r($)inputArray;exit;
            
            $inputArray['modifiedby'] = $this->Auth->User('id');
            if(empty($dbId)) {
                $lastIdinserted = $this->createDatabasesConnection($inputArray);
            }
            else { 
                 $inputArray[_DATABASE_CONNECTION_DEVINFO_DB_ID] = $dbId; 
                 unset($inputArray['id']);
                $lastIdinserted = $this->updateDatabasesConnection($inputArray);
            }

            if ($lastIdinserted > 0) {
                // success
                $returnData = true;                         
            }
            else {               
                $returnData = _ERR138;      // Db Connection not modified due to database error 
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
    function getValidatedDbConFields($fields=[], $dbId) {

        $has_error = false;
        $errCode = ''; //Invalid Parameters supplied

        $validated = ["isError"=>false, "errCode"=>''];
      
        if(count($fields) > 0) {

            $db_source = (isset($fields['databaseType'])) ? trim($fields['databaseType']) : '';
            $db_connection_name = (isset($fields['connectionName'])) ? trim($fields['connectionName']) : '';
            $db_host = (isset($fields['hostAddress'])) ? trim($fields['hostAddress']) : '';
            $db_login = (isset($fields['userName'])) ? trim($fields['userName']) : '';
            $db_port = (isset($fields['port'])) ? trim($fields['port']) : '';
            $db_database = (isset($fields['databaseName'])) ? $fields['databaseName'] : '';
            $db_password = (isset($fields['password'])) ? $fields['password'] : '';
            
            if(empty($db_connection_name) || empty($db_host) || empty($db_login) ||  empty($db_database) || empty($db_password)) {
                $has_error = TRUE;
                $errCode = _ERR135; //Missing Parameters
            }
            else{
               
                $db_con = array(
                    'db_source' => $db_source,
                    'db_connection_name' => $db_connection_name,
                    'db_host' => $db_host,
                    'db_login' => $db_login,
                    'db_password' => $db_password,
                    'db_port' => $db_port,
                    'db_database' => $db_database
                );
                $jsondata = array(
                    _DATABASE_CONNECTION_DEVINFO_DB_CONN => json_encode($db_con)
                );                       
                $jsondata = json_encode($jsondata);                        
                $returnTestDetails = $this->testConnection($jsondata);               
                if($returnTestDetails === true){ 
                    
                                               
                    //check unique connection name
                    $isUniqueCon = $this->MDatabaseConnections->uniqueConnection($db_connection_name, $dbId);
                    if( !$isUniqueCon === true) {
                        $has_error = TRUE;
                        $errCode = _ERR102; // connection name is  not unique
                    }                               
                }
                else{
                    $has_error = TRUE;
                    $errCode = _ERR101; // Invalid database connection details 
                }
            }

        }
        else {
            $has_error = TRUE;
            $errCode = _ERR135; //Missing Parameters
        }

        if($has_error) {
            $validated['isError'] = $has_error;
            $validated['errCode'] = $errCode;
        }

        return $validated;
        
    }
   
}
