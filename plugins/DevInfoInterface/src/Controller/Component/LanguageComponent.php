<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Unit Component
 */
class LanguageComponent extends Component {

    // The other component your component uses
    public $LangObj = NULL;
    public $components = ['TransactionLogs','Common','Auth',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.Indicator',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.Unit',  
        'DevInfoInterface.Footnote',          
        'DevInfoInterface.Data',
        'DevInfoInterface.Area',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Metadatareport',
        'DevInfoInterface.Metadata',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.SubgroupType',
        'DevInfoInterface.CommonInterface'];
	
    public function initialize(array $config) {
        parent::initialize($config);
        $this->LangObj = TableRegistry::get('DevInfoInterface.Language');

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
        return $this->LangObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = []) {
        return $this->UnitObj->deleteRecords($conditions);
    }

    public function export_lang_database($fromLangCode = 'en',$fromLangName='English [en]',$toLangCode='',$toLangName = '')
    {
          //echo $toLangName;
          // pr(func_get_args());
            $start_row = 2;
            // --- Excel
            $objPHPExcel = $this->CommonInterface->readXlsOrCsv(_XLS_PATH_WEBROOT . DS . 'SAMPLE_LANG_EXPORT_SHEET.xlsx', false);
            
            //  Get the current sheet with all its newly-set style properties
            $objWorkSheetBase = $objPHPExcel->getSheet();
            $this->add_language_heading_row($objWorkSheetBase,$fromLangName,$toLangName); 
            // Remove current sheet(Data 1) as its preventing us from renaming
            $objPHPExcel->removeSheetByIndex(0);
                       
            //IC 
            $wsheet_obj = clone $objWorkSheetBase;                  
            $this->prep_langexport_ic_worksheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('INDICATOR_CLASSIFICATIONS');
            $objPHPExcel->addSheet($wsheet_obj);

             //Subgroup Val         
            $wsheet_obj = clone $objWorkSheetBase;            
            $this->prep_langexport_subgroup_val_worksheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('SUBGROUP_VALS');
            $objPHPExcel->addSheet($wsheet_obj);

             //Indicator
            $wsheet_obj = clone $objWorkSheetBase;            
            $this->prep_langexport_indicator_worksheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('INDICATOR');
            $objPHPExcel->addSheet($wsheet_obj);


            //Unit
            $wsheet_obj = clone $objWorkSheetBase;            
           $this->prep_langexport_unit_sheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('UNIT');
            $objPHPExcel->addSheet($wsheet_obj);

             //Subgroup
            $wsheet_obj = clone $objWorkSheetBase;            
           $this->prep_langexport_subgroup_sheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('SUBGROUP');
            $objPHPExcel->addSheet($wsheet_obj);

             //fOOTnote
           $wsheet_obj = clone $objWorkSheetBase;             
           $this->prep_langexport_footnote_worksheet_data($wsheet_obj,$start_row);
           $wsheet_obj->setTitle('FOOTNOTE');
           $objPHPExcel->addSheet($wsheet_obj);

             //Area
           $wsheet_obj = clone $objWorkSheetBase;            
           $this->prep_langexport_area_sheet_data($wsheet_obj,$start_row);
           $wsheet_obj->setTitle('AREA');
           $objPHPExcel->addSheet($wsheet_obj);


            //Area level
            $wsheet_obj = clone $objWorkSheetBase;              
            $this->prep_langexport_arealevel_sheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('AREA_LEVEL');
            $objPHPExcel->addSheet($wsheet_obj);

            //Area Feature type
            $wsheet_obj = clone $objWorkSheetBase;             
            $this->prep_langexport_areafeaturetype_worksheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('AREA_FEATURE_TYPE');
            $objPHPExcel->addSheet($wsheet_obj);


            //Subgroup type
           $wsheet_obj = clone $objWorkSheetBase;            
           $this->prep_langexport_subgroup_type_sheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('SUBGROUP_TYPE');
            $objPHPExcel->addSheet($wsheet_obj);

            //DD Metadata          

            $wsheet_obj = clone $objWorkSheetBase;           
            $this->prep_langexport_dbmetadata_worksheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('DBMETADATA');
            $objPHPExcel->addSheet($wsheet_obj);
             
              //Meta data category
            $wsheet_obj = clone $objWorkSheetBase;             
            $this->prep_langexport_metadatacategory_worksheet_data($wsheet_obj,$start_row);
            $wsheet_obj->setTitle('METADATA_CATEGORY');
            $objPHPExcel->addSheet($wsheet_obj);

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $activeDbConId = $this->request->session()->read('dbId');
            if(!empty($activeDbConId))
            {
                $dbConnection = $this->Common->getDbConnectionDetails($activeDbConId); //dbId
                $dbConnectionDetail = json_decode($dbConnection, true);  
                           
               $file_name = $dbConnectionDetail['db_database'].'-Language-'.$fromLangName.'-'.$toLangName.'.xls';
            }
            else{
                   $file_name = 'Language-'.$fromLangName.'-'.$toLangName.'.xls';

            }
            $export_file_name = 
	        $saveFile = _XLS_PATH_WEBROOT . DS .$file_name;
	        $saved = $objWriter->save($saveFile);
	        return $saveFile;
            

    }

