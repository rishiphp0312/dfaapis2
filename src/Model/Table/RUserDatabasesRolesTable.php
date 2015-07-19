<?php

namespace App\Model\Table;

use App\Model\Entity\RUserDatabasesRole;
use Cake\ORM\Table;

/**
 * RUserDatabasesRoles Model
 */
class RUserDatabasesRolesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('r_user_database_roles');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }
	
	 public function setListTypeKeyValuePairs(array $fields)
    {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }


    /*
     * Function to add roles of user with the database 
     */

    public function addUserRoles($fieldsArray = []) {
        $databaseUserRoles = $this->newEntity();
        $databaseUserRoles = $this->patchEntity($databaseUserRoles, $fieldsArray);
        if ($this->save($databaseUserRoles)) {
            return $databaseUserRoles->id;
        } else {
            return 0;
        }
    }

    /**
     * delete ROLES  method
      @$RUD_ids is the array of RUD
     * @return void
     */
    public function deleteUserRolesDatabase($RUD_ids = []) {

        $result = $this->deleteAll([_RUSERDBROLE_USER_DB_ID . ' IN' => $RUD_ids]);

        return $result;
    }

    /**
     * deleteUserRoles method when modifying user their roles may be updated 
      @$RUD_ids is the array of RUD table
     * $type IN  means roles  to be deleted 'NOT IN' roles not to be deleted 
	   default will be IN for type 
     * @return void
     */
    public function deleteUserRoles($RUD_ids = [], $roledids = [], $type = ' IN ') {

        
            $result = $this->deleteAll([_RUSERDBROLE_USER_DB_ID . ' IN' => $RUD_ids, _RUSERDBROLE_ROLE_ID . $type => $roledids]);
        

        return $result;
    }

    /**
     *  getRoleIDsDatabase get the role ids of specific user 
     *  @param  $ids  array of  ids  belongs to ids of r_user_databases   
     *  @return array  key for  RUDR id  and array value is  role_id 
     */
    public function getRoleIDsDatabase($ids = []) {
        
        $options['fields'] = array(_RUSERDBROLE_ID,_RUSERDBROLE_ROLE_ID);
        $this->setListTypeKeyValuePairs($options['fields']);
        $options['conditions'] = array(_RUSERDBROLE_USER_DB_ID . ' IN' => $ids);
        $query = $this->find('list', $options); //
        $returnRoleIds = $query->hydrate(false)->all()->toArray();    
        return $returnRoleIds;
    }

    /**
     * find ids  for specific users  method
     * @param  $ids the  ids in array for rows will be deleted    
     * @return void
     */
    public function getDetails(array $fields, array $conditions, $type = 'all', $extra = []) {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $query = $this->find($type, $options);
        $data = $query->hydrate(false)->all()->toArray();

        return $data;
    }

}
