<?php

namespace App\Model\Table;

use App\Model\Entity\MSystemConfirguration;
use Cake\ORM\Table;

/**
 * MSystemConfirgurationsTable Model
 *
 */
class MSystemConfirgurationsTable extends Table {

    /**
     * Initialize method     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('m_system_confirgurations');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * Set key/values for 'list' query type
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]); // Key
        $this->displayField($fields[1]); // Value
    }

    public function findByKey($key = '') {
        $config_value = false;
        $options = [];

        if (isset($key) && !empty($key)) {
            $options['conditions'] = array('key_name' => $key);
        }
        if ($key != '') {
            $MSystemConfirgurations = $this->find('all', $options);
            $config_value = $MSystemConfirgurations->hydrate(false)->first();
            $config_value = !empty($config_value) ? $config_value['key_value'] : false;
        }

        return $config_value;
    }

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
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

        $order = [];
        if (isset($extra['order']) && !empty($extra['order'])) {
            $order = $extra['order'];
        } else {
            $order = [_SYSCONFIG_ID => 'ASC'];
        }
        
        $results = $query->order($order)->hydrate(false)->all();
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
    public function insertData($fieldsArray = [], $extra = '') {
        //Create New Entity
        $sysConfig = $this->newEntity();

        //Update New Entity Object with data
        $sysConfig = $this->patchEntity($sysConfig, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($sysConfig);
        if ($result) {
            return $result->{_SYSCONFIG_ID};
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
    public function insertOrUpdateBulkData($dataArray = []) {
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
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
        //debug($query);
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
        } else {
            return 0;
        }
    }

}
