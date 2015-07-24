<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Subgroup Component
 */
class SubgroupComponent extends Component {

    public $SubgroupObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->SubgroupObj = TableRegistry::get('DevInfoInterface.Subgroup');
    }

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all', $debug = false) {
        return $this->SubgroupObj->getRecords($fields, $conditions, $type, $debug);
    }

    /**
     * deleteByParams  method for Subgroup
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {
        return $this->SubgroupObj->deleteRecords($conditions);
    }

    /**
     * insertDataSubgroup method is used to add new subgroup  *
     * @param fieldsArray is passed as posted data  
     * @return void
     */
    public function insertData($fieldsArray) {
        return $this->SubgroupObj->insertData($fieldsArray);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        //return $this->SubgroupObj->insertBulkData($insertDataArray, $insertDataKeys);
        return $this->SubgroupObj->insertOrUpdateBulkData($insertDataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->SubgroupObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = []) {
        return $this->SubgroupObj->getMax($column, $conditions);
    }

}
