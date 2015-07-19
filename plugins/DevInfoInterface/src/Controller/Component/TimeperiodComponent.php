<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

/**
 * Time period Component
 */
class TimeperiodComponent extends Component {

    public $TimeperiodObj = NULL;
    public $delim1 = '-';
    public $delim2 = '.';

    public function initialize(array $config) {
        parent::initialize($config);
        $this->TimeperiodObj = TableRegistry::get('DevInfoInterface.TimePeriods');
    }

    /**
     * getDataByIds method
     *
     * @param array $ids the ids can be multiple or single to get filtered records . {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param  $type the the type of list user needs it can be list or first or count . {DEFAULT : all}
     * @return void
     */
    public function getDataByIds($ids, $fields = [], $type) {
        return $this->TimeperiodObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParams method
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams($fields = [], $conditions = [], $type = 'all') {
        return $this->TimeperiodObj->getDataByParams($fields, $conditions, $type);
    }

    /**
     * @param $timeperiodvalue the timeperiod value according to which records will be filtered .
     * @param $periodicity the periodicity value will be optional .
     * @return void
     */
    public function getDataByTimeperiod($timeperiodvalue, $periodicity = '') {
        return $this->TimeperiodObj->getDataByTimeperiod($timeperiodvalue, $periodicity);
    }

    /**
     * deleteByIds method
     *
     * @param  $ids the ids which needs to be deleted . {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        return $this->TimeperiodObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {    // pr($conditions);die;
        return $this->TimeperiodObj->deleteByParams($conditions);
    }

    /**
     * @param $timeperiodvalue the timeperiod value which will be deleted .
     * @return void
     */
    public function deleteByTimePeriod($timeperiodvalue) {
        return $this->TimeperiodObj->deleteByTimePeriod($timeperiodvalue);
    }

    /**
     * insertUpdateDataTimeperiod method to save or update data 
     *
     * @param array $fieldsArray Fields to insert or update  with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertUpdateDataTimeperiod($fieldsArray = []) {
        $timeperiodvalue = $fieldsArray[_TIMEPERIOD_TIMEPERIOD];
        $timeformatData = $this->checkTimePeriodFormat($timeperiodvalue);
        $fieldsArray[_TIMEPERIOD_STARTDATE] = new Time($timeformatData[_TIMEPERIOD_STARTDATE]);
        $fieldsArray[_TIMEPERIOD_ENDDATE] = new Time($timeformatData[_TIMEPERIOD_ENDDATE]);
        return $this->TimeperiodObj->insertData($fieldsArray);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        $timeperiodvalue = $fieldsArray[_TIMEPERIOD_TIMEPERIOD];
        $timeformatData = $this->checkTimePeriodFormat($timeperiodvalue);
        return $this->TimeperiodObj->updateDataByParams($fieldsArray, $conditions);
    }

    /*
      Function returns the end and start date after checking the format of timeperiod
     */

    public function checkTimePeriodFormat($timeperiodvalue = '') {
        //
        //echo $timeperiodvalue;
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
            $year = $explodedelim2[0]; //start and end year  
            $month = $explodedelim2[1]; // start month 			
            $numberofdays_end_month = cal_days_in_month(CAL_GREGORIAN, 12, $year); // 31			
            $startyear = $year . '-' . $month . '-01';

            $endyear = $year . '-' . $month . '-' . $numberofdays_end_month;
            //die;
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

}
