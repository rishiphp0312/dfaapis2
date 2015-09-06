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
        'DevInfoInterface.Indicator',
        'DevInfoInterface.Unit',
        'DevInfoInterface.SubgroupVals',
        'Common'
    ];
    public $DataObj = NULL;
    public $IUSValidations = NULL;
    public $customLogDetails = [];
    public $footnoteObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);

        $this->session = $this->request->session();
        $this->DataObj = TableRegistry::get('DevInfoInterface.Data');
        $this->FootnoteObj = TableRegistry::get('DevInfoInterface.Footnote');
    }

    /**
     * deleteRecords method
     *
     * @param array $conditions  to delete . {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        return $this->DataObj->deleteRecords($conditions);
    }

    /**
     * getRecords method for Areas
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->DataObj->getRecords($fields, $conditions, $type, $extra);
    }

    /*
      function save data
     */

    public function saveData($dbId, $jsonData = '', $validation = true, $customLog = false, $dbLog = true) {
        $this->customLogDetails = [];
        $this->customLogDetails['startTime'] = date('Y-m-d H:i:s');

        $params = array('validation' => $validation, 'isCustomLog' => $customLog, 'isDbLog' => $dbLog, 'dbId' => $dbId);

        $return = true;

        // pass json data into prepare data
        $dataArray = $this->prepareData($jsonData, $params);
       
        if ($dataArray) {
            $cnt = 1;
            foreach ($dataArray as $dataRow) {

                $params['rowCounter'] = $cnt;
                // apply validations
                $validation = $this->applyValidation($dataRow, $params);

                // if validation returns true
                if ($validation) {
                    // check for update or insert
                    $updateCheck = $this->checkUpdateOrInsert($dataRow);

                    if ($updateCheck['update'] == true) {
                        // update case
                        if (!isset($dataRow['dNid']) || empty($dataRow['dNid']))
                            $dataRow['dNid'] = $updateCheck['dNid'];
                        $return = $this->updateData($dataRow, $params);
                    } else {
                        // insert case
                        $return = $this->insertData($dataRow, $params);
                    }
                }

                $cnt++;
            }
        }
        $this->customLogDetails['endTime'] = date('Y-m-d H:i:s');

        return ['status' => true, 'customLogJson' => $this->customLogDetails];
    }

    /**
     * process source for DES
     * 
     * @param array $source source name
     * @param array $existingSources existing source list
     */
    public function processSource($source, $existingSources) {
        $srcNid = '';

        if (in_array($source, $existingSources)) {
            $srcNid = array_search($source, $existingSources);
        } else {
            $explodedSrc = explode('_', $source);
            if (count($explodedSrc) >= 3) {
                if (count($explodedSrc) == 3) {
                    $publisher = $explodedSrc[0];
                    $title = $explodedSrc[1];
                    $year = $explodedSrc[2];
                } else {
                    $publisher = $explodedSrc[0];
                    $year = $explodedSrc[count($explodedSrc) - 1];
                    unset($explodedSrc[0]);
                    unset($explodedSrc[count($explodedSrc) - 1]);
                    $title = implode('_', $explodedSrc);
                }
                $srcNid = $this->IndicatorClassifications->manageSource(['publisher' => $publisher, 'title' => $title, 'year' => $year, 'shortName' => null], $getSourceNid = true);
                if (isset($srcNid) && !empty($srcNid))
                    $existingSources[$srcNid] = $source;
            }
        }

        return ['srcNid' => $srcNid, 'existingSources' => $existingSources];
    }

    /**
     * get Data table associations record for DES
     * 
     * @param array $dataArray Data arra from DES
     */
    public function getDataAssociationsRecords($dataArray, $extra = []) {
        //tp, source, footnote
        $tp = $this->Timeperiod->getRecords([_TIMEPERIOD_TIMEPERIOD_NID, _TIMEPERIOD_TIMEPERIOD], [], 'list');
        $src = $this->IndicatorClassifications->getSource([_IC_IC_NID, _IC_IC_NAME], [], 'list');
        $footnote = $this->Footnote->getRecords([_FOOTNOTE_NId, _FOOTNOTE_VAL], [], 'list');

        if (isset($extra['getAllAreas']) && $extra['getAllAreas'] == true) {
            $area = $this->Area->getRecords([_AREA_AREA_NID, _AREA_AREA_ID, _AREA_AREA_NAME], [], 'all');
            $areaNameWithNid = array_column($area, _AREA_AREA_NAME, _AREA_AREA_NID);
            $areaIdWithNid = array_column($area, _AREA_AREA_ID, _AREA_AREA_NID);
            $area = ['areaNameWithNid' => $areaNameWithNid, 'areaIdWithNid' => $areaIdWithNid];
        } else {
            // area
            $areaIds = array_column($dataArray, 'aId');
            $area = $this->Area->getNidsFromIds([_AREA_AREA_NID, _AREA_AREA_ID], $areaIds, 'list');
        }

        // indicator
        $indicator = $this->Indicator->getRecords([_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_GID], [], 'all');
        $indicatorNameWithNid = array_column($indicator, _INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_NID);
        $indicatorGidWithNid = array_column($indicator, _INDICATOR_INDICATOR_GID, _INDICATOR_INDICATOR_NID);

        // Unit
        $unit = $this->Unit->getRecords([_UNIT_UNIT_NID, _UNIT_UNIT_NAME, _UNIT_UNIT_GID], [], 'all');
        $unitNameWithNid = array_column($unit, _UNIT_UNIT_NAME, _UNIT_UNIT_NID);
        $unitGidWithNid = array_column($unit, _UNIT_UNIT_GID, _UNIT_UNIT_NID);

        // Subgroup
        $subgroup = $this->SubgroupVals->getRecords([_SUBGROUP_VAL_SUBGROUP_VAL_NID, _SUBGROUP_VAL_SUBGROUP_VAL, _SUBGROUP_VAL_SUBGROUP_VAL_GID], [], 'all');
        $subgroupNameWithNid = array_column($subgroup, _SUBGROUP_VAL_SUBGROUP_VAL, _SUBGROUP_VAL_SUBGROUP_VAL_NID);
        $subgroupGidWithNid = array_column($subgroup, _SUBGROUP_VAL_SUBGROUP_VAL_GID, _SUBGROUP_VAL_SUBGROUP_VAL_NID);

        return [
            'tp' => $tp,
            'src' => $src,
            'footnote' => $footnote,
            'area' => $area,
            'indicatorNameWithNid' => $indicatorNameWithNid,
            'indicatorGidWithNid' => $indicatorGidWithNid,
            'unitNameWithNid' => $unitNameWithNid,
            'unitGidWithNid' => $unitGidWithNid,
            'subgroupNameWithNid' => $subgroupNameWithNid,
            'subgroupGidWithNid' => $subgroupGidWithNid,
        ];
    }

    /*
      function to prepare data from JSON data
      @output: data array
     */

    public function prepareData($jsonData = '', $params = []) {

        $dataArray = [];
        $iusGIds = [];

        // convert json data to array
        if (!empty($jsonData)) {
            $dataArray = json_decode($jsonData, true);

            $areaAccess = $this->UserAccess->getAreaAccessToUser(['type' => 'list']);
            $areaAccessIds = array_keys($areaAccess);

            $dataAssociations = $this->getDataAssociationsRecords($dataArray);
            extract($dataAssociations);

            foreach ($dataArray as $key => $value) {

                $this->cntAllRec++;    //total no of records 

                if (empty($value['iusNid'])) {

                    $iNid = $uNid = $sNid = $iusNid = $tpNid = $aNid = $iGid = $uGid = $sGid = $srcNid = '';

                    if (isset($value['iGid']) && !empty($value['iGid'])) {
                        if (in_array($value['iGid'], $indicatorGidWithNid)) {
                            $iNid = array_search($value['iGid'], $indicatorGidWithNid);
                            $iGid = $indicatorGidWithNid[$iNid];
                        }
                    } else if (isset($value['iName']) && !empty($value['iName'])) {
                        if (in_array($value['iName'], $indicatorNameWithNid)) {
                            $iNid = array_search($value['iName'], $indicatorNameWithNid);
                            $iGid = $indicatorGidWithNid[$iNid];
                        }
                    }

                    if (isset($value['uGid']) && !empty($value['uGid'])) {
                        if (in_array($value['uGid'], $unitGidWithNid)) {
                            $uNid = array_search($value['uGid'], $unitGidWithNid);
                            $uGid = $unitGidWithNid[$uNid];
                        }
                    } else if (isset($value['uName']) && !empty($value['uName'])) {
                        if (in_array($value['uName'], $unitNameWithNid)) {
                            $uNid = array_search($value['uName'], $unitNameWithNid);
                            $uGid = $unitGidWithNid[$uNid];
                        }
                    }

                    if (isset($value['sGid']) && !empty($value['sGid'])) {
                        if (in_array($value['sGid'], $subgroupGidWithNid)) {
                            $sNid = array_search($value['sGid'], $subgroupGidWithNid);
                            $sGid = $subgroupGidWithNid[$sNid];
                        }
                    } else if (isset($value['sName']) && !empty($value['sName'])) {
                        if (in_array($value['sName'], $subgroupNameWithNid)) {
                            $sNid = array_search($value['sName'], $subgroupNameWithNid);
                            $sGid = $subgroupGidWithNid[$sNid];
                        }
                    }

                    if (!empty($iNid) && !empty($uNid) && !empty($sNid)) {
                        $iusResult = $this->IndicatorUnitSubgroup->getRecords([_IUS_IUSNID], [_IUS_INDICATOR_NID => $iNid, _IUS_UNIT_NID => $uNid, _IUS_SUBGROUP_VAL_NID => $sNid], 'all');
                        if (!empty($iusResult)) {
                            $iusNid = reset($iusResult)[_IUS_IUSNID];
                        }
                    }

                    // Area
                    if (isset($value['aNid']) && !empty($value['aNid'])) {
                        $aNid = $value['aNid'];
                    } else if (isset($value['aId']) && !empty($value['aId'])) {
                        if (in_array($value['aId'], $area)) {
                            if (!empty($areaAccessIds) && !in_array($value['aId'], $areaAccessIds)) {
                                $aNid = _NO_AREA_ACCESS;
                            } else {
                                $aNid = array_search($value['aId'], $area);
                            }
                        }
                    }

                    // Time-period
                    if (isset($value['tpNid']) && !empty($value['tpNid'])) {
                        $tpNid = $value['tpNid'];
                    } else if (isset($value['tp']) && !empty($value['tp'])) {
                        if (in_array($value['tp'], $tp)) {
                            $tpNid = array_search($value['tp'], $tp);
                        } else {
                            $tpResult = $this->Timeperiod->insertRecords([_TIMEPERIOD_TIMEPERIOD => $value['tp']]);
                            $tpNid = $tpResult['id'];
                            $tp[$tpNid] = $value['tp'];
                        }
                    }

                    // Source
                    if (isset($value['srcNid']) && !empty($value['srcNid'])) {
                        $srcNid = $value['srcNid'];
                    } else if (isset($value['src']) && !empty($value['src'])) {
                        $result = $this->processSource($value['src'], $src);                        
                        extract($result);
                    }

                    // Unset to replace the prepared data in same key
                    unset($dataArray[$key]);

                    $dataArray[$key] = [
                        'dNid' => '',
                        'iusNid' => $iusNid,
                        'dv' => $value['dv'],
                        'srcNid' => $srcNid,
                        'footnote' => $value['footnote'],
                        'tpNid' => $tpNid,
                        'aNid' => $aNid,
                        'iNid' => $iNid,
                        'uNid' => $uNid,
                        'sNid' => $sNid,
                        'iGid' => $iGid,
                        'uGid' => $uGid,
                        'sGid' => $sGid,
                        'DESInfo' => [
                            'sheetName' => $value['DESInfo']['sheetName'],
                            'rowNo' => $value['DESInfo']['rowNo']
                        ],
                    ];

                    if (!empty($iGid) && !empty($uGid) && !empty($sGid)) {
                        $iusGIds['iGid'][] = $iGid;
                        $iusGIds['uGid'][] = $uGid;
                        $iusGIds['sGid'][] = $sGid;
                    }
                } else {

                    $IUSNId = $value['iusNid'];

                    $iusRec = $this->IndicatorUnitSubgroup->getIUSDetails($IUSNId); //get ius records  details 
                    $iusRec = current($iusRec);
                    $dataArray[$key]['sNid'] = $iusRec['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID];
                    $dataArray[$key]['sGid'] = $iusRec['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID];
                    $dataArray[$key]['uNid'] = $iusRec['unit'][_UNIT_UNIT_NID];
                    $dataArray[$key]['uGid'] = $iusRec['unit'][_UNIT_UNIT_GID];
                    $dataArray[$key]['iNid'] = $iusRec['indicator'][_INDICATOR_INDICATOR_NID];
                    $dataArray[$key]['iGid'] = $iusRec['indicator'][_INDICATOR_INDICATOR_GID];
                    $iusGIds['iGid'][] = $dataArray[$key]['iGid'];
                    $iusGIds['uGid'][] = $dataArray[$key]['uGid'];
                    $iusGIds['sGid'][] = $dataArray[$key]['sGid'];
                }
            }
            //pr($dataArray);die;

            if ($params['validation'] === true) {
                // get validation rules from application database
                $this->getIUSValidationRule($iusGIds, $params['dbId']);
            }
        }

        return $dataArray;
    }

    /*
      function to prepare data from JSON data
      @output: data array
     */

    public function prepareData_old($jsonData = '', $params = []) {

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
                    $this->cntAllRec++;    //total no of records 
                    $IUSNId = $value['iusNid'];

                    $iusRec = $this->IndicatorUnitSubgroup->getIUSDetails($IUSNId); //get ius records  details 
                    $iusRec = current($iusRec);
                    $dataArray[$index]['sNid'] = $iusRec['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID];
                    $dataArray[$index]['sGid'] = $iusRec['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID];
                    $dataArray[$index]['uNid'] = $iusRec['unit'][_UNIT_UNIT_NID];
                    $dataArray[$index]['uGid'] = $iusRec['unit'][_UNIT_UNIT_GID];
                    $dataArray[$index]['iNid'] = $iusRec['indicator'][_INDICATOR_INDICATOR_NID];
                    $dataArray[$index]['iGid'] = $iusRec['indicator'][_INDICATOR_INDICATOR_GID];
                    $iusGIds['iGid'][] = $dataArray[$index]['iGid'];
                    $iusGIds['uGid'][] = $dataArray[$index]['uGid'];
                    $iusGIds['sGid'][] = $dataArray[$index]['sGid'];

                    //$iusGIds[] = array('iGid' => $dataArray[$index]['iGid'], 'uGid' => $dataArray[$index]['uGid'], 'sGid' => $dataArray[$index]['sGid']);
                }


                if ($params['validation'] === true) {

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
        $emptyStatus = $this->checkEmpty($dataRow, $params);

        //$logdata[] = ['rowValue' => $dataRow, 'error' => ''];
        if ($emptyStatus == true) {

            if ($params['validation'] == true) {
                $status = $this->checkIUSValidation($dataRow, $params['dbId']);

                if ($status == false) {

                    // call db log
                    $this->addDBLog($params['dbId'], _ACTION_VALIDATION, $this->getMessage(101), _FAILED, $dataRow['dNid'], '', $dataRow['dv'], $params['isDbLog']);
                    // call custom log
                    $this->customLog($params['dbId'], false, $dataRow, $params, 101, $params['isCustomLog']);

                    $return = false;
                }
            }
        } else {
            $return = false;
        }

        return $return;
    }

    /*
      function to check desInfo
      @input: dataRow
      @output: params
     */

    public function checkGetRowLogInfo($dataRow = [], $params, $msg = '') {
        $rowLog = [];
        $sheetName = $rowCount = '';

        if ($dataRow) {
            if (isset($dataRow['DESInfo'])) {
                $sheetName = $dataRow['DESInfo']['sheetName'];
                $rowCount = $dataRow['DESInfo']['rowNo'];
            } else {
                $rowCount = $params['rowCounter'];
            }

            $rowLog['sheetName'] = $sheetName;
            $rowLog['rowNo'] = $rowCount;
            $rowLog['msg'] = $msg;
        }

        return $rowLog;
    }

    /*
      function to checkEmpty
      @input: dataRow,params
      @output: boolean
     */

    function checkEmpty($dataRow = [], $params) {
        $return = true;
        $msgCode = '';
        if ($dataRow['dv'] == '') {

            $return = false;
            $msgCode = 100; //  _ERR_DATAVAL_EMPTY;
        } elseif ($dataRow['tpNid'] == '') {

            $return = false;
            $msgCode = 104; //_ERR_TIME_PERIOD_EMPTY;
        } elseif ($dataRow['iusNid'] == '') {

            $return = false;
            $msgCode = 105; //_ERR_IUS_NId_EMPTY;  
        } elseif ($dataRow['aNid'] == '') {

            $return = false;
            $msgCode = 106; //_ERR_AREAID_EMPTY;_NO_AREA_ACCESS
        } elseif ($dataRow['aNid'] == _NO_AREA_ACCESS) {

            $return = false;
            $msgCode = 108; //area is not  allowed 
        } elseif ($dataRow['srcNid'] == '') {

            $return = false;
            $msgCode = 107; //_ERR_SOURCENID_EMPTY;  
        }
        if ($return == false) {

            $msg = $this->getMessage($msgCode);
            $this->addDBLog($params['dbId'], _ACTION_VALIDATION, $msg, _FAILED, '', '', '', $params['isDbLog']);

            // call custom log
            $this->customLog($params['dbId'], false, $dataRow, $params, $msgCode, $params['isCustomLog']);
        }
        return $return;
    }

    /*
      function to apply IUS validation
      @input: dataRow,dbId
      @output: boolean
     */

    public function checkIUSValidation($dataRow = [], $dbId) {

        $return = true;
        $iusValidation = $this->IUSValidations;
        if (!empty($iusValidation)) {
            $ids = array_column($iusValidation, _MIUSVALIDATION_ID);
            $iGids = array_column($iusValidation, _MIUSVALIDATION_INDICATOR_GID, _MIUSVALIDATION_ID);
            $uGids = array_column($iusValidation, _MIUSVALIDATION_UNIT_GID, _MIUSVALIDATION_ID);
            $sGids = array_column($iusValidation, _MIUSVALIDATION_SUBGROUP_GID, _MIUSVALIDATION_ID);
            //$iGids
            $iGidsIntersect = array_intersect($iGids, [$dataRow['iGid']]);
            $uGidsIntersect = array_intersect($uGids, [$dataRow['uGid']]);
            $sGidsIntersect = array_intersect($sGids, [$dataRow['sGid']]);
            $iusValidationFound = array_intersect_key($iGidsIntersect, $uGidsIntersect, $sGidsIntersect);
            if (!empty($iusValidationFound)) {
                $iusValidationFound = array_keys($iusValidationFound);
                $validation = $iusValidation[array_search($iusValidationFound[0], $ids)];
            }
        }
        //---- IUS Validation starts

        if (empty($validation)) {
            $return = true;
        } else {
            // If not textual
            if ($validation['is_textual'] == 0) {
                // number check
                if (preg_match('/(\d+\.?\d*)/', $dataRow['dv']) == 0) { //checks not numeric 
                    $return = false;
                }
                // Check min value
                if ($validation['min_value'] != null && ($dataRow['dv'] < $validation['min_value'])) {
                    $return = false;
                }
                // Check max value
                if ($validation['max_value'] != null && ($dataRow['dv'] > $validation['max_value'])) {
                    $return = false;
                }
            } else {
                // If Textual
                // anything
            }
        }
        //return ['status'=>$return,'is_textual'=>$validation['is_textual'];
        return $return;
    }

    /*
      function to check update or insert case
      @input: dataRow
      @output: boolean - true for update case, false for insert case
     */

    public function checkUpdateOrInsert($dataRow = []) {
        $dNid = isset($dataRow['dNid']) ? $dataRow['dNid'] : '';
        $return = ['update' => true, 'dNid' => $dNid];

        if (empty($dNid)) {

            $fields = [_MDATA_NID];
            $conditions = [_MDATA_AREANID => $dataRow['aNid'], _MDATA_IUSNID => $dataRow['iusNid'], _MDATA_SOURCENID => $dataRow['srcNid'], _MDATA_TIMEPERIODNID => $dataRow['tpNid']];

            $data = $this->DataObj->getRecords($fields, $conditions, 'all');
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
      @input: dataRow,params
      @output: boolean
     */

    public function updateData($dataRow = [], $params = []) {
        $numericstatus = false;
        $footnoteNId = $this->saveFootnote($dataRow['footnote']);
        $customLogStatus = true;
        $fields1 = [_MDATA_DATAVALUE];
        $conditions1 = [_MDATA_NID => $dataRow['dNid']];
        $data = $this->DataObj->getRecords($fields1, $conditions1, 'all');

        $prevvalue = current($data)[_MDATA_DATAVALUE];
        $numericstatus = $this->CommonInterface->checkNumeric(trim($dataRow['dv']));
        $fields = [_MDATA_FOOTNOTENID => $footnoteNId,
            _MDATA_TIMEPERIODNID => $dataRow['tpNid'],
            _MDATA_SOURCENID => $dataRow['srcNid'],
            _MDATA_AREANID => $dataRow['aNid'],
        ];
        if ($numericstatus == true) {
            $additionaldata = [_MDATA_DATAVALUE => $dataRow['dv'], _MDATA_ISTEXT_DATA => 0];
            $fields = array_merge($fields, $additionaldata);
        } else {
            $additionaldata = [_MDATA_TEXTUAL_DATA_VALUE => $dataRow['dv'], _MDATA_ISTEXT_DATA => 1];
            $fields = array_merge($fields, $additionaldata);
        }


        $return = $this->DataObj->updateRecords($fields, [_MDATA_NID => $dataRow['dNid']]);

        //-- TRANSACTION Log

        $newvalue = $dataRow['dv'];
        $msgCode = '';
        if ($return) {
            $this->addDBLog($params['dbId'], _UPDATE, '', _DONE, $dataRow['dNid'], $prevvalue, $newvalue, $params['isDbLog']);
        } else {
            $msgCode = 103;
            $this->addDBLog($params['dbId'], _UPDATE, $this->getMessage(103), _FAILED, $dataRow['dNid'], $prevvalue, $newvalue, $params['isDbLog']);

            $customLogStatus = false;
        }

        // call custom log
        $this->customLog($params['dbId'], $customLogStatus, $dataRow, $params, $msgCode, $params['isCustomLog']);
    }

    /*
      function to insert footnote
      @input: footNoteValue
      @output: boolean
     */

    public function saveFootnote($footNoteValue = '') {
        $footnote = '';
        if (!empty($footNoteValue)) {
            $footnotes = [$footNoteValue];
            $extra = ['fields' => [_FOOTNOTE_NId, _FOOTNOTE_VAL], 'type' => 'list'];
            $datavalue = $this->Footnote->saveAndGetFootnoteRec($footnotes, $extra);
            $footNoteNid = array_keys($datavalue)[0];
        } else {
            $footNoteNid = '-1';
        }
        return $footNoteNid;
    }

    /*
      function to insert data
      @input: dataRow,params
      @output: boolean
     */

    public function insertData($dataRow = [], $params = []) {
        $footnoteNId = $this->saveFootnote($dataRow['footnote']);
        $customLogStatus = true;
        $numericstatus   = false;
        $fieldsArray = [
            _MDATA_IUSNID => $dataRow['iusNid'],
            _MDATA_TIMEPERIODNID => $dataRow['tpNid'],
            _MDATA_AREANID => $dataRow['aNid'],
            _MDATA_FOOTNOTENID => $footnoteNId,
            _MDATA_SOURCENID => $dataRow['srcNid'],
            _MDATA_INDICATORNID => $dataRow['iNid'],
            _MDATA_UNITNID => $dataRow['uNid'],
            _MDATA_SUBGRPNID => $dataRow['sNid'],
            _MDATA_IUNID => $dataRow['iNid'] . '_' . $dataRow['uNid'],
           // _MDATA_DATAVALUE => $dataRow['dv'],
        ];
        $numericstatus = $this->CommonInterface->checkNumeric(trim($dataRow['dv']));
      
        if ($numericstatus == true) {
            $additionaldata = [_MDATA_DATAVALUE => $dataRow['dv'], _MDATA_ISTEXT_DATA => 0];
            $fieldsArray = array_merge($fieldsArray, $additionaldata);
        } else {
            $additionaldata = [_MDATA_TEXTUAL_DATA_VALUE => $dataRow['dv'], _MDATA_ISTEXT_DATA => 1];
            $fieldsArray = array_merge($fieldsArray, $additionaldata);
        }
   
        $dataNId = $this->DataObj->insertData($fieldsArray, $params);
        $newvalue = $dataRow['dv'];
        $msgCode = '';

        if ($dataNId) {
            //-- TRANSACTION Log           
            $this->addDBLog($params['dbId'], _INSERT, '', _DONE, $dataNId, '', $newvalue, $params['isDbLog']);
        } else {
            // failed insert 
            $customLogStatus = false;
            $msgCode = 102;
            $this->addDBLog($params['dbId'], _INSERT, $this->getMessage($msgCode), _FAILED, $dataNId, '', $newvalue, $params['isDbLog']);
        }

        // call custom log
        $this->customLog($params['dbId'], $customLogStatus, $dataRow, $params, $msgCode, $params['isCustomLog']);
    }

    /*
      function to add log data
      @input: $logdata array
      @output: boolean

     */

    public function addDBLog($dbId, $action, $desc, $status, $identifier, $prevvalue = '', $newvalue = '', $isDbLog = true) {
        if ($isDbLog === true) {

            $fieldsArray = [
                _MTRANSACTIONLOGS_DB_ID => $dbId,
                _MTRANSACTIONLOGS_ACTION => $action, // update ,insert or validation 
                _MTRANSACTIONLOGS_MODULE => _DATAENTRYVAL, // DATAENTRY
                _MTRANSACTIONLOGS_SUBMODULE => _SUB_MOD_DATA_ENTRY, // formdata 
                _MTRANSACTIONLOGS_IDENTIFIER => $identifier,
                _MTRANSACTIONLOGS_PREVIOUSVALUE => $prevvalue,
                _MTRANSACTIONLOGS_NEWVALUE => $newvalue,
                _MTRANSACTIONLOGS_DESCRIPTION => $desc,
                _MTRANSACTIONLOGS_STATUS => $status,
            ];
            $LogId = $this->TransactionLogs->createRecord($fieldsArray);
        }
    }

    /*
      function to create custome log data
      @input: params array , dbId database id
      @output: json data
     */

    public function customLog($dbId = '', $status, $dataRow, $params = [], $msgCode, $isCustomLog = true) {

        if ($isCustomLog === true) {

            // check param status = true/false
            if (isset($status)) {
                if ($status === true) {
                    // increase counter of imported
                    $counterName = 'totalImported';
                } else {
                    // increase counter of issues
                    $counterName = 'totalIssues';
                    // add errors
                    // maintain issues array
                    $issues = $this->checkGetRowLogInfo($dataRow, $params, $this->getMessage($msgCode));
                }

                // increase counter
                if (isset($this->customLogDetails[$counterName])) {
                    $this->customLogDetails[$counterName] = $this->customLogDetails[$counterName] + 1;
                } else {
                    $this->customLogDetails[$counterName] = 1;
                }


                //pr($dataRow);
                //pr($msgCode);

                if (isset($issues)) {
                    $this->customLogDetails['issues'][] = $issues;
                }
            }
        }
    }

    /*
      function to delete the data
      @data:json data
     */

    public function deleteDataValue($dbId = '', $data = '') {
        if (isset($data) && !empty($data)) {
            $dNids = [];
            $dataArray = json_decode($data, true);

            if ($dataArray) {

                foreach ($dataArray as $index => $value) {
                    if ($value['dNid'] != ''){
                        $dNids[] = $value['dNid'];
                        $conditions = [_MDATA_NID . ' IN ' => $dNids];
                        $result = $this->deleteRecords($conditions);
                        if($result>0){
                             $this->addDBLog($dbId, _DELETE, '', _DONE, $value['dNid'], $value['dv'], '', true);                    
                        }else{
                             $this->addDBLog($dbId, _DELETE, '', _FAILED, $value['dNid'], $value['dv'], '', true);
                        }
                    }
                        
                }
                
            }
        }
    }

    /*
      function to get IUS valiadtion rules
      @input: IUSGids ,dbId
     */

    public function getIUSValidationRule($IUSGids = [], $dbId) {

        $fields = [
            _MIUSVALIDATION_ID,
            _MIUSVALIDATION_INDICATOR_GID,
            _MIUSVALIDATION_UNIT_GID,
            _MIUSVALIDATION_SUBGROUP_GID,
            _MIUSVALIDATION_IS_TEXTUAL,
            _MIUSVALIDATION_MIN_VALUE,
            _MIUSVALIDATION_MAX_VALUE
        ];

        $conditions = [];

        $conditions[_MIUSVALIDATION_DB_ID] = $dbId;

        if (isset($IUSGids['iGid']) && !empty($IUSGids['iGid']))
            $conditions[_MIUSVALIDATION_INDICATOR_GID . ' IN '] = $IUSGids['iGid'];

        if (isset($IUSGids['uGid']) && !empty($IUSGids['uGid']))
            $conditions[_MIUSVALIDATION_UNIT_GID . ' IN '] = $IUSGids['uGid'];

        if (isset($IUSGids['sGid']) && !empty($IUSGids['sGid']))
            $conditions[_MIUSVALIDATION_SUBGROUP_GID . ' IN '] = $IUSGids['sGid'];

        $iusValidations = $this->MIusValidations->getRecords($fields, $conditions, 'all');

        $this->IUSValidations = $iusValidations;
    }

    /*
      function to get IUSNIds, gid and names
     */

    public function getIusOparands($iusArray = []) {
        $returnData = [];
        if ($iusArray) {

            // filter User access
            $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);

            $iGidArray = $uGidArray = $sGidArray = [];

            foreach ($iusArray as $index => $ius) {
                $iusAr = explode(_DELEM1, $ius);

                if (isset($iusAr[0]) && !empty($iusAr[0])) {
                    if ($indicatorGidsAccessible !== false && count($indicatorGidsAccessible) > 0)
                        if (!in_array($iusAr[0], $indicatorGidsAccessible))
                            continue;
                    $iGidArray[] = $iusAr[0];
                    $iusGids[$index]['Indicator.' . _INDICATOR_INDICATOR_GID] = $iusAr[0];
                }
                if (isset($iusAr[1]) && !empty($iusAr[1])) {
                    $uGidArray[] = $iusAr[1];
                    $iusGids[$index]['Unit.' . _UNIT_UNIT_GID] = $iusAr[1];
                }
                if (isset($iusAr[2]) && !empty($iusAr[2])) {
                    $sGidArray[] = $iusAr[2];
                    $iusGids[$index]['SubgroupVals.' . _SUBGROUP_VAL_SUBGROUP_VAL_GID] = $iusAr[2];
                }
            }

            // check if user has assigned some indicator
            if ($indicatorGidsAccessible !== false && count($indicatorGidsAccessible) > 0) {
                $newIGids = array_intersect($indicatorGidsAccessible, $iGidArray);
                $iGidArray = $newIGids;
            }

            //$iudInfo = $this->IndicatorUnitSubgroup->getIusNidsDetails($iGidArray, $uGidArray, $sGidArray);
            $iudInfo = $this->IndicatorUnitSubgroup->getDetailsFromGids($iusGids);

            foreach ($iudInfo as $ius) {
                $returnData['ius'][$ius['IUSNId']] = [
                    'iName' => $ius['indicator']['Indicator_Name'],
                    'iNid' => $ius['indicator']['Indicator_NId'],
                    'iGid' => $ius['indicator']['Indicator_GId'],
                    'uName' => $ius['unit']['Unit_Name'],
                    'uNid' => $ius['unit']['Unit_NId'],
                    'uGid' => $ius['unit']['Unit_GId'],
                    'sName' => $ius['subgroup_val']['Subgroup_Val'],
                    'sNid' => $ius['subgroup_val']['Subgroup_Val_NId'],
                    'sGid' => $ius['subgroup_val']['Subgroup_Val_GId']
                ];

                $returnData['iusNids'][] = $ius['IUSNId'];
                $returnData['iusGids'][$ius['IUSNId']] = [
                    _MIUSVALIDATION_INDICATOR_GID => $ius['indicator']['Indicator_GId'],
                    _MIUSVALIDATION_UNIT_GID => $ius['unit']['Unit_GId'],
                    _MIUSVALIDATION_SUBGROUP_GID => $ius['subgroup_val']['Subgroup_Val_GId']
                ];
            }
        }

        return $returnData;
    }

    /*
      function to get data
     */

    public function getData($fields = [], $conditions = [], $extra = []) {
        $returnData = ['data' => [], 'iusInfo' => []];
        // get all IUSNIds and info
        $iusInfo = $this->getIusOparands($extra);
        if (isset($iusInfo['iusNids'])) {
            // will return IUS breakup info
            $returnData['iusInfo'] = $iusInfo;

            $conditions[_MDATA_IUSNID . ' IN '] = $iusInfo['iusNids'];
            $returnData['data'] = $this->DataObj->getRecords($fields, $conditions, 'all');
        }

        return $returnData;
    }

    /*
      function to get data replace to getDEsearchData
     */

    public function getFootnoteList() {
        $returnData = [];

        $returnData = $this->FootnoteObj->find('all')->combine(_FOOTNOTE_NId, _FOOTNOTE_VAL)->toArray();

        return $returnData;
    }

    /*
     * 
     * method to get total no of data  
     */

    public function getDataCount($conditions = []) {
        $count = 0;
        return $count = $this->DataObj->getCount($conditions);
    }

    /*
      function to return message
      @input: dataRow
      @output: boolean
     */

    public function getMessage($case) {
        $msg = '';
        switch ($case) {
            case "100":
                $msg = _ERR_DATAVAL_EMPTY;
                break;
            case "101":
                $msg = _ERR_IUSVALIDATION;
                break;
            case "102":
                $msg = _ERR_SAVE_OPERATION;
                break;
            case "103":
                $msg = _ERR_UPDATE_OPERATION;
                break;
            case "104":
                $msg = _ERR_TIME_PERIOD_EMPTY;
                break;
            case "105":
                $msg = _ERR_IUS_NId_EMPTY;
                break;
            case "106":
                $msg = _ERR_AREAID_EMPTY;
                break;
            case "107":
                $msg = _ERR_SOURCENID_EMPTY;
                break;
            case "108":
                $msg = _ERR_AREAID_NOTALLOW;
                break;
        }

        return $msg;
    }

    
}
