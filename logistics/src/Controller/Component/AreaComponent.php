<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;

/**
 * Area Component
 */
class AreaComponent extends Component {

    public $AreaObj = NULL;
    public $AreaLevelObj = NULL;
    public $AreaMapObj = NULL;
    public $AreaMapLayerObj = NULL;
    public $AreaMapMetadataObj = NULL;
    public $AreaFeatureTypeObj = NULL;
    public $processdAreaIds=[];
    public $importFailedRecords=0;
    public $importSuccesRecords=0;
    public $counterAll=2;//start row of records in excel sheet 
    public $errorMessages=[];
    
    public $components = ['Auth', 'Common', 'UserCommon', 'Administration','Package','Items'];
    
    public function initialize(array $config) {
        parent::initialize($config);
        $this->AreaObj = TableRegistry::get('Areas');          
        $this->AreaLevelObj = TableRegistry::get('AreaLevels');
        $this->Shipments = TableRegistry::get('Shipments');
        $this->ShipmentLocations = TableRegistry::get('ShipmentLocations');
        $this->ShipmentPackages = TableRegistry::get('ShipmentPackages');
        $this->ShipmentPackageItems = TableRegistry::get('ShipmentPackageItems');
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
    }

   
    
    /**
     * method  returns array of area details  as per passed conditions 
     * $inputAreaCodes array  all area codes of excel  
     * @type  is by default all else list 
     * used in import 
     */
    public function getAreaDetailsByCode($inputAreaCodes = null, $type = 'all') {
          //code is area id 
        $fields = ['id', 'parent_id', 'code']; // dnt change the order of fields 
        $conditions = array();
        if(!empty($inputAreaCodes))
        $conditions = ['code IN ' => $inputAreaCodes];
        $areaDetails = $this->AreaObj->getRecords($fields, $conditions, $type);
        return $areaDetails;
    }
    
    /**
     * 
     * method to get total no of geographical areas
    */
    public function getAreasCount($conditions = []) {

        $count = 0;
        return $count = $this->AreaObj->getCount($conditions);
    }
    
    
    /**
     returns array with   area ids and parent ids present in sheet

    */
    public function getexcelAreaParentIds($data = [], $insertDataKeys) {

        $insertDataAreaParentids = $getAllExcelAreaids = [];

        foreach ($data as $row => &$value) {

            //$value = array_combine($insertDataKeys, $value);
            $value['code']=$value[0];
            $value['name']=$value[1];
            $value['area_level_id']=$value[2];
            $value['parent_id']=$value[3];
                //pr($insertDataKeys);die;
            unset($value[0]);  unset($value[1]);  unset($value[2]);  unset($value[3]);
            $value = array_filter($value);
            if (array_key_exists(_INSERTKEYS_AREACODE, $insertDataKeys) && !isset($value[$insertDataKeys[_INSERTKEYS_AREACODE]])) {
                unset($value); //unset($newcats); //removing unnecesaary row 
            } else if (isset($value[$insertDataKeys[_INSERTKEYS_AREACODE]])) {
                $getAllExcelAreaids[$row] = $value[$insertDataKeys[_INSERTKEYS_AREACODE]];
                if (!empty($value[$insertDataKeys[_INSERTKEYS_PARENTNID]]))
                    $insertDataAreaParentids[$row] = $value[$insertDataKeys[_INSERTKEYS_PARENTNID]];
            }
        }

        $insertDataAreaParentids = array_unique($insertDataAreaParentids);

        return ['getAllExcelAreaids' => $getAllExcelAreaids, 'insertDataAreaParentids' => $insertDataAreaParentids];
    }
    
    /**
     * 
     * resetChunkAreaData removes the first title row from chunk
     * @$data is the data array 
     */
    public function resetChunkAreaData($data) {
        $limitedRows = [];
        $cnt = 0;
        foreach ($data as $index => $valueArray) {
            if ($index == 1) {
                unset($valueArray);
            }if ($index > 1) {
                foreach ($valueArray as $innerIndex => $innervalueArray) {

                    if ($innerIndex > 3)
                        break;
                    $limitedRows[$index][$innerIndex] = $innervalueArray;

                    unset($innervalueArray);
                    $cnt++;
                }
            }

            unset($valueArray);
        }

        return $limitedRows;
    }

    /*
     *  processAreadata to insert update area records after readfing chunk file
     * @param array $filename chunk File . {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database.work as table columns and keys for insertDataArr {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */

