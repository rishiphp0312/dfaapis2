<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Data;
use Cake\ORM\Table;

/**
 * Data Model
 */
class DataTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Data');
        $this->primaryKey(_MDATA_NID);
        $this->addBehavior('Timestamp');
    }

    /*
     * @Cakephp3: defaultConnectionName method
     * @Defines which DB connection to use from multiple database connections
     * @Connection Created in: CommonInterfaceComponent
     */

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
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        if (isset($extra['limit']))
            $options['limit'] = $extra['limit'];

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $data = $this->find($type, $options)->hydrate(false)->all()->toArray();

        //$data = $this->find()->all()->toArray();


        return $data;
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        $data = $this->newEntity();
        $data = $this->patchEntity($data, $fieldsArray);
        $result = $this->save($data);
        if ($result) {
            return $result->{_MDATA_NID};
        } else {
            return 0;
        }
    }

    /**
     * updateRecords method
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();

        $code = $query->errorCode();

        if ($code == '00000') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * deleteRecords method to delete on passed conditions 
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        return $this->deleteAll($conditions);
    }
    
    /*
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    
    public function  getCount($conditions=[]){
       return $total =  $this->find()->where($conditions)->count();
      //  return $total =  $this->query()->find()->where($conditions)->count();
    }


}
