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

	//public $dbcon ='';
    //Loading Components
    public $components = ['Auth',
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
        'DevInfoInterface.Area'
    ];

    public function initialize(array $config) {
       
		//parent::initialize($config);
         
        $this->session = $this->request->session();
        $this->arrayDepth = 1;
        $this->arrayDepthIterator = 1;
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
        
	
	/*$config = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            //'port' => 'nonstandard_port_number',
            'username' => 'root',
            'password' => '',
            'database' => 'developer_evaluation_database',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ];
        */
        ConnectionManager::config('devInfoConnection', $config);

        $conn = ConnectionManager::get('devInfoConnection');
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
     * divideNameAndGids method    
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
            $value = array_filter($value);

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
        //extract($loadDataFromXlsOrCsv);
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
        return $objPHPExcel;
    }

    /**
     * divideXlsOrCsvInChunks method    
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
    
    /**
     * getParentnidLevel method
     * @param $parentNid parentnid in excel 
       returns arealevel 
     */
    public function getParentnidLevel($parentNid){
        $fields =[_AREA_AREA_LEVEL];
        $conditions[_AREA_AREA_ID]=$parentNid;
        return $this->Area->getDataByParams($fields,$conditions);
    }

    /**
     * divideAreaids method
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function divideAreaids($insertDataKeys = null, $insertDataArr = null, $extra = null) {

        $insertDataAreaids = [];
        $insertDataAreaParentids = [];
        $blnkParentidsAreaids = [];
        $areaidswithparentid = [];
		$limitedRows =[];
		$compareAreaidDParentId =[];
		$allAreaParents =[];
		//$compareAreaidDParentId =[];
		
		foreach($insertDataArr as $index=>$valueArray){
			if($index==1){
				unset($valueArray);
			}if($index >1){
			foreach($valueArray as $innerIndex=>$innervalueArray){
					if($innerIndex>4)
						break;
					$limitedRows[$index][$innerIndex]=$innervalueArray;
					unset($innervalueArray);
			}}
			unset($valueArray);
			
		}
		
		
        $newinsertDataArr = $limitedRows;
        $compareAreaidParentId = $limitedRows;
        $errorLogArray = [];
		
		$insertDataArr=$limitedRows;
        // loop to get all parent nids 
        foreach ($insertDataArr as $row => &$value) {		
			
            $value = array_combine($insertDataKeys, $value);			
            $value = array_filter($value);
			
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
			$finalareaids=[];
			$chkuniqueAreaids=[];
			$ignoreAreaIdsAsSubParent=[];
			$forParentAreaId=[];
			$allAreaIdsAsSubParent=[];
            foreach ($newinsertDataArr as $row => &$value) {
				
			    $allAreblank=false;
                $value = array_combine($insertDataKeys, $value);
                $value = array_filter($value);
				
				if(empty($value))
				{
					$allAreblank=true;
				}
			
                if (array_key_exists('areaid', $insertDataKeys) && (!isset($value[$insertDataKeys['areaid']]) || empty($value[$insertDataKeys['areaid']]) )) { 
				//ignore if area id is blank
                       if($allAreblank == false  ){
							$_SESSION['errorLog'][]= $value;
							$_SESSION['errorLog']['STATUS'][] = $errorLogArray[$row]['STATUS'] = 'Error';
							$_SESSION['errorLog']['Description'][] = $errorLogArray[$row]['Description'] = 'Area id  not empty!!';
						}
					

                    unset($value);
                    unset($newinsertDataArr[$row]);
                }else if (isset($value[$insertDataKeys['areaid']]) && !empty($value[$insertDataKeys['areaid']])) {
                    if (!empty($value[$insertDataKeys['parentnid']]) && $value[$insertDataKeys['parentnid']]!='-1' && in_array($value[$insertDataKeys['parentnid']], 				$areaidswithparentid) == true) {
						//case when parent id is not empty and exists in database also 
				        if($allAreblank == false  ){
							$_SESSION['errorLog'][]= $errorLogArray[$row] = $value;   
						}
						if(!array_key_exists($insertDataKeys['level'],$value)){
							$level='';
						}else{
							$level=$value[$insertDataKeys['level']];
						}
						$value[$insertDataKeys['level']] =  $this->Area->returnAreaLevel($level,$value[$insertDataKeys['parentnid']]);
						$value[$insertDataKeys['parentnid']] = array_search($value[$insertDataKeys['parentnid']], $areaidswithparentid);
						
						$uniquestatus=false; // false means yet not added in array chkuniqueAreaids
						$insertDataAreaids[$row] = $value[$insertDataKeys['areaid']]; // will be needed for  update
					   	
						if($allAreblank == false){
								 $_SESSION['errorLog']['STATUS'][] = $errorLogArray[$row]['STATUS'] = 'Done';
							     $_SESSION['errorLog']['Description'][] = $errorLogArray[$row]['Description'] = '';	
						}else{						
								$_SESSION['errorLog']['STATUS'][]=$errorLogArray[$row]['STATUS'] = 'Done';      // Error //Duplicate entry 222
								$_SESSION['errorLog']['Description'][]=  $errorLogArray[$row]['Description'] = '';
						}					
						
						
				}elseif (!empty($value[$insertDataKeys['parentnid']]) && ($value[$insertDataKeys['parentnid']]!='-1') && in_array($value[$insertDataKeys['parentnid']], 			$areaidswithparentid) == false) {
						
						//case when parent id is not empty and do not exists in database  
						if($allAreblank == false){

							$_SESSION['errorLog'][] = $value;
						}
						$uniqueStatus = false;
						$errcnt = 0;
						if(!empty($LogAreaIds) ){
								
							$keylog = array_search($value[$insertDataKeys['areaid']],$LogAreaIds);
							if(($keylog==$row) && (in_array($value[$insertDataKeys['areaid']],$LogAreaIds)==true)){
								$uniqueStatus=false;
							}else{
								$uniqueStatus=true;
							if(empty($keylog)){
								$errcnt =1; // 	 //means areaid before parent id	
							}elseif($keylog < $row){
								$errcnt =2; // means areaid repeated  twice after parent id
							}else{
								$errcnt =1; // 	 //means areaid before parent id	 
							}								 
						  }
						}else{
								$errcnt =1; // means areaid before parent id	
								$uniqueStatus=true;
						}
				
					
					if($uniqueStatus==true){
							if($allAreblank == false){
								if($errcnt==1){
									$_SESSION['errorLog']['STATUS'][] = 'Error';

									$_SESSION['errorLog']['Description'][] = 'Parent id not found ';
								}  else{																	
									$_SESSION['errorLog']['STATUS'][] = 'Done'; // Error 'Duplicate entry';
									$_SESSION['errorLog']['Description'][] = '';	
									//$_SESSION['errorLog']['Description'][] = 'Duplicate entry';
								}
							    	
							}
							unset($value);
							unset($newinsertDataArr[$row]);
				    }else{
							if($allAreblank == false){
								$_SESSION['errorLog']['STATUS'][]=$errorLogArray[$row]['STATUS'] = 'Done';
								$_SESSION['errorLog']['Description'][]=  $errorLogArray[$row]['Description'] = '';
							}						  
					}
				
				}elseif ( empty($value[$insertDataKeys['parentnid']]) || ($value[$insertDataKeys['parentnid']] == '-1')) {
		                        //case when parent id is empty 
						if($allAreblank == false  ){
						   $_SESSION['errorLog'][]= $errorLogArray[$row] = $value;
						}
					
						$value[$insertDataKeys['parentnid']] = '-1';
						$value[$insertDataKeys['level']]=1; // do hardcore level value 1 for parent area ids 
						$value[$insertDataKeys['level']] = $this->Area->returnAreaLevel($value[$insertDataKeys['level']],$value[$insertDataKeys['parentnid']]);
						
						$conditions =[];
						$fields =[];
						$areadbdetails = '';
						
						$conditions =[_AREA_AREA_ID => $value[$insertDataKeys['areaid']]];
			            $fields =[_AREA_AREA_ID];
						$chkAreaId = $this->Area->getDataByParams($fields,$conditions);
			           
						if(!empty($chkAreaId))
						$areadbdetails = current(current($chkAreaId));
					
						if($areadbdetails!=''){
							//case when parent nid is blank and also exists in database 
							$insertDataAreaids[$row] = $value[$insertDataKeys['areaid']]; // will be needed for  update 

							if($allAreblank == false){
								$_SESSION['errorLog']['STATUS'][] = 'Done';
								$_SESSION['errorLog']['Description'][] = '';
							}	
						
						}else{
							
							//case when parent nid is blank and also do not exists in database 
							$uniquestatus=false;
							//////////// code for last ids
							if(!empty($allAreaParents) && in_array($value[$insertDataKeys['areaid']],$allAreaParents)==true) {
								$uniquestatus = true; // if value is  unset will not be added in array 
								
							}
							if($uniquestatus ==false){
									//
								if(empty($allAreaParents)  ||  !in_array($value[$insertDataKeys['areaid']],$allAreaParents)){
							 
								$forParentAreaId[$value[$insertDataKeys['areaid']]]['parentiddetails']=$value;
								foreach($compareAreaidParentId as $index=>$compareParentId){
									if(($value[$insertDataKeys['areaid']]==$compareParentId[4]) && ($index>$row)){
										$LogAreaIds[$index]=$compareParentId[0];
										$combinedValue  = array_combine($insertDataKeys,$compareParentId);
										$combinedValue[$insertDataKeys['level']] = $this->Area->returnAreaLevel($combinedValue[$insertDataKeys['level']],$combinedValue[$insertDataKeys['parentnid']],'NEW');								
										if(empty($allAreaIdsAsSubParent) ||  !in_array($compareParentId[0],$allAreaIdsAsSubParent))
										$forParentAreaId[$value[$insertDataKeys['areaid']]]['childiddetails'][]=$combinedValue;
										$allAreaIdsAsSubParent[] = $compareParentId[0];
									
									
									}
								
									if(($value[$insertDataKeys['areaid']]==$compareParentId[4])&& ($index<$row)){
										$ignoreAreaIdsAsSubParent[$index]=$compareParentId[0]; //ignore area ids which not to be inserted 								
									}
									
							    }
							  }
							  
								$allAreaParents[]=$value[$insertDataKeys['areaid']]; // adding all parent ids 
						
								$insertDataAreaids[$row] = $value[$insertDataKeys['areaid']]; // will be needed for  update 
							  
								if($allAreblank == false){
									 $_SESSION['errorLog']['STATUS'][] = $errorLogArray[$row]['STATUS'] = 'Done';
									 $_SESSION['errorLog']['Description'][] = $errorLogArray[$row]['Description'] = '';	
								  }
									//
							}else{
							
								if($allAreblank == false){
									 $_SESSION['errorLog']['STATUS'][] = $errorLogArray[$row]['STATUS'] = 'Done';  // 'Error';
									 $_SESSION['errorLog']['Description'][] = $errorLogArray[$row]['Description'] = '';	//'Duplicate entry of parent id ';
										//$_SESSION['errorLog']['STATUS'][]=$errorLogArray[$row]['STATUS'] = 'Error';
										//$_SESSION['errorLog']['Description'][]=  $errorLogArray[$row]['Description'] = 'Duplicate entry of parent id ';
								  }
								unset($value);
								unset($newinsertDataArr[$row]);
							}
							
							
						}
						
					
				
				}else {
						if($allAreblank == false  ){
							$_SESSION['errorLog'][]=$errorLogArray[$row] = $value;
							$_SESSION['errorLog']['STATUS'][] = $errorLogArray[$row]['STATUS'] = 'Error';
							$_SESSION['errorLog']['Description'][] = $errorLogArray[$row]['Description'] = 'Invalid Details ';

						}
					   
						unset($value); 

						unset($newinsertDataArr[$row]);
                    }
				
				} // end of isset of area id 
				
				
				
				
            }// for loop

        }
        $newinsertDataArr = array_filter($newinsertDataArr);
		$allParentChild = array_merge($allAreaIdsAsSubParent,$allAreaParents);

       
		
        return ['dataArray' => $newinsertDataArr, 'insertDataAreaids' => $insertDataAreaids, 'allParentChild' => $allParentChild,'ignoreAreaIdsAsSubParent'=>$ignoreAreaIdsAsSubParent, 'forParentAreaId'=>$forParentAreaId];
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
     *  $extra['callfunction'] is the parameter if its Area it will execute for area 
     * 
     * 
     */

    function splitInsertUpdate($insertDataKeys, $insertDataArr, $extra) {
        if (array_key_exists('callfunction', $extra) && $extra['callfunction'] == 'Area') {
            return $this->divideAreaids($insertDataKeys, $insertDataArr, $extra);
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
        $extra['limitRows'] = 20; // Number of rows in each file chunks
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
        } else if ($component == 'Area') {
            //$params['nid'] = _AREA_AREA_NID;
            return $this->bulkUploadXlsOrCsvForArea($divideXlsOrCsvInChunks, $extra);
        }

        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;

        // Bulk upload each chunk separately
        foreach ($divideXlsOrCsvInChunks as $filename) {
            $loadDataFromXlsOrCsv = $this->prepareDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
            $this->nameGidLogic($loadDataFromXlsOrCsv, $component, $params);
            unlink($filename);
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
        //Gives dataArray, insertDataNames, insertDataGids,insertDataAreaids
        extract($loadDataFromXlsOrCsv);
        $insertArrayFromGids = [];
        $insertArrayFromNames = [];
		//pr($loadDataFromXlsOrCsv);
        $insertDataAreaIdsData = [];
        $extraParam['updateGid'] = isset($params['updateGid']) ? $params['updateGid'] : false;
        $insertDataKeys = $params['insertDataKeys'];
        $extraParam['logFileName'] = isset($params['logFileName']) ? $params['logFileName'] : false;

        //Update records based on Indicator GID
        if ($extraParam['updateGid'] == true) {
            if (!empty($insertDataGids)) {
                $extraParam['nid'] = $params['nid'];
                $extraParam['component'] = $component;
                $insertArrayFromGids = $this->updateColumnsFromGid($insertDataGids, $dataArray, $insertDataKeys, $extraParam);
                unset($insertDataGids); //save Buffer
            }
        }

        //Update records based on Indicator Name
        if (!empty($insertDataNames)) {
            $extraParam['nid'] = $params['nid'];
            $extraParam['component'] = $component;
            $insertArrayFromNames = $this->updateColumnsFromName($insertDataNames, $dataArray, $insertDataKeys, $extraParam);
            unset($insertDataNames);    //save Buffer
        }

        //Update records based on Area ids
	
        if (!empty($insertDataAreaids)) {
			
            $extraParam['nid'] = $params['nid'];
            $extraParam['component'] = $component;
            $insertDataAreaIdsData = $this->updateColumnsFromAreaIds($insertDataAreaids, $dataArray, $insertDataKeys,$allParentChild,$ignoreAreaIdsAsSubParent,$forParentAreaId,$extraParam);
            unset($insertDataAreaids);  //save Buffer
        }
        
        $insertArray = array_merge(array_keys($insertArrayFromGids), array_keys($insertArrayFromNames), array_keys($insertDataAreaIdsData));        

        //save Buffer
        unset($insertArrayFromGids);
        unset($insertArrayFromNames);
        unset($insertDataAreaIds);

        $insertArray = array_flip($insertArray);	
        $insertArray = array_intersect_key($dataArray, $insertArray);      
        unset($dataArray);  //save Buffer
        //Check if New records
        if (!empty($insertArray)) {
            //Prepare Insert Data
            array_walk($insertArray, function(&$val, $key) use($params, $insertDataKeys) {
                //auto-generate GUID if not set
                if (!array_key_exists($insertDataKeys['gid'], $val)) {
                    $autoGenGuid = $this->guid();
                    $val[$insertDataKeys['gid']] = $autoGenGuid;
                }
                //If 'highIsGood' needs to be inserted but is blank, keep default value 0
                if (array_key_exists('highIsGood', $insertDataKeys) && !array_key_exists($insertDataKeys['highIsGood'], $val)) {
                    $val[$insertDataKeys['highIsGood']] = 0;
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

        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;

        foreach ($divideXlsOrCsvInChunks as $filename) {

            $objPHPExcel = $this->readXlsOrCsv($filename);

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();
                $highestRow = $worksheet->getHighestRow(); // e.g. 10
                $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

				if ($highestRow == 1) {
                    return ['error' => 'The file is empty'];
                }

                //Initialize Vars
                $insertFieldsArr = [];
                $insertDataArrRows = [];
                $insertDataArrCols = [];
                $unsettedKeys = [];

                $insertFieldsArr = [];
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

                        if ($row == 1) {    //Headings row
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
                            if (!isset($indicatorFieldKey)) {
                                $indicatorFieldKey = array_search(strtolower('Indicator'), array_map('strtolower', $insertFieldsArr));
                            }
                            if (!isset($indicatorGidFieldKey)) {
                                $indicatorGidFieldKey = array_search(strtolower('IndicatorGid'), array_map('strtolower', $insertFieldsArr));
                            }
                            if (!isset($unitFieldKey)) {
                                $unitFieldKey = array_search(strtolower('Unit'), array_map('strtolower', $insertFieldsArr));
                            }
                            if (!isset($unitGidFieldKey)) {
                                $unitGidFieldKey = array_search(strtolower('UnitGid'), array_map('strtolower', $insertFieldsArr));
                            }
                            if (!isset($subgroupValFieldKey)) {
                                $subgroupValFieldKey = array_search(strtolower('Subgroup'), array_map('strtolower', $insertFieldsArr));
                                if (gettype($subgroupValFieldKey) == 'integer') {
                                    $subgroupTypeFieldKey = $subgroupValFieldKey + 1;
                                }
                            }
                            if (!isset($subgroupValGidFieldKey)) {
                                $subgroupValGidFieldKey = array_search(strtolower('SubgroupGid'), array_map('strtolower', $insertFieldsArr));
                                if (gettype($subgroupValGidFieldKey) == 'integer') {
                                    $subgroupTypeFieldKey = $subgroupValGidFieldKey + 1;
                                }
                            }

                            $insertDataArrRows[$row][] = $val;
                            $insertDataArrCols[$col][$row] = $val;

                            if (($col != 0) && ($col < $indicatorFieldKey)) {
                                if ($col == 1 && !empty($val)) {
                                    $levelArray[$row][] = $val;
                                } else if (isset($levelArray[$row])) {
                                    $levelArray[$row][] = $val;
                                } else {  //--- maintain error log ---//
                                    $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, 'IC Level1 Name is empty.');
                                }
                            } else {

                                if ($col == $indicatorFieldKey || (isset($indicatorGidFieldKey) && $col == $indicatorGidFieldKey)) {
                                    if ($col == $indicatorFieldKey && !empty($val)) {
                                        $indicatorArray[$row][] = $val;
                                    } else if (isset($indicatorArray[$row])) {
                                        $indicatorArray[$row][] = $val;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, 'Indicator is empty.');
                                    }
                                } else if ($col == $unitFieldKey || (isset($unitGidFieldKey) && $col == $unitGidFieldKey)) {
                                    if ($col == $unitFieldKey && !empty($val)) {
                                        $unitArray[$row][] = $val;
                                    } else if (isset($unitArray[$row])) {
                                        $unitArray[$row][] = $val;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, 'Unit is empty.');
                                    }
                                } else if ($col == $subgroupValFieldKey || (isset($subgroupValGidFieldKey) && $col == $subgroupValGidFieldKey)) {
                                    if ($col == $subgroupValFieldKey && !empty($val)) {
                                        $subgroupValArray[$row][] = $val;
                                    } else if (isset($subgroupValArray[$row])) {
                                        $subgroupValArray[$row][] = $val;
                                    } else {  //--- maintain error log ---//
                                        $unsettedKeys = $this->maintainErrorLogs($row, $unsettedKeys, 'Subgroup is empty.');
                                    }
                                } else if ($col >= $subgroupTypeFieldKey) {
                                    if (isset($subgroupValArray[$row])) {
                                        $subgroupTypeArray[$row][] = $val;
                                    }
                                }
                            }
                        }
                    }

                    //Check Columns format
                    if ($row == 1) {
                        $validFormat = $this->importFormatCheck('icius');
                        $formatDiff = array_diff($validFormat, array_map('strtolower', $insertFieldsArr));
                        if (!empty($formatDiff)) {
                            return ['error' => 'Invalid Columns Format'];
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
                }
            }

            $indicatorFieldKey = array_search(strtolower('Indicator'), array_map('strtolower', $insertFieldsArr));
            $subgroupFieldKey = array_search(strtolower('SubgroupGid'), array_map('strtolower', $insertFieldsArr));

            $insertDataArrColsLevel1 = array_unique(array_filter(array_values($insertDataArrCols[1])));
            $insertDataArrRowsFiltered = $insertDataArrRows;

            foreach ($insertDataArrCols as $key => $value) {

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
                        array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination, &$ICArray, &$levelArray) {
                            if (!empty($val[$key - 1])) {
                                $parent_Nid = -1;
                                $val[$key - 1] = array_search("(" . $parent_Nid . ",'" . $val[$key - 1] . "')", $levelIcRecsWithNids);
                                $levelCombination[$index] = "(" . $parent_Nid . ",'" . $val[$key - 1] . "')";
                                $ICArray[$index] = $val[$key - 1];
                            }
                        });
                    } else { // IC Level > Level-1
                        // Use below line when 'all' selected in getConcatedFields used generate $levelIcRecsWithNids
                        //$levelIcRecsWithNids = array_column($levelIcRecsWithNids, 'concatinated', _IC_IC_NID);
                        array_walk($levelArray, function(&$val, $index) use ($key, $levelIcRecsWithNids, &$levelCombination) {
                            if (!empty($val[$key - 1])) {
                                $parent_Nid = $val[$key - 2];
                                $levelCombination[$index] = "(" . $val[$key - 2] . ",'" . $val[$key - 1] . "')";
                            }
                        });

                        $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_NID];
                        $conditions = ['(' . _IC_IC_PARENT_NID . ',' . _IC_IC_NAME . ') IN (' . implode(',', array_unique($levelCombination)) . ')'];
                        $getConcatedFields = $this->IndicatorClassifications->getConcatedFields($fields, $conditions, 'list');

                        $field = [];
                        $field[] = _IC_IC_NAME;
                        $field[] = _IC_IC_PARENT_NID;
                        $field[] = _IC_IC_GID;
                        $field[] = _IC_IC_TYPE;

                        //------ Prepare New records
                        $insertResults = array_unique(array_filter(array_diff($levelCombination, $getConcatedFields)));
                        if (!empty($insertResults)) {
                            array_walk($insertResults, function(&$val, $rowIndex) use ($field, $levelArray, $key, $icTypes) {
                                if (!empty($val)) {
                                    $returnFields = [];
                                    $returnFields[$field[0]] = $levelArray[$rowIndex][$key - 1];
                                    $returnFields[$field[1]] = $levelArray[$rowIndex][$key - 2];
                                    $returnFields[$field[2]] = $this->guid();
                                    $returnFields[$field[3]] = $icTypes[$rowIndex];
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
                        $conditions = ['(' . _IC_IC_PARENT_NID . ',' . _IC_IC_NAME . ') IN (' . implode(',', $levelCombination) . ')'];
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

                    if ($key == $indicatorFieldKey) {   //--- INDICATOR ---//
                        $indicatorRecWithNids = $this->saveAndGetIndicatorRecWithNids($indicatorArray);
                    } else if ($key == $unitFieldKey) { //--- UNIT ---//
                        $unitRecWithNids = $this->saveAndGetUnitRecWithNids($unitArray);
                    } else if ($key == $subgroupValFieldKey) {  //--- SUBGROUP_VALS ---//
                        $extraParam['key'] = $key;
                        $subgroupValsNIdsReturn = $this->saveAndGetSubgroupValsRecWithNids($subgroupValArray, $extraParam);
                        $allSubgroups = $subgroupValsNIdsReturn['allSubgroups'];
                        $subgroupValsNIds = $subgroupValsNIdsReturn['subgroupValsNIds'];
                    } else if ($key >= $subgroupTypeFieldKey) { //--- SUBGROUP DIMENSIONS ---//
                        if (!isset($getSubGroupTypeNidAndName)) {
                            //$subgroupValsNIds = $this->saveAndGetSubGroupTypeRecWithNids($subgroupTypeFields);
                            $getSubGroupTypeNidAndNameReturn = $this->getSubGroupTypeNidAndName($subgroupTypeFields);
                            $getSubGroupTypeNidAndName = $getSubGroupTypeNidAndNameReturn['getSubGroupTypeNidAndName'];
                            $subGroupTypeList = $getSubGroupTypeNidAndNameReturn['subGroupTypeList'];
                        }
                        $subgroupType = array_search($subGroupTypeList[$key], $getSubGroupTypeNidAndName);

                        if (isset($allSubgroups)) {
                            array_walk($allSubgroups, function(&$val, $index) use ($valueOriginal, $key, $subgroupType, &$subGroupValsConditions, &$subGroupValsConditionsWithRowIndex,&$subGroupValsConditionsArray) {
                                if (!empty($valueOriginal[$index])) {
                                    $return = $val;
                                    $return[$key] = $valueOriginal[$index];
                                    //$return[count($val)] = $valueOriginal[$index];
                                    $subGroupValsConditionsWithRowIndex[$index][] = '("' . $valueOriginal[$index] . '",' . $subgroupType . ')';
                                    $subGroupValsConditions[] = '("' . $valueOriginal[$index] . '",' . $subgroupType . ')';
                                    $subGroupValsConditionsArrayFields[_SUBGROUP_SUBGROUP_NAME] = $valueOriginal[$index];
                                    $subGroupValsConditionsArrayFields[_SUBGROUP_SUBGROUP_TYPE] = $subgroupType;
                                    $subGroupValsConditionsArray[] = $subGroupValsConditionsArrayFields;
                                    $subGroupValsConditions = array_unique($subGroupValsConditions);
                                    $val = $return;
                                }
                            });
                        }

                        $conditions = [_SUBGROUP_SUBGROUP_TYPE => $subgroupType];
                        $maxSubgroupOrder = $this->Subgroup->getMax(_SUBGROUP_SUBGROUP_ORDER, $conditions);

                        array_walk($value, function(&$val) use($subgroupType, &$maxSubgroupOrder) {
                            $returnData = [];
                            $returnData[] = $val;
                            $returnData[] = '';
                            $returnData[] = $subgroupType;
                            $returnData[] = ++$maxSubgroupOrder;
                            $val = $returnData;
                        });

                        $insertDataKeys = ['name' => _SUBGROUP_SUBGROUP_NAME, 'gid' => _SUBGROUP_SUBGROUP_GID, 'subgroup_type' => _SUBGROUP_SUBGROUP_TYPE, 'subgroup_order' => _SUBGROUP_SUBGROUP_ORDER];
                        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $value);

                        $params['nid'] = _SUBGROUP_SUBGROUP_NID;
                        $params['insertDataKeys'] = $insertDataKeys;
                        $params['updateGid'] = FALSE;
                        $component = 'Subgroup';

                        $this->nameGidLogic($divideNameAndGids, $component, $params);
                        $subGroupValsConditionsArrayFiltered = array_intersect_key($subGroupValsConditionsArray, $subGroupValsConditions);
                        
                        //Last Dimension Column
                        if ($key == (array_keys($subGroupTypeList)[count(array_keys($subGroupTypeList)) - 1])) {
                            
                            //$conditions = ['(' . _SUBGROUP_SUBGROUP_NAME . ',' . _SUBGROUP_SUBGROUP_TYPE . ') IN (' . implode(',', $subGroupValsConditions) . ')'];
                            $conditions = ['OR' => $subGroupValsConditionsArrayFiltered];
                            $getSubGroupNidAndName = $this->Subgroup->getDataByParams(
                                    [_SUBGROUP_SUBGROUP_NID, _SUBGROUP_SUBGROUP_NAME], $conditions, 'list');

                            array_walk($allSubgroups, function($val, $index) use ($getSubGroupNidAndName, $subgroupValFieldKey, $subgroupValsNIds, $getSubGroupNidAndName, &$subGroupValsComb, &$subGroupValsCombArray) {
                                $subgroupvalsubgroup = $val;
                                $subgroup = $subgroupvalsubgroup[$subgroupValFieldKey];
                                unset($subgroupvalsubgroup[$subgroupValFieldKey]);

                                //Ensure the Dimensions are given
                                if (!empty($subgroupvalsubgroup)) {
                                    foreach ($subgroupvalsubgroup as $dimKey => $dimVal) {
                                        $subGroupValsComb[] = '(' . array_search($subgroup, $subgroupValsNIds) . ',' . array_search($dimVal, $getSubGroupNidAndName) . ')';
                                        $subGroupValsCombArray[] = [
                                            _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID => array_search($subgroup, $subgroupValsNIds),
                                            SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID => array_search($dimVal, $getSubGroupNidAndName)
                                        ];
                                    }
                                    $subGroupValsComb = array_unique($subGroupValsComb);
                                }
                            });

                            $subGroupValsSubgroupWithNids = $this->SubgroupValsSubgroup->bulkInsert($subGroupValsComb, $subGroupValsCombArray);

                            $extra['group'] = _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID;
                            $extra['order'] = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID => 'ASC'];
                            $fields = [
                                _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID,
                                //SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . '_CONCATED' => 'GROUP_CONCAT(' . SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' ORDER BY ' . SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ')'];
                                SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
                            $conditions = [ _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID . ' IN' => array_keys($subgroupValsNIds)];
                            $subGroupNidGroupedBySubgroupValNids = $this->SubgroupValsSubgroup->getDataByParams($fields, $conditions, 'all', $extra);
                            $subGroupNidGroupedBySubgroupValNids = array_column($subGroupNidGroupedBySubgroupValNids, SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . '_CONCATED', _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID);

                            //debug($subGroupNidGroupedBySubgroupValNids); 
                        }
                    }
                }
            } //Individual Column Foreach Ends
            //------------- IUS ------------//
            $iusCombinations = [];
            //$insertDataArrRowsFiltered = array_intersect_key($insertDataArrRowsFiltered, array_unique(array_map('serialize', $insertDataArrRowsFiltered)));

            $unsettedKeysNew = array_intersect_key($unsettedKeys, array_filter(array_intersect_key(array_map('array_filter', $insertDataArrRowsFiltered), $unsettedKeys)));
            $insertDataArrRowsFiltered = array_diff_key($insertDataArrRowsFiltered, $unsettedKeys);
            $unsettedKeys = $unsettedKeysNew;
            unset($unsettedKeysNew);

            // Prepare IUS
            foreach ($insertDataArrRowsFiltered as $key => $val) {

                //Skip records entry if Indicator OR Unit OR Subgroup is not found.
                if (empty($val[$indicatorFieldKey]) || empty($val[$unitFieldKey]) || empty($val[$subgroupValFieldKey])) {
                    unset($insertDataArrRowsFiltered[$key]);
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
                /*$conditions = ['('
                    . _IUS_INDICATOR_NID
                    . ',' . _IUS_UNIT_NID
                    . ',' . _IUS_SUBGROUP_VAL_NID
                    . ',' . _IUS_SUBGROUP_NIDS
                    . ') IN ('
                    . implode(',', $iusCombinationsCond)
                    . ')'];*/

                $conditions = ['OR' => $iusCombinations];
                //debug($conditions);exit;
                $getExistingRecords = $this->IndicatorUnitSubgroup->getConcatedIus($columnKeys, $conditions, 'list');
                
                if (!empty($getExistingRecords)) {
                    $iusCombinations = array_diff_key($iusCombinations, array_intersect($iusCombinationsCond, $getExistingRecords));
                }
//debug($iusCombinations);exit;
                if (!empty($iusCombinations)) {
                    // Insert New IUS records
                    $insertDataKeys = [_IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_SUBGROUP_NIDS];
                    $this->IndicatorUnitSubgroup->insertBulkData($iusCombinations, $insertDataKeys);
                }

                $getExistingRecords = $this->IndicatorUnitSubgroup->getConcatedIus($columnKeys, $conditions, 'list');
            }

            //------------- ICIUS ------------//
            $extraIcius['iusCombinationsCond'] = $iusCombinationsCond;
            $extraIcius['getExistingRecords'] = $getExistingRecords;
            $extraIcius['ICArray'] = $ICArray;
            $this->bulkInsertIcIus($insertDataArrRowsFiltered, $extraIcius);

            $unsettedKeysAllChunksArr[] = $unsettedKeys;
            $allChunksRowsArr[] = array_keys($insertDataArrRows);

            // ---- ICIUS successfully added - chunk
        }// Chunk Loop

        return $this->createImportLog($allChunksRowsArr, $unsettedKeysAllChunksArr);

        // ---- ICIUS successfully added - whole file
        //debug($unsettedKeys);
        //debug('ICIUS Successfully added');
        //exit;
        //return true;
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
        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID];
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
        $insertDataKeys = ['name' => _UNIT_UNIT_NAME, 'gid' => _UNIT_UNIT_GID];
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
            'subgroup_val_order' => _SUBGROUP_VAL_SUBGROUP_VAL_ORDER
        ];

        $maxSubgroupValOrder = $this->SubgroupVals->getMax(_SUBGROUP_VAL_SUBGROUP_VAL_ORDER);
        $subgroupValArrayUnique = array_intersect_key($subgroupValArray, array_unique(array_map('serialize', $subgroupValArray)));
        $allSubgroups = array_filter(array_combine(array_keys($subgroupValArray), array_column($subgroupValArray, 0)));

        array_walk($allSubgroups, function(&$val) use($key) {
            $return = [];
            $return[$key] = $val;
            $val = $return;
        });

        array_walk($subgroupValArrayUnique, function(&$val, $index) use(&$subgroupValArrayUnique, &$maxSubgroupValOrder, &$subgroupValsName) {
            if (empty(array_filter($val))) {
                unset($subgroupValArrayUnique[$index]);
            } else {
                $val[] = ++$maxSubgroupValOrder;
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
        return ['allSubgroups' => $allSubgroups, 'subgroupValsNIds' => $subgroupValsNIds];
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

        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'highIsGood' => _INDICATOR_HIGHISGOOD];
        $extra['limitRows'] = 1000; // Number of rows in each file chunks
        $extra['startRows'] = 2; // Row from where the data reading starts

        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunks($filename, $extra);

        foreach ($divideXlsOrCsvInChunks as $filename) {

            $loadDataFromXlsOrCsv = $this->loadDataFromXlsOrCsv($filename, $insertDataKeys, $extra);

            $params['insertDataKeys'] = $insertDataKeys;
            $params['updateGid'] = TRUE;
            $params['nid'] = _INDICATOR_INDICATOR_NID;

            $component = 'Indicator';

            $this->bulkInsert($component, $loadDataFromXlsOrCsv, $params);
            unlink($filename);
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
     * updateColumnsFromName method
     *
     * @param array $names Names Array. {DEFAULT : empty}
     * @return void
     */
    public function updateColumnsFromAreaIds($areaids = [], $dataArray, $insertDataKeys,$allParentChild,$ignoreAreaIdsAsSubParent,$forParentAreaId, $extra = null) {
		
		
        $component = 'Area';
        $fields = [$extra['nid'], $insertDataKeys['areaid']];
        $conditions = [$insertDataKeys['areaid'] . ' IN' => $areaids];
        $updateGid = $extra['updateGid']; // true/false
        //Get NIds based on areaid found in db 
        $getDataByAreaid = $this->{$component}->getDataByParams($fields, $conditions, 'list');//data which needs to be updated       
		
		if (!empty($getDataByAreaid)) {
            foreach ($getDataByAreaid as $Nid => $areaId) {
                $key = array_search($areaId, $areaids);
                $updateData = $dataArray[$key]; // data which needs to be updated using area  nid                 		
                $this->{$component}->updateDataByParams($updateData, [$extra['nid'] => $Nid]);
            }
        }
		
        //Get Areaids that are not found in the database
		$freshRecordsNames = array_diff($areaids, $getDataByAreaid);// records which needs to be inserted 
		$freshRecordsNames = array_unique($freshRecordsNames);		 
		$finalrecordsforinsert = array_diff($freshRecordsNames,$allParentChild);
		
		if(!empty($forParentAreaId)){			
			
			foreach($forParentAreaId as $parentAreaId=>$AreaData){				  
		   		  $parentNewid='';	
				  $areaParentData='';
					if(empty($AreaData['parentiddetails'][$insertDataKeys['gid']])){
						$AreaData['parentiddetails'][$insertDataKeys['gid']]= $this->guid();
					}
					if(!array_key_exists($insertDataKeys['gid'],$AreaData['parentiddetails'])){
						$AreaData['parentiddetails'][$insertDataKeys['gid']]= $this->guid();
					}
					
				  $areaParentData  = 	$AreaData['parentiddetails'];				 
				  $parentNewid = $this->{$component}->insertUpdateAreaData($areaParentData);
				 
				  if(isset($AreaData['childiddetails']) && count($AreaData['childiddetails'])>0){
					array_walk($AreaData['childiddetails'],function(&$val, $key) use($parentNewid,$insertDataKeys) {
						
						$autoGenGuid = $this->guid();

						if (!array_key_exists($insertDataKeys['gid'], $val)) {
						   $val[$insertDataKeys['gid']] = $autoGenGuid;
                         }
						if(empty($val[$insertDataKeys['gid']])){
						    $val[$insertDataKeys['gid']] = $autoGenGuid;                    
						}
						$val[$insertDataKeys['parentnid']]=$parentNewid;						
						
					});
					
					$bulkchildiddetails = $AreaData['childiddetails'];
					$this->{$component}->insertBulkData($bulkchildiddetails, $insertDataKeys);
				  }
			}		
			
		}
		return  $finalrecordsforinsert;
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
        $conditions = [$insertDataKeys['name'] . ' IN' => $names];
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
        $conditions = [$insertDataKeys['gid'] . ' IN' => $gids];
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
     * divideXlsOrCsvInChunks method
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function divideXlsOrCsvInChunks($filename = null, $extra = null) {

        //The following line should do the same like App::import() in the older version of cakePHP 
        $objPHPExcel = $this->readXlsOrCsv($filename);
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;
        $filesArray = [];

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g. 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

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

                    $currentRow = $row - (($sheet - 1) * $limitRows);

                    $PHPExcel->getActiveSheet()->SetCellValue($character . $currentRow, $val);
                    $character++;
                }

                if (($row == $endrows) || ($row == $highestRow)) {
                    //echo '<pre>'; print_r($PHPExcel);
                    $PHPExcel->setActiveSheetIndex(0);
                    $objWriter = new \PHPExcel_Writer_Excel2007($PHPExcel);
                    $sheetPath = WWW_ROOT . 'uploads' . DS . time() . $sheet . '.xls';
                    $objWriter->save($sheetPath);
                    $filesArray[] = $sheetPath;
                    $PHPExcel = new \PHPExcel();
                    $startRows += $limitRows;
                    $sheet++;
                }
            }
        }

        return $filesArray;
    }

    /**
     * bulkUploadXlsOrCsvForArea method
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function bulkUploadXlsOrCsvForArea($filename = [], $extra = null) {

        $insertFieldsArr = [];
        $insertDataArrRows = [];
        $insertDataArrCols = [];
        $extra['limitRows'] = 500; // Number of rows in each area chunks file 
        $extra['startRows'] = 1; // Row from where the data reading starts
        $extra['callfunction'] = 'Area';

        $insertDataKeys = [_INSERTKEYS_AREAID => _AREA_AREA_ID,
            _INSERTKEYS_NAME=> _AREA_AREA_NAME,
            _INSERTKEYS_LEVEL => _AREA_AREA_LEVEL,
            _INSERTKEYS_GID => _AREA_AREA_GID,
            _INSERTKEYS_PARENTNID => _AREA_PARENT_NId,
        ];

        $objPHPExcel = $this->readXlsOrCsv($filename['filename']);
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = 1; //first row 
        $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn(); // e.g. 'F'
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        // code for validation of uploaded  file 
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow(); // e.g. 10   			
        if ($highestRow == 1) {
            return ['error' => 'The file is empty'];
        }
        $titlearray = [];  // for titles of sheet
        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $cell = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $startRow);
            $titlearray[] = $val = $cell->getValue();
        }
        $validFormat = $this->importFormatCheck(_MODULE_NAME_AREA);  //Check file Columns format
        $formatDiff = array_diff($validFormat, array_map('strtolower', $titlearray));
        if (!empty($formatDiff)) {
            return ['error' => 'Invalid Columns Format'];
        }

        // end of file validation 	


        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunkFiles($objPHPExcel, $extra); // split  the file in chunks 

        $firstRow = ['A' => 'AreaId', 'B' => 'AreaName', 'C' => 'AreaLevel', 'D' => 'AreaGId', 'E' => 'Parent AreaId', 'F' => 'Status', 'G' => 'Description'];
        $areaErrorLog = $this->createErrorLog($firstRow, 'Area');   //returns error log file 

        foreach ($divideXlsOrCsvInChunks as $filename) {
            
			$loadDataFromXlsOrCsv = $this->prepareDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
			
			array_walk($loadDataFromXlsOrCsv['dataArray'], function(&$val, $key) use($insertDataKeys) {
			  if (!array_key_exists($insertDataKeys['gid'], $val)) {
                    $val[$insertDataKeys['gid']] = $this->guid();
                }
            });
            $component = 'Area';
            $params['nid'] = _AREA_AREA_NID;
            $params['insertDataKeys'] = $insertDataKeys;           
            $params['updateGid'] = TRUE;

            $this->nameGidLogic($loadDataFromXlsOrCsv, $component, $params);
            unlink($filename);
        }
		
        $this->appendErrorLogData(WWW_ROOT.$areaErrorLog,$_SESSION['errorLog']); //
        return true;
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

        // Prepare ICIUS
        foreach ($insertDataArrRowsFiltered as $key => $val) {
            $ius = array_search($iusCombinationsCond[$key], $getExistingRecords);
            if (isset($ICArray[$key]) && $ius !== false) {
                $IcIusDataArray[$key][_ICIUS_IC_NID] = $ICArray[$key];
                $IcIusDataArray[$key][_ICIUS_IUSNID] = $ius;
                $IcIusCombination[$key] = "(" . $ICArray[$key] . "," . $ius . ")";
            }
        }

        $fields = [_ICIUS_IC_NID, _ICIUS_IUSNID, _ICIUS_IC_IUSNID];
        $conditions = ['(' . _ICIUS_IC_NID . ',' . _ICIUS_IUSNID . ') IN (' . implode(',', array_unique($IcIusCombination)) . ')'];
        $getExistingRecords = $this->IcIus->getConcatedFields($fields, $conditions, 'list');

        if (!empty($getExistingRecords)) {
            $IcIusDataArray = array_diff_key($IcIusDataArray, array_intersect($IcIusCombination, $getExistingRecords));
        }
        if (!empty($IcIusDataArray)) {
            $insertDataKeys = [_ICIUS_IC_NID, _ICIUS_IUSNID];
            $this->IcIus->insertBulkData($IcIusDataArray, $insertDataKeys);
        }
    }

    /*
     * createImportLog
     *
     * @param array $allChunksRowsArr Sheet Rows indexes Array
     * @param array $unsettedKeysAllChunksArr Indexes having errors
     * @return Exported File path
     */

    public function createImportLog($allChunksRowsArr, $unsettedKeysAllChunksArr) {

        //$PHPExcel = new \PHPExcel();
        $sheet = 1;
        $chunkParams = $this->session->consume('ChunkParams');
        //$startRows = $chunkParams['startRows'];
        $limitRows = $chunkParams['limitRows'];
        $highestRow = $chunkParams['highestRow'];
        $highestColumn = $chunkParams['highestColumn'];

        $count = 0;
        $lastColumn = $highestColumn;
        $columnToWrite = [];
        $columnToWrite['status'] = ++$lastColumn;
        $columnToWrite['description'] = ++$lastColumn;
        $PHPExcel = $this->readXlsOrCsv(_LOGPATH);

        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . '1', _STATUS);
        $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . '1', _DESCRIPTION);
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
        $sheetPath = _LOGPATH;
        $objWriter->save($sheetPath);

        return _LOGPATH;
    }

    /*
     * exportIcius
     *
     * @return Exported File path
     */

    public function exportIcius() {

        $iciusFields = [_ICIUS_IC_NID, _ICIUS_IUSNID];
        $iciusConditions = [];
        $iciusRecords = $this->IcIus->getDataByParams($iciusFields, $iciusConditions);
        
        $icNids = array_column($iciusRecords, _ICIUS_IC_NID);
        $iusNids = array_unique(array_column($iciusRecords, _ICIUS_IUSNID));
        
        $icFields = [_IC_IC_NID, _IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_TYPE];
        $icConditions = [];//[_IC_IC_NID . ' IN' => array_unique($icNids)];
        $icRecords = $this->IndicatorClassifications->getDataByParams($icFields, $icConditions);
        
        $iusFields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_SUBGROUP_NIDS];
        $iusConditions = [_IUS_IUSNID . ' IN' => array_unique($iusNids)];
        $iusRecords = $this->IndicatorUnitSubgroup->getDataByParams($iusFields, $iusConditions);
        
        $parentChildNodes = $this->getParentChild('IndicatorClassifications', '-1');
        
        
        debug($parentChildNodes);
        debug(max(array_column($parentChildNodes, 'arrayDepth')));exit;
        // Write File
        $character = 'A';
        $PHPExcel = new \PHPExcel();
        $PHPExcel->getActiveSheet()->SetCellValue($character, $cellValue);
        $PHPExcel->setActiveSheetIndex(0);
        $objWriter = new \PHPExcel_Writer_Excel2007($PHPExcel);
        $sheetPath = _LOGPATH;
        $objWriter->save($sheetPath);
    }

    /*
     * createErrorLog used to create error logs 
     * 
     */

    public function createErrorLog($firstRowdata = [], $module) {
        unset($_SESSION['errorLog']['STATUS']);
        unset($_SESSION['errorLog']['Description']);
        unset($_SESSION['errorLog']);
		$authUserId = $this->Auth->User('id');   
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $rowCount = 1;
        foreach ($firstRowdata as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $returnFilename = _IMPORTERRORLOG_FILE . $module . '_' . $authUserId . '_' .time().'.xls';
        $objWriter->save($returnFilename);
        return $returnFilename;
    }

    /*
     *  function to append data 
     */

    public function appendErrorLogData($filename, $data = [],$firstRowdata=[]) {

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        $objPHPExcel->setActiveSheetIndex(0);
        $chrarrya = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        $cnt = 0;
		$startRow = 1;
        foreach ($firstRowdata as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $startRow, $value);
        }
		//$startRow = $objPHPExcel->getActiveSheet()->getHighestRow();
		$startRow=2;
		//pr($data);
		$statuslogArray=$data[_STATUS];
		$desclogArray=$data[_DESCRIPTION];
		unset($data[_STATUS]);
		unset($data[_DESCRIPTION]);
		$width =20;
	
        foreach ($data as $index => $value) { 
            
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_AREA_AREA_ID])) ? $value[_AREA_AREA_ID] : '' )->getColumnDimension('A')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_AREA_AREA_NAME])) ? $value[_AREA_AREA_NAME] : '')->getColumnDimension('B')->setWidth($width+10);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($value[_AREA_AREA_LEVEL])) ? $value[_AREA_AREA_LEVEL] : '')->getColumnDimension('C')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($value[_AREA_AREA_GID])) ? $value[_AREA_AREA_GID] : '' )->getColumnDimension('D')->setWidth($width+20);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $startRow, (isset($value[_AREA_PARENT_NId])) ? $value[_AREA_PARENT_NId] : '' )->getColumnDimension('E')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $startRow, (isset($statuslogArray[$index])) ? $statuslogArray[$index] : '' )->getColumnDimension('F')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $startRow, (isset($desclogArray[$index])) ? $desclogArray[$index] : '' )->getColumnDimension('G')->setWidth($width+5);
            $startRow++;
           
        }

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($filename);
    }

    /*
     * getParentChild
     */
    public function getParentChild($component, $parentNID) {

        $conditions = array();
        if($component == 'IndicatorClassifications'){
            $conditions[_IC_IC_PARENT_NID] = $parentNID;
            $order = array(_IC_IC_NAME => 'ASC');
        }else if($component == 'Area'){
            $conditions[_AREA_PARENT_NId] = $parentNID;
            $order = array(_AREA_AREA_NAME => 'ASC');
        }
        $recordlist = $this->{$component}->find('all', array('conditions' => $conditions, 'fields' => array(), 'order' => $order));

        $list = $this->getDataRecursive($recordlist, $component);
        //$list['levels'] = $AreaLevel->find('all', array());
        
        return $list;
    }
    
    /**
     * function to recursive call to get children areas
     *
     * @access public
     */
    function getDataRecursive($recordlist, $component) {
        
        $rec_list = array();
        // start loop through area data
        for ($lsCnt = 0; $lsCnt < count($recordlist); $lsCnt++) {
            
            $childExists = false;
            
            // get selected Rec details
            if($component == 'IndicatorClassifications'){
                $NId = $recordlist[$lsCnt][_IC_IC_NID];
                $ID = $recordlist[$lsCnt][_IC_IC_TYPE];
                $name = $recordlist[$lsCnt][_IC_IC_NAME];
                $parentNID = $recordlist[$lsCnt][_IC_IC_PARENT_NID];
                
                $childData = $this->{$component}->find('all', array('conditions' => array(_IC_IC_PARENT_NID => $NId), 'order' => array(_IC_IC_NAME => 'ASC')));
            }else if($component == 'Area'){
                $NId = $recordlist[$lsCnt][_AREA_AREA_NID];
                $ID = $recordlist[$lsCnt][_AREA_AREA_ID];
                $name = $recordlist[$lsCnt][_AREA_AREA_NAME];
                $parentNID = $recordlist[$lsCnt][_AREA_PARENT_NId];
                
                $childData = $this->{$component}->find('all', array('conditions' => array(_AREA_PARENT_NId => $NId), 'order' => array(_AREA_AREA_NAME => 'ASC')));
            }
            
            //if child data found
            if (count($childData) > 0) {
                $this->arrayDepthIterator = $this->arrayDepthIterator + 1;
                
                if($this->arrayDepthIterator > $this->arrayDepth){
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
	
	
}
    
