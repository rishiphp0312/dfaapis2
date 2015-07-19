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

    public function beforesave(Event $event, Entity $entity) {

        //$this->slug($entity);
    }

    /**
     * getDataByIds method
     * @param array $id The WHERE conditions with ids only for the Query. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, array $fields, $type = 'all') {

        $options = [];

        if (isset($ids) && !empty($ids))
            $options['conditions'] = [_TIMEPERIOD_TIMEPERIOD_NID . ' IN' => $ids];

        if (isset($fields) && !empty($fields))
            $options['fields'] = $fields;

        if ($type == 'list' && empty($fields))
            $options['fields'] = array(_TIMEPERIOD_TIMEPERIOD_NID, _TIMEPERIOD_TIMEPERIOD);


        if (empty($type))
            $type = 'all';

        if ($type == 'list') {
            $options['keyField'] = $fields[0];
            $options['valueField'] = $fields[1];
            $query = $this->find($type, $options);
        } else {
            $query = $this->find($type, $options);
        }
        //pr($query);die;

        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        // Once we have a result set we can get all the rows

        return $data;
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
     * getDataByParams method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;

        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        // Find all the rows.
        // At this point the query has not run.
        $query = $this->find($type, $options);

        // Calling execute will execute the query
        // and return the result set.
        $results = $query->hydrate(false)->all();
        
        // Once we have a result set we can get all the rows
        $data = $results->toArray();

        return $data;
    }

    /**
     *  getDataByTimeperiod method
     *  @param $timeperiodvalue The value on which you will get details on basis of the timeperiodvalue. {DEFAULT : empty}
     *  @param  $periodicity is optional parameter {DEFAULT : empty}
     */
    public function getDataByTimeperiod($timeperiodvalue, $periodicity = '') {
        $options = [];

        if (isset($timeperiodvalue) && !empty($timeperiodvalue)) {
            $options['conditions'] = array(_TIMEPERIOD_TIMEPERIOD => $timeperiodvalue);
        }

        if (isset($periodicity) && !empty($periodicity)) {
            $options['conditions'] = array(_TIMEPERIOD_PERIODICITY => $periodicity);
        }

        if (isset($timeperiodvalue) && !empty($timeperiodvalue)) {
            $timperioddetails = $this->find('all', $options);
            //$results = $timperioddetails->hydrate(false)->all();
            // Once we have a result set we can get all the rows
            // $data = $results->toArray();
            $data = $timperioddetails->hydrate(false)->first();
            // Once we have a result set we can get all the rows
        }

        return $data;
    }

    /**
     * deleteByIds method
     * @param array $ids it can be one or more to delete the timeperiod rows . {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {

        $result = $this->deleteAll([_TIMEPERIOD_TIMEPERIOD_NID . ' IN' => $ids]);

        return $result;
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions on the basis of which record will be deleted . 
     * @return void
     */
    public function deleteByParams(array $conditions) {
        $result = $this->deleteAll($conditions);

        return $result;
    }

    /**
     * 
     * deleteByTimePeriod method       
     * @param  $timeperiodvalue timeperiod which will be saved in database if exists  will be deleted. 
     * @return void
     *
     */
    public function deleteByTimePeriod($timeperiodvalue) {

        if (isset($timeperiodvalue) && !empty($timeperiodvalue)) {
            //deleteentity  checks whether record exists or not 
            $deleteentity = $this->find()->where([_TIMEPERIOD_TIMEPERIOD => $timeperiodvalue])->first();

            if (isset($deleteentity) && !empty($deleteentity)) {

                if ($result = $this->delete($deleteentity)) {
                    return 1;
                } else {
                    return 0;
                }
            } else {                                   // Already exists
                return 0;
            }
        } else {
            return 0;
        }
    }

