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
	
	
	
	/**
     * export the subgroup Val details to excel 
	*/	
	public function exportSubgroupValDetails($status,$dbId='') {
		
		$width    	= 50;
        $dbId      	= (isset($dbId))?$dbId:'';
        $dbDetails 	= $this->Common->parseDBDetailsJSONtoArray($dbId);
        $dbConnName = $dbDetails['db_connection_name'];
        $dbConnName = str_replace(' ', '-', $dbConnName);
        $resultSet =[];
		
		$conditions=[];
		$fields = [_SUBGROUP_VAL_SUBGROUP_VAL_GID, _SUBGROUP_VAL_SUBGROUP_VAL,_SUBGROUP_VAL_SUBGROUP_VAL_NID];
		$resultSet 		=	$this->getRecords($fields,$conditions,'all');	
		$newarray = [];
		$resultSbgrpNids = [];
		foreach($resultSet as $value){
			
			$nid = $value[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
			$fields = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID,SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
			$conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID=>$nid];
			$resultSbgrpNids[$nid]	=	$this->SubgroupValsSubgroup->getRecords($fields,$conditions,'list');	
		}
		$fields = [];
		$fields = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME];
			$conditions = [];
		$resultSbgrpList		=	$this->Subgroup->getRecords($fields,$conditions,'list');
		$newarray =[];
		foreach($resultSet as $value){
			
			$nid = $value[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
			/*$fields = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID,SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID];
			$conditions = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID=>$nid];
			$resultSbgrpNids[$nid]	=	$this->SubgroupValsSubgroup->getRecords($fields,$conditions,'list');	*/
			foreach($resultSbgrpNids[$nid]	as $sg){
				//$newarray[]['sgdetails']['	']	$resultSbgrpList[$sg];
				$fields = [];
				$fields = [_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_TYPE];
				$conditions = [_SUBGROUP_SUBGROUP_NID=>$sg];
				$resultSbgrpTypeId		=	$this->Subgroup->getRecords($fields,$conditions,'all');
				$fields = [];
				$fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NAME,_SUBGROUPTYPE_SUBGROUP_TYPE_GID];
				$conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID=>$resultSbgrpTypeId[0][_SUBGROUP_SUBGROUP_TYPE]];
				$dimName =  $this->SubgroupType->getRecords($fields,$conditions,'all');
				$newarray['sgdetails'][]['svalgid']=$value[_SUBGROUP_VAL_SUBGROUP_VAL_GID];
				$newarray['sgdetails'][]['svalName']=$value[_SUBGROUP_VAL_SUBGROUP_VAL];
				$newarray['sgdetails'][]['sName']=$dimName[0][_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
				$newarray['sgdetails'][]['sgid']=$dimName[0][_SUBGROUPTYPE_SUBGROUP_TYPE_GID];
				//$newarray['sgdetails']=$value;

			}
			//$newarray[]['sgdetails']=$value;
		}
		pr($newarray);

		pr($resultSet);die;
        $authUserId 	= $this->Auth->User('id');
        $objPHPExcel 	= new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();

       // $returnFilename = $dbConnName. _DELEM4 . _MODULE_NAME_UNIT ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = $dbConnName. _DELEM4 . _INDICATOREXPORT_FILE ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = str_replace(' ', '-', $returnFilename);
        $rowCount 		= 1;
        $firstRow 		= ['A' => 'Indicator Details'];
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
		if($status==true){
			$secRow = ['A' => 'Indicator Name', 'B' => 'Indicator Gid','C' => 'Unit Name', 'D' => 'Unit Gid','E' => 'Subgroup Name', 'F' => 'Subgroup Gid'];
        }else{
			$secRow = ['A' => 'Indicator Name', 'B' => 'Indicator Gid'];
		}   
		   //     $objPHPExcel->getActiveSheet()->getStyle("A$rowCount:B$rowCount")->getFont()->setItalic(true);

		foreach ($secRow as $index => $value) {
			$objPHPExcel->getActiveSheet()->getStyle("$index$rowCount")->getFont()->setItalic(true);
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }

        $returndata = $data = [];

        $startRow = 6;
		if(!empty($resultSet)){
			
		foreach ($resultSet as $index => $value) {
			
			if($status==true){
				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value['indicator'][_INDICATOR_INDICATOR_NAME])) ? $value['indicator'][_INDICATOR_INDICATOR_NAME] : '' )->getColumnDimension('A')->setWidth($width);
				$objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value['indicator'][_INDICATOR_INDICATOR_GID])) ? $value['indicator'][_INDICATOR_INDICATOR_GID] : '')->getColumnDimension('B')->setWidth($width);
			
				$objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($value['unit'][_UNIT_UNIT_NAME])) ? $value['unit'][_UNIT_UNIT_NAME] : '')->getColumnDimension('C')->setWidth($width);
				$objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($value['unit'][_UNIT_UNIT_GID])) ? $value['unit'][_UNIT_UNIT_GID] : '')->getColumnDimension('D')->setWidth($width);
					
				$objPHPExcel->getActiveSheet()->SetCellValue('E' . $startRow, (isset($value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL])) ? $value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL] : '')->getColumnDimension('E')->setWidth($width);
				$objPHPExcel->getActiveSheet()->SetCellValue('F' . $startRow, (isset($value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID])) ? $value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID] : '')->getColumnDimension('F')->setWidth($width);
			}
			else{
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_INDICATOR_INDICATOR_NAME])) ? $value[_INDICATOR_INDICATOR_NAME] : '11' )->getColumnDimension('A')->setWidth($width);
				$objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_INDICATOR_INDICATOR_GID])) ? $value[_INDICATOR_INDICATOR_GID] : '22')->getColumnDimension('B')->setWidth($width);
			
			}
	
			$startRow++;
        }
		}
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $saveFile = _INDICATOR_PATH . DS .$returnFilename;
        $saved = $objWriter->save($saveFile);
		return $saveFile;

    }

}
