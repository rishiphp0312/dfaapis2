<?php  
namespace App\Model\Table;
use App\Model\Entity\User;
use Cake\ORM\Table;
use Cake\I18n\Time;

/**
 * User Model
 */
 
class UsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('m_users');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    
        $this->belongsToMany('MDatabaseConnections', 
        [
            'targetForeignKey' => 'db_id',
            'foreignKey' => 'user_id',
             'joinTable' => 'r_user_databases',
            //  'through' => 'RUserDatabases',
        ]);
         
    }
	
	
	/*
	*
	* to update Last LoggedIn of  user 
	*
	*/
    
    function updateLastLoggedIn($fieldsArray = [] ){
              
				$User = $this->newEntity();
				$fieldsArray[_USER_LASTLOGGEDIN] = date('Y-m-d H:i:s');  
                $User = $this->patchEntity($User, $fieldsArray);
				if ($this->save($User)) {
                    return $User->id;
                } else {
                    return 0;
                }
    }
	
	
	/*
	*
	*getDataByParams used get user details 
	*
	*/
	
	 public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        
		$options = [];
        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);
        $query = $this->find($type, $options);
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
    }
	
	/*
	*
	*get User details  with email ,id and name in auto complete list 
	*
	*/
	
	function  getAutoCompleteDetails()
	{    		
		$options['fields']     = [_USER_ID,_USER_EMAIL,_USER_NAME];
		$query = $this->find('all', $options);		
        $results = $query->hydrate(false)->all();		
        $data = $results->toArray();		
	    return  $data;
		
	}
	
	
	/*
	*
	*checkEmailExists is the function to check uniqueness of email
	*/
	
	function  checkEmailExists($email=null,$userId=null)
	{		
        if(!empty($email))		
		$conditions[_USER_EMAIL] = $email;
		
		if(!empty($userId))			
		$conditions[_USER_ID.' !='] = $userId;
		
		$options['conditions'] =$conditions;
		//$options['fields']     = [_USER_ID];
		$query = $this->find('all', $options);		
        $results = $query->hydrate(false)->count();		
	    return  $results;	
		
	}		
	
	
	/*
	*
	*function to check the status of activation link 
	*/
	
	function  checkActivationLink($userId=null)
	{   	
		if(!empty($userId))	{
			$conditions[_USER_ID]     = $userId;	
			$conditions[_USER_STATUS] = _INACTIVE;	
		}		
		
		$options['conditions'] = $conditions;
		$options['fields']   = [_USER_STATUS];
		$results = $this->find('all', $options)->hydrate(false)->count();		
	    return  $results;	
		
	}		
		
	
	
	
	/*
	*
	* function to add/modify user
	* @fieldsArray is the posted data  
	*/
	
    function addModifyUser($fieldsArray = [] ){
		
			$User = $this->newEntity();
			$User = $this->patchEntity($User, $fieldsArray);
			if ($this->save($User)) {
				return $User->id;
			} else {
				return 0;
			}           
	}
	
	
	/*
	*
	* function to modify user on passed conditions
	* @ fieldsArray fields to be updated 
	* @ conditions  to be passed to updated record 
	*/
	
	
	 public function updateDataByParams($fieldsArray = [], $conditions = []) {
        
		$User = $this->get($conditions);
        $User = $this->patchEntity($User, $fieldsArray);
        if ($this->save($User)) {
            return 1;
        } else {
            return 0;
        }
    }
	
    
    
    /**
     * deleteByIds method to delete records using id
     *
     * @param array $ids of user   . {DEFAULT : null}
     * 
     */
	 
    public function deleteByIds($ids = null) {

        $result = $this->deleteAll([_USER_ID . ' IN' => $ids]);

        return $result;
    }
	
	/**
     * getdatabaseList method to get the database  list of logged user  
     *
     * @param array $userId is user Id  . {DEFAULT : null}
     * 
     */
	public function getdatabaseList($userId) {

        $result = $this->find()->where(['id' => $userId])->contain(['MDatabaseConnections'], true)->hydrate(false)->all()->toArray();

        return $result;
    }
	
	
	
	
	
    
    
  

}