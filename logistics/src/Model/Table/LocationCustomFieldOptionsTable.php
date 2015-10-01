<?php
namespace App\Model\Table;

use App\Model\Entity\LocationCustomFieldOption;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * LocationCustomFieldOptions Model
 */
class LocationCustomFieldOptionsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('location_custom_field_options');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('LocationCustomFields', [
            'foreignKey' => 'location_custom_field_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ModifiedUsers', [
            'foreignKey' => 'modified_user_id'
        ]);
        $this->belongsTo('CreatedUsers', [
            'foreignKey' => 'created_user_id',
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
            ->requirePresence('value', 'create')
            ->notEmpty('value');
            
        $validator
            ->add('order', 'valid', ['rule' => 'numeric'])
            ->requirePresence('order', 'create')
            ->notEmpty('order');
            
        $validator
            ->add('visible', 'valid', ['rule' => 'numeric'])
            ->requirePresence('visible', 'create')
            ->notEmpty('visible');

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
        $rules->add($rules->existsIn(['location_custom_field_id'], 'LocationCustomFields'));
        $rules->add($rules->existsIn(['modified_user_id'], 'ModifiedUsers'));
        $rules->add($rules->existsIn(['created_user_id'], 'CreatedUsers'));
        return $rules;
    }
}
