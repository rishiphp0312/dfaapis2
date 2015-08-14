<?php
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Subgroup;
use Cake\ORM\Table;

/**
 * SubgroupTable Model
 */
class SubgroupTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Subgroup_en');
        $this->primaryKey(_SUBGROUP_SUBGROUP_NID);
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
    public function getRecords(array $fields, array $conditions, $type = null, $debug = false)
    {
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
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }
        
        if($debug == true){
            debug($query);exit;
        }
            
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
        if ($result > 0)
            return $result;
        else
            return 0;
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray)
    { //Create New Entity
        $Subgroup = $this->newEntity();
        
        //Update New Entity Object with data
        $Subgroup = $this->patchEntity($Subgroup, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($Subgroup);
        if ($result) {
			return $result->{_SUBGROUP_SUBGROUP_NID};	   
				
        } else {
            return 0;
        }        
	}

	/*
	public function insertData($fieldsArray)
    {

        $Subgroup_Name = $fieldsArray[_SUBGROUP_SUBGROUP_NAME];

        $conditions = array();

        if (isset($fieldsArray[_SUBGROUP_SUBGROUP_NAME]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NAME]))
            $conditions[_SUBGROUP_SUBGROUP_NAME] = $fieldsArray[_SUBGROUP_SUBGROUP_NAME];

        if (isset($fieldsArray[_SUBGROUP_SUBGROUP_NID]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NID]))
            $conditions[_SUBGROUP_SUBGROUP_NID . ' !='] = $fieldsArray[_SUBGROUP_SUBGROUP_NID];

        if (isset($Subgroup_Name) && !empty($Subgroup_Name)) {

            $numrows = $this->find()->where($conditions)->count();
            if (isset($numrows) && $numrows == 0) {  // new record			   
                if (empty($fieldsArray[_SUBGROUP_SUBGROUP_ORDER])) {

                    $query = $this->find();
                    $results = $query->select(['max' => $query->func()->max(_SUBGROUP_SUBGROUP_ORDER)])->first();
                    $ordervalue = $results->max;
                    $maxordervalue = $ordervalue + 1;
                    $fieldsArray['Subgroup_Order'] = $maxordervalue;
                }

                $Subgroup = $this->newEntity();
                $Subgroup = $this->patchEntity($Subgroup, $fieldsArray);

                if ($this->save($Subgroup)) {
                    return 1;
                }
            }
        }
        return 0;
    }
	*/

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = [])
    {
        //Get Entities based on Coditions
        $Subgroup = $this->get($conditions);

        //Update Entity Object with data
        $Subgroup = $this->patchEntity($Subgroup, $fieldsArray);

        //Update the Data
        if ($this->save($Subgroup)) {
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
        $query = $this->query()->select([$alias => $column])->where($conditions)->order([_SUBGROUP_SUBGROUP_ORDER => 'DESC'])->limit(1);

        $data = $query->hydrate(false)->first();
        if(!empty($data)){
            return $data[$alias];
        }else{
            return 0;
        }
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
        return $this->find('all', ['fields' => [], 'conditions' => [_SUBGROUP_SUBGROUP_NID => 0]])->hydrate(false)->all();
    }

}
