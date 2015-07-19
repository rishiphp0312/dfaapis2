<?php  
namespace App\Model\Table;
use App\Model\Entity\MRole;
use Cake\ORM\Table;

/**
 * MRoles Model
 */
 
class MRolesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('m_roles');
        $this->primaryKey(_DATABASE_ROLE_ID);
        $this->addBehavior('Timestamp'); 
        $this->displayField(_DATABASE_ROLE_NAME); //used for find('list')
        
         $this->belongsToMany('RUserDatabases',[
            'targetForeignKey' => 'user_database_id',
            'foreignKey' => 'role_id',
            'joinTable' => 'r_user_database_roles',
            //   'through' => 'RUserDatabases',
        ]);
         
         $this->hasMany('RUserDatabases',[
            'targetForeignKey' => 'user_database_id',
            'foreignKey' => 'role_id',
            'joinTable' => 'r_user_database_roles',
            //   'through' => 'RUserDatabases',
        ]);
        
         
    }
     public function setListTypeKeyValuePairs(array $fields)
    {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    
    /**
     * listAllRoles method
     * list of all the roles from roles table
     * @return void
     */
    public function listAllRoles()
    { 
        $options = [];
        $fields = [_DATABASE_ROLE,_DATABASE_ROLE_NAME];

        if(!empty($fields))
            $options['fields'] = $fields;

        $this->setListTypeKeyValuePairs($fields);
              
        $query = $this->find('list',$options);        
        // Calling execute will execute the query
        // and return the result set.
         
        $results = $query->all();


        // Once we have a result set we can get all the rows
        $data = $results->toArray();
       
        return $data;
    }

    /**
     * return role and role name
     * @return void
     */
    public function getRoleByID($roleId=null)
    { 
        $options = [];

        if(!empty($roleId)) {
            $options['fields'] = [_DATABASE_ROLE,_DATABASE_ROLE_NAME];
            $options['conditions'] = array(_DATABASE_ROLE_ID=> $roleId);
            $results = $this->find('all', $options)->all()->toArray();
       
            return [$results[0][_DATABASE_ROLE], $results[0][_DATABASE_ROLE_NAME]];     
        }
    }
	
	
	/*
     * 
     * function to return the role id on basis of passed role value
     * @roleValue is  passed as roles  like 'ADMIN' or 'DATAENTRY'
     */
    function returnRoleId($roleValue=null){        
        $roleslist = $this->find('all')->combine(_DATABASE_ROLE,_DATABASE_ROLE_ID)->toArray();
        if(isset($roleValue) && $roleValue!=''){
            return $roleslist[$roleValue];
        }      
    }
	
	/*
     * 
     * Return the role value 'ADMIN','TEMPLATE' on basis of passed role id
     * 
     */
    function returnRoleValue($roleId=null){        
        $roleslist = $this->find('all')->combine(_DATABASE_ROLE_ID,_DATABASE_ROLE)->toArray();
        if(isset($roleId) && $roleId!=''){
            return $roleslist[$roleId];
        }      
    }



}