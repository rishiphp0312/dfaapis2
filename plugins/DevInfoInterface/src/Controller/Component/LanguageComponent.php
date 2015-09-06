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
        "sgroupval" => "SUBGROUP_VALS",
        "indicator" => "INDICATOR",
        "sgroup" => "SUBGROUP",
        "unit" => "UNIT",
        "ftnote" => "FOOTNOTE",
        "area" => "AREA",
        "arlevel" => "AREA_LEVEL",
        "arfeattype" => "AREA_FEATURE_TYPE",
        "mapmetadata" => "MAP_NAME",
        "sgrouptype" => "SUBGROUP_TYPE",
        "dbmetadata" => "DBMETADATA",
        "metadatacat" => "METADATA_CATEGORY",
        "metadatareport"=>"METADATA_REPORT"
    ];
    public $LangTableNames = [
        "ic" => "UT_Indicator_Classifications_{suffix}",
        "sgroupval" => "UT_Subgroup_Vals_{suffix}",
        "indicator" => "UT_Indicator_{suffix}",
        "sgroup" => "UT_Subgroup_{suffix}",
        "unit" => "UT_Unit_{suffix}",
        "ftnote" => "UT_FootNote_{suffix}",
        "area" => "UT_Area_{suffix}",
        "arlevel" => "UT_Area_Level_{suffix}",
        "arfeattype" => "UT_Area_Feature_Type_{suffix}",
        "mapmetadata" => "UT_Area_Map_Metadata_{suffix}",
        "sgrouptype" => "UT_Subgroup_Type_{suffix}",
        "dbmetadata" => "UT_DBMetadata_{suffix}",
        "metadatacat" => "UT_Metadata_Category_{suffix}",
        "metadatareport" => "ut_metadatareport_{suffix}"
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
        $this->addLanguageHeadingRow($objWorkSheetBase, $fromLangName, $toLangName);
        // Remove current sheet(Data 1) as its preventing us from renaming
        $objPHPExcel->removeSheetByIndex(0);

        //IC 
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportIcWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        //echo $wsheet_obj->getCell('B3')->getValue();         
        $objPHPExcel->addSheet($wsheet_obj);

        //Subgroup Val         
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportSubgroupValWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);


        //Indicator
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportIndicatorWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);


        //Subgroup
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportSubgroupSheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);


        //Unit
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportUnitSheetSata($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);


        //fOOTnote
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportFootnoteWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //Area
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportAreaSheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);


        //Area level
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportArealevelSheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //Area Feature type
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportAreaFeatureTypeWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //Map Name            
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportAreaMapMetadataWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //Subgroup type
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportSubgroupTypeSheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //DD Metadata          

        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportDbMetadataWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //Meta data category
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportMetadataCategoryWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        //Meta data report
        $wsheet_obj = clone $objWorkSheetBase;
        $this->exportMetadataReportWorksheetData($wsheet_obj, $start_row, $fromLangCode, $toLangCode);
        $objPHPExcel->addSheet($wsheet_obj);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $activeDbConId = $this->request->session()->read('dbId');
        if (!empty($activeDbConId)) {
            $dbConnection = $this->Common->getDbConnectionDetails($activeDbConId); //dbId
            $dbConnectionDetail = json_decode($dbConnection, true);

            $file_name = $dbConnectionDetail['db_connection_name'] . '-Language-' . $fromLangName . '-' . $toLangName . '.xls';
        } else {
            $file_name = 'Language-' . $fromLangName . '-' . $toLangName . '.xls';
        }
        //$export_file_name = 
        $saveFile = _XLS_PATH_WEBROOT . DS . 'language' . DS . $file_name;
        //check if file already exists delete it
        if (file_exists($saveFile)) {
            unlink($saveFile);
        }
        $saved = $objWriter->save($saveFile);

        //create a transaction log
        /*$fieldsArray = [
            _MTRANSACTIONLOGS_DB_ID => $activeDbConId,
            _MTRANSACTIONLOGS_ACTION => _EXPORT,
            _MTRANSACTIONLOGS_MODULE => _MODULE_NAME_ADMINISTRATION,
            _MTRANSACTIONLOGS_SUBMODULE => _MODULE_NAME_LANGUAGE,
            _MTRANSACTIONLOGS_IDENTIFIER => '',
            _MTRANSACTIONLOGS_STATUS => _DONE
        ];
        $LogId = $this->TransactionLogs->createRecord($fieldsArray);
		*/
        return _WEBSITE_URL . _LANG_PATH_WEBROOT . '/' . $file_name;
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

        $errLogData = [];


        $fieldsArray = [
                            _MTRANSACTIONLOGS_DB_ID => $dbId,
                            _MTRANSACTIONLOGS_ACTION => _IMPORT,
                            _MTRANSACTIONLOGS_MODULE => _MODULE_NAME_ADMINISTRATION,
                            _MTRANSACTIONLOGS_SUBMODULE => _MODULE_NAME_LANGUAGE,
                            _MTRANSACTIONLOGS_IDENTIFIER => '',
                            _MTRANSACTIONLOGS_STATUS => _STARTED
                        ];
         $LogId = $this->TransactionLogs->createRecord($fieldsArray);

        
        //Fetch translation text from each sheet & update accordingly.

        //Indicator Classifications sheet
        $ICSheet = $objPHPExcel->getSheet(0);
        $response = $this->importIndicatorClassificationsLangData($ICSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['ic']] = $response['errCode'];
        }
       
        //Subgroup Val sheet
        $SubgroupValSheet = $objPHPExcel->getSheet(1);
        $response =  $this->importSubGroupValLangData($SubgroupValSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['sgroupval']] = $response['errCode'];
        }

        //Indicator sheet
        $IndicatorSheet = $objPHPExcel->getSheet(2);
        $response =  $this->importIndicatorLangData($IndicatorSheet);
        if(isset($response['errCode']))
        {
             $errLogData[$this->LangSheetNames['indicator']] = $response['errCode'];
        }

        //Subgroup sheet
        $SubgroupSheet = $objPHPExcel->getSheet(3);
        $response =  $this->importSubGroupLangData($SubgroupSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['sgroup']] = $response['errCode'];
        }

        //Unit sheet import
        $UnitSheet = $objPHPExcel->getSheet(4);
        $response =  $this->importUnitLangData($UnitSheet);
       if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['unit']] = $response['errCode'];
        }

        //Footnote sheet import
        $FootnoteSheet = $objPHPExcel->getSheet(5);
        $response =  $this->importFootnoteLangData($FootnoteSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['ftnote']] = $response['errCode'];
        }

        //Area sheet import
       $AreaSheet = $objPHPExcel->getSheet(6);
        $response = $this->importAreaLangData($AreaSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['area']] = $response['errCode'];
        }
        
        //Area Level sheet import
        $AreaLevelSheet = $objPHPExcel->getSheet(7);
        $response =  $this->importAreaLevelLangData($AreaLevelSheet);
       if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['arlevel']] = $response['errCode'];
        }

        //Area Feature type sheet import
        $AreaFeatTypeSheet = $objPHPExcel->getSheet(8);
        $response =  $this->importAreaFeatureTypeLangData($AreaFeatTypeSheet);
       if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['arfeattype']] = $response['errCode'];
        }

        //Map Meta data sheet import
        $MapMetadataSheet = $objPHPExcel->getSheet(9);
        $response =  $this->importMapMetadataLangData($MapMetadataSheet);

        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['mapmetadata']] = $response['errCode'];
        }


        //Subgroup type sheet import
        $SubgroupTypeSheet = $objPHPExcel->getSheet(10);
        $response =   $this->importSubgroupTypeLangData($SubgroupTypeSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['sgrouptype']] = $response['errCode'];
        }

        //DBMetadata sheet import
        $DBMetadataSheet = $objPHPExcel->getSheet(11);
        $response = $this->importDBMetadataLangData($DBMetadataSheet);
        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['dbmetadata']] = $response['errCode'];
        }

        //Metadata Cateogry sheet import
        $MetadataCategorySheet = $objPHPExcel->getSheet(12);
        $response = $this->importMetadataCategoryLangData($MetadataCategorySheet);

        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['metadatacat']]  = $response['errCode'];
        }

         //Metadata Report sheet import
        $MetadataReportSheet = $objPHPExcel->getSheet(13);
        $response = $this->importMetadataReportLangData($MetadataReportSheet);

        if(isset($response['errCode']))
        {
            $errLogData[$this->LangSheetNames['metadatareport']]  = $response['errCode'];
        }
        
        
        //Maintain Transaction log
        if(!empty($errLogData)){
             $transactionLogDesc = "Import Sheet Errors :";
            foreach($errLogData as $sheetName=>$errCode){

                $transactionLogDesc .= $sheetName." - ".$errCode."\n";
              
            }
            $transactionLogDesc = $transactionLogDesc;            
            $LogId = $this->TransactionLogs->updateRecord($fieldsArray,[_MTRANSACTIONLOGS_ID => $LogId]);
        }else{
           $transactionLogDesc ='';
        }
		  
		  $fieldsArray = [
                            _MTRANSACTIONLOGS_DB_ID => $dbId,                           
							_MTRANSACTIONLOGS_DESCRIPTION => $transactionLogDesc,							
                            _MTRANSACTIONLOGS_STATUS => _DONE
                        ];
          $this->TransactionLogs->updateRecord($fieldsArray,[_MTRANSACTIONLOGS_ID => $LogId]);
       
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
    /* Function to Validate Import language Sheet Title
    * Params
    * Sheet Object
    * sheet code to match title with
    */
    public function validateImportLangSheetTitle($objPHPSheet,$sheetCode){
        $predefSheetTitle = $this->LangSheetNames[$sheetCode];

        $sheetTitle = $objPHPSheet->getTitle();
        if(!empty($predefSheetTitle) && $predefSheetTitle == $sheetTitle){
            return TRUE;
        }
        else{
            return FALSE;
        }


    }

   
    
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM DB META DATA SHEET

    */
    public function importMetadataCategoryLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'metadatacat')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }      
       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES];
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
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }

        if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
         //GET Source Table GUID list
       $srcTableObj = $this->Metadata->MetadatacategoryObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_META_CATEGORY_GID,_META_CATEGORY_NAME],[],'list');

       $tblICTgtObj = $this->Metadata->MetadatacategoryObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
             //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){  
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('metadatacat',$tgtLangCode,[_META_CATEGORY_GID => $srcGID]);
                      if($tgtGidExists >0){    
                            $updFields = [];
                            $updFields[_META_CATEGORY_NAME] = $tgtLangText;
                            $condtions[_META_CATEGORY_GID] = $srcGID;              
                            $tblICTgtObj->updateRecords($updFields, $condtions);
                        }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('metadatacat',$srcLangCode,[_META_CATEGORY_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                             
                              $srcRowData[_META_CATEGORY_NAME] = $tgtLangText;
                              $tgtNIdExists = $this->getLangTableRecordCount('metadatacat',$tgtLangCode,[_META_CATEGORY_NID => $srcRowData[_META_CATEGORY_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_META_CATEGORY_NID];
                               unset($srcRowData[_META_CATEGORY_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_META_CATEGORY_NID => $tgtNId]);
                              }
                              else{
                                  unset($srcRowData[_META_CATEGORY_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                            
                          }
                      }
              }

          }
       }
       
    }

     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM DB META DATA SHEET

    */
    public function importDBMetadataLangData($objPHPSheet){
        
      if(!$this->validateImportLangSheetTitle($objPHPSheet,'dbmetadata')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }  

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES];
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
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }

        //GET Source Table GUID list
       $srcTableObj = $this->Metadata->DbMetadataObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_DBMETA_NID,_DBMETA_DESC],[],'list');

       $tblICTgtObj = $this->Metadata->DbMetadataObj;
       $tblICTgtObj->table($tgtTableName);
      
      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('dbmetadata',$tgtLangCode,[_DBMETA_NID => $srcGID]);
                      if($tgtGidExists >0){    
                          $updFields = [];
                          $updFields[_DBMETA_DESC] = $tgtLangText;
                          $condtions[_DBMETA_NID] = $srcGID;          
                          $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('dbmetadata',$srcLangCode,[_DBMETA_NID => $srcGID]);
                          if(!empty($srcRowData)){                              
                              //Insert new row
                              unset($srcRowData[_DBMETA_NID]);
                              $srcRowData[_DBMETA_DESC] = $tgtLangText;
                              $tblICTgtObj->insertData($srcRowData);

                            
                          }
                      }
              }

          }
       }
       
    }

    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM MAP META DATA SHEET

    */
    public function importSubgroupTypeLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'sgrouptype')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }  

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }

       //GET Source Table GUID list
       $srcTableObj = $this->SubgroupType->SubgroupTypeObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_SUBGROUPTYPE_SUBGROUP_TYPE_GID,_SUBGROUPTYPE_SUBGROUP_TYPE_NAME],[],'list');

      $tblICTgtObj = $this->SubgroupType->SubgroupTypeObj;
      $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){  
               //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('sgrouptype',$tgtLangCode,[_SUBGROUPTYPE_SUBGROUP_TYPE_GID => $srcGID]);
                      if($tgtGidExists >0){   
                              $updFields = [];
                              $updFields[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $tgtLangText;
                              $condtions[_SUBGROUPTYPE_SUBGROUP_TYPE_GID] = $srcGID;
             
                             $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('sgrouptype',$srcLangCode,[_SUBGROUPTYPE_SUBGROUP_TYPE_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $tgtLangText;
                              $tgtNIdExists = $this->getLangTableRecordCount('sgrouptype',$tgtLangCode,[_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $srcRowData[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
                               unset($srcRowData[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $tgtNId]);
                              }
                              else{
                                  unset($srcRowData[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }


                          }
                      }
              }

          }
       }
       
    }
    

    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM MAP META DATA SHEET

    */
    public function importMapMetadataLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'mapmetadata')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }  

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
          return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
            
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }

       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
       
        //GET Source Table GUID list
       $srcTableObj = $this->Area->AreaMapMetadataObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_AREAMAP_METADATA_NID,_AREAMAP_METADATA_LAYER_NAME],[],'list');

       $tblICTgtObj = $this->Area->AreaMapMetadataObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('mapmetadata',$tgtLangCode,[_AREAMAP_METADATA_NID => $srcGID]);
                      if($tgtGidExists >0){                
                          $updFields = [];
                          $updFields[_AREAMAP_METADATA_LAYER_NAME] = $tgtLangText;
                          $condtions[_AREAMAP_METADATA_NID] = $srcGID;
                          $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('mapmetadata',$srcLangCode,[_AREAMAP_METADATA_NID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                                unset($srcRowData[_AREAMAP_METADATA_NID]);
                                $srcRowData[_AREAMAP_METADATA_LAYER_NAME] = $tgtLangText;
                                $tblICTgtObj->insertData($srcRowData);

                          }
                      }
              }

          }
       }
       
    }


     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Area Feature Type SHEET

    */
    public function importAreaFeatureTypeLangData($objPHPSheet){
        
      if(!$this->validateImportLangSheetTitle($objPHPSheet,'arfeattype')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
        extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
          return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES];
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
                return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }

        //GET Source Table GUID list
       $srcTableObj = $this->Area->AreaFeatureTypeObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_AREAFEATURE_TYPE_NID,_AREAFEATURE_TYPE],[],'list');

       $tblICTgtObj = $this->Area->AreaFeatureTypeObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('arfeattype',$tgtLangCode,[_AREAFEATURE_TYPE_NID => $srcGID]);
                      if($tgtGidExists >0){   
                                  $updFields = [];
                                  $updFields[_AREAFEATURE_TYPE] = $tgtLangText;
                                  $condtions[_AREAFEATURE_TYPE_NID] = $srcGID;                           
                                  $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('arfeattype',$srcLangCode,[_AREAFEATURE_TYPE_NID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                            unset($srcRowData[_AREAFEATURE_TYPE_NID]);
                            $srcRowData[_AREAFEATURE_TYPE] = $tgtLangText;
                            $tblICTgtObj->insertData($srcRowData);

                           
                          }
                      }
              }

          }
       }
       
    }

     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Area Level SHEET

    */
    public function importAreaLevelLangData($objPHPSheet){
        
         if(!$this->validateImportLangSheetTitle($objPHPSheet,'arlevel')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
           return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
       //GET Source Table GUID list
       $srcTableObj = $this->Area->AreaLevelObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_AREALEVEL_LEVEL_NID,_AREALEVEL_LEVEL_NAME],[],'list');

       $tblICTgtObj = $this->Area->AreaLevelObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){  
               //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('arlevel',$tgtLangCode,[_AREALEVEL_LEVEL_NID => $srcGID]);
                      if($tgtGidExists >0){  
                          $updFields = [];
                          $updFields[_AREALEVEL_LEVEL_NAME] = $tgtLangText;
                          $condtions[_AREALEVEL_LEVEL_NID] = $srcGID;
                          $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('arlevel',$srcLangCode,[_AREALEVEL_LEVEL_NID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              unset($srcRowData[_AREALEVEL_LEVEL_NID]);
                              $srcRowData[_AREALEVEL_LEVEL_NAME] = $tgtLangText;
                              $tblICTgtObj->insertData($srcRowData);

                            
                          }
                      }
             }

          }
       }
      
    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Area SHEET

    */
    public function importAreaLangData($objPHPSheet){
        
        if(!$this->validateImportLangSheetTitle($objPHPSheet,'area')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
           return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }

       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
     
            //GET Source Table GUID list
       $srcTableObj = $this->Area->AreaObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_AREA_AREA_GID,_AREA_AREA_NAME],[],'list');


       $tblICTgtObj = $this->Area->AreaObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  
                   //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('area',$tgtLangCode,[_AREA_AREA_GID => $srcGID]);
                      if($tgtGidExists >0){  
                              $updFields = [];
                              $updFields[_AREA_AREA_NAME] = $tgtLangText;
                              $condtions[_AREA_AREA_GID] = $srcGID;
                              $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('area',$srcLangCode,[_AREA_AREA_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_AREA_AREA_NAME] = $tgtLangText;
                              $tgtNIdExists = $this->getLangTableRecordCount('area',$tgtLangCode,[_AREA_AREA_NID => $srcRowData[_AREA_AREA_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_AREA_AREA_NID];
                               unset($srcRowData[_AREA_AREA_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_AREA_AREA_NID => $tgtNId]);
                              }
                              else{
                                   unset($srcRowData[_AREA_AREA_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                            
                          }
                      }
              }

          }
       }

    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM Footnote SHEET

    */
    public function importFootnoteLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'ftnote')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
           return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];

       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
           //GET Source Table GUID list
       $srcTableObj = $this->Footnote->FootnoteObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_FOOTNOTE_GID,_FOOTNOTE_VAL],[],'list');


       $tblICTgtObj = $this->Footnote->FootnoteObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  
                   //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('ftnote',$tgtLangCode,[_FOOTNOTE_GID => $srcGID]);
                      if($tgtGidExists >0){     
                              $updFields = [];
                              $updFields[_FOOTNOTE_VAL] = $tgtLangText;
                              $condtions[_FOOTNOTE_GID] = $srcGID;   
                              $tblICTgtObj->updateRecords($updFields, $condtions);
                       }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('ftnote',$srcLangCode,[_FOOTNOTE_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_FOOTNOTE_VAL] = $tgtLangText;
                              $tgtNIdExists = $this->getLangTableRecordCount('ftnote',$tgtLangCode,[_FOOTNOTE_NId => $srcRowData[_FOOTNOTE_NId]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_FOOTNOTE_NId];
                               unset($srcRowData[_FOOTNOTE_NId]);
                               $tblICTgtObj->updateRecords($srcRowData,[_FOOTNOTE_NId => $tgtNId]);
                              }
                              else{
                                   unset($srcRowData[_FOOTNOTE_NId]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                            
                          }
                      }
              }
          }
       }

    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM UNIT SHEET

    */
