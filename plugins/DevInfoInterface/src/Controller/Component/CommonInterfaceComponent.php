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
        'DevInfoInterface.Data',
        'DevInfoInterface.Footnote',
        'Common',
        'DevInfoInterface.Language',
        'DevInfoInterface.Metadata',
    ];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->arrayDepth = 1;
        $this->arrayDepthIterator = 1;
        $this->icDepth = 1;
        $this->dbName = '';
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
    /*
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
	*/
	
	
	/*
	 method to generate unique guid 
	*/	
	function guid( $opt = false ){       //  Set to true/false as your default way to do this.

		if( function_exists('com_create_guid') ){
			if( $opt ){ return com_create_guid(); }
				else { return trim( com_create_guid(), '{}' ); }
			}
			else {
				mt_srand( (double)microtime() * 10000 );    // optional for php 4.2.0 and up.
				$charid = strtoupper( md5(uniqid(rand(), true)) );
				$hyphen = chr( 45 );    // "-"
				$left_curly = $opt ? chr(123) : "";     //  "{"
				$right_curly = $opt ? chr(125) : "";    //  "}"
				$uuid = $left_curly
					. substr( $charid, 0, 8 ) . $hyphen
					. substr( $charid, 8, 4 ) . $hyphen
					. substr( $charid, 12, 4 ) . $hyphen
					. substr( $charid, 16, 4 ) . $hyphen
					. substr( $charid, 20, 12 )
					. $right_curly;
				return $uuid;
				}
	}
	
	
	/*
	 method to check boundary length
	*/	
	public function checkBoundaryLength($inputVal='',$length =''){     
	
	    if($inputVal!=''){
			if(strlen($inputVal)>$length){
				return false;
			}
		}
		return true;
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
            if(count($insertDataKeys) != count($value)) {debug($insertDataKeys);debug($value);exit;}
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
    public function readXlsOrCsv($filename = null, $unlinkFile = true) {

        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        if($unlinkFile == true) $this->unlinkFiles($filename); // Delete The uploaded file
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
     * resetChunkAreaData removes the first title row from chunk
     * @$data is the data array 
     */
    public function resetChunkAreaData($data) {
        $limitedRows = [];
        $cnt = 0;
        foreach ($data as $index => $valueArray) {
            if ($index == 1) {
                unset($valueArray);
            }if ($index > 1) {
                foreach ($valueArray as $innerIndex => $innervalueArray) {

                    if ($innerIndex > 4)
                        break;
                    $limitedRows[$index][$innerIndex] = $innervalueArray;

                    unset($innervalueArray);
                    $cnt++;
                }
            }

            unset($valueArray);
        }

        return $limitedRows;
    }

    /*
     *  arealogDetails method to return array
     * @param array $status ,$description . {DEFAULT : null}
     */

    public function arealogDetails($status = '', $description = '') {
        $data = [];
        $data[_STATUS] = $status;
        $data[_DESCRIPTION] = $description;
        return $data;
    }    
    
    /*
    returns array with   area ids and parent ids present in sheet
	  
    */

    public function getexcelAreaParentIds($data = [], $insertDataKeys) {

        $insertDataAreaParentids = $getAllExcelAreaids = $limitedAreaLevelRows = [];

        foreach ($data as $row => &$value) {

            $value = array_combine($insertDataKeys, $value);
            $value = array_filter($value);
            $limitedAreaLevelRows[] = (isset($value[$insertDataKeys[_INSERTKEYS_LEVEL]])) ? $value[$insertDataKeys[_INSERTKEYS_LEVEL]] : '';
            if (array_key_exists(_INSERTKEYS_AREAID, $insertDataKeys) && !isset($value[$insertDataKeys[_INSERTKEYS_AREAID]])) {
                unset($value); //unset($newcats); //removing unnecesaary row 
            } else if (isset($value[$insertDataKeys[_INSERTKEYS_AREAID]])) {
                $getAllExcelAreaids[$row] = $value[$insertDataKeys[_INSERTKEYS_AREAID]];
                if (!empty($value[$insertDataKeys[_INSERTKEYS_PARENTNID]]))
                    $insertDataAreaParentids[$row] = $value[$insertDataKeys[_INSERTKEYS_PARENTNID]];
            }
        }

        $insertDataAreaParentids = array_unique($insertDataAreaParentids);

        return ['getAllExcelAreaids' => $getAllExcelAreaids, 'insertDataAreaParentids' => $insertDataAreaParentids];
    }
	
	
	 /*
      processAreaCase1 returns array with area  log details of status and message
	  method is called when  parent id is not empty in excel sheet  and exists in database also 
	  
     */
    public function processAreaCase1($params, $areaidswithParentId, $getAllDbAreaIds, $areaidAlradyexistStatus) {
         
        //case when parent id is not empty and exists in database also 
        $gidStatus = false;									$indexGid='';  $gidFormat =false;
        $allAreblank 	=	$params['allAreblank'] ; 		$insertDataKeys = $params['insertDataKeys'] ;
		$allGids 		= 	$params['allGids'] ;			$excelAreaId 	= $params['excelAreaId'] ;
        $indexParentAreaId = $params['indexParentAreaId'] ; $value 			= $params['value'] ; 
		$indexGid = $insertDataKeys['gid'];
		$row = $params['row'] ;

        if (!array_key_exists($insertDataKeys['level'], $value)) {
            $level = '';
        } else {
            $level = $value[$insertDataKeys['level']];
        }
        //returns area level and and any warning if exists 
        $levelDetails = $this->Area->returnAreaLevel($level, $value[$indexParentAreaId], $row);
        $value[$insertDataKeys['level']] = $levelDetails['level'];
        $value[$indexParentAreaId] = array_search($value[$indexParentAreaId], $areaidswithParentId);

        if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) { //when areaid in db 
            // update data here 
            $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // 
            if(isset($value[$indexGid]) && !empty($value[$indexGid])){
				 
				$validGid = $this->Common->validateGuid($value[$indexGid]);
				if($validGid == false){
					$gidFormat=true; // gid invalid characters 
				}
				$returngidvalue  = $this->Area->checkGidExist($value[$indexGid],$areaNid);
                if($returngidvalue>0){
                    $gidStatus = true; // gid already exists  while update  
                }
            }
            
            if (empty($allGids[$areaNid])&& (!isset($value[$indexGid])|| empty($value[$indexGid]))) //check gid in db is empty or not 
                $value[_AREA_AREA_GID] = $this->guid();
            
        }else {
            
			$areadbdetails = '';
			$chkAreaId = $this->Area->checkAreaId($excelAreaId);

			if (!empty($chkAreaId)) {
                $areadbdetails = current($chkAreaId);
                $areaNid = $areadbdetails[_AREA_AREA_NID];
                 if(isset($value[$indexGid]) && !empty($value[$indexGid])){
					
				
                    $returngidvalue  = $this->Area->checkGidExist($value[$indexGid],$areaNid);
                    if($returngidvalue>0){
                        $gidStatus = true; // gid already exists  while update  
                    }
                }
               
                if (empty($areadbdetails[_AREA_AREA_GID])&& (!isset($value[$indexGid])|| empty($value[$indexGid]))) //check gid in db is empty or not 
                    $value[_AREA_AREA_GID] = $this->guid();
            }else {
               
                // insert if new entry
                $returnid = '';
                if (empty($value[$indexGid])) {
                    $value[$indexGid] = $this->guid();
                }
                if (!array_key_exists($indexGid, $value)) {
                    $value[$indexGid] = $this->guid();
                }
                if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                    $value[_AREA_AREA_GLOBAL] = '0';
                }
                if(isset($value[$indexGid]) && !empty($value[$indexGid]) && in_array($value[$indexGid],$allGids)==true){
                      
                    $gidStatus = true; // gid already exists while insert 
                }else{
                    if(isset($value[$indexGid]) && !empty($value[$indexGid])){
                       $validGid = $this->Common->validateGuid($value[$indexGid]);
					   if($validGid == false){
							$gidFormat=true; // gid invalid characters 
					   }
					   $returngidvalue  = $this->Area->checkGidExist($value[$indexGid],'');
                       if($returngidvalue>0){
                           $gidStatus= true; // gid already exists  while insert 
                       }
                    }
                     
                }
            }
        }
        if ($areaidAlradyexistStatus == false && $gidStatus==false && $gidFormat ==false) {
            if (!empty($areaNid)) {
               //updateRecords
                $returnid = $this->Area->updateRecords($value, [_AREA_AREA_NID => $areaNid]); // update  case handled here 
            } else {             
                $returnid = $this->Area->insertUpdateAreaData($value); // insert case handled here 
            }
            if ($returnid) {// insert sucess 
                if ($allAreblank == false) {
                    if ($levelDetails['error'] == true) {
                        return $this->arealogDetails(_WARN, _AREA_LOGCOMMENT5);
                    } else {
                        return $this->arealogDetails(_OK, '');
                    }
                }
            } else { // insert failed 
                if ($allAreblank == false) {
                    return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT2);
                }
            }
        } else {
            if ($allAreblank == false) {
                if($gidStatus==true){ 
                     return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT7);//gid duplicate case  
                }elseif($gidFormat ==true){
					 return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT8); //gid format invalid                 
				}else{
					 return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT6);
                 //duplicate case           
                }
                                        
            }
        }
    }

      /*
      processAreaCase2 returns array with log details of status and message
	  method called  when parent id is not empty and do not exists in database  
     */
    public function processAreaCase2($params, $getAllDbAreaIds,$areaidAlradyexistStatus) {
        //  get parent details 
        $gidStatus= false;$indexGid='';$gidFormat =false;
        $allAreblank =$params['allAreblank'] ; $insertDataKeys =$params['insertDataKeys'] ;
		$allGids=$params['allGids'] ; $excelAreaId = $params['excelAreaId'] ;		
        $indexParentAreaId = $params['indexParentAreaId'] ; $value=$params['value'] ; $row=$params['row'] ;
        $indexGid	= $insertDataKeys['gid'];
        $parentchkAreaId = $parentfields = $parentconditions = [];
        $parentareadbdetails = '';
		$parentchkAreaId =  $this->Area->checkParentAreaId($value[$insertDataKeys['parentnid']]);
	    //check parent id exists in db or not 
        if (!empty($parentchkAreaId))
            $parentareadbdetails = current(current($parentchkAreaId));


        if (!array_key_exists($insertDataKeys['level'], $value)) {
            $level = '';
        } else {
            $level = $value[$insertDataKeys['level']];
        }
        $levelDetails = $this->Area->returnAreaLevel($level, $value[$indexParentAreaId], $row);
        $value[$insertDataKeys['level']] = $levelDetails['level'];

        if (!empty($parentareadbdetails)) { //when areaid in db and  parent also exists due to loop insertion  
            if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) {

                $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // $key = 2;
                if(isset($value[$indexGid]) && !empty($value[$indexGid])){
					$validGid = $this->Common->validateGuid($value[$indexGid]);
					if($validGid == false){
						$gidFormat=true; // gid invalid characters 
					}
                    $returngidvalue  = $this->Area->checkGidExist($value[$indexGid],$areaNid);
                    if($returngidvalue>0){
                        $gidStatus = true; //gid already exists    
                    }
                }
                if (empty($allGids[$areaNid]) && (!isset($value[$indexGid]) || empty($value[$indexGid])))  //check gid in db is empty or not 
                    $value[_AREA_AREA_GID] = $this->guid();//set new gid if dont exist in db and sheet 
            }else {
				
				$chkAreaId = $this->Area->checkAreaId($excelAreaId);
                if (!empty($chkAreaId)) {
                    $areadbdetails = current($chkAreaId);
                    $areaNid = $areadbdetails[_AREA_AREA_NID];
                    if(isset($value[$indexGid]) && !empty($value[$indexGid])){
                        $validGid = $this->Common->validateGuid($value[$indexGid]);
						if($validGid == false){
							$gidFormat=true; // gid invalid characters 
						}
						$returngidvalue  = $this->Area->checkGidExist($value[$indexGid],$areaNid);
                        if($returngidvalue>0){
                            $gidStatus = true; // gid already exists  while update 
                        }
                    }
                    if (empty($areadbdetails[_AREA_AREA_GID]) && (!isset($value[$indexGid])|| empty($value[$indexGid]))) //check gid in db is empty or not 
                        $value[_AREA_AREA_GID] = $this->guid();
                   
                }else {
                    // insert case 
                    if (empty($value[$indexGid])) {
                        $value[$indexGid] = $this->guid();
                    }
                    if (!array_key_exists($indexGid, $value)) {
                        $value[$indexGid] = $this->guid();
                    }
                    if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                        $value[_AREA_AREA_GLOBAL] = '0';
                    }
                    if(in_array($value[$indexGid],$allGids)==true){					
                       $gidStatus = true; //gid already exists while insert
                    }else{
                        if(isset($value[$indexGid]) && !empty($value[$indexGid])){
                            $validGid = $this->Common->validateGuid($value[$indexGid]);
							if($validGid == false){
								$gidFormat=true; // gid invalid characters 
							}
							$returngidvalue  = $this->Area->checkGidExist($value[$indexGid],'');
                            if($returngidvalue>0){
                                $gidStatus= true;
                            }
                        }
                    }
                   
                }

                //
            }
            $value[$indexParentAreaId] = $parentareadbdetails;
            if ($areaidAlradyexistStatus == false && $gidStatus==false && $gidFormat==false) {
                if (!empty($areaNid)) {
                 
                    $returnid = $this->Area->updateRecords($value, [_AREA_AREA_NID => $areaNid]);
                } else {
                    $returnid = $this->Area->insertUpdateAreaData($value);
                }
                if ($returnid) {
                    if ($allAreblank == false) {
                        if ($levelDetails['error'] == true) {
                            return $this->arealogDetails(_WARN, _AREA_LOGCOMMENT5);
                        } else {
                            return $this->arealogDetails(_OK, '');
                        }
                    }
                } else {
                    if ($allAreblank == false) {

                        return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT2);
                    }
                }
            } else {
                if ($allAreblank == false) {
					/////////
					if($gidStatus==true){ 
                     return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT7);//gid duplicate case  
					}elseif($gidFormat ==true){
						 return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT8); //gid format invalid                 
					}else{
						 return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT6);
					 //duplicate case           
					}
					////
				  
                    
                }
            }
        } else {
            // when  parent id dont  exists 
            if ($allAreblank == false) {
                return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT3);
                //parent id not found 
            }
        }
    }
	
	

    /*
      processAreaCase3 method called  when parent area id  is empty 
      returns  array with log status and description
	  processArea
     */
    public function processAreaCase3($params, $getAllDbAreaIds,$areaidAlradyexistStatus) {
        $gidStatus = false;$gidFormat =false;
        $allAreblank =$params['allAreblank'] ; $insertDataKeys =$params['insertDataKeys'] ;
		$allGids = $params['allGids'] ; $excelAreaId = $params['excelAreaId'] ;
        $indexParentAreaId = $params['indexParentAreaId'] ; $value = $params['value'] ; $row = $params['row'] ;
		$indexGid = $insertDataKeys['gid'];
       
        $levelError = false;  // status when level is more than 1 and parent id is blank
        if (!array_key_exists($insertDataKeys['level'], $value)) {
            $level = '';
        } else {
            $level = (isset($value[$insertDataKeys['level']]) && !empty($value[$insertDataKeys['level']])) ? $value[$insertDataKeys['level']] : _AREAPARENT_LEVEL;
        }

        if ($level > _AREAPARENT_LEVEL) {
            //level if given when parent nid is blank or -1 no insertion will take place 
            $levelError = true;
        }

        $levelDetails = $this->Area->returnAreaLevel($level, _GLOBALPARENT_ID, $row);
        $value[$indexParentAreaId] = _GLOBALPARENT_ID; // value is -1
        $value[$insertDataKeys['level']] = $levelDetails['level']; // do hardcore level value 1 for parent area ids
        $conditions = [];
        $fields = [];
        $areadbdetails = '';
        if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) { //when areaid in db 
            // update data here 
            $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // 
            if(isset($value[$indexGid]) && !empty($value[$indexGid])){
				$validGid = $this->Common->validateGuid($value[$indexGid]);
				if($validGid == false){
					$gidFormat=true; // gid invalid characters 
				}
                $returngidvalue  = $this->Area->checkGidExist($value[$indexGid],$areaNid);
                if($returngidvalue>0){
                    $gidStatus = true; // gid already exists  
                }
            }

            if (empty($allGids[$areaNid])&& (!isset($value[$indexGid])|| empty($value[$indexGid]))) //check gid in db is empty or not 
                $value[_AREA_AREA_GID] = $this->guid();
        }else {
			
			$chkAreaId = $this->Area->checkAreaId($excelAreaId);
            if (!empty($chkAreaId)) {
                $areadbdetails = current($chkAreaId);
                $areaNid = $areadbdetails[_AREA_AREA_NID];
                if(isset($value[$indexGid]) && !empty($value[$indexGid])){
                    $validGid = $this->Common->validateGuid($value[$indexGid]);
					if($validGid == false){
						$gidFormat=true; // gid invalid characters 
					}
					$returngidvalue  = $this->Area->checkGidExist($value[$indexGid],$areaNid);
                    if($returngidvalue>0){
                        $gidStatus = true; // gid already exists  
                    }
                }
                if (empty($areadbdetails[_AREA_AREA_GID])&& (!isset($value[$indexGid])|| empty($value[$indexGid])))//check gid in db is empty or not 
                    $value[_AREA_AREA_GID] = $this->guid();
            }else {

                $returnid = '';

                if (!array_key_exists(_AREA_AREA_GLOBAL, $value)) {
                    $value[_AREA_AREA_GLOBAL] = '0';
                }
                if (empty($value[$indexGid])) {
                    $value[$indexGid] = $this->guid();
                }
                if (!array_key_exists($indexGid, $value)) {
                    $value[$indexGid] = $this->guid();
                }
                if(in_array($value[$indexGid],$allGids)==true){                  
                    
					$gidStatus= true;//gid already exists while insert	
					
                }else{
                    if(isset($value[$indexGid]) && !empty($value[$indexGid])){
                        $validGid = $this->Common->validateGuid($value[$indexGid]);
						if($validGid == false){
							$gidFormat=true; // gid invalid characters 
						}
						$returngidvalue  = $this->Area->checkGidExist($value[$indexGid],'');
                        if($returngidvalue>0){
                            $gidStatus = true; // gid already exists  
                        }
                    }
                }
            }
        }
        ///

        if ($areaidAlradyexistStatus == false && $levelError == false && $gidStatus==false && $gidFormat==false) {

            if (!empty($areaNid)) {
                $returnid = $this->Area->updateRecords($value, [_AREA_AREA_NID => $areaNid]);
            } else {
                $returnid = $this->Area->insertUpdateAreaData($value);
            }

            if ($returnid) {
                if ($allAreblank == false) {
                    if ($levelDetails['error'] == true) {

                        return $this->arealogDetails(_WARN, _AREA_LOGCOMMENT5);
                    } else {
                        return $this->arealogDetails(_OK, '');
                    }
                }
            } else {
                if ($allAreblank == false) {

                    return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT2);
                }
            }
        } else {
            if ($allAreblank == false) {
				
						/////////
					if ($levelError == true){
						return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT3); // areaid empty and level>1
					}elseif($gidStatus==true){
						return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT7); //gid duplicate case  
					}elseif($gidFormat ==true){
						return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT8); //gid format invalid                 
					}else{
						return $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT6);
						//duplicate case           
					}			
						//
             
            }
        }
    }

    /*
     *  processAreadata to insert update area records after readfing chunk file
     * @param array $filename chunk File . {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database.work as table columns and keys for insertDataArr {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */

    public function processAreadata($insertDataKeys = null, $insertDataArr = null, $extra = null) {

        $chunkFilename = basename($extra['chunkFilename']);


        $limitedAreaLevelRows = $areaidswithParentId = $parentchkAreaId = $getAllExcelAreaids = $insertDataAreaParentids = $insertDataAreaIds = [];
        $newinsertDataArr = [];
        $filteredRowsArray = $this->resetChunkAreaData($insertDataArr);     //reset records   
        $newinsertDataArr = $filteredRowsArray;
        
        $allGids  = $this->Area->getAreaGIDSlist('','list');
		
        $excelIds = $this->getexcelAreaParentIds($newinsertDataArr, $insertDataKeys);
        $getAllExcelAreaids = $excelIds['getAllExcelAreaids']; //all excel area ids
        $insertDataAreaParentids = $excelIds['insertDataAreaParentids']; // all excel parent ids 
        // $areaidswithparentid getting list which parentnids exists in db  
        $areaidswithParentId = $this->Area->getAreaDetails($insertDataAreaParentids, 'list');
        //get all aread ids exists in db already
        $getAllDbAreaIds = $this->Area->getAreaDetails($getAllExcelAreaids, 'list');
        //$getAllDbAreaGIds = $this->Area->getAreaGIDSlist($getAllExcelAreaids, 'list');

        // areaidswithparentid contains  parent ids which exist in db 

        if (isset($newinsertDataArr) && !empty($newinsertDataArr)) {
            $finalareaids = [];
            $chkuniqueAreaids = [];
            //$processedAreaIds = [];      // PROCESSED AREA IDS  OF CHUNK 
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
                if (!isset($value[$insertDataKeys['areaid']]) || empty($value[$insertDataKeys['areaid']])) {
                    //case 1 when ignore if area id is blank
                    if ($allAreblank == false) {
                        $errorLogArray[$chunkFilename][$row] = $value;
                        $errorLogArray[$chunkFilename][$row]['data'] = $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT1); //area id empty 
                    }
                    unset($value);
                    unset($newinsertDataArr[$row]);
                } else if (isset($value[$insertDataKeys['areaid']]) && !empty($value[$insertDataKeys['areaid']])) {
                    // all cases inside this when area id is not empty 
                    $excelAreaId = $value[$insertDataKeys['areaid']]; // area id read from excelof current row  
                    $indexParentAreaId = $insertDataKeys['parentnid'];
                    $desc = '';
                   
                    if (!empty($_SESSION['processedAreaIds']) && in_array($excelAreaId, $_SESSION['processedAreaIds']) == true) {
                        $areaidAlradyexistStatus = true;
                    }
                    $_SESSION['processedAreaIds'][] = $excelAreaId;//used for duplicate check of area ids 
                    
					if (isset($value[$indexParentAreaId]) && !empty($value[$indexParentAreaId]) && $value[$indexParentAreaId] != _GLOBALPARENT_ID && in_array($value[$indexParentAreaId], $areaidswithParentId) == true) {
                        //case when parent id is not empty and exists in database also 
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                        }
						
						if (empty($value[$insertDataKeys['name']]))
							$value[$insertDataKeys['name']] = $excelAreaId;
						
                        /// call case1 here starts 
							$params=['allAreblank'=>$allAreblank,'insertDataKeys'=>$insertDataKeys,'indexParentAreaId'=>$indexParentAreaId,'value'=>$value,'row'=>$row,'allGids'=> $allGids,'excelAreaId'=>$excelAreaId];
							$errorLogArray[$chunkFilename][$row]['data'] = $this->processAreaCase1($params, $areaidswithParentId, $getAllDbAreaIds, $areaidAlradyexistStatus);
                        /// case 1 ends here 
                        
                    } elseif (!empty($value[$indexParentAreaId]) && ($value[$indexParentAreaId] != _GLOBALPARENT_ID) && in_array($value[$indexParentAreaId], $areaidswithParentId) == false) {

                        //case when parent id is not empty and do not exists in database  
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                        }
						
						if (empty($value[$insertDataKeys['name']]))
						$value[$insertDataKeys['name']] = $excelAreaId;
					
                        /// call case2 here starts 					
                        $params=['allAreblank'=>$allAreblank,'insertDataKeys'=>$insertDataKeys,'indexParentAreaId'=>$indexParentAreaId,'value'=>$value,'row'=>$row,'allGids'=> $allGids,'excelAreaId'=>$excelAreaId];
    					$errorLogArray[$chunkFilename][$row]['data'] = $this->processAreaCase2($params, $getAllDbAreaIds,$areaidAlradyexistStatus);
                        /// case 2 ends here 
                        
                    }//case 3 starts here 
                    elseif (empty($value[$indexParentAreaId]) || ($value[$indexParentAreaId] == _GLOBALPARENT_ID)) {
                        //case when parent area id  is empty 
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                        }
						if (empty($value[$insertDataKeys['name']]))
						$value[$insertDataKeys['name']] = $excelAreaId;					
					
                        /// call case3 here starts 
                        $params=['allAreblank'=>$allAreblank,'insertDataKeys'=>$insertDataKeys,'indexParentAreaId'=>$indexParentAreaId,'value'=>$value,'row'=>$row,'allGids'=> $allGids,'excelAreaId'=>$excelAreaId];
                        $errorLogArray[$chunkFilename][$row]['data'] = $this->processAreaCase3($params, $getAllDbAreaIds, $areaidAlradyexistStatus);
                        /// case 3 ends here 
                        
                    } else {
                        if ($allAreblank == false) {
                            $errorLogArray[$chunkFilename][$row] = $value;
                            $errorLogArray[$chunkFilename][$row]['data'] = $this->arealogDetails(_FAILED, _AREA_LOGCOMMENT4); //invalid details
                        }
                    }
                }// end of if of area id exists 
            }
        }


        $newinsertDataArr = [];
        $insertDataAreaids = [];

        return ['dataArray' => $newinsertDataArr, 'insertDataAreaids' => $insertDataAreaids, 'errorLogArray' => $errorLogArray];
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
        } else if ($component == 'IcIus') {  //Bulk upload - ICIUS
            return $this->bulkUploadIcius($divideXlsOrCsvInChunks, $extra);
        } else if ($component == 'Area') {  //Bulk upload - ICIUS
            return $this->bulkUploadXlsOrCsvArea($divideXlsOrCsvInChunks, $extra, $objPHPExcel);
        }
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;

        // Bulk upload each chunk separately
        foreach ($divideXlsOrCsvInChunks as $filename) {
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


        $insertArray = array_merge(array_keys($insertArrayFromGids), array_keys($insertArrayFromNames));

        //save Buffer
        unset($insertArrayFromGids);
        unset($insertArrayFromNames);

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
            $this->{$component}->insertOrUpdateBulkData($insertArray);
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
        if ($type == _ICIUS) {
            return [
                'class type',
                'level1',
                'indicator',
                'unit',
                'subgroup'
            ];
        } else if ($type == strtolower(_MODULE_NAME_AREA)) {
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

        // ---- Add ICIUS
        $bulkUploadReturn = $this->IcIus->bulkUploadIcius($divideXlsOrCsvInChunks, $extra);
        extract($bulkUploadReturn);

        if (isset($bulkUploadReturn['error'])) {
            return $bulkUploadReturn;
        }

        // Generate Import Log file
        $extraParams = ['highestValidColumn' => \PHPExcel_Cell::stringFromColumnIndex($highestSubgroupTypeColumn)];
        return $this->createImportLog($allChunksRowsArr, $unsettedKeysAllChunksArr, $warningKeysAllChunksArr, $extraParams);
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

        $indicatorArray = array_intersect_key($indicatorArray, array_unique(array_map('serialize', $indicatorArray)));
        $indicatorArray = array_intersect_key($indicatorArray, array_unique(array_combine(array_keys($indicatorArray), array_column($indicatorArray, 0))));

        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'IndiGlobal' => _INDICATOR_INDICATOR_GLOBAL];
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $indicatorArray);

        $params['nid'] = _INDICATOR_INDICATOR_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = FALSE;
        $component = 'Indicator';

        $this->nameGidLogic($divideNameAndGids, $component, $params);

        $fields = [_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME];
        $conditions = [_INDICATOR_INDICATOR_NAME . ' IN' => array_filter(array_unique(array_column($indicatorArray, 0)))];
        return $indicatorRecWithNids = $this->Indicator->getRecords($fields, $conditions, 'list');
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

        $unitArray = array_intersect_key($unitArray, array_unique(array_map('serialize', $unitArray)));
        $unitArray = array_intersect_key($unitArray, array_unique(array_combine(array_keys($unitArray), array_column($unitArray, 0))));

        $insertDataKeys = ['name' => _UNIT_UNIT_NAME, 'gid' => _UNIT_UNIT_GID, 'unitGlobal' => _UNIT_UNIT_GLOBAL];
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $unitArray);

        $params['nid'] = _UNIT_UNIT_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;
        $component = 'Unit';

        $this->nameGidLogic($divideNameAndGids, $component, $params);

        $fields = [_UNIT_UNIT_NID, _UNIT_UNIT_NAME];
        $conditions = [_UNIT_UNIT_NAME . ' IN' => array_filter(array_unique(array_column($unitArray, 0)))];
        return $unitRecWithNids = $this->Unit->getRecords($fields, $conditions, 'list');
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

        //$subgroupValArray = array_intersect_key($subgroupValArray, array_unique(array_map('serialize', $subgroupValArray)));

        $insertDataKeys = [
            'name' => _SUBGROUP_VAL_SUBGROUP_VAL,
            'gid' => _SUBGROUP_VAL_SUBGROUP_VAL_GID,
            'subgroup_val_order' => _SUBGROUP_VAL_SUBGROUP_VAL_ORDER,
            'subgroupValGlobal' => _SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL
        ];

        $maxSubgroupValOrder = $this->SubgroupVals->getMax(_SUBGROUP_VAL_SUBGROUP_VAL_ORDER);
        $subgroupValArrayUnique = array_intersect_key($subgroupValArray, array_unique(array_map('serialize', $subgroupValArray)));

        $subgroupValsName = [];
        foreach($subgroupValArrayUnique as $index => &$val) {
            if (empty(array_filter($val))) {
                unset($subgroupValArrayUnique[$index]);
            } else {
                $val[] = ++$maxSubgroupValOrder;
                $val[] = 0; // for _SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL
                $subgroupValsName[] = $val[0];
            }
        }
        /*array_walk($subgroupValArrayUnique, function(&$val, $index) use(&$subgroupValArrayUnique, &$maxSubgroupValOrder, &$subgroupValsName) {
            if (empty(array_filter($val))) {
                unset($subgroupValArrayUnique[$index]);
            } else {
                $val[] = ++$maxSubgroupValOrder;
                $val[] = 0; // for _SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL
                $subgroupValsName[] = $val[0];
            }
        });*/
        $subgroupValArray = $subgroupValArrayUnique;
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $subgroupValArray);

        $params['nid'] = _SUBGROUP_VAL_SUBGROUP_VAL_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = FALSE;
        $component = 'SubgroupVals';

        $this->nameGidLogic($divideNameAndGids, $component, $params);
        $subgroupValsNIds = $this->SubgroupVals->getRecords(
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

        $getSubGroupTypeNidAndName = $this->SubgroupType->getRecords(
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
        $getDataByName = $this->{$component}->getRecords($fields, $conditions, 'list');

        if (!empty($getDataByName)) {
            foreach ($getDataByName as $Nid => $name) {
                
                $key = array_search($name, $names);
                $name = $dataArray[$key];

                /**
                 * WE DON'T UPDATE THE ROW IF 
                 * 1. NAME IS FOUND AND 
                 * 2. GID IS BLANK AND 
                 * 3. UPDATING GID IS NOT REQUIRED 
                 * BECAUSE THAT WILL OVERWRITE THE GUID
                 */ 
                if(empty($name[$insertDataKeys['gid']])) {
                    if($updateGid == false) {
                        continue;
                    } else {
                        $autoGenGuid = $this->guid();
                        $name[$insertDataKeys['gid']] = $autoGenGuid;
                    }
                }
                
                if (array_key_exists('highIsGood', $insertDataKeys)) {
                    if (!array_key_exists($insertDataKeys['highIsGood'], $name)) {
                        $name[$insertDataKeys['highIsGood']] = 0;
                    }
                }

                $this->{$component}->updateRecords($name, [$extra['nid'] => $Nid]);
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
        $getDataByGid = $this->{$component}->getRecords($fields, $conditions, 'list');

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

                //$this->Indicator->updateRecords($gid, [$extra['nid'] => $Nid]);
                $this->{$component}->updateRecords($gid, [$extra['nid'] => $Nid]);
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
     * bulkUploadXlsOrCsvArea method
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function bulkUploadXlsOrCsvArea($fileChunksArray = [], $extra = null, $xlsObject = null) {
        
		if (isset($_SESSION['processedAreaIds']))
            unset($_SESSION['processedAreaIds']);

        $dbId = $this->request->data['dbId'];
        $dbDetails = $this->Common->parseDBDetailsJSONtoArray($dbId);
        //pr($dbDetails['db_connection_name']);
        $dbConnName = $dbDetails['db_connection_name'];
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
            return ['error' => _ERR122];
        }
        $titlearray = [];  // for titles of sheet
        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $cell = $xlsObject->getActiveSheet()->getCellByColumnAndRow($col, $startRow);
            $titlearray[] = $val = $cell->getValue();
        }
        $validFormat = $this->importFormatCheck(strtolower(_MODULE_NAME_AREA));  //Check file Columns format
        $formatDiff = array_diff($validFormat, array_map('strtolower', $titlearray));
        $columSequeceStatus = false;
        if ((strtolower($titlearray[0]) == strtolower(_EXCEL_AREA_ID)) && (strtolower($titlearray[1]) == strtolower(_EXCEL_AREA_NAME)) &&
                (strtolower($titlearray[2]) == strtolower(_EXCEL_AREA_LEVEL)) && (strtolower($titlearray[3]) == strtolower(_EXCEL_AREA_GID)) && (strtolower($titlearray[4]) == strtolower(_EXCEL_AREA_PARENTID))) {
            $columSequeceStatus = true;
        }

        // return ['error' => _ERROR_2];
        if (!empty($formatDiff) || $columSequeceStatus == false) {
            return ['error' => _ERR123];
        }
        // end of file validation 	
        $firstRow = ['A' => _EXCEL_AREA_ID, 'B' => _EXCEL_AREA_NAME, 'C' => _EXCEL_AREA_LEVEL, 'D' => _EXCEL_AREA_GID, 'E' => _EXCEL_AREA_PARENTID, 'F' => _STATUS, 'G' => _DESCRIPTION];

        $logData = [];
        foreach ($fileChunksArray as $filename) {

            $extra['chunkFilename'] = $filename;
            $loadDataFromXlsOrCsv = $this->prepareDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
            // $params['nid'] = _AREA_AREA_NID;
            //$params['insertDataKeys'] = $insertDataKeys;
            // $params['updateGid'] = TRUE;
            $logData[] = $loadDataFromXlsOrCsv['errorLogArray'];
            // $this->nameGidLogic($loadDataFromXlsOrCsv, $component, $params);
            // @unlink($filename);
        }

        $finalLogArray = [];

        foreach ($logData as $fileChunk => $chunkName) {
            foreach ($chunkName as $chunkIndex => $chunkData) {
                foreach ($chunkData as $logDt) {
                    $finalLogArray[] = $logDt; //final log array ready to write into  log file 
                }
            }
        }


        // $this->appendErrorLogData(WWW_ROOT.$areaErrorLog,$_SESSION['errorLog']); //
        //return $this->appendErrorLogData($firstRow, $_SESSION['errorLog']); //
        return $this->appendErrorLogData($firstRow, $finalLogArray, $dbConnName); //
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
            $unsettedKeys = array_replace($unsettedKeys, array_fill_keys($filledArrayKeys, [$msg]));
        } else {
            if (!in_array($msg, $unsettedKeys[$row])) {
                $unsettedKeys[$row][] = $msg;
            }
        }
        return $unsettedKeys;
    }

    /**
     * maintainErrorLogs method     *
     * @param string $row row to check. {DEFAULT : null}
     * @param array $warningKeys Error storing Array. {DEFAULT : null}
     * @param string $msg Message if row not found. {DEFAULT : null}
     * @return unsettedKeys array
     */
    public function maintainWarningLogs($row, $warningKeys, $msg) {
        if (!array_key_exists($row, $warningKeys)) {
            $filledArrayKeys = [$row];
            $warningKeys = array_replace($warningKeys, array_fill_keys($filledArrayKeys, [$msg]));
        } else {
            if (!in_array($msg, $warningKeys[$row])) {
                $warningKeys[$row][] = $msg;
            }
        }
        return $warningKeys;
    }

    /*
     * createImportLog
     *
     * @param array $allChunksRowsArr Sheet Rows indexes Array
     * @param array $unsettedKeysAllChunksArr Indexes having errors
     * @return Exported File path
     */

    public function createImportLog($allChunksRowsArr, $unsettedKeysAllChunksArr, $warningKeysAllChunksArr, $extra = []) {

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

                /* for ($row = $startRows; $row <= ($startRows + (count($startRows) - 1)); ++$row) {
                  if (array_key_exists($row, $unsettedKeysAllChunksArr[$key])) {
                  $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $row, _FAILED);
                  $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $row, $unsettedKeysAllChunksArr[$key][$row]);
                  } else {
                  $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $row, _OK);
                  $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $row, '');
                  }
                  } */

                if (array_key_exists($value, $unsettedKeysAllChunksArr[$key])) {
                    $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $startRows, _FAILED);
                    $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $startRows, implode(', ', $unsettedKeysAllChunksArr[$key][$value]));
                } else if (array_key_exists($value, $warningKeysAllChunksArr[$key])) {
                    $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $startRows, _WARNING);
                    $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $startRows, implode(', ', $warningKeysAllChunksArr[$key][$value]));
                } else {
                    $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['status'] . $startRows, _OK);
                    $PHPExcel->getActiveSheet()->SetCellValue($columnToWrite['description'] . $startRows, '');
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
     *  function to append data 
     */

    public function appendErrorLogData($firstRowdata = [], $data = [], $dbConnName = '') {
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


        $width = 30;
        $styleArray = array(
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 10,
                'name' => 'Arial',
        ));

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

            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $startRow, (isset($value['data'][_STATUS])) ? $value['data'][_STATUS] : '' )->getColumnDimension('F')->setWidth($width - 10);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $startRow)->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $startRow, (isset($value['data'][_DESCRIPTION])) ? $value['data'][_DESCRIPTION] : '' )->getColumnDimension('G')->setWidth($width + 5);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $startRow)->applyFromArray($styleArray);

            $startRow++;
        }
        $module = 'Area';

        //$dbDetails = $this->Common->parseDBDetailsJSONtoArray();
        //$dbdetails= ConnectionManager::get('devInfoConnection');
        // $db = $dbdetails['config']['database'];
        $dbConnName = str_replace(' ', '-', $dbConnName);
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $saveFile = _LOGS_PATH . DS . _IMPORTERRORLOG_FILE . '_' . _MODULE_NAME_AREA . '_' . $dbConnName . '_' . date('Y-m-d-H-i-s') . '.xls';
        //$returnFilename = WWW_ROOT.'uploads'.DS.'logs'.DS._IMPORTERRORLOG_FILE . _MODULE_NAME_AREA . '_' . $authUserId . '_' .time().'.xls';
        $returnFilename = _IMPORTERRORLOG_FILE . '_' . _MODULE_NAME_AREA . '_' . $dbConnName . '_' . date('Y-m-d-H-i-s') . '.xls';
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
            if (isset($extra['conditions'])) {
                $conditions = array_merge($conditions, $extra['conditions']);
            } else {
                $conditions[_AREA_PARENT_NId] = $parentNID;
            }
            $order = array(_AREA_AREA_NAME => 'ASC');
        }

        $recordlist = $this->{$component}->find('all', array('conditions' => $conditions, 'fields' => array(), 'order' => $order));
        $list = $this->getDataRecursive($recordlist, $component, $onDemand, $extra);


        return $list;
    }

    /**
     * function to recursive call to get children 
     *
     * @access public
     */
    function getDataRecursive($recordlist, $component, $onDemand = false, $extra = []) {

        $rec_list = array();
        $childData = array();
        // start loop through area data
        for ($lsCnt = 0; $lsCnt < count($recordlist); $lsCnt++) {

            $childExists = false;
            $blocks = '';
            $areaLvl = '';

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
                $blocks = $recordlist[$lsCnt][_AREA_AREA_BLOCK];
                $areaLvl = $recordlist[$lsCnt][_AREA_AREA_LEVEL];

                if ($onDemand === false) {
                    $childData = $this->{$component}->find('all', array('conditions' => array(_AREA_PARENT_NId => $NId), 'order' => array(_AREA_AREA_NAME => 'ASC')));
                } else {
                    $childCount = $this->{$component}->find('all', array('conditions' => array(_AREA_PARENT_NId => $NId)), array('count' => 1));
                    $childExists = ($childCount) ? true : false;
                    $childExists = (isset($extra['childExists'])) ? $extra['childExists'] : $childExists;
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

                $rec_list[] = $this->prepareNode($NId, $ID, $name, $childExists, $dataArr, $this->arrayDepth, $blocks, $areaLvl);
            }
            //if child data not found then make list with its id and name
            else {
                $this->arrayDepthIterator = 1;
                $rec_list[] = $this->prepareNode($NId, $ID, $name, $childExists, array(), 1, $blocks, $areaLvl);
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
    public function prepareNode($NId, $ID, $name, $childExists, $nodes = array(), $depth = 1, $blocks='', $areaLvl = '') {
        return array('nid' => $NId, 'id' => $ID, 'name' => $name, 'childExists' => $childExists, 'nodes' => $nodes, 'arrayDepth' => $depth, 'block'=>$blocks, 'areaLvl' => $areaLvl);
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
    
    /**
     * Get Unit List
     */
    public function getUnitList($conditions = []) {
        
        $order = [_UNIT_UNIT_NAME => 'ASC'];
        $fields = ['id' => _UNIT_UNIT_NID, 'gid' => _UNIT_UNIT_GID, 'name' => _UNIT_UNIT_NAME];
        $unitRecords = $this->Unit->getRecords($fields, $conditions, 'all', ['order' => $order]);
        $list = [];

        foreach ($unitRecords as $unitRecord) {
            $list[] = $this->prepareNode($unitRecord['id'], $unitRecord['gid'], $unitRecord['name'], false, [], 1);
        }

        return $list;
    }

    /*
     * get Valid IC type Names(ShortForms)
     *
     * @return IC Type List
     */

    public function getValidIcTypes($lowerCase = false) {

        $validIcTypes = ['CF', 'CN', 'GL', 'IT', 'SC', 'SR', 'TH'];
        if ($lowerCase == true)
            $validIcTypes = array_map('strtolower', $validIcTypes);

        return $validIcTypes;
    }

    /*
     * get Time Period List in Tree View
     *
     * @return Time Period List
     */

    public function getTimePeriodTreeList() {
        $fields = ['id' => _TIMEPERIOD_TIMEPERIOD_NID, 'name' => _TIMEPERIOD_TIMEPERIOD];
        $tpRecords = $this->Timeperiod->getRecords($fields);
        $list = [];

        foreach ($tpRecords as $tpRecord) {
            $list[] = $this->prepareNode($tpRecord['id'], $tpRecord['id'], $tpRecord['name'], false, [], 1);
        }

        return $list;
    }

    /*
     * get Source List in Tree View
     *
     * @return Source List
     */

    public function getSourceTreeList() {
        $fields = ['id' => _IC_IC_GID, 'nid' => _IC_IC_NID, 'name' => _IC_IC_NAME];
		$conditions=[];
		$order =[_IC_IC_NAME =>'ASC'];
        $srcRecords = $this->IndicatorClassifications->getSource($fields,$conditions,'all',['order' => $order]);
        $list = [];

        foreach ($srcRecords as $srcRecord) {
            $list[] = $this->prepareNode($srcRecord['nid'], $srcRecord['id'], $srcRecord['name'], false, [], 1);
        }

        return $list;
    }

	/*
     * get Subgroup tree View List
     *
     * @return Subgroup List
     */
    public function getSubgroupValTreeList() {			

        $fields = ['id' =>  _SUBGROUP_VAL_SUBGROUP_VAL_GID, 'nid' =>_SUBGROUP_VAL_SUBGROUP_VAL_NID, 'name' => _SUBGROUP_VAL_SUBGROUP_VAL];
        $conditions=[];
		$extra['order'] = [_SUBGROUP_VAL_SUBGROUP_VAL => 'ASC'];
		$sbGrpRecords = $this->SubgroupVals->getRecords($fields, $conditions,'all',$extra);
        $list = [];

        foreach ($sbGrpRecords as $sgRecord) {
            $list[] = $this->prepareNode($sgRecord['nid'], $sgRecord['id'], $sgRecord['name'], false, [], 1);
        }

        return $list;
    }
	
	/*
     * get Subgroup type tree View List
     *
     * @return Subgroup type List
     */
    public function getSubgroupTypeTreeList() {			

        $fields = ['id' =>  _SUBGROUPTYPE_SUBGROUP_TYPE_GID, 'nid' =>_SUBGROUPTYPE_SUBGROUP_TYPE_NID, 'name' => _SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
        $conditions=[];
		$extra['order'] = [_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => 'ASC'];
		$sbGrpRecords = $this->SubgroupType->getRecords($fields, $conditions,'all',$extra);
        $list = [];

        foreach ($sbGrpRecords as $sgRecord) {
            $list[] = $this->prepareNode($sgRecord['nid'], $sgRecord['id'], $sgRecord['name'], false, [], 1);
        }

        return $list;
    }
	
	/*
	 method to allow alpha numeric with space only  
	*/
	function allowAlphaNumeric($inputValue =''){
		 if(preg_match('/^[\sA-Za-z0-9]+$/', $inputValue) === 0) {
            return false;  // not valid 
        } else {
            return true; // when its valid 
        }
		
	}
	
	
    public function testCasesFromTable() {

        //$data= $this->IndicatorUnitSubgroup->query('select * from UT_Indicator_Unit_Subgroup  limit 0,10');
        //$data= $this->SubgroupType->getRecords([],[],'all');
        //$data= $this->SubgroupType->deleteRecords(['Subgroup_Type_NId' => 13]);
        //$data= $this->Indicator->getRecords([],[_INDICATOR_INDICATOR_GID => 'NAR'],'all');
        //$this->Indicator->deleteRecords([_INDICATOR_INDICATOR_GID => 'NAR']);
        //$dataObj = TableRegistry::get('DevInfoInterface.Data');
        //$data = $dataObj->find()->hydrate(false)->toArray();
        $this->IndicatorClassifications->deleteIcChilds(442);
        debug($data);exit;
    }

}
