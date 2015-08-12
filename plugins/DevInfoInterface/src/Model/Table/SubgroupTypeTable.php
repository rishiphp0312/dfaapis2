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
    public function getRecords(array $fields, array $conditions, $type = null) {

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
			$options['conditions'] = $conditions;
            $query = $this->find($type, $options);
        }

        $data = $query->order([_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => 'ASC']);
        //pr($data);
    
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

        return $data;
    }
        
    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords(array $conditions) {
        return $this->deleteAll($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
   
	 public function insertData($fieldsArray = [],$extra='')
    {
        //Create New Entity
        $Subgroup_Type = $this->newEntity();
        
        //Update New Entity Object with data
        $Subgroup_Type = $this->patchEntity($Subgroup_Type, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($Subgroup_Type);
        if ($result) {
			return $result->{_SUBGROUPTYPE_SUBGROUP_TYPE_NID};	   
				
        } else {
            return 0;
        }        
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        /*
        // IF only one record being inserted/updated
        if(count($dataArray) == 1){
            return $this->insertData(reset($dataArray));
        }*/
        
        // Remove any Duplicate entry
        $dataArray = array_intersect_key($dataArray, array_unique(array_map('serialize', $dataArray)));
        
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
	
	
	 public function getMax($column = '', $conditions = [])
    {
        $alias = 'maximum';
        //$query = $this->query()->select([$alias => 'MAX(' . $column . ')'])->where($conditions);
        $query = $this->query()->select([$alias => $column])->where($conditions)->order([_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => 'DESC'])->limit(1);

        $data = $query->hydrate(false)->first();
        if(!empty($data)){
            return $data[$alias];
        }else{
            return 0;
        }
    }

    /**
     * - For DEVELOPMENT purpose only
     * Test method to do anything based on this model (Run RAW queries or complex queries)
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        return $this->find('all', ['fields' => ['Subgroup_Type_Name'], 'conditions' => []])->hydrate(false)->all();
        //return $this->deleteRecords(['Subgroup_Type_Name IN' => ['Import_Status', 'Description']]);
    }

}
