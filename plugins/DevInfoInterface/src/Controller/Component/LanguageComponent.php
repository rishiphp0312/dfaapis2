<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Language Component
 */
class LanguageComponent extends Component {

    // The other component your component uses
    public $LangSheetNames = [
        "ic" => "INDICATOR_CLASSIFICATIONS",
        "sgval" => "SUBGROUP_VALS"
    ];
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
        $this->session = $this->request->session();
		
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

    public function exportLangDatabase($fromLangCode = 'en',$fromLangName='English [en]',$toLangCode='',$toLangName = '')
    {
         
            $start_row = 2;
            // --- Excel
            $objPHPExcel = $this->CommonInterface->readXlsOrCsv(_XLS_PATH_WEBROOT . DS . 'SAMPLE_LANG_EXPORT_SHEET.xlsx', false);
            
            //  Get the current sheet with all its newly-set style properties
            $objWorkSheetBase = $objPHPExcel->getSheet();
            $this->addLanguageHeadingRow($objWorkSheetBase,$fromLangName,$toLangName); 
            // Remove current sheet(Data 1) as its preventing us from renaming
            $objPHPExcel->removeSheetByIndex(0);
                       
            //IC 
            $wsheet_obj = clone $objWorkSheetBase; 
            $wsheet_obj->setTitle('INDICATOR_CLASSIFICATIONS');                 
            $this->exportIcWorksheetData($wsheet_obj,$start_row,$fromLangCode);           
            $objPHPExcel->addSheet($wsheet_obj);

             //Subgroup Val         
            $wsheet_obj = clone $objWorkSheetBase; 
             $wsheet_obj->setTitle('SUBGROUP_VALS');           
            $this->exportSubgroupValWorksheetData($wsheet_obj,$start_row,$fromLangCode);           
            $objPHPExcel->addSheet($wsheet_obj);

             //Indicator
            $wsheet_obj = clone $objWorkSheetBase; 
            $wsheet_obj->setTitle('INDICATOR');           
            $this->exportIndicatorWorksheetData($wsheet_obj,$start_row,$fromLangCode);            
            $objPHPExcel->addSheet($wsheet_obj);


             //Subgroup
            $wsheet_obj = clone $objWorkSheetBase;  
            $wsheet_obj->setTitle('SUBGROUP');          
            $this->exportSubgroupSheetData($wsheet_obj,$start_row,$fromLangCode);            
            $objPHPExcel->addSheet($wsheet_obj);


            //Unit
            $wsheet_obj = clone $objWorkSheetBase;
             $wsheet_obj->setTitle('UNIT');            
            $this->exportUnitSheetSata($wsheet_obj,$start_row,$fromLangCode);           
            $objPHPExcel->addSheet($wsheet_obj);

            
             //fOOTnote
           $wsheet_obj = clone $objWorkSheetBase;  
           $wsheet_obj->setTitle('FOOTNOTE');           
           $this->exportFootnoteWorksheetData($wsheet_obj,$start_row,$fromLangCode);          
           $objPHPExcel->addSheet($wsheet_obj);

             //Area
           $wsheet_obj = clone $objWorkSheetBase;   
           $wsheet_obj->setTitle('AREA');         
           $this->exportAreaSheetData($wsheet_obj,$start_row,$fromLangCode);           
           $objPHPExcel->addSheet($wsheet_obj);


            //Area level
            $wsheet_obj = clone $objWorkSheetBase;  
             $wsheet_obj->setTitle('AREA_LEVEL');            
            $this->exportArealevelSheetData($wsheet_obj,$start_row,$fromLangCode);
           
            $objPHPExcel->addSheet($wsheet_obj);

            //Area Feature type
            $wsheet_obj = clone $objWorkSheetBase; 
            $wsheet_obj->setTitle('AREA_FEATURE_TYPE');            
            $this->exportAreaFeatureTypeWorksheetData($wsheet_obj,$start_row,$fromLangCode);            
            $objPHPExcel->addSheet($wsheet_obj);

            //Map Name            
            $wsheet_obj = clone $objWorkSheetBase; 
            $wsheet_obj->setTitle('MAP_NAME');            
            $this->exportAreaMapMetadataWorksheetData($wsheet_obj,$start_row,$fromLangCode);            
            $objPHPExcel->addSheet($wsheet_obj);

            //Subgroup type
           $wsheet_obj = clone $objWorkSheetBase;  
             $wsheet_obj->setTitle('SUBGROUP_TYPE');          
           $this->exportSubgroupTypeSheetData($wsheet_obj,$start_row,$fromLangCode);
          
            $objPHPExcel->addSheet($wsheet_obj);

            //DD Metadata          

            $wsheet_obj = clone $objWorkSheetBase; 
            $wsheet_obj->setTitle('DBMETADATA');          
            $this->prep_LangexportDbMetadataWorksheetData($wsheet_obj,$start_row,$fromLangCode);
           
            $objPHPExcel->addSheet($wsheet_obj);
             
              //Meta data category
            $wsheet_obj = clone $objWorkSheetBase;  
            $wsheet_obj->setTitle('METADATA_CATEGORY');           
            $this->exportMetadataCategoryWorksheetData($wsheet_obj,$start_row,$fromLangCode);
           
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
	        $saveFile = _XLS_PATH_WEBROOT . DS .'language'. DS. $file_name;
	        $saved = $objWriter->save($saveFile);
	        return _WEBSITE_URL."webroot/uploads/xls/language/".$file_name;
            

    }