    public function add_language_heading_row($objPHPSheet,$fromLangName,$toLangName){
        
        $objPHPSheet->setCellValue('A1', $fromLangName);
        $objPHPSheet->setCellValue('B1', $toLangName);


    }

    public function prep_langexport_ic_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
        //IC DATA
            $params = [];
            $params['fields'] = array(_IC_IC_NID,_IC_IC_PARENT_NID,_IC_IC_GID,_IC_IC_NAME);
            $params['conditions'] = [];           
            $IClist = $this->IndicatorClassifications->getRecords($params['fields'],$params['conditions']);

          //  pr($IClist);die;
            if(!empty($IClist) && is_array($IClist))
            {
                $row = $startRow;
                foreach($IClist as $IClist_detail)
                {
                    $objPHPSheet->setCellValue('A'.($row), $IClist_detail[_IC_IC_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$IClist_detail[_IC_IC_NAME]);

                    $row++;

                }


            }


        return $objPHPSheet;


    }


public function prep_langexport_indicator_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
        //Indicator list DATA
            $params = [];
            $params['fields'] = array(_INDICATOR_INDICATOR_NID,_INDICATOR_INDICATOR_NAME,_INDICATOR_INDICATOR_GID);
            $params['conditions'] = [];          
            $Indicatorlist = $this->Indicator->getRecords($params['fields'],$params['conditions']); 
          //  pr($IClist);die;
            if(!empty($Indicatorlist) && is_array($Indicatorlist))
            {
                 $row = $startRow;
                foreach($Indicatorlist as $IClist_detail)
                {
                    $objPHPSheet->setCellValue('A'.($row), $IClist_detail[_INDICATOR_INDICATOR_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$IClist_detail[_INDICATOR_INDICATOR_NAME]);

                    $row++;

                }


            }


        return $objPHPSheet;


    }
    
    
 public function prep_langexport_subgroup_val_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
         //Subgroup values list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_VAL_SUBGROUP_VAL_NID,_SUBGROUP_VAL_SUBGROUP_VAL,_SUBGROUP_VAL_SUBGROUP_VAL_GID);
            $params['conditions'] = [];    

            $SubgroupValslist = $this->SubgroupVals->getRecords($params['fields'],$params['conditions']); 

            if(!empty($SubgroupValslist) && is_array($SubgroupValslist))
            {
                 $row = $startRow;
                foreach($SubgroupValslist as $subgroup_val_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $subgroup_val_data[_SUBGROUP_VAL_SUBGROUP_VAL]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$subgroup_val_data[_SUBGROUP_VAL_SUBGROUP_VAL]);

                    $row++;

                }


            }


        return $objPHPSheet;


    }
	
    public function prep_langexport_unit_sheet_data($objPHPSheet,$startRow = 1)
    {
        //Unit list DATA
            $params = [];
            $params['fields'] = array(_UNIT_UNIT_NID,_UNIT_UNIT_NAME,_UNIT_UNIT_GID);
            $params['conditions'] = [];       

            $Unitlist = $this->Unit->getRecords($params['fields'],$params['conditions']); 

            if(!empty($Unitlist) && is_array($Unitlist))
            {
                 $row = $startRow;
                foreach($Unitlist as $unit_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $unit_data[_UNIT_UNIT_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$unit_data[_UNIT_UNIT_NAME]);

                    $row++;

                }


            }


        return $objPHPSheet;

    }

