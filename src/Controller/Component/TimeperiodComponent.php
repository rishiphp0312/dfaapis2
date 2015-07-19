<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Time period Component
 */
class TimeperiodComponent extends Component
{

    
	public $TimeperiodObj = NULL;

    public function beforeFilter()
    {
        $this->TimeperiodObj = TableRegistry::get('TimePeriod');
    }
	
	
	
	/**
     * getDataByIds method
     *
     * @param array $ids the ids can be multiple or single to get filtered records . {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @param  $type the the type of list user needs it can be list or first or count . {DEFAULT : all}
     * @return void
    */
    public function getDataByIds($ids, $fields=[] , $type)
	{ 
        return $this->TimeperiodObj->getDataByIds($ids, $fields, $type);
    }


    /**
     * getDataByParams method
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields,array $conditions)
	{
        return $this->TimeperiodObj->getDataByParams($fields,$conditions);
    }
	
	/**
     * @param $timeperiodvalue the timeperiod value according to which records will be filtered .
     * @param $periodicity the periodicity value will be optional .
     * @return void
    */
	 
    public function getDataByTimeperiod($timeperiodvalue,$periodicity ='')
	{      
        return $this->TimeperiodObj->getDataByTimeperiod($timeperiodvalue,$periodicity );

    }
	


    /**
     * deleteByIds method
     *
     * @param  $ids the ids which needs to be deleted . {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null)
	{
        return $this->TimeperiodObj->deleteByIds($ids);
    }


    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = [])
	{
        return $this->TimeperiodObj->deleteByParams($conditions);
    }

	
	/**
     * @param $timeperiodvalue the timeperiod value which will be deleted .
     * @return void
    */
	 
    public function deleteByTimePeriod($timeperiodvalue)
	{   
        return $this->TimeperiodObj->deleteByTimePeriod($timeperiodvalue);

    }
	

    /**
     * insertUpdateDataTimeperiod method to save or update data 
     *
     * @param array $fieldsArray Fields to insert or update  with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertUpdateDataTimeperiod($fieldsArray = [])
	{
        return $this->TimeperiodObj->insertData($fieldsArray);
    }

    
   
	


 


}
