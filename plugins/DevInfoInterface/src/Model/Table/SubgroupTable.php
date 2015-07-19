<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Subgroup;
use Cake\ORM\Table;

/**
 * SubgroupTable Model
 */
class SubgroupTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Subgroup_en');
        $this->primaryKey(_SUBGROUP_SUBGROUP_NID);
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
            $options['conditions'] = [_SUBGROUP_SUBGROUP_NID . ' IN' => $ids];

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
        return $data;
    }

    /**
     * getDataByParams method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = null, $debug = false) {

        $options = [];

        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if ($type == 'list' && empty($fields))
            $options['fields'] = array($fields[0], $fields[1]);

        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if (empty($type))
            $type = 'all';

        if ($type == 'list') {
            $options['keyField'] = $fields[0];
            $options['valueField'] = $fields[1];
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }
        
        if($debug == true){
            debug($query);exit;
        }
            
        $results = $query->hydrate(false)->all();
        
        $data = $results->toArray();

        return $data;
    }

    /**
     *  getDataBySubgroupName method
     *  @param $SubgroupName The value on which you will get details on basis of  the Subgroup. {DEFAULT : empty}
     *  @return  array
     */
    public function getDataBySubgroupName($SubgroupName) {
        $Subgroup_Namedetails = array();
        if (!empty($SubgroupName))
            $Subgroup_Namedetails = $this->find('all')->where([_SUBGROUP_SUBGROUP_NAME => $SubgroupName])->hydrate(false)->first();

        return $Subgroup_Namedetails;
    }

    /**
     * deleteByIds method
     * @param array $ids it can be one or more to delete the Subgroup  rows . {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {

        $result = $this->deleteAll([_SUBGROUP_SUBGROUP_NID . ' IN' => $ids]);
        if ($result > 0)
            return $result;
        return 0;
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
        else
            return 0;
    }

    /**
     * 
     * deleteBySubgroupName method       
     * @param  $Subgroupvalue Subgroup name   if exists  will be deleted. 
     * @return void
     *
     */
    public function deleteBySubgroupName($Subgroupvalue) {

        if (isset($Subgroupvalue) && !empty($Subgroupvalue)) {
            //deleteentity  checks whether record exists or not 
            $deleteentity = $this->find()->where([_SUBGROUP_SUBGROUP_NAME => $Subgroupvalue])->first();
            if (isset($deleteentity) && !empty($deleteentity)) {
                if ($result = $this->delete($deleteentity)) {
                    return 1;
                }
            }
        }
        return 0;
    }


    /**
     * 
     * insertData method       
     * @param  $fieldsArray contains  posted data 
     * @return void
     *
     */
    public function insertData($fieldsArray) {

        $Subgroup_Name = $fieldsArray[_SUBGROUP_SUBGROUP_NAME];

        $conditions = array();

        if (isset($fieldsArray[_SUBGROUP_SUBGROUP_NAME]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NAME]))
            $conditions[_SUBGROUP_SUBGROUP_NAME] = $fieldsArray[_SUBGROUP_SUBGROUP_NAME];

        if (isset($fieldsArray[_SUBGROUP_SUBGROUP_NID]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NID]))
            $conditions[_SUBGROUP_SUBGROUP_NID . ' !='] = $fieldsArray[_SUBGROUP_SUBGROUP_NID];

        if (isset($Subgroup_Name) && !empty($Subgroup_Name)) {

            $numrows = $this->find()->where($conditions)->count();
            if (isset($numrows) && $numrows == 0) {  // new record			   
                if (empty($fieldsArray[_SUBGROUP_SUBGROUP_ORDER])) {

                    $query = $this->find();
                    $results = $query->select(['max' => $query->func()->max(_SUBGROUP_SUBGROUP_ORDER)])->first();
                    $ordervalue = $results->max;
                    $maxordervalue = $ordervalue + 1;
                    $fieldsArray['Subgroup_Order'] = $maxordervalue;
                }

                $Subgroup = $this->newEntity();
                $Subgroup = $this->patchEntity($Subgroup, $fieldsArray);

                if ($this->save($Subgroup)) {
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
        $Subgroup = $this->get($conditions);

        //Update Entity Object with data
        $Subgroup = $this->patchEntity($Subgroup, $fieldsArray);

        //Update the Data
        if ($this->save($Subgroup)) {
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

    /**
     * getMax method
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return void
     */
    public function getMax($column = '', $conditions = []) {
        $alias = 'max';
        $query = $this->query()->select([$alias => 'MAX(' . $column . ')'])->where($conditions);
        $data = $query->hydrate(false)->first();

        return $data[$alias];
    }

}
