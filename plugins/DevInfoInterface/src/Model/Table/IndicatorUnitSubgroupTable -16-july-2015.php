<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\IndicatorUnitSubgroup;
use Cake\ORM\Table;


/**
 * IndicatorUnitSubgroup Model
 */
class IndicatorUnitSubgroupTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Indicator_Unit_Subgroup');
        $this->primaryKey(_IUS_IUSNID);
        $this->displayField(_IUS_IUSNID); //used for find('list')
        $this->addBehavior('Timestamp');
		
		$this->belongsTo('Indicator', [
            'className' => 'DevInfoInterface.Indicator',
            'foreignKey' => 'Indicator_NId',
			'joinType' => 'INNER',
			//'conditions'=>array('Indicator_NId'),
        ]);
		$this->belongsTo('Unit', [
            'className' => 'DevInfoInterface.Unit',
            'foreignKey' => 'Unit_NId',
			'joinType' => 'INNER',
			//'conditions'=>array(),
        ]);
		$this->belongsTo('SubgroupVals', [
            'className' => 'DevInfoInterface.SubgroupVals',
            'foreignKey' => 'Subgroup_Val_NId',
			'joinType' => 'INNER',
			//'conditions'=>array(),
        ]);
	
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

        $options['conditions'] = [_IUS_IUSNID.' IN'=>$ids];

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

        $results = $this->find('list')->where($conditions);
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
        $result = $this->deleteAll([_IUS_IUSNID.' IN' => $ids]);

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
        $IndicatorUnitSubgroup = $this->newEntity();
        
        //Update New Entity Object with data
        $IndicatorUnitSubgroup = $this->patchEntity($IndicatorUnitSubgroup, $fieldsArray);
        
        //Create new row and Save the Data
        if ($this->save($IndicatorUnitSubgroup)) {
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
     * bulkInsert method
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function bulkInsert($dataArray = [])
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
        //Get Entities based on Coditions
        $IndicatorUnitSubgroup = $this->get($conditions);
        
        //Update Entity Object with data
        $IndicatorUnitSubgroup = $this->patchEntity($IndicatorUnitSubgroup, $fieldsArray);
        
        //Update the Data
        if ($this->save($IndicatorUnitSubgroup)) {
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

            $maxNId = $this->find()->select(_IUS_IUSNID)->max(_IUS_IUSNID);
            return $maxNId->{_IUS_IUSNID};

    }

    /**
     * getConcatedFields method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedIus(array $fields, array $conditions, $type = null) {

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
                    _IUS_INDICATOR_NID => 'literal',
                    ',',
                    _IUS_UNIT_NID => 'literal',
                    ',',
                    _IUS_SUBGROUP_VAL_NID => 'literal',
                    ',\'',
                    _IUS_SUBGROUP_NIDS => 'literal',
                    '\')'
                ]);
        $query->select(['concatinated' => $concat]);*/
        
        $results = $query->hydrate(false)->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

        foreach($data as $key => &$value){
            $value['concatinated'] = '(' . $value[_IUS_INDICATOR_NID] . ',' . $value[_IUS_UNIT_NID] . ',' . $value[_IUS_SUBGROUP_VAL_NID] . ',\'' . $value[_IUS_SUBGROUP_NIDS] . '\')';
        }
        
        return $data;
    }


    /**
     * getAllIUConcatinated method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function getAllIUConcatinated($fields = [], $conditions = [], $extra = [])
    {
        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if (!empty($conditions))
            $options['conditions'] = $conditions;
        
        if (!isset($extra['type']))
            $type = 'all';
        else
            $type = $extra['type'];
        
        $query = $this->find('all', $options);
        
        /*$concat = $query->func()->concat([
            '(',
            _IUS_INDICATOR_NID => 'literal',
            ',',
            _IUS_UNIT_NID => 'literal',
            ')'
        ]);
        $query->select(['concatinated' => $concat]);*/
        
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();
        
        foreach($data as $key => &$value){
            $value['concatinated'] = '(' . $value[_IUS_INDICATOR_NID] . ',' . $value[_IUS_UNIT_NID] . ')';
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