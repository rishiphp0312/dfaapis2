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

	
	
	
	
	function manageSubgroupData($subgroupValData){

		
		$dbId = $subgroupValData['dbId'];
		if($dbId == ''){
			return ['error' => _ERR106]; //db id is blank
		}
		$fieldsArray = [];
		$Data = (isset($subgroupValData['subgroupValData']))?$subgroupValData['subgroupValData']:'';
		$fieldsArray[_SUBGROUP_SUBGROUP_NAME]   = (isset($Data['dvName']))?$Data['dvName']:'';
		$fieldsArray[_SUBGROUP_SUBGROUP_GLOBAL] = '0';
		$fieldsArray[_SUBGROUP_SUBGROUP_TYPE]   = (isset($Data['dcNid']))?$Data['dcNid']:'';
		//$fieldsArray[_SUBGROUP_SUBGROUP_NID]    = $Data['dvNid'];
		$checkNameSg = $this->SubgroupType->checkNameSg($Data['dvName'],$Data['dcNid']);
		$result =0;
		if($checkNameSg==false){
			return ['error'=>_ERR150];  // sg name already exists 
		}
		if(isset($Data['dvNid']) && $Data['dvNid']=='')
		$result = $this->insertData($fieldsArray);
		if($result>0)
		$compArray = ['dcNid' =>$Data['dcNid'],'dvName'=>$Data['dvName'],'status'=>true];		
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
