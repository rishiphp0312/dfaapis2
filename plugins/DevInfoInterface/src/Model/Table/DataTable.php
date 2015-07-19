<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Data;
use Cake\ORM\Table;


/**
 * Data Model
 */
class DataTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Data');
        $this->primaryKey(_MDATA_NID);
        $this->addBehavior('Timestamp');
			
		/*
		$this->belongsTo('Indicator', [
            'className' => 'DevInfoInterface.Indicator',
            'foreignKey' => 'Indicator_NId',
			'joinType' => 'INNER',
        ]);
		$this->belongsTo('Unit', [
            'className' => 'DevInfoInterface.Unit',
            'foreignKey' => 'Unit_NId',
			'joinType' => 'INNER',
        ]);
			$this->belongsTo('SubgroupVals', [
            'className' => 'DevInfoInterface.SubgroupVals',
            'foreignKey' => 'Subgroup_Val_NId',
			'joinType' => 'INNER',
        ]);
		$this->belongsTo('SubgroupVals', [
            'className' => 'DevInfoInterface.SubgroupVals',
            'foreignKey' => 'Subgroup_Val_NId',
			'joinType' => 'INNER',
        ]);
		$this->belongsTo('Footnote', [
            'className' => 'DevInfoInterface.Footnote',
            'foreignKey' => 'FootNote_NId',
			'joinType' => 'INNER',
        ]);
		
	
		$this->addAssociations([
		  'belongsTo' => [
			'Indicator' => ['className' => 'DevInfoInterface.Indicator', 'foreignKey' => 'Indicator_NId',]
		  ],
      'belongsTo' => [
			'Unit' => ['className' => 'DevInfoInterface.Unit', 'foreignKey' => 'Unit_NId',]
		  ],
      'belongsTo' => [
			'SubgroupVals' => ['className' => 'DevInfoInterface.SubgroupVals', 'foreignKey' => 'Subgroup_Val_NId',]
		  ],
    ]);*/
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

        $options['conditions'] = [_UNIT_UNIT_NID . ' IN'=>$ids];

        if($type == 'list') $this->setListTypeKeyValuePairs($fields);
      //  $data = $this->find($type, $options)->all()->toArray();
		$data = $this->find()->all()->toArray();
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
       
        $data = $this->find($type, $options)->hydrate(false)->all()->toArray();
		 
		//$data = $this->find()->all()->toArray();
    
	
        return $data;

    }


    
        
   


    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = [])
    {
        $Unit = $this->newEntity();
        $Unit = $this->patchEntity($Unit, $fieldsArray);       
        if ($this->save($Unit)) {
            return 1;
        } else {
            return 0;
        }        

    }




    


    /**
     * updateDataByParams method
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = [])
    {
       
        $query = $this->query();        
        $query->update()->set($fieldsArray)->where($conditions);
      
        $query->execute();
    }


}