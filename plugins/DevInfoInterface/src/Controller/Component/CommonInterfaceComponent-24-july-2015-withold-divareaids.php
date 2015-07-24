<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Collection\Collection;
use Cake\I18n\Time;

/**
 * CommonInterface Component
 */
class CommonInterfaceComponent extends Component {

//Loading Components

    public $components = [
        'Auth',
        'DevInfoInterface.Indicator',
        'DevInfoInterface.Unit',
        'DevInfoInterface.Timeperiod',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.SubgroupType',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Area',
        'DevInfoInterface.Data'
    ];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->arrayDepth = 1;
        $this->arrayDepthIterator = 1;
        $this->icDepth = 1;
        $this->conn;
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function setDbConnection($dbConnection) {

        $dbConnection = json_decode($dbConnection, true);
        $db_database = $dbConnection['db_database'];
        $db_source = $dbConnection['db_source'];
        $db_connection_name = $dbConnection['db_connection_name'];
        $db_password = $dbConnection['db_password'];

        $config = [
            'className' => 'Cake\Database\Connection',
            'persistent' => false,
            'host' => $dbConnection['db_host'],
            'port' => $dbConnection['db_port'],
            'username' => $dbConnection['db_login'],
            'password' => $db_password,
            'database' => $db_database,
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ];

        if (strtolower($db_source) == 'mysql') {
            $config['encoding'] = 'utf8';
            $config['driver'] = 'Cake\Database\Driver\Mysql';
        } else {
            $config['driver'] = 'Cake\Database\Driver\Sqlserver';
        }

        ConnectionManager::config('devInfoConnection', $config);

        $this->conn = ConnectionManager::get('devInfoConnection');
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function serviceInterface($component = NULL, $method = NULL, $params = null, $dbConnection = null) {
        if (!empty($dbConnection)) {
            $this->setDbConnection($dbConnection);
        }

        if ($component . 'Component' == (new \ReflectionClass($this))->getShortName()) {
            return call_user_func_array([$this, $method], $params);
        } else {
            return call_user_func_array([$this->{$component}, $method], $params);
        }
    }

    /**
     * Auto-Generates Random Guid
     * @return GUID
     */
    public function guid() {

        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12);
            return $uuid;
        }
    }

    /**
     * divideNameAndGids method    
     * 
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function divideNameAndGids($insertDataKeys = null, $insertDataArr = null, $extra = null) {
        $insertDataNames = [];
        $insertDataGids = [];
        foreach ($insertDataArr as $row => &$value) {
            $value = array_combine($insertDataKeys, $value);
            //We don't need this row if the name field is empty
            if (!isset($value[$insertDataKeys['name']])) {
                unset($value);
            } else if (!isset($value[$insertDataKeys['gid']])) {
            //Name found
                $insertDataNames[$row] = $value[$insertDataKeys['name']];
            } else {
                //GUID found
                $insertDataGids[$row] = $value[$insertDataKeys['gid']];
            }
        }

        $insertDataArr = array_filter($insertDataArr);
        return ['dataArray' => $insertDataArr, 'insertDataNames' => $insertDataNames, 'insertDataGids' => $insertDataGids];
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function nameGidLogic($loadDataFromXlsOrCsv = [], $component = null, $params = []) {
//Gives dataArray, insertDataNames, insertDataGids
        $this->bulkInsert($component, $loadDataFromXlsOrCsv, $params);
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function readXlsOrCsv($filename = null) {

//The following line should do the same like App::import() in the older version of cakePHP
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        $this->unlinkFiles($filename); // Delete The uplaoded file
        return $objPHPExcel;
    }

    /**
     * divideXlsOrCsvInChunkFiles method    
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function divideXlsOrCsvInChunkFiles($objPHPExcel = null, $extra = null) {
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;
        $filesArray = [];
        $titleRow = [];

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g. 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            $chunkParams = [
                'startRows' => $extra['startRows'],
                'limitRows' => $extra['limitRows'],
                'highestRow' => $highestRow,
                'highestColumn' => $highestColumn,
            ];
            $this->session->write('ChunkParams', $chunkParams);

            if ($extra['limitRows'] !== null) {
                $limitRows = $extra['limitRows'];
                $sheetCount = 1;
                if ($highestRow > ($limitRows + ($startRows - 1))) {
                    $sheetCount = ceil($highestRow - ($startRows - 1) / $limitRows);
                }
            } else {
                $limitRows = 0;
            }

            $PHPExcel = new \PHPExcel();
            $sheet = 1;

            for ($row = $startRows; $row <= $highestRow; ++$row) {

                $endrows = $limitRows + ($startRows - 1);
                $character = 'A';

                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if ($sheet > 1) {
                        $currentRow = ($row - (($sheet - 1) * $limitRows)) + 1;
                    } else {
                        $currentRow = $row - (($sheet - 1) * $limitRows);
                    }

                    if ($row == 1) {
                        $titleRow[$character . $currentRow] = $val;
                    }

                    $PHPExcel->getActiveSheet()->SetCellValue($character . $currentRow, $val);
                    $character++;
                }

                if (($row == $endrows) || ($row == $highestRow)) {
                    $PHPExcel->setActiveSheetIndex(0);
                    $objWriter = new \PHPExcel_Writer_Excel2007($PHPExcel);
                    $sheetPath = _CHUNKS_PATH . DS . time() . $sheet . '.xls';
                    $objWriter->save($sheetPath);
                    $filesArray[] = $sheetPath;
                    $PHPExcel = new \PHPExcel();
                    foreach ($titleRow as $titleRowKey => $titleRowVal) {
                        $PHPExcel->getActiveSheet()->SetCellValue($titleRowKey, $titleRowVal);
                    }
                    $startRows += $limitRows;
                    $sheet++;
                }
            }
        }

        return $filesArray;
    }

    /*
     * 
     * getAreaDetails returns array on passsed input 
     */

    public function getAreaDetails($input = null, $type = 'all') {

        $fields = [_AREA_AREA_NID, _AREA_AREA_ID, _AREA_AREA_GID];
        $conditions = array();
        $conditions = [_AREA_AREA_ID . ' IN ' => $input];
        return $areaDetails = $this->Area->getDataByParams($fields, $conditions, $type);
    }

    /*
     * 
     * resetChunkAreaData removes the first title row from chunk
     * @$data is the data array 
     */

    public function resetChunkAreaData($data) {
        $limitedRows = [];
        foreach ($data as $index => $valueArray) {
            if ($index == 1) {
                unset($valueArray);
            }if ($index > 1) {
                foreach ($valueArray as $innerIndex => $innervalueArray) {
                    if ($innerIndex > 4)
                        break;
                    $limitedRows[$index][$innerIndex] = $innervalueArray;
                    unset($innervalueArray);
                }
            }

            unset($valueArray);
        }

        return $limitedRows;
    }

    

    /*
     *  processAreadata to insert update area records after readfing chunk file
     *  $insertDataKeys  array for keys of insertDataArr work as table columns 
     *  
    */
    
    public function processAreadata($insertDataKeys = null, $insertDataArr = null, $extra = null) {
        $chunkFilename = basename($extra['chunkFilename']);
        $insertDataAreaIds = [];
        $insertDataAreaParentids = [];

        $areaidswithparentid = [];
        $compareAreaidDParentId = [];
        $allAreaParents = [];
        $parentchkAreaId = [];
        $getAllExcelAreaids = [];
        $filteredRowsArray = $this->resetChunkAreaData($insertDataArr);     //reset records   
        $newinsertDataArr = $filteredRowsArray;
        $compareAreaidParentId = $filteredRowsArray;
        $insertDataArr = $filteredRowsArray;

// get parent ids which exist in db 
        foreach ($insertDataArr as $row => &$value) {

            $value = array_combine($insertDataKeys, $value);
            $value = array_filter($value);
            if (array_key_exists('areaid', $insertDataKeys) && !isset($value[$insertDataKeys['areaid']])) {
                unset($value); //unset($newcats); //removing unnecesaary row 
            } else if (isset($value[$insertDataKeys['areaid']])) {
                $getAllExcelAreaids[$row] = $value[$insertDataKeys['areaid']];
                if (!empty($value[$insertDataKeys['parentnid']]))
                    $insertDataAreaParentids[$row] = $value[$insertDataKeys['parentnid']];
            }
        }


        $insertDataAreaParentids = array_unique($insertDataAreaParentids);
        /* uncomment later 
          $fields = [_AREA_AREA_NID, _AREA_AREA_ID];
          $conditions = array();
          $conditions = [_AREA_AREA_ID . ' IN ' => $insertDataAreaParentids];
          $areaidswithparentid = $this->Area->getDataByParams($fields, $conditions, 'list');
         */
        //getting list which parentnids exists in db  
        $areaidswithparentid = $this->getAreaDetails($insertDataAreaParentids, 'list');

        /*
          uncomment later
          $conditions = [];
          $conditions = [_AREA_AREA_ID . ' IN ' => $getAllExcelAreaids];
          $getAllDbAreaIds = $this->Area->getDataByParams($fields, $conditions, 'list');
         */

        //get all aread ids exists in db already
        $getAllDbAreaIds = $this->getAreaDetails($insertDataAreaParentids, 'list');

        $conditions = [];
        $conditions = [_AREA_AREA_ID . ' IN ' => $getAllExcelAreaids];
        $fields = [_AREA_AREA_ID, _AREA_AREA_GID];
        $getAllDbAreaGIds = $this->Area->getDataByParams($fields, $conditions, 'list'); //get all aread gids exists in db already 
        // areaidswithparentid contains  parent ids which exist in db 


        if (isset($newinsertDataArr) && !empty($newinsertDataArr)) {
            $finalareaids = [];
            $chkuniqueAreaids = [];
            $ignoreAreaIdsAsSubParent = [];
            $forParentAreaId = [];
            $storePassedAreaIds = [];
            $allAreaIdsAsSubParent = [];

            foreach ($newinsertDataArr as $row => &$value) {
                $areaidAlradyexistStatus = false;
                $allAreblank = false;
                $value = array_combine($insertDataKeys, $value);
                $value = array_filter($value);
                $areaNid = '';

                if (empty($value)) {
                    $allAreblank = true;
                }

                if (array_key_exists('areaid', $insertDataKeys) && (!isset($value[$insertDataKeys['areaid']]) || empty($value[$insertDataKeys['areaid']]) )) {
//case 1 when ignore if area id is blank
                    if ($allAreblank == false) {
                        $errorLogArray[$chunkFilename][$row] = $value;
                        $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                        $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT1; //area id empty 
                    }


                    unset($value);
                    unset($newinsertDataArr[$row]);
                } else if (isset($value[$insertDataKeys['areaid']]) && !empty($value[$insertDataKeys['areaid']])) {
                // all cases inside this when area id is not empty 
                    $excelAreaId = $value[$insertDataKeys['areaid']];
                    $desc = '';
                    if (!empty($storePassedAreaIds) && in_array($excelAreaId, $storePassedAreaIds) == true) {
                    //foreach($storePassedAreaIds as $storedValue){
                    //if($excelAreaId==$storedValue)
                        $areaidAlradyexistStatus = true;
                    // }
                        // echo $excelAreaId;
                        // pr($storePassedAreaIds);
                    }
                    $storePassedAreaIds[$row] = $excelAreaId;

                    if (array_key_exists($insertDataKeys['parentnid'], $value) && !empty($value[$insertDataKeys['parentnid']]) && $value[$insertDataKeys['parentnid']] != _AREAPARENT_ID && in_array($value[$insertDataKeys['parentnid']], $areaidswithparentid) == true) {
                    //case when parent id is not empty and exists in database also 
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                        }
                        if (!array_key_exists($insertDataKeys['level'], $value)) {
                            $level = '';
                        } else {
                            $level = $value[$insertDataKeys['level']];
                        }
                    //returns area level and and any warning if exists 
                        $levelDetails = $this->Area->returnAreaLevel($level, $value[$insertDataKeys['parentnid']], $row);
                        $value[$insertDataKeys['level']] = $levelDetails['level'];
                        $value[$insertDataKeys['parentnid']] = array_search($value[$insertDataKeys['parentnid']], $areaidswithparentid);

                        $conditions = [];
                        $fields = [];
                        $areadbdetails = '';

                        if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) { //when areaid in db 
                        // update data here 
                            $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // 
                        //$value[_AREA_AREA_NID] = $areaNid;
                            if (empty($getAllDbAreaGIds[$excelAreaId])) //check gid in db is empty or not 
                                $value[_AREA_AREA_GID] = $this->guid();
                        //$desc= 'update getAllDbAreaIds '.$excelAreaId ;		
                        }else {
                            $conditions = [];
                            $fields = [];
                            $areadbdetails = '';

                            $conditions = [_AREA_AREA_ID => $excelAreaId];
                            $fields = [_AREA_AREA_ID, _AREA_AREA_NID, _AREA_AREA_GID];

                            $chkAreaId = $this->Area->getDataByParams($fields, $conditions);
                            if (!empty($chkAreaId)) {
                                $areadbdetails = current($chkAreaId);
                                $areaNid = $areadbdetails[_AREA_AREA_NID];
                                if (empty($areadbdetails[_AREA_AREA_GID])) //check gid in db is empty or not 
                                    $value[_AREA_AREA_GID] = $this->guid();
                                    //$desc= 'update chkAreaId '.$excelAreaId ;		
                            }else {
                                    //$desc= 'insert  '.$excelAreaId ;		
                                    // insert if new entry
                                $returnid = '';
                                if (empty($value[$insertDataKeys['gid']])) {
                                    $value[$insertDataKeys['gid']] = $this->guid();
                                }
                                if (!array_key_exists($insertDataKeys['gid'], $value)) {
                                    $value[$insertDataKeys['gid']] = $this->guid();
                                }
                                if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                                    $value[_AREA_AREA_GLOBAL] = '0';
                                }
                            }
                        }
                        if ($areaidAlradyexistStatus == false) {
                            if (!empty($areaNid)) {
                                // unset($value[_AREA_AREA_NID]);
                                $returnid = $this->Area->customUpdate($value, [_AREA_AREA_NID => $areaNid]);//only update  case handled here 
                            } else {
                                $returnid = $this->Area->insertUpdateAreaData($value);//only insert case handled here 
                            }
                            //$returnid = $this->Area->insertUpdateAreaData($value); //insert new entry 
                            if ($returnid) {// insert sucess 
                                if ($allAreblank == false) {
                                    if ($levelDetails['error'] == true) {
                                        $errorLogArray[$chunkFilename][$row][_STATUS] = _WARN;
                                        $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT5;
                                    } else {
                                        $errorLogArray[$chunkFilename][$row][_STATUS] = _OK;
                                        $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = $desc;
                                    }
                                }
                            } else { // insert failed 
                                if ($allAreblank == false) {
                                    $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                                    $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT2;
                                }
                            }
                        } else {
                            if ($allAreblank == false) {
                             $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                             $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT6; //duplicaate case
                            }
                        }

                    //$areadbdetails = current(current($chkAreaId));
                    } elseif (!empty($value[$insertDataKeys['parentnid']]) && ($value[$insertDataKeys['parentnid']] != _AREAPARENT_ID) && in_array($value[$insertDataKeys['parentnid']], $areaidswithparentid) == false) {

                    //case when parent id is not empty and do not exists in database  
                        if ($allAreblank == false) {

                            $errorLogArray[$chunkFilename][$row] = $value;
                        }

                        //  get parent details 
                        $parentconditions = [];
                        $parentfields = [];
                        $parentchkAreaId = [];
                        $parentareadbdetails = '';

                        $parentconditions = [_AREA_AREA_ID => $value[$insertDataKeys['parentnid']]];
                        $parentfields = [_AREA_AREA_NID];
                        $parentchkAreaId = $this->Area->getDataByParams($parentfields, $parentconditions);

                            //check parent id exists in db or not 
                        if (!empty($parentchkAreaId))
                            $parentareadbdetails = current(current($parentchkAreaId));


                        if (!array_key_exists($insertDataKeys['level'], $value)) {
                            $level = '';
                        } else {
                            $level = $value[$insertDataKeys['level']];
                        }
                        $levelDetails = $this->Area->returnAreaLevel($level, $value[$insertDataKeys['parentnid']], $row);
                        $value[$insertDataKeys['level']] = $levelDetails['level'];

                        if (!empty($parentareadbdetails)) { //when areaid in db and  parent also exists due to loop insertion  
                            if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) {

                                $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // $key = 2;
                                //$value[_AREA_AREA_NID] = $areaNid;
                                if (empty($getAllDbAreaGIds[$excelAreaId])) //check gid in db is empty or not 
                                    $value[_AREA_AREA_GID] = $this->guid();
                                    //$desc= 'update getAllDbAreaIds '.$excelAreaId ;		
                            }else {

                                $conditions = [];
                                $fields = [];
                                $areadbdetails = '';
                                
                                $conditions = [_AREA_AREA_ID => $excelAreaId];
                                $fields = [_AREA_AREA_ID, _AREA_AREA_NID, _AREA_AREA_GID];

                                $chkAreaId = $this->Area->getDataByParams($fields, $conditions);
                                if (!empty($chkAreaId)) {
                                    $areadbdetails = current($chkAreaId);
                                    $areaNid = $areadbdetails[_AREA_AREA_NID];
                                    if (empty($areadbdetails[_AREA_AREA_GID])) //check gid in db is empty or not 
                                        $value[_AREA_AREA_GID] = $this->guid();
                                    //$desc= 'update chkAreaId '.$excelAreaId ;		
                                }else {
                                        // insert case 
                                    if (empty($value[$insertDataKeys['gid']])) {
                                        $value[$insertDataKeys['gid']] = $this->guid();
                                    }
                                    if (!array_key_exists($insertDataKeys['gid'], $value)) {
                                        $value[$insertDataKeys['gid']] = $this->guid();
                                    }
                                    if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                                        $value[_AREA_AREA_GLOBAL] = '0';
                                    }
                                    //$desc= 'insert chkAreaId '.$excelAreaId ;	
                                }

//
                            }
                            $value[$insertDataKeys['parentnid']] = $parentareadbdetails;
                            if ($areaidAlradyexistStatus == false) {
                                if (!empty($areaNid)) {
                                    // unset($value[_AREA_AREA_NID]);
                                    $returnid = $this->Area->customUpdate($value, [_AREA_AREA_NID => $areaNid]);
                                } else {
                                    $returnid = $this->Area->insertUpdateAreaData($value);
                                }
//$returnid = $this->Area->insertUpdateAreaData($value); //insert new entry 
                                if ($returnid) {
                                    if ($allAreblank == false) {
                                        if ($levelDetails['error'] == true) {
                                            $errorLogArray[$chunkFilename][$row][_STATUS] = _WARN;
                                            $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT5;
                                        } else {
                                            $errorLogArray[$chunkFilename][$row][_STATUS] = _OK;
                                            $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = $desc;
                                        }
                                    }
                                } else {
                                    if ($allAreblank == false) {
                                        $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                                        $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT2;
                                    }
                                }
                            } else {
                                if ($allAreblank == false) {
                                    $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                                    $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT6;
                                }
                            }
                        } else {
// when  parent id dont  exists 
                            if ($allAreblank == false) {
                                $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                                $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT3; //parent id not found 
                            }
                        }
                    }//case 3 starts here 
                    elseif (empty($value[$insertDataKeys['parentnid']]) || ($value[$insertDataKeys['parentnid']] == _AREAPARENT_ID)) {
//case when parent nid is empty 
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                        }

                        if (!array_key_exists($insertDataKeys['level'], $value)) {
                            $level = '';
                        } else {
                            $level = $value[$insertDataKeys['level']];
                        }
                        $levelDetails = $this->Area->returnAreaLevel($level, _AREAPARENT_ID, $row);

                        $value[$insertDataKeys['parentnid']] = _AREAPARENT_ID; // value is -1
                        $value[$insertDataKeys['level']] = $levelDetails['level']; // do hardcore level value 1 for parent area ids 						

                        $conditions = [];
                        $fields = [];
                        $areadbdetails = '';
