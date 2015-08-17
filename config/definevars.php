<?php

// Defining CONSTANTS
$website_base_url = "http://" . $_SERVER['HTTP_HOST'];
$website_base_url .= preg_replace('@/+$@', '', dirname($_SERVER['SCRIPT_NAME'])) . "/";
$website_base_url = str_replace('webroot/', '', $website_base_url);

return [
    define('_WEBSITE_URL', $website_base_url),
    //Common
    define('_DEVINFO', 'DI7'),
    // Indicator Table
    define('_INDICATOR_INDICATOR_NID', 'Indicator_NId'),
    define('_INDICATOR_INDICATOR_NAME', 'Indicator_Name'),
    define('_INDICATOR_INDICATOR_GID', 'Indicator_GId'),
    define('_INDICATOR_INDICATOR_INFO', 'Indicator_Info'),
    define('_INDICATOR_INDICATOR_GLOBAL', 'Indicator_Global'),
    define('_INDICATOR_SHORT_NAME', 'Short_Name'),
    define('_INDICATOR_KEYWORDS', 'Keywords'),
    define('_INDICATOR_INDICATOR_ORDER', 'Indicator_Order'),
    define('_INDICATOR_DATA_EXIST', 'Data_Exist'),
    define('_INDICATOR_HIGHISGOOD', 'HighIsGood'),
    // Unit Table
    define('_UNIT_UNIT_NID', 'Unit_NId'),
    define('_UNIT_UNIT_NAME', 'Unit_Name'),
    define('_UNIT_UNIT_GID', 'Unit_GId'),
    define('_UNIT_UNIT_GLOBAL', 'Unit_Global'),
    // Subgroup Type table
    define('_SUBGROUPTYPE_SUBGROUP_TYPE_NID', 'Subgroup_Type_NId'),
    define('_SUBGROUPTYPE_SUBGROUP_TYPE_NAME', 'Subgroup_Type_Name'),
    define('_SUBGROUPTYPE_SUBGROUP_TYPE_GID', 'Subgroup_Type_GID'),
    define('_SUBGROUPTYPE_SUBGROUP_TYPE_GLOBAL', 'Subgroup_Type_Global'),
    define('_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER', 'Subgroup_Type_Order'),
    // Subgroup table
    define('_SUBGROUP_SUBGROUP_NID', 'Subgroup_NId'),
    define('_SUBGROUP_SUBGROUP_NAME', 'Subgroup_Name'),
    define('_SUBGROUP_SUBGROUP_GID', 'Subgroup_GId'),
    define('_SUBGROUP_SUBGROUP_GLOBAL', 'Subgroup_Global'),
    define('_SUBGROUP_SUBGROUP_TYPE', 'Subgroup_Type'),
    define('_SUBGROUP_SUBGROUP_ORDER', 'Subgroup_Order'),
    // Subgroup_vals table
    define('_SUBGROUP_VAL_SUBGROUP_VAL_NID', 'Subgroup_Val_NId'),
    define('_SUBGROUP_VAL_SUBGROUP_VAL', 'Subgroup_Val'),
    define('_SUBGROUP_VAL_SUBGROUP_VAL_GID', 'Subgroup_Val_GId'),
    define('_SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL', 'Subgroup_Val_Global'),
    define('_SUBGROUP_VAL_SUBGROUP_VAL_ORDER', 'Subgroup_Val_Order'),
    // Subgroup_vals_subgroup table
    define('_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID', 'Subgroup_Val_Subgroup_NId'),
    define('_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID', 'Subgroup_Val_NId'),
    define('SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID', 'Subgroup_NId'),
    // Time Period table
    define('_TIMEPERIOD_TIMEPERIOD_NID', 'TimePeriod_NId'),
    define('_TIMEPERIOD_TIMEPERIOD', 'TimePeriod'),
    define('_TIMEPERIOD_STARTDATE', 'StartDate'),
    define('_TIMEPERIOD_ENDDATE', 'EndDate'),
    define('_TIMEPERIOD_PERIODICITY', 'Periodicity'),
    // Indicator_Classifications Table
    define('_IC_IC_NID', 'IC_NId'),
    define('_IC_IC_PARENT_NID', 'IC_Parent_NId'),
    define('_IC_IC_GID', 'IC_GId'),
    define('_IC_IC_NAME', 'IC_Name'),
    define('_IC_IC_GLOBAL', 'IC_Global'),
    define('_IC_IC_INFO', 'IC_Info'),
    define('_IC_IC_TYPE', 'IC_Type'),
    define('_IC_IC_SHORT_NAME', 'IC_Short_Name'),
    define('_IC_PUBLISHER', 'Publisher'),
    define('_IC_TITLE', 'Title'),
    define('_IC_DIYEAR', 'DIYear'),
    define('_IC_SOURCELINK1', 'SourceLink1'),
    define('_IC_SOURCELINK2', 'SourceLink2'),
    define('_IC_IC_ORDER', 'IC_Order'),
    define('_IC_ISBN', 'ISBN'),
    define('_IC_NATURE', 'Nature'),
    // Indicator_Unit_Subgroup Table
    define('_IUS_IUSNID', 'IUSNId'),
    define('_IUS_INDICATOR_NID', 'Indicator_NId'),
    define('_IUS_UNIT_NID', 'Unit_NId'),
    define('_IUS_SUBGROUP_VAL_NID', 'Subgroup_Val_NId'),
    define('_IUS_MIN_VALUE', 'Min_Value'),
    define('_IUS_MAX_VALUE', 'Max_Value'),
    define('_IUS_SUBGROUP_NIDS', 'Subgroup_NIds'),
    define('_IUS_DATA_EXISTS', 'Data_Exist'),
    define('_IUS_ISDEFAULTSUBGROUP', 'IsDefaultSubgroup'),
    define('_IUS_AVLMINDATAVALUE', 'AvlMinDataValue  '),
    define('_IUS_AVLMAXDATAVALUE', 'AvlMaxDataValue'),
    define('_IUS_AVLMINTIMEPERIOD', 'AvlMinTimePeriod'),
    define('_IUS_AVLMAXTIMEPERIOD', 'AvlMaxTimePeriod'),
    // IC_IUS Table
    define('_ICIUS_IC_IUSNID', 'IC_IUSNId'),
    define('_ICIUS_IC_NID', 'IC_NId'),
    define('_ICIUS_IUSNID', 'IUSNId'),
    define('_ICIUS_RECOMMENDEDSOURCE', 'RecommendedSource'),
    define('_ICIUS_IC_IUS_ORDER', 'IC_IUS_Order'),
    define('_ICIUS_IC_IUS_LABEL', 'IC_IUS_Label'),
    // Area table
    define('_AREA_AREA_NID', 'Area_NId'),
    define('_AREA_PARENT_NId', 'Area_Parent_NId'),
    define('_AREA_AREA_ID', 'Area_ID'),
    define('_AREA_AREA_NAME', 'Area_Name'),
    define('_AREA_AREA_GID', 'Area_GId'),
    define('_AREA_AREA_LEVEL', 'Area_Level'),
    define('_AREA_AREA_MAP', 'Area_Map'), //NI
    define('_AREA_AREA_BLOCK', 'Area_Block'),
    define('_AREA_AREA_GLOBAL', 'Area_Global'), //NI
    define('_AREA_DATA_EXIST', 'Data_Exist'),
    define('_AREA_AREA_SHORT_NAME', 'AreaShortName'),
    // Area Level table
    define('_AREALEVEL_LEVEL_NID', 'Level_NId'),
    define('_AREALEVEL_AREA_LEVEL', 'Area_Level'),
    define('_AREALEVEL_LEVEL_NAME', 'Area_Level_Name'),
    // Area Map
    define('_AREAMAP_AREA_MAP_NID', 'Area_Map_NId'),
    define('_AREAMAP_AREA_NID', 'Area_NId'),
    define('_AREAMAP_FEATURE_LAYER', 'Feature_Layer'),
    define('_AREAMAP_FEATURE_TYPE_NID', 'Feature_Type_NId'),
    define('_AREAMAP_LAYER_NID', 'Layer_NId'),
    // Area Map Layer
    define('_AREAMAPLAYER_LAYER_NID', 'Layer_NId'),
    define('_AREAMAPLAYER_LAYER_SIZE', 'Layer_Size'),
    define('_AREAMAPLAYER_LAYER_SHP', 'Layer_Shp'),
    define('_AREAMAPLAYER_LAYER_SHX', 'Layer_Shx'),
    define('_AREAMAPLAYER_LAYER_DBF', 'Layer_dbf'),
    define('_AREAMAPLAYER_LAYER_TYPE', 'Layer_Type'),
    define('_AREAMAPLAYER_MINX', 'MinX'),
    define('_AREAMAPLAYER_MINY', 'MinY'),
    define('_AREAMAPLAYER_MAXX', 'MaxX'),
    define('_AREAMAPLAYER_MAXY', 'MaxY'),
    define('_AREAMAPLAYER_START_DATE', 'Start_Date'),
    define('_AREAMAPLAYER_END_DATE', 'End_Date'),
    define('_AREAMAPLAYER_METADATA_NID', 'Metadata_NId'),
    define('_AREAMAPLAYER_UPDATE_TIMESTAMP', 'Update_Timestamp'),
    // Area Map Metadata
    define('_AREAMAPMETADATA_METADATA_NID', 'Metadata_NId'),
    define('_AREAMAPMETADATA_LAYER_NID', 'Layer_NId'),
    define('_AREAMAPMETADATA_METADATA_TEXT', 'Metadata_Text'),
    define('_AREAMAPMETADATA_LAYER_NAME', 'Layer_Name'),
    // database connections table
    define('_DATABASE_CONNECTION_DEVINFO_DB_CONN', 'devinfo_db_connection'),
    define('_DATABASE_CONNECTION_DEVINFO_DB_ID', 'ID'),
    define('_DATABASE_CONNECTION_DEVINFO_DB_ARCHIVED', 'archived'),
    define('_DATABASE_CONNECTION_DEVINFO_DB_CREATEDBY', 'createdby'),
    define('_DATABASE_CONNECTION_DEVINFO_DB_MODIFIEDBY', 'modifiedby'),
    // database Roles  table
    define('_DATABASE_ROLE_ID', 'id'),
    define('_DATABASE_ROLE', 'role'),
    define('_DATABASE_ROLE_NAME', 'role_name'),
    define('_DATABASE_ROLE_DESC', 'description'),
    // users   table
    define('_USER_ID', 'id'),
    define('_USER_NAME', 'name'),
    define('_USER_STATUS', 'status'),
    define('_USER_LASTLOGGEDIN', 'lastloggedin'),
    define('_USER_EMAIL', 'email'),
    define('_USER_ROLE_ID', 'role_id'),
    define('_USER_PASSWORD', 'password'),
    define('_USER_CREATED', 'created'),
    define('_USER_CREATEDBY', 'createdby'),
    define('_USER_MODIFIED', 'modified'),
    define('_USER_MODIFIEDBY', 'modifiedby'),
    // R_users_databases  table
    define('_RUSERDB_ID', 'id'),
    define('_RUSERDB_USER_ID', 'user_id'),
    define('_RUSERDB_DB_ID', 'db_id'),
    define('_RUSERDB_CREATED', 'created'),
    define('_RUSERDB_CREATEDBY', 'createdby'),
    define('_RUSERDB_MODIFIED', 'modified'),
    define('_RUSERDB_MODIFIEDBY', 'modifiedby'),
    // R_users_databases_roles table
    define('_RUSERDBROLE_ID', 'id'),
    define('_RUSERDBROLE_AREA_ACCESS', 'area_access'),
    define('_RUSERDBROLE_INDICATOR_ACCESS', 'indicator_access'),
    define('_RUSERDBROLE_ROLE_ID', 'role_id'),
    define('_RUSERDBROLE_USER_DB_ID', 'user_database_id'),
    define('_RUSERDBROLE_CREATED', 'created'),
    define('_RUSERDBROLE_CREATEDBY', 'createdby'),
    define('_RUSERDBROLE_MODIFIED', 'modified'),
    define('_RUSERDBROLE_MODIFIEDBY', 'modifiedby'),
    // m_application_logs  table
    define('_MAPPLICATIONLOG_ID', 'id'),
    define('_MAPPLICATIONLOG_MODULE', 'module'),
    define('_MAPPLICATIONLOG_ACTION', 'action'),
    define('_MAPPLICATIONLOG_DESC', 'description'),
    define('_MAPPLICATIONLOG_CREATED', 'created'),
    define('_MAPPLICATIONLOG_CREATEDBY', 'createdby'),
    define('_MAPPLICATIONLOG_IPADDRESS', 'ip_address'),
    // M_transaction_logs table
    define('_MTRANSACTIONLOGS_ID', 'id'),
    define('_MTRANSACTIONLOGS_USER_ID', 'user_id'),
    define('_MTRANSACTIONLOGS_DB_ID', 'db_id'),
    define('_MTRANSACTIONLOGS_ACTION', 'action'),
    define('_MTRANSACTIONLOGS_MODULE', 'module'),
    define('_MTRANSACTIONLOGS_SUBMODULE', 'submodule'),
    define('_MTRANSACTIONLOGS_IDENTIFIER', 'identifier'),
    define('_MTRANSACTIONLOGS_PREVIOUSVALUE', 'previousvalue'),
    define('_MTRANSACTIONLOGS_NEWVALUE', 'newvalue'),
    define('_MTRANSACTIONLOGS_STATUS', 'status'),
    define('_MTRANSACTIONLOGS_DESCRIPTION', 'description'),
    define('_MTRANSACTIONLOGS_CREATED', 'created'),
    //Footnote table
    define('_FOOTNOTE_NId', 'FootNote_NId'),
    define('_FOOTNOTE_VAL', 'FootNote'),
    define('_FOOTNOTE_GID', 'FootNote_GId'),
    // data table
    define('_MDATA_NID', 'Data_NId'),
    define('_MDATA_IUSNID', 'IUSNId'),
    define('_MDATA_TIMEPERIODNID', 'TimePeriod_NId'),
    define('_MDATA_AREANID', 'Area_NId'),
    define('_MDATA_IUNID', 'IUNId'),
    define('_MDATA_SOURCENID', 'Source_NId'),
    define('_MDATA_DATAVALUE', 'Data_Value'),
    define('_MDATA_FOOTNOTENID', 'FootNote_NId'),
    define('_MDATA_INDICATORNID', 'Indicator_NId'),
    define('_MDATA_UNITNID', 'Unit_NId'),
    define('_MDATA_SUBGRPNID', 'Subgroup_Val_NId'),
    define('_MDATA_DATA_DENOMINATOR', 'Data_Denominator'),
    // m_ius_validations table
    define('_MIUSVALIDATION_ID', 'id'),
    define('_MIUSVALIDATION_DB_ID', 'db_id'),
    define('_MIUSVALIDATION_INDICATOR_GID', 'indicator_gid'),
    define('_MIUSVALIDATION_UNIT_GID', 'unit_gid'),
    define('_MIUSVALIDATION_SUBGROUP_GID', 'subgroup_gid'),
    define('_MIUSVALIDATION_IS_TEXTUAL', 'is_textual'),
    define('_MIUSVALIDATION_MIN_VALUE', 'min_value'),
    define('_MIUSVALIDATION_MAX_VALUE', 'max_value'),
    define('_MIUSVALIDATION_CREATEDBY', 'createdby'),
    define('_MIUSVALIDATION_MODIFIEDBY', 'modifiedby'),
    
    // r_access_areas table
    define('_RACCESSAREAS_ID', 'id'),
    define('_RACCESSAREAS_USER_DATABASE_ROLE_ID', 'user_database_role_id'),
    define('_RACCESSAREAS_USER_DATABASE_ID', 'user_database_id'),
    define('_RACCESSAREAS_AREA_ID', 'area_id'),
    define('_RACCESSAREAS_AREA_NAME', 'area_name'),
    
    // r_access_indicators table
    define('_RACCESSINDICATOR_ID', 'id'),
    define('_RACCESSINDICATOR_USER_DATABASE_ROLE_ID', 'user_database_role_id'),
    define('_RACCESSINDICATOR_USER_DATABASE_ID', 'user_database_id'),
    define('_RACCESSINDICATOR_INDICATOR_GID', 'indicator_gid'),
    define('_RACCESSINDICATOR_INDICATOR_NAME', 'indicator_name'),
	
    // metadata category  table
    define('_META_CATEGORY_NID', 'CategoryNId'),
    define('_META_CATEGORY_NAME', 'CategoryName'),
    define('_META_CATEGORY_TYPE', 'CategoryType'),
    define('_META_CATEGORY_ORDER', 'CategoryOrder'),
    define('_META_PARENT_CATEGORY_NID', 'ParentCategoryNId'),
    define('_META_CATEGORY_GID', 'CategoryGId'),
    define('_META_CATEGORY_DESC', 'CategoryDescription'),
    define('_META_CATEGORY_PRESENT', 'IsPresentational'),
    define('_META_CATEGORY_MAND', 'IsMandatory'),

    // metadata report   table
    define('_META_REPORT_NID', 'MetadataReport_Nid'),
    define('_META_REPORT_METADATA', 'Metadata'),
    define('_META_REPORT_CATEGORY_NID', 'Category_Nid'),
    define('_META_REPORT_TARGET_NID', 'Target_Nid'),
    
    // language table
    define('_LANGUAGE_LANGUAGE_NID', 'Language_NId'),
    define('_LANGUAGE_LANGUAGE_NAME', 'Language_Name'),
    define('_LANGUAGE_LANGUAGE_CODE', 'Language_Code'),
    define('_LANGUAGE_LANGUAGE_DEFAULT', 'Language_Default'),
    
    define('_LANGUAGE_GLOBAL_LOCK', 'Language_GlobalLock'),

    // Error Codes
    define('_DFAERR', 'DFAERR'),        //  Error code prefix 
    define('_ERR100', _DFAERR . '100'), //   Operation not completed due to server error.
    define('_ERR101', _DFAERR . '101'), //   Invalid database connection details 
    define('_ERR102', _DFAERR . '102'), //   connection name is not unique 
    define('_ERR103', _DFAERR . '103'), //   database connection name is empty
    define('_ERR104', _DFAERR . '104'), //   Activation link already used 
    define('_ERR105', _DFAERR . '105'), //   records not  deleted
    define('_ERR106', _DFAERR . '106'), //   dbid is blank
    define('_ERR107', _DFAERR . '107'), //   database details not found 
    define('_ERR108', _DFAERR . '108'), //   user is unauthorized to perform action  
    define('_ERR109', _DFAERR . '109'), //   user id is blank 
    //define('_ERR110', _DFAERR . '110'), // records not  deleted for service 1200
	
    define('_ERR111', _DFAERR . '111'), //   Email or  name may be empty
    define('_ERR112', _DFAERR . '112'), //   Roles are  empty service 1201
    define('_ERR113', _DFAERR . '113'), //   Empty password   
   // define('_ERR114', _DFAERR . '114'), //   user not  modified 
    define('_ERR115', _DFAERR . '115'), //   activation key  is empty    service 1204
    //define('_ERR116', _DFAERR . '116'), //   password not updated   service 1204
    define('_ERR117', _DFAERR . '117'), //   invalid activation key    service 1204
    define('_ERR118', _DFAERR . '118'), //   user not modified bcoz email already exists   service 1204
    define('_ERR119', _DFAERR . '119'), //   user is already added to this database 
    define('_ERR120', _DFAERR . '120'), //   user is not assigned to this database 
    define('_ERR121', _DFAERR . '121'), //   Email do not exists  
	
    //Import ICIUS Error Codes
    define('_ERR122', _DFAERR . '122'), //   The file is empty
    define('_ERR123', _DFAERR . '123'), //   Invalid Columns Format
    define('_ERR124', _DFAERR . '124'), //   Sheet should start from Class Type
    define('_ERR125', _DFAERR . '125'), //   Sheet should have all the Dimension columns from database
    define('_ERR126', _DFAERR . '126'), //   Dimension order should be same as in database
    define('_ERR127', _DFAERR . '127'), //   Minimum and Maximum value can only have digits
    define('_ERR128', _DFAERR . '128'), //   One or more IC level columns are empty
    define('_ERR129', _DFAERR . '129'), //   Subgroup Dimensions are not provided
    define('_ERR130', _DFAERR . '130'), //   Shortname already Exists. Please enter another text
    define('_ERR131', _DFAERR . '131'), //   error code for  publisher is empty 
    define('_ERR132', _DFAERR . '132'), //   Source name alraedy  Exists. Please enter another text
    define('_ERR133', _DFAERR . '133'), //   Invalid date format 
    define('_ERR134', _DFAERR . '134'), //   Date already exists  
    define('_ERR135', _DFAERR . '135'), //    Missing Parameters
    define('_ERR136', _DFAERR . '136'), //   Db Connection not modified due to database error 

    define('_ERR137', _DFAERR . '137'), //   Gid already exists  
    define('_ERR138', _DFAERR . '138'), //    Indicator Name already exists  
    define('_ERR139', _DFAERR . '139'), //    Type is empty while export in unit and indicator  
    define('_ERR140', _DFAERR . '140'), //    Gid empty
    define('_ERR141', _DFAERR . '141'), //    Indicator Name is  empty
    define('_ERR142', _DFAERR . '142'), //    Invalid Gid
    define('_ERR143', _DFAERR . '143'), //    Unit Name is empty 
    define('_ERR144', _DFAERR . '144'), //    category alraedy exists
    define('_ERR145', _DFAERR . '145'), //    invalid request 
    define('_ERR146', _DFAERR . '146'), //    only alpha numeric and space is allowed 
    define('_ERR147', _DFAERR . '147'), //    Subgroup type name empty
    define('_ERR148', _DFAERR . '148'), //    Subgroup Name empty

    define('_ERR149', _DFAERR . '149'), //    Subgroup type Name already exists  
    define('_ERR150', _DFAERR . '150'), //    Subgroup Name already exists  
    define('_ERR151', _DFAERR . '151'), //    Subgroup type nid empty  
    define('_ERR152', _DFAERR . '152'), //    Subgroup val  Name empty
    define('_ERR153', _DFAERR . '153'), //    Subgroup val Name already exists  
    
    define('_ERR154', _DFAERR . '154'), //    Indicator classification name already exists
    define('_ERR155', _DFAERR . '155'), //    Indicator classification parent does not exists
    
    define('_ERR156', _DFAERR . '156'), //    Area name already exists
    define('_ERR157', _DFAERR . '157'), //    Area parent does not exists

    define('_ERR158', _DFAERR . '158'), //    GID already exists
    define('_ERR159', _DFAERR . '159'), //    ZIP file should have only three files with extensions as SHP, SHX, DBF
    define('_ERR160', _DFAERR . '160'), //    All 3 files should have same name but different extensions
    define('_ERR161', _DFAERR . '161'), //    Invalid shape files
    define('_ERR162', _DFAERR . '162'), //    Map name already exists
    define('_ERR163', _DFAERR . '163'), //    File Not uploaded
    
    define('_ERR164', _DFAERR . '164'), //    Publisher length is 100
    define('_ERR165', _DFAERR . '165'), //    Year length is 10
    define('_ERR166', _DFAERR . '166'), //    boundary length error 
	
    define('_GID_LENGTH', 50),         //    gid lenght 32
    define('_SGVALNAME_LENGTH', 255), //    sg val  length 255 
    define('_SGNAME_LENGTH', 128), //    sg  length 128
    define('_SGTYPENAME_LENGTH', 128), //    sg type length 128
    define('_INDNAME_LENGTH', 255), //    indicator name  length 255
    define('_METACATEGORY_LENGTH', 255), //    META CATEGORY   length 255
    define('_UNITNAME_LENGTH', 128), //    unit Name length 128

	

    // Super Admin Role Id Hardcodes	
    define('_SUPERADMIN_ROLE', 'SUPERADMIN'), // super admin id 
    define('_ADMIN_ROLE', 'ADMIN'), // ADMIN
    define('_TEMPLATE_ROLE', 'TEMPLATE'), // TEMPLATE admin id 
    define('_DATAENTRY_ROLE', 'DATAENTRY'), // DATAENTRY admin id 
	
    define('_SUPERADMINROLEID', '1'), // super admin id 
    define('_SUPERADMINNAME', 'Super Admin'), // super admin name 
    define('_SALTPREFIX1', 'abcd#####'), // used in  activation key 
    define('_SALTPREFIX2', 'abcd###*99*'), // used in   activation key 
    // Text messages 
    define('_SUCCESS', 'SUCCESS'), // success in response 
    define('_FAILED', 'FAILED'), // failed in response 
    define('_WARNING', 'Warning'), // warning in response 
    define('_STARTED', 'STARTED'), // started in transaction 
    define('_YES', 'yes'), // Yes for json format 
    define('_NO', 'no'), // 
    define('_INACTIVE', '0'), // User status inactive  
    define('_ACTIVE', '1'), // User status inactive  
    define('_DBDELETED', '1'), // when database is deleted   
    define('_DBNOTDELETED', '0'), // when database is active  
    define('_IMPORTERRORLOG_FILE', 'Log'), // User status inactive  
    define('_CUSTOMLOG_FILE', 'Log_Import_'), // User status inactive  
    define('_OK', 'OK'),
    define('_WARN', 'WARNING'),
    define('_DONE', 'DONE'),
    define('_STATUS', 'STATUS'), // Done or Error in import log of area and ICIUS
    define('_IMPORT_STATUS', 'Import_Status'), // Error description in  import log of area and ICIUS
    define('_DESCRIPTION', 'Description'), // Error description in  import log of area and ICIUS
    define('_ICIUS', 'icius'),
    define('_AREA', 'area'),
    define('_UNITEXPORT', 'unit'),
    define('_INDIEXPORT', 'indicator'),
    define('_SUBGRPVALEXPORT', 'subgroupval'),
    define('_UNITEXPORT_FILE', 'Units'),
    define('_INDICATOREXPORT_FILE', 'Indicators'),
    define('_SUBGRPVALEXPORT_FILE', 'Subgroups'),
    define('_DES', 'des'),
    define('_ICIUSEXPORT', 'iciusExport'),
    define('_IMPORTDES', 'DES'),
    //Chunks, Logs, xls 	
    define('_CHUNKS_PATH_WEBROOT', 'uploads' . '/' . 'chunks'),
    define('_LOGS_PATH_WEBROOT', 'uploads' . '/' . 'logs'),
    define('_XLS_PATH_WEBROOT', 'uploads' . '/' . 'xls'),
    define('_DES_PATH_WEBROOT', 'uploads' . '/' . 'DES'),
    define('_UNIT_PATH_WEBROOT', 'uploads' . '/' . 'UNIT'),
    define('_INDICATOR_PATH_WEBROOT', 'uploads' . '/' . 'INDICATOR'),
    define('_SUBGROUPVAL_PATH_WEBROOT', 'uploads' . '/' . 'SUBGROUP'),
    define('_MAPS_PATH_WEBROOT', 'uploads' . '/' . 'MAPS'),
    define('_CHUNKS_PATH', WWW_ROOT . _CHUNKS_PATH_WEBROOT),
    define('_LOGS_PATH', WWW_ROOT . _LOGS_PATH_WEBROOT),
    define('_XLS_PATH', WWW_ROOT . _XLS_PATH_WEBROOT),
    define('_DES_PATH', WWW_ROOT . _DES_PATH_WEBROOT),
    define('_UNIT_PATH', WWW_ROOT . _UNIT_PATH_WEBROOT),
    define('_INDICATOR_PATH', WWW_ROOT . _INDICATOR_PATH_WEBROOT),
    define('_SUBGROUPVAL_PATH', WWW_ROOT . _SUBGROUPVAL_PATH_WEBROOT),
    define('_MAPS_PATH', WWW_ROOT . _MAPS_PATH_WEBROOT),
    define('_TV_AREA', 'area'), // _TV_AREA -> Tree View Area
    define('_TV_IU', 'iu'), // indicator unit
    define('_TV_IU_S', 's'), // subgroup vals
    define('_TV_IUS', 'ius'), // subgroup list based on indicator, unit
    define('_TV_IC', 'ic'), // indicator classification list
    define('_TV_ICIND', 'icind'), // indicator classification and indicator belongs to that IC
    define('_TV_SGVAL', 'subgroupval'), // subgroup val  list
    define('_TV_SGTYPE', 'dimensioncategory'), // subgroup val  list
    define('_TV_IND', 'ind'), // indicators list
    define('_TV_UNIT', 'unit'), // indicators list
    define('_TV_ICIUS', 'icius'), // indicator classification and indicator belongs to that IC
    define('_TV_TP', 'tp'), // indicator classification and indicator belongs to that IC
    define('_TV_SOURCE', 'source'), // indicator classification and indicator belongs to that IC
    define('_TPL_Export_', 'TPL_Export_'),
    define('_LevelName', 'Level-'), // for area level name 
    define('_DATAENTRYSAVE', 'dataEntry'), // for area level name 
    // insertdatakeys indexes for area 
    define('_INSERTKEYS_AREAID', 'areaid'),
    define('_INSERTKEYS_NAME', 'name'),
    define('_INSERTKEYS_LEVEL', 'level'),
    define('_INSERTKEYS_GID', 'gid'),
    define('_INSERTKEYS_PARENTNID', 'parentnid'),
    //Module names
    define('_MODULE_NAME_AREA', 'Area'),
    define('_MODULE_NAME_UNIT', 'Unit'),
    define('_MODULE_NAME_INDICATOR', 'Indicator'),
    define('_MODULE_NAME_SUBGROUPVAL', 'Subgroup'),
    define('_MODULE_NAME_ICIUS', 'ICIUS'),
    define('_MODULE_NAME_DATAENTRY', 'DATAENTRY'),
    define('_MODULE_NAME_MAP', 'MAP'),
    //Area Error log comments names
    define('_AREA_LOGCOMMENT1', 'Area id is  empty!!'), //area id is empty 
    define('_AREA_LOGCOMMENT2', 'Record not saved'), // error in insert  
    define('_AREA_LOGCOMMENT3', 'Parent id not found!!'), // error Parent id not found
    define('_AREA_LOGCOMMENT4', 'Invalid Details'), // error Invalid details
    define('_AREA_LOGCOMMENT5', 'Invalid Area Level'), // error Invalid details
    define('_AREA_LOGCOMMENT6', 'Duplicate entry of Area Id '), // error Invalid details
    define('_AREA_LOGCOMMENT7', 'Gid already exists'), // error Gid already exists
    define('_AREA_LOGCOMMENT8', 'Invalid gid format'), // error Invalid gid format 
    //Module names
    //Error msgs
    define('_INDICATOR_IS_EMPTY', 'Indicator is Empty'),
    define('_UNIT_IS_EMPTY', 'Unit is Empty'),
    define('_SUBGROUP_IS_EMPTY', 'Subgroup is Empty'),
    define('_IMPORT_LOG', 'importLog'),
    define('_EXPORT_DES', 'exportDes'),
    // Delemeters
    define('_DELEM1', '{~}'),
    define('_DELEM5', '{-}'),
    define('_DELEM2', '[~]'),
    define('_DELEM3', '-'),          // used in  salt explode for activation key
    define('_DELEM4', '_'),
    define('_UNAUTHORIZED_ACCESS', 'Unauthorized Access'),

    define('_DATAENTRYVAL', 'DATAENTRY'), //Column value for data entry in role table used in comparisons from table 
    define('_TEMPLATEVAL', 'TEMPLATE'), //Column value for data entry in role table used in comparisons from table     
    
    //Transactional Log constants
    define('_INSERT', 'INSERT'),
    define('_UPDATE', 'UPDATE'),
    define('_DELETE', 'DELETE'),
    define('_EXPORT', 'EXPORT'),
    define('_FOOTNOTE', 'Footnote'),
    define('_DATA', 'Data'),
    define('_SUB_MOD_DATA_ENTRY', 'FORM DATA'), //sub module 
	define('_ACTION_VALIDATION', 'VALIDATION'), //sub module 
    define('_INDICATOR', 'Indicator'),
    define('_UNIT', 'Unit'),
    define('_TIMEPERIOD', 'TIMEPERIOD'),
	//below errors used in log of data entry 
    define('_ERR_DATAVAL_EMPTY', 'Data value is empty'),
    define('_ERR_TIME_PERIOD_EMPTY', 'Time Period is empty'),
    define('_ERR_IUS_NId_EMPTY', 'IUS NId is empty'),
    define('_ERR_AREAID_EMPTY', 'Area Id is empty'),
    define('_ERR_SOURCENID_EMPTY', 'Source NId is empty'),
    define('_ERR_IUSVALIDATION', 'Failed IUS validation'),
    define('_ERR_SAVE_OPERATION', 'Unable to save due to server error'),
    define('_ERR_UPDATE_OPERATION', 'Unable to update due to server error'),
	

    define('_AREAPARENT_LEVEL','1'), //area level of parent is always 1
    define('_GLOBALPARENT_ID','-1'), //parent id is always  -1

    define('_ACTIVATIONEMAIL_SUBJECT', 'DFA Data Admin - Registration Activation'),
    define('_FORGOTPASSWORD_SUBJECT', 'DFA Data Admin - Reset your password'),
    define('_ASSIGNEDDB_SUBJECT', 'DFA Data Admin - Assigned database notification'),
    define('_ADMIN_EMAIL', 'vpdwivedi@dataforall.com'),
    
    // Import Sheet errors
    define('_ERROR_6', 'Different Name for same Indicator GID'),
    define('_ERROR_7', 'Different Name for same Unit GID'),
    define('_ERROR_8', 'Different Name for same Subgroup GID'),
    define('_ERROR_9', 'IC type can only have value from CF,CN,GL,IT,SC,SR,TH'),
    define('_ERROR_10', 'Same Dimension value in different Dimensions'),
    define('_ERROR_IC_LEVEL_EMPTY', 'IC Level1 Name is empty'),
    define('_ERROR_INDICATOR_EMPTY', 'Indicator is empty'),
    define('_ERROR_UNIT_EMPTY', 'Unit is empty'),
    define('_ERROR_SUBGROUP_EMPTY', 'Subgroup is empty'),
    define('_ERROR_INVALID_FILE', 'Invalid file'),
    define('_WARNING_SHEETSUBGROUP_UNMATCHES_DBSUBGROUP', 'Dimension combination is different than Subgroup'),
    define('_ERROR_11', 'Dimensions are empty'),
    
    //File upload error
    define('_ERROR_UNACCEPTED_METHOD', 'File uploaded via unaccepted method.'),
    define('_ERROR_UPLOAD_FAILED', 'File upload failed.'),
    define('_ERROR_LOCATION_INACCESSIBLE', 'This location cannot be accessed.'),
    
    //columns names of Area in Excel sheet
    define('_EXCEL_AREA_ID', 'AreaId'),
    define('_EXCEL_AREA_NAME', 'AreaName'),
    define('_EXCEL_AREA_GID', 'AreaGId'),
    define('_EXCEL_AREA_LEVEL', 'AreaLevel'),
    define('_EXCEL_AREA_PARENTID', 'Parent AreaId'),
    
    define('_SOURCE_BREAKUP_DETAILS', 'sourceBreakupDetails'),
    define('_SOURCE', 'source'),
    define('_INDICATOR_ACCESS_NOT_ALLOWED', 'Indicator access not allowed'),
    define('_NO_AREA_ACCESS', '_NOAREAACCESS_'),
    
    define('_INVALID_INPUT', 'Invalid Input(s)'), // Invalid Input(s)
    
    define('_ICIUS_TRANSAC', 'ICIUS'),
    define('_IC_TRANSAC', 'IC'),
    define('_AREA_TRANSAC', 'AREA'),
    define('_AREAMAP_TRANSAC', 'AREAMAP'),
    define('_AREAMAPLAYER_TRANSAC', 'AREAMAPLAYER'),
    define('_AREAMETADATA_TRANSAC', 'AREAMETADATA'),


    // DB metadata table
    define('_DBMETA_NID', 'DBMtd_NId'),
    define('_DBMETA_DESC', 'DBMtd_Desc'),
    

     // Area feature table
    define('_AREAFEATURE_TYPE_NID', 'Feature_Type_NId'),
    define('_AREAFEATURE_TYPE', 'Feature_Type'),

    // Area Map Metadata table
    define('_AREAMAP_METADATA_NID', 'Metadata_NId'),
    define('_AREAMAP_METADATA_LAYER_NID', 'Layer_NId'),
    define('_AREAMAP_METADATA_TEXT', 'Metadata_Text'),
    define('_AREAMAP_METADATA_LAYER_NAME', 'Layer_Name'),
    
    define('_IMPORT_LANG', 'language'),
   
    // Map Cases
    define('_MAP_TYPE_AREA', 'area'),
    define('_MAP_TYPE_GROUP', 'group'),


];

?>
