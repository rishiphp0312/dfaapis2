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
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
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

                $dest = _XLS_PATH . DS . $fileDetails['name'];

                $mimeType = $fileDetails['type'];
                if (!in_array($mimeType, $this->mimeTypes($allowedExtensions))) {
                    return ['error' => 'Invalid file.'];
                }

                // Upload File
                // 
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

    public function getTreeViewJSON($type = _TV_AREA, $dbId = null, $parentId = -1, $onDemand = true) {
        $returndData = [];

        if (!empty($dbId)) {
            $dbConnection = $this->getDbConnectionDetails($dbId);

            switch (strtolower($type)) {
                case _TV_AREA:
                    // Get Area Tree Data
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getParentChild', ['Area', $parentId, $onDemand], $dbConnection);

                    break;
                case _TV_IU:
                    $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);
                    // get Subgroup Tree data
                    if ($parentId != '-1') {
                        $parentIds = explode(_DELEM1, $parentId);
                        if ($indicatorGidsAccessible === false || in_array($parentIds[0], $indicatorGidsAccessible)) {
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

                        $extra = ['type' => 'all', 'unique' => false, 'onDemand' => $onDemand, 'group'=>true];
                        if($indicatorGidsAccessible !== false && !empty($indicatorGidsAccessible)){
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
                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getParentChild', ['IndicatorClassifications', $parentId, $onDemand], $dbConnection);

                    break;
                case _TV_ICIND:

                    $returndData = $this->getICINDList($type, $dbConnection, $parentId, $onDemand);

                    break;
                case _TV_IND:

                    $returndData = $this->CommonInterface->serviceInterface('CommonInterface', 'getIndicatorList', ['Indicator'], $dbConnection);

                    break;
                case _TV_ICIUS:
                    // coming soon
                    break;
            }
        }

        $data = $this->convertDataToTVArray($type, $returndData, $onDemand, $dbId);

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

    public function convertDataToTVArray($type, $dataArray, $onDemand, $dbId) {
        $returnArray = array();
        //pr($dataArray);//die('nahiiiiiiii');
        $i = 0;
        foreach ($dataArray as $dt) {

            $caseData = $this->convertDataToTVArrayCase($type, $dt);

            if (isset($caseData['returnData'])) {
                $caseData['returnData']['dbId'] = $dbId;
                $caseData['returnData']['type'] = $type;
                $caseData['returnData']['onDemand'] = $onDemand;
            }

            $returnArray[$i]['id'] = $caseData['rowid'];
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

    function convertDataToTVArrayCase($type, $data) {
        $retData = $fields = $returnData = array();
        $rowid = '';

        switch (strtolower($type)) {
            case _TV_AREA:
                $rowid = $data['id'];
                $fields = array('aname' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                break;
            case _TV_IU:
                // Subgroup List
                if (array_key_exists(_IUS_IUSNID, $data)) {
                    $rowid = $data['iusGid'];
                    $fields = array('sName' => $data['sName']);
                    $returnData = array('iusGid' => $data['iusGid'], _IUS_IUSNID => $data[_IUS_IUSNID]);
                }// IU List
                else {
                    $rowid = $data['iGid'] . _DELEM1 . $data['uGid'];
                    $fields = array('iName' => $data['iName'], 'uName' => $data['uName']);
                    //$returnData = array('pnid' => $data['iGid'] . '{~}' . $data['uGid'], 'iGid' => $data['iGid'], 'uGid' => $data['uGid']);
                    $returnData = array('pnid' => $data['iGid'] . _DELEM1 . $data['uGid']);
                }
                break;
            case _TV_IU_S:
                $rowid = $data['sGid'];
                $fields = array('sName' => $data['sName']);
                $returnData = array('sGid' => $data['sGid'], _IUS_IUSNID => $data[_IUS_IUSNID]);
                break;
            case _TV_IUS:
                // coming soon
                break;
            case _TV_IC:
                $rowid = $data['id'];
                $fields = array('icName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                break;
            case _TV_ICIND:

                $rowid = $data['id'];
                $fields = array('icName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);

                break;
            case _TV_IND:

                $rowid = $data['id'];
                $fields = array('iName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);

                break;
            case _TV_ICIUS:
                // coming soon
                break;
        }

        return array('rowid' => $rowid, 'fields' => $fields, 'returnData' => $returnData);
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
                    
                    // Update Case
                    if (!empty($validationExist)) {
                        $conditions = [_MIUSVALIDATION_ID => $validationExist[_MIUSVALIDATION_ID]];
                        $updateArray = [
                            _MIUSVALIDATION_IS_TEXTUAL => ($extra['isTextual'] === true || $extra['isTextual'] == 'true') ? 1 : 0,
                            _MIUSVALIDATION_MIN_VALUE => (isset($extra['minimumValue'])) ? $extra['minimumValue'] : null,
                            _MIUSVALIDATION_MAX_VALUE => (isset($extra['maximumValue'])) ? $extra['maximumValue'] : null,
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
                            _MIUSVALIDATION_IS_TEXTUAL => ($extra['isTextual'] === true || $extra['isTextual'] == 'true') ? 1 : 0,
                            _MIUSVALIDATION_MIN_VALUE => (isset($extra['minimumValue'])) ? $extra['minimumValue'] : null,
                            _MIUSVALIDATION_MAX_VALUE => (isset($extra['maximumValue'])) ? $extra['maximumValue'] : null,
                            _MIUSVALIDATION_CREATEDBY => $this->Auth->user('id')
                        ];
                    }
                }
            }

            // insert bulk
            if(isset($MIusValidationsInsert) && count($MIusValidationsInsert) > 0) {
                $insertDataKeys = [
                    _MIUSVALIDATION_DB_ID,
                    _MIUSVALIDATION_INDICATOR_GID,
                    _MIUSVALIDATION_UNIT_GID,
                    _MIUSVALIDATION_SUBGROUP_GID,
                    _MIUSVALIDATION_IS_TEXTUAL,
                    _MIUSVALIDATION_MIN_VALUE,
                    _MIUSVALIDATION_MAX_VALUE,
                    _MIUSVALIDATION_CREATEDBY,
                    _MIUSVALIDATION_MODIFIEDBY
                ];
                $this->MIusValidations->insertBulkData($MIusValidationsInsert, $insertDataKeys);
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
                    $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'deleteByParams', $params, $dbConnection);
                }
            }
            $status = true;

        }

        return $status;
    }


}
