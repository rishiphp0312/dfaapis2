<?php

namespace DevInfoInterface\Model\Table;

use App\Model\Entity\TimePeriod;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * TimePeriodsTable Model
 */
class TimePeriodsTable extends Table {

    public $delim1 = '-';
    public $delim2 = '.';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        $this->table('UT_TimePeriod');
        $this->primaryKey(_TIMEPERIOD_TIMEPERIOD_NID);
        $this->addBehavior('Timestamp');
    }

    public static function defaultConnectionName() {
        return 'devInfoConnection';
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
     * getRecords method     *
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
        // Set ORDER
        $query->order([_TIMEPERIOD_TIMEPERIOD => 'ASC']);
        
        if(isset($extra['first']) && $extra['first'] == true) {
            $data = $query->hydrate(false)->first();
        } else {
            $results = $query->hydrate(false)->all();
            // Once we have a result set we can get all the rows
            $data = $results->toArray();
        }

        return $data;
    }

    /**
     * deleteRecords method
     *
     * @param array $conditions on the basis of which record will be deleted . 
     * @return void
     */
    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);

        return $result;
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
        $tp = $this->newEntity();

        //Update New Entity Object with data
        $tp = $this->patchEntity($tp, $fieldsArray);
        
        //Create new row and Save the Data
        $result = $this->save($tp);
        if ($result) {
            return [
                'id' => $result->{_TIMEPERIOD_TIMEPERIOD_NID},
                'name' => $result->{_TIMEPERIOD_TIMEPERIOD}
            ];
        } else {
            return 0;
        }
    }

    /**
     * insertData method
     * @param array $fieldsArray Fields to insert with their Data.
     * @return void
     */
    /*public function insertData($fieldsArray = []) {

        $timeperiodvalue = $fieldsArray[_TIMEPERIOD_TIMEPERIOD];

        $conditions = array();

        if (isset($fieldsArray[_TIMEPERIOD_TIMEPERIOD]) && !empty($fieldsArray[_TIMEPERIOD_TIMEPERIOD]))
            $conditions[_TIMEPERIOD_TIMEPERIOD] = $fieldsArray[_TIMEPERIOD_TIMEPERIOD];

        if (isset($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID]) && !empty($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID]))
            $conditions[_TIMEPERIOD_TIMEPERIOD_NID . ' !='] = $fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID];

        if (isset($timeperiodvalue) && !empty($timeperiodvalue)) {

            //numrows if numrows >0 then record already exists else insert new row
            $numrows = $this->find()->where($conditions)->count();

            if (isset($numrows) && $numrows == 0) {  // new record
                //Create New Entity
                $TimeperiodData = $this->newEntity();
                //pr($fieldsArray);die;
                //Update New Entity Object with data
                $TimeperiodData = $this->patchEntity($TimeperiodData, $fieldsArray);

                //Create new row and Save the Data
                if ($this->save($TimeperiodData)) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }*/

    /**
     * updateRecords method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    /*public function updateRecords($fieldsArray = [], $conditions = []) {

        $Timeperiod = $this->get($conditions);
        //Update Entity Object with data
        $Timeperiod = $this->patchEntity($Timeperiod, $fieldsArray);

        //Update the Data
        if ($this->save($Timeperiod)) {
            return 1;
        } else {
            return 0;
        }
    }*/
	 
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

}
