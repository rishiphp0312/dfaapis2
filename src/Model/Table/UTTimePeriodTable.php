<?php  
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Table;


/**
 * UTTimeperiod Model
 */
class UTTimeperiodTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Timeperiod');
        $this->primaryKey('TimePeriod_NId');
        $this->addBehavior('Timestamp');
    }


    /**
     * getTimePeriodById method
     * @param array $id get the records on basis of passed id . {DEFAULT : null}
     * @param array $fields columns which will be fetched from table. {DEFAULT : empty}
	 * @param type  by default its value will be all user can pass  first or count also
     * @return void
     */
	 
    public function getTimePeriodById($id = null, array $fields, $type = 'all' )
    {
        $options = [];

        if(isset($fields) && !empty($fields))
           $options['fields'] = $fields;
	   
	   
		if(isset($id) && !empty($id))        
          $options['conditions'] = array('UTTimeperiod.TimePeriod_NId'=>$id);

         // Find all the articles.
         // At this point the query has not run.
         $query = $this->find($type, $options);

         // Calling execute will execute the query
         // and return the result set.
         $results = $query->all();

         // Once we have a result set we can get all the rows
         $data = $results->toArray();
         return $data;
    }


    /**
    *  getDataByTimeperiod method
    *  @param $timeperiodvalue The value on which you will get details on baisis of  the timeperiodvalue. {DEFAULT : empty}
    *  @return  array
    */
	 
    public function getDataByTimeperiod($timeperiodvalue)
    {
       
		if(!empty($timeperiodvalue))       
		$timperioddetails = $this->find()->Select(['TimePeriod','Periodicity','TimePeriod_NId','EndDate','StartDate'])->where(['TimePeriod'=>$timeperiodvalue])->toArray();
	    else
		$timperioddetails = $this->find()->where()->all()->toArray();
       
		return $timperioddetails;

    }
	
	
	/**
     * savesingleTimePeriod method 
     * @param  $timeperiodvalue timeperiod which will be saved in database if exists already nothing will be happened. {DEFAULT : empty}
     * @param  $Periodicity is optional if it is passed then fine else by default its value will be A {DEFAULT : A}
     * @return void
    */
		
	public function savesingleTimePeriod($timeperiodvalue,$Periodicity='A'){
	
		if(isset($timeperiodvalue) && !empty($timeperiodvalue)){            
			
			//numrows if numrows >0 then record already exists else insert new row
		    $numrows = $this->find()->where(['TimePeriod'=>$timeperiodvalue])->count();
			
			if(isset($numrows) &&  $numrows ==0){  // new record
			
				$data = $this->newEntity();
				$data->TimePeriod     = $timeperiodvalue;
				$numberofdays_dec     = cal_days_in_month(CAL_GREGORIAN, 12, date('Y')); // 31
                //echo "There were {$number} days in August 2003";
				$data->StartDate      = date('Y').'-01-01';
				$data->EndDate        = date('Y').'-12-'.$numberofdays_dec;
				$data->Periodicity    = $Periodicity;
				if($this->save($data)){
					 $msg['id']      = $TimePeriod_NId = $this->id;   // Record saved new id returned 
					 $msg['success'] = 'Record saved successfully!!';
					 return $msg;
				}else{
					 return $msg['error']='Error while saving details';  
				}			
			}else{                                   // Already exists
				     return  $msg['error']='Error while saving details';				
			}
		}else{
				     return $msg['error']='No time period value ';			
		}
	}// end of function 
	

	
		
	/**
    * 
	* deletesingleTimePeriod method       
    * @param  $timeperiodvalue timeperiod which will be saved in database if exists  will be deleted. {DEFAULT : empty}
    * @return void
    *
	*/
		
	public function deletesingleTimePeriod($timeperiodvalue){
	
		if(isset($timeperiodvalue) && !empty($timeperiodvalue)){            
	
        	//deleteentity  checks whether record exists or not 
		    $deleteentity = $this->find()->where(['TimePeriod'=>$timeperiodvalue])->first();
			if(isset($deleteentity) &&  !empty($deleteentity)){  
			
				if($result = $this->delete($deleteentity)){
					$msg['success']       = 'Record deleted successfully!!';
				    return $msg;
				}else{
					return $msg['error'] = 'Error while deletion';  
				}			
			}else{                                   // Already exists
				    return $msg['error'] = 'Entity not found';				
			}
		}else{
				    return $msg['error'] = 'No time period value ';			
		}
	}// end of function 

}