public function importUnitLangData($objPHPSheet){        
        if(!$this->validateImportLangSheetTitle($objPHPSheet,'unit')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
        extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
          return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];

       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
         //GET Source Table GUID list
       $srcTableObj = $this->Unit->UnitObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_UNIT_UNIT_GID,_UNIT_UNIT_NAME],[],'list');

       $tblICTgtObj = $this->Unit->UnitObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);              
              if($srcGID !==false){  
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('unit',$tgtLangCode,[_UNIT_UNIT_GID => $srcGID]);
                      if($tgtGidExists >0){    
                                $updFields = [];
                                $updFields[_UNIT_UNIT_NAME] = $tgtLangText;
                                $condtions[_UNIT_UNIT_GID] = $srcGID;
                                $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('unit',$srcLangCode,[_UNIT_UNIT_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_UNIT_UNIT_NAME] = $tgtLangText;
                               $tgtNIdExists = $this->getLangTableRecordCount('unit',$tgtLangCode,[_UNIT_UNIT_NID => $srcRowData[_UNIT_UNIT_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_UNIT_UNIT_NID];
                               unset($srcRowData[_UNIT_UNIT_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_UNIT_UNIT_NID => $tgtNId]);
                              }
                              else{
                                  unset($srcRowData[_UNIT_UNIT_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                          }
                      }
              }
            

          }
       }

    }

     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM SUBGROUP SHEET

    */
    public function importSubGroupLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'sgroup')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
        extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
          return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }

       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }

        //GET Source Table GUID list
       $srcTableObj = $this->Subgroup->SubgroupObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_NAME],[],'list');

       $tblICTgtObj = $this->Subgroup->SubgroupObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
       $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
         if(!empty($srcLangText) && !empty($tgtLangText)){
             //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                    //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('sgroup',$tgtLangCode,[_SUBGROUP_SUBGROUP_GID => $srcGID]);
                      if($tgtGidExists >0){    
                                  $updFields = [];
                                  $updFields[_SUBGROUP_SUBGROUP_NAME] = $tgtLangText;
                                  $condtions[_SUBGROUP_SUBGROUP_GID] = $srcGID; 
                                  $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('sgroup',$srcLangCode,[_SUBGROUP_SUBGROUP_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_SUBGROUP_SUBGROUP_NAME] = $tgtLangText;
                              $tgtNIdExists = $this->getLangTableRecordCount('sgroup',$tgtLangCode,[_SUBGROUP_SUBGROUP_NID => $srcRowData[_SUBGROUP_SUBGROUP_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_SUBGROUP_SUBGROUP_NID];
                               unset($srcRowData[_SUBGROUP_SUBGROUP_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_SUBGROUP_SUBGROUP_NID => $tgtNId]);
                              }
                              else{
                                   unset($srcRowData[_SUBGROUP_SUBGROUP_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                              
                          }
                      }
              }

          }
       }
       
    }

    public function importIndicatorClassificationsLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'ic')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

         $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
         extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
           return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
       //GET Source Table GUID list
       $srcTableObj = $this->IndicatorClassifications->IndicatorClassificationsObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_IC_IC_GID,_IC_IC_NAME],[],'list');

       
       $tblICTgtObj = $this->IndicatorClassifications->IndicatorClassificationsObj;
       $tblICTgtObj->table($tgtTableName);


      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();      
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){
                      //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('ic',$tgtLangCode,[_IC_IC_GID => $srcGID]);
                      if($tgtGidExists >0){
                          $updFields = [];
                          $updFields[_IC_IC_NAME] = $tgtLangText;
                          $condtions[_IC_IC_GID] = $srcGID;                   
                          $executed = $tblICTgtObj->updateRecords($updFields, $condtions); 
                      }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('ic',$srcLangCode,[_IC_IC_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_IC_IC_NAME] = $tgtLangText;
                               $tgtNIdExists = $this->getLangTableRecordCount('ic',$tgtLangCode,[_IC_IC_NID => $srcRowData[_IC_IC_NID]]);
                              if($tgtNIdExists > 0){
                              $tgtNId = $srcRowData[_IC_IC_NID];
                               unset($srcRowData[_IC_IC_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_IC_IC_NID => $tgtNId]);
                              }
                              else{
                                  unset($srcRowData[_IC_IC_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                          }

                      }
                    
                                
              }
              
              

          }
         
      }
     

    }
    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM SUBGROUP_VALS SHEET

    */
    public function importSubGroupValLangData($objPHPSheet){
        
      if(!$this->validateImportLangSheetTitle($objPHPSheet,'sgroupval')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

        $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
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
           return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }

        //GET Source Table GUID list
       $srcTableObj = $this->SubgroupVals->SubgroupValsObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_SUBGROUP_VAL_SUBGROUP_VAL_GID,_SUBGROUP_VAL_SUBGROUP_VAL],[],'list');


       $tblICTgtObj = $this->SubgroupVals->SubgroupValsObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
               //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('sgroupval',$tgtLangCode,[_SUBGROUP_VAL_SUBGROUP_VAL_GID => $srcGID]);
                      if($tgtGidExists >0){           
                              $updFields = [];
                              $updFields[_SUBGROUP_VAL_SUBGROUP_VAL] = $tgtLangText;
                              $condtions[_SUBGROUP_VAL_SUBGROUP_VAL_GID] = $srcGID;                  
                              $tblICTgtObj->updateRecords($updFields, $condtions);
                        }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('sgroupval',$srcLangCode,[_SUBGROUP_VAL_SUBGROUP_VAL_GID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_SUBGROUP_VAL_SUBGROUP_VAL] = $tgtLangText;
                              $tgtNIdExists = $this->getLangTableRecordCount('sgroupval',$tgtLangCode,[_SUBGROUP_VAL_SUBGROUP_VAL_NID => $srcRowData[_SUBGROUP_VAL_SUBGROUP_VAL_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_SUBGROUP_VAL_SUBGROUP_VAL_NID];
                               unset($srcRowData[_SUBGROUP_VAL_SUBGROUP_VAL_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_SUBGROUP_VAL_SUBGROUP_VAL_NID => $tgtNId]);
                              }
                              else{
                                  unset($srcRowData[_SUBGROUP_VAL_SUBGROUP_VAL_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }

                            
                          }
                      }
              }

          }
       }
       
    }

    /* IMPORT LANAGUAGE TRANSALTION TEXT FROM SUBGROUP_VALS SHEET

    */
    public function importIndicatorLangData($objPHPSheet){
        
      if(!$this->validateImportLangSheetTitle($objPHPSheet,'indicator')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }     

       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
          return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES]; 
       }
      if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode)){
            //Create all language based tables
           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
       }

       //check if table exists or not
       $srcTableName = "UT_Indicator_".$srcLangCode;
       $tgtTableName = "UT_Indicator_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
           return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING]; 
       }
       if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }

         //GET Source Table GUID list
       $srcTableObj = $this->Indicator->IndicatorObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_INDICATOR_INDICATOR_GID,_INDICATOR_INDICATOR_NAME],[],'list');

       $tblICTgtObj = $this->Indicator->IndicatorObj;
       $tblICTgtObj->table($tgtTableName);

      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
               //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
            
              if($srcGID !== false){
                 
                    //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('indicator',$tgtLangCode,[_INDICATOR_INDICATOR_GID => $srcGID]);
               
                      if($tgtGidExists >0){ 
                                               
                                $updFields = [];
                                $updFields[_INDICATOR_INDICATOR_NAME] = $tgtLangText;
                                $condtions[_INDICATOR_INDICATOR_GID] = $srcGID;                
                                if(!empty($condtions))                
                                $tblICTgtObj->updateRecords($updFields, $condtions);
                      }
                      else{
                         
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('indicator',$srcLangCode,[_INDICATOR_INDICATOR_GID => $srcGID]);
                       
                          if(!empty($srcRowData)){
                              //Insert new row
                              $srcRowData[_INDICATOR_INDICATOR_NAME] = $tgtLangText; 
                              
                              $tgtNIdExists = $this->getLangTableRecordCount('indicator',$tgtLangCode,[_INDICATOR_INDICATOR_NID => $srcRowData[_INDICATOR_INDICATOR_NID]]);
                              if($tgtNIdExists > 0){
                               $tgtNId = $srcRowData[_INDICATOR_INDICATOR_NID];
                               unset($srcRowData[_INDICATOR_INDICATOR_NID]);
                               $tblICTgtObj->updateRecords($srcRowData,[_INDICATOR_INDICATOR_NID => $tgtNId]);
                              }
                              else{
                                 // pr($tblICTgtObj);
                                  unset($srcRowData[_INDICATOR_INDICATOR_NID]);
                                  $tblICTgtObj->insertData($srcRowData);

                              }
                             
                            
                          }
               
                        }
                      
              }

              }
         }
      
    }

    /*
    Function to prepare language sheet heading row
    */
    public function addLanguageHeadingRow($objPHPSheet, $fromLangName, $toLangName) {        
        $objPHPSheet->setCellValue('A1', $fromLangName);
        $objPHPSheet->setCellValue('B1', $toLangName);
    }
    /* Function to add sheetTitle for language export
    *
    */
    public function addExportSheetTitle($objPHPSheet, $sheetCode) {        
       $objPHPSheet->setTitle($this->LangSheetNames[$sheetCode]);
    }


   
    /* Function to specified language table object by table code & language code
    *
    */
    public function getLanguageTableObject($langTableCode,$langCode){
        $tblObj = NULL;
        if(isset($this->LangTableNames[$langTableCode])){
            $tableName = $this->LangTableNames[$langTableCode];
            $tableName = str_replace('{suffix}',$langCode,$tableName);
            switch($langTableCode){
                case 'ic':
                $tblObj = $this->IndicatorClassifications->IndicatorClassificationsObj;
                $tblObj->table($tableName);
                break;
                case 'indicator':
                $tblObj = $this->Indicator->IndicatorObj;
                $tblObj->table($tableName);
                break;
                case 'unit':
                $tblObj = $this->Unit->UnitObj;
                $tblObj->table($tableName);
                break;
                case 'area':
                $tblObj = $this->Area->AreaObj;
                $tblObj->table($tableName);
                break;
                case 'arlevel':
                $tblObj = $this->Area->AreaLevelObj;
                $tblObj->table($tableName);
                break;
                case 'arfeattype':
                $tblObj = $this->Area->AreaFeatureTypeObj;
                $tblObj->table($tableName);
                break;
                case 'sgroup':
                $tblObj = $this->Subgroup->SubgroupObj;
                $tblObj->table($tableName);
                break;
                case 'sgrouptype':
                $tblObj = $this->SubgroupType->SubgroupTypeObj;
                $tblObj->table($tableName);
                break;
                case 'sgroupval':
                $tblObj = $this->SubgroupVals->SubgroupValsObj;
                $tblObj->table($tableName);
                break;
                case 'ftnote':
                $tblObj = $this->Footnote->FootnoteObj;
                $tblObj->table($tableName);
                break;
                case 'dbmetadata':
                $tblObj = $this->Metadata->DbMetadataObj;
                $tblObj->table($tableName);
                break;
                case 'metadatacat':
                $tblObj = $this->Metadata->MetadatacategoryObj;
                $tblObj->table($tableName);
                break;
                case 'mapmetadata':
                $tblObj = $this->Area->AreaMapMetadataObj;
                $tblObj->table($tableName);
                break;
                case 'metadatareport':
                $tblObj = $this->Metadata->MetadatareportObj;
                $tblObj->table($tableName);
                break;                
                default:
                break;
            }
        }
        return $tblObj;

    }

    /*
    Function to get table records from related language table
    */
    function getTableRecords($langTableCode, $languageCode='en', $params=[]) {
       // pr(func_get_args());
        $tblData = [];
        $suffix = '{suffix}';
        $dbTblName = $this->LangTableNames[$langTableCode];
        $dbTblName = str_replace($suffix, $languageCode, $dbTblName);
        if(!empty($dbTblName)) {
            if($this->LangObj->check_table_exists(strtolower($dbTblName))) {               
                // get the related table object              
                $bMetaDataTblObj = $this->getLanguageTableObject($langTableCode,$languageCode);                
                if(!empty($bMetaDataTblObj)){                    
                $tblData = $bMetaDataTblObj->getRecords($params['fields'], $params['conditions'], 'list');
                }
            }            
        }

        return $tblData;
    }

    /*
    Function to export Indicator Classifications sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportIcWorksheetData(&$objPHPSheet, $startRow = 1, $fromLangCode='en', $toLangCode='') {
        //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'ic');

        //IC DATA
        $returnData = [];
        $params = [];
        $params['fields'] = array(_IC_IC_GID,_IC_IC_NAME);
        $params['conditions'] = [];           
                 
        $IndicatorlistFrom = $this->getTableRecords('ic', $fromLangCode, $params);
        // if from language table exists
        if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                $IndicatorlistTo = $this->getTableRecords('ic', $toLangCode, $params);
               
                $row = $startRow;
                    foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                    {
                        $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                        if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                            $toLangStr = $IndicatorlistTo[$IndicatorKey];
                        }
                        else {
                        $toLangStr = '#'.$IndicatorVal;
                        }
                        
                        $objPHPSheet->setCellValue('B'.($row),  $toLangStr);
                
                        $row++;

                    }


          }
        else {
            $returnData = ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];
        }

        return $returnData;
    }

    /*
    Function to export Indicator sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportIndicatorWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
        //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'indicator');
        //Indicator list DATA
            $params = [];
            $params['fields'] = array(_INDICATOR_INDICATOR_GID,_INDICATOR_INDICATOR_NAME);
            $params['conditions'] = [];          
          
             $IndicatorlistFrom = $this->getTableRecords('indicator', $fromLangCode, $params);
            // if from language table exists
            if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                $IndicatorlistTo = $this->getTableRecords('indicator', $toLangCode, $params);
                

                $row = $startRow;
                    foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                    {
                        $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                        if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                              $toLangStr = $IndicatorlistTo[$IndicatorKey];
                        }
                        else {
                        $toLangStr = '#'.$IndicatorVal;
                        }

                        $objPHPSheet->setCellValue('B'.($row),  $toLangStr);
                       

                        $row++;

                    }


          }
        else{
              
              return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

          }

    }
    
    /*
    Function to export Subgroup Val sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
 public function exportSubgroupValWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
        //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'sgroupval');
         //Subgroup values list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_VAL_SUBGROUP_VAL_GID,_SUBGROUP_VAL_SUBGROUP_VAL);
            $params['conditions'] = [];    

          $IndicatorlistFrom = $this->getTableRecords('sgroupval', $fromLangCode, $params);
            // if from language table exists
            if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                $IndicatorlistTo = $this->getTableRecords('sgroupval', $toLangCode, $params);

                $row = $startRow;
                  foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                    {
                        $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                        if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                              $toLangStr = $IndicatorlistTo[$IndicatorKey];
                        }
                        else {
                        $toLangStr = '#'.$IndicatorVal;
                        }

                        $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                        $row++;

                    }


          }
        else{
              
              return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

          }


    }
	/*
    Function to export Unit sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportUnitSheetSata(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'unit');
        //Unit list DATA
            $params = [];
            $params['fields'] = array(_UNIT_UNIT_GID,_UNIT_UNIT_NAME);
            $params['conditions'] = [];       

           $IndicatorlistFrom = $this->getTableRecords('unit', $fromLangCode, $params);
            // if from language table exists
            if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                $IndicatorlistTo = $this->getTableRecords('unit', $toLangCode, $params);

                $row = $startRow;
                  foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                    {
                        $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                        if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                              $toLangStr = $IndicatorlistTo[$IndicatorKey];
                        }
                        else {
                        $toLangStr = '#'.$IndicatorVal;
                        }

                        $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                        $row++;

                    }


          }
        else{
              
              return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

          }


    }
    /*
    Function to export Area sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
public function exportAreaSheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'area');
         //Area list DATA
            $params = [];
            $params['fields'] = array(_AREA_AREA_GID,_AREA_AREA_NAME);
            $params['conditions'] = [];
            
           $IndicatorlistFrom = $this->getTableRecords('area', $fromLangCode, $params);
            // if from language table exists
            if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                $IndicatorlistTo = $this->getTableRecords('area', $toLangCode, $params);

                $row = $startRow;
                  foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                    {
                        $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                        if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                             $toLangStr = $IndicatorlistTo[$IndicatorKey];
                        }
                        else {
                        $toLangStr = '#'.$IndicatorVal;
                        }

                        $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                        $row++;

                    }


          }
        else{
              
              return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

          }

    }
    /*
    Function to export Area Level sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportArealevelSheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'arlevel');
            //Area Level list DATA
            $params = [];
            $params['fields'] = array(_AREALEVEL_LEVEL_NID,_AREALEVEL_LEVEL_NAME);
            $params['conditions'] = [];
           
             $IndicatorlistFrom = $this->getTableRecords('arlevel', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('arlevel', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                 $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }

    }
    /*
    Function to export Subgroup Type sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportSubgroupTypeSheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'sgrouptype');
            //Subgroup type list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUPTYPE_SUBGROUP_TYPE_GID,_SUBGROUPTYPE_SUBGROUP_TYPE_NAME);
            $params['conditions'] = [];
            
            $IndicatorlistFrom = $this->getTableRecords('sgrouptype', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('sgrouptype', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                  $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }

    }
    /*
    Function to export Subgroup sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */

