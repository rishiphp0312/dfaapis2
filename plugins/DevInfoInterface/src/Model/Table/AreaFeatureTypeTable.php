<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\AreaFeatureType;
use Cake\ORM\Table;
use Cake\Network\Session;

/**
 * Area Model
 */
class AreaFeatureTypeTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) 
    {
        $session = new Session();
        $defaultLangcode = $session->read('defaultLangcode');
        $this->table('UT_Area_Feature_Type_' . $defaultLangcode);
        $this->primaryKey('Feature_Type_NId');
        $this->addBehavior('Timestamp');
    }

    public static function defaultConnectionName() {
        return 'devInfoConnection';
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
    public function getRecords(array $fields, array $conditions, $type = 'all') {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        
        $data = $this->find($type, $options)->hydrate(false)->all()->toArray();
        return $data;
    }


    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        //Create New Entity
        $subgroupVal = $this->newEntity();
        
        //Update New Entity Object with data
        $subgroupVal = $this->patchEntity($subgroupVal, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($subgroupVal);
        if ($result) {
            return $result->{_AREAFEATURE_TYPE_NID};
        } else {
            return 0;
        }        
    }
    
   

}
