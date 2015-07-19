<?php

namespace App\Model\Table;

use App\Model\Entity\MDatabaseConnection;
use Cake\ORM\Table;

/**
 * MDatabaseConnectionsTable Model
 *
 */
class MDatabaseConnectionsTable extends Table {

    /**
     * Initialize method     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('m_database_connections');
        $this->primaryKey('ID');
        $this->addBehavior('Timestamp');

        $this->belongsToMany('Users', [
            'targetForeignKey' => 'user_id',
            'foreignKey' => 'db_id',
            'joinTable' => 'r_user_databases',
        ]);
    }

    public function getDbConnectionDetails($ID = null) {
        $result = false;
        $db_jsondetails = '';
        $options = [];
        if (isset($ID) && !empty($ID)) {
            $options['conditions'] = array(_DATABASE_CONNECTION_DEVINFO_DB_ID => $ID, 'archived' => 0);
            //$options['fields'] => array('devinfo_db_connection') ;
        }
        if ($ID != '') {
            $MDatabaseConnections = $this->find('all', $options);
            $result = $MDatabaseConnections->hydrate(false)->first();
            if (!empty($result)) {
                $db_jsondetails = $result[_DATABASE_CONNECTION_DEVINFO_DB_CONN];
            }
        }
        return $db_jsondetails;
    }

    /*
     * getDbNameByID function 
     * get db details on basis of Id 
     * 
     */

    public function getDbNameByID($ID = null) {
        $result = false;
        $data = [];
        $options = [];
        if (isset($ID) && !empty($ID)) {
            $options['conditions'] = array(_DATABASE_CONNECTION_DEVINFO_DB_ID => $ID, 'archived' => 0);
        }
        if ($ID != '') {
            $MDatabaseConnections = $this->find('all', $options);
            $result = $MDatabaseConnections->hydrate(false)->first();
            if (!empty($result)) {
                $db_jsondetails = $result[_DATABASE_CONNECTION_DEVINFO_DB_CONN];
                $jsonresult = json_decode($db_jsondetails, true);
                $data['id'] = $result[_DATABASE_CONNECTION_DEVINFO_DB_ID];
                $data['dbName'] = $jsonresult['db_connection_name'];
            }
        }
        return $data;
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        $databaseDetails = $this->newEntity();
        $databaseDetails = $this->patchEntity($databaseDetails, $fieldsArray);
        if ($this->save($databaseDetails)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * uniqueConnection method
     *
     * @param  $connectionName the connection name uniqueness  {DEFAULT : empty}
     * @return void
     */
    public function uniqueConnection($connectionName = null) {

        $options = array();
        $getconnectionname = array();
        $options['fields'] = array(_DATABASE_CONNECTION_DEVINFO_DB_CONN);
        $MDatabaseConnections = $this->find('all', $options);
        $result = $MDatabaseConnections->hydrate(false)->all();
        if (isset($result) && !empty($result)) {
            foreach ($result as $index => $valuedb) {
                $connectionObject = json_decode($valuedb[_DATABASE_CONNECTION_DEVINFO_DB_CONN], true);
                if (isset($connectionObject['db_connection_name'])) {
                    if (strtolower(trim($connectionName)) == strtolower(trim($connectionObject['db_connection_name']))) {
                        return false; // connection already exists
                    }
                }
                // new connection 
            } // end of foreach
        } // end of if
        return true;
    }

    /**
     * getAllDatabases method
     *
     * @param get all databasess for super admin 
     * @return void
     */
    public function getAllDatabases() {

        $options = array();
        $data = array();
        $getconnectionname = array();
        $options['conditions'] = array(_DATABASE_CONNECTION_DEVINFO_DB_ARCHIVED => _DBNOTDELETED); //1 means deleted dbs       
        $options['fields'] = array(_DATABASE_CONNECTION_DEVINFO_DB_CONN, _DATABASE_CONNECTION_DEVINFO_DB_ID);
        //$options['devinfo_db_connection']=
        $MDatabaseConnections = $this->find('all', $options);
        $result = $MDatabaseConnections->hydrate(false)->all();
        if (isset($result) && !empty($result)) {
            $cnt = 0;
            foreach ($result as $index => $valuedb) {
                $connectionObject = json_decode($valuedb[_DATABASE_CONNECTION_DEVINFO_DB_CONN], true);
                if (isset($connectionObject['db_connection_name']) && !empty($connectionObject['db_connection_name'])) {
                    $data[$cnt]['id'] = $valuedb[_DATABASE_CONNECTION_DEVINFO_DB_ID];
                    $data[$cnt]['dbName'] = $connectionObject['db_connection_name'];

                    $cnt++;
                }
            }
            $data['user']['role'] = _SUPERADMINNAME;
        }

        return $data;
    }

    /**
     * deleteDatabase method
     * @param  $userId the database id   {DEFAULT : empty}
     * @param  $dbId the database id   {DEFAULT : empty}
     * @return void
     */
    public function deleteDatabase($dbId = null, $userId = null) {

        $fieldsArray = array();
        $fieldsArray[_DATABASE_CONNECTION_DEVINFO_DB_ARCHIVED] = _DBDELETED; // means deleted 
        if (!empty($dbId))
            $fieldsArray[_DATABASE_CONNECTION_DEVINFO_DB_ID] = $dbId;
        if (!empty($userId))
            $fieldsArray[_DATABASE_CONNECTION_DEVINFO_DB_MODIFIEDBY] = $userId;

        if (!empty($dbId) && !empty($userId)) {

            $databaseDetails = $this->newEntity();
            $databaseDetails = $this->patchEntity($databaseDetails, $fieldsArray);
            if ($this->save($databaseDetails)) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    /*
     * listAllUsersDb list all users associated to specific dbid
     * dbId the database id   	 
     *  
     */

    public function listAllUsersDb($dbId = null) {

        $data = array();
        $All_databases = $this->find()->where(['id' => $dbId])->contain(['Users'], true)->hydrate(false)->all()->toArray();
        $All_databases = current($All_databases)['users'];
        if (isset($All_databases) && !empty($All_databases)) {
            foreach ($All_databases as $index => $valueUsers) {
                $data[$index][_USER_EMAIL] = $valueUsers[_USER_EMAIL];
                $data[$index][_USER_NAME] = $valueUsers[_USER_NAME];
                $data[$index][_USER_ID] = $valueUsers[_USER_ID];
                $data[$index][_USER_STATUS] = $valueUsers[_USER_STATUS];
                $data[$index]['lastLoggedIn'] = strtotime($valueUsers[_USER_LASTLOGGEDIN]);
                // $data[$index]['roles']  = ['Admin'];
                $data[$index]['access']['area'] = 0;
                $data[$index]['access']['indicator'] = 0;
            }
        }

        return $data;
    }

}