        /**
     * Process Language Entry database Spreadsheet
     * 
     * @param string $filename File to be processed
     * @param string $dbId Database Id
     * 
     * @return string Custom Log file path
     */
    public function importLanguageDatabase($filename, $dbId, $dbConnection)
    {

        $objPHPExcel = $this->CommonInterface->readXlsOrCsv($filename, false);
        $startRows = 1;
        $return = [];


        // ---------------------------------------------

        // Read Language XLS file

        // Check if the sheet is valid

        // check if the given to labguage is already created

            // if no
            // create (craete a copy of from language table and rename with subfix) new language table
            // make entry in language table

            // if yes
            
        // Get the gid from too language and update string

        // ---------------------------------------------








        
        //Fetch translation text from each sheet & update accordingly.

        //Indicator Classifications sheet
        $ICSheet = $objPHPExcel->getSheet(0);
        $response = $this->importIndicatorClassificationsLangData($ICSheet);

        //Subgroup Val sheet
        $SubgroupValSheet = $objPHPExcel->getSheet(1);
        $response =  $this->importSubGroupValLangData($SubgroupValSheet);

        //Indicator sheet
        $IndicatorSheet = $objPHPExcel->getSheet(2);
        $response =  $this->importIndicatorLangData($IndicatorSheet);

        //Subgroup sheet
        $SubgroupSheet = $objPHPExcel->getSheet(3);
        $response =  $this->importSubGroupLangData($SubgroupSheet);

        //Unit sheet import
        $UnitSheet = $objPHPExcel->getSheet(4);
        $response =  $this->importUnitLangData($UnitSheet);

        //Footnote sheet import
        $FootnoteSheet = $objPHPExcel->getSheet(5);
        $response =  $this->importFootnoteLangData($FootnoteSheet);

        //Area sheet import
        $AreaSheet = $objPHPExcel->getSheet(6);
        $response = $this->importAreaLangData($AreaSheet);

        //Area Level sheet import
        $AreaLevelSheet = $objPHPExcel->getSheet(7);
        $response =  $this->importAreaLevelLangData($AreaLevelSheet);

        //Area Feature type sheet import
        $AreaFeatTypeSheet = $objPHPExcel->getSheet(8);
        $response =  $this->importAreaFeatureTypeLangData($AreaFeatTypeSheet);

        //Map Meta data sheet import
        $MapMetadataSheet = $objPHPExcel->getSheet(9);
        $response =  $this->importMapMetadataLangData($MapMetadataSheet);


        //Subgroup type sheet import
        $SubgroupTypeSheet = $objPHPExcel->getSheet(10);
        $response =   $this->importSubgroupTypeLangData($SubgroupTypeSheet);

        //DBMetadata sheet import
        $DBMetadataSheet = $objPHPExcel->getSheet(11);
        $response = $this->importDBMetadataLangData($DBMetadataSheet);

        //Metadata Cateogry sheet import
        $MetadataCategorySheet = $objPHPExcel->getSheet(12);
        $this->importMetadataCategoryLangData($MetadataCategorySheet);


    }
    /* Function to Find language code of text translation imported sheet i.e sourceLanaguage code && targetLanguage code
    *  Sheet Object
    */
    public function getImportSheetLanguageCodes($objPHPSheet){

       $srcLanguage = $objPHPSheet->getCell('A1')->getValue();
       $tgtLanguage = $objPHPSheet->getCell('B1')->getValue();

       preg_match('^\[(.*)\]^',$srcLanguage,$from_lang_matches);
       $srcLangCode = $from_lang_matches['1'];

       
       preg_match('^\[(.*)\]^',$tgtLanguage,$to_lang_matches);       
       $tgtLangCode = $to_lang_matches['1'];

       return  ['srcLangCode'=>$srcLangCode,'tgtLangCode'=>$tgtLangCode,'srcLangName'=>$srcLanguage,'tgtLangName'=>$tgtLanguage];

    }

   
    
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM DB META DATA SHEET

