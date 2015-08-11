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
    public $SubgroupTypeObj = NULL;
	public $components = [
        'Auth',
        'UserAccess',
        'MIusValidations',
        'TransactionLogs',
        'DevInfoInterface.CommonInterface',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.IcIus',       
        'DevInfoInterface.IndicatorUnitSubgroup',      
        'DevInfoInterface.Indicator',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.Data',
        'DevInfoInterface.IcIus',
        'Common'
    ];

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->SubgroupTypeObj = TableRegistry::get('DevInfoInterface.SubgroupType');
    }

    /**
     * Get records based on conditions
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all')
    {
        return $this->SubgroupTypeObj->getRecords($fields, $conditions, $type);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = [])
    {
        return $this->SubgroupTypeObj->deleteRecords($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
       // return $this->SubgroupTypeObj->insertData($fieldsArray);
        return $this->SubgroupTypeObj->insertData($fieldsArray);
    }
	
	/**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
	 public function insertDatanew($fieldsArray = [])
    {
       // return $this->SubgroupTypeObj->insertData($fieldsArray);
        return $this->SubgroupTypeObj->insertDatanew($fieldsArray);
    }

    /**
     * Insert multiple rows at once (runs single query for multiple records)
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($insertDataArray = [])
    {
        return $this->SubgroupTypeObj->insertOrUpdateBulkData($insertDataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = [])
    {
        return $this->SubgroupTypeObj->updateRecords($fieldsArray, $conditions);
    }
	
	/*
	 method to get the subgroup type  list  
	 @sgIds is array of sub group nids 
	 
	*/
	function getsgTypeNids($typeNid=''){
		$fields = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NID];
		$conditions = [_SUBGROUP_SUBGROUP_TYPE .' IN '=>$typeNid];
		$resultSgTypeIds	=	$this->Subgroup->getRecords($fields,$conditions,'list');
		return $resultSgTypeIds;
		
	}
	
	
	
	
	/*
	 method to get the subgroup nids list 
	  @sgValNids='' is array  of sub val nids 
	 
	*/
	function getsgValNids($sgIds=[]){
		
		$fields = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID,_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID];
		$conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID .' IN '=>$sgIds];
		$sgValNids	=	$this->SubgroupValsSubgroup->getRecords($fields,$conditions,'list');	
		
		return $sgValNids;
		
			
	}
	
	
	public function deleteSubgroupdata($sgId=''){
		
		if($sgId){
			// $sgIds= $this->getsgTypeNids($nId);
			// get subgroup vals subgroups  records
			$sgvalsgIds = $this->getsgValNids($sgId);
			//pr($sgIds); // delete them 			
			pr($sgvalsgIds);// delete them 
					
			$conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_SUBGROUP_VAL_NID.' IN '=>$sgvalsgIds];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');
			pr($getIusNids);
			//delete them  from sg 			
			$conditions = [];
            $conditions = [_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgId];
            $sgId = $this->Subgroup->deleteRecords($conditions);
			
			if($sgId>0){
				
					echo 'Subgroup deleted ';
			pr($sgId);
			
			//delete them  from sg val
			echo 'Subgroup VALS deleted ';
			pr($sgvalsgIds);
			$conditions = [];
            $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $data = $this->SubgroupVals->deleteRecords($conditions);
			
			//delete them  from sg val sg val 
			echo 'SubgroupValsSubgroup deleted ';
			pr($sgId);
			$conditions = [];
            $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgId];//CHECK AGAIN THIS 
            $data = $this->SubgroupValsSubgroup->deleteRecords($conditions);
			
			 //deleet ius     
			echo 'IndicatorUnitSubgroup deleted ';
			pr($sgvalsgIds);			 
            $conditions = [];
            $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);
			
			 //deleet ius   
			echo 'Data deleted ';
			pr($sgvalsgIds);				 
            $conditions = [];
            $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalsgIds];
            $data = $this->Data->deleteRecords($conditions);
			if (count($getIusNids) > 0) {
                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
                echo 'IcIus deleted ';
			pr($getIusNids);	
				$data = $this->IcIus->deleteRecords($conditions);
            }
			return true;
		}else{
			return false;
		}
		            
        }else{
			return false;
		}
	}
	
	public function deleteSubgroupTypedata($nId=''){
		if($nId){
			// delete data 
			
			$sgIds= $this->getsgTypeNids($nId);
			// get subgroup vals subgroups  records 
			
			$sgvalsgIds= $this->getsgValNids($sgIds);
			pr($sgIds); // delete them 			
			pr($sgvalsgIds);// delete them 
					
			$conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_SUBGROUP_VAL_NID.' IN '=>$sgvalsgIds];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');
			pr($getIusNids);
			//delete them  from sg type
			
			$conditions = [];
            $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN ' => $nId];
            $sgtype = $this->deleteRecords($conditions);
			echo 'sgtype deleted ';
			pr($sgtype);
			if($sgtype>0){
				
					echo 'Subgroup deleted ';
			pr($sgIds);
			//delete them  from sg 			
			$conditions = [];
            $conditions = [_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
            $data = $this->Subgroup->deleteRecords($conditions);
			pr($data);
			//delete them  from sg val
			echo 'SubgroupVals deleted ';
			pr($sgvalsgIds);
			$conditions = [];
            $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $data = $this->SubgroupVals->deleteRecords($conditions);
			pr($data);
			//delete them  from sg val sg val 
			echo 'SubgroupValsSubgroup deleted ';
			pr($sgIds);
			$conditions = [];
            $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
            $data = $this->SubgroupValsSubgroup->deleteRecords($conditions);
			pr($data);
			 //deleet ius     
			echo 'IndicatorUnitSubgroup deleted ';
			pr($sgvalsgIds);			 
            $conditions = [];
            $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);
			pr($data);
			 //deleet ius   
			echo 'Data deleted ';
			pr($sgvalsgIds);				 
            $conditions = [];
            $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalsgIds];
            $data = $this->Data->deleteRecords($conditions);
			pr($data);
			if (count($getIusNids) > 0) {
                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
                echo 'IcIus deleted ';
			pr($getIusNids);	
				$data = $this->IcIus->deleteRecords($conditions);
				pr($data);
            }
			return true;
		}else{
			return false;
		}
		            
        }else{
			return false;
		}
		
	}
	
	
	  /*
     * check name if name exists in indicator table or not
     * return true or false
     */

    public function checkDmTypeName($sgTypeName = '', $sgtypeNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
        $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $sgTypeName];
        if (isset($sgtypeNid) && !empty($sgtypeNid)) {
            $extra[_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' !='] = $sgtypeNid;
            $conditions = array_merge($conditions, $extra);
        }
        $nameexits = $this->getRecords($fields, $conditions);
	
        if (!empty($nameexits)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * check gid if exists in subgroup type table or not
     * return true or false
	  @sgtypeGid gid
	  @sgtypeNid  sg type nidd
     */
    public function checkDmTypeGid($sgtypeGid = '', $sgtypeNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
        $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_GID => $sgtypeGid];
        if (isset($sgtypeNid) && !empty($sgtypeNid)) {
            $extra[_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' !='] = $sgtypeNid;
            $conditions = array_merge($conditions, $extra);
        }

        $gidexits = $this->getRecords($fields, $conditions);

        if (!empty($gidexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }

	
	/*
     * check gid if exists in subgroup  table or not
     * return true or false
	  @sgGid gid
	  @sgNid  sg  nid
     */

	public function checkGidSg($sgGid = '', $sgNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_SUBGROUP_NID];
        $conditions = [_SUBGROUP_SUBGROUP_GID => $sgGid];
        if (isset($sgNid) && !empty($sgNid)) {
            $extra[_SUBGROUP_SUBGROUP_NID . ' !='] = $sgNid;
            $conditions = array_merge($conditions, $extra);
        }

        $gidexits = $this->Subgroup->getRecords($fields, $conditions);

        if (!empty($gidexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }
	
	public function checkNameSg($sgName = '', $sgNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_SUBGROUP_NID];
        $conditions = [_SUBGROUP_SUBGROUP_NAME => $sgName];
        if (isset($sgNid) && !empty($sgNid)) {
            $extra[_SUBGROUP_SUBGROUP_NID . ' !='] = $sgNid;
            $conditions = array_merge($conditions, $extra);
        }

        $gidexits = $this->Subgroup->getRecords($fields, $conditions);

        if (!empty($gidexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }
	
	/*
	method to add modify subgroup
	@ sgData array subgroup data 
	@ sgTypeNid sg type nid
	*/
	
	function manageSubgroup($sgData=[],$sgTypeNid){		
		echo 'sgData';
		//pr($sgData);
		pr($sgTypeNid);
		if(isset($sgData) && !empty($sgData)){
			foreach($sgData as $value){				
				$sgNid = '';
				$subgrpdetails =[];
				$sgNid = $value['nId']; 
				$subgrpdetails[_SUBGROUP_SUBGROUP_NAME]=$value['val'];
				$subgrpdetails[_SUBGROUP_SUBGROUP_NID]=$sgNid;
				$subgrpdetails[_SUBGROUP_SUBGROUP_TYPE]=$sgTypeNid;
				$subgrpdetails[_SUBGROUP_SUBGROUP_GID]=$value['gId'];
				if(!empty($sgNid)){
					$catConditions = [];
					$catConditions = [_SUBGROUP_SUBGROUP_NID => $sgNid];
					unset($subgrpdetails[_SUBGROUP_SUBGROUP_NID]);
					pr($subgrpdetails);
					$this->Subgroup->updateRecords($subgrpdetails, $catConditions); //update case
				}
				else{
					pr($subgrpdetails);
					$this->Subgroup->insertData($subgrpdetails);
					
				}
					
			}
			
		}
	}
	
	
	/*
	method  to add modify the subgroup type 
	@subgroupData array 
	*/
	function manageSubgroupTypeData($subgroupData=[]){
		pr($subgroupData['subgroupData']);
		$subgrpTypedetails=[];
		$subgroupData = json_decode($subgroupData['subgroupData'],true);
		if(isset($subgroupData) && !empty($subgroupData)){
			// check sg type name 
			$sgTypeNid = $subgroupData['nId'];
			pr($subgroupData['dName']);
			$sgTypeName =$this->checkDmTypeName(trim($subgroupData['dName'])  ,$sgTypeNid); //check subgrpType name 
			if($sgTypeName ==false){
				return ['error' => _ERR138];
			}
			if(empty($subgroupData['dGid'])){
				$subgroupData['dGid'] = $this->CommonInterface->guid();
			}
			$sgTypeGid =$this->checkDmTypeGid(trim($subgroupData['dGid']),$sgTypeNid); //check subgrpType gId 
			if($sgTypeGid ==false){
				return ['error' => _ERR137];
			}
			foreach($subgroupData['dValues'] as $value){
				$sgName =$this->checkNameSg(trim($value['val']),$value['nId']);
				if($sgName ==false){
					return ['error' => _ERR138];
				}
				if(empty($value['gId'])){
					$value['gId'] = $this->CommonInterface->guid();
				}
				$sgGid =$this->checkGidSg(trim($value['gId']),$value['nId']);
				if($sgGid ==false){
					return ['error' => _ERR137];
				}
			}
			// check sg type gids
			// check sg names 
			// check sg gids
			
			$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]=$subgroupData['dName'];
			$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]=$sgTypeNid;
			$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_GID]=$subgroupData['dGid'];
			if(isset($subgroupData['nId']) && !empty($subgroupData['nId'])){			
				//modify 				
					$catConditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $sgTypeNid];
					unset($subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]);
					$result = $this->updateRecords($subgrpTypedetails, $catConditions); //update case 
					$this->manageSubgroup($subgroupData['dValues'],$sgTypeNid);
				//
			}else{
					
					$result = $sgTypeNid = $this->insertDatanew($subgrpTypedetails);	
					$this->manageSubgroup($subgroupData['dValues'],$sgTypeNid);			
			}
			if ($result > 0) {
				return true;
			} else {
				return ['error' => _ERR100]; //server error 
			}
			
		}
	}
	
	/*
	
	method to get the all subgroup type  details
	return array	
	*/	
	function  getSubgroupTypeList(){
		$data =[];$sgType =[];
		$fields=[ _SUBGROUPTYPE_SUBGROUP_TYPE_NID,
		_SUBGROUPTYPE_SUBGROUP_TYPE_GID,
		_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
		$conditions =[];
		$data = $this->getRecords($fields,$conditions);
		if(!empty($data)){
			
			foreach($data as $index=> $value){
				$sgType[$index]['nId']=$value[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
				$sgType[$index]['gid']=$value[_SUBGROUPTYPE_SUBGROUP_TYPE_GID];
				$sgType[$index]['name']=$value[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
			}
		}
		return $sgType;
	}
	
	/*
	
	method to get the subgroup type with subgroup details
	return array	
	*/	
	function  getSubgroupTypeDetailsById($sgTypeNid=''){
		$data =[];$sgType =[];
		$fields=[ _SUBGROUPTYPE_SUBGROUP_TYPE_NID,
		_SUBGROUPTYPE_SUBGROUP_TYPE_GID,
		_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
		$conditions =[_SUBGROUPTYPE_SUBGROUP_TYPE_NID=>$sgTypeNid];
		$data = $this->getRecords($fields,$conditions);
		if(!empty($data)){
			
			foreach($data as $value){
				$sgType['nId']=$value[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
				$sgType['dGid']=$value[_SUBGROUPTYPE_SUBGROUP_TYPE_GID];
				$sgType['dName']=$value[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
				
				$fields1=[ _SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_TYPE,_SUBGROUP_SUBGROUP_GID];
				$conditions1 =[_SUBGROUP_SUBGROUP_TYPE=>$sgTypeNid];
				$sgdata = $this->Subgroup->getRecords($fields1,$conditions1);
				foreach($sgdata as $ind=> $value){
					$sgType['dValues'][$ind]['nId']=$value[_SUBGROUP_SUBGROUP_NID];
					$sgType['dValues'][$ind]['dGid']=$value[_SUBGROUP_SUBGROUP_GID];
					$sgType['dValues'][$ind]['dName']=$value[_SUBGROUP_SUBGROUP_NAME];
				}
			}
		}
		
		//pr($sgType);
		return $sgType;
	}

    /**
     * - For DEVELOPMENT purpose only
     * Test method to do anything based on this model (Run RAW queries or complex queries)
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        return $this->SubgroupTypeObj->testCasesFromTable($params);
    }

}
