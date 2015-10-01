<?php
namespace App\Model\Table;

use App\Model\Entity\Role;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Roles Model
 */
class RolesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('roles');
        $this->displayField('role');
        $this->primaryKey('id');
        $this->hasMany('Users', [
            'foreignKey' => 'role_id'
        ]);
        $this->belongsToMany('Modules', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'module_id',
            'joinTable' => 'roles_modules'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->requirePresence('role', 'create')
            ->notEmpty('role');
            
        $validator
            ->requirePresence('role_name', 'create')
            ->notEmpty('role_name');
            
        $validator
            ->allowEmpty('description');

        return $validator;
    }
    
    /**
     * Set key value pairs for find-list
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    
    
    /*
     * 
     * function to return the role id on basis of passed role value
     * @roleValue is  passed as roles  like 'ADMIN' or 'DATAENTRY'
     */
    public function returnRoleId($roleValue=null){        
        $id = 0;        
        if(!empty($roleValue)  ){
            $query = $this->query()->select(['id'])->where(['role'=>$roleValue]);
            $roleslist = $query->hydrate(false)->first();
            if( !empty($roleslist)){
                $id = $roleslist['id'];
            }
        }
        return  $id;
    }
	
    /*
     * 
     * Return the role value 'ADMIN','TEMPLATE' on basis of passed role id
     * 
     */
    public function returnRoleValue($roleId=null){        
    
         $role = '';        
        if(!empty($roleId)  ){
            $query = $this->query()->select(['role',''])->where(['id'=>$roleId]);
            $roleslist = $query->hydrate(false)->first();
            if( !empty($roleslist)){
                $role = $roleslist['role'];
            }
        }
        return  $role;
      
    }
}

