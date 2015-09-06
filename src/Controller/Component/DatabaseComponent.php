<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;

/**
 * TransactionLogs Component
 */
class DatabaseComponent extends Component {

    //Loading Components
    public $components = ['DevInfoInterface.CommonInterface','Auth', 'TransactionLogs','UserCommon','Common','DevInfoInterface.Metadata'];
   
    public $MDatabaseConnections = '';
    public $MSystemConfirgurations = '';
    public $dbcon = '';
    public $conn = '';
    public $Users = '';
    public $Roles = '';
    
    public function initialize(array $config) {
        parent::initialize($config);       
        $this->session = $this->request->session();        
        $this->MDatabaseConnections = TableRegistry::get('MDatabaseConnections');
        $this->MSystemConfirgurations = TableRegistry::get('MSystemConfirgurations');
        $this->Users = TableRegistry::get('Users');
        $this->Roles = TableRegistry::get('MRoles');
    }
    
    /*
    Function to create a new database or update existing database connection
    */
    public function createUpdateDBConnection($inputArray, $dbId='') {
        $returnData = true;
        // identify the case
        $actionFlag='insert';

        $authUserId = $this->Auth->user(_USER_ID);

        // prepare connection details
        $conDetails = $this->createConnectionArray($inputArray);
        
        // Setup new database
        if(isset($inputArray['createDatabase']) && $inputArray['createDatabase'] == 'true') {
            $return = $this->createDI7Db($conDetails);
            if($return !== true) return $return;
        }
        
        // validating connection details
        $validate = $this->getValidatedDbConFields($inputArray, $dbId);
        if(isset($validate['errCode'])&& !empty($validate['errCode'])){            
            return ['error'=>$validate['errCode']];
        }
        // validation done

        // Preparing connection data
        $data = array();
        $data[_DATABASE_CONNECTION_DEVINFO_DB_CONN] = json_encode($conDetails);
        $data[_DATABASE_CONNECTION_DEVINFO_DB_CREATEDBY]  = $authUserId ;
        $data[_DATABASE_CONNECTION_DEVINFO_DB_MODIFIEDBY] = $authUserId ;
        
        if(!empty($dbId)){
            $data[_DATABASE_CONNECTION_DEVINFO_DB_ID] = $dbId;
            $actionFlag='update';     
        }
        $dbId = $this->MDatabaseConnections->insertData($data); 
        
        if($dbId > 0){
            // get and update meatadata information
            $this->getUpdateDbMetadataInAppDb($dbId, $actionFlag);
            $returnData = $dbId;
        }else{
            $returnData = ['error'=>_ERR100];
        } 
        
       return $returnData;       
    }


    /*
    function to get meatadata from database and update required data in application database
    @input: $dbId required
    @input: $actionFlag insert/update
    */
    public function getUpdateDbMetadataInAppDb($dbId, $actionFlag='insert') {

        if(!empty($dbId)) {
            $dbConnection = $this->Common->getDbConnectionDetails($dbId); //dbId
         
            ($actionFlag == 'insert') ? $updateFlag = 'no' : $updateFlag = 'yes';
            
            // get meatadata from database
            $metatData = $this->getMetadataInfo($dbConnection, $dbId, $updateFlag);
  
            if($metatData) {
                $updatedOn = (isset($metatData['updatedOn'])) ? $metatData['updatedOn'] : '';
                $updatedById = (isset($metatData['updatedById'])) ? $metatData['updatedById'] : 0;
                
                // update meatadata desc, data count, updated on and updated by in the data admin application database
                $this->updateMeatadataInAppDb($dbId, $metatData['description'], $metatData['noofdata'], $updatedOn, $updatedById);  
                  
            }            
        }        
    }


    /*
    function to get database metadata information
    */
    public function getMetadataInfo($dbConnection, $dbId, $updateFlag='yes') {       

        $returnData = [];

        if($dbConnection) {

            // call a plugin function to get dataabase details
            $params = ['fields'=>[],'conditions'=>[]];
            $metaData = $this->CommonInterface->serviceInterface('Metadata', 'getDbMetadataRecords', $params, $dbConnection);
         
            if($metaData) {
                $metaData = current($metaData);    
                $returnData['nId'] = $metaData[_DBMETA_NID];
                $returnData['description'] = $metaData[_DBMETA_DESC];
                $returnData['noofAreas'] = $metaData[_DBMETA_AREACNT];  // areaCount
                $returnData['noofIndicators'] = $metaData[_DBMETA_INDCNT]; // indCount
                $returnData['noofIus'] = $metaData[_DBMETA_IUSCNT];
                $returnData['noofSources'] = $metaData[_DBMETA_SRCCNT];
                $returnData['noofdata'] = $metaData[_DBMETA_DATACNT];
                $returnData['noofTime'] = $metaData[_DBMETA_TIMECNT];            

                if($updateFlag == 'yes') {
                    $updatedOn = '';
                    $updatedBy = 0; 
                    $updatedByName = '';   
                    $updatedDetails = $this->TransactionLogs->getDbUpdatedDetails($dbId);  
                    if($updatedDetails) {
                        $updatedOn = $updatedDetails['updatedOn'];
                        $updatedBy = $updatedDetails['updatedById']; 
                        $updatedByName = $updatedDetails['updatedByName'];    
                    }   
                    $returnData['userInfo']['userId'] = $updatedBy;
                    $returnData['userInfo']['created'] = strtotime($updatedOn);
                    $returnData['userInfo']['name'] = $updatedByName; 
                }
            }   
        }
        else {
            $returnData = ['error' => _ERR135];
        } 
        return $returnData;
    }


