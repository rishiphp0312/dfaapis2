<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;


/**
 * Data Component
 */
class DataComponent extends Component
{
    
    // The other component your component uses
    public $components   = ['Auth','DevInfoInterface.IndicatorClassifications','DevInfoInterface.IcIus','DevInfoInterface.Timeperiod'];
    public $AreaObj      = NULL;
    public $AreaLevelObj = NULL;
    public $DataObj      = NULL;
    public $TimeperiodObj      = NULL;
    public $footnoteObj      = NULL;
    public $Indicator      = NULL;
    public $IndicatorUnitSubgroupObj      = NULL;
    public $SourceObj      = NULL;
    public $IcIusObj      = NULL;
	public $delm ='[-]'; 	
 
	public function initialize(array $config)
    {
       // parent::initialize($config);
        $this->AreaObj = TableRegistry::get('DevInfoInterface.Areas');
        $this->DataObj = TableRegistry::get('DevInfoInterface.Data');
        $this->TimeperiodObj = TableRegistry::get('DevInfoInterface.TimePeriods');
        $this->AreaLevelObj = TableRegistry::get('DevInfoInterface.AreaLevel');
        $this->Indicator = TableRegistry::get('DevInfoInterface.Indicator');
        $this->IndicatorUnitSubgroupObj = TableRegistry::get('DevInfoInterface.IndicatorUnitSubgroup');
        $this->FootnoteObj = TableRegistry::get('DevInfoInterface.Footnote');
        $this->IndicatorClassificationsObj = TableRegistry::get('DevInfoInterface.IndicatorClassifications');
		$this->IcIusObj = TableRegistry::get('DevInfoInterface.IcIus');

    }
	
	public function getIusDataCollection($iusArray) {    
			
		
			$tempDataAr = array(); // temproryly store data for all element name		
		
			foreach($iusArray as $ius) {
				
				$iusAr = explode($this->delm, $ius);
				
				$iGid = $iusAr[0];
				$uGid = $iusAr[1];
				
				if(count($iusAr)=='3'){
					$sGid = $iusAr[2];	
				}else{
					$sGid = '';				
				}
				//$data = $this->IndicatorUnitSubgroupObj->find()->where(['Indicator.Indicator_GId' => $iGid,'Unit.Unit_GId'=>$uGid,'SubgroupVals.Subgroup_Val_GId'=>$sGid])->contain(['Indicator','SubgroupVals','Unit'], true)->hydrate(false)->all()->toArray();
				
				//$this->IndicatorClassifications->getIusdetails($fields1, $conditions1, 'all'); 
				if($sGid!='')
				$data = $this->IndicatorUnitSubgroupObj->find()->where(['Indicator.Indicator_GId' => $iGid,'Unit.Unit_GId'=>$uGid,'SubgroupVals.Subgroup_Val_GId'=>$sGid])->contain(['Indicator','SubgroupVals','Unit'], true)->hydrate(false)->all()->toArray();
				else
				$data = $this->IndicatorUnitSubgroupObj->find()->where(['Indicator.Indicator_GId' => $iGid,'Unit.Unit_GId'=>$uGid])->contain(['Indicator','SubgroupVals','Unit'], true)->hydrate(false)->all()->toArray();
				
				foreach($data as  $valueIus){
				
				$tempDataAr['ind'][$valueIus['indicator']['Indicator_NId']][0] = $iGid;
				$tempDataAr['ind'][$valueIus['indicator']['Indicator_NId']][1] = $valueIus['indicator']['Indicator_Name'];

				$tempDataAr['unit'][$valueIus['unit']['Unit_NId']][0] = $uGid;
				$tempDataAr['unit'][$valueIus['unit']['Unit_NId']][1] = $valueIus['unit']['Unit_Name'];
				if($sGid!=''){
				$tempDataAr['sg'][$valueIus['subgroup_val']['Subgroup_Val_NId']][0] = $sGid;
				$tempDataAr['sg'][$valueIus['subgroup_val']['Subgroup_Val_NId']][1] = $valueIus['subgroup_val']['Subgroup_Val'];
	
				}else{
				$tempDataAr['sg'][$valueIus['subgroup_val']['Subgroup_Val_NId']][0] = $valueIus['subgroup_val']['Subgroup_Val_GId'];
				$tempDataAr['sg'][$valueIus['subgroup_val']['Subgroup_Val_NId']][1] = $valueIus['subgroup_val']['Subgroup_Val'];
					
				}
				
				$tempDataAr['iusnids'][] = $valueIus['IUSNId'];	
				
				}

			}
			return $tempDataAr;
		}		
		
		
		
		
		
    
	
