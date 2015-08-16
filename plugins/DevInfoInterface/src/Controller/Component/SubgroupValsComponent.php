<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * SubgroupVals Component
 */
class SubgroupValsComponent extends Component
{
    
    // The other component your component uses
    public $SubgroupValsObj = NULL;
	
	 public $components = ['TransactionLogs','Common','Auth',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Data',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Metadatareport',
        'DevInfoInterface.Metadata',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.SubgroupType',
        'DevInfoInterface.CommonInterface'
		];
	

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->SubgroupValsObj = TableRegistry::get('DevInfoInterface.SubgroupVals');
		require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
	
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
        // MSSQL Compatibilty - MSSQL can't support more than 2100 params - 900 to be safe
        $chunkSize = 900;
        
        if(isset($conditions['OR']) && count($conditions['OR'], true) > $chunkSize){
            
            $result = [];
            $countIncludingChildparams = count($conditions['OR'], true);
            
            // count for single index
            //$orSingleParamCount = count(reset($conditions['OR']));
            //$splitChunkSize = floor(count($conditions['OR'])/$orSingleParamCount);
            $splitChunkSize = floor(count($conditions['OR']) / ($countIncludingChildparams / $chunkSize));
            
            // MSSQL Compatibilty - MSSQL can't support more than 2100 params
            $orConditionsChunked = array_chunk($conditions['OR'], $splitChunkSize);
            
            foreach($orConditionsChunked as $orCond){
                $conditions['OR'] = $orCond;
                $subgroupVals = $this->SubgroupValsObj->getRecords($fields, $conditions, $type,$extra);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if($type == 'list'){
                    $result = array_replace($result, $subgroupVals);
                }// we dont need to preserve keys, just merge
                else{
                    $result = array_merge($result, $subgroupVals);
                }
            }
        }else{
            $result = $this->SubgroupValsObj->getRecords($fields, $conditions, $type,$extra);
        }
        return $result;
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = [])
    {
        return $this->SubgroupValsObj->deleteRecords($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        return $this->SubgroupValsObj->insertData($fieldsArray);
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
        return $this->SubgroupValsObj->insertOrUpdateBulkData($insertDataArray);
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
        return $this->SubgroupValsObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = [])
    {
        //print_r(get_class_methods($this->SubgroupValsObj));exit;
        return $this->SubgroupValsObj->getMax($column, $conditions);
    }
	
	/*
	 method to get the subgroup type  list  
	 @sgIds is array of sub group nids 
	 
	*/
	function getsgTypeNids($sgIds=[]){
	
		$sgnames =[];
		$sgTypeNids =[];
		$fields = [_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_TYPE,_SUBGROUP_SUBGROUP_NID];
		$conditions = [_SUBGROUP_SUBGROUP_NID .' IN '=>$sgIds];
		$resultSgTypes	=	$this->Subgroup->getRecords($fields,$conditions,'all');	
		foreach($resultSgTypes as $value){
			$sgnames[$value[_SUBGROUP_SUBGROUP_NID]]   = $value[_SUBGROUP_SUBGROUP_NAME];
			$sgTypeNids[$value[_SUBGROUP_SUBGROUP_NID]] = $value[_SUBGROUP_SUBGROUP_TYPE];
		}
		
		return ['sgnames'=>$sgnames ,'sgTypeNids'=>$sgTypeNids];
		
	}
	
	/*
	 method to get the subgroup nids list 
	  @sgValNids='' is array  of sub val nids 
	 
	*/
	function getsgNids($sgValNids=''){
		
		$fields = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID,SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
		$conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID .' IN '=>$sgValNids];
		$resultSbgrpNids	=	$this->SubgroupValsSubgroup->getRecords($fields,$conditions,'list');	
		
		return $resultSbgrpNids;
		
			
	}
	
	/*
	 method to get the subgroup nids list 
	  @sgValNids='' is array  of sub val nids 
	 
	*/
	function getSubgroupNids($sgValId=''){
		$conditions =[];
		$fields = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID,SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
		if($sgValId!='')
		$conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID .' IN '=>$sgValId];
		$sgValNids	=	$this->SubgroupValsSubgroup->getRecords($fields,$conditions,'list');		
		return $sgValNids;
		
			
	}
	
	
	function getSgValsSgData($sgValId=''){
		$conditions =[];
		$fields = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID,SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
		if($sgValId!='')
		$conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID .' IN '=>$sgValId];
		$sgValNidsData	=	$this->SubgroupValsSubgroup->getRecords($fields,$conditions,'all');		
		return $sgValNidsData;
		
			
	}
	
	
	function getSubgroupValsDimensionListById($sgValNid=''){
		$finalArray=[];
		$sgvalData = $this->getSubgroupValData($sgValNid);
		if(!empty($sgvalData)){
				
				
				foreach($sgvalData as $index=> $value){
				//$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]
				$finalArray['sNid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
				$finalArray['sGid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_GID];
				$finalArray['sName']=$value[_SUBGROUP_VAL_SUBGROUP_VAL];
				$getSgNids = $this->getSubgroupNids($value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]);
				
				
				foreach($getSgNids as $innerindex=> $innerValue){
					$fields = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_TYPE];
					$conditions = [_SUBGROUP_SUBGROUP_NID .' IN '=> $innerValue];
					$resultSbgrp	=	$this->Subgroup->getRecords($fields,$conditions,'all');	
					//pr($resultSbgrp);
					if(!empty($resultSbgrp)){
						$finalArray['dimension'][$innerindex]['dcNid'] = $resultSbgrp[0][_SUBGROUP_SUBGROUP_TYPE];
						$finalArray['dimension'][$innerindex]['dvNid'] = $resultSbgrp[0][_SUBGROUP_SUBGROUP_NID];
						$finalArray['dimension'][$innerindex]['dvName']= $resultSbgrp[0][_SUBGROUP_SUBGROUP_NAME];
						$finalArray['dimension'] = array_values($finalArray['dimension']);
					}
					
				}
				//$finalArray['']	
				//pr($getSgNids);die;
			}
		}
		 //return $newarray = $finalArray;
		return $finalArray;
	}
	
	function getSubgroupValsDimensionList_old(){
		
		$sgvalData = $finalArray = $sgDetails = $resultSbgrp = $sbgrpListArray = $dimArray	=$sTypeRecords= $allDcNids = $returnData = [];
		$sgvalData = $this->SubgroupValsObj->getSgValSgData(); // get sub group val details list 
		
		
		$fields         = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_TYPE];
		$conditions     = [];  //$conditions = [_SUBGROUP_SUBGROUP_NID .' IN '=> $innerValue];
		$resultSbgrp	=	$this->Subgroup->getRecords($fields,$conditions,'all');	
		   // prepare subgroup list
        if(!empty($resultSbgrp)){
			foreach($resultSbgrp as $index=> $value){
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['nid']= $value[_SUBGROUP_SUBGROUP_NID];// storing sg nids 
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['gid']= $value[_SUBGROUP_SUBGROUP_GID];// storing sg gid  
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['name']= $value[_SUBGROUP_SUBGROUP_NAME];// storing sg name  
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['type']= $value[_SUBGROUP_SUBGROUP_TYPE];// storing sg type 
			}
		}
        if(!empty($sgvalData)){
			$cnt=0;
			foreach($sgvalData as $index=> $value){		
				//pr($value);
				//die;
				$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['sNid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
				$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['sGid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_GID];
				$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['sName']=$value[_SUBGROUP_VAL_SUBGROUP_VAL];
				
				$resultSbgrp = (isset($sgDetails[$value['SGS']['Subgroup_NId']]))?$sgDetails[$value['SGS']['Subgroup_NId']]:'';
				$sgNid = (isset($resultSbgrp['nid']))?$resultSbgrp['nid']:'';
				
				if($sgNid!=''){
					$allDcNids[$resultSbgrp['type']] = $resultSbgrp['type'];					
					$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$resultSbgrp['nid']]['dcNid'] =  $resultSbgrp['type'];
					$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$resultSbgrp['nid']]['dvNid'] = $resultSbgrp['nid'];
					$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$resultSbgrp['nid']]['dvName']= $resultSbgrp['name'];
					$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension']= array_values($finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension']);
						
				}
				/*
				foreach($value['subgroup_vals_subgroup'] as $innerIndex=>$innerValue ){
					$resultSbgrp = (isset($sgDetails[$innerValue[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID]]))?$sgDetails[$innerValue[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID]]:'';
					if(!empty($resultSbgrp)){
						$allDcNids[$resultSbgrp['type']] = $resultSbgrp['type'];
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$innerIndex]['dcNid'] =  $resultSbgrp['type'];
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$innerIndex]['dvNid'] = $resultSbgrp['nid'];
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$innerIndex]['dvName']= $resultSbgrp['name'];
					}
					
				}
				*/
				
				$cnt++;
		}
				
		$sbgrpListArray['subgroupList']=array_values($finalArray);
		
	
		}
		
	
		// get all subgroup types
		if(!empty($allDcNids))
		$sTypeRecords = $this->getSubgroupTypeData($allDcNids); //get dimensions 
	
		// prepare Subugroup Types List
		if(!empty($sTypeRecords)){	
            //$dimArray['dimensionList'] = $sTypeRecords;	
			foreach ($sTypeRecords as  $sTypeindex=> $sTypeValue) {
                $dimArray['dimensionList'][] = ['id'=>$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID], 'name'=>$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]];
			}
		}
		$returnData = array_merge($sbgrpListArray,$dimArray);		
	return $returnData;
		
		
	}
	
	/*
	 method to get the  Subgroup Dimensions with their values in subgroup table   
	  return array 
	 
	*/	
	function getSubgroupValsDimensionList(){
		
		$sgDetails =$returnData=$allDcNids= $sbgrpListArray = $sgNids = $finalArray = $dimArray = $sTypeRecords = $sgvalData = [];
		//$subgrpValsData = $this->SubgroupValsObj->find()->where(['1=1'])->contain(['SubgroupValsSubgroup'], true)->hydrate(false)->all()->toArray();
     
		//--------------------------- GETTING COMPLETE RECORDS
        
        // get all subgroups
		$sgvalData = $this->getSubgroupValData();
        //pr($getSgNidsData); exit;
        
        //pr($getSgNidsData); exit;
        // Get Subgroup Val Subgroup
		$getSgNidsData = $this->getSgValsSgData(); //sg val sg val data		
        //pr($getSgNidsData); exit;
        // get subgroups
		$fields         = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_TYPE];
		$conditions     = [];  //$conditions = [_SUBGROUP_SUBGROUP_NID .' IN '=> $innerValue];
		$resultSbgrp	=	$this->Subgroup->getRecords($fields,$conditions,'all');	

        //--------------------------- PREPARING DATA
        
        // prepare Subugroup Types List
		/*if(!empty($sTypeRecords)){	
            //$dimArray['dimensionList'] = $sTypeRecords;	
			foreach ($sTypeRecords as  $sTypeindex=> $sTypeValue) {
                $dimArray['dimensionList'][] = ['id'=>$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID], 'name'=>$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]];
			}
		}*/
        //pr($sTypeRecords); exit;
        // prepare subgroup list
        if(!empty($resultSbgrp)){
			foreach($resultSbgrp as $index=> $value){
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['nid']= $value[_SUBGROUP_SUBGROUP_NID];// storing sg nids 
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['gid']= $value[_SUBGROUP_SUBGROUP_GID];// storing sg gid  
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['name']= $value[_SUBGROUP_SUBGROUP_NAME];// storing sg name  
				$sgDetails[$value[_SUBGROUP_SUBGROUP_NID]]['type']= $value[_SUBGROUP_SUBGROUP_TYPE];// storing sg type 
			}
		}
        //pr($resultSbgrp); exit;		
		if(!empty($getSgNidsData)){			
			foreach($getSgNidsData as $index=> $value){
				$sgNids[$value[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID]][]= $value[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];// storing sg nids 
			}			
		}
		if(!empty($sgvalData)){			
			foreach($sgvalData as $index=> $value){				
				$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['sNid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
				$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['sGid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_GID];
				$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['sName']=$value[_SUBGROUP_VAL_SUBGROUP_VAL];
				$getSgNids = (isset($sgNids[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]))?$sgNids[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]:''; //get all sg nids for specific sg val nid 
			
				if(!empty($getSgNids)){
					foreach($getSgNids as $innerindex=> $innerValue){				
					$resultSbgrp = (isset($sgDetails[$innerValue]))?$sgDetails[$innerValue]:'';
					
					if(!empty($resultSbgrp)){
						$sgNid = (isset($resultSbgrp['nid']))?$resultSbgrp['nid']:'';
				
						if($sgNid!=''){
						$allDcNids[$resultSbgrp['type']] = $resultSbgrp['type'];						
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$innerindex]['dcNid'] = $resultSbgrp['type'];
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$innerindex]['dvNid'] = $resultSbgrp['nid'];
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'][$innerindex]['dvName']= $resultSbgrp['name'];
						$finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension'] = array_values($finalArray[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['dimension']);
						
						}	
	
						
					}
				  }				
				}					
			}
			$sbgrpListArray['subgroupList']=array_values($finalArray);
		}
		
		
		// get all subgroup types
		if(!empty($allDcNids))
		$sTypeRecords = $this->getSubgroupTypeData($allDcNids); //get dimensions 
	
		// prepare Subugroup Types List
		if(!empty($sTypeRecords)){	
            //$dimArray['dimensionList'] = $sTypeRecords;	
			foreach ($sTypeRecords as  $sTypeindex=> $sTypeValue) {
                $dimArray['dimensionList'][] = ['id'=>$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID], 'name'=>$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]];
			}
		}
		$returnData = array_merge($sbgrpListArray,$dimArray);
	
		//$newArray = array_merge($sbgrpListArray,$dimArray);		
		return $returnData;
	}
	
	

	/*
	 method to get the  Subgroup Dimensions with their values in subgroup table   
	  return array 
	 
	*/
	function getSubgroupDimensionList(){
		$stypeNid  ='';
		$resultSbgrp	= $sTypeRecords = $sTypeRows = [];
	    $sTypeRecords = $this->getSubgroupTypeData();
		
		//Prepare Subugroup Types List
		if(!empty($sTypeRecords)){
			
		foreach ($sTypeRecords as  $sTypeindex=> $sTypeValue) {
			$stypeNid = $sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];

		    $fields = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID];
		    $conditions = [_SUBGROUP_SUBGROUP_TYPE .' IN '=> $stypeNid];
		    $resultSbgrp	=	$this->Subgroup->getRecords($fields,$conditions,'all');	
			if(!empty($resultSbgrp)){
				
				foreach($resultSbgrp as $index=> $value){
					$sTypeRows['dimensionValue'][$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]][$index]['dvNid'] = $value[_SUBGROUP_SUBGROUP_NID];
					$sTypeRows['dimensionValue'][$sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]][$index]['dv']    = $value[_SUBGROUP_SUBGROUP_NAME];
				}
				$sTypeRows['dimensionList'][$stypeNid]['id']   = $sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
				$sTypeRows['dimensionList'][$stypeNid]['name'] = $sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
				$sTypeRows['dimensionList'] = array_values($sTypeRows['dimensionList']);
			}
		  }
		
		}
		return $sTypeRows;
		
	}
	
	/*
	
	 get Subgroups type Records  
	 returns array
	 */
	function getSubgroupTypeData($sTypeNids=[]){
		
		//get Subgroups type Records  
		$sTypeFields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME, _SUBGROUPTYPE_SUBGROUP_TYPE_GID, _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER];
		$sTypeConditions =[];
		if(isset($sTypeNids) && !empty($sTypeNids)){
			$sTypeConditions =[_SUBGROUPTYPE_SUBGROUP_TYPE_NID.' IN '=>$sTypeNids];
		}
		$sTypeRecords = $this->SubgroupType->getRecords($sTypeFields, $sTypeConditions);
		return $sTypeRecords;
	}
	
	/*
	
	 get Subgroups val Records  
	 returns array
	 */
	function getSubgroupValData($sgValNid=''){
		
		//get Subgroups val Records  
		$conditions=[];
		if(isset($sgValNid) && $sgValNid!=''){
			$conditions =[_SUBGROUP_VAL_SUBGROUP_VAL_NID=>$sgValNid];
		}
		$fields = [_SUBGROUP_VAL_SUBGROUP_VAL_GID, _SUBGROUP_VAL_SUBGROUP_VAL,_SUBGROUP_VAL_SUBGROUP_VAL_NID];
		$extra['order']=[_SUBGROUP_VAL_SUBGROUP_VAL=>'ASC'];

		$resultSet 		=	$this->getRecords($fields,$conditions,'all',$extra);	
		return $resultSet;
	}
	 

    	
	
	/**
     * export the subgroup Val details to excel 
	 @dbId is the databasee id 
	*/	
	public function exportSubgroupValDetails($dbId='') {
		$resultSet = $fields = $conditions = $sgvalindexSgIdvalue=[];	
		$width    	= 50;
        $dbId      	= (isset($dbId))?$dbId:'';
        $dbDetails 	= $this->Common->parseDBDetailsJSONtoArray($dbId);
        $dbConnName = $dbDetails['db_connection_name'];
        $dbConnName = str_replace(' ', '-', $dbConnName);
        
		//get Subgroup val  Records
		$resultSet 		=	$this->getSubgroupValData();	
		
		  //get Subgroups type Records  
		$sTypeRecords = $this->getSubgroupTypeData();
			//Prepare Subugroup Types List
		foreach ($sTypeRecords as $sTypeValue) {
			$sTypeRows[] = $sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
		}
		
        $objPHPExcel 	= new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();

       // $returnFilename = $dbConnName. _DELEM4 . _MODULE_NAME_UNIT ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = $dbConnName. _DELEM4 . _SUBGRPVALEXPORT_FILE ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = str_replace(' ', '-', $returnFilename);
        $rowCount 		= 1;
        $firstRow 		= ['A' => 'Subgroup Details'];
        $styleArray 	= array(
				'font' => array(
					'bold' => false,
					'color' => array('rgb' => '000000'),
					'size' => 20,
					'name' => 'Arial',
				));
		
        foreach ($firstRow as $index => $value) {            
			$objPHPExcel->getActiveSheet()->SetCellValue($index.$rowCount, $value)->getColumnDimension($index)->setWidth($width);
            $objPHPExcel->getActiveSheet()->getStyle($index. $rowCount)->applyFromArray($styleArray);
        }
		
		$rowCount = 3;		
         
		if(empty($sTypeRows)){
			 $sTypeRows = ['Location', 'Sex', 'Age', 'Other'];
		}
		
		$charVar ='A';
		$secRow = ['Subgroup Name', 'Subgroup Gid'];
		$secRow = array_merge($secRow,$sTypeRows);   		

		$objPHPExcel->getActiveSheet()->getStyle("A$rowCount:Z$rowCount")->getFont()->setItalic(true);

		foreach ($secRow as $index => $value) {			
			$objPHPExcel->getActiveSheet()->getStyle("$charVar$rowCount")->getFont()->setItalic(true);
            $objPHPExcel->getActiveSheet()->SetCellValue($charVar . $rowCount, $value);
			$charVar++;			
        }

        $returndata = $data = [];
        $startRow = 6;		
		if(!empty($resultSet)){			
		$cnt=0;
		foreach ($resultSet as $index => $value) {
		
				$charVar ='A';
				$objPHPExcel->getActiveSheet()->SetCellValue($charVar . $startRow, (isset($value[_SUBGROUP_VAL_SUBGROUP_VAL])) ? $value[_SUBGROUP_VAL_SUBGROUP_VAL] : '' )->getColumnDimension('A')->setWidth($width+20);
				$objPHPExcel->getActiveSheet()->SetCellValue($charVar . $startRow, (isset($value[_SUBGROUP_VAL_SUBGROUP_VAL])) ? $value[_SUBGROUP_VAL_SUBGROUP_VAL] : '')->getColumnDimension('B')->setWidth($width);
				$charVar++;
				$objPHPExcel->getActiveSheet()->SetCellValue($charVar . $startRow, (isset($value[_SUBGROUP_VAL_SUBGROUP_VAL_GID])) ? $value[_SUBGROUP_VAL_SUBGROUP_VAL_GID] : '')->getColumnDimension('C')->setWidth($width);
				$objPHPExcel->getActiveSheet()->SetCellValue($charVar . $startRow, (isset($value[_SUBGROUP_VAL_SUBGROUP_VAL_GID])) ? $value[_SUBGROUP_VAL_SUBGROUP_VAL_GID] : '')->getColumnDimension('D')->setWidth($width);
				
				$sValnids = $value[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
				$sgIds =	$this->getsgNids($sValnids );
				$sgTypes=[];
				$sgTypes =	$this->getsgTypeNids($sgIds );	
				//return ['sgnames'=>$sgnames ,'sgTypeNids'=>$sgTypeNids];
		
				foreach ($sTypeRecords as $sTypeValue) {
					$charVar++;
					$sgTypename='';
					
					if(!empty($sgTypes)){
					if (in_array($sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID], $sgTypes['sgTypeNids'])) {
					    $sgNid =	array_search($sTypeValue[_SUBGROUPTYPE_SUBGROUP_TYPE_NID],$sgTypes['sgTypeNids']);
						$sgTypename = $sgTypes['sgnames'][$sgNid];
						
					}
					}
					$objPHPExcel->getActiveSheet()->SetCellValue($charVar . $startRow, $sgTypename)->getColumnDimension($charVar)->setWidth($width); //SubgroupVals GID
					 //Increment Column
				}
					
				
				$startRow++;
        }
	}
        
	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$saveFile = _SUBGROUPVAL_PATH . DS .$returnFilename;
	$saved = $objWriter->save($saveFile);
	return $saveFile;

    }
		
	
	/*
	delete subgroup and its corresponding details 
	@sgId is the subgroup nid 	
	*/
	public function deleteSubgroupValData($sgvalNid=''){
	
		if(!empty($sgvalNid)){

			// get subgroup vals subgroups  records
			//$sgvalsgIds = $this->getsgValNids([$sgId]); //get subgroup val nids
			
			$conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_SUBGROUP_VAL_NID.' IN '=>$sgvalNid];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');	
			
			$conditions = [];
            $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalNid];
            $rsltsgVal  = $this->deleteRecords($conditions);			
			
			
			if($rsltsgVal>0){				
			
			 //deleete from sgvalsg table       
            
			$conditions = [];			
            $conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID . ' IN ' => $sgvalNid];
            $rslt = $this->SubgroupValsSubgroup->deleteRecords($conditions);
						
			 //deleete ius     
            $conditions = [];
            $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalNid];
            $rslt = $this->IndicatorUnitSubgroup->deleteRecords($conditions);
		
			 //deleete data    
				 
            $conditions = [];
            $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalNid];
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
     * check name if name exists in indicator table or not
     * return true or false
     */

    public function checkSgValGid($sgValGid = '',  $sgvalNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_VAL_SUBGROUP_VAL_NID];
        $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_GID => $sgValGid];
        if (isset($sgvalNid) && !empty($sgvalNid)) {
            $extra[_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' !='] = $sgvalNid;
            $conditions = array_merge($conditions, $extra);
        }
        $gidexits = $this->getRecords($fields, $conditions);
	
        if (!empty($gidexits)) {
            return false;
        } else {
            return true;
        }
    }
	
	  /*
     * check name if name exists in indicator table or not
     * return true or false
     */

    public function checkSubgrpValName($sgValName = '', $sgvalNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_VAL_SUBGROUP_VAL_NID];
        $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL => $sgValName];
        if (isset($sgvalNid) && !empty($sgvalNid)) {
            $extra[_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' !='] = $sgvalNid;
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
		method  to validate the input data 
	
	*/
	
	function validDateInputData($subgroupValData){
		
		$posetdNameandGid  = $this->getNameGids($subgroupValData);
 		$posetdsValName       = $posetdNameandGid['sValName'];
 		$posetdsValGid        = $posetdNameandGid['sValGid'];
 		$posetdsbgrpName      = $posetdNameandGid['sbgrpName'];
		
		$errodata=[];
		
		foreach($subgroupValData as $value){				
				//validate subgroup val details 
				$sNid = (isset($value['sNid']) && !empty($value['sNid']))? trim($value['sNid']):''; //sbgrp val nid   
				$sName = (isset($value['sName']) && !empty($value['sName']))? trim($value['sName']):''; //sbgrp val name  gid 
				$sGid = (isset($value['sGid']) && !empty($value['sGid']))? trim($value['sGid']):'';  //sbgrp val gid 
				
				if($sName!='' && $posetdsValName[$sName]>1){					
					$errodata['sName'][]=$sName;
					//['error' => _ERR152,'sName'=>$sName]; // sg val name already exists 
				}
				
				if($sGid!='' &&  $posetdsValGid[$sGid]>1){					
					$errodata['sName'][]=$sName;
					//['error' => _ERR137,'sName'=>$sName]; // sg val name already exists 
				}
				
				if(empty($sGid)){
					
				}else{
					
					$sgGidcheck  = $this->checkSgValGid(trim($sGid),$sNid); // check subgrpType gId 
					if($sgGidcheck ==false){
						$errodata['sName'][]=$sName;
						//return ['error' => _ERR137];//gid already exists
					}
					$validGid = $this->Common->validateGuid(trim($sGid));
					if($validGid == false){

						$errodata['sName'][]=$sName;
						//return ['error' => _ERR142];  // gid invalid 
					}
				}
				
				if(empty($sName)){
						return ['error' => _ERR152]; 		//sbgrp val name   empty
				}else{
					
					//$chkAllowchar = $this->CommonInterface->allowAlphaNumeric($sName);
					//if($chkAllowchar==false){
							
						    //$errodata['sName'][]=$sName;
							// return ['error' => _ERR146]; //allow only space and [0-9 or a-z]
					//}
					$sgValName =$this->checkSubgrpValName($sName  ,$sNid); //check subgrp val name exists or not 
					
					if($sgValName == false){

						$errodata['sName'][]=$sName;
						//return ['error' => _ERR153]; // sg val name already exists 
					}
				}
				
				//validate subgroup details 
				/*
				foreach($value['dimension'] as $innerVal){
					
					$chkAllowchar ='';
					$dcNid  =	(isset($innerVal['dcNid']))?trim($innerVal['dcNid']):'';
					if(empty($dcNid)){
						 return ['error' =>_ERR151]; //sbgrp type nid is blank 
					}
					$dvNid  = 	(isset($innerVal['dvNid']))?  trim($innerVal['dvNid']):'';   // sbgrp nid 
					$dvName =	(isset($innerVal['dvName']))? trim($innerVal['dvName']):''; //sbgrp name 
					if(empty($dvName)){
						return ['error' => _ERR148]; 		//sbgrp name   empty
					}else{
						
						if($posetdsbgrpName[$dvName]>1){					
							return ['error' => _ERR150]; // sg val name already exists 
						}
						$chkAllowchar = $this->CommonInterface->allowAlphaNumeric($dvName);
						
						if($chkAllowchar==false){
							 return ['error' => _ERR146]; //allow only space and [0-9 or a-z]
						}
						$sgName =$this->SubgroupType->checkNameSg($dvName  ,$dvNid); //check subgrp name exists or not 
						if($sgName ==false){
							return ['error' => _ERR150]; // subgrp name already exists 
						}
					}
				}
				*/
			}
			return ['errordata'=>(isset($errodata['sName']))?$errodata['sName']:''];
	}
	
	/*
	check combination of sg val nid and sg nid 
	return boolean
	*/
	
	function checSgValSgCombination($nid,$sgNid){
		$data = $conditions = $fields=[];
		$conditions[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID]= $nid;
		$conditions[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID]= $sgNid;
		$fields =[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID];
		$data = $this->SubgroupValsSubgroup->getRecords($fields,$conditions);
	    if (!empty($data)) {
            return false;
        } else {
            return true;
        }
	}
	
	
	
	
	/*
	method  to save subgroup  data  
	@subgroupValData array
	*/
	
	function manageSubgroup($sgdata,$nid){
		$orderNo =0;
		$orderNo = $this->Subgroup->getMax(_SUBGROUP_SUBGROUP_ORDER,[]);
		$orderNo = $orderNo+1;
		
		foreach($sgdata as $value){
			$subgrpdetails =[];
			$sgNid = $value['dvNid']; 
			$subgrpdetails[_SUBGROUP_SUBGROUP_NAME]= trim($value['dvName']); // sg name 
			$subgrpdetails[_SUBGROUP_SUBGROUP_NID]=$value['dvNid']; //sg nid 
			$subgrpdetails[_SUBGROUP_SUBGROUP_TYPE]=$value['dcNid']; // sg type nid 
			if(isset($sgNid) && !empty($sgNid)){   // modify case 
				
				unset($subgrpdetails[_SUBGROUP_SUBGROUP_NID]);
				$conditions = [];
				$conditions = [_SUBGROUP_SUBGROUP_NID =>$sgNid];
				$lastId = $this->Subgroup->updateRecords($subgrpdetails,$conditions);        		// modify sg val
				$combExists = $this->checSgValSgCombination($nid,$sgNid);				 			// check combination 
				
				if($combExists == true){
					$sgvalSgdata=[];
					$sgvalSgdata[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID]= $nid; //sgvalnid 
					$sgvalSgdata[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID]= $sgNid;  // sg nid 
					$this->SubgroupValsSubgroup->insertData($sgvalSgdata);
				}
			}else{   // insert  case 
					
					$subgrpdetails[_SUBGROUP_SUBGROUP_GID]   = $this->CommonInterface->guid();
					$subgrpdetails[_SUBGROUP_SUBGROUP_ORDER] = $orderNo;
					$lastsgId = $this->Subgroup->insertData($subgrpdetails); 				// save subgroup 
					
					$combExists = $this->checSgValSgCombination($nid,$lastsgId);  					// check combination 
					if($combExists==true){
						$sgvalSgdata=[];
						$sgvalSgdata[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID]= $nid;
						$sgvalSgdata[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID]= $lastsgId;
						$this->SubgroupValsSubgroup->insertData($sgvalSgdata);
					
					}
					$orderNo++;
			}
		}
		
		
	}
	
	/*
	method  to add /Modify subgroup val data  
	@subgroupValData array
	*/
	function addModifySubgroupValData($subgroupValData,$skipSgValname){


		$orderNo = $this->getMax(_SUBGROUP_VAL_SUBGROUP_VAL_ORDER,[]);
		$orderNo = $orderNo+1;
		foreach($subgroupValData as $value){
				$skip = false;
				if(!empty($skipSgValname) && in_array(trim($value['sName']),$skipSgValname)==true){
					$skip = true;					
				}				
				if($skip == false){
				$data =	[];				
				$data[_SUBGROUP_VAL_SUBGROUP_VAL_NID] = (isset($value['sNid']))? trim($value['sNid']):''; //sbgrp val nid   
				$data[_SUBGROUP_VAL_SUBGROUP_VAL] = (isset($value['sName']))? trim($value['sName']):''; //sbgrp val name   
				if(isset($data[_SUBGROUP_VAL_SUBGROUP_VAL_NID]) && !empty($data[_SUBGROUP_VAL_SUBGROUP_VAL_NID])){
						if(isset($value['sGid']) && !empty($value['sGid']))
						$data[_SUBGROUP_VAL_SUBGROUP_VAL_GID]  = trim($value['sGid']);
					
						unset($data[_SUBGROUP_VAL_SUBGROUP_VAL_NID]);
					    $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID =>$value['sNid']];											
					 	$lastId = $this->updateRecords($data,$conditions);        		// modify sg val
						$conditions = [];			
						$conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID . ' IN ' => $value['sNid']];
						$rslt = $this->SubgroupValsSubgroup->deleteRecords($conditions);	
                        if(isset($value['dimension']) && !empty($value['dimension']))						
					    $this->manageSubgroup($value['dimension'],$value['sNid']);	 //add /modify subgroup
					
			    }else{
						$data[_SUBGROUP_VAL_SUBGROUP_VAL_GID]  = (isset($value['sGid']) && !empty($value['sGid']))? trim($value['sGid']):$this->CommonInterface->guid(); //sbgrp val gid
						$data[_SUBGROUP_VAL_SUBGROUP_VAL_ORDER] =  $orderNo;  //sbgrp val order 
						$data[_SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL] =  '0';  //sbgrp val order 
						//pr($data);
						//pr($value);die;
						$lastId = $this->insertData($data); 	// insert sg val 
					 	if(isset($value['dimension']) && !empty($value['dimension']))
						$this->manageSubgroup($value['dimension'],$lastId);	 // add /modify subgroup 	
				}
				
				$orderNo++;
				}
					   
		}
			
		//if($lastId)
			return true;
		//else			
			//return false;
			//return false;
	}
	
	/*
	returns array of name and gids 
	*/
	function getNameGids($subgroupValData){
		$sbgrpName= $sValName= $sValGid=[];
		$cnt=0;
		foreach($subgroupValData as $value){
				
			//validate subgroup val details 
			$sValName[$cnt] = (isset($value['sName']))? trim($value['sName']):''; //sbgrp val name  gid 
			$sValGid[$cnt]  = (isset($value['sGid']))? trim($value['sGid']):'';  //sbgrp val gid 
			if(isset($value['dimension']) && !empty($value['dimension'])){
				foreach($value['dimension'] as $innerVal){
				
				$sbgrpName[$cnt]=	(isset($innerVal['dvName']))? trim($innerVal['dvName']):''; //sbgrp name 
				$cnt++;
				}
			}
			
				$cnt++;
		}
		return ['sValName'=>array_count_values($sValName),'sValGid'=>array_count_values($sValGid),'sbgrpName'=>array_count_values($sbgrpName)];
	}
	
		/*
	method  to add modify the subgroup type 
	@subgroupData array 
	*/
	function manageSubgroupValData($subgroupValData){
		
		$dbId = $subgroupValData['dbId'];
		if($dbId == ''){
			return ['error' => _ERR106]; //db id is blank
		}	

		$subgroupValData = json_decode($subgroupValData['subgroupValData'],true);
		
		
		if(isset($subgroupValData) && !empty($subgroupValData)){
			///// validation starts  here 
			$validate = $this->validDateInputData($subgroupValData);
			if(isset($validate['error'])){
				return ['error'=>$validate['error']];
			}
			$skipSgValname=[];
			if(isset($validate['errordata']) && !empty($validate['errordata'])){
				$skipSgValname = $validate['errordata'];
			}
			//pr($skipSgValname);die;
			/// validation ends here 
			    
			$result = $this->addModifySubgroupValData($subgroupValData,$skipSgValname); // add /modify  in sg val table 
			
			if ($result==true) {
				return true;
			} else {
				return ['error' => _ERR100]; //server error 
			}
			
		}
	}
	
	

}
