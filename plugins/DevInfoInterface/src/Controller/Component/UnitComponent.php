<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Unit Component
 */
class UnitComponent extends Component
{
    
    // The other component your component uses
    public $components = ['TransactionLogs'];
    public $UnitObj = NULL;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->UnitObj = TableRegistry::get('DevInfoInterface.Unit');
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
        return $this->UnitObj->getRecords($fields, $conditions, $type);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteByParams($conditions = [])
    {
        return $this->UnitObj->deleteByParams($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        $return = $this->UnitObj->insertData($fieldsArray);
        //-- TRANSACTION Log
        $LogId = $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _UNIT, $fieldsArray[_UNIT_UNIT_GID], _DONE);
        return $return;
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
        return $this->UnitObj->insertBulkData($insertDataArray, $insertDataKeys);
    }
    
    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        return $this->UnitObj->insertOrUpdateBulkData($dataArray);
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
        return $this->UnitObj->updateDataByParams($fieldsArray, $conditions);
    }

}
