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
ini_set('memory_limit', '5000M');
ini_set('max_input_vars', 5000000);

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
        
        try {

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
                
                //-- TEST CASE
                case 'test':
                    //$returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getIusCount', [], $dbConnection);
                    //$returnData = $this->Template->getIcDetails(['icNid' => 575], [], $dbConnection);
                    //$returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                    //debug($returnData);
                    //exit;
                    break;

                // ================== INDICATOR ================== //
                
                case 105: // INSERT/UPDATE -- INDICATOR
                    if ($this->request->is('post')):
                        $indicatorDetails = [
                            _INDICATOR_INDICATOR_NID => (isset($_POST['iNid'])) ? $_POST['iNid'] : '',
                            _INDICATOR_INDICATOR_NAME => (isset($_POST['iName'])) ? $_POST['iName'] : '',
                            _INDICATOR_INDICATOR_GID => (isset($_POST['iGid'])) ? $_POST['iGid'] : ''
                        ];
                        $unitNids = (isset($_POST['uNid'])) ? $_POST['uNid'] : '';
                        $subgrpNids = (isset($_POST['sNid'])) ? $_POST['sNid'] : '';

                        $metadataArray = (isset($_POST['metadata'])) ? $_POST['metadata'] : '';
                        $metadataArray = json_encode($metadataArray);

                        $params[] = ['indicatorDetails' => $indicatorDetails, 'unitNids' => $unitNids, 'subgrpNids' => $subgrpNids, 'metadataArray' => $metadataArray];
                        $result = $this->CommonInterface->serviceInterface('Indicator', 'manageIndicatorData', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }                        
                    endif;

                break;

                case 108: // GET -- INDICATOR using iuNid

                    if ($this->request->is('post')):
                        $iuNid = (isset($_POST['iuNid'])) ? $_POST['iuNid'] : '';
                        if (!empty($iuNid) && !empty($dbId)) {
                            $params = ['iuNid' => $iuNid];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('Indicator', 'getIndicatorById', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'indDetail';
                        } else {
                            $returnData['errCode'] = _ERR145;  //invalid request 
                        }                        
                    endif;

                break;

                case 109: // DELETE -- INDICATOR and associations using iuNid
                    if ($this->request->is('post')):
                        $iuNid = (isset($_POST['iuNid'])) ? $_POST['iuNid'] : '';
                        if (!empty($iuNid) && !empty($dbId)) {
                            $params = ['iuNid' => $iuNid];
                            $result = $this->CommonInterface->serviceInterface('Indicator', 'deleteIndicatordata', $params, $dbConnection);
                            if ($result == true) {

                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                //  Not deleted  due server error 
                                $returnData['errCode'] = _ERR100;
                            }
                        } else {
                            //invalid request 
                            $returnData['errCode'] = _ERR145;
                        }
                    endif;

                break;

                case 110: // GET METADATA -- INDICATOR
                    if ($this->request->is('post')):
                        $params = ['iNid' => (isset($_POST['iNid'])) ? $_POST['iNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Metadata', 'getMetaDataDetails', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'metaDetail';                        
                    endif;

                break;

                case 111: // DELETE METADATA -- INDICATOR
                    if ($this->request->is('post')):
                        $params = ['iNid' => (isset($_POST['iNid'])) ? $_POST['iNid'] : '', 'nId' => (isset($_POST['nId'])) ? $_POST['nId'] : ''];
                        $result = $this->CommonInterface->serviceInterface('Metadata', 'deleteMetaData', $params, $dbConnection);
                        
                        if ($result == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            // Not deleted due server error
                            $returnData['errCode'] = _ERR100;      
                        }                        
                    endif;

                break;
                
                // ================== UNIT ================== //

                case 204: // DELETE -- UNIT
                    if ($this->request->is('post')):
                        // = [_UNIT_UNIT_NID . ' IN' => $uNid];
                        $uNid = (isset($_POST['uNid'])) ? $_POST['uNid'] : '';
                        $params['uNid'] = $uNid;
                        $result = $this->CommonInterface->serviceInterface('Unit', 'deleteUnitdata', $params, $dbConnection);
                        
                        if ($result == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            // Not deleted  due server error  
                            $returnData['errCode'] = _ERR100;      
                        }
                    endif;

                break;

                case 205: // INSERT/UPDATE -- UNIT
                    if ($this->request->is('post')):
                        $posteddata = [
                            _UNIT_UNIT_NAME => $this->request->data['uName'], 
                            _UNIT_UNIT_GID => $this->request->data['uGid'], 
                            _UNIT_UNIT_NID => (isset($this->request->data['uNid'])) ? $this->request->data['uNid'] : '',
                            _UNIT_UNIT_GLOBAL => '0'
                        ];

                        $params[] = $posteddata;
                        $result = $this->CommonInterface->serviceInterface('Unit', 'manageUnitdata', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                            $returnData['status'] = _SUCCESS;
                        }                        
                    endif;

                break;

                case 208: // GET -- UNIT
                    if ($this->request->is('post')):                        
                        $params = ['uNid' => (isset($_POST['uNid'])) ? $_POST['uNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Unit', 'getUnitById', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'unitDetail';
                    endif;

                break;
                
                // ================== TIMEPERIOD ================== //

                case 302: // DELETE -- TIMEPERIOD

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

                case 303: // INSERT/UPDATE -- TIMEPERIOD

                    if ($this->request->is('post')):
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
                    endif;

                break;

                case 305: // GET -- TIMEPERIOD
                    if ($this->request->is('post')):
                        $params = ['tpNid' => (isset($_POST['tpNid'])) ? $_POST['tpNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Timeperiod', 'getTimeperiodByID', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'timperiodDetails';
                    endif;

                break;
                
                // ================== SUBGROUP ================== //

                case 404: // DELETE -- SUBGROUP TYPE and associated data
                    if ($this->request->is('post')):
                        $nId = (isset($_POST['nId'])) ? $_POST['nId'] : '';
                    
                        if (!empty($nId) && !empty($dbId)) {
                            $params = ['nId' => $nId];
                            $result = $this->CommonInterface->serviceInterface('SubgroupType', 'deleteSubgroupTypedata', $params, $dbConnection);
                            if ($result == true) {
                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                // Not deleted  due server error 
                                $returnData['errCode'] = _ERR100;
                            }
                        } else {
                            // Invalid details 
                            $returnData['errCode'] = _ERR145;
                        }
                    endif;

                break;

                case 405: // DELETE -- SUBGROUP and associated data
                    if ($this->request->is('post')):
                        $nId = (isset($_POST['nId'])) ? $_POST['nId'] : ''; // subgroup nid
                        
                        if (!empty($nId) && !empty($dbId)) {
                            $params = ['sgId' => $nId];
                            $result = $this->CommonInterface->serviceInterface('SubgroupType', 'deleteSubgroupdata', $params, $dbConnection);
                            if ($result == true) {
                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                // Not deleted due server error
                                $returnData['errCode'] = _ERR100;
                            }
                        } else {
                            // invalid request 
                            $returnData['errCode'] = _ERR145;
                        }                        
                    endif;

                break;

                case 406: // GET -- SUBGROUP TYPE [Dimesion details]

                    if ($this->request->is('post')):
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
                    endif;

                break;

                case 407: // GET --  SUBGROUP TYPE LIST
                    if ($this->request->is('post')):
                        if (!empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupType', 'getSubgroupTypeList', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgrpTypeList';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }                
                    endif;

                break;

                case 408: // INSERT/UPDATE -- SUBGROUP TYPE
                    if ($this->request->is('post')):
                        $subgroupData = [];
                        $subgroupData['dName'] = (isset($_POST['dName'])) ? $_POST['dName'] : '';
                        $subgroupData['dValues'] = (isset($_POST['dValues'])) ? $_POST['dValues'] : '';
                        $subgroupData['dGid'] = (isset($_POST['dGid'])) ? $_POST['dGid'] : '';
                        $subgroupData['nId'] = (isset($_POST['nId'])) ? $_POST['nId'] : '';
                        //$subgroupData = (isset($_POST['subgroupData'])) ? $_POST['subgroupData'] : '';
                        $subgroupData = json_encode($subgroupData);
                        $params[] = ['subgroupData' => $subgroupData];

                        $result = $this->CommonInterface->serviceInterface('SubgroupType', 'manageSubgroupTypeData', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'dimCategory';
                            $returnData['status'] = _SUCCESS;
                        }                
                    endif;

                break;

                case 409: // INSERT -- SUBGROUP DIMENSION VALUE DETAILS
                    if ($this->request->is('post')):
                        $subgroupData = [];
                        $subgroupData['dvName'] = (isset($_POST['dvName'])) ? trim($_POST['dvName']) : '';
                        $subgroupData['dvGid'] = (isset($_POST['dvGid'])) ? trim($_POST['dvGid']) : '';
                        $subgroupData['dcNid'] = $_POST['dcNid'];

                        //$subgroupData = (isset($_POST['subgroupData']))?$_POST['subgroupData']:'';
                        //$subgroupData = json_encode($subgroupData);	
                        $params[] = ['subgroupValData' => $subgroupData, 'dbId' => $dbId];

                        $result = $this->CommonInterface->serviceInterface('Subgroup', 'manageSubgroupData', $params, $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error']; // 
                        } else {

                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'dimVal';
                            $returnData['status'] = _SUCCESS;
                        }                      
                    endif;

                break;

                case 601: // DELETE -- SUBGROUP VALS and associated records
                    if ($this->request->is('post')):
                        $nId = (isset($_POST['nId'])) ? $_POST['nId'] : '';
                        if (!empty($nId) && !empty($dbId)) {

                            $params = ['sgvalNid' => $nId];

                            $result = $this->CommonInterface->serviceInterface('SubgroupVals', 'deleteSubgroupValData', $params, $dbConnection);
                            if ($result == true) {
                                $returnData['status'] = _SUCCESS;
                                $returnData['responseKey'] = '';
                            } else {
                                // Not deleted  due server error 
                                $returnData['errCode'] = _ERR100;
                            }
                        } else {
                            // Invalid details 
                            $returnData['errCode'] = _ERR145;
                        }                        
                    endif;

                break;

                case 604: // INSERT/UPDATE - SUBGROUP VALS
                    if ($this->request->is('post')):
                        $subgroupVal = [];
                        $subgroupVal = (isset($_POST['subgroupList'])) ? $_POST['subgroupList'] : '';
                        if (!empty($subgroupVal) && !empty($dbId)) {

                            $subgroupVal = json_encode($subgroupVal);

                            $params[] = ['subgroupValData' => $subgroupVal, 'dbId' => $dbId];
                            $result = $this->CommonInterface->serviceInterface('SubgroupVals', 'manageSubgroupValData', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = '';
                                $returnData['responseKey'] = '';
                                $returnData['status'] = _SUCCESS;
                            }
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }                    
                    endif;

                break;

                case 608: // GET -- SUBGROUP DIMESIONS LIST
                    if ($this->request->is('post')):
                        if (!empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'getSubgroupDimensionList', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgrpDimList';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    endif;
                
                break;

                case 609: // GET -- SUBGROUP VALS + DIMESIONS LIST
                    if ($this->request->is('post')):
                        if (!empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'getSubgroupValsDimensionList', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgrpValList';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    endif;

                break;

                case 610: // GET -- SUBGROUP VALS + DIMESIONS LIST using SGVAL ID
                    if ($this->request->is('post')):
                        if (!empty($dbId)) {
                            $params = ['sgValNid' => (isset($_POST['sgValNid'])) ? $_POST['sgValNid'] : ''];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'getSubgroupValsDimensionListById', $params, $dbConnection);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnData['data'];
                            $returnData['responseKey'] = 'subgroupVal';
                        } else {
                            $returnData['errCode'] = _ERR145;
                        }
                    endif;

                break;

                // ================== AREA ================== //
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
                        $result = $this->Template->getAreaDetails($this->request->data['aNid'], $dbConnection);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'aDetail';
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;

                break;

                case 802: // DELETE -- Area
                    if ($this->request->is('post')):
                        $aNid = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : null;

                        if (empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $params = ['conditions' => [_AREA_AREA_NID => $aNid]];
                            $result = $this->CommonInterface->serviceInterface('Area', 'deleteRecords', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'area';
                                $returnData['status'] = _SUCCESS;
                            }
                        }                        
                    endif;

                break;

                case 803: // INSERT/UPDATE -- Area
                    if ($this->request->is('post')):
                        $fieldsArray[_AREA_AREA_NAME] = isset($this->request->data['aName']) ? $this->request->data['aName'] : '';
                        $fieldsArray[_AREA_AREA_ID] = isset($this->request->data['aId']) ? $this->request->data['aId'] : '';
                        $fieldsArray[_AREA_AREA_NID] = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : null;
                        $fieldsArray[_AREA_AREA_BLOCK] = isset($this->request->data['blockNids']) ? $this->request->data['blockNids'] : '';

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
                                //-- TRANSACTION Log - SUCCESS
                                if (empty($fieldsArray[_AREA_AREA_BLOCK]))
                                    $areaTransac = _AREA_TRANSAC;
                                else
                                    $areaTransac = _GROUP_TRANSAC;

                                // UPDATE Case
                                if (!empty($fieldsArray[_AREA_AREA_NID])) {
                                    $this->TransactionLogs->createLog(_UPDATE, _TEMPLATEVAL, $areaTransac, $result, _DONE, '', '', $prevVal = $fieldsArray[_AREA_AREA_NAME] . ' (' . $fieldsArray[_AREA_AREA_ID] . ')', $newVal = $fieldsArray[_AREA_AREA_NAME] . ' (' . $fieldsArray[_AREA_AREA_ID] . ')');
                                } // INSERT Case
                                else {
                                    $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, $areaTransac, $result, _DONE, '', '', $prevVal = '', $newVal = $fieldsArray[_AREA_AREA_NAME] . ' (' . $fieldsArray[_AREA_AREA_ID] . ')');
                                }

                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'area';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;

                case 804: // GET -- AREA PARENT Details
                    if ($this->request->is('post')):
                        $aNid = $this->request->data['aNid'];

                        if (empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $pnid = $this->CommonInterface->serviceInterface('Area', 'getRecords', [[_AREA_PARENT_NId], [_AREA_AREA_NID => $aNid], 'all', ['first' => true]], $dbConnection);

                            if (!empty($pnid)) {
                                if ($pnid[_AREA_PARENT_NId] != '-1') {
                                    $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', [['aNid' => _AREA_AREA_NID, 'aGid' => _AREA_AREA_GID, 'aName' => _AREA_AREA_NAME], [_AREA_AREA_NID => $pnid[_AREA_PARENT_NId]], 'all', ['first' => true]], $dbConnection = '');
                                    if (empty($result)) {
                                        $result['error'] = _ERR157;
                                    }
                                } else {
                                    $result = ['aNid' => '-1', 'aGid' => '', 'aName' => ''];
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
                    endif;

                break;

                case 805: // ADD -- area map
                    if ($this->request->is('post')):
                        $result = $this->Template->addMap($_FILES, $this->request->data, _MAP_TYPE_AREA, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                        }                        
                    endif;

                break;

                case 806: // INSERT/UPDATE -- GROUP
                    if ($this->request->is('post')):
                        $result = $this->Template->addModifyGroup($_FILES, $this->request->data, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'group';
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;

                break;

                case 807: // GET -- GROUP
                    if ($this->request->is('post')):
                        $aNid = $this->request->data['aNid'];
                        if (empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $fields = ['aNid' => _AREA_AREA_NID, 'aId' => _AREA_AREA_ID, 'aName' => _AREA_AREA_NAME, 'aBlock' => _AREA_AREA_BLOCK, 'pnid' => _AREA_PARENT_NId];
                            $conditions = [_AREA_AREA_NID => $aNid];

                            $params['fields'] = $fields;
                            $params['conditions'] = $conditions;
                            $params['type'] = 'all';
                            $params['extra'] = ['first' => true];
                            $result = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection);
                            $dbConnection = '';

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                //-- Area Blocks Details
                                if (!empty($result['aBlock'])) {
                                    $aBlock = explode(',', $result['aBlock']);
                                    $params = [];
                                    $params['fields'] = ['aNid' => _AREA_AREA_NID, 'aId' => _AREA_AREA_ID, 'aName' => _AREA_AREA_NAME, 'aLevel' => _AREA_AREA_LEVEL];
                                    $params['cond'] = [_AREA_AREA_NID . ' IN'];
                                    $params['AreaIds'] = $aBlock;
                                    $params['type'] = 'all';
                                    $blocks = $this->CommonInterface->serviceInterface('Area', 'getAreaRecords', $params, $dbConnection);
                                } else {
                                    $blocks = [];
                                }
                                $result['aBlocks'] = $blocks;

                                //-- Maps Details
                                $paramsMap['aNid'] = $aNid;
                                $paramsMap['lNid'] = '';
                                $paramsMap['extra'] = ['first' => true];
                                $maps = $this->CommonInterface->serviceInterface('Area', 'getAreaMapDetails', $paramsMap, $dbConnection);
                                $result['aMap'] = $maps;

                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'aDetail';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;

                case 808: // GET MAP -- AREA/GROUP
                    if ($this->request->is('post')):
                        $aNid = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : '';
                        $layerNid = isset($this->request->data['layerNid']) ? $this->request->data['layerNid'] : '';
                        if (empty($aNid)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $paramsMap['aNid'] = $aNid;
                            $paramsMap['layerNid'] = $layerNid;
                            $paramsMap['extra'] = []; //['first' => true];
                            $maps = $this->CommonInterface->serviceInterface('Area', 'getAreaMapDetails', $paramsMap, $dbConnection);
                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['data'] = $maps;
                                $returnData['responseKey'] = 'aMap';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;

                case 809: // DELETE MAP - AREA/GROUP
                    if ($this->request->is('post')):
                        $mapLayerNId = isset($this->request->data['mapLayerNId']) ? $this->request->data['mapLayerNId'] : null;
                        $aNid = isset($this->request->data['aNid']) ? $this->request->data['aNid'] : null;

                        if (empty($aNid) || empty($mapLayerNId)) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $paramsMap['mapLayerNId'] = $mapLayerNId;
                            $paramsMap['aNid'] = $aNid;
                            $maps = $this->CommonInterface->serviceInterface('Area', 'deleteMapAssociations', $paramsMap, $dbConnection);
                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;

                case 904: // BULK UPLOAD -- AREA
                    $filename = $extra['filename'];
                    //$params['filename'] = $filename;
                    //$params['filename'] = $extra['filename']='C:\-- Projects --\D3A\dfa_devinfo_data_admin\webroot\data-import-formats\Area-mylist.xls';
                    $params['filename'] = $extra['filename'];
                    $params['component'] = 'Area';
                    $params['extraParam'] = [];                      
                   
                    $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                    //pr($returnData);die;
                    return $returnData;
                    
                break;
               
                case 1101: // ADD DATABASE
                    if ($this->request->is('post')) {
                        if ($this->UserCommon->checkSAAccess()) {
                            $result =  $this->Database->createUpdateDBConnection($this->request->data, $dbId);
                            
                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else if (is_numeric($result)){ 
                                $returnData['status'] = _SUCCESS;
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'addDb';
                            } else {
                                $returnData['errMsg'] = $result;
                            }
                        }else {
                            $returnData['isAuthorised'] = false;
                        }
                    }
                break;                
                
                case 1102: // CHECK UNIQUE CONNECTION NAME -- DB CONNECTION
                    if ($this->request->is('post')) {
                        if (isset($this->request->data['connectionName'])) {

                            $connectionName = trim($this->request->data['connectionName']);
                            $returnUniqueDetails = $this->Database->uniqueConnection($connectionName);

                            if ($returnUniqueDetails === true) {
                                $returnData['status'] = _SUCCESS; // new connection name 

                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR102; // database connection name already exists
                            }
                        } else {
                            $returnData['errCode'] = _ERR103; // database connection name is empty 
                        }                      
                    }
                
                break;
                                
                case 1103: // GET -- DATABASE LIST
                    $databases = $this->Database->getDatabases();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $databases;
                    $returnData['responseKey'] = 'dbList';
                
                break;
                
                case 1104: // DELETE -- DATABASE
                    if ($this->request->is('post')) {
                        if (isset($dbId) && !empty($dbId)) {
                            // Super-admin
                            if ($chkSAStatus == true) {
                                $returnDatabaseDetails = $this->Database->deleteDatabase($dbId, $authUserId);
                                $getDBDetailsById = $this->Database->getDbNameByID($dbId);

                                if ($returnDatabaseDetails) {
                                    $returnData['status'] = _SUCCESS;
                                    $returnData['data'] = $getDBDetailsById;
                                    $returnData['responseKey'] = '';
                                } else {
                                    // no record deleted server error
                                    $returnData['errCode'] = _ERR100; 
                                }
                            } else {
                                // Non Super-admin
                                $returnData['isAuthorised'] = false;
                            }
                        } else {
                            // db id is blank
                            $returnData['errCode'] = _ERR106;
                        }                      
                    }
                
                break;
                
                case 1105: // TEST -- DB CONNECTION
                    if ($this->request->is('post')) {
                        $db_con = array(
                            'db_source' => $this->request->data['databaseType'],
                            'db_connection_name' => $this->request->data['connectionName'],
                            'db_host' => $this->request->data['hostAddress'],
                            'db_login' => $this->request->data['userName'],
                            'db_password' => $this->request->data['password'],
                            'db_port' => $this->request->data['port'],
                            'db_database' => $this->request->data['databaseName']
                        );
                        $data = array(_DATABASE_CONNECTION_DEVINFO_DB_CONN => json_encode($db_con) );

                        $data = json_encode($data);
                        $returnTestDetails = $this->Database->testConnection($data);
                        if ($returnTestDetails === true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey '] = '';
                        } else {
                            $returnData['errCode'] = _ERR101; // //  Invalid database connection details
                        }
                    }
                
                break;
                
                case 1106: // GET -- DB DETAILS
                    if ($this->request->is('post')) {
                        if (isset($dbId) && !empty($dbId)) {
                            $returnSpecificDbDetails = $this->Database->getDbNameByID($dbId);
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $returnSpecificDbDetails;
                            $returnData['responseKey'] = 'db';
                        } else {
                            $returnData['errCode'] = _ERR106;      // db id is blank
                        }
                    }
                
                break;

                case 1108: // GET -- ROLE TYPES
                    $listAllRoles = $this->UserCommon->listAllRoles();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $listAllRoles;
                    $returnData['responseKey'] = 'roleDetails';

                break;
                  
                case 1109: // GET -- USER DETAILS + ROLES + ACCESSS using dbId
                    if ($this->request->is('post')) {
                        if (isset($dbId) && !empty($dbId)) {
                            $listAllUsersDb = $this->UserCommon->listAllUsersDb($dbId);
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = 'userList';
                            $returnData['data'] = $listAllUsersDb;
                        } else {
                            // db id is blank
                            $returnData['errCode'] = _ERR106;
                        }                        
                    }
                break;
                                
                case 1200: // DELETE - USERS + ASSOCIATED DB + ASSOCIATED ROLES
                    if ($this->request->is('post')) {
                        $userIds = '';
                        if (isset($this->request->data['userIds']) && !empty($this->request->data['userIds']))
                            $userIds = $this->request->data['userIds'];

                        if (isset($userIds) && !empty($userIds)) {
                            if (isset($dbId) && !empty($dbId)) {

                                $status = 0;
                                $status = $this->UserCommon->getAuthorizationStatus($userIds,$dbId);// if status is 1 then not allowed 
                                if ($status == 0) {
                                    $deleteAllUsersDb = $this->UserCommon->deleteUserRolesAndDbs($userIds, $dbId);
                                    if ($deleteAllUsersDb > 0) {
                                        $returnData['status'] = _SUCCESS;
                                        $returnData['responseKey'] = '';
                                    } else {
                                        // Not deleted  due server error 
                                        $returnData['errCode'] = _ERR100;
                                    }
                                } else {
                                    // Not allowed to delete
                                    $returnData['isAuthorised'] = false;
                                }
                            } else {
                                // db id is blank
                                $returnData['errCode'] = _ERR106;
                            }
                        } else {
                            // user id is blank
                            $returnData['errCode'] = _ERR109;
                        }                    
                    }

                break;
                
                case 1201: // MODIFY -- USERS + ASSOCIATED DB + ASSOCIATED ROLES
                    if ($this->request->is('post')) {
                        $accessStatus = $this->UserCommon->checkAuthorizeUser($this->request->data[_USER_ID], $dbId, $this->request->data['roles']); //return true if allowed to modify
                        
                        if ($accessStatus == true) {
                            $response = $this->UserCommon->saveUserDetails($this->request->data, $dbId);
                            if ($response === true) {
                                $returnData['status'] = _SUCCESS;
                            } else {
                                $returnData['errCode'] = $response;
                            }
                        } else {
                            // Un-authorised user
                            $returnData['isAuthorised'] = false;
                        }                        
                    }

                break;

                case 1202: // GET -- AUTO-COMPLETE DETALS for USERS (EMAIL, ID, NAME)
                    $listAllUsersDb = $this->UserCommon->getAutoCompleteDetails();
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $listAllUsersDb;
                    $returnData['responseKey'] = 'usersList';
                break;
                
                case 1203: // RESET PASSWORD -- USER
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

                case 1204: // UPDATE PASSWORD from ACTIVATION LINK - USERS
                   if ($this->request->is('post')) {
                       $result = $this->Common->accountActivation($this->request->data);
                        if (isset($result['error'])) {
                           $returnData['errCode'] = $result['error'];
                       } else {
                           $returnData['data'] = '';
                           $returnData['responseKey'] ='';
                           $returnData['status'] = _SUCCESS;
                       }
                   }
                break;
                              
                
                                
                case 1205: // GET -- DB ROLES (LOGGED-IN USER)
                    if ($this->request->is('post')) {
                        $dataUsrDbRoles = $this->UserCommon->getUserDatabasesRoles($authUserId, $dbId);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $dataUsrDbRoles;
                        $returnData['responseKey'] = 'usrDbRoles';                        
                    }
                
                break;
                                
                case 1206: // GET -- SESSION DETAILS (LOGGED-IN USER)
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
                break;
                                
                case 1207: // FORGOT PASSWORD -- USERS
                    if ($this->request->is('post')) {
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
                    }

                break;

                case 2102: // GET -- INDICATOR CLASSIFICATION
                    if ($this->request->is('post')):
                        $result = $this->Template->getIcDetails($this->request->data, [], $dbConnection);
                        
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = [$result['ic'], $result['ius'], $result['iu']];
                            $returnData['responseKey'] = ['icDetail', 'ius', 'iu'];
                            $returnData['status'] = _SUCCESS;
                        }
                    endif;

                break;

                case 2104: // DELETE -- INDICATOR CLASSIFICATION
                    if ($this->request->is('post')):
                        $params['conditions'] = [_IC_IC_NID => $this->request->data['icNid']];
                        $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'deleteIc', $params, $dbConnection);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'ic';
                            $returnData['status'] = _SUCCESS;
                        }                        
                    endif;

                break;

                case 2105: // INSERT/UPDATE(using NID) -- INDICATOR CLASSIFICATION
                    if ($this->request->is('post')):
                        $fieldsArray[_IC_IC_TYPE] = isset($this->request->data['icType']) ? $this->request->data['icType'] : '';
                        $fieldsArray[_IC_IC_PARENT_NID] = isset($this->request->data['parentICId']) ? $this->request->data['parentICId'] : -1;
                        $fieldsArray[_IC_IC_NAME] = isset($this->request->data['icName']) ? $this->request->data['icName'] : '';
                        $fieldsArray[_IC_IC_GID] = isset($this->request->data['icGid']) ? $this->request->data['icGid'] : '';
                        $ius = isset($this->request->data['ius']) ? $this->request->data['ius'] : [];
                        
                        if (isset($this->request->data['icNid']))
                            $fieldsArray[_IC_IC_NID] = $this->request->data['icNid'];

                        if (empty($fieldsArray[_IC_IC_TYPE]) || empty($fieldsArray[_IC_IC_NAME])) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $params = ['fieldsArray' => $fieldsArray];
                            $params['params'] = ['ius' => explode(',', $ius), 'parentIcNid' => $fieldsArray[_IC_IC_PARENT_NID]];
                            //$result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'saveIC', $params, $dbConnection);
                            $result = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'saveIcAndIcius', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else if($result === false) {
                                $returnData['status'] = _FAILED;
                            } else {
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'ic';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;

                case 2106: // GET -- IC Parent Name
                    if ($this->request->is('post')):
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
                    endif;

                break;

                case 2202: // GET -- INDICATOR UNIT SUBGROUP (IUS)
                    $fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID];
                    $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN' => [244, 25]];

                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;

                    $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getRecords', $params, $dbConnection);

                break;

                case 2204: // DELETE - IUS
                    $params['conditions'] = $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                    $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'deleteRecords', $params, $dbConnection);
                
                break;

                case 2205: // INSERT - IUS
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

                case 2209: // GET -- TREE STRUCTURE [LIST]
                    if ($this->request->is('post')):
                        // possible Types Area,IU,IUS,IC and ICIND
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

                        //$type = 'Area'; $parentId = 18274; $onDemand = true; $idVal = 'nId'; $showGroup = false;

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

                case 2211:  // GET -- IUS Details FROM IU(S) GIDs -- INDICATOR UNIT SUBGROUP
                    if ($this->request->is('post')):
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

                case 2212: //Save Validation Details -- INDICATOR UNIT SUBGROUP
                    if ($this->request->is('post')):
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
                                
                case 2213: // DELETE -- IU or IUS
                    if ($this->request->is('post')):
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

                /* Commented now - Will be used for CRUD opartions 
                case 2302: //Delete Data using Conditions -- ICIUS table
                    //deleteRecords(array $conditions)
                    $params['conditions'] = $conditions = [_ICIUS_IC_NID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                    $returnData = $this->CommonInterface->serviceInterface('IcIus', 'deleteRecords', $params, $dbConnection);
                break; 
                */

                case 2307: // BULK INSERT/UPDATE -- ICIUS table
                    //$params['filename'] = $filename = 'C:\-- Projects --\xls\Temp_Selected_ExcelFile.xls';
                    $params['filename'] = $extra['filename'];
                    $params['component'] = 'IcIus';
                    $params['extraParam'] = [];
                    return $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                  
                break;

                case 2401: // BULK IMPORT - ICIUS + AREA + DES
                    if ($this->request->is('post')):
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
                                $extraParam['subModule'] = _IMPORTDES;
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
                                _MTRANSACTIONLOGS_ACTION => _IMPORT,
                                _MTRANSACTIONLOGS_MODULE => $module,
                                _MTRANSACTIONLOGS_SUBMODULE => $extraParam['subModule'],
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
                                $fieldsArray = [_MTRANSACTIONLOGS_STATUS => _DONE, _MTRANSACTIONLOGS_IDENTIFIER => $logFileName, _MTRANSACTIONLOGS_ACTION => _IMPORT];
                                $conditions = [_MTRANSACTIONLOGS_ID => $LogId];
                                $this->TransactionLogs->updateRecord($fieldsArray, $conditions);

                                $return = _WEBSITE_URL . _LOGS_PATH_WEBROOT . '/' . $logFileName;
                                $returnData['data'] = $return;
                                $returnData['responseKey'] = _IMPORT_LOG;
                                $returnData['status'] = _SUCCESS;
                            }
                        }                        
                    endif;

                break;

                              
                case 2403: // GET -- DATA from IUS, TIMEPERIOD, AREA
                    if ($this->request->is('post')):
                        $areaNidArray = $this->request->data['areaNid'];
                        $timePeriodNidArray = $this->request->data['tp'];
                        $iusgidArray = $this->request->data['iusGids'];
                        $sourceArray = (isset($this->request->data['source'])) ? $this->request->data['source'] : [];

                        $return = $this->Common->deSearchIUSData($areaNidArray, $timePeriodNidArray, $iusgidArray, ['dbConnection' => $dbConnection, 'dbId' => $dbId, 'source' => $sourceArray]);
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
                    endif;
                    break;
                    
                case 2404: // SAVE - DATA ENTRY
                    if ($this->request->is('post')) {
                        $deleteValue = '';
                        $jsonData = (isset($_POST['dataEntry'])) ? json_encode($_POST['dataEntry']) : '';
                        $params = ['dbId' => $dbId, 'jsonData' => $jsonData, $validation = true, $customLog = false, $isDbLog = true];
                        $datavalue = $this->CommonInterface->serviceInterface('Data', 'saveData', $params, $dbConnection);
                            //dataDelete
                        $deleteValue = (isset($_POST['dataDelete'])) ? json_encode($_POST['dataDelete']) : '';
                       //die; 
                       if (isset($deleteValue) && !empty($deleteValue)) {
                            $params = ['dbId' => $dbId, 'data' => $deleteValue];
                            $remData = $this->CommonInterface->serviceInterface('Data', 'deleteDataValue', $params);
                        }
                        $datamergeForm['log']['Formdata'] = [];
                        if ($datavalue['status'] == true) {
                            $datamergeForm['log']['Formdata']['customLogJson'] = $datavalue['customLogJson'];
                            $data = $this->Common->writeLogFile($datamergeForm, $dbId);

                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                        } else {
                            $returnData['errCode'] = '';
                        }                        
                    }
                
                break;

                case 2405: // INSERT/MODIFY -- SOURCE
                    if ($this->request->is('post')): 
                        $params = ['fieldsArray' => $this->request->data];
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
                    endif;

                break;

                case 2406: // GET -- USER DETAILS
                    if ($this->request->is('post')) {
                        $userId = $this->request->data['userId'];
                        if ($dbId) {
                            if (isset($userId) && !empty($userId)) {
                                $data = $this->UserCommon->listSpecificUsersdetails($userId, $dbId);
                                $returnData['status'] = _SUCCESS;
                                $returnData['data'] = $data;
                                $returnData['responseKey'] = 'userDetails';
                            } else {
                                // user id is blank
                                $returnData['errCode'] = _ERR109;
                            }
                        } else {
                            // db id is blank
                            $returnData['errCode'] = _ERR106;
                        }
                    }
                
                break;

                case 2407: // GET - SOURCE BREAKUP DETAILS
                    if ($this->request->is('post')):
                        $fields = [_IC_IC_NID, _IC_IC_PARENT_NID, _IC_IC_GID, _IC_IC_NAME, _IC_PUBLISHER, _IC_DIYEAR];
                        $params = ['fields' => $fields, [], 'all', ['getAll' => true]];
                        $returnData['data'] = $this->Common->getSourceBreakupDetails($params, $dbConnection);
                        $returnData['responseKey'] = _SOURCE_BREAKUP_DETAILS;
                        $returnData['status'] = _SUCCESS;
                    endif;
                
                break;

                case 2408: // IMPORT -- DES
                    $filename = $extra['filename'];
                    return $returnData = $this->DataEntry->importDes($filename, $dbId, $dbConnection);

                break;

                case 2409: // DELETE -- SOURCE + ASSOCIATED DATA + ICIUS(SR)
                    if ($this->request->is('post')):
                        $params['srcNid'] = (isset($_POST['srcNid'])) ? $_POST['srcNid'] : '';
                        $Data = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'deleteSourceData', $params, $dbConnection);
                        if ($Data == true) {
                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            //$returnData['errCode'] = _ERR110;     // Not deleted   
                            $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                        }                        
                    endif;

                break;
                
                case 2410: // DELETE -- TIMEPERIOD + ASSOCIATED DATA
                    if ($this->request->is('post')):
                        $params['tpNId'] = (isset($_POST['tpNid'])) ? $_POST['tpNid'] : '';

                        $Data = $this->CommonInterface->serviceInterface('Timeperiod', 'deleteTimeperiodData', $params, $dbConnection);
                        if ($Data == true) {

                            $returnData['status'] = _SUCCESS;
                            $returnData['responseKey'] = '';
                        } else {
                            $returnData['errCode'] = _ERR100;      //  Not deleted  due server error 
                        }                    
                    endif;

                break;

                case 2411: // GET -- SOURCE
                    if ($this->request->is('post')):
                        $params = ['srcNid' => (isset($_POST['srcNid'])) ? $_POST['srcNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getSourceByID', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'sourceDetail';                        
                    endif;

                break;

                case 2412: // GET - DB CONNECTION by DbID
                    if (isset($dbConnection) && !empty($dbConnection)) {
                        $dbConDetails = json_decode($dbConnection, true);
                        $dbDetails = array('id' => $dbId, 'databaseType' => $dbConDetails['db_source'], 'connectionName' => $dbConDetails['db_connection_name'], 'hostAddress' => $dbConDetails['db_host'], 'databaseName' => $dbConDetails['db_database'], 'port' => $dbConDetails['db_port']);

                        //$dbDetails = array_merge($dbDetails, $dbConDetails);                  
                        //  unset($dbDetails['db_password']);
                        $returnData['data'] = $dbDetails;
                        $returnData['status'] = _SUCCESS;
                        $returnData['responseKey'] = 'dbConDetails';
                    }
                                    
                break;
                
                case 2413: // UPDATE -- DATABASE
                    if ($this->request->is('post')) {
                        $result =  $this->Database->createUpdateDBConnection($this->request->data, $dbId);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                        }                      
                    }

                break;
                                
                case 2414: // GET -- TRANSACTION LOG
                    $conditions = $this->TransactionLogs->prepareFilterCondtions($dbId);
                    $fields = array('id' => _MTRANSACTIONLOGS_ID, 'userId' => _MTRANSACTIONLOGS_USER_ID, 'action' => _MTRANSACTIONLOGS_ACTION, 'txnModule' => _MTRANSACTIONLOGS_MODULE, 'submodule' => _MTRANSACTIONLOGS_SUBMODULE, 'identifier' => _MTRANSACTIONLOGS_IDENTIFIER, 'previousValue' => _MTRANSACTIONLOGS_PREVIOUSVALUE, 'newValue' => _MTRANSACTIONLOGS_NEWVALUE, 'status' => _MTRANSACTIONLOGS_STATUS, 'description' => _MTRANSACTIONLOGS_DESCRIPTION, 'created' => _MTRANSACTIONLOGS_CREATED);
                    
                    // Get Transac Log records display limit
                    $limit = $this->Common->getSystemConfig('TRANSACTION_LIMIT');
                    $extra = !empty($limit) ? ['limit' => $limit['TRANSACTION_LIMIT']] : [] ;
                    
                    $results = $this->TransactionLogs->getTransactionLogsData($fields, $conditions, 'all', $extra);
                    $returnData['status'] = _SUCCESS;
                    $returnData['data'] = $results;
                    $returnData['responseKey'] = 'dbLog';
                
                break;

                case 2415: // EXPORT - DES
                    if ($this->request->is('post')) {
                        $areaNidArray = isset($this->request->data['areaNid']) ? $this->request->data['areaNid'] : [];
                        $timePeriodNidArray = isset($this->request->data['tp']) ? $this->request->data['tp'] : [];
                        $iusgidArray = isset($this->request->data['iusGids']) ? $this->request->data['iusGids'] : [];

                        $sheetLink = $this->DataEntry->exportDes($areaNidArray, $timePeriodNidArray, $iusgidArray, ['dbConnection' => $dbConnection, 'dbId' => $dbId]);
                        $returnFilePath = _WEBSITE_URL . _DES_PATH_WEBROOT . '/' . basename($sheetLink);

                        $returnData['data'] = $returnFilePath;
                        $returnData['responseKey'] = _EXPORT_DES;
                        $returnData['status'] = _SUCCESS;
                    }
                
                break;
                
                case 2416: // DELETE -- TRANSACTION LOG
                    if ($this->request->is('post')) {
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
                    }

                break;

                case 2417: // EXPORT - UNIT, INDICATOR, SUBGROUP, ICIUS, AREA
                    if ($this->request->is('post')):
                        $type = '';
                        $module = '';
                        $type = (isset($_POST['type'])) ? $_POST['type'] : '';
                        if (!empty($dbId)) {
                            if (!empty($type)) {

                                if (strtolower($type) == _UNITEXPORT) {
                                    $params = ['dbId' => $dbId];
                                    $expFile = $this->CommonInterface->serviceInterface('Unit', 'exportUnitDetails', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _UNIT_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'unitExport';
                                    $module = _MODULE_NAME_UNIT;
                                    
                                } else if (strtolower($type) == _INDIEXPORT) {
                                    $status = (isset($_POST['status'])) ? $_POST['status'] : false;
                                    $params = ['status' => $status, 'dbId' => $dbId];
                                    $expFile = $this->CommonInterface->serviceInterface('Indicator', 'exportIndicatorDetails', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _INDICATOR_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'indiExport';
                                    $module = _MODULE_NAME_INDICATOR;
                                    
                                } else if (strtolower($type) == _SUBGRPVALEXPORT) {
                                    //$status = (isset($_POST['status']))?$_POST['status']:false;
                                    $params = ['dbId' => $dbId];
                                    $expFile = $this->CommonInterface->serviceInterface('SubgroupVals', 'exportSubgroupValDetails', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _SUBGROUPVAL_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'subgrpExport';
                                    $module = _MODULE_NAME_SUBGROUPVAL;
                                    
                                } else if (strtolower($type) == _ICIUS) {
                                     $expFile =  $this->CommonInterface->serviceInterface('IcIus', 'exportIcius', [], $dbConnection);
                                     $returnData['data'] = _WEBSITE_URL . _ICIUS_PATH_WEBROOT . '/' . basename($expFile);
                                     $reponse = 'iciusExport';
                                     $module = _MODULE_NAME_ICIUS;
                                    
                                } else if (strtolower($type) == _AREA) {
                                    $params[] = $fields = [_AREA_AREA_ID, _AREA_AREA_NAME, _AREA_AREA_GID, _AREA_AREA_LEVEL, _AREA_PARENT_NId];
                                    $params[] = $conditions = [];
                                    $expFile =  $this->CommonInterface->serviceInterface('Area', 'exportArea', $params, $dbConnection);
                                    $returnData['data'] = _WEBSITE_URL . _AREA_PATH_WEBROOT . '/' . basename($expFile);
                                    $reponse = 'areaExport';
                                    $module = _MODULE_NAME_AREA;
                                }

                                if ($returnData['data']) {
                                    $this->TransactionLogs->createLog(_EXPORT, _TEMPLATEVAL, $module, basename($expFile), _DONE);
                                    $returnData['status'] = _SUCCESS;
                                    $returnData['responseKey'] = $reponse;
                                } else {
                                    $this->TransactionLogs->createLog(_EXPORT, _TEMPLATEVAL, $module, '', _FAILED);
                                }
                            } else {
                                $returnData['errCode'] = _ERR139; //type blank							
                            }
                        } else {
                            $returnData['errCode'] = _ERR106; //dbid  blank
                        }                        
                    endif;

                break;

                case 2418; // GET -- INDICATOR DETAILS
                    if ($this->request->is('post')):
                        $params = ['iNid' => (isset($_POST['iNid'])) ? $_POST['iNid'] : ''];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Indicator', 'getIndicatorById', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'indDetail';                        
                    endif;

                break;

                case 2419: // GET -- DATABASE LANGUAGE [LIST]
                    if ($dbConnection):
                        $params = ['fields' => ['nid' => _LANGUAGE_LANGUAGE_NID, 'languageName' => _LANGUAGE_LANGUAGE_NAME, 'languageCode' => _LANGUAGE_LANGUAGE_CODE, 'isDefault' => _LANGUAGE_LANGUAGE_DEFAULT], 'conditions' => []];
                        $lang_list = $this->CommonInterface->serviceInterface('Language', 'getRecords', $params, $dbConnection);
                        $returnData['data'] = $lang_list;

                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'langDetail';                
                    endif;

                break;

                case 2420: // EXPORT -- LANGUAGE DATABASE
                    if ($this->request->is('post')) {
                        $from_lang_code = isset($this->request->data['fromLangCode']) ? $this->request->data['fromLangCode'] : '';
                        $from_lang_name = isset($this->request->data['fromLangName']) ? $this->request->data['fromLangName'] : '';
                        $to_lang_code = isset($this->request->data['toLangCode']) ? $this->request->data['toLangCode'] : '';
                        $to_lang_name = isset($this->request->data['toLangName']) ? $this->request->data['toLangName'] : '';
                        if ($from_lang_code != '' && $from_lang_name != '' && $to_lang_code != '' && $to_lang_name != '') {
                            $params = [];
                            $params['fromLangCode'] = $from_lang_code;
                            $params['fromLangName'] = $from_lang_name;
                            $params['toLangCdoe'] = $to_lang_code;
                            $params['toLangName'] = $to_lang_name;

                            $returnFilePath = $this->CommonInterface->serviceInterface('Language', 'exportLangDatabase', $params, $dbConnection);

                            $returnData['data'] = $returnFilePath;
                            $returnData['responseKey'] = 'exportUrl';
                            $returnData['status'] = _SUCCESS;
							$this->TransactionLogs->createLog(_EXPORT, _MODULE_NAME_ADMINISTRATION, _MODULE_NAME_LANGUAGE, basename($returnFilePath), _DONE,'','',$from_lang_name,$to_lang_name);
                        } else {
							$this->TransactionLogs->createLog(_EXPORT, _MODULE_NAME_ADMINISTRATION, _MODULE_NAME_LANGUAGE, '', _FAILED);

                            $returnData['errCode'] = _ERR135;    //Missing parameters                 
                        }
                    }

                break;

                case 2421: // IMPORT -- LANGUAGE DATABASE
                    if ($this->request->is('post')):
                        $allowedExtensions = ['xls', 'xlsx'];

                        $module = _DATAENTRYVAL;
                        $subModule = _MODULE_NAME_LANGUAGE;
                        $extraParam['subModule'] = _MODULE_NAME_LANGUAGE;
                        $extraParam['dest'] = _DES_PATH;
                        $extraParam['dbName'] = $dbName;
                        $filePaths = $this->Common->processFileUpload($_FILES, $allowedExtensions, $extraParam);

                        if (isset($filePaths['error'])) {
                            $returnData['errMsg'] = $filePaths['error'];
                        } else {
                            $filename = $filePaths[0];
                            $extra['filename'] = $filename;

                            $params = [];
                            $params['filename'] = $filename;
                            $params['dbId'] = $dbId;
                            $params['dbConnection'] = $dbConnection;

                            $returnData = $this->CommonInterface->serviceInterface('Language', 'importLanguageDatabase', $params, $dbConnection);

                            if (isset($returnData['errCode'])) {
                                $returnData['errMsg'] = $returnData['errCode'];
                            } else {
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;
                
                case 2423: // RE-ORDER - SUBGROUP DIMENSION
                    if ($this->request->is('post')):
                        $data = (isset($_POST['data'])) ? $_POST['data'] : '';
                        $result = $this->Template->changeSubgroupDimOrder($data, $dbConnection, $dbId);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                        }                      
                    endif;

                break;
                
                case 2424: // RE-ARRANGE -- SUBGROUP NAMES by requested order
                    if ($this->request->is('post')):
                        if (isset($dbId) && !empty($dbId)) {
                            $params = [];
                            $returnData['data'] = $this->CommonInterface->serviceInterface('SubgroupVals', 'changeSubgroupNames', $params, $dbConnection);
                        }                       
                    endif;

                break;

                case 2422: // SET -- DEFAULT LANGUAGE
                    if ($this->request->is('post')):
                        $nid = isset($this->request->data['nid']) ? $this->request->data['nid'] : '';
                        $userRoles = $this->UserCommon->getUserDatabasesRoles($authUserId, $dbId);

                        if (empty($nid) || ($chkSAStatus == false && !in_array(_ADMIN_ROLE, $userRoles))) {
                            $returnData['errCode'] = _INVALID_INPUT;
                        } else {
                            $params = ['nid' => $nid];
                            $result = $this->CommonInterface->serviceInterface('Language', 'setDefaultLang', $params, $dbConnection);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                // Store defautl language Code in session for later use
                                $defaultLang = $this->CommonInterface->serviceInterface('Language', 'getRecords', [[_LANGUAGE_LANGUAGE_CODE], [_LANGUAGE_LANGUAGE_DEFAULT => 1]]);

                                if (!empty($defaultLang)) {
                                    $defaultLangcode = reset($defaultLang)[_LANGUAGE_LANGUAGE_CODE];
                                    $this->session->write('defaultLangcode', $defaultLangcode);
                                }
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'lang';
                                $returnData['status'] = _SUCCESS;
                            }
                        }
                    endif;

                break;
                                
                case 2425: // GET -- NUMBER OF COUNTS (IUS, INDICATOR, SOURCE, AREA)
                    if ($this->request->is('post')):
                        $result = $this->Database->displayMetadataInfoOnDbHome($dbConnection, $dbId, 'yes');
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'metaData';
                        }                    
                    endif;

                break;
                     
                case 2426: // MODIFY -- DB METADATA
                    if ($this->request->is('post')):
                        $desc = (isset($_POST['desc'])) ? $_POST['desc'] : "";                        
                        $dbMetaid = (isset($_POST['nId'])) ? $_POST['nId'] : '';

                        $result = $this->Database->updateMetadataDescription($dbConnection, $desc, $dbMetaid, $dbId);
                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = '';
                            $returnData['responseKey'] = '';
                        }
                    endif;

                break;  
                
                case 2427: // SAVE-AS -- DI7 DB (Copy/Duplicate DB)
                    if ($this->request->is('post')):
                    //if (true):
                        if ($this->UserCommon->checkSAAccess()) {
                            // Only superadmin can access this service
                            $result = $this->Database->saveAsDI7Db($this->request->data, $dbId);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else if ($result != true && is_string($result)){ 
                                $returnData['errMsg'] = $result;
                            } else {
                                $returnData['status'] = _SUCCESS;
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'addDb';
                            }
                        }else {
                            $returnData['isAuthorised'] = false;
                        }
                    endif;
                    
                    break;

                case 2428: // CRON CALL -- Save As DI7 DB (Copy/Duplicate DB)
                    //if ($this->request->is('post')):
                    if (true):
                        /*
                         * http://stackoverflow.com/questions/443421/sql-2005-quick-way-to-quickly-duplicate-a-database-data
                         * http://stackoverflow.com/questions/79669/how-best-to-copy-entire-databases-in-ms-sql-server
                         */
                        // Only superadmin can access this service
                        $result = $this->Database->duplicateDb($this->request->data);

                        if (isset($result['error'])) {
                            $returnData['errCode'] = $result['error'];
                        } else if ($result != true && is_string($result)){ 
                            $returnData['errMsg'] = $result;
                        } else {
                            $returnData['status'] = _SUCCESS;
                            $returnData['data'] = $result;
                            $returnData['responseKey'] = 'addDb';
                        }
                    endif;

                    break;
                    
                case 2429: // GET -- SYSTEM CONFIGURATION
                    if ($this->request->is('post')):
                        //if ($this->UserCommon->checkSAAccess()) {
                            
                            $keyName = isset($this->request->data['keyName']) ? $this->request->data['keyName'] : null ;
                            $result = $this->Common->getSystemConfig($keyName);

                            if (isset($result['error'])) {
                                $returnData['errCode'] = $result['error'];
                            } else {
                                $returnData['status'] = _SUCCESS;
                                $returnData['data'] = $result;
                                $returnData['responseKey'] = 'sysConfig';
                            }
                        //}// Not Super-admin
                        //else {
                        //    $returnData['isAuthorised'] = false;
                        //}
                    endif;
                    
                    break;
                    
                case 2430: // MODIFY -- SYSTEM CONFIGURATION
                    if ($this->request->is('post')):
                    //if (true):
                        if ($this->UserCommon->checkSAAccess()) {
                            // Only superadmin can access this service
                            $result = $this->Common->saveSystemConfig($this->request->data);

                            if (isset($result['errMsg'])) {
                                $returnData['errMsg'] = $result['errMsg'];
                            } else {
                                $returnData['status'] = _SUCCESS;
                            }
                        }// Not Super-admin
                        else {
                            $returnData['isAuthorised'] = false;
                        }
                    endif;

                    break;
                    
                    case 2431: // MODIFY -- SYSTEM CONFIGURATION
                    if ($this->request->is('post')):
                      if(!empty($dbId)){
                        $params = [];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Metadata', 'getMetaDataCategoryList', $params, $dbConnection);
                        $returnData['status'] = _SUCCESS;
                        $returnData['data'] = $returnData['data'];
                        $returnData['responseKey'] = 'metadataList';
                      }else {
                        $returnData['errCode'] = _ERR145;  //invalid request 
                      }   
                       
                    endif;

                    break;
                    
                default:
                break;

            endswitch;

        } catch (Exception $e) {
            $returnData['errMsg'] = $e->getMessage();
        }

        return $this->serviceResponse($returnData, $convertJson, $dbId, $chkSAStatus);
    }

    // Function to prepare service data based on response key
    public function serviceResponse($response, $convertJson = _YES, $dbId, $superAdminStatus = false) {
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
                $returnSpecificDbDetails = $this->Database->getDbNameByID($dbId);
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

            // COUNTS
            if(!empty($responseKey)) {
                $countArray = $this->Common->getDatabaseCounts($responseKey);
                if(!empty($countArray)) {
                    $returnData['data'] = array_merge($returnData['data'], $countArray);
                }
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
