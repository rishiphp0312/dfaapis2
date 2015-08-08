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
    public $components = ['Auth', 'Common'];
    public $AreaObj = NULL;
    public $AreaLevelObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->AreaObj = TableRegistry::get('DevInfoInterface.Areas');
        $this->AreaLevelObj = TableRegistry::get('DevInfoInterface.AreaLevel');
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
    }

    /**
     * getRecords method for Areas
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all') {
        return $this->AreaObj->getRecords($fields, $conditions, $type);
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
     * deleteRecords method for Areas 
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        return $this->AreaObj->deleteRecords($conditions);
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

}
