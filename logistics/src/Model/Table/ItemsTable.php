<?php

namespace App\Model\Table;

use App\Model\Entity\Item;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * Items Model
 */
class ItemsTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('items');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        
        $this->belongsTo('FieldOptionValues', [
            'className' => 'FieldOptionValues',
            'foreignKey' => 'type_id',
                //'conditions'=>array(),
        ]);
        
        
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
     * method to add items 
     * @fieldsArray is the posted data
     */
    function saveItem($fieldsArray = []) {

        $Area = $this->newEntity();
        $Area = $this->patchEntity($Area, $fieldsArray);
        if ($this->save($Area)) {
            return $Area->id;
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
