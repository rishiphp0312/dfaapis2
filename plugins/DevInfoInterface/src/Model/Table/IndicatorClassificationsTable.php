<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\IndicatorClassifications;
use Cake\ORM\Table;


/**
 * IndicatorClassifications Model
 */
class IndicatorClassificationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Indicator_Classifications_en');
        $this->primaryKey(_IC_IC_NID);
        $this->displayField(_IC_IC_NAME); //used for find('list')
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
     * setListTypeKeyValuePairs method
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields)
    {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * getRecords method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = [])
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
        
        if(isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);exit;
        }
        
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
    public function getGroupedList(array $fields, array $conditions)
    {
        $options = [];
        
        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
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
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords(array $conditions)
    {
        $result = $this->deleteAll($conditions);

        return $result;
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = [])
    {
        //Create New Entity
        $IndicatorClassifications = $this->newEntity();
        
        //Update New Entity Object with data
        $IndicatorClassifications = $this->patchEntity($IndicatorClassifications, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($IndicatorClassifications);
        if ($result) {
            return $result->{_IC_IC_NID};
        } else {
            return 0;
        }        

    }

    /**
     * insertOrUpdateBulkData method
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
     * updateRecords method
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
     * autoGenerateNIdFromTable method
     *
     * @param array $connection Database to use. {DEFAULT : empty}
     * @param array $tableName table to query. {DEFAULT : empty}
     * @param array $NIdColumnName Column used to generate NId. {DEFAULT : empty}
     * @return void
     */
    public function autoGenerateNIdFromTable($connection = null){

        $maxNId = $this->find()->select(_IC_IC_NID)->max(_IC_IC_NID);
        return $maxNId->{_IC_IC_NID};

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
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();
        
        if(array_key_exists(2, $fields)){
            foreach($data as $key => &$value){
                $value['concatinated'] = '(' . $value[$fields[0]] . ',\'' . $value[$fields[1]] . '\',\'' . $value[$fields[2]] . '\')';
            }
        }
        
        return $data;
    }


    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        //return $this->autoGenerateNIdFromTable();
        //return $this->find('all', ['fields' => [], 'conditions' => [_IC_IC_NAME . ' IN' => ['Demography2']]])->hydrate(false)->all();
        return $this->find('all', ['fields' => [], 'conditions' => [_IC_IC_NID => 1]])->hydrate(false)->all();
    }


}