//
//pr($getAllDbAreaIds); pr($excelAreaId);
                        if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) { //when areaid in db 
// update data here 
                            $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // 
//die('hua');

                            if (empty($getAllDbAreaGIds[$excelAreaId])) //check gid in db is empty or not 
                                $value[_AREA_AREA_GID] = $this->guid();
//$desc= 'update getAllDbAreaIds '.$excelAreaId ;	
                        }else {

                            $conditions = [_AREA_AREA_ID => $excelAreaId];
                            $fields = [_AREA_AREA_ID, _AREA_AREA_NID, _AREA_AREA_GID];

                            $chkAreaId = $this->Area->getDataByParams($fields, $conditions);
//pr($chkAreaId);

                            if (!empty($chkAreaId)) {
                                $areadbdetails = current($chkAreaId);
                                $areaNid = $areadbdetails[_AREA_AREA_NID];
                                if (empty($areadbdetails[_AREA_AREA_GID])) //check gid in db is empty or not 
                                    $value[_AREA_AREA_GID] = $this->guid();

//$desc= 'update chkAreaId '.$excelAreaId ;	
                            }else {

                                $returnid = '';
//$desc= 'insert chkAreaId '.$excelAreaId ;	

                                if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                                    $value[_AREA_AREA_GLOBAL] = '0';
                                }
                                if (empty($value[$insertDataKeys['gid']])) {
                                    $value[$insertDataKeys['gid']] = $this->guid();
                                }
                                if (!array_key_exists($insertDataKeys['gid'], $value)) {
                                    $value[$insertDataKeys['gid']] = $this->guid();
                                }
//die('hua nahi kabse ');
                            }
                        }
