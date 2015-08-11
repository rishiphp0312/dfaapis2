<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * DataEntry component
 */
class DataEntryComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];    
    public $components = ['Common', 'UserAccess', 'DevInfoInterface.CommonInterface', 'TransactionLogs'];

    public function initialize(array $config) {
        parent::initialize($config);
    }
    
    /**
     * Get Indicator and Unit GIDs from names
     * 
     * @param array $dataArray Data read from DES file
     * 
     * @return array [iGid, uGid] else [Error]
     */
    public function getIndicatorAndUnitGids($worksheet)
    {
        $indicatorName = $worksheet->getCellByColumnAndRow($col = 1, $row = 5)->getValue();
        $indicatorGid = $worksheet->getCellByColumnAndRow($col = 11, $row = 5)->getValue();
                
        // Check If Indicator Exists
        if(!empty($indicatorName) || !empty($indicatorGid)) {
            
            // Check Gid
            if(!empty($indicatorGid)) {
                $check = 'gid';
                $conditions = [_INDICATOR_INDICATOR_GID => trim($indicatorGid)];
            } // Check Name
            else {
                $check = 'name';
                $conditions = [_INDICATOR_INDICATOR_NAME => trim($indicatorName)];
            }
            
            $params = [];
            $params['fields'] = [_INDICATOR_INDICATOR_GID];
            $params['conditions'] = $conditions;
            $result = $this->CommonInterface->serviceInterface('Indicator', 'getRecords', $params);
            
            if(!empty($result)) {
                $iGid = reset($result)[_INDICATOR_INDICATOR_GID];
            } else {
                if($check == 'gid')
                    return ['error' => 'Invalid Indicator Gid'];
                if($check == 'name')
                    return ['error' => 'Invalid Indicator Name'];
            }
        } else {
            return ['error' => 'Invalid Sheet'];
        }
        
        $unitName = $worksheet->getCellByColumnAndRow($col = 1, $row = 7)->getValue();
        $unitGid = $worksheet->getCellByColumnAndRow($col = 11, $row = 7)->getValue();
        
        // Check If Unit Exists
        if (!empty($unitName) || !empty($unitGid)) {
            
            // Check Gid
            if(!empty($unitGid)) {
                $check = 'gid';
                $conditions = [_UNIT_UNIT_GID => trim($unitGid)];
            } // Check Name
            else {
                $check = 'name';
                $conditions = [_UNIT_UNIT_NAME => trim($unitName)];
            }
            
            $params = [];
            $params['fields'] = [_UNIT_UNIT_GID];
            $params['conditions'] = $conditions;
            $result = $this->CommonInterface->serviceInterface('Unit', 'getRecords', $params);
            
            if(!empty($result)) {
                $uGid = reset($result)[_UNIT_UNIT_GID];                    
            } else {
                if($check == 'gid')
                    return ['error' => 'Invalid Unit Gid'];
                if($check == 'name')
                    return ['error' => 'Invalid Unit Name'];
            }
        } else {
            return ['error' => 'Invalid Sheet'];
        }
        
        return ['iGid' => $iGid, 'uGid' => $uGid];
    }
    
    /**
     * Prepare DES data for Data Save service
     * 
     * @param array $dataArray Data read from DES file
     * @param string $isJSON return is JSON or not
     * @param array $extra Any extra param
     * 
     * @return array/json prepared Data
     */
    public function prepareDesData($data, $row, $extra = [])
    {        
        // Get Gid if given
        $sGid = (!empty($data[11])) ? $data[11] : '';
        
        $preparedData = [
                'dNid' => '',
                'iusNid' => '',
                'iusGId' => '',
                'dv' => $data[3],
                'src' => $data[5],
                'srcNid' => '',
                'footnote' => $data[6],
                'tpNid' => '',
                'tp' => $data[0],
                'aId' => $data[1],
                'aNid' => '',
                'iGid' => $extra['iGid'],
                'uGid' => $extra['uGid'],
                'sGid' => $sGid,
                'sName' => $data[4],
                'DESInfo' => [
                    'sheetName' => $extra['sheetName'], 
                    'rowNo' => $row
                ],
            ];
        
        return $preparedData;
    }
    
    /**
     * Prepare DES(Data Entry Spreadsheet) Data
     * 
     * @param string $filename File to be processed
     * @param string $dbId Database Id
     * 
     * @return string Custom Log file path
     */
    public function prepareDesSheetData($worksheet, $isJSON = false, $extra = [])
    {
        $worksheetTitle = $worksheet->getTitle();
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $startRows = isset($extra['startRow']) ? $extra['startRow'] : 1 ;
        
        // get prepared data for all rows
        for ($row = $startRows; $row <= $highestRow; ++$row) {
            $dataArray = [];
            for ($col = 0; $col < $highestColumnIndex; ++$col) {
                $cell = $worksheet->getCellByColumnAndRow($col, $row);
                $val = $cell->getValue();

                if ($row >= $startRows) {
                    $dataArray[] = $val;
                } else {
                    continue;
                }
            }
            
            $preparedData[] = $this->prepareDesData($dataArray, $row, $extra);
        }
        
        if($isJSON == true)
            return json_encode($preparedData);
        else
            return $preparedData;
    }
    
    /**
     * Process DES(Data Entry Spreadsheet)
     * 
     * @param string $filename File to be processed
     * @param string $dbId Database Id
     * 
     * @return string Custom Log file path
     */
    public function importDes($filename, $dbId, $dbConnection)
    {
        // Establish DevInfo DB conenction
        $this->CommonInterface->setDbConnection($dbConnection);
        
        $objPHPExcel = $this->CommonInterface->readXlsOrCsv($filename, false);
        $startRows = 1;
        $return = [];
        
        // Iterate through Worksheets
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            
            $worksheetTitle = $worksheet->getTitle();
            $result = $this->getIndicatorAndUnitGids($worksheet);
            
            if(isset($result['error'])) {
                $return['log'][$worksheetTitle]['status'] = false;
                $return['log'][$worksheetTitle]['error'] = $result['error'];
            } else {
                
                // filter User access - Indicator
                $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);
                
                if ($indicatorGidsAccessible !== false && !empty($indicatorGidsAccessible) && !in_array($result['iGid'], $indicatorGidsAccessible)) {
                    $return['log'][$worksheetTitle]['status'] = false;
                    $return['log'][$worksheetTitle]['error'] = _INDICATOR_ACCESS_NOT_ALLOWED;
                } else {
                    $extra = ['startRow' => 11, 'iGid' => $result['iGid'], 'uGid' => $result['uGid'], 'sheetName' => $worksheetTitle];
                    $preparedData = $this->prepareDesSheetData($worksheet, $isJSON = true, $extra);                 

                    $params = ['dbId' => $dbId, 'jsonData' => $preparedData, $validation = true, $customLog = true, $isDbLog = false];
                    $result = $this->CommonInterface->serviceInterface('Data', 'saveData', $params);

                    if(!isset($startTime))
                        $startTime = $result['customLogJson']['startTime'];
                    $endTime = $result['customLogJson']['startTime'];

                    $return['log'][$worksheetTitle] = $result;
                }
            }
        }
        
        if(!empty($return)) {
            $return['startTime'] = $startTime;
            $return['endTime'] = $endTime;
            $logFile = $this->Common->writeLogFile($return, $dbId);
        }
        
        return $logFile;
    }
        
    /**
     * Generate IU sheet object
     * 
     * @param string $filename File to be processed
     * @param string $dbId Database Id
     * 
     * @return string Custom Log file path
     */
    public function getIuSheetObject($objWorkSheet, $dataAssociations, $conditions, $extra = [])
    {
        extract($dataAssociations);
        extract($area);
        extract($extra);
        
        $objWorkSheet->SetCellValue('B5', $indicatorNameWithNid[$iusGroups[$key][_IUS_INDICATOR_NID]]); // Indicator Name
        $objWorkSheet->SetCellValue('L5', $indicatorGidWithNid[$iusGroups[$key][_IUS_INDICATOR_NID]]); // Indicator Gid
        $objWorkSheet->SetCellValue('B7', $unitNameWithNid[$iusGroups[$key][_IUS_UNIT_NID]]); // Unit Name
        $objWorkSheet->SetCellValue('L7', $unitGidWithNid[$iusGroups[$key][_IUS_UNIT_NID]]); // Unit Gid

        $conditions[_MDATA_INDICATORNID] = $iusGroups[$key][_IUS_INDICATOR_NID];
        $conditions[_MDATA_UNITNID] = $iusGroups[$key][_IUS_UNIT_NID];

        $params['fields'] = [_MDATA_IUSNID,_MDATA_TIMEPERIODNID, _MDATA_AREANID, _MDATA_DATAVALUE, _MDATA_SOURCENID, _MDATA_FOOTNOTENID, _MDATA_DATA_DENOMINATOR, _MDATA_INDICATORNID, _MDATA_UNITNID, _MDATA_SUBGRPNID];
        $params['conditions'] = $conditions;
        $params['type'] = 'all';
        $params['extra'] = [];//['limit' => 20000];

        $dataDetails = $this->CommonInterface->serviceInterface('Data', 'getRecords', $params, $connect);

        if(!empty($dataDetails)) {

            $paramsIcius = ['fields' => [_ICIUS_IC_IUSNID, _ICIUS_IC_NID], 'conditions' => [_ICIUS_IUSNID => $dataDetails[0][_MDATA_IUSNID]], 'list'];
            $IcIusDetails = $this->CommonInterface->serviceInterface('IcIus', 'getRecords', $paramsIcius, $connect);

            $paramsIC = ['fields' => [_IC_IC_NID, _IC_IC_NAME], 'conditions' => [_IC_IC_NID . ' IN' => $IcIusDetails, _IC_IC_TYPE . ' <>' => 'SR']];
            $IcDetails = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getRecords', $paramsIC, $connect);

            if(!empty($IcDetails)) {
                $sector = $IcDetails[0][_IC_IC_NAME];
            } else {
                $sector = '';
            }

            // Rename sheet
            $rowCount = $startRows;

            $objWorkSheet->SetCellValue('B3', $sector); // Sector Name

            // get prepared data for all rows
            foreach($dataDetails as $dataDetail) {
                if(!isset($tp[$dataDetail[_MDATA_TIMEPERIODNID]])) {
                    continue;
                } else if(!isset($subgroupNameWithNid[$dataDetail[_MDATA_SUBGRPNID]])) {
                    continue;
                } else if(!isset($src[$dataDetail[_MDATA_SOURCENID]])) {
                    continue;
                } else if(!isset($footnote[$dataDetail[_MDATA_FOOTNOTENID]])) {
                    continue;
                }

                $objWorkSheet->SetCellValue('A'.$rowCount, $tp[$dataDetail[_MDATA_TIMEPERIODNID]]); // Time
                $objWorkSheet->SetCellValue('B'.$rowCount, $areaIdWithNid[$dataDetail[_MDATA_AREANID]]); // Area Id
                $objWorkSheet->SetCellValue('C'.$rowCount, $areaNameWithNid[$dataDetail[_MDATA_AREANID]]); // Area Name
                $objWorkSheet->SetCellValue('D'.$rowCount, $dataDetail[_MDATA_DATAVALUE]); // Data Value
                $objWorkSheet->SetCellValue('E'.$rowCount, $subgroupNameWithNid[$dataDetail[_MDATA_SUBGRPNID]]); // Subgroup
                $objWorkSheet->SetCellValue('F'.$rowCount, $src[$dataDetail[_MDATA_SOURCENID]]); // Source
                $objWorkSheet->SetCellValue('G'.$rowCount, $footnote[$dataDetail[_MDATA_FOOTNOTENID]]); // Footnote
                $objWorkSheet->SetCellValue('H'.$rowCount, $dataDetail[_MDATA_DATA_DENOMINATOR]); // Data Denominator
                $objWorkSheet->SetCellValue('L'.$rowCount, $subgroupGidWithNid[$dataDetail[_MDATA_SUBGRPNID]]); // Subgroup Gid
                $rowCount++;
            }

        }
        
        return $objWorkSheet;
    }
        
    /**
     * Process DES(Data Entry Spreadsheet) Export
     * 
     * @param array $areaNidArray Area Nids
     * @param array $timePeriodNidArray Timeperiod Nids
     * @param array $iusgidArray IUS Gids array
     * @param array $iusgidArray Any extra param
     * 
     * @return string export file path
     */
    public function exportDes($areaNidArray, $timePeriodNidArray, $iusgidArray, $extra = [])
    {
        //-- TRANSACTION Log - STARTED
        $LogId = $this->TransactionLogs->createLog(_EXPORT, _DATAENTRYVAL, _DES, '', _STARTED);
                    
        extract($extra);
        $conditions = $iusConditions = [];
        $startRows = 11;
        $connect = $dbConnection;
                
        if(!empty($iusgidArray)) {
            $iusNids = $this->CommonInterface->serviceInterface('Data', 'getIusOparands', ['iusArray' => $iusgidArray], $dbConnection);
            if(!empty($iusNids['iusNids'])) {
                $conditions[_MDATA_IUSNID . ' IN'] = $iusNids['iusNids'];
                $iusConditions[_IUS_IUSNID . ' IN'] = $iusNids['iusNids'];
            }
            $connect = '';
        }
        
        //$iusConditions[_IUS_DATA_EXISTS] = 1;
        $iusGroups = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getAllIUConcatinated', ['fields' => [_IUS_INDICATOR_NID,_IUS_UNIT_NID,_IUS_SUBGROUP_VAL_NID], 'conditions' => $iusConditions, 'extra' => ['group' => true]], $connect);
        $connect = '';
        
        if(!empty($iusGroups)) {
            
            $iuGroups = array_unique(array_column($iusGroups, 'concatinated'));
            
            // Area Access
            $areaAccess = $this->UserAccess->getAreaAccessToUser(['type' => 'list']);
            $areaAccessNIds = array_keys($areaAccess);
            if(!empty($areaAccessNIds)) $areaNidArray = array_intersect($areaNidArray, $areaAccessNIds);

            if(!empty($areaNidArray)) $conditions[_MDATA_AREANID . ' IN'] = $areaNidArray;
            if(!empty($timePeriodNidArray)) $conditions[_MDATA_TIMEPERIODNID . ' IN'] = $timePeriodNidArray;

            // --- Excel
            $objPHPExcel = $this->CommonInterface->readXlsOrCsv(_XLS_PATH_WEBROOT . DS . 'SAMPLE_DES.xls', false);
            
            //  Get the current sheet with all its newly-set style properties
            $objWorkSheetBase = $objPHPExcel->getSheet();
            
            // Remove current sheet(Data 1) as its preventing us from renaming
            $objPHPExcel->removeSheetByIndex(0);
            
            $dataAssociations = $this->CommonInterface->serviceInterface('Data', 'getDataAssociationsRecords', [[], ['getAllAreas' => true]], $connect);
            //extract($dataAssociations);
            //extract($area);
            $sheet = 1;
  
            foreach($iuGroups as $key => $iuGroup) {
                //  Create a clone of the current sheet, with all its style properties
                $objWorkSheet = clone $objWorkSheetBase;
                $objWorkSheet->setTitle('Data '.$sheet);
                
                // Fill sheet object with data
                $extra = ['key' => $key, 'startRows' => $startRows, 'iusGroups' => $iusGroups, 'connect' => $connect];
                $objWorkSheet = $this->getIuSheetObject($objWorkSheet, $dataAssociations, $conditions, $extra);
                
                //  Attach the newly-cloned sheet to the $objPHPExcel workbook
                $objPHPExcel->addSheet($objWorkSheet);
                
                $sheet++;
            }
        }
        
        // Write Title and Data to Excel
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $dbName = json_decode($dbConnection, true)['db_connection_name'];
        $dbName = str_replace(' ', '_', $dbName);
        $returnFilename = $dbName . '_' . 'DES' . '_' . date('Y-m-d-h-i-s') . '.xls';
        $returnFilePath = _DES_PATH_WEBROOT . DS . $returnFilename;
        $objWriter->save($returnFilePath);
        
        //-- TRANSACTION Log - SUCCESS
        $LogId = $this->TransactionLogs->createLog(_EXPORT, _DATAENTRYVAL, _DES, $returnFilename, _SUCCESS, $LogId);
        
        return $returnFilePath;
    }
    
}