	 /**
     * getDEsearchData to get the details of search on basis of IUSNid,
      @areanid
      @TimeperiodNid
      @$iusNid can be mutiple in form of array
      returns data value with source
     * @access public
     */
	 
	 
    public function getDEsearchData($fields = [], $conditions = [], $extra = []) {
		 
		 $iusnidData =[];
		 
		 $iusNids = $this->getIusDataCollection($extra);
		 $returnediusNids= $iusNids['iusnids']; //iusnids 
		 pr($returnediusNids);
		 //$returnediusNids= [2398,2660,23930];		
		 
		 // getting all classifications 
		 $fields1=[_IC_IC_NAME,_IC_IC_GID,_IC_IC_NID,_IC_IC_TYPE];
		 $conditions1=[];
		 $sourceList = $this->IndicatorClassifications->getDataByParams($fields1, $conditions1, 'all'); 
		
		 // 
		 //echo 'source list';
			//pr($sourceList);		
		// structuring classification for name and gid
		 $classificationArray = array();
		 foreach($sourceList as $index=>$value){
			$classificationArray[$value['IC_NId']]['IC_GId']=$value['IC_GId'];
			$classificationArray[$value['IC_NId']]['IC_Name']=$value['IC_Name'];
			$classificationArray[$value['IC_NId']]['IC_Type']=$value['IC_Type'];
		 }
		
		
		 // getting all timperiod list 
		$fields2=[_TIMEPERIOD_TIMEPERIOD_NID,_TIMEPERIOD_TIMEPERIOD];
		$conditions2=[];		 
        $timeperiodList = $this->Timeperiod->getDataByParams($fields2, $conditions2, 'list');
		
       
		 // getting all footnote list  
  	    $footnoteList = $this->FootnoteObj->find('all')->combine(_FOOTNOTE_NId,_FOOTNOTE_VAL)->toArray();			
			
		$conditions[_MDATA_IUSNID .' IN '] =$returnediusNids;	
		
		// $data = $this->DataObj->find()->where(['Data.Indicator_NId' => '375','Data.IUSNId IN '=>[2599,2469],'Data.Area_NId'=>'19785'])->contain(['Indicator','SubgroupVals','Unit','Footnote'], true)->hydrate(false)->all()->toArray();
	    
		$fields=[];
		$data = $this->DataObj->getDataByParams($fields, $conditions, 'all');
		
		$alldataIusnids =[]; // store all iusnids from data 
		 
		$iusnidData =[];
		 
		foreach($data as $index => $value){
			 echo 'source=='.$value['Source_NId'];
			 echo '<br>';
				 $IUNId = 'IU_'.$value['IUNId'];
				 $iusnidData[$IUNId][$index]['dv']        = $value['Data_Value'];
				 $iusnidData[$IUNId][$index]['src']       = $classificationArray[$value['Source_NId']]['IC_Name'];
				 $iusnidData[$IUNId][$index]['footnote']  = $footnoteList[$value['FootNote_NId']];
				 $iusnidData[$IUNId][$index]['tp']        = $timeperiodList[$value['TimePeriod_NId']];
				 
				 $iusnidData[$IUNId][$index]['sGid']      = $iusNids['sg'][$value['Subgroup_Val_NId']][0]; //gid 
				 $iusnidData[$IUNId][$index]['sName']     = $iusNids['sg'][$value['Subgroup_Val_NId']][1]; // name 
				 
				 $iusnidData[$IUNId][$index]['iusnid']    = $value['IUSNId'];
				 $alldataIusnids[]                        = $value['IUSNId'];
				 $alldataIndicators[$value['IUSNId']]     = $value['Indicator_NId']; // storing ind index w.r.t iusnids
				 $alldataUnits[$value['IUSNId']]          = $value['Unit_NId']; // storing unit index w.r.t iusnids
				 
				/*
  				 $iusnidData[$value['IUNId']][$value['IUSNId']]['dv']      = $value['Data_Value'];
				 $iusnidData[$value['IUNId']][$value['IUSNId']]['src']     = $sourceList[$value['Source_NId']];
				 $iusnidData[$value['IUNId']][$value['IUSNId']]['footnote']= $footnoteList[$value['FootNote_NId']];
				 $iusnidData[$value['IUNId']][$value['IUSNId']]['tp']      = $timeperiodList[$value['TimePeriod_NId']];
				 
				 $iusnidData[$value['IUNId']][$value['IUSNId']]['sGid']    = $iusNids['sg'][$value['Subgroup_Val_NId']][0];
				 $iusnidData[$value['IUNId']][$value['IUSNId']]['sName']   = $iusNids['sg'][$value['Subgroup_Val_NId']][1];
				 
				 $iusnidData[$value['IUSNId']]['iusnid']  = $value['IUSNId'];
				 $alldataIusnids[]                        = $value['IUSNId'];
				 $alldataIndicators[$value['IUSNId']]     = $value['Indicator_NId'];
				 $alldataUnits[$value['IUSNId']]          = $value['Unit_NId'];
				 */
				 
		}
		
		
		 $finalArray =[];		
		 
		 pr($returnediusNids);
		
		 foreach($returnediusNids as $index=> $iusnidvalue){
			 // first classification
			 $conditions4 = [_ICIUS_IUSNID.' IN '=> $iusnidvalue];
			 $fields4 = [];
			 //$icData  = $this->IcIusObj->find()->where([_ICIUS_IUSNID.' IN '=> $iusnidvalue])->hydrate(false)->first();
			 $icData  = $this->IcIus->getDataByParams($fields4, $conditions4, 'all');
			 
			 $icnid   = $icData[0]['IC_NId']; 
			
			 $prepareIU = 'IU_'.$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue];// using IU index for array 

			 $finalArray[$icnid]['icName'] = $classificationArray[$icnid]['IC_Name'];
			 $finalArray[$icnid]['iGid']   = $classificationArray[$icnid]['IC_GId'];
			 $finalArray[$icnid]['iu'][$prepareIU]['iname']   = $iusNids['ind'][$alldataIndicators[$iusnidvalue]][1];//name 
			 $finalArray[$icnid]['iu'][$prepareIU]['iGid']    = $iusNids['ind'][$alldataIndicators[$iusnidvalue]][0];
		     $finalArray[$icnid]['iu'][$prepareIU]['uGid']    = $iusNids['unit'][$alldataUnits[$iusnidvalue]][0];
			 $finalArray[$icnid]['iu'][$prepareIU]['uName']   = $iusNids['unit'][$alldataUnits[$iusnidvalue]][1];
			 
			 if(in_array($iusnidvalue,$alldataIusnids)==true){
				 $finalArray[$icnid]['iu'][$prepareIU]['subgrps'] = $iusnidData[$prepareIU];
			 }else{
				
				  
				  
			 }
	
			 /*
			 $icData  = $this->IcIusObj->find()->where([_ICIUS_IUSNID.' IN '=> $iusnidvalue])->hydrate(false)->first();
			 $icnid = $icData['IC_NId'];	
			 $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['icName'] = $classificationArray[$icnid]['IC_Name'];
			 $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['iGid']   = $classificationArray[$icnid]['IC_GId'];
			 $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['iu']['iname']   = $iusNids['ind'][$alldataIndicators[$iusnidvalue]][1];//name 
			 $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['iu']['iGid']    = $iusNids['ind'][$alldataIndicators[$iusnidvalue]][0];
		     $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['iu']['uGid']    = $iusNids['unit'][$alldataUnits[$iusnidvalue]][0];
			 $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['iu']['uName']   = $iusNids['unit'][$alldataUnits[$iusnidvalue]][1];
			 //$sbgrp = $finalArray[$index]['iu']['subgrps'];
			 if(in_array($iusnidvalue,$alldataIusnids)==true){
								//pr($iusnidData[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]);die;
				 $finalArray[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]]['iu']['subgrps'] = $iusnidData[$alldataIndicators[$iusnidvalue].'_'.$alldataUnits[$iusnidvalue]];

			 }else{
				
				  
				  
			 }
			 */
			 
		 }
	
		
		
