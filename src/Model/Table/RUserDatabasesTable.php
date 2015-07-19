<?php

namespace App\Model\Table;

use App\Model\Entity\RUserDatabase;
use Cake\ORM\Table;

/**
 * RUserDatabasesTable Model
 *
 */
class RUserDatabasesTable extends Table {

    /**
     * Initialize method     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('r_user_databases');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');

        /*
         * $this->belongsTo('MDatabaseConnections',[
          //'targetForeignKey' => 'db_id',
          'foreignKey' => 'db_id']);
          $this->belongsTo('Users',[
          //'targetForeignKey' => 'db_id',
          'foreignKey' => 'user_id']);
         * 
         */
        $this->belongsToMany('MRoles', [
            'targetForeignKey' => _RUSERDBROLE_ROLE_ID,
            'foreignKey' => _RUSERDBROLE_USER_DB_ID,
            'joinTable' => 'r_user_database_roles',
        ]);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        //Create New Entity
        $databaseDetails = $this->newEntity();
        //Update New Entity Object with data
        $databaseDetails = $this->patchEntity($databaseDetails, $fieldsArray);
        //Create new row and Save the Data
        if ($this->save($databaseDetails)) {
            return 1;
        } else {
            return 0;
        }
    }

    /*
     * Function to add the  database relation with user
     * @$fieldsArray posted data
     */

    public function addUserDatabases($fieldsArray = []) {
        $databaseDetails = $this->newEntity();
        $databaseDetails = $this->patchEntity($databaseDetails, $fieldsArray);
        if ($this->save($databaseDetails)) {
            return $databaseDetails->id;
        } else {
            return 0;
        }
    }

    /**
     * deleteUserDatabase method
      delete the databases association with user
     * $ids is RUD ids in  array to delete 
     * @return void
     */
    public function deleteUserDatabase($ids = []) {

        $result = $this->deleteAll([_RUSERDB_ID . ' IN' => $ids]);

        return $result;
    }

    /**
     * getUserDatabaseId 
     * @param  $userId the  with respect to the rows of users in  r_user_databases   
     * @$dbId is database id  
     * @return the RUD  id of table of  specific dbid of specific user
     */
    public function getUserDatabaseId($userId = [], $dbId = null) {
        $returnIds = [];        
        $options['conditions'] = [_RUSERDB_USER_ID . ' IN' => $userId, _RUSERDB_DB_ID => $dbId];
        $data = $this->find('all', $options)->hydrate(false)->all()->toArray();
        if (isset($data)) {
            foreach ($data as $index => $valueId) {
                $returnIds[] = $valueId[_RUSERDBROLE_ID];
            }
        }
        return $returnIds;
    }

    /*
     *  check whether user is already added to database  or not 
     *  @userId is the user id 
     *  @dbId is the database id 
     */

    public function checkUserDbRelation($userId, $dbId) {

        $count = $this->find()->where([_RUSERDB_USER_ID => $userId, _RUSERDB_DB_ID => $dbId])->hydrate(false)->count();

        return $count;
    }

}
