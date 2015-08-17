<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Subgroup Component
 */
class SubgroupComponent extends Component {

    public $SubgroupObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->SubgroupObj = TableRegistry::get('DevInfoInterface.Subgroup');
    }
	public $components = [
        'Auth',
        'UserAccess',
       
        'TransactionLogs',
        'DevInfoInterface.CommonInterface',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.SubgroupType',
         'Common'
    ];

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $debug = false) {
        return $this->SubgroupObj->getRecords($fields, $conditions, $type, $debug);
    }

    /**
     * Delete Records
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        return $this->SubgroupObj->deleteRecords($conditions);
    }

    /**
     * Insert Single record
     * 
     * @param fieldsArray is passed as posted data  
     * @return void
     */
    public function insertData($fieldsArray) {
        return $this->SubgroupObj->insertData($fieldsArray);
    }

    /**
     * Insert or Update bulk/multiple Records
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($insertDataArray = []) {
        return $this->SubgroupObj->insertOrUpdateBulkData($insertDataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->SubgroupObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = []) {
        return $this->SubgroupObj->getMax($column, $conditions);
    }

	
	/*
	
	manage add  sub group details 
	@subgroupValData subgroup details 
	*/	
	function manageSubgroupData($subgroupValData){

		
		$dbId = $subgroupValData['dbId'];
		if($dbId == ''){
			return ['error' => _ERR106]; //db id is blank
		}
		$fieldsArray = [];
		$Data = (isset($subgroupValData['subgroupValData']))?$subgroupValData['subgroupValData']:'';
		$fieldsArray[_SUBGROUP_SUBGROUP_NAME]   = (isset($Data['dvName'])  && !empty($Data['dvName']))?trim($Data['dvName']):'';
		$fieldsArray[_SUBGROUP_SUBGROUP_GLOBAL] = '0';
		$fieldsArray[_SUBGROUP_SUBGROUP_TYPE]   = (isset($Data['dcNid'])  && !empty($Data['dcNid']))?$Data['dcNid']:'';
		$gid   = (isset($Data['dvGid']) && !empty($Data['dvGid']))?trim($Data['dvGid']):$this->CommonInterface->guid();
		//$fieldsArray[_SUBGROUP_SUBGROUP_NID]    = $Data['dvNid'];
		if(empty($Data['dvName'])){
			   return ['error' => _ERR147]; 		//sg   empty
		}
		
		if(isset($Data['dvGid']) && !empty($Data['dvGid'])){
			$validgidlength = $this->CommonInterface->checkBoundaryLength($gid,_GID_LENGTH);
			if($validgidlength == false){
					return ['error' => _ERR166];  // gid length 
			}
			$sgGid = $this->SubgroupType->checkGidSg(trim($Data['dvGid']) ,'');
			if($sgGid ==false){
					return ['error' => _ERR137];	//gid already exists
			}
		}
		
		$fieldsArray[_SUBGROUP_SUBGROUP_GID]   = $gid ;
		$checkNameSg = $this->SubgroupType->checkNameSg($Data['dvName'],'');
		$result =0;
		
		
		if($checkNameSg==false){
			return ['error'=>_ERR150];  // sg name already exists 
		}
		//if(isset($Data['dvNid']) && $Data['dvNid']=='')
		//pr($fieldsArray);die;
		$result = $this->insertData($fieldsArray);
		//$dimVal=['dvName'=>$Data['dvName'],'dvNid'=>$result];
		
		if($result>0)
		return $compArray = ['dcNid' =>$Data['dcNid'],'dv'=>$Data['dvName'],'dvNid'=>$result,'status'=>true];	
		else
		return ['error' => _ERR100]; //server error 
	}
    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->SubgroupObj->testCasesFromTable($params);
    }
}