    /*
    function to display metadat on database home page
    and fetch meatdata infor from database and update in application page
    */
    public function displayMetadataInfoOnDbHome($dbConnection, $dbId, $updateFlag='yes') {       
           
         $metadatInfo = $this->getMetadataInfo($dbConnection, $dbId, 'yes'); 
        if($metadatInfo) {
            // update meatadata info in app database
            $meataDesc = (isset($metadatInfo['description'])) ? $metadatInfo['description'] : '';
            $dataCount = (isset($metadatInfo['noofdata'])) ? $metadatInfo['noofdata'] : '';
            $updatedOn = (isset($metadatInfo['userInfo'])) ? $metadatInfo['userInfo']['created'] : '';
            $updatedBy = (isset($metadatInfo['userInfo'])) ? $metadatInfo['userInfo']['userId'] : 0;

            $this->updateMeatadataInAppDb($dbId, $meataDesc, $dataCount, $updatedOn, $updatedBy);
        }           
         
        return $metadatInfo;
    }


    /*
    function to update meatadata info in application database
    */
    public function updateMeatadataInAppDb($dbId, $meataDesc, $dataCount, $updatedOn, $updatedBy) {

        if(!empty($dbId)) {
            // coming soon
            $authUserId = $this->Auth->user(_USER_ID);
            $data =[];
            $data[_DATABASE_CONNECTION_DEVINFO_DB_MODIFIEDBY] = $authUserId ;                    
            if(!empty($updatedOn)) $data[_DATABASE_CONNECTION_DEVINFO_DB_UPDATEDON] = $updatedOn;
            if(!empty($updatedBy)) $data[_DATABASE_CONNECTION_DEVINFO_DB_UPDATEDBY] = $updatedBy;                    
            if(!empty($meataDesc)) $data[_DATABASE_CONNECTION_DEVINFO_DB_METADESC] = $meataDesc;                    
            if(!empty($dataCount)) $data[_DATABASE_CONNECTION_DEVINFO_DB_DATACNT] = $dataCount;                    
            $data[_DATABASE_CONNECTION_DEVINFO_DB_ID] = $dbId;
                
            $returnId = $this->MDatabaseConnections->insertData($data);
            if($returnId>0){
                return true;
            }else{
                return ['error'=>_ERR100];
            }            
        }
    }

    /*
    function to update meatadata description in database
    */
    public function updateMetadataDescription($dbConnection, $desc, $dbMetaid, $dbId=''){
        $returnData = [];
        $authUserId = $this->Auth->user(_USER_ID);


        if (!empty($dbConnection) && !empty($dbMetaid) && !empty($desc)) {
            
            $params =['fieldsArray'=>[_DBMETA_DESC=>$desc] ,'conditions'=>[_DBMETA_NID=>$dbMetaid]];
            $result = $this->CommonInterface->serviceInterface('Metadata', 'updateDbMetadataRecords', $params, $dbConnection);
           
            if($result>0) {
                $returnData = true;

                if(!empty($dbId)) {  
                    //$this->updateMeatadataInAppDb($dbId, $desc, '', date('Y-m-d'), $authUserId);    
                }                
            }
            else {
                $returnData = ['error' => _ERR135];
            }                        
        }  
        else {
            $returnData = ['error' => _ERR135];
        }

        return $returnData;
    }
    
    /*
    function to prepare database connection array from inputs
    */
    public function createConnectionArray($inputArray=[]) {
        $returnArray = [];
        if($inputArray) {
            $returnArray = array(
                'db_source' => $inputArray['databaseType'],
                'db_connection_name' => $inputArray['connectionName'],
                'db_host' => $inputArray['hostAddress'],
                'db_login' => $inputArray['userName'],
                'db_password' => $inputArray['password'],
                'db_port' => $inputArray['port'],                          
                'db_database' => $inputArray['databaseName']
            );
        }        
        return $returnArray;
    }

