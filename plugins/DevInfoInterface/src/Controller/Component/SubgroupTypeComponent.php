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
    public function getRecords(array $fields, array $conditions, $type = 'all',$extra=[])
    {
        return $this->SubgroupTypeObj->getRecords($fields, $conditions, $type,$extra);
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
	function getsgNids($typeNid=''){
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
	
	/*
	delete subgroup and its corresponding details 
	@sgId is the subgroup nid 	
	*/
	public function deleteSubgroupdata($sgId=''){
	
		if($sgId){

			// get subgroup vals subgroups  records
			$sgvalsgIds = $this->getsgValNids([$sgId]); //get subgroup val nids
			
			$conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_SUBGROUP_VAL_NID.' IN '=>$sgvalsgIds];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');
			
			//delete them  from sg 			
			$conditions = [];
            $conditions = [_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgId];
            $rsltsgId = $this->Subgroup->deleteRecords($conditions);
			
			if($rsltsgId>0){				
			
			$conditions = [];
            $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $rslt      = $this->SubgroupVals->deleteRecords($conditions);
		
			$conditions = [];
			
            $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgId];//CHECK AGAIN THIS 
            $rslt = $this->SubgroupValsSubgroup->deleteRecords($conditions);
						
			 //deleete ius     
            $conditions = [];
            $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $rslt = $this->IndicatorUnitSubgroup->deleteRecords($conditions);
		
			 //deleete data    
				 
            $conditions = [];
            $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalsgIds];
            $rslt = $this->Data->deleteRecords($conditions);
			
			if (count($getIusNids) > 0) {
                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];      
					//deleete icius    
				$rslt = $this->IcIus->deleteRecords($conditions);
				
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
	delete subgroup type and its corresponding details 
	@nid is the subgroup type nid 	
	*/	
	public function deleteSubgroupTypedata($nId=''){
		
		if($nId){
			// delete data 
			
			$sgIds= $this->getsgNids($nId);          //get subgroup nids
			// get subgroup vals subgroups  records 			
			$sgvalsgIds= $this->getsgValNids($sgIds); //get subgroup val nids
			
			$conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_SUBGROUP_VAL_NID.' IN '=>$sgvalsgIds];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');
			
			//delete them  from sg type			
			$conditions = [];
            $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN ' => $nId];
            $sgtype = $this->deleteRecords($conditions);
			
			if($sgtype>0){
			
			//delete them  from sg 			
			$conditions = [];
            $conditions = [_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
            $data = $this->Subgroup->deleteRecords($conditions);
			
			//delete them  from sg val
			
			$conditions = [];
            $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $data = $this->SubgroupVals->deleteRecords($conditions);
			
			//delete them  from sg val sg val 
			
			$conditions = [];
            $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
            $data = $this->SubgroupValsSubgroup->deleteRecords($conditions);
			
			 //deleet ius     
				 
            $conditions = [];
            $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
            $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);
			
			 //deleet ius  						 
            $conditions = [];
            $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalsgIds];
            $data = $this->Data->deleteRecords($conditions);
			
			if (count($getIusNids) > 0) {
                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
              
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
	
	/*
     * check name if exists in subgroup  table or not
     * return true or false
	  @sgName sub group name 
	  @sgNid  sg  nid
     */

	public function checkNameSg($sgName = '', $sgNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_SUBGROUP_NID];
        $conditions = [_SUBGROUP_SUBGROUP_NAME => $sgName];
        if (isset($sgNid) && !empty($sgNid)) {
            $extra[_SUBGROUP_SUBGROUP_NID . ' !='] = $sgNid;
            $conditions = array_merge($conditions, $extra);
        }

        $nameexits = $this->Subgroup->getRecords($fields, $conditions);

        if (!empty($nameexits)) {
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
	public function manageSubgroup($sgData=[],$sgTypeNid){
		
		$orderNo =0;
		$orderNo = $this->Subgroup->getMax(_SUBGROUP_SUBGROUP_ORDER,[]);
		$orderNo =$orderNo+1;
		if(isset($sgData) && !empty($sgData)){
			foreach($sgData as $value){				
				$sgNid = '';
				$subgrpdetails =[];
				$sgNid = $value['nId']; 
				$sgName  = trim($value['val']);
				if($sgName!=''){
					
					$subgrpdetails[_SUBGROUP_SUBGROUP_NAME]=$sgName;
					$subgrpdetails[_SUBGROUP_SUBGROUP_NID]=$sgNid;
					$subgrpdetails[_SUBGROUP_SUBGROUP_TYPE]=$sgTypeNid;
					if(!empty($sgNid)){
					$catConditions = [];
					$catConditions = [_SUBGROUP_SUBGROUP_NID => $sgNid];
					unset($subgrpdetails[_SUBGROUP_SUBGROUP_NID]);
					//pr($subgrpdetails);
					$this->Subgroup->updateRecords($subgrpdetails, $catConditions); //update case
					
					}else{
						
					$subgrpdetails[_SUBGROUP_SUBGROUP_GID]=(isset($value['gId']) && !empty($value['gId']))?trim($value['gId']):$this->CommonInterface->guid();
					$subgrpdetails[_SUBGROUP_SUBGROUP_ORDER]=$orderNo;
					$subgrpdetails[_SUBGROUP_SUBGROUP_GLOBAL]='0';
					//pr($subgrpdetails);
					$this->Subgroup->insertData($subgrpdetails);
					$orderNo++;	

					
				}

				
				}
				
				
				
				
				
			}
			
		}
	}
	
	
	/*
	method  to add modify the subgroup type 
	@subgroupData array 
	*/
	public function manageSubgroupTypeData($subgroupData=[]){
		
		$subgrpTypedetails = [];
		$subgroupData      = json_decode($subgroupData['subgroupData'],true);
		if(isset($subgroupData) && !empty($subgroupData)){
			// check sg type name 
			$sgTypeNid = (isset($subgroupData['nId']))?$subgroupData['nId']:'';
			$subgroupData['dName'] = trim($subgroupData['dName']);
			if(empty($subgroupData['dName'])){
			   return ['error' => _ERR147]; //sg type  empty
			}else{
				$chkAllowchar = $this->CommonInterface->allowAlphaNumeric($subgroupData['dName']);
				
				if($chkAllowchar==false){
					 return ['error' => _ERR146]; //allow only space and [0-9 or a-z]
				}
				$sgTypeName =$this->checkDmTypeName($subgroupData['dName']  ,$sgTypeNid); //check subgrpType name 
				if($sgTypeName ==false){
					return ['error' => _ERR149]; //type name already exists 
				}
			}
			
			if(empty($subgroupData['dGid'])){
				if($sgTypeNid=='')
				$subgroupData['dGid'] = $this->CommonInterface->guid();
			}else{
				$sgTypeGid =$this->checkDmTypeGid(trim($subgroupData['dGid']),$sgTypeNid); //check subgrpType gId 
				if($sgTypeGid ==false){
					return ['error' => _ERR137];//gid already exists
				}
				//pr($subgroupData['dGid']);die;
				$validGid = $this->Common->validateGuid(trim($subgroupData['dGid']));
				if($validGid == false){
					return ['error' => _ERR142];  // gid emty
				}
			}
			
			if(isset($subgroupData['dValues']) && !empty($subgroupData['dValues']) ){ 
				foreach($subgroupData['dValues'] as $value){
				
				$sgNameval =  trim($value['val']);
				if(empty($sgNameval)){
					// return ['error' => _ERR148]; //sg name is  empty
				}else{
					$chkAllowchar = $this->CommonInterface->allowAlphaNumeric($sgNameval);
					if($chkAllowchar==false){
						 return ['error' => _ERR146]; //allow only space and [0-9 or a-z]
					}
					$sgName =$this->checkNameSg($sgNameval,$value['nId']);
					if($sgName ==false){
						return ['error' => _ERR150]; // sg name  already exists
					}
				}
				$value['gId'] =(isset($value['gId']))?trim($value['gId']):'';
				if(empty($value['gId'])){
					// nothing 
				}else{
					$sgGid =$this->checkGidSg($value['gId'],$value['nId']);
					if($sgGid ==false){
						return ['error' => _ERR137];//already exists sg  gid 
					}
					$validGidsg = $this->Common->validateGuid($value['gId']);
					if($validGidsg == false){
						return ['error' => _ERR142];  // gid emty
					}
				}
				
			}
			}
			
			
			
			// check sg type gids
			// check sg names 
			// check sg gids
			$orderNo = $this->getMax(_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER,[]);
			$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $subgroupData['dName'];
			$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]  = $sgTypeNid;			
			$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_GID]  = (isset($subgroupData['dGid']) && !empty($subgroupData['dGid']))?$subgroupData['dGid']:'';

			
			if(isset($subgroupData['nId']) && !empty($subgroupData['nId'])){			
				    // modify 				
					$catConditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $sgTypeNid];
					unset($subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]);
					$result = $this->updateRecords($subgrpTypedetails, $catConditions); //update case 
					$this->manageSubgroup($subgroupData['dValues'],$sgTypeNid);
				    //
			}else{
					$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER]=$orderNo+1;
					$subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_GLOBAL]='0';
			
					$result = $sgTypeNid = $this->insertData($subgrpTypedetails);	
					$this->manageSubgroup($subgroupData['dValues'],$sgTypeNid);			
					//Subgroup_Global
			}
			$returnData =['dName'=> $subgroupData['dName'],'id'=>$sgTypeNid];
			if ($result > 0) {
				return ['success' =>true,'returnData'=>$returnData];
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
					$sgType['dValues'][$ind]['gId']=$value[_SUBGROUP_SUBGROUP_GID];
					$sgType['dValues'][$ind]['val']=$value[_SUBGROUP_SUBGROUP_NAME];
				}
			}
		}
		
		return $sgType;
	}
	
	/**
     * to get the highest value
     * 
    */	
	public function getMax($column = '', $conditions = []) {
        return $this->SubgroupTypeObj->getMax($column, $conditions);
    }
	/*
	public function getOrderno(){
		
		$query = $this->SubgroupTypeObj->find();
		$result = $query->select(['max' => $query->func()->max(_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER),
		])->hydrate(false)->toArray();
		return $result = current($result)['max'];
		
	}
	*/
	
	
	
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
