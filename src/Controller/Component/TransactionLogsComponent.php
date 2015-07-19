<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
/**
 * TransactionLogs Component
 */
class TransactionLogsComponent extends Component {

    //Loading Components
    public $components = ['Auth'];
    public $TransactionLogsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->TransactionLogsObj = TableRegistry::get('MTransactionLogs');
    }

    /**
     * Creates record
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecord($fieldsArray) {
        if(!isset($fieldsArray[_MTRANSACTIONLOGS_USER_ID])):
            $fieldsArray[_MTRANSACTIONLOGS_USER_ID] = $this->Auth->user('id');
        endif;
        return $this->TransactionLogsObj->createRecord($fieldsArray);
    }

    /**
     * Update record
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecord($fieldsArray = [], $conditions = []) {
        return $this->TransactionLogsObj->updateRecord($fieldsArray, $conditions);
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecords($fields = [], $conditions = [], $type = 'all') {
        return $this->TransactionLogsObj->getRecords($fields, $conditions, $type);
    }

}
