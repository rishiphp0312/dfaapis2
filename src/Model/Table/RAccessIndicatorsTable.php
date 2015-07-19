<?php

namespace App\Model\Table;

use App\Model\Entity\RAccessIndicator;
use Cake\ORM\Table;

/**
 * RAccessIndicators Model
 */
class RAccessIndicatorsTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('r_access_indicators');
        $this->primaryKey(_RACCESSINDICATOR_ID);
        $this->addBehavior('Timestamp');
        $this->displayField(_RACCESSINDICATOR_INDICATOR_NAME); //used for find('list')         
    }

    /**
     * setListTypeKeyValuePairs method
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields) {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * Creates record 
     * @param array $fieldsArray data to be created
     * @return \Cake\ORM\RulesChecker
     */
    public function createRecord($fieldsArray = []) {
        $RAccessIndicators = $this->newEntity();
        $RAccessIndicators = $this->patchEntity($RAccessIndicators, $fieldsArray);
        $result = $this->save($RAccessIndicators);
        if ($result) {
            return $result->{_RACCESSINDICATOR_ID};
        } else {
            return 0;
        }
    }

    /**
     * Update record     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return \Cake\ORM\RulesChecker
     */
    public function updateRecord($fieldsArray = [], $conditions = []) {
        $query = $this->query();
        $query->update()->set($fieldsArray)->where($conditions);
        $query->execute();
    }

    /**
     * Get Records
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param string $type Query type {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all') {
        $options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $query = $this->find($type, $options);
        $data = $query->hydrate(false)->all()->toArray();
        return $data;
    }
	
	
	/*
	getAssignedIndicators to get the indicators assigned to user on db 
	@rudId is the user database id
	@rudrId is the user db role id 
	*/
	
	public function getAssignedIndicators($rudId=null, $rudrId=null) {
        
		$options = [];
        $options['fields'] = [_RACCESSINDICATOR_INDICATOR_GID,_RACCESSINDICATOR_INDICATOR_NAME];
		$options['conditions'] = [_RACCESSINDICATOR_USER_DATABASE_ROLE_ID=>$rudId,_RACCESSINDICATOR_USER_DATABASE_ID=>$rudrId];
        $this->setListTypeKeyValuePairs($fields);
        $query = $this->find('list', $options);
        $data = $query->hydrate(false)->all()->toArray();
        return $data;
    }
	
	

    /**
     * deleteUserAreas method used when modifying areas  
       @RUD_ids is the array of RUD table
       @RUDR_ids is the array of RUDR table
       @indicatorgids which needs to be deleted
     * @return void
	 * $type can be IN or NOT IN for role ids default is IN 
     */
    public function deleteUserIndicators($RUD_ids = [], $RUDR_ids = [],$type=' IN ') {    
		$result = $this->deleteAll([_RACCESSINDICATOR_USER_DATABASE_ID . ' IN' => $RUD_ids, _RACCESSINDICATOR_USER_DATABASE_ROLE_ID . $type => $RUDR_ids]);
        return $result;
    }
	
	
	

}
