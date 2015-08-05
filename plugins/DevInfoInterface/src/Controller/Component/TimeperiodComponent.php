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
    public $components = ['DevInfoInterface.Data'];
    public function initialize(array $config) {
        parent::initialize($config);
        $this->TimeperiodObj = TableRegistry::get('DevInfoInterface.TimePeriods');
    }

    /**
     * get Time period Records
     * 
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param string $type Query type. {DEFAULT : all}
     * @return void
     */
    public function getRecords($fields = [], $conditions = [], $type = 'all', $extra = []) {
        return $this->TimeperiodObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * insertRecords method to save or update data 
     *
     * @param array $fieldsArray Fields to insert or update  with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertRecords($fieldsArray = []) {
        
		$tpVal = $fieldsArray[_TIMEPERIOD_TIMEPERIOD];
			
		$conditions = [];
		$conditions = [_TIMEPERIOD_TIMEPERIOD => $tpVal];
		if(isset($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID]) && !empty($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID])){
		  $extra[_TIMEPERIOD_TIMEPERIOD_NID.' !='] = $fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID];
		  $conditions =  array_merge($conditions,$extra);    		
		}

        $result = $this->getRecords(['id' => _TIMEPERIOD_TIMEPERIOD_NID, 'name' => _TIMEPERIOD_TIMEPERIOD],$conditions, 'all', ['first' => true]);
        if(!empty($result)) return ['error'=>_ERR134];///already exist

        // Create Start/End date
        $tpStartEnd = $this->getStartEndDate($tpVal);
        if($tpStartEnd === false) return ['error'=>_ERR133]; //date format 
        
        $fieldsArray[_TIMEPERIOD_STARTDATE] = $tpStartEnd[_TIMEPERIOD_STARTDATE];
        $fieldsArray[_TIMEPERIOD_ENDDATE] = $tpStartEnd[_TIMEPERIOD_ENDDATE];
        
		if(isset($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID]) && !empty($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID])){
		    $upadateCond[_TIMEPERIOD_TIMEPERIOD_NID] = $fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID];
		    unset($fieldsArray[_TIMEPERIOD_TIMEPERIOD_NID]);
		    $return = $this->TimeperiodObj->updateRecords($fieldsArray,$upadateCond);		
		  
		}else{
			$return = $this->TimeperiodObj->insertData($fieldsArray);
		}
		if($return>0){
				return true;
		}else{
				return ['error'=>_ERR100];//server error 
		}
        
    }

    /**
     * updateRecords method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        $tpVal = $fieldsArray[_TIMEPERIOD_TIMEPERIOD];
        
        // Create Start/End date
        $tpStartEnd = $this->getStartEndDate($tpVal);
        if($tpStartEnd === false) return false;
        
        $fieldsArray[_TIMEPERIOD_STARTDATE] = $tpStartEnd[_TIMEPERIOD_STARTDATE];
        $fieldsArray[_TIMEPERIOD_ENDDATE] = $tpStartEnd[_TIMEPERIOD_ENDDATE];
        
        return $this->TimeperiodObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * Delete records
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {    // pr($conditions);die;
        return $this->TimeperiodObj->deleteRecords($conditions);
    }

    /**
     * get Start and End date from Timeperiod
     *
     * @param string $tp Time Period Value
     * @return void
     */
    public function getStartEndDate($tp)
    {
        // Check Validations
        $validationTp = $this->validateTimeperiod($tp);
        if($validationTp === false) return false;
        
        // Range(-)
        if(substr_count($tp, '-') == 1) {
            
            $startEndDate = [];
            $tpExploded = explode('-', $tp);
            foreach($tpExploded as $tpVal) {
                $startEndDate[] = $this->getStartEndDateSingleFormat($tpVal);
            }
            $startDate = $startEndDate[0][_TIMEPERIOD_STARTDATE];
            $endDate = $startEndDate[1][_TIMEPERIOD_ENDDATE];
            
            if($startDate > $endDate) return false;
            
        } // Quater(:)
        else if(substr_count($tp, ':') == 1) {
            
            $tpExploded = explode(':', $tp);
            $quater = str_replace('Q', '', $tpExploded[0]);
            $endMonth = $quater * 3;
            $startMonth = $endMonth - 3;
            $startDay = 1;
            $endDay = cal_days_in_month(CAL_GREGORIAN, $endMonth, $tpExploded[1]);
            
        }// Month/Day/Year
        else {
            $startEndDate = $this->getStartEndDateSingleFormat($tp);
            $startDate = $startEndDate[_TIMEPERIOD_STARTDATE];
            $endDate = $startEndDate[_TIMEPERIOD_ENDDATE];
        }
        
        if(!isset($startDate))
            $startDate = date('Y-m-d H:i:s', mktime(0, 0, 0, $startMonth, $startDay, $startYear));
        if(!isset($endDate))
            $endDate = date('Y-m-d H:i:s', mktime(0, 0, 0, $endMonth, $endDay, $endYear));
        
        return [_TIMEPERIOD_STARTDATE => $startDate, _TIMEPERIOD_ENDDATE => $endDate];
        
    }

    /**
     * get Start and End date for 
     * YYYY
     * YYYY.MM
     * YYYY.MM.DD
     *
     * @param string $tp Time Period Value
     * @return void
     */
    public function getStartEndDateSingleFormat($tp)
    {
        // Month/Day(.)
        if(substr_count($tp, '.') > 0) {
            
            $tpExploded = explode('.', $tp);
            $startMonth = $endMonth = $tpExploded[1];
            $startYear = $endYear = $tpExploded[0];
            
             // YYYY.MM.DD
            if(isset($tpExploded[2])) {
                $startDay = $endDay = $tpExploded[2];
            } // YYYY.MM
            else {
                $startDay = 1;
                $endDay = cal_days_in_month(CAL_GREGORIAN, $tpExploded[1], $tpExploded[0]);
            }
            
        } // Year
        else {
            $startDay = 1;
            $startMonth = 1;
            $endDay = 31;
            $endMonth = 12;
            $startYear = $endYear = $tp;
        }
        
        $startDate = date('Y-m-d H:i:s', mktime(0, 0, 0, $startMonth, $startDay, $startYear));
        $endDate = date('Y-m-d H:i:s', mktime(0, 0, 0, $endMonth, $endDay, $endYear));
        
        return [_TIMEPERIOD_STARTDATE => $startDate, _TIMEPERIOD_ENDDATE => $endDate];
    }
    
    /**
     * Valdiate Time formats
     * 
     * @params string $timePeriod Time Format to check
     * @return boolean true/false
     */
    public function validateTimeperiod($timePeriod)
    {
        // Hyphen found
        if(strpos($timePeriod, '-') !== false){
            
            // Only single hyphen is present
            if(substr_count($timePeriod, '-') == 1) {
                
                if(substr_count($timePeriod, ':') > 0) return false;
                
                $timePeriodExploded = explode('-', $timePeriod);
                
                foreach($timePeriodExploded as $tp){
                    $return = $this->checkValidDate($tp);
                    if($return === false) {
                        return false;
                    }
                }                
                return true;
                
            } // multiple hyphens in TP
            else{
                return false;
            }
        } // No Hyphen found
        else {
            return $this->checkValidDate($timePeriod);
        }
    }
    
    /**
     * 
     * Validate Date
     * 
     * @params string $timePeriod Time Format to check
     * @return boolean true/false
     */
    public function checkValidDate($timePeriod)
    {
        // Qn.YYYY
        if(substr_count($timePeriod, 'Q') == 1){
            if(preg_match('/^Q[1-4]\.[\d]{4}$/', $timePeriod) === 0) {
                return false;
            }
        } // YYYY
        else if(substr_count($timePeriod, '.') == 0){
            if(preg_match('/^\d{4}$/', $timePeriod) === 0) {
                return false;
            }
        } // YYYY.MM
        else if(substr_count($timePeriod, '.') == 1){
            if(preg_match('/^((\d{4}\.0[1-9])|\d{4}\.1[0-2])$/', $timePeriod) === 0) {
                return false;
            }
        } // YYYY.MM.DD
        else if(substr_count($timePeriod, '.') == 2){
            if(preg_match('/^((\d{4}\.0[1-9]\.(0[1-9]|[12]\d|3[0-1]))|(\d{4}\.1[0-2]\.(0[1-9]|[12]\d|3[0-1])))$/', $timePeriod) === 0) {
                return false;
            }
        } else {
            return false;
        }
        
        return true;
    }
    
     /**
     * 
     * Time period delete 
     * corresponding records will also be deleted from data 
     * @params tpId timeperiod nid 
     * @return boolean true/false
     */
    public function deleteTimeperiodData($tpNId=''){
       
       $conditions = [];
       $conditions = [_TIMEPERIOD_TIMEPERIOD_NID . ' IN ' => $tpNId];
       $tpdata = $this->deleteRecords($conditions);
       
       if($tpdata>0){
            $conditions = [];
            $conditions = [_MDATA_TIMEPERIODNID . ' IN ' => $tpNId];
            $data = $this->Data->deleteRecords($conditions);
            
       }else{
           return false;
       }
    }

}
