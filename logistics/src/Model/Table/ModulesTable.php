<?php
namespace App\Model\Table;

use App\Model\Entity\Module;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Modules Model
 */
class ModulesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('modules');
        $this->displayField('title');
        $this->primaryKey('id');
        $this->belongsTo('ParentModules', [
            'className' => 'Modules',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('ChildModules', [
            'className' => 'Modules',
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsToMany('Roles', [
            'foreignKey' => 'module_id',
            'targetForeignKey' => 'role_id',
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
            ->requirePresence('title', 'create')
            ->notEmpty('title');
            
        $validator
            ->add('active', 'valid', ['rule' => 'boolean'])
            ->requirePresence('active', 'create')
            ->notEmpty('active');

        return $validator;
    }
    
    /**
     * Set key value pairs for find-list
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }
}
