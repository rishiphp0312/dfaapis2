<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Configure;
use Cake\View\View;

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
        if (preg_match('/^[0-9a-zA-Z\$@\_\-]+$/', $gid) === 0) {
            return false;  // not valid 
        } else {
            return true; // when its valid 
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
        return ['error' => _ERROR_LOCATION_INACCESSIBLE];
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

    public function getTreeViewJSON($type = _TV_AREA, $dbId = null, $parentId = -1, $onDemand = true, $idVal = '', $icType = '', $showGroup = false) {
        $returndData = $extra = [];

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
                        $fields = [_IUS_IUSNID, _IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID];
                        //$fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID];
                        $conditions = [];

                        $extra = ['type' => 'all', 'unique' => false, 'onDemand' => $onDemand, 'group' => true, 'dontConcat' => true];
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
                    $extra['icType'] = $icType;
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


        $data = $this->convertDataToTVArray($type, $returndData, $onDemand, $dbId, $idVal, $showGroup, $extra);

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

    public function convertDataToTVArray($type, $dataArray, $onDemand, $dbId, $idVal = '', $showGroup = false, $extra = []) {
        $returnArray = array();
        $i = 0;
        foreach ($dataArray as $dt) {

            $caseData = $this->convertDataToTVArrayCase($type, $dt, $idVal, $showGroup, $extra);

            if (isset($caseData['returnData']) && $onDemand == true) {
                $caseData['returnData']['dbId'] = $dbId;
                $caseData['returnData']['type'] = $type;
                $caseData['returnData']['onDemand'] = $onDemand;
                $caseData['returnData']['showGroup'] = $showGroup;
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

    function convertDataToTVArrayCase($type, $data, $idVal = '', $showGroup = false, $extra = []) {
        $retData = $fields = $returnData = array();
        $rowid = $uid = '';

        switch (strtolower($type)) {
            case _TV_AREA:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                if ($showGroup == 'true' && isset($data['block']) && !empty($data['block'])) {
                    // group handling
                    $fields = array('gname' => $data['name']);
                } else {
                    $fields = array('aname' => $data['name']);
                }
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;
                $returnData['level'] = $data['areaLvl'];

                break;
            case _TV_IU:
                // Subgroup List
                if (array_key_exists(_IUS_IUSNID, $data)) {
                    $rowid = (strtolower($idVal) == 'nid') ? $data['IUSNId'] : $data['iusGid'];
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
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;

                break;
            case _TV_IU_S:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['sGid'];
                $uid = (strtolower($idVal) == 'nid') ? $data['sGid'] : $data['nid'];

                $fields = array('sName' => $data['sName']);
                $returnData = array('sGid' => $data['sGid'], _IUS_IUSNID => $data[_IUS_IUSNID]);
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;

                break;
            case _TV_IUS:
                // coming soon
                break;
            case _TV_IC:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('icName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;

                if (isset($extra['icType']))
                    $returnData['icType'] = $extra['icType'];
                break;
            case _TV_ICIND:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('icName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;

                break;
            case _TV_IND:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('iName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;

                break;
            case _TV_UNIT:
                $rowid = (strtolower($idVal) == 'nid') ? $data['nid'] : $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? $data['id'] : $data['nid'];

                $fields = array('uName' => $data['name']);
                $returnData = array('pnid' => $data['nid'], 'pid' => $data['id']);
                if (!empty($idVal))
                    $returnData['idVal'] = $idVal;

                break;
            case _TV_ICIUS:
                // coming soon
                break;
            case _TV_TP:
                $rowid = (strtolower($idVal) == 'nid') ? (int) $data['nid'] : (int) $data['id'];
                $uid = (strtolower($idVal) == 'nid') ? (int) $data['id'] : (int) $data['nid'];

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
                        
                        if(!empty($minimumValue) && !empty($maximumValue) && $minimumValue > $maximumValue) {
                            return ['error' => _ERR185];
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
        if (!empty($source))
            $conditions[_MDATA_SOURCENID . ' IN '] = $source;
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
                if (!empty($dt[_MDATA_NID])) {
                    $iusGid = '';
                    if (isset($iusGids[$dt[_MDATA_IUSNID]])) {
                        $iusGid = $iusGids[$dt[_MDATA_IUSNID]]['indicator_gid'] . _DELEM1 . $iusGids[$dt[_MDATA_IUSNID]]['unit_gid'] . _DELEM1 . $iusGids[$dt[_MDATA_IUSNID]]['subgroup_gid'];
                    }

                    $dv = ($dt[_MDATA_ISTEXT_DATA]) ? $dt[_MDATA_DATA_TEXTUALDATAVALUE] : $dt[_MDATA_DATAVALUE] ;
                    
                    $iusData[] = [
                        'dNid' => $dt[_MDATA_NID],
                        'iusNid' => $dt[_MDATA_IUSNID],
                        'iusGid' => $iusGid,
                        'tpNid' => $dt[_MDATA_TIMEPERIODNID],
                        'srcNid' => $dt[_MDATA_SOURCENID],
                        'aNid' => $dt[_MDATA_AREANID],
                        'footnote' => (isset($footnotes[$dt[_MDATA_FOOTNOTENID]])) ? $footnotes[$dt[_MDATA_FOOTNOTENID]] : '',
                        'dv' => $dv
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

                $returnData[$iusNId] = $validationsArray;
            }
        }
        return $returnData;
    }

    /*
      function to get html data format for log file
      $params array
     */

    public function getHtmlData($params = []) {
        $data = isset($params['data']) ? $params['data'] : '';
        $errMsgArr = (isset($data['log']) && !empty($data['log'])) ? $data['log'] : 0;

        // Grabbing View Data
        $view = new View($this->request, $this->response, null);
        $view->set('data', $data);
        $view->set('errMsgArr', $errMsgArr);
        $view->viewPath = 'Logs'; // Directory inside view directory to search for .ctp files
        $view->layout = false; //$view->layout='ajax'; // layout to use or false to disable
        $html = $view->render('de_custom_log');

        return $html;
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
     * method to post the data using curl and save into job db
     * @requestedData posted data by service 
     */

    public function executecurl($requestedData) {

        $ch = curl_init(_JOB_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestedData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        $resp = curl_exec($ch);
        curl_close($ch);
    }

    /*
     * 
     * method to save the entry in jobs database
     * @dbId is the database Id
     * @diffDataOrder is the order with subgroup dimension  nids 
     */

    public function createDFAMJ($diffDataOrder = '', $dbId) {
        $webpath = _WEBSITE_URL;
        $job_parameters = ['dbId' => $dbId, 'diffDataOrder' => $diffDataOrder];
        $jobservice = 'services/serviceQuery/2424';
        $postdata = [];
        $postdata['job_shell_path'] = 'job_shell_path';
        $postdata['name'] = 'Subgroup Re-Order';
        $postdata['job_parameters'] = json_encode($job_parameters);
        $postdata['job_web_path'] = $webpath;
        $postdata['job_service'] = $jobservice;
        $str = http_build_query($postdata);
        $this->executecurl($str);
    }

    /*
      function to get Counts from data base
     */

    public function getDatabaseCounts($countType) {
        $countArray = '';
        switch (strtolower($countType)) {
            case _TV_AREA:
                $count = $this->CommonInterface->serviceInterface('Area', 'getAreasCount', [], '');
                $countArray = ['AreaCount' => $count];
                break;
        }

        return $countArray;
    }
    
    /*
     * method to validate activation details 
     * @data posted data 
     */
    public function validateLink($data) {
        $actkey = (isset($data['key'])) ? $data['key'] : '';
        if (empty($actkey)) {
            return ['error' => _ERR115]; //checks key is empty or not
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

        $activationStatus = $this->checkActivationLink($userId);
        if ($activationStatus == 0)
            return ['error' => _ERR104];            //  Activation link already used 

        if (!isset($data['password']) || empty($data['password'])) {
            return ['error' => _ERR113];             // Empty password   
        }
    }

    /*
     * 
     * method to update password on activation link
     * @data posted info 
     */
    public function accountActivation($data = []){

        $validate = $this->validateLink($data);
       
        if(isset($validate['error'])){
            return ['error'=>$validate['error']];
        }
       
        $actkey = $data['key'];
        $requestdata = array();
        $encodedstring = trim($actkey);
        $decodedstring = base64_decode($encodedstring);
        $explodestring = explode(_DELEM3, $decodedstring);        
        $requestdata[_USER_MODIFIEDBY] = $requestdata[_USER_ID] = $userId = $explodestring[1];
        $password = $requestdata[_USER_PASSWORD] = trim($data['password']);
        $requestdata[_USER_STATUS] = _ACTIVE; // Activate user 
        $returndata = $this->UserCommon->updatePassword($requestdata);
        if ($returndata > 0) {
            $returnData['status'] = _SUCCESS;
        } else {
            $returnData['error'] = _ERR100;      // password not updated due to server error   
        }
    
    }

    
    /**
     * Create cron job
     * 
     * @param string $name call identifier
     * @param string $param cron return parameters
     * @param string $dbId current DB Id
     * @param string $serviceNo service number to be called by cron
     */
    public function createCronJob($name, $param, $dbId = null, $serviceNo = null) {
        
        $postdata = $job_parameters = [];
        $webpath = _WEBSITE_URL;
        
        if(!empty($dbId))
            $job_parameters['dbId'] = $dbId;
        
        if(!empty($serviceNo))
            $postdata['job_service'] = 'services/serviceQuery/' . $serviceNo;
        
        $job_parameters['param'] = $param;
        
        $postdata['name'] = $name;
        $postdata['job_shell_path'] = 'job_shell_path';
        $postdata['job_parameters'] = json_encode($job_parameters);
        $postdata['job_web_path'] = $webpath;
        
        $str = http_build_query($postdata);
        $this->executecurl($str);
    }
    
    /**
     * Get System Configuration
     * 
     * @param string $configKey Configuration key name
     * @return array config key list
     */
    public function getSystemConfig($configKey = null) {
        
        $conditions = [];
        
        // Return requested config
        if(!empty($configKey))
            $conditions = [_SYSCONFIG_KEY_NAME => $configKey];
        
        return $this->MSystemConfirgurations->getRecords([_SYSCONFIG_KEY_NAME, _SYSCONFIG_KEY_VALUE], $conditions, 'list');
    }
    
    /**
     * Save System Configuration
     * 
     * @param array $fieldsArray Row Fields array
     * @return boolean true/false
     */
    public function saveSystemConfig($fieldsArray) {
        try {
            foreach($fieldsArray as $keyName => $keyvalue) {
                $this->MSystemConfirgurations->updateRecords([_SYSCONFIG_KEY_VALUE => $keyvalue], [_SYSCONFIG_KEY_NAME => $keyName]);
            }
        } catch (Exception $exc) {
            return ['errMsg' => $exc->getMessage()];
        }
        
        return true;
    }

}
