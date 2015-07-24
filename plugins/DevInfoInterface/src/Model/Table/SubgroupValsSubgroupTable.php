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
        $this->primaryKey(_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID);
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
     * Set key/values for 'list' query type
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields)
    {
        $this->primaryKey($fields[0]); // Key
        $this->displayField($fields[1]); // Value
    }

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = null, $extra = []) {

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
        
        return $data;
    }
        
    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords(array $conditions)
    {
        return $this->deleteAll($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [])
    {
        //Create New Entity
        $subgroupValSubgroup = $this->newEntity();
        
        //Update New Entity Object with data
        $subgroupValSubgroup = $this->patchEntity($subgroupValSubgroup, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($subgroupValSubgroup);
        if ($result) {
            return $result->{_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID};
        } else {
            return 0;
        }        
    }

    /**
     * Insert multiple rows at once (runs single query for multiple records)
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
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {        
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
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query(); // Initialize
        $query->update()->set($fieldsArray)->where($conditions); // Set
        $query->execute(); // Execute
    }

    /**
     * getConcatedFields method    
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return fetched data with concatinated fields
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
        
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();
        
        if(array_key_exists(2, $fields)){
            foreach($data as $key => &$value){
                $value['concatinated'] = '(' . $value[_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID] . ',' . $value[SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID] . ')';
            }
        }
        
        return $data;
    }

    /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = []) {

        $alias = 'max';
        $query = $this->query()->select([$alias => 'MAX(' . $column . ')'])->where($conditions);
        $data = $query->hydrate(false)->first();

        return $data[$alias];
    }

}
