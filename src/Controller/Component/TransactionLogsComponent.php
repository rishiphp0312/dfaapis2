<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * TransactionLogs Component
 */
class TransactionLogsComponent extends Component {

    //Loading Components
    public $components = ['Auth', 'Common',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.CommonInterface', 'DevInfoInterface.Data',
        'DevInfoInterface.Metadatareport', 'DevInfoInterface.Metadata',
        'DevInfoInterface.IcIus',
        'UserAccess', 'MIusValidations',
        'DevInfoInterface.IndicatorClassifications', 'DevInfoInterface.Timeperiod',
        'DevInfoInterface.Footnote', 'DevInfoInterface.Area',
        'DevInfoInterface.Indicator', 'DevInfoInterface.Unit',
        'DevInfoInterface.SubgroupVals', 'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.SubgroupType', 'DevInfoInterface.Subgroup', 'UserCommon',
    ];
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
        if (!isset($fieldsArray[_MTRANSACTIONLOGS_USER_ID])):
            $fieldsArray[_MTRANSACTIONLOGS_USER_ID] = $this->Auth->user('id');
        endif;
        $logid =  $this->TransactionLogsObj->createRecord($fieldsArray);
        if(isset($fieldsArray[_MTRANSACTIONLOGS_SUBMODULE]) && ($fieldsArray[_MTRANSACTIONLOGS_SUBMODULE]==_SUB_MOD_DATA_ENTRY)){
            
        if($logid > 0){
             //  'all', _IMPORT, _DONE, $dbConnection
            $dbId = $this->session->read('dbId');
            $dbConnection = $this->Common->getDbConnectionDetails($dbId); 
            
            $this->requestToUpdateDbCounts(_SUB_MOD_DATA_ENTRY, $fieldsArray[_MTRANSACTIONLOGS_ACTION], _DONE, $dbConnection);
        }
        }
        return  $logid;
    }

    /**
     * Update record
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecord($fieldsArray = [], $conditions = []) {
        $logid = $this->TransactionLogsObj->updateRecord($fieldsArray, $conditions);    
          // Update counts
        if(isset($fieldsArray[_MTRANSACTIONLOGS_ACTION]) && $fieldsArray[_MTRANSACTIONLOGS_ACTION]==_IMPORT && isset($fieldsArray[_MTRANSACTIONLOGS_STATUS]) && $fieldsArray[_MTRANSACTIONLOGS_STATUS]==_DONE){
            
         if($conditions[_MTRANSACTIONLOGS_ID] >0){
              
              $dbId = $this->session->read('dbId');
              $dbConnection = $this->Common->getDbConnectionDetails($dbId); 
              $this->requestToUpdateDbCounts('all', _IMPORT, _DONE, $dbConnection);

         }

        }
      return $logid;
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecords($fields = [], $conditions = [], $type = 'all',$extra=[]) {
        return $this->TransactionLogsObj->getRecords($fields, $conditions, $type,$extra);
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function createLog($action = null, $module = null, $subModule = null, $identifier = null, $status = null, $LogId = null, $dbId = null, $prevValue = null, $newValue = null, $desc = null) {

        if (!empty($dbId))
            $fieldsArray[_MTRANSACTIONLOGS_DB_ID] = $dbId;
        else
            $fieldsArray[_MTRANSACTIONLOGS_DB_ID] = $this->session->read('dbId');

        if (!empty($action))
            $fieldsArray[_MTRANSACTIONLOGS_ACTION] = $action;
        if (!empty($module))
            $fieldsArray[_MTRANSACTIONLOGS_MODULE] = $module;
        if (!empty($subModule))
            $fieldsArray[_MTRANSACTIONLOGS_SUBMODULE] = $subModule;
        if (!empty($identifier))
            $fieldsArray[_MTRANSACTIONLOGS_IDENTIFIER] = $identifier;
        if (!empty($status))
            $fieldsArray[_MTRANSACTIONLOGS_STATUS] = $status;
        if (!empty($prevValue))
            $fieldsArray[_MTRANSACTIONLOGS_PREVIOUSVALUE] = $prevValue;
        if (!empty($newValue))
            $fieldsArray[_MTRANSACTIONLOGS_NEWVALUE] = $newValue;
        if (!empty($desc))
            $fieldsArray[_MTRANSACTIONLOGS_DESCRIPTION] = $desc;
        if (!empty($LogId))
            $fieldsArray[_MTRANSACTIONLOGS_ID] = $LogId;

        $logid =  $this->createRecord($fieldsArray);
        
        // Update counts
        if($logid > 0){
            $dbId = $this->session->read('dbId');
            $dbConnection = $this->Common->getDbConnectionDetails($dbId); 
            $this->requestToUpdateDbCounts($subModule, $action, $status, $dbConnection);
        }
        
        return $logid;

    }

    /*
    function to requesting to update database counts such as dataCount, Area Count, IUSCount etc.
    */
   public function requestToUpdateDbCounts($case = 'all', $action, $status, $dbConnection) {
        if (!empty($dbConnection)) {
            if ($status == _DONE) {
                if ($action == _IMPORT)
                    $case = 'all';

                if ($action != _EXPORT)
                    $returndData = $this->CommonInterface->serviceInterface('Metadata', 'updateMetadataCount', ['case' => $case]);
            }
        }
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
    public function deleteTransactiondata($transactionID = NULL, $db_id = NULL) {
        $conditions = [];
        if (!empty($transactionID))
            $conditions[_MTRANSACTIONLOGS_ID] = $transactionID;
        if (!empty($db_id))
            $conditions[_MTRANSACTIONLOGS_DB_ID] = $db_id;

        if (empty($conditions))
            return FALSE;
        // pr($conditions);exit;
        $result = $this->deleteRecords($conditions);

        if ($result > 0) {

            return true;
        } else {
            return false;
        }
    }

    

    /* Prepare conditon for transaction filter
     *
     */

    public function prepareFilterCondtions($dbId) {
        //Filters
        $formDate = $this->request->data('fromDate');
        $toDate = $this->request->data('toDate');
        $userId = $this->request->data('userId');
        $module = $this->request->data('txnModule');
        $action = $this->request->data('action');
        $status = $this->request->data('status');
        $previousvalue = $this->request->data('previousValue');
        $newvalue = $this->request->data('newValue');
        $submodule = $this->request->data('txnSubmodule');

        $conditions = array();

        $conditions[_MTRANSACTIONLOGS_DB_ID] = $dbId;
        if ($userId != '')
            $conditions[_MTRANSACTIONLOGS_USER_ID] = $userId;
        if ($module != '')
            $conditions[_MTRANSACTIONLOGS_MODULE] = $module;
        if ($action != '')
            $conditions[_MTRANSACTIONLOGS_ACTION] = $action;
        if ($status != '')
            $conditions[_MTRANSACTIONLOGS_STATUS] = $status;
        if ($previousvalue != '')
            $conditions[_MTRANSACTIONLOGS_PREVIOUSVALUE] = $previousvalue;
        if ($newvalue != '')
            $conditions[_MTRANSACTIONLOGS_NEWVALUE] = $newvalue;
          if ($submodule != '')
            $conditions[_MTRANSACTIONLOGS_SUBMODULE] = $submodule;

        if (!empty($formDate) || !empty($toDate)) {

            if (!empty($formDate)) {
                $conditions[_MTRANSACTIONLOGS_CREATED . ' >= '] = $formDate;
            }
            if (!empty($toDate)) {

                $conditions[_MTRANSACTIONLOGS_CREATED . ' <= '] = $toDate;
            }
        } else {
            //curr week data

            $wk_start = date('Y-m-d', strtotime('Monday This week'));
            $wk_end = date('Y-m-d', strtotime('Sunday This week'));
            $conditions[_MTRANSACTIONLOGS_CREATED . ' >= '] = $wk_start;
            $conditions[_MTRANSACTIONLOGS_CREATED . ' <= '] = $wk_end;
        }
        return $conditions;
    }

    public function prepareTransactionLogIdentifierPath($action, $submodule, $identifier) {
        $identifierPath = '';
        if (strtolower($action) == 'import') {
            if (strtolower($submodule) == 'area') {
                $identifierPath = _WEBSITE_URL . _LOGS_PATH_WEBROOT . DS . $identifier;
            }
            if (strtolower($submodule) == 'icius') {
                $identifierPath = _WEBSITE_URL . _LOGS_PATH_WEBROOT . DS . $identifier;
            }
            if (strtolower($submodule) == 'des') {
                $identifierPath = _WEBSITE_URL . _DES_PATH_WEBROOT . DS . $identifier;
            }
        }
        if (strtolower($action) == 'export') {
            if (strtolower($submodule) == 'unit') {

                $identifierPath = _WEBSITE_URL . _UNIT_PATH_WEBROOT . DS . $identifier;
            }
            if (strtolower($submodule) == 'indicator') {
                $identifierPath = _WEBSITE_URL . _INDICATOR_PATH_WEBROOT . DS . $identifier;
            }
            if (strtolower($submodule) == 'subgroup') {
                $identifierPath = _WEBSITE_URL . _SUBGROUPVAL_PATH_WEBROOT . DS . $identifier;
            }

            if (strtolower($submodule) == 'des') {
                $identifierPath = _WEBSITE_URL . _DES_PATH_WEBROOT . DS . $identifier;
            }
            
            if (strtolower($submodule) == 'area') {
                $identifierPath = _WEBSITE_URL . _AREA_PATH_WEBROOT . DS . $identifier;
            }
            if (strtolower($submodule) == 'icius') {
                $identifierPath = _WEBSITE_URL . _ICIUS_PATH_WEBROOT . DS . $identifier;
            }
        }
        return $identifierPath;
    }

    /* Get transaction log data
     *
     */

    public function getTransactionLogsData($fields = [], $conditions = [], $type = 'all', $extra = []) {
        $results = $this->TransactionLogsObj->getRecords($fields, $conditions, $type, $extra);
        if (!empty($results) && is_array($results)) {
            foreach ($results as &$row) {

                if (!empty($row['identifier']) && strpos($row['identifier'], ".") !== false) {

                    $identifierPath = $this->prepareTransactionLogIdentifierPath($row['action'], $row['submodule'], $row['identifier']);
                    if (!empty($identifierPath)) {
                        $row['identifierUrlPath'] = $identifierPath;
                    }
                }
                $txn_created = $row['userId'];
                $row['created'] = strtotime($row['created']);
                $rowUserId = $row['userId'];
                $userRow = $this->UserCommon->getUserDetails([_USER_NAME], [_USER_ID => $rowUserId]);
                if (!empty($userRow))
                    $row['userName'] = $userRow['0'][_USER_NAME];
            }
        }
        return $results;
    }

    /*
    function to get database updated destails 
    */
    public function getDbUpdatedDetails($dbId) {

        $returnData = [];

        $fields=[_MTRANSACTIONLOGS_USER_ID,_MTRANSACTIONLOGS_CREATED] ;
        $conditions=[_MTRANSACTIONLOGS_DB_ID=>$dbId,_MTRANSACTIONLOGS_ACTION.' !='=>_EXPORT];
        $extra['order'] = [_MTRANSACTIONLOGS_ID=>'DESC'];
        $extra['first'] = true;
        $details =  $this->getRecords($fields, $conditions,'all', $extra);
        if($details) {            
            $userData =  $this->UserCommon->getUserDetailsById($details[_MTRANSACTIONLOGS_USER_ID]); 
            
            $returnData['updatedOn'] = $details[_MTRANSACTIONLOGS_CREATED];
            $returnData['updatedById'] = $details[_MTRANSACTIONLOGS_USER_ID];
            $returnData['updatedByName'] = $userData['name'];
        }

        return $returnData;        
    }

}
