<?php
namespace App\Model\Table;

use App\Model\Entity\ConfigItemOption;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ConfigItemOptions Model
 */
class ConfigItemOptionsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('config_item_options');
        $this->displayField('option');
        $this->primaryKey('id');
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
            ->requirePresence('option_type', 'create')
            ->notEmpty('option_type');
            
        $validator
            ->requirePresence('option', 'create')
            ->notEmpty('option');
            
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
}
