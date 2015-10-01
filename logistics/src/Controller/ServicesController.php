<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;
use Cake\Network\Email\Email;

/**
 * Services Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class ServicesController extends AppController {

    //Loading Components
    public $components = ['Auth', 'Common', 'UserCommon', 'Package', 'Shipment', 'Administration', 'Items', 'Reports', 'Subscription', 'CustomFields', 'Area', 'RapidPro.Sms','Courier'];

    public function initialize() {
        parent::initialize();
        $this->session = $this->request->session();
    }

    public function beforeFilter(Event $event) {

        parent::beforeFilter($event);
        $this->Auth->allow();
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function serviceQuery($case = null, $urlParam = null) {

        $this->autoRender = false;
        $this->autoLayout = false;
        $returnData = [];
        $ignoreCases = [500, 402, 401];

        // Unauthorized if user is not logged-in
        if (!$this->Auth->user() && !in_array($case, $ignoreCases)) {
            $this->Common->sendResponseHeader(401);
        }

        // Set User Roles in session
        $this->setRolesInSessions();
        $sessionPermissions = $this->session->read('permissions');

        try {
            switch ($case):

                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ TEST CASE
                case 1:
                    //$permissions = $this->Administration->getSysConfigDetails([], ['type' => 'Config']);
                    //$permissions = $this->session->read('permissions');
                    $permissions = $this->Subscription->find('all', 'Subscriptions', ['fields' => [
                        'id', 'name', 'email', 'mobile', 'alert', 'area_id', 'location_id', 'comments', 'created', 'modified', 'CreatedUsers.username', 'ModifiedUsers.username'
                    ], 'contain' => ['CreatedUsers', 'ModifiedUsers']]);
                    pr($permissions);
                    exit;
                    break;

                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SHIPMENT (10x)
                //-- GET all packages
                case 101:
                    if ($this->request->is('post')):
                        $shipCode = (isset($_POST['shipCode'])) ? $_POST['shipCode'] : '';
                        $result = $this->Package->listAllPackages($shipCode);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'packagesList';
                    endif;
                    break;

                //-- CHECK login uniqueness
                case 102:
                    if ($this->request->is('post')) {
                        $login = (isset($_POST['login'])) ? $_POST['login'] : '';
                        $userId = (isset($_POST['userId'])) ? $_POST['userId'] : '';
                        if (!empty($login)) {
                            $results = $this->UserCommon->checkUsernameExists($login, $userId);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $results;
                            $returnData['responseKey'] = 'loginCheck';
                        } else {
                            $returnData['errCode'] = _ERR105;
                        }
                    }
                    break;

                //-- GET all items list for package
                case 103:
                    $results = $this->Items->getPackageItemsList();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $results;
                    $returnData['responseKey'] = 'itemsList';
                    break;

                //-- GET package code generated
                case 104:
                    if (true):
                        $results = $this->Package->getPackageCode($this->request->data);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $results;
                            $returnData['responseKey'] = 'pkgCodeDetails';
                        }
                    endif;
                    break;

                //-- ADD/MODIFY package details  
                case 105:
                    if ($this->request->is('post')) {

                        // Check Permissions
                        if (empty($this->request->data['packageId']) && $sessionPermissions['SHIPMENTS']['PACKAGES']['C'] == false) // ADD
                            $this->Common->sendResponseHeader(403);
                        if (!empty($this->request->data['packageId']) && $sessionPermissions['SHIPMENTS']['PACKAGES']['U'] == false) // EDIT
                            $this->Common->sendResponseHeader(403);

                        $result = $this->Package->savePackageDetails($this->request->data);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }
                    break;

                //-- LIST shipment
                case 106:
                    if ($this->request->is('post')):
                        $result = $this->Shipment->getShipmentList();
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'shipmentList';
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- ADD/MODIFY shipment
                case 107:
                    if ($this->request->is('post')):
                        $fieldsArray['id'] = isset($this->request->data['id']) ? $this->request->data['id'] : null;

                        // Check Permissions
                        if (empty($fieldsArray['id']) && $sessionPermissions['SHIPMENTS']['SHIPMENT']['C'] == false) // ADD
                            $this->Common->sendResponseHeader(403);
                        if (!empty($fieldsArray['id']) && $sessionPermissions['SHIPMENTS']['SHIPMENT']['U'] == false) // EDIT
                            $this->Common->sendResponseHeader(403);

                        $fieldsArray['shipmentCode'] = isset($this->request->data['shipmentCode']) ? $this->request->data['shipmentCode'] : null;
                        $fieldsArray['shipFrom'] = isset($this->request->data['shipFrom']) ? $this->request->data['shipFrom'] : null;
                        $fieldsArray['deliveryPoint'] = isset($this->request->data['deliveryPoint']) ? $this->request->data['deliveryPoint'] : null;
                        $fieldsArray['finalDeliveryPoint'] = isset($this->request->data['finalDeliveryPoint']) ? $this->request->data['finalDeliveryPoint'] : null;

                        if (empty($fieldsArray['shipmentCode']) || empty($fieldsArray['shipFrom']) || empty($fieldsArray['finalDeliveryPoint']['shipTo']['locationId']) || empty($fieldsArray['shipFrom']['locationId']) || empty($fieldsArray['finalDeliveryPoint']['courierId']) || empty($fieldsArray['finalDeliveryPoint'])) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        }

                        $result = $this->Shipment->addShipment($fieldsArray);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;
                    break;

                //-- GET shipment details
                case 108:
                    if (true):
                        $id = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        $code = isset($this->request->data['shipmentCode']) ? $this->request->data['shipmentCode'] : null;
                        if (empty($id) && empty($code)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        }
                        $result = $this->Shipment->getShipment($id, $code);
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'shipmentDetails';
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- AUTO-GENERATED shipment Code
                case 110:
                    if ($this->request->is('post')):
                        $result = $this->Shipment->getAutoGeneratedCode();
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'shipmentCode';
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- DELETE shipment
                case 111:
                    if ($this->request->is('post')):

                        // Permissions
                        if ($sessionPermissions['SHIPMENTS']['SHIPMENT']['D'] == false)
                            $this->Common->sendResponseHeader(403);

                        $id = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        if (empty($id)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        }
                        $result = $this->Shipment->deleteShipment($id);
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- DELETE package details and its correpsonding data 
                case 112:
                    if (true):
                        // Permissions
                        if ($sessionPermissions['SHIPMENTS']['PACKAGES']['D'] == false)
                            $this->Common->sendResponseHeader(403);

                        $pkgId = (isset($_POST['packageId'])) ? $_POST['packageId'] : '';
                        $result = $this->Package->deletePackage($pkgId);
                        if ($result == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            // Not deleted due server error
                            $returnData['errCode'] = _ERR100;
                        }
                    endif;
                    break;

                //-- GET specific  package details   
                case 113:
                    if ($this->request->is('post')):
                        $packageCode = (isset($this->request->data['packageCode'])) ? $this->request->data['packageCode'] : '';
                        $result = $this->Package->getPackageDetails($packageCode);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'packageDetails';
                    endif;
                    break;


                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ FREIGHT (20x)
                //-- GET deliveries/confirmations details
                case 201:
                    if ($this->request->is('post')):
                        $deliveryId = (isset($_POST['deliveryId'])) ? $_POST['deliveryId'] : '';
                        $result = $this->Shipment->getShipmentPackageDetails($deliveryId);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'deliveryDetails';
                    endif;
                    break;

                //-- 
                case 202:

                    break;

                //-- UPDATE deliveries
                case 203:
                    if ($this->request->is('post')) {

                        // Permissions
                        if ($sessionPermissions['FREIGHT']['DELIVERIES']['U'] == false)
                            $this->Common->sendResponseHeader(403);

                        $fieldsArray['shipmentId'] = isset($this->request->data['shipmentId']) ? $this->request->data['shipmentId'] : null;
                        $fieldsArray['sequence'] = isset($this->request->data['sequenceNumber']) ? $this->request->data['sequenceNumber'] : null;
                        $fieldsArray['id'] = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        $fieldsArray['statusId'] = isset($this->request->data['statusId']) ? $this->request->data['statusId'] : null;
                        $fieldsArray['statusName'] = isset($this->request->data['statusName']) ? $this->request->data['statusName'] : null;
                        $fieldsArray['toLocationId'] = isset($this->request->data['toLocationId']) ? $this->request->data['toLocationId'] : null;
                        $fieldsArray['shipmentCode'] = isset($this->request->data['shipmentCode']) ? $this->request->data['shipmentCode'] : null;
                        $fieldsArray['deliveryLatitude'] = isset($this->request->data['latitude']) ? $this->request->data['latitude'] : null;
                        $fieldsArray['deliveryLongitude'] = isset($this->request->data['longitude']) ? $this->request->data['longitude'] : null;
                        $fieldsArray['deliveryComments'] = isset($this->request->data['deliveryComments']) ? $this->request->data['deliveryComments'] : null;

                        if (!empty($_FILES)) {
                            $user = $this->Auth->user();
                            $allowedExtensions = ['jpg', 'jpg2'];
                            $extraParam['dest'] = _TMP_PATH;
                            $extraParam['newFileName'] = _ATTACHMENT_TYPE_DELIVERY . '_' . date('Y-m-d-h-i-s', time()) . '_' . $user['id'] . '_' . rand(25, 222569);
                            $filePaths = $this->Common->processFileUpload($_FILES, $allowedExtensions, $extraParam);
                            if (isset($filePaths['error'])) {
                                $returnData['error'] = $filePaths['error'];
                            }
                        } else {
                            $filePaths[0] = null;
                        }

                        if (!isset($returnData['error'])) {
                            $returnData = $this->Common->saveDeliveryAndConfirmations($fieldsArray, $filePaths[0]);
                        }

                        if (isset($returnData['error'])) {
                            $returnData['errCode'] = $returnData['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;

                //-- UPDATE confirmations
                case 204:
                    if ($this->request->is('post')) {

                        // Permissions
                        if ($sessionPermissions['FREIGHT']['CONFIRMATIONS']['U'] == false)
                            $this->Common->sendResponseHeader(403);

                        $fieldsArray['shipmentId'] = isset($this->request->data['shipmentId']) ? $this->request->data['shipmentId'] : null;
                        $fieldsArray['sequence'] = isset($this->request->data['sequenceNumber']) ? $this->request->data['sequenceNumber'] : null;
                        $fieldsArray['id'] = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        $fieldsArray['statusId'] = isset($this->request->data['statusId']) ? $this->request->data['statusId'] : null;
                        $fieldsArray['statusName'] = isset($this->request->data['statusName']) ? $this->request->data['statusName'] : null;
                        $fieldsArray['toLocationId'] = isset($this->request->data['toLocationId']) ? $this->request->data['toLocationId'] : null;
                        $fieldsArray['shipmentCode'] = isset($this->request->data['shipmentCode']) ? $this->request->data['shipmentCode'] : null;
                        $fieldsArray['packageDetails'] = isset($this->request->data['packageDetails']) ? $this->request->data['packageDetails'] : null;
                        $fieldsArray['confirmationComments'] = isset($this->request->data['confirmationComments']) ? $this->request->data['confirmationComments'] : null;

                        if (!empty($_FILES)) {
                            $user = $this->Auth->user();
                            $allowedExtensions = ['jpg', 'jpg2'];
                            $extraParam['dest'] = _TMP_PATH;
                            $extraParam['newFileName'] = _ATTACHMENT_TYPE_CONFIRMATION . '_' . date('Y-m-d-h-i-s', time()) . '_' . $user['id'] . '_' . rand(25, 222569);
                            $filePaths = $this->Common->processFileUpload($_FILES, $allowedExtensions, $extraParam);
                            if (isset($filePaths['error'])) {
                                $returnData['error'] = $filePaths['error'];
                            }
                        } else {
                            $filePaths[0] = null;
                        }

                        if (!isset($returnData['error'])) {
                            $returnData = $this->Common->saveDeliveryAndConfirmations($fieldsArray, $filePaths[0]);
                        }

                        if (isset($returnData['error'])) {
                            $returnData['errCode'] = $returnData['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;

                //-- GET deliveries List
                case 205:
                    $result = $this->Shipment->getDeliveryList();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $result;
                    $returnData['responseKey'] = 'deliveryList';
                    break;

                //-- GET confirmations List
                case 206:
                    $result = $this->Shipment->getDeliveryList($confirmation = true);
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $result;
                    $returnData['responseKey'] = 'deliveryList';
                    break;


                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ REPORTS (30x)
                //-- GET package labels
                case 300:
                    if (true) {
                        $shipmentId = !empty($urlParam) ? $urlParam : null;
                        if (empty($shipmentId)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        }
                        $returnData = $this->Reports->getPackageLabels($shipmentId, $combinePacakges = false);
                        if (isset($returnData['error'])) {
                            $returnData['errCode'] = $returnData['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }
                    break;

                //-- GET shipment labels
                case 301:
                    if (true) {
                        $shipmentId = !empty($urlParam) ? $urlParam : null;
                        if (empty($shipmentId)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        }
                        $returnData = $this->Reports->getShipmentLabels($shipmentId);
                        if (isset($returnData['error'])) {
                            $returnData['errCode'] = $returnData['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }
                    break;

                //-- dashboard shipment list all
                case 302:
                    if (true) {
                        $returnData = $this->Reports->getShipmentListDetails();
                        $returnData['data'] = $returnData;
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'dashboardshipmentList';
                    }
                    break;

                //-- dashboard shipment list of specific shipcode  with package details    
                case 303:
                    if ($this->request->is('post')) {
                        $shipcode = (isset($this->request->data['shipCode'])) ? $this->request->data['shipCode'] : '';
                        if (!empty($shipcode)) {
                            $returnData = $this->Reports->getShipmentListwithPackageDetails($shipcode);
                            $returnData['data'] = $returnData;
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'shipmentDetail';
                        } else {
                            $returnData['errCode'] = _ERR105;
                        }
                    }
                    break;

                //-- shipment labels list with package count details 
                case 304:
                    if (true) {
                        $returnData = $this->Reports->shipmentLabelsList();
                        $returnData['data'] = $returnData;
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'shipmentLabelList';
                    }
                    break;

                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ADMINISTRATION (40x)
                //-- forgot password 
                case 401:

                    if ($this->request->is('post')) {
                        //$this->request->data['userName']='jonseen9966';
                        $returnData = $this->UserCommon->forgotPassword($this->request->data);
                        if (isset($returnData['error'])) {
                            $returnData['errCode'] = $returnData['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }
                    break;

                //-- UPDATE PASSWORD from ACTIVATION LINK - USERS
                case 402:
                    if ($this->request->is('post')) {
                        $result = $this->UserCommon->accountActivation($this->request->data);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }
                    }
                    break;

                //-- ADD/MODIFY user details  
                case 403:
                    if ($this->request->is('post')) {

                        // Check Permissions
                        if (empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['USERS']['C'] == false) // ADD
                            $this->Common->sendResponseHeader(403);
                        if (!empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['USERS']['U'] == false) // EDIT
                            $this->Common->sendResponseHeader(403);
                        $Data = $this->UserCommon->saveUserDetails($this->request->data);
                        if (isset($Data['error'])) {
                            $returnData['errCode'] = $Data['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }
                    break;

                //-- GET -- ALL Users
                case 404:
                    $listAllUsers = $this->UserCommon->listAllUsers();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $listAllUsers;
                    $returnData['responseKey'] = 'usersList';

                    break;

                //-- DELETE -- specific User
                case 405:
                    if ($this->request->is('post')):

                        // Permissions
                        if ($sessionPermissions['ADMINISTRATION']['USERS']['D'] == false)
                            $this->Common->sendResponseHeader(403);

                        $userId = (isset($_POST['userId'])) ? $_POST['userId'] : '';
                        $result = $this->UserCommon->deleteUser($userId);

                        if ($result == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            $returnData['errCode'] = _ERR100;
                        }
                    endif;

                    break;

                //-- GET -- specific User details 
                case 406:
                    if ($this->request->is('post')):
                        $userId = (isset($_POST['userId'])) ? $_POST['userId'] : '';
                        $result = $this->UserCommon->getUserDetailsById($userId);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'userDetail';
                    endif;
                    break;


                //-- GET -- Status list (FILTER)
                case 407:
                    if ($this->request->is('post')):
                        $result = $this->Administration->getStatusList('all', ['id', 'code', 'name', 'colorCode' => 'color_code']);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'statusList';
                    endif;
                    break;


                //-- GET -- Location list (FILTER)
                case 408:
                    if ($this->request->is('post')):
                        $result = $this->Administration->getLocationList('all', ['id', 'code', 'name', 'areaId' => 'area_id']);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = array_values($result);
                        $returnData['responseKey'] = 'locationList';
                    endif;
                    break;

                //-- GET -- Area list (FILTER)
                case 409:
                    if ($this->request->is('post')):
                        $areaId = isset($this->request->data['id']) ? $this->request->data['id'] : -1;
                        $onDemand = isset($this->request->data['onDemand']) ? $this->request->data['onDemand'] : true;
                        $result = $this->Common->getTreeViewJSON(_TV_AREA, $areaId, $onDemand);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'areaList';
                    endif;
                    break;

                //-- GET -- Courier list (ADD/MODIFY SHIPMENT)
                case 410:
                    if ($this->request->is('post')):
                        $result = $this->Administration->getCourierList('all', ['id', 'code', 'name']);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = array_values($result);
                        $returnData['responseKey'] = 'courierList';
                    endif;
                    break;

                //-- GET -- Subscriptions
                case 411:
                    if ($this->request->is('post')):
                        $subscriptionId = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        /*if (empty($subscriptionId))
                            $returnData['errCode'] = _INVALID_INPUT;
                        $result = $this->Subscription->find('all', 'Subscriptions', ['fields' => [
                            'id', 'name', 'email', 'mobile', 'alert', 'areaId' => 'area_id', 'locationId' => 'location_id', 'comments', 'created', 'modified'
                        ], 'conditions' => ['id' => $subscriptionId]], ['first' => true]);*/
                        
                        $result = $this->Subscription->getSubscriptionList($subscriptionId);
                        
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'subscriptionDetails';
                    endif;
                    break;

                //-- ADD/MODIFY -- Subscriptions
                case 412:
                    if ($this->request->is('post')):
                        // Check Permissions
                        if (empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['SUBSCRIPTIONS']['C'] == false) // ADD
                            $this->Common->sendResponseHeader(403);
                        if (!empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['SUBSCRIPTIONS']['U'] == false) // EDIT
                            $this->Common->sendResponseHeader(403);

                        //if(empty($subscriptionId)) $returnData['errCode'] = _INVALID_INPUT;
                        $result = $this->Subscription->saveSubscriptions($this->request->data);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;
                    break;

                //-- GET -- Subscriptions - LIST
                case 413:
                    if ($this->request->is('post')):
                        $result = $this->Subscription->getSubscriptionList();
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'subscriptionList';
                        }
                    endif;
                    break;

                //-- DELETE -- Subscriptions
                case 414:
                    if ($this->request->is('post')):
                        // Check Permissions
                        if ($sessionPermissions['ADMINISTRATION']['SUBSCRIPTIONS']['D'] == false)
                            $this->Common->sendResponseHeader(403);

                        $subscriptionId = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        if (empty($subscriptionId))
                            $returnData['errCode'] = _INVALID_INPUT;

                        $result = $this->Subscription->deleteSubscription(['id' => $subscriptionId]);
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;


                //-- FUTURE-USE -- Subscriptions
                case 415:
                    break;

                //-- GET -- Location detail
                case 416:
                    if ($this->request->is('post')):
                        $locationId = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        if (empty($locationId))
                            $returnData['errCode'] = _INVALID_INPUT;

                        $result = $this->Administration->getLocation($conditions = ['id' => $locationId]);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = reset($result);
                            $returnData['responseKey'] = 'locationDetails';
                        }
                    endif;
                    break;

                //-- ADD/MODIFY -- Locations
                case 417:
                    if ($this->request->is('post')):
                        // Check Permissions
                        if (empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['LOCATIONS']['C'] == false) // ADD
                            $this->Common->sendResponseHeader(403);
                        if (!empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['LOCATIONS']['U'] == false) // EDIT
                            $this->Common->sendResponseHeader(403);

                        /* $locationId = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                          if(empty($locationId)) $returnData['errCode'] = _INVALID_INPUT; */

                        $result = $this->Administration->saveLocation($this->request->data);
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- DELETE -- Locations
                case 418:
                    if ($this->request->is('post')):
                        // Check Permissions
                        if ($sessionPermissions['ADMINISTRATION']['LOCATIONS']['D'] == false)
                            $this->Common->sendResponseHeader(403);

                        $locationId = isset($this->request->data['id']) ? $this->request->data['id'] : 15;
                        if (empty($locationId))
                            $returnData['errCode'] = _INVALID_INPUT;

                        $result = $this->Administration->deleteLocation($locationId);
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- GET -- Locations LISTING
                case 419:
                    if ($this->request->is('post')):
                        $result = $this->Administration->getLocationListing();
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'locationList';
                        }
                    endif;
                    break;

                //-- GET -- Location Custom Fields
                case 420:
                    if ($this->request->is('post')):
                        $result = $this->CustomFields->getLocationCustomFields($conditions = []);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'locationCustomFieldsList';
                        }
                    endif;
                    break;

                //-- GET -- Status listing
                case 421:
                    if ($this->request->is('post')):
                        $result = $this->Administration->getStatusConfigList();
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'statusList';
                    endif;
                    break;

                //-- ADD/MODIFY -- Status
                case 422:
                    if ($this->request->is('post')):
                        // Check Permissions
                        if (empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['STATUS']['C'] == false) // ADD
                            $this->Common->sendResponseHeader(403);
                        if (!empty($this->request->data['id']) && $sessionPermissions['ADMINISTRATION']['STATUS']['U'] == false) // EDIT
                            $this->Common->sendResponseHeader(403);

                        $statusId = isset($this->request->data['id']) ? $this->request->data['id'] : null;
                        $colorCode = isset($this->request->data['colorCode']) ? $this->request->data['colorCode'] : null;
                        if (empty($colorCode) || empty($statusId))
                            $returnData['errCode'] = _INVALID_INPUT;

                        $result = $this->Administration->saveStatus($this->request->data);
                        if (is_numeric($result)) {
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;
                    break;

                //-- GET -- Congifurations LISTING
                case 423:
                    if ($this->request->is('post')):
                        $result = $this->Administration->getSysConfigDetails([], ['type' => 'Config']);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'configurationList';
                    endif;
                    break;

                //-- MODIFY -- Congifurations
                case 424:
                    if ($this->request->is('post')):
                        $result = $this->Administration->saveSysConfigDetails($this->request->data);
                        if ($result == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $this->Common->getSystemConfig();
                            $returnData['responseKey'] = 'sysConfig';
                        }
                    endif;
                    break;


                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SYSTEM (50x)
                //-- GET session details
                case 500:
                    if ($this->request->is('post')):
                        if ($this->Auth->user()) {
                            $permissions = $this->Common->getModulesPermissions($this->Auth->user('role_id'));
                            $returnDatas[] = session_id();
                            $returnDatas[] = [
                                'id' => $this->Auth->user('id'),
                                'name' => $this->Auth->user('first_name') . ' ' . $this->Auth->user('last_name'),
                                'email' => $this->Auth->user('email'),
                                'username' => $this->Auth->user('username'),
                                'roleId' => $this->Auth->user('role_id'),
                                'roleName' => $this->session->read('roleList')[$this->Auth->user('role_id')],
                                'permission' => $permissions['permission']
                            ];

                            //$returnDatas[] = $permissions['permission'];

                            $this->session->write('permissions', $permissions['permission']);

                            $returnData['data'] = $returnDatas;
                            $returnData['responseKey'][] = 'id';
                            $returnData['responseKey'][] = 'user';
                            //$returnData['responseKey'][] = 'permission';
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;
                    break;

                //-- GET module permissions
                case 501:
                    if ($this->request->is('post')):
                        $roleId = null;
                        $role = isset($this->request->data['role']) ? $this->request->data['role'] : null;
                        if (!empty($role)) {
                            $roles = $this->session->read('roles');
                            $roleId = $roles[$role]['id'];
                        }
                        $result = $this->Common->getModulesPermissions($roleId);
                        $returnData['data'] = $result;
                        $returnData['responseKey'] = 'permission';
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- GET roles list
                case 502:
                    if ($this->request->is('post')):
                        $results = $this->Common->getRoles([], ['id', 'role', 'name' => 'role_name']);
                        $returnData['data'] = array_values($results);
                        $returnData['responseKey'] = 'roles';
                        $returnData['status'] = _SUCCESS;
                    endif;
                    break;

                //-- GET all custom type list  
                case 503:
                    if ($this->request->is('post')) {
                        $codeValue = (isset($this->request->data['code']) && !empty($this->request->data['code'])) ? $this->request->data['code'] : '';
                        if ($codeValue != '') {
                            $results = $this->Common->getTypeLists($codeValue);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $results;
                            $returnData['responseKey'] = 'typeList';
                        } else {
                            $returnData['errCode'] = _ERR105;
                        }
                    }
                    break;
                    
                //-- IMPORT Areas                  
                case 504:
                    if ($this->request->is('post')) {
                        $extraParam = [];
                        $allowedExtensions = ['xls', 'xlsx'];
                        //pr($_FILES);die;
                        $filePaths = $this->Common->processFileUpload($_FILES, $allowedExtensions, $extraParam);
                        if (isset($filePaths['error'])) {
                            $returnData['errCode'] = $filePaths['error'];
                        } else {
                            $extra['filename'] = $filePaths[0];
                            $importResult = $this->Common->bulkUploadXlsOrCsv($extra);
                            if (isset($importResult['error'])) {
                                $returnData['errCode'] = $importResult['error'];
                            } else {
                                $data = [];
                                $returnData['status'] = _SUCCESS;
                                if (isset($importResult['failedRecords']) && $importResult['failedRecords'] > 0) {
                                    // $importResult['importResult'];
                                    $data = $this->Common->writeLogFile($importResult);
                                    $returnData['data'] = $data;
                                } else {
                                    $returnData['data'] = $importResult;
                                }

                                $returnData['responseKey'] = 'importDetails';
                            }
                        }
                    }

                    break;
                // EXPORT - AREA
                case 505:
                    if ($this->request->is('post')) {
                        $expFile = $this->Area->exportArea();
                        $returnData['data'] = _WEBSITE_URL . _AREA_PATH_WEBROOT . '/' . basename($expFile);
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'areaExport';
                    }

                    break;
                // Add /Modify AREA level
                case 506:
                    if ($this->request->is('post')) {
                        $data = $this->Area->saveAreaLevel($this->request->data);
                        if (isset($data['error'])) {
                            $returnData['errCode'] = $data['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;

                // Delete level
                case 507:
                    if ($this->request->is('post')) {
                        $levelId = (isset($this->request->data['levelId'])) ? $this->request->data['levelId'] : '';
                        $data = $this->Area->deleteLevel($levelId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;


                //  level list
                case 508:
                    if (true) {

                        $data = $this->Area->getLevelList();
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'levelList';
                        $returnData['data'] = $data;
                    }

                    break;

                //  level details 
                case 509:
                    if ($this->request->is('post')) {
                        $levelId = (isset($this->request->data['levelId'])) ? $this->request->data['levelId'] : '';
                        $data = $this->Area->getLevelDetails($levelId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'levelDetails';
                            $returnData['data'] = $data;
                        }
                    }

                    break;


                // AREA list
                case 510:
                    if (true) {

                        $data = $this->Area->getAreaList();
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'areaList';
                        $returnData['data'] = $data;
                    }

                    break;

                //  Area details 
                case 511:
                   //if(true){
                        if ($this->request->is('post')) {
                        $areaId = (isset($this->request->data['areaId'])) ? $this->request->data['areaId'] : '';
                        $data = $this->Area->getAreaDetails($areaId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'areaDetails';
                            $returnData['data'] = $data;
                        }
                    }

                    break;

                    // Add /Modify Area
                case 512:
                    if ($this->request->is('post')) {
                        $data = $this->Area->saveAreaDetails($this->request->data);
                        if (isset($data['error'])) {
                            $returnData['errCode'] = $data['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;


                // Delete areas
                case 513:
                    if ($this->request->is('post')) {
                        $areaId = (isset($this->request->data['areaId'])) ? $this->request->data['areaId'] : '';
                        $data = $this->Area->deleteArea($areaId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;
                    
                    // Items list
                case 514:
                    if (true) {

                        $data = $this->Items->getItemsList();
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'itemList';
                        $returnData['data'] = $data;
                    }

                    break;

                //  Item details 
                case 515:
                      if ($this->request->is('post')) {
                        $itemId = (isset($this->request->data['itemId'])) ? $this->request->data['itemId'] : '';
                        $data = $this->Items->getItemDetailsById($itemId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'itemDetails';
                            $returnData['data'] = $data;
                        }
                    }

                    break;

                     //  Delete Item  
                case 516:
                    //if(true){
                    if ($this->request->is('post')) {
                        $itemId = (isset($this->request->data['itemId'])) ? $this->request->data['itemId'] : '';
                        $data = $this->Items->deleteItems($itemId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;
                    
                         //   Item save/modify 
                case 517:
                   
                    if ($this->request->is('post')) {
                        $data = $this->Items->saveItemsDetails($this->request->data);
                        if (isset($data['error'])) {
                            $returnData['errCode'] = $data['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;
                    
                           // Couriers list
                case 518:
                    if (true) {
                        $data = $this->Courier->getCourierList();
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'couriersList';
                        $returnData['data'] = $data;
                    }

                    break;

                //  Courier details 
                case 519:
                    if ($this->request->is('post')) {
                        $courierId = (isset($this->request->data['courierId'])) ? $this->request->data['courierId'] : '';
                        $data = $this->Courier->getCourierDetailsById($courierId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'courierDetails';
                            $returnData['data'] = $data;
                        }
                    }

                    break;
                    
                //  Delete Courier  
                case 520:
                    if ($this->request->is('post')) {
                        $courierId = (isset($this->request->data['courierId'])) ? $this->request->data['courierId'] : '';
                        $data = $this->Courier->deleteCourier($courierId);
                        if (isset($data['errorCode'])) {
                            $returnData['errCode'] = $data['errorCode'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;
                    
                 //   Courier save/modify 
                 case 521:
                    if ($this->request->is('post')) {
                        $data = $this->Courier->saveCourierDetails($this->request->data);
                        if (isset($data['error'])) {
                            $returnData['errCode'] = $data['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }
                    }

                    break;


                default:

            endswitch;
        } catch (Exception $e) {
            $returnData['errMsg'] = $e->getMessage();
        }

        return $this->serviceResponse($returnData, $convertJson = _YES);
    }

    /**
     * Prepare and Send Service Response
     * 
     * @param array $response raw response
     * @param string $convertJson convert to json
     * @return array Formatted Response array
     */
    public function serviceResponse($response, $convertJson = _YES) {

        // Initialize Result		
        $success = false;
        $dataUsrUserRole = [];
        $errCode = $errMsg = '';

        if (isset($response['status']) && $response['status'] == _SUCCESS):
            $success = true;
            $responseData = isset($response['data']) ? $response['data'] : [];
        else:
            $errCode = isset($response['errCode']) ? $response['errCode'] : '';
            $errMsg = isset($response['errMsg']) ? $response['errMsg'] : '';
        endif;

        // Set Result
        $returnData['success'] = $success;
        $returnData['err']['code'] = $errCode;
        $returnData['err']['msg'] = $errMsg;


        if ($success == true) {
            $responseKey = '';
            //responseKey is an array
            if (isset($response['responseKey']) && is_array($response['responseKey'])) {
                foreach ($response['responseKey'] as $key => $responseKey) {
                    if (!empty($responseKey))
                        $returnData['data'][$responseKey] = $responseData[$key];
                }
            }//responseKey is a string
            else {
                if (isset($response['responseKey']) && !empty($response['responseKey']))
                    $responseKey = $response['responseKey'];
                if (isset($responseKey) && !empty($responseKey))
                    $returnData['data'][$responseKey] = $responseData;
            }
        }

        if ($convertJson == _YES) {
            $returnData = json_encode($returnData);
        }

        // Return Result
        if (!$this->request->is('requested')) {
            $this->response->body($returnData);
            return $this->response;
        } else {
            return $returnData;
        }
    }

    /**
     * SET user roles in session to prevent query into DB
     */
    public function setRolesInSessions() {
        if ($this->session->check('roles') == false) {
            $roles = $this->Common->getRoles();
            $roleList = [];
            foreach ($roles as $role) {
                $roleList[$role['id']] = $role['role_name'];
            }
            $this->session->write('roleList', $roleList);
            $this->session->write('roles', $roles);
        }
    }

}
