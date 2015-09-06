<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\DbMetadata;
use Cake\ORM\Table;
use Cake\Network\Session;

/**
 * Metadata category Model
 */
class DbMetadataTable extends Table
{

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
        $this->table('UT_DBMetadata_' . $defaultLangcode);
        $this->primaryKey('DBMtd_NId');
        $this->addBehavior('Timestamp');
    }

    /*
     * @Cakephp3: defaultConnectionName method
     * @Defines which DB connection to use from multiple database connections
     * @Connection Created in: CommonInterfaceComponent
     */
    public static function defaultConnectionName()
    {
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
    public function deleteRecords(array $conditions)
    {
        return $this->deleteAll($conditions);
    }

   /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = [])
    {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
       
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
        } else {
            return 0;
        }
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
        $dbmetadata = $this->newEntity();
        
        //Update New Entity Object with data
        $dbmetadata = $this->patchEntity($dbmetadata, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($dbmetadata);
        if ($result) {
            return $result->{_DBMETA_NID};
        } else {
            return 0;
        }        
    }

    
}