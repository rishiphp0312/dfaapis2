<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * De component
 */
class DeComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];    
    public $components = ['Common', 'DevInfoInterface.CommonInterface'];

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
    public function getIndicatorAndUnitGids($dataArray)
    {
        // Check If Indicator Exists
        if(isset($dataArray[5]) && isset($dataArray[5][1])) {
            
            // Check Gid
            if(isset($dataArray[5][11]) && !empty($dataArray[5][11])) {
                $check = 'gid';
                $conditions = [_INDICATOR_INDICATOR_GID => trim($dataArray[5][11])];
            } // Check Name
            else {
                $check = 'name';
                $conditions = [_INDICATOR_INDICATOR_NAME => trim($dataArray[5][1])];
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
        
        // Check If Unit Exists
        if (isset($dataArray[7]) && isset($dataArray[7][1])) {
            
            // Check Gid
            if(isset($dataArray[7][11]) && !empty($dataArray[7][11])) {
                $check = 'gid';
                $conditions = [_UNIT_UNIT_GID => trim($dataArray[7][11])];
            } // Check Name
            else {
                $check = 'name';
                $conditions = [_UNIT_UNIT_NAME => trim($dataArray[7][1])];
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
    public function prepareDesData($dataArray, $isJSON = false, $extra = [])
    {
        $startRow = isset($extra['startRow']) ? $extra['startRow'] : 1 ;
        
        foreach($dataArray as $row => $data) {
            if($row < $startRow) continue;
            
            // Get Gid if given
            $sGid = (!empty($data[11])) ? $data[11] : '';
            
            $preparedData[] = [
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
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $dataArray = [];
            
            // get data for all rows
            for ($row = $startRows; $row <= $highestRow; ++$row) {
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();

                    if ($row >= $startRows) {
                        $dataArray[$row][] = $val;
                    } else {
                        continue;
                    }
                }
            }
            
            if(!empty($dataArray)) {
                $result = $this->getIndicatorAndUnitGids($dataArray);
                
                if(isset($result['error'])) {
                    $unsettedSheet[$worksheetTitle] = $result['error'];
                } else {
                    $isJSON = true;
                    $extra = ['startRow' => 11, 'iGid' => $result['iGid'], 'uGid' => $result['uGid'], 'sheetName' => $worksheetTitle];
                    $preparedData = $this->prepareDesData($dataArray, $isJSON, $extra);
                    
                    $params = ['dbId' => $dbId, 'jsonData' => $preparedData, $validation = true, $customLog = true, $isDbLog = true];
                    $result = $this->CommonInterface->serviceInterface('Data', 'saveData', $params);
                    
                    if(!isset($startTime))
                        $startTime = $result['startTime'];
                    $endTime = $result['endTime'];
                    
                    $return['log'][$worksheetTitle] = $result;
                    debug($return);exit;
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
}
