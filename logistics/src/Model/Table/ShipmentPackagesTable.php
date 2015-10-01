<?php

namespace App\Model\Table;

use App\Model\Entity\ShipmentPackage;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * ShipmentPackages Model
 */
class ShipmentPackagesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('shipment_packages');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Shipments', [
            'className' => 'Shipments',
            'foreignKey' => 'shipment_id',
                //'conditions'=>array(),
        ]);
        $this->belongsTo('FieldOptionValues', [
            'className' => 'FieldOptionValues',
            'foreignKey' => 'package_type_id',
                //'conditions'=>array(),
        ]);
         $this->hasMany('ShipmentPackageItems', [
            'className' => 'ShipmentPackageItems',
            'foreignKey' => 'package_id',
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

        if (isset($extra['contain']) && $extra['contain'] == true)
            $options['contain'] = $extra['model'];

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
     * method to add/modify package 
     * @fieldsArray is the posted data
     */
    function addModifyPackage($fieldsArray = []) {

        $Package = $this->newEntity();
        $Package = $this->patchEntity($Package, $fieldsArray);
        if ($this->save($Package)) {
            return $Package->id;
        } else {
            return 0;
        }
    }

    /**
     * function to modify package on passed conditions
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
     * method checkPackageCode  to check uniqueness of code 
     * @code is the code 
     * @shipId is the code 
     * @id is the code 
     */
    public function checkPackageCode($code = null, $shipId = null, $id = '') {
        if (!empty($code))
            $conditions['code'] = $code;

        if (!empty($shipId))
            $conditions['shipment_id'] = $shipId;

        if (!empty($id))
            $conditions['id !='] = $id;

        $options['conditions'] = $conditions;
        //$options['fields']     = [_USER_ID];
        $query = $this->find('all', $options);
        $results = $query->hydrate(false)->count();
        return $results;
    }

    /*
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
     * 
     * method to checkpkgcode exists or not 
     * $pkgCode package code 
     */
    public function checkPkgCodeExists($pkgCode = '', $pkgId = '') {
        if (!empty($pkgCode))
            $conditions['code'] = $pkgCode;

        if (!empty($pkgId))
            $conditions['id !='] = $pkgId;

        $options['conditions'] = $conditions;
        $query = $this->find('all', $options);
        $results = $query->hydrate(false)->count();
        return $results;
    }

    /*
      public function getShipcode(){

      $data = $this->find()->where($conditions)->contain(['Indicator', 'Unit', 'SubgroupVals'], true)->hydrate(false)->select($fields)->all()->toArray();

      } */
    
    
     /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = []) {
        $alias = 'maximum';
        //$query = $this->query()->select([$alias => 'MAX(' . $column . ')'])->where($conditions);
        $query = $this->query()->select([$alias => $column])->where($conditions)->order(['id' => 'DESC'])->limit(1);

        $data = $query->hydrate(false)->first();
        if (!empty($data)) {
            return $data[$alias];
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
