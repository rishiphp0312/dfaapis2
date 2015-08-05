<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\IcIus;
use Cake\ORM\Table;


/**
 * IcIus Model
 */
class IcIusTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('ut_IC_IUS');
        $this->primaryKey(_ICIUS_IC_IUSNID);
        $this->displayField(_ICIUS_IC_IUSNID); //used for find('list')
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
    public function getRecords(array $fields, array $conditions, $type = 'all')
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;
       
        if($type == 'list') $this->setListTypeKeyValuePairs($fields);

        $results = $this->find('list')->where($conditions);
       
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
        $IcIus = $this->newEntity();
        
        //Update New Entity Object with data
        $IcIus = $this->patchEntity($IcIus, $fieldsArray);
        
        //Create new row and Save the Data
        if ($this->save($IcIus)) {
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
     * updateRecords method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = [])
    {
        //Get Entities based on Coditions
        $IcIus = $this->get($conditions);
        
        //Update Entity Object with data
        $IcIus = $this->patchEntity($IcIus, $fieldsArray);
        
        //Update the Data
        if ($this->save($IcIus)) {
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

            $maxNId = $this->find()->select(_ICIUS_IC_IUSNID)->max(_ICIUS_IC_IUSNID);
            return $maxNId->{_ICIUS_IC_IUSNID};

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

        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if (empty($type))
            $type = 'all';

        $query = $this->find($type, $options);
        /*$concat = $query->func()->concat([
                    '(',
                    _ICIUS_IC_NID => 'literal',
                    ',',
                    _ICIUS_IUSNID => 'literal',
                    ')'
                ]);
        $query->select(['concatinated' => $concat]);*/
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

        if(array_key_exists(2, $fields)){
            foreach($data as $key => &$value){
                $value['concatinated'] = '(' . $value[_ICIUS_IC_NID] . ',' . $value[_ICIUS_IUSNID] . ')';
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
        //return $results = $this->find()->count();
        return $results = $this->query('SELECT IcIus.IC_NId AS [IcIus__IC_NId], IcIus.IUSNId AS [IcIus__IUSNId] FROM ut_IC_IUS IcIus WHERE IC_NId in (426)')->hydrate(false)->limit(50)->count();
        //return $this->find('all', ['conditions' => [_ICIUS_IC_IUSNID => 33602]])->hydrate(false)->all();
    }


}