public function exportSubgroupSheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'sgroup');
         //Subgroup list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_NAME);
            $params['conditions'] = [];

          $IndicatorlistFrom = $this->getTableRecords('sgroup', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('sgroup', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                 $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }
    }
    /*
    Function to export FootNote sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */

 public function exportFootnoteWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'ftnote');
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_FOOTNOTE_GID,_FOOTNOTE_VAL);
            $params['conditions'] = [];
          
           $IndicatorlistFrom = $this->getTableRecords('ftnote', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('ftnote', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                 $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }


    }
	/*
    Function to export Area Feature Type sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportAreaFeatureTypeWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'arfeattype');
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_AREAFEATURE_TYPE_NID,_AREAFEATURE_TYPE);
            $params['conditions'] = [];
           
            $IndicatorlistFrom = $this->getTableRecords('arfeattype', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('arfeattype', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                 $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }



    }
	
   
    /*
    Function to export Metadata Category sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    
   public function exportMetadataCategoryWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
             //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'metadatacat');
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_META_CATEGORY_NID,_META_CATEGORY_NAME);
            $params['conditions'] = [];
           
            $IndicatorlistFrom = $this->getTableRecords('metadatacat', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('metadatacat', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                 $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }



    }

   
    /*
    Function to export DB Metadata sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    public function exportDbMetadataWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'dbmetadata');
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_DBMETA_NID,_DBMETA_DESC);
            $params['conditions'] = [];
          
            $IndicatorlistFrom = $this->getTableRecords('dbmetadata', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('dbmetadata', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                  $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }


    }
    /*
    Function to export Area Map Metadata sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    
  public function exportAreaMapMetadataWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
         //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'mapmetadata');
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array('Metadata_NId','Layer_Name');
            $params['conditions'] = [];
          
           $IndicatorlistFrom = $this->getTableRecords('mapmetadata', $fromLangCode, $params);
                // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('mapmetadata', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                  $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = $IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }


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
           

         $translableLangTblList = array('UT_Indicator_{suffix}','UT_Area_{suffix}','UT_Area_Feature_Type_{suffix}','UT_Area_Level_{suffix}','UT_Area_Map_Metadata_{suffix}','UT_DBMetadata_{suffix}','UT_FootNote_{suffix}','UT_Indicator_Classifications_{suffix}','UT_Metadata_Category_{suffix}','UT_Subgroup_{suffix}','UT_Subgroup_Type_{suffix}','UT_Subgroup_Vals_{suffix}','UT_Unit_{suffix}','ut_metadatareport_{suffix}');   

         $activeDbType = $this->getActiveDbType();

         foreach($translableLangTblList as $translableLangTblName){

             $srcTableName = $translableLangTblName;
             $srcTableName = str_replace('{suffix}',$srcLangCode,$srcTableName);
            

           $tgtTableName = $translableLangTblName;
           $tgtTableName = str_replace('{suffix}',$tgtLangCode,$tgtTableName);
           
           
             if($this->LangObj->check_table_exists(strtolower($srcTableName)) && !$this->LangObj->check_table_exists(strtolower($tgtTableName))){

                  if($activeDbType == 'mysql'){

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
    public function delmssqltables($langEXT=''){
        
        
        $query = "DELETE FROM UT_Language WHERE Language_Code='".$langEXT."'";   //  Language_Code
        $this->LangObj->executeTableCreateQuery($query);
        
        $queryInd = "DROP TABLE UT_Indicator_'".$langEXT."'";                    //  UT_Indicator_en
        $this->LangObj->executeTableCreateQuery($queryInd);
             
        
        $queryIndClass = "DROP TABLE UT_Indicator_Classifications_'".$langEXT."'"; //  UT_Indicator_Classifications_en
        $this->LangObj->executeTableCreateQuery($queryIndClass);
        
        $queryUnit = "DROP TABLE UT_Unit_'".$langEXT."'";                        //  UT_Unit_en
        $this->LangObj->executeTableCreateQuery($queryUnit);
        
        $queryArea = "DROP TABLE  UT_Area_'".$langEXT."'";                       //  UT_Area_en
        $this->LangObj->executeTableCreateQuery($queryArea);
        
        $queryAreaFeatType = "DROP TABLE   UT_Area_Feature_Type_'".$langEXT."'"; // UT_Area_Feature_Type_en
        $this->LangObj->executeTableCreateQuery($queryAreaFeatType);
        
        $queryAreaLevel = "DROP TABLE   UT_Area_Level_'".$langEXT."'";           // UT_Area_Level_en
        $this->LangObj->executeTableCreateQuery($queryAreaLevel);
        
        $queryAreaMapmeta = "DROP TABLE   UT_Area_Map_Metadata_'".$langEXT."'";  // UT_Area_Map_Metadata_en
        $this->LangObj->executeTableCreateQuery($queryAreaMapmeta);
        
        $queryAreaMetaCateg = "DROP TABLE   UT_Metadata_Category_'".$langEXT."'"; //UT_Metadata_Category_en
        $this->LangObj->executeTableCreateQuery($queryAreaMetaCateg);
        
        $querysg = "DROP TABLE UT_Subgroup_'".$langEXT."'";
        $this->LangObj->executeTableCreateQuery($querysg);  //UT_Subgroup_zh
        
        $querysgType = "DROP TABLE UT_Subgroup_Type_'".$langEXT."'";  //UT_Subgroup_Type_zh       
        $this->LangObj->executeTableCreateQuery($querysgType);
        
        $querysgVals = "DROP TABLE UT_Subgroup_Vals_'".$langEXT."'";  //UT_Subgroup_Vals_zh       
        $this->LangObj->executeTableCreateQuery($querysgVals);
        
        $queryFootnote = "DROP TABLE UT_FootNote_'".$langEXT."'";  //UT_FootNote_zh       
        $this->LangObj->executeTableCreateQuery($queryFootnote);
        
        $queryDbMetadata = "DROP TABLE UT_DBMetadata_'".$langEXT."'";  //UT_DBMetadata_zh       
        $this->LangObj->executeTableCreateQuery($queryDbMetadata);
        
        $queryMetadatareport = "DROP TABLE ut_metadatareport_'".$langEXT."'";  //ut_metadatareport_zh       
        $this->LangObj->executeTableCreateQuery($queryMetadatareport);
       
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->LangObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateRecords($dataArray = []) {
        return $this->LangObj->insertOrUpdateRecords($dataArray);
    }
    
    public function setDefaultLang($nid) {
        // Set lang_detfault as 0 to all
        $newdataValue =$olddataValue='';
		$olddata = $this->getRecords([_LANGUAGE_LANGUAGE_NAME],[_LANGUAGE_LANGUAGE_DEFAULT => '1']);
        if(!empty($olddata)){
		 $olddataValue = current($olddata)[_LANGUAGE_LANGUAGE_NAME];
		}
		$this->updateRecords([_LANGUAGE_LANGUAGE_DEFAULT => 0]);
        
        // Set lang_default as 1 to requested nid
        $dataArray[] = [_LANGUAGE_LANGUAGE_NID => $nid, _LANGUAGE_LANGUAGE_DEFAULT => 1];
		$newdata = $this->getRecords([_LANGUAGE_LANGUAGE_NAME],[_LANGUAGE_LANGUAGE_NID => $nid]);
        if(!empty($newdata)){
		 $newdataValue = current($newdata)[_LANGUAGE_LANGUAGE_NAME];
		}
		$return =  $this->insertOrUpdateRecords($dataArray);
		if($return>0)
		$this->TransactionLogs->createLog(_UPDATE, _MODULE_NAME_ADMINISTRATION, _MODULE_NAME_LANGUAGE, $nid, _DONE, '', '', $olddataValue, $newdataValue, '');
         else
		$this->TransactionLogs->createLog(_UPDATE, _MODULE_NAME_ADMINISTRATION, _MODULE_NAME_LANGUAGE, $nid, _FAILED, '', '', $olddataValue, $newdataValue, '');
        	 
    }

    /*
    Function to export Metadata Report sheet
    * Params List
    * Sheet Object
    * Starting Row index
    * FromLanguageCode i.e en|fr
    * ToLanguageCode i.e en|fr
    */
    
   public function exportMetadataReportWorksheetData(&$objPHPSheet,$startRow = 1,$fromLangCode='en',$toLangCode='')
    {
             //set export sheet title
        $this->addExportSheetTitle($objPHPSheet,'metadatareport');
            //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_META_REPORT_NID,_META_REPORT_METADATA);
            $params['conditions'] = [];
            $IndicatorlistFrom = $this->getTableRecords('metadatareport', $fromLangCode, $params);           
            // if from language table exists
                if(!empty($IndicatorlistFrom) && is_array($IndicatorlistFrom)) {
            
                    $IndicatorlistTo = $this->getTableRecords('metadatareport', $toLangCode, $params);

                    $row = $startRow;
                      foreach($IndicatorlistFrom as $IndicatorKey=>$IndicatorVal)
                        {
                            $objPHPSheet->setCellValue('A'.($row), $IndicatorVal);

                            if(isset($IndicatorlistTo[$IndicatorKey]) && !empty($IndicatorlistTo[$IndicatorKey])) {
                                 $toLangStr = $IndicatorlistTo[$IndicatorKey];
                            }
                            else {
                            $toLangStr = '#'.$IndicatorVal;
                            }

                            $objPHPSheet->setCellValue('B'.($row),  $toLangStr);

                            $row++;

                        }


              }
            else{
              
                  return ['errCode'=>_ERROR_EXPORT_LANG_FROMTBL_MISSING];

              }

    }
     /* IMPORT LANAGUAGE TRANSALTION TEXT FROM  METADATA REPORT SHEET

    */
    public function importMetadataReportLangData($objPHPSheet){
        
       if(!$this->validateImportLangSheetTitle($objPHPSheet,'metadatareport')){
           return ['errCode'=>_ERROR_IMPORT_LANG_INVALID_SHEET];
       }      
       $sheetLangCodes = $this->getImportSheetLanguageCodes($objPHPSheet);
       extract($sheetLangCodes);

       if((empty($srcLangCode) || empty($tgtLangCode))){
           
           return ['errCode'=>_ERROR_INVALID_LANG_IMPORT_CODES];
       }

      if(!$this->LangObj->checkLanguageExistsByCode($tgtLangCode))
       {
           //Create all language based tables

           $this->createTranslableLanguageTables($srcLangCode,$srcLangName,$tgtLangCode,$tgtLangName);
          
       }
     
       //check if table exists or not
       $srcTableName = "ut_metadatareport_".$srcLangCode;
       $tgtTableName = "ut_metadatareport_".$tgtLangCode;

       $srcTblExists = $this->LangObj->check_table_exists(strtolower($srcTableName));
       $tgtTblExists = $this->LangObj->check_table_exists(strtolower($tgtTableName));

       if(!$srcTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_FROMTBL_MISSING];
       }

        if(!$tgtTblExists){
            return ['errCode'=>_ERROR_IMPORT_LANG_TOTBL_MISSING];
       }
         //GET Source Table GUID list
       $srcTableObj = $this->Metadata->MetadatareportObj;
       $srcTableObj->table($srcTableName);
       $srcTblGIDList = $srcTableObj->getRecords([_META_REPORT_NID,_META_REPORT_METADATA],[],'list');      
       $tblICTgtObj = $this->Metadata->MetadatareportObj;
       $tblICTgtObj->table($tgtTableName);       
      //Now replace excel entries in the table name
      $highestRowIndex = $objPHPSheet->getHighestRow();
      //$objPHPSheet->getCell('A1')->getValue()
      for($i=2;$i<=$highestRowIndex;$i++){
          
         $srcLangText = $objPHPSheet->getCell('A'.$i)->getValue();
         $tgtLangText = $objPHPSheet->getCell('B'.$i)->getValue();
          
          if(!empty($srcLangText) && !empty($tgtLangText)){
              
             //Get source GID 
              $srcGID = array_search($srcLangText,$srcTblGIDList);
              if($srcGID !==false){ 
                  //check if srcGID exists in target table
                      $tgtGidExists = $this->getLangTableRecordCount('metadatareport',$tgtLangCode,[_META_REPORT_NID => $srcGID]);
                      if($tgtGidExists >0){
  
                            $updFields = [];
                            $updFields[_META_REPORT_METADATA] = $tgtLangText;
                            $condtions[_META_REPORT_NID] = $srcGID;              
                            $tblICTgtObj->updateRecords($updFields, $condtions);
                       }
                      else{
                          //Get src row record
                          $srcRowData = $this->getLangTableFirstRecord('metadatareport',$srcLangCode,[_META_REPORT_NID => $srcGID]);
                          if(!empty($srcRowData)){
                              //Insert new row
                              unset($srcRowData[_META_REPORT_NID]);
                              $srcRowData[_META_REPORT_METADATA] = $tgtLangText;
                              $tblICTgtObj->insertData($srcRowData);

                          }
                      }

          }
          
       }

      }
       
    }

    /* Function to return count for language tables
    *
    * Params :
    * Lanaguage table code
    * language code
    * condtions array

    */
    public function getLangTableRecordCount($tableCode,$langCode,$conditions =[]){
        $count = 0;
        $tblObj = $this->getLanguageTableObject($tableCode,$langCode);
        if(!empty($tblObj)){            
            $count = $tblObj->find()->where($conditions)->count();
        }
        return  $count;
    }

     /* Function to return first record for language tables
    *
    * Params :
    * Lanaguage table code
    * language code
    * condtions array

    */
    public function getLangTableFirstRecord($tableCode,$langCode,$conditions =[]){
        $res = [];
        $tblObj = $this->getLanguageTableObject($tableCode,$langCode);
        if(!empty($tblObj)){            
            $res = $tblObj->find()->where($conditions)->hydrate(false)->first();
        }
        return  $res;
    }
   
}
