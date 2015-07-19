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
use Cake\ORM\Entity;
use Cake\Network\Exception\NotFoundException;
use Cake\Datasource\ConnectionManager; // This line is required

/**
 * CommonInterface Component
 */
class CommonInterfaceComponent extends Component
{
    
    //Loading Components
	public $components = [
            'DevInfoInterface.Indicator', 
            'DevInfoInterface.Unit', 
            'DevInfoInterface.Timeperiod', 
            'DevInfoInterface.Subgroup', 
            'DevInfoInterface.IndicatorClassifications',
            'DevInfoInterface.Area',
        ];
    

    public function initialize(array $config)
    {
        parent::initialize($config);
    }
	
	 public function setDbConnection($dbConnection)
    {
      
        $config = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'dgps-os',
            //'port' => 'nonstandard_port_number',
            'username' => 'root',
            'password' => 'root',
            'database' => 'Developer_Evaluation_Database',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ];
          
        ConnectionManager::config('devInfoConnection', $config);
        //$conn = ConnectionManager::get('devInfoConnection');
    }



    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
   

    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function serviceInterface($component = NULL, $method = NULL, $params = null, $dbConnection = null)
    {
		
        if(!empty($dbConnection)){
           $this->setDbConnection($dbConnection);
        }

        /*
		*
        * http://php.net/manual/en/function.call-user-func-array.php
        * call_user_func_array(array($classObj, $method), $params);
        *
		*/
        if($component.'Component' == (new \ReflectionClass($this))->getShortName()){
            return call_user_func_array([$this, $method], $params);
        }else{
            return call_user_func_array([$this->{$component}, $method], $params);
        }
        
	}


    /**
	* Auto-Generates Random Guid
	* @return GUID
	*/
    public function guid(){
		
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			//$uuid =// chr(123)// "{"
            $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
            //.chr(125);// "}"
            return $uuid;
        }
	}


    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function bulkInsert($component = null, $dataArray = null, $insertDataNames = null, $insertDataGids = NULL, $params = null)
    {

        $insertArrayFromGids = [];
        $insertArrayFromNames = [];
        $extraParam['updateGid'] = isset($params['updateGid']) ? $params['updateGid'] : false ;
        $insertDataKeys = $params['insertDataKeys'];
        
        //Update records based on Indicator GID
        if($extraParam['updateGid'] == true){
            if(!empty($insertDataGids)){
                $extraParam['nid'] = $params['nid'];
                $extraParam['component'] = $component;
                $insertArrayFromGids = $this->updateColumnsFromGid2($insertDataGids, $dataArray, $insertDataKeys, $extraParam);

                //save Buffer
                unset($insertDataGids);
            }
        }

        //Update records based on Indicator Name
        if(!empty($insertDataNames)){
            $extraParam['nid'] = $params['nid'];
            $extraParam['component'] = $component;
            $insertArrayFromNames = $this->updateColumnsFromName2($insertDataNames, $dataArray, $insertDataKeys, $extraParam);

            //save Buffer
            unset($insertDataNames);
        }
        
        $insertArray = array_merge(array_keys($insertArrayFromGids), array_keys($insertArrayFromNames));

        //save Buffer
        unset($insertArrayFromGids);
        unset($insertArrayFromNames);

        $insertArray = array_flip($insertArray);
        $insertArray = array_intersect_key($dataArray, $insertArray);
        
        //save Buffer
        unset($dataArray);

        //Insert New records
        if(!empty($insertArray)){

            array_walk($insertArray, function(&$val, $key) use($params, $insertDataKeys) {
                
                if($params['updateGid'] == true){
                    if(!array_key_exists($insertDataKeys['gid'], $val)){
                        $autoGenGuid = $this->guid();
                        $val[$insertDataKeys['gid']] = $autoGenGuid;
                    }
                }

                if(array_key_exists('highIsGood', $insertDataKeys) && !array_key_exists($insertDataKeys['highIsGood'], $val)){
                    $val[$insertDataKeys['highIsGood']] = 0;
                }

            });
                    
            $this->{$component}->insertBulkData($insertArray, $insertDataKeys);
        }

    }

    
    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function bulkUploadXlsOrCsvForIndicatorOld($params = null)
    {
        extract($params);

        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'highIsGood' => _INDICATOR_HIGHISGOOD];
        $extra['limitRows'] = 1000; // Number of rows in each file chunks
        $extra['startRows'] = 6; // Row from where the data reading starts

        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunks($filename, $extra);

        foreach($divideXlsOrCsvInChunks as $filename){

            $insertArrayFromGids = [];
            $insertArrayFromNames = [];

            $loadDataFromXlsOrCsv = $this->loadDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
            extract($loadDataFromXlsOrCsv);

            echo '<pre>';print_r($loadDataFromXlsOrCsv);exit;
        
            //save Buffer
            unset($loadDataFromXlsOrCsv);

            //Update records based on Indicator GID
            if(!empty($insertDataGids)){
                $extraParam['nid'] = _INDICATOR_INDICATOR_NID;
                $extraParam['component'] = 'Indicator';
                $insertArrayFromGids = $this->updateColumnsFromGid($insertDataGids, $dataArray, $insertDataKeys, $extraParam);

                //save Buffer
                unset($insertDataGids);
            }

            //Update records based on Indicator Name
            if(!empty($insertDataNames)){
                $extraParam['nid'] = _INDICATOR_INDICATOR_NID;
                $extraParam['component'] = 'Indicator';
                $insertArrayFromNames = $this->updateColumnsFromName($insertDataNames, $dataArray, $insertDataKeys, $extraParam);

                //save Buffer
                unset($insertDataNames);
            }

            $insertArray = array_merge(array_keys($insertArrayFromGids), array_keys($insertArrayFromNames));

            //save Buffer
            unset($insertArrayFromGids);
            unset($insertArrayFromNames);

            $insertArray = array_flip($insertArray);
            $insertArray = array_intersect_key($dataArray, $insertArray);

            //save Buffer
            unset($dataArray);

            //Insert New records
            if(!empty($insertArray)){

                array_walk($insertArray, function(&$val, $key) {
                        
                    if(!array_key_exists(_INDICATOR_INDICATOR_GID, $val)){
                        $autoGenGuid = $this->guid();
                        $val[_INDICATOR_INDICATOR_GID] = $autoGenGuid;
                    }

                    if(!array_key_exists(_INDICATOR_HIGHISGOOD, $val)){
                        $val[_INDICATOR_HIGHISGOOD] = 0;
                    }

                });
                    
                $this->Indicator->insertBulkData($insertArray, $insertDataKeys);
            }

            unlink($filename);

        }
	}

    
    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function bulkUploadXlsOrCsvForIndicator($params = null)
    {
        extract($params);

        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'highIsGood' => _INDICATOR_HIGHISGOOD];
        $extra['limitRows'] = 1000; // Number of rows in each file chunks
        $extra['startRows'] = 6; // Row from where the data reading starts

        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunks($filename, $extra);

        foreach($divideXlsOrCsvInChunks as $filename){

            $loadDataFromXlsOrCsv = $this->loadDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
            
            $dataArray = $loadDataFromXlsOrCsv['dataArray'];
            $insertDataNames = $loadDataFromXlsOrCsv['insertDataNames'];
            $insertDataGids = $loadDataFromXlsOrCsv['insertDataGids'];

            $params['insertDataKeys'] = $insertDataKeys;
            $params['updateGid'] = TRUE;
            $params['nid'] = _INDICATOR_INDICATOR_NID;

            $component = 'Indicator';
            
            $this->bulkInsert($component, $dataArray, $insertDataNames, $insertDataGids, $params);

            unlink($filename);

        }
	}

    
    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function bulkUploadXlsOrCsvForUnit($params = null)
    {
        //The following line should do the same like App::import() in the older version of cakePHP
        require_once(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');

        extract($params);

        $insertFieldsArr = [];
        $insertDataArr = [];
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
                
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                    
            for ($row = 1; $row <= $highestRow; ++ $row) {

                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);
                            
                    if($row == 1){
                        $insertFieldsArr[] = $val;
                    }else{
                        $insertDataArr[$row][$insertFieldsArr[$col]] = $val;
                    }
                }

            }
        }
                
        $dataArray = array_values($insertDataArr);
        
        //insertOrUpdateBulkData(array $Indicator = $this->request->data)
        $returnData = $this->Unit->insertOrUpdateBulkData($dataArray);
    }

    
    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function bulkUploadXlsOrCsvForIUS($params = null)
    {
        extract($params);

        //The following line should do the same like App::import() in the older version of cakePHP
        require_once(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');

        $insertFieldsArr = [];
        $insertDataArrRows = [];
        $insertDataArrCols = [];

        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
                
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                    
            for ($row = 1; $row <= $highestRow; ++ $row) {

                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if($row == 1){
                        $insertFieldsArr[$col] = $val;
                    }else{  //-- Data Strats from row 2 --//
                                
                        if(!isset($indicatorFieldKey)){
                            $indicatorFieldKey = array_search(strtolower('Indicator'), array_map('strtolower', $insertFieldsArr));
                        }
                                
                        $insertDataArrRows[$row][] = $val;

                        if(($col != 0) && ($col < $indicatorFieldKey)){
                            $insertDataArrCols[$col][$row] = $val;
                            $levelArray[$row][] = $val;
                        }else{
                            $insertDataArrCols[$col][$row] = $val;
                        }

                        /*
                        $count = count($insertDataArr[$row]);

                        //if($insertDataArr[$row][$count - 1]){
                        if($col == 0){}
                        */
                    }
                }

                // Unset if whole row is blank
                //if(isset($insertDataArr[$row]) && count(array_filter($insertDataArr[$row])) === 0){
                if(isset($insertDataArr[$row]) && array_filter($insertDataArr[$row]) == null){
                    unset($insertDataArr[$row]);
                }

                if(isset($levelArray[$row])){
                    if(empty(array_filter($levelArray[$row]))){
                        unset($levelArray[$row]);
                    }
                }

            }

        }
                
        echo '<pre>';
        echo $indicatorFieldKey = array_search(strtolower('Indicator'), array_map('strtolower', $insertFieldsArr)), '<br>';
        echo $subgroupFieldKey = array_search(strtolower('SubgroupGid'), array_map('strtolower', $insertFieldsArr)), '<br>';
        echo count($insertFieldsArr), '<br>';
        /*print_r($insertFieldsArr);
        print_r($insertDataArrRows);
        print_r($insertDataArrCols);*/
        //$insertDataArrColsUnique = array_unique(array_filter($insertDataArrCols));
        $insertDataArrColsLevel1 = array_unique(array_filter(array_values($insertDataArrCols[1])));
        print_r($insertDataArrColsLevel1);
        //print_r(array_filter($levelArray));

        // -------- GET NIds for Level1 -------- //
        /*$fields = [_IC_IC_NID, _IC_IC_NAME];
        $conditions = [_IC_IC_PARENT_NID => '-1', _IC_IC_NAME . ' IN' => $insertDataArrColsLevel1];
        $result = $this->IndicatorClassifications->getDataByParams($fields, $conditions, 'list');*/

        //print_r($result);exit;

        /*
        $fields = [_IC_IC_NID, _IC_IC_NAME];

        foreach($levelArray as &$levelArr){
            $levelArr = "('".$levelArr[0]."','".$levelArr[1]."')";
        }

        $conditions = ['("'._IC_IC_PARENT_NID.'","'._IC_IC_NAME.'") IN ('.implode(',', $levelArray).')'];
        $result = $this->IndicatorClassifications->getDataByParams($fields, $conditions, 'list');
        print_r(array_filter($result));exit;
        */
        foreach($insertDataArrCols as $key => $value){

            // Class type
            if(($key != 0) && ($key < $indicatorFieldKey)){
                $fields = [_IC_IC_NID, _IC_IC_NAME];

                if($key == 1){

                    $value = array_unique(array_filter($value));
                            
                    $fields = [_IC_IC_NID, _IC_IC_NAME];
                    $conditions = [_IC_IC_PARENT_NID => '-1', _IC_IC_NAME . ' IN' => $value];
                    $result = $this->IndicatorClassifications->getDataByParams($fields, $conditions, 'list');
                    $insertResults = array_diff($value, $result);
                            
                    $field = [];
                    $field[] = _IC_IC_NAME;
                    $field[] = _IC_IC_PARENT_NID;
                    $field[] = _IC_IC_GID;
                    $bulkInsertArray = array_map(function($val) use ($field){
                        $returnFields = [];
                        $returnFields[$field[0]] = $val;
                        $returnFields[$field[1]] = '-1';
                        $returnFields[$field[2]] = $this->guid();
                        return $val = $returnFields;
                    }, $insertResults);

                    // Insert New Data
                    $this->IndicatorClassifications->insertOrUpdateBulkData($bulkInsertArray);
                    $result = $this->IndicatorClassifications->getDataByParams($fields, $conditions, 'list');
                    print_r($result);exit;
                            
                }else{
                    $conditions = [_IC_IC_NAME . ' IN' => $value, _IC_IC_PARENT_NID => '-1'];
                }
                        
                $result = $this->IndicatorClassifications->getDataByParams($fields, $conditions, 'list');
                        
            }else{
                $value = array_unique(array_filter($value));
            }
                    
            $insertFieldsArr[$key];
        }

        print_r(array_filter($insertDataArrCols));
        exit;

        $dataArray = array_values($insertDataArr);

        //insertOrUpdateBulkData(array $Indicator = $this->request->data)
        $returnData = $this->Unit->insertOrUpdateBulkData($dataArray);

	}


    /**
     * updateColumnsFromName method
     *
     * @param array $names Names Array. {DEFAULT : empty}
     * @return void
     */
    public function updateColumnsFromName($names = [], $dataArray, $insertDataKeys, $extra = null)
    {

        $fields = [$extra['nid'], $insertDataKeys['name']];
        $conditions = [$insertDataKeys['name'] . ' IN'=>$names];
        $component = $extra['component'];

        //Get NIds based on Name - //Check if Names found in database
        //getDataByParams(array $fields, array $conditions, $type = 'all')
        $getDataByName = $this->{$component}->getDataByParams($fields, $conditions, 'list');
        
        /* WE DON'T UPDATE THE ROW IF NAME IS FOUND BECAUSE THAT WILL OVERWRITE THE GUID
        if(!empty($getDataByName)){
            foreach($getDataByName as $Nid => $name){
                $key = array_search($name, $names);
                $name = $dataArray[$key];

                $autoGenGuid = $this->guid();
                $name[$insertDataKeys['gid']] = $autoGenGuid;

                if(array_key_exists('highIsGood', $insertDataKeys)){
                    if(!array_key_exists($insertDataKeys['highIsGood'], $name)){
                        $name[$insertDataKeys['highIsGood']] = 0;
                    }
                }
                
                $this->{$component}->updateDataByParams($name, [$extra['nid'] => $Nid]);
            }
        }
        */
        //Get Guids that are not found in the database
        return $freshRecordsNames = array_diff($names, $getDataByName);

    }


    /**
     * updateColumnsFromName method
     *
     * @param array $names Names Array. {DEFAULT : empty}
     * @return void
     */
    public function updateColumnsFromName2($names = [], $dataArray, $insertDataKeys, $extra = null)
    {

        $fields = [$extra['nid'], $insertDataKeys['name']];
        $conditions = [$insertDataKeys['name'] . ' IN'=>$names];
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
        if($updateGid == true){
            if(!empty($getDataByName)){
                foreach($getDataByName as $Nid => $name){
                    $key = array_search($name, $names);
                    $name = $dataArray[$key];

                    $autoGenGuid = $this->guid();
                    $name[$insertDataKeys['gid']] = $autoGenGuid;

                    if(array_key_exists('highIsGood', $insertDataKeys)){
                        if(!array_key_exists($insertDataKeys['highIsGood'], $name)){
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
    public function updateColumnsFromGid($gids = [], $dataArray, $insertDataKeys, $extra = null)
    {
        
        $fields = [$extra['nid'], $insertDataKeys['gid']];
        $conditions = [$insertDataKeys['gid'] . ' IN'=>$gids];
        $component = $extra['component'];
        
        //Get NIds based on GID - //Check if Guids found in database
        //getDataByParams(array $fields, array $conditions, $type = 'all')
        //$getDataByGid = $this->Indicator->getDataByParams($fields, $conditions, 'list');
        $getDataByGid = $this->{$component}->getDataByParams($fields, $conditions, 'list');
        
        //Get Guids that are not found in the database
        $freshRecordsGid = array_diff($gids, $getDataByGid);
        
        if(!empty($getDataByGid)){
            foreach($getDataByGid as $Nid => &$gid){
                
                $key = array_search($gid, $gids);
                $gid = $dataArray[$key];

                if(array_key_exists('highIsGood', $insertDataKeys)){
                    if(!array_key_exists($insertDataKeys['highIsGood'], $gid)){
                        $gid[$insertDataKeys['highIsGood']] = 0;
                    }
                }                

                //$this->Indicator->updateDataByParams($gid, [$extra['nid'] => $Nid]);
                $this->{$component}->updateDataByParams($gid, [$extra['nid'] => $Nid]);
            }
        }

        if(!empty($freshRecordsGid)){

            array_walk($freshRecordsGid, function($val, $key) use ($dataArray, $insertDataKeys, &$names) {
                $names[$key] = $dataArray[$key][$insertDataKeys['name']];
            });

            //Check existing Names when Guids NOT found in database
            return $this->updateColumnsFromName($names, $dataArray, $insertDataKeys, $extra);
        }else{
            return [];
        }

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
    public function updateColumnsFromGid2($gids = [], $dataArray, $insertDataKeys, $extra = null)
    {
        
        $fields = [$extra['nid'], $insertDataKeys['gid']];
        $conditions = [$insertDataKeys['gid'] . ' IN'=>$gids];
        $component = $extra['component'];
        
        //Get NIds based on GID - //Check if Guids found in database
        //getDataByParams(array $fields, array $conditions, $type = 'all')
        //$getDataByGid = $this->Indicator->getDataByParams($fields, $conditions, 'list');
        $getDataByGid = $this->{$component}->getDataByParams($fields, $conditions, 'list');
        
        //Get Guids that are not found in the database
        $freshRecordsGid = array_diff($gids, $getDataByGid);
        
        if(!empty($getDataByGid)){
            foreach($getDataByGid as $Nid => &$gid){
                
                $key = array_search($gid, $gids);
                $gid = $dataArray[$key];

                if(array_key_exists('highIsGood', $insertDataKeys)){
                    if(!array_key_exists($insertDataKeys['highIsGood'], $gid)){
                        $gid[$insertDataKeys['highIsGood']] = 0;
                    }
                }                

                //$this->Indicator->updateDataByParams($gid, [$extra['nid'] => $Nid]);
                $this->{$component}->updateDataByParams($gid, [$extra['nid'] => $Nid]);
            }
        }

        if(!empty($freshRecordsGid)){

            array_walk($freshRecordsGid, function($val, $key) use ($dataArray, $insertDataKeys, &$names) {
                $names[$key] = $dataArray[$key][$insertDataKeys['name']];
            });

            //Check existing Names when Guids NOT found in database
            return $this->updateColumnsFromName2($names, $dataArray, $insertDataKeys, $extra);
        }else{
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
    public function loadDataFromXlsOrCsv($filename = null, $insertDataKeys = null, $extra = null)
    {
        
        //The following line should do the same like App::import() in the older version of cakePHP
        require_once(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');

        $insertDataArr = [];
        $insertDataNames = [];
        $insertDataGids = [];
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1 ;
        
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
       
            for($row = $startRows; $row <= $highestRow; ++ $row) {
         
                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                      
				   $val = $cell->getValue();
				   
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);
                            pr($val);
                           pr($dataType);
                    if($row >= 1){  //-- Data Strats from row 6 --//                      
                        $insertDataArr[$row][] = $val;
                    }else{
                        continue;
                    }
                }
				

                if(isset($insertDataArr[$row])):
                            
                    $insertDataArr[$row] = array_combine($insertDataKeys, $insertDataArr[$row]);
                    $insertDataArr[$row] = array_filter($insertDataArr[$row]);

                    //We don't need this row if the name field is empty
                    if(!isset($insertDataArr[$row][$insertDataKeys['name']])){
                        unset($insertDataArr[$row]);
                    }else if(!isset($insertDataArr[$row][$insertDataKeys['gid']])){
                        $insertDataNames[$row] = $insertDataArr[$row][$insertDataKeys['name']];
                    }else{
                        $insertDataGids[$row] = $insertDataArr[$row][$insertDataKeys['gid']];
                    }
					 if($row>2){
			pr($insertDataKeys);	pr($insertDataGids);pr($insertDataNames);
die; 
		 
				 }

                endif;

            }
        }
        pr($insertDataArr);die;
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
    public function divideXlsOrCsvInChunks($filename = null, $extra = null)
    {
        
        //The following line should do the same like App::import() in the older version of cakePHP
        require_once(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');

        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1 ;
        $filesArray = [];
        
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $worksheet->getHighestColumn(); // e.g. 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            
            if($extra['limitRows'] !== null){
                $limitRows = $extra['limitRows'];
                
                $sheetCount = 1;
                if($highestRow > ($limitRows + ($startRows - 1))){
                    $sheetCount = ceil($highestRow - ($startRows - 1)/$limitRows);
                }
            }else{
                $limitRows = 0;
            }

            $PHPExcel = new \PHPExcel();
            $sheet = 1;

            for ($row = $startRows; $row <= $highestRow; ++ $row) {

                $endrows = $limitRows + ($startRows - 1);
                $character = 'A';

                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    $currentRow = $row - (($sheet - 1) * $limitRows);

                    $PHPExcel->getActiveSheet()->SetCellValue($character.$currentRow, $val);
                    $character++;
                }

                if(($row == $endrows) || ($row == $highestRow)){
                    //echo '<pre>'; print_r($PHPExcel);
                    $PHPExcel->setActiveSheetIndex(0);
                    $objWriter = new \PHPExcel_Writer_Excel2007($PHPExcel);
                    $sheetPath = WWW_ROOT . 'uploads' . DS . time().$sheet.'.xlsx';
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
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function bulkUploadXlsOrCsvForArea($params = null)
    {
		extract($params);
		
		
        $insertDataKeys = ['name' => _AREA_AREA_NAME, 
		'gid' => _AREA_AREA_GID, 
		'areaid' => _AREA_AREA_ID,
		//'areashortname' => _AREA_AREA_SHORT_NAME,
		'arealevel' => _AREA_AREA_LEVEL,
		'areaparentid' => _AREA_PARENT_NId,
		//'dataexists' => _AREA_DATA_EXIST,		
		//'arealevel_levelname' => _AREALEVEL_LEVEL_NAME
		
		
		];
        $extra['limitRows'] = 1000; // Number of rows in each file chunks
        $extra['startRows'] = 1; // Row from where the data reading starts

        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunks($filename, $extra);
pr( $divideXlsOrCsvInChunks);
        foreach($divideXlsOrCsvInChunks as $filename){

            $loadDataFromXlsOrCsv = $this->loadDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
            
			pr( $loadDataFromXlsOrCsv);
			pr( $insertDataKeys);
			pr( $extra);
			die;
            $dataArray = $loadDataFromXlsOrCsv['dataArray'];
            $insertDataNames = $loadDataFromXlsOrCsv['insertDataNames'];
            $insertDataGids = $loadDataFromXlsOrCsv['insertDataGids'];

            $params['insertDataKeys'] = $insertDataKeys;
            $params['updateGid'] = TRUE;
            $params['nid'] = _INDICATOR_INDICATOR_NID;

            $component = 'Indicator';
            
            $this->bulkInsert($component, $dataArray, $insertDataNames, $insertDataGids, $params);

            unlink($filename);

        }
			//pr( $loadDataFromXlsOrCsv);
			die;
	}
	
	

} 
