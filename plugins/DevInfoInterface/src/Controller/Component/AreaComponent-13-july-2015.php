<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;


/**
 * Area Component
 */
class AreaComponent extends Component
{
    
    // The other component your component uses
    public $components = ['Auth'];
    public $AreaObj = NULL;
    public $AreaLevelObj = NULL;
 
	public function initialize(array $config)
    {
       // parent::initialize($config);
        $this->AreaObj = TableRegistry::get('DevInfoInterface.Areas');
        $this->AreaLevelObj = TableRegistry::get('DevInfoInterface.AreaLevel');
		require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');

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

		$returnFilename = _TPL_Export_. $module . '_' . $authUserId . '_' . date('Y-m-d') . '.xls';		
        $rowCount = 1;
        $firstRow = ['A' => 'AreaId', 'B' => 'AreaName', 'C' => 'AreaLevel', 'D' => 'AreaGId', 'E' => 'Parent AreaId'];

        foreach ($firstRow as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }
		
		//$conditions=['1'=>'1'];
		 $conditions=[];
		$areadData = $this->AreaObj->getDataByParams( $fields, $conditions,'all');
		
		$startRow = 2;
		foreach ($areadData as $index => $value) {
			
			
			
			$newconditions =[_AREA_AREA_NID => $value[_AREA_PARENT_NId]];
			$newfields =[_AREA_AREA_ID];
        	$parentnid= $this->getDataByParams($newfields,$newconditions);
			
			if($value[_AREA_PARENT_NId]!='-1')   //case when not empty or -1
			$parentnid= current($parentnid)[_AREA_AREA_ID];
			else
		    $parentnid= '-1';
		
			$objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_AREA_AREA_ID])) ? $value[_AREA_AREA_ID] : '' );
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_AREA_AREA_NAME])) ? $value[_AREA_AREA_NAME] : '');
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($value[_AREA_AREA_LEVEL])) ? $value[_AREA_AREA_LEVEL] : '');
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($value[_AREA_AREA_GID])) ? $value[_AREA_AREA_GID] : '' );
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $startRow, (isset($parentnid)) ? $parentnid : '' );
			$startRow++;		
			
		}
	
	    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		header('Content-Type: application/vnd.ms-excel;');
		header('Content-Disposition: attachment;filename='.$returnFilename);
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		exit;


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
    public function find($type, $options =[]) {
        $query =  $this->AreaObj->find($type, $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();
        return $data;
         
    }
	
	/*
	 function to add area level if not exists and validations while import for level according to  parent id 
	 returns area level 
	
	*/
	
    public function returnAreaLevel($level='',$parentNid=''){
		
		$areaFields=[_AREA_AREA_LEVEL];
		$levelFields =[_AREALEVEL_AREA_LEVEL];

		$data=[];
		//$level=1;
		//$parentNid='IND030001005';
     // case 1 when level is empty but parent nid is not  empty 
	 if(empty($level) && !empty($parentNid) && $parentNid!='-1'){
			 $areaConditions[_AREA_AREA_ID]=$parentNid;
			 $levelValue = $this->AreaObj->getDataByParams($areaFields, $areaConditions, 'all');
			 $parentAreaLevel  = current($levelValue)[_AREA_AREA_LEVEL]+1;
		
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
		 $levelValue = $this->AreaObj->getDataByParams($areaFields, $areaConditions, 'all');
		 $parentAreaLevel  = current($levelValue)[_AREA_AREA_LEVEL];
		
		 if($parentAreaLevel <= $level){
			 $finallevel = $parentAreaLevel+1;
			 $levelConditions[_AREALEVEL_AREA_LEVEL]=$finallevel;
			 $getlevelDetails   = $this->AreaLevelObj->getDataByParams($levelFields, $levelConditions, 'all');
			 if(empty($getlevelDetails)){
				 $data[_AREALEVEL_AREA_LEVEL] = $finallevel;
				 $data[_AREALEVEL_LEVEL_NAME] = _LevelName.$finallevel;
				 $this->AreaLevelObj->insertData($data);
				 return  $finallevel;			
			 }else{
				return $finallevel = current($getlevelDetails)[_AREALEVEL_AREA_LEVEL]; 				
			 }
		 }else{
			 $levelConditions[_AREALEVEL_AREA_LEVEL]=$level;
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
