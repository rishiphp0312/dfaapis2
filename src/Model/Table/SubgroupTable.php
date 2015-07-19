<?php  
namespace App\Model\Table;

use App\Model\Entity\Subgroup;
use Cake\ORM\Table;


/**
 * SubgroupTable Model
 */
class SubgroupTable extends Table
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
        $this->primaryKey(_SUBGROUP_SUBGROUP_NID);
        $this->addBehavior('Timestamp');
    }


	 /**
     * getDataByIds method
     * @param array $id The WHERE conditions with ids only for the Query. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
	 
    public function getDataByIds($ids = null, array $fields, $type  ){
        
		$options = [];
		
        if(isset($ids) && !empty($ids))
        $options['conditions'] = [_SUBGROUP_SUBGROUP_NID.' IN'=>$ids];
	    
		if(isset($fields) && !empty($fields))
         $options['fields'] = $fields;	
	 
	   	if(empty($type))
         $type = 'all';	

		
		if($type=='list'){
			 $options['keyField']   = $fields[0];	    		
             $options['valueField'] = $fields[1];	  
  		     $query = $this->find($type, $options);
		}else{
    		  $query = $this->find($type, $options);
		}
		
        $results = $query->hydrate(false)->all();	
        
		$data = $results->toArray();
		pr($data);
		die;
         
        // Once we have a result set we can get all the rows
		
        return $data;
    }


    /**
     * getDataByParams method     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions){
        
		$options = [];
		
        if(!empty($fields))
           $options['fields']     = $fields;
        if(!empty($conditions))
           $options['conditions'] = $conditions;
	   
        $query = $this->find('all', $options);
		
        $results = $query->hydrate(false)->all();
		// Once we have a result set we can get all the rows
        $data = $results->toArray();
        return $data;

    }

    /**
    *  getDataBySubgroupName method
    *  @param $SubgroupName The value on which you will get details on basis of  the Subgroup. {DEFAULT : empty}
    *  @return  array
    */
	 
    public function getDataBySubgroupName($SubgroupName)
    {        
	    $Subgroup_Namedetails =array();
		if(!empty($SubgroupName))       
		$Subgroup_Namedetails = $this->find('all')->where([_SUBGROUP_SUBGROUP_NAME=>$SubgroupName])->hydrate(false)->first();
	   		   
		return $Subgroup_Namedetails;
    }
	
	
	    /**
     * deleteByIds method
     * @param array $ids it can be one or more to delete the Subgroup  rows . {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null){
        
		$result = $this->deleteAll([_SUBGROUP_SUBGROUP_NID.' IN' => $ids]);
		if($result>0)
		return $result;
        return 0;
    }

        
    
	/**
     * deleteByParams method
     *
     * @param array $conditions on the basis of which record will be deleted . 
     * @return void
    */
    
	public function deleteByParams(array $conditions){
       // pr($conditions);die;
		$result = $this->deleteAll($conditions);
		if($result>0)
			return $result;
        return 0;
    }
	
	/**
    * 
	* deleteBySubgroupName method       
    * @param  $Subgroupvalue Subgroup name   if exists  will be deleted. 
    * @return void
    *
	*/
		
	public function deleteBySubgroupName($Subgroupvalue){
		
		if(isset($Subgroupvalue) && !empty($Subgroupvalue)){            
	
        	//deleteentity  checks whether record exists or not 
		    $deleteentity = $this->find()->where([_SUBGROUP_SUBGROUP_NAME=>$Subgroupvalue])->first();
			if(isset($deleteentity) &&  !empty($deleteentity)){  
			
				if($result = $this->delete($deleteentity)){
						return 1;
					}else{
						return 0;  
				}			
			}else{                                   // Already exists
				    return 0; 	
			}
		}else{
				    return 0; 		
		}
	}// end of function 



	
	
	/**
    * 
	* insertData method       
    * @param  $fieldsArray contains  posted data 
    * @return void
    *
	*/
	
	public function insertData($fieldsArray){
	
	    $Subgroup_Name = $fieldsArray[_SUBGROUP_SUBGROUP_NAME];		
	   
        $conditions = array();
	    
		if(isset($fieldsArray[_SUBGROUP_SUBGROUP_NAME]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NAME]))            
		$conditions[_SUBGROUP_SUBGROUP_NAME] = $fieldsArray[_SUBGROUP_SUBGROUP_NAME];		
		
		if(isset($fieldsArray[_SUBGROUP_SUBGROUP_NID]) && !empty($fieldsArray[_SUBGROUP_SUBGROUP_NID]))            
		$conditions[_SUBGROUP_SUBGROUP_NID.' !='] = $fieldsArray[_SUBGROUP_SUBGROUP_NID];
	
	    //pr($conditions);die;
		
		
		if(isset($Subgroup_Name) && !empty($Subgroup_Name)){            
			
			//numrows if numrows >0 then record already exists else insert new row
		    $numrows = $this->find()->where($conditions)->count();
		
			if(isset($numrows) &&  $numrows ==0){  // new record			   
				
				if(empty($fieldsArray[_SUBGROUP_SUBGROUP_ORDER])){
					
					$query         = $this->find();
					$results       = $query->select(['max' => $query->func()->max(_SUBGROUP_SUBGROUP_ORDER)])->first();
					$ordervalue    = $results->max;
					$maxordervalue = $ordervalue+1;
					$fieldsArray['Subgroup_Order'] = $maxordervalue;	
				}
			   
                //Create New Entity
                $Subgroup = $this->newEntity();
				//		pr( $Subgroup);
                //Update New Entity Object with data
                $Subgroup = $this->patchEntity($Subgroup, $fieldsArray);
				
				if ($this->save($Subgroup)) {
					return 1;
				}else{
					return 0;					
				}			
			}else{         // Subgroup Already exists
				    return 0;							
			}
		}else{
				    return 0;					
		}
        	
	}// end of function 
	

}