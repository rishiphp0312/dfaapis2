<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\IndicatorUnitSubgroup;
use Cake\ORM\Table;

/**
 * IndicatorUnitSubgroup Model
 */
class IndicatorUnitSubgroupTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Indicator_Unit_Subgroup');
        $this->primaryKey(_IUS_IUSNID);
        $this->displayField(_IUS_IUSNID); //used for find('list')
        $this->addBehavior('Timestamp');

        $this->belongsTo('Indicator', [
            'className' => 'DevInfoInterface.Indicator',
            'foreignKey' => _INDICATOR_INDICATOR_NID,
            'joinType' => 'INNER',
                //'conditions'=>array('Indicator_NId'),
        ]);
        $this->belongsTo('Unit', [
            'className' => 'DevInfoInterface.Unit',
            'foreignKey' => _UNIT_UNIT_NID,
            'joinType' => 'INNER',
                //'conditions'=>array(),
        ]);
        $this->belongsTo('SubgroupVals', [
            'className' => 'DevInfoInterface.SubgroupVals',
            'foreignKey' => _SUBGROUP_VAL_SUBGROUP_VAL_NID,
            'joinType' => 'INNER',
                //'conditions'=>array(),
        ]);
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
     * getDataByIds method
     *
     * @param array $id The WHERE conditions for the Query. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, array $fields, $type = 'all') {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;

        $options['conditions'] = [_IUS_IUSNID . ' IN' => $ids];

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        // Find all the rows.
        // At this point the query has not run.
        $query = $this->find($type, $options);

        // Calling execute will execute the query
        // and return the result set.
        $results = $query->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

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

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $results = $this->find('list')->where($conditions);

        // Find all the rows.
        // At this point the query has not run.
        $query = $this->find($type, $options);

        // Calling execute will execute the query
        // and return the result set.
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

        return $data;
    }

    /**
     * getGroupedList method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getGroupedList(array $fields, array $conditions) {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        $query = $this->find('list', [
            'keyField' => $fields[0],
            'valueField' => $fields[1],
            'groupField' => $fields[2],
            'conditions' => $conditions
        ]);

        // Once we have a result set we can get all the rows
        $data = $query->toArray();

        return $data;
    }

    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        /*
          //---- This can also be used but we don't want 2 steps ----//
          $entity = $this->find('all')->where(['Indicator_NId IN' => $ids]);
          $result = $this->delete($entity);
         */
        $result = $this->deleteAll([_IUS_IUSNID . ' IN' => $ids]);

        return $result;
    }

    /**
     * deleteByParams method
     *
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
        $IndicatorUnitSubgroup = $this->newEntity();

        //Update New Entity Object with data
        $IndicatorUnitSubgroup = $this->patchEntity($IndicatorUnitSubgroup, $fieldsArray);
        
        //Create new row and Save the Data
        if ($this->save($IndicatorUnitSubgroup)) {
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

      
        foreach ($insertDataArray as $insertData) {
            $query->insert($insertDataKeys)->values($insertData); // person array contains name and title
        }

        return $query->execute();
    }

    /**
     * bulkInsert method
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function bulkInsert($dataArray = []) {
        
        if(count($dataArray) == 1){
            return $this->insertData(reset($dataArray));
        }
        
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
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        //Get Entities based on Coditions
        $IndicatorUnitSubgroup = $this->get($conditions);

        //Update Entity Object with data
        $IndicatorUnitSubgroup = $this->patchEntity($IndicatorUnitSubgroup, $fieldsArray);

        //Update the Data
        if ($this->save($IndicatorUnitSubgroup)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * autoGenerateNIdFromTable method
     *
     * @param array $connection Database to use. {DEFAULT : empty}
     * @param array $tableName table to query. {DEFAULT : empty}
     * @param array $NIdColumnName Column used to generate NId. {DEFAULT : empty}
     * @return void
     */
    public function autoGenerateNIdFromTable($connection = null) {

        $maxNId = $this->find()->select(_IUS_IUSNID)->max(_IUS_IUSNID);
        return $maxNId->{_IUS_IUSNID};
    }

    /**
     * getConcatedFields method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedIus(array $fields, array $conditions, $type = null) {

        $options = [];
		
        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if (empty($type))
            $type = 'all';

        $query = $this->find($type, $options);    
        
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();
        
        foreach ($data as $key => &$value) {
            $value['concatinated'] = '(' . $value[_IUS_INDICATOR_NID] . ',' . $value[_IUS_UNIT_NID] . ',' . $value[_IUS_SUBGROUP_VAL_NID] . ',\'' . $value[_IUS_SUBGROUP_NIDS] . '\')';
        }
        
        return $data;
    }

    /**
     * getAllIUConcatinated method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function getAllIUConcatinated($fields = [], $conditions = [], $extra = []) {
        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if (!isset($extra['type']))
            $type = 'all';
        else
            $type = $extra['type'];

        if (isset($extra['group'])) {
            $query = $this->find('all', $options)->group($fields);    
        }
        else {
            $query = $this->find('all', $options);    
        }
        

        /* $concat = $query->func()->concat([
          '(',
          _IUS_INDICATOR_NID => 'literal',
          ',',
          _IUS_UNIT_NID => 'literal',
          ')'
          ]);
          $query->select(['concatinated' => $concat]); */

        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        foreach ($data as $key => &$value) {
            $value['concatinated'] = '(' . $value[_IUS_INDICATOR_NID] . ',' . $value[_IUS_UNIT_NID] . ')';
        }

        return $data;
    }
    
    /*
     * get all ius details or iu details on basis of ind gid,unit gid and subgrp gid 
     * @iGid indicator gid 
     * @uGid  unit gid 
     * @sGid subgroup val gid
     * return the iusnid details with ind,unit and subgrp details .	 
     */
    public function getIusNidsDetails($iGid = '', $uGid = '', $sGid = '') {
     
        if ($sGid != '')
            $data=  $this->find()->where(['Indicator.'._INDICATOR_INDICATOR_GID => $iGid, 'Unit.'._UNIT_UNIT_GID => $uGid, 'SubgroupVals.'._SUBGROUP_VAL_SUBGROUP_VAL_GID => $sGid])->contain(['Indicator', 'Unit', 'SubgroupVals'], true)->hydrate(false)->all()->toArray();
        else
            $data= $this->find()->where(['Indicator.'._INDICATOR_INDICATOR_GID  => $iGid, 'Unit.'._UNIT_UNIT_GID => $uGid])->contain(['Indicator', 'Unit', 'SubgroupVals'], true)->hydrate(false)->all()->toArray();
            return $data;
    }
	
	/*
     * get all indicator details 
     * @iusnids ius nids 
     * return indicator details on passed iusnids 
     */
    public function getIndicatorDetails($iusnids = []) {
            return $data = $this->find()->where([_IUS_IUSNID .' IN ' => $iusnids])->contain(['Indicator'], true)->hydrate(false)->all()->toArray();
        
    }

    /**
     * testCasesFromTable method
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
	 
    public function testCasesFromTable($params = []) {
        
        //return $this->autoGenerateNIdFromTable();
        $query = $this->find()->where(['Indicator_NId' => 110, 'Unit_NId' => 1, 'Subgroup_Val_NId' => 53, 'Subgroup_NIds' => '16,39']);
        //$query = $this->find()->where(['IUSNId' => 32940]);
        $data = $query->hydrate(false)->all()->toArray(); 
        debug($query);exit;
        //return $this->query('SELECT IndicatorUnitSubgroup.IUSNId AS [IndicatorUnitSubgroup__IUSNId], IndicatorUnitSubgroup.Indicator_NId AS [IndicatorUnitSubgroup__Indicator_NId], IndicatorUnitSubgroup.Unit_NId AS [IndicatorUnitSubgroup__Unit_NId], IndicatorUnitSubgroup.Subgroup_Val_NId AS [IndicatorUnitSubgroup__Subgroup_Val_NId], IndicatorUnitSubgroup.Subgroup_Nids AS [IndicatorUnitSubgroup__Subgroup_Nids] FROM UT_Indicator_Unit_Subgroup IndicatorUnitSubgroup WHERE ((Indicator_NId = 110 AND Unit_NId = 1 AND Subgroup_Val_NId = 53 AND Subgroup_Nids = "16,39")')->execute();
        //return $this->query('INSERT INTO UT_Indicator_Unit_Subgroup SET Indicator_NId = 110, Unit_NId = 1, Subgroup_Val_NId = 53, Subgroup_Nids = "16,39"')->execute();
        //return $data;
        return $this->insertData(['Indicator_NId' => 110, 'Unit_NId' => 1, 'Subgroup_Val_NId' => 53, 'Subgroup_Nids' => '16,39']);
    }

}
