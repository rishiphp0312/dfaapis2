<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace ConvertDI7ToMdb6\Controller;
use ConvertDI7ToMdb6\Controller\AppController;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Exception\Exception;
use Cake\Utility\Xml;
use Cake\Network\Email\Email;
/**
 * Static content controller
 *
 * This controller will render views from Template/Operations/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class OperationsController extends AppController {

    public $TestJobs = '';
    public $conn = '';
    public $mdbCon = '';
    
    private $dsnLocation = '';
    private $dsnFileName = '';
    private $mdbUsername = '';
    private $mdbPassword = '';
    private $dbToProcess= '';
    
    private $dbVendor = '';
    private $sqlHost = '';
    private $sqlDb = '';
    private $sqlUsername = '';
    private $sqlPassword = '';
    private $sqlPort = '';
    public $pluginWebRoot = '';
    private $authToken = '';
    
    public $components = ['Common'];
    
    /**
        Function : initialize
        Purpose : Use to initialize class variables
        Created On : 04/08/15
        Created By : Rahul D.
    */    
    public function initialize() {
        $this->pluginWebRoot = DC_PLUGIN_WEBROOT;
        $this->dsnFileName = DEVINFO_6_SCHEMA;
        $this->dsnLocation = MS_ACC_DSN_LOCATION;
        $this->mdbUsername = MS_ACC_USERNAME;
        $this->mdbPassword = MS_ACC_PASSWORD;
        parent::initialize();
    }    

    /**
        Function : _mdbConnection
        Purpose : Establish connection with MS-access database
        Created On : 04/08/15
        Created By : Rahul D.
    */
    private function _mdbConnection($dsnLocation, $username, $password) {
        $addi_info = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
        try {
             $this->mdbCon = new \PDO(
               "odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=$dsnLocation",
               $username,
               $password,
               $addi_info
               );
             return true;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }        
    }

    /**
        Function : _dbConnection
        Purpose : Establish connection with MySql/MsSql Server
        Created On : 04/08/15
        Created By : Rahul D.
    */    
    private function _dbConnection($dv_vendor='', $db_host, $db_name, $db_user, $db_pass, $port='') {
        $addi_info = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
        $db_host = $port!="" ? $db_host.",".$port : $db_host;
        if (strtolower($dv_vendor)=='mysql') {
            try {
                $this->conn = new \PDO(
                                       'mysql:host=' . $db_host . ';dbname=' . $db_name,
                                       $db_user,
                                       $db_pass,
                                       $addi_info
                                       );
                return true;
           } catch (\PDOException $e) {
               return $e->getMessage();
           }            
        } elseif (strtolower($dv_vendor)=='mssql') {
           try {
               $this->conn = new \PDO(
                                      "sqlsrv:server={$db_host};Database={$db_name}",
                                      $db_user,
                                      $db_pass,
                                      $addi_info
                                    );
               return true;
           } catch (\PDOException $e) {
               return $e->getMessage();
           }
       }
    }
    
    /**
        Function : index
        Purpose : Just for testing purpose
        Created On : 05/08/15
        Created By : Rahul D.
    */      
    public function index() {
        $this->executeConvertIntoMsAccess();
    }  

    /**
        Function : executeConvertIntoMsAccess
        Purpose : Create new database to convert and process functions to dump data into ms access
        Created On : 04/08/15
        Created By : Rahul D.
    */    
    public function executeConvertIntoMsAccess() {
        if (!empty($this->request->data) && $this->request->is('post')) {
            try {
                $connectionInfo = $this->Common->getDbConnectionDetails($this->request->data['dbId']);
                $connectionInfo = json_decode($connectionInfo, true);
                $this->dbVendor = $connectionInfo['db_source'];
                $this->_dbConnection(
                                     $this->dbVendor,
                                     $connectionInfo['db_host'],
                                     $connectionInfo['db_database'],
                                     $connectionInfo['db_login'],
                                     $connectionInfo['db_password'],
                                     $connectionInfo['db_port']
                                     );
                if (!$this->conn) throw new Exception('Not able to connect with source databse'); 
            } catch(Exception $err) {
                return $err->getMessage();exit;
            }
            $this->dbToProcess = $connectionInfo['db_database'] . '_6.1_' . date('YmdHis') . '.mdb';
            if (!copy($this->dsnLocation . $this->dsnFileName, $this->dsnLocation . $this->dbToProcess)) {
                $status = "Could not created database schema.";
                echo $status;exit;
            }
            $this->_mdbConnection($this->dsnLocation . $this->dbToProcess, $this->mdbUsername, $this->mdbPassword);
            $di6tables_with_fields = $this->_devinfo6TableAndFields();
            $this->autoRender=false;
            $totaltablesCount = 0;
            if (!empty($di6tables_with_fields)) {
                foreach ($di6tables_with_fields as $entity=>$fields) {
                    if (in_array($entity, array('ut_data', 'ut_indicator_unit_subgroup'))){
                        $this->_dumpToMSAccess($fields, $entity);
                        $totaltablesCount++;
                    } else {
                        $this->_fetchAndSaveIntoMdb($entity, $fields);
                        $totaltablesCount++;
                    }
                }
            }
            $this->_callbkDBVersion();
            $this->_callbkIndicatorUpdateMetaData();
            $this->_callbkSourceUpdateMetaData();
            $this->_callbkAreaUpdateMetaData();
            if ($totaltablesCount > 0) {
                $this->mdbCon = "";
                $this->conn = "";
                echo "SUCCESS|^|" . $this->dbToProcess;
                exit;
            }
        } else {
            echo "Bad request";exit;
        }
    }
    
    public function _fetchLimitedAndSaveRecords($table, $fields) {
        if (!empty($table) && !empty($fields)) {
            $pKey = "";
            if (isset($fields[0]))
                $pKey = $fields[0];
            $this->_truncateMSAccessTable($table, $pKey);
            $pKy = isset($fields[0]) ? $fields[0] : "*";
            $sql = "SELECT COUNT($pKy) as total FROM $table";
            $st = $this->conn->prepare($sql);
            $st->execute();
            $totalCounts = $st->fetchAll();
            $toProcess = 1000;
            if (!empty($totalCounts[0]['total']) && $totalCounts[0]['total']>$toProcess) { // Save records using chunks
                $total = $totalCounts[0]['total'];
                $offSet = 0;
                $chunksLen = ceil($total / $toProcess);
                $cunksCollect = [];
                $msSqlLimit = [];
                for ($x=0; $x<$chunksLen; $x++) {
                    $cunksCollect[] = "$offSet,$toProcess";
                    $msSqlLimit[$x]['start'] = $offSet;
                    $offSet = $offSet+$toProcess;
                    $msSqlLimit[$x]['end'] = $offSet;
                }
                if (!empty($cunksCollect) && !empty($msSqlLimit)) {
                    $data = array();
                    foreach ($cunksCollect as $iter=>$limit) {
                        if (isset($this->dbVendor) && $this->dbVendor=="mssql")
                            $sql = " SELECT c.* FROM
                                    ( SELECT ROW_NUMBER() OVER(ORDER BY $pKy) AS RowID,*  FROM $table ) AS c
                                    WHERE c.RowID > " . $msSqlLimit[$iter]['start'] . " AND c.RowID <= " . $msSqlLimit[$iter]['end'];
                        else
                            $sql = "SELECT * FROM $table LIMIT $limit";
                        $st = $this->conn->prepare($sql);
                        $st->execute();
                        $data = $st->fetchAll(\PDO::FETCH_ASSOC);
                        // save data
                        if (!empty($data)) {
                            foreach ($data as $iter=>$collect) {
                                $data_arr = [];
                                if (is_array($fields)) {
                                    foreach ($fields as $key=>$col) {
                                        if (isset($collect[$fields[0]]) && ($collect[$fields[0]]=='-1' || $collect[$fields[0]]=='0')) continue;
                                        if (isset($collect[$col])) {
                                            $data_arr[":".$col] = $this->_manupulateValueByDataType($table, $col, $collect[$col]);
                                        }
                                    }
                                    $fields_str = $this->prepareFieldsString($data_arr, ":");
                                    $fields_str_with_coln = $this->prepareFieldsString($data_arr);
                                    if (!empty($data_arr)) {
                                        try{
                                            $stmt = $this->mdbCon->prepare("INSERT INTO $table ( $fields_str ) VALUES ( $fields_str_with_coln )");
                                            $status = $stmt->execute($data_arr);
                                            if (!$status) { throw new Exception("Error while inserting records.");}                            
                                        } catch(Exception $err) {
                                            $status = $err->getMessage();
                                        }
                                    }
                                }
                            }                            
                        }
                        // save data
                    }
                }
            } else {
                $data = array();
                $sql = "SELECT * FROM $table";
                $st = $this->conn->prepare($sql);
                $st->execute();
                $data = $st->fetchAll(\PDO::FETCH_ASSOC);                
                if (!empty($data)) {  // Save records
                    foreach ($data as $iter=>$collect) {
                        $data_arr = [];
                        if (is_array($fields)) {
                            foreach ($fields as $key=>$col) {
                                if (isset($collect[$fields[0]]) && ($collect[$fields[0]]=='-1' || $collect[$fields[0]]=='0')) continue;
                                if (isset($collect[$col])) {
                                    $data_arr[":".$col] = $this->_manupulateValueByDataType($table, $col, $collect[$col]);
                                }
                            }
                            $fields_str = $this->prepareFieldsString($data_arr, ":");
                            $fields_str_with_coln = $this->prepareFieldsString($data_arr);
                            if (!empty($data_arr)) {
                                try{
                                    $stmt = $this->mdbCon->prepare("INSERT INTO $table ( $fields_str ) VALUES ( $fields_str_with_coln )");
                                    $status = $stmt->execute($data_arr);
                                    if (!$status) { throw new Exception("Error while inserting records.");}                            
                                } catch(Exception $err) {
                                    $status = $err->getMessage();
                                }
                            }
                        }
                    }                            
                }                
            }
        }
        return $status;
    }    
    
    /**
        Function : _truncateMSAccessTable
        Purpose : This will used to delete records from a table and set auto increment id to 1
        Created On : 03/08/15
        Created By : Rahul D.
    */     
    public function _truncateMSAccessTable($table, $pKey) {
        if (!empty($table)) {
            $del = ($table=="ut_ic_ius") ? "DELETE FROM ut_indicator_classifications_ius" : "DELETE FROM $table";
            $stmt = $this->mdbCon->prepare($del);
            $status = $stmt->execute();            
            if (!empty($pKey)){
                $alter = ($table=="ut_ic_ius") ? "ALTER TABLE ut_indicator_classifications_ius ALTER COLUMN $pKey COUNTER(1,1);" : "ALTER TABLE $table ALTER COLUMN $pKey COUNTER(1,1);";
                $st = $this->mdbCon->prepare($alter);
                $status = $st->execute();                 
            }
        }
        return $status;
    }
    
    public function prepareFieldsString($inArr=[], $todo="") {
        $str = "";
        if(!empty($inArr)) {
            foreach ($inArr as $k=>$v)
                $str = $str=="" ? $k : $str . ', '. $k ;
        }
        if (!empty($todo)) { 
            $str = str_replace($todo, "", $str);
        }
        return $str;
    }
    
    /**
        Function : _fetchAndSaveIntoMdb
        Purpose : This will fetch data from DI-7 MSSql/MySql database and will dump data into DI-6 MS-Access database. This function reads every table of DI7 and copy all data of same table in MS-Access table 
        Created On : 06/08/15
        Created By : Rahul D.
    */   
    public function _fetchAndSaveIntoMdb($table, $fields) {
        $status = false;
        $pKey = "";
        if (!empty($table) && !empty($fields)) {
            $sql = "SELECT *
                    FROM $table
                    WHERE 1=1";
            $st = $this->conn->prepare($sql);
            $st->execute();
            $data = $st->fetchAll(\PDO::FETCH_ASSOC);
            if (isset($fields[0]))
                $pKey = $fields[0];
            $this->_truncateMSAccessTable($table, $pKey);
            if (!empty($data)) {
                foreach ($data as $iter=>$collect) {
                    $data_arr = [];
                    if (is_array($fields)) {
                        foreach ($fields as $key=>$col) {
                            if (isset($collect[$fields[0]]) && ($collect[$fields[0]]=='-1' || $collect[$fields[0]]=='0')) continue;
                            if ($table=="ut_indicator_unit_subgroup" && ($col=="Subgroup_NIds")){
                                if (in_array('Subgroup_NIds', $fields)) { // Special handling, Because in Mysql database the name of  field "Subgroup_NIds" of table "ut_indicator_unit_subgroup" is different with MS-Sql database  
                                    $collect[$col] = isset($collect['Subgroup_NIds']) ? $collect['Subgroup_NIds'] : $collect['Subgroup_Nids'];
                                }
                            }
                            if (isset($collect[$col])) {
                                $data_arr[":".$col] = $this->_manupulateValueByDataType($table, $col, $collect[$col]);
                            }
                        }
                        $fields_str = $this->prepareFieldsString($data_arr, ":");
                        $fields_str_with_coln = $this->prepareFieldsString($data_arr);
                        if (!empty($data_arr)) {
                            try{
                                if ($table=="ut_ic_ius") {
                                    $stmt = $this->mdbCon->prepare("INSERT INTO ut_indicator_classifications_ius ( $fields_str ) VALUES ( $fields_str_with_coln )");
                                } else {
                                    $stmt = $this->mdbCon->prepare("INSERT INTO $table ( $fields_str ) VALUES ( $fields_str_with_coln )");
                                }
                                $status = $stmt->execute($data_arr);
                                if (!$status) { throw new Exception("Error while inserting records.");}                            
                            } catch(Exception $err) {
                                $status = $err->getMessage();
                            }
                        }
                    }
                }
            }
        }
        return $status;
    }

    /**
        Function : _manupulateValueByDataType
        Purpose : This function convert a value according to its datatype with MS-Access  
        Created On : 07/08/15
        Created By : Rahul D.
    */       
    public function _manupulateValueByDataType($table, $field, $value) {
        $outValue = (isset($value)) ? $value : '';
        $tableSchema = $this->_fetchTablesSchema();
        if (array_key_exists($table, $tableSchema) && array_key_exists($field, $tableSchema[$table])) {
            if (isset($tableSchema[$table][$field]['required']) && isset($tableSchema[$table][$field]['isBool'])) {
                $outValue = isset($value) ? '-1' : '0';
            }
            if (isset($tableSchema[$table][$field]['required']) && isset($tableSchema[$table][$field]['datetime'])) {
                $outValue = $value ? date('m/d/Y h:i:s A', strtotime($value)) : '';
            }
            if (isset($tableSchema[$table][$field]['number']) && isset($tableSchema[$table][$field]['number'])) {
                $outValue = $value ? $value : '0';
            }
        }
        return $outValue;   
    }

    /**
        Function : _groupAssoc
        Purpose : Use to group by an array by a field
        Created On : 07/08/15
        Created By : Rahul D.
    */       
    public function _groupAssoc($reqArray, $groupBy) {
        $resArray = array();
        if (!empty($reqArray)) {
            foreach($reqArray as $collect) {
                if (array_key_exists($groupBy, $collect)){
                    $resArray[$collect[$groupBy]][] = $collect;
                }
            }
        }
        return $resArray;
    }    
 
    /**
        Function : _convertArrayToXml
        Purpose : Use to create xml structure of an array
        Created On : 07/08/15
        Created By : Rahul D.
    */ 
    public function _convertArrayToXml($xmlArray=[]) {
        $xmlString = "";
        if (!empty($xmlArray)) {
            $xmlObject = Xml::fromArray($xmlArray, ['format' => 'tags']);
            $xmlString = $xmlObject->asXML();   
        }
        return $xmlString;
    }

    /**
        Function : _fetchMetaData
        Purpose : Use to fetch metadata info for indicator, source or area
        Created On : 07/08/15
        Created By : Rahul D.
    */     
    public function _fetchMetaData($target="") {
        $records = array();
        if (!empty($target)) {
            $target = strtolower($target);
            $asField = 'Indicator_NId';
            if ($target=="a")
                $asField = 'Layer_NId';
            if ($target=="s")
                $asField = 'IC_NId';
                
            $table = "ut_metadata_category_en";
            $fields = [
                       "ut_metadata_category_en.CategoryNId",
                       "ut_metadatareport_en.Target_Nid AS {$asField}",
                       "ut_metadata_category_en.CategoryName",
                       "ut_metadatareport_en.Metadata",
                       "ut_metadata_category_en.CategoryType"
                       ];
            $joins = " INNER JOIN ut_metadatareport_en
                       ON (ut_metadatareport_en.Category_Nid=ut_metadata_category_en.CategoryNId)";
            $conditions = "LOWER(ut_metadata_category_en.CategoryType) = '$target' AND ut_metadatareport_en.Target_Nid <> ''";          
            $fields_str = !empty($fields) ? implode(", ", $fields) : "*";
        }     
        $sql = "SELECT $fields_str
                FROM $table
                $joins
                WHERE $conditions 
                ";
        $st = $this->conn->prepare($sql);
        $st->execute();
        $data = $st->fetchAll();
        if (!empty($data))
            $records = $data;
        return $records;
    }

    /**
        Function : _updateEntityFieldByPk
        Purpose : Use to update a field of a table according to criteria what has passed
        Created On : 07/08/15
        Created By : Rahul D.
    */        
    public function _updateEntityFieldByPk($entity="", $pkField="", $pkVal="", $field="", $value="") {
        $status=false;
        if (!empty($entity) && !empty($pkField) && !empty($pkVal) && !empty($field)) {
            $command = "UPDATE $entity
                        SET $field = :$field
                        WHERE $pkField = :$pkField";
            $stmt = $this->mdbCon->prepare($command);
            $stmt->bindParam(':'.$pkField,$pkVal);
            $stmt->bindParam(':'.$field,$value);
            $status = $stmt->execute();
            return $status;
        }
    }

    /**
        Function : _callbkIndicatorUpdateMetaData
        Purpose : Use to update metadata for Indicator
        Created On : 07/08/15
        Created By : Rahul D.
    */      
    public function _callbkIndicatorUpdateMetaData() {
        $status = false;
        $data = $this->_fetchMetaData("i");
        $rslt = $this->_groupAssoc($data, 'Indicator_NId');
        if (!empty($rslt)) {
            foreach ($rslt as $node=>$info) {
                $index = 0;
                $nodeArray = array();
                $xmlObj = "";
                if (!empty($info)) {
                    foreach ($info as $iter=>$collect) {
                        $nodeArray['metadata']['Category'][$index]['@name'] = utf8_encode($collect['CategoryName']);
                        $nodeArray['metadata']['Category'][$index]['para'] = utf8_encode($collect['Metadata']);
                        $index++;
                    }
                }
                try{
                    $xmlObj = $this->_convertArrayToXml($nodeArray);
                    $status = $this->_updateEntityFieldByPk("ut_indicator_en", "Indicator_NId", $node, "Indicator_Info", $xmlObj);
                    if(!$status) throw new Exception('Error occured while updating Indicator_Info metadata');
                } catch(Exception $err) {
                    $status = $err->getMessage();
                }
            }
        }
        return $status;
    }    

    /**
        Function : _callbkAreaUpdateMetaData
        Purpose : Use to update metadata for area
        Created On : 07/08/15
        Created By : Rahul D.
    */     
    public function _callbkAreaUpdateMetaData() {
        $status = false;
        $data = $this->_fetchMetaData("a");
        $rslt = $this->_groupAssoc($data, 'Layer_NId');
        if (!empty($rslt)) {
            foreach ($rslt as $node=>$info) {
                $index = 0;
                $nodeArray = array();
                $xmlObj = "";
                if (!empty($info)) {
                    foreach ($info as $iter=>$collect) {
                        $nodeArray['metadata']['Category'][$index]['@name'] = utf8_encode($collect['CategoryName']);
                        $nodeArray['metadata']['Category'][$index]['para'] = utf8_encode($collect['Metadata']);
                        $index++;
                    }
                }
                try{                    
                    $xmlObj = $this->_convertArrayToXml($nodeArray);
                    $status = $this->_updateEntityFieldByPk("ut_area_map_metadata_en", "Layer_NId", $node, "Metadata_Text", $xmlObj);
                    if(!$status) throw new Exception('Error occured while updating Indicator_Info metadata');
                } catch(Exception $err) {
                    $status = $err->getMessage();
                }
            }
        }
        return $status;
    }

    /**
        Function : _callbkSourceUpdateMetaData
        Purpose : Use to update metadata for source
        Created On : 07/08/15
        Created By : Rahul D.
    */     
    public function _callbkSourceUpdateMetaData() {
        $status = false;
        $data = $this->_fetchMetaData("s");
        $rslt = $this->_groupAssoc($data, 'IC_NId');
        if (!empty($rslt)) {
            foreach ($rslt as $node=>$info) {
                $index = 0;
                $nodeArray = array();
                $xmlObj = "";
                if (!empty($info)) {
                    foreach ($info as $iter=>$collect) {
                        $nodeArray['metadata']['Category'][$index]['@name'] = utf8_encode($collect['CategoryName']);
                        $nodeArray['metadata']['Category'][$index]['para'] = utf8_encode($collect['Metadata']);
                        $index++;
                    }
                }
                try{
                    $xmlObj = $this->_convertArrayToXml($nodeArray);
                    $status = $this->_updateEntityFieldByPk("ut_indicator_classifications_en", "IC_NId", $node, "IC_Info", $xmlObj);
                    if(!$status) throw new Exception('Error occured while updating Indicator_Info metadata');
                } catch(Exception $err) {
                    $status = $err->getMessage();
                }
            }
        }
        return $status;
    }    

    /**
        Function : _callbkDBVersion
        Purpose : Use to update database version
        Created On : 14/08/15
        Created By : Rahul D.
    */     
    public function _callbkDBVersion() {
        $status = false;
        $fields = [':Version_Number'=>'', ':Version_Change_Date'=>'', ':Version_Comments'=>''];
        $fields_str = $this->prepareFieldsString($fields, ":");
        $fields_str_with_coln = $this->prepareFieldsString($fields);
        $data_arr[":Version_Number"] = '6.0.0.1';
        $data_arr[":Version_Change_Date"] = date('d M Y');
        $data_arr[":Version_Comments"] = 'Convert from DI7 database to DI6.1';
        $stmt = $this->mdbCon->prepare("INSERT INTO db_version ( $fields_str ) VALUES ( $fields_str_with_coln )");
        $status = $stmt->execute($data_arr);      
        return $status;
    }
    
    /**
        Function : _dumpToMSAccess
        Purpose : This function will fetch data on basis of given threshold and create threshold's size csv then dump that csv into MS-Access database
        Created On : 07/08/15
        Created By : Rahul D.
    */        
    public function _dumpToMSAccess($fields=[], $table="") {
        $status = false;
        $idDirCreated = false;
        $threshold=10000;
        $timeStamp = date('dmyHis');
        $temp_dir = $this->pluginWebRoot . 'sql_temp\\' . $table . $timeStamp ;
        if (!file_exists($temp_dir)) {
                @mkdir($this->pluginWebRoot . 'sql_temp');
                @mkdir($temp_dir);
        }
        $abs_temp_dir = $temp_dir;
        $temp_dir = str_replace("\\", "\\\\", $temp_dir).'\\';
        $pKy = "";
        if (isset($fields[0]))
            $pKy = $fields[0];
        $this->_truncateMSAccessTable($table, $pKy);
        
        $fetch_fields  = implode(', ', $fields);
        $fields_with_IfNull_collect = $this->_addIsNullPrefix($fields);
        $fields_with_IfNull = implode(', ', $fields_with_IfNull_collect);
        
        // Fetch data start
        $sql = "SELECT COUNT($pKy) as total FROM $table";
        $st = $this->conn->prepare($sql);
        $st->execute();
        $totalCounts = $st->fetchAll();
        $toProcess = $threshold;
        if (!empty($totalCounts[0]['total']) && $totalCounts[0]['total']>$toProcess) { // Save records using chunks
            $total = $totalCounts[0]['total'];
            $offSet = 0;
            $chunksLen = ceil($total / $toProcess);
            $cunksCollect = [];
            $msSqlLimit = [];
            for ($x=0; $x<$chunksLen; $x++) {
                $cunksCollect[] = "$offSet,$toProcess";
                $msSqlLimit[$x]['start'] = $offSet;
                $offSet = $offSet+$toProcess;
                $msSqlLimit[$x]['end'] = $offSet;
            }
            if (!empty($cunksCollect) && !empty($msSqlLimit)) {
                $data = array();
                foreach ($cunksCollect as $iter=>$limit) {
                    if (isset($this->dbVendor) && $this->dbVendor=="mssql")
                        $sql = " SELECT c.* FROM
                                ( SELECT ROW_NUMBER() OVER(ORDER BY $pKy) AS RowID,*  FROM $table ) AS c
                                WHERE c.RowID > " . $msSqlLimit[$iter]['start'] . " AND c.RowID <= " . $msSqlLimit[$iter]['end'];
                    else
                        $sql = "SELECT $fetch_fields FROM $table LIMIT $limit";
                    $st = $this->conn->prepare($sql);
                    $st->execute();
                    $data = $st->fetchAll(\PDO::FETCH_ASSOC);

                    $forSlot = $iter;
                    $temp_file_name = $temp_dir . '\\utab_name'.$forSlot.'.csv';
                    $temp_file_name = str_replace('utab_name', $table, $temp_file_name);
                    $temp_file_name_ins = $table."_$forSlot".".csv";
                    $csvFileToProcess = $abs_temp_dir . '\\'.$table.'_'.$forSlot.'.csv';                    
                    if (!empty($data)) {
                        $this->_generateCSV($csvFileToProcess, $fields, $data);
                        $status = $this->_bulkInsertInToMsAccess($table, $temp_dir, $temp_file_name_ins, $fetch_fields);
                        @unlink($csvFileToProcess);
                    }
                }
            }
        } else {
            $sql = "SELECT $fetch_fields FROM $table";
            $st = $this->conn->prepare($sql);
            $st->execute();
            $data = $st->fetchAll(\PDO::FETCH_ASSOC);
            $forSlot = 0;
            $temp_file_name = $temp_dir . '\\utab_name'.$forSlot.'.csv';
            $temp_file_name = str_replace('utab_name', $table, $temp_file_name);
            $temp_file_name_ins = $table."_$forSlot".".csv";
            $csvFileToProcess = $abs_temp_dir . '\\'.$table.'_'.$forSlot.'.csv';                    
            if (!empty($data)) {
                $this->_generateCSV($csvFileToProcess, $fields, $data);
                $status = $this->_bulkInsertInToMsAccess($table, $temp_dir, $temp_file_name_ins, $fetch_fields);
                @unlink($csvFileToProcess);
            }               
        }
        @rmdir($abs_temp_dir);
        return $status;
        // Fetch data end
    }    

    /**
        Function : _generateCSV
        Purpose : Use to cerate csv of an array
        Created On : 13/08/15
        Created By : Rahul D.
    */     
    public function _generateCSV($fileName="", $header_fields=[], $bulkData=[]) {
        $fp = fopen($fileName, 'w');
        fputcsv($fp, $header_fields);
        foreach ($bulkData as $collect) {
            fputcsv($fp, $collect);
        }
        fclose($fp);
        return true;
    }
 
    /**
        Function : _addIsNullPrefix
        Purpose : Add IsNull Prefix before table field
        Created On : 13/08/15
        Created By : Rahul D.
    */      
    public function _addIsNullPrefix($fields) {
        $fields_with_IfNull_collect = [];
        if (!empty($fields)) {
            foreach ($fields as $iter=>$collect) {
                $field_name = $collect;
                $fields_with_IfNull_collect[] = str_replace($field_name, "ISNULL($collect, '')", $collect);
            }
        }
        return $fields_with_IfNull_collect;
    }

    /**
        Function : _bulkInsertInToMsAccess
        Purpose : Add bulk data InTo MsAccess table
        Created On : 13/08/15
        Created By : Rahul D.
    */     
    public function _bulkInsertInToMsAccess($table, $fileLoc, $fileName, $fields) {
        $status = false;
        $msAccSql = "INSERT INTO $table ( $fields )
                     SELECT $fields
                     FROM [Text;Database=$fileLoc;HDR=yes].[$fileName]";
        $status = $this->mdbCon->query($msAccSql);
        return $status;
    }
    
    /**
        Function : _devinfo6TableAndFields
        Purpose : This function returns an array of all tables along with their field's name of devingo6.1
        Created On : 04/08/15
        Created By : Rahul D.
    */
    public function _devinfo6TableAndFields() {
        $file = $this->pluginWebRoot . 'ms-access-db\devinfo6_schema.json';
        if (file_exists($file)) {
            $devinfo6TableAndFields = file_get_contents($file);
            return json_decode($devinfo6TableAndFields, true);
        }
        return false;
    }    

    /**
        Function : _fetchTablesSchema
        Purpose : This function returns an array of those tables (along with their field's datatype) which does not have schema similar to devinfo7 
        Created On : 04/08/15
        Created By : Rahul D.
    */       
    public function _fetchTablesSchema() {
        $file = $this->pluginWebRoot . 'ms-access-db\devinfo6_fields_type.json';
        if (file_exists($file)) {
            $devinfo6ColumsType = file_get_contents($file);
            return json_decode($devinfo6ColumsType, true);
        }
        return false;
    }   

    public function sendEmail($toEmail, $fromEmail, $subject = null, $message = null, $type = 'Smtp') {
        $return = false;
        try {
            if (!empty($toEmail) && !empty($fromEmail)) {
                ($type == 'Smtp') ? $type = 'defaultsmtp' : $type = 'default';
                $emailClass = new Email($type);
                $result = $emailClass->emailFormat('html')->from([$fromEmail => $subject])->to($toEmail)->subject($subject)->send($message);
                if ($result) {
                    $return = true;
                }
            }
        } catch (Exception $e) {
            $return = $e;
        }

        return $return;
    }    
    
    public function sendDownloadLink() {
        $flag = false;
        $this->autoRender=false;
        if (!empty($this->request->data) && $this->request->is('post') && !empty($this->request->data['fileName'])) {
            $dbName = $this->request->data['fileName'];
            $email = $fromEmail = $this->request->data['userEmail'];
            $dlink = Router::url('/', true) . 'ConvertDI7ToMdb6/Operations/download?file=' . $dbName;
            $message= "Your request to download devinfo6.1 database has been completed.<a href='$dlink'>Click here to download MSAccess database</a>";
            $subject = 'D3A :: Request completed to download devinfo6.1 database';
            $message = "<div>Dear User,<br/><br/>
                Please 	<a href='" . $dlink . "'>Click here  </a> to download MS Access (devinfo6.1 format) database.<br/><br/>
                Thank you.<br/>
                Regards,<br/>
                D3A Support Team
                </div> ";
            try{
                $flag = $this->sendEmail($email, $fromEmail, $subject, $message, 'Smtp');
            } catch (Exception $e) {
               echo $e->getMessage();exit;
           } 
            echo $flag;exit;
        } else {
            echo 'Bad request.';exit;
        }
    }
  
    public function download() {
        $this->autoRender=false;
        $file = $this->dsnLocation . $this->request->query['file'];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/x-msaccess');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            exit('File not found. Please contact to site admin.');
        }     
    }  
  
}
