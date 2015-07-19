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
    define('_IUS_SUBGROUP_NIDS', 'Subgroup_Nids'),
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
	define('_USER_ROLE_ID', 'role_id'),
    define('_USER_NAME', 'name'),
    define('_USER_STATUS', 'status'),
    define('_USER_LASTLOGGEDIN', 'lastloggedin'),
    define('_USER_EMAIL', 'email'),
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
    // Error Codes
    define('_DFAERR', 'DFAERR'), //  Error code prefix 
    define('_ERR100', _DFAERR . '100'), //   database not added 
    define('_ERR101', _DFAERR . '101'), //   Invalid database connection details 
    define('_ERR102', _DFAERR . '102'), //   connection name is  not unique 
    define('_ERR103', _DFAERR . '103'), //   database connection name is empty
    define('_ERR104', _DFAERR . '104'), //   Activation link already used 
    define('_ERR105', _DFAERR . '105'), //   records not  deleted
    define('_ERR106', _DFAERR . '106'), //   db id is blank
    define('_ERR107', _DFAERR . '107'), //   database details not found 
    define('_ERR109', _DFAERR . '109'), //   user id is blank 
    define('_ERR110', _DFAERR . '110'), //   records not  deleted for service 1200
    define('_ERR111', _DFAERR . '111'), //   Email or  name may be empty
    define('_ERR112', _DFAERR . '112'), //   Roles are  empty service 1201
    define('_ERR113', _DFAERR . '113'), //   Empty password   
    define('_ERR114', _DFAERR . '114'), //   user not  modified 
    define('_ERR115', _DFAERR . '115'), //   activation key  is empty    service 1204
    define('_ERR116', _DFAERR . '116'), //   password not updated   service 1204
    define('_ERR117', _DFAERR . '117'), //   invalid activation key    service 1204
    define('_ERR118', _DFAERR . '118'), //   user not modified bcoz email already exists   service 1204
    define('_ERR119', _DFAERR . '119'), //   user is already added to this database 
    define('_ERR120', _DFAERR . '120'), //   user is not assigned to this database 
    // SUper Admin Role Id Hardcodes
    define('_SUPERADMINROLEID', '1'), // super admin id 
    define('_SUPERADMINNAME', 'Super Admin'), // super admin name 
    define('_SALTPREFIX1', 'abcd#####'), // used in  activation key 
    define('_SALTPREFIX2', 'abcd###*99*'), // used in   activation key 
    // Text messages 
    define('_SUCCESS', 'Success'), // success in response 
    define('_FAILED', 'Failed'), // failed in response 
    define('_STARTED', 'started'), // started in transaction 
    define('_YES', 'yes'), // Yes for json format 
    define('_NO', 'no'), // 
    define('_INACTIVE', '0'), // User status inactive  
    define('_ACTIVE', '1'), // User status inactive  
    define('_DBDELETED', '1'), // when database is deleted   
    define('_DBNOTDELETED', '0'), // when database is active  
    define('_IMPORTERRORLOG_FILE', 'TPL_Import_'), // User status inactive  
    define('_OK', 'OK'),
    define('_STATUS', 'STATUS'), // Done or Error in import log of area and ICIUS
    define('_DESCRIPTION', 'Description'), // Error description in  import log of area and ICIUS
    define('_ICIUS', 'icius'),
    define('_AREA', 'area'),
    define('_ICIUSEXPORT', 'iciusExport'),
    //Chunks, Logs, xls Folders
    define('_CHUNKS_PATH_WEBROOT', 'uploads' . DS . 'chunks'),
    define('_LOGS_PATH_WEBROOT', 'uploads' . DS . 'logs'),
    define('_XLS_PATH_WEBROOT', 'uploads' . DS . 'xls'),
    define('_CHUNKS_PATH', WWW_ROOT . _CHUNKS_PATH_WEBROOT),
    define('_LOGS_PATH', WWW_ROOT . _LOGS_PATH_WEBROOT),
    define('_XLS_PATH', WWW_ROOT . _XLS_PATH_WEBROOT),
    define('_TV_AREA', 'area'), // _TV_AREA -> Tree View Area
    define('_TV_IU', 'iu'), // indicator unit
    define('_TV_IU_S', 's'), // subgroup vals
    define('_TV_IUS', 'ius'), // subgroup list based on indicator, unit
    define('_TV_IC', 'ic'), // indicator classification list
    define('_TV_ICIND', 'icind'), // indicator classification and indicator belongs to that IC
    define('_TV_ICIUS', 'icius'), // indicator classification and indicator belongs to that IC
    define('_TPL_Export_', 'TPL_Export_'),
    define('_LevelName', 'Level-'), // for area level name 
    // insertdatakeys indexes for area 
    define('_INSERTKEYS_AREAID', 'areaid'),
    define('_INSERTKEYS_NAME', 'name'),
    define('_INSERTKEYS_LEVEL', 'level'),
    define('_INSERTKEYS_GID', 'gid'),
    define('_INSERTKEYS_PARENTNID', 'parentnid'),
    //Module names
    define('_MODULE_NAME_AREA', 'area'),
    //Area Error log comments names
    define('_AREA_LOGCOMMENT1', 'Area id is  empty!!'), //area id is empty 
    define('_AREA_LOGCOMMENT2', 'Record not saved'), // error in insert  
    define('_AREA_LOGCOMMENT3', 'Parent id not found!!'), // error Parent id not found
    define('_AREA_LOGCOMMENT4', 'Invalid Details'), // error Invalid details
    //Module names
    //Error msgs
    define('_INDICATOR_IS_EMPTY', 'Indicator is Empty'),
    define('_UNIT_IS_EMPTY', 'Unit is Empty'),
    define('_SUBGROUP_IS_EMPTY', 'Subgroup is Empty'),
    
    define('_IMPORT_LOG', 'importLog'),
    // Delemeters
    define('_DELEM1', '{~}'),
    define('_DELEM2', '[~]'),
        ]
?>
