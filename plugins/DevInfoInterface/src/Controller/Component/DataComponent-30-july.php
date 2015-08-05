<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Data Component
 */
class DataComponent extends Component {

    // The other component your component uses
    public $components = [
        'Auth',
        'UserAccess',
        'MIusValidations',
        'TransactionLogs',
        'DevInfoInterface.CommonInterface',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Timeperiod',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Footnote',
        'DevInfoInterface.Area',
    ];
    public $DataObj = NULL;
    public $IUSValidations = NULL;
    public $footnoteObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        $this->DataObj = TableRegistry::get('DevInfoInterface.Data');
        $this->FootnoteObj = TableRegistry::get('DevInfoInterface.Footnote');
    }

    

    /*
      function save data
     */

    public function saveData($dbId, $jsonData = '', $validation = true, $customLog = false, $dbLog = true) {


        $params = array('validation' => $validation, 'customLog' => $customLog, 'dbLog' => $dbLog, 'dbId'=>$dbId);
        $return = true;

        // pass json data into prepare data
        $dataArray = $this->prepareData($jsonData, $params);

        if ($dataArray) {
            foreach ($dataArray as $dataRow) {
                // apply validations
                $validation = $this->applyValidation($dataRow, $params);

                pr($dataRow);
                // if validation returns true
                if ($validation) {
                    // check for update or insert
                    $updateCheck = $this->checkUpdateOrInsert($dataRow);

                    if ($updateCheck['update'] == true) {
                        // update case
                        if(!isset($dataRow['dNid']) || empty($dataRow['dNid'])) $dataRow['dNid'] = $updateCheck['dNid'];
                        $return = $this->updateData($dataRow);
                    } else {
                        // insert case
                        $return = $this->insertData($dataRow);
                    }
                }
            }
        }

        return $return;
    }

    /*
      function to prepare data from JSON data
      @output: data array
     */

    public function prepareData($jsonData = '', $params=[]) {
        $dataArray = [];
        $iusGIds = [];

        // convert json data to array
        if (!empty($jsonData)) {
            // get IUSNId with nid, gid
            // coming

            // get indicatorList with nid, gid
            // coming

            // get unitList
            // coming

            // get Subgroup list
            // coming

            // get Time period list
            // coming

            // get source list
            // coming

            $dataArray = json_decode($jsonData, true);
            if ($dataArray) {               

                foreach ($dataArray as $index => $value) {

                    $IUSNId = $value['iusNId'];

                    $iusRec = $this->IndicatorUnitSubgroup->getIUSDetails($IUSNId); //get ius records  details 
                    $iusRec = current($iusRec);
                    $dataArray[$index]['sNid'] = $iusRec['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID];
                    $dataArray[$index]['sGid'] = $iusRec['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID];
                    $dataArray[$index]['uNid'] = $iusRec['unit'][_UNIT_UNIT_NID];
                    $dataArray[$index]['uGid'] = $iusRec['unit'][_UNIT_UNIT_GID];
                    $dataArray[$index]['iNid'] = $iusRec['indicator'][_INDICATOR_INDICATOR_NID];
                    $dataArray[$index]['iGid'] = $iusRec['indicator'][_INDICATOR_INDICATOR_GID];

                    $iusGIds[] = array('iGid'=>$dataArray[$index]['iGid'], 'uGid'=>$dataArray[$index]['uGid'], 'sGid'=>$dataArray[$index]['sGid']);
                }

                if($params['validation'] === true) {
                    
                    // get validation rules from application database
                    $this->getIUSValidationRule($iusGIds, $params['dbId']);
                }
            }

            //------------------------
            // get nids
            // coming soon
            //------------------------
        }


        return $dataArray;
    }

    /*
      function to apply validation
      @input: dataRow
      @output: boolean
    */
    public function applyValidation($dataRow = [], $params) {
        $return = true;
        $action = 'VALIDATION';

        if ($dataRow['dataValue'] == '') {
            $this->addDBLog($params['dbId'], $action, $this->getMessage(100), _FAILED, $params['dbLog']);    

            $this->customLog($params['customLog']);

            $return = false;
        }
        else {
            if ($params['validation'] == true) {
                $status = $this->checkIUSValidation($dataRow, $params['dbId']);
                
                if ($status == false) {
                    $this->addDBLog($params['dbId'], $action, $this->getMessage(101), _FAILED, $params['dbLog']);

                    $this->customLog($params['customLog']);

                    $return = false;
                }
            }            
        }       

        return $return;
    }    

    /*
      function to apply IUS validation
      @input: dataRow
      @output: boolean
     */

    public function checkIUSValidation($dataRow = [], $dbId) {

        $return = true;
        
        $iusValidation = $this->IUSValidations;

        if (empty($iusValidation)) {
            $return = true;
        } else {
            // If not textual
            if ($iusValidation['is_textual'] == 0) {
                // number check
                if (preg_match('/(\d+\.?\d*)/', $dataRow['dataValue']) != 0) {
                    $return = false;
                }                
                // Check min value
                if ($iusValidation['min_value'] != null && $dataRow['dataValue'] <= $iusValidation['min_value']) {
                    $return = false;
                }
                // Check max value
                if ($iusValidation['max_value'] != null && $dataRow['dataValue'] >= $iusValidation['max_value']) {
                    $return = false;
                }
            }
            else {
                // If Textual
                // anything
            }
        }

        return $return;
    }

    /*
      function to check update or insert case
      @input: dataRow
      @output: boolean - true for update case, false for insert case
     */

    public function checkUpdateOrInsert($dataRow = []) {
        $dNid = isset($dataRow['dNid']) ? $dataRow['dNid'] : '';
        $return = ['update'=>true, 'dNid'=>$dNid];
        
        if(empty($dNid)) {

            $fields = [_MDATA_NID];
            $conditions = [_MDATA_AREANID => $dataRow['areaNId'], _MDATA_IUSNID => $dataRow['iusNId'], _MDATA_SOURCENID => $dataRow['sourceNid'], _MDATA_TIMEPERIODNID => $dataRow['timeperiodNid']];

            $data = $this->DataObj->getDataByParams($fields, $conditions, 'all');
            $dataNId = current($data)[_MDATA_NID];

            if (!empty($dataNId)) {
                $return['dNid'] = $dataNId; //update case 
            } else {
                $return['update'] = false;
            }
        } 
        
        return $return;
    }

    /*
      function to update data
      @input: dataRow
      @output: boolean
     */

    public function updateData($dataRow = []) {
        $footnote='';
        if(!empty($dataRow['footnote'])){            
          $footnote  =$dataRow['footnote'];
          $fieldsArray[_FOOTNOTE_VAL]=$footnote;
          //$fieldsArray[_FOOTNOTE_GID]=$this->CommonInterface->u;
          $footnoteNId = $this->footnoteObj->insertData($fieldsArray);
            
        }else{
            $footnoteNId ='-1';
        }
        
        $fields = [ _MDATA_FOOTNOTENID => $footnoteNId,
            _MDATA_TIMEPERIODNID => $dataRow['timeperiodNid'],
             _MDATA_SOURCENID => $dataRow['sourceNid'],
            _MDATA_DATAVALUE => $dataRow['dataValue'],
            _MDATA_AREANID => $dataRow['areaNId'],
        ];
        
        
 
       
        $return = $this->DataObj->updateDataByParams($fields, [_MDATA_NID => $dataRow['dNid']]);
           
        
        $conditions[_MDATA_NID . ' IN '] = $dataRow['dNid'];
        $fields = [];
        $data = $this->DataObj->getDataByParams($fields, $conditions, 'all');
           
        //-- TRANSACTION Log
        if ($return)
            $LogId = $this->TransactionLogs->createLog(_UPDATE, _DATAENTRYVAL, _DATA, _MDATA_NID, _DONE);
        else
            $LogId = $this->TransactionLogs->createLog(_UPDATE, _DATAENTRYVAL, _DATA, _MDATA_NID, _FAILED);
        pr($return);
    }

    /*
      function to insert data
      @input: dataRow
      @output: boolean
     */

    public function insertData($dataRow = [], $params = []) {
        $fieldsArray = [
            _MDATA_IUSNID => $dataRow['iusNId'],
            _MDATA_TIMEPERIODNID => $dataRow['timeperiodNid'],
            _MDATA_AREANID => $dataRow['areaNId'],
            _MDATA_FOOTNOTENID => '',
            _MDATA_SOURCENID => $dataRow['sourceNid'],
            _MDATA_INDICATORNID => $dataRow['indNid'],
            _MDATA_UNITNID => $dataRow['unitNid'],
            _MDATA_SUBGRPNID => $dataRow['sNid'],
            _MDATA_IUNID => $dataRow['indNid'] . '_' . $dataRow['unitNid'],
            _MDATA_DATAVALUE => $dataRow['dataValue'],
        ];
        $dataNId = $this->DataObj->insertData($fieldsArray);
        if ($dataNId) {
            //-- TRANSACTION Log
            // remove this function and add addDBLog
            $LogId = $this->TransactionLogs->createLog(_INSERT, _DATAENTRYVAL, _DATA, $dataNId, _DONE);
        }
        else {
            // fialed
        }
    }


    /*
      function to add log data
      @input: $logdata array
      @output: boolean
    */
    public function addDBLog($dbId, $action, $desc, $status, $isDbLog=true) {
        if($isDbLog === true) {
            $fieldsArray = [
                _MTRANSACTIONLOGS_DB_ID => $dbId,
                _MTRANSACTIONLOGS_ACTION => $action,
                _MTRANSACTIONLOGS_MODULE => _MODULE_NAME_DATAENTRY,
                _MTRANSACTIONLOGS_SUBMODULE => _MODULE_NAME_DATAENTRY,
                _MTRANSACTIONLOGS_DESCRIPTION => $desc,
                _MTRANSACTIONLOGS_STATUS => $status,
            ];
            $LogId = $this->TransactionLogs->createRecord($fieldsArray);    
        }        
    }

    /*
      function to get IUS valiadtion rules
      @input: $dataArray
    */
    public function getIUSValidationRule($IUSGids=[], $dbId) {

        $fields = [
            _MIUSVALIDATION_ID,
            _MIUSVALIDATION_INDICATOR_GID,
            _MIUSVALIDATION_UNIT_GID,
            _MIUSVALIDATION_SUBGROUP_GID,
            _MIUSVALIDATION_IS_TEXTUAL,
            _MIUSVALIDATION_MIN_VALUE,
            _MIUSVALIDATION_MAX_VALUE
        ];

        $iusValidation = $this->MIusValidations->getRecords($fields, [_MIUSVALIDATION_INDICATOR_GID => $dataRow['iGid'],
            _MIUSVALIDATION_UNIT_GID => $dataRow['uGid'], _MIUSVALIDATION_SUBGROUP_GID => $dataRow['sGid'],
            _MIUSVALIDATION_DB_ID => $dbId], 'all');

        $this->IUSValidations = current($iusValidation);
        
    }







    public function getIusDataCollection($iusArray) {

        $tempDataAr = array(); // temproryly store data for all element name		
        // Get Indicator Access
        $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);

        foreach ($iusArray as $ius) {
            $iusAr = explode(_DELEM1, $ius);

            $iGid = $iusAr[0];
            $uGid = $iusAr[1];

            // Check for Indicator access
            if ($indicatorGidsAccessible !== false && !empty($indicatorGidsAccessible) && !in_array($iGid, $indicatorGidsAccessible)) {
                continue;
            }

            if (count($iusAr) == '3') {
                $sGid = $iusAr[2];
            } else {
                $sGid = '';
            }

            $data = $this->IndicatorUnitSubgroup->getIusNidsDetails($iGid, $uGid, $sGid);

            foreach ($data as $valueIus) {

                $iu = $valueIus['indicator'][_IUS_INDICATOR_NID] . '_' . $valueIus['unit'][_UNIT_UNIT_NID];
                $tempDataAr['ind'][$valueIus['indicator'][_INDICATOR_INDICATOR_NID]][0] = $iGid;
                $tempDataAr['ind'][$valueIus['indicator'][_INDICATOR_INDICATOR_NID]][1] = $valueIus['indicator'][_INDICATOR_INDICATOR_NAME];

                $tempDataAr['unit'][$valueIus['unit'][_UNIT_UNIT_NID]][0] = $uGid;
                $tempDataAr['unit'][$valueIus['unit'][_UNIT_UNIT_NID]][1] = $valueIus['unit'][_UNIT_UNIT_NAME];

                $tempDataAr['sg']['ius'][$valueIus[_IUS_IUSNID]][0] = $valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID];
                $tempDataAr['sg']['ius'][$valueIus[_IUS_IUSNID]][1] = $valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL];

                $tempDataAr['ind']['ius'][$valueIus[_IUS_IUSNID]][0] = $valueIus['indicator'][_INDICATOR_INDICATOR_GID];
                $tempDataAr['ind']['ius'][$valueIus[_IUS_IUSNID]][1] = $valueIus['indicator'][_INDICATOR_INDICATOR_NAME];

                $tempDataAr['unit']['ius'][$valueIus[_IUS_IUSNID]][0] = $valueIus['unit'][_UNIT_UNIT_GID];
                $tempDataAr['unit']['ius'][$valueIus[_IUS_IUSNID]][1] = $valueIus['unit'][_UNIT_UNIT_NAME];

                $tempDataAr['IUNid']['ius'][$valueIus[_IUS_IUSNID]] = 'IU_' . $iu;
                if ($sGid != '') {

                    $tempDataAr['sg'][$valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID]][0] = $sGid;
                    $tempDataAr['sg'][$valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID]][1] = $valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL];
                } else {

                    $tempDataAr['sg'][$valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID]][0] = $valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID];
                    $tempDataAr['sg'][$valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID]][1] = $valueIus['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL];
                }

                $tempDataAr['iusnids'][] = $valueIus[_IUS_IUSNID];
            }
        }
        return $tempDataAr;
    }

    
    /*
      function to get data replace to getDEsearchData 
     */

    public function getData($fields = [], $conditions = [], $extra = []) {
        
    }

    /**
     * getDEsearchData to get the details of search on basis of IUSNid,
     * @parameters passed in conditions will be areanid , TimeperiodNid ,IUSNid
     * $iusgids will be passed as array in extra 
     * returns data value with source
     * @access public
     */
    public function getDEsearchData($fields = [], $conditions = [], $extra = []) {

        $iusnidData = [];
        $returnediusNids = [];
        $iusNids = $this->getIusDataCollection($extra);

        if (!empty($iusNids['iusnids']))
            $returnediusNids = $iusNids['iusnids']; //iusnids 

        if (!empty($returnediusNids)) {

            //$returnediusNids= [2398,2660,23930];
            // getting all classifications 
            $fields1 = [_IC_IC_NAME, _IC_IC_GID, _IC_IC_NID, _IC_IC_TYPE];
            $conditions1 = [];
            $sourceList = $this->IndicatorClassifications->getDataByParams($fields1, $conditions1, 'all');

            // structuring classification for name and gid
            $classificationArray = array();
            foreach ($sourceList as $index => $value) {
                $classificationArray[$value[_IC_IC_NID]]['IC_GId'] = $value[_IC_IC_GID];
                $classificationArray[$value[_IC_IC_NID]]['IC_Name'] = $value[_IC_IC_NAME];
                $classificationArray[$value[_IC_IC_NID]]['IC_Type'] = $value[_IC_IC_TYPE];
            }

            // getting all timperiod list 
            $fields2 = [_TIMEPERIOD_TIMEPERIOD_NID, _TIMEPERIOD_TIMEPERIOD];
            $conditions2 = [];
            $timeperiodList = $this->Timeperiod->getDataByParams($fields2, $conditions2, 'list');

            // getting all footnote list  
            $footnoteList = $this->FootnoteObj->find('all')->combine(_FOOTNOTE_NId, _FOOTNOTE_VAL)->toArray();

            $conditions[_MDATA_IUSNID . ' IN '] = $returnediusNids;
            $fields = [];
            $data = $this->DataObj->getDataByParams($fields, $conditions, 'all');

            $alldataIusnids = []; // store all iusnids from data table

            $iusnidData = [];

            foreach ($data as $index => $value) {

                $IUNId = 'IU_' . $value['IUNId'];
                $iusnid = $value[_MDATA_IUSNID];

                $iusnidData[$IUNId][$iusnid]['dNid'] = $value[_MDATA_NID];
                $iusnidData[$IUNId][$iusnid]['tp'] = $value[_MDATA_TIMEPERIODNID];
                $iusnidData[$IUNId][$iusnid]['dv'] = $value[_MDATA_DATAVALUE];
                $iusnidData[$IUNId][$iusnid]['src'] = $value[_MDATA_SOURCENID];
                $iusnidData[$IUNId][$iusnid]['sGid'] = $iusNids['sg'][$value[_MDATA_SUBGRPNID]][0]; //sbgrp gid 
                $iusnidData[$IUNId][$iusnid]['sName'] = $iusNids['sg'][$value[_MDATA_SUBGRPNID]][1]; //sbgrp  name 
                $iusnidData[$IUNId][$iusnid]['footnote'] = (!empty($value[_MDATA_FOOTNOTENID])) ? $footnoteList[$value[_MDATA_FOOTNOTENID]] : '';
                $iusnidData[$IUNId][$iusnid]['iusnid'] = $value[_MDATA_IUSNID];
                $alldataIusnids[] = $value[_MDATA_IUSNID];
                $alldataIndicators[$value[_MDATA_IUSNID]] = $value[_MDATA_INDICATORNID]; // storing ind index w.r.t iusnids
                $alldataUnits[$value[_MDATA_IUSNID]] = $value[_MDATA_UNITNID]; // storing unit index w.r.t iusnids
            }

            $finalArray = [];
            $iusValidationsArray = [];

            foreach ($returnediusNids as $index => $iusnidvalue) {
                // first classification         
                if (in_array($iusnidvalue, $alldataIusnids) == true) {
                    $prepareIU = 'IU_' . $alldataIndicators[$iusnidvalue] . '_' . $alldataUnits[$iusnidvalue]; // using IU index for array 
                } else {

                    $prepareIU = $iusNids['IUNid']['ius'][$iusnidvalue]; //get from array 
                }
                //$prepareIU = 'IU_' .$iusnidData['IUNid'];
                //$finalArray[$icnid]['icName'] = $classificationArray[$icnid]['IC_Name'];
                // $finalArray[$icnid]['iGid'] = $classificationArray[$icnid]['IC_GId'];
                $finalArray[$prepareIU]['iName'] = $iusNids['ind']['ius'][$iusnidvalue][1]; //name 
                $finalArray[$prepareIU]['iGid'] = $iusNids['ind']['ius'][$iusnidvalue][0];
                $finalArray[$prepareIU]['uName'] = $iusNids['unit']['ius'][$iusnidvalue][1];
                $finalArray[$prepareIU]['uGid'] = $iusNids['unit']['ius'][$iusnidvalue][0];

                $iusValidationsArray[] = [
                    _MIUSVALIDATION_INDICATOR_GID => $iusNids['ind']['ius'][$iusnidvalue][0],
                    _MIUSVALIDATION_UNIT_GID => $iusNids['unit']['ius'][$iusnidvalue][0],
                    _MIUSVALIDATION_SUBGROUP_GID => $iusNids['sg']['ius'][$iusnidvalue][0],
                ];

                if (in_array($iusnidvalue, $alldataIusnids) == true) {
                    $finalArray[$prepareIU]['subgrps'][] = $iusnidData[$prepareIU][$iusnidvalue];
                } else {
                    $finalArray[$prepareIU]['subgrps'][] = ['dNid' => '', 'sName' => $iusNids['sg']['ius'][$iusnidvalue][1], 'sGid' => $iusNids['sg']['ius'][$iusnidvalue][0],
                        'iusnid' => $iusnidvalue, 'dv' => '', 'tp' => '', 'src' => '', 'footnote' => ''];
                }
            }
            $finalArray = array_values($finalArray);
            $return['iu'] = $finalArray;
            $return['iusValidations'] = $iusValidationsArray;
            return $return;
        }
    }

    

    public function saveDataEntry($dataDetails = [], $extra = []) {
        //$dataDetailsArray = json_decode($dataDetails, true);
        $dataDetailsArray = array_values($dataDetails);

        // Get Indicator Access
        $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);

        //-- Footnote
        $footnotes = array_column($dataDetailsArray, 'footnote');
        $extra = ['fields' => [_FOOTNOTE_NId, _FOOTNOTE_VAL], 'type' => 'list'];
        $footnoteRec = $this->Footnote->saveAndGetFootnoteRec($footnotes, $extra);

        //-- Area
        $areaIds = array_column($dataDetailsArray, 'areaId');
        $fields = [_AREA_AREA_NID, _AREA_AREA_ID];
        $conditions = [_AREA_AREA_ID . ' IN' => $areaIds];
        $areaRec = $this->Area->getDataByParams($fields, $conditions, 'list');

        //-- Data
        $dNids = array_column($dataDetailsArray, 'dNid');
        $dataDetailsUpdate = array_intersect_key($dataDetailsArray, array_filter($dNids));
        $dataDetailsInsert = array_diff_key($dataDetailsArray, array_filter($dNids));

        //-- IUSNId
        $IUSNIds = array_column($dataDetailsArray, 'iusId');
        $fields = [_IUS_IUSNID, _IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID];
        $conditions = [_IUS_IUSNID . ' IN' => $IUSNIds];
        $iusRec = $this->IndicatorUnitSubgroup->getDataByParams($fields, $conditions, 'all');
        $IUSNIdsList = array_column($iusRec, _IUS_IUSNID);
        $iusRec = array_combine($IUSNIdsList, $iusRec);

        //-- iGid, uGid, sGid
        foreach ($dataDetailsArray as $dataDetails) {
            // Check for Indicator access
            if ($indicatorGidsAccessible !== false && !empty($indicatorGidsAccessible) && !in_array($dataDetails['iGid'], $indicatorGidsAccessible)) {
                continue;
            }
            $iusGids[] = [
                _MIUSVALIDATION_INDICATOR_GID => $dataDetails['iGid'],
                _MIUSVALIDATION_UNIT_GID => $dataDetails['uGid'],
                _MIUSVALIDATION_SUBGROUP_GID => $dataDetails['sGid']
            ];
        }
        $fields = [
            _MIUSVALIDATION_ID,
            _MIUSVALIDATION_INDICATOR_GID,
            _MIUSVALIDATION_UNIT_GID,
            _MIUSVALIDATION_SUBGROUP_GID,
            _MIUSVALIDATION_IS_TEXTUAL,
            _MIUSVALIDATION_MIN_VALUE,
            _MIUSVALIDATION_MAX_VALUE
        ];
        $iusValidations = $this->MIusValidations->getRecords($fields, ['OR' => $iusGids], 'all');

        $iGids = $uGids = $sGids = [];
        if (!empty($iusValidations)) {
            $ids = array_column($iusValidations, _MIUSVALIDATION_ID);
            $iGids = array_column($iusValidations, _MIUSVALIDATION_INDICATOR_GID, _MIUSVALIDATION_ID);
            $uGids = array_column($iusValidations, _MIUSVALIDATION_UNIT_GID, _MIUSVALIDATION_ID);
            $sGids = array_column($iusValidations, _MIUSVALIDATION_SUBGROUP_GID, _MIUSVALIDATION_ID);
        }

        foreach ($dataDetailsArray as $key => $dataDetails) {

            $iGidsIntersect = array_intersect($iGids, [$dataDetails['iGid']]);
            $uGidsIntersect = array_intersect($uGids, [$dataDetails['uGid']]);
            $sGidsIntersect = array_intersect($sGids, [$dataDetails['sGid']]);

            //---- IUS Validation starts
            $iusValidationFound = array_intersect_key($iGidsIntersect, $uGidsIntersect, $sGidsIntersect);
            if (!empty($iusValidationFound)) {
                $iusValidationFound = array_keys($iusValidationFound);
                $validation = $iusValidations[array_search($iusValidationFound[0], $ids)];

                // Check if is_textual applied
                if ($validation['is_textual'] == 0) {
                    if (preg_match('/(\d+\.?\d*)/', $dataDetails['dataValue']) == 0) {
                        continue;
                    }
                }// is_textual not applied
                else {
                    // Check min-value condition
                    if ($validation['min_value'] != null && $dataDetails['dataValue'] < $validation['min_value']) {
                        continue;
                    }
                    // Check max-value condition
                    if ($validation['max_value'] != null && $dataDetails['dataValue'] > $validation['max_value']) {
                        continue;
                    }
                }
            }
            //---- IUS Validation ends
            // Insert Data Rows
            if (array_key_exists($key, $dataDetailsInsert)) {
                $footnote = ($dataDetailsInsert[$key]['footnote'] == '') ? '-1' : array_search($dataDetailsInsert[$key]['footnote'], $footnoteRec);
                $fieldsArray = [
                    _MDATA_IUSNID => $dataDetailsInsert[$key]['iusId'],
                    _MDATA_TIMEPERIODNID => $dataDetailsInsert[$key]['timeperiod'],
                    _MDATA_AREANID => array_search($dataDetailsInsert[$key]['areaId'], $areaRec),
                    _MDATA_FOOTNOTENID => $footnote,
                    _MDATA_SOURCENID => $dataDetailsInsert[$key]['source'],
                    _MDATA_INDICATORNID => $iusRec[$dataDetailsInsert[$key]['iusId']][_IUS_INDICATOR_NID],
                    _MDATA_UNITNID => $iusRec[$dataDetailsInsert[$key]['iusId']][_IUS_UNIT_NID],
                    _MDATA_SUBGRPNID => $iusRec[$dataDetailsInsert[$key]['iusId']][_IUS_SUBGROUP_VAL_NID],
                    _MDATA_IUNID => $iusRec[$dataDetailsInsert[$key]['iusId']][_IUS_INDICATOR_NID] . '_' . $iusRec[$dataDetailsInsert[$key]['iusId']][_IUS_UNIT_NID],
                    _MDATA_DATAVALUE => $dataDetailsInsert[$key]['dataValue'],
                ];
                $dataNId = $this->DataObj->insertData($fieldsArray);

                if ($dataNId) {

                    //-- TRANSACTION Log
                    $LogId = $this->TransactionLogs->createLog(_INSERT, _DATAENTRYVAL, _DATA, $dataNId, _DONE);
                }
            }// Update Data Rows
            else if (array_key_exists($key, $dataDetailsUpdate)) {
                $footnote = ($dataDetailsUpdate[$key]['footnote'] == '') ? '-1' : array_search($dataDetailsUpdate[$key]['footnote'], $footnoteRec);
                $fields = [
                    _MDATA_FOOTNOTENID => $footnote,
                    _MDATA_SOURCENID => $dataDetailsUpdate[$key]['source'],
                    _MDATA_DATAVALUE => $dataDetailsUpdate[$key]['dataValue'],
                ];
                $this->DataObj->updateDataByParams($fields, [_MDATA_NID => $dataDetailsUpdate[$key]['dNid']]);
                //-- TRANSACTION Log
                $LogId = $this->TransactionLogs->createLog(_UPDATE, _DATAENTRYVAL, _DATA, _MDATA_NID, _DONE);
            }
        }

        return true;
    }


    /*
      function to apply validation
      @input: dataRow
      @output: boolean
    */
    public function getMessage($case) {
        $msg = '';
        switch($case) {
            case "100":
                $msg = 'Data value is empty';
            break;
            case "101":
                $msg = 'Failed IUS validation';
            break;
        }

        return $msg;
    }

}
