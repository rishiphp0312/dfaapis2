<?php
namespace App\Model\Table;

use App\Model\Entity\RolesModule;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RolesModules Model
 */
class RolesModulesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('roles_modules');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Modules', [
            'foreignKey' => 'module_id',
            'joinType' => 'INNER'
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
            ->add('create', 'valid', ['rule' => 'boolean'])
            ->requirePresence('create', 'create')
            ->notEmpty('create');
            
        $validator
            ->add('read', 'valid', ['rule' => 'boolean'])
            ->requirePresence('read', 'create')
            ->notEmpty('read');
            
        $validator
            ->add('update', 'valid', ['rule' => 'boolean'])
            ->requirePresence('update', 'create')
            ->notEmpty('update');
            
        $validator
            ->add('delete', 'valid', ['rule' => 'boolean'])
            ->requirePresence('delete', 'create')
            ->notEmpty('delete');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['role_id'], 'Roles'));
        $rules->add($rules->existsIn(['module_id'], 'Modules'));
        return $rules;
    }
}
