<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Indicator Component
 */
class IndicatorComponent extends Component {

    // The other component your component uses
    public $components = ['TransactionLogs'];
    public $IndicatorObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->IndicatorObj = TableRegistry::get('DevInfoInterface.Indicator');
    }

    /**
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all') {
        return $this->IndicatorObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        // MSSQL Compatibilty - MSSQL can't support more than 2100 params - 900 to be safe
        $chunkSize = 900;
        
        if(isset($conditions['OR']) && count($conditions['OR'], true) > $chunkSize){
            
            $result = [];
            
            // count for single index
            $orSingleParamCount = count(reset($conditions['OR']));
            $splitChunkSize = floor(count($conditions['OR'])/$orSingleParamCount);
            
            // MSSQL Compatibilty - MSSQL can't support more than 2100 params
            $orConditionsChunked = array_chunk($conditions['OR'], $splitChunkSize);
            
            foreach($orConditionsChunked as $orCond){
                $conditions['OR'] = $orCond;
                $getIndicator = $this->IndicatorObj->getDataByParams($fields, $conditions, $type);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if($type == 'list'){
                    $result = array_replace($result, 'concatinated', _IUS_IUSNID);
                }// we dont need to preserve keys, just merge
                else{
                    $result = array_merge($result, $getIndicator);
                }
            }
        }else{
            $result = $this->IndicatorObj->getDataByParams($fields, $conditions, $type);
        }
        return $result;
    }

    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        return $this->IndicatorObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {
        return $this->IndicatorObj->deleteByParams($conditions);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        $return = $this->IndicatorObj->insertData($fieldsArray);
        //-- TRANSACTION Log
        $LogId = $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _INDICATOR, $fieldsArray[_INDICATOR_INDICATOR_GID], _DONE);
        return $return;
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->IndicatorObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * insertOrUpdateBulkData method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->IndicatorObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->IndicatorObj->updateDataByParams($fieldsArray, $conditions);
    }
    
    
    

    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->IndicatorObj->testCasesFromTable($params);
    }


    /**
     * find method 
     *
     * @param string $type Query Type
     * @param array $options Extra options
     * @return void
     */
    public function find($type, $options = [], $extra = null) {
        $query = $this->IndicatorObj->find($type, $options);
        if (isset($extra['count'])) {
            $data = $query->count();
        } else {
            $results = $query->hydrate(false)->all();
            $data = $results->toArray();
        }
        return $data;
    }

}
