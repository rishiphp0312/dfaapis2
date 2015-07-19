<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IcIus Component
 */
class IcIusComponent extends Component {

    // The other component your component uses
    public $components = [];
    public $IcIusObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->IcIusObj = TableRegistry::get('DevInfoInterface.IcIus');
    }

    /**
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all') {
        return $this->IcIusObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        return $this->IcIusObj->getDataByParams($fields, $conditions, $type);
    }

    /**
     * getGroupedList method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getGroupedList(array $fields, array $conditions) {
        return $this->IcIusObj->getGroupedList($fields, $conditions);
    }

    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        return $this->IcIusObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {
        return $this->IcIusObj->deleteByParams($conditions);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        return $this->IcIusObj->insertData($fieldsArray);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->IcIusObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * bulkInsert method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function bulkInsert($dataArray = []) {
        return $this->IcIusObj->bulkInsert($dataArray);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->IcIusObj->updateDataByParams($fieldsArray, $conditions);
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
            $result = $this->IcIusObj->getConcatedFields($fields, $conditions, 'all');
            if(!empty($result)){
                return array_column($result, 'concatinated', $fields[2]);
            }else{
                return [];
            }
        }else{
            return $this->IcIusObj->getConcatedFields($fields, $conditions, $type);
        }
    }
    
    /**
     * getConcatedIus method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedIus(array $fields, array $conditions, $type = null)
    {
        $result = $this->IcIusObj->getConcatedIus($fields, $conditions, 'all');
        if($type == 'list'){            
            if(!empty($result)){
                $result = array_column($result, 'concatinated', _IUS_IUSNID);
            }
        }
        
        return $result;
    }

    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->IcIusObj->testCasesFromTable($params);
    }

}
