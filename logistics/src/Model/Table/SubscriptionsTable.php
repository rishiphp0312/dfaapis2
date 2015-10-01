<?php
namespace App\Model\Table;

use App\Model\Entity\Subscription;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Subscriptions Model
 */
class SubscriptionsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('subscriptions');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Locations', [
            'foreignKey' => 'location_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ModifiedUsers', [
            'className' => 'Users',
            'foreignKey' => 'modifiedBy'
        ]);
        $this->belongsTo('CreatedUsers', [
            'className' => 'Users',
            'foreignKey' => 'createdBy',
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
            ->allowEmpty('name');
            
        $validator
            ->add('email', 'valid', ['rule' => 'email'])
            ->allowEmpty('email');
            
        $validator
            ->allowEmpty('mobile');
            
        $validator
            ->add('alert', 'valid', ['rule' => 'numeric'])
            ->requirePresence('alert', 'create')
            ->notEmpty('alert');
            
        $validator
            ->allowEmpty('comments');
            
        $validator
            ->add('createdBy', 'valid', ['rule' => 'numeric'])
            ->requirePresence('createdBy', 'create')
            ->notEmpty('createdBy');
            
        $validator
            ->add('modifiedBy', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('modifiedBy');

        return $validator;
    }

    /**
     * setListTypeKeyValuePairs method
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * getRecords method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        if (isset($extra['limit']))
            $options['limit'] = $extra['limit'];

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $data = $this->find($type, $options)->hydrate(false)->all()->toArray();
        return $data;
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
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['area_id'], 'Areas'));
        $rules->add($rules->existsIn(['location_id'], 'Locations'));
        return $rules;
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
      $extra if extra is nid then returns nid else gives gid
     */
    public function saveRecords($fieldsArray = []) {
        //Create New Entity
        $subscription = $this->newEntity();

        //Update New Entity Object with data
        $subscription = $this->patchEntity($subscription, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($subscription);
        if ($result) {
            return $result->id;
        } else {
            return 0;
        }
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function saveBulkRecords($dataArray = []) {
        // IF only one record being inserted/updated
        if (count($dataArray) == 1) {
            return $this->insertData(reset($dataArray));
        }

        // Remove any Duplicate entry
        $dataArray = array_intersect_key($dataArray, array_unique(array_map('serialize', $dataArray)));

        //Create New Entities (multiple entities for multiple rows/records)
        $entities = $this->newEntities($dataArray);

        foreach ($entities as $entity) {
            if (!$entity->errors()) {
                //Create new row and Save the Data
                $this->save($entity);
            }
        }
    }
}
