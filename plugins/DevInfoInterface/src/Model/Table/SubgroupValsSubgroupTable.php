<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\SubgroupValsSubgroup;
use Cake\ORM\Table;

/**
 * SubgroupValsSubgroup Model
 */
class SubgroupValsSubgroupTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_Subgroup_Vals_Subgroup');
        $this->primaryKey(_SUBGROUP_VAL_SUBGROUP_VAL_NID);
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
            $options['conditions'] = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN' => $ids];

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
    public function getDataByParams(array $fields, array $conditions, $type = null, $extra = []) {

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
            if(array_key_exists(2, $fields)){
                $options['groupField'] = $fields[2];
            }
            
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }

        /*if(array_key_exists('group', $extra)){
            $query->group($extra['group']);
        }*/
        if(array_key_exists('order', $extra)){
            $query->order($extra['order']);
        }
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();
        
        if(array_key_exists('group', $extra)){
            $data = array_intersect_key($data, array_unique(array_map('serialize', $data)));
            foreach($data as $key => $value){
                $newData[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . '_CONCATED'][$value[$fields[0]]][] = $value[$fields[1]];
            }
            $data = [];
            foreach($newData[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . '_CONCATED'] as $key => &$value){
                sort($value);
                $value = implode(',', $value);
                $dataFields[$fields[0]] = $key;
                $dataFields[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . '_CONCATED'] = $value;
                $data[] = $dataFields;
            }
        }
        //debug($data);exit;
        return $data;
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
            $query->insert($insertDataKeys)->values($insertData); // array
        }
        
        return $query->execute();
    }

    /**
     * getConcatedFields method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedFields(array $fields, array $conditions, $type = null) {

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
            if(array_key_exists(2, $fields)){
                $options['groupField'] = $fields[2];
            }
            
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }

        /*$concat = $query->func()->concat([
            '(',
            _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID => 'literal',
            ',',
            SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID => 'literal',
            ')'
        ]);
        $query->select(['concatinated' => $concat]);*/
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();
        
        if(array_key_exists(2, $fields)){
            foreach($data as $key => &$value){
                $value['concatinated'] = '(' . $value[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID] . ',' . $value[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID] . ')';
            }
        }
        
        return $data;
    }

}
