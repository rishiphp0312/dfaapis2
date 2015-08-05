<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * SubgroupValsSubgroup Component
 */
class SubgroupValsSubgroupComponent extends Component
{
    
    // The other component your component uses
    public $components = ['DevInfoInterface.CommonInterface'];
    public $SubgroupValsSubgroupObj = NULL;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->SubgroupValsSubgroupObj = TableRegistry::get('DevInfoInterface.SubgroupValsSubgroup');
    }

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @param array $extra any extra param
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = [])
    {
        // MSSQL Compatibilty - MSSQL can't support more than 2100 params - 900 to be safe
        $chunkSize = 900;

        if (isset($conditions['OR']) && count($conditions['OR'], true) > $chunkSize) {

            $result = [];
            $countIncludingChildparams = count($conditions['OR'], true);

            // count for single index
            //$orSingleParamCount = count(reset($conditions['OR']));
            
            //$splitChunkSize = floor(count($conditions['OR']) / $orSingleParamCount);
            $splitChunkSize = floor(count($conditions['OR']) / ($countIncludingChildparams / $chunkSize));

            // MSSQL Compatibilty - MSSQL can't support more than 2100 params
            $orConditionsChunked = array_chunk($conditions['OR'], $splitChunkSize);

            foreach ($orConditionsChunked as $orCond) {
                $conditions['OR'] = $orCond;
                $subgroupValsSubgroup = $this->SubgroupValsSubgroupObj->getRecords($fields, $conditions, $type, $extra);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if ($type == 'list') {
                    $result = array_replace($result, $subgroupValsSubgroup);
                }// we dont need to preserve keys, just merge
                else {
                    $result = array_merge($result, $subgroupValsSubgroup);
                }
            }
        } else {
            $result = $this->SubgroupValsSubgroupObj->getRecords($fields, $conditions, $type, $extra);
        }
        return $result;
        
        //return $this->SubgroupValsSubgroupObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = [])
    {
        return $this->SubgroupValsSubgroupObj->deleteRecords($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        return $this->SubgroupValsSubgroupObj->insertData($fieldsArray);
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        return $this->SubgroupValsSubgroupObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = [])
    {
        return $this->SubgroupValsSubgroupObj->updateRecords($fieldsArray, $conditions);
    }
    
    /**
     * get concatinated fields 
     * 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedFields(array $fields, array $conditions, $type = null)
    {
        if($type == 'list'){
            $result = $this->SubgroupValsSubgroupObj->getConcatedFields($fields, $conditions, 'all');
            if(!empty($result)){
                return array_column($result, 'concatinated', _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID);
            }else{
                return [];
            }
        }else{
            return $this->SubgroupValsSubgroupObj->getConcatedFields($fields, $conditions, $type);
        }
    }

    /**
     * bulkInsert method
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type
     * @return void
     */
    public function bulkInsert(array $pairs, array $pairsArray) {
        
        $concatedFields = [];
        $pairsArray = array_intersect_key($pairsArray, $pairs); // unique pairsArray
        
        // Split Big array into chunks (MSSQL Can't handle more than 2100 params)
        $chunkSize = 900;
        $pairsArrayChunked = array_chunk($pairsArray, $chunkSize);
        $pairsChunked = array_chunk($pairs, $chunkSize);
        
        //Unset unwanted arrays
        unset($pairs); unset($pairsArray);
        
        foreach($pairsArrayChunked as $key => $pairsArray){
        
            //Check if records exists for subgroup_vals
            $fields = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID, SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID, _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID];
            $conditions = ['OR' => $pairsArray];
            $getSubGroupValsSubgroupNids = $this->getConcatedFields($fields, $conditions, 'list');

            $alreadyExistingRec = array_intersect($getSubGroupValsSubgroupNids, $pairsChunked[$key]);
            $newRec = array_diff($pairsChunked[$key], $getSubGroupValsSubgroupNids);

            $pairsArray = array_intersect_key($pairsArray, $newRec);
            
            if(!empty($pairsArray)){
                $insertDataKeys = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID, SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
                $insertDataArray = $pairsArray;
                $this->insertOrUpdateBulkData($insertDataArray, $insertDataKeys);
            }

            $concatedFields = array_replace($concatedFields, $this->getConcatedFields($fields, $conditions, 'list'));
        }

        return $concatedFields;
        //return $this->getConcatedFields($fields, $conditions, 'list');        
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
        return $this->SubgroupValsSubgroupObj->getMax($column, $conditions);
    }

    /**
     * - For DEVELOPMENT purpose only
     * Test method to do anything based on this model (Run RAW queries or complex queries)
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        return $this->SubgroupValsSubgroupObj->testCasesFromTable($params);
    }
    
}
