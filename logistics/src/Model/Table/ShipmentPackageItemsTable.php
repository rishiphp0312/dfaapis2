<?php

namespace App\Model\Table;

use App\Model\Entity\ShipmentPackageItem;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * ShipmentPackageItems Model
 */
class ShipmentPackageItemsTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('shipment_package_items');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
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

        if (isset($extra['contain']) && $extra['contain'] == true)
            $options['contain'] = ['Shipments', 'FieldOptionValues'];

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
        $query = $this->find($type, $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
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
        $item = $this->newEntity();

        //Update New Entity Object with data
        $item = $this->patchEntity($item, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($item);
        if ($result) {
            return $result->id;
        } else {
            return 0;
        }
    }

    /**
     * method to add/modify package items  
     * @fieldsArray is the posted data
     */
    public function addModifyPackageItems($fieldsArray = []) {

        $PackageItems = $this->newEntity();
        $PackageItems = $this->patchEntity($PackageItems, $fieldsArray);
        if ($this->save($PackageItems)) {
            return $PackageItems->id;
        } else {
            return 0;
        }
    }

    /**
     * function to modify user on passed conditions
     * @ fieldsArray fields to be updated 
     * @ conditions  to be passed to updated record 
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {

        $Package = $this->get($conditions);
        $Package = $this->patchEntity($Package, $fieldsArray);
        if ($this->save($Package)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
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
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    public function getCount($conditions = []) {
        return $total = $this->find()->where($conditions)->count();
       
    }

}