    */
    public function importMetadataCategoryLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
       if($sheetTitle !='METADATA_CATEGORY'){
             return ['hasError'=>TRUE,'errCode'=>'Invalid MetadataCategory import sheet name'];

        }

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name'];
       }

      if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

     
       //check if table exists or not
       $srcTableName = "UT_Metadata_Category_".$srcLangCode;
       $tgtTableName = "UT_Metadata_Category_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }

        if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


       $tblICTgtObj = $this->Metadata->MetadatacategoryObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_META_CATEGORY_NAME] = $tgtLangText;
              $condtions[_META_CATEGORY_NAME] = $srcLangText;
              
            // pr($condtions);
              
           $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
       
    }

     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM DB META DATA SHEET

    */
    public function importDBMetadataLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='DBMETADATA'){
            return ['hasError'=>TRUE,'errCode'=>'Invalid DBMetadata import sheet name'];

        }

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name'];
       }

       if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
         

       }

       //check if table exists or not
       $srcTableName = "UT_DBMetadata_".$srcLangCode;
       $tgtTableName = "UT_DBMetadata_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


       $tblICTgtObj = $this->Metadata->DbMetadataObj;
       $tblICTgtObj->table($tgtTableName);
      
      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_DBMETA_DESC] = $tgtLangText;
              $condtions[_DBMETA_DESC] = $srcLangText;
           //  pr($updFields);
         //    pr($condtions);
           //   die;
          $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
       
    }

    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM MAP META DATA SHEET

    */
    public function importSubgroupTypeLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='SUBGROUP_TYPE'){
            return ['hasError'=>TRUE,'errCode'=>'Invalid Subgrouptype import sheet name'];

        }

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }

        if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

       //check if table exists or not
       $srcTableName = "UT_Subgroup_Type_".$srcLangCode;
       $tgtTableName = "UT_Subgroup_Type_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


      $tblICTgtObj = $this->SubgroupType->SubgroupTypeObj;
      $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $tgtLangText;
              $condtions[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $srcLangText;
              
            // pr($condtions);
              
           $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
       
    }
    

    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM MAP META DATA SHEET

    */
    public function importMapMetadataLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='MAP_NAME'){
              return ['hasError'=>TRUE,'errCode'=>'Invalid Map Metadata import sheet name'];

        }

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
          return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }

        if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

       //check if table exists or not
       $srcTableName = "UT_Area_Map_Metadata_".$srcLangCode;
       $tgtTableName = "UT_Area_Map_Metadata_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            
            return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }

       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


       $tblICTgtObj = $this->Area->AreaMapMetadataObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_AREAMAP_METADATA_LAYER_NAME] = $tgtLangText;
              $condtions[_AREAMAP_METADATA_LAYER_NAME] = $srcLangText;
              
            // pr($condtions);
              
           $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
       
    }


     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Area Feature Type SHEET

    */
    public function importAreaFeatureTypeLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='AREA_FEATURE_TYPE'){
               return ['hasError'=>TRUE,'errCode'=>'Invalid Area Feature Type import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
          return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name'];
       }

       if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

       //check if table exists or not
       $srcTableName = "UT_Area_Feature_Type_".$srcLangCode;
       $tgtTableName = "UT_Area_Feature_Type_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           
return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


       $tblICTgtObj = $this->Area->AreaFeatureTypeObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_AREAFEATURE_TYPE] = $tgtLangText;
              $condtions[_AREAFEATURE_TYPE] = $srcLangText;
              
            // pr($condtions);
              
           $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
       
    }

     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Area Level SHEET

    */
    public function importAreaLevelLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='AREA_LEVEL'){
              return ['hasError'=>TRUE,'errCode'=>'Invalid Area level import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }
       if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
           

       }

       //check if table exists or not
       $srcTableName = "UT_Area_Level_".$srcLangCode;
       $tgtTableName = "UT_Area_Level_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }

       $tblICTgtObj = $this->Area->AreaLevelObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_AREALEVEL_LEVEL_NAME] = $tgtLangText;
              $condtions[_AREALEVEL_LEVEL_NAME] = $srcLangText;
              
            // pr($condtions);
              
           $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
      
    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Area SHEET

    */
    public function importAreaLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='AREA'){
              return ['hasError'=>TRUE,'errCode'=>'Invalid Area import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }
        if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

       //check if table exists or not
       $srcTableName = "UT_Area_".$srcLangCode;
       $tgtTableName = "UT_Area_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }

       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }
     

       $tblICTgtObj = $this->Area->AreaObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_AREA_AREA_NAME] = $tgtLangText;
              $condtions[_AREA_AREA_NAME] = $srcLangText;
             // pr($updFields);
            // pr($condtions);
              
             $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }

    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Footnote SHEET

    */
    public function importFootnoteLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='FOOTNOTE'){
               return ['hasError'=>TRUE,'errCode'=>'Invalid FootNote import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }

       if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          


       }

       //check if table exists or not
       $srcTableName = "UT_FootNote_".$srcLangCode;
       $tgtTableName = "UT_FootNote_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];

       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }

       $tblICTgtObj = $this->Footnote->FootnoteObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_FOOTNOTE_VAL] = $tgtLangText;
              $condtions[_FOOTNOTE_VAL] = $srcLangText;           
              
              $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }

    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM UNIT SHEET

    */
    public function importUnitLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='UNIT'){
              return ['hasError'=>TRUE,'errCode'=>'Invalid Unit import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
          return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }
       if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
           
       }
       //check if table exists or not
       $srcTableName = "UT_Unit_".$srcLangCode;
       $tgtTableName = "UT_Unit_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];

       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


       $tblICTgtObj = $this->Unit->UnitObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_UNIT_UNIT_NAME] = $tgtLangText;
              $condtions[_UNIT_UNIT_NAME] = $srcLangText;
             // pr($updFields);
            // pr($condtions);
              
             $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }

    }

     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM SUBGROUP SHEET

    */
    public function importSubGroupLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='SUBGROUP'){
            return ['hasError'=>TRUE,'errCode'=>'Invalid Subgroup import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
          return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }

        if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

       //check if table exists or not
       $srcTableName = "UT_Subgroup_".$srcLangCode;
       $tgtTableName = "UT_Subgroup_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }

       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }

       $tblICTgtObj = $this->Subgroup->SubgroupObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
       $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
            
              $updFields = [];
              $updFields[_SUBGROUP_SUBGROUP_NAME] = $tgtLangText;
              $condtions[_SUBGROUP_SUBGROUP_NAME] = $srcLangText;
              
             
               $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }
       
    }

    public function importIndicatorClassificationsLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();

        if($sheetTitle !='INDICATOR_CLASSIFICATIONS'){
         return ['hasError'=>TRUE,'errCode'=>'Invalid Indicator Classifications import sheet name'];


        }

         $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
         extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }

       if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
           

       }


       //check if table exists or not
       $srcTableName = "UT_Indicator_Classifications_".$srcLangCode;
       $tgtTableName = "UT_Indicator_Classifications_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }

      
       $tblICTgtObj = $this->IndicatorClassifications->IndicatorClassificationsObj;
       $tblICTgtObj->table($tgtTableName);
      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_IC_IC_NAME] = $tgtLangText;
              $condtions[_IC_IC_NAME] = $srcLangText;
             // pr($updFields);
            // pr($condtions);
              
             $tblICTgtObj->updateRecords($updFields, $condtions);

          }
         
      }
      

    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM SUBGROUP_VALS SHEET

    */
    public function importSubGroupValLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='SUBGROUP_VALS'){
             return ['hasError'=>TRUE,'errCode'=>'Invalid SubgroupVal import sheet name'];

        }

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }
      if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
           

       }
       //check if table exists or not
       $srcTableName = "UT_Subgroup_Vals_".$srcLangCode;
       $tgtTableName = "UT_Subgroup_Vals_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['hasError'=>TRUE,'errCode'=>'Source table missing.'];
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


       $tblICTgtObj = $this->SubgroupVals->SubgroupValsObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_SUBGROUP_VAL_SUBGROUP_VAL] = $tgtLangText;
              $condtions[_SUBGROUP_VAL_SUBGROUP_VAL] = $srcLangText;
            
              
             $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }

    }

    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM SUBGROUP_VALS SHEET

    */
    public function importIndicatorLangData($objPHPSheet){
        
       $sheetTitle = $objPHPSheet->getTitle();
        if($sheetTitle !='INDICATOR'){
            return ['hasError'=>TRUE,'errCode'=>'Invalid Indicator import sheet name'];

        }

         $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode)) || ($srcLangCode == $tgtLangCode)){
           
          return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }

      if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
          

       }

       //check if table exists or not
       $srcTableName = "UT_Indicator_".$srcLangCode;
       $tgtTableName = "UT_Indicator_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['hasError'=>TRUE,'errCode'=>'Invalid source and target language name']; 
       }
       if(!$tgtTblExists){
            return ['hasError'=>TRUE,'errCode'=>'Target table missing.'];
       }


      

       $tblICTgtObj = $this->Indicator->IndicatorObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              
              $updFields = [];
              $updFields[_INDICATOR_INDICATOR_NAME] = $tgtLangText;
              $condtions[_INDICATOR_INDICATOR_NAME] = $srcLangText;
             // pr($updFields);
            // pr($condtions);
              
             $tblICTgtObj->updateRecords($updFields, $condtions);

          }
       }

    }

    public function addLanguageHeadingRow($objPHPSheet,$fromLangName,$toLangName){
        
        $objPHPSheet->setCellValue('A1', $fromLangName);
        $objPHPSheet->setCellValue('B1', $toLangName);


    }

    public function exportIcWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
        //IC DATA
            $params = [];
            $params['fields'] = array(_IC_IC_NID,_IC_IC_PARENT_NID,_IC_IC_GID,_IC_IC_NAME);
            $params['conditions'] = [];           
          //  $IClist = $this->IndicatorClassifications->getRecords($params['fields'],$params['conditions']);

          $dbMetaDataTblName = "UT_Indicator_Classifications_".$fromLangCode;
         

           if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {
              
               $bMetaDataTblObj = $this->IndicatorClassifications->IndicatorClassificationsObj;
               $bMetaDataTblObj->table($dbMetaDataTblName);
               $IClist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
           }
        else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }


       // return $objPHPSheet;


    }


