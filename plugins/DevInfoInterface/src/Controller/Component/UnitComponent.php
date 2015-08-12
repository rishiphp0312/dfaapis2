<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Unit Component
 */
class UnitComponent extends Component {

    // The other component your component uses
    public $UnitObj = NULL;
	
	public $components = ['Auth', 'Common',
		'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.CommonInterface',
        'DevInfoInterface.Data',
        'DevInfoInterface.IcIus', 'TransactionLogs'
    ];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->UnitObj = TableRegistry::get('DevInfoInterface.Unit');
		require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');

    }

    /**
     * Get records based on conditions
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->UnitObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = []) {
        $return =  $this->UnitObj->deleteRecords($conditions);
		
		if($return)
        $LogId = $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _UNIT, '', _DONE);
		else
		$LogId = $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _UNIT, '', _FAILED);
		return $return;
    }

    /**
     * Delete records from unit as well as associated records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteUnitdata($uNid = '') {
        $conditions = [];
        $conditions = [_UNIT_UNIT_NID . ' IN ' => $uNid];
        $result = $this->deleteRecords($conditions);

        if ($result > 0) {

            // delete data 
            $conditions = [];
            $conditions = [_MDATA_UNITNID . ' IN ' => $uNid];
            $data = $this->Data->deleteRecords($conditions);

            $conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_UNIT_NID . ' IN ' => $uNid];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');

            //deleet ius             
            $conditions = [];
            $conditions = [_IUS_UNIT_NID . ' IN ' => $uNid];
            $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);


            if (count($getIusNids) > 0) {
                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
                $data = $this->IcIus->deleteRecords($conditions);
            }
            return true;
        } else {
            return false;
        }
    }

    /*
     * check name if name exists in unit table or not
     * return true or false
     */

    public function checkName($unitName = '', $uNid = '') {
        $conditions = $fields = [];
        $fields = [_UNIT_UNIT_NID];
        $conditions = [_UNIT_UNIT_NAME => $unitName];
        if (isset($uNid) && !empty($uNid)) {
            $extra[_UNIT_UNIT_NID . ' !='] = $uNid;
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
     * check gid if exists in unit table or not
     * return true or false
     */

    public function checkGid($gid = '', $uNid = '') {
        $conditions = $fields = [];
        $fields = [_UNIT_UNIT_NID];
        $conditions = [_UNIT_UNIT_GID => $gid];
        if (isset($uNid) && !empty($uNid)) {
            $extra[_UNIT_UNIT_NID . ' !='] = $uNid;
            $conditions = array_merge($conditions, $extra);
        }

        $gidexits = $this->getRecords($fields, $conditions);

        if (!empty($gidexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }

    /**
     * manage unit from unit to update or insert the unit details 
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function manageUnitdata($fieldsArray = []) {
		$unitName =$gid ='';
        $gid = $fieldsArray[_UNIT_UNIT_GID];
        $unitName = $fieldsArray[_UNIT_UNIT_NAME]= trim($fieldsArray[_UNIT_UNIT_NAME]);
        $uNid = (isset($fieldsArray[_UNIT_UNIT_NID])) ? $fieldsArray[_UNIT_UNIT_NID] : '';
        if(empty($gid)){
			//return ['error' => _ERR140];  // gid emty
			$gid = $this->CommonInterface->guid();
			
		}else{			
			$validGid = $this->Common->validateGuid(trim($gid));
			if($validGid == false){
				return ['error' => _ERR142];  // gid emty
			}		
			$checkGid = $this->checkGid($gid, $uNid);
			if ($checkGid == false) {
				return ['error' => _ERR135]; //gid  exists 
			}			
		}
		
		if(empty($unitName)){
			   return ['error' => _ERR143]; //unitName emty
		}else{
			$chkAllowchar = $this->CommonInterface->allowAlphaNumeric($unitName);
			if($chkAllowchar==false){
				 return ['error' => _ERR146]; //allow only space and [0-9 or a-z]
			}
			$checkname = $this->checkName($unitName, $uNid);

			if ($checkname == false) {
				return ['error' => _ERR136]; // name already  exists 
			}
		}
		
       
        if (empty($uNid)) {
            $return = $this->insertData($fieldsArray);
        } else {
            $conditions[_UNIT_UNIT_NID] = $fieldsArray[_UNIT_UNIT_NID];
            unset($fieldsArray[_UNIT_UNIT_NID]);
            //pr($fieldsArray); pr($conditions);
            $return = $this->updateRecords($fieldsArray, $conditions);
        }
        if ($return > 0) {
            return $return;
        } else {
            return ['error' => _ERR100]; //server error 
        }
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = []) {
        $return = $this->UnitObj->insertData($fieldsArray);
        //-- TRANSACTION Log
		if($return)
        $LogId = $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _UNIT, $fieldsArray[_UNIT_UNIT_GID], _DONE);
		else
		$LogId = $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _UNIT, $fieldsArray[_UNIT_UNIT_GID], _FAILED);
			
        return $return;
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->UnitObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $return = $this->UnitObj->updateRecords($fieldsArray, $conditions);
		if($return)
        $LogId = $this->TransactionLogs->createLog(_UPDATE, _TEMPLATEVAL, _UNIT, $fieldsArray[_UNIT_UNIT_GID], _DONE);
		else
		$LogId = $this->TransactionLogs->createLog(_UPDATE, _TEMPLATEVAL, _UNIT, $fieldsArray[_UNIT_UNIT_GID], _FAILED);
		
		return $return 	;
    }

    /**
     * to get  unit details of specific id 
     * 
     * @param srcNid the source nid. {DEFAULT : empty}
     * @return void
     */
    public function getUnitById($uNid = '') {
        $returndata = $data = [];
        $fields = [_UNIT_UNIT_GID, _UNIT_UNIT_NAME, _UNIT_UNIT_NID];
        $conditions = [_UNIT_UNIT_NID => $uNid];
        $data = $this->getRecords($fields, $conditions);
        if (!empty($data)) {

            $data = current($data);
            $returndata = ['uNid' => $data[_UNIT_UNIT_NID], 'uGid' => $data[_UNIT_UNIT_GID], 'uName' => $data[_UNIT_UNIT_NAME]];
        }
        return $returndata;
    }
	
	/**
     * export the unit details to excel 
	*/
    public function exportUnitDetails($dbId='') {
		
		$width    	= 50;
        $dbId      	= (isset($dbId))?$dbId:'';
        $dbDetails 	= $this->Common->parseDBDetailsJSONtoArray($dbId);
        $dbConnName = $dbDetails['db_connection_name'];
        $dbConnName = str_replace(' ', '-', $dbConnName);
		
        $authUserId 	= $this->Auth->User('id');
        $objPHPExcel 	= new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();

       // $returnFilename = $dbConnName. _DELEM4 . _MODULE_NAME_UNIT ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = $dbConnName. _DELEM4 . _UNITEXPORT_FILE ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = str_replace(' ', '-', $returnFilename);
        $rowCount 		= 1;
        $firstRow 		= ['A' => 'Unit Details'];
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
        $secRow = ['A' => 'Unit Name', 'B' => 'Unit Gid'];
        $objPHPExcel->getActiveSheet()->getStyle("A$rowCount:B$rowCount")->getFont()->setItalic(true);
        foreach ($secRow as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }

        $returndata = $data = [];
        $fields = [_UNIT_UNIT_GID, _UNIT_UNIT_NAME];
        $conditions = [];
        $unitData = $this->getRecords($fields, $conditions);
        $startRow = 5;
        foreach ($unitData as $index => $value) {

            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_UNIT_UNIT_NAME])) ? $value[_UNIT_UNIT_NAME] : '' )->getColumnDimension('A')->setWidth($width);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_UNIT_UNIT_GID])) ? $value[_UNIT_UNIT_GID] : '')->getColumnDimension('B')->setWidth($width);
			$startRow++;
        }
		
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $saveFile = _UNIT_PATH . DS .$returnFilename;
        $saved = $objWriter->save($saveFile);
         // if($saved)
		return $saveFile;

    }

	

}
