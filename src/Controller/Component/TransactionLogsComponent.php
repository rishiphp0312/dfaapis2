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
        $this->session = $this->request->session();
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

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function createLog($action = null, $module = null, $subModule = null, $identifier = null, $status = null, $LogId = null) {
        
        $fieldsArray[_MTRANSACTIONLOGS_DB_ID] = $this->session->read('dbId');
        
        if($action !== null)
            $fieldsArray[_MTRANSACTIONLOGS_ACTION] = $action;
        if($module !== null)
            $fieldsArray[_MTRANSACTIONLOGS_MODULE] = $module;
        if($subModule !== null)
            $fieldsArray[_MTRANSACTIONLOGS_SUBMODULE] = $subModule;
        if($identifier !== null)
            $fieldsArray[_MTRANSACTIONLOGS_IDENTIFIER] = $identifier;
        if($status !== null)
            $fieldsArray[_MTRANSACTIONLOGS_STATUS] = $status;
        if(!empty($LogId))
            $fieldsArray[_MTRANSACTIONLOGS_ID] = $LogId;
        
        return $this->createRecord($fieldsArray);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = []) {
        return $this->TransactionLogsObj->deleteRecords($conditions);
    }

     /**
     * Delete records from Transaction log using conditions
     *
     * @param transaction log ID
     * @return string deleted records count
     */
    public function deleteTransactiondata($transactionID =NULL,$db_id = NULL) {
        $conditions = [];
        if(!empty($transactionID))
        $conditions[_MTRANSACTIONLOGS_ID] = $transactionID;
         if(!empty($db_id))
        $conditions[_MTRANSACTIONLOGS_DB_ID] = $db_id;

        if(empty($conditions)) return FALSE;
       // pr($conditions);exit;
        $result = $this->deleteRecords($conditions);

        if ($result > 0) {

            return true;
        } else {
            return false;
        }
    }

}
