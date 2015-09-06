<?php
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\SubgroupVal;
use Cake\ORM\Table;
use Cake\Network\Session;

/**
 * SubgroupValsTable Model
 */
class SubgroupValsTable extends Table {

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
        $this->table('UT_Subgroup_Vals_' . $defaultLangcode);
        $this->primaryKey(_SUBGROUP_VAL_SUBGROUP_VAL_NID);
        $this->addBehavior('Timestamp');
        $this->hasMany('SubgroupValsSubgroup', [
            'className' => 'DevInfoInterface.SubgroupValsSubgroup',
            'foreignKey' => _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID,
            'joinType' => 'INNER',
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
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = null,$extra=[]) {

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
		$order =[];
		if(isset($extra['order']) && !empty($extra['order'])){
			$order = $extra['order'];
		}else{
			$order =[_SUBGROUP_VAL_SUBGROUP_VAL_NID =>'ASC'];
		}
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
     */
    public function insertData($fieldsArray = [])
    {
        //Create New Entity
        $subgroupVal = $this->newEntity();
        
        //Update New Entity Object with data
        $subgroupVal = $this->patchEntity($subgroupVal, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($subgroupVal);
        if ($result) {
            return $result->{_SUBGROUP_VAL_SUBGROUP_VAL_NID};
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
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
       
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
        } else {
            return 0;
        }
    }
	
	public function getSgValSgData() {
			 $conditions =[];
			 //$query = $this->find()->where($conditions)->contain(['SubgroupValsSubgroup'], true)->hydrate(false);
			 $fields = [
                'SubgroupVals.Subgroup_Val_NId', 
                'SubgroupVals.Subgroup_Val', 
                'SubgroupVals.Subgroup_Val_GId', 
                'SGS.Subgroup_NId'
            ];
			$query = $this->find()
				->hydrate(false)
				->select($fields)
				->join([
					'SGS' => [
						'table' => 'UT_Subgroup_Vals_Subgroup',
						'type' => 'INNER',
						'conditions' => 'SGS.Subgroup_Val_NId = SubgroupVals.Subgroup_Val_NId',
					]
			]);
			//pr($query); exit;
        
			$data = $query->all()->toArray(); 
			return $data;
			
  
    }


    /**
     * get maximum value of column given based on conditions
     *
     * @param array $column max column. {DEFAULT : empty}
     * @param array $conditions Query conditinos. {DEFAULT : empty}
     * @return max value if found else 0
     */
    public function getMax($column = '', $conditions = []) {

        $alias = 'max';
        $query = $this->query()->select([$alias => 'MAX(' . $column . ')'])->where($conditions);
        $data = $query->hydrate(false)->first();

        return $data[$alias];
    }

}
