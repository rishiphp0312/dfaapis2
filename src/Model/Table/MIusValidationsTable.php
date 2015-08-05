<?php
namespace App\Model\Table;

use App\Model\Entity\MIusValidation;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MIusValidations Model
 */
class MIusValidationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('m_ius_validations');
        $this->displayField(_MIUSVALIDATION_ID);
        $this->primaryKey(_MIUSVALIDATION_ID);
        $this->addBehavior('Timestamp');
        $this->belongsTo('MDatabaseConnections', [
            'foreignKey' => 'db_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['db_id'], 'MDatabaseConnections'));
        return $rules;
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
     * Creates record
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecord($fieldsArray = [])
    {
        $MIusValidations = $this->newEntity();
        $MIusValidations = $this->patchEntity($MIusValidations, $fieldsArray);
        
        $result = $this->save($MIusValidations);
        
        if ($result) {
            return $result->{_MIUSVALIDATION_ID};
        } else {
            return 0;
        }        
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
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
        
        $query = $this->find($type, $options);
        
        if(isset($extra['debug']) && $extra['debug'] == true){
            debug($query);exit;
        }
        
        if(isset($extra['count']) && $extra['count'] == true){
            return $data = $query->count();
        }else if(isset($extra['first']) && $extra['first'] == true){
            $data = $query->hydrate(false)->first();
        }else{
            $results = $query->hydrate(false)->all();
            $data = $results->toArray();
        }

        return $data;

    }

    /**
     * Update record
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecord($fieldsArray = [], $conditions = [])
    {
        //Initialize
        $query = $this->query();
        
        //Set
        $query->update()->set($fieldsArray)->where($conditions);
        
        //Execute
        $query->execute();
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
    
}
