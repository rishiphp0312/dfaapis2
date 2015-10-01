<?php
namespace App\Model\Table;

use App\Model\Entity\Location;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Locations Model
 */
class LocationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('locations');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Localities', [
            'foreignKey' => 'locality_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Types', [
            'foreignKey' => 'type_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Statuses', [
            'foreignKey' => 'status_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Ownerships', [
            'foreignKey' => 'ownership_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Sectors', [
            'foreignKey' => 'sector_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Providers', [
            'foreignKey' => 'provider_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Genders', [
            'foreignKey' => 'gender_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('SecurityGroups', [
            'foreignKey' => 'security_group_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ModifiedUsers', [
            'className' => 'Users',
            'foreignKey' => 'modified_user_id'
        ]);
        $this->hasMany('LocationCustomFieldValues', [
            'foreignKey' => 'location_id',
            'dependent' => true,
        ]);
        $this->hasMany('Subscriptions', [
            'foreignKey' => 'location_id'
        ]);
    }
        
    /**
     * 
     * @param  $conditions array 
     * return array of location 
     */
    public function getLocationDetails($conditions=[]){
      
        return $query = $this->query()->select([])->where($conditions)->hydrate(false)->first();
        
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
        $location = $this->newEntity();

        //Update New Entity Object with data
        $location = $this->patchEntity($location, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($location);
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
