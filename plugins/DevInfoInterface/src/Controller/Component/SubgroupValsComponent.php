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
        'DevInfoInterface.CommonInterface'];
	

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
		//echo 'sgTypes';
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
	
	
	/**
     * export the subgroup Val details to excel 
	*/	
	public function exportSubgroupValDetails($dbId='') {
		
		$width    	= 50;
        $dbId      	= (isset($dbId))?$dbId:'';
        $dbDetails 	= $this->Common->parseDBDetailsJSONtoArray($dbId);
        $dbConnName = $dbDetails['db_connection_name'];
        $dbConnName = str_replace(' ', '-', $dbConnName);
        $resultSet =[];
		//get Subgroup val  Records
		$conditions=[];
		$fields = [_SUBGROUP_VAL_SUBGROUP_VAL_GID, _SUBGROUP_VAL_SUBGROUP_VAL,_SUBGROUP_VAL_SUBGROUP_VAL_NID];
		$resultSet 		=	$this->getRecords($fields,$conditions,'all');	
		$fields = $conditions = $sgvalindexSgIdvalue=[];
		
		
		
		  //get Subgroups type Records  
		$sTypeFields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME, _SUBGROUPTYPE_SUBGROUP_TYPE_GID, _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER];
		$sTypeConditions =[];
		///$sTypeConditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN' => $sTypeNidsArr];
		$sTypeRecords = $this->SubgroupType->getRecords($sTypeFields, $sTypeConditions);
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

}