///
//  if($excelAreaId='raj2'){
// echo $excelAreaId; pr($storePassedAreaIds);
//echo 'area--'.$areaidAlradyexistStatus;
//die;
// }
                        if ($areaidAlradyexistStatus == false) {
// pr($value);echo  $AREA_AREA_NID; 
// $conn = ConnectionManager::get('my_connection');
                            if (!empty($areaNid)) {
// unset($value[_AREA_AREA_NID]);
                                $returnid = $this->Area->customUpdate($value, [_AREA_AREA_NID => $areaNid]);
                            } else {
                                $returnid = $this->Area->insertUpdateAreaData($value);
                            }


                            if ($returnid) {
                                if ($allAreblank == false) {
                                    if ($levelDetails['error'] == true) {
                                        $errorLogArray[$chunkFilename][$row][_STATUS] = _WARN;
                                        $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT5;
                                    } else {
                                        $errorLogArray[$chunkFilename][$row][_STATUS] = _OK;
                                        $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = $desc;
                                    }
                                }
                            } else {
                                if ($allAreblank == false) {
                                    $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                                    $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT2;
                                }
                            }
                        } else {
                            if ($allAreblank == false) {
                                $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                                $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT6;
                            }
                        }
                    } else {
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                            $errorLogArray[$chunkFilename][$row][_STATUS] = _FAILED;
                            $errorLogArray[$chunkFilename][$row][_DESCRIPTION] = _AREA_LOGCOMMENT4; //invalid details 
                        }
                    }
                }// end of if of area id exists 
            }
        }
