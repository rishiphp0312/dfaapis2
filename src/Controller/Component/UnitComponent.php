<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Unit Component
 */
class UnitComponent extends Component
{
    
    // The other component your component uses
    public $components = [];
    public $UnitObj = NULL;

    public function beforeFilter()
    {
        $this->UnitObj = TableRegistry::get('Unit');
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
        return $this->UnitObj->getDataByIds($ids, $fields, $type);
    }


    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions)
    {
        return $this->UnitObj->getDataByParams($conditions, $fields);
    }


    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null)
    {
        return $this->UnitObj->deleteByIds($ids);
    }


    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = [])
    {
        return $this->UnitObj->deleteByParams($conditions);
    }


    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = [])
    {
        return $this->UnitObj->insertData($fieldsArray);
    }

    
    /**
     * insertOrUpdateBulkData method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($dataArray = [])
    {
        return $this->UnitObj->insertOrUpdateBulkData($dataArray);
    }


    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        return $this->UnitObj->updateDataByParams($fieldsArray, $conditions);
    }

}
