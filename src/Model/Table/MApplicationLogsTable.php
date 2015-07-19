<?php  
namespace App\Model\Table;
use App\Model\Entity\MApplicationLog;
use Cake\ORM\Table;

/**
 * MApplicationLogs Model
 */
 
class MApplicationLogsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('m_application_logs');
        $this->primaryKey(_MAPPLICATIONLOG_ID);
        $this->addBehavior('Timestamp'); 
        
         
    }
	
	
   /*
    * save application logs 
    * @ $fieldsArray array the log information 
   */
    
	public function saveLog($fieldsArray = []) {

                $Logs = $this->newEntity();
                $Logs = $this->patchEntity($Logs, $fieldsArray);
                if ($this->save($Logs)) {
                    return 1;
                } else {
                    return 0;
                }

    }
    
	
	
	
	



}