public function exportIndicatorWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
        //Indicator list DATA
            $params = [];
            $params['fields'] = array(_INDICATOR_INDICATOR_NID,_INDICATOR_INDICATOR_NAME,_INDICATOR_INDICATOR_GID);
            $params['conditions'] = [];          
           // $Indicatorlist = $this->Indicator->getRecords($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Indicator_".$fromLangCode;
            if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {         
               $bMetaDataTblObj = $this->Indicator->IndicatorObj;
               $bMetaDataTblObj->table($dbMetaDataTblName);
               $Indicatorlist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);

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
          }
        else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }


      //  return $objPHPSheet;


    }
    
    
 public function exportSubgroupValWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
         //Subgroup values list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_VAL_SUBGROUP_VAL_NID,_SUBGROUP_VAL_SUBGROUP_VAL,_SUBGROUP_VAL_SUBGROUP_VAL_GID);
            $params['conditions'] = [];    

          //  $SubgroupValslist = $this->SubgroupVals->getRecords($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Subgroup_Vals_".$fromLangCode;
              if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {   
           
               $bMetaDataTblObj = $this->SubgroupVals->SubgroupValsObj;
               $bMetaDataTblObj->table($dbMetaDataTblName);
               $SubgroupValslist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
            }
            else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }



       // return $objPHPSheet;


    }
	
    public function exportUnitSheetSata($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        //Unit list DATA
            $params = [];
            $params['fields'] = array(_UNIT_UNIT_NID,_UNIT_UNIT_NAME,_UNIT_UNIT_GID);
            $params['conditions'] = [];       

          //  $Unitlist = $this->Unit->getRecords($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Unit_".$fromLangCode;
            if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {             
           $bMetaDataTblObj = $this->Unit->UnitObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $Unitlist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions'],'all');


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
          }
          else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }



      //  return $objPHPSheet;

    }

