<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * UserAccess Component
 */
class UserAccessComponent extends Component {

    //Loading Components
    public $components = ['Auth'];
    public $RAccessAreasObj = NULL;
    public $RAccessIndicatorsObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->RAccessAreasObj = TableRegistry::get('RAccessAreas');
        $this->RAccessIndicatorsObj = TableRegistry::get('RAccessIndicators');
    }

    /**
     * Creates record - Area_Access
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecordAreaAccess($fieldsArray) {
        return $this->RAccessAreasObj->createRecord($fieldsArray);
    }

    /**
     * Update record - Area_Access
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecordAreaAccess($fieldsArray = [], $conditions = []) {
        return $this->RAccessAreasObj->updateRecord($fieldsArray, $conditions);
    }

    /**
     * Get Records - Area_Access
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecordsAreaAccess($fields = [], $conditions = [], $type = 'all') {
        return $this->RAccessAreasObj->getRecords($fields, $conditions, $type);
    }

    /**
     * Get Area Access for a User - Area_Access
     *
     * @param array $extra Extra Params {DEFAULT : empty}
     * @return void
     */
    public function getAreaAccessToUser($extra = ['type' => 'all']) {
        $returnData = [];
        extract($extra);
		
        if ($this->session->check('userAccess')) {
            $userAccess = $this->session->read('userAccess');
			//if(isset($extra['getidsRUDR']) && !empty($extra['getidsRUDR']))
			//$userDbRoleId = $extra['getidsRUDR'];        
			//else
			$userDbRoleId = $userAccess['userDbRoleId'];
            if ($userAccess['areaAccess'] == 1) {
                
                if(!isset($fields)){
                    $fields = [ 'id' => _RACCESSAREAS_AREA_ID, 'name' => _RACCESSAREAS_AREA_NAME]; //Blank is all
                }
                if ($type == 'list') {
                    $fields = array_values($fields); // we need 0,1 as keys
                }
				
                $conditions = [_RACCESSAREAS_USER_DATABASE_ROLE_ID .' IN '=> $userDbRoleId];
                $returnData = $this->getRecordsAreaAccess($fields, $conditions, $type);
            }
        }
        return $returnData;
    }
	
    /*
     * deleteUserAreaAccess to delete the Areas assigned to user 
     * $rudIds can be array  RUDR table ids 
     * $rudrIds can be array RUD table ids 
	 * $type can be IN or NOT IN for role ids default is IN 
     */
    public function deleteUserAreaAccess($rudIds = [], $rudrIds = [],$type) {
        return $deleteAreas = $this->RAccessAreasObj->deleteUserAreas($rudIds, $rudrIds,$type); //delete db		
    }
	
	/*
	getAssignedAreas to get the Areas assigned to specific user on specific db 
	@rudrId is the user db role id 
	*/
	public function getAssignedAreas($rudrId) {
        $returnData = [];
        $data = $this->RAccessAreasObj->getAssignedAreas($rudrId); 	

        foreach($data as $gid=>$name) {
            $returnData[] = array('id'=>$gid, 'name'=>$name);    
        }

        return $returnData;
    }
	
    /**
     * Creates record - Indicator_Access
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecordIndicatorAccess($fieldsArray) {
        return $this->RAccessIndicatorsObj->createRecord($fieldsArray);
    }

    /**
     * Update record - Indicator_Access
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecordIndicatorAccess($fieldsArray = [], $conditions = []) {
        return $this->RAccessIndicatorsObj->updateRecord($fieldsArray, $conditions);
    }

    /**
     * Get Records - Indicator_Access
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecordsIndicatorAccess($fields = [], $conditions = [], $type = 'all') {
        return $this->RAccessIndicatorsObj->getRecords($fields, $conditions, $type);
    }
	
    /**
     * Get Indicator Access for a User - Indicator_Access
     *
     * @param array $extra Extra Params {DEFAULT : empty}
     * @return void
     */
    public function getIndicatorAccessToUser($extra = ['type' => 'all']) {
        $returnData = false;
        extract($extra);
        if ($this->session->check('userAccess')) {
            $userAccess = $this->session->read('userAccess');			
			//if(isset($extra['getidsRUDR']) && !empty($extra['getidsRUDR']))
			//$userDbRoleId = $extra['getidsRUDR'];        
			//else
			$userDbRoleId = $userAccess['userDbRoleId'];
		
            if ($userAccess['indicatorAccess'] == 1) {
               
                if(!isset($fields)){
                    $fields = [ 'id' => _RACCESSINDICATOR_INDICATOR_GID, 'name' => _RACCESSINDICATOR_INDICATOR_NAME]; //Blank is all
                }
                if ($type == 'list') {
                    $fields = array_values($fields); // we need 0,1 as keys
                }
                $conditions = [_RACCESSINDICATOR_USER_DATABASE_ROLE_ID .' IN '=> $userDbRoleId];
                $returnData = $this->getRecordsIndicatorAccess($fields, $conditions, $type);
            }
        }
        return $returnData;
    }
	
    /*
     * deleteUserIndicatorAccess to delete the indicators assigned to user 
     * $userId can be array multiple user ids 
     * $dbId is database id 
     */
    public function deleteUserIndicatorAccess($RUD_ids = [], $RUDR_ids = []) {
        return $deleteIndicators = $this->RAccessIndicatorsObj->deleteUserIndicators($RUD_ids, $RUDR_ids); //delete db		
    }
	
	/*
	getAssignedIndicators to get the Indicators assigned to specific user on specific db 
	@rudrId is the user db role id 
	*/	
	public function getAssignedIndicators($rudrId) {
        $returnData = [];
        $data = $this->RAccessIndicatorsObj->getAssignedIndicators($rudrId); 	

        foreach($data as $gid=>$name) {
            $returnData[] = array('id'=>$gid, 'name'=>$name);    
        }

        return $returnData;
    }

}
