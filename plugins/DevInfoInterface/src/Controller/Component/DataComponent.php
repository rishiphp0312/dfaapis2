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

        $params = array('validation' => $validation, 'iscustomLog' => $customLog, 'isDbLog' => $dbLog, 'dbId' => $dbId);

        $return = true;

        // pass json data into prepare data
        $dataArray = $this->prepareData($jsonData, $params);

        if ($dataArray) {
            foreach ($dataArray as $dataRow) {
                // apply validations
                $validation = $this->applyValidation($dataRow, $params);

die('boy');

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
            }
        }

        return $return;
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

                    //$iusGIds[] = array('iGid' => $dataArray[$index]['iGid'], 'uGid' => $dataArray[$index]['uGid'], 'sGid' => $dataArray[$index]['sGid']);
					$iusGids[] = [
						_MIUSVALIDATION_INDICATOR_GID => $dataArray[$index]['iGid'],
						_MIUSVALIDATION_UNIT_GID => $dataArray[$index]['uGid'],
						_MIUSVALIDATION_SUBGROUP_GID => $dataArray[$index]['sGid']
					];
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

        $logdata[] = ['rowValue' => $dataRow, 'error' => ''];
        if ($dataRow['dv'] == '') {

            $this->addDBLog($params['dbId'], _ACTION_VALIDATION, $this->getMessage(100), _FAILED, '', '', '', $params['isDbLog']);
            //$logdata[]=['rowValue'=>$dataRow,'error'=>$this->getMessage(100)];   
            //$this->customLog($logdata,$params['iscustomLog']);
            $return = false;
        } else {
            if ($params['validation'] == true) {
                $status = $this->checkIUSValidation($dataRow, $params['dbId']);
die('aagaya');
                if ($status == false) {
                    $this->addDBLog($params['dbId'], _ACTION_VALIDATION, $this->getMessage(101), _FAILED, '', '', '', $params['isDbLog']);
                    // $logdata[]=['rowValue'=>$dataRow,'error'=>$this->getMessage(100)];
                    //$this->customLog($logdata,$params['iscustomLog']);
                    $return = false;
                }
            }
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

        if (empty($iusValidation)) {
            $return = true;
        } else {
            // If not textual
            if ($iusValidation['is_textual'] == 0) {
                // number check
                if (preg_match('/(\d+\.?\d*)/', $dataRow['dv']) != 0) {
                    $return = false;
                }
                // Check min value
                if ($iusValidation['min_value'] != null && $dataRow['dv'] <= $iusValidation['min_value']) {
                    $return = false;
                }
                // Check max value
                if ($iusValidation['max_value'] != null && $dataRow['dv'] >= $iusValidation['max_value']) {
                    $return = false;
                }
            } else {
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
        $return = ['update' => true, 'dNid' => $dNid];

        if (empty($dNid)) {

            $fields = [_MDATA_NID];
            $conditions = [_MDATA_AREANID => $dataRow['aNId'], _MDATA_IUSNID => $dataRow['iusNId'], _MDATA_SOURCENID => $dataRow['srcNid'], _MDATA_TIMEPERIODNID => $dataRow['tpNid']];

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
      @input: dataRow,params
      @output: boolean
     */

    public function updateData($dataRow = [], $params = []) {

        $footnoteNId = $this->addFootnote($dataRow['footnote']);

        $fields = [_MDATA_FOOTNOTENID => $footnoteNId,
            _MDATA_TIMEPERIODNID => $dataRow['tpNid'],
            _MDATA_SOURCENID => $dataRow['srcNid'],
            _MDATA_DATAVALUE => $dataRow['dv'],
            _MDATA_AREANID => $dataRow['aNId'],
        ];

        $return = $this->DataObj->updateDataByParams($fields, [_MDATA_NID => $dataRow['dNid']]);
        /*
          $conditions[_MDATA_NID . ' IN '] = $dataRow['dNid'];
          $fields = [];
          $data = $this->DataObj->getDataByParams($fields, $conditions, 'all');
         */
        //-- TRANSACTION Log
        $fields1 = [_MDATA_DATAVALUE];
        $conditions1 = [_MDATA_NID => $dataRow['dNid']];
        $data = $this->DataObj->getDataByParams($fields1, $conditions1, 'all');

        $prevvalue = current($data)[_MDATA_DATAVALUE];
        $newvalue = $dataRow['dv'];

        if ($return) {
            $this->addDBLog($params['dbId'], _UPDATE, '', _SUCCESS, $dataRow['dNid'], $prevvalue, $newvalue, $params['isDbLog']);
            //$this->customLog($logdata,$params['iscustomLog']);
        } else {
            $this->addDBLog($params['dbId'], _UPDATE, $this->getMessage(102), _FAILED, $dataRow['dNid'], $prevvalue, $newvalue, $params['isDbLog']);
            //$this->customLog($logdata,$params['iscustomLog']);
        }
    }

    /*
      function to insert footnote
      @input: footNoteValue
      @output: boolean
     */

    public function addFootnote($footNoteValue = '') {
        $footnote = '';
        if (!empty($footNoteValue)) {
            
             $fields = [_FOOTNOTE_NId] ;
             $conditions = [_FOOTNOTE_VAL => $footNoteValue] ;
             $data = $this->Footnote->getDataByParams($fields, $conditions, 'all');
             if(!empty($data)){
             
                $footNoteNid = $data[0][_FOOTNOTE_NId];
                
             }else{                 
                
                $fieldsArray[_FOOTNOTE_VAL] = $footNoteValue;
                $fieldsArray[_FOOTNOTE_GID] = $this->CommonInterface->guid();
                $footNoteNid = $this->FootnoteObj->insertData($fieldsArray);
                
             }        
            
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

        $footnoteNId = $this->addFootnote($dataRow['footnote']);
        $fieldsArray = [
            _MDATA_IUSNID => $dataRow['iusNId'],
            _MDATA_TIMEPERIODNID => $dataRow['tpNid'],
            _MDATA_AREANID => $dataRow['aNId'],
            _MDATA_FOOTNOTENID => $footnoteNId,
            _MDATA_SOURCENID => $dataRow['srcNid'],
            _MDATA_INDICATORNID => $dataRow['iNid'],
            _MDATA_UNITNID => $dataRow['uNid'],
            _MDATA_SUBGRPNID => $dataRow['sNid'],
            _MDATA_IUNID => $dataRow['iNid'] . '_' . $dataRow['uNid'],
            _MDATA_DATAVALUE => $dataRow['dv'],
        ];
        $dataNId = $this->DataObj->insertData($fieldsArray, $params);
        $newvalue = $dataRow['dv'];

        if ($dataNId) {
            //-- TRANSACTION Log

            $this->addDBLog($params['dbId'], _INSERT, '', _SUCCESS, $dataNId, '', $newvalue, $params['isDbLog']);
            // $this->customLog($logdata,$params['iscustomLog']);
        } else {
            // failed insert 
            $this->addDBLog($params['dbId'], _INSERT, $this->getMessage(102), _FAILED, $dataNId, '', $newvalue, $params['isDbLog']);
            //$this->customLog($logdata,$params['iscustomLog']);
        }
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
      function to add log data
      @input: $logdata array
      @output: boolean
     */

    public function customLog($dbId, $action, $desc, $status, $identifier, $prevvalue = '', $newvalue = '', $customLog = true) {
        
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
		pr($IUSGids);die;

		

  /*      $iusValidation = $this->MIusValidations->getRecords($fields, [_MIUSVALIDATION_INDICATOR_GID => $dataRow['iGid'],
            _MIUSVALIDATION_UNIT_GID => $dataRow['uGid'], _MIUSVALIDATION_SUBGROUP_GID => $dataRow['sGid'],
            _MIUSVALIDATION_DB_ID => $dbId], 'all');
*/
        $iusValidations = $this->MIusValidations->getRecords($fields, ['OR' => $IUSGids], 'all');
		pr($iusValidations);
		DIE;
               // pr($iusValidations ) ;die('aa');  
    
        $this->IUSValidations = current($iusValidation);		

    }

    /*
      function to get IUSNIds, gid and names
     */

    public function getIusOparands($iusArray = []) {
        $returnData = [];
        if ($iusArray) {

            // filter User access
            $indicatorGidsAccessible = $this->UserAccess->getIndicatorAccessToUser(['type' => 'list', 'fields' => [_RACCESSINDICATOR_ID, _RACCESSINDICATOR_INDICATOR_GID]]);
            if ($indicatorGidsAccessible !== false && !empty($indicatorGidsAccessible) && !in_array($iGid, $indicatorGidsAccessible)) {
                continue;
            }

            $iGidArray = $uGidArray = $sGidArray = [];

            foreach ($iusArray as $ius) {
                $iusAr = explode(_DELEM1, $ius);

                if (isset($iusAr[0]) && !empty($iusAr[0]))
                    $iGidArray[] = $iusAr[0];
                if (isset($iusAr[1]) && !empty($iusAr[1]))
                    $uGidArray[] = $iusAr[1];
                if (isset($iusAr[2]) && !empty($iusAr[2]))
                    $sGidArray[] = $iusAr[2];
            }

            // check if user has assigned some indicator
            //
            if ($indicatorGidsAccessible !== false && count($indicatorGidsAccessible) > 0) {
                $newIGids = array_intersect($indicatorGidsAccessible, $iGidArray);
                $iGidArray = $newIGids;
            }

            $iudInfo = $this->IndicatorUnitSubgroup->getIusNidsDetails($iGidArray, $uGidArray, $sGidArray);

            foreach ($iudInfo as $ius) {
                $returnData['ius'][$ius['IUSNId']] = [
                    'iName' => $ius['indicator']['Indicator_Name'],
                    'iNId' => $ius['indicator']['Indicator_NId'],
                    'iGId' => $ius['indicator']['Indicator_GId'],
                    'uName' => $ius['unit']['Unit_Name'],
                    'uNId' => $ius['unit']['Unit_NId'],
                    'uGId' => $ius['unit']['Unit_GId'],
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
      function to get data replace to getDEsearchData
    */
    public function getData($fields = [], $conditions = [], $extra = []) {
        $returnData = ['data' => [], 'iusInfo' => []];
        // get all IUSNIds and info
        $iusInfo = $this->getIusOparands($extra);
        if (isset($iusInfo['iusNids'])) {
            // will return IUS breakup info
            $returnData['iusInfo'] = $iusInfo;

            $conditions[_MDATA_IUSNID . ' IN '] = $iusInfo['iusNids'];
            $returnData['data'] = $this->DataObj->getDataByParams($fields, $conditions, 'all');
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


                break;
        }

        return $msg;
    }

}
