<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\SubgroupType;
use Cake\ORM\Table;

/**
 * SubgroupTypeTable Model
 */
class SubgroupTypeTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Subgroup_Type_en');
        $this->primaryKey(_SUBGROUPTYPE_SUBGROUP_TYPE_NID);
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
     * getDataByIds method
     * @param array $id The WHERE conditions with ids only for the Query. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, array $fields, $type) {

        $options = [];

        if (isset($ids) && !empty($ids))
            $options['conditions'] = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN' => $ids];

        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if ($type == 'list' && empty($fields))
            $options['fields'] = array(_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_NAME);

        if (empty($type))
            $type = 'all';

        if ($type == 'list') {
            $options['keyField'] = $fields[0];
            $options['valueField'] = $fields[1];
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }

        $results = $query->hydrate(false)->all();
        $data = $results->toArray();
        // Once we have a result set we can get all the rows		
        return $data;
    }

    /**
     * getDataByParams method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = null) {

        $options = [];

        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if ($type == 'list' && empty($fields))
            $options['fields'] = array($fields[0], $fields[1]);

        if (empty($type))
            $type = 'all';

        if ($type == 'list') {
            $options['keyField'] = $fields[0];
            $options['valueField'] = $fields[1];
            $options['conditions'] = $conditions;
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }

        $query->order([_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => 'ASC']);
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

        return $data;
    }

    /**
     *  getDataBySubgroupTypeName method
     *  @param $Subgroup_Type_Name The value on which you will get all details corresponding to the  Subgroup type name. {DEFAULT : empty}
     *  @return  array
     */
    public function getDataBySubgroupTypeName($Subgroup_Type_Name) {
        $Subgroup_Namedetails = array();

        if (!empty($Subgroup_Type_Name))
            $Subgroup_Namedetails = $this->find('all')->where([_SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $Subgroup_Type_Name])->hydrate(false)->first();

        return $Subgroup_Namedetails;
    }

    /**
     * 
     * deletesingleSubgroupType method       
     * @param  $Subgroup_Type_Name contains  Subgroup type  name  which will be deleted from database if exists 
     * @return void
     *
     */
    public function deletesingleSubgroupType($Subgroup_Type_Name) {

        if (isset($Subgroup_Type_Name) && !empty($Subgroup_Type_Name)) {

            //deleteentity  checks whether record exists or not 
            $deleteentity = $this->find()->where([_SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $Subgroup_Type_Name])->first();
            if (isset($deleteentity) && !empty($deleteentity)) {

                if ($result = $this->delete($deleteentity)) {
                    return 1;
                }
            }
        }
        return 0;
    }

// end of function 

    /**
     * deleteByIds method
     * @param array $ids it can be one or more to delete the Subgroup  rows . {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {

        $result = $this->deleteAll([_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN' => $ids]);
        return $result;
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions on the basis of which record will be deleted . 
     * @return void
     */
    public function deleteByParams(array $conditions) {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        return 0;
    }

    /**
     * 
     * deleteBySubgroupTypeName method       
     * @param  $SubgroupTypevalue Subgrouptype  name   if exists  will be deleted. 
     * @return void
     *
     */
    public function deleteBySubgroupTypeName($SubgroupTypevalue) {

        if (isset($SubgroupTypevalue) && !empty($SubgroupTypevalue)) {

            //deleteentity  checks whether record exists or not 
            $deleteentity = $this->find()->where([_SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $SubgroupTypevalue])->first();
            if (isset($deleteentity) && !empty($deleteentity)) {

                if ($result = $this->delete($deleteentity)) {
                    return 1;
                }
            }
        }
        return 0;
    }

// end of function 

    /**
     * insertData  method 
      @return void
     */
    public function insertData($fieldsArray) {

        $conditions = array();

        if (isset($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]) && !empty($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]))
            $conditions[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];

        if (isset($fieldsArray[_SUBGROUP_SUBGROUP_NID]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NID]))
            $conditions[_SUBGROUP_SUBGROUP_NID . ' !='] = $fieldsArray[_SUBGROUP_SUBGROUP_NID];

        $Subgroup_Type_Name = $fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
        if (isset($Subgroup_Type_Name) && !empty($Subgroup_Type_Name)) {

            $numrows = $this->find()->where($conditions)->count();

            if (isset($numrows) && $numrows == 0) {  // new record
                if (empty($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER])) {

                    $query = $this->find();
                    $results = $query->select(['max' => $query->func()->max(_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER)])->first();
                    $ordervalue = $results->max;
                    $maxordervalue = $ordervalue + 1;
                    $fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER] = $maxordervalue;
                }

                //Create New Entity
                $Subgroup_Type = $this->newEntity();

                //Update New Entity Object with data
                $Subgroup_Type = $this->patchEntity($Subgroup_Type, $fieldsArray);
                if ($this->save($Subgroup_Type)) {
                    return 1;
                }
            }
        }
        return 0;
    }

// end of function 

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        //Get Entities based on Coditions
        $Subgroup_Type = $this->get($conditions);

        //Update Entity Object with data
        $Subgroup_Type = $this->patchEntity($Subgroup_Type, $fieldsArray);

        //Update the Data
        if ($this->save($Subgroup_Type)) {
            return 1;
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
        //Create New Entities (multiple entities for multiple rows/records)
        //$entities = $this->newEntities($insertDataArray);

        $query = $this->query();

        /*
         * http://book.cakephp.org/3.0/en/orm/query-builder.html#inserting-data
         * http://blog.cnizz.com/2014/10/29/inserting-multiple-rows-with-cakephp-3/
         */
        foreach ($insertDataArray as $insertData) {
            $query->insert($insertDataKeys)->values($insertData); // person array contains name and title
        }

        return $query->execute();
    }

}
