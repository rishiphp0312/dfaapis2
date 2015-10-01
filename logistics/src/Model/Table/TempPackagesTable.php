<?php
namespace App\Model\Table;

use App\Model\Entity\TempPackage;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TempPackages Model
 */
class TempPackagesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('temp_packages');
        $this->displayField('id');
        $this->primaryKey('id');
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
        $temp = $this->newEntity();

        //Update New Entity Object with data
        $temp = $this->patchEntity($temp, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($temp);
        if ($result) {
            return $result->id;
        } else {
            return 0;
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

    
    /**
     * get total no of records 
     * array @conditions  The WHERE conditions for the Query. {DEFAULT : empty} 
     */
    public function getCount($conditions = []) {
        return $total = $this->find()->where($conditions)->count();
        //  return $total =  $this->query()->find()->where($conditions)->count();
    }

    /*
     * 
     * method to delete the packages  
     * @conditions array 
     */

    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        else
            return 0;
    }
}
