<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IndicatorClassifications Component
 */
class IndicatorClassificationsComponent extends Component {

    // The other component your component uses
    public $components = ['DevInfoInterface.CommonInterface','DevInfoInterface.Data','DevInfoInterface.IcIus'];
    public $IndicatorClassificationsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
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
    public function deleteRecords($conditions = []) {
        return $this->IndicatorClassificationsObj->deleteRecords($conditions);
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
        $insertResults = array_map('unserialize', array_diff(array_map('serialize', $orCond), array_map('serialize', $result)));
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
     */
	public function checkSourceName($fieldsArray=[],$srNid=''){
		
            $conditions = [_IC_IC_NAME => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year']];
            
            if(isset($srNid) && !empty($srNid)){
                $extra[_IC_IC_NID .' != '] = $srNid;
                $conditions =  array_merge($conditions,$extra);
            }
                
              
            return $existingSources = $this->getSource(['id' => _IC_IC_NID, 'name' => _IC_IC_NAME],$conditions, 'all', ['debug' => false]);
	}
	
	
	/**
     * method to check short name 
     * 
     * @param array $fieldsArray for all data. {DEFAULT : empty}
	  @ srNid  is source nid 
     * @return void
     */
	
	public function checkShortName($fieldsArray=[],$srNid=''){
			
			$conditions = [_IC_IC_SHORT_NAME => $fieldsArray['shortName']];
            
			if(isset($srNid) && !empty($srNid)){
                $extra[_IC_IC_NID .' != '] = $srNid;
                $conditions =  array_merge($conditions,$extra);
            }            
            
			return  $existingShortName = $this->getSource([_IC_IC_SHORT_NAME], $conditions, 'all', ['debug' => false]);
           
	}

    /**
     * insert or update source records
     * 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function manageSource($fieldsArray = []) {
      
      $return = true;
      $publisher  = $fieldsArray['publisher'];
      $srNid      = (isset($fieldsArray['srNid']))?$fieldsArray['srNid']:'';
      if (!empty($publisher)) {
           /*
            $conditions = [_IC_IC_NAME => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year']];
            
            if(isset($srNid) && !empty($srNid)){
                $extra[_IC_IC_NID .' != '] = $srNid;
                $conditions =  array_merge($conditions,$extra);
            }
            //$conditions = [ _IC_IC_NAME => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year']];
                
              
            $existingSources = $this->getSource(['id' => _IC_IC_NID, 'name' => _IC_IC_NAME],$conditions, 'all', ['debug' => false]);
            */
			$existingSources = $this->checkSourceName($fieldsArray,$srNid);  //check source name 
            if (!empty($existingSources)) {
                return ['error' => _ERR132];// source  already exists 
            }
           /*
            $conditions1 = [_IC_IC_SHORT_NAME => $fieldsArray['shortName']];
            
            if(isset($srNid) && !empty($srNid)){             
                $conditions1 =  array_merge($conditions1,$extra);
            }
			 $existingShortName = $this->getSource([_IC_IC_SHORT_NAME], $conditions1, 'all', ['debug' => false]);
           */
			$existingShortName = $this->checkShortName($fieldsArray,$srNid);  //check source name 

            
            if (!empty($existingShortName)) {
                return ['error' => _ERR131];// short name already exists 
            }

            
            $existingPublishers = $this->saveAndGetPublishers([$publisher]);
            $parentId = key($existingPublishers);
            $dataSave = [];            
            $dataSave[_IC_IC_PARENT_NID] = $parentId;
            $dataSave[_IC_IC_NAME]       = $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year'];
            $dataSave[_IC_IC_GID]        = $this->CommonInterface->guid();
            $dataSave[_IC_IC_TYPE]       = 'SR';
            $dataSave[_IC_IC_GLOBAL]     = '0';
            $dataSave[_IC_IC_SHORT_NAME] = $fieldsArray['shortName']; 
            $dataSave[_IC_DIYEAR]        = $fieldsArray['year']; 
            $fields[] = $dataSave;            
            if(isset($srNid) && !empty($srNid)){            
                $sourceNid = $this->updateRecords($dataSave,[_IC_IC_NID=>$srNid]);               
            }else{                
                $sourceNid = $this->insertSource($fields);
            }
			if($sourceNid>0){
				return true;				
			}else{
				return ['error' => _ERR100];// server error
			}
      }else{
          return false;
      }  
      
    }
    
    
    public function insertOrUpdateSource($fieldsArray = []) {
        $return = true;
        $fields = [];
        $publisher = $fieldsArray['publisher'];
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
     */
    public function saveAndGetPublishers($publishers = []) {
        $return = [];
        $existingPublishers = $this->getSource([_IC_IC_NID, _IC_IC_NAME], [_IC_IC_NAME . ' IN' => $publishers], 'list');
        $newRecords = array_diff($publishers, $existingPublishers);

        if (!empty($newRecords)) {
            foreach ($newRecords as $publisher) {
                $insert[] = [
                    _IC_IC_PARENT_NID => '-1',
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
     * save and get publisher details
     * 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    
    public function saveAndGetPublishers_old($publisher = '',$srNid='') {
        
        $publisher = trim($publisher);
        echo $publisher;
        if(isset($srNid) && !empty($srNid))
        $conditions[_IC_IC_NID .'!= ']= $srNid;
        
        $existingPublishers = $this->getSource([_IC_IC_NID, _IC_IC_NAME], [_IC_IC_NAME => $publisher, _IC_IC_PARENT_NID => '-1'], 'list');
        pr( $existingPublishers);
        if(empty($existingPublishers)){
            $insert = [
                    _IC_IC_PARENT_NID => '-1',
                    _IC_IC_NAME => $publisher,
                    _IC_IC_GID => $this->CommonInterface->guid(),
                    _IC_IC_TYPE => 'SR',
                ];
           return   $publisherNid = $this->insertSource($insert);
        }else{
           return  $publisherNid =  array_keys($existingPublishers);
        }
                
          
    }
    
    /**
     * to delete the source 
     * 
     * @param srcNid the source nid. {DEFAULT : empty}
     * @return void
     */
    public function deleteSourceData($srcNid='') {
        
        $conditions = [];
        $conditions = [_IC_IC_NID . ' IN ' => $srcNid];
        $result = $this->deleteSource($conditions);       
        
        if($result>0){            
            
            $conditions = [];
            $conditions = [_MDATA_SOURCENID . ' IN ' => $srcNid];
            $data = $this->Data->deleteRecords($conditions);
            
            $conditions = [];
            $conditions = [_ICIUS_IC_NID . ' IN ' => $srcNid];
            $icius = $this->IcIus->deleteRecords($conditions);            
            return true;    
            
        }else {
            return false;
        }
        
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
    
    

}
