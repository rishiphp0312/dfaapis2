<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Area;
use Cake\ORM\Table;
use Cake\Network\Session;

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
    public function initialize(array $config) 
    {
        $session = new Session();
        $defaultLangcode = $session->read('defaultLangcode');
        $this->table('UT_Area_' . $defaultLangcode);
        $this->primaryKey(_AREA_AREA_NID);
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
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $query = $this->find($type, $options);
        
        if(isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);exit;
        }
        
        // and return the result set.
        if(isset($extra['first']) && $extra['first'] == true) {
            $results = $query->first();
        } else {
            $results = $query->hydrate(false)->all();            
        }
        
        if(!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
    
        return $results;
    }

    /**
     * deleteRecords method
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
        //Create New Entity
        $Area = $this->newEntity();

        //Update New Entity Object with data
        $Area = $this->patchEntity($Area, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($Area);
        
        if ($result) {
            return $result->{_AREA_AREA_NID};
        } else {
            return 0;
        }
        
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData_old($fieldsArray = []) {


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
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
        //$query->update()->set($fieldsArray)->where($conditions); // Set
        //  $query->execute(); // Execute
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
        } else {
            return 0;
        }
    }
    
     /*
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    
    public function  getCount($conditions=[]){
        return  $total =  $this->find()->where($conditions)->count();
       
    }
    
}
