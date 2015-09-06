<?php
namespace App\Model\Table;

use App\Model\Entity\MTransactionLog;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MTransactionLogs Model
 */
class MTransactionLogsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('m_transaction_logs');
        $this->displayField(_MTRANSACTIONLOGS_ID);
        $this->primaryKey(_MTRANSACTIONLOGS_ID);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['db_id'], 'MDatabaseConnections'));
        return $rules;
    }

    /**
     * Creates record
     *
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecord($fieldsArray = [])
    {
        $MTransactionLogs = $this->newEntity();
        $MTransactionLogs = $this->patchEntity($MTransactionLogs, $fieldsArray);
        
        $result = $this->save($MTransactionLogs);
        
        if ($result) {
            return $result->{_MTRANSACTIONLOGS_ID};
        } else {
            return 0;
        }        
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
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords(array $conditions)
    {
        $result = $this->deleteAll($conditions);
        return $result;
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all',$extra=[])
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;
        
        $order = [];
        if (isset($extra['order']) && !empty($extra['order'])) {
            $order = $extra['order'];
        } else {
            $order = ['id' => 'DESC'];
        }

        if($type == 'list') $this->setListTypeKeyValuePairs($fields);
        
        $query = $this->find($type, $options);
       
        if(isset($extra['first']) && $extra['first'] == true) {
            $results = $query->order($order)->first();
        } else {
            if(isset($extra['limit'])) {
                $results = $query->order($order)->limit($extra['limit'])->hydrate(false)->all();
            } else {
                $results = $query->order($order)->hydrate(false)->all();
            }
        }
        //  $results = $query->order($order)->hydrate(false)->all();
        if(!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
        
        return $results;

    }
    
}
