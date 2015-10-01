<?php
namespace App\Model\Table;

use App\Model\Entity\AreaLevel;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AreaLevels Model
 */
class AreaLevelsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('area_levels');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('ModifiedUsers', [
            'foreignKey' => 'modified_user_id'
        ]);
        $this->belongsTo('CreatedUsers', [
            'foreignKey' => 'created_user_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Areas', [
            'foreignKey' => 'area_level_id'
        ]);
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
     * getRecords method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $query = $this->find($type, $options);
        
        if(isset($extra['debug']) && $extra['debug'] == true) {
            debug($query);exit;
        }
        
        // and return the result set.
        if(isset($extra['first']) && $extra['first'] == true) {
            $results = $query->first();
        } else {
            $results = $query->hydrate(false)->all();            
        }
        
        if(!empty($results)) {
            // Once we have a result set we can get all the rows
            $results = $results->toArray();
        }
    
        return $results;
    }

    /**
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);

        return $result;
    }
	
	    /**
     * updateRecords method     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $query = $this->query()->update()->set($fieldsArray)->where($conditions)->execute();  // Initialize
        //$query->update()->set($fieldsArray)->where($conditions); // Set
        //  $query->execute(); // Execute
        $code = $query->errorCode();

        if ($code == '00000') {
            return 1;
        } else {
            return 0;
        }
    }
	
	
	
	
    /**
     * method to add/modify area level 
     * @fieldsArray is the posted data
     */
    public function insertData($fieldsArray = []) {

        $AreaLevel = $this->newEntity();
        $AreaLevel = $this->patchEntity($AreaLevel, $fieldsArray);
        if ($this->save($AreaLevel)) {
            return $AreaLevel->id;
        } else {
            return 0;
        }
    }


    
}
