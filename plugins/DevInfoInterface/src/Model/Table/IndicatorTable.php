<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Indicator;
use Cake\ORM\Table;

/**
 * Indicator Model
 */
class IndicatorTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Indicator_en');
        $this->primaryKey(_INDICATOR_INDICATOR_NID);
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
    public function getRecords(array $fields, array $conditions, $type = 'all',$extra=[])
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;
        
        if($type == 'list') $this->setListTypeKeyValuePairs($fields);

        // Find all the rows.
        // At this point the query has not run.
        $query = $this->find($type, $options);
		
		$order =[];
		if(isset($extra['order']) && !empty($extra['order'])){
			$order = $extra['order'];
		}else{
			$order =[_INDICATOR_INDICATOR_NID =>'ASC'];
		}
		

        // Calling execute will execute the query
        // and return the result set.
        $results = $query->order($order)->hydrate(false)->all();

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
    public function deleteRecords(array $conditions)
    {
        return $this->deleteAll($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
		$extra if extra is nid then returns nid else gives gid 
     */
    public function insertData($fieldsArray = [],$extra='')
    {
        //Create New Entity
        $Indicator = $this->newEntity();
        
        //Update New Entity Object with data
        $Indicator = $this->patchEntity($Indicator, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($Indicator);
        if ($result) {
            if(isset($extra) && $extra=='nid')
			return $result->{_INDICATOR_INDICATOR_NID};
		    else
			return $result->{_INDICATOR_INDICATOR_GID};
				
        } else {
            return 0;
        }        
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
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = [])
    {
        // IF only one record being inserted/updated
        if(count($dataArray) == 1){
            return $this->insertData(reset($dataArray));
        }
        
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
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = [])
    {
        $alias = 'maximum';
        //$query = $this->query()->select([$alias => 'MAX(' . $column . ')'])->where($conditions);
        $query = $this->query()->select([$alias => $column])->where($conditions)->order([_INDICATOR_INDICATOR_ORDER => 'DESC'])->limit(1);

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
        return $this->find('all', ['conditions' => ['Indicator_Name' => 'Indicator Testing 1']])->hydrate(false)->all();
    }
    
}