    /*
    function to get validated database connection details
    */
    public function getValidatedDbConFields($inputArray = [], $dbId='') {

        $hasError = false;
        $errCode = ''; //Invalid Parameters supplied

        $returnData = ["isError" => false, "errCode" => ''];

        if (count($inputArray) > 0) {

            $dbSource = (isset($inputArray['databaseType'])) ? trim($inputArray['databaseType']) : '';
            $dbConnectionName = (isset($inputArray['connectionName'])) ? trim($inputArray['connectionName']) : '';
            $dbHost = (isset($inputArray['hostAddress'])) ? trim($inputArray['hostAddress']) : '';
            $dbLogin = (isset($inputArray['userName'])) ? trim($inputArray['userName']) : '';
            $dbPort = (isset($inputArray['port'])) ? trim($inputArray['port']) : '';
            $dbDatabase = (isset($inputArray['databaseName'])) ? $inputArray['databaseName'] : '';
            $dbPassword = (isset($inputArray['password'])) ? $inputArray['password'] : '';

            if (empty($dbConnectionName) || empty($dbHost) || empty($dbLogin) || empty($dbDatabase) || empty($dbPassword)|| empty($dbSource)|| empty($dbPort)) {                
                
                $hasError = TRUE;
                $errCode = _ERR135; //Missing Parameters

            } else {
                $dbCon = $this->createConnectionArray($inputArray);

                $jsonData = array(
                    _DATABASE_CONNECTION_DEVINFO_DB_CONN => json_encode($dbCon)
                );
                $jsonData = json_encode($jsonData);
                $returnTestDetails = $this->testConnection($jsonData);
                
                if ($returnTestDetails === true) {
                    //check unique connection name
                    $isUniqueCon = $this->MDatabaseConnections->uniqueConnection($dbConnectionName, $dbId);
                  
                    if (!$isUniqueCon === true) {
                        $hasError = TRUE;
                        $errCode = _ERR102; // connection name is  not unique
                    }                     
                } else {
                    $hasError = TRUE;
                    $errCode = _ERR101; // Invalid database connection details 
                }
            }
        } else {
            $hasError = TRUE;
            $errCode = _ERR135; //Missing Parameters
        }

        if ($hasError) {
            $returnData['isError'] = $hasError;
            $returnData['errCode'] = $errCode;
        }
       
        return $returnData;
    }
    
    
     /*
      Get List of the Database as per the logged in  User
     *
     */

    public function getDatabases() {

        $userId = $this->Auth->User('id');
        $roleId = $this->Auth->User('role_id');

        if ($roleId == _SUPERADMINROLEID) // for super admin acces to all databases            
            $returnDatabaseDetails = $this->MDatabaseConnections->getAllDatabases();
        else
            $returnDatabaseDetails = $this->getdatabaseListOfUser($userId); //db list for logged in user 

        return $returnDatabaseDetails;
    }
    
    
    
    /*
      getdatabaseListOfUser to get the list of all the databases associated to specific users
      $userId the user Id of user
     */

