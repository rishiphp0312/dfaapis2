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
        $this->primaryKey('IC_NId');
        $this->displayField('IC_Name'); //used for find('list')
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
     * getDataByIds method
     *
     * @param array $id The WHERE conditions for the Query. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, array $fields, $type = 'all' )
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;

        $options['conditions'] = [_IC_IC_NID.' IN'=>$ids];

        if($type == 'list') $this->setListTypeKeyValuePairs($fields);

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
    public function getDataByParams(array $fields, array $conditions, $type = 'all')
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;
        
        if($type == 'list') $this->setListTypeKeyValuePairs($fields);

        //$results = $this->find('list')->where($conditions);
        //print_r($results);exit;

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
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null)
    {
        /*
        //---- This can also be used but we don't want 2 steps ----//
        $entity = $this->find('all')->where(['Indicator_NId IN' => $ids]);
        $result = $this->delete($entity);
        */
        $result = $this->deleteAll([_IC_IC_NID.' IN' => $ids]);

        return $result;
    }

        
    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams(array $conditions)
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
        if ($this->save($IndicatorClassifications)) {
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
    public function insertBulkData($insertDataArray = [], $insertDataKeys = [])
    {
        //Create New Entities (multiple entities for multiple rows/records)
        //$entities = $this->newEntities($insertDataArray);
        
        $query = $this->query();
        
        /*
         * http://book.cakephp.org/3.0/en/orm/query-builder.html#inserting-data
         * http://blog.cnizz.com/2014/10/29/inserting-multiple-rows-with-cakephp-3/
         */
        foreach($insertDataArray as $insertData){
            $query->insert($insertDataKeys)->values($insertData); // person array contains name and title
        }
        
        return $query->execute();

    }
    

    /**
     * insertOrUpdateBulkData method
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
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
        //Initialize
        $query = $this->query();
        
        //Set
        $query->update()
            ->set($fieldsArray)
            ->where($conditions);
        
        //Execute
        $query->execute();
        
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
        
        /*if(array_key_exists(2, $fields)){
            $concat = $query->func()->concat([
                '(',
                //'CONVERT(varchar, '.$fields[0].')' => 'literal',
                $fields[0] => 'literal',
                ',\'',
                $fields[1] => 'literal',
                '\')'
            ]);
            $query->select(['concatinated' => $concat]);
        }*/
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();
        
        if(array_key_exists(2, $fields)){
            foreach($data as $key => &$value){
                $value['concatinated'] = '(' . $value[$fields[0]] . ',\'' . $value[$fields[1]] . '\')';
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
        return $this->autoGenerateNIdFromTable();
    }


}