    public function processAreadata($insertDataKeys = null, $insertDataArr = null, $extra = null) {

        $chunkFilename = basename($extra['chunkFilename']);
      
        $areaidswithParentId = $getAllExcelAreaids = $insertDataAreaParentids = [];
        $newinsertDataArr = [];
        $newinsertDataArr = $this->resetChunkAreaData($insertDataArr);     //get area records without using this function         

        $excelIds = $this->getexcelAreaParentIds($newinsertDataArr, $insertDataKeys);// get area codes         
       
        $getAllExcelAreaids = $excelIds['getAllExcelAreaids']; //all excel area codes
        $insertDataAreaParentids = $excelIds['insertDataAreaParentids']; // all excel parent codes  
        
        // $areaidswithparentid getting list which parentnids exists in db  
        $areaidswithParentId = $this->getAreaDetailsByCode($insertDataAreaParentids, 'list');
        //get all aread ids exists in db already
        $getAllDbAreaIds = $this->getAreaDetailsByCode($getAllExcelAreaids, 'list');
      
        // areaidswithparentid contains  parent ids which exist in db 

        if (isset($newinsertDataArr) && !empty($newinsertDataArr)) {
            $finalareaids = [];
            $chkuniqueAreaids = [];
            $allAreaIdsAsSubParent = [];

            foreach ($newinsertDataArr as $row => &$value) {
                $areaidAlradyexistStatus = false;
            
                $allAreblank = false;
                $value['code']=$value[0];
                $value['name']=$value[1];
                $value['area_level_id']=$value[2];
                $value['parent_id']=$value[3];
                //pr($insertDataKeys);die;
                unset($value[0]);  unset($value[1]);  unset($value[2]);  unset($value[3]);
                //$value = array_combine($insertDataKeys, $value);
                $value = array_filter($value);
                $areaNid = '';
                $value['modified_user_id'] = $this->Auth->User('id');
                
           

                if (empty($value)) {
                    $allAreblank = true;
                    //$this->importFailedRecords++;
                    //complete row is empty 
                   
                }elseif (!isset($value[$insertDataKeys['area_code']]) || empty($value[$insertDataKeys['area_code']])) {
                    //case 1 when ignore if area code is blank
                    unset($value);
                    unset($newinsertDataArr[$row]);
                    $this->createErrorLogDetails(false,_AREA_LOG_AREAID_EMPTY,$this->counterAll);
            
                } else if (isset($value[$insertDataKeys['area_code']]) && !empty($value[$insertDataKeys['area_code']])) {
                    // all cases inside this when area id is not empty 
                    $excelAreaId = $value[$insertDataKeys['area_code']]; // area id read from excelof current row  
                    $indexParentAreaId = $insertDataKeys['parent_id'];
                    $desc = '';

                    if (!empty($this->processdAreaIds) && in_array($excelAreaId, $this->processdAreaIds) == true) {
                        $areaidAlradyexistStatus = true;
                    }
                    $this->processdAreaIds[] = $excelAreaId; //used for duplicate check of area ids 
                  
                    if (isset($value[$indexParentAreaId]) && !empty($value[$indexParentAreaId]) && $value[$indexParentAreaId] != _GLOBALPARENT_ID && in_array($value[$indexParentAreaId], $areaidswithParentId) == true) {
                        //case when parent id is not empty and exists in database also 
                        
                        if (empty($value[$insertDataKeys['area_name']]))
                            $value[$insertDataKeys['area_name']] = $excelAreaId;

                        /// call case1 here starts 
                        $params = ['allAreblank' => $allAreblank, 'insertDataKeys' => $insertDataKeys, 'indexParentAreaId' => $indexParentAreaId, 'value' => $value,  'excelAreaId' => $excelAreaId];
                         $this->processAreaCase1($params, $areaidswithParentId, $getAllDbAreaIds, $areaidAlradyexistStatus,$this->counterAll);
                        /// case 1 ends here 
                    } elseif (!empty($value[$indexParentAreaId]) && ($value[$indexParentAreaId] != _GLOBALPARENT_ID) && in_array($value[$indexParentAreaId], $areaidswithParentId) == false) {

                        //case when parent id is not empty and do not exists in database  
                      
                        if (empty($value[$insertDataKeys['area_name']]))
                            $value[$insertDataKeys['area_name']] = $excelAreaId;

                        /// call case2 here starts 					
                        $params = ['allAreblank' => $allAreblank, 'insertDataKeys' => $insertDataKeys, 'indexParentAreaId' => $indexParentAreaId, 'value' => $value,  'excelAreaId' => $excelAreaId];
                       $this->processAreaCase2($params, $getAllDbAreaIds, $areaidAlradyexistStatus,$this->counterAll);
                        /// case 2 ends here 
                    }//case 3 starts here 
                    elseif (empty($value[$indexParentAreaId]) || ($value[$indexParentAreaId] == _GLOBALPARENT_ID)) {
                        //case when parent area id  is empty                         
                        if (empty($value[$insertDataKeys['area_name']]))
                            $value[$insertDataKeys['area_name']] = $excelAreaId;
                        /// call case3 here starts 
                        $params = ['allAreblank' => $allAreblank, 'insertDataKeys' => $insertDataKeys, 'indexParentAreaId' => $indexParentAreaId, 'value' => $value,   'excelAreaId' => $excelAreaId];
                        $this->processAreaCase3($params, $getAllDbAreaIds, $areaidAlradyexistStatus,$this->counterAll);
                        /// case 3 ends here 
                    } else {
                        
                    }
                }// end of if of area id exists 
                    $this->counterAll++;
            }
        }


        $newinsertDataArr = [];
        
        return  ['failedRecords'=>$this->importFailedRecords,'importedRecords'=>$this->importSuccesRecords,'errordetails'=>$this->errorMessages];
        
    }
    
    
    /**
     * processAreaCase1 returns array with area  log details of status and message
       method is called when  parent id is not empty in excel sheet  and exists in database also
     * @param type $params
     * @param type $areaidswithParentId
     * @param type $getAllDbAreaIds
     * @param type $areaidAlradyexistStatus
     * @param type $cnt excel row counter 
     */
       public function processAreaCase1($params, $areaidswithParentId, $getAllDbAreaIds, $areaidAlradyexistStatus,$cnt='') {

        //case when parent id is not empty and exists in database also 
       
        $allAreblank = $params['allAreblank'];
        $insertDataKeys = $params['insertDataKeys']; 
        $excelAreaId = $params['excelAreaId'];
        $indexParentAreaId = $params['indexParentAreaId'];
        $value = $params['value'];     

        if (!array_key_exists($insertDataKeys['area_level_id'], $value)) {
            $level = '';
        } else {
            $level = $value[$insertDataKeys['area_level_id']];
        }
        //returns area level and and any warning if exists 
        $levelDetails = $this->returnAreaLevel($level, $value[$indexParentAreaId]);//check  return data details
        $value[$insertDataKeys['area_level_id']] = $levelDetails['level']; //change area level
        $value[$indexParentAreaId] = array_search($value[$indexParentAreaId], $areaidswithParentId);//get parent nid 

        if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) { //when areacode  already in db 
            // update data here  
            $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // get area nid 
           
        }else {

            $areadbdetails = '';
            $chkAreaId = $this->checkAreaId($excelAreaId);//check area code 
            if (!empty($chkAreaId)) {
                $areadbdetails = current($chkAreaId);
                $areaNid = $areadbdetails['id'];               //get area nid  
            }else {
                // insert if new entry
                $returnid = '';                
                $value['`order`'] = _ORDER;
                $value['visible'] = _VISBLE;
                $value['created_user_id'] = $this->Auth->User('id');                
            }
               
        }
        if ($areaidAlradyexistStatus == false  ) {
            if (!empty($areaNid)) {
                //updateRecords
                $returnid = $this->AreaObj->updateRecords($value, ['id' => $areaNid]); // update  case handled here 
            } else {
                $returnid = $this->AreaObj->saveArea($value); // insert case handled here 
            }
            if ($returnid) {// insert sucess                 
                $this->createErrorLogDetails(true,'','');
            } else { // insert failed 
                if ($allAreblank == false) {                   
                    $this->createErrorLogDetails(false,_AREA_LOG_OPERATION_FAILED,$cnt);
                }
            }
        } else {
                $this->createErrorLogDetails(false,$this->getLogdetails($allAreblank, false),$cnt);               
            
        }
    }

    /**
     *  processAreaCase2 returns array with log details of status and message
      method called  when parent area code  is not empty and do not exists in database
     * @param type $params
     * @param type $getAllDbAreaIds
     * @param type $areaidAlradyexistStatus
     * @param type $cnt
     */
    public function processAreaCase2($params, $getAllDbAreaIds, $areaidAlradyexistStatus, $cnt = '') {
        //  get parent details 
      
        $indexGid = '';
        $allAreblank = $params['allAreblank'];
        $insertDataKeys = $params['insertDataKeys'];
        $excelAreaId = $params['excelAreaId'];
        $indexParentAreaId = $params['indexParentAreaId'];
        $value = $params['value'];     
        $parentchkAreaId =  [];
        $parentareadbdetails = '';

        $parentchkAreaId = $this->checkAreaId($value[$insertDataKeys['parent_id']]);      
        //check parent id exists in db or not 
        if (!empty($parentchkAreaId))
            $parentareadbdetails = current($parentchkAreaId)['id']; //get parent nid   

        if (!empty($parentareadbdetails)) { //when area code in db and  parent also exists due to loop insertion  
            if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) {
                //case when excel area code already exists in database  modify case 
                 $areaNid = array_search($excelAreaId, $getAllDbAreaIds); //get area id 
            }else {  
                
                $chkAreaId = $this->checkAreaId($excelAreaId);
                if (!empty($chkAreaId)) {
                    //case of modifing area details  using  area nid 
                    $areadbdetails = current($chkAreaId);
                    $areaNid = $areadbdetails['id'];  // area nid   
                }else {
                    // case of inserting  area details            
                     $value['`order`'] = _ORDER;
                     $value['visible'] = _VISBLE;
                     $value['created_user_id'] = $this->Auth->User('id');
                }
                //
            }
            $value[$indexParentAreaId] = $parentareadbdetails;//parent nid 
            if (!array_key_exists($insertDataKeys['area_level_id'], $value)) {
                $level = '';
            } else {
                $level = $value[$insertDataKeys['area_level_id']];
            }        
           echo $levelDetails = $this->returnAreaLevel($level, $value[$indexParentAreaId]);
            $value[$insertDataKeys['area_level_id']] = $levelDetails['level'];
            pr($value);die;
            
            if ($areaidAlradyexistStatus == false ) {
                if (!empty($areaNid)) {
                    $returnid = $this->AreaObj->updateRecords($value, ['id' => $areaNid]);
                } else {
                    $returnid = $this->AreaObj->saveArea($value);
                }
                 if ($returnid) {// insert sucess 
                     $this->createErrorLogDetails(true,'','');
                 } else{                     
                    if ($allAreblank == false) {
                         $this->createErrorLogDetails(false,_AREA_LOG_OPERATION_FAILED,$cnt);
                    }
                }
            } else {
                  $this->createErrorLogDetails(false,$this->getLogdetails($allAreblank, false),$cnt);
            }
        } else {
            // when  parent id dont  exists 
            if ($allAreblank == false) {               
                  $this->createErrorLogDetails(false,_AREA_LOG_PARENTID_MISSING,$cnt);
                //parent id not found 
            }
        }
    }

    /**
     *  processAreaCase3 method called  when parent area code   is empty
      
     *
     * @param type $params
     * @param type $getAllDbAreaIds
     * @param type $areaidAlradyexistStatus
     * @param type $cnt row counter 
     */     
    public function processAreaCase3($params, $getAllDbAreaIds, $areaidAlradyexistStatus,$cnt='') {
    
        $allAreblank = $params['allAreblank'];
        $insertDataKeys = $params['insertDataKeys'];        
        $excelAreaId = $params['excelAreaId'];
        $indexParentAreaId = $params['indexParentAreaId'];
        $value = $params['value'];
   
    
        $levelError = false;  // status when level is more than 1 and parent id is blank
        if (!array_key_exists($insertDataKeys['area_level_id'], $value)) {
            $level = '';
        } else {
            $level = (isset($value[$insertDataKeys['area_level_id']]) && !empty($value[$insertDataKeys['area_level_id']])) ? $value[$insertDataKeys['area_level_id']] : _AREAPARENT_LEVEL;
        }

        if ($level > _AREAPARENT_LEVEL) {
            //level if given when parent nid is blank or -1 no insertion will take place 
            $levelError = true;
        }else{
            $levelDetails = $this->returnAreaLevel($level, _GLOBALPARENT_ID);     
            $value[$indexParentAreaId] = _GLOBALPARENT_ID; // value is -1
            $value[$insertDataKeys['area_level_id']] = $levelDetails['level']; // set area level
        }

       
        $conditions = [];
        $fields = [];
        $areadbdetails = '';

        if (!empty($getAllDbAreaIds) && in_array($excelAreaId, $getAllDbAreaIds) == true) { //when areaid in db 
            // update data here 
            $areaNid = array_search($excelAreaId, $getAllDbAreaIds); // 
            

        }else {

            $chkAreaId = $this->checkAreaId($excelAreaId);
            if (!empty($chkAreaId)) {
                $areadbdetails = current($chkAreaId);
                $areaNid = $areadbdetails['id'];                
   
            }else {

                $returnid = '';
                $value['`order`'] = _ORDER;
                $value['visible'] = _VISBLE;
                $value['created_user_id'] = $this->Auth->User('id');
                
            }
        }
        ///

        if ($areaidAlradyexistStatus == false && $levelError == false ) {

            if (!empty($areaNid)) {
                $returnid = $this->AreaObj->updateRecords($value, ['id' => $areaNid]);
            } else {
                $returnid = $this->AreaObj->saveArea($value);
            }

             if ($returnid) {// insert sucess 
                    $this->createErrorLogDetails(true,'','');
                } else{
                     
                if ($allAreblank == false) {
                    $this->createErrorLogDetails(false,_AREA_LOG_OPERATION_FAILED,$cnt);
                    
                }
            }
        } else {
                  
                   $this->createErrorLogDetails(false,$this->getLogdetails($allAreblank, $levelError),$cnt);
              }
    }
    
    
    /*
      function to add area level if not exists and validations while import for level according to  parent id
      returns array of area level and any error if exists
      if $type is New that means parent id don't exist in db and have childs in excel sheet
     */

    public function returnAreaLevel($level = '', $parentAreaCode = '') {
        
        $errorFlag   = false;$levelId='';
        $areaFields  = ['area_level_id'];
        $levelFields = ['level'];        
	$data        = [];
        $returnarray = array('level' => '', 'error' => $errorFlag);
        $areaConditions['code'] = $parentAreaCode; //parent area code 

        // case 1 when level is empty but parent code  is not  empty or not equal to -1
        if (empty($level) && !empty($parentAreaCode) && $parentAreaCode != _GLOBALPARENT_ID) {

            /*
             * get level id of parent nid in area table 
             * get level id of level+1 from level table
             * check level+1 in level table if exists get id else add and get id  
             */          
            $levelValue = $this->AreaObj->getRecords($areaFields, $areaConditions, 'all');
            if (!empty($levelValue)){
                $parentAreaLevel = current($levelValue)['area_level_id'];//get parent level id 
                $levelId = $this->saveGetAreaLevelId($parentAreaLevel,''); //return level id 
                
            }
            unset($areaFields); unset($areaConditions);                   
            return $returnarray = array('level' => $levelId, 'error' => $errorFlag);           
            
        }

        // case 2 when level  may be empty or not  but parent code  is empty or -1
        if ((!empty($level) || empty($level)) && (empty($parentAreaCode) || $parentAreaCode == _GLOBALPARENT_ID)) {
            if (!empty($level) && $level != _AREAPARENT_LEVEL) {
                $errorFlag = true;
            }        
            $levelId = $this->saveGetAreaLevelId('',_AREAPARENT_LEVEL); //return level id 
            return $returnarray = array('level' => $levelId, 'error' => $errorFlag);
        }

        // case 3 when both are not empty 
        if (!empty($level) && !empty($parentAreaCode) && $parentAreaCode != _GLOBALPARENT_ID) {

            
            $levelValue = $this->AreaObj->getRecords($areaFields, $areaConditions, 'all');
            if(!empty($levelValue)){
                $parentAreaLevel = current($levelValue)['area_level_id'];  //get parent level id           
                $levelId = $this->saveGetAreaLevelId($parentAreaLevel,''); //return level id 
            }
            unset($areaFields); unset($areaConditions); 
            return $returnarray = array('level' => $levelId, 'error' => $errorFlag);    
           
         
           
        }
    }

    
    
    /*
     * CHECK WHETHER PASSED AREA ID EXISTS OR NOT 
     * @areaId is the area id 
     * returns array of passed area id 
     */

    public function checkAreaId($areaId = '') {

        $conditions = ['code' => $areaId];
        $fields = ['code', 'id'];
        return $chkAreaId = $this->AreaObj->getRecords($fields, $conditions);
    }
    
     /*
     * 
     * method to  get error log details
     *  returns comments with status of failed or warning
     * all status are passed 
     * $allAreblank , $levelError,$gidStatus,$gidFormat  boolean true/false 
     */
    public function getLogdetails($allAreblank, $levelError) {
        if ($allAreblank == false) {
            /////////
            if ($levelError == true) {
                return  _AREA_LOG_PARENTID_MISSING; // parent missing
            } else {
                return  _AREA_LOG_AREAID_DUPLICATE; //area id is duplicate 
                //duplicate case           
            }
            //
        }
    }
    
    /**
     * method to create error log response with no of records failed with issues 
     * @param type $status
     * @param type $msg
     * @param type $cnt
     */
    public function createErrorLogDetails($status=true,$msg='',$cnt=0){
        if($status ==true){
            $this->importSuccesRecords++;
        }else{
            $this->importFailedRecords++; //returns error log details 
            $this->errorMessages[$cnt]= $msg;
        }
    }
    
    
     /**
     * exportArea method for exporting area details 
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function exportArea() {
        $levelDetails =[];
        $authUserId = $this->Auth->User('id');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $returnFilename = _MODULE_NAME_AREA . '_' . date('Y-m-d-H-i-s') .'_'.$authUserId. '.xls';
        $returnFilename = str_replace(' ', '-', $returnFilename);
        $rowCount = 1;
        $width = 30;
        $firstRow = ['A' => 'AreaCode', 'B' => 'AreaName', 'C' => 'AreaLevel', 'D' => 'Parent AreaId'];
        $objPHPExcel->getActiveSheet()->getStyle("A1:G1")->getFont()->setItalic(true);
        foreach ($firstRow as $index => $value) {
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value)->getColumnDimension($index)->setWidth($width);
        }
      
        $fields=[];
        $conditions= ['code <>'=>''];
        $areadData = $this->AreaObj->getRecords($fields,$conditions , 'all');
       
        $startRow = 2;
        
        if (!empty($areadData)) {
            foreach ($areadData as $index => $value) {
                if($value['code']!=''){
                    $newconditions = ['id' => $value['parent_id']];
                    $newfields = ['code'];
                    $parentnid = $this->AreaObj->getRecords($newfields, $newconditions);
                    if ($value['parent_id'] != _GLOBALPARENT_ID)   //case when not empty or -1
                        $parentnid = current($parentnid)['code'];
                    else
                        $parentnid = '';
                    if(isset($value['area_level_id']) && !empty($value['area_level_id']))
                    $levelDetails = $this->getLevelDetails($value['area_level_id']);

                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value['code'])) ? $value['code'] : '' )->getColumnDimension('A')->setWidth($width);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value['name'])) ? $value['name'] : '')->getColumnDimension('B')->setWidth($width);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($levelDetails['code'])) ? $levelDetails['code'] : '')->getColumnDimension('C')->setWidth($width - 20);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($parentnid)) ? $parentnid : '' )->getColumnDimension('E')->setWidth($width + 5);
                    $startRow++;    
                }
                
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $saveFile = _AREA_PATH . DS . $returnFilename;
        $saved = $objWriter->save($saveFile);
        return $saveFile;
    }
    
    /**
     * 
     * @param type $code
     * @id level id 
     */
    public function checkLevelCode($code='',$id=''){
        //$parentnid = $this->AreaObj->getRecords($newfields, $newconditions);
        $conditions=[];
        
        if($code!='')
         $conditions['level']=$code;
        
        if($id!='')
         $conditions['id !=']=$id;
        
        $existingRecord = $this->AreaLevelObj->getRecords(['level'],$conditions , 'all', ['first' => true]);
        if(!empty($existingRecord)){
            return false;
        }
        return true;
    }
    
     /**
     * 
     * @param type $code
     */
    public function checkLevelName($name='',$id=''){
        //$parentnid = $this->AreaObj->getRecords($newfields, $newconditions);
        $conditions=[];
        
        if($name!='')
         $conditions['name']=$name;
        
        if($id!='')
         $conditions['id !=']=$id;
       
        $existingRecord = $this->AreaLevelObj->getRecords(['name'],$conditions , 'all', ['first' => true]);
        if(!empty($existingRecord)){
            return false;
        }
        return true;
    }
    
    
    
    
    /**
     * method to validate level
     * @param type $data array 
     */
    public function validateLevel($fields=[]){
        
          if (!empty($fields) && count($fields) > 0) {

            $code = (isset($fields['code'])) ? trim($fields['code']) : '';
            $levelName = (isset($fields['levelName'])) ? trim($fields['levelName']) : '';
            $lvlId = (isset($fields['id'])) ? trim($fields['id']) : '';
            /*if(empty($fields['statusId'])){
                   return ['errCode' => _ERR105]; //missing parameters
            }
            */
            if (empty($code)) {
                return ['errCode' => _ERR147]; //level code is empty 
            }else{
                
                $validlength = $this->Common->checkBoundaryLength($code, _LEVEL_CODE_LENGTH); //3 only
                if ($validlength == false) {
                    return ['errCode' => _ERR150];              //level code length exceeded
                }
                $return = $this->checkLevelCode($code,$lvlId);
                if($return==false){
                    return ['errCode' => _ERR149];              //level code already exist 
                }
              
            }
            if (empty($levelName)) {
                return ['errCode' => _ERR148]; //level name is empty 
            } else {
                $validlength = $this->Common->checkBoundaryLength($levelName, _LEVEL_NAME_LENGTH); //50 only
                if ($validlength == false) {
                    return ['errCode' => _ERR152];               //level  name  length exceeded
                }
                $chkname = $this->checkLevelName($levelName, $lvlId); //if false means name  exists 
                if ($chkname ==false) {
                    return ['errCode' => _ERR151];
                }
            }
            ///
        } else {
            return ['errCode' => _ERR105];
        }
    }
    
    
    /**
     * saveAreaLevel - Insert if not exists
     * 
     * 
     * @return $data
     */
    public function saveAreaLevel($data=[]) {
                $savedata=[];
                $errorCode = $this->validateLevel($data);//validate data 
                if(isset($errorCode['errCode'])){
                    return ['error'=>$errorCode['errCode']];
                }
                
                $savedata['level'] = $data['code'];
                $savedata['name'] = ucfirst($data['levelName']);
                $savedata['visible'] = $data['statusId'];
                $savedata['comments'] = (isset($data['comments']) && !empty($data['comments']))?$data['comments']:'';                             
                                
                if (empty($data['id'])) {   
                 $savedata['created_user_id'] = $this->Auth->User('id');
                 $savedata['modified_user_id'] = $this->Auth->User('id');
                }else{
                 $savedata['modified_user_id'] = $this->Auth->User('id');
                }
                if (!empty($data['id'])) {
                  
                   $areaLevelNid = $this->AreaLevelObj->updateRecords($savedata,['id'=>$data['id']]); 
                   unset($data['id']);
                }else{
                   $areaLevelNid = $this->AreaLevelObj->insertData($savedata); 
                }
                if($areaLevelNid>0){
                    return true;
                }else{
                  return ['errorCode' => _ERR100];      // user not modified due to database error 
                }
               
    }
    
    /**
     * method to delete area levels and its corresponding data
     * $levelId is the level id 
     */
    public function deleteLevel($levelId = '') {
        if ($levelId != '') {
            $conditions = ['id' => $levelId];
            $remlevels = $this->AreaLevelObj->deleteRecords($conditions);
            //$remlevels =1;
            if ($remlevels > 0) {
                
                $conditions=[];
                $conditions=['area_level_id'=>$levelId];
                $areaIds = $this->AreaObj->getRecords(['id','id'],$conditions,'list');
                $deleteareaIds = $this->AreaObj->deleteRecords($conditions);
                $this->deleteShipment($areaIds);
                /*
                $conditions=[];
                $conditions=['OR'=>[ 'to_area_id  IN '=>$areaIds,'from_area_id  IN '=>$areaIds]];
                $shipmentIds = $this->Shipments->getRecords(['id','id'],$conditions,'list');
                $deleteshipmentIds = $this->Shipments->deleteRecords($conditions);
              
                $conditions=[];
                $conditions=['shipment_id  IN '=>$shipmentIds];
                $deleteshipmentPkgsIds = $this->ShipmentPackages->deleteRecords($conditions);               
                $deleteshipmentPkgsItemsIds = $this->ShipmentPackageItems->deleteRecords($conditions);
                $deleteshipmentLocationIds = $this->ShipmentLocations->deleteRecords($conditions);
                */
                return true;
            } else {
                return ['errorCode' => _ERR100];      // user not modified due to database error 
            }
        } else {
            return ['errorCode' => _ERR105];      // user not modified due to database error 
        }
    }
    
    
     /**
     * method to get level list 
     *
     */
    public function getLevelList() {
            $levelList=[];$modifyBy='';
            $levelData = $this->AreaLevelObj->getRecords([] ,['visible'=>_VISBLE], 'all');
            if (!empty($levelData) ) {
            foreach($levelData as $index=>$value){
                
                
                 if($value['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($value['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }

               
                $levelList[]=['code'=>$value['level'],'levelName'=>$value['name'],'modifyBy'=>$modifyBy,'modified'=>$value['modified'],
                    'statusId'=>$value['visible'],'id'=>$value['id']];
                $modifyBy='';
            }
                
            }
            return $levelList;
        
    }
    
    /**
     * method to get level details 
     *$levelId is level id 
    */
    public function getLevelDetails($levelId=''){
        
        $levelList=[];$modifyBy='';
        if($levelId!=''){
            $levelData = $this->AreaLevelObj->getRecords([],[['id'=>$levelId,'visible'=>_VISBLE]] , 'all', ['first' => true]);
            if(!empty($levelData)){     
                
                if(!empty($levelData['modified_user_id']!='')){
                   $usrdetails = $this->UserCommon->getUserDetailsById($levelData['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }

                $levelList=['code'=>$levelData['level'],'levelName'=>$levelData['name'],'modifyBy'=>$modifyBy,'modified'=>$levelData['modified'],
                    'statusId'=>$levelData['visible'],'id'=>$levelData['id'],'comments'=>$levelData['comments']];
                
            }
           
         return $levelList;   
        }else{
            return ['errorCode' => _ERR105];
        }
    }
    
    /**
     *  get all records of area
     */
    public function getAreaList(){
            $areaList=[];$modifyBy='';
            $areaData = $this->AreaObj->getRecords([],['visible'=>_VISBLE] , 'all');
            if (!empty($areaData) ) {
            foreach($areaData as $index=>$value){
                
                 if($value['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($value['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }
                
                 
               
                $areaList[]=['code'=>$value['code'],'Name'=>$value['name'],'areaId' => $value['parent_id'],'id' => $value['id'],'modifyBy'=>$modifyBy,'modified'=>$value['modified'],
                    'statusId'=>$value['visible'],'levelId'=>$value['area_level_id']];
                $modifyBy='';
            }
                
            }
            return $areaList;
    }
    
    
    
    /**
     * method to get area details 
     * $areaId is area id 
     */
    public function getAreaDetails($areaId = '') {
        ///'visible'=>_VISBLE
        $areaList=[];
        $parentName = $modifyBy = '';
        if ($areaId != '') {
            $areaData = $this->AreaObj->getRecords([], [['id' => $areaId,'visible'=>_VISBLE]], 'all', ['first' => true]);
            if (!empty($areaData)) {
                if($areaData['modified_user_id']!=''){
                   $usrdetails = $this->UserCommon->getUserDetailsById($areaData['modified_user_id']);
                    if (!empty($usrdetails)) {
                        $modifyBy = $usrdetails['firstName'] . _DELEM7 . $usrdetails['lastName'];
                    } 
                }
                $parentAreaDt  =  $this->AreaObj->getRecords([], [['id' => $areaData['parent_id'],'visible'=>_VISBLE]], 'all', ['first' => true]);
                //$this->getAreaDetails($areaData['parent_id']);
                if(!empty($parentAreaDt)){
                        $parentName = $parentAreaDt['name'];
                }
                $areaList = ['code' => $areaData['code'],'Name' => $areaData['name'], 'areaId' => $areaData['parent_id'],
                    'id' => $areaData['id'],'modifyBy' => $modifyBy, 'modified' => $areaData['modified'],'comments' => $areaData['comments'],
                     'statusId' => $areaData['visible'],'areaName' => $parentName,
                    ];
            }

            return $areaList;
        } else {
            return ['errorCode' => _ERR105];
        }
    }
    
    /**
     * saveAreaDetails - Insert if not exists
     * 
     * 
     * @return $data
     */
    public function saveAreaDetails($data=[]) {
                $level= '';$leveId =  '';
                $savedata=[];
                $errorCode = $this->validateArea($data);//validate data 
                if(isset($errorCode['errCode'])){
                    return ['error'=>$errorCode['errCode']];
                }
                
                $savedata['code'] = $data['code'];
                $savedata['name'] = ucfirst($data['Name']);
                $savedata['visible'] = $data['statusId'];
                $savedata['comments'] = (isset($data['comments']) && !empty($data['comments']))?$data['comments']:'';
                if(empty($data['areaId'])){
                   // $level=_AREAPARENT_LEVEL;
                    $savedata['parent_id'] =_GLOBALPARENT_ID;
                    $leveId =  $this->saveGetAreaLevelId('',_AREAPARENT_LEVEL);
                }else{
                    $areaData = $this->AreaObj->getRecords([], [['id' => $data['areaId']]], 'all', ['first' => true]);
                    if (!empty($areaData)) {                            
                           $leveId = $this->saveGetAreaLevelId($areaData['area_level_id']);
                           $savedata['parent_id'] =$areaData['id'];
                    }else{
                         return ['error'=>_ERR105];
                    }
                }
                  $savedata['area_level_id'] =$leveId;
                
                
                if (empty($data['id'])) {   
                 $savedata['created_user_id'] = $this->Auth->User('id');
                 $savedata['modified_user_id'] = $this->Auth->User('id');
                }else{
                 $savedata['modified_user_id'] = $this->Auth->User('id');
                }
//                pr($savedata);die;
                if (!empty($data['id'])) {                  
                   $areaNid = $this->AreaObj->updateRecords($savedata,['id'=>$data['id']]); 
                   unset($data['id']);
                }else{
                   $areaNid = $this->AreaObj->saveArea($savedata); 
                }
                if($areaNid>0){
                    return true;
                }else{
                  return ['errorCode' => _ERR100];      // user not modified due to database error 
                }
               
    }
    
     /**
     * method to validate area
     * @param type $data array 
     */
    public function validateArea($fields=[]){
        
          if (!empty($fields) && count($fields) > 0) {

            $code = (isset($fields['code'])) ? trim($fields['code']) : '';
            $Name = (isset($fields['Name'])) ? trim($fields['Name']) : '';
            $parentId = (isset($fields['areaId'])) ? trim($fields['areaId']) : '';//parent id
            $areaId = (isset($fields['id'])) ? trim($fields['id']) : '';
            /*if(empty($fields['statusId'])){
                   return ['errCode' => _ERR105]; //missing parameters
            }
            */
            if (empty($code)) {
                return ['errCode' => _ERR153]; //area code is empty 
            }else{
                
                $validlength = $this->Common->checkBoundaryLength($code, _AREA_CODE_LENGTH); //20 only
                if ($validlength == false) {
                    return ['errCode' => _ERR156];              //area code length exceeded 
                }
                $return = $this->checkAreaCode($code,$areaId);
                if($return==false){
                    return ['errCode' => _ERR157];              //area code already exist 
                }
              
            }
            if (empty($Name)) {
                return ['errCode' => _ERR155]; //area name is empty 
            } else {
                $validlength = $this->Common->checkBoundaryLength($Name, _AREA_NAME_LENGTH); //50 only
                if ($validlength == false) {
                    return ['errCode' => _ERR154];               //area name length exceeded
                }
                
            }
            ///
        } else {
            return ['errCode' => _ERR105];
        }
    }
    
    
     /**
     * 
     * @param type $code
     * @id level id 
     */
    public function checkAreaCode($code='',$id=''){
        //$parentnid = $this->AreaObj->getRecords($newfields, $newconditions);
        $conditions=[];
        
        if($code!='')
         $conditions['code']=$code;
        
        if($id!='')
         $conditions['id !=']=$id;
        
        $existingRecord = $this->AreaObj->getRecords(['code'],$conditions , 'all', ['first' => true]);
        if(!empty($existingRecord)){
            return false;
        }
        return true;
    }
    
    /**
     * method returns level id on basis of parent id and level value 
     * @param type $parentLevelId
     * @param type $levelValue
     * @return type
     */
     public function saveGetAreaLevelId($parentLevelId='',$levelValue=''){
         if($parentLevelId!=''){
             $levelDet = $this->getLevelDetails($parentLevelId) ;            
             if(!empty($levelDet))
             $levelValue = $levelDet['code']+1;             
        }
        $areaLevel = $this->AreaLevelObj->getRecords(['level','id'], [['level' => $levelValue]], 'all', ['first' => true]);
        if(!empty($areaLevel)){
            return $areaLevel['id'];
        }else{
            $save=[];
            $save['order']=_ORDER;
            $save['visible']=_VISBLE;
            $save['level']=$levelValue;
            $save['name']=_LevelName.$levelValue;
            $save['created_user_id']=$this->Auth->User('id');
            $save['modified_user_id']=$this->Auth->User('id');
            return $this->AreaLevelObj->insertdata($save);
        }
    }
    
    /**
     * $areaId is the area Id
     * @param type $areaId
     */    
    public function deleteArea($areaId=''){
         if ($areaId != '') {
            $conditions = ['id' => $areaId];               
            $deleteareaIds = $this->AreaObj->deleteRecords($conditions);
            //$remlevels =1;
            if ($deleteareaIds > 0) {
                $this->deleteShipment($areaId);               
                return true;
            } else {
                return ['errorCode' => _ERR100];      //  not modified due to database error 
            }
        } else {
            return ['errorCode' => _ERR105];      //  not modified due to database error 
        }
    }
    /**
     * 
     * @param type $areaIds
     */
    public function deleteShipment($areaIds){
                $conditions=[];
                $conditions=['OR'=>[ 'to_area_id  IN '=>$areaIds,'from_area_id  IN '=>$areaIds]];
                $shipmentIds = $this->Shipments->getRecords(['id','id'],$conditions,'list');
                $deleteshipmentIds = $this->Shipments->deleteRecords($conditions);
              
                $conditions=[];
                $conditions=['shipment_id  IN '=>$shipmentIds];
                $deleteshipmentPkgsIds = $this->ShipmentPackages->deleteRecords($conditions);               
                $deleteshipmentPkgsItemsIds = $this->ShipmentPackageItems->deleteRecords($conditions);
                $deleteshipmentLocationIds = $this->ShipmentLocations->deleteRecords($conditions);
                
    }
    
    
}
