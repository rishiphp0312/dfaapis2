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
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all' )
    {
        return $this->SubgroupValsSubgroupObj->getDataByIds($ids, $fields, $type);
    }


    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all', $extra = [])
    {
        return $this->SubgroupValsSubgroupObj->getDataByParams($fields, $conditions, $type, $extra);
    }


    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null)
    {
        return $this->SubgroupValsSubgroupObj->deleteByIds($ids);
    }


    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = [])
    {
        return $this->SubgroupValsSubgroupObj->deleteByParams($conditions);
    }


    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = [])
    {
        return $this->SubgroupValsSubgroupObj->insertData($fieldsArray);
    }
    
    
    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = [])
    {
        return $this->SubgroupValsSubgroupObj->insertBulkData($insertDataArray, $insertDataKeys);
    }
    

    /**
     * insertOrUpdateBulkData method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        return $this->SubgroupValsSubgroupObj->insertOrUpdateBulkData($dataArray);
    }


    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        return $this->SubgroupValsSubgroupObj->updateDataByParams($fieldsArray, $conditions);
    }


    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        return $this->SubgroupValsSubgroupObj->testCasesFromTable($params);
    }
    
    /**
     * getMax method
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return void
     */
    public function getMax($column = '', $conditions = [])
    {
        return $this->SubgroupValsSubgroupObj->getMax($column, $conditions);
    }
    
    /**
     * getConcatedFields method     *
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
            //$conditions = ['(' . _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID . ',' . SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ') IN (' . implode(',', $pairs) . ')'];
            $conditions = ['OR' => $pairsArray];
            $getSubGroupValsSubgroupNids = $this->getConcatedFields($fields, $conditions, 'list');

            $alreadyExistingRec = array_intersect($getSubGroupValsSubgroupNids, $pairsChunked[$key]);
            $newRec = array_diff($pairsChunked[$key], $getSubGroupValsSubgroupNids);

            $pairsArray = array_intersect_key($pairsArray, $newRec);
            
            if(!empty($pairsArray)){
                $insertDataKeys = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID, SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
                $insertDataArray = $pairsArray;
                $this->insertBulkData($insertDataArray, $insertDataKeys);
            }

            $concatedFields = array_replace($concatedFields, $this->getConcatedFields($fields, $conditions, 'list'));
        }

        return $concatedFields;
        //return $this->getConcatedFields($fields, $conditions, 'list');        
    }
}
