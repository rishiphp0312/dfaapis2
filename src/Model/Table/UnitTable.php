<?php  
namespace App\Model\Table;

use App\Model\Entity\Unit;
use Cake\ORM\Table;


/**
 * Unit Model
 */
class UnitTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Unit_en');
        //$this->primaryKey('Unit_NId');
        $this->addBehavior('Timestamp');
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

        $options['conditions'] = ['Unit_NId IN'=>$ids];

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
    public function getDataByParams(array $fields, array $conditions)
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;

        // Find all the rows.
        // At this point the query has not run.
        $query = $this->find('all', $options);

        // Calling execute will execute the query
        // and return the result set.
        $results = $query->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();

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
        $result = $this->deleteAll(['Unit_NId IN' => $ids]);

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
        $Unit = $this->newEntity();

        //Update New Entity Object with data
        $Unit = $this->patchEntity($Unit, $fieldsArray);
        
        //Create new row and Save the Data
        if ($this->save($Unit)) {
            return 1;
        } else {
            return 0;
        }        

    }


    /**
     * insertBulkData method
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
        //Get Entities based on Coditions
        $Unit = $this->get($conditions);
        
        //Update Entity Object with data
        $Unit = $this->patchEntity($Unit, $fieldsArray);
        
        //Update the Data
        if ($this->save($Unit)) {
            return 1;
        } else {
            return 0;
        }  
    }


}