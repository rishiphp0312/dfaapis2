<?php

namespace App\Model\Table;

use App\Model\Entity\FieldOptionValue;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * FieldOptionValues Model
 */
class FieldOptionValuesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('field_option_values');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
         $this->belongsTo('FieldOptions', [		   
            'foreignKey' => 'field_option_id',
            'joinType' => 'INNER',
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
    public function getRecords(array $fields, array $conditions, $type = 'all',$extra=[]) {

        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
        $query = $this->find($type, $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
    }

       
    
    /*
     * 
     * method to delete the packages  
     * @conditions array 
     */
    public function deleteRecords(array $conditions)
    {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        else
            return 0;
    }
    
    
    /**
     * 
     * method returns ID of plugin value 
     * @name plugin value
     * @plugin plugin value
     *  
     */
    public function getIdByName($name = '', $plugin = '') {
        $conditions = [];
        if ($name != '')
            $conditions = ['LOWER(FieldOptionValues.name)' => strtolower($name),'FieldOptions.plugin'=>$plugin];
         $data = $this->query()->select(['FieldOptionValues.id'])->where($conditions)->contain(['FieldOptions'], true)->hydrate(false)->first();
//$data =	$this->find('all')->where(['FieldOptions.plugin'  => $plugin,'FieldOptions.name'  => $name])->contain(['FieldOptionValues'], true)->hydrate(false)->all()->toArray();
		return $data;
     


        // make join with field option
        // input params
    }

}
