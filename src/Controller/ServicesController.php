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

set_time_limit(0);
ini_set('memory_limit', '2000M');

/**
 * Services Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class ServicesController extends AppController {

    //Loading Components
    public $components = ['Auth', 'DevInfoInterface.CommonInterface', 'Common', 'UserCommon', 'TransactionLogs', 'MIusValidations', 'UserAccess', 'DataEntry', 'Template', 'Database'];

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
    public function serviceQuery($case = null, $extra = []) {
        $this->autoRender = false;
        $this->autoLayout = false; //$this->layout = '';
        $convertJson = _YES;
        $returnData = [];
        $dbConnection = '';
        $authUserId = $this->Auth->user(_USER_ID);       // logged in user id
        $authUserRoleId = $this->Auth->user(_USER_ROLE_ID);  // logged in user id
        $chkSAStatus = false;
        $chkSAStatus = $this->UserCommon->checkSAAccess(); // returns true if superadmin 
        //_SUPERADMIN_ROLE
        $dbId = '';
        //$_REQUEST['dbId']=46;  // for testing 
        if (isset($_REQUEST['dbId']) && !empty($_REQUEST['dbId'])) {
            $dbId = $_REQUEST['dbId'];

            // Write to session to be used to write Transaction log anywhere
            $this->session->write('dbId', $dbId);

            $dbConnection = $this->Common->getDbConnectionDetails($dbId); //dbId

            $dbDetails = json_decode($dbConnection, true);
            $dbName = $dbDetails['db_connection_name'];
            $this->session->write('dbName', $dbName);

            // $role_id = $this->Auth->user(_USER_ROLE_ID);
            // User is not Superadmin
            if ($chkSAStatus == false) {
                //---- Store User access data into session, if found
                $authUserId = $this->Auth->user(_USER_ID);
                // Check fake call
                if (!empty($authUserId)) {
                    $userDbId = $this->UserCommon->getUserDatabaseId($authUserId, $dbId);
                    // check user is using the assigned DB only
                    if (!empty($userDbId)) {
                        $getDbRolesDetails = $this->UserCommon->getDbRolesDetails($fields = [], [_RUSERDBROLE_USER_DB_ID => $userDbId[0]]);
                        // Check User DB role
                        if (!empty($getDbRolesDetails)) {
                            $getDbRolesDetails = reset($getDbRolesDetails);
                            $userDbRoleId = $getDbRolesDetails[_RUSERDBROLE_ID];
                            $areaAccess = $getDbRolesDetails[_RUSERDBROLE_AREA_ACCESS];
                            $indicatorAccess = $getDbRolesDetails[_RUSERDBROLE_INDICATOR_ACCESS];

                            // Store user access in session for later use
                            $this->session->write('userAccess', [
                                'userDbRoleId' => $userDbRoleId,
                                'areaAccess' => $areaAccess,
                                'indicatorAccess' => $indicatorAccess
                            ]);
                        }
                    } // User is not assigned this DB - fake call
                    else {
                        $returnData['success'] = _FAILED;
                        $returnData['errCode'] = _ERR120;
                        $returnData['isAuthorised'] = false;
                        $case = 0;
                    }
                }
            } else {
                // Delete old user access session if found
                if ($this->session->check('userAccess')) {
                    $this->session->delete('userAccess');
                }
            }
        }

        switch ($case):

            case 'test':

                //$returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'testCasesFromTable', [], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'testCasesFromTable', [], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('SubgroupValsSubgroup', 'testCasesFromTable', [], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('SubgroupType', 'getRecords', [[], []], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getRecords', [[], []], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('IcIus', 'getRecords', [[], []], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('Indicator', 'getRecords', [[], []], $dbConnection);
                //$timePeriod = $_GET['tp'];//'2011.06';//'2011.06.11';//'2011-2012.03';
                //$timePeriod[_TIMEPERIOD_TIMEPERIOD] = $_GET['tp'];
                //$returnData = $this->CommonInterface->serviceInterface('Timeperiod', 'insertRecords', ['timePeriods' => $timePeriod], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('Timeperiod', 'getStartEndDate', ['timePeriods' => $timePeriod], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'guid', [], $dbConnection);
                //$returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'testCasesFromTable', [], $dbConnection);
                //debug($returnData);
                //exit;
                break;

            case 102: //Select Data using Conditions -- Indicator table

                $fields = [_INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_INFO];
                $conditions = [_INDICATOR_INDICATOR_GID . ' IN' => ['POPDEN', 'AREA']];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                $returnData = $this->CommonInterface->serviceInterface('Indicator', 'getRecords', $params, $dbConnection);
                break;

            case 104: //Delete Data using Conditions -- Indicator table

                $conditions = [_INDICATOR_INDICATOR_GID . ' IN' => ['TEST_GID', 'TEST_GID2']];

                //deleteRecords(array $conditions)
                $params['conditions'] = $conditions = [_INDICATOR_INDICATOR_GID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                $returnData = $this->CommonInterface->serviceInterface('Indicator', 'deleteRecords', $params, $dbConnection);
                break;

            case 105: //Insert New Data -- Indicator table
                if ($this->request->is('post')):
                    try {

                        $indicatorDetails = [
                            _INDICATOR_INDICATOR_NID => (isset($_POST['iNid'])) ? $_POST['iNid'] : '',
                            _INDICATOR_INDICATOR_NAME => (isset($_POST['iName'])) ? $_POST['iName'] : '',
                            _INDICATOR_INDICATOR_GID => (isset($_POST['iGid'])) ? $_POST['iGid'] : ''];
                        $unitNids = (isset($_POST['uNid'])) ? $_POST['uNid'] : '';
                        $subgrpNids = (isset($_POST['sNid'])) ? $_POST['sNid'] : '';

                        $metadataArray = (isset($_POST['metadata'])) ? $_POST['metadata'] : '';
                        $metadataArray = json_encode($metadataArray);

                        $params[] = ['indicatorDetails' => $indicatorDetails, 'unitNids' => $unitNids, 'subgrpNids' => $subgrpNids,
                            'metadataArray' => $metadataArray];
                        $result = $this->CommonInterface->serviceInterface('Indicator', 'manageIndicatorData', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 106: //Update Data using Conditions -- Indicator table

                $fields = [
                    _INDICATOR_INDICATOR_NAME => 'Custom_test_name3',
                    _INDICATOR_INDICATOR_GID => 'SOME_003_TEST'
                ];
                $conditions = ['Indicator_NId' => '384'];

                if ($this->request->is('post')):
                    //updateRecords(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('Indicator', 'updateRecords', $params, $dbConnection);
                endif;

                break;

            case 107: //Bulk Insert/Update Data -- Indicator table
                //if($this->request->is('post')):
                if (true):
                    $params['filename'] = $filename = 'C:\-- Projects --\Indicator2000.xls';
                    $params['component'] = 'Indicator';
                    $params['extraParam'] = [];
                    //$returnData = $this->CommonInterface->bulkUploadXlsOrCsvForIndicator($params);                    
                    $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                endif;

                break;

            case 108: //get indicator details using indicator id 

                 if ($this->request->is('post')):
                    //if (true):                    
                    try {
                        
                        $iuNid = (isset($_POST['iuNid'])) ? $_POST['iuNid'] : '';
                        if(!empty($iuNid) && !empty($dbId)){
                            $params = ['iuNid' => $iuNid];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('Indicator', 'getIndicatorById', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'indDetail';
                        }else{
                             $returnData['errCode'] = _ERR145;  //invalid request 
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;



            case 109: //delete indicator details and its corresponding data  using indicator id 
                 if ($this->request->is('post')):
                    //if (true):
                    
                    try {
                        $iuNid = (isset($_POST['iuNid'])) ? $_POST['iuNid'] : '';
                        if(!empty($iuNid) && !empty($dbId)){
                            $params = ['iuNid' => $iuNid];
                            $result = $this->CommonInterface->serviceInterface('Indicator', 'deleteIndicatordata', $params, $dbConnection);
                            if ($result == true) {

                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                            }
                        }else{
                             $returnData['errCode'] = _ERR145;  //invalid request 
                        }
                        
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 110: //get metadata  details using indicator nid 
                if ($this->request->is('post')):
                //if (true):

                    try {

                        $params = ['iNid' => (isset($_POST['iNid'])) ? $_POST['iNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Metadata', 'getMetaDataDetails', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'metaDetail';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 111:  //delete  metadata  details using indicator nid 
            if ($this->request->is('post')):
               // if (true):

                    try {

                        $params = ['iNid' => (isset($_POST['iNid'])) ? $_POST['iNid'] : '', 'nId' => (isset($_POST['nId'])) ? $_POST['nId'] : ''];
                        $result = $this->CommonInterface->serviceInterface('Metadata', 'deleteMetaData', $params, $dbConnection);
                        if ($result == true) {

                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            //$returnData['errCode'] = _ERR110;     // Not deleted   
                            $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            

            case 202: //Select Data using Conditions -- Unit table

                $params['fields'] = $fields = [_UNIT_UNIT_NAME, _UNIT_UNIT_GLOBAL];
                $params['conditions'] = $conditions = [_UNIT_UNIT_GID . ' IN' => ['POPDEN', 'AREA']];

                $returnData = $this->CommonInterface->serviceInterface('Unit', 'getRecords', $params, $dbConnection);
                break;

            case 204: //Delete Data using Conditions -- Unit table
                if ($this->request->is('post')):
                    //if (true):

                    try {
                        // = [_UNIT_UNIT_NID . ' IN' => $uNid];

                        $uNid = (isset($_POST['uNid'])) ? $_POST['uNid'] : '';
                        $params['uNid'] = $uNid;
                        $result = $this->CommonInterface->serviceInterface('Unit', 'deleteUnitdata', $params, $dbConnection);
                        if ($result == true) {

                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            //$returnData['errCode'] = _ERR110;     // Not deleted   
                            $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 205: //Insert New Data -- Unit table
                if ($this->request->is('post')):
                    //if(true):
                    try {

                        /*
                         * $this->request->data = [
                          _UNIT_UNIT_NID => '222',
                          _UNIT_UNIT_NAME => 'brothers',
                          _UNIT_UNIT_GID => 'SOME_002_brot77',
                          _UNIT_UNIT_GLOBAL => '0'
                          ];
                         */
                        $posteddata = [_UNIT_UNIT_NAME => $this->request->data['uName'], _UNIT_UNIT_GID => $this->request->data['uGid'],
                            _UNIT_UNIT_NID => (isset($this->request->data['uNid'])) ? $this->request->data['uNid'] : '', _UNIT_UNIT_GLOBAL => '0'];


                        //insertData(array $fieldsArray = $this->request->data)
                        $params[] = $posteddata;
                        $result = $this->CommonInterface->serviceInterface('Unit', 'manageUnitdata', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;

                break;




            case 206: //Update Data using Conditions -- Unit table

                $fields = [
                    _UNIT_UNIT_NAME => 'Custom_test_name3',
                    _UNIT_UNIT_GID => 'SOME_003_TEST'
                ];
                $conditions = [_UNIT_UNIT_NID => '43'];

                if ($this->request->is('post')):
                    //updateRecords(array $fields, array $conditions)
                    $params[] = $fields;
                    $params[] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('Unit', 'updateRecords', $params, $dbConnection);
                endif;

                break;

            case 207: //Bulk Insert/Update Data -- Unit table
                //if($this->request->is('post')):
                if (true):
                    $params['filename'] = $filename = 'C:\-- Projects --\Unit.xls';
                    $params['component'] = 'Unit';
                    $params['extraParam'] = [];
                    $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                endif;

                break;

            case 208: //service to get the unit data for specific id 
                if ($this->request->is('post')):
                    //if(true):

                    try {

                        $params = ['uNid' => (isset($_POST['uNid'])) ? $_POST['uNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Unit', 'getUnitById', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'unitDetail';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;

                break;

            case 301: // GET - Timeperiod

                if ($this->request->is('post')):
                    $params = ['fields' => [], 'conditions' => []];
                    $returnData['data'] = $this->CommonInterface->serviceInterface('Timeperiod', 'getRecords', $params, $dbConnection);

                    if ($returnData['data'] === false)
                        $returnData['status'] = _FAILED;
                    else
                        $returnData['status'] = _SUCCESS;
                endif;

                break;

            case 302: // DELETE - Timeperiod

                if ($this->request->is('post')):
                    $params = ['conditions' => []];
                    $data = $this->CommonInterface->serviceInterface('Timeperiod', 'deleteRecords', $params, $dbConnection);
                    if ($data) {
                        $returnData['status'] = _SUCCESS;
                    } else {
                        $returnData['status'] = _FAILED;
                    }


                endif;

                break;

            case 303: // INSERT - TIMEPERIOD

                if ($this->request->is('post')):
                    //if(true):
                    try {
                        //$this->request->data['tpNid']=43;
                        $fields[_TIMEPERIOD_TIMEPERIOD] = $this->request->data['name'];
                        if (isset($this->request->data['periodicity']))
                            $fields[_TIMEPERIOD_PERIODICITY] = $this->request->data['periodicity'];
                        if (isset($this->request->data['tpNid']))
                            $fields[_TIMEPERIOD_TIMEPERIOD_NID] = $this->request->data['tpNid'];

                        $params = ['fields' => $fields];
                        $result = $this->CommonInterface->serviceInterface('Timeperiod', 'insertRecords', $params, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'tp';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 304: // UPDATE - TIMEPERIOD
                if ($this->request->is('post')):
                    $params = ['fields' => [], 'conditions' => []];
                    $returnData['data'] = $this->CommonInterface->serviceInterface('Timeperiod', 'updateRecords', $params, $dbConnection);

                    if ($returnData['data'])
                        $returnData['status'] = _SUCCESS;
                    else
                        $returnData['status'] = _FAILED;
                endif;
                break;

            case 305: // get timperiod by id 
                if ($this->request->is('post')):
                    try {
                        $params = ['tpNid' => (isset($_POST['tpNid'])) ? $_POST['tpNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Timeperiod', 'getTimeperiodByID', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'timperiodDetails';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;




            case 404: // service for deleting the subgroup types and its corresponding data using  subgroup type nid
                //  if ($this->request->is('post')):
                if (true):
                    try {

                        $nId = (isset($_POST['nId'])) ? $_POST['nId'] : '';
                        if (!empty($nId) && !empty($dbId)) {

                            $params = ['nId' => $nId];

                            $result = $this->CommonInterface->serviceInterface('SubgroupType', 'deleteSubgroupTypedata', $params, $dbConnection);
                            if ($result == true) {
                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                            }
                        } else {
                            $returnData['errCode'] = _ERR145;      // Invalid details 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }


                endif;
                break;


            case 405: // service for deleting the subgroup details and its corresponding data using  subgroup  nid
               if ($this->request->is('post')):

                    try {

                        $nId = (isset($_POST['nId'])) ? $_POST['nId'] : '';  // subgroup nid 					
                        if (!empty($nId) && !empty($dbId)) {
                            $params = ['sgId' => $nId];
                            $result = $this->CommonInterface->serviceInterface('SubgroupType', 'deleteSubgroupdata', $params, $dbConnection);
                            if ($result == true) {
                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                            }
                        } else {
                            $returnData['errCode'] = _ERR145;      //  invalid request 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;


            case 406: //get  Subgroup type  details  using subgroup type id [Dimesion details of specific record]
                
                if ($this->request->is('post')):
                //if (true):
                    try {
                        $sgTypeNid = (isset($_POST['nId'])) ? $_POST['nId'] : '';
                        if (!empty($sgTypeNid) && !empty($dbId)) {
                            $params = ['sgTypeNid' => $sgTypeNid];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupType', 'getSubgroupTypeDetailsById', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'dimesionDetail';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }



            endif;
            break;

            case 407: //get  Subgroup type  list  [Dimesion list]
                 if ($this->request->is('post')):
                //if (true):
                    try {
                        if (!empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupType', 'getSubgroupTypeList', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgrpTypeList';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }



                endif;
                break;

            case 408: //manage  subgroup type add/modify 
                 if ($this->request->is('post')):
                //if (true):
                    try {
                        
                        $subgroupData=[];
                        $subgroupData['dName'] = (isset($_POST['dName'])) ? $_POST['dName'] : '';
                        $subgroupData['dValues'] =(isset($_POST['dValues'])) ? $_POST['dValues'] : '' ;
                        $subgroupData['dGid'] = (isset($_POST['dGid'])) ? $_POST['dGid'] : '' ;
                        $subgroupData['nId'] =(isset($_POST['nId'])) ? $_POST['nId'] : '' ;
                        
                        /* 	
                          $subgroupData=[];
                          $subgroupData['dName'] = 'cat7type11';
                          $subgroupData['nId'] = '234';
                          $subgroupData['dGid']   = 'cat6type15422';
                          $subgroupData['dValues'][0]['nId']   = '675';
                          $subgroupData['dValues'][0]['val']   = 'boolo';
                          $subgroupData['dValues'][0]['gId']   = 'boolo';
                          $subgroupData['dValues'][1]['nId']   = '670';
                          $subgroupData['dValues'][1]['val']   = 'cat34typ00e1144';
                          $subgroupData['dValues'][1]['gId']   = 'cat34typ00e1144';
                        */
                        
                        //$subgroupData = (isset($_POST['subgroupData'])) ? $_POST['subgroupData'] : '';
                        $subgroupData = json_encode($subgroupData);
                        $params[] = ['subgroupData' => $subgroupData];

                        $result = $this->CommonInterface->serviceInterface('SubgroupType', 'manageSubgroupTypeData', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = $result['returnData'];
                            $returnData['responseKey'] = 'returnData';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;


            case 409: // add subgroup  
               //if ($this->request->is('post')):
                if (true):

                    try {

                        $subgroupData = [];
                       /* $subgroupData['dvName'] = 'Rural';
                        $subgroupData['dcNid'] = '234';
                        $subgroupData['dvNid'] = '';*/
                        $subgroupData['dvName'] = $_POST['dvName'];
                        $subgroupData['dcNid'] = $_POST['dcNid'];

                        //$subgroupData = (isset($_POST['subgroupData']))?$_POST['subgroupData']:'';
                        //$subgroupData = json_encode($subgroupData);	
                        $params[] = ['subgroupValData' => $subgroupData, 'dbId' => $dbId];

                        $result = $this->CommonInterface->serviceInterface('Subgroup', 'manageSubgroupData', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            // service no. starting from  501 are for subgroup
            case 501: // service for saving  subgroup  name 
                if ($this->request->is('post')):
                    $data = array();
                    $params[] = $data;
                    $saveDataforSubgroupType = $this->CommonInterface->serviceInterface('Subgroup', 'insertData', $params, $dbConnection);

                    $returnData['returnvalue'] = $saveDataforSubgroupType;
                endif;
                break;

            case 502: // service for updating the   subgroup  name 
                if ($this->request->is('post')):
                    $data = array();
                    $fields = [_SUBGROUP_SUBGROUP_NAME, _SUBGROUP_SUBGROUP_TYPE];

                    $params['fields'] = $fields;
                    $params['conditions'] = $data;
                    $saveDataforSubgroupType = $this->CommonInterface->serviceInterface('SubgroupType', 'deleteRecords', $params, $dbConnection);
                    $returnData['returnvalue'] = $saveDataforSubgroupType;
                endif;
                break;

            case 503: // service for getting the Subgroup  details on basis of any parameter  
                if ($this->request->is('post')):
                    $conditions = $fields = [];
                    $params[] = $fields;
                    $params[] = $conditions;

                    $SubgroupDetails = $this->CommonInterface->serviceInterface('Subgroup', 'getRecordsSubgroup', $params, $dbConnection);
                    $returnData['data'] = $SubgroupDetails;
                endif;
                break;

            case 504: // service for deleting the Subgroup Name using  any parameters
                if ($this->request->is('post')):
                    $conditions = [];
                    $params[] = $conditions;

                    $deleteallSubgroup = $this->CommonInterface->serviceInterface('Subgroup', 'deleteRecords', $params, $dbConnection);
                    $returnData['returnvalue'] = $deleteallSubgroup;
                endif;
                break;

            case 601: // service for deleting the subgroup vals and its corresponding data using  subgroup val nid
                //  if ($this->request->is('post')):
                if (true):
                    try {

                        $nId = (isset($_POST['nId'])) ? $_POST['nId'] : '';
                        if (!empty($nId) && !empty($dbId)) {

                            $params = ['sgvalNid' => $nId];

                            $result = $this->CommonInterface->serviceInterface('SubgroupVals', 'deleteSubgroupValData', $params, $dbConnection);
                            if ($result == true) {
                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                            }
                        } else {
                            $returnData['errCode'] = _ERR145;      // Invalid details 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }


                endif;
                break;

            case 602: //Select Data using Conditions -- SubgroupVals table

                $fields = [_SUBGROUP_VAL_SUBGROUP_VAL, _SUBGROUP_VAL_SUBGROUP_VAL_GID];
                $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_GID . ' IN' => ['T', 'U']];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'getRecords', $params, $dbConnection);
                break;

            case 604: //manage  subgroup val add/modify 
                if ($this->request->is('post')):
                    //if(true):
                    try {

                        $subgroupVal = [];


                        /* 	
                          $subgroupVal[0]['sNid']   = '26';
                          $subgroupVal[0]['sName']  = 'Male 0-6 yrs';
                          $subgroupVal[0]['sGid']   = '';

                          $subgroupVal[0]['dimension'][0]['dcNid']   = '1';
                          $subgroupVal[0]['dimension'][0]['dvNid']   = '23';
                          $subgroupVal[0]['dimension'][0]['dvName']   = 'Male';

                          $subgroupVal[0]['dimension'][1]['dcNid']   = '3';
                          $subgroupVal[0]['dimension'][1]['dvNid']   = '5';
                          $subgroupVal[0]['dimension'][1]['dvName']   = '0-6 yrs';

                          $subgroupVal[0]['dimension'][2]['dcNid']   = '4';
                          $subgroupVal[0]['dimension'][2]['dvNid']   = '405';
                          $subgroupVal[0]['dimension'][2]['dvName']   = 'Any';
                         */
                        /*
                          $subgroupVal[1]['sNid']   = '';
                          $subgroupVal[1]['sName']  = '11polo0788';
                          $subgroupVal[1]['sGid']   = '11poloabc098';

                          $subgroupVal[1]['dimension'][0]['dcNid']   = '233';
                          $subgroupVal[1]['dimension'][0]['dvNid']   = '690';
                          $subgroupVal[1]['dimension'][0]['dvName']   = '11abc09049';

                          $subgroupVal[1]['dimension'][1]['dcNid']   = '233';
                          $subgroupVal[1]['dimension'][1]['dvNid']   = '';
                          $subgroupVal[1]['dimension'][1]['dvName']   = '11abc77709049';


                          $subgroupVal[2]['sNid']   = '';
                          $subgroupVal[2]['sName']  = 'sname009';
                          $subgroupVal[2]['sGid']   = 'sname009';

                          $subgroupVal[2]['dimension'][0]['dcNid']   = '233';
                          $subgroupVal[2]['dimension'][0]['dvNid']   = '';
                          $subgroupVal[2]['dimension'][0]['dvName']   = 'sname00988';

                          $subgroupVal[2]['dimension'][1]['dcNid']   = '233';
                          $subgroupVal[2]['dimension'][1]['dvNid']   = '';
                          $subgroupVal[2]['dimension'][1]['dvName']   = 'sname00981';

                          $subgroupVal[3]['sNid']   = '';
                          $subgroupVal[3]['sName']  = 'sname109';
                          $subgroupVal[3]['sGid']   = 'sname109';

                          $subgroupVal[3]['dimension'][0]['dcNid']   = '233';
                          $subgroupVal[3]['dimension'][0]['dvNid']   = '';
                          $subgroupVal[3]['dimension'][0]['dvName']   = 'sname988';

                          $subgroupVal[3]['dimension'][1]['dcNid']   = '233';
                          $subgroupVal[3]['dimension'][1]['dvNid']   = '';
                          $subgroupVal[3]['dimension'][1]['dvName']   = 'sname81';
                         */
                        $subgroupVal = (isset($_POST['subgroupList'])) ? $_POST['subgroupList'] : '';
                        $subgroupVal = json_encode($subgroupVal);

                        $params[] = ['subgroupValData' => $subgroupVal, 'dbId' => $dbId];
                        $result = $this->CommonInterface->serviceInterface('SubgroupVals', 'manageSubgroupValData', $params, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 605: //Insert New Data -- SubgroupVals table
                if ($this->request->is('post')):

                    $this->request->data = [
                        _SUBGROUP_VAL_SUBGROUP_VAL_NID => '965',
                        _SUBGROUP_VAL_SUBGROUP_VAL => 'Custom_test_name2',
                        _SUBGROUP_VAL_SUBGROUP_VAL_GID => 'SOME_001_TEST',
                        _SUBGROUP_VAL_SUBGROUP_VAL_GLOBAL => '0',
                        _SUBGROUP_VAL_SUBGROUP_VAL_ORDER => '102',
                    ];

                    //insertData(array $fieldsArray = $this->request->data)
                    $params['conditions'] = $conditions = $this->request->data;
                    $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'insertData', $params, $dbConnection);
                endif;

                break;

            case 606: //Update Data using Conditions -- SubgroupVals table

                $fields = [
                    _SUBGROUP_VAL_SUBGROUP_VAL => 'Custom_test_name3',
                    _SUBGROUP_VAL_SUBGROUP_VAL_GID => 'SOME_003_TEST'
                ];
                $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID => '965'];

                if ($this->request->is('post')):
                    //updateRecords(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'updateRecords', $params, $dbConnection);
                endif;

                break;

            case 607: //Bulk Insert/Update Data -- SubgroupVals table
                //if($this->request->is('post')):
                if (true):
                    $params['filename'] = $filename = 'C:\-- Projects --\Indicator2000.xls';
                    $params['component'] = 'SubgroupVals';
                    $params['extraParam'] = [];
                    //$returnData = $this->CommonInterface->bulkUploadXlsOrCsvForIndicator($params);                    
                    $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                endif;

                break;

            case 608: //get  Subgroup dimension with their values  list  
                if (true):
                    try {
                        if (!empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'getSubgroupDimensionList', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgrpDimList';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }



                endif;
                break;

            case 609: //get  Subgroup val  with their dimension and subgroup values   list  
                if (true):
                    try {
                        if (!empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'getSubgroupValsDimensionList', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgrpValList';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }



                endif;
                break;

            case 610: //get  Subgroup val  with their dimension and subgroup values   list  
                if (true):
                    try {
                        if (!empty($dbId)) {
                            $params = ['sgValNid' => (isset($_POST['sgValNid'])) ? $_POST['sgValNid'] : ''];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'getSubgroupValsDimensionListById', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data']   = $returnData['data'];
                            $returnData['responseKey'] = 'subgroupVal';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }



                endif;
                break;

            case 701:

                //if($this->request->is('post')):
                if (true):
                    //$params['filename'] = $filename = 'C:\-- Projects --\xls\Temp_Selected_ExcelFile.xls';
                    $params['filename'] = $extra['filename'];
                    $params['component'] = 'IndicatorClassifications';
                    $params['extraParam'] = [];
                    //$returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsvForIUS', $params, $dbConnection);
                    $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                endif;

                break;

            // services for Area
            case 800:

                try {

                    $returnData['success'] = true;
                    $returnData['data']['id'] = $this->Auth->user(_USER_ID);
                } catch (Exception $e) {
                    echo 'Exception occured while loading the project list file';
                    exit;
                }

                break;
            
            case 801: // GET -- Area
                if ($this->request->is('post')):
                //if (true):
                    try{
                        $aNid = $this->request->data['aNid'];
                        if (empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $fields = ['aNid' => _AREA_AREA_NID, 'aId' => _AREA_AREA_ID, 'aName' => _AREA_AREA_NAME];
                            $conditions = [_AREA_AREA_NID => $aNid];

                            $params['fields'] = $fields;
                            $params['conditions'] = $conditions;
                            $params['type'] = 'all';
                            $params['extra'] = ['first' => true];
                            $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'aDetail';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    }catch(Exception $e){
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;


            case 802: // DELETE - Area
                //if ($this->request->is('post')):
                if (true):
                    try {
                        $aNid = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : null;

                        if (empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $params = ['fieldsArray' => $fieldsArray];
                            $result = $this->CommonInterface->serviceInterface('Area', 'saveAndGetAreaNid', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'area';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 803: // INSERT/UPDATE - Area
                if ($this->request->is('post')):
                    //if(true):
                    try {
                        $fieldsArray[_AREA_AREA_NAME] = isset($this->request->data['aName']) ? $this->request->data['aName'] : '';
                        $fieldsArray[_AREA_AREA_ID] = isset($this->request->data['aId']) ? $this->request->data['aId'] : '';
                        $fieldsArray[_AREA_AREA_NID] = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : null;

                        if (isset($this->request->data['pnid']))
                            $fieldsArray[_AREA_PARENT_NId] = $this->request->data['pnid'];

                        if (empty($fieldsArray[_AREA_AREA_NAME]) || empty($fieldsArray[_AREA_AREA_ID])) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $params = ['fieldsArray' => $fieldsArray];
                            $result = $this->CommonInterface->serviceInterface('Area', 'saveAndGetAreaNid', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'area';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;
                
            case 804: // GET Area Parent Details
                if ($this->request->is('post')):
                //if (true):
                    try{
                        $aNid = $this->request->data['aNid'];
                        
                        if(empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $pnid = $this->CommonInterface->serviceInterface('Area', 'getRecords', [[_AREA_PARENT_NId], [_AREA_AREA_NID => $aNid], 'all', ['first' => true]], $dbConnection);                            
                            
                            if(!empty($pnid)) {
                                if($pnid[_AREA_PARENT_NId] != '-1') {
                                    $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', [['aNid' => _AREA_AREA_NID, 'aGid' => _AREA_AREA_GID, 'aName' => _AREA_AREA_NAME], [_AREA_AREA_NID => $pnid[_AREA_PARENT_NId]], 'all', ['first' => true]], $dbConnection = '');
                                    if(empty($result)) {
                                        $result['error'] = _ERR157;
                                    }
                                } else {
                                    $result = [
                                        'aNid' => '-1',
                                        'aGid' => '',
                                        'aName' => ''
                                    ];
                                }
                                if (isset($result['error'])) {
                                    $returnData['errCode'] = $result['error'];
                                } else {
                                    $returnData['data'] = $result;
                                    $returnData['responseKey'] = 'aDetail';
                                    $returnData['status'] = _SUCCESS;
                                }
                            }
                        }
                    }catch(Exception $e){
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;
                
            case 805: //ADD area map
                //if ($this->request->is('post')):
                if (true):
                    try{
                        $type = isset($this->request->data['type']) ? $this->request->data['type'] : null;
                        $aNid = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : null;
                        $mapName = isset($this->request->data['mapName']) ? $this->request->data['mapName'] : null;
                        $startDate = isset($this->request->data['startDate']) ? $this->request->data['startDate'] : null;
                        $endDate = isset($this->request->data['endDate']) ? $this->request->data['endDate'] : null;
                        
                        // Optionals
                        $sibling = isset($this->request->data['endDate']) ? $this->request->data['sibling'] : null;
                        $siblingOption = isset($this->request->data['endDate']) ? $this->request->data['siblingOption'] : null;
                        $split = isset($this->request->data['endDate']) ? $this->request->data['split'] : null;
                        $assocCompMap = isset($this->request->data['endDate']) ? $this->request->data['assocCompMap'] : null;
                        
                        if(empty($aNid) || empty($type) || empty($mapName) || empty($startDate) || empty($endDate)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            //-- UPLOAD FILE
                            $allowedExtensions = ['zip', 'zip2'];
                            
                            $dbDetails = json_decode($dbConnection, true);
                            $extraParam['dbName'] = $dbDetails['db_connection_name'];
                            $extraParam['subModule'] = _MODULE_NAME_MAP;
                            $extraParam['dest'] = _MAPS_PATH;
                            
                            $filePaths = $this->Common->processFileUpload($_FILES, $allowedExtensions, $extraParam);
                            
                            if(!empty($filePaths)) {
                                // prepare inputs to be send like $type
                                $inputs = [
                                    'aNid' => $aNid,
                                    'filename' => $filePaths[0],
                                    'mapName' => $mapName,
                                    'startDate' => $startDate,
                                    'endDate' => $endDate,
                                    'sibling' => $sibling,
                                    'siblingOption' => $siblingOption,
                                    'split' => $split,
                                    'assocCompMap' => $assocCompMap,
                                ];
                                $result = $this->Template->addMap($type, $inputs, $dbConnection);
                            } else {
                                $result['error'] = _ERR163;
                            }
                            
                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    } catch (Exception $ex) {

                    }
                endif;
                break;

            case 901:
                //  service for getting the AREA LEVEL details on basis of passed parameters
                if (!empty($_POST['Level_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Area_Level_Name'])) {
                    $conditions = array();
                    $params[] = $fields = [_AREALEVEL_LEVEL_NAME, _AREALEVEL_AREA_LEVEL, _AREALEVEL_LEVEL_NID];
                    $params[] = $conditions;

                    $getAreaLevelDetailsData = $this->CommonInterface->serviceInterface('Area', 'getRecordsAreaLevel', $params, $dbConnection);

                    if ($getAreaLevelDetailsData) {

                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $getAreaLevelDetailsData;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {

                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }

                break;

            case 902:
                // service for deleting the Area level using  any parameters below 
                if (!empty($_POST['Level_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Area_Level_Name'])) {

                    $conditions = array();
                    $params[] = $conditions;
                    $deleteallAreaLevel = $this->CommonInterface->serviceInterface('Area', 'deleteRecordsAreaLevel', $params, $dbConnection);
                    if ($deleteallAreaLevel) {
                        $returnData['message'] = 'Record deleted successfully';
                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $deleteallAreaLevel;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {
                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }

                break;

            case 903:

                // service for saving the  Area level details 
                if (!empty($_POST['Level_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Area_Level_Name'])) {

                    $conditions = array();
                    $params[] = $conditions;
                    $insertAreaLeveldata = $this->CommonInterface->serviceInterface('Area', 'insertUpdateAreaLevel', $params, $dbConnection);

                    if ($insertAreaLeveldata) {
                        $returnData['message'] = 'Record saved successfully';
                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $insertAreaLeveldata;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {
                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }

                break;

            case 904:
                // service for bulk upload of area excel sheet                
                //if($this->request->is('post')):

                try {

                    $filename = $extra['filename'];
                    //$params['filename'] = $filename;
                    //$params['filename'] = $extra['filename']='C:\-- Projects --\D3A\dfa_devinfo_data_admin\webroot\data-import-formats\Area-mylist.xls';
                    $params['filename'] = $extra['filename'];
                    $params['component'] = 'Area';
                    $params['extraParam'] = [];


                    return $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);

                    // return $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsvForArea', $params, $dbConnection);
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }

                break;

            case 905:
                // service for bulk export  of area in excel sheet                
                try {
                    $type = $_REQUEST['type'];
                    if (strtolower($type) == _ICIUS) {
                        $returnData['data'] = $this->CommonInterface->serviceInterface('IcIus', 'exportIcius', [], $dbConnection);
                    } else if (strtolower($type) == _AREA) {
                        $params[] = $fields = [_AREA_AREA_ID, _AREA_AREA_NAME, _AREA_AREA_GID, _AREA_AREA_LEVEL, _AREA_PARENT_NId];
                        $params[] = $conditions = [];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Area', 'exportArea', $params, $dbConnection);
                    }
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }

                break;


            // service for adding databases
            case 1101:
                if ($this->request->is('post')) {

                    try {

                        $db_con = array(
                            'db_source' => $this->request->data['databaseType'],
                            'db_connection_name' => $this->request->data['connectionName'],
                            'db_host' => $this->request->data['hostAddress'],
                            'db_login' => $this->request->data['userName'],
                            'db_password' => $this->request->data['password'],
                            'db_port' => $this->request->data['port'],
                            'db_database' => $this->request->data['databaseName']
                        );

                        $jsondata = array(
                            _DATABASE_CONNECTION_DEVINFO_DB_CONN => json_encode($db_con)
                        );
                        $this->request->data[_DATABASE_CONNECTION_DEVINFO_DB_CONN] = $jsondata[_DATABASE_CONNECTION_DEVINFO_DB_CONN];

                        $jsondata = json_encode($jsondata);
                        $returnTestDetails = $this->Common->testConnection($jsondata);

                        $this->request->data[_DATABASE_CONNECTION_DEVINFO_DB_CREATEDBY] = $authUserId;
                        $this->request->data[_DATABASE_CONNECTION_DEVINFO_DB_MODIFIEDBY] = $authUserId;

                        $returnUniqueDetails = '';

                        if (isset($this->request->data['connectionName']) && !empty($this->request->data['connectionName'])) {

                            $returnUniqueDetails = $this->Common->uniqueConnection($this->request->data['connectionName']);
                        }
                        if ($chkSAStatus == true) {

                            if ($returnUniqueDetails === true) {

                                if ($returnTestDetails === true) {
                                    $db_con_id = $this->Common->createDatabasesConnection($this->request->data);
                                    if ($db_con_id) {
                                        $returnData['status'] = _SUCCESS;        // database added 
                                        //$returnData['database_id'] = $db_con_id;
                                    } else {
                                        $returnData['errCode'] = _ERR100;      // database not added due to server error 
                                    }
                                } else {
                                    $returnData['errCode'] = _ERR101; // Invalid database connection details 
                                }
                            } else {
                                $returnData['errCode'] = _ERR102; // connection name is  not unique 
                            }
                        } else {
                            $returnData['isAuthorised'] = false; // user should be super admin   
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }

                break;

            // service for checking unique connection name for db connection
            case 1102:
                if ($this->request->is('post')) {
                    try {

                        if (isset($this->request->data['connectionName'])) {

                            $connectionName = trim($this->request->data['connectionName']);
                            $returnUniqueDetails = $this->Common->uniqueConnection($connectionName);

                            if ($returnUniqueDetails === true) {
                                $returnData['status'] = _SUCCESS; // new connection name 

                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR102; // database connection name already exists
                            }
                        } else {
                            $returnData['errCode'] = _ERR103; // database connection name is empty 
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }
                break;

            // service for getting list of databases
            case 1103:
                try {
                    $databases = $this->Common->getDatabases();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $databases;
                    $returnData['responseKey'] = 'dbList';
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }
                break;

            // service for deletion of specific database 
            case 1104:
                if ($this->request->is('post')) {
                    try {

                        if (isset($dbId) && !empty($dbId)) {
                            if ($chkSAStatus == true) {
                                $returnDatabaseDetails = $this->Common->deleteDatabase($dbId, $authUserId);
                                $getDBDetailsById = $this->Common->getDbNameByID($dbId);

                                if ($returnDatabaseDetails) {
                                    $returnData['status'] = _SUCCESS; // records deleted
                                    $returnData['data'] = $getDBDetailsById;
                                    $returnData['responseKey'] = '';
                                } else {
                                    //$returnData['errCode'] = _ERR105; // // no  record deleted
                                    $returnData['errCode'] = _ERR100; // no  record deleted server error 
                                }
                            } else {
                                $returnData['isAuthorised'] = false; // unauthorized user should be super admin 
                            }
                        } else {
                            $returnData['errCode'] = _ERR106; // // db id is blank
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }
                break;

            // service for testing db connection
            case 1105:
                if ($this->request->is('post')) {

                    try {

                        $db_con = array(
                            'db_source' => $this->request->data['databaseType'],
                            'db_connection_name' => $this->request->data['connectionName'],
                            'db_host' => $this->request->data['hostAddress'],
                            'db_login' => $this->request->data['userName'],
                            'db_password' => $this->request->data['password'],
                            'db_port' => $this->request->data['port'],
                            'db_database' => $this->request->data['databaseName']
                        );
                        $data = array(_DATABASE_CONNECTION_DEVINFO_DB_CONN => json_encode($db_con)
                        );

                        $data = json_encode($data);
                        $returnTestDetails = $this->Common->testConnection($data);
                        if ($returnTestDetails === true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey '] = '';
                        } else {
                            $returnData['errCode'] = _ERR101; // //  Invalid database connection details
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }
                break;

            // service bascially  for testing of db details on basis of dbId
            case 1106:
                if ($this->request->is('post')) {
                    try {
                        if (isset($dbId) && !empty($dbId)) {
                            $returnSpecificDbDetails = $this->Common->getDbNameByID($dbId);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnSpecificDbDetails;
                            $returnData['responseKey'] = '';
                        } else {
                            $returnData['errCode'] = _ERR106;      // db id is blank
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }

                break;

            // service  for list role types 
            case 1108:

                try {
                    $listAllRoles = $this->UserCommon->listAllRoles();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $listAllRoles;
                    $returnData['responseKey'] = 'roleDetails';
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }
                break;

            // service for  listing of users belonging to specific db details with their roles and access  
            case 1109:
                if ($this->request->is('post')) {

                    try {
                        if (isset($dbId) && !empty($dbId)) {
                            $listAllUsersDb = $this->UserCommon->listAllUsersDb($dbId);
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'userList';
                            $returnData['data'] = $listAllUsersDb;
                        } else {
                            $returnData['errCode'] = _ERR106;      // db id is blank
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }
                break;

            // service for  deletion of  users with respect to associated db and roles respectively
            case 1200:

                if ($this->request->is('post')) {
                    try {
                        $userIds = '';
                        if (isset($this->request->data['userIds']) && !empty($this->request->data['userIds']))
                            $userIds = $this->request->data['userIds'];

                        if (isset($userIds) && !empty($userIds)) {
                            if (isset($dbId) && !empty($dbId)) {

                                $status = 0;
                                foreach ($userIds as $toId) {
                                    $acessStatus = $this->UserCommon->checkAuthorizeUser($toId, $dbId); //check authentication 
                                    if ($acessStatus == false) {
                                        $status = 1;
                                        break;
                                    }
                                }
                                if ($status == 0) {
                                    $deleteAllUsersDb = $this->UserCommon->deleteUserRolesAndDbs($userIds, $dbId);
                                    if ($deleteAllUsersDb > 0) {
                                        $returnData['status'] = _SUCCESS;
                                        $returnData['responseKey'] = '';
                                    } else {
                                        //$returnData['errCode'] = _ERR110;     // Not deleted   
                                        $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                                    }
                                } else {
                                    $returnData['isAuthorised'] = false;      //   Not allowed   to delete 
                                }
                            } else {
                                $returnData['errCode'] = _ERR106;         // db id is blank
                            }
                        } else {
                            $returnData['errCode'] = _ERR109;      // user  id is blank
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }
                break;

            // service for  modification of  users with respect to associated db and roles respectively
            case 1201:
                if ($this->request->is('post')) {

                    try {

                        $accessStatus = $this->UserCommon->checkAuthorizeUser($this->request->data[_USER_ID], $dbId, $this->request->data['roles']); //return true if allowed to modify
                        if ($accessStatus == true) {
                            $response = $this->UserCommon->saveUserDetails($this->request->data, $dbId);
                            if ($response === true) {
                                $returnData['status'] = _SUCCESS;
                            } else {
                                $returnData['errCode'] = $response;
                            }
                        } else {
                            $returnData['isAuthorised'] = false; //means user is restricted to perform action 
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }

                break;

            /*
             * service to get AutoCompleteDetails of users with email ,id and name 
             */

            case 1202:
                try {

                    $listAllUsersDb = $this->UserCommon->getAutoCompleteDetails();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $listAllUsersDb;
                    $returnData['responseKey'] = 'usersList';
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }
                break;

            // service to reset user password
            case 1203:
                if ($this->request->is('post')) {
                    if (!empty($authUserId)) {
                        $userId = $this->request->data['userId'];
                        if (!empty($userId)) {
                            $dt = $this->UserCommon->resetPassword($userId);

                            if ($dt['status'])
                                $returnData['status'] = _SUCCESS;
                            else
                                $returnData['errMsg'] = $dt['error'];
                        }
                    }
                }
                break;

            /* service to update password on activation link  */

            case 1204:

                if ($this->request->is('post')) {

                    try {

                        if (isset($_POST['key']) && !empty($_POST['key'])) {

                            $requestdata = array();
                            $encodedstring = trim($_POST['key']);
                            $decodedstring = base64_decode($encodedstring);
                            $explodestring = explode(_DELEM3, $decodedstring);

                            if ($explodestring[0] == _SALTPREFIX1 && $explodestring[2] == _SALTPREFIX2) {

                                $requestdata[_USER_MODIFIEDBY] = $requestdata[_USER_ID] = $userId = $explodestring[1];

                                if (isset($_POST['password']) && !empty($_POST['password']))
                                    $password = $requestdata[_USER_PASSWORD] = trim($_POST['password']);

                                $requestdata[_USER_STATUS] = _ACTIVE; // Activate user 

                                $activationStatus = $this->Common->checkActivationLink($userId);
                                if ($activationStatus > 0) {

                                    if (!empty($password)) {
                                        if (isset($userId) && !empty($userId)) {
                                            $returndata = $this->UserCommon->updatePassword($requestdata);
                                            if ($returndata > 0) {
                                                $returnData['status'] = _SUCCESS;
                                            } else {
                                                $returnData['errCode'] = _ERR100;      // password not updated due to server error   
                                            }
                                        } else {
                                            $returnData['errCode'] = _ERR109;      // user id  is empty 
                                        }
                                    } else {
                                        $returnData['errCode'] = _ERR113;         // Empty password   
                                    }
                                } else {
                                    $returnData['errCode'] = _ERR104;             // Activation link already used 
                                }
                            } else {
                                $returnData['errCode'] = _ERR117;            //  invalid key    
                            }
                        } else {
                            $returnData['errCode'] = _ERR115;           //  key is empty   
                        }
                    } catch (Exception $e) {

                        $returnData['errMsg'] = $e->getMessage();
                    }
                }

                break;

            //service to get  db roles of logged in user 
            case 1205:
                if ($this->request->is('post')) {

                    try {

                        $dataUsrDbRoles = $this->UserCommon->getUserDatabasesRoles($authUserId, $dbId);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $dataUsrDbRoles;
                        $returnData['responseKey'] = 'usrDbRoles';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }
                break;

            //service to get  session details of logged in user 
            case 1206:

                $returnData['status'] = _SUCCESS;
                $returnData['data']['id'] = session_id();
                $returnData['data']['user'][_USER_ID] = $authUserId;
                $returnData['data']['user'][_USER_NAME] = $this->Auth->user(_USER_NAME);
                $returnData['responseKey'] = '';
                if ($chkSAStatus == true)
                    $returnData['data']['user']['role'][] = _SUPERADMINNAME;
                else
                    $returnData['data']['user']['role'][] = '';

                if ($authUserId) {
                    $returnData['isAuthenticated'] = true;
                }
                //echo json_encode($returnData);
                break;

            // service for forgot password
            case 1207:
                if ($this->request->is('post')) {

                    //    if (true) {
                    try {
                        $email = $this->request->data['email'];
                        if (isset($email) && !empty($email)) {

                            $chkEmail = $this->UserCommon->checkEmailExists($email); //check email exists or not 1 means email exists 

                            if ($chkEmail > 0) {
                                //email  found in db 
                                $this->UserCommon->forgotPassword($email);
                                if ($dt['status'])
                                    $returnData['status'] = _SUCCESS;
                            } else {
                                $returnData['errCode'] = _ERR121;      // email not found   
                            }
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }



                break;

            case 2102: // GET -- Indicator Classification
                if ($this->request->is('post')):
                    try {
                        $fields = ['icNid' => _IC_IC_NID, 'icGid' => _IC_IC_GID, 'icName' => _IC_IC_NAME];
                        $conditions = [_IC_IC_NID => $this->request->data['icNid']];

                        $params['fields'] = $fields;
                        $params['conditions'] = $conditions;
                        $params['type'] = 'all';
                        $params['extra'] = ['first' => true];
                        $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getRecords', $params, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'icDetail';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 2104: // DELETE -- Indicator Classification
                if ($this->request->is('post')):
                    //if (true):
                    try {
                        //$this->request->data['icNid'] = 442;
                        $params['conditions'] = [_IC_IC_NID => $this->request->data['icNid']];
                        $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'deleteRecords', $params, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'ic';
                            $returnData['status'] = _SUCCESS;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 2105: // INSERT/UPDATE(using NID) -- Indicator Classification
                if ($this->request->is('post')):
                    //if (true):
                    try {
                        $fieldsArray[_IC_IC_TYPE] = isset($this->request->data['icType']) ? $this->request->data['icType'] : '';
                        $fieldsArray[_IC_IC_PARENT_NID] = isset($this->request->data['parentICId']) ? $this->request->data['parentICId'] : -1;
                        $fieldsArray[_IC_IC_NAME] = isset($this->request->data['icName']) ? $this->request->data['icName'] : '';
                        $fieldsArray[_IC_IC_GID] = isset($this->request->data['icGid']) ? $this->request->data['icGid'] : '';

                        if (isset($this->request->data['icNid']))
                            $fieldsArray[_IC_IC_NID] = $this->request->data['icNid'];

                        if (empty($fieldsArray[_IC_IC_TYPE]) || empty($fieldsArray[_IC_IC_NAME])) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $params = ['fieldsArray' => $fieldsArray];
                            $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'saveIC', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'ic';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 2106: // GET IC Parent Name
                if ($this->request->is('post')):
                    //if (true):
                    try {
                        $icNid = $this->request->data['icNid'];

                        if (empty($icNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $pnid = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getRecords', [[_IC_IC_PARENT_NID], [_IC_IC_NID => $icNid], 'all', ['first' => true]], $dbConnection);
                            if (!empty($pnid)) {
                                if ($pnid[_IC_IC_PARENT_NID] != '-1') {
                                    $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getRecords', [['icNid' => _IC_IC_NID, 'icGid' => _IC_IC_GID, 'icName' => _IC_IC_NAME], [_IC_IC_NID => $pnid[_IC_IC_PARENT_NID]], 'all', ['first' => true]], $dbConnection = '');
                                    if (empty($result)) {
                                        $result['error'] = _ERR155;
                                    }
                                } else {
                                    $result = [
                                        'icNid' => '-1',
                                        'icGid' => '',
                                        'icName' => ''
                                    ];
                                }
                                if (isset($result['error'])) {
                                    $returnData['errCode'] = $result['error'];
                                } else {
                                    $returnData['data'] = $result;
                                    $returnData['responseKey'] = 'icDetail';
                                    $returnData['status'] = _SUCCESS;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 2202: //Select Data using Conditions -- Indicator Unit Subgroup table

                $fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID];
                $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN' => [244, 25]];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getRecords', $params, $dbConnection);
                break;

            case 2204: //Delete Data using Conditions -- Indicator Unit Subgroup table
                $params['conditions'] = $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'deleteRecords', $params, $dbConnection);
                break;

            case 2205: //Insert New Data -- Indicator Unit Subgroup table
                if ($this->request->is('post')):

                    $this->request->data = [
                        _IUS_INDICATOR_NID => '384',
                        _IUS_UNIT_NID => 'Short name',
                        _IUS_SUBGROUP_VAL_NID => 'Some Keyword',
                    ];

                    $params['conditions'] = $conditions = $this->request->data;
                    $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'insertData', $params, $dbConnection);
                endif;


                break;

            case 2206: //Update Data using Conditions -- Indicator Unit Subgroup table

                $fields = [
                    _IUS_MIN_VALUE => 'Custom_test_name3',
                    _IUS_MAX_VALUE => 'SOME_003_TEST'
                ];

                $conditions = [_IUS_IUSNID => 11];

                if ($this->request->is('post')):
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'updateRecords', $params, $dbConnection);
                endif;

                break;



            case 2209: //get Tree Structure List
                //if ($this->request->is('post')):
                if (true):
                    // possible Types Area,IU,IUS,IC and ICIND
                    // $this->request->data['pnid']=485;//_TV_SGVAL
                    //$this->request->data['type'] = _TV_UNIT;//sgRecord

                    $type = (isset($this->request->data['type'])) ? $this->request->data['type'] : _TV_UNIT;
                    $parentId = (isset($this->request->data['pnid'])) ? $this->request->data['pnid'] : '-1';
                    $onDemand = (isset($this->request->data['onDemand'])) ? $this->request->data['onDemand'] : false;
                    // Incase of IC
                    $icType = (isset($this->request->data['icType'])) ? $this->request->data['icType'] : 'SC';
                    // in case of area extra parametr will come
                    $idVal = (isset($this->request->data['idVal'])) ? $this->request->data['idVal'] : '';
                    $showGroup = (isset($this->request->data['showGroup'])) ? $this->request->data['showGroup'] : false;
                    //$nodeLevel = (isset($this->request->data['nodeLevel'])) ? $this->request->data['nodeLevel'] : 0;
                    if (empty($parentId))
                        $parentId = -1;
                    if (empty($nodeLevel))
                        $nodeLevel = 0;

                    $returnData['data'] = $this->Common->getTreeViewJSON($type, $dbId, $parentId, $onDemand, $idVal, $icType, $showGroup);

                    if ($type == _TV_IU) {
                        $iCount = count(array_unique(array_column(array_column($returnData['data'], 'fields'), 'iName')));
                        $uCount = count(array_unique(array_column(array_column($returnData['data'], 'fields'), 'uName')));
                        $returnDatas[] = $returnData['data'];
                        $returnDatas[] = ['iCount' => $iCount, 'uCount' => $uCount];
                        $returnData['data'] = $returnDatas;
                        $returnData['responseKey'][] = $type;
                        $returnData['responseKey'][] = 'iuCount';
                    } else {
                        $returnData['responseKey'] = $type;
                    }

                    $returnData['status'] = _SUCCESS;
                endif;
                break;

            case 2211:  //get IUS Details FROM IU(S) GIDs -- Indicator Unit Subgroup table

                if ($this->request->is('post')):
                    //if (true):
                    //$this->request->data['iusId'] = '075362FE-0120-55C1-4520-914CFDA8FA0B{~}69299B62-FD0A-9936-3E72-688AD73B4709';
                    //$this->request->data['iusId'] = '075362FE-0120-55C1-4520-914CFDA8FA0B{~}69299B62-FD0A-9936-3E72-688AD73B4709{~}AAC7855A-3921-4824-AF8C-C1B1985875B0';

                    $iusGids = (isset($this->request->data['iusId'])) ? $this->request->data['iusId'] : '';
                    if (!empty($iusGids)) {
                        $validationsArray = [];
                        $iusGidsExploded = explode('{~}', $iusGids);

                        $iGid = $iusGidsExploded[0];
                        $uGid = $iusGidsExploded[1];
                        $sGid = isset($iusGidsExploded[2]) ? $iusGidsExploded[2] : '';

                        $params['conditions'] = ['iGid' => $iGid, 'uGid' => $uGid, 'sGid' => $sGid];
                        $params['extra'] = [];
                        $getIusNameAndGids = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getIusNameAndGids', $params, $dbConnection);

                        // Either Indicator, Unit or Subgroup GID not found
                        if (isset($getIusNameAndGids['error'])) {
                            $status = _FAILED;
                            $returnData['errMsg'] = $getIusNameAndGids['error'];
                        }// All IUS GIDs are found
                        else if ($getIusNameAndGids !== false) {
                            $extra['first'] = true;
                            $fields = [_MIUSVALIDATION_IS_TEXTUAL, _MIUSVALIDATION_MIN_VALUE, _MIUSVALIDATION_MAX_VALUE];
                            $conditions = [
                                _MIUSVALIDATION_INDICATOR_GID => $getIusNameAndGids['iGid'],
                                _MIUSVALIDATION_UNIT_GID => $getIusNameAndGids['uGid'],
                                _MIUSVALIDATION_SUBGROUP_GID => $getIusNameAndGids['sGid'],
                                _MIUSVALIDATION_DB_ID => $dbId
                            ];
                            $IusValidationsRecordExist = $this->MIusValidations->getRecords($fields, $conditions, 'all', $extra);

                            // Validation Record already Exists
                            if (!empty($IusValidationsRecordExist)) {
                                $isTextual = ($IusValidationsRecordExist[_MIUSVALIDATION_IS_TEXTUAL] == '1') ? true : false;
                                $minimumValue = $IusValidationsRecordExist[_MIUSVALIDATION_MIN_VALUE];
                                $maximumValue = $IusValidationsRecordExist[_MIUSVALIDATION_MAX_VALUE];
                                $isMinimum = ($minimumValue === NULL || $minimumValue === '') ? false : true;
                                $isMaximum = ($maximumValue === NULL || $maximumValue === '') ? false : true;
                                $validationsArray = [
                                    'isTextual' => $isTextual,
                                    'isMinimum' => $isMinimum,
                                    'isMaximum' => $isMaximum,
                                    'minimumValue' => $minimumValue,
                                    'maximumValue' => $maximumValue,
                                ];
                            }
                            $status = _SUCCESS;
                        }
                        $return = array_merge($getIusNameAndGids, $validationsArray);
                        $returnData['data'] = $return;
                    } else {
                        $status = _FAILED;
                        $returnData['errMsg'] = false;
                    }

                    $returnData['status'] = $status;
                    $returnData['responseKey'] = 'iusValidations';
                    $returnData['errCode'] = '';
                endif;
                break;

            case 2212: //Save IUS Details FROM IU(S) GIDs -- Indicator Unit Subgroup table
                if ($this->request->is('post')):
                    //$this->request->data['iusId'] = ['275362FE-0120-55C1-4520-914CFDA8FA0B{~}69299B62-FD0A-9936-3E72-688AD73B4709{~}AAC7855A-3921-4824-AF8C-C1B1985875B0'];

                    $status = _FAILED;
                    $returnData['errMsg'] = false;
                    $returnData['errCode'] = '';

                    $iusGids = (isset($this->request->data['iusId'])) ? $this->request->data['iusId'] : '';
                    if (!empty($iusGids)) {

                        $extra = [];
                        $extra['isTextual'] = (isset($this->request->data['isTextual'])) ? $this->request->data['isTextual'] : 0;
                        $extra['minimumValue'] = (isset($this->request->data['minimumValue'])) ? $this->request->data['minimumValue'] : null;
                        $extra['maximumValue'] = (isset($this->request->data['maximumValue'])) ? $this->request->data['maximumValue'] : null;
                        $check = $this->Common->addUpdateIUSValidations($dbId, $iusGids, $extra);

                        if (isset($check['error'])) {
                            $returnData['errCode'] = $check['error'];
                        } else if ($check) {
                            $status = _SUCCESS;
                            $returnData['errMsg'] = true;
                        }
                    }

                    $returnData['status'] = $status;
                    $returnData['responseKey'] = 'iusValidationsSave';
                endif;
                break;

            // Delete IU or IUS
            case 2213:
                if ($this->request->is('post')):
                    //$this->request->data['iusId'] = ['275362FE-0120-55C1-4520-914CFDA8FA0B{~}69299B62-FD0A-9936-3E72-688AD73B4709{~}AAC7855A-3921-4824-AF8C-C1B1985875B0'];

                    $iusGids = (isset($this->request->data['iusId'])) ? $this->request->data['iusId'] : '';
                    if (!empty($iusGids)) {

                        $check = $this->Common->deleteIUS($dbConnection, $iusGids);

                        if ($check) {
                            $status = _SUCCESS;
                            $returnData['errMsg'] = true;
                        }
                    }

                    $returnData['status'] = $status;
                    $returnData['responseKey'] = 'deleteIUS';
                    $returnData['errCode'] = '';
                endif;
                break;

            /* Commented now
              // Will be used for CRUD opartions

              case 2302: //Select Data using Conditions -- ICIUS table

              $fields = [_ICIUS_IC_NID, _ICIUS_IUSNID];
              $conditions = [_ICIUS_IC_NID . ' IN' => [244, 25]];

              $params['fields'] = $fields;
              $params['conditions'] = $conditions;

              $returnData = $this->CommonInterface->serviceInterface('IcIus', 'getRecords', $params, $dbConnection);
              break;

              case 2304: //Delete Data using Conditions -- ICIUS table
              //deleteRecords(array $conditions)
              $params['conditions'] = $conditions = [_ICIUS_IC_NID . ' IN' => ['TEST_GID', 'TEST_GID2']];
              $returnData = $this->CommonInterface->serviceInterface('IcIus', 'deleteRecords', $params, $dbConnection);
              break;

              case 2305: //Insert New Data -- ICIUS table
              if ($this->request->is('post')):
              $this->request->data = [
              _ICIUS_IUSNID => 'Short name',
              _ICIUS_IC_NID => 'Some Keyword',
              ];
              //insertData(array $fieldsArray = $this->request->data)
              $params['conditions'] = $conditions = $this->request->data;
              $returnData = $this->CommonInterface->serviceInterface('IcIus', 'insertData', $params, $dbConnection);
              endif;
              break;

              case 2306: //Update Data using Conditions -- ICIUS table

              $fields = [
              _ICIUS_IUSNID => 'Custom_test_name3',
              _ICIUS_IC_NID => 'SOME_003_TEST'
              ];
              $conditions = [_IUS_IUSNID => 11];
              if ($this->request->is('post')):
              //updateRecords(array $fields, array $conditions)
              $params['fields'] = $fields;
              $params['conditions'] = $conditions;
              $returnData = $this->CommonInterface->serviceInterface('IcIus', 'updateRecords', $params, $dbConnection);
              endif;
              break; */


            case 2307: //Bulk Insert/Update Data -- ICIUS table
                if ($this->request->is('post')):
                    //if (true):
                    //$params['filename'] = $filename = 'C:\-- Projects --\xls\Temp_Selected_ExcelFile.xls';
                    $params['filename'] = $extra['filename'];
                    $params['component'] = 'IcIus';
                    $params['extraParam'] = [];
                    return $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                endif;

                break;

            case 2401: //Upload Files

                if ($this->request->is('post')):
                    //if (true):
                    try {
                        $extraParam = [];
                        $dbDetails = json_decode($dbConnection, true);
                        $dbName = $dbDetails['db_connection_name'];

                        $seriveToCall = strtolower($this->request->data['type']);
                        $allowedExtensions = ['xls', 'xlsx'];

                        // Kept here to include other params like allowed ext as well
                        switch ($seriveToCall):
                            case _ICIUS:
                                $case = 2307;
                                $module = _TEMPLATEVAL;
                                $extraParam['createLog'] = true;
                                $extraParam['subModule'] = _MODULE_NAME_ICIUS;
                                break;
                            case _AREA:
                                $case = 904;
                                $module = _TEMPLATEVAL;
                                $extraParam['subModule'] = _MODULE_NAME_AREA;
                                break;
                            case _DES:
                                $case = 2408;
                                $module = _DATAENTRYVAL;
                                $extraParam['subModule'] = _MODULE_NAME_DATAENTRY;
                                $extraParam['dest'] = _DES_PATH;
                                break;
                        endswitch;

                        $extraParam['dbName'] = $dbName;

                        $filePaths = $this->Common->processFileUpload($_FILES, $allowedExtensions, $extraParam);

                        if (isset($filePaths['error'])) {
                            $returnData['errMsg'] = $filePaths['error'];
                        } else {
                            //-- TRANSAC Log
                            $fieldsArray = [
                                _MTRANSACTIONLOGS_DB_ID => $dbId,
                                _MTRANSACTIONLOGS_ACTION => 'IMPORT',
                                _MTRANSACTIONLOGS_MODULE => $module,
                                _MTRANSACTIONLOGS_SUBMODULE => $seriveToCall,
                                _MTRANSACTIONLOGS_IDENTIFIER => '',
                                _MTRANSACTIONLOGS_STATUS => _STARTED
                            ];
                            $LogId = $this->TransactionLogs->createRecord($fieldsArray);

                            //Actual Service Call
                            $extra['filename'] = $filePaths[0];
                            $return = $this->serviceQuery($case, $extra);


                            if (isset($return['error'])) {
                                //-- TRANSAC Log
                                $fieldsArray = [_MTRANSACTIONLOGS_STATUS => _FAILED];
                                $conditions = [_MTRANSACTIONLOGS_ID => $LogId];
                                $this->TransactionLogs->updateRecord($fieldsArray, $conditions);

                                $returnData['errCode'] = $return['error'];
                            } else {
                                //-- TRANSAC Log
                                $logFileName = basename($return);
                                $fieldsArray = [_MTRANSACTIONLOGS_STATUS => _SUCCESS, _MTRANSACTIONLOGS_IDENTIFIER => $logFileName];
                                $conditions = [_MTRANSACTIONLOGS_ID => $LogId];
                                $this->TransactionLogs->updateRecord($fieldsArray, $conditions);

                                $return = _WEBSITE_URL . _LOGS_PATH_WEBROOT . '/' . $logFileName;
                                $returnData['data'] = $return;
                                $returnData['responseKey'] = _IMPORT_LOG;
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            case 2402: //Export ICIUS
                if ($this->request->is('post')):

                    $returnData['data'] = $this->CommonInterface->serviceInterface('CommonInterface', 'exportIcius', [], $dbConnection);

                    $returnData['status'] = 'success';
                    $returnData['responseKey'] = 'iciusExport';
                    $returnData['errCode'] = '';
                    $returnData['errMsg'] = '';
                endif;
                break;

            // service to get search data on basis of IUS ,timeperiod and area 
            case 2403:

                if ($this->request->is('post')):
                    //if (true):
                    try {
                        /* $iusgidArray=['BA8EDD9C-2C2B-9654-59D3-45D2FCBBFB2F{~}F215AB90-C32D-454E-39F6-CB96CB32F932'];
                          //$iusgidArray=['790eacc9-57d3-4422-9be5-cf8ae96944dc{~}7fe05dc0-714b-4af5-9d6e-f3d531a8f408{~}8f1910bb-f9d8-479c-a274-123f6f4f6bc2'];
                          $areaNidArray = ['18274'];
                          $timePeriodNidArray = ['2']; */

                        $areaNidArray = $this->request->data['areaNid'];
                        $timePeriodNidArray = $this->request->data['tp'];
                        $iusgidArray = $this->request->data['iusGids'];

                        $return = $this->Common->deSearchIUSData($areaNidArray, $timePeriodNidArray, $iusgidArray, ['dbConnection' => $dbConnection, 'dbId' => $dbId]);
                        extract($return);

                        $returnData['status'] = _SUCCESS;
                        // Ius Data
                        $returnData['responseKey'][] = 'iusData';
                        $returnData['data'][] = $iusData;

                        // Ius Validations Data
                        $returnData['responseKey'][] = 'iusValidations';
                        $returnData['data'][] = $iusValidations;

                        // Ius List data
                        $returnData['responseKey'][] = 'iusList';
                        $returnData['data'][] = $iusList;
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                endif;
                break;

            //service for saving data entry 
            case 2404:
                if (true) {
                    //if ($this->request->is('post')) {
                    try {

                        $jsonData = (isset($_POST['dataEntry'])) ? json_encode($_POST['dataEntry']) : '';
                        $params = ['dbId' => $dbId, 'jsonData' => $jsonData, $validation = true, $customLog = true, $isDbLog = true];
                        $datavalue = $this->CommonInterface->serviceInterface('Data', 'saveData', $params, $dbConnection);

                        $deletedata = (isset($_POST['deleteData'])) ? json_encode($_POST['deleteData']) : '';
                        if (isset($deletedata)) {
                            $params = ['dbId' => $dbId, 'data' => $deleteData];
                            $remData = $this->CommonInterface->serviceInterface('Data', 'deleteData', $params);
                        }

                        if ($datavalue['status'] == true) {

                            $data = $this->Common->writeLogFile($datavalue['customLogJson'], $dbId);
                            //pr($datavalue);die; 
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                        } else {
                            $returnData['errCode'] = '';
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                } //endif;
                break;

            case 2405: // INSERT - Source

                if ($this->request->is('post')):
                    // if (true): 
                    try {
                        /* $this->request->data['publisher'] = 'mk953';$this->request->data['year']= '2012';
                          $this->request->data['title']= 'mk758';$this->request->data['shortName']= 'mk748'; */

                        $fieldsArray = [
                            'publisher' => $this->request->data['publisher'],
                            'title' => $this->request->data['title'],
                            'year' => $this->request->data['year'],
                            'shortName' => $this->request->data['shortName'],
                            'srcNid' => (isset($this->request->data['srcNid'])) ? $this->request->data['srcNid'] : ''
                        ];

                        $params = ['fieldsArray' => $fieldsArray];
                        $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'manageSource', $params, $dbConnection);
                        if (!empty($result) || $result !== false) {
                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error']; // source or shortname already exists 
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = _SOURCE;
                                $returnData['status'] = _SUCCESS;
                            }
                        } else {
                            $returnData['errCode'] = _ERR131; //publisher is empty 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }


                endif;
                break;

            case 2406:
                //if(true):
                if ($this->request->is('post')) {
                    try {

                        $userId = $this->request->data['userId'];
                        if ($dbId) {
                            if (isset($userId) && !empty($userId)) {
                                $data = $this->UserCommon->listSpecificUsersdetails($userId, $dbId);
                                $returnData['status'] = _SUCCESS;
                                $returnData['data'] = $data;
                                $returnData['responseKey'] = 'userDetails';
                            } else {
                                $returnData['errCode'] = _ERR109;        // user id is blank
                            }
                        } else {
                            $returnData['errCode'] = _ERR106;      // db id is blank
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                } //endif;
                break;

            case 2407:
                if ($this->request->is('post')):
                    //if (true):
                    $fields = [_IC_IC_NID, _IC_IC_PARENT_NID, _IC_IC_GID, _IC_IC_NAME, _IC_PUBLISHER, _IC_DIYEAR];
                    $params = ['fields' => $fields, [], 'all', ['getAll' => true]];
                    $returnData['data'] = $this->Common->getSourceBreakupDetails($params, $dbConnection);
                    $returnData['responseKey'] = _SOURCE_BREAKUP_DETAILS;
                    $returnData['status'] = _SUCCESS;
                endif;
                break;

            case 2408:
                //if ($this->request->is('post')):
                if (true):
                    $filename = $extra['filename'];
                    //$params['filename'] = $filename = 'C:\-- Projects --\xls\DES\MDG5B_DES_r1.xls';
                    //$params['dbId'] = $dbId;
                    //return $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkImportDes', $params, $dbConnection);
                    return $returnData = $this->DataEntry->importDes($filename, $dbId, $dbConnection);
                    return 'log.html';
                endif;
                break;

            // service to delete the source and its corresponding data and icius  
            case 2409:
                //if ($this->request->is('post')):
                if (true):

                    try {
                        $params['srcNid'] = (isset($_POST['srcNid'])) ? $_POST['srcNid'] : '';
                        $Data = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'deleteSourceData', $params, $dbConnection);
                        if ($Data == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            //$returnData['errCode'] = _ERR110;     // Not deleted   
                            $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;


            // service to delete the timperiod  and its corresponding data
            case 2410:
                if ($this->request->is('post')):
                    //if (true):

                    try {
                        $params['tpNId'] = (isset($_POST['tpNid'])) ? $_POST['tpNid'] : '';

                        $Data = $this->CommonInterface->serviceInterface('Timeperiod', 'deleteTimeperiodData', $params, $dbConnection);

                        if ($Data == true) {

                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            //$returnData['errCode'] = _ERR110;     // Not deleted   
                            $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 2411: // get source  by id 
                // if ($this->request->is('post')):
                if (true):
                    try {
                        $params = ['srcNid' => (isset($_POST['srcNid'])) ? $_POST['srcNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getSourceByID', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'sourceDetail';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 2412: // get DB connnection details by DbID
                try {
                    if (isset($dbConnection) && !empty($dbConnection)) {
                        $dbConDetails = json_decode($dbConnection, true);
                        //debug($dbConDetails);
                        $dbDetails = array('id' => $dbId, 'databaseType' => $dbConDetails['db_source'], 'connectionName' => $dbConDetails['db_connection_name'], 'hostAddress' => $dbConDetails['db_host'], 'databaseName' => $dbConDetails['db_database'], 'port' => $dbConDetails['db_port']);

                        //$dbDetails = array_merge($dbDetails, $dbConDetails);                  
                        //  unset($dbDetails['db_password']);
                        $returnData['data'] = $dbDetails;
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'dbConDetails';
                    }
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }
                break;

            // service for updating databases
            case 2413:
                //$this->request->is('post')          
                if (true) {
                    // $this->request->data = $this->request->query;
                    //pr($this->request->data );die;
                    try {

                        $loggedInUserId = $this->Auth->User(_USER_ID);
                        if ($this->UserCommon->checkSAAccess()) {

                            $response = $this->Common->saveDbConnectionDetails($this->request->data, $dbId);


                            if ($response === true) {
                                $returnData['status'] = _SUCCESS;
                            } else {
                                $returnData['errCode'] = $response;
                            }
                        } else {
                            $returnData['isAuthorised'] = false;
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }

                break;

            // service to get transaction log
            case 2414:
                try {
                    $this->request->data = $_REQUEST;
                    //Filters
                    $formDate = $this->request->data('fromDate');
                    $toDate = $this->request->data('toDate');
                    $userId = $this->request->data('userId');
                    $module = $this->request->data('txnModule');
                    $action = $this->request->data('action');
                    $status = $this->request->data('status');

                    $conditions = array();

                    $conditions[_MTRANSACTIONLOGS_DB_ID] = $dbId;
                    if ($userId != '')
                        $conditions[_MTRANSACTIONLOGS_USER_ID] = $userId;
                    if ($module != '')
                        $conditions[_MTRANSACTIONLOGS_MODULE] = $module;
                    if ($action != '')
                        $conditions[_MTRANSACTIONLOGS_ACTION] = $action;
                    if ($status != '')
                        $conditions[_MTRANSACTIONLOGS_STATUS] = $status;

                    if (!empty($formDate) || !empty($toDate)) {

                        if (!empty($formDate)) {
                            $conditions[_MTRANSACTIONLOGS_CREATED . ' >= '] = $formDate;
                        }
                        if (!empty($toDate)) {

                            $conditions[_MTRANSACTIONLOGS_CREATED . ' <= '] = $toDate;
                        }
                    } else {
                        //curr week data

                        $wk_start = date('Y-m-d', strtotime('Monday This week'));
                        $wk_end = date('Y-m-d', strtotime('Sunday This week'));
                        $conditions[_MTRANSACTIONLOGS_CREATED . ' >= '] = $wk_start;
                        $conditions[_MTRANSACTIONLOGS_CREATED . ' <= '] = $wk_end;
                    }


                    // pr($extra);
                    //  die;
                    $fields = array('id' => _MTRANSACTIONLOGS_ID, 'userId' => _MTRANSACTIONLOGS_USER_ID, 'action' => _MTRANSACTIONLOGS_ACTION, 'txnModule' => _MTRANSACTIONLOGS_MODULE, 'submodule' => _MTRANSACTIONLOGS_SUBMODULE, 'identifier' => _MTRANSACTIONLOGS_IDENTIFIER, 'previouValue' => _MTRANSACTIONLOGS_PREVIOUSVALUE, 'newValue' => _MTRANSACTIONLOGS_NEWVALUE, 'status' => _MTRANSACTIONLOGS_STATUS, 'description' => _MTRANSACTIONLOGS_DESCRIPTION, 'created' => _MTRANSACTIONLOGS_CREATED);
                    // pr($extra);exit;

                    $results = $this->TransactionLogs->getRecords($fields, $conditions, 'all');
                    if (!empty($results) && is_array($results)) {
                        foreach ($results as &$row) {
                            // debug($row);exit;
                            $txn_created = $row['userId'];
                            $row['created'] = strtotime($row['created']);
                            $rowUserId = $row['userId'];
                            $userRow = $this->UserCommon->getUserDetails([_USER_NAME], [_USER_ID => $rowUserId]);
                            if (!empty($userRow))
                                $row['userName'] = $userRow['0'][_USER_NAME];
                        }
                    }

                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $results;
                    $returnData['responseKey'] = 'dbLog';
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }

                break;

            case 2415:
                //if($this->request->is('post')){ 
                if (true) {

                    $areaNidArray = isset($this->request->data['areaNid']) ? $this->request->data['areaNid'] : [];
                    $timePeriodNidArray = isset($this->request->data['tp']) ? $this->request->data['tp'] : [];
                    $iusgidArray = isset($this->request->data['iusGids']) ? $this->request->data['iusGids'] : [];

                    /* $areaNidArray = [19];
                      $iusgidArray = ['c65202bd-73f2-4dea-ac4b-47c5c5d80a6c{~}9ac1c75e-46b3-4347-98a3-fad396e61fc4', '9ad8c74d-c21d-4db7-b380-482195ffc2a4{~}B602B58B-6879-4188-9D49-DD833281FE4E'];
                      $timePeriodNidArray = [29]; */

                    $sheetLink = $this->DataEntry->exportDes($areaNidArray, $timePeriodNidArray, $iusgidArray, ['dbConnection' => $dbConnection, 'dbId' => $dbId]);
                    $returnFilePath = _WEBSITE_URL . _DES_PATH_WEBROOT . '/' . basename($sheetLink);

                    $returnData['data'] = $returnFilePath;
                    $returnData['responseKey'] = _EXPORT_DES;
                    $returnData['status'] = _SUCCESS;
                }
                break;

            // service to delete transaction log
            case 2416:
                //true //
                if (true) {
                    try {
                        // $this->request->data = $this->request->query;
                        if ($dbId) {

                            $loggedInUserId = $this->Auth->User(_USER_ID);
                            if ($this->UserCommon->checkSAAccess()) {

                                $transactionID = $this->request->data(_MTRANSACTIONLOGS_ID) ? $this->request->data(_MTRANSACTIONLOGS_ID) : NULL;

                                $Data = $this->TransactionLogs->deleteTransactiondata($transactionID, $dbId);
                                if ($Data == true) {

                                    $returnData['status'] = _SUCCESS;
                                    $returnData['responseKey'] = '';
                                } else {
                                    $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                                }
                            } else {
                                $returnData['isAuthorised'] = false;
                            }
                        }
                    } catch (Exception $ex) {
                        $returnData['errMsg'] = $e->getMessage();
                    }
                }

                break;

            case 2417:
                // service for bulk export  of unit  in excel sheet                
                //if($this->request->is('post')):

                if (true):
                    try {
                        $type = '';
                        $module = '';
                        $type = (isset($_POST['type'])) ? $_POST['type'] : _SUBGRPVALEXPORT;
                        if (!empty($dbId)) {
                            if (!empty($type)) {

                                if (strtolower($type) == _UNITEXPORT) {
                                    $params = ['dbId' => $dbId];
                                    $expFile = $this->CommonInterface->serviceInterface('Unit', 'exportUnitDetails', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _UNIT_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'unitExport';
                                    $module = _MODULE_NAME_UNIT;
                                }
                                if (strtolower($type) == _INDIEXPORT) {
                                    $status = (isset($_POST['status'])) ? $_POST['status'] : false;
                                    $params = ['status' => $status, 'dbId' => $dbId];
                                    $expFile = $this->CommonInterface->serviceInterface('Indicator', 'exportIndicatorDetails', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _INDICATOR_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'indiExport';
                                    $module = _MODULE_NAME_INDICATOR;
                                }
                                if (strtolower($type) == _SUBGRPVALEXPORT) {
                                    //$status = (isset($_POST['status']))?$_POST['status']:false;
                                    $params = ['dbId' => $dbId];
                                    $expFile = $this->CommonInterface->serviceInterface('SubgroupVals', 'exportSubgroupValDetails', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _SUBGROUPVAL_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'subgrpExport';
                                    $module = _MODULE_NAME_SUBGROUPVAL;
                                }


                                if ($returnData['data']) {
                                    $this->TransactionLogs->createLog(_EXPORT, _TEMPLATEVAL, $module, basename($expFile), _DONE);
                                    $returnData['status'] = _SUCCESS;
                                    $returnData['responseKey'] = $reponse;
                                }
                            } else {
                                $returnData['errCode'] = _ERR139; //type blank							
                            }
                        } else {
                            $returnData['errCode'] = _ERR106;    //dbid  blank
                        }
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;

            case 2418;
                if (true):
                    try {

                        $params = ['iNid' => (isset($_POST['iNid'])) ? $_POST['iNid'] : '70267'];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Indicator', 'getIndicatorById', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'indDetail';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                // $returnData['data'] = $this->CommonInterface->serviceInterface('Indicator', 'getIndicatorById', ['iNid'=>70267], $dbConnection);

                endif;
                break;

            case 2419: //To list database language list
                if ($dbConnection):
                    try {

                        $params = ['fields' => ['nid' => _LANGUAGE_LANGUAGE_NID, 'name' => _LANGUAGE_LANGUAGE_NAME, 'code' => _LANGUAGE_LANGUAGE_CODE, 'isDefault' => _LANGUAGE_LANGUAGE_DEFAULT], 'conditions' => []];
                        $lang_list = $this->CommonInterface->serviceInterface('Language', 'getRecords', $params, $dbConnection);
                        $returnData['data'] = $lang_list;

                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'langDetail';
                    } catch (Exception $e) {
                        $returnData['errMsg'] = $e->getMessage();
                    }

                endif;
                break;
            case 2420: //To export language database
                //if($this->request->is('post')){ 
                if (true) {
                    $this->request->data = $_REQUEST;
                    $from_lang_code = isset($this->request->data['fromLangCode']) ? $this->request->data['fromLangCode'] : 'en';
                    $from_lang_name = isset($this->request->data['fromLangName']) ? $this->request->data['fromLangName'] : 'English [en]';
                    $to_lang_code = isset($this->request->data['toLangCode']) ? $this->request->data['toLangCode'] : '';
                    $to_lang_name = isset($this->request->data['toLangName']) ? $this->request->data['toLangName'] : '';


                    if ($from_lang_code != '' && $from_lang_name != '' && $to_lang_code != '' && $to_lang_name != '') {

                        $params = [];
                        $params['fromLangCode'] = $from_lang_code;
                        $params['fromLangName'] = $from_lang_name;
                        $params['toLangCdoe'] = $to_lang_code;
                        $params['toLangName'] = $to_lang_name;

                        $returnFilePath = $this->CommonInterface->serviceInterface('Language', 'export_lang_database', $params, $dbConnection);

                        $returnData['data'] = $returnFilePath;
                        $returnData['responseKey'] = 'exportLanguageFilePath';
                        $returnData['status'] = _SUCCESS;
                    } else {
                        $returnData['errCode'] = _ERR135;    //Missing parameters
                    }
                }
                break;
                
                
            default:
                break;

        endswitch;

        return $this->service_response($returnData, $convertJson, $dbId, $chkSAStatus);
    }

    public function service_response($response, $convertJson = _YES, $dbId, $superAdminStatus = false) {

        // Initialize Result		
        $success = false;
        $isAuthenticated = false;
        $isAuthorised = true;
        $isSuperAdmin = false;
        $errCode = '';
        $errMsg = '';
        $dataUsrId = '';
        $dataUsrUserId = '';
        $dataUsrUserName = '';
        $dataUsrUserRole = [];
        $dataDbDetail = '';
        $dataUsrDbRoles = [];

        if ($this->Auth->user(_USER_ID)) {

            $isAuthenticated = true;
            $dataUsrId = session_id();
            $dataUsrUserId = $this->Auth->user(_USER_ID);
            $dataUsrUserName = $this->Auth->user(_USER_NAME);
            $role_id = $this->Auth->user(_USER_ROLE_ID);

            if ($superAdminStatus == true):
                $isSuperAdmin = true;
                $rdt = $this->Common->getRoleDetails($role_id);
                $dataUsrUserRole[] = $rdt[1];
            endif;

            if ($dbId):
                $returnSpecificDbDetails = $this->Common->getDbNameByID($dbId);
                $dataDbDetail = $returnSpecificDbDetails;

                if ($superAdminStatus == false):
                    $dataUsrDbRoles = $this->UserCommon->getUserDatabasesRoles($dataUsrUserId, $dbId);
                endif;
            endif;
        }

        if (isset($response['status']) && $response['status'] == _SUCCESS):
            $success = true;
            $responseData = isset($response['data']) ? $response['data'] : [];
        else:
            $errCode = isset($response['errCode']) ? $response['errCode'] : '';
            $errMsg = isset($response['errMsg']) ? $response['errMsg'] : '';
        endif;

        if (isset($response['isAuthorised']) && $response['isAuthorised'] == false) {
            $isAuthorised = false;
        }

        // Set Result
        $returnData['success'] = $success;
        $returnData['isAuthenticated'] = $isAuthenticated;
        $returnData['isAuthorised'] = $isAuthorised;
        $returnData['isSuperAdmin'] = $isSuperAdmin;
        $returnData['err']['code'] = $errCode;
        $returnData['err']['msg'] = $errMsg;
        $returnData['data']['usr']['id'] = $dataUsrId;
        $returnData['data']['usr']['user']['id'] = $dataUsrUserId;
        $returnData['data']['usr']['user']['name'] = $dataUsrUserName;
        $returnData['data']['usr']['user']['role'] = $dataUsrUserRole;
        $returnData['data']['dbDetail'] = $dataDbDetail;
        $returnData['data']['usrDbRoles'] = $dataUsrDbRoles;

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

}
