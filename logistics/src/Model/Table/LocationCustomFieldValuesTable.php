<?php
namespace App\Model\Table;

use App\Model\Entity\LocationCustomFieldValue;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * LocationCustomFieldValues Model
 */
class LocationCustomFieldValuesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('location_custom_field_values');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('LocationCustomFields', [
            'foreignKey' => 'location_custom_field_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Locations', [
            'foreignKey' => 'location_id',
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
        $rules->add($rules->existsIn(['location_id'], 'Locations'));
        $rules->add($rules->existsIn(['modified_user_id'], 'ModifiedUsers'));
        $rules->add($rules->existsIn(['created_user_id'], 'CreatedUsers'));
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
        $shipment = $this->newEntity();

        //Update New Entity Object with data
        $shipment = $this->patchEntity($shipment, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($shipment);
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
