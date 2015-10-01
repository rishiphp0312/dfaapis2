<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Statement\PDOStatement;
use Cake\Core\Configure;
use Cake\View\View;

//use Cake\Network\Email\Email;

/**
 * Common period Component
 */
class CommonComponent extends Component {

    public $Users = null;
    public $Roles = null;
    public $Modules = null;
    public $RolesModules = null;
    public $FieldOptionsObj = null;
    public $FieldOptionValues = null;
    public $ConfigItemsObj = null;
    public $components = ['Auth', 'UserCommon', 'Shipment', 'Administration','Area'];
    public $processdAreaIds = [];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->arrayDepth = 1;
        $this->arrayDepthIterator = 1;
        $this->Users = TableRegistry::get('Users');
        $this->Roles = TableRegistry::get('Roles');
        $this->Modules = TableRegistry::get('Modules');
        $this->RolesModules = TableRegistry::get('RolesModules');
        $this->FieldOptionsObj = TableRegistry::get('FieldOptions');
        $this->FieldOptionValues = TableRegistry::get('FieldOptionValues');
        $this->ConfigItemsObj = TableRegistry::get('ConfigItems');
    }

    /**
     * guid is function which returns gid
     */
    public function guid() {

        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            //$uuid =// chr(123)// "{"
            $uuid = substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12);
            //.chr(125);// "}"
            return $uuid;
        }
    }

    /**
     * Check for valid Guid
     * 
     * @param string $guid Guid String accepts only A-Z, a-z, @, 0-9, _, -, $
     * @return boolean true/false
     */
    public function validateGuid($gid) {
        if (preg_match('/^[0-9a-zA-Z\$@\_\-]+$/', $gid) === 0) {
            return false;  // not valid 
        } else {
            return true; // when its valid 
        }
    }

    /**
     * function to check activation link is used or not
     * @params $userId , $email
     */
    public function checkActivationLink($userId) {
        $status = $this->Users->checkActivationLink($userId);
        return $status;
    }

    /**
     * Get mime Types List
     * 
     * @param array $allowedExtensions Allowed extensions
     * @return Mime Types array
     */
    public function mimeTypes($allowedExtensions = []) {
        $mimeTypes = [
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'zip2' => 'application/octet-stream',
            'jpg' => 'image/jpeg',
            'jpg2' => 'image/pjpeg',
            'jpeg' => 'image/jpeg',
            'jpeg2' => 'image/pjpeg',
            'png' => 'image/png',
        ];

        $allowedExtensionsMimeTypes = array_intersect_key($mimeTypes, array_flip($allowedExtensions));

        return $allowedExtensionsMimeTypes;
    }

    /**
     * Process File uploads
     * 
     * @param array $files POST $_FILES Variable
     * @param array $extensions Valid extension allowed 
     * @return uploaded filename
     */
    public function processFileUpload($files = null, $allowedExtensions = [], $extra = []) {

        // Check Blank Calls
        if (!empty($files)) {

            foreach ($files as $fieldName => $fileDetails):
                // Check if file was uploaded via HTTP POST
                if (!is_uploaded_file($fileDetails['tmp_name'])) :
                    return ['error' => _ERROR_UNACCEPTED_METHOD];
                endif;

                if (isset($extra['dest']) && $extra['dest'] == true) {
                    $pathinfo = pathinfo($fileDetails['name']);
                    if(isset($extra['newFileName'])) {
                        $dest = $extra['dest'] . DS . $extra['newFileName'] . '.' . $pathinfo['extension'];
                    } else {
                        $dest = $extra['dest'] . DS . date('Y-m-d-h-i-s', time()) . '_' . rand(25, 222569) . '.' . $pathinfo['extension'];
                    }
                } else {
                    $dest = _XLS_PATH . DS . $fileDetails['name'];
                }

                $mimeType = $fileDetails['type'];
                if (!in_array($mimeType, $this->mimeTypes($allowedExtensions))) {
                    return ['error' => _ERROR_INVALID_FILE];
                }

                // Upload File
                if (move_uploaded_file($fileDetails['tmp_name'], $dest)) :
                    if (isset($extra['createLog']) && $extra['createLog'] == true) {
                        $pathinfo = pathinfo($fileDetails['name']);
                        
                        // CREATE duplicate file for LOG
                        $copyDest = _LOGS_PATH . DS . _IMPORTERRORLOG_FILE . '_' . $extra['subModule'] . '_' . $extra['dbName'] . '_' . date('Y-m-d-h-i-s', time()) . '.' . $pathinfo['extension'];
                        if (!@copy($dest, $copyDest)) {
                            return ['error' => _ERROR_UPLOAD_FAILED];
                        }
                        define('_LOG_FILEPATH', $copyDest);
                    }
                    $filePaths[] = $dest;   // Upload Successful

                else:
                    return ['error' => _ERROR_UPLOAD_FAILED];   // Upload Failed
                endif;

            endforeach;

            return $filePaths;
        }
        return ['error' => _ERROR_LOCATION_INACCESSIBLE];
    }

    /**
     * function to get role details
     */
    public function getRoleDetails($roleId) {

        return $this->Roles->getRoleByID($roleId);
    }

    /**
     * method to post the data using curl and save into job db
     * @requestedData posted data by service 
     */
    public function executecurl($requestedData) {

        $ch = curl_init(_JOB_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestedData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        $resp = curl_exec($ch);
        curl_close($ch);
    }

    /**
     * method to verify valid email 
     * @email email  
     */
    public function validEmail($email = '') {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return true; //valid email
        } else {
            return false; //invalid email
        }
    }

    /**
     * send Response header to AJAX request
     * 
     * @param integer $code HTTP request code
     */
    public function sendResponseHeader($code) {
        switch ($code):
            //-- Unauthorized
            case 401:
                header('X-PHP-Response-Code: 401', true, 401);
                break;

            //-- Forbidden
            case 403:
                header('X-PHP-Response-Code: 403', true, 403);
                break;

            //-- Not Found
            case 404:
                header('X-PHP-Response-Code: 404', true, 404);
                break;

            //-- Internal Server Error
            case 500:
                header('X-PHP-Response-Code: 500', true, 500);
                break;

            //-- Invalid Header Code
            default:
                echo 'INVAID HEADER CODE';
                break;
        endswitch;

        exit;
    }

    /**
     * GET modules permissions
     * 
     * @param integer $roleId Role ID
     * @param integer $moduleId Module ID
     * @param array $fields fields to fetch/get
     * @return array modules-roles result array
     */
    public function getModulesPermissions($roleId = null, $moduleId = null, $fields = [], $getParentList = true) {

        $conditions = [];
        $return['permission'] = [];

        if (!empty($roleId))
            $conditions['role_id'] = $roleId;
        if (!empty($moduleId))
            $conditions['module_id'] = $moduleId;

        $query = $this->RolesModules->find('all', ['fields' => $fields, 'conditions' => $conditions])->contain(['Roles', 'Modules']);
        $results = $query->hydrate(false)->all()->toArray();

        if (!empty($results)) {

            $parentModulesList = $this->Modules->find('list', ['fields' => [], 'conditions' => ['parent_id' => '-1']])->hydrate(false)->all()->toArray();

            foreach ($results as $result) {

                $role = $result['role']['role'];
                $parentId = $result['module']['parent_id'];
                $moduleTitle = $result['module']['title'];
                $moduleId = $result['module']['id'];

                // We dont need parents
                if ($parentId != '-1') {
                    $permissions[$role][$parentModulesList[$parentId]][$moduleTitle] = [
                        'C' => $result['create'],
                        'R' => $result['read'],
                        'U' => $result['update'],
                        'D' => $result['delete'],
                    ];
                }
            }

            if (!empty($roleId)) {
                $return['permission'] = $permissions[$role];
            } else {
                $return['permission'] = $permissions;
            }

            // Attach parent list
            if ($getParentList)
                $return['parentList'] = $parentModulesList;
        }

        return $return;
    }

    /*
      method to check boundary length
     */

    public function checkBoundaryLength($inputVal = '', $length = '') {

        if ($inputVal != '') {
            if (strlen($inputVal) > $length) {
                return false;
            }
        }
        return true;
    }

    /**
     * GET roles
     */
    public function getRoles($condtions = [], $fields = []) {
        $return = [];
        $results = $this->Roles->find('all', ['fields' => $fields, 'conditions' => $condtions])->hydrate(false)->all()->toArray();

        if (!empty($results)) {
            foreach ($results as $key => $result) {
                $return[$result['role']] = $result;
            }
        }
        return $return;
    }

    /*
     * method getTypeLists is the type list for packages and locations  
     * $code=''
     */

    public function getTypeLists($code = '', $list = false) {
        $listTypes = [];
        $data = $this->FieldOptionsObj->getTypeLists($code);
       
        if (!empty($data)) {
            foreach ($data as $index => $value) {
                if (count($value['field_option_values']) > 0) {
                    foreach ($value['field_option_values'] as $innerIndex => $innerValue) {
                        if($list == true) {
                            $listTypes[$innerValue['id']] = $innerValue['name'];
                        } else {
                            $listTypes[$innerIndex]['name'] = $innerValue['name'];
                            $listTypes[$innerIndex]['id'] = $innerValue['id'];
                        }
                    }
                }
            }
        }
        
        return $listTypes;
    }

    /**
     * GET Tree view
     */
    public function getTreeViewJSON($type = _TV_AREA, $parentId = -1, $onDemand = true) {
        $returndData = $extra = [];

        switch (strtolower($type)) {
            case _TV_AREA:
                $returndData = $this->getParentChild(_TV_AREA, $parentId, $onDemand, $extra);
                break;
        }

        $data = $this->convertDataToTVArray($type, $returndData, $onDemand, $extra);

        return $data;
    }

    /**
     * Convert Tree view RAW data to TREE view array format
     */
    public function convertDataToTVArray($type, $dataArray, $onDemand, $extra = []) {
        $returnArray = array();
        $i = 0;
        foreach ($dataArray as $dt) {

            $caseData = $this->convertDataToTVArrayCase($type, $dt, $extra);

            if (isset($caseData['returnData']) && $onDemand == true) {
                $caseData['returnData']['type'] = $type;
                $caseData['returnData']['onDemand'] = $onDemand;
            }

            $returnArray[$i]['id'] = $caseData['id'];
            $returnArray[$i]['code'] = $caseData['code'];
            $returnArray[$i]['fields'] = $caseData['fields'];
            $returnArray[$i]['returnData'] = $caseData['returnData'];
            $returnArray[$i]['isChildAvailable'] = $dt['childExists'];
            if (count($dt['nodes']) > 0) {
                $returnArray[$i]['nodes'] = $this->convertDataToTVArray($type, $dt['nodes'], $onDemand);
            } else {
                $returnArray[$i]['nodes'] = $dt['nodes'];
            }

            $i++;
        }

        return $returnArray;
    }

    /**
     * CASE WISE
     * Convert Tree view RAW data to TREE view array format
     */
    function convertDataToTVArrayCase($type, $data, $extra = []) {
        $retData = $fields = $returnData = array();
        $rowid = $uid = '';

        switch (strtolower($type)) {
            case _TV_AREA:
                $fields = array('aname' => $data['name']);
                $returnData = array(
                    'id' => $data['id'],
                    'code' => $data['code'],
                    'level' => $data['areaLvl']
                );
                break;
        }

        return array('id' => $data['id'], 'code' => $data['code'], 'fields' => $fields, 'returnData' => $returnData);
    }

    /**
     * GET Parent-Child Relationship associative array
     */
    public function getParentChild($type, $parentId, $onDemand = false, $extra = []) {
        $conditions = [];

        if ($type == _TV_AREA) {
            if (isset($extra['conditions'])) {
                $conditions = array_merge($conditions, $extra['conditions']);
            } else {
                $conditions['parent_id'] = $parentId;
            }
            $order = array('name' => 'ASC');
            $recordlist = $this->Administration->getAreaList('all', $options = ['conditions' => $conditions, 'order' => $order]);
        }

        $list = $this->getDataRecursive($recordlist, $type, $onDemand, $extra);

        return $list;
    }

    /**
     * function to recursive call to get children 
     *
     * @access public
     */
    public function getDataRecursive($recordlist, $type, $onDemand = false, $extra = []) {

        $rec_list = array();
        $childData = array();
        // start loop through area data
        for ($lsCnt = 0; $lsCnt < count($recordlist); $lsCnt++) {

            $childExists = false;
            $areaLvl = '';

            if ($type == _TV_AREA) {

                $id = $recordlist[$lsCnt]['id'];
                $code = $recordlist[$lsCnt]['code'];
                $name = $recordlist[$lsCnt]['name'];
                $parentId = $recordlist[$lsCnt]['parent_id'];
                $areaLvl = $recordlist[$lsCnt]['area_level_id'];

                if ($onDemand === false) {
                    $childData = $this->Administration->find('all', $model = 'Areas', array('conditions' => ['parent_id' => $id], 'order' => ['name' => 'ASC']));
                } else {
                    $childCount = $this->Administration->find('all', $model = 'Areas', array('conditions' => ['parent_id' => $id]), ['count' => 1]);
                    $childExists = ($childCount) ? true : false;
                    $childExists = (isset($extra['childExists'])) ? $extra['childExists'] : $childExists;
                }
            }

            //if child data found
            if (count($childData) > 0) {

                $this->arrayDepthIterator = $this->arrayDepthIterator + 1;

                if ($this->arrayDepthIterator > $this->arrayDepth) {
                    $this->arrayDepth = $this->arrayDepth + 1;
                }

                $childExists = true;

                // call function again to get selected area another child data
                $dataArr = $this->getDataRecursive($childData, $type);

                $rec_list[] = $this->prepareNode($id, $code, $name, $childExists, $dataArr, $this->arrayDepth, $areaLvl);
            }
            //if child data not found then make list with its id and name
            else {
                $this->arrayDepthIterator = 1;
                $rec_list[] = $this->prepareNode($id, $code, $name, $childExists, array(), 1, $areaLvl);
            }
        }
        // end of loop for area data

        return $rec_list;
    }

    /**
     * method to prepare Node
     *
     * @access public
     */
    public function prepareNode($id, $code, $name, $childExists, $nodes = [], $depth = 1, $areaLvl = '') {
        return array('id' => $id, 'code' => $code, 'name' => $name, 'childExists' => $childExists, 'nodes' => $nodes, 'arrayDepth' => $depth, 'areaLvl' => $areaLvl);
    }

    /**
     * GET all english alphabetical letters
     */
    public function getAlphabets() {
        $letters = range('A', 'Z');
        return $letters;
    }
    
    /**
     * SAVE delivery and confirmation details
     */
    public function saveDeliveryAndConfirmations($data, $files) {
        
        // SAVE delivery and confirmation details
        //$this->Shipment->saveDeliveryAndConfirmations($data, $files);
        $this->Shipment->saveDeliveryPoint($data, $files);
    }
    
    /**
     * get HTML view Data as return
     * 
     * @param array $dataSets View variables
     * @param string $viewPath View path to search for .ctp
     * @param string $filename CTP filename
     * @return string Rendered HTML
     */
    public function getViewHtml($dataSets, $viewPath, $fileName) {

        $html = null;
        
        if(!empty($dataSets) && is_array($dataSets)) {
            
            // Grabbing View Data
            $view = new View($this->request, $this->response, null);

            foreach($dataSets as $dataSetKey => $dataSetValue) {
                // SETTING view variables
                // LIKE - $view->set('data', $data);
                $view->set($dataSetKey, $dataSetValue);
            }

            // Directory inside view directory to search for .ctp files 
            // LIKE - $view->viewPath='Logs';
            $view->viewPath = $viewPath;

            // Layout to use or false to disable
            // LIKE - $view->layout='ajax';
            $view->layout = false;

            // CTP File to render
            // LIKE - $view->render('de_custom_log');
            $html = $view->render($fileName);
        }
        
        return $html;
    }


    /**
      Function to read system configration variables
     * returns array key(code)/default value pair
     */
    public function getSystemConfig($type = 'all', $options = []) {
        $configArray = [];
        if( $type == 'all' && (!isset($options['fields']) || (isset($options['fields']) && empty($options['fields']))) ) {
            $options['fields'] = ['code', 'value', 'default_value'];
        }
        $configData = $this->ConfigItemsObj->getConfigDetails($type, $options);
        if (!empty($configData)) {
            if ($type == 'list') {
                $configArray = $configData;
            } else {
                foreach ($configData as $index => $value) {
                    if ($value['value'] === '' || $value['value'] === null) {
                        $configArray[strtolower($value['code'])] = $value['default_value'];
                    } else {
                        $configArray[strtolower($value['code'])] = $value['value'];
                    }
                }
            }
        }
        return $configArray;
    }
    
    
     /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function readXlsOrCsv($filename = null, $unlinkFile = true) {

        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        if ($unlinkFile == true)
            $this->unlinkFiles($filename); // Delete The uploaded file
        return $objPHPExcel;
    }
    
     /**
     * divideXlsOrCsvInChunkFiles method    
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function divideXlsOrCsvInChunkFiles($objPHPExcel = null, $extra = null) {
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;
        $filesArray = [];
        $titleRow = [];

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g. 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            
            if ($extra['limitRows'] !== null) {
                $limitRows = $extra['limitRows'];
                $sheetCount = 1;
                if ($highestRow > ($limitRows + ($startRows - 1))) {
                    $sheetCount = ceil($highestRow - ($startRows - 1) / $limitRows);
                }
            } else {
                $limitRows = 0;
            }

            $PHPExcel = new \PHPExcel();
            $sheet = 1;

            for ($row = $startRows; $row <= $highestRow; ++$row) {

                $endrows = $limitRows + ($startRows - 1);
                $character = 'A';

                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if ($sheet > 1) {
                        $currentRow = ($row - (($sheet - 1) * $limitRows)) + 1;
                    } else {
                        $currentRow = $row - (($sheet - 1) * $limitRows);
                    }

                    if ($row == 1) {
                        $titleRow[$character . $currentRow] = $val;
                    }

                    $PHPExcel->getActiveSheet()->SetCellValue($character . $currentRow, $val);
                    $character++;
                }

                if (($row == $endrows) || ($row == $highestRow)) {
                    $PHPExcel->setActiveSheetIndex(0);
                    $objWriter = new \PHPExcel_Writer_Excel2007($PHPExcel);
                    $sheetPath = _CHUNKS_PATH . DS . time() . $sheet . '.xls';
                    $objWriter->save($sheetPath);
                    $filesArray[] = $sheetPath;
                    $PHPExcel = new \PHPExcel();
                    foreach ($titleRow as $titleRowKey => $titleRowVal) {
                        $PHPExcel->getActiveSheet()->SetCellValue($titleRowKey, $titleRowVal);
                    }
                    $startRows += $limitRows;
                    $sheet++;
                }
            }
        }

        return $filesArray;
    }
    
   

    /**
     * prepareDataFromXlsOrCsv method
     *
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $insertDataKeys Fields to insert into database. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function prepareDataFromXlsOrCsv($filename = null, $insertDataKeys = null, $extra = null) {
        $insertDataArr = [];
        $insertDataNames = [];
        $insertDataGids = [];
        $startRows = (isset($extra['startRows'])) ? $extra['startRows'] : 1;

        $objPHPExcel = $this->readXlsOrCsv($filename);

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            for ($row = $startRows; $row <= $highestRow; ++$row) {

                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);

                    if ($row >= $startRows) {  //-- Data Strats from row 2 --//                      
                        $insertDataArr[$row][] = $val;
                    } else {
                        continue;
                    }
                }
            }
        }
       
         return $this->Area->processAreadata($insertDataKeys, $insertDataArr, $extra);
    }
    
   
     /**
     * 
     * bulkUploadXlsOrCsv
     * 
     * @param string $filename bulk file
     * @param string $component Component name for bulk import
     * @param array $extraParam Any extra parameter
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function bulkUploadXlsOrCsv($params=[]) {
         
        $filename =$params['filename'];
        $objPHPExcel = $this->readXlsOrCsv($filename);
        $extra = [];
        $extra['limitRows'] = 3; // Number of rows in each file chunks
        $extra['startRows'] = 1; // Row from where the data reading starts
        $divideXlsOrCsvInChunks = $this->divideXlsOrCsvInChunkFiles($objPHPExcel, $extra);
        return $this->bulkUploadXlsOrCsvArea($divideXlsOrCsvInChunks, $extra, $objPHPExcel);
        
    }
    
    
    /**
     * bulkUploadXlsOrCsvArea method
     * @param array $filename File to load. {DEFAULT : null}
     * @param array $extra Extra Parameters to use. {DEFAULT : null}
     * @return void
     */
    public function bulkUploadXlsOrCsvArea($fileChunksArray = [], $extra = null, $xlsObject = null) {
         $importDetails=[];
        if (isset($this->processdAreaIds) && count($this->processdAreaIds)>0)
            $this->processdAreaIds=[];
     
        $component  = 'Area';
        $insertFieldsArr = [];
        $insertDataArrRows = [];
        $insertDataArrCols = [];
        // $extra['limitRows'] = 200; // Number of rows in each area chunks file 
        $extra['startRows'] = 1; // Row from where the data reading starts
        $extra['callfunction'] = $component;

        $insertDataKeys = [_INSERTKEYS_AREACODE => 'code',
            _INSERTKEYS_NAME => 'name',
            _INSERTKEYS_LEVEL => 'area_level_id',
            _INSERTKEYS_PARENTNID => 'parent_id',
        ];
        // start file validation

        $xlsObject->setActiveSheetIndex(0);
        $startRow = 1; //first row 
        $highestColumn = $xlsObject->getActiveSheet()->getHighestColumn(); // e.g. 'F'
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        // code for validation of uploaded  file 
        $highestRow = $xlsObject->getActiveSheet()->getHighestRow(); // e.g. 10   			
        if ($highestRow == 1) {
            return ['error' => _ERR145];//file is empty
        }

        $titlearray = [];  // for titles of sheet
        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $cell = $xlsObject->getActiveSheet()->getCellByColumnAndRow($col, $startRow);
            $titlearray[] = $val = $cell->getValue();
        }
        $validFormat = $this->importFormatCheck(strtolower(_MODULE_NAME_AREA));  //Check file Columns format
        $formatDiff = array_diff($validFormat, array_map('strtolower', $titlearray));
        $columSequeceStatus = false;
        if ((strtolower($titlearray[0]) == strtolower(_EXCEL_AREA_CODE)) && (strtolower($titlearray[1]) == strtolower(_EXCEL_AREA_NAME)) &&
                (strtolower($titlearray[2]) == strtolower(_EXCEL_AREA_LEVEL)) && (strtolower($titlearray[3]) == strtolower(_EXCEL_AREA_PARENTID))) {
            $columSequeceStatus = true;
        }
    
        if (!empty($formatDiff) || $columSequeceStatus == false) {
            return ['error' => _ERR146];//invalid column format 
        }
        // end of file validation 	
        foreach ($fileChunksArray as $filename) {
            $importDetails=[];
            $extra['chunkFilename'] = $filename;
            $importDetails = $this->prepareDataFromXlsOrCsv($filename, $insertDataKeys, $extra);
        }
      

        return $importDetails;
       
    }

    /**
     * function to Delete Files from the disk
     *
     * @param array $filepaths files to be deleted
     * @access public
     */
    public function unlinkFiles($filepaths = null) {
        if (is_array($filepaths)) {
            foreach ($filepaths as $filepath) {
                @unlink($filepath);
            }
        } else {
            @unlink($filepaths);
        }
    }

    /**
     * importFormatCheck
     * 
     * @param string $type Upload Type
     * 
     * @return boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function importFormatCheck($type = null) {
        if ($type == strtolower(_MODULE_NAME_AREA)) {
            return [
                'areacode',
                'areaname',
                'arealevel',
                'parent areaid'
            ];
        }
        return [];
    }
    
    
    
    /**
      method to create the custom log file and save on server
     */
    public function writeLogFile($data = '') {

       
        $logfilename = _CUSTOMLOG_FILE . date('l') . ',' . date('F-d-Y-H-i-s') . '-' . '.txt';
       
        $logfile = fopen(_LOGS_PATH . DS . $logfilename, "w") or die("Unable to open file!");
        $file = $this->prepareErrorStringData($data);

        fwrite($logfile, $file);
        fclose($logfile);
        $filepath = _LOGS_PATH . DS . $logfilename;

        return ['status' => true, 'filepath' => $filepath];
    }
    
    function prepareErrorStringData($data=[]){
        $txt='';
        $txt.="Imported Records ".$data['importedRecords'];
        $txt.="\r\n \r\n Failed Records ".$data['failedRecords'];
        $txt.="\r\n \r\n ";
        $txt.="\r\n \r\n ";
        
        $txt.="\r\n \r\n Error details  ";
        foreach($data['errordetails'] as $index=> $value){
              $txt.="\r\n \r\n Row No :".$index." Message : ".$value;            
        }
        return $txt;
    }
}
