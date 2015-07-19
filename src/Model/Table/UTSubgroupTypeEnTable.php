<?php  
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Table;


/**
 * UTSubgroupTypeEnTable Model
 */
class UTSubgroupTypeEnTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_Subgroup_type_en');
        $this->primaryKey('Subgroup_Type_NId');
        $this->addBehavior('Timestamp');
    }



    /**
    *  getDataBySubgroupTypeName method
    *  @param $Subgroup_Type_Name The value on which you will get details on basis of  the Subgroup type name. {DEFAULT : empty}
    *  @return  array
    */
	 
    public function getDataBySubgroupTypeName($Subgroup_Type_Name)
    {        
		if(!empty($Subgroup_Type_Name))       
		$Subgroup_Namedetails = $this->find('all')->where(['Subgroup_Type_Name'=>$Subgroup_Type_Name])->all()->toArray();
	    else
		$Subgroup_Namedetails = $this->find()->where()->all()->toArray();        		   
		return $Subgroup_Namedetails;
    }
	
	
	/**
     * savesingleSubgroupTypeName method 
     * @param  $Subgroup_Type_Name Subgroup  type Name which will be saved in database if exists already nothing will be happened.
     * @param  $Subgroup_Type_Order is optional  {DEFAULT : 1}
     * @param  $Subgroup_Type_Global is optional  {DEFAULT : 0}
     * @return void
    */
		
	public function savesingleSubgroupTypeName($Subgroup_Type_Name,$Subgroup_Type_Order =1,$Subgroup_Type_Global=0){
	
		if(isset($Subgroup_Type_Name) && !empty($Subgroup_Type_Name)){            
			
			//numrows if numrows >0 then record already exists else insert new row
		    $numrows = $this->find()->where(['Subgroup_Type_Name'=>$Subgroup_Type_Name])->count();
			
			if(isset($numrows) &&  $numrows ==0){  // new record
			
				$data = $this->newEntity();
				$data->Subgroup_Type_GID     = uniqid();
				$data->Subgroup_Type_Name    = $Subgroup_Type_Name;
				$data->Subgroup_Type_Global  = $Subgroup_Type_Global;
				$data->Subgroup_Type_Order   = $Subgroup_Type_Order;
				
				if($this->save($data)){
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
	* deletesingleSubgroupType method       
    * @param  $Subgroup_Type_Name contains  Subgroup type  name  which will be deleted from database if exists 
    * @return void
    *
	*/
		
	public function deletesingleSubgroupType($Subgroup_Type_Name){
	
		if(isset($Subgroup_Type_Name) && !empty($Subgroup_Type_Name)){            
	
        	//deleteentity  checks whether record exists or not 
		    $deleteentity = $this->find()->where(['Subgroup_Type_Name'=>$Subgroup_Type_Name])->first();
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