    public function getdatabaseListOfUser($userId) {
        $data = array();
        $All_databases = $this->Users->getdatabaseList($userId);
        $alldatabases = current($All_databases)['m_database_connections'];
        if (isset($alldatabases) && !empty($alldatabases)) {
            foreach ($alldatabases as $index => $valuedb) {
                $connectionObject = json_decode($valuedb[_DATABASE_CONNECTION_DEVINFO_DB_CONN], true);
                if (isset($connectionObject['db_connection_name']) && !empty($connectionObject['db_connection_name']) && $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ARCHIVED] == '0') {
                    $dbId = $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ID];
                    $data[] = [
                        'id' => $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ID],
                        'dbName' => $connectionObject['db_connection_name'],
                        'dbMetadata' => $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_METADESC],
                        'dbDataCount' => $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_DATACNT],
                        'dbUpdatedOn' => strtotime($valuedb[_DATABASE_CONNECTION_DEVINFO_DB_UPDATEDON]),
                        'dbRoles' => $this->UserCommon->getUserDatabasesRoles($userId, $dbId)
                    ];
                }
            }
        }
        return $data;
    }

        
    /*
     * Function deleteDatabase is used for deleting the database details
     * $dbId  database id 
     * $userId user id 
     */

    public function deleteDatabase($dbId, $userId) {

        return $databasedetails = $this->MDatabaseConnections->deleteDatabase($dbId, $userId);
    }
    
    /*
      Function getDbNameByID is to get  the database information with respect to passed database id
      @$dbId is used to pass the database id
     */

    public function getDbNameByID($dbId) {

        $databasedetails = array();

        $databasedetails = $this->MDatabaseConnections->getDbNameByID($dbId);

        return $databasedetails;
    }


    /*
     * 
     * check the database connection  
    */
    public function testConnection($connectionstring = null) {

        $db_source = '';
        $db_connection_name = '';
        $db_host = '';
        $db_password = '';
        $db_login = '';
        $db_database = '';
        $db_port = '';
        $connectionstringdata = [];
        $connectionstring = json_decode($connectionstring, true);

        if (isset($connectionstring[_DATABASE_CONNECTION_DEVINFO_DB_CONN])) {

            $connectionstringData = json_decode($connectionstring[_DATABASE_CONNECTION_DEVINFO_DB_CONN], true);
            $db_source = trim($connectionstringData['db_source']);
            $db_connection_name = trim($connectionstringData['db_connection_name']);
            $db_host = trim($connectionstringData['db_host']);
            $db_login = trim($connectionstringData['db_login']);
            $db_password = trim($connectionstringData['db_password']);
            $db_port = trim($connectionstringData['db_port']);
            $db_database = trim($connectionstringData['db_database']);

            $db_source = strtolower($db_source);
        }

        $flags = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );

        if ($db_source == 'mysql') {
            try {
                $this->dbcon = new \PDO('mysql:host=' . $db_host . ';dbname=' . $db_database, $db_login, $db_password, $flags);
                return true;
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        } else {
            try {
                $this->dbcon = new \PDO(
                        "sqlsrv:server={$db_host};Database={$db_database}", $db_login, $db_password, $flags
                );
                return true;
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        }
    }
    
     /*
      uniqueConnection is used to check the uniqueness of database connection name
      @$dbConnectionName is used to pass the database Connection Name
     */

    public function uniqueConnection($dbConnectionName) {
        $databasedetails = $this->MDatabaseConnections->uniqueConnection($dbConnectionName);
        return $databasedetails;
    }

    /**
     * set DB connection at runtime
     * 
     * @param string $dbConnection DB connection details
     * @return void
     */
    public function setDbConnection($dbConnection, $isJOSN = false) {
        
        if($isJOSN == true)
            $dbConnection = json_decode($dbConnection, true);
        
        $flags = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
        
        if ($dbConnection['db_source'] == 'mysql') {
            try {
                $this->dbcon = new \PDO('mysql:host=' . $dbConnection['db_host'], $dbConnection['db_login'], $dbConnection['db_password'], $flags);
                return true;
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        } else {
            try {
                $this->dbcon = new \PDO("sqlsrv:server={$dbConnection['db_host']};", $dbConnection['db_login'], $dbConnection['db_password'], $flags);
                return true;
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        }
    }
    
    /**
     * Create DI7 Database
     * 
     * @param string $dbConnection Database connection details
     * @return boolean true/false
     */
    public function createDI7Db($dbConnection) {
        
        // Set DB connection
        $return = $this->setDbConnection($dbConnection);
        if($return !== true) return $return;
        
        // Create DI7 DB if not exists
        try{
            $this->dbcon->query('CREATE DATABASE '.$dbConnection['db_database']);
            
            //-- Create DI7 Tables --//
            // Select newly created DB
            $this->dbcon->query('USE ' . $dbConnection['db_database']);
            
            // Get DI7 schema file
            if ($dbConnection['db_source'] == 'mysql')
                $dumpFile = _SCHEMA_DI7_MYSQL_FILE;
            else
                $dumpFile = _SCHEMA_DI7_MSSQL_FILE;
            
            $schemaStructure = file_get_contents( _EXTRAFOLDER . DS . $dumpFile);
            
            // Import the schema to newly created DB
            $this->dbcon->query($schemaStructure);
            
        } // Throw error if DB already exists
        catch (\PDOException $e) {
            
            $dbError = trim(strrchr($e->getMessage(), "]"), '[]');
            if(strpos($dbError, 'already exists') !== false) {
                return ['error' => _ERR180];
            } else {
                return $e->getMessage();
            }
        }
        
        return true;
    }

    /**
     * Create SaveAs DB Cron Job
     * 
     * @param array $params Job parameters
     * @param string $fromDbId From Database ID
     */
    public function createSaveAsJob($params, $fromDbId) {
        $this->Common->createCronJob('Copy Database', $params, $fromDbId, 2428);
        return true;
    }
    
    /**
     * save copy of DI7 db
     * 
     * @param array $param parameters
     * @param string $fromDbId From Database ID
     */
    public function saveAsDI7Db($param, $fromDbId) {
        $dbId = $this->createUpdateDBConnection($param);
        
        if ($dbId !== true && !is_string($dbId)){
            $param['toDbId'] = $dbId;
            try{
                $this->createSaveAsJob($param, $fromDbId);
            } catch (\PDOException $e) {
                return $e->getMessage();
            }
        }
        
        return $dbId;
    }
    
    /**
     * duplicate DB
     * 
     * @param array $param parameters
     */
    public function duplicateDb($param) {
        
    }

}
