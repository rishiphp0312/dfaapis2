<?php

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * User Model
 */
class UsersTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('users');
        $this->primaryKey('id');
        $this->displayField('username');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
            'joinType' => 'LEFT'
        ]);
        $this->belongsTo('Locations', [
            'foreignKey' => 'location_id',
            'joinType' => 'LEFT'
        ]);
        $this->belongsTo('Couriers', [
            'foreignKey' => 'courier_id',
            'joinType' => 'LEFT'
        ]);
    }
    
    /**
     * Set key value pairs for find-list
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    

    /**
     * getRecords used get user details
     */
    public function getRecords(array $fields, array $conditions, $type = 'all',$extra=[]) {

        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        
        if(isset($extra['contain']) && !empty($extra['contain']))
            $options['contain'] = $extra['contain'];
            
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
       
        if(isset($extra['order']) && !empty($extra['order'])){
            $order = $extra['order'];
        }else{
            $order = ['username' =>'ASC'];
        }
        $query = $this->find($type, $options);
        
        $results = $query->order($order)->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
    }

    /**
     * get User details  with email ,id and name in auto complete list
     */
    function getAutoCompleteDetails() {
        $options['fields'] = ['id', 'email','username', 'role_id'];
        $options['conditions'] = [' role_id IS NULL '];
        $query = $this->find('all', $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();
        return $data;
    }

    /**
     * checkEmailExists is the function to check uniqueness of email
     */
    function checkEmailExists($email = null, $userId = null) {
        if (!empty($email))
            $conditions['email'] = $email;

        if (!empty($userId))
            $conditions['id !='] = $userId;

        $options['conditions'] = $conditions;
        //$options['fields']     = [_USER_ID];
        $query = $this->find('all', $options);
        $results = $query->hydrate(false)->count();
        return $results;
    }

    /**
     * check the status of activation link
     */
    function checkActivationLink($userId = null) {
        if (!empty($userId)) {
            $conditions['id'] = $userId;
            $conditions['status_id'] = _INACTIVE;
        }

        $options['conditions'] = $conditions;
        $options['fields'] = ['status_id'];
        $results = $this->find('all', $options)->hydrate(false)->count();
        return $results;
    }

    /**
     * function to add/modify user
     * @fieldsArray is the posted data
     */
    function addModifyUser($fieldsArray = []) {

        $User = $this->newEntity();
        $User = $this->patchEntity($User, $fieldsArray);
        if ($this->save($User)) {
            return $User->id;
        } else {
            return 0;
        }
    }

    /**
     * function to modify user on passed conditions
     * @ fieldsArray fields to be updated 
     * @ conditions  to be passed to updated record 
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {

        $User = $this->get($conditions);
        $User = $this->patchEntity($User, $fieldsArray);
        if ($this->save($User)) {
            return 1;
        } else {
            return 0;
        }
    }
    
    
     /**
     * checkUsernameExists is the function to check uniqueness of username
     */
    function checkUsernameExists($username = null, $userId = null) {
        if (!empty($username))
            $conditions['username'] = $username;

        if (!empty($userId))
            $conditions['id !='] = $userId;

        $options['conditions'] = $conditions;
        //$options['fields']     = [_USER_ID];
        $query = $this->find('all', $options);
        $results = $query->hydrate(false)->count();
        return $results;
    }
    
    /*
     * 
     * method to delete the users 
     * @conditions array 
     */
    public function deleteRecords(array $conditions)
    {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        else
            return 0;
    }
}