// end of function 

    /**
     * insertData method
     * @param array $fieldsArray Fields to insert with their Data.
     * @return void
     */
    public function insertData($fieldsArray = []) {

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
    }

    public function insertData_old($fieldsArray) {

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
                $data = $this->newEntity();

                $data->TimePeriod = $timeperiodvalue;
                $numberofdays_dec = cal_days_in_month(CAL_GREGORIAN, 12, date('Y')); // 31
                $timeformatData = $this->checkTimePeriodFormat($timeperiodvalue);

                $data->StartDate = $timeformatData[_TIMEPERIOD_STARTDATE];
                $data->EndDate = $timeformatData[_TIMEPERIOD_ENDDATE];
                $data->Periodicity = $fieldsArray[_TIMEPERIOD_PERIODICITY];
                
                //Create new row and Save the Data
                if ($this->save($data)) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }

        return $msg;
    }

    /*
      Function returns the end and start date after checking the format of timeperiod
     */

    public function checkTimePeriodFormat($timeperiodvalue = '') {
        //
        $pos_delim1 = strpos($timeperiodvalue, $this->delim1);
        $pos_delim2 = strpos($timeperiodvalue, $this->delim2);

        if ($pos_delim1 > 0 && $pos_delim2 == false) {
            //case only hypen		
            return $this->dataTimeFormatReturned($timeperiodvalue, $this->delim1);
        }

        if ($pos_delim1 == false && $pos_delim2 > 0) {
            // case only period   
            return $this->dataTimeFormatReturned($timeperiodvalue, $this->delim2);
        }

        if ($pos_delim1 > 0 && $pos_delim2 > 0) {
            //case both hypen and period
            return $this->dataTimeFormatReturned($timeperiodvalue, 'Both');
        }

        if ($pos_delim1 == false && $pos_delim2 == false) {
            //case nothing occurs either hypen or period				  
            return $this->dataTimeFormatReturned($timeperiodvalue, 'NA');
        }
    }

// end of function checkTimePeriodFormat

    /*
      Function returns the end and start date according to the the format of timeperiod
      $type its type of separator passed can be . or -
     */

    public function dataTimeFormatReturned($timeperiodvalue, $type) {


        if ($type == '.') {
            // case 2012.02
            $explodedelim2 = explode($this->delim2, $timeperiodvalue);
            pr($explodedelim2);
            $year = $explodedelim2[0]; //start and end year  
            $month = $explodedelim2[1]; // start month 			
            $numberofdays_end_month = cal_days_in_month(CAL_GREGORIAN, 12, $year); // 31			

            echo $startyear = $year . '-' . $month . '-01';
            //	echo '<br>';
            echo $endyear = $year . '-' . $month . '-' . $numberofdays_end_month;

            return array('StartDate' => $startyear, 'EndDate' => $endyear, 'success' => true);
        } elseif ($type == 'Both') {
            // case 2012.02-2013.06
            $explodedelim1 = explode($this->delim1, $timeperiodvalue); //  explode hypen 
            // first breaking - values 
            $firstdate = $explodedelim1[0];
            $lastdate = $explodedelim1[1];

            $explodefirstdate = explode($this->delim2, $firstdate);  //  explode period
            $explodelastdate = explode($this->delim2, $lastdate);   //  explode period


            $year1 = $explodefirstdate[0]; // start year
            $month1 = $explodefirstdate[1]; // start month

            $year2 = $explodelastdate[0]; // end year
            $month2 = $explodelastdate[1]; // end month  

            $numberofdays_end_month = cal_days_in_month(CAL_GREGORIAN, $month2, $year2); // 31
            $startyear = $year1 . '-' . $month1 . '-01';
            $endyear = $year2 . '-' . $month2 . '-' . $numberofdays_end_month;

            return array('StartDate' => $startyear, 'EndDate' => $endyear, 'success' => true);
        } elseif ($type == '-') { // case 2012-2013
            $explodedelim1 = explode($this->delim1, $timeperiodvalue);
            $year1 = $explodedelim1[0]; // start year
            $year2 = $explodedelim1[1];  // end  year 	
            $numberofdays_end_month = cal_days_in_month(CAL_GREGORIAN, 12, $year2); // 31			
            $startyear = $year1 . '-01-01';
            $endyear = $year2 . '-12-' . $numberofdays_end_month;
            return array('StartDate' => $startyear, 'EndDate' => $endyear, 'success' => true);
        } else {
            // case 2012
            $numberofdays_end_month = cal_days_in_month(CAL_GREGORIAN, 12, $timeperiodvalue); // 31			
            $startyear = $timeperiodvalue . '-01-01';
            $endyear = $timeperiodvalue . '-12-' . $numberofdays_end_month;
            return array('StartDate' => $startyear, 'EndDate' => $endyear, 'success' => true);
        }
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {

        $Timeperiod = $this->get($conditions);
        //Update Entity Object with data
        $Timeperiod = $this->patchEntity($Timeperiod, $fieldsArray);

        //Update the Data
        if ($this->save($Timeperiod)) {
            return 1;
        } else {
            return 0;
        }
    }

}
