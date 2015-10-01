<?php

namespace App\Model\Table;

use App\Model\Entity\FieldOption;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * FieldOptions Model
 */
class FieldOptionsTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('field_options');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
		$this->hasMany('FieldOptionValues', [		   
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

      method to get associated records for Types based on plugin  name
      @plugin plugin name
      @name plugin type name
     */
    public function getTypeLists($code = null) {
        $data = $this->find('all')->where(['FieldOptions.code' => $code, 'visible' => _VISBLE])->contain(['FieldOptionValues'], true)->hydrate(false)->all()->toArray();
        return $data;
    }

}