public function exportAreaSheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
         //Area list DATA
            $params = [];
            $params['fields'] = array(_AREA_AREA_NID,_AREA_PARENT_NId,_AREA_AREA_ID,_AREA_AREA_NAME,_AREA_AREA_GID,_AREA_AREA_LEVEL);
            $params['conditions'] = [];
            
           // $Arealist = $this->Area->getRecords($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Area_".$fromLangCode;
            if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           $bMetaDataTblObj = $this->Area->AreaObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $Arealist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
             }
             else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }



       // return $objPHPSheet;

    }

    public function exportArealevelSheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
            //Area Level list DATA
            $params = [];
            $params['fields'] = array(_AREALEVEL_LEVEL_NID,_AREALEVEL_AREA_LEVEL,_AREALEVEL_LEVEL_NAME);
            $params['conditions'] = [];
           
          // $AreaLevellist = $this->Area->getRecordsAreaLevel($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Area_Level_".$fromLangCode;
            if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
               $bMetaDataTblObj = $this->Area->AreaLevelObj;
               $bMetaDataTblObj->table($dbMetaDataTblName);
               $AreaLevellist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);

           
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
           }
        else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }


       // return $objPHPSheet;

    }

    public function exportSubgroupTypeSheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
            //Subgroup type list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUPTYPE_SUBGROUP_TYPE_NID,_SUBGROUPTYPE_SUBGROUP_TYPE_NAME,_SUBGROUPTYPE_SUBGROUP_TYPE_GID);
            $params['conditions'] = [];
            
           // $SubgroupTypelist = $this->SubgroupType->getRecords($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Subgroup_Type_".$fromLangCode;
           if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           $bMetaDataTblObj = $this->SubgroupType->SubgroupTypeObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $SubgroupTypelist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
           }
           else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }


        //return $objPHPSheet;

    }

