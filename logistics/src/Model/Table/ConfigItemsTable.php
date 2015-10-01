<?php
namespace App\Model\Table;

use App\Model\Entity\ConfigItem;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ConfigItems Model
 */
class ConfigItemsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('config_items');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
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
     * method to get all config details 
     * $options array to pass conditions and contain models 
     * @return void
     */
    public function getConfigDetails($type = 'all', $options = []){
        if ($type == 'list' && isset($options['fields']) && !empty($options['fields']))
            $this->setListTypeKeyValuePairs($options['fields']);
        return $this->find($type ,$options)->hydrate(false)->all()->toArray();
      
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
      $extra if extra is nid then returns nid else gives gid
     */
    public function saveRecords($fieldsArray = []) {
        //Create New Entity
        $entity = $this->newEntity();

        //Update New Entity Object with data
        $entity = $this->patchEntity($entity, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($entity);
        if ($result) {
            return $result->id;
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
    public function saveBulkRecords($dataArray = []) {
        // IF only one record being inserted/updated
        if (count($dataArray) == 1) {
            return $this->saveRecords(reset($dataArray));
        }

        // Remove any Duplicate entry
        $dataArray = array_intersect_key($dataArray, array_unique(array_map('serialize', $dataArray)));

        //Create New Entities (multiple entities for multiple rows/records)
        $entities = $this->newEntities($dataArray);
        $entities = $this->patchEntities($entities, $dataArray);

        foreach ($entities as $entity) {
            if (!$entity->errors()) {
                //Create new row and Save the Data
                $this->save($entity);
            }
        }
    }   
}
