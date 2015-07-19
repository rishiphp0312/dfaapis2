<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\SubgroupVal;
use Cake\ORM\Table;

/**
 * SubgroupValTable Model
 */
class SubgroupValTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('Ut_subgroup_vals_en');
        $this->primaryKey(_SUBGROUP_SUBGROUP_VAL_NID);
        $this->addBehavior('Timestamp');
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
            $options['conditions'] = [_SUBGROUP_SUBGROUP_VAL_NID . ' IN' => $ids];

        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

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
    public function getDataByParams(array $fields, array $conditions) {

        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;

        if (!empty($conditions))
            $options['conditions'] = $conditions;

        $query = $this->find('all', $options);
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
    public function getDataBySubgroupVal($SubgroupVal) {
        $Subgroup_Namedetails = array();

        if (!empty($SubgroupVal))
            $Subgroup_Namedetails = $this->find('all')->where([_SUBGROUP_SUBGROUP_VAL => $SubgroupVal])->hydrate(false)->first();

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
     * insertData  method 
      @return void
     */
    public function insertData($fieldsArray) {

        $conditions = array();

        if (isset($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]) && !empty($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME]))
            $conditions[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];

        if (isset($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]) && !empty($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]))
            $conditions[_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' !='] = $fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];

        $Subgroup_Type_Name = $fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
        if (isset($Subgroup_Type_Name) && !empty($Subgroup_Type_Name)) {

            //numrows if numrows >0 then record already exists else insert new row
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

                    if (isset($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]) && !empty($fieldsArray[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]))
                        return 1;
                    else
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
        $Subgroup_Val = $this->get($conditions);

        //Update Entity Object with data
        $Subgroup_Val = $this->patchEntity($Subgroup_Val, $fieldsArray);

        //Update the Data
        if ($this->save($Subgroup_Val)) {
            return 1;
        } else {
            return 0;
        }
    }

}
