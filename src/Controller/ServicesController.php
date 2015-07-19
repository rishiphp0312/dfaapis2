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
    public $components = [ 'Auth', 'DevInfoInterface.CommonInterface', 'Common', 'ExcelReader', 'UserCommon', 'TransactionLogs', 'MIusValidations', 'UserAccess'];

    public function initialize() {
        parent::initialize();
        $this->session = $this->request->session();
    }

    public function beforeFilter(Event $event) {

        //parent::beforeFilter($event);
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
        $dbConnection = 'test';
        $authUserId = $this->Auth->user(_USER_ID); // logged in user id
        $authUserRoleId = $this->Auth->user(_USER_ROLE_ID); // logged in user id
        $dbId = '';
        //$_REQUEST['dbId']=46;  // for testing 
        if (isset($_REQUEST['dbId']) && !empty($_REQUEST['dbId'])) {

            $dbId = $_REQUEST['dbId'];
            $dbConnection = $this->Common->getDbConnectionDetails($dbId); //dbId
           // $role_id = $this->Auth->user('role_id');

            // User is not Superadmin
            if ($authUserRoleId != _SUPERADMINROLEID) {
                //---- Store User access data into session, if found
                $authUserId = $this->Auth->user(_USER_ID);
                // Check fake call
                if (!empty($authUserId)) {
                    $userDbId = $this->UserCommon->getUserDatabasesRoles([$authUserId], $dbId);
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
                        $case = 0;
                    }
                }
            }
        }

        switch ($case):

            case 'test':

                /* $returnData['data'] = $this->UserAccess->getAreaAccessToUser(['type'=>'all']);
                  $returnData['data'] = $this->UserAccess->getAreaAccessToUser(['type'=>'list']);

                  $returnData['data'] = $this->UserAccess->getIndicatorAccessToUser(['type'=>'all']);
                  $returnData['data'] = $this->UserAccess->getIndicatorAccessToUser(['type'=>'list']); */

                $returnData = $this->CommonInterface->serviceInterface('IcIus', 'testCasesFromTable', [], $dbConnection);
                debug($returnData);
                exit;
                break;

            case 101: //Select Data using Indicator_NId -- Indicator table
                //getDataByIds($ids = null, $fields = [], $type = 'all' )
                $params[] = [317, 318, 386];
                $returnData = $this->CommonInterface->serviceInterface('Indicator', 'getDataByIds', $params, $dbConnection);
                break;

            case 102: //Select Data using Conditions -- Indicator table

                $fields = [_INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_INFO];
                $conditions = [_INDICATOR_INDICATOR_GID . ' IN' => ['POPDEN', 'AREA']];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                //getDataByParams(array $fields, array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('Indicator', 'getDataByParams', $params, $dbConnection);
                break;

            case 103: //Delete Data using Indicator_NId -- Indicator table
                //deleteByIds($ids = null)
                $params[] = [383, 384, 385];
                $returnData = $this->CommonInterface->serviceInterface('Indicator', 'deleteByIds', $params, $dbConnection);
                break;

            case 104: //Delete Data using Conditions -- Indicator table

                $conditions = [_INDICATOR_INDICATOR_GID . ' IN' => ['TEST_GID', 'TEST_GID2']];

                //deleteByParams(array $conditions)
                $params['conditions'] = $conditions = [_INDICATOR_INDICATOR_GID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                $returnData = $this->CommonInterface->serviceInterface('Indicator', 'deleteByParams', $params, $dbConnection);
                break;

            case 105: //Insert New Data -- Indicator table
                if ($this->request->is('post')):

                    $this->request->data = [
                        _INDICATOR_INDICATOR_NID => '384',
                        _INDICATOR_INDICATOR_NAME => 'Custom_test_name2',
                        _INDICATOR_INDICATOR_GID => 'SOME_001_TEST',
                        _INDICATOR_INDICATOR_INFO => '<?xml version="1.0" encoding="utf-8"?><metadata><Category name="Definition"><para /></Category><Category name="Method of Computation"><para /></Category><Category name="Overview"><para /></Category><Category name="Comments and Limitations"><para /></Category><Category name="Data Collection for Global Monitoring"><para /></Category><Category name="Obtaining Data:"><para /></Category><Category name="Data Availability:"><para /></Category><Category name="Treatment of Missing Values:"><para /></Category><Category name="Regional and Global Estimates:"><para /></Category><Category name="Data Availability"><para /></Category></metadata>',
                        _INDICATOR_INDICATOR_GLOBAL => '0',
                        _INDICATOR_SHORT_NAME => 'Short name',
                        _INDICATOR_KEYWORDS => 'Some Keyword',
                        _INDICATOR_INDICATOR_ORDER => '5',
                        _INDICATOR_DATA_EXIST => '1',
                        _INDICATOR_HIGHISGOOD => '1'
                    ];

                    //insertData(array $fieldsArray = $this->request->data)
                    $params['conditions'] = $conditions = $this->request->data;
                    $returnData = $this->CommonInterface->serviceInterface('Indicator', 'insertData', $params, $dbConnection);
                endif;


                break;

            case 106: //Update Data using Conditions -- Indicator table

                $fields = [
                    _INDICATOR_INDICATOR_NAME => 'Custom_test_name3',
                    _INDICATOR_INDICATOR_GID => 'SOME_003_TEST'
                ];
                $conditions = ['Indicator_NId' => '384'];

                if ($this->request->is('post')):
                    //updateDataByParams(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('Indicator', 'updateDataByParams', $params, $dbConnection);
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

            case 201: //Select Data using Unit_NId -- Unit table
                //getDataByIds($ids = null, $fields = [], $type = 'all' )
                $params[] = [10, 41];
                $returnData = $this->CommonInterface->serviceInterface('Unit', 'getDataByIds', $params, $dbConnection);
                break;

            case 202: //Select Data using Conditions -- Unit table

                $params['fields'] = $fields = [_UNIT_UNIT_NAME, _UNIT_UNIT_GLOBAL];
                $params['conditions'] = $conditions = [_UNIT_UNIT_GID . ' IN' => ['POPDEN', 'AREA']];

                //getDataByParams(array $fields, array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('Unit', 'getDataByParams', $params, $dbConnection);
                break;

            case 203: //Delete Data using Unit_NId -- Unit table
                //deleteByIds($ids = null)
                $params[] = [42];
                $returnData = $this->CommonInterface->serviceInterface('Unit', 'deleteByIds', $params, $dbConnection);
                break;

            case 204: //Delete Data using Conditions -- Unit table

                $params['conditions'] = $conditions = [_UNIT_UNIT_NID . ' IN' => ['SOME_001_TEST', 'SOME_003_TEST']];

                //deleteByParams(array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('Unit', 'deleteByParams', $params, $dbConnection);

            case 205: //Insert New Data -- Unit table

                $this->request->data = [
                    _UNIT_UNIT_NID => '43',
                    _UNIT_UNIT_NAME => 'Custom_test_name',
                    _UNIT_UNIT_GID => 'SOME_002_TEST',
                    _UNIT_UNIT_GLOBAL => '0'
                ];

                if ($this->request->is('post')):
                    //insertData(array $fieldsArray = $this->request->data)
                    $params[] = $this->request->data;
                    $returnData = $this->CommonInterface->serviceInterface('Unit', 'insertData', $params, $dbConnection);
                endif;

                break;

            case 206: //Update Data using Conditions -- Unit table

                $fields = [
                    _UNIT_UNIT_NAME => 'Custom_test_name3',
                    _UNIT_UNIT_GID => 'SOME_003_TEST'
                ];
                $conditions = [_UNIT_UNIT_NID => '43'];

                if ($this->request->is('post')):
                    //updateDataByParams(array $fields, array $conditions)
                    $params[] = $fields;
                    $params[] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('Unit', 'updateDataByParams', $params, $dbConnection);
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


            // nos starting with 301 are for timeperiod


            case 301:
                // service for getting the Timeperiod details on basis of any parameter  
                // passing array $fields, array $conditions

                if (!empty($_POST['TimePeriod']) || !empty($_POST['periodicity']) || !empty($_POST['EndDate']) || !empty($_POST['StartDate']) || !empty($_POST['TimePeriod_NId'])) {

                    $conditions = array();
                    $fields = array();
                    $params[] = $conditions;

                    $params[] = $fields;

                    $getDataByTimeperiod = $this->CommonInterface->serviceInterface('Timeperiod', 'getDataByParams', $params, $dbConnection);

                    // $getDataByTimeperiod  = $this->Timeperiod->getDataByParams( $fields ,$conditions);
                    if (isset($getDataByTimeperiod) && count($getDataByTimeperiod) > 0) {
                        $returnData['data'] = $getDataByTimeperiod;
                        $returnData['success'] = true;
                    } else {
                        $returnData['success'] = false;
                        $returnData['message'] = 'No records found';
                    }
                } else {

                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }
                break;


            case 302:
                // service for deleting the time period using  any parameters   
                if (!empty($_POST['TimePeriod']) || !empty($_POST['periodicity']) || !empty($_POST['EndDate']) || !empty($_POST['StartDate']) || !empty($_POST['TimePeriod_NId'])) {

                    $conditions = array();
                    $params[] = $conditions;

                    $deleteallTimeperiod = $this->CommonInterface->serviceInterface('Timeperiod', 'deleteByParams', $params, $dbConnection);

                    //$deleteallTimeperiod  = $this->Timeperiod->deleteByParams($conditions);
                    if ($deleteallTimeperiod) {
                        $returnData['message'] = 'Record deleted successfully';
                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $deleteallTimeperiod;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {
                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }
                break;


            /// cases for saving Time period 
            case 303:
                // service for saving  details of timeperiod 
                $data = array();

                $params[] = $data;

                $saveTimeperiodDetails = $this->CommonInterface->serviceInterface('Timeperiod', 'insertUpdateDataTimeperiod', $params, $dbConnection);

                // $saveTimeperiodDetails  = $this->Timeperiod->insertUpdateDataTimeperiod($data);
                if ($saveTimeperiodDetails) {
                    $returnData['success'] = true;
                    $returnData['message'] = 'Record inserted successfully!!';
                    $returnData['returnvalue'] = $saveTimeperiodDetails;
                } else {
                    $returnData['success'] = false;
                }

                break;

            /// cases for updating  Time period 
            case 304:
                // service for updating  details of timeperiod 
                $data = array();

                $_POST['TimePeriod_NId'] = 12;
                $_POST['Periodicity'] = 'A';
                $fields = [
                    'TimePeriod' => '2029',
                ];
                $conditions = $data;

                //updateDataByParams(array $fields, array $conditions)
                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                $saveTimeperiodDetails = $this->CommonInterface->serviceInterface('Timeperiod', 'updateDataByParams', $params, $dbConnection);

                // $saveTimeperiodDetails  = $this->Timeperiod->insertUpdateDataTimeperiod($data);
                if ($saveTimeperiodDetails) {
                    $returnData['success'] = true;
                    $returnData['message'] = 'Record inserted successfully!!';
                    $returnData['returnvalue'] = $saveTimeperiodDetails;
                } else {
                    $returnData['success'] = false;
                }

                break;


            // service no. starting with 401 are for subgroup type 
            case 401:

                if ($this->request->is('post')):
                    $data = array();
                    $data[_SUBGROUPTYPE_SUBGROUP_TYPE_GID] = $this->Common->guid();
                    $params[] = $data;

                    $saveDataforSubgroupType = $this->CommonInterface->serviceInterface('Subgroup', 'insertUpdateDataSubgroupType', $params, $dbConnection);
                    $returnData['returnvalue'] = $saveDataforSubgroupType;
                endif;

                break;

            case 402:  // service for updating  details of subgroup type 

                if ($this->request->is('post')):
                    $data = array();

                    $fields = ['Subgroup_Type_Name' => '2029'];
                    $conditions = $data;

                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $saveDataforSubgroupType = $this->CommonInterface->serviceInterface('Subgroup', 'updateDataByParamsSubgroupType', $params, $dbConnection);
                    $returnData['returnvalue'] = $saveTimeperiodDetails;
                endif;
                break;

            case 403: // service for getting the Subgroup type   details on basis of any parameter  					

                if ($this->request->is('post')):
                    $conditions = [];
                    $fields = [];
                    $params[] = $fields;
                    $params[] = $conditions;

                    $SubgroupTypeDetails = $this->CommonInterface->serviceInterface('Subgroup', 'getDataByParamsSubgroupType', $params, $dbConnection);
                    $returnData['data'] = $SubgroupTypeDetails;
                endif;
                break;

            case 404: // service for deleting the subgroup types using  any parameters
                if ($this->request->is('post')):
                    $conditions = [];
                    $params[] = $conditions;

                    $deleteallSubgroupType = $this->CommonInterface->serviceInterface('Subgroup', 'deleteByParamsSubgroupType', $params, $dbConnection);
                    $returnData['returnvalue'] = $deleteallSubgroupType;
                endif;
                break;

            // service no. starting from  501 are for subgroup
            case 501: // service for saving  subgroup  name 
                if ($this->request->is('post')):
                    $data = array();
                    $params[] = $data;
                    $saveDataforSubgroupType = $this->CommonInterface->serviceInterface('Subgroup', 'insertUpdateDataSubgroup', $params, $dbConnection);

                    $returnData['returnvalue'] = $saveDataforSubgroupType;
                endif;
                break;

            case 502: // service for updating the   subgroup  name 
                if ($this->request->is('post')):
                    $data = array();
                    $fields = [_SUBGROUP_SUBGROUP_NAME, _SUBGROUP_SUBGROUP_TYPE];

                    $params['fields'] = $fields;
                    $params['conditions'] = $data;
                    $saveDataforSubgroupType = $this->CommonInterface->serviceInterface('Subgroup', 'deleteByParamsSubgroupType', $params, $dbConnection);
                    $returnData['returnvalue'] = $saveDataforSubgroupType;
                endif;
                break;

            case 503: // service for getting the Subgroup  details on basis of any parameter  
                if ($this->request->is('post')):
                    $conditions = $fields = [];
                    $params[] = $fields;
                    $params[] = $conditions;

                    $SubgroupDetails = $this->CommonInterface->serviceInterface('Subgroup', 'getDataByParamsSubgroup', $params, $dbConnection);
                    $returnData['data'] = $SubgroupDetails;
                endif;
                break;

            case 504: // service for deleting the Subgroup Name using  any parameters
                if ($this->request->is('post')):
                    $conditions = [];
                    $params[] = $conditions;

                    $deleteallSubgroup = $this->CommonInterface->serviceInterface('Subgroup', 'deleteByParamsSubgroup', $params, $dbConnection);
                    $returnData['returnvalue'] = $deleteallSubgroup;
                endif;
                break;

            case 601: //Select Data using SubgroupVal_NId -- SubgroupVals table
                //getDataByIds($ids = null, $fields = [], $type = 'all' )
                $params[] = [317, 318, 386];
                $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'getDataByIds', $params, $dbConnection);
                break;

            case 602: //Select Data using Conditions -- SubgroupVals table

                $fields = [_SUBGROUP_VAL_SUBGROUP_VAL, _SUBGROUP_VAL_SUBGROUP_VAL_GID];
                $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_GID . ' IN' => ['T', 'U']];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                //getDataByParams(array $fields, array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'getDataByParams', $params, $dbConnection);
                break;

            case 603: //Delete Data using Indicator_NId -- SubgroupVals table
                //deleteByIds($ids = null)
                $params[] = [960, 961, 962];
                $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'deleteByIds', $params, $dbConnection);
                break;

            case 604: //Delete Data using Conditions -- SubgroupVals table
                //deleteByParams(array $conditions)
                $params['conditions'] = $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_GID . ' IN' => ['A', 'BG']];
                $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'deleteByParams', $params, $dbConnection);
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
                    //updateDataByParams(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('SubgroupVals', 'updateDataByParams', $params, $dbConnection);
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
                    $returnData['data']['id'] = $this->Auth->user('id');
                } catch (Exception $e) {
                    echo 'Exception occured while loading the project list file';
                    exit;
                }

                break;

            case 801:
                //  service for getting the Area details on basis of passed parameters
                if (!empty($_POST['Area_ID']) || !empty($_POST['Area_Name']) || !empty($_POST['Area_GId']) || !empty($_POST['Area_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Data_Exist']) || !empty($_POST['AreaShortName']) || !empty($_POST['Area_Parent_NId']) || !empty($_POST['Area_Block'])) {

                    $conditions = array();

                    $params[] = $fields = [_AREA_AREA_BLOCK, _AREA_AREA_SHORT_NAME, _AREA_AREA_ID];
                    $params[] = $conditions;

                    $getAreaDetailsData = $this->CommonInterface->serviceInterface('Area', 'getDataByParams', $params, $dbConnection);
                    if ($getAreaDetailsData) {

                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $getAreaDetailsData;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {

                    $returnData[] = false;
                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }
                break;



            case 802:
                // service for deleting the Area using  any parameters below 
                if (!empty($_POST['Area_ID']) || !empty($_POST['Area_Name']) || !empty($_POST['Area_GId']) || !empty($_POST['Area_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Data_Exist']) || !empty($_POST['AreaShortName']) || !empty($_POST['Area_Parent_NId']) || !empty($_POST['Area_Block'])) {

                    $conditions = array();
                    $params[] = $conditions;
                    $deleteallArea = $this->CommonInterface->serviceInterface('Area', 'deleteByParams', $params, $dbConnection);
                    if ($deleteallArea) {
                        $returnData['message'] = 'Record deleted successfully';
                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $deleteallArea;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {
                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }

                break;

            case 803:
                // service for saving the  Area details using  any parameters below 
                if (!empty($_POST['Area_ID']) || !empty($_POST['Area_Name']) || !empty($_POST['Area_GId']) || !empty($_POST['Area_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Data_Exist']) || !empty($_POST['AreaShortName']) || !empty($_POST['Area_Parent_NId']) || !empty($_POST['Area_Block'])) {
                    $conditions = array();
                    $params[] = $conditions;
                    $insertAreadata = $this->CommonInterface->serviceInterface('Area', 'insertUpdateAreaData', $params, $dbConnection);
                    if ($insertAreadata) {
                        $returnData['message'] = 'Record saved successfully';
                        $returnData['success'] = true;
                        $returnData['returnvalue'] = $insertAreadata;
                    } else {
                        $returnData['success'] = false;
                    }
                } else {
                    $returnData['success'] = false;
                    $returnData['message'] = 'Invalid request';      //COM005; //'Invalid request'		
                }

                break;


            case 901:
                //  service for getting the AREA LEVEL details on basis of passed parameters
                if (!empty($_POST['Level_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Area_Level_Name'])) {
                    $conditions = array();
                    $params[] = $fields = [_AREALEVEL_LEVEL_NAME, _AREALEVEL_AREA_LEVEL, _AREALEVEL_LEVEL_NID];
                    $params[] = $conditions;

                    $getAreaLevelDetailsData = $this->CommonInterface->serviceInterface('Area', 'getDataByParamsAreaLevel', $params, $dbConnection);

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
                // service for deleting the Area using  any parameters below 
                if (!empty($_POST['Level_NId']) || !empty($_POST['Area_Level']) || !empty($_POST['Area_Level_Name'])) {

                    $conditions = array();
                    $params[] = $conditions;
                    $deleteallAreaLevel = $this->CommonInterface->serviceInterface('Area', 'deleteByParamsAreaLevel', $params, $dbConnection);
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
                    //$filename = $extra['filename'];
                    //$params[]['filename'] = $filename;
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
                        $returnData['data'] = $this->CommonInterface->serviceInterface('CommonInterface', 'exportIcius', [], $dbConnection);
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

                        if ($returnUniqueDetails === true) {

                            if ($returnTestDetails === true) {
                                $db_con_id = $this->Common->createDatabasesConnection($this->request->data);
                                if ($db_con_id) {
                                    $returnData['status'] = _SUCCESS;        // database added 
                                    //$returnData['database_id'] = $db_con_id;
                                } else {
                                    $returnData['errCode'] = _ERR100;      // database not added 
                                }
                            } else {
                                $returnData['errCode'] = _ERR101; // Invalid database connection details 
                            }
                        } else {
                            $returnData['errCode'] = _ERR102; // connection name is  not unique 
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
                            $returnDatabaseDetails = $this->Common->deleteDatabase($dbId, $authUserId);
                            $getDBDetailsById = $this->Common->getDbNameByID($dbId);
                            if ($returnDatabaseDetails) {
                                $returnData['status'] = _SUCCESS; // records deleted
                                $returnData['data'] = $getDBDetailsById;
                                $returnData['responseKey'] = '';
                            } else {
                                $returnData['errCode'] = _ERR105; // // no  record deleted
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

            // service bascially  for testing of dbdetails on basis of dbId
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

            // service for  listing of users belonging to specific  db details with their roles and access  
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

            // service for  deleteion of  users with respect to associated db and roles respectively
            case 1200:
                if ($this->request->is('post')) {
                    try {
                        $userIds = '';
                        if (isset($this->request->data['userIds']) && !empty($this->request->data['userIds']))
                            $userIds = $this->request->data['userIds'];

                        if (isset($userIds) && !empty($userIds)) {
                            if (isset($dbId) && !empty($dbId)) {
                                $deleteAllUsersDb = $this->UserCommon->deleteUserRolesAndDbs($userIds, $dbId);
                                if ($deleteAllUsersDb > 0) {
                                    $returnData['status'] = _SUCCESS;

                                    $returnData['responseKey'] = '';
                                } else {
                                    $returnData['errCode'] = _ERR110;      // Not deleted   
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

                        $data = array();
                        if (isset($_POST['name']) && !empty($_POST['name']) && isset($_POST['email']) && !empty($_POST['email'])) {

                            $this->request->data[_USER_NAME] = trim($_POST['name']);
                            $conditions[_USER_EMAIL] = $this->request->data[_USER_EMAIL] = trim($_POST['email']);
                            $userId = '0'; 
                            if (isset($this->request->data[_USER_ID]) && !empty($this->request->data[_USER_ID])) {
                                $userId = $this->request->data[_USER_ID]; // case of modify or when user is already added once 
                            } else {
                                $this->request->data[_USER_STATUS] = _INACTIVE; // 0 means Inactive
                            }
                            $isModified = $this->request->data['isModified']; // when value is false its add case else modify one  
                            $chkuserDbRel = 0;
                            $this->request->data[_USER_MODIFIEDBY] = $authUserId;
                            $this->request->data[_USER_CREATEDBY] = $authUserId;
							$this->request->data['areaAccess'] =1;
							$this->request->data['indAccess'] =1;
                            $rolesarray = $this->request->data['roles'];
                            if (isset($rolesarray) && count($rolesarray) > 0) {
                                if (isset($dbId) && !empty($dbId)) {
                                    $chkEmail = $this->UserCommon->checkEmailExists($this->request->data[_USER_EMAIL], $this->request->data[_USER_ID]);
                                    if ($chkEmail == 0) {   // email is unique
                                        if ($isModified == 'false' && $userId != '0') {
                                            $chkuserDbRel = $this->UserCommon->checkUserDbRelation($userId, $dbId); //check whether user is already added or not 							 
                                        }

                                        if ($chkuserDbRel == 0) {    //user is not  associated with this db 
                                           $this->request->data['areaid'] = ['IND008', 'IND007', 'IND006'];
                                           $this->request->data['indGids'] = ['LR_7PLUS', 'D127A005-397D-3091-5253-D3279AC481AA', 'C1A7FA43-340F-7506-979A-501CF11AB325'];
										   
                                            $lastIdinserted = $this->UserCommon->addModifyUser($this->request->data, $dbId);
                                            if ($lastIdinserted > 0) {
                                                $returnData['status'] = _SUCCESS;
                                                $returnData['responseKey'] = '';

                                                if ($userId == '0') {
                                                    $fields = [_USER_ID];
                                                    $conditions[_USER_STATUS] = _INACTIVE;
                                                    $userdetails = $this->UserCommon->getDataByParams($fields, $conditions);
                                                    if (!empty($userdetails)) {
                                                        $registeredUserId = current($userdetails)[_USER_ID];
                                                       // $this->UserCommon->sendActivationLink($registeredUserId, $this->request->data[_USER_EMAIL], $this->request->data[_USER_NAME]);
                                                    }
                                                } else {
                                                    if ($isModified == 'false') {
                                                        $this->UserCommon->sendDbAddNotify($this->request->data[_USER_EMAIL], $this->request->data[_USER_NAME]);
                                                    }
                                                }
                                            } else {
                                                $returnData['errCode'] = _ERR114;      //  user not modified due to database error 
                                            }
                                        } else {
                                            $returnData['errCode'] = _ERR119;   //  user is already added to this database   
                                        }
                                    } else {
                                        $returnData['errCode'] = _ERR118;   //  user not modified due to email  already exists  
                                    }
                                } else {
                                    $returnData['errCode'] = _ERR106;      //  db id empty 
                                }
                            } else {
                                $returnData['errCode'] = _ERR112;      //  Roles are  empty
                            }
                        } else {
                            $returnData['errCode'] = _ERR111;      //  Email or  name may be empty 
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
                //if ($this->request->is('post')) {
                if (true) {
                    if (!empty($authUserId)) {
                        $userId = 186; //$this->request->data['userId'];
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
                            $explodestring = explode('-', $decodedstring);

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
                                                $returnData['errCode'] = _ERR116;      // password not updated   
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
                if ($authUserRoleId == _SUPERADMINROLEID)
                    $returnData['data']['user']['role'][] = _SUPERADMINNAME;
                else
                    $returnData['data']['user']['role'][] = '';

                if ($authUserId) {
                    $returnData['isAuthenticated'] = true;
                }
                //echo json_encode($returnData);
                break;



            case 2101: //Select Data using _IC_IC_NID -- Indicator Classification table

                $params[] = [446, 447, 448];
                //getDataByIds($ids = null, $fields = [], $type = 'all' )
                $returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getDataByIds', $params, $dbConnection);
                break;

            case 2102: //Select Data using Conditions -- Indicator Classification table

                $fields = [_IC_IC_PARENT_NID, _IC_IC_NAME, _IC_IC_GID, _IC_IC_TYPE];
                $conditions = [_IC_IC_GID . ' IN' => ['60F415DF-FDE8-8442-2A8B-B5FE582DB65B', '6E6080E5-4C43-6019-47FE-6C5BBFB44E9D']];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                //getDataByParams(array $fields, array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getDataByParams', $params, $dbConnection);
                break;

            case 2103: //Delete Data using _IC_IC_NID -- Indicator Classification table
                //deleteByIds($ids = null)
                $params[] = [472, 473, 474];
                $returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'deleteByIds', $params, $dbConnection);
                break;

            case 2104: //Delete Data using Conditions -- Indicator Classification table
                //deleteByParams(array $conditions)
                $params['conditions'] = $conditions = [_IC_IC_GID . ' IN' => ['91E4A3EF-4D2C-9325-2C9D-D6B102522180', '26E78CB8-1E20-457D-45E7-6F631114AB6E']];
                $returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'deleteByParams', $params, $dbConnection);
                break;

            case 2105: //Insert New Data -- Indicator Classification table
                if ($this->request->is('post')):
                    //if (true):
                    $this->request->data = [
                        _IC_IC_PARENT_NID => '-1',
                        _IC_IC_GID => 'SOME_001_TEST',
                        _IC_IC_NAME => 'Custom_test_name2',
                        _IC_IC_TYPE => 'SC'
                    ];

                    //insertData(array $fieldsArray = $this->request->data)
                    $params['conditions'] = $conditions = $this->request->data;
                    $returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'insertData', $params, $dbConnection);
                endif;
                break;

            case 2106: //Update Data using Conditions -- Indicator Classification table

                $fields = [
                    _IC_IC_NAME => 'Custom_test_name3',
                    _IC_IC_GID => 'SOME_001_TEST'
                ];
                $conditions = [_IC_IC_GID => 'SOME_001_TEST'];

                if ($this->request->is('post')):
                    //if (true):
                    //updateDataByParams(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;

                    $returnData = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'updateDataByParams', $params, $dbConnection);
                endif;

                break;

            case 2107: //Bulk Insert/Update Data -- Indicator Classification table
                if ($this->request->is('post')):

                endif;
                break;

            case 2201: //get IUS List by Id -- Indicator Unit Subgroup table
                if ($this->request->is('post')):
                    //if (true):
                    $params[] = [446, 447, 448];
                    //getDataByIds($ids = null, $fields = [], $type = 'all' )
                    $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getDataByIds', $params, $dbConnection);
                endif;
                break;

            case 2202: //Select Data using Conditions -- Indicator Unit Subgroup table

                $fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID];
                $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN' => [244, 25]];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                //getDataByParams(array $fields, array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getDataByParams', $params, $dbConnection);
                break;

            case 2203: //Delete Data using _IUS_IUSNID -- Indicator Unit Subgroup table
                //deleteByIds($ids = null)
                $params[] = [383, 384, 385];
                $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'deleteByIds', $params, $dbConnection);
                break;

            case 2204: //Delete Data using Conditions -- Indicator Unit Subgroup table
                //deleteByParams(array $conditions)
                $params['conditions'] = $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'deleteByParams', $params, $dbConnection);
                break;

            case 2205: //Insert New Data -- Indicator Unit Subgroup table
                if ($this->request->is('post')):

                    $this->request->data = [
                        _IUS_INDICATOR_NID => '384',
                        _IUS_UNIT_NID => 'Short name',
                        _IUS_SUBGROUP_VAL_NID => 'Some Keyword',
                    ];

                    //insertData(array $fieldsArray = $this->request->data)
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
                    //updateDataByParams(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'updateDataByParams', $params, $dbConnection);
                endif;

                break;



            case 2209: //get Tree Structure List
                //if ($this->request->is('post')):
                if (true):
                    $this->request->data['type'] = _TV_ICIND;
                    $this->request->data['onDemand'] = false;
                    // Post Variables                    
                    // possible Types Area,IU,IUS,IC and ICIND
                    $type = (isset($this->request->data['type'])) ? $this->request->data['type'] : '';
                    $parentId = (isset($this->request->data['pnid'])) ? $this->request->data['pnid'] : '-1';
                    $onDemand = (isset($this->request->data['onDemand'])) ? $this->request->data['onDemand'] : true;
                    $returnData['data'] = $this->Common->getTreeViewJSON($type, $dbId, $parentId, $onDemand);
                    $returnData['status'] = _SUCCESS;
                    $returnData['responseKey'] = $type;
                endif;
                break;

            case 2210: //get Subgroup List from IU Gids -- Indicator Unit Subgroup table
                if ($this->request->is('post')):
                    //if (true):
                    $fields = [_IUS_SUBGROUP_VAL_NID];

                    $params['fields'] = $fields;
                    $params['conditions'] = ['iGid' => '075362FE-0120-55C1-4520-914CFDA8FA0B', 'uGid' => '69299B62-FD0A-9936-3E72-688AD73B4709'];
                    $params['extra'] = ['type' => 'all', 'unique' => true];
                    $returnData['data'] = $this->CommonInterface->serviceInterface('IndicatorUnitSubgroup', 'getAllSubgroupsFromIUGids', $params, $dbConnection);
                    $returnData['status'] = _SUCCESS;
                    $returnData['responseKey'] = 'subgroupList';
                    $returnData['errCode'] = '';
                    $returnData['errMsg'] = '';
                endif;
                break;

            case 2211: //get IUS Details FROM IU(S) GIDs -- Indicator Unit Subgroup table

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

                    $iusGids = (isset($this->request->data['iusId'])) ? $this->request->data['iusId'] : '';
                    if (!empty($iusGids)) {

                        $extra = [];
                        $extra['isTextual'] = (isset($this->request->data['isTextual'])) ? $this->request->data['isTextual'] : 0;
                        $extra['minimumValue'] = (isset($this->request->data['minimumValue'])) ? $this->request->data['minimumValue'] : null;
                        $extra['maximumValue'] = (isset($this->request->data['maximumValue'])) ? $this->request->data['maximumValue'] : null;
                        $check = $this->Common->addUpdateIUSValidations($dbId, $iusGids, $extra);

                        if ($check) {
                            $status = _SUCCESS;
                            $returnData['errMsg'] = true;
                        }
                    }

                    $returnData['status'] = $status;
                    $returnData['responseKey'] = 'iusValidationsSave';
                    $returnData['errCode'] = '';
                endif;
                break;

            case 2301: //Get Records -- ICIUS table

                $params[] = [446, 447, 448];
                //getDataByIds($ids = null, $fields = [], $type = 'all' )
                $returnData = $this->CommonInterface->serviceInterface('IcIus', 'getDataByIds', $params, $dbConnection);
                break;

            case 2302: //Select Data using Conditions -- ICIUS table

                $fields = [_ICIUS_IC_NID, _ICIUS_IUSNID];
                $conditions = [_ICIUS_IC_NID . ' IN' => [244, 25]];

                $params['fields'] = $fields;
                $params['conditions'] = $conditions;

                //getDataByParams(array $fields, array $conditions)
                $returnData = $this->CommonInterface->serviceInterface('IcIus', 'getDataByParams', $params, $dbConnection);
                break;

            case 2303: //Delete Data using _ICIUS_IC_IUSNID -- ICIUS table
                //deleteByIds($ids = null)
                $params[] = [1000, 256, 385];
                $returnData = $this->CommonInterface->serviceInterface('IcIus', 'deleteByIds', $params, $dbConnection);
                break;

            case 2304: //Delete Data using Conditions -- ICIUS table
                //deleteByParams(array $conditions)
                $params['conditions'] = $conditions = [_ICIUS_IC_NID . ' IN' => ['TEST_GID', 'TEST_GID2']];
                $returnData = $this->CommonInterface->serviceInterface('IcIus', 'deleteByParams', $params, $dbConnection);
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
                    //updateDataByParams(array $fields, array $conditions)
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $returnData = $this->CommonInterface->serviceInterface('IcIus', 'updateDataByParams', $params, $dbConnection);
                endif;
                break;


            case 2307: //Bulk Insert/Update Data -- ICIUS table
                if ($this->request->is('post')):
                    //if (true):
                    //$params['filename'] = $filename = 'C:\-- Projects --\xls\Temp_Selected_ExcelFile.xls';
                    $params['filename'] = $extra['filename'];
                    $params['component'] = 'Icius';
                    $params['extraParam'] = [];
                    return $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'bulkUploadXlsOrCsv', $params, $dbConnection);
                endif;

                break;

            case 2401: //Upload Files
                if ($this->request->is('post')):
                    //if (true):
                    try {
                        $extraParam = [];

                        $seriveToCall = strtolower($this->request->data['type']);
                        $allowedExtensions = ['xls', 'xlsx'];

                        // Kept here to include other params like allowed ext as well
                        switch ($seriveToCall):
                            case _ICIUS:
                                $case = 2307;
                                $module = _ICIUS;
                                $extraParam['createLog'] = true;
                                break;
                            case _AREA:
                                $case = 904;
                                $module = _AREA;
                                break;
                        endswitch;

                        $extraParam['module'] = $module;

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

                                $returnData['errMsg'] = $return['error'];
                            } else {
                                //-- TRANSAC Log
                                $logFileName = basename($return);
                                $fieldsArray = [_MTRANSACTIONLOGS_STATUS => _SUCCESS, _MTRANSACTIONLOGS_IDENTIFIER => $logFileName];
                                $conditions = [_MTRANSACTIONLOGS_ID => $LogId];
                                $this->TransactionLogs->updateRecord($fieldsArray, $conditions);

                                $return = _WEBSITE_URL . _CHUNKS_PATH_WEBROOT . DS . $logFileName;
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
                    //if (true):
                    $returnData['data'] = $this->CommonInterface->serviceInterface('CommonInterface', 'exportIcius', [], $dbConnection);

                    $returnData['status'] = 'success';
                    $returnData['responseKey'] = 'iciusExport';
                    $returnData['errCode'] = '';
                    $returnData['errMsg'] = '';
                endif;
                break;

            // service to get search data on basis of IUS ,timeperiod and area 
            case 2403:
                //if ($this->request->is('post')):


                try {

                    $iusgidArray = [
                        'LR_7PLUS' . _DELEM2 . '20C6CF95-37AA-C024-FE3B-895AFD42EEF8' . _DELEM2 . '21A70BB5-3833-FDAA-2A1E-99B990A0CC7E'
                        , 'LR_7PLUS' . _DELEM2 . '20C6CF95-37AA-C024-FE3B-895AFD42EEF8' . _DELEM2 . '9E361AE4-35F5-F7EE-4AAA-C584923BFB4F'
                        // , 'LTR_7PLUS' . _DELEM2 . 'BBCFF050-90E9-F3F6-3A7A-30CFB9BF9A39'
                        // , 'MAINWORK_OT' . _DELEM2 . 'BBCFF050-90E9-F3F6-3A7A-30CFB9BF9A39'
                        //,'AREA'. _DELEM2 . 'SQKM'
                        //,'97D798F6-8C22-927F-0B0C-CDD49939276D'. _DELEM2 . '20C6CF95-37AA-C024-FE3B-895AFD42EEF8',
                        , 'D127A005-397D-3091-5253-D3279AC481AA' . _DELEM2 . 'B602B58B-6879-4188-9D49-DD833281FE4E'
                            //._DELEM2 . '21A70BB5-3833-FDAA-2A1E-99B990A0CC7E'
                    ];

                    //	$iusgidArray=['LR_7PLUS'._DELEM2.'20C6CF95-37AA-C024-FE3B-895AFD42EEF8'];
                    $areaNid = '18274';
                    $timePeriodNid = '2';

                    $conditions = [_MDATA_TIMEPERIODNID => $timePeriodNid, _MDATA_AREANID => $areaNid];
                    $fields = [_MDATA_NID, _INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME];
                    $params['fields'] = $fields;
                    $params['conditions'] = $conditions;
                    $params['extra'] = $iusgidArray;
                    $returnData = $this->CommonInterface->serviceInterface('CommonInterface', 'getDEsearchData', $params, $dbConnection);

                    //--- Prepare IUS Validations
                    $iusGids = $returnData['iusValidations'];
                    $iusValidations = [];
                    $fields = [
                        _MIUSVALIDATION_INDICATOR_GID,
                        _MIUSVALIDATION_UNIT_GID,
                        _MIUSVALIDATION_SUBGROUP_GID,
                        _MIUSVALIDATION_IS_TEXTUAL,
                        _MIUSVALIDATION_MIN_VALUE,
                        _MIUSVALIDATION_MAX_VALUE
                    ];
                    $conditions = ['OR' => $iusGids, _MIUSVALIDATION_DB_ID => $dbId];
                    $IusValidationsRecordExist = $this->MIusValidations->getRecords($fields, $conditions, 'all', $extra = []);
                    foreach ($IusValidationsRecordExist as $records) {
                        $isTextual = ($records[_MIUSVALIDATION_IS_TEXTUAL] == '1') ? true : false;
                        $minimumValue = $records[_MIUSVALIDATION_MIN_VALUE];
                        $maximumValue = $records[_MIUSVALIDATION_MAX_VALUE];
                        $isMinimum = ($minimumValue === NULL || $minimumValue === '') ? false : true;
                        $isMaximum = ($maximumValue === NULL || $maximumValue === '') ? false : true;
                        $validationsArray = [
                            'isTextual' => $isTextual,
                            'isMinimum' => $isMinimum,
                            'isMaximum' => $isMaximum,
                            'minimumValue' => $minimumValue,
                            'maximumValue' => $maximumValue,
                        ];
                        $iusValidations = [
                            $records[_MIUSVALIDATION_INDICATOR_GID]
                            . _DELEM1
                            . $records[_MIUSVALIDATION_UNIT_GID]
                            . _DELEM1
                            . $records[_MIUSVALIDATION_SUBGROUP_GID] => $validationsArray
                        ];
                    }

                    $returnData['status'] = _SUCCESS;
                    // Ius Data
                    $returnData['responseKey'][] = 'iusData';
                    $returnData['data'][] = $returnData['iu'];

                    // Ius Validations Data
                    $returnData['responseKey'][] = 'iusValidations';
                    $returnData['data'][] = $iusValidations;
                } catch (Exception $e) {
                    $returnData['errMsg'] = $e->getMessage();
                }
                //endif;
                break;

            case 2404: // Save Data from IUS ,timeperiod and area

                if (true):
                    $demo = [
                        [
                            'dNid' => '',
                            'iusId' => 2599,
                            'dataValue' => '22',
                            'source' => 401,
                            'footnote' => 'Demo Footnote',
                            'timeperiod' => 2,
                            'areaId' => 'IND',
                        ],
                        [
                            'dNid' => 6054328,
                            'iusId' => 2793,
                            'dataValue' => '152',
                            'source' => 401,
                            'footnote' => 'Some Text',
                            'timeperiod' => 2,
                            'areaId' => 'IND',
                        ]
                    ];
                    $this->request->data['iusDetails'] = json_encode($demo); //'tp'
                    $iusDetails = $this->request->data['iusDetails'];
                    
                    if (!empty($iusDetails)) {
                        $params = [
                            'dataDetails' => $iusDetails,
                            'extra' => $extra,
                        ];
                        $returnData = $this->CommonInterface->serviceInterface('Data', 'saveDataEntry', $params, $dbConnection);
                        
                        if(isset($result['error'])){
                            $returnData['errMsg'] = $result['error'];
                            $status = _FAILED;
                        }else{
                            $returnData['data'] = $result;
                            $status = _SUCCESS;
                        }
                    }

                    $returnData['status'] = $status;
                    $returnData['responseKey'] = $responseKey;
                    $returnData['errCode'] = '';
                    $returnData['errMsg'] = '';
                endif;
                break;
                
            case 2405: // Get DE single table lists

                if (true):
                    $this->request->data['type'] = 'source'; //'tp'
                    $type = $this->request->data['type'];
                    if ($type == 'source') {
                        $params = ['fields' => ['srcGid' => _IC_IC_GID, 'srcName' => _IC_IC_NAME]];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getSourceList', $params, $dbConnection);
                        $responseKey = 'source';
                    } else if ($type == 'tp') {
                        $params = ['fields' => ['tpNid' => _TIMEPERIOD_TIMEPERIOD_NID, 'tpName' => _TIMEPERIOD_TIMEPERIOD]];
                        $returnData['data'] = $this->CommonInterface->serviceInterface('Timeperiod', 'getDataByParams', $params, $dbConnection);
                        $responseKey = 'tp';
                    }

                    $returnData['status'] = _SUCCESS;
                    $returnData['responseKey'] = $responseKey;
                    $returnData['errCode'] = '';
                    $returnData['errMsg'] = '';
                endif;
                break;
				
				case 2405:
                 if (true):
                    $this->request->data['type'] = _TV_ICIND;
                    $this->request->data['onDemand'] = false;
					die;
                    // Post Variables                    
                    // possible Types Area,IU,IUS,IC and ICIND
                    $type = (isset($this->request->data['type'])) ? $this->request->data['type'] : '';
                    $parentId = (isset($this->request->data['pnid'])) ? $this->request->data['pnid'] : '-1';
                    $onDemand = (isset($this->request->data['onDemand'])) ? $this->request->data['onDemand'] : true;

                    $returnData['data'] = $this->Common->getTreeViewJSON($type, $dbId, $parentId, $onDemand);

                    $returnData['status'] = _SUCCESS;
                    $returnData['responseKey'] = $type;
                endif;
                break;


            default:
                break;

        endswitch;

        return $this->service_response($returnData, $convertJson, $dbId);
    }

    public function service_response($response, $convertJson = _YES, $dbId) {

        // Initialize Result		
        $success = false;
        $isAuthenticated = false;
        $isSuperAdmin = false;
        $errCode = '';
        $errMsg = '';
        $dataUsrId = '';
        $dataUsrUserId = '';
        $dataUsrUserName = '';
        $dataUsrUserRole = [];
        $dataDbDetail = '';
        $dataUsrDbRoles = [];

        if ($this->Auth->user('id')) {

            $isAuthenticated = true;
            $dataUsrId = session_id();
            $dataUsrUserId = $this->Auth->user('id');
            $dataUsrUserName = $this->Auth->user('name');
            $role_id = $this->Auth->user('role_id');

            if ($role_id == _SUPERADMINROLEID):
                $isSuperAdmin = true;
                $rdt = $this->Common->getRoleDetails($role_id);
                $dataUsrUserRole[] = $rdt[1];
            endif;

            if ($dbId):
                $returnSpecificDbDetails = $this->Common->getDbNameByID($dbId);
                $dataDbDetail = $returnSpecificDbDetails;

                if ($role_id != _SUPERADMINROLEID):
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

        // Set Result
        $returnData['success'] = $success;
        $returnData['isAuthenticated'] = $isAuthenticated;
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
