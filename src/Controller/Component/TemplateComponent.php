<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * Template component
 */
class TemplateComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $components = ['DevInfoInterface.CommonInterface', 'Common'];

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
    public function addMap($files, $data, $type, $dbConnection)
    {
        
        $data['sibling'] = true;
        $data['siblingOption'] = 'all';

        $aNid = isset($data['aNid']) ? $data['aNid'] : null;
        $mapName = isset($data['mapName']) ? $data['mapName'] : null;
        $startDate = isset($data['startDate']) ? $data['startDate'] : null;
        $endDate = isset($data['endDate']) ? $data['endDate'] : null;

        // Optionals
        $sibling = isset($data['sibling']) ? $data['sibling'] : null;
        $siblingOption = isset($data['siblingOption']) ? $data['siblingOption'] : null;
        $split = isset($data['split']) ? $data['split'] : null;
        $assocCompMap = isset($data['assocCompMap']) ? $data['assocCompMap'] : null;
        
        if(empty($aNid) || empty($type) || empty($mapName) || empty($startDate) || empty($endDate)) {
            return ['error' => _INVALID_INPUT];
        } else {
            //-- UPLOAD FILE
            $allowedExtensions = ['zip', 'zip2'];

            $dbDetails = json_decode($dbConnection, true);
            $extraParam['dbName'] = $dbDetails['db_connection_name'];
            $extraParam['subModule'] = _MODULE_NAME_MAP;
            $extraParam['dest'] = _MAPS_PATH;

            $filePaths = $this->Common->processFileUpload($files, $allowedExtensions, $extraParam);

            if(!empty($filePaths)) {
                // prepare inputs to be send like $type
                $inputs = [
                    'aNid' => $aNid,
                    'filename' => $filePaths[0],
                    'mapName' => $mapName,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'sibling' => $sibling,
                    'siblingOption' => $siblingOption,
                    'split' => $split,
                    'assocCompMap' => $assocCompMap,
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
    }
    
    /**
     * Add/Modify Group
     * 
     * @param array $files Uploaded file details
     * @param array $data Input params 
     * @param string $dbConnection DB connection details
     * @return void
     */
    public function addModifyGroup($files, $data, $dbConnection)
    {
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
            return $this->addMap($files, $data, _MAP_TYPE_GROUP, $dbConnection);
        }
        
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
    public function getAreaAncestors($nid, $fromLevel, $toLevel, $dbConnection, $pnid = '')
    {
        $return = false;
        
        if(empty($pnid)) {
            $params['fields'] = [_AREA_PARENT_NId];
            $params['conditions'] = [_AREA_AREA_NID => $nid];
            $params['type'] = 'all';
            $params['extra'] = ['first' => true];
            $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);
            if(!empty($result)) {
                $pnid = $result[_AREA_PARENT_NId];
            } else {
                return false;
            }
        }
        
        if($fromLevel > $toLevel) {
            
            for ($i = $fromLevel - 1; $i >= $toLevel; $i--) {
                $params = [
                    'fields' => ['aNid' => _AREA_AREA_NID, 'aId' => _AREA_AREA_ID, 'aName' => _AREA_AREA_NAME, 'aLevel' => _AREA_AREA_LEVEL, 'pnid' => _AREA_PARENT_NId],
                    'conditions' => [_AREA_AREA_NID => $pnid, _AREA_AREA_LEVEL => $i],
                    'type' => 'all',
                    'extra' => ['first' => true],
                ];
                $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);
                
                if(!empty($result)) {
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
    public function getAreaDetails($aNid, $dbConnection)
    {
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
            if(!empty($result)) {
                
                if($result[_AREA_AREA_LEVEL] > 1) {
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
}