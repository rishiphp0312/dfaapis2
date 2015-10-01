<?php

namespace App\Model\Table;

use App\Model\Entity\UserLog;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * User Model
 */
class UserLogsTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('user_logs');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }
    
    /**
     * Set key value pairs for find-list
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * add user LoggedIn details 
     */
    public function savedata($fieldsArray = []) {

        $UserLog = $this->newEntity();
        $UserLog = $this->patchEntity($UserLog, $fieldsArray);
        if ($this->save($UserLog)) {
            return $UserLog->id;
        } else {
            return 0;
        }
    }

    /**
     * getRecords used get user details
     */
    public function getRecords(array $fields, array $conditions, $type = 'all') {

        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
        $query = $this->find($type, $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
    }
    
     /**
     * method to delete the user logs  
     * @conditions array 
     */
    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        else
            return 0;
    }


}
