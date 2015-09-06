<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * Template component
 */
class TemplateComponent extends Component {

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $components = ['DevInfoInterface.CommonInterface', 
        'DevInfoInterface.SubgroupType',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Area',
        'DevInfoInterface.Indicator',
        'DevInfoInterface.Metadata','TransactionLogs','UserCommon',
        'Common'];

    public function initialize(array $config) {
        parent::initialize($config);
    }

    /**
     * Add map for AREA/GROUP
     * 
     * @param array $files Uploaded file details
     * @param array $data Input params 
     * @param array $type Area/Group
     * @param string $dbConnection DB connection details
     * @return void
     */
    public function addMap($files, $data, $type, $dbConnection, $connect = true) {
        $aNid = isset($data['aNid']) ? $data['aNid'] : null;
        $mapName = isset($data['mapName']) ? $data['mapName'] : null;
        $startDate = isset($data['startDate']) ? $data['startDate'] : null;
        $endDate = isset($data['endDate']) ? $data['endDate'] : null;

        // Optionals
        $sibling = isset($data['sibling']) ? $data['sibling'] : null;
        //$siblingOption = isset($data['siblingOption']) ? $data['siblingOption'] : null;
        $siblingOptionLevel = isset($data['siblingOptionLevel']) ? $data['siblingOptionLevel'] : null;
        $siblingOptionArea = isset($data['siblingOptionArea']) ? $data['siblingOptionArea'] : null;
        $split = isset($data['split']) ? $data['split'] : null;
        $assocCompMap = isset($data['assocCompMap']) ? $data['assocCompMap'] : null;
        $layerNid = isset($data['mapNid']) ? $data['mapNid'] : null;

        if (empty($aNid) || empty($type) || empty($mapName) || empty($startDate) || empty($endDate)) {
            return ['error' => _INVALID_INPUT];
        } else {
            
            if(empty($layerNid)) {
                if (empty($files)) return ['error' => _INVALID_INPUT];
            }
            
            if ($connect === false) $dbConnection = '';
            if (!empty($files)) {
                //-- UPLOAD FILE
                $allowedExtensions = ['zip', 'zip2'];

                $dbDetails = json_decode($dbConnection, true);
                $extraParam['dbName'] = $dbDetails['db_connection_name'];
                $extraParam['subModule'] = _MODULE_NAME_MAP;
                $extraParam['dest'] = _MAPS_PATH;

                $filePaths = $this->Common->processFileUpload($files, $allowedExtensions, $extraParam);
                
            }// in case of edit when zip is not uploaded
            else {
                $filePaths[0] = '';
            }
            
            if (!empty($filePaths)) {
                // prepare inputs to be send like $type
                $inputs = [
                    'aNid' => $aNid,
                    'filename' => $filePaths[0],
                    'mapName' => $mapName,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'sibling' => $sibling,
                    //'siblingOption' => $siblingOption,
                    'siblingOptionLevel' => $siblingOptionLevel,
                    'siblingOptionArea' => $siblingOptionArea,
                    'split' => $split,
                    'assocCompMap' => $assocCompMap,
                    'layerNid' => $layerNid,
                ];

                $params = ['inputs' => $inputs];

                switch ($type) {
                    // area
                    case _MAP_TYPE_AREA:
                        $return = $this->CommonInterface->serviceInterface('Area', 'areaMap', $params, $dbConnection);
                        break;

                    // group
                    case _MAP_TYPE_GROUP:
                        $return = $this->CommonInterface->serviceInterface('Area', 'groupMap', $params, $dbConnection);
                        break;
                }
            } else {
                return ['error' => _ERR163];
            }
        }
        
        return $return;
    }

    /**
     * Add/Modify Group
     * 
     * @param array $files Uploaded file details
     * @param array $data Input params 
     * @param string $dbConnection DB connection details
     * @return void
     */
    public function addModifyGroup($files, $data, $dbConnection) {
        $result = '';

        $fieldsArray[_AREA_AREA_NAME] = isset($data['aName']) ? $data['aName'] : '';
        $fieldsArray[_AREA_AREA_ID] = isset($data['aId']) ? $data['aId'] : '';
        $fieldsArray[_AREA_AREA_NID] = isset($data['aNid']) ? $data['aNid'] : null;
        $fieldsArray[_AREA_AREA_BLOCK] = isset($data['blockNids']) ? $data['blockNids'] : null;

        if (isset($data['pnid']))
            $fieldsArray[_AREA_PARENT_NId] = $data['pnid'];

        if (empty($fieldsArray[_AREA_AREA_NAME]) || empty($fieldsArray[_AREA_AREA_ID]) || empty($fieldsArray[_AREA_AREA_BLOCK])) {
            return ['error' => _INVALID_INPUT];
        } else {
            $params = ['fieldsArray' => $fieldsArray];
            $result = $this->CommonInterface->serviceInterface('Area', 'saveAndGetAreaNid', $params, $dbConnection);

            if (isset($result['error'])) {
                return ['error' => $result['error']];
            } else if ($result === false) {
                return ['error' => false];
            }

            $data['aNid'] = $result;
            if (isset($data['mapName']) && !empty($data['mapName'])) {
                $result = $this->addMap($files, $data, _MAP_TYPE_GROUP, $dbConnection, $connect = false);
            }
        }
        return $result;
    }

