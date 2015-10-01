<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\View\View;

/**
 * Administration Component
 */
class AdministrationComponent extends Component {

    public $Areas = null;
    public $Statuses = null;
    public $ShipmentLocations = null;
    public $Locations = null;
    public $Couriers = null;
    public $ConfigItems = null;
    public $ConfigItemOptions = null;
    public $components = ['Auth', 'CustomFields'];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->Areas = TableRegistry::get('Areas');
        $this->Statuses = TableRegistry::get('Statuses');
        $this->ShipmentLocations = TableRegistry::get('ShipmentLocations');
        $this->Locations = TableRegistry::get('Locations');
        $this->Couriers = TableRegistry::get('Couriers');
        $this->ConfigItems = TableRegistry::get('ConfigItems');
        $this->ConfigItemOptions = TableRegistry::get('ConfigItemOptions');
    }
    
    /**
     * GET Area List
     */
    public function getAreaList($type = 'all', $options = []) {
        
        $query = $this->Areas->find($type, $options);        
        $results = $query->hydrate(false)->all()->toArray();
        
        return $results;
    }
    
    /**
     * GET Statuses List
     */
    public function getStatusList($type = 'all', $fields = [], $conditions = []) {
        
        $query = $this->Statuses->find($type, ['fields' => $fields, 'conditions' => $conditions]);
        $results = $query->hydrate(false)->all()->toArray();
        
        return $results;
    }
    
    /**
     * GET Shipment Statuses List
     */
    public function getShipmentLocationList($type = 'all', $fields = [], $conditions = []) {
        
        $output = [];
        
        $query = $this->ShipmentLocations->find($type, ['fields' => $fields, 'conditions' => $conditions]);
        $results = $query->hydrate(false)->all()->toArray();
        
        foreach($results as $result) {
            $output[$result['id']] = $result;
        }
        
        return $output;
    }
    
    /**
     * GET Statuses List
     */
    public function getLocationList($type = 'all', $fields = [], $conditions = []) {
        
        $output = [];
        
        $query = $this->Locations->find($type, ['fields' => $fields, 'conditions' => $conditions]);
        $results = $query->hydrate(false)->all()->toArray();
        
        foreach($results as $result) {
            $output[$result['id']] = $result;
        }
        
        return $output;
    }
    
    /**
     * GET Statuses List
     */
    public function getCourierList($type = 'all', $fields = [], $conditions = []) {
        
        $output = [];
        
        $query = $this->Couriers->find($type, ['fields' => $fields, 'conditions' => $conditions]);
        $results = $query->hydrate(false)->all()->toArray();
        
        foreach($results as $result) {
            $output[$result['id']] = $result;
        }
        
        return $output;
    }

    /**
     * find method 
     *
     * @param string $type Query Type
     * @param array $options Extra options
     * @return void
     */
    public function find($type, $model, $options = [], $extra = null) {
        $query = $this->{$model}->find($type, $options);
        if (isset($extra['count'])) {
            $data = $query->count();
        }if (isset($extra['first'])) {
            $results = $query->first();
            if(isset($extra['debug']) && $extra['debug'] == true) {
                debug($results);exit;
            }
            $data = $results->toArray();
        } else {
            $results = $query->hydrate(false)->all();
            if(isset($extra['debug']) && $extra['debug'] == true) {
                debug($results);exit;
            }
            $data = $results->toArray();
        }
        return $data;
    }

    /**
     * GET Statuses List with color code details in array
    */
    public function convertStatusIntoArray($data=[]) {
        $returnData = [];
        if(count($data)) {
            foreach($data as $dt) {
                $returnData[$dt['id']] = ['name'=>$dt['name'], 'color_code'=>$dt['color_code']];
            }
        }
        return $returnData;
    }
    
    /**
     * GET custom field options
     * 
     * @param array $data Description
     * @return array FieldOptions
     */
    public function getLocationCustomValues($results) {
        
        $return = [];
        if(!empty($results)) {
            foreach($results as $result) {
                $return[] = [
                    'id' => $result['id'],
                    'value' => $result['value'],
                    'locationCustomFieldId' => $result['location_custom_field_id'],
                ];
            }
        }
        
        return $return;
    }
    
    /**
     * ADD/MODIFY Location
     * 
     * @param array $data Column values
     * @return int last inserted/updated ID
     */
    public function getLocation($conditions = [], $extra = []) {
        
        $return = [];
        $query = $this->Locations->find('all', $options = ['conditions' => $conditions]);
        $query = $query->contain(['LocationCustomFieldValues']);
        $results = $query->hydrate(false)->all()->toArray();
        
        if(!empty($results)) {
            foreach($results as $result) {
                $locationCustomValues = $this->getLocationCustomValues($result['location_custom_field_values']);                
                $return[] = [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'typeId' => $result['type_id'],
                    'code' => $result['code'],
                    'areaId' => $result['area_id'],
                    'address' => $result['address'],
                    'longitude' => $result['longitude'],
                    'latitude' => $result['latitude'],
                    'contact' => $result['contact_person'],
                    'phone' => $result['telephone'],
                    'email' => $result['email'],
                    'statusId' => $result['status_id'],
                    'comments' => $result['comments'],
                    'customValues' => $locationCustomValues,
                ];
            }
        }
        
        return $return;
    }
    
    
    
    /**
     * ADD/MODIFY Location
     * 
     * @param array $data Column values
     * @return int last inserted/updated ID
     */
    public function getLocationListing($conditions = []) {
        $return = [];
        $query = $this->Locations->find('all', $options = ['fields' => ['id', 'code', 'name', 'modifiedBy' => 'ModifiedUsers.username', 'modified'], 'conditions' => $conditions]);
        $query = $query->contain(['ModifiedUsers']);
        $results = $query->hydrate(false)->all()->toArray();
        
        return $results;
    }
    
    /**
     * DELETE location and associated data
     * 
     * @param int $id primary key
     */
    public function deleteLocation($id) {
        if(!empty($id) && is_numeric($id)) {
            $entity = $this->Locations->get($id);
            $this->Locations->delete($entity);
        }
    }
    
    /**
     * ADD/MODIFY Location
     * 
     * @param array $data Column values
     * @return int last inserted/updated ID
     */
    public function saveLocation($result) {
        
        if(!empty($result)) {
            $CustomFieldsData = [];
            $user = $this->Auth->user();

            $LocationData = [
                'name' => $result['name'],
                'type_id' => $result['typeId'],
                'code' => $result['code'],
                'area_id' => $result['areaId'],
                'address' => $result['address'],
                'longitude' => $result['longitude'],
                'latitude' => $result['latitude'],
                'contact_person' => $result['contact'],
                'telephone' => $result['phone'],
                'email' => $result['email'],
                'status_id' => $result['statusId'],
                'comments' => $result['comments'],
                'comments' => $result['comments'],
                'modified_user_id' => $user['id'],
                /*'customValues' => [
                    [
                        'id' => $result['id'],
                        'value' => $result['value'],
                        'locationCustomFieldId' => $result['location_custom_field_id'],
                    ],
                    [
                        'id' => $result['id'],
                        'value' => $result['value'],
                        'locationCustomFieldId' => $result['location_custom_field_id'],
                    ]
                    ],*/
            ];
            
            // UPDATE Locations
            if(isset($result['id'])) {
                $LocationData['id'] = $result['id'];
                $LocationData['created_user_id'] = $user['id'];
            }

            // PREPARE location custom fields data
            //$customValues = $result['customValues'];
            $customValues = [];
            if(!empty($customValues)) {
                foreach($customValues as $customValue) {
                    $CustomFieldsData[] = [
                        'value' => $customValue['value'],
                        'location_custom_field_id' => $customValue['locationCustomFieldId'],
                        'location_id' => $result['id'],
                        'modified_user_id' => $user['id'],
                    ];
                    // UPDATE Custom fields
                    if(isset($customValue['id'])) {
                        $CustomFieldsData[count($CustomFieldsData) - 1]['id'] = $customValue['id'];
                    } // INSERT Custom fields
                    else {
                        $CustomFieldsData[count($CustomFieldsData) - 1]['created_user_id'] = $user['id'];
                    }
                }
            }

            // SAVE Locations
            $lcoationId = $this->Locations->saveRecords($LocationData);
            // SAVE Location custom fields values
            if(!empty($CustomFieldsData)) {
                $this->CustomFields->saveLocationCustomFields($CustomFieldsData);
            }
        }
        
        return true;
    }
    
    /**
     * GET status Config list
     * 
     * @param array $conditions query conditions
     * @return array Status Config List
     */
    public function getStatusConfigList($conditions = []) {
        $query = $this->Statuses->find('all', ['fields' => [ 'id', 'code', 'name', 'colorCode' => 'color_code', 'modifiedBy' => 'ModifiedUsers.username', 'modified'], 'conditions' => $conditions])->contain(['ModifiedUsers']);
        $results = $query->hydrate(false)->all()->toArray();
        return $results;
    }
    
    /**
     * ADD/MODFY status
     * 
     * @param array $data data array
     * @return int Last inserted/modified ID
     */
    public function saveStatus($data) {
        $insertArray = [];
        $insertArray['id'] = $data['id'];
        $insertArray['color_code'] = $data['colorCode'];
        return $this->Statuses->saveRecords($insertArray);
    }
    
    /**
     * GET system configuration details
     * 
     * @param array $conditions Query conditions
     * @param array $extra Extra params
     * @return array Sys config details
     */
    public function getSysConfigDetails($conditions = [], $extra = []) {
        
        $return = [];
        $conditions['ConfigItems.visible'] = 1;
        if(isset($extra['type'])) {
            $conditions['type'] = $extra['type'];
        }
        
        // GET config records
        $query = $this->ConfigItems->find('all', ['fields' => [], 'conditions' => $conditions]);
        $results = $query->hydrate(false)->all()->toArray();
        
        foreach ($results as $result) {
            $options = [];
            if(!empty($result['option_type'])) {
                $query = $this->ConfigItemOptions->find('all', array('fields' => [], 'conditions' => array('option_type' => $result['option_type'], 'visible' => 1)));
                $optionTypes = $query->hydrate(false)->all()->toArray();
                foreach($optionTypes as $optionType) {
                    $options[] = [
                        'id' => $optionType['value'],
                        'name' => $optionType['option'],
                    ];
                }
            }
            
            if ($result['value'] === '' || $result['value'] === null) {
                $value = $result['default_value'];
            } else {
                $value = $result['value'];
            }
            
            $disabled = ($result['editable'] == 0) ? true : false ;
            
            // we need to do this array this way to deal with single select and multi select(checkboxes) conditions
            $return[str_replace(' ', '_', strtolower($result['name']))]['title'] = $result['name'];
            if(strtolower($result['field_type']) == 'checkbox') {
                $value = ($value == 'false') ? false : true ;
            }
            $return[str_replace(' ', '_', strtolower($result['name']))]['inputs'][] = [
                'id' => $result['id'],
                'label' => $result['label'],
                'inputType' => $result['field_type'],
                'name' => $result['code'],
                'value' => $value,
                'disabled' => $disabled,
                'options' => $options,
            ];
        }
        
        // ATTACH status configuration to return array
        $return['status'] = [
            'title' => 'Status',
            'type' => 'customStatus',
            'statusList' => $this->getStatusConfigList()
        ];
        
        return $return;
    }
    
    /**
     * MODIFY system configuration details
     * 
     * @param array $conditions Query conditions
     * @param array $extra Extra params
     * @return boolean true/false
     */
    public function saveSysConfigDetails($results = [], $extra = []) {
        
        $inputArray = [];
        //$results = $this->getSysConfigDetails([], ['type' => 'Config']);
        
        if(isset($results['inputs'])) {
            foreach($results['inputs'] as $field) {
                $inputArray[] = [
                    'id' => (int) $field['id'],
                    'value' => $field['value'],
                ];
            }
        }
        
        // SAVE Config records
        if(!empty($inputArray)) {
            $this->ConfigItems->saveBulkRecords($inputArray);
        }
        
        // SAVE status
        if(isset($results['statusList'])) {
            $insertStatus = [];
            foreach($results['statusList'] as $statusList) {
                $insertStatus[] = [
                    'id' => (int) $statusList['id'],
                    'color_code' => $statusList['colorCode'],
                ];
            }
            $this->Statuses->saveBulkRecords($insertStatus);
        }
        
        return true;
    }
    
}
