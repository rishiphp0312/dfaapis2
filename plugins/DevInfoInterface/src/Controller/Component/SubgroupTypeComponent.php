<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * SubgroupType Component
 */
class SubgroupTypeComponent extends Component
{
    
    // The other component your component uses
    public $components = [];
    public $SubgroupTypeObj = NULL;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->SubgroupTypeObj = TableRegistry::get('DevInfoInterface.SubgroupType');
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
        return $this->SubgroupTypeObj->getRecords($fields, $conditions, $type);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteByParams($conditions = [])
    {
        return $this->SubgroupTypeObj->deleteRecords($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        return $this->SubgroupTypeObj->insertData($fieldsArray);
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
        return $this->SubgroupTypeObj->insertBulkData($insertDataArray, $insertDataKeys);
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
        return $this->SubgroupTypeObj->updateRecords($fieldsArray, $conditions);
    }

}
