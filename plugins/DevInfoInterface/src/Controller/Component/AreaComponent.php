<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

/**
 * Area Component
 */
class AreaComponent extends Component {

    // The other component your component uses
    public $components = ['Auth', 'Common', 'DevInfoInterface.CommonInterface'];
    public $AreaObj = NULL;
    public $AreaLevelObj = NULL;
    public $AreaMapObj = NULL;
    public $AreaMapLayerObj = NULL;
    public $AreaMapMetadataObj = NULL;    
    public $AreaFeatureTypeObj = NULL;


    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->AreaObj = TableRegistry::get('DevInfoInterface.Areas');
        $this->AreaLevelObj = TableRegistry::get('DevInfoInterface.AreaLevel');
        $this->AreaMapObj = TableRegistry::get('DevInfoInterface.AreaMap');
        $this->AreaMapLayerObj = TableRegistry::get('DevInfoInterface.AreaMapLayer');
        $this->AreaMapMetadataObj = TableRegistry::get('DevInfoInterface.AreaMapMetadata');
        $this->AreaFeatureTypeObj = TableRegistry::get('DevInfoInterface.AreaFeatureType');
        $this->AreaMapMetadataObj = TableRegistry::get('DevInfoInterface.AreaMapMetadata');
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
    }

    /**
     * getRecords method for Areas
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->AreaObj->getRecords($fields, $conditions, $type, $extra);
    }
    
    /**
     * Get Area Details from AreaIds
     * 
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param array $areaIds Areaa Ids Array
     * @param string $type Query type
     * @return void
     */
    public function getNidsFromIds($fields = [], array $AreaIds, $type = 'all')
    {
        // MSSQL Compatibilty - MSSQL can't support more than 2100 params - 900 to be safe
        $chunkSize = 900;

        if (isset($AreaIds) && count($AreaIds, true) > $chunkSize) {

            $result = [];
            $countIncludingChildparams = count($AreaIds, true);

            // count for single index
            $splitChunkSize = floor(count($AreaIds) / ($countIncludingChildparams / $chunkSize));

            // MSSQL Compatibilty - MSSQL can't support more than 2100 params
            $orConditionsChunked = array_chunk($AreaIds, $splitChunkSize);

            foreach ($orConditionsChunked as $orCond) {
                $conditions[_AREA_AREA_ID . ' IN'] = $orCond;
                $getArea = $this->AreaObj->getRecords($fields, $conditions, $type);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if ($type == 'list') {
                    $result = array_replace($result, $getArea);
                }// we dont need to preserve keys, just merge
                else {
                    $result = array_merge($result, $getArea);
                }
            }
        } else {
            $conditions[_AREA_AREA_ID . ' IN'] = $AreaIds;
            $result = $this->AreaObj->getRecords($fields, $conditions, $type);
        }
        return $result;
    }

    /**
     * exportArea method for exporting area details 
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function exportArea($fields, $conditions, $module = 'Area') {

        $dbId      = $this->request->query['dbId'];
        $dbDetails = $this->Common->parseDBDetailsJSONtoArray($dbId);
        $dbConnName  = $dbDetails['db_connection_name'];
        //$dbConnName = $this->session->read('dbName');

        $dbConnName = str_replace(' ', '-', $dbConnName);
        $authUserId = $this->Auth->User('id');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();


        $returnFilename = _MODULE_NAME_AREA . '_' . $dbConnName . '_' . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = str_replace(' ', '-', $returnFilename);
        $rowCount = 1;
        $firstRow = ['A' => 'AreaId', 'B' => 'AreaName', 'C' => 'AreaLevel', 'D' => 'AreaGId', 'E' => 'Parent AreaId'];
        $objPHPExcel->getActiveSheet()->getStyle("A1:G1")->getFont()->setItalic(true);
        foreach ($firstRow as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }

        //$conditions=['1'=>'1'];
        $conditions = [];
        $areadData = $this->AreaObj->getRecords($fields, $conditions, 'all');

        $startRow = 2;
        $width = 30;
        foreach ($areadData as $index => $value) {

            $newconditions = [_AREA_AREA_NID => $value[_AREA_PARENT_NId]];
            $newfields = [_AREA_AREA_ID];
            $parentnid = $this->getRecords($newfields, $newconditions);
            if ($value[_AREA_PARENT_NId] != '-1')   //case when not empty or -1
                $parentnid = current($parentnid)[_AREA_AREA_ID];
            else
                $parentnid = '';

            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_AREA_AREA_ID])) ? $value[_AREA_AREA_ID] : '' )->getColumnDimension('A')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_AREA_AREA_NAME])) ? $value[_AREA_AREA_NAME] : '')->getColumnDimension('B')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($value[_AREA_AREA_LEVEL])) ? $value[_AREA_AREA_LEVEL] : '')->getColumnDimension('C')->setWidth($width - 20);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($value[_AREA_AREA_GID])) ? $value[_AREA_AREA_GID] : '' )->getColumnDimension('D')->setWidth($width + 20);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $startRow, (isset($parentnid)) ? $parentnid : '' )->getColumnDimension('E')->setWidth($width + 5);
            $startRow++;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment;filename=' . $returnFilename);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * getRecords for Area Level method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getRecordsAreaLevel(array $fields, array $conditions, $type = 'all') {

        return $this->AreaLevelObj->getRecords($fields, $conditions, $type);
    }

    /**
     * Delete IC childs
     *
     * @param array $nid IC_NID
     * @return void
     */
    public function deleteAreaChilds($nid) {
        $childs = $this->CommonInterface->getParentChild('Area', $nid, $onDemand = true, $extra = []);
        foreach($childs as $child) {
            $this->deleteRecords([_IC_IC_NID => $child['nid']]);
        }
    }

    /**
     * deleteRecords method for Areas 
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        
        // $conditions must be an array else it will truncate whole table
        if(!is_array($conditions)) return false;
        
        // Get Area_Nid to delete Associated Records
        $results = $this->getRecords([_AREA_AREA_NID], $conditions, 'all');
        
        if(!empty($results)) {
            // Delete AREA
            $return = $this->AreaObj->deleteRecords($conditions);

            // Deleted Associated Records - from AreaMap, Area_map_layer, Area_map_metadata
            if($return) {
                
                foreach($results as $result) {                
                    //-- TRANSAC Log
                    $dbId = $this->session->read('dbId');
                    $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _AREA_TRANSAC, $result[_AREA_AREA_NID], _DONE, $LogId = null, $dbId);

                    // Delete AREA_MAP
                    $this->IcIus->deleteAreaAssociations($result[_AREA_AREA_NID]);

                    // Delete Childs
                    $this->deleteAreaChilds($result[_AREA_AREA_NID]);
                }
                
            } else {
                return false;
            }
        }
    }

    /**
     * deleteRecords method for Area Level 
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecordsAreaLevel($conditions = []) {

        return $this->AreaLevelObj->deleteRecords($conditions);
    }

    /**
     * insertData method for Area
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertUpdateAreaData($fieldsArray = []) {
        return $this->AreaObj->insertData($fieldsArray);
    }

    /**
     * insertData method for Area level
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertUpdateAreaLevel($fieldsArray = []) {
        return $this->AreaLevelObj->insertData($fieldsArray);
    }

    /**
     * Insert multiple rows at once
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->AreaObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * Insert multiple rows at once - Area level
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkDataAreaLevel($dataArray = []) {
        return $this->AreaLevelObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateRecords method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->AreaObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * updateRecords method for Area level
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateRecordsAreaLevel($fieldsArray = [], $conditions = []) {
        return $this->AreaLevelObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * find method 
     *
     * @param string $type Query Type
     * @param array $options Extra options
     * @return void
     */
    public function find($type, $options = [], $extra = null) {
        $query = $this->AreaObj->find($type, $options);
        if (isset($extra['count'])) {
            $data = $query->count();
        } else {
            $results = $query->hydrate(false)->all();
            $data = $results->toArray();
        }
        return $data;
    }	
	
    /**
     * method  returns array of area details  as per passed conditions 
     * @inputAreaids array  all area ids of excel  
     * @type  is by default all else list 
     * used in import 
     */

    public function getAreaDetails($inputAreaids = null, $type = 'all') {

        $fields = [_AREA_AREA_NID, _AREA_AREA_ID, _AREA_AREA_GID];// dnt change the order of fields 
        $conditions = array();
        $conditions = [_AREA_AREA_ID . ' IN ' => $inputAreaids];
        return $areaDetails = $this->getRecords($fields, $conditions, $type);
    }
	
    /**
     *  method  returns array list of gids with index of area nid  
     *  @type  is list 
     * 	@return list  
     */

    public function getAreaGIDSlist($inputAreaids = null, $type = 'all') {
        $fields = [_AREA_AREA_NID, _AREA_AREA_GID];
		$conditions = array();
		if(!empty($inputAreaids))
        $conditions = [_AREA_AREA_ID . ' IN ' => $inputAreaids];
        return $areaGidList = $this->getRecords($fields, $conditions, $type);
    }
	
    /**
     *  checkGidExist method to check gid already exist in db or not 
     * return boolean
     */
    public function checkGidExist($gid='',$aNid='',$type='all'){
       
        $fields = [_AREA_AREA_ID];
        $conditions = array();
        $conditions[_AREA_AREA_GID]=$gid;
        if($aNid!='')
        $conditions[_AREA_AREA_NID.' !='] = $aNid;        
        $areaDetails = $this->getRecords($fields, $conditions, $type);       
        $areaId = current($areaDetails)[_AREA_AREA_ID];
        if(!empty($areaId))
        return 1;
        else
        return 0;
    }

    /*
      function to add area level if not exists and validations while import for level according to  parent id
      returns array of area level and any error if exists
      if $type is New that means parent id don't exist in db and have childs in excel sheet

     */

    public function returnAreaLevel($level = '', $parentNid = '') {
        $errorFlag = false;
        $areaFields = [_AREA_AREA_LEVEL];
        $levelFields = [_AREALEVEL_AREA_LEVEL];
        $data = [];
        $returnarray = array('level' => '', 'error' => $errorFlag);

        // case 1 when level is empty but parent nid is not  empty 
        if (empty($level) && !empty($parentNid) && $parentNid != _GLOBALPARENT_ID) {

            $areaConditions[_AREA_AREA_ID] = $parentNid;
            $levelValue = $this->AreaObj->getRecords($areaFields, $areaConditions, 'all');
            if (!empty($levelValue))
                $parentAreaLevel = current($levelValue)[_AREA_AREA_LEVEL] + 1;
            else
                $parentAreaLevel = _AREAPARENT_LEVEL; //1

            if ($parentAreaLevel) {
                $levelConditions[_AREALEVEL_AREA_LEVEL] = $parentAreaLevel;
                $getlevelDetails = $this->AreaLevelObj->getRecords($levelFields, $levelConditions, 'all');
                if (empty($getlevelDetails)) {
                    $data[_AREALEVEL_AREA_LEVEL] = $parentAreaLevel;
                    $data[_AREALEVEL_LEVEL_NAME] = _LevelName . $parentAreaLevel;
                    $this->AreaLevelObj->insertData($data);
                    return $returnarray = array('level' => $parentAreaLevel, 'error' => $errorFlag);
                } else {
                    $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL];
                    return $returnarray = array('level' => $finallevel, 'error' => $errorFlag);
                }

                unset($levelConditions);
                unset($areaConditions);
                unset($data);
            }
        }

        // case 2 when level  may be empty or not  but parent nid is empty or -1
        if ((!empty($level) || empty($level)) && (empty($parentNid) || $parentNid == _GLOBALPARENT_ID)) {

            if (!empty($level) && $level != _AREAPARENT_LEVEL) {
                $errorFlag = true;
            }

            $level = _AREAPARENT_LEVEL;
            $levelConditions[_AREALEVEL_AREA_LEVEL] = $level;
            $getlevelDetails = $this->AreaLevelObj->getRecords($levelFields, $levelConditions, 'all');

            if (empty($getlevelDetails)) {
                $data[_AREALEVEL_AREA_LEVEL] = $level;
                $data[_AREALEVEL_LEVEL_NAME] = _LevelName . $level;
                $this->AreaLevelObj->insertData($data);
                //return $level;
                $level = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL];
                return $returnarray = array('level' => $level, 'error' => $errorFlag);
            } else {
                return $returnarray = array('level' => $level, 'error' => $errorFlag);
            }

            unset($levelConditions);
            unset($areaConditions);
            unset($data);
        }

        // case 3 when both not empty 
        if (!empty($level) && !empty($parentNid) && $parentNid != _GLOBALPARENT_ID) {

            $areaConditions[_AREA_AREA_ID] = $parentNid;
            $parentAreaLevel = 0;
            $levelValue = $this->AreaObj->getRecords($areaFields, $areaConditions, 'all');
            $parentAreaLevel = current($levelValue)[_AREA_AREA_LEVEL];
            $finallevel = $parentAreaLevel + 1;
            if ($level != $finallevel) {
                $errorFlag = true;
            }

            $levelConditions[_AREALEVEL_AREA_LEVEL] = $finallevel;
            $getlevelDetails = $this->AreaLevelObj->getRecords($levelFields, $levelConditions, 'all');

            if (empty($getlevelDetails)) {
                $data[_AREALEVEL_AREA_LEVEL] = $finallevel;
                $data[_AREALEVEL_LEVEL_NAME] = _LevelName . $finallevel;
                $this->AreaLevelObj->insertData($data);
                return $returnarray = array('level' => $finallevel, 'error' => $errorFlag);
            } else {
                $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL];
                return $returnarray = array('level' => $finallevel, 'error' => $errorFlag);
            }

            // case when level >= parent level or level< parent level

            unset($levelConditions);
            unset($areaConditions);
            unset($data);
        }
    }

    function checkParentAreaId($parentAreaId = '') {
        $conditions = $fields = [];
        $conditions = [_AREA_AREA_ID => $parentAreaId];
        $fields = [_AREA_AREA_NID];
        return $parentchkAreaId = $this->getRecords($fields, $conditions);
    }

    function checkAreaId($areaId = '') {

        $conditions = [_AREA_AREA_ID => $areaId];
        $fields = [_AREA_AREA_ID, _AREA_AREA_NID, _AREA_AREA_GID];
        return $chkAreaId = $this->getRecords($fields, $conditions);
    }
    
    /**
     * Get Child Area Level - Insert if not exists
     * 
     * @param string $pnid Parent Level Nid
     * @param string $pid Parent Level AreaID
     * @return Integer/Boolean Int - Area level Count, Boolean - false
     */
    public function saveAndGetAreaLevel($pnid = '', $pid = '') {
        
        if(!empty($pnid) || !empty($pid)) {

            // Return Level 1 when parent_Nid is -1
            if($pnid == '-1') return 1;
            
            if(!empty($pnid))
                $conditions[_AREA_PARENT_NId] = $pnid;
            if(!empty($pid))
                $conditions[_AREA_AREA_ID] = $pid;

            $existingRecord = $this->getRecords([_AREA_AREA_LEVEL], $conditions, 'all', ['first' => true]);

            if(!empty($existingRecord)) {
                $newLevel = $existingRecord[_AREA_AREA_LEVEL] + 1;
                $existingLevel = $this->getRecordsAreaLevel([_AREALEVEL_LEVEL_NID], [_AREALEVEL_AREA_LEVEL => $newLevel]);
                
                // if exists - return Level
                if (!empty($existingLevel)) {
                    return $newLevel;
                } // if not-exists, INSERT and return Level
                else {
                    $data[_AREALEVEL_AREA_LEVEL] = $newLevel;
                    $data[_AREALEVEL_LEVEL_NAME] = _LevelName . $newLevel;
                    $areaLevelNid = $this->AreaLevelObj->insertData($data);
                    if($areaLevelNid) {
                        return $newLevel;
                    } // Creating Area level failed
                    else {
                        return ['error' => _ERR147];
                    }
                }                
            } //- Area Record does not exist
            else {
                return ['error' => _ERR148];
            }
        }
        
        return false;
    }
    
    /**
     * Insert/update Area and get Area Nid
     * 
     * @param array $fieldsArray Insert/update fields
     * @return Integer/boolean Int - AreaNid, Boolean - false
     */
    public function saveAndGetAreaNid($fieldsArray) {
        
        // UPDATE Case
        if(isset($fieldsArray[_AREA_AREA_NID]) && $fieldsArray[_AREA_AREA_NID] != null) {
            $conditions[_AREA_AREA_NID] = $fieldsArray[_AREA_AREA_NID];
        } // INSERT Case
        else {
            if(!isset($fieldsArray[_AREA_PARENT_NId]) || empty($fieldsArray[_AREA_PARENT_NId])) 
                $fieldsArray[_AREA_PARENT_NId] = -1;
            
            $conditions[_AREA_AREA_ID] = $fieldsArray[_AREA_AREA_ID];
            $conditions[_AREA_AREA_NAME] = $fieldsArray[_AREA_AREA_NAME];
            $conditions[_AREA_PARENT_NId] = $fieldsArray[_AREA_PARENT_NId];
        }
        
        // check if record exists
        $existingRecord = $this->getRecords([_AREA_AREA_NID], $conditions, 'all', ['first' => true]);
        
        // Exists - update and return Nid
        if(!empty($existingRecord)) {
            if(!isset($fieldsArray[_AREA_AREA_NID]) || empty($fieldsArray[_AREA_AREA_NID])) {
                return ['error' => _ERR156];
            }
        }// Not exists - INSERT and return Nid
        else {
            $areaLevel = $this->saveAndGetAreaLevel($fieldsArray[_AREA_PARENT_NId]);
            
            if(isset($areaLevel['error'])) return $areaLevel;
            
            $fieldsArray[_AREA_AREA_LEVEL] = $areaLevel;

            if(!isset($fieldsArray[_AREA_AREA_GID]))
                $fieldsArray[_AREA_AREA_GID] = $this->CommonInterface->guid();
            
            if(!isset($fieldsArray[_AREA_AREA_GLOBAL]))
                $fieldsArray[_AREA_AREA_GLOBAL] = 0;
        }
        
        $aNid = $this->insertUpdateAreaData($fieldsArray);
        
        if($aNid) //- Success
            return $aNid;
        else    //- Failed
            return false;//return ['error' => _ERR146];
    }

    /**
     * getRecords method for Areas Feature types
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getAreaFeatureTypes(array $fields, array $conditions, $type = 'all') {
        return $this->AreaFeatureTypeObj->getRecords($fields, $conditions, $type);
    }
    
    /**
     * delete Area maps
     *
     * @param array $aNid AREA_NID
     * @return void
     */
    public function deleteAreaAssociations($aNid) {
        $mapLayerId = $this->AreaMapObj->getRecords([_AREAMAP_LAYER_NID], [_AREAMAP_AREA_NID => $aNid], 'all');
        if(!empty($mapLayerId)) {
            $mapLayerIds = $this->AreaMapObj->getRecords([_AREAMAP_AREA_MAP_NID], [_AREAMAP_LAYER_NID => $mapLayerId[0][_AREAMAP_LAYER_NID]], 'all');
            if(count($mapLayerIds) == 1) {
                
                // Delete Area_map
                $this->AreaMapObj->deleteRecords([_AREAMAP_AREA_MAP_NID => $mapLayerIds[0][_AREAMAP_AREA_MAP_NID]]);
                //-- TRANSAC Log
                $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _AREAMAP_TRANSAC, $mapLayerIds[0][_AREAMAP_AREA_MAP_NID], _DONE, $LogId = null, $dbId);
                
                // Delete Area_map_layer
                $this->AreaMapLayerObj->deleteRecords([_AREAMAPLAYER_LAYER_NID => $mapLayerId[0][_AREAMAP_LAYER_NID]]);
                //-- TRANSAC Log
                $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _AREAMAPLAYER_TRANSAC, $mapLayerId[0][_AREAMAP_LAYER_NID], _DONE, $LogId = null, $dbId);
                
                // Delete Area_map_metadata
                $this->AreaMapMetadataObj->deleteRecords([_AREAMAPMETADATA_LAYER_NID => $mapLayerId[0][_AREAMAP_LAYER_NID]]);
                //-- TRANSAC Log
                $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _AREAMETADATA_TRANSAC, $mapLayerId[0][_AREAMAP_LAYER_NID], _DONE, $LogId = null, $dbId);
                
            }
        }
        
    }
    
    /**
     * get Shape type ID
     * 
     * @param array $shapeType Shape type
     * @return int/boolean Shape type ID
     */
    public function getshapeTypeId($shapeType) {
        $shapeTypes = [
            0 => 'featurepoint',
            1 => 'point',
            2 => 'featurepolyLine',
            3 => 'polyline',
            4 => 'eaturepolygon',
            5 => 'polygon',
        ];
        
        return array_search(strtolower($shapeType), $shapeTypes);
    }
    
    /**
     * Add area map
     * 
     * @param array $inputs Input params for adding area map
     * @return boolean true/false
     */
    public function areaMap($inputs) {
        
        // Validate map
        $shapeFiles = $this->validateMaps($inputs['filename']);
        if(isset($shapeFiles['error'])) return $shapeFiles;

        // add map to area
        return $this->addMap($inputs, $shapeFiles);
    }
    
    /**
     * Add group map
     * 
     * @param array $inputs Input params for adding group map
     * @return boolean true/false
     */
    public function groupMap($inputs) {
        
        // Validate map
        $shapeFiles = $this->validateMaps($inputs['filename']);
        if(isset($shapeFiles['error'])) return $shapeFiles;
        
        // add map to area
        return $this->addMap($inputs, $shapeFiles, $area = false);
        
    }
    
    /**
     * Add map
     * 
     * @param array $inputs Input params for adding group map
     * @return boolean true/false
     */
    public function addMap($inputs, $shapeFiles, $area = true) {
        
        $existingMap = $this->AreaMapMetadataObj->getRecords([_AREAMAPMETADATA_METADATA_NID], [_AREAMAPMETADATA_LAYER_NAME => $inputs['mapName']], 'all');
        
        if(!empty($existingMap)) return ['error' => _ERR162];
        
        // Read Shape File
        $shpData = $this->shapeFileReader($shapeFiles);
        
        if($shpData !== false) {
            // Add layer data
            $fieldsArray = [
                _AREAMAPLAYER_LAYER_SIZE => $shpData['shp']['size'],
                _AREAMAPLAYER_LAYER_SHP => $shpData['shp']['data'],
                _AREAMAPLAYER_LAYER_SHX => $shpData['shx'],
                _AREAMAPLAYER_LAYER_DBF => $shpData['dbf'],
                _AREAMAPLAYER_LAYER_TYPE => $shpData['shp']['type'],
                _AREAMAPLAYER_MINX => $shpData['shp']['xMin'],
                _AREAMAPLAYER_MINY => $shpData['shp']['yMin'],
                _AREAMAPLAYER_MAXX => $shpData['shp']['xMax'],
                _AREAMAPLAYER_MAXY => $shpData['shp']['yMax'],
                _AREAMAPLAYER_START_DATE => $inputs['startDate'],
                _AREAMAPLAYER_END_DATE => $inputs['endDate'],
            ];
            
            $areaMapLayerNId = $this->AreaMapLayerObj->insertData($fieldsArray);
            if($areaMapLayerNId) {
                // Adding Area Map
                $fieldsArray = [
                    _AREAMAP_AREA_NID => $inputs['aNid'],
                    _AREAMAP_FEATURE_TYPE_NID => '-1',
                    _AREAMAP_FEATURE_LAYER => 0,
                    _AREAMAP_LAYER_NID => $areaMapLayerNId,
                ];
                $this->AreaMapObj->insertData($fieldsArray);
                
                // adding MetaData
                $fieldsArray = [
                    _AREAMAPMETADATA_LAYER_NID => $areaMapLayerNId,
                    _AREAMAPMETADATA_METADATA_TEXT => '',
                    _AREAMAPMETADATA_LAYER_NAME => $inputs['mapName'],
                ];
                $this->AreaMapMetadataObj->insertData($fieldsArray);
            } else {
                return false;
            }
        } else {
            return false;
        }
        
        //-- "Add To Sibling" and "Split" is only allowed for Area
        if($area == true) {
            // Add to sibling
            if($inputs['sibling'] == true) {
                $this->addMapToSiblings($inputs, $areaMapLayerNId);
            }// Split
            else if($inputs['split'] == true) {
                $this->splitMap($inputs, $areaMapLayerNId);
            }
        }
        
        return true;
    }
    
    /**
     * Validate map
     * 
     * @param array $inputs Input params for adding group map
     * @return boolean true/false
     */
    public function validateMaps($filename) {
        // Initializing PHP ZipArchive class Object
        $zip = new \ZipArchive;
        
        // Check for Valid ZIP
        if ($zip->open($filename) === TRUE) {
            
            $destPath = _MAPS_PATH . DS . time();
            // Create folder if not exists
            if(!file_exists($destPath))
                mkdir($destPath); 
            // extract
            $zip->extractTo($destPath);
            $zip->close();
            
            $files = array_diff(scandir($destPath), array('..', '.'));
            
            // Check if only 3 files are there in ZIP
            if(count($files) != 3) return ['error' => _ERR159];
            
            $extensions['shp'] = $extensions['shx'] = $extensions['dbf'] = $name = '';
            foreach($files as $file) {
                $fileParts = pathinfo($file);
                if(strtolower($fileParts['extension']) == 'shp' && empty($extensions['shp']))
                    $extensions['shp'] = $destPath . DS . $file;
                else if(strtolower($fileParts['extension']) == 'shx' && empty($extensions['shx']))
                    $extensions['shx'] = $destPath . DS . $file;
                else if(strtolower($fileParts['extension']) == 'dbf' && empty($extensions['dbf']))
                    $extensions['dbf'] = $destPath . DS . $file;
                
                if(empty($name))
                    $name = $fileParts['filename'];
                else if($name != $fileParts['filename'])
                    return ['error' => _ERR160];
            }
            
            // Delelte ZIP file, we dont require it now
            @unlink($filename);
            
            return $extensions;
            
        } else {
            // ZIP file not valid
            return ['error' => _ERR161];
        }
    }
    
    /**
     * Shape File reader
     * 
     * @param array $inputs Input params for adding group map
     * @return boolean true/false
     */
    public function shapeFileReader($shapeFiles) {
        if(!empty($shapeFiles['shp'])) {
            
            require_once(ROOT . DS . 'vendor' . DS . 'shpParser' . DS . 'shpParser.php');
            $shpParserObj = new \shpParser;
            $shpParserObj->load($shapeFiles['shp']);
            
            // get Header Info
            $headerInfo = $shpParserObj->headerInfo();
            $shpSize = ceil($headerInfo['length'] / 1024); // Convert Bytes To KB
            $shpType = $this->getshapeTypeId($headerInfo['shapeType']['id']);
            
            // if shapeType is not found in list, set default shapeType i.e. 5 (polygon)
            if($shpType === false) $shpType = 5;
            
            //$getShapeData = $shpParserObj->getShapeData();
            $getShapeData = file_get_contents($shapeFiles['shp']);
            
            $return['shp'] = [
                'size' => $shpSize,
                'type' => $shpType,
                'xMin' => $headerInfo['boundingBox']['xmin'],
                'yMin' => $headerInfo['boundingBox']['ymin'],
                'xMax' => $headerInfo['boundingBox']['xmax'],
                'yMax' => $headerInfo['boundingBox']['ymax'],
                'data' => $getShapeData,
            ];
            
            $return['shx'] = file_get_contents($shapeFiles['shx']);
            $return['dbf'] = file_get_contents($shapeFiles['dbf']);
                        
            // remove shape files and folder
            $this->CommonInterface->unlinkFiles($shapeFiles);
            $this->CommonInterface->unlinkFiles(basename(dirname($shapeFiles['shp'])));
            
            return $return;
        } else {
            return false;
        }
    }
    
    /**
     * Add map to area/group siblings
     * 
     * @param array $inputs Input params for adding group map
     * @return boolean true/false
     */
    public function addMapToSiblings($inputs, $areaMapLayerNId) {
        
        // DUMMY
        $fieldsArray = [];
        $level = 2;
        
        if($inputs) {
            // All level
            if($inputs['siblingOption'] == 'all') {
                $areaNids = $this->getRecords($fields = [_AREA_AREA_NID], $conditions = [_AREA_AREA_LEVEL => $level], 'all');
            } // All Parents Level
            else if($inputs['siblingOption'] == 'all') {
                $areaNids = $this->getAreaChilds($inputs['aNid'], $level, $fromLevel = '');
            }
            
            if(!empty($areaNids)) {
                foreach($areaNids as $areaNid) {
                    // Adding Area Map
                    $fieldsArray[] = [
                        _AREAMAP_AREA_NID => $areaNid[_AREA_AREA_NID],
                        _AREAMAP_FEATURE_TYPE_NID => '-1',
                        _AREAMAP_FEATURE_LAYER => 0,
                        _AREAMAP_LAYER_NID => $areaMapLayerNId,
                    ];
                }
                $this->AreaMapObj->insertOrUpdateBulkData($fieldsArray);
            }
            return true;
        }
        return false;
    }
    
    /**
     * Split map
     * 
     * @param array $inputs Input params for adding group map
     * @return boolean true/false
     */
    public function splitMap($inputs, $areaMapLayerNId) {
        
        if(isset($inputs['assocCompMap'])) {        
            // Save New Areas In DB
            if($inputs['assocCompMap'] == false) {

            } // Save New Areas In DB and Associate them with $areaMapLayerNId
            else {

            }
        }
    }

    /**
     * getRecords method for Area Map Metadata
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @return void
     */
    public function getAreaMapMetadata(array $fields, array $conditions, $type = 'all') {
        return $this->AreaMapMetadataObj->getRecords($fields, $conditions, $type);
    }
    
    /**
     * Get Area parent Details
     * 
     * @param string $nid Area NId
     * @param string $toLevel Till the level
     * @param string $fromLevel Level to start
     * @return void
     */
    public function getAreaChilds($nid, $toLevel, $fromLevel = '')
    {
        $return = false;
        
        if(empty($fromLevel)) {
            $params['fields'] = [_AREA_AREA_LEVEL];
            $params['conditions'] = [_AREA_AREA_NID => $nid];
            $params['type'] = 'all';
            $params['extra'] = ['first' => true];
            $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);
            if(!empty($result) && !empty($result[_AREA_AREA_LEVEL])) {
                $fromLevel = $result[_AREA_AREA_LEVEL];
            } else {
                return false;
            }
        }
        // eg; 1 < 4
        if($fromLevel < $toLevel) {
            for ($i = $fromLevel + 1; $i <= $toLevel; $i++) {
                if($i == $fromLevel + 1) {
                    $AreaNIds = $this->getRecords([_AREA_AREA_NID, _AREA_AREA_NID], [_AREA_PARENT_NId => $nid, _AREA_AREA_LEVEL => $i], 'list', ['first' => true]);
                } else if($i == $toLevel) {
                    $return = $this->getAreaRecords([_AREA_AREA_NID], [_AREA_PARENT_NId . ' IN'], $AreaNIds, 'all');
                } else {
                    $AreaNIds = $this->getAreaRecords([_AREA_AREA_NID, _AREA_AREA_NID], [_AREA_PARENT_NId . ' IN'], $AreaNIds, 'list');
                }
            }
        }
        
        return $return;
    }

    /**
     * get area records (chunks used for heavy conditions)
     * 
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     */
    public function getAreaRecords($fields, $cond, $AreaIds, $type)
    {
        $result = [];
        $chunkSize = 900;
        $countIncludingChildparams = count($AreaIds, true);

        // count for single index
        $splitChunkSize = floor(count($AreaIds) / ($countIncludingChildparams / $chunkSize));

        // MSSQL Compatibilty - MSSQL can't support more than 2100 params
        $orConditionsChunked = array_chunk($AreaIds, $splitChunkSize);

        foreach ($orConditionsChunked as $orCond) {
            $conditions[$cond[0]] = $orCond;
            $getArea = $this->AreaObj->getRecords($fields, $conditions, $type);
            // We want to preserve the keys in list, as there will always be Nid in keys
            if ($type == 'list') {
                $result = array_replace($result, $getArea);
            }// we dont need to preserve keys, just merge
            else {
                $result = array_merge($result, $getArea);
            }
        }
        
        return $result;
    }

}