    /**
     * Get Area parent Details
     * 
     * @param string $nid Area NId
     * @param string $fromLevel Level to start
     * @param string $toLevel Till the level
     * @param string $dbConnection DB connection details
     * @return void
     */
    public function getAreaAncestors($nid, $fromLevel, $toLevel, $dbConnection, $pnid = '') {
        $return = false;

        if (empty($pnid)) {
            $params['fields'] = [_AREA_PARENT_NId];
            $params['conditions'] = [_AREA_AREA_NID => $nid];
            $params['type'] = 'all';
            $params['extra'] = ['first' => true];
            $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);
            $dbConnection = '';
            if (!empty($result)) {
                $pnid = $result[_AREA_PARENT_NId];
            } else {
                return false;
            }
        }

        if ($fromLevel > $toLevel) {

            for ($i = $fromLevel - 1; $i >= $toLevel; $i--) {
                $params = [
                    'fields' => ['aNid' => _AREA_AREA_NID, 'aId' => _AREA_AREA_ID, 'aName' => _AREA_AREA_NAME, 'aLevel' => _AREA_AREA_LEVEL, 'pnid' => _AREA_PARENT_NId],
                    'conditions' => [_AREA_AREA_NID => $pnid, _AREA_AREA_LEVEL => $i],
                    'type' => 'all',
                    'extra' => ['first' => true],
                ];
                $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);

                if (!empty($result)) {
                    $return[] = $result;
                    $pnid = $result['pnid'];
                }
                $dbConnection = '';
            }
        }

        return $return;
    }

    /**
     * Add map for AREA/GROUP
     * 
     * @param array $inputs Input params 
     * @param array $dbConnection DB connection details
     * @return void
     */
    public function getAreaDetails($aNid, $dbConnection) {
        $return = $parentLevels = [];

        if (empty($aNid)) {
            return ['error' => _INVALID_INPUT];
        } else {
            //$params['fields'] = ['aNid' => _AREA_AREA_NID, 'aId' => _AREA_AREA_ID, 'aName' => _AREA_AREA_NAME];
            $params['fields'] = [_AREA_AREA_NID, _AREA_AREA_ID, _AREA_AREA_NAME, _AREA_AREA_LEVEL, _AREA_PARENT_NId];
            $params['conditions'] = [_AREA_AREA_NID => $aNid];
            $params['type'] = 'all';
            $params['extra'] = ['first' => true];
            $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);
            if (!empty($result)) {

                if ($result[_AREA_AREA_LEVEL] > 1) {
                    $parentLevels = $this->getAreaAncestors($aNid, $result[_AREA_AREA_LEVEL], 1, '', $result[_AREA_PARENT_NId]);
                }

                $return = [
                    'aNid' => $result[_AREA_AREA_NID],
                    'aId' => $result[_AREA_AREA_ID],
                    'aName' => $result[_AREA_AREA_NAME],
                    'aLevel' => $result[_AREA_AREA_LEVEL],
                    'ancestors' => $parentLevels,
                ];
            }
        }

        return $return;
    }

    /**
     * change the subgroup names as per requested order 
     * 
     * @param array $orderData the data which needs to be renamed   
     * @param array $dbId DB connection details
     * @dbConnection the data base connection details 
     * @return void
     */
    public function changeSubgroupDimOrder($orderData, $dbConnection, $dbId) {

        if (isset($orderData) && !empty($orderData) && !empty($dbConnection) && !empty($dbId)) {
            $params['orderData'] = $orderData;
            $result = $this->CommonInterface->serviceInterface('SubgroupType', 'manageSubgroupTypeOrder', $params, $dbConnection);
            if (!empty($result)) {
                if ($result['status'] == true) {
                    if (isset($result['resultset']) && !empty($result['resultset'])) {
                        $diffdata = $result['resultset'];
                        $this->Common->createDFAMJ($diffdata, $dbId); //store values in job
                    }

                    return true;
                }
            }
        } else {
            return ['error' => _ERR135];
        }
    }
    
    
    public function getIcDetails($fieldsArray, $extra, $dbConnection) {
        
        $result = $iusList = $iuList = [];
        
        $fields = ['icNid' => _IC_IC_NID, 'icGid' => _IC_IC_GID, 'icName' => _IC_IC_NAME];
        $conditions = [_IC_IC_NID => $fieldsArray['icNid']];

        $params['fields'] = $fields;
        $params['conditions'] = $conditions;
        $params['type'] = 'all';
        $params['extra'] = ['first' => true];
        $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getRecords', $params, $dbConnection);
        
        if(!empty($result)) {
            if(isset($result['error'])) return $result;
            
            $icNid = $result['icNid'];
            $iusList = $this->CommonInterface->serviceInterface('IcIus', 'getRecords', [['id' => _ICIUS_IUSNID], [_ICIUS_IC_NID => $icNid], 'all'], $dbConnection = '');
            if(isset($iusList['error'])) return $iusList;
            $iusList = array_map(function(&$val){
                $return['id'] = (int)$val['id'];
                return $val = $return;
            }, $iusList);
            
            $IUSes = array_column($iusList, 'id');
            $IUNids = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getRecords', [[_IUS_INDICATOR_NID, _IUS_UNIT_NID], [_IUS_IUSNID . ' IN' => $IUSes], 'all'], $dbConnection = '');
            $IUNidsUnique = array_intersect_key($IUNids, array_unique(array_map('serialize', $IUNids)));
            $iuList = array_map(function(&$val){
                return implode(_DELEM1, $val);
            }, $IUNidsUnique);
            
        } else {
            $result = [];
        }
        
        return ['ic' => $result, 'ius' => array_values($iusList), 'iu' => array_values($iuList)];
    }
    
    public function getIuGidsFromNids($iusNids) {
        
    }

}