//pr($storePassedAreaIds);die;
//pr($errorLogArray);
        $newinsertDataArr = [];
        $insertDataAreaids = [];


        return ['dataArray' => $newinsertDataArr, 'insertDataAreaids' => $insertDataAreaids, 'errorLogArray' => $errorLogArray];
    }

    /**
     * divideAreaids method
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function divideAreaids_old($insertDataKeys = null, $insertDataArr = null, $extra = null) {
        $chunkFilename = $extra['chunkFilename'];
        $insertDataAreaIds = [];
        $insertDataAreaParentids = [];
        $blnkParentidsAreaids = [];
        $areaidswithparentid = [];
        $limitedRows = [];
        $compareAreaidDParentId = [];
        $allAreaParents = [];
        $parentchkAreaId = [];
//$compareAreaidDParentId =[];
// resetting passed array 
        foreach ($insertDataArr as $index => $valueArray) {
            if ($index == 1) {
                unset($valueArray);
            }if ($index > 1) {
                foreach ($valueArray as $innerIndex => $innervalueArray) {
                    if ($innerIndex > 4)
                        break;
                    $limitedRows[$index][$innerIndex] = $innervalueArray;
                    unset($innervalueArray);
                }
            }
            unset($valueArray);
        }


        $newinsertDataArr = $limitedRows;
        $compareAreaidParentId = $limitedRows;
        $errorLogArray = [];

        $insertDataArr = $limitedRows;
// loop to get all parent nids 
        foreach ($insertDataArr as $row => &$value) {

            $value = array_combine($insertDataKeys, $value);
            $value = array_filter($value);
//$value[_AREA_AREA_GLOBAL]=0;

            if (array_key_exists('areaid', $insertDataKeys) && !isset($value[$insertDataKeys['areaid']])) {
                unset($value); //unset($newcats); //removing unnecesaary row 
            } else if (isset($value[$insertDataKeys['areaid']])) {
                if (!empty($value[$insertDataKeys['parentnid']]))
                    $insertDataAreaParentids[$row] = $value[$insertDataKeys['parentnid']];
            }
        }

        $insertDataAreaParentids = array_unique($insertDataAreaParentids);
        $fields = [_AREA_AREA_NID, _AREA_AREA_ID];
        $conditions = array();
        $conditions = [_AREA_AREA_ID . ' IN ' => $insertDataAreaParentids];
        $areaidswithparentid = $this->Area->getDataByParams($fields, $conditions, 'list'); //getting database exists parentnids 

        if (isset($newinsertDataArr) && !empty($newinsertDataArr)) {
            $finalareaids = [];
            $chkuniqueAreaids = [];
            $ignoreAreaIdsAsSubParent = [];
            $forParentAreaId = [];
            $allAreaIdsAsSubParent = [];
            foreach ($newinsertDataArr as $row => &$value) {

                $allAreblank = false;
                $value = array_combine($insertDataKeys, $value);
                $value = array_filter($value);

                if (empty($value)) {
                    $allAreblank = true;
                }

                if (array_key_exists('areaid', $insertDataKeys) && (!isset($value[$insertDataKeys['areaid']]) || empty($value[$insertDataKeys['areaid']]) )) {
//ignore if area id is blank
                    if ($allAreblank == false) {
                        $_SESSION['errorLog'][] = $value;
                        $_SESSION['errorLog'][_STATUS][] = _FAILED;
                        $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT1; //area id empty 
                    }


                    unset($value);
                    unset($newinsertDataArr[$row]);
                } else if (isset($value[$insertDataKeys['areaid']]) && !empty($value[$insertDataKeys['areaid']])) {
//pr($areaidswithparentid);
                    if (array_key_exists($insertDataKeys['parentnid'], $value) && !empty($value[$insertDataKeys['parentnid']]) && $value[$insertDataKeys['parentnid']] != '-1' && in_array($value[$insertDataKeys['parentnid']], $areaidswithparentid) == true) {
//case when parent id is not empty and exists in database also 
                        if ($allAreblank == false) {
                            $_SESSION['errorLog'][] = $errorLogArray[$row] = $value;
                        }
                        if (!array_key_exists($insertDataKeys['level'], $value)) {
                            $level = '';
                        } else {
                            $level = $value[$insertDataKeys['level']];
                        }
                        $levelDetails = $this->Area->returnAreaLevel($level, $value[$insertDataKeys['parentnid']], $row);
                        $value[$insertDataKeys['level']] = $levelDetails['level'];
                        $value[$insertDataKeys['parentnid']] = array_search($value[$insertDataKeys['parentnid']], $areaidswithparentid);


                        $conditions = [];
                        $fields = [];
                        $areadbdetails = '';

                        $conditions = [_AREA_AREA_ID => $value[$insertDataKeys['areaid']]];
                        $fields = [_AREA_AREA_ID];
                        $chkAreaId = $this->Area->getDataByParams($fields, $conditions);
                        if (!empty($chkAreaId))
                            $areadbdetails = current(current($chkAreaId));

                        if ($areadbdetails != '') {
                            $insertDataAreaIds[$row] = $value[$insertDataKeys['areaid']]; // will be needed for  update
                            if ($allAreblank == false) {
                                if ($levelDetails['error'] == true) {
                                    $_SESSION['errorLog'][_STATUS][] = _WARN;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT5;
                                } else {
                                    $_SESSION['errorLog'][_STATUS][] = _OK;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = '';
                                }
                            }
                        } else {

                            $returnid = '';

                            if (empty($value[$insertDataKeys['gid']])) {
                                $value[$insertDataKeys['gid']] = $this->guid();
                            }
                            if (!array_key_exists($insertDataKeys['gid'], $value)) {
                                $value[$insertDataKeys['gid']] = $this->guid();
                            }
                            if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {

                                $value[_AREA_AREA_GLOBAL] = '0';
                            }

                            $returnid = $this->Area->insertUpdateAreaData($value);
                            if ($returnid) {
                                if ($allAreblank == false) {
                                    $_SESSION['errorLog'][_STATUS][] = _OK;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = '';
                                }
                            } else {
                                $_SESSION['errorLog'][_STATUS][] = _FAILED;
                                $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT2;
                            }
                        }
                    } elseif (!empty($value[$insertDataKeys['parentnid']]) && ($value[$insertDataKeys['parentnid']] != '-1') && in_array($value[$insertDataKeys['parentnid']], $areaidswithparentid) == false) {

//case when parent id is not empty and do not exists in database  
                        if ($allAreblank == false) {

                            $_SESSION['errorLog'][] = $value;
                        }

                        $conditions = [];
                        $fields = [];
                        $chkAreaId = [];
                        $areadbdetails = [];

                        $conditions = [_AREA_AREA_ID => $value[$insertDataKeys['areaid']]];
                        $fields = [_AREA_AREA_ID];
                        $chkAreaId = $this->Area->getDataByParams($fields, $conditions);
                        if (!empty($chkAreaId))
                            $areadbdetails = current(current($chkAreaId));

                        $parentconditions = [];
                        $parentfields = [];
                        $parentchkAreaId = [];
                        $parentareadbdetails = '';

                        $parentconditions = [_AREA_AREA_ID => $value[$insertDataKeys['parentnid']]];
                        $parentfields = [_AREA_AREA_NID];
                        $parentchkAreaId = $this->Area->getDataByParams($parentfields, $parentconditions);

                        if (!array_key_exists($insertDataKeys['level'], $value)) {
                            $level = '';
                        } else {
                            $level = $value[$insertDataKeys['level']];
                        }
                        $levelDetails = $this->Area->returnAreaLevel($level, $value[$insertDataKeys['parentnid']], $row);
//pr($levelDetails);
//pr($value);

                        $value[$insertDataKeys['level']] = $levelDetails['level'];

//check parent id exists in db or not 
                        if (!empty($parentchkAreaId))
                            $parentareadbdetails = current(current($parentchkAreaId));

                        if ($areadbdetails != '') {    //already exist area
                            $insertDataAreaIds[$row] = $value[$insertDataKeys['areaid']]; // will be needed for  update
                            if ($allAreblank == false) {
                                if ($levelDetails['error'] == true) {
                                    $_SESSION['errorLog'][_STATUS][] = _WARN;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT5;
                                } else {
                                    $_SESSION['errorLog'][_STATUS][] = _OK;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = '';
                                }
                            }
                        } else {
                            if ($parentareadbdetails != '') {
//if parent id found do insert 
                                $returnid = '';

                                $value[$insertDataKeys['parentnid']] = $parentareadbdetails;

                                if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                                    $value[_AREA_AREA_GLOBAL] = '0';
                                }
                                if (empty($value[$insertDataKeys['gid']])) {
                                    $value[$insertDataKeys['gid']] = $this->guid();
                                }
                                if (!array_key_exists($insertDataKeys['gid'], $value)) {
                                    $value[$insertDataKeys['gid']] = $this->guid();
                                }
                                $returnid = $this->Area->insertUpdateAreaData($value);
                                if ($returnid) {
                                    if ($allAreblank == false) {
                                        $_SESSION['errorLog'][_STATUS][] = _OK;
                                        $_SESSION['errorLog'][_DESCRIPTION][] = '';
                                    }
                                } else {
                                    $_SESSION['errorLog'][_STATUS][] = _FAILED;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT2;
                                }
                            } else {
// error if parent id not found
                                if ($allAreblank == false) {
                                    $_SESSION['errorLog'][_STATUS][] = _FAILED;
                                    $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT3; //parent id not found 
                                }
                            }
                        }
                    } elseif (empty($value[$insertDataKeys['parentnid']]) || ($value[$insertDataKeys['parentnid']] == '-1')) {
//case when parent id is empty 
                        if ($allAreblank == false) {
                            $_SESSION['errorLog'][] = $value;
                        }

                        if (!array_key_exists($insertDataKeys['level'], $value)) {
                            $level = '';
                        } else {
                            $level = $value[$insertDataKeys['level']];
                        }
                        $levelDetails = $this->Area->returnAreaLevel($level, '-1', $row);

                        $value[$insertDataKeys['parentnid']] = '-1';
                        $value[$insertDataKeys['level']] = $levelDetails['level']; // do hardcore level value 1 for parent area ids 						

                        $conditions = [];
                        $fields = [];
                        $areadbdetails = '';

                        $conditions = [_AREA_AREA_ID => $value[$insertDataKeys['areaid']]];
                        $fields = [_AREA_AREA_ID];
                        $chkAreaId = $this->Area->getDataByParams($fields, $conditions);
                        if (!empty($chkAreaId))
                            $areadbdetails = current(current($chkAreaId));

                        if ($areadbdetails != '') {
                            $insertDataAreaIds[$row] = $value[$insertDataKeys['areaid']]; // will be needed for  update
                            if ($allAreblank == false) {
                                $_SESSION['errorLog'][_STATUS][] = _OK;
                                $_SESSION['errorLog'][_DESCRIPTION][] = '';
                            }
                        } else {
                            $returnid = '';

                            if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                                $value[_AREA_AREA_GLOBAL] = '0';
                            }
                            if (empty($value[$insertDataKeys['gid']])) {
                                $value[$insertDataKeys['gid']] = $this->guid();
                            }
                            if (!array_key_exists($insertDataKeys['gid'], $value)) {
                                $value[$insertDataKeys['gid']] = $this->guid();
                            }

                            $returnid = $this->Area->insertUpdateAreaData($value);
                            if ($returnid) {
                                if ($allAreblank == false) {
                                    if ($levelDetails['error'] == true) {
                                        $_SESSION['errorLog'][_STATUS][] = _WARN;
                                        $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT5;
                                    } else {
                                        $_SESSION['errorLog'][_STATUS][] = _OK;
                                        $_SESSION['errorLog'][_DESCRIPTION][] = '';
                                    }
                                }
                            } else {
                                $_SESSION['errorLog'][_STATUS][] = _FAILED;
                                $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT2;
                            }
                        }
                    } else {
                        if ($allAreblank == false) {
                            $_SESSION['errorLog'][] = $value;
                            $_SESSION['errorLog'][_STATUS][] = _FAILED;
                            $_SESSION['errorLog'][_DESCRIPTION][] = _AREA_LOGCOMMENT4; //invalid details 
                        }

                        unset($value);
                        unset($newinsertDataArr[$row]);
                    }
                } // end of isset of area id 
            }// for loop
        }
        $newinsertDataArr = array_filter($newinsertDataArr);



        return ['dataArray' => $newinsertDataArr, 'insertDataAreaIds' => $insertDataAreaIds];
    }

    /**
     * prepareDataFromXlsOrCsv method
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function prepareDataFromXlsOrCsv($filename = null, $insertDataKeys = null, $extra = null) {
        
        $insertDataArr = [];
        $insertDataNames = [];
        $insertDataGids = [];
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;

        $objPHPExcel = $this->readXlsOrCsv($filename);
        
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);


            for ($row = $startRows; $row <= $highestRow; ++$row) {

                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if ($row >= $startRows) {  //-- Data Strats from row 2 --//                      
                        $insertDataArr[$row][] = $val;
                    } else {
                        continue;
                    }
                }
            }
        }

        return $divideNameAndGids = $this->splitInsertUpdate($insertDataKeys, $insertDataArr, $extra);
    }

    /*
     * splitInsertUpdate is the function to check which element has to execute
     * $extra['callfunction'] is the parameter if its Area it will execute for area 
     */

    function splitInsertUpdate($insertDataKeys, $insertDataArr, $extra) {
        if (array_key_exists('callfunction', $extra) && $extra['callfunction'] == 'Area') {
            return $this->processAreadata($insertDataKeys, $insertDataArr, $extra); 
        } else {
            return $this->divideNameAndGids($insertDataKeys, $insertDataArr);
        }
    }

    /**
     * 
     * bulkUploadXlsOrCsv
     * 
     * @param string $filename bulk file
     * @param string $component Component name for bulk import
     * @param array $extraParam Any extra parameter
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function bulkUploadXlsOrCsv($filename = null, $component = null, $extraParam = []) {

        $objPHPExcel = $this->readXlsOrCsv($filename);
        extract($extraParam);
        $extra = [];
        $extra['limitRows'] = 1000; // Number of rows in each file chunks
        $extra['startRows'] = 1; // Row from where the data reading starts
        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunkFiles($objPHPExcel, $extra);

        if ($component == 'Indicator') {    //Bulk upload - Indicator
            $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'highIsGood' => _INDICATOR_HIGHISGOOD];
            $params['nid'] = _INDICATOR_INDICATOR_NID;
        } else if ($component == 'Unit') {  //Bulk upload - Unit
            $insertDataKeys = ['name' => _UNIT_UNIT_NAME, 'gid' => _UNIT_UNIT_GID];
            $params['nid'] = _UNIT_UNIT_NID;
        } else if ($component == 'Icius') {  //Bulk upload - ICIUS
            return $this->bulkUploadIcius($divideXlsOrCsvInChunks, $extra);
        } else if ($component == 'Area') {  //Bulk upload - ICIUS
            return $this->bulkUploadXlsOrCsvForArea($divideXlsOrCsvInChunks, $extra, $objPHPExcel);
        }
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;

// Bulk upload each chunk separately
        foreach ($divideXlsOrCsvInChunks as $filename) {
//$extra['chunkFilename']=$filename;
            $loadDataFromXlsOrCsv = $this->prepareDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
            $this->nameGidLogic($loadDataFromXlsOrCsv, $component, $params);
            $this->unlinkFiles($filename);
        }
    }

    /**
     * 
     * bulkInsert
     * 
     * @param string $component Name of the component to call
     * @param array $loadDataFromXlsOrCsv names,gids data arrays
     * @param array $params Any extra parameters
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function bulkInsert($component = null, $loadDataFromXlsOrCsv = [], $params = null) {
//Gives dataArray, insertDataNames, insertDataGids,insertDataAreaIds
        extract($loadDataFromXlsOrCsv);
        $insertArrayFromGids = [];
        $insertArrayFromNames = [];
        $insertDataAreaIdsData = [];
        $extraParam['updateGid'] = isset($params['updateGid']) ? $params['updateGid'] : false;
        $insertDataKeys = $params['insertDataKeys'];
        $extraParam['logFileName'] = isset($params['logFileName']) ? $params['logFileName'] : false;

//Update records based on GID
        if (!empty($insertDataGids)) {
            $extraParam['nid'] = $params['nid'];
            $extraParam['component'] = $component;
            $insertArrayFromGids = $this->updateColumnsFromGid($insertDataGids, $dataArray, $insertDataKeys, $extraParam);
            unset($insertDataGids); //save Buffer
        }

//Update records based on Name
        if (!empty($insertDataNames)) {
            $extraParam['nid'] = $params['nid'];
            $extraParam['component'] = $component;
            $insertArrayFromNames = $this->updateColumnsFromName($insertDataNames, $dataArray, $insertDataKeys, $extraParam);
            $insertArrayFromNames = array_unique($insertArrayFromNames);
            unset($insertDataNames);    //save Buffer
        }

//Update records based on Area ids
        if (!empty($insertDataAreaIds)) {
            $extraParam['nid'] = $params['nid'];
            $extraParam['component'] = $component;
            $insertDataAreaIdsData = $this->updateColumnsFromAreaIds($insertDataAreaIds, $dataArray, $insertDataKeys, $extraParam);
            unset($insertDataAreaIds);  //save Buffer
        }

        $insertArray = array_merge(array_keys($insertArrayFromGids), array_keys($insertArrayFromNames));

//save Buffer
        unset($insertArrayFromGids);
        unset($insertArrayFromNames);
        unset($insertDataAreaIds);
        unset($insertDataAreaIdsData);

        $insertArray = array_flip($insertArray);
        $insertArray = array_intersect_key($dataArray, $insertArray);

        unset($dataArray);  //save Buffer
//Check if New records
        if (!empty($insertArray)) {
//Prepare Insert Data
            array_walk($insertArray, function(&$val, $key) use($params, $insertDataKeys) {
//auto-generate GUID if not set

                if (array_key_exists('gid', $insertDataKeys) && (!array_key_exists($insertDataKeys['gid'], $val) || $val[$insertDataKeys['gid']] == '')) {
                    $autoGenGuid = $this->guid();
                    $val[$insertDataKeys['gid']] = $autoGenGuid;
                }
//If 'highIsGood' needs to be inserted but is blank, keep default value 0
                if (array_key_exists('highIsGood', $insertDataKeys) && !array_key_exists($insertDataKeys['highIsGood'], $val)) {
                    $val[$insertDataKeys['highIsGood']] = 0;
                }
//If 'IndiGlobal' needs to be inserted but is blank, keep default value 0
                if (array_key_exists('IndiGlobal', $insertDataKeys) && !array_key_exists($insertDataKeys['IndiGlobal'], $val)) {
                    $val[$insertDataKeys['IndiGlobal']] = 0;
                }
//If 'unitGlobal' needs to be inserted but is blank, keep default value 0
                if (array_key_exists('unitGlobal', $insertDataKeys) && !array_key_exists($insertDataKeys['unitGlobal'], $val)) {
                    $val[$insertDataKeys['unitGlobal']] = 0;
                }
//If 'subgroupValGlobal' needs to be inserted but is blank, keep default value 0
                if (array_key_exists('subgroupValGlobal', $insertDataKeys) && !array_key_exists($insertDataKeys['subgroupValGlobal'], $val)) {
                    $val[$insertDataKeys['subgroupValGlobal']] = 0;
                }
//If 'subgroup_global' needs to be inserted but is blank, keep default value 0
                if (array_key_exists('subgroup_global', $insertDataKeys) && !array_key_exists($insertDataKeys['subgroup_global'], $val)) {
                    $val[$insertDataKeys['subgroup_global']] = 0;
                }
            });

//Insert New records
            $this->{$component}->insertBulkData($insertArray, $insertDataKeys);
        }
    }

    /**
     * importFormatCheck
     * 
     * @param string $type Upload Type
     * 
     * @return boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function importFormatCheck($type = null) {
        if ($type == 'icius') {
            return [
                'class type',
                'level1',
                'indicator',
                'unit',
                'subgroup'
            ];
        } else if ($type == _MODULE_NAME_AREA) {
            return [
                'areaid',
                'areaname',
                'arealevel',
                'areagid',
                'parent areaid'
            ];
        }
        return [];
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
        $t1 = microtime();
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;

        foreach ($divideXlsOrCsvInChunks as $filename) {
            $objPHPExcel = $this->readXlsOrCsv($filename);

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();
                $highestRow = $worksheet->getHighestRow(); // e.g. 10
                $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

                if ($highestRow == 1) {
                    $this->unlinkFiles($divideXlsOrCsvInChunks);
                    return ['error' => _ERROR_1];
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
                                    $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, _ERROR_IC_LEVEL_EMPTY);
                                }
                            } else {

                                if ($col == $indicatorFieldKey || (isset($indicatorGidFieldKey) && $col == $indicatorGidFieldKey)) {
                                    if ($col == $indicatorFieldKey && !empty($val)) {
                                        $indicatorArray[$row][] = $val;
                                    } else if (isset($indicatorArray[$row])) {
                                        $indicatorArray[$row][] = $val;
                                        $indicatorArray[$row][] = 0;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, _ERROR_INDICATOR_EMPTY);
                                    }
                                } else if ($col == $unitFieldKey || (isset($unitGidFieldKey) && $col == $unitGidFieldKey)) {
                                    if ($col == $unitFieldKey && !empty($val)) {
                                        $unitArray[$row][] = $val;
                                    } else if (isset($unitArray[$row])) {
                                        $unitArray[$row][] = $val;
                                        $unitArray[$row][] = 0;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, _ERROR_UNIT_EMPTY);
                                    }
                                } else if ($col == $subgroupValFieldKey || (isset($subgroupValGidFieldKey) && $col == $subgroupValGidFieldKey)) {
                                    if ($col == $subgroupValFieldKey && !empty($val)) {
                                        $subgroupValArray[$row][] = $val;
                                    } else if (isset($subgroupValArray[$row])) {
                                        $subgroupValArray[$row][] = $val;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, _ERROR_SUBGROUP_EMPTY);
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

                        $validFormat = $this->importFormatCheck('icius');
                        $formatDiff = array_diff($validFormat, array_map('strtolower', $insertFieldsArr));
                        if (!empty($formatDiff)) {
                            $this->unlinkFiles($divideXlsOrCsvInChunks);
                            return ['error' => _ERROR_2];
                        }

// Check if sheet should start from Class type
                        if (strtolower(reset($insertFieldsArr)) !== 'class type') {
                            $this->unlinkFiles($divideXlsOrCsvInChunks);
                            return ['error' => _ERROR_3];
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
                            $this->unlinkFiles($divideXlsOrCsvInChunks);
                            return ['error' => _ERROR_4];
                        } else {
// Exactly all the Dimensions from DB are gien in sheet
                            if (count($existingSubgroupTypesWithNids) == count($subgroupTypeFieldsWithColumn)) {
// Check if DB and uploaded Dimensions are in same order 
                                if (array_values($existingSubgroupTypesWithNids) !== array_values($subgroupTypeFieldsWithColumn)) {
                                    $this->unlinkFiles($divideXlsOrCsvInChunks);
                                    return ['error' => _ERROR_5];
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
                                        _SUBGROUPTYPE_SUBGROUP_TYPE_GID => $this->guid(),
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
                        $levelIcRecsWithNids = $this->IndicatorClassifications->saveNameAndGetNids($fields, $value, $extra);

                        $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                        $conditions = [_IC_IC_NAME . ' IN' => $levelIcRecsWithNids];
                        $levelIcRecsWithNids = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');

                        $allKeys = array_keys($levelArray);
                        $levelArray = array_intersect_key($levelArray, array_filter(array_combine(array_keys($levelArray), array_column($levelArray, $key - 1))));

//--- maintain error log - starts ---//
                        $keysToUnset = array_diff($allKeys, array_keys($levelArray));
                        $keysToUnset = array_flip(array_diff_key(array_flip($keysToUnset), $unsettedKeys));
                        $unsettedKeys = array_replace($unsettedKeys, array_fill_keys($keysToUnset, 'IC Level1 Name is empty.'));
//--- maintain error log - ends ---//

                        /*
                         * Use below line to prepare list if 'all' selected above in getConcatedFields
                         * $levelIcRecsWithNids = array_column($levelIcRecsWithNids, 'concatinated', _IC_IC_NID);
                         */
                        array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination, &$levelCombinationCond, &$ICArray, &$levelArray) {
                            if (!empty($val[$key - 1])) {
                                $parent_Nid = -1;
                                $val[$key - 1] = array_search("(" . $parent_Nid . ",'" . $val[$key - 1] . "')", $levelIcRecsWithNids);
                                $levelCombination[$index] = "(" . $parent_Nid . ",'" . $val[$key - 1] . "')";
                                $levelCombinationCond[$index][_IC_IC_PARENT_NID] = $parent_Nid;
                                $levelCombinationCond[$index][_IC_IC_NAME] = $val[$key - 1];
                                $ICArray[$index] = $val[$key - 1];
                            }
                        });
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
                            array_walk($insertResults, function(&$val, $rowIndex) use ($field, $levelArray, $key, $icTypes, $icGlobal) {
                                if (!empty($val)) {
                                    $returnFields = [];
                                    $returnFields[$field[0]] = $levelArray[$rowIndex][$key - 1];
                                    $returnFields[$field[1]] = $levelArray[$rowIndex][$key - 2];
                                    $returnFields[$field[2]] = $this->guid();
                                    $returnFields[$field[3]] = $icTypes[$rowIndex];
                                    $returnFields[$field[4]] = false;
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
                        $indicatorRecWithNids = $this->saveAndGetIndicatorRecWithNids($indicatorArray);
                    } else if ($key == $unitFieldKey) { //--- UNIT ---//
                        $unitRecWithNids = $this->saveAndGetUnitRecWithNids($unitArray);
                    } else if ($key >= $subgroupTypeFieldKey) { //--- SUBGROUP DIMENSIONS ---//
                        if (!isset($getSubGroupTypeNidAndName)) {
//$subgroupValsNIds = $this->saveAndGetSubGroupTypeRecWithNids($subgroupTypeFields);
                            $getSubGroupTypeNidAndNameReturn = $this->getSubGroupTypeNidAndName($subgroupTypeFields);
                            $getSubGroupTypeNidAndName = $getSubGroupTypeNidAndNameReturn['getSubGroupTypeNidAndName'];
                            $subGroupTypeList = $getSubGroupTypeNidAndNameReturn['subGroupTypeList'];
                        }

                        $subgroupType = array_search($subGroupTypeList[$key], $getSubGroupTypeNidAndName);
//debug($valueOriginal);exit;
                        $subGroupValsConditions = $subGroupValsConditionsArray = [];
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
                            $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $value);

                            $params['nid'] = _SUBGROUP_SUBGROUP_NID;
                            $params['insertDataKeys'] = $insertDataKeys;
                            $params['updateGid'] = FALSE;
                            $component = 'Subgroup';

                            $this->nameGidLogic($divideNameAndGids, $component, $params);
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

                            $subgroupValsNIdsReturn = $this->saveAndGetSubgroupValsRecWithNids($subgroupValArray, ['key' => $subgroupValFieldKey]);
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
                    $unsettedKeys = $this->maintainErrorLogs($key, $unsettedKeys, _ERROR_6);
                    continue;
                }
                if ((array_search($val[$unitFieldKey], $unitRecWithNids) == 0)) {
                    $unsettedKeys = $this->maintainErrorLogs($key, $unsettedKeys, _ERROR_7);
                    continue;
                }
                if ((array_search($val[$subgroupValFieldKey], $subgroupValsNIds) == 0)) {
                    $unsettedKeys = $this->maintainErrorLogs($key, $unsettedKeys, _ERROR_8);
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

            //------------- ICIUS ------------//
            $extraIcius['iusCombinationsCond'] = $iusCombinationsCond;
            $extraIcius['getExistingRecords'] = $getExistingRecords;
            $extraIcius['ICArray'] = $ICArray;
            $this->bulkInsertIcIus($insertDataArrRowsFiltered, $extraIcius);
            unset($insertDataArrRowsFiltered); //save buffer

            $unsettedKeysAllChunksArr[] = $unsettedKeys;
            $allChunksRowsArr[] = array_keys($insertDataArrRows);

            // ---- ICIUS successfully added - chunk
            $this->unlinkFiles($filename);
        }// Chunk Loop
        // ---- ICIUS successfully added - whole file   
        // Generate Import Log file
        $extraParams = ['highestValidColumn' => \PHPExcel_Cell::stringFromColumnIndex($highestSubgroupTypeColumn)];
        return $this->createImportLog($allChunksRowsArr, $unsettedKeysAllChunksArr, $extraParams);
    }

    /**
     * saveAndGetIndicatorRecWithNids
     * 
     * @param array $indicatorArray Indicator data Array
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function saveAndGetIndicatorRecWithNids($indicatorArray = []) {
        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'IndiGlobal' => _INDICATOR_INDICATOR_GLOBAL];
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $indicatorArray);

        $params['nid'] = _INDICATOR_INDICATOR_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;
        $component = 'Indicator';

        $this->nameGidLogic($divideNameAndGids, $component, $params);

        $fields = [_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME];
        $conditions = [_INDICATOR_INDICATOR_NAME . ' IN' => array_filter(array_unique(array_column($indicatorArray, 0)))];
        return $indicatorRecWithNids = $this->Indicator->getDataByParams($fields, $conditions, 'list');
    }

    /**
     * saveAndGetUnitRecWithNids
     * 
     * @param array $unitArray Unit data Array
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function saveAndGetUnitRecWithNids($unitArray = []) {
        $insertDataKeys = ['name' => _UNIT_UNIT_NAME, 'gid' => _UNIT_UNIT_GID, 'unitGlobal' => _UNIT_UNIT_GLOBAL];
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $unitArray);

        $params['nid'] = _UNIT_UNIT_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;
        $component = 'Unit';

        $this->nameGidLogic($divideNameAndGids, $component, $params);

        $fields = [_UNIT_UNIT_NID, _UNIT_UNIT_NAME];
        $conditions = [_UNIT_UNIT_NAME . ' IN' => array_filter(array_unique(array_column($unitArray, 0)))];
        return $unitRecWithNids = $this->Unit->getDataByParams($fields, $conditions, 'list');
    }

    /**
     * saveAndGetSubgroupValsRecWithNids
     * 
     * @param array $subgroupValArray SubgroupVals data Array
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function saveAndGetSubgroupValsRecWithNids($subgroupValArray = [], $extraParam = []) {
        extract($extraParam);
        $insertDataKeys = [
            'name' => _SUBGROUP_VAL_SUBGROUP_VAL,
            'gid' => _SUBGROUP_VAL_SUBGROUP_VAL_GID,
            'subgroup_val_order' => _SUBGROUP_VAL_SUBGROUP_VAL_ORDER,
            'subgroupValGlobal' => _SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL
        ];

        $maxSubgroupValOrder = $this->SubgroupVals->getMax(_SUBGROUP_VAL_SUBGROUP_VAL_ORDER);
        $subgroupValArrayUnique = array_intersect_key($subgroupValArray, array_unique(array_map('serialize', $subgroupValArray)));

        array_walk($subgroupValArrayUnique, function(&$val, $index) use(&$subgroupValArrayUnique, &$maxSubgroupValOrder, &$subgroupValsName) {
            if (empty(array_filter($val))) {
                unset($subgroupValArrayUnique[$index]);
            } else {
                $val[] = ++$maxSubgroupValOrder;
                $val[] = 0; // for _SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL
                $subgroupValsName[] = $val[0];
            }
        });

        $subgroupValArray = $subgroupValArrayUnique;
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $subgroupValArray);

        $params['nid'] = _SUBGROUP_VAL_SUBGROUP_VAL_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;
        $component = 'SubgroupVals';

        $this->nameGidLogic($divideNameAndGids, $component, $params);
        $subgroupValsNIds = $this->SubgroupVals->getDataByParams(
                [_SUBGROUP_VAL_SUBGROUP_VAL_NID, _SUBGROUP_VAL_SUBGROUP_VAL], [_SUBGROUP_VAL_SUBGROUP_VAL . ' IN' => $subgroupValsName], 'list');

        return ['subgroupValsNIds' => $subgroupValsNIds];
    }

    /**
     * getSubGroupTypeNidAndName
     * 
     * @param array $subgroupTypeFields SubgroupType data Array
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function getSubGroupTypeNidAndName($subgroupTypeFields = []) {
        $insertDataKeys = ['name' => _SUBGROUPTYPE_SUBGROUP_TYPE_NAME, 'gid' => _SUBGROUPTYPE_SUBGROUP_TYPE_GID];

        //Add one more element for GID
        array_walk($subgroupTypeFields, function(&$val, $key) use (&$subGroupTypeList) {
            $val[] = '';
            $subGroupTypeListVal = array_values($val);
            $subGroupTypeList[$key] = $subGroupTypeListVal[0];
        });

        $subGroupTypeList = array_filter($subGroupTypeList);
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $subgroupTypeFields);

        $params['nid'] = _SUBGROUPTYPE_SUBGROUP_TYPE_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;
        $component = 'SubgroupType';

        $this->nameGidLogic($divideNameAndGids, $component, $params);

        $getSubGroupTypeNidAndName = $this->SubgroupType->getDataByParams(
                [_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME], [_SUBGROUPTYPE_SUBGROUP_TYPE_NAME . ' IN' => $subGroupTypeList], 'list');

        return ['getSubGroupTypeNidAndName' => $getSubGroupTypeNidAndName, 'subGroupTypeList' => $subGroupTypeList,];
    }

    /**
     * bulkUploadXlsOrCsvForIndicator
     * 
     * @param array $params Any extra parameter
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function bulkUploadXlsOrCsvForIndicator($params = null) {
        extract($params);
        $objPHPExcel = $this->readXlsOrCsv($filename);

        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'highIsGood' => _INDICATOR_HIGHISGOOD];
        $extra['limitRows'] = 1000; // Number of rows in each file chunks
        $extra['startRows'] = 2; // Row from where the data reading starts

        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunkFiles($objPHPExcel, $extra);

        foreach ($divideXlsOrCsvInChunks as $filename) {

            $loadDataFromXlsOrCsv = $this->loadDataFromXlsOrCsv($filename, $insertDataKeys, $extra);

            $params['insertDataKeys'] = $insertDataKeys;
            $params['updateGid'] = TRUE;
            $params['nid'] = _INDICATOR_INDICATOR_NID;

            $component = 'Indicator';

            $this->bulkInsert($component, $loadDataFromXlsOrCsv, $params);
            $this->unlinkFiles($filename);
        }
    }

    /**
     * 
     * bulkUploadXlsOrCsvForUnit
     * 
     * @param array $params Any Extra param
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function bulkUploadXlsOrCsvForUnit($params = null) {

        extract($params);

        $insertFieldsArr = [];
        $insertDataArr = [];
        $objPHPExcel = $this->readXlsOrCsv($filename);

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            for ($row = 1; $row <= $highestRow; ++$row) {

                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if ($row == 1) {
                        $insertFieldsArr[] = $val;
                    } else {
                        $insertDataArr[$row][$insertFieldsArr[$col]] = $val;
                    }
                }
            }
        }

        $dataArray = array_values($insertDataArr);
        $returnData = $this->Unit->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateColumnsFromAreaIds  method to update existing area ids 
     *
     * @param array $areaids area ids Array. {DEFAULT : empty}
     * @return void
     */
    public function updateColumnsFromAreaIds($areaids = [], $dataArray, $insertDataKeys, $extra = null) {

        $component = 'Area';
        $fields = [$extra['nid'], $insertDataKeys['areaid']];
        $conditions = [$insertDataKeys['areaid'] . ' IN' => $areaids];
        $updateGid = $extra['updateGid']; // true/false
        //Get NIds based on areaid found in db 
        $getDataByAreaid = $this->{$component}->getDataByParams($fields, $conditions, 'list'); //data which needs to be updated       

        if (!empty($getDataByAreaid)) {
            foreach ($getDataByAreaid as $Nid => $areaId) {
                $key = array_search($areaId, $areaids);
                $updateData = $dataArray[$key]; // data which needs to be updated using area  nid 
                pr($updateData);
                $this->{$component}->updateDataByParams($updateData, [$extra['nid'] => $Nid]);
            }
        }

        //Get Areaids that are not found in the database
        $freshRecordsNames = array_diff($areaids, $getDataByAreaid); // records which needs to be inserted 
        $finalrecordsforinsert = array_unique($freshRecordsNames);


        return $finalrecordsforinsert;
    }

    /**
     * updateColumnsFromName method
     *
     * @param array $names Names Array. {DEFAULT : empty}
     * @param array $dataArray Data Array From XLS/XLSX/CSV.
     * @param array $insertDataKeys Fields to be inserted Array.
     * @param array $extra Extra Parameters Array. {DEFAULT : null}
     * @return void
     */
    public function updateColumnsFromName($names = [], $dataArray, $insertDataKeys, $extra = null) {
        $fields = [$extra['nid'], $insertDataKeys['name']];
        $conditions = [$insertDataKeys['name'] . ' IN' => array_unique($names)];
        $component = $extra['component'];
        $updateGid = $extra['updateGid']; // true/false
        //Get NIds based on Name - //Check if Names found in database
        //getDataByParams(array $fields, array $conditions, $type = 'all')
        $getDataByName = $this->{$component}->getDataByParams($fields, $conditions, 'list');

        /*
         * WE DON'T UPDATE THE ROW IF 
         * 1. NAME IS FOUND AND 
         * 2. UPDATING GID IS NOT REQUIRED 
         * BECAUSE THAT WILL OVERWRITE THE GUID
         */
        if ($updateGid == true) {
            if (!empty($getDataByName)) {
                foreach ($getDataByName as $Nid => $name) {
                    $key = array_search($name, $names);
                    $name = $dataArray[$key];

                    $autoGenGuid = $this->guid();
                    $name[$insertDataKeys['gid']] = $autoGenGuid;

                    if (array_key_exists('highIsGood', $insertDataKeys)) {
                        if (!array_key_exists($insertDataKeys['highIsGood'], $name)) {
                            $name[$insertDataKeys['highIsGood']] = 0;
                        }
                    }

                    $this->{$component}->updateDataByParams($name, [$extra['nid'] => $Nid]);
                }
            }
        }

        //Get Guids that are not found in the database
        return $freshRecordsNames = array_diff($names, $getDataByName);
    }

    /**
     * updateColumnsFromGid method
     *
     * @param array $gids Gids Array. {DEFAULT : empty}
     * @param array $dataArray Data Array From XLS/XLSX/CSV.
     * @param array $insertDataKeys Fields to be inserted Array.
     * @param array $extra Extra Parameters Array. {DEFAULT : null}
     * @return void
     */
    public function updateColumnsFromGid($gids = [], $dataArray, $insertDataKeys, $extra = null) {

        $fields = [$extra['nid'], $insertDataKeys['gid']];
        $conditions = [$insertDataKeys['gid'] . ' IN' => array_unique($gids)];
        $component = $extra['component'];

        //Get NIds based on GID - //Check if Guids found in database
        $getDataByGid = $this->{$component}->getDataByParams($fields, $conditions, 'list');

        //Get Guids that are not found in the database
        $freshRecordsGid = array_diff($gids, $getDataByGid);

        if (!empty($getDataByGid)) {
            foreach ($getDataByGid as $Nid => &$gid) {

                $key = array_search($gid, $gids);
                $gid = $dataArray[$key];

                if (array_key_exists('highIsGood', $insertDataKeys)) {
                    if (!array_key_exists($insertDataKeys['highIsGood'], $gid)) {
                        $gid[$insertDataKeys['highIsGood']] = 0;
                    }
                }

                //$this->Indicator->updateDataByParams($gid, [$extra['nid'] => $Nid]);
                $this->{$component}->updateDataByParams($gid, [$extra['nid'] => $Nid]);
            }
        }

        if (!empty($freshRecordsGid)) {

            array_walk($freshRecordsGid, function($val, $key) use ($dataArray, $insertDataKeys, &$names) {
                $names[$key] = $dataArray[$key][$insertDataKeys['name']];
            });

            //Check existing Names when Guids NOT found in database
            return $this->updateColumnsFromName($names, $dataArray, $insertDataKeys, $extra);
        } else {
            return [];
        }
    }

    /**
     * loadDataFromXlsOrCsv method
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function loadDataFromXlsOrCsv($filename = null, $insertDataKeys = null, $extra = null) {

        $insertDataArr = [];
        $insertDataNames = [];
        $insertDataGids = [];
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;

        $objPHPExcel = $this->readXlsOrCsv($filename);

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            for ($row = $startRows; $row <= $highestRow; ++$row) {

                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if ($row >= $startRows) {  //-- Data Strats from row 6 --//                      
                        $insertDataArr[$row][] = $val;
                    } else {
                        continue;
                    }
                }
                if (isset($insertDataArr[$row])):
                    $insertDataArr[$row] = array_combine($insertDataKeys, $insertDataArr[$row]);
                    $insertDataArr[$row] = array_filter($insertDataArr[$row]);

                    //We don't need this row if the name field is empty
                    if (!isset($insertDataArr[$row][$insertDataKeys['name']])) {
                        unset($insertDataArr[$row]);
                    } else if (!isset($insertDataArr[$row][$insertDataKeys['gid']])) {
                        $insertDataNames[$row] = $insertDataArr[$row][$insertDataKeys['name']];
                    } else {
                        $insertDataGids[$row] = $insertDataArr[$row][$insertDataKeys['gid']];
                    }
                endif;
            }
        }

        //Re-assigned to its own variable to save buffer (as new array will be of small size)
        $insertDataArr = array_filter($insertDataArr);
        return ['dataArray' => $insertDataArr, 'insertDataNames' => $insertDataNames, 'insertDataGids' => $insertDataGids];
    }

    /**
     * bulkUploadXlsOrCsvForArea method
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function bulkUploadXlsOrCsvForArea($fileChunksArray = [], $extra = null, $xlsObject = null) {
        $component = 'Area';
        $insertFieldsArr = [];
        $insertDataArrRows = [];
        $insertDataArrCols = [];
        // $extra['limitRows'] = 200; // Number of rows in each area chunks file 
        $extra['startRows'] = 1; // Row from where the data reading starts
        $extra['callfunction'] = $component;

        $insertDataKeys = [_INSERTKEYS_AREAID => _AREA_AREA_ID,
            _INSERTKEYS_NAME => _AREA_AREA_NAME,
            _INSERTKEYS_LEVEL => _AREA_AREA_LEVEL,
            _INSERTKEYS_GID => _AREA_AREA_GID,
            _INSERTKEYS_PARENTNID => _AREA_PARENT_NId,
        ];
        // start file validation

        $xlsObject->setActiveSheetIndex(0);
        $startRow = 1; //first row 
        $highestColumn = $xlsObject->getActiveSheet()->getHighestColumn(); // e.g. 'F'
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        // code for validation of uploaded  file 
        $highestRow = $xlsObject->getActiveSheet()->getHighestRow(); // e.g. 10   			
        if ($highestRow == 1) {
            return ['error' => 'The file is empty'];
        }
        $titlearray = [];  // for titles of sheet
        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $cell = $xlsObject->getActiveSheet()->getCellByColumnAndRow($col, $startRow);
            $titlearray[] = $val = $cell->getValue();
        }
        $validFormat = $this->importFormatCheck(_MODULE_NAME_AREA);  //Check file Columns format
        $formatDiff = array_diff($validFormat, array_map('strtolower', $titlearray));
        if (!empty($formatDiff)) {
            return ['error' => 'Invalid Columns Format'];
        }
        // end of file validation 	
        // $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunkFiles($objPHPExcel, $extra); // split  the file in chunks 
        $firstRow = ['A' => 'AreaId', 'B' => 'AreaName', 'C' => 'AreaLevel', 'D' => 'AreaGId', 'E' => 'Parent AreaId', 'F' => 'Status', 'G' => 'Description'];
        //$areaErrorLog = $this->createErrorLog($firstRow, 'Area');   //returns error log file 
        $this->resetLogdata();
        //pr($fileChunksArray);
        $logData = [];
        foreach ($fileChunksArray as $filename) {

            $extra['chunkFilename'] = $filename;
            $loadDataFromXlsOrCsv = $this->prepareDataFromXlsOrCsv($filename, $insertDataKeys, $extra);


            $params['nid'] = _AREA_AREA_NID;
            $params['insertDataKeys'] = $insertDataKeys;
            $params['updateGid'] = TRUE;

            $logData[] = $loadDataFromXlsOrCsv['errorLogArray'];

            $this->nameGidLogic($loadDataFromXlsOrCsv, $component, $params);
            //@unlink($filename);
        }

        $finalLogArray = [];

        foreach ($logData as $fileChunk => $chunkName) {
            foreach ($chunkName as $chunkIndex => $chunkData) {
                foreach ($chunkData as $logDt) {
                    $finalLogArray[] = $logDt;
                }
            }
        }


        // $this->appendErrorLogData(WWW_ROOT.$areaErrorLog,$_SESSION['errorLog']); //
        //return $this->appendErrorLogData($firstRow, $_SESSION['errorLog']); //
        return $this->appendErrorLogData($firstRow, $finalLogArray); //
    }

    /**
     * maintainErrorLogs method     *
     * @param string $row row to check. {DEFAULT : null}
     * @param array $unsettedKeys Error storing Array. {DEFAULT : null}
     * @param string $msg Message if row not found. {DEFAULT : null}
     * @return unsettedKeys array
     */
    public function maintainErrorLogs($row, $unsettedKeys, $msg) {
        if (!array_key_exists($row, $unsettedKeys)) {
            $filledArrayKeys = [$row];
            $unsettedKeys = array_replace($unsettedKeys, array_fill_keys($filledArrayKeys, $msg));
        }
        return $unsettedKeys;
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
            $getExistingRecords = $this->IcIus->getConcatedFields($fields, $conditions, 'list');

            if (!empty($getExistingRecords)) {
                $IcIusDataArray = array_diff_key($IcIusDataArray, array_intersect($IcIusCombination, $getExistingRecords));
            }
            if (!empty($IcIusDataArray)) {
                $insertDataKeys = [_ICIUS_IC_NID, _ICIUS_IUSNID];
                $this->IcIus->insertBulkData($IcIusDataArray, $insertDataKeys);
            }
        }
    }

    /*
     * createImportLog
     *
     * @param array $allChunksRowsArr Sheet Rows indexes Array
     * @param array $unsettedKeysAllChunksArr Indexes having errors
     * @return Exported File path
     */

    public function createImportLog($allChunksRowsArr, $unsettedKeysAllChunksArr, $extra = []) {

        //$PHPExcel = new \PHPExcel();
        $sheet = 1;
        $chunkParams = $this->session->consume('ChunkParams'); //Read and destroy session with consume
        //$startRows = $chunkParams['startRows'];
        $limitRows = $chunkParams['limitRows'];
        $highestRow = $chunkParams['highestRow'];
        //$highestColumn = $chunkParams['highestColumn'];
        $highestColumn = isset($extra['highestValidColumn']) ? $extra['highestValidColumn'] : $chunkParams['highestColumn'];

        $count = 0;
        $lastColumn = $highestColumn;
        $columnToWrite = [];
        $columnToWrite['status'] = ++$lastColumn;
        $columnToWrite['description'] = ++$lastColumn;
        $PHPExcel = $this->readXlsOrCsv(_LOG_FILEPATH);

        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . '1', _IMPORT_STATUS); // Title Row Status
        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . '1', _DESCRIPTION); // Title Row Description

        foreach ($allChunksRowsArr as $key => $chunkRows) {

            foreach ($chunkRows as $chunkRowsKey => $value) {

                if ($count === 0) {
                    $startRows = ($count * $limitRows) + $value;
                } else {
                    $startRows = ($count * $limitRows) + ($value - 1);
                }

                for ($row = $startRows; $row <= ($startRows + (count($startRows) - 1)); ++$row) {
                    if (array_key_exists($row, $unsettedKeysAllChunksArr[$key])) {
                        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $row, _FAILED);
                        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $row, $unsettedKeysAllChunksArr[$key][$row]);
                    } else {
                        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $row, _OK);
                        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $row, '');
                    }
                }
            }

            $count++;
            $sheet++;
        }

        $PHPExcel->setActiveSheetIndex(0);
        $objWriter = new \PHPExcel_Writer_Excel2007($PHPExcel);
        $sheetPath = _LOG_FILEPATH;
        $objWriter->save($sheetPath);

        return _LOG_FILEPATH;
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
        $iciusRecords = $this->IcIus->getDataByParams($iciusFields, $iciusConditions);

        //IC_NIDS from ICIUS
        $icNids = array_column($iciusRecords, _ICIUS_IC_NID);

        //IUS_NIDS from ICIUS
        $iusNids = array_unique(array_column($iciusRecords, _ICIUS_IUSNID));

        //IUS Records
        $iusFields = [_IUS_IUSNID, _IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_SUBGROUP_NIDS];
        $iusConditions = [_IUS_IUSNID . ' IN' => array_unique($iusNids)];
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
        $iConditions = [_INDICATOR_INDICATOR_NID . ' IN' => $iNids];
        $iRecords = $this->Indicator->getDataByParams($iFields, $iConditions);
        $iNidsIndependent = array_column($iRecords, _INDICATOR_INDICATOR_NID);

        //get Unit Records
        $uFields = [_UNIT_UNIT_NID, _UNIT_UNIT_NAME, _UNIT_UNIT_GID];
        $uConditions = [_UNIT_UNIT_NID . ' IN' => $uNids];
        $uRecords = $this->Unit->getDataByParams($uFields, $uConditions);
        $uNidsIndependent = array_column($uRecords, _UNIT_UNIT_NID);

        //get SubgroupVals Records
        $sValFields = [_SUBGROUP_VAL_SUBGROUP_VAL_NID, _SUBGROUP_VAL_SUBGROUP_VAL, _SUBGROUP_VAL_SUBGROUP_VAL_GID];
        $sValConditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN' => $sValNids];
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
        $parentChildNodes = $this->getParentChild('IndicatorClassifications', '-1', true, ['conditions' => [_IC_IC_TYPE . ' <>' => 'SR']]);
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
            $getIcDepthReturn = $this->getIcDepth($iciusValue[_ICIUS_IC_NID], $icRecords);

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
        $authUserId = $this->Auth->User('id');
        $returnFilename = _TPL_Export_ . $module . '_' . $authUserId . '_' . date('Y-m-d-h-i-s') . '.xls';
        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment;filename=' . $returnFilename);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    /*
     * getIcDepth
     *
     * @return Get IC depth
     */

    public function getIcDepth($icNid, $icRecords) {

        $icDepthArray = [];

        //IC_NIDS - Independent
        $icNidsIndependent = array_column($icRecords, _IC_IC_NID);
        //_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_TYPE

        if (in_array($icNid, $icNidsIndependent)) {
            $icNidsKey = array_search($icNid, $icNidsIndependent);
            $icRecordsChild = [_IC_IC_NAME => $icRecords[$icNidsKey][_IC_IC_NAME], _IC_IC_TYPE => $icRecords[$icNidsKey][_IC_IC_TYPE]];

            if ($icRecords[$icNidsKey][_IC_IC_PARENT_NID] != '-1') {
                $getIcDepth = $this->getIcDepth($icRecords[$icNidsKey][_IC_IC_PARENT_NID], $icRecords);
                if ($getIcDepth !== false) {
                    array_push($getIcDepth, $icRecordsChild);
                    $icDepthArray = $getIcDepth;
                } else {
                    return false;
                }
            } else {
                $icDepthArray[] = $icRecordsChild;
            }
        } else {
            return false;
        }

        //$this->icDepth = 1;
        return $icDepthArray;
    }

    /*
     * resetLogdata used to reset session of log  
     * 
     */

    public function resetLogdata() {
        unset($_SESSION['errorLog']['STATUS']);
        unset($_SESSION['errorLog']['Description']);
        unset($_SESSION['errorLog']);
    }

    /*
     *  function to append data 
     */

    public function appendErrorLogData($firstRowdata = [], $data = []) {
        /* style for headings */

        $authUserId = $this->Auth->User('id');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $rowCount = 1;

        $objPHPExcel->setActiveSheetIndex(0);
        $chrarrya = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        $cnt = 0;
        $startRow = 1;
        $objPHPExcel->getActiveSheet()->getStyle("A1:G1")->getFont()->setItalic(true);
        foreach ($firstRowdata as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $startRow, $value)->getStyle($index . $startRow);
        }
        //$startRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $startRow = 2;
        /*
          $statuslogArray = $data[_STATUS];
          $desclogArray = $data[_DESCRIPTION];
          unset($data[_STATUS]);
          unset($data[_DESCRIPTION]);
         */

        $width = 30;
        $styleArray = array(
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 10,
                'name' => 'Arial',
        ));
        //pr($data);die;

        foreach ($data as $index => $value) {

            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_AREA_AREA_ID])) ? $value[_AREA_AREA_ID] : '' )->getColumnDimension('A')->setWidth($width);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $startRow)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_AREA_AREA_NAME])) ? $value[_AREA_AREA_NAME] : '')->getColumnDimension('B')->setWidth($width + 10);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $startRow)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($value[_AREA_AREA_LEVEL])) ? $value[_AREA_AREA_LEVEL] : '')->getColumnDimension('C')->setWidth($width - 20);
            $objPHPExcel->getActiveSheet()->getStyle('C' . $startRow)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($value[_AREA_AREA_GID])) ? $value[_AREA_AREA_GID] : '' )->getColumnDimension('D')->setWidth($width + 20);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $startRow)->applyFromArray($styleArray);


            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $startRow, (isset($value[_AREA_PARENT_NId])) ? $value[_AREA_PARENT_NId] : '' )->getColumnDimension('E')->setWidth($width);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $startRow)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $startRow, (isset($value[_STATUS])) ? $value[_STATUS] : '' )->getColumnDimension('F')->setWidth($width - 10);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $startRow)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $startRow, (isset($value[_DESCRIPTION])) ? $value[_DESCRIPTION] : '' )->getColumnDimension('G')->setWidth($width + 5);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $startRow)->applyFromArray($styleArray);

            $startRow++;
        }
        $module = 'Area';
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $saveFile = _LOGS_PATH . DS . _IMPORTERRORLOG_FILE . _MODULE_NAME_AREA . '_' . $authUserId . '_' . date('Y-m-d-H-i-s') . '.xls';
        //$returnFilename = WWW_ROOT.'uploads'.DS.'logs'.DS._IMPORTERRORLOG_FILE . _MODULE_NAME_AREA . '_' . $authUserId . '_' .time().'.xls';
        $returnFilename = _IMPORTERRORLOG_FILE . _MODULE_NAME_AREA . '_' . $authUserId . '_' . date('Y-m-d-H-i-s') . '.xls';
        $objWriter->save($saveFile);
        return $saveFile;
        // $objWriter->save($filename);
    }

    /*
     * getParentChild
     */

    public function getParentChild($component, $parentNID, $onDemand = false, $extra = []) {
        $conditions = array();
        if ($component == 'IndicatorClassifications') {
            $conditions[_IC_IC_PARENT_NID] = $parentNID;

            if (isset($extra['conditions'])) {
                $conditions = array_merge($conditions, $extra['conditions']);
            }

            $order = array(_IC_IC_NAME => 'ASC');
        } else if ($component == 'Area') {
            $conditions[_AREA_PARENT_NId] = $parentNID;
            $order = array(_AREA_AREA_NAME => 'ASC');
        }
        $recordlist = $this->{$component}->find('all', array('conditions' => $conditions, 'fields' => array(), 'order' => $order));
        $list = $this->getDataRecursive($recordlist, $component, $onDemand);


        return $list;
    }

    /**
     * function to recursive call to get children areas
     *
     * @access public
     */
    function getDataRecursive($recordlist, $component, $onDemand = false) {

        $rec_list = array();
        $childData = array();
        // start loop through area data
        for ($lsCnt = 0; $lsCnt < count($recordlist); $lsCnt++) {

            $childExists = false;

            // get selected Rec details
            if ($component == 'IndicatorClassifications') {
                $NId = $recordlist[$lsCnt][_IC_IC_NID];
                $ID = $recordlist[$lsCnt][_IC_IC_GID];
                $name = $recordlist[$lsCnt][_IC_IC_NAME];
                $parentNID = $recordlist[$lsCnt][_IC_IC_PARENT_NID];

                if ($onDemand === false) {
                    $childData = $this->{$component}->find('all', array('conditions' => array(_IC_IC_PARENT_NID => $NId), 'order' => array(_IC_IC_NAME => 'ASC')));
                } else {
                    $childCount = $this->{$component}->find('all', array('conditions' => array(_IC_IC_PARENT_NID => $NId)), array('count' => 1));
                    $childExists = ($childCount) ? true : false;
                }
            } else if ($component == 'Area') {

                $NId = $recordlist[$lsCnt][_AREA_AREA_NID];
                $ID = $recordlist[$lsCnt][_AREA_AREA_ID];
                $name = $recordlist[$lsCnt][_AREA_AREA_NAME];
                $parentNID = $recordlist[$lsCnt][_AREA_PARENT_NId];

                if ($onDemand === false) {
                    $childData = $this->{$component}->find('all', array('conditions' => array(_AREA_PARENT_NId => $NId), 'order' => array(_AREA_AREA_NAME => 'ASC')));
                } else {
                    $childCount = $this->{$component}->find('all', array('conditions' => array(_AREA_PARENT_NId => $NId)), array('count' => 1));
                    $childExists = ($childCount) ? true : false;
                }
            }

            //if child data found
            if (count($childData) > 0) {

                $this->arrayDepthIterator = $this->arrayDepthIterator + 1;

                if ($this->arrayDepthIterator > $this->arrayDepth) {
                    $this->arrayDepth = $this->arrayDepth + 1;
                }

                $childExists = true;

                // call function again to get selected area another child data
                $dataArr = $this->getDataRecursive($childData, $component);

                $rec_list[] = $this->prepareNode($NId, $ID, $name, $childExists, $dataArr, $this->arrayDepth);
            }
            //if child data not found then make list with its id and name
            else {
                $this->arrayDepthIterator = 1;
                $rec_list[] = $this->prepareNode($NId, $ID, $name, $childExists);
            }
        }
        // end of loop for area data

        return $rec_list;
    }

    /**
     * function to prapare Node
     *
     * @access public
     */
    public function prepareNode($NId, $ID, $name, $childExists, $nodes = array(), $depth = 1) {
        return array('nid' => $NId, 'id' => $ID, 'name' => $name, 'childExists' => $childExists, 'nodes' => $nodes, 'arrayDepth' => $depth);
    }

    /**
     * function to Delete Files from the disk
     *
     * @param array $filepaths files to be deleted
     * @access public
     */
    public function unlinkFiles($filepaths = null) {
        if (is_array($filepaths)) {
            foreach ($filepaths as $filepath) {
                @unlink($filepath);
            }
        } else {
            @unlink($filepaths);
        }
    }

    /**
     * getDEsearchData to get the details of search on basis of IUSNid,
      @areanid
      @TimeperiodNid
      @$iusGid can be mutiple in form of array
      returns data value with source
     * @access public
     */
    public function getDEsearchData($fields = [], $conditions = [], $extra = []) {
        return $this->Data->getDEsearchData($fields, $conditions, $extra);
    }

    /*
     * getICIndicatorList returns the indicator list 
     * @$IcNid is the Ic nid
     *  $component is the component used 
     * 
     */

    public function getICIndicatorList($component, $parentNID, $onDemand = false) {
        $returnData = array();
        $conditions = array();

        if (!empty($parentNID) && $parentNID != -1) {
            $conditions = [_ICIUS_IC_NID => $parentNID];
        }
        $fields = [_ICIUS_IUSNID, _ICIUS_IUSNID];
        // returns the indicator details of passed icnid   
        return $returnData = $this->{$component}->getICIndicatorList($fields, $conditions);
    }

    /*
     * to get Indicator list
     */

    public function getIndicatorList($component, $conditions = array()) {

        $list = array();

        if ($component == 'Indicator') {
            $order = array(_INDICATOR_INDICATOR_NAME => 'ASC');

            $recordlist = $this->{$component}->find('all', array('conditions' => $conditions, 'fields' => array(), 'order' => $order));

            foreach ($recordlist as $dt) {

                $NId = $dt[_INDICATOR_INDICATOR_NID];
                $ID = $dt[_INDICATOR_INDICATOR_GID];
                $name = $dt[_INDICATOR_INDICATOR_NAME];
                $list[] = $this->prepareNode($NId, $ID, $name, false, array(), 1);
            }
        }

        return $list;
    }

}
