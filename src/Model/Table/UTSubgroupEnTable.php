<?php  
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Table;


/**
 * UTSubgroupEnTable Model
 */
class UTSubgroupEnTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Subgroup_en');
        $this->primaryKey('Subgroup_NId');
        $this->addBehavior('Timestamp');
    }



    /**
    *  getDataBySubgroupName method
    *  @param $SubgroupName The value on which you will get details on basis of  the Subgroup. {DEFAULT : empty}
    *  @return  array
    */
	 
    public function getDataBySubgroupName($SubgroupName)
    {        
		if(!empty($SubgroupName))       
		$Subgroup_Namedetails = $this->find('all')->where(['Subgroup_Name'=>$SubgroupName])->all()->toArray();
	    else
		$Subgroup_Namedetails = $this->find()->where()->all()->toArray();        		   
		return $Subgroup_Namedetails;
    }
	
	
	/**
     * savesingleSubgroup method 
     * @param  $Subgroup_Name Subgroup Name which will be saved in database if exists already nothing will be happened.
     * @param  $Subgroup_Type the type which it belongs to 
     * @param  $Subgroup_Order is optional if it is passed then fine else by default its value will be A {DEFAULT : 1}
     * @param  $Subgroup_Global is optional if it is passed then fine else by default its value will be A {DEFAULT : 0}
     * @return void
    */
		
	public function savesingleSubgroup($Subgroup_Name,$Subgroup_Type,$Subgroup_Order =1,$Subgroup_Global=0){
	
		if(isset($Subgroup_Name) && !empty($Subgroup_Name)){            
			
			//numrows if numrows >0 then record already exists else insert new row
		    $numrows = $this->find()->where(['Subgroup_Name'=>$Subgroup_Name])->count();
			
			if(isset($numrows) &&  $numrows ==0){  // new record
			
				$data = $this->newEntity();
				$data->Subgroup_GId     = uniqid();
				$data->Subgroup_Name    = $Subgroup_Name;
				$data->Subgroup_Global  = date('Y-m-d');
				$data->Subgroup_Type    = date('Y-m-d');
				$data->Subgroup_Order   = date('Y-m-d');
				
				if($this->save($data)){
					// $msg['id']      = $TimePeriod_NId = $this->id;   // Record saved new id returned 
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
	* deletesingleSubgroup method       
    * @param  $Subgroupvalue contains  Subgroup name  which will be deleted from database if exists 
    * @return void
    *
	*/
		
	public function deletesingleSubgroup($Subgroupvalue){
	
		if(isset($Subgroupvalue) && !empty($Subgroupvalue)){            
	
        	//deleteentity  checks whether record exists or not 
		    $deleteentity = $this->find()->where(['Subgroup_Name'=>$Subgroupvalue])->first();
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