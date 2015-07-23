<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
/**
 * ApplicationLogs Component
 */
class ApplicationLogsComponent extends Component {

    //Loading Components
    public $components = ['Auth'];
    public $TransactionLogsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->ApplicationLogsObj = TableRegistry::get('MApplicationLogs');
    }

    /**
     * Creates record
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecord($fieldsArray) {
        if(!isset($fieldsArray[_MAPPLICATIONLOG_CREATEDBY])):
            $fieldsArray[_MAPPLICATIONLOG_CREATEDBY] = $this->Auth->user('id');
        endif;
        return $this->ApplicationLogsObj->createRecord($fieldsArray);
    }
	
    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function createLog($action = null, $module = null, $subModule = null, $identifier = null, $status = null) {
        
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
        
        return $this->createRecord($fieldsArray);
    }
	
	

  

}
