<?php
    // Defining CONSTANTS
    $website_base_url = "http://".$_SERVER['HTTP_HOST'];
    $website_base_url .= preg_replace('@/+$@','',dirname($_SERVER['SCRIPT_NAME']))."/";
    $website_base_url = str_replace('webroot/','', $website_base_url);
    
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
        define('_SUBGROUPTYPE_SUBGROUP_TYPE_GID', 'Subgroup_Type_GID'),
        define('_SUBGROUPTYPE_SUBGROUP_TYPE_NAME', 'Subgroup_Type_Name'),
        define('_SUBGROUPTYPE_SUBGROUP_TYPE_GLOBAL', 'Subgroup_Type_Global'),
        define('_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER', 'Subgroup_Type_Order'),
        define('_SUBGROUPTYPE_SUBGROUP_TYPE_NID', 'Subgroup_Type_NId'),
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
        define('_AREA_AREA_MAP', 'Area_Map'),//NI
        define('_AREA_AREA_BLOCK', 'Area_Block'),
        define('_AREA_AREA_GLOBAL', 'Area_Global'),//NI
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
        define('_RUSERDBROLE_ACCESS', 'area_access'),
        define('_RUSERDBROLE_ROLE_ID', 'role_id'),
        define('_RUSERDBROLE_USER_DB_ID', 'user_database_id'),
        define('_RUSERDBROLE_CREATED', 'created'),
        define('_RUSERDBROLE_CREATEDBY', 'createdby'),
        define('_RUSERDBROLE_MODIFIED', 'modified'),
        define('_RUSERDBROLE_MODIFIEDBY', 'modifiedby'),
        
        // Error Codes
        define('_DFAERR', 'DFAERR'),
        define('_ERR100', _DFAERR.'100'), //
        define('_ERR101', _DFAERR.'101'), //
        define('_ERR102', _DFAERR.'102'), //
        define('_ERR103', _DFAERR.'103'), //
        define('_ERR104', _DFAERR.'104'), //
        define('_ERR105', _DFAERR.'105'), //
        define('_ERR106', _DFAERR.'106'), //
        define('_ERR107', _DFAERR.'107'), //
        define('_ERR108', _DFAERR.'108'), //
        define('_ERR109', _DFAERR.'109'), //
        define('_ERR110', _DFAERR.'110'), //
        define('_ERR111', _DFAERR.'111'), //
        define('_ERR112', _DFAERR.'112'), //
        define('_ERR113', _DFAERR.'113'), //
        define('_ERR114', _DFAERR.'114'), //
        define('_ERR115', _DFAERR.'115'), //
        define('_ERR116', _DFAERR.'116'),  //

        // SUper Admin Role Id Hardcodes
        define('_SUPERADMINROLEID', '1'),  //


        // Text messages 
        define('_SUCCESS', 'success'), 
        define('_FAILED', 'failed'), 
        define('_YES', 'yes'), 
        define('_NO', 'no'), 
    
    ]
?>
