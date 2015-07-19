<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Subgroup Component
 */
class SubgroupComponent extends Component {

    public $SubgroupTypeObj = NULL;
    public $SubgroupObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->SubgroupTypeObj = TableRegistry::get('DevInfoInterface.SubgroupType');
        $this->SubgroupObj = TableRegistry::get('DevInfoInterface.Subgroup');
    }

    /**
     * insertDataSubgroupType method is used to add new subgroup type      *
     * @param fieldsArray is passed as posted data  
     * @return void
     */
    public function insertUpdateDataSubgroupType($fieldsArray) {
        return $this->SubgroupTypeObj->insertData($fieldsArray);
    }

    /**
     * insertDataSubgroup method is used to add new subgroup  *
     * @param fieldsArray is passed as posted data  
     * @return void
     */
    public function insertUpdateDataSubgroup($fieldsArray) {
        return $this->SubgroupObj->insertData($fieldsArray);
    }

    /*
     * getDataByIdsSubgroupType method
     *
     * @param array $ids the ids can be multiple or single to get filtered records . {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param  $type the the type of list user needs it can be list or all . {DEFAULT : all}
     * @return void
     */

    public function getDataByIdsSubgroupType($ids, $fields = [], $type) {
        return $this->SubgroupTypeObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByIdsSubgroup method
     *
     * @param array $ids the ids can be multiple or single to get filtered records . {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param  $type the the type of list user needs it can be list  or all . {DEFAULT : all}
     * @return void
     */
    public function getDataByIdsSubgroup($ids, $fields = [], $type) {
        return $this->SubgroupObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParamsSubgroupType method
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParamsSubgroupType(array $fields, array $conditions) {
        return $this->SubgroupTypeObj->getDataByParams($fields, $conditions);
    }

    /**
     * getDataByParamsSubgroup method
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParamsSubgroup(array $fields, array $conditions) {
        return $this->SubgroupObj->getDataByParams($fields, $conditions);
    }

    /**
     *  getDataBySubgroupTypeName method
     *  @param $Subgroup_Type_Name The value on which you will get all details corresponding to the  Subgroup type name.
     *  @return  array
     */
    public function getDataBySubgroupTypeName($Subgroup_Type_Name) {
        return $this->SubgroupTypeObj->getDataBySubgroupTypeName($Subgroup_Type_Name);
    }

    /**
     *  getDataBySubgroupName method
     *  @param $Subgroup_Name The value on which you will get all details corresponding to the  Subgroup  name.
     *  @return  array
     */
    public function getDataBySubgroupName($Subgroup_Name) {
        return $this->SubgroupObj->getDataBySubgroupName($Subgroup_Name);
    }

    /**
     * deleteByIds method for SubgroupType
     *
     * @param  $ids the ids which needs to be deleted . {DEFAULT : null}
     * @return void
     */
    public function deleteByIdsSubgroupType($ids = null) {
        return $this->SubgroupTypeObj->deleteByIds($ids);
    }

    /**
     * deleteByIds method for Subgroup
     *
     * @param  $ids the ids which needs to be deleted . {DEFAULT : null}
     * @return void
     */
    public function deleteByIdsSubgroup($ids = null) {
        return $this->SubgroupObj->deleteByIds($ids);
    }

    /**
     * deleteByParams  method for SubgroupType
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParamsSubgroupType($conditions = []) {
        return $this->SubgroupTypeObj->deleteByParams($conditions);
    }

    /**
     * deleteByParams  method for Subgroup
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParamsSubgroup($conditions = []) {
        return $this->SubgroupObj->deleteByParams($conditions);
    }

    /**
     * @param $SubgroupTypevalue the SubgroupType name value which will be deleted .
     * @return void
     */
    public function deleteBySubgroupTypeName($SubgroupTypevalue) {
        return $this->SubgroupTypeObj->deleteBySubgroupTypeName($SubgroupTypevalue);
    }

    /**
     * @param $Subgroupvalue the Subgroup name  value which will be deleted .
     * @return void
     */
    public function deleteBySubgroupName($Subgroupvalue) {
        return $this->SubgroupObj->deleteBySubgroupName($Subgroupvalue);
    }

    /**
     * updateDataByParams method for SubgroupName
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParamsSubgroupName($fieldsArray = [], $conditions = []) {
        return $this->SubgroupObj->updateDataByParams($fieldsArray, $conditions);
    }

    /**
     * updateDataByParams method for  Subgroup Type Name
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParamsSubgroupTypeName($fieldsArray = [], $conditions = []) {
        return $this->SubgroupTypeObj->updateDataByParams($fieldsArray, $conditions);
    }

    /**
     * getDataByParams method
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all', $debug = false) {
        return $this->SubgroupObj->getDataByParams($fields, $conditions, $type, $debug);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->SubgroupObj->updateDataByParams($fieldsArray, $conditions);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->SubgroupObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * getMax method
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return void
     */
    public function getMax($column = '', $conditions = []) {
        return $this->SubgroupObj->getMax($column, $conditions);
    }

}
