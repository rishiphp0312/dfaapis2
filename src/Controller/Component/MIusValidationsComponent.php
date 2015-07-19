<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * MIusValidations Component
 */
class MIusValidationsComponent extends Component {

    //Loading Components
    //public $components = ['Auth'];
    public $MIusValidationsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->MIusValidationsObj = TableRegistry::get('MIusValidations');
    }

    /**
     * Creates record
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecord($fieldsArray) {
        if(!isset($fieldsArray[_MIUSVALIDATION_CREATEDBY])):
            $fieldsArray[_MIUSVALIDATION_CREATEDBY] = $this->Auth->user('id');
        endif;
        return $this->MIusValidationsObj->createRecord($fieldsArray);
    }

    /**
     * Update record
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecord($fieldsArray = [], $conditions = []) {
        if(!isset($fieldsArray[_MIUSVALIDATION_MODIFIEDBY])):
            $fieldsArray[_MIUSVALIDATION_MODIFIEDBY] = $this->Auth->user('id');
        endif;
        return $this->MIusValidationsObj->updateRecord($fieldsArray, $conditions);
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecords($fields = [], $conditions = [], $type = 'all', $extra = []) {
        return $this->MIusValidationsObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->MIusValidationsObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

}
