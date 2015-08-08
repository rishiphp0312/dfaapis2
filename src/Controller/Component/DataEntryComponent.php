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
    public $components = ['Common', 'UserAccess', 'DevInfoInterface.CommonInterface'];

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
     * Get DES(Data Entry Spreadsheet) Structure
     * 
     * @param string $filename File to be processed
     * @param string $dbId Database Id
     * 
     * @return string Custom Log file path
     */
    public function getDesSheetStructure($dataArray = [], $extra = [])
    {
        //Get PHPExcel vendor
        /*require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);*/
        
        $objPHPExcel = $this->CommonInterface->readXlsOrCsv(_XLS_PATH_WEBROOT . DS . 'SAMPLE_DES.xls', false);
        //$objPHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . '1', _IMPORT_STATUS); // Title Row Status
        
        if(!empty($dataArray)) {
            // get prepared data for all rows
            for ($row = $startRows; $row <= $startRows + (count($dataArray)-1); ++$row) {
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
        }
        
        //Write Title and Data to Excel
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $dbName = json_decode($extra['dbConnection'], true)['db_connection_name'];
        $dbName = str_replace(' ', '_', $dbName);
        $returnFilename = $dbName . '_' . 'DES' . '_' . date('Y-m-d-h-i-s') . '.xls';
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment;filename=' . $returnFilename);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * Process DES(Data Entry Spreadsheet) Export
     * 
     * @param string $filename File to be processed
     * @param string $dbId Database Id
     * 
     * @return string Custom Log file path
     */
    public function exportDes($areaNidArray, $timePeriodNidArray, $iusgidArray, $extra = [])
    {
        extract($extra);
        $conditions = [];
        $startRows = 11;
        $connect = $dbConnection;
                
        if(!empty($iusgidArray)) {
            $iusNids = $this->CommonInterface->serviceInterface('Data', 'getIusOparands', ['iusArray' => $iusgidArray], $dbConnection);
            if(!empty($iusNids['iusNids'])) $conditions[_MDATA_IUSNID . ' IN'] = $iusNids['iusNids'];
            $connect = '';
        }
        
        // Area Access
        $areaAccess = $this->UserAccess->getAreaAccessToUser(['type' => 'list']);
        $areaAccessNIds = array_keys($areaAccess);
        $areaNidArray = array_intersect($areaNidArray, $areaAccessNIds);
        
        if(!empty($areaNidArray)) $conditions[_MDATA_AREANID . ' IN'] = $areaNidArray;
        if(!empty($timePeriodNidArray)) $conditions[_MDATA_TIMEPERIODNID . ' IN'] = $timePeriodNidArray;
        
        $params['fields'] = [_MDATA_TIMEPERIODNID, _MDATA_AREANID, _MDATA_DATAVALUE, _MDATA_SOURCENID, _MDATA_FOOTNOTENID, _MDATA_DATA_DENOMINATOR, _MDATA_INDICATORNID, _MDATA_UNITNID, _MDATA_SUBGRPNID];
        $params['conditions'] = $conditions;
        $params['type'] = 'all';
        $params['extra'] = ['limit' => 20000];
        $dataDetails = $this->CommonInterface->serviceInterface('Data', 'getRecords', $params, $connect);
        
        // --- Excel
        $objPHPExcel = $this->CommonInterface->readXlsOrCsv(_XLS_PATH_WEBROOT . DS . 'SAMPLE_DES.xls', false);
        //  Get the current sheet with all its newly-set style properties
        $objWorkSheetBase = $objPHPExcel->getSheet();
        
        //$this->getDesSheetStructure([], ['dbConnection' => $dbConnection]);
        if(!empty($dataDetails)) {
            
            $dataAssociations = $this->CommonInterface->serviceInterface('Data', 'getDataAssociationsRecords', [[], ['area' => true]]);
            extract($dataAssociations);
            $sheet = $rowCount = [];
            
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
                
                // Detect Sheet Index
                if(!in_array($dataDetail[_MDATA_INDICATORNID].','.$dataDetail[_MDATA_UNITNID], $sheet)) {
                    $sheet[] = $dataDetail[_MDATA_INDICATORNID].','.$dataDetail[_MDATA_UNITNID];
                    
                    if(count($sheet) > 1) {
                        //  Create a clone of the current sheet, with all its style properties
                        $objWorkSheet1 = clone $objWorkSheetBase;
                        $objWorkSheet1->setTitle('Data '.count($sheet));

                        //  Attach the newly-cloned sheet to the $objPHPExcel workbook
                        $objPHPExcel->addSheet($objWorkSheet1);
                        unset($objWorkSheet1);
                    }
                    
                    //$objPHPExcel->createSheet();
                    $sheetIndex = array_search($dataDetail[_MDATA_INDICATORNID].','.$dataDetail[_MDATA_UNITNID], $sheet);
                    // Rename sheet
                    //$objPHPExcel->getActiveSheet()->setTitle('Data '.count($sheet));
                    $rowCount[$sheetIndex] = $startRows;
                }
                $sheetIndex = array_search($dataDetail[_MDATA_INDICATORNID].','.$dataDetail[_MDATA_UNITNID], $sheet);
                $objPHPExcel->setActiveSheetIndex($sheetIndex);
                
                $objPHPExcel->getActiveSheet()->SetCellValue('B5', $indicatorNameWithNid[$dataDetail[_MDATA_INDICATORNID]]); // Indicator Name
                $objPHPExcel->getActiveSheet()->SetCellValue('L5', $indicatorGidWithNid[$dataDetail[_MDATA_INDICATORNID]]); // Indicator Gid
                $objPHPExcel->getActiveSheet()->SetCellValue('B7', $unitNameWithNid[$dataDetail[_MDATA_UNITNID]]); // Unit Name
                $objPHPExcel->getActiveSheet()->SetCellValue('L7', $unitGidWithNid[$dataDetail[_MDATA_UNITNID]]); // Unit Gid
                
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount[$sheetIndex], $tp[$dataDetail[_MDATA_TIMEPERIODNID]]); // Time
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount[$sheetIndex], $dataDetail[_MDATA_AREANID]); // Area Id
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount[$sheetIndex], $dataDetail[_MDATA_AREANID]); // Area Name
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount[$sheetIndex], $dataDetail[_MDATA_DATAVALUE]); // Data Value
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount[$sheetIndex], $subgroupNameWithNid[$dataDetail[_MDATA_SUBGRPNID]]); // Subgroup
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount[$sheetIndex], $src[$dataDetail[_MDATA_SOURCENID]]); // Source
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount[$sheetIndex], $footnote[$dataDetail[_MDATA_FOOTNOTENID]]); // Footnote
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount[$sheetIndex], $dataDetail[_MDATA_DATA_DENOMINATOR]); // Data Denominator
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount[$sheetIndex], $subgroupGidWithNid[$dataDetail[_MDATA_SUBGRPNID]]); // Subgroup Gid
                $rowCount[$sheetIndex]++;
            }
        }
        
        // Write Title and Data to Excel
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $dbName = json_decode($extra['dbConnection'], true)['db_connection_name'];
        $dbName = str_replace(' ', '_', $dbName);
        $returnFilename = $dbName . '_' . 'DES' . '_' . date('Y-m-d-h-i-s') . '.xls';
        $returnFilePath = _DES_PATH_WEBROOT . DS . $returnFilename;
        //header('Content-Type: application/vnd.ms-excel;');
        //header('Content-Disposition: attachment;filename=' . $returnFilename);
        //header('Cache-Control: max-age=0');
        //$objWriter->save('php://output');
        $objWriter->save($returnFilePath);
        return $returnFilePath;
        //exit;
    }
    
}
