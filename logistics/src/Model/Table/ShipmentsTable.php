<?php
namespace App\Model\Table;

use App\Model\Entity\Shipment;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Shipments Model
 */
class ShipmentsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('shipments');
        $this->displayField('code');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('ShipmentLocationAttachments', [
            'foreignKey' => 'shipment_id'
        ]);
        $this->hasMany('ShipmentLocations', [
            'foreignKey' => 'shipment_id'
        ]);
        $this->hasMany('ShipmentPackageItems', [
            'foreignKey' => 'shipment_id'
        ]);
        $this->hasMany('ShipmentPackages', [
            'className' => 'ShipmentPackages',
            'foreignKey' => 'shipment_id'
        ]);
        $this->belongsTo('FromLocations', [
            'className' => 'Locations',
            'foreignKey' => 'from_location_id',
        ]);
        $this->belongsTo('ToLocations', [
            'className' => 'Locations',
            'foreignKey' => 'to_location_id',
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
            ->allowEmpty('code');
            
        $validator
            ->add('shipment_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('shipment_date');

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
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
        
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
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
    
    /**
     * 
     * @param type $code
     * return shipment id 
     */
    public function getShipmentId($code=''){
        $conditions =['code'=>$code];
        return $query = $this->query()->select(['id'])->where($conditions)->hydrate(false)->first();
        
    }
    
    /*
     * 
     * method to delete the shipments   
     * @conditions array 
     */

    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        else
            return 0;
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
        
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $query = $this->find($type, $options);
        
        if(isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);exit;
        }
        
        // and return the result set.
        if(isset($extra['first']) && $extra['first'] == true) {
            $results = $query->first();
        } else {
            $results = $query->hydrate(false)->all();            
        }
        
        if(!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
    
        return $results;
    }
    
    
}
