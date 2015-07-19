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
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all' )
    {
        return $this->SubgroupTypeObj->getDataByIds($ids, $fields, $type);
    }


    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all')
    {
        return $this->SubgroupTypeObj->getDataByParams($fields, $conditions, $type);
    }


    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null)
    {
        return $this->SubgroupTypeObj->deleteByIds($ids);
    }


    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = [])
    {
        return $this->SubgroupTypeObj->deleteByParams($conditions);
    }


    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = [])
    {
        return $this->SubgroupTypeObj->insertData($fieldsArray);
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
        return $this->SubgroupTypeObj->insertBulkData($insertDataArray, $insertDataKeys);
    }
    

    /**
     * insertOrUpdateBulkData method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        return $this->SubgroupTypeObj->insertOrUpdateBulkData($dataArray);
    }


    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        return $this->SubgroupTypeObj->updateDataByParams($fieldsArray, $conditions);
    }


    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        return $this->SubgroupTypeObj->testCasesFromTable($params);
    }

}
