<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * SubgroupVals Component
 */
class SubgroupValsComponent extends Component
{
    
    // The other component your component uses
    public $components = [];
    public $SubgroupValsObj = NULL;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->SubgroupValsObj = TableRegistry::get('DevInfoInterface.SubgroupVals');
    }

    /**
     * Get records based on conditions
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return array fetched records
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all')
    {
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
                $getIndicator = $this->SubgroupValsObj->getRecords($fields, $conditions, $type);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if($type == 'list'){
                    $result = array_replace($result, $getIndicator);
                }// we dont need to preserve keys, just merge
                else{
                    $result = array_merge($result, $getIndicator);
                }
            }
        }else{
            $result = $this->SubgroupValsObj->getRecords($fields, $conditions, $type);
        }
        return $result;
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteByParams($conditions = [])
    {
        return $this->SubgroupValsObj->deleteRecords($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        return $this->SubgroupValsObj->insertData($fieldsArray);
    }

    /**
     * Insert multiple rows at once (runs single query for multiple records)
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = [])
    {
        return $this->SubgroupValsObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        return $this->SubgroupValsObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = [])
    {
        //print_r(get_class_methods($this->SubgroupValsObj));exit;
        return $this->SubgroupValsObj->getMax($column, $conditions);
    }
}
