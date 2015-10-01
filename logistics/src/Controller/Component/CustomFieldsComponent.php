<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;

/**
 * CustomFields component
 */
class CustomFieldsComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $LocationCustomFields = null;
    public $LocationCustomFieldOptions = null;
    public $LocationCustomFieldValues = null;
    
    public function initialize(array $config) {
        parent::initialize($config);
        $this->LocationCustomFields = TableRegistry::get('LocationCustomFields');
        $this->LocationCustomFieldOptions = TableRegistry::get('LocationCustomFieldOptions');
        $this->LocationCustomFieldValues = TableRegistry::get('LocationCustomFieldValues');
    }
    
    /**
     * GET custom field options
     * 
     * @param array $data Description
     * @return array FieldOptions
     */
    public function getCustomFieldsOptions($results) {
        
        $return = [];
        if(!empty($results)) {
            foreach($results as $result) {
                if($result['visible'] == '1') {
                    $return[] = [
                        'id' => $result['id'],
                        'value' => $result['value'],
                        'order' => $result['order'],
                    ];
                }
            }
        }
        
        return $return;
    }
    
    /**
     * GET Custom fields - Locations
     * 
     * @param array $conditions query conditions
     */
    public function getLocationCustomFields($conditions = []) {
        
        $return = [];
        $query = $this->LocationCustomFields->find('all', ['fields' => [], 'conditions' => $conditions])->contain('LocationCustomFieldOptions');
        $results = $query->hydrate(false)->all()->toArray();
        
        if(!empty($results)) {
            foreach($results as $result) {
                if($result['visible'] == '1') {
                    // Prepare custom field options
                    $locationCustomFields = $this->getCustomFieldsOptions($result['location_custom_field_options']);
                    $return[] = [
                        'id' => $result['id'],
                        'name' => $result['name'],
                        'order' => $result['order'],
                        'type' => $result['type'],
                        'options' => $locationCustomFields
                    ];
                }
            }
        }
        
        return $return;
    }
    
    /**
     * ADD/MODIFY Custom fields - Locations
     */
    public function saveLocationCustomFields($data) {
        $this->LocationCustomFields->saveBulkRecords($data);
    }
}
