<?php
namespace App\Model\Table;

use App\Model\Entity\Courier;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Couriers Model
 */
class CouriersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('couriers');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Statuses', [
            'foreignKey' => 'status_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Types', [
            'foreignKey' => 'type_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('CourierCustomFieldValues', [
            'foreignKey' => 'courier_id'
        ]);
        $this->hasMany('ShipmentLocations', [
            'foreignKey' => 'courier_id'
        ]);
        $this->belongsTo('FieldOptionValues', [
            'className' => 'FieldOptionValues',
            'foreignKey' => 'type_id',
                //'conditions'=>array(),
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
            ->requirePresence('code', 'create')
            ->notEmpty('code');
            
        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');
            
        $validator
            ->allowEmpty('login');
            
        $validator
            ->allowEmpty('contact');
            
        $validator
            ->allowEmpty('phone');
            
        $validator
            ->allowEmpty('fax');
            
        $validator
            ->add('email', 'valid', ['rule' => 'email'])
            ->allowEmpty('email');
            
        $validator
            ->allowEmpty('comments');

        return $validator;
    }

    /**
     * Set key value pairs for find-list
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * getRecords used get user details
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {

        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        $options['contain']=['FieldOptionValues'];
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
        $query = $this->find($type, $options);
        
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

    /**
     * 
     * method to delete the packages  
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
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
        //$query->update()->set($fieldsArray)->where($conditions); // Set
        //  $query->execute(); // Execute
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
        } else {
            return 0;
        }
    }
    
	
    /**
     * method to add Couriers 
     * @fieldsArray is the posted data
     */
    function saveCourier($fieldsArray = []) {

        $Courier = $this->newEntity();
        $Courier = $this->patchEntity($Courier, $fieldsArray);
        if ($this->save($Courier)) {
            return $Courier->id;
        } else {
            return 0;
        }
    }
    
    
    /**
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    public function getCount($conditions = []) {
        return $total = $this->find()->where($conditions)->count();
        //  return $total =  $this->query()->find()->where($conditions)->count();
    }
}
