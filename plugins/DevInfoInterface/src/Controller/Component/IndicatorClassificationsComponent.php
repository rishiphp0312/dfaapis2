<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IndicatorClassifications Component
 */
class IndicatorClassificationsComponent extends Component {

    // The other component your component uses
    public $components = ['DevInfoInterface.CommonInterface'];
    public $IndicatorClassificationsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->IndicatorClassificationsObj = TableRegistry::get('DevInfoInterface.IndicatorClassifications');
    }

    /**
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all') {
        return $this->IndicatorClassificationsObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        return $this->IndicatorClassificationsObj->getDataByParams($fields, $conditions, $type);
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
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        return $this->IndicatorClassificationsObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {
        return $this->IndicatorClassificationsObj->deleteByParams($conditions);
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
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->IndicatorClassificationsObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * insertOrUpdateBulkData method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->IndicatorClassificationsObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->IndicatorClassificationsObj->updateDataByParams($fieldsArray, $conditions);
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
        $conditions = [_IC_IC_PARENT_NID => '-1', _IC_IC_NAME . ' IN' => $cond];
        $result = $this->getDataByParams($fields, $conditions, 'list');
        $insertResults = array_diff($cond, $result);
        
        if(!empty($insertResults)){        
            $field = [];
            $field[] = _IC_IC_NAME;
            $field[] = _IC_IC_PARENT_NID;
            $field[] = _IC_IC_GID;
            $field[] = _IC_IC_TYPE;
            $field[] = _IC_IC_GLOBAL;

            array_walk($insertResults, function(&$val, $key) use ($field, $icTypes) {
                $returnFields = [];
                $returnFields[$field[0]] = $val;
                $returnFields[$field[1]] = '-1';
                $returnFields[$field[2]] = $this->CommonInterface->guid();
                $returnFields[$field[3]] = $icTypes[$key];
                $returnFields[$field[4]] = 0;
                $val = $returnFields;
            });
            $bulkInsertArray = $insertResults;

            // Insert New Data
            $this->insertOrUpdateBulkData($bulkInsertArray);
        
            //Get all records asked
            $result = $this->getDataByParams($fields, $conditions, 'list');
        }
        
        return $result;
        
    }
    
    /**
     * getConcatedFields method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedFields(array $fields, array $conditions, $type = null)
    {
        if($type == 'list' && array_key_exists(2, $fields)){
            $result = $this->IndicatorClassificationsObj->getConcatedFields($fields, $conditions, 'all');
            if(!empty($result)){
                return array_column($result, 'concatinated', $fields[2]);
            }else{
                return [];
            }
        }else{
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
        $query =  $this->IndicatorClassificationsObj->find($type, $options);
        if(isset($extra['count'])) {
            $data = $query->count();
        }
        else {
            $results = $query->hydrate(false)->all();
            $data = $results->toArray();
        }        
        return $data;
         
    }
    
    /**
     * getConcatedFields method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getSourceList($fields = [], $conditions = [], $type = 'all')
    {
        // IC_TYPE condition is fixed - add others to it
        $conditions = array_merge($conditions, [_IC_IC_TYPE => 'SR']);
        $result = $this->getDataByParams($fields, $conditions, $type);
        return $result;
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
