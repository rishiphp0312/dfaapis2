<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IndicatorClassifications Component
 */
class IndicatorClassificationsComponent extends Component {

    // The other component your component uses
    public $components = ['DevInfoInterface.CommonInterface', 'DevInfoInterface.Data', 'DevInfoInterface.IcIus', 'TransactionLogs', 'Common', 'DevInfoInterface.IndicatorUnitSubgroup'];
    public $IndicatorClassificationsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->IndicatorClassificationsObj = TableRegistry::get('DevInfoInterface.IndicatorClassifications');
    }

    /**
     * getRecords method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->IndicatorClassificationsObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * getGroupedList method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getGroupedList(array $fields, array $conditions) {
        return $this->IndicatorClassificationsObj->getGroupedList($fields, $conditions);
    }

    /**
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteIc($conditions = []) {
        // $conditions must be an array else it will truncate whole table
        if (!is_array($conditions))
            return false;

        if (!isset($conditions[_IC_IC_NID]))
            $result = $this->getRecords([_IC_IC_NID], $conditions, 'all', ['first' => true]);
        else
            $result[_IC_IC_NID] = $conditions[_IC_IC_NID];

        if (!empty($result)) {
            
            $result[_IC_IC_NID] = (int) $result[_IC_IC_NID];

            // Get all childs
            $childs = $this->getIcChilds($result[_IC_IC_NID]);
            $IcNids = array_merge($childs, [$result[_IC_IC_NID]]);

            // Get all IUS from ICIUS for this ICNid
            $IUSes = $this->IcIus->getRecords([_ICIUS_IC_IUSNID, _ICIUS_IUSNID], [_ICIUS_IC_NID => $result[_IC_IC_NID]], 'list');

            // Delete IC and their associated child records 
            if (!empty($IcNids)) {
                
                // Get Ancestors
                $ancestors = $this->getIcAncestors($result[_IC_IC_NID]);
                $icNidsList = $this->IndicatorClassificationsObj->getRecords([_IC_IC_NID, _IC_IC_NAME], [_IC_IC_NID . ' IN' => $IcNids], 'list');
                
                foreach ($IcNids as $IcNid) {
                    // Delete IC
                    $this->IndicatorClassificationsObj->deleteRecords([_IC_IC_NID => $IcNid]);

                    //-- TRANSAC Log
                    $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _IC_TRANSAC, $IcNid, _DONE, $LogId = null, $icNidsList[$IcNid], '');
                }

                // Return Ancestors + childs + Requested Nid
                $IcNids = array_merge($ancestors, $IcNids);
                
                // Deleted Associated Records - from ICIUS
                if (!empty($IUSes)) {
                    foreach($IcNids as $IcNid) {
                        $this->IcIus->deleteRecords([_ICIUS_IC_NID => $IcNid, _ICIUS_IUSNID . ' IN' => array_values($IUSes)]);
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        return $this->IndicatorClassificationsObj->insertData($fieldsArray);
    }

    /**
     * Insert/Update multiple rows at once
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->IndicatorClassificationsObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateRecords method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->IndicatorClassificationsObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * saveNameAndGetNids method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @param array $cond Parent_Nid and Name Combination Array. {DEFAULT : empty}
     * @return void
     */
    public function saveNameAndGetNids($fieldsArray = [], $cond = [], $extra = []) {

        $fields = $fieldsArray;
        $icTypes = $extra['icTypes'];
        $orCond = [];
        foreach ($cond as $keys => $vals) {
            if (!empty($icTypes[$keys])) {
                $orCond[$keys][_IC_IC_NAME] = $vals;
                $orCond[$keys][_IC_IC_TYPE] = $icTypes[$keys];
            }
        }

        //$conditions = [_IC_IC_PARENT_NID => '-1', _IC_IC_NAME . ' IN' => $cond];
        $conditions = [_IC_IC_PARENT_NID => '-1', 'OR' => $orCond];
        $fieldsNew = [_IC_IC_NAME, _IC_IC_TYPE];
        $result = $this->getRecords($fieldsNew, $conditions, 'all');
        $insertResults = array_map('unserialize', array_diff(array_unique(array_map('serialize', $orCond)), array_map('serialize', $result)));
        //$insertResults = array_diff($cond, $result);

        if (!empty($insertResults)) {
            $field = [];
            $field[] = _IC_IC_NAME;
            $field[] = _IC_IC_PARENT_NID;
            $field[] = _IC_IC_GID;
            $field[] = _IC_IC_TYPE;
            $field[] = _IC_IC_GLOBAL;

            array_walk($insertResults, function(&$val, $key) use ($field, $icTypes) {
                $returnFields = [];
                //$returnFields[$field[0]] = $val;
                $returnFields[$field[0]] = $val[_IC_IC_NAME];
                $returnFields[$field[1]] = '-1';
                $returnFields[$field[2]] = $this->CommonInterface->guid();
                $returnFields[$field[3]] = $icTypes[$key];
                $returnFields[$field[4]] = 0;
                $val = $returnFields;
            });
            $bulkInsertArray = $insertResults;

            // Insert New Data
            $this->insertOrUpdateBulkData($bulkInsertArray);
        }
        $result = $this->getRecords($fields, $conditions, 'all');

        return $result;
    }

    /**
     * getConcatedFields method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedFields(array $fields, array $conditions, $type = null) {
        if ($type == 'list' && array_key_exists(3, $fields)) {
            $result = $this->IndicatorClassificationsObj->getConcatedFields($fields, $conditions, 'all');
            if (!empty($result)) {
                return array_column($result, 'concatinated', $fields[3]);
            } else {
                return [];
            }
        } else {
            return $this->IndicatorClassificationsObj->getConcatedFields($fields, $conditions, $type);
        }
    }

    /*
     * find method
     * 
     * @param string $type Query type. {DEFAULT : all}
     * @param array $options Query options. {DEFAULT : empty}
     * @param array $extra any extra params. {DEFAULT : empty}
     * @return void
     */

    public function find($type, $options = [], $extra = null) {
        $query = $this->IndicatorClassificationsObj->find($type, $options);
        if (isset($extra['count'])) {
            $data = $query->count();
        } else {
            $results = $query->hydrate(false)->all();
            $data = $results->toArray();
        }
        return $data;
    }

    /**
     * get source records
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type Query type. {DEFAULT : all}
     * @return void
     */
    public function getSource($fields = [], $conditions = [], $type = 'all', $extra = []) {
        if (isset($extra['getAll']) && $extra['getAll'] == true) {
            
        } else if (isset($extra['publisher']) && $extra['publisher'] == true) {
            $conditions[_IC_IC_PARENT_NID] = '-1';
        } else {
            $conditions[_IC_IC_PARENT_NID . ' <>'] = '-1';
        }

        // IC_TYPE condition is fixed - add others to it
        $conditions = array_merge($conditions, [_IC_IC_TYPE => 'SR']);
        $result = $this->getRecords($fields, $conditions, $type, $extra);
        return $result;
    }

    /**
     * insert new source records
     * 
     * @param array $fieldsArray The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function insertSource($fieldsArray = []) {
        $result = $this->IndicatorClassificationsObj->insertOrUpdateBulkData($fieldsArray);
        return $result;
    }

    /**
     * update existing source records
     * 
     * @param array $fieldsArray The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateSource($fieldsArray = [], $conditions = []) {
        // IC_TYPE condition is fixed - add others to it
        $conditions = array_merge($conditions, [_IC_IC_TYPE => 'SR']);
        $result = $this->IndicatorClassificationsObj->updateRecords($fieldsArray, $conditions);
        return $result;
    }

    /**
     * delete source records
     * 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function deleteSource($conditions = []) {
        // IC_TYPE condition is fixed - add others to it
        $conditions = array_merge($conditions, [_IC_IC_TYPE => 'SR']);
        $result = $this->IndicatorClassificationsObj->deleteRecords($conditions);
        return $result;
    }

    /**
     * method to check source name 
     * 
     * @param array $fieldsArray for all data. {DEFAULT : empty}
      @ srNid  is source nid
     * @return void

      NOT REQUIRED - REMOVE IT AND USE checkSource


     */
    public function checkSourceName($fieldsArray = [], $srNid = '') {

        $conditions = [_IC_IC_NAME => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year']];

        if (isset($srNid) && !empty($srNid)) {
            $extra[_IC_IC_NID . ' != '] = $srNid;
            $conditions = array_merge($conditions, $extra);
        }

        return $existingSources = $this->getSource(['id' => _IC_IC_NID, 'name' => _IC_IC_NAME], $conditions, 'all', ['debug' => false]);
    }

    /**
     * method to check short name 
     * 
     * @param array $fieldsArray for all data. {DEFAULT : empty}
      @ srNid  is source nid
     * @return void
     */
    public function checkShortName($fieldsArray = [], $srNid = '') {

        $returnData = '';
        if (isset($fieldsArray['shortName']) && !empty($fieldsArray['shortName'])) {
            $conditions = [_IC_IC_SHORT_NAME => $fieldsArray['shortName']];

            if (isset($srNid) && !empty($srNid)) {
                $extra[_IC_IC_NID . ' != '] = $srNid;
                $conditions = array_merge($conditions, $extra);
            }

            $returnData = $this->getSource([_IC_IC_SHORT_NAME], $conditions, 'all', ['debug' => false]);
        }
        return $returnData;
    }

    /*
      function to check data legth
     */

    public function sourceDataLengthCheck($publisher, $year,$shortname='') {
        $success = true;
        $error = '';

        if (strlen($publisher) > 100) {
            $success = false;
            $error = _ERR164;
        }
        if (strlen($year) > 10) {
            $success = false;
            $error = _ERR165;
        }
        if ($shortname!='' && strlen($shortname) > 50) {
            $success = false;
            $error = _ERR197;
        }
        

        return ['success' => $success, 'error' => $error];
    }

    

    // NOT REQUIRED - REMOVE IT AND USE addUpdateSource

    public function insertOrUpdateSource($fieldsArray = []) {
        $return = true;
        $fields = [];
        $publisher[] = $fieldsArray['publisher'];
        $fields = [
            _IC_PUBLISHER => $fieldsArray['publisher'],
            _IC_IC_NAME => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year'],
            _IC_DIYEAR => $fieldsArray['year'],
        ];

        // Check if publisher is provided for at least one record
        if (!empty($publisher)) {

            // Get all Publishers
            $existingPublishers = $this->saveAndGetPublishers($publisher);

            // Get existing sources
            $existingSources = $this->getSource(['id' => _IC_IC_NID, 'name' => _IC_IC_NAME], [$fields], 'all', ['debug' => false]);

            // Return immediately if already found else INSERT
            if (!empty($existingSources)) {
                return reset($existingSources);
            }

            $newRecords = array_diff(array_column($fields, _IC_IC_NAME), $existingSources);
            $fields = array_intersect_key($fields, $newRecords);

            // Return immediately with error if Shortname Exists else INSERT
            $existingShortName = $this->getSource([_IC_IC_SHORT_NAME], [_IC_IC_SHORT_NAME => $fieldsArray['shortName']], 'all', ['debug' => false]);
            if (!empty($existingShortName)) {
                return ['error' => _ERR130];
            }

            if (!empty($fields)) {
                foreach ($fields as &$field) {
                    if (in_array($field[_IC_PUBLISHER], $existingPublishers)) {
                        $field[_IC_IC_PARENT_NID] = array_search($field[_IC_PUBLISHER], $existingPublishers);
                        $field[_IC_IC_GID] = $this->CommonInterface->guid();
                        $field[_IC_IC_TYPE] = 'SR';
                        $field[_IC_IC_GLOBAL] = '0';
                        $field[_IC_IC_SHORT_NAME] = $fieldsArray['shortName'];
                    }
                }
                // Insert Source - take only first element as we are inserting only one record
                $sourceNid = $this->insertSource($fields);
                $return = ['id' => $sourceNid, 'name' => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year']];
            }
        } // No pulisher provided
        else {
            $return = false;
        }

        return $return;
    }

    /**
     * save and get publisher details
     * 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void

      RENAME THE FUNCTION NAME - save multiple publisher and get nids


     */
    public function saveAndGetPublishers($publishers = []) {
        $return = [];
        $existingPublishers = $this->getSource([_IC_IC_NID, _IC_IC_NAME], [_IC_IC_NAME . ' IN' => $publishers], 'list', ['publisher' => true]);
        $newRecords = array_diff($publishers, $existingPublishers);

        if (!empty($newRecords)) {
            foreach ($newRecords as $publisher) {
                $insert[] = [
                    _IC_IC_PARENT_NID => '-1',
                    _IC_IC_GLOBAL => '0',
                    _IC_IC_NAME => $publisher,
                    _IC_IC_GID => $this->CommonInterface->guid(),
                    _IC_IC_TYPE => 'SR',
                ];
            }
            $publisherNid = $this->insertSource($insert);
            $return[$publisherNid] = $publisher;
        }

        return array_replace($existingPublishers, $return);
    }

    /**
     * to delete the source 
     * 
     * @param srcNid the source nid. {DEFAULT : empty}
     * @return void
     */
    public function deleteSourceData($srcNid = '') {
        $action = _DELETE; $olddataValue ='';
        
        $conditions = [];
        $conditions = [_IC_IC_NID . ' IN ' => $srcNid];
		
		$SourcesOld = $this->getSource([_IC_IC_NAME], [_IC_IC_NID => $srcNid], 'all', ['debug' => false]);
		
		if(!empty($SourcesOld) ){                
			$olddataValue = current($SourcesOld)[_IC_IC_NAME];   
		
        $result = $this->deleteSource($conditions);

        if ($result > 0) {
			

            $conditions = [];
            $conditions = [_MDATA_SOURCENID . ' IN ' => $srcNid];
            $data = $this->Data->deleteRecords($conditions);

            $conditions = [];
            $conditions = [_ICIUS_IC_NID . ' IN ' => $srcNid];
            $icius = $this->IcIus->deleteRecords($conditions);
          
            $this->TransactionLogs->createLog($action, _DATAENTRYVAL, _SOURCE, $srcNid, _DONE, '', '', $olddataValue, '', '');
            return true;
        } else {

            $this->TransactionLogs->createLog($action, _DATAENTRYVAL, _SOURCE, $srcNid, _FAILED, '', '', $olddataValue, '', _ERR_TRANS_LOG);
            return false;
        }
		
		}else{
			$this->TransactionLogs->createLog($action, _DATAENTRYVAL, _SOURCE, $srcNid, _FAILED, '', '', '', '', _ERR_RECORD_NOTFOUND);
            return false;
      	
		}
    }

    /**
     * to get  source details of specific id 
     * 
     * @param srcNid the source nid. {DEFAULT : empty}
     * @return void

      NOT REQUIRED - REMOVE IT AND USE getSourceDetail

     */
    public function getSourceByID($srcNid = '') {
        $returnArray = [];
        $fields = [_IC_IC_NAME, _IC_IC_SHORT_NAME, _IC_IC_TYPE, _IC_PUBLISHER, _IC_TITLE, _IC_DIYEAR];
        $conditions = [_IC_IC_NID => $srcNid];
        $data = $this->getRecords($fields, $conditions);
        $data = current($data);
        $publisher = '';
        $title = '';
        $year = '';
        if (isset($data[_IC_IC_NAME])) {
            $exp = explode("_", $data[_IC_IC_NAME]);
            if (count($exp) > 0) {

                $publisher = $exp[0];
                $year = $exp[count($exp) - 1];
                for ($i = 1; $i < count($exp) - 1; $i++) {
                    $tit[] = $exp[$i];
                }
                if (!empty($tit))
                    $title = implode("_", $tit);
            }
        }
        $returnArray = ['publisher' => $publisher, 'title' => $title
            , 'year' => $year, 'shortName' => $data[_IC_IC_SHORT_NAME]];
        return $returnArray;
    }

    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->IndicatorClassificationsObj->testCasesFromTable($params);
    }

    // ------------------------------------------------------------
    // written by ved
    // CRUD for Source

    /*
      Function to get and add publisher details
     */
    public function getSourceDetail($srcNid) {
        $fields = [_IC_IC_NAME, _IC_IC_SHORT_NAME, _IC_IC_TYPE, _IC_PUBLISHER, _IC_TITLE, _IC_DIYEAR];
        $conditions = [_IC_IC_NID => $srcNid];

        return $this->getSource($fields, $conditions, 'all');
    }

    
    
    
    /*
    * validate the source 
    */
    public function validateSource($fieldsArray,$getSourceNid){
        
        if(!isset($fieldsArray['publisher']) || empty($fieldsArray['publisher']) ){
            return ['error' => _ERR183]; // source  empty
        }
        
        if(!isset($fieldsArray['year']) || empty($fieldsArray['year']) ){
            return ['error' => _ERR184]; // year  empty
        }
        
        if(!isset($fieldsArray['title']) || empty($fieldsArray['title']) ){
            return ['error' => _ERR185]; // title  empty
        }
        
        $srNid = (isset($fieldsArray['srcNid']) && !empty($fieldsArray['publisher']))? $fieldsArray['srcNid'] : '';
        
        $shortName =  (isset($fieldsArray['shortName']) && !empty($fieldsArray['shortName']))?$fieldsArray['shortName']:'';
        
        $lengthCheck = $this->sourceDataLengthCheck($fieldsArray['publisher'], $fieldsArray['year'],$shortName);
        if ($lengthCheck['success'] === false) {
            return ['error' => $lengthCheck['error']]; // source  already exists 
        }
        
        $existingShortName = $this->checkShortName($fieldsArray, $srNid);  //check short  name 
        if (!empty($existingShortName)) {
             return ['error' =>   _ERR130]; // short name already exists 
        }
        
        $existingSources = $this->checkSourceName($fieldsArray, $srNid);  //check source name 
        if (!empty($existingSources)) {
            if ($getSourceNid == true) {
                return ['id'=> reset($existingSources)['id']];
            }
            return ['error' => _ERR132]; // source  already exists 
        }
        
    }
    
    /*
       method  to Add/modify the source in database
       @fieldsArray posted data 
       @getSourceNid default false returns source id if true 
     *  
    */

    public function manageSource($fieldsArray = [], $getSourceNid = false) { 
        
        $newValue = '';   $olddataValue = '';
        $validate = $this->validateSource($fieldsArray,$getSourceNid); //validate the source details 
        if ($getSourceNid == true) {
            if(isset($validate['id'])){            
               return $validate['id'];   //if needs to return nid of source 
            }
        }
        if(isset($validate['error'])){
            
            return ['error'=>$validate['error']]; //if any error 
        }
        
        $publisher = $fieldsArray['publisher'] ; $title = $fieldsArray['title'] ; $year  = $fieldsArray['year'] ;
        $srNid     = (isset($fieldsArray['srcNid']) && !empty($fieldsArray['srcNid']))?$fieldsArray['srcNid']:'' ;
        $shortName = (isset($fieldsArray['shortName']) && !empty($fieldsArray['shortName']))?$fieldsArray['shortName']:'';
        
       
        if (!empty($publisher)) {
            
            $existingPublishers = $this->saveAndGetPublishers([$publisher]);//if new add else get existing id 
            
            if (!empty($existingPublishers)) {
                
                $parentId = key($existingPublishers);                
                $dataSave[_IC_IC_PARENT_NID] = $parentId;
                $dataSave[_IC_IC_NAME] = $publisher . _DELEM4 . $title . _DELEM4 . $year;
                if (empty($srNid)) {
                     $dataSave[_IC_IC_GID] = $this->CommonInterface->guid();
                }
                $dataSave[_IC_IC_TYPE] = 'SR';
                $dataSave[_IC_IC_GLOBAL] = '0';
                $dataSave[_IC_PUBLISHER] = $publisher;
                $dataSave[_IC_TITLE] = $title;
                $dataSave[_IC_IC_SHORT_NAME] = $shortName;
                $dataSave[_IC_DIYEAR] = $year;
                $fields[] = $dataSave;    
				$newValue = $dataSave[_IC_IC_NAME];
            
                if (isset($srNid) && !empty($srNid)) {
                    
                    $action = _UPDATE; //
                   
                    $lastNid = $srNid;
                    $SourcesOld = $this->getSource([_IC_IC_NAME], [_IC_IC_NID => $srNid], 'all', ['debug' => false]);
					
                    if(!empty($SourcesOld)){                
                        $olddataValue = current($SourcesOld)[_IC_IC_NAME];   
                    
					}
					
                    $sourceNid = $this->updateRecords($dataSave, [_IC_IC_NID => $srNid]);
                    $sourceNid = $srNid;
                    
                } else {
                    
                    $action = _INSERT; //
                    $lastNid = $sourceNid = $this->insertSource($fields);
                }

                if ($sourceNid > 0) {
                    $status = _DONE;
                    $this->TransactionLogs->createLog($action, _DATAENTRYVAL, _SOURCE, $lastNid, $status, '', '', $olddataValue, $newValue, '');
                    return ['id' => $sourceNid, 'name' => $publisher . _DELEM4 . $title . _DELEM4 . $year];
                } else {
                    $status = _FAILED;
                    $this->TransactionLogs->createLog($action, _DATAENTRYVAL, _SOURCE, $lastNid, $status, '', '', $olddataValue, $newValue, _ERR_TRANS_LOG);

                    return ['error' => _ERR100]; // server error
                }
            } //
            

        }
        
        
    }
    
   

    /*
      Function to get and add publisher details
     */

    public function getAddPublisher($publisher) {
        $return = [];

        $check = $this->getSource([_IC_IC_NID, _IC_IC_NAME], [_IC_IC_NAME => $publishers, _IC_IC_PARENT_NID => -1], 'all');
        // Publisher already exists
        if ($check) {
            $pubNid = $check[_IC_IC_NID];
            $pubName = $check[_IC_IC_NAME];
        } else {
            // create a new publisher
            $insert[] = [
                _IC_IC_PARENT_NID => '-1',
                _IC_IC_NAME => $publisher,
                _IC_IC_GID => $this->CommonInterface->guid(),
                _IC_IC_TYPE => 'SR',
            ];
            $pubNid = $this->insertSource($insert);
            $pubName = $publisher;
        }

        $return = ['nid' => $pubNid, 'name' => $pubName];

        return $return;
    }

    /*
     * method to check source name 
     */

    public function checkSource($source, $srcNid = '') {
        $conditions[_IC_IC_NAME] = $source;
        if (!empty($srcNid)) {
            $conditions[_IC_IC_NID . ' != '] = $srcNid;
        }

        return $this->getSource(['id' => _IC_IC_NID, 'name' => _IC_IC_NAME], $conditions, 'all', ['debug' => false]);
    }

    /**
     * ADD/MODIFY Indicator Classification 
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return string _IC_IC_NID
     */
    public function saveIC($fieldsArray) {

        // Check in DB
        $results = $this->getRecords([_IC_IC_NID, _IC_IC_GID], [_IC_IC_NAME => $fieldsArray[_IC_IC_NAME], _IC_IC_TYPE => $fieldsArray[_IC_IC_TYPE]], 'list');

        // INSERT Case
        if (!isset($fieldsArray[_IC_IC_NID]) || empty($fieldsArray[_IC_IC_NID])) {

            // Requested name already exists
            if (!empty($results)) {
                return ['error' => _ERR154];
            }

            // GID not set OR is empty
            if (!isset($fieldsArray[_IC_IC_GID]) || empty($fieldsArray[_IC_IC_GID])) {
                $fieldsArray[_IC_IC_GID] = $this->CommonInterface->guid();
            }
            // Parent_NId not set OR is empty
            if (!isset($fieldsArray[_IC_IC_PARENT_NID]) || empty($fieldsArray[_IC_IC_PARENT_NID])) {
                $fieldsArray[_IC_IC_PARENT_NID] = -1;
            }
        } // UPDATE Case
        else {

            // Check if the existing record is the requested one, if YES, then UNSET
            if (array_key_exists($fieldsArray[_IC_IC_NID], $results)) {
                unset($results[$fieldsArray[_IC_IC_NID]]);
            }

            // Check if Requested name already exists
            if (!empty($results)) {
                return ['error' => _ERR154];
            }

            // GID not set OR is empty
            if (!isset($fieldsArray[_IC_IC_GID]) || empty($fieldsArray[_IC_IC_GID])) {
                // Check in DB
                $results = $this->getRecords([_IC_IC_GID], [_IC_IC_NID => $fieldsArray[_IC_IC_NID]], 'all', ['first' => true]);
                if (!empty($results)) {
                    // If GUID is empty in DB - Auto Generate
                    if (empty($results[_IC_IC_GID])) {
                        $fieldsArray[_IC_IC_GID] = $this->CommonInterface->guid();
                    } else {
                        unset($fieldsArray[_IC_IC_GID]);
                    }
                }
            }

            // Parent_NId set AND is empty
            if (isset($fieldsArray[_IC_IC_PARENT_NID]) && empty($fieldsArray[_IC_IC_PARENT_NID])) {
                $fieldsArray[_IC_IC_PARENT_NID] = -1;
            }
        }

        // GID exists in request
        if (isset($fieldsArray[_IC_IC_GID]) && !empty($fieldsArray[_IC_IC_GID])) {
            
            // Check if requested GID already exists
            $results = $this->getRecords([_IC_IC_NID, _IC_IC_GID], [_IC_IC_GID => $fieldsArray[_IC_IC_GID]], 'list');
            
            // Check if the existing record is the requested one, if YES, then UNSET
            if (array_key_exists($fieldsArray[_IC_IC_NID], $results))
                unset($results[$fieldsArray[_IC_IC_NID]]);

            // Check if Requested GID already exists
            if (!empty($results))
                return ['error' => _ERR158];

            // Validate Guid
            $validateGuid = $this->Common->validateGuid($fieldsArray[_IC_IC_GID]);
            if ($validateGuid == false)
                return ['error' => _ERR142];
        }
        
        $icNid = $this->insertData($fieldsArray);
        
        if($icNid) {
            if (!isset($fieldsArray[_IC_IC_NID]) || empty($fieldsArray[_IC_IC_NID])) {
                $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _IC_TRANSAC, $icNid, _DONE, $LogId = null, '', '', $fieldsArray[_IC_IC_NAME]);
            } else {
                $this->TransactionLogs->createLog(_UPDATE, _TEMPLATEVAL, _IC_TRANSAC, $icNid, _DONE, $LogId = null, '', $fieldsArray[_IC_IC_NAME], $fieldsArray[_IC_IC_NAME]);
            }
            return $icNid;
        } else {
            $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _IC_TRANSAC, '', _FAILED, $LogId = null, '', '', $fieldsArray[_IC_IC_NAME]);
            return false;
        }
    }

    /**
     * Save IC and Join With IUS to make ICIUS
     * 
     * @param array $fieldsArray IC fields to be saved/modified
     * @param array $params IUS params to be associated with
     * @return array/boolean Error/true-false
     */
    public function saveIcAndIcius($fieldsArray, $params) {

        // Save IC
        $icNid = $this->saveIC($fieldsArray);

        // Simply return if there is some error
        if (!empty($icNid) && isset($icNid['error']))
            return $icNid;

        // Associate with IUS and make ICIUS
        $result = $this->associateIcWithIUS($icNid, $params);

        return $result;
    }

    /**
     * Associate IC with IUS and create/delete ICIUS
     * 
     * @param string $IcNid ICNid
     * @param array $params IUS params to be associated with
     * @return array/boolean Error/true-false
     */
    public function associateIcWithIUS($icNid, $param) {

        // Check if valid IUS combination
        $iusNidsRequest = array_filter($param['ius']);
        $parentIcNid = $param['parentIcNid'];

        // Get IC ancestors and Childs
        $icNids = $this->getAncestorsAndChildIcs($icNid, $parentIcNid, $getFirstChild = true);
        $icNids = array_merge($icNids, [$icNid]);

        // Get all IUS from this IC
        $iusNidsDB = $this->IcIus->getRecords([_ICIUS_IC_IUSNID, _ICIUS_IUSNID], [_ICIUS_IC_NID => $icNid], 'list');

        // Delete those ICIUS which exists in DB but not in request
        $iciusToBeDeleted = array_diff($iusNidsDB, $iusNidsRequest);
        if (!empty($iciusToBeDeleted))
            $this->deleteIcAssociationsFromIUS($iciusToBeDeleted, $icNids);

        // Check for ICIUS from request which does not exists in DB
        $newIus = array_diff($iusNidsRequest, $iusNidsDB);

        // INSERT IC and IUS combination which doesn't exists in DB
        if (!empty($newIus)) {
            return $this->createIcius($icNids, $newIus);
        }

        return true;
    }

    public function deleteIcAssociationsFromIUS($IUSes, $icNids) {
        return $this->IcIus->deleteRecords([_ICIUS_IUSNID . ' IN' => $IUSes, _ICIUS_IC_NID . ' IN' => $icNids]);
    }

    /**
     * Get Ancestors and Childs Nids for a IC
     * 
     * @param string $IcNid ICNid
     * @param string $parentIcNid Parent ICNid
     * @return array Ancestors and Childs Nids
     */
    public function getAncestorsAndChildIcs($IcNid, $parentIcNid = null, $getFirstChild = false) {

        // Get Ancestors
        if (!empty($parentIcNid) && $parentIcNid != '-1') {
            $ancestors = $this->getIcAncestors($parentIcNid);
            $ancestors[] = $parentIcNid;
        } else {
            $ancestors = $this->getIcAncestors($IcNid);
        }

        // Get Childs
        $childs = $this->getIcChilds($IcNid, $getFirstChild);

        // Return Ancestors + childs
        return array_merge($ancestors, $childs);
    }

    /**
     * Get IC Ancestors 
     * 
     * @param string $nid ICNid
     * @return array Ancestors Nids
     */
    public function getIcAncestors($nid, $addNid = false) {

        $ancestors = [];
        $icDetails = $this->getRecords([_IC_IC_PARENT_NID], [_IC_IC_NID => $nid], 'all', ['first' => true]);

        if (!empty($icDetails)) {
            if ($icDetails[_IC_IC_PARENT_NID] == '-1') {
                if ($addNid == true)
                    $ancestors = [$nid];
            } else {
                if ($addNid == true)
                    $ancestors[] = $nid;
                $ancestors = array_merge($ancestors, $this->getIcAncestors($icDetails[_IC_IC_PARENT_NID], true));
            }
        }

        return $ancestors;
    }

    /**
     * Get IC Childs 
     * 
     * @param string $IcNid ICNid
     * @return array Childs Nids
     */
    public function getIcChilds($IcNid, $getFirstChild = false, $getParentChilds = null, $nthLevel = true) {

        $childs = [];
        
        if(empty($getParentChilds)) {
            // Get Child associative array view
            $getParentChilds = $this->CommonInterface->getParentChild('IndicatorClassifications', $IcNid, $onDemand = false, $extra = []);
        }
        
        if (!empty($getParentChilds)) {
            if($getFirstChild == false) {
                foreach($getParentChilds as $getParentChild) {
                    array_push($childs, $getParentChild['nid']);
                    $arrayDepth = $getParentChild['arrayDepth'];
                    if ($arrayDepth > 1 && $nthLevel == true) {
                        for ($i = 2; $i <= $arrayDepth; $i++) {
                            $childs = array_merge($childs, $this->getIcChilds(null, false, $getParentChild['nodes']));
                        }
                    }
                }
            } else {
                $getParentChild = reset($getParentChilds);
                array_push($childs, $getParentChild['nid']);
                $arrayDepth = $getParentChild['arrayDepth'];
                if ($arrayDepth > 1) {
                    for ($i = 2; $i <= $arrayDepth; $i++) {
                        $getParentChild = reset($getParentChild['nodes']);
                        array_push($childs, $getParentChild['nid']);
                    }
                }
            }
        } // Child levels gathered (if exists) for existing parent
        
        return $childs;
    }

    /**
     * Create Icius from IC and IUS
     * 
     * @param string $icNids ICNids
     * @param string $newIus IUS Nids
     * @return array Ancestors and Childs Nids
     */
    public function createIcius($icNids, $IUSes) {

        $existingIciusList = $this->IcIus->getConcatedFields([_ICIUS_IUSNID, _ICIUS_IC_NID, _ICIUS_IC_IUSNID], [_ICIUS_IUSNID . ' IN' => $IUSes, _ICIUS_IC_NID . ' IN' => $icNids], 'list');

        foreach ($icNids as $icNid) {
            foreach ($IUSes as $ius) {
                $concatIcIus = '(' . $icNid . ',' . $ius . ')';
                if (!in_array($concatIcIus, $existingIciusList)) {
                    $inserIcius = [
                        _ICIUS_IC_NID => $icNid,
                        _ICIUS_IUSNID => $ius
                    ];
                    // CREATE ICIUS one by one as MSSQL has 2100 param limit
                    $this->IcIus->insertData($inserIcius);
                }
            }
        }
        /* if(!empty($inserIcius))
          return $this->IcIus->insertOrUpdateBulkData($inserIcius); */

        return true;
    }
    
    /**
     * method to get total no of SOURCES  
     */
    public function getSourcesCount($conditions = []) {

        $count = 0;
        return $count = $this->IndicatorClassificationsObj->getCount($conditions);
    }

}
