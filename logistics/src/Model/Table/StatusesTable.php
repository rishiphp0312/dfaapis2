<?php
namespace App\Model\Table;

use App\Model\Entity\Status;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Statuses Model
 */
class StatusesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('statuses');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Couriers', [
            'foreignKey' => 'status_id'
        ]);
        $this->hasMany('Items', [
            'foreignKey' => 'status_id'
        ]);
        $this->hasMany('Locations', [
            'foreignKey' => 'status_id'
        ]);
        $this->hasMany('ShipmentLocations', [
            'foreignKey' => 'status_id'
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'status_id'
        ]);
        $this->belongsTo('ModifiedUsers', [
            'className' => 'Users',
            'foreignKey' => 'modified_user_id'
        ]);
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
        $entity = $this->newEntity();

        //Update New Entity Object with data
        $entity = $this->patchEntity($entity, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($entity);
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
            return $this->saveRecords(reset($dataArray));
        }

        // Remove any Duplicate entry
        $dataArray = array_intersect_key($dataArray, array_unique(array_map('serialize', $dataArray)));

        //Create New Entities (multiple entities for multiple rows/records)
        $entities = $this->newEntities($dataArray);
        $entities = $this->patchEntities($entities, $dataArray);

        foreach ($entities as $entity) {
            if (!$entity->errors()) {
                //Create new row and Save the Data
                $this->save($entity);
            }
        }
    }
}
