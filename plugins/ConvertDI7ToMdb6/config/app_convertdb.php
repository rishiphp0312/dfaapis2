<?php
// Path settings
if (!defined('DC_PLUGIN_WEBROOT')) define ('DC_PLUGIN_WEBROOT', ROOT . '\plugins\ConvertDI7ToMdb6\webroot\\');
if (!defined('MS_ACC_DSN_LOCATION')) define ('MS_ACC_DSN_LOCATION', DC_PLUGIN_WEBROOT . 'ms-access-db\\');

// MS-Access database settings
if (!defined('DEVINFO_6_SCHEMA')) define ('DEVINFO_6_SCHEMA', 'devinfo6.1_schema.mdb');
if (!defined('MS_ACC_USERNAME')) define ('MS_ACC_USERNAME', 'Avalon');
if (!defined('MS_ACC_PASSWORD')) define ('MS_ACC_PASSWORD', 'unitednations2000');

// MY-SQL database settings
if (!defined('MYSQL_VENDOR')) define ('MYSQL_VENDOR', 'mysql');
if (!defined('MYSQL_HOST')) define ('MYSQL_HOST', '192.168.1.11');
if (!defined('MYSQL_DB')) define ('MYSQL_DB', 'developer_evaluation_database');
if (!defined('MYSQL_USERNAME')) define ('MYSQL_USERNAME', 'di-act');
if (!defined('MYSQL_PASSWORD')) define ('MYSQL_PASSWORD', 'diact');

// MS-SQL database settings
if (!defined('MSSQL_VENDOR')) define ('MSSQL_VENDOR', 'mssql');
if (!defined('MSSQL_HOST')) define ('MSSQL_HOST', '192.168.1.11,1433');
if (!defined('MSSQL_DB')) define ('MSSQL_DB', 'd3a_censusinfopca');
if (!defined('MSSQL_USERNAME')) define ('MSSQL_USERNAME', 'sa');
if (!defined('MSSQL_PASSWORD')) define ('MSSQL_PASSWORD', 'l9ce130');

?>