		 pr($finalArray);
		 die('finalArray');
		 
		
		
		 
	  }

    /**
    * getDataByIds method
    *
    * @param array $conditions Conditions on which to search. {DEFAULT : empty}
    * @param array $fields Fields to fetch. {DEFAULT : empty}
    * @return void
    */
    public function getDataByIds($ids = null, $fields = [], $type = 'all' )
    {
		
        return $this->AreaObj->getDataByIds($ids, $fields, $type);
    }


    /**
     * getDataByParams method for Areas
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all')
    {				        
        return $this->AreaObj->getDataByParams($fields, $conditions, $type);
    }
	
	
	public function exportArea($fields, $conditions,$module='Area'){
		
		
		$authUserId =$this->Auth->User('id');   
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();


		$returnFilename = _TPL_Export_. _MODULE_NAME_AREA . '_' . $authUserId . '_' .date('Y-m-d'). '.xls';		

        $rowCount = 1;
        $firstRow = ['A' => 'AreaId', 'B' => 'AreaName', 'C' => 'AreaLevel', 'D' => 'AreaGId', 'E' => 'Parent AreaId'];

        foreach ($firstRow as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }
		
		//$conditions=['1'=>'1'];
		 $conditions=[];
		$areadData = $this->AreaObj->getDataByParams( $fields, $conditions,'all');
		
		$startRow = 2;
		$width    = 20;
		

	}
	
	/**
     * getDataByParams for Area Level method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
    */
    public function getDataByParamsAreaLevel(array $fields, array $conditions, $type = 'all')
    {				        

        return $this->AreaLevelObj->getDataByParams($fields, $conditions, $type);
    }

    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null)
    {
        return $this->AreaObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method for Areas 
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = [])
    {	
        return $this->AreaObj->deleteByParams($conditions);
    }
	
	
	/**
     * deleteByParams method for Area Level 
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
	
	
	public function deleteByParamsAreaLevel($conditions = [])
    {				        

        return $this->AreaLevelObj->deleteByParams($conditions);
    }

    /**
     * insertData method for Area
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
    */
    public function insertUpdateAreaData($fieldsArray = [])
    {
        return $this->AreaObj->insertData($fieldsArray);
    }
	
	
	/**
     * insertData method for Area level
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
    */
    public function insertUpdateAreaLevel($fieldsArray = [])
    {
        return $this->AreaLevelObj->insertData($fieldsArray);
    }


    
    
    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = [])
    {
        return $this->AreaObj->insertBulkData($insertDataArray, $insertDataKeys);
    }
	
	
	 /**
     * insertBulkData method for Area level
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkDataAreaLevel($insertDataArray = [], $insertDataKeys = [])
    {
        return $this->AreaLevelObj->insertBulkData($insertDataArray, $insertDataKeys);
    }
	
	
	

    
    /**
     * insertOrUpdateBulkData method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        return $this->AreaObj->insertOrUpdateBulkData($dataArray);
    }
	
	
	/**
     * insertOrUpdateBulkData method for Area level
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkDataAreaLevel($dataArray = [])
    {
        return $this->AreaLevelObj->insertOrUpdateBulkData($dataArray);
    }


    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        return $this->AreaObj->updateDataByParams($fieldsArray, $conditions);
    }
	
	 /**
     * updateDataByParams method for Area level
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParamsAreaLevel($fieldsArray = [], $conditions = [])
    {
        return $this->AreaLevelObj->updateDataByParams($fieldsArray, $conditions);
    }
	
	
	
	/**
    * updateDataByParams method for  Area
    *
    * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
    * @return void
    */
    public function updateDataByParamsArea($fieldsArray = [], $conditions = [])
    {
        return $this->AreaObj->updateDataByParams($fieldsArray, $conditions);
    }
    
    /**
    * find method 
    *
    * @param string $type Query Type
    * @param array $options Extra options
    * @return void
    */
    public function find($type, $options =[], $extra=null) {
        $query =  $this->AreaObj->find($type, $options);
        if(isset($extra['count'])) {
            $data = $query->count();
        }
        else {
            $results = $query->hydrate(false)->all();        
            $data = $results->toArray();    
        }        
        return $data;
         
    }
	
	/*
	 function to add area level if not exists and validations while import for level according to  parent id 
	 returns area level 
	 if $type is New that means parent id don't exist in db and have childs in excel sheet 
	
	*/
	
    public function returnAreaLevel($level='',$parentNid='',$type=''){
		
		$areaFields=[_AREA_AREA_LEVEL];
		$levelFields =[_AREALEVEL_AREA_LEVEL];				
		$data=[];
		
		if($type  ='NEW' && !empty($parentNid) && $parentNid!='-1'){
			
			$areaConditions[_AREA_AREA_ID] = $parentNid;
			$levelValue = $this->AreaObj->getDataByParams($areaFields, $areaConditions, 'all');
			if(!empty($levelValue)){
					$level  = current($levelValue)[_AREA_AREA_LEVEL]+1;
					$levelConditions[_AREALEVEL_AREA_LEVEL]=$level;
					 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
					 if(empty($getlevelDetails)){
						 $data[_AREALEVEL_AREA_LEVEL] = $level;
						 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$level;
						 $this->AreaLevelObj->insertData($data);
						 return  $level;			
					 }else{
						return $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 				
					 }
		    
			}else{
				if(empty($level)){
					$level=2;
				}
				if($level<=1){
					$level=2;
				}
			 $levelConditions[_AREALEVEL_AREA_LEVEL]=$level;
			 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
			 if(empty($getlevelDetails)){
				 $data[_AREALEVEL_AREA_LEVEL] = $level;
				 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$level;
				 $this->AreaLevelObj->insertData($data);
				 return  $level;			
			 }else{
				return $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 				
			 }
			}
			unset($areaConditions);
			unset($levelConditions);
		}
	
     // case 1 when level is empty but parent nid is not  empty 
	 if(empty($level) && !empty($parentNid) && $parentNid!='-1'){
		    
			
			$areaConditions[_AREA_AREA_ID]=$parentNid;
			$levelValue = $this->AreaObj->getDataByParams($areaFields, $areaConditions, 'all');
			if(!empty($levelValue))
			$parentAreaLevel  = current($levelValue)[_AREA_AREA_LEVEL]+1;
		    else
			$parentAreaLevel  =1;	
		    
		
		   if($parentAreaLevel){
		     $levelConditions[_AREALEVEL_AREA_LEVEL]=$parentAreaLevel;
			 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
			 if(empty($getlevelDetails)){
				 $data[_AREALEVEL_AREA_LEVEL] = $parentAreaLevel;
				 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$parentAreaLevel;
				 $this->AreaLevelObj->insertData($data);
				 return  $parentAreaLevel;			
			 }else{
				return $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 				
			 }
		 
		 unset($levelConditions);
		 unset($areaConditions);
		 unset($data);
	 }
	 }
	 
	 // case 2 when level  may be empty or not  but parent nid is empty or -1
	 if((!empty($level)||empty($level)) && (empty($parentNid) || $parentNid=='-1')){
			 $level = 1;
		     $levelConditions[_AREALEVEL_AREA_LEVEL]= $level;
			 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
			 if(empty($getlevelDetails)){
				 $data[_AREALEVEL_AREA_LEVEL] = $level;
				 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$level;
				 $this->AreaLevelObj->insertData($data);
				 return  $level;			
			 }else{
				return $level = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 				
			 }
		 
		 unset($levelConditions);
		 unset($areaConditions);
		  unset($data);
		  
	  }
	  
	 // case 3 when both not empty 
	 if(!empty($level) && !empty($parentNid) && $parentNid!='-1'){
	     
		 $areaConditions[_AREA_AREA_ID]=$parentNid;
		 $parentAreaLevel = 0;
		 $levelValue = $this->AreaObj->getDataByParams($areaFields, $areaConditions, 'all');		
		 $parentAreaLevel  = current($levelValue)[_AREA_AREA_LEVEL];
					 		


		 if($parentAreaLevel >= $level){
			 $finallevel = $parentAreaLevel+1;
			 $levelConditions[_AREALEVEL_AREA_LEVEL] = $finallevel;
			 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
			 if(empty($getlevelDetails)){
				 $data[_AREALEVEL_AREA_LEVEL] = $finallevel;
				 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$finallevel;
				 $this->AreaLevelObj->insertData($data);
				 
				 return  $finallevel;			
			 }else{
				  $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 	
				return 	$finallevel;		
			 }			 
		 }else{			 
			  
			 
			 $levelConditions[_AREALEVEL_AREA_LEVEL] = $level;
			 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
			 if(empty($getlevelDetails)){
				 $data[_AREALEVEL_AREA_LEVEL] = $level;
				 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$level;
				 $this->AreaLevelObj->insertData($data);
				 return  $level;			
			 }else{
				
				return $level = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 				
			 }
		 }		
				unset($levelConditions);
				unset($areaConditions);
				unset($data);
	 }
	 
	 
	 
	 
	 
	}  //  function ends here 

}
