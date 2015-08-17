<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Language;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;

/**
 * Unit Model
 */
class LanguageTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        
        $this->table('UT_Language');
        $this->primaryKey(_LANGUAGE_LANGUAGE_NID);
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
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra)
    {
      
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;
        if(isset($extra['order']))
            $options['order'] = $extra['order'];

        if($type == 'list') $this->setListTypeKeyValuePairs($fields);
        //debug($options);exit;
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
        $result = $this->deleteAll($conditions);
        return $result;
    }

    /*  Function to check if table exists
    *
    */
    public function check_table_exists($table_name,$dbConnectionName = 'devInfoConnection') {
            $db = ConnectionManager::get($dbConnectionName);
            // Create a schema collection.
            $collection = $db->schemaCollection();
            // Get the table names
            $tablesList = $collection->listTables();
            //make all lowercase to avoid schema name case
            $tablesList = array_map('strtolower',$tablesList);           
            return in_array($table_name,$tablesList);
    }
    
    /* Function to execute query
    * Param 
    * sql_query
    * connectionName : connection name
    * 
    */
    public function executeTableCreateQuery($query,$dbConnectionName = 'devInfoConnection'){

            $connection = ConnectionManager::get('devInfoConnection');
            $results = $connection->execute($query);


    }

   

     /* Function to check if a language exists in language table
    * Param 
    * langCode
    *     * 
    */
    public function checkLanguageExistsByCode($langCode){

         $query =  $this->find()->where([_LANGUAGE_LANGUAGE_CODE => $langCode]);  
         $count = $query->count();
         return $count;

    }

   
  

}