public function exportSubgroupSheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
         //Subgroup list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_TYPE);
            $params['conditions'] = [];

          // $Subgrouplist = $this->CommonInterface->serviceInterface('Subgroup', 'getRecords', $params); 

           $dbMetaDataTblName = "UT_Subgroup_".$fromLangCode;
           if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           $bMetaDataTblObj = $this->Subgroup->SubgroupObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $Subgrouplist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
           }
           else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }

       // return $objPHPSheet;
    }

 public function exportFootnoteWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_FOOTNOTE_NId,_FOOTNOTE_VAL,_FOOTNOTE_GID);
            $params['conditions'] = [];
          
           // $Footnotelist = $this->Footnote->getRecords($params['fields'],$params['conditions']);  

           $dbMetaDataTblName = "UT_FootNote_".$fromLangCode;
          if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           $bMetaDataTblObj = $this->Footnote->FootnoteObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $Footnotelist = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
           }
           else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }



        return $objPHPSheet;


    }
	
    public function exportAreaFeatureTypeWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_AREAFEATURE_TYPE_NID,_AREAFEATURE_TYPE);
            $params['conditions'] = [];
           
          // $AreaFeatureList = $this->Area->getAreaFeatureTypes($params['fields'],$params['conditions']);

           $dbMetaDataTblName = "UT_Area_Feature_Type_".$fromLangCode;
          if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
               $bMetaDataTblObj = $this->Area->AreaFeatureTypeObj;
               $bMetaDataTblObj->table($dbMetaDataTblName);
               $AreaFeatureList = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
           }
           else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }


    }
	
   

    
   public function exportMetadataCategoryWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_META_CATEGORY_NAME,_META_CATEGORY_NID);
            $params['conditions'] = [];
           
         //  $MetadataCatgoryList = $this->Metadata->getRecords($params['fields'],$params['conditions']);

           $dbMetaDataTblName = "UT_Metadata_Category_".$fromLangCode;
            if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           
                   $metaDataCategoryTblObj = $this->Metadata->MetadatacategoryObj;
                   $metaDataCategoryTblObj->table($dbMetaDataTblName);
                   $MetadataCatgoryList = $metaDataCategoryTblObj->getRecords($params['fields'],$params['conditions']);

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
           }
           else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }



      //  return $objPHPSheet;


    }

    public function prep_LangexportDbMetadataWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_DBMETA_NID,_DBMETA_DESC);
            $params['conditions'] = [];
          
          // $DbMetadataList = $this->Metadata->getDbMetadataRecords($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_DBMetadata_".$fromLangCode;
          if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           $bMetaDataTblObj = $this->Metadata->DbMetadataObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $DbMetadataList = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


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
           }
          else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }



        //return $objPHPSheet;


    }
    
  public function exportAreaMapMetadataWorksheetData($objPHPSheet,$startRow = 1,$fromLangCode='en')
    {
        
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array('Metadata_NId','Layer_Name');
            $params['conditions'] = [];
          
          // $DbMetadataList = $this->Area->getAreaMapMetadata($params['fields'],$params['conditions']); 

           $dbMetaDataTblName = "UT_Area_Map_Metadata_".$fromLangCode;
          if($this->LangObj->check_table_exists(strtolower($dbMetaDataTblName))) {  
           $bMetaDataTblObj = $this->Area->AreaMapMetadataObj;
           $bMetaDataTblObj->table($dbMetaDataTblName);
           $DbMetadataList = $bMetaDataTblObj->getRecords($params['fields'],$params['conditions']);


          // pr($MetadataCatgoryList);die;
            if(!empty($DbMetadataList) && is_array($DbMetadataList))
            {
                $row = $startRow;
                foreach($DbMetadataList as $ftnote_data)
                {
                    $objPHPSheet->setCellValue('A'.($row), $ftnote_data['Layer_Name']);
                    $objPHPSheet->setCellValue('B'.($row), $ftnote_data['Layer_Name']);

                    $row++;

                }

            }
          }
          else{
              
              return ['hasError'=>TRUE,'errCode'=>'Target Table does not exits'];

          }


        //return $objPHPSheet;


    }

   /* Function to return active db connection db type
    *
    */
    public function getActiveDbType()
    {
        
        $activeDbConId = $this->session->read('dbId');
        $conDetails = $this->Common->getDbConnectionDetails($activeDbConId);
        $conDetailsArr = json_decode($conDetails,true);
        $dbSource = strtolower($conDetailsArr['db_source']);
        return  $dbSource;

    }

     /* Function to create translate language tables
    *
    */
    public function createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName){
       
        if($srcLangCode !='' && $srcLangName !='' && $tgtLangCode !='' && $tgtLangName !=''){
         $translableLangTblList = array('UT_Indicator_{suffix}','UT_Area_{suffix}','UT_Area_Feature_Type_{suffix}','UT_Area_Level_{suffix}','UT_Area_Map_Metadata_{suffix}','UT_DBMetadata_{suffix}','UT_FootNote_{suffix}','UT_Indicator_Classifications_{suffix}','UT_Metadata_Category_{suffix}','UT_Subgroup_{suffix}','UT_Subgroup_Type_{suffix}','UT_Subgroup_Vals_{suffix}');   

         $activeDbType = $this->getActiveDbType();

         foreach($translableLangTblList as $translableLangTblName){

             $srcTableName = $translableLangTblName;
             $srcTableName = str_replace('{suffix}',$srcLangCode,$srcTableName);

             $tgtTableName = $translableLangTblName;
             $tgtTableName = str_replace('{suffix}',$tgtLangCode,$tgtTableName);

             if($this->LangObj->check_table_exists($srcTableName) && !$this->LangObj->check_table_exists($tgtTableName)){

                  if($dbSource == 'mysql'){

                       $this->LangObj->executeTableCreateQuery("CREATE TABLE $tgtTableName LIKE $srcTableName");
                       $this->LangObj->executeTableCreateQuery("INSERT $tgtTableName SELECT * FROM $srcTableName"); 
                

                   }
                   else{
               
                       $this->LangObj->executeTableCreateQuery("SELECT * INTO $tgtTableName FROM $srcTableName"); 
                   } 
             }
          }  

          //Make an entry in language table
            $langEntity = $this->LangObj->newEntity([   
            _LANGUAGE_LANGUAGE_NAME => $tgtLangName,
            _LANGUAGE_LANGUAGE_CODE => $tgtLangCode,
            _LANGUAGE_LANGUAGE_DEFAULT =>'0',
            _LANGUAGE_GLOBAL_LOCK => '0' 
            ]);

            $this->LangObj->save($langEntity);

        }
        




    }
   
}
