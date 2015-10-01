<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;

/**
 * Subscription component
 */
class SubscriptionComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $Subscriptions = null;
    public $components = ['Auth', 'Administration', 'RapidPro.Sms'];
    
    public function initialize(array $config) {
        parent::initialize($config);
        $this->Subscriptions = TableRegistry::get('Subscriptions');
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
     * GET Subscription list
     */
    public function getSubscriptionList($id = null) {
        $conditions = [];
        if(!empty($id)) {
            $conditions = ['Subscriptions.id' => $id];
        }
        
        $results = $this->find('all', 'Subscriptions', ['fields' => [
            'id', 
            'name', 
            'email', 
            'mobile', 
            'alert', 
            'areaId' => 'Subscriptions.area_id', 
            'areaName' => 'Areas.name', 
            'locationId' => 'Subscriptions.location_id', 
            'locationName' => 'Locations.name', 
            'comments', 
            'created', 
            'modified', 
            'CreatedUsers.username', 
            'ModifiedUsers.username'
        ], 'conditions' => $conditions, 'contain' => ['Areas', 'Locations', 'CreatedUsers', 'ModifiedUsers']]);

        foreach($results as &$result) {
            
            $result['areaId'] = (int) $result['areaId'];
            $result['locationId'] = (int) $result['locationId'];
            
            if(isset($result['modified_user']) && isset($result['modified_user']['username'])) {
                $result['modifiedBy'] = $result['modified_user']['username'];
                unset($result['modified_user']);
            }
            if(isset($result['created_user']) && isset($result['created_user']['username'])) {
                $result['createdBy'] = $result['created_user']['username'];
                unset($result['created_user']);
            }
        }
        
        if(!empty($id)) {
            $results = reset($results);
        }
        
        return $results;
    }
    
    /**
     * ADD/MODIFY subscriptions
     * 
     * @param array $data Subscription column data
     * @return boolean/error True/error-code
     */
    public function saveSubscriptions($data) {
        if(isset($data['areaId'])) {
            $data['area_id'] = $data['areaId'];
            unset($data['areaId']);
        }
        if(isset($data['locationId'])) {
            $data['location_id'] = $data['locationId'];
            unset($data['locationId']);
        }
        
        $user = $this->Auth->user();
        
        // CREATE
        if(!isset($data['id'])) {
            $data['createdBy'] = $user['id'];
            $data['modifiedBy'] = $user['id'];
        } // UPDATE
        else {
            $data['modifiedBy'] = $user['id'];
        }
            
        $return = $this->Subscriptions->saveRecords($data);
        return $return;
    }
    
    /**
     * Delete Subscriptions
     * 
     * @param array $conditions Query conditions
     */
    public function deleteSubscription($conditions = []) {
        return $this->Subscriptions->deleteAll($conditions);
    }
    
    /**
     * Send SMS to subscribers
     * 
     * @param int/array $locationId Location Id
     * @param string $message Text to be sent
     * @return boolean true/false
     */
    public function sendSmsToSubscribers($locationId, $message) {
        
        //$query = $this->Subscriptions->find('list', array('fields' => ['id', 'mobile'], 'conditions' => ['location_id IN' => $locationId, 'alert' => 1]));
        $phonesList = $this->Subscriptions->getRecords(['id', 'mobile'], ['location_id IN' => $locationId, 'alert' => 1], 'list');
        
        // Send SMS
        if(!empty($phonesList) && is_array($phonesList)) {
            //$response = $this->Sms->sendSms($phonesList, $message);
        }
    }
    
    /**
     * SUBSCRIBE user when coming from Rapid-pro
     */
    public function processSmsSubscription($locationCode, $phone) {
        // GET locationID from location Code
        $location = $this->Administration->find('all', 'Locations', $options = array('fields' => ['id', 'area_id'], 'conditions' => ['code' => $locationCode]), ['first' => true]);
        if(!empty($location)) {
            $locationId = $location['id'];

            // GET area_id of this location_id
            $areaId = $location['area_id'];

            // GET susbcriber details if exists
            $subscriber = $this->Subscriptions->find('all', array('fields' => ['id', 'alert'], 'conditions' => ['mobile' => $phone]))->first()->toArray();
            if(!empty($subscriber)) {
                $subscriber['alert'] = 1;
            } else {
                $subscriber['mobile'] = $phone;
                $subscriber['area_id'] = $areaId;
                $subscriber['location_id'] = $locationId;
                $subscriber['createdBy'] = 0; // 0 because we are not creating any user. Only subscribers.
                $subscriber['alert'] = 1;
            }
            $subscriberId = $this->Subscriptions->saveRecords($subscriber);
            if(is_numeric($subscriberId) && !empty($subscriberId)) {
                $return = true;
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }
        
        return $return;   
    }
    
    /**
     * GET ship package details
     */
    public function getShipPackageDetails($shipCode) {
        
    }
    
    /**
     * GET ship delivery details
     */
    public function getShipDeliveryDetails($shipCode) {
        
    }
    
    /**
     * GET ship status details
     */
    public function getShipStatusDetails($shipCode) {
        
    }
    
    /**
     * GET ship contact details
     */
    public function getShipContactDetails($shipCode) {
        
    }
    
    /**
     * Process SMS response
     */
    public function processSmsResponse($response) {
        
        $return = false;
        
        $text = $response['text'];
        $flow = $response['flow'];
        $phone = $response['phone'];
        
        $textArray = explode(' ', $text);
        
        switch ($textArray[0]) {
            case 'SUBSCRIBE':
                $locationCode = $textArray[1];
                $return = $this->processSmsSubscription($locationCode, $phone);
                break;
                
            case 'WHAT':
                $shipCode = $textArray[1];
                $return = $this->getShipPackageDetails($shipCode);
                break;
            
            case 'WHEN':
                $shipCode = $textArray[1];
                $return = $this->getShipDeliveryDetails($shipCode);
                break;
                
            case 'WHERE':
                $shipCode = $textArray[1];
                $return = $this->getShipStatusDetails($shipCode);
                break;
            
            case 'WHO':
                $shipCode = $textArray[1];
                $return = $this->getShipContactDetails($shipCode);
                break;
            
            case 'HOW':
                $shipCode = $textArray[1];
                $return = $this->getShipLocationDetails($shipCode);
                break;
                
            case 'COLLECTED':
                // coming soon...
                break;
            
            case 'DELIVERED':
                // coming soon...
                break;
                
            case 'CONFIRMED':
                // coming soon...
                break;
            
            case 'HELP':
                $return = $this->listOfCommands();
                break;
        }
        
        return $return;
    }
}
