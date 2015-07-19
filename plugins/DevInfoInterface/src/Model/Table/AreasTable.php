<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Area;
use Cake\ORM\Table;

/**
 * Area Model
 */
class AreasTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Area_en');
        $this->primaryKey('Area_NId');
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
     * getDataByIds method
     *
     * @param array $ids will compare area id. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, array $fields, $type = 'all') {
       
        $options = [];
        $options['conditions'] = [_AREA_AREA_NID . ' IN' => $ids];        
        if (!empty($fields))
            $options['fields'] = $fields;
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
     
        $data = $this->find($type, $options)->all()->toArray();        
        return $data;
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
		//pr($options);die;
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $data = $this->find($type, $options)->hydrate(false)->all()->toArray();
    
        return $data;
    }

    /**
     * deleteByIds method    
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {

        $result = $this->deleteAll([_AREA_AREA_NID . ' IN' => $ids]);

        return $result;
    }

    /**
     * deleteByParams method
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams(array $conditions) {
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


        //Create New Entity		
        $conditions = array();

        //if(isset($fieldsArray[_AREALEVEL_AREA_LEVEL]) && !empty($fieldsArray[_AREALEVEL_AREA_LEVEL]))            
        //$conditions[_AREALEVEL_AREA_LEVEL] = $fieldsArray[_AREALEVEL_AREA_LEVEL];	

        if (isset($fieldsArray[_AREA_AREA_ID]) && !empty($fieldsArray[_AREA_AREA_ID]))
            $conditions[_AREA_AREA_ID] = $fieldsArray[_AREA_AREA_ID];

        if (isset($fieldsArray[_AREA_AREA_NID]) && !empty($fieldsArray[_AREA_AREA_NID]))
            $conditions[_AREA_AREA_NID . ' !='] = $fieldsArray[_AREA_AREA_NID];

        $Area_Id = $fieldsArray[_AREA_AREA_ID];
        if (isset($Area_Id) && !empty($Area_Id)) {

            //numrows if numrows >0 then record already exists else insert new row
            $numrows = $this->find()->where($conditions)->count();

            if (isset($numrows) && $numrows == 0) {  // new record
                //Create New Entity
                $Area = $this->newEntity();

                //Update New Entity Object with data
                $Area = $this->patchEntity($Area, $fieldsArray);

                //Create new row and Save the Data
                if ($this->save($Area)) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
	 
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {        
        $query = $this->query();
        foreach ($insertDataArray as $insertData) {
            $query->insert($insertDataKeys)->values($insertData); // person array contains name and title
        }

        return $query->execute();
    }

    /**
     * insertOrUpdateBulkData method 
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        $entities = $this->newEntities($dataArray);
        foreach ($entities as $entity) {
            if (!$entity->errors()) {
                $this->save($entity);
            }
        }
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        $Area = $this->get($conditions);
        $Area = $this->patchEntity($Area, $fieldsArray);
        if ($this->save($Area)) {
            return 1;
        } else {
            return 0;
        }
    }

}
