<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IndicatorClassifications Component
 */
class IndicatorClassificationsComponent extends Component {

    // The other component your component uses
    public $components = ['DevInfoInterface.CommonInterface', 'DevInfoInterface.Data', 'DevInfoInterface.IcIus', 'TransactionLogs'];
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
     * Delete IC childs
     *
     * @param array $nid IC_NID
     * @return void
     */
    public function deleteIcChilds($nid) {
        $childs = $this->CommonInterface->getParentChild('IndicatorClassifications', $nid, $onDemand = true, $extra = []);
        foreach($childs as $child) {
            $this->deleteRecords([_IC_IC_NID => $child['nid']]);
        }
    }

    /**
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        
        // $conditions must be an array else it will truncate whole table
        if(!is_array($conditions)) return false;
        
        $result = $this->getRecords([_IC_IC_NID], $conditions, 'all', ['first' => true]);
        
        if(!empty($result)) {
            // Delete IC
            $return = $this->IndicatorClassificationsObj->deleteRecords($conditions);

            // Deleted Associated Records - from ICIUS
            if($return) {                
                //-- TRANSAC Log
                $dbId = $this->session->read('dbId');
                $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _IC_TRANSAC, $result[_IC_IC_NID], _DONE, $LogId = null, $dbId);
                
                // Delete ICIUS
                $iciusReturn = $this->IcIus->deleteRecords([_ICIUS_IC_NID => $result[_IC_IC_NID]]);
                
                if($iciusReturn) {                    
                    //-- TRANSAC Log
                    $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _ICIUS_TRANSAC, $result[_IC_IC_NID], _DONE, $LogId = null, $dbId);
                }
                
                // Delete Childs
                $this->deleteIcChilds($result[_IC_IC_NID]);
                
            } else {
                return false;
            }
        }
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
        if(isset($extra['getAll']) && $extra['getAll'] == true) {
            
        } else if(isset($extra['publisher']) && $extra['publisher'] == true) {
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
        if(isset($fieldsArray['shortName']) && !empty($fieldsArray['shortName'])) {
            $conditions = [_IC_IC_SHORT_NAME => $fieldsArray['shortName']];

            if (isset($srNid) && !empty($srNid)) {
                $extra[_IC_IC_NID . ' != '] = $srNid;
                $conditions = array_merge($conditions, $extra);
            }

            $returnData = $this->getSource([_IC_IC_SHORT_NAME], $conditions, 'all', ['debug' => false]);
        }
        return $returnData;
        
    }

    /**
     * insert or update source records
     * 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void

      NOT REQUIRED - REMOVE IT AND USE addUpdateSource


     */
    public function manageSource($fieldsArray = [], $getSourceNid = false) {

        $return = true;
        $publisher = $fieldsArray['publisher'];
        $srNid = (isset($fieldsArray['srcNid'])) ? $fieldsArray['srcNid'] : '';
        if (!empty($publisher)) {

            $existingSources = $this->checkSourceName($fieldsArray, $srNid);  //check source name 
            if (!empty($existingSources)) {
                if ($getSourceNid == true) {
                    return reset($existingSources)['id'];
                }
                return ['error' => _ERR132]; // source  already exists 
            }

            $existingShortName = $this->checkShortName($fieldsArray, $srNid);  //check source name 
            
            if (!empty($existingShortName)) {
                return ['error' => _ERR130]; // short name already exists 
            }

            $existingPublishers = $this->saveAndGetPublishers([$publisher]);
            $parentId = key($existingPublishers);
            $dataSave = [];
            $dataSave[_IC_IC_PARENT_NID] = $parentId;
            $dataSave[_IC_IC_NAME] = $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year'];
            $dataSave[_IC_IC_GID] = $this->CommonInterface->guid();
            $dataSave[_IC_IC_TYPE] = 'SR';
            $dataSave[_IC_IC_GLOBAL] = '0';
            $dataSave[_IC_PUBLISHER] = $fieldsArray['publisher'];
            $dataSave[_IC_TITLE] = $fieldsArray['title'];
            $dataSave[_IC_IC_SHORT_NAME] = $fieldsArray['shortName'];
            $dataSave[_IC_DIYEAR] = $fieldsArray['year'];
            $fields[] = $dataSave;

            if (isset($srNid) && !empty($srNid)) {
                $sourceNid = $this->updateRecords($dataSave, [_IC_IC_NID => $srNid]);
                $sourceNid = $srNid;
            } else {
                $sourceNid = $this->insertSource($fields);
            }

            if ($sourceNid > 0) {
                 return ['id' => $sourceNid, 'name' => $fieldsArray['publisher'] . _DELEM4 . $fieldsArray['title'] . _DELEM4 . $fieldsArray['year']];
            } else {
                return ['error' => _ERR100]; // server error
            }
        } else {
            return false;
        }
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

        $conditions = [];
        $conditions = [_IC_IC_NID . ' IN ' => $srcNid];
        $result = $this->deleteSource($conditions);

        if ($result > 0) {

            $conditions = [];
            $conditions = [_MDATA_SOURCENID . ' IN ' => $srcNid];
            $data = $this->Data->deleteRecords($conditions);

            $conditions = [];
            $conditions = [_ICIUS_IC_NID . ' IN ' => $srcNid];
            $icius = $this->IcIus->deleteRecords($conditions);
            return true;
        } else {
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
      Function to Add New source in database
     */

    public function addUpdateSource($publisher, $title, $year, $shortName = null, $srcNid = '') {

        $returnData = [];
        // create source name <Publisher>_<title>_<year>
        $source = $publisher . _DELEM4 . $title . _DELEM4 . $year;

        // chekc if source name is already exist in database
        $sourceCheck = $this->checkSource($source, $srcNid);
        if (!empty($sourceCheck)) {
            $returnData['error'] = _ERR132; // source  already exists 
        } else {
            // Insert source
            // check if publisher is already exist and get nid            
            $publisherData = $this->getAddPublisher($publisher);
            if (isset($publisherData['nid']) && !empty($publisherData['nid'])) {
                // create new source
                $srcData = [
                    _IC_IC_PARENT_NID => $publisherData['nid'],
                    _IC_IC_NAME => $source,
                    _IC_IC_GID => $this->CommonInterface->guid(),
                    _IC_IC_TYPE => 'SR',
                    _IC_IC_GLOBAL => '0',
                    _IC_IC_SHORT_NAME => $shortName,
                    _IC_DIYEAR => $year,
                    _IC_PUBLISHER => $publisher
                ];

                if (!empty($srcNid)) {
                    $sourceNid = $this->updateSource($srcData, [_IC_IC_NID => $srcNid]);
                } else {
                    $fields[] = $srcData;
                    $sourceNid = $this->insertSource($fields);
                }
                if ($sourceNid > 0) {
                    $returnData = true;
                } else {
                    $returnData['error'] = _ERR100; // server error
                }
            }
        }

        return $returnData;
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
        if(!isset($fieldsArray[_IC_IC_NID]) || empty($fieldsArray[_IC_IC_NID])) {
            
            // Requested name already exists
            if(!empty($results)) {
                return ['error' => _ERR154];
            }
            
            // GID not set OR is empty
            if(!isset($fieldsArray[_IC_IC_GID]) || empty($fieldsArray[_IC_IC_GID])) {
                $fieldsArray[_IC_IC_GID] = $this->CommonInterface->guid();
            }
            // Parent_NId not set OR is empty
            if(!isset($fieldsArray[_IC_IC_PARENT_NID]) || empty($fieldsArray[_IC_IC_PARENT_NID])) {
                $fieldsArray[_IC_IC_PARENT_NID] = -1;
            }

        } // UPDATE Case
        else {
            
            // Check if the existing record is the requested one, if YES, then UNSET
            if(array_key_exists($fieldsArray[_IC_IC_NID], $results)) {
                unset($results[$fieldsArray[_IC_IC_NID]]);
            }
            
            // Check if Requested name already exists
            if(!empty($results)) {
                return ['error' => _ERR154];
            }
            
            // GID not set OR is empty
            if(!isset($fieldsArray[_IC_IC_GID]) || empty($fieldsArray[_IC_IC_GID])) {
                // Check in DB
                $results = $this->getRecords([_IC_IC_GID], [_IC_IC_NID => $fieldsArray[_IC_IC_NID]], 'all', ['first' => true]);
                if(!empty($results)) {
                    // If GUID is empty in DB - Auto Generate
                    if(empty($results[_IC_IC_GID])){
                        $fieldsArray[_IC_IC_GID] = $this->CommonInterface->guid();
                    }
                }
            }
            // Parent_NId set AND is empty
            if(isset($fieldsArray[_IC_IC_PARENT_NID]) && empty($fieldsArray[_IC_IC_PARENT_NID])) {
                $fieldsArray[_IC_IC_PARENT_NID] = -1;
            }
        }
        
        return $this->insertData($fieldsArray);
    }

}
