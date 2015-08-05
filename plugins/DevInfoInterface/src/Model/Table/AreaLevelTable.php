<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\AreaLevel;
use Cake\ORM\Table;

/**
 * Area Model
 */
class AreaLevelTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Area_Level_en');
        $this->primaryKey('Level_NId');
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
        $conditions = array();
        //if(isset($fieldsArray[_AREALEVEL_AREA_LEVEL]) && !empty($fieldsArray[_AREALEVEL_AREA_LEVEL]))            
        //$conditions[_AREALEVEL_AREA_LEVEL] = $fieldsArray[_AREALEVEL_AREA_LEVEL];	
	
        if (isset($fieldsArray[_AREALEVEL_AREA_LEVEL]) && !empty($fieldsArray[_AREALEVEL_AREA_LEVEL]))
            $conditions[_AREALEVEL_AREA_LEVEL] = $fieldsArray[_AREALEVEL_AREA_LEVEL];

        if (isset($fieldsArray[_AREALEVEL_LEVEL_NID]) && !empty($fieldsArray[_AREALEVEL_LEVEL_NID]))
            $conditions[_AREALEVEL_LEVEL_NID . ' !='] = $fieldsArray[_AREALEVEL_LEVEL_NID];

        $Area_Level = $fieldsArray[_AREALEVEL_AREA_LEVEL];
        if (isset($Area_Level) && !empty($Area_Level)) {
			
            //numrows if numrows >0 then record already exists else insert new row
            $numrows = $this->find()->where($conditions)->count();

            if (isset($numrows) && $numrows == 0) {  // new record
                $Area = $this->newEntity();

                $Area = $this->patchEntity($Area, $fieldsArray);
                if ($this->save($Area)) {
                    return $Area->id;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
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
