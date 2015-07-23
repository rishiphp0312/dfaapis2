<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Unit;
use Cake\ORM\Table;

/**
 * Unit Model
 */
class UnitTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Unit_en');
        $this->primaryKey(_UNIT_UNIT_NID);
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
    public function getRecords(array $fields, array $conditions, $type = 'all')
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;

        if($type == 'list') $this->setListTypeKeyValuePairs($fields);
        
        $query = $this->find($type, $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
    }
        
    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteByParams(array $conditions)
    {
        $result = $this->deleteAll($conditions);
        return $result;
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
        $Unit = $this->newEntity();

        //Update New Entity Object with data
        $Unit = $this->patchEntity($Unit, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($Unit);
        if ($result) {
            return $result->{_UNIT_UNIT_NID};
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
    public function insertBulkData($insertDataArray = [], $insertDataKeys = [])
    {   
        //Prevent duplicate records from inserting
        $insertDataArray = array_intersect_key($insertDataArray, array_unique(array_map('serialize', $insertDataArray)));
        $query = $this->query();
        
        foreach($insertDataArray as $insertData){
            $query->insert($insertDataKeys)->values($insertData); // person array contains name and title
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
     * @return integer 1 if saved else 0
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        $query = $this->query(); // Initialize
        $query->update()->set($fieldsArray)->where($conditions); // Set
        $query->execute(); // Execute
    }

}