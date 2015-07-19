<?php  
namespace App\Model\Table;

use App\Model\Entity\MSystemConfirguration;
use Cake\ORM\Table;
/**
 * MSystemConfirgurationsTable Model
 *
 */
class MSystemConfirgurationsTable extends Table {

	/**
     * Initialize method     *
     * @param array $config The configuration for the Table.
     * @return void
     */
     public function initialize(array $config)
	{
        $this->table('m_system_confirgurations');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }
	

	public function findByKey($key='')	
	{
		$config_value = false;
		$options =[];
		
		if(isset($key) && !empty($key)) {
		    $options['conditions'] = array('key_name'=>$key);
		}	
		
		
		if ($key != '') {
			
	   
			$MSystemConfirgurations = $this->find('all',$options);
		    $config_value = $MSystemConfirgurations->hydrate(false)->first();            
			$config_value = !empty($config_value) ? $config_value['key_value'] : false;
		}
		
		return $config_value;
	}
	
	

}