public function prep_langexport_area_sheet_data($objPHPSheet,$startRow = 1)
    {
         //Area list DATA
            $params = [];
            $params['fields'] = array(_AREA_AREA_NID,_AREA_PARENT_NId,_AREA_AREA_ID,_AREA_AREA_NAME,_AREA_AREA_GID,_AREA_AREA_LEVEL);
            $params['conditions'] = [];
            
            $Arealist = $this->Area->getRecords($params['fields'],$params['conditions']); 

            if(!empty($Arealist) && is_array($Arealist))
            {
                 $row = $startRow;
                foreach($Arealist as $area_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $area_data[_AREA_AREA_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$area_data[_AREA_AREA_NAME]);

                    $row++;

                }


            }


        return $objPHPSheet;

    }

    public function prep_langexport_arealevel_sheet_data($objPHPSheet,$startRow = 1)
    {
            //Area Level list DATA
            $params = [];
            $params['fields'] = array(_AREALEVEL_LEVEL_NID,_AREALEVEL_AREA_LEVEL,_AREALEVEL_LEVEL_NAME);
            $params['conditions'] = [];
           
            $AreaLevellist = $this->Area->getRecordsAreaLevel($params['fields'],$params['conditions']); 
           
            if(!empty($AreaLevellist) && is_array($AreaLevellist))
            {
                 $row = $startRow;
                foreach($AreaLevellist as $area_level_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $area_level_data[_AREALEVEL_LEVEL_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$area_level_data[_AREALEVEL_LEVEL_NAME]);

                    $row++;

                }


            }


        return $objPHPSheet;

    }

    public function prep_langexport_subgroup_type_sheet_data($objPHPSheet,$startRow = 1)
    {
            //Subgroup type list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUPTYPE_SUBGROUP_TYPE_NID,_SUBGROUPTYPE_SUBGROUP_TYPE_NAME,_SUBGROUPTYPE_SUBGROUP_TYPE_GID);
            $params['conditions'] = [];
            
            $SubgroupTypelist = $this->SubgroupType->getRecords($params['fields'],$params['conditions']); 

            if(!empty($SubgroupTypelist) && is_array($SubgroupTypelist))
            {
                 $row = $startRow;
                foreach($SubgroupTypelist as $area_level_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $area_level_data[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$area_level_data[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]);

                    $row++;

                }


            }


        return $objPHPSheet;

    }

public function prep_langexport_subgroup_sheet_data($objPHPSheet,$startRow = 1)
    {
         //Subgroup list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_TYPE);
            $params['conditions'] = [];
           $Subgrouplist = $this->CommonInterface->serviceInterface('Subgroup', 'getRecords', $params); 

            if(!empty($Subgrouplist) && is_array($Subgrouplist))
            {
                $row = $startRow;
                foreach($Subgrouplist as $area_level_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $area_level_data[_SUBGROUP_SUBGROUP_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$area_level_data[_SUBGROUP_SUBGROUP_NAME]);

                    $row++;

                }


            }
        return $objPHPSheet;
    }

 public function prep_langexport_footnote_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_FOOTNOTE_NId,_FOOTNOTE_VAL,_FOOTNOTE_GID);
            $params['conditions'] = [];
          
            $Footnotelist = $this->Footnote->getRecords($params['fields'],$params['conditions']);  
            if(!empty($Footnotelist) && is_array($Footnotelist))
            {
                 $row = $startRow;
                foreach($Footnotelist as $ftnote_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $ftnote_data[_FOOTNOTE_VAL]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$ftnote_data[_FOOTNOTE_VAL]);

                    $row++;

                }


            }


        return $objPHPSheet;


    }
	
    public function prep_langexport_areafeaturetype_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_AREAFEATURE_TYPE_NID,_AREAFEATURE_TYPE);
            $params['conditions'] = [];
           
           $AreaFeatureList = $this->Area->getAreaFeatureTypes($params['fields'],$params['conditions']);
            if(!empty($AreaFeatureList) && is_array($AreaFeatureList))
            {
                 $row = $startRow;
                foreach($AreaFeatureList as $ftnote_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $ftnote_data[_AREAFEATURE_TYPE]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$ftnote_data[_AREAFEATURE_TYPE]);

                    $row++;

                }


            }


        return $objPHPSheet;


    }
	
   

    
   public function prep_langexport_metadatacategory_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_META_CATEGORY_NAME,_META_CATEGORY_NID);
            $params['conditions'] = [];
           
           $MetadataCatgoryList = $this->Metadata->getRecords($params['fields'],$params['conditions']);
          // pr($MetadataCatgoryList);die;
            if(!empty($MetadataCatgoryList) && is_array($MetadataCatgoryList))
            {
                $row = $startRow;
                foreach($MetadataCatgoryList as $ftnote_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $ftnote_data[_META_CATEGORY_NAME]);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$ftnote_data[_META_CATEGORY_NAME]);

                    $row++;

                }

            }


        return $objPHPSheet;


    }

    public function prep_langexport_dbmetadata_worksheet_data($objPHPSheet,$startRow = 1)
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_DBMETA_NID,_DBMETA_DESC);
            $params['conditions'] = [];
          
           $DbMetadataList = $this->Metadata->getDbMetadataRecords($params['fields'],$params['conditions']); 
          // pr($MetadataCatgoryList);die;
            if(!empty($DbMetadataList) && is_array($DbMetadataList))
            {
                $row = $startRow;
                foreach($DbMetadataList as $ftnote_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $ftnote_data['DBMtd_Desc']);
                    $objPHPSheet->setCellValue('B'.($row), '#'.$ftnote_data['DBMtd_Desc']);

                    $row++;

                }

            }


        return $objPHPSheet;


    }

}
