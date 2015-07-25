<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IcIus Component
 */
class IcIusComponent extends Component {

    // The other component your component uses
    public $components = [
        'Auth', 
        'CommonInterface',
        'DevInfoInterface.Indicator',
        'DevInfoInterface.Unit',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.SubgroupType',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.IndicatorClassifications'
    ];
    public $IcIusObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->IcIusObj = TableRegistry::get('DevInfoInterface.IcIus');
    }

    /**
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all') {
        return $this->IcIusObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
	
        return $this->IcIusObj->getDataByParams($fields, $conditions, $type);
    }

    /**
     * getGroupedList method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getGroupedList(array $fields, array $conditions) {
        return $this->IcIusObj->getGroupedList($fields, $conditions);
    }

    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        return $this->IcIusObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {
        return $this->IcIusObj->deleteByParams($conditions);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        return $this->IcIusObj->insertData($fieldsArray);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        //return $this->IcIusObj->insertBulkData($insertDataArray, $insertDataKeys);
        return $this->IcIusObj->bulkInsert($insertDataArray);
    }

    /**
     * bulkInsert method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function bulkInsert($dataArray = []) {
        return $this->IcIusObj->bulkInsert($dataArray);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->IcIusObj->updateDataByParams($fieldsArray, $conditions);
    }
    
    /**
     * getConcatedFields method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedFields(array $fields, array $conditions, $type = null)
    {
        if($type == 'list' && array_key_exists(2, $fields)){
            $result = $this->IcIusObj->getConcatedFields($fields, $conditions, 'all');
            if(!empty($result)){
                return array_column($result, 'concatinated', $fields[2]);
            }else{
                return [];
            }
        }else{
            return $this->IcIusObj->getConcatedFields($fields, $conditions, $type);
        }
    }
    
    /**
     * getConcatedIus method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedIus(array $fields, array $conditions, $type = null)
    {
        $result = $this->IcIusObj->getConcatedIus($fields, $conditions, 'all');
        if($type == 'list'){            
            if(!empty($result)){
                $result = array_column($result, 'concatinated', _IUS_IUSNID);
            }
        }
        
        return $result;
    }
	
	
	/*
     * returns the indicator list on basis of passed ic ind 
     * $fields array 
     * $conditions array 
     * 
     */

    public function getICIndicatorList($fields = [], $conditions = []) {

        $iusIds = $this->getDataByParams($fields, $conditions, 'list'); // get ius nids 
        $indiData = $this->IndicatorUnitSubgroup->getIndicatorDetails($iusIds); // get indicator ids   
        $indicatorDetails = [];
        if (!empty($indiData)) {
            foreach ($indiData as $index => $value) {

                $indicatorDetails[$value[_IUS_INDICATOR_NID]] = $this->CommonInterface->prepareNode($value[_IUS_INDICATOR_NID], $value['indicator'][_INDICATOR_INDICATOR_GID], $value['indicator'][_INDICATOR_INDICATOR_NAME], false);
            }
        }
        $indicatorDetails = array_values($indicatorDetails);

        return $indicatorDetails;
    }

    /*
     * exportIcius     
     * @return Exported File path
     */

    public function exportIcius() {
        $titleRow = $icLevels = $sTypeRows = [];

        //IC Records
        $icFields = [_IC_IC_NID, _IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_TYPE];
        $icConditions = [_IC_IC_TYPE . ' <>' => 'SR']; //[_IC_IC_NID . ' IN' => array_unique($icNids)];
        $icRecords = $this->IndicatorClassifications->getDataByParams($icFields, $icConditions);

        //IC_NIDS - Independent
        $icNidsIndependent = array_column($icRecords, _IC_IC_NID);

        //ICIUS Records
        $iciusFields = [_ICIUS_IC_NID, _ICIUS_IUSNID];
        $iciusConditions = [_ICIUS_IC_NID . ' IN' => $icNidsIndependent];
        $iciusRecords = $this->getDataByParams($iciusFields, $iciusConditions);

        //IC_NIDS from ICIUS
        $icNids = array_column($iciusRecords, _ICIUS_IC_NID);

        //IUS_NIDS from ICIUS
        $iusNids = array_unique(array_column($iciusRecords, _ICIUS_IUSNID));

        //IUS Records
        $iusFields = [_IUS_IUSNID, _IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_SUBGROUP_NIDS];
        //$iusConditions = [_IUS_IUSNID . ' IN' => array_unique($iusNids)];
        $iusNidsUnique = array_unique($iusNids);
        foreach($iusNidsUnique as $key => &$val){
            $returnIus[_IUS_IUSNID] = $val;
            $val = $returnIus;
        }
        unset($returnIus);
        $iusConditions = ['OR' => $iusNidsUnique];
        $iusRecords = $this->IndicatorUnitSubgroup->getDataByParams($iusFields, $iusConditions);

        //Get Individual Indicator, Unit, Subgroup, SubgroupVals
        $iusNids = array_unique(array_column($iusRecords, _IUS_IUSNID));
        $iNids = array_unique(array_column($iusRecords, _IUS_INDICATOR_NID));
        $uNids = array_unique(array_column($iusRecords, _IUS_UNIT_NID));
        $sValNids = array_unique(array_column($iusRecords, _IUS_SUBGROUP_VAL_NID));
        $sNids = array_unique(array_column($iusRecords, _IUS_SUBGROUP_NIDS));

        //convert comma separated NIDs into Individual NIDs
        $sNidsArr = [];
        array_map(function($val) use (&$sNidsArr) {
            $explodedVals = explode(',', $val);
            $sNidsArr = array_merge($sNidsArr, $explodedVals);
            $sNidsArr = array_unique($sNidsArr);
        }, $sNids);
        sort($sNidsArr);
        unset($sNids); //save buffer
        //get Indicator Records
        $iFields = [_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_GID];
        //$iConditions = [_INDICATOR_INDICATOR_NID . ' IN' => $iNids];
        $iConditionsUnique = array_unique($iNids);
        foreach($iConditionsUnique as $key => &$val){
            $returnInd[_INDICATOR_INDICATOR_NID] = $val;
            $val = $returnInd;
        }
        unset($returnInd);
        $iConditions = ['OR' => $iConditionsUnique];
        $iRecords = $this->Indicator->getDataByParams($iFields, $iConditions);
        $iNidsIndependent = array_column($iRecords, _INDICATOR_INDICATOR_NID);

        //get Unit Records
        $uFields = [_UNIT_UNIT_NID, _UNIT_UNIT_NAME, _UNIT_UNIT_GID];
        $uConditions = [_UNIT_UNIT_NID . ' IN' => $uNids];
        $uRecords = $this->Unit->getDataByParams($uFields, $uConditions);
        $uNidsIndependent = array_column($uRecords, _UNIT_UNIT_NID);

        //get SubgroupVals Records
        $sValFields = [_SUBGROUP_VAL_SUBGROUP_VAL_NID, _SUBGROUP_VAL_SUBGROUP_VAL, _SUBGROUP_VAL_SUBGROUP_VAL_GID];
        //$sValConditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN' => $sValNids];
        $sValConditionsUnique = array_unique($sValNids);
        foreach($sValConditionsUnique as $key => &$val){
            $returnSubgroupVal[_SUBGROUP_VAL_SUBGROUP_VAL_NID] = $val;
            $val = $returnSubgroupVal;
        }
        unset($returnSubgroupVal);
        $sValConditions = ['OR' => $sValConditionsUnique];
        $sValRecords = $this->SubgroupVals->getDataByParams($sValFields, $sValConditions);
        $sValNidsIndependent = array_column($sValRecords, _SUBGROUP_VAL_SUBGROUP_VAL_NID);

        //get Subgroups Records
        $sFields = [_SUBGROUP_SUBGROUP_NID, _SUBGROUP_SUBGROUP_NAME, _SUBGROUP_SUBGROUP_GID, _SUBGROUP_SUBGROUP_TYPE];
        $sConditions = [_SUBGROUP_SUBGROUP_NID . ' IN' => $sNidsArr];
        $sRecords = $this->Subgroup->getDataByParams($sFields, $sConditions);
        $sNidsIndependent = array_column($sRecords, _SUBGROUP_SUBGROUP_NID);

        //SubgroupType Nids from Subgroups
        $sTypeNidsTypeList = array_column($sRecords, _SUBGROUP_SUBGROUP_TYPE, _SUBGROUP_SUBGROUP_NID);
        $sTypeNidsArr = array_unique($sTypeNidsTypeList);

        //get Subgroups type Records
        $sTypeFields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME, _SUBGROUPTYPE_SUBGROUP_TYPE_GID, _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER];
        $sTypeConditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN' => $sTypeNidsArr];
        $sTypeRecords = $this->SubgroupType->getDataByParams($sTypeFields, $sTypeConditions);

        //Get Max IC levels
        $parentChildNodes = $this->CommonInterface->getParentChild('IndicatorClassifications', '-1', true, ['conditions' => [_IC_IC_TYPE . ' <>' => 'SR']]);
        $maxIcLevel = max(array_column($parentChildNodes, 'arrayDepth'));

        //Prepare levels
        for ($i = 1; $i <= $maxIcLevel; $i++) {
            $icLevels[] = 'Level' . $i;
        }

        //Prepare Subugroup Types List
        foreach ($sTypeRecords as $sTypeValue) {
            $sTypeRows[] = $sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
        }

        //Prepare Title row for Excel
        $titleRow = ['Class type'];
        $titleRow = array_merge($titleRow, $icLevels);
        $titleRow = array_merge($titleRow, ['Indicator', 'IndicatorGid', 'Unit', 'UnitGid', 'Subgroup', 'SubgroupGid']);
        $titleRow = array_merge($titleRow, $sTypeRows);

        //------ Write File
        //Get PHPExcel vendor
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        //Prepare Title row Cells
        $character = 'A';
        $row = 1;
        foreach ($titleRow as $titleColumns) {
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $titleColumns);
            $character++;
        }

        //Prepare Data row Cells
        foreach ($iciusRecords as $iciuskey => $iciusValue) {
            $character = 'A';
            //IC
            $icNidsKey = array_search($iciusValue[_ICIUS_IC_NID], $icNidsIndependent);
            $getIcDepthReturn = $this->CommonInterface->getIcDepth($iciusValue[_ICIUS_IC_NID], $icRecords);

            //Skip this step if No IC found
            if ($getIcDepthReturn === false)
                continue;

            //Auto Increment row numbers
            $row++;

            //IC Type
            $icCombination[$iciuskey] = $getIcDepthReturn;
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $getIcDepthReturn[0][_IC_IC_TYPE]);
            $character++; //Increment Column
            //Levels
            for ($i = 1; $i <= $maxIcLevel; $i++) {
                if (array_key_exists($i - 1, $getIcDepthReturn)) {
                    $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $getIcDepthReturn[$i - 1][_IC_IC_NAME]);
                }
                $character++; //Increment Column
            }

            //IUS
            $iusNidsKey = array_search($iciusValue[_ICIUS_IUSNID], $iusNids);

            //Indicator
            $iNidsKey = array_search($iusRecords[$iusNidsKey][_IUS_INDICATOR_NID], $iNidsIndependent);
            $iName = $iRecords[$iNidsKey][_INDICATOR_INDICATOR_NAME];
            $iGid = $iRecords[$iNidsKey][_INDICATOR_INDICATOR_GID];

            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $iName); //Indicator name
            $character++; //Increment Column
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $iGid); //Indicator GID
            $character++; //Increment Column
            //Unit
            $uNidsKey = array_search($iusRecords[$iusNidsKey][_IUS_UNIT_NID], $uNidsIndependent);
            $uName = $uRecords[$uNidsKey][_UNIT_UNIT_NAME];
            $uGid = $uRecords[$uNidsKey][_UNIT_UNIT_GID];

            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $uName); //Unit name
            $character++; //Increment Column
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $uGid); //Unit GID
            $character++; //Increment Column
            //SubgroupVals
            $sValNidsKey = array_search($iusRecords[$iusNidsKey][_IUS_SUBGROUP_VAL_NID], $sValNidsIndependent);
            $sValName = $sValRecords[$sValNidsKey][_SUBGROUP_VAL_SUBGROUP_VAL];
            $sValGid = $sValRecords[$sValNidsKey][_SUBGROUP_VAL_SUBGROUP_VAL_GID];

            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $sValName); //SubgroupVals name
            $character++; //Increment Column
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $sValGid); //SubgroupVals GID
            $character++; //Increment Column
            //Subgroup
            //$sValNidsKey = array_search($iusRecords[$iusNidsKey][_IUS_SUBGROUP_NIDS], $sNidsIndependent);
            $sNids = explode(',', $iusRecords[$iusNidsKey][_IUS_SUBGROUP_NIDS]);
            $sNidsFlipped = array_flip($sNids);
            $sNidsWithType = array_intersect_key($sTypeNidsTypeList, $sNidsFlipped);

            foreach ($sTypeRecords as $sTypeValue) {
                if (in_array($sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID], $sNidsWithType)) {
                    $sNid = array_search($sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID], $sNidsWithType);
                    $sKey = array_search($sNid, $sNidsIndependent);
                    $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $sRecords[$sKey][_SUBGROUP_SUBGROUP_NAME]); //SubgroupVals GID
                }
                $character++; //Increment Column
            }
        }

        //Write Title and Data to Excel
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $module = _ICIUS;
        $dbName = $this->session->read('dbName');
        $dbName = str_replace(' ', '_', $dbName);
        $returnFilename = _MODULE_NAME_ICIUS . '_' . $dbName . '_' . date('Y-m-d-h-i-s') . '.xls';
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment;filename=' . $returnFilename);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * bulkInsertIcIus method
     *
     * @param string $insertDataArrRowsFiltered Data rows to insert. {DEFAULT : null}
     * @return unsettedKeys array
     */
    public function bulkInsertIcIus($insertDataArrRowsFiltered, $extraParams = []) {
        extract($extraParams);
        $IcIusDataArray = [];

        // Prepare ICIUS
        foreach ($insertDataArrRowsFiltered as $key => $val) {
            if (!isset($iusCombinationsCond[$key])) {
                continue;
            }
         
            $ius = array_search($iusCombinationsCond[$key], $getExistingRecords);
            if (isset($ICArray[$key]) && $ius !== false) {
                $IcIusDataArray[$key][_ICIUS_IC_NID] = $ICArray[$key];
                $IcIusDataArray[$key][_ICIUS_IUSNID] = $ius;
                $IcIusCombination[$key] = "(" . $ICArray[$key] . "," . $ius . ")";
            }
        }
  
        if (!empty($IcIusDataArray)) {
            $IcIusDataArrayUnique = array_intersect_key($IcIusDataArray, array_unique(array_map('serialize', $IcIusDataArray)));

            $fields = [_ICIUS_IC_NID, _ICIUS_IUSNID, _ICIUS_IC_IUSNID];
            $conditions = ['OR' => $IcIusDataArrayUnique];
            $getExistingRecords = $this->getConcatedFields($fields, $conditions, 'list');

            if (!empty($getExistingRecords)) {
                $IcIusDataArray = array_diff_key($IcIusDataArray, array_intersect($IcIusCombination, $getExistingRecords));
            }
            
            if (!empty($IcIusDataArray)) {
                $insertDataKeys = [_ICIUS_IC_NID, _ICIUS_IUSNID];
                $this->insertBulkData($IcIusDataArray, $insertDataKeys);
            }
        }
    }

    /**
     * bulkUploadIcius
     * 
     * @param array $divideXlsOrCsvInChunks File Chunks
     * @param array $extra Any Extra parameter
     * 
     * @return boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function bulkUploadIcius($divideXlsOrCsvInChunks = [], $extra = null) {
        
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;

        foreach ($divideXlsOrCsvInChunks as $filename) {
            $objPHPExcel = $this->CommonInterface->readXlsOrCsv($filename);

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();
                $highestRow = $worksheet->getHighestRow(); // e.g. 10
                $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

                if ($highestRow == 1) {
                    $this->CommonInterface->unlinkFiles($divideXlsOrCsvInChunks);
                    return ['error' => _ERR122];
                }

                //Initialize Vars
                $insertFieldsArr = [];
                $insertDataArrRows = [];
                $insertDataArrCols = [];
                $unsettedKeys = [];
                $subgroupTypeFields = [];
                $levelArray = [];
                $indicatorArray = [];
                $unitArray = [];
                $subgroupValArray = [];
                $subgroupTypeArray = [];

                for ($row = 1; $row <= $highestRow; ++$row) {
                    $subgroupTypeFound = 0;

                    for ($col = 0; $col < $highestColumnIndex; ++$col) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $val = $cell->getValue();
                        $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                        if ($row == 1) {    //-- Headings row --//
                            $insertFieldsArr[$col] = $val;

                            if ($subgroupTypeFound == 1) {
                                $subgroupTypeFound = 2;
                            }
                            if ((strtolower($val) == strtolower('Subgroup'))) {
                                $subgroupTypeFieldKey = $col + 1;
                                $subgroupTypeFound = 1;
                            }
                            if (strtolower($val) == strtolower('SubgroupGid')) {
                                $subgroupTypeFieldKey = $col + 1;
                                $subgroupTypeFound = 1;
                            }
                            if ($subgroupTypeFound == 2) {
                                $subgroupTypeFields[$col][$row] = $val;
                            }
                        } else {  //-- Data Strats from row 2 --//
                            if ($importStatusFieldKey === false || ($importStatusFieldKey !== false && $col < $importStatusFieldKey)) {
                                $insertDataArrRows[$row][] = $val;
                                $insertDataArrCols[$col][$row] = $val;
                            }

                            if (($col != 0) && ($col < $indicatorFieldKey)) {
                                if ($col == 1 && !empty($val)) {
                                    $levelArray[$row][] = $val;
                                } else if (isset($levelArray[$row])) {
                                    $levelArray[$row][] = $val;
                                } else {  //--- maintain error log ---//
                                    $unsettedKeys = $this->CommonInterface->maintainErrorLogs($row, $unsettedKeys, _ERROR_IC_LEVEL_EMPTY);
                                }
                            } else {

                                if ($col == $indicatorFieldKey || (isset($indicatorGidFieldKey) && $col == $indicatorGidFieldKey)) {
                                    if ($col == $indicatorFieldKey && !empty($val)) {
                                        $indicatorArray[$row][] = $val;
                                    } else if (isset($indicatorArray[$row])) {
                                        $indicatorArray[$row][] = $val;
                                        $indicatorArray[$row][] = 0;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->CommonInterface->maintainErrorLogs($row, $unsettedKeys, _ERROR_INDICATOR_EMPTY);
                                    }
                                } else if ($col == $unitFieldKey || (isset($unitGidFieldKey) && $col == $unitGidFieldKey)) {
                                    if ($col == $unitFieldKey && !empty($val)) {
                                        $unitArray[$row][] = $val;
                                    } else if (isset($unitArray[$row])) {
                                        $unitArray[$row][] = $val;
                                        $unitArray[$row][] = 0;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->CommonInterface->maintainErrorLogs($row, $unsettedKeys, _ERROR_UNIT_EMPTY);
                                    }
                                } else if ($col == $subgroupValFieldKey || (isset($subgroupValGidFieldKey) && $col == $subgroupValGidFieldKey)) {
                                    if ($col == $subgroupValFieldKey && !empty($val)) {
                                        $subgroupValArray[$row][] = $val;
                                    } else if (isset($subgroupValArray[$row])) {
                                        $subgroupValArray[$row][] = $val;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->CommonInterface->maintainErrorLogs($row, $unsettedKeys, _ERROR_SUBGROUP_EMPTY);
                                    }
                                } else if ($col >= $subgroupTypeFieldKey) {
                                    if ($importStatusFieldKey === false || ($importStatusFieldKey !== false && $col < $importStatusFieldKey)) {
                                        if (isset($subgroupValArray[$row])) {
                                            $subgroupTypeArray[$row][] = $val;
                                        }
                                    }
                                }
                            }
                        }
                    } //-- Column Loop ends --//
                //Check Columns format
                    if ($row == 1) {

                        $validFormat = $this->CommonInterface->importFormatCheck('icius');
                        $formatDiff = array_diff($validFormat, array_map('strtolower', $insertFieldsArr));
                        if (!empty($formatDiff)) {
                            $this->CommonInterface->unlinkFiles($divideXlsOrCsvInChunks);
                            return ['error' => _ERR123];
                        }

                        // Check if sheet should start from Class type
                        if (strtolower(reset($insertFieldsArr)) !== 'class type') {
                            $this->CommonInterface->unlinkFiles($divideXlsOrCsvInChunks);
                            return ['error' => _ERR124];
                        }

                        // ------ Get Indicator, Unit, Subgroup etc. column Keys
                        // Check if Indicator Column exists
                        if (!isset($indicatorFieldKey)) {
                            $indicatorFieldKey = array_search(strtolower('Indicator'), array_map('strtolower', $insertFieldsArr));
                        }
                        // Check if IndicatorGid Column exists
                        if (!isset($indicatorGidFieldKey)) {
                            $indicatorGidFieldKey = array_search(strtolower('IndicatorGid'), array_map('strtolower', $insertFieldsArr));
                        }
                        // Check if unit Column exists
                        if (!isset($unitFieldKey)) {
                            $unitFieldKey = array_search(strtolower('Unit'), array_map('strtolower', $insertFieldsArr));
                        }
                        // Check if unitGid Column exists
                        if (!isset($unitGidFieldKey)) {
                            $unitGidFieldKey = array_search(strtolower('UnitGid'), array_map('strtolower', $insertFieldsArr));
                        }
                        // Check if SubgroupVal Column exists
                        if (!isset($subgroupValFieldKey)) {
                            $subgroupValFieldKey = array_search(strtolower('Subgroup'), array_map('strtolower', $insertFieldsArr));
                            if (gettype($subgroupValFieldKey) == 'integer') {
                                $subgroupTypeFieldKey = $subgroupValFieldKey + 1;
                            }
                        }
                        // Check if SubgroupValGid Column exists
                        if (!isset($subgroupValGidFieldKey)) {
                            $subgroupValGidFieldKey = array_search(strtolower('SubgroupGid'), array_map('strtolower', $insertFieldsArr));
                            if (gettype($subgroupValGidFieldKey) == 'integer') {
                                $subgroupTypeFieldKey = $subgroupValGidFieldKey + 1;
                            }
                        }
                        // Check if Import status Column exists
                        $importStatusFieldKey = array_search(strtolower(_IMPORT_STATUS), array_map('strtolower', $insertFieldsArr));
                        if ($importStatusFieldKey !== false) {
                            $titleFieldsKey = array_keys($insertFieldsArr);
                            foreach ($titleFieldsKey as $val) {
                                if ($val >= $importStatusFieldKey) {
                                    unset($insertFieldsArr[$val]);
                                    unset($subgroupTypeFields[$val]);
                                }
                            }
                        }

                        $subgroupTypeFields = array_intersect_key($subgroupTypeFields, $insertFieldsArr);
                        $highestSubgroupTypeColumn = max(array_keys($subgroupTypeFields));

                        $subgroupTypeFieldsWithColumn = array_combine(array_keys($subgroupTypeFields), array_column($subgroupTypeFields, 1));

                        $fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME, _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER];
                        $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NAME . ' IN' => $subgroupTypeFieldsWithColumn];
                        $existingSubgroupTypes = $this->SubgroupType->getDataByparams($fields, $conditions, 'all', ['order' => [_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => 'ASC']]);
                        $existingSubgroupTypesWithNids = array_column($existingSubgroupTypes, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME, _SUBGROUPTYPE_SUBGROUP_TYPE_NID);
                        $subgroupTypeMissingInSheet = array_diff($existingSubgroupTypesWithNids, $subgroupTypeFieldsWithColumn);

                        if (!empty($subgroupTypeMissingInSheet)) {
                            $this->CommonInterface->unlinkFiles($divideXlsOrCsvInChunks);
                            return ['error' => _ERR125];
                        } else {
                            // Exactly all the Dimensions from DB are gien in sheet
                            if (count($existingSubgroupTypesWithNids) == count($subgroupTypeFieldsWithColumn)) {
                                // Check if DB and uploaded Dimensions are in same order 
                                if (array_values($existingSubgroupTypesWithNids) !== array_values($subgroupTypeFieldsWithColumn)) {
                                    $this->CommonInterface->unlinkFiles($divideXlsOrCsvInChunks);
                                    return ['error' => _ERR126];
                                }
                            }// Some new dimensions are added in the sheet
                            else {
                                $subgroupTypeExtraInSheet = array_diff($subgroupTypeFieldsWithColumn, $existingSubgroupTypesWithNids);
                                $maxOrder = max(array_column($existingSubgroupTypes, _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER));
                                $maxOrderIncrement = $maxOrder;
                                foreach ($subgroupTypeExtraInSheet as $newSubgroupName) {
                                    $maxOrderIncrement++;
                                    $insertArray[] = [
                                        _SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $newSubgroupName,
                                        _SUBGROUPTYPE_SUBGROUP_TYPE_GID => $this->CommonInterface->guid(),
                                        _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => $maxOrderIncrement,
                                        _SUBGROUPTYPE_SUBGROUP_TYPE_GLOBAL => 0
                                    ];
                                }
                                $this->SubgroupType->insertBulkData($insertArray);

                                $subgroupTypeFieldsWithColumn = array_values($subgroupTypeFieldsWithColumn);
                                $order = 1;
                                foreach ($subgroupTypeFieldsWithColumn as $value) {
                                    $this->SubgroupType->updateDataByParams([_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => $order], [_SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $value]);
                                    $order++;
                                }
                            }
                        }
                    }

                    // Unset if whole row is blank
                    if (isset($insertDataArrRows[$row]) && array_filter($insertDataArrRows[$row]) == null) {
                        unset($insertDataArrRows[$row]);
                    }

                    // Unset IC level if whole row is blank
                    if (isset($levelArray[$row])) {
                        if (empty(array_filter($levelArray[$row]))) {
                            unset($levelArray[$row]);
                        }
                    }
                } //-- Row Loop ends --//
            }

            $indicatorFieldKey = array_search(strtolower('Indicator'), array_map('strtolower', $insertFieldsArr));
            $subgroupFieldKey = array_search(strtolower('SubgroupGid'), array_map('strtolower', $insertFieldsArr));
            $insertFieldsArr = array_filter($insertFieldsArr);

            $insertDataArrColsLevel1 = array_unique(array_filter(array_values($insertDataArrCols[1])));
            $insertDataArrRowsFiltered = $insertDataArrRows;

            $subGroupValsConditionsArray = [];
            $subGroupValsConditions = [];

            foreach ($insertDataArrCols as $key => $value) {

                if (!array_key_exists($key, $insertFieldsArr)) {
                    if ($key > (array_keys($insertFieldsArr)[count(array_keys($insertFieldsArr)) - 1])) {
                        break;
                    } else {
                        continue;
                    }
                }

                $valueOriginal = $value;

                if ($key == 0) {  //-- IC type
                } else if (($key != 0) && ($key < $indicatorFieldKey)) {  //--- IC Levels ---//
                    $fields = [_IC_IC_NID, _IC_IC_NAME];
                    $levelCombination = [];
                    if (!isset($ICArray))
                        $ICArray = [];

                    // IC Level 1
                    if ($key == 1) {
                        $value = array_filter(array_unique($value));
                        $icTypes = $extra['icTypes'] = $insertDataArrCols[$key - 1];
                        
                        // Unset Invalid IC type rows
                        foreach($icTypes as $rowCount => $val){
                            if(strlen($val) > 2){
                                $unsettedKeys = $this->CommonInterface->maintainErrorLogs($rowCount, $unsettedKeys, _ERROR_9);
                                unset($icTypes[$rowCount]);
                            }
                        }
                        // Use only valid Values
                        $value = array_diff_key($value, $unsettedKeys);
                        
                        $levelIcRecsWithNids = $this->IndicatorClassifications->saveNameAndGetNids($fields, $value, $extra);

                        $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                        $conditions = [_IC_IC_NAME . ' IN' => $levelIcRecsWithNids];
                        $levelIcRecsWithNids = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');

                        // Use only valid Values
                        $levelArray = array_diff_key($levelArray, $unsettedKeys);
                        
                        $allKeys = array_keys($levelArray);
                        $levelArray = array_intersect_key($levelArray, array_filter(array_combine(array_keys($levelArray), array_column($levelArray, $key - 1))));

                        //--- maintain error log - starts ---//
                        $keysToUnset = array_diff($allKeys, array_keys($levelArray));
                        $keysToUnset = array_flip(array_diff_key(array_flip($keysToUnset), $unsettedKeys));
                        $unsettedKeys = array_replace($unsettedKeys, array_fill_keys($keysToUnset, _ERROR_IC_LEVEL_EMPTY));
                        //--- maintain error log - ends ---//

                        /*
                         * Use below line to prepare list if 'all' selected above in getConcatedFields
                         * $levelIcRecsWithNids = array_column($levelIcRecsWithNids, 'concatinated', _IC_IC_NID);
                         */
                        $levelCombination = $levelCombinationCond = [];
                        
                        array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination, &$levelCombinationCond, &$ICArray, &$levelArray, $indicatorFieldKey) {
                            if (!empty($val[$key - 1])) {
                                $parent_Nid = -1;
                                if ($indicatorFieldKey != 2) {
                                    $val[$key - 1] = array_search("(" . $parent_Nid . ",'" . $val[$key - 1] . "')", $levelIcRecsWithNids);
                                }
                                $levelCombination[$index] = "(" . $parent_Nid . ",'" . $val[$key - 1] . "')";
                                $levelCombinationCond[$index][_IC_IC_PARENT_NID] = $parent_Nid;
                                $levelCombinationCond[$index][_IC_IC_NAME] = $val[$key - 1];
                                $ICArray[$index] = $val[$key - 1];
                            }
                        });

                        if ($indicatorFieldKey == 2) {
                            $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                            $conditions = ['OR' => $levelCombinationCond];
                            $getConcatedFields = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');

                            $field = [];
                            $field[] = _IC_IC_NAME;
                            $field[] = _IC_IC_PARENT_NID;
                            $field[] = _IC_IC_GID;
                            $field[] = _IC_IC_TYPE;
                            $field[] = _IC_IC_GLOBAL;

                            //------ Prepare New records
                            $insertResults = array_unique(array_filter(array_diff($levelCombination, $getConcatedFields)));

                            if (!empty($insertResults)) {
                                array_walk($insertResults, function(&$val, $rowIndex) use ($field, $levelArray, $key, $icTypes) {
                                    if (!empty($val)) {
                                        $returnFields = [];
                                        $returnFields[$field[0]] = $levelArray[$rowIndex][$key - 1];
                                        $returnFields[$field[1]] = '-1';
                                        $returnFields[$field[2]] = $this->CommonInterface->guid();
                                        $returnFields[$field[3]] = $icTypes[$rowIndex];
                                        $returnFields[$field[4]] = 0;
                                        $val = $returnFields;
                                    }
                                });
                            }

                            $bulkInsertArray = $insertResults;
                            unset($insertResults); //Save Buffer
                            
                            //------ Insert New records
                            if (!empty($bulkInsertArray)) {
                                $this->IndicatorClassifications->insertOrUpdateBulkData($bulkInsertArray);
                            }

                            $levelCombination = array_unique($levelCombination);
                            $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                            $levelCombinationCond = array_intersect_key($levelCombinationCond, array_unique(array_map('serialize', $levelCombinationCond)));

                            $conditions = ['OR' => $levelCombinationCond];
                            $levelIcRecsWithNids = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');
                            $levelArray = array_intersect_key($levelArray, array_filter(array_combine(array_keys($levelArray), array_column($levelArray, $key - 1))));
                            
                            array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination, &$ICArray) {
                                if (!empty($val[$key - 1])) {
                                    $parent_Nid = '-1';
                                    $val[$key - 1] = array_search("(" . $parent_Nid . ",'" . $val[$key - 1] . "')", $levelIcRecsWithNids);
                                    $ICArray[$index] = $val[$key - 1];
                                }
                            });
                            
                        }
                    } else { // IC Level > Level-1
                        // Use below line when 'all' selected in getConcatedFields used generate $levelIcRecsWithNids
                        //$levelIcRecsWithNids = array_column($levelIcRecsWithNids, 'concatinated', _IC_IC_NID);
                        array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination, &$levelCombinationCond) {
                            if (!empty($val[$key - 1])) {
                                $parent_Nid = $val[$key - 2];
                                $levelCombination[$index] = "(" . $val[$key - 2] . ",'" . $val[$key - 1] . "')";
                                $levelCombinationCond[$index][_IC_IC_PARENT_NID] = $val[$key - 2];
                                $levelCombinationCond[$index][_IC_IC_NAME] = $val[$key - 1];
                            }
                        });


                        $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                        $conditions = ['OR' => $levelCombinationCond];
                        $getConcatedFields = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');

                        $field = [];
                        $field[] = _IC_IC_NAME;
                        $field[] = _IC_IC_PARENT_NID;
                        $field[] = _IC_IC_GID;
                        $field[] = _IC_IC_TYPE;
                        $field[] = _IC_IC_GLOBAL;

                        //------ Prepare New records
                        $insertResults = array_unique(array_filter(array_diff($levelCombination, $getConcatedFields)));
                        if (!empty($insertResults)) {
                            array_walk($insertResults, function(&$val, $rowIndex) use ($field, $levelArray, $key, $icTypes) {
                                if (!empty($val)) {
                                    $returnFields = [];
                                    $returnFields[$field[0]] = $levelArray[$rowIndex][$key - 1];
                                    $returnFields[$field[1]] = $levelArray[$rowIndex][$key - 2];
                                    $returnFields[$field[2]] = $this->CommonInterface->guid();
                                    $returnFields[$field[3]] = $icTypes[$rowIndex];
                                    $returnFields[$field[4]] = 0;
                                    $val = $returnFields;
                                }
                            });
                        }

                        $bulkInsertArray = $insertResults;
                        unset($insertResults); //Save Buffer
                        //------ Insert New records
                        if (!empty($bulkInsertArray)) {
                            $this->IndicatorClassifications->insertOrUpdateBulkData($bulkInsertArray);
                        }

                        $levelCombination = array_unique($levelCombination);
                        $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                        $levelCombinationCond = array_intersect_key($levelCombinationCond, array_unique(array_map('serialize', $levelCombinationCond)));

                        $conditions = ['OR' => $levelCombinationCond];
                        $levelIcRecsWithNids = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');
                        $levelArray = array_intersect_key($levelArray, array_filter(array_combine(array_keys($levelArray), array_column($levelArray, $key - 1))));

                        array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination, &$ICArray) {
                            if (!empty($val[$key - 1]) || !empty($val[$key - 2])) {
                                $parent_Nid = $val[$key - 2];
                                $val[$key - 1] = array_search("(" . $parent_Nid . ",'" . $val[$key - 1] . "')", $levelIcRecsWithNids);
                                $ICArray[$index] = $val[$key - 1];
                            }
                        });
                    }
                } else {

                    $subgroupValSubgroupArr = [];
                    $value = array_unique(array_filter($value));

                    // Last Column should not be skipped even if its empty as we need to combine all dimensions at the end
                    //if ($key != (array_keys($insertDataArrCols)[count(array_keys($insertDataArrCols)) - 1])) {
                    if ($key != (array_keys($insertFieldsArr)[count(array_keys($insertFieldsArr)) - 1])) {
                        if (empty($value)) {
                            continue;
                        }
                    }

                    if ($key == $indicatorFieldKey) {   //--- INDICATOR ---//
                        $indicatorRecWithNids = $this->CommonInterface->saveAndGetIndicatorRecWithNids($indicatorArray);
                        
                    } else if ($key == $unitFieldKey) { //--- UNIT ---//
                        $unitRecWithNids = $this->CommonInterface->saveAndGetUnitRecWithNids($unitArray);
                        
                    } else if ($key >= $subgroupTypeFieldKey) { //--- SUBGROUP DIMENSIONS ---//
                        if (!isset($getSubGroupTypeNidAndName)) {
                            $getSubGroupTypeNidAndNameReturn = $this->CommonInterface->getSubGroupTypeNidAndName($subgroupTypeFields);
                            $getSubGroupTypeNidAndName = $getSubGroupTypeNidAndNameReturn['getSubGroupTypeNidAndName'];
                            $subGroupTypeList = $getSubGroupTypeNidAndNameReturn['subGroupTypeList'];
                        }

                        $subgroupType = array_search($subGroupTypeList[$key], $getSubGroupTypeNidAndName);

                        //$subGroupValsConditions = $subGroupValsConditionsArray = [];
                        foreach ($valueOriginal as $val) {
                            if (!empty($val)) {
                                $subGroupValsConditions[] = '("' . $val . '",' . $subgroupType . ')';
                                $subGroupValsConditionsArray[] = [
                                    _SUBGROUP_SUBGROUP_NAME => $val,
                                    _SUBGROUP_SUBGROUP_TYPE => $subgroupType
                                ];
                                $subGroupValsConditions = array_unique($subGroupValsConditions);
                            }
                        }

                        $conditions = [_SUBGROUP_SUBGROUP_TYPE => $subgroupType];
                        $maxSubgroupOrder = $this->Subgroup->getMax(_SUBGROUP_SUBGROUP_ORDER, $conditions);
                        if (!empty($value)) {
                            array_walk($value, function(&$val) use($subgroupType, &$maxSubgroupOrder) {
                                $returnData = [];
                                $returnData[] = $val;
                                $returnData[] = '';
                                $returnData[] = $subgroupType;
                                $returnData[] = ++$maxSubgroupOrder;
                                $returnData[] = false;
                                $val = $returnData;
                            });
                            $insertDataKeys = ['name' => _SUBGROUP_SUBGROUP_NAME, 'gid' => _SUBGROUP_SUBGROUP_GID, 'subgroup_type' => _SUBGROUP_SUBGROUP_TYPE, 'subgroup_order' => _SUBGROUP_SUBGROUP_ORDER, 'subgroup_global' => _SUBGROUP_SUBGROUP_GLOBAL];
                            $divideNameAndGids = $this->CommonInterface->divideNameAndGids($insertDataKeys, $value);

                            $params['nid'] = _SUBGROUP_SUBGROUP_NID;
                            $params['insertDataKeys'] = $insertDataKeys;
                            $params['updateGid'] = FALSE;
                            $component = 'Subgroup';

                            $this->CommonInterface->nameGidLogic($divideNameAndGids, $component, $params);
                        }

                        $subGroupValsConditionsArrayFiltered = array_intersect_key($subGroupValsConditionsArray, $subGroupValsConditions);

                        //Last Dimension Column
                        if ($key == (array_keys($subGroupTypeList)[count(array_keys($subGroupTypeList)) - 1])) {

                            if (empty($subGroupValsConditionsArrayFiltered))
                                continue;
                            $subgroupTypeArrayFiltered = [];
                            foreach ($subgroupTypeArray as $key => $val) {
                                $val = array_filter($val);
                                $subgroupTypeArrayFiltered[$key] = $val;
                                $subgroupValArray[$key][0] = implode(' ', $val);
                            }

                            $subgroupValsNIdsReturn = $this->CommonInterface->saveAndGetSubgroupValsRecWithNids($subgroupValArray, ['key' => $subgroupValFieldKey]);
                            $subgroupValsNIds = $subgroupValsNIdsReturn['subgroupValsNIds'];

                            $conditions = ['OR' => $subGroupValsConditionsArrayFiltered];
                            $getSubGroupNidAndName = $this->Subgroup->getDataByParams(
                                    [_SUBGROUP_SUBGROUP_NID, _SUBGROUP_SUBGROUP_NAME], $conditions, 'list');
                            
                            //--- SUBGROUP_VALS ---//
                            $subGroupValsComb = [];
                            $subGroupValsCombArray = [];
                            foreach ($subgroupTypeArrayFiltered as $rowKey => $subgroupvalsubgroup) {

                                $subgroup = $subgroupValArray[$rowKey][0];

                                //Ensure the Dimensions are given
                                if (!empty($subgroupvalsubgroup)) {
                                    foreach ($subgroupvalsubgroup as $dimKey => $dimVal) {
                                        if (array_search($subgroup, $subgroupValsNIds) == 0 || array_search($subgroup, $subgroupValsNIds) === false) {
                                            continue;
                                        }
                                        $subGroupValsComb[] = '(' . array_search($subgroup, $subgroupValsNIds) . ',' . array_search($dimVal, $getSubGroupNidAndName) . ')';
                                        $subGroupValsCombArray[] = [
                                            _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID => array_search($subgroup, $subgroupValsNIds),
                                            SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID => array_search($dimVal, $getSubGroupNidAndName)
                                        ];
                                    }

                                    $subGroupValsComb = array_unique($subGroupValsComb);
                                }
                            }

                            $subGroupValsSubgroupWithNids = $this->SubgroupValsSubgroup->bulkInsert($subGroupValsComb, $subGroupValsCombArray);
  
                            $extra['group'] = _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID;
                            $extra['order'] = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID => 'ASC'];
                            $fields = [
                                _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID,
                                SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
                            $conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID . ' IN' => array_keys($subgroupValsNIds)];
                            $subGroupNidGroupedBySubgroupValNids = $this->SubgroupValsSubgroup->getDataByParams($fields, $conditions, 'all', $extra);
                            $subGroupNidGroupedBySubgroupValNids = array_column($subGroupNidGroupedBySubgroupValNids, SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . '_CONCATED', _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID);
                        }
                    }
                }
            } //Individual Column Foreach Ends
            
            //------------- IUS ------------//
            $iusCombinations = [];
            $iusCombinationsCond = [];

            $unsettedKeysNew = array_intersect_key($unsettedKeys, array_filter(array_intersect_key(array_map('array_filter', $insertDataArrRowsFiltered), $unsettedKeys)));
            $insertDataArrRowsFiltered = array_diff_key($insertDataArrRowsFiltered, $unsettedKeys);
            $unsettedKeys = $unsettedKeysNew;
            
            unset($unsettedKeysNew); // Save buffer
            // Prepare IUS
            foreach ($insertDataArrRowsFiltered as $key => $val) {

                // replace subgroupVal field from concated/custom made subgroupVal
                $val[$subgroupValFieldKey] = $subgroupValArray[$key][0];

                //Skip records entry if Indicator OR Unit OR Subgroup is not found.
                if (empty($val[$indicatorFieldKey]) || empty($val[$unitFieldKey]) || empty($val[$subgroupValFieldKey])) {
                    unset($insertDataArrRowsFiltered[$key]);
                    continue;
                }
                if ((array_search($val[$indicatorFieldKey], $indicatorRecWithNids) == 0)) {
                    $unsettedKeys = $this->CommonInterface->maintainErrorLogs($key, $unsettedKeys, _ERROR_6);
                    continue;
                }
                if ((array_search($val[$unitFieldKey], $unitRecWithNids) == 0)) {
                    $unsettedKeys = $this->CommonInterface->maintainErrorLogs($key, $unsettedKeys, _ERROR_7);
                    continue;
                }
                if ((array_search($val[$subgroupValFieldKey], $subgroupValsNIds) == 0)) {
                    $unsettedKeys = $this->CommonInterface->maintainErrorLogs($key, $unsettedKeys, _ERROR_8);
                    continue;
                }
                // Ignore rows having errors
                if(array_key_exists($key, $unsettedKeys)){
                    continue;
                }

                $iusCombinations[$key][_IUS_INDICATOR_NID] = array_search($val[$indicatorFieldKey], $indicatorRecWithNids);
                $iusCombinations[$key][_IUS_UNIT_NID] = array_search($val[$unitFieldKey], $unitRecWithNids);
                $subgroupValNid = array_search($val[$subgroupValFieldKey], $subgroupValsNIds);
                $iusCombinations[$key][_IUS_SUBGROUP_VAL_NID] = $subgroupValNid;
                $iusCombinations[$key][_IUS_SUBGROUP_NIDS] = $subGroupNidGroupedBySubgroupValNids[$subgroupValNid];

                $iusCombinationsCond[$key] = '('
                        . $iusCombinations[$key][_IUS_INDICATOR_NID] . ','
                        . $iusCombinations[$key][_IUS_UNIT_NID] . ','
                        . $iusCombinations[$key][_IUS_SUBGROUP_VAL_NID] . ','
                        . '\'' . $iusCombinations[$key][_IUS_SUBGROUP_NIDS] . '\''
                        //. $iusCombinations[$key][_IUS_SUBGROUP_NIDS]
                        . ')';
            }
          
            if (!empty($iusCombinations)) {
                $columnKeys = [_IUS_IUSNID, _IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_SUBGROUP_NIDS];
                $conditions = ['OR' => $iusCombinations];
                $getExistingRecords = $this->IndicatorUnitSubgroup->getConcatedIus($columnKeys, $conditions, 'list');

                //Some records already exists, don't add them
                if (!empty($getExistingRecords)) {
                    $iusCombinations = array_diff_key($iusCombinations, array_intersect($iusCombinationsCond, $getExistingRecords));
                }

                // Insert New IUS records
                if (!empty($iusCombinations)) {
                    $insertDataKeys = [_IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_SUBGROUP_NIDS];
                    //$this->IndicatorUnitSubgroup->insertBulkData($iusCombinations, $insertDataKeys);
                    $this->IndicatorUnitSubgroup->bulkInsert($iusCombinations);
                }

                $getExistingRecords = $this->IndicatorUnitSubgroup->getConcatedIus($columnKeys, $conditions, 'list');
            }

            //------------- INSERT ICIUS ------------//
            $extraIcius['iusCombinationsCond'] = $iusCombinationsCond;
            $extraIcius['getExistingRecords'] = $getExistingRecords;
            $extraIcius['ICArray'] = $ICArray;
            $this->bulkInsertIcIus($insertDataArrRowsFiltered, $extraIcius);
            unset($insertDataArrRowsFiltered); //save buffer

            $unsettedKeysAllChunksArr[] = $unsettedKeys;
            $allChunksRowsArr[] = array_keys($insertDataArrRows);

            // ---- ICIUS successfully added - chunk
            $this->CommonInterface->unlinkFiles($filename);
            
        }// Chunk Loop ends
        
        // ---- ICIUS successfully added - whole file ----- //
        // Prepare Return
        $return = [
            'allChunksRowsArr' => $allChunksRowsArr,
            'unsettedKeysAllChunksArr' => $unsettedKeysAllChunksArr,
            'highestSubgroupTypeColumn' => $highestSubgroupTypeColumn,
        ];
        
        return $return;
    }

    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->IcIusObj->testCasesFromTable($params);
    }

}
