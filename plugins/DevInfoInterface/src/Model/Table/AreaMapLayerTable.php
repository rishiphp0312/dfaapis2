<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\AreaMapLayer;
use Cake\ORM\Table;

/**
 * Area Map Layer
 */
class AreaMapLayerTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('ut_area_map_layer');
        $this->primaryKey(_AREAMAPLAYER_LAYER_NID);
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
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);

        return $result;
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        $Area = $this->newEntity();

        $Area = $this->patchEntity($Area, $fieldsArray);
        $result = $this->save($Area);
        
        if ($result) {
            return $result->{_AREAMAPLAYER_LAYER_NID};
        } else {
            return 0;
        }
    }

    /**
     * insertOrUpdateBulkData method     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        // IF only one record being inserted/updated
        if(count($dataArray) == 1){
            return $this->insertData(reset($dataArray));
        }
        
        // Remove any Duplicate entry
        $dataArray = array_intersect_key($dataArray, array_unique(array_map('serialize', $dataArray)));
        
        $entities = $this->newEntities($dataArray);
        foreach ($entities as $entity) {
            if (!$entity->errors()) {
                $this->save($entity);
            }
        }
    }

    /**
     * updateRecords method     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $Area = $this->get($conditions);
        $Area = $this->patchEntity($Area, $fieldsArray);
        if ($this->save($Area)) {
            return 1;
        } else {
            return 0;
        }
    }

}
