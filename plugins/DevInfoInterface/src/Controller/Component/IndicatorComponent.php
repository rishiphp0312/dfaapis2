<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Indicator Component
 */
class IndicatorComponent extends Component {

    // The other component your component uses
    public $components = ['TransactionLogs',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Data',
        'DevInfoInterface.Metadatareport',
        'DevInfoInterface.Metadata',
        'DevInfoInterface.CommonInterface'];
    public $IndicatorObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->IndicatorObj = TableRegistry::get('DevInfoInterface.Indicator');
    }

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all') {
        // MSSQL Compatibilty - MSSQL can't support more than 2100 params - 900 to be safe
        $chunkSize = 900;

        if (isset($conditions['OR']) && count($conditions['OR'], true) > $chunkSize) {

            $result = [];
            $countIncludingChildparams = count($conditions['OR'], true);

            // count for single index
            //$orSingleParamCount = count(reset($conditions['OR']));
            //$splitChunkSize = floor(count($conditions['OR']) / $orSingleParamCount);
            $splitChunkSize = floor(count($conditions['OR']) / ($countIncludingChildparams / $chunkSize));

            // MSSQL Compatibilty - MSSQL can't support more than 2100 params
            $orConditionsChunked = array_chunk($conditions['OR'], $splitChunkSize);

            foreach ($orConditionsChunked as $orCond) {
                $conditions['OR'] = $orCond;
                $getIndicator = $this->IndicatorObj->getRecords($fields, $conditions, $type);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if ($type == 'list') {
                    $result = array_replace($result, $getIndicator);
                }// we dont need to preserve keys, just merge
                else {
                    $result = array_merge($result, $getIndicator);
                }
            }
        } else {
            $result = $this->IndicatorObj->getRecords($fields, $conditions, $type);
        }
        return $result;
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = []) {
        return $this->IndicatorObj->deleteRecords($conditions);
    }

    /**
     * Delete records from Indicator as well as associated records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteIndicatordata($iNid = '') {
        $conditions = [];
        $conditions = [_INDICATOR_INDICATOR_NID . ' IN ' => $iNid];
        $result = $this->deleteRecords($conditions);

        if ($result > 0) {

            // delete data 
            $conditions = [];
            $conditions = [_MDATA_INDICATORNID . ' IN ' => $iNid];
            $data = $this->Data->deleteRecords($conditions);

            $conditions = $fields = [];
            $fields = [_IUS_IUSNID, _IUS_IUSNID];
            $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid];
            $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');

            //deleet ius             
            $conditions = [];
            $conditions = [_META_REPORT_TARGET_NID . ' IN ' => $iNid];
            $data = $this->Metadata->deleteRecords($conditions);

            //deleet ius             
            $conditions = [];
            $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid];
            $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);


            if (count($getIusNids) > 0) {
                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
                $data = $this->IcIus->deleteRecords($conditions);
            }
            return true;
        } else {
            return false;
        }
    }

    /*
     * check name if name exists in indicator table or not
     * return true or false
     */

    public function checkName($indName = '', $iNid = '') {
        $conditions = $fields = [];
        $fields = [_INDICATOR_INDICATOR_NID];
        $conditions = [_INDICATOR_INDICATOR_NAME => $indName];
        if (isset($iNid) && !empty($iNid)) {
            $extra[_INDICATOR_INDICATOR_NID . ' !='] = $iNid;
            $conditions = array_merge($conditions, $extra);
        }
        $nameexits = $this->getRecords($fields, $conditions);
        if (!empty($nameexits)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * check gid if exists in indicator table or not
     * return true or false
     */

    public function checkGid($gid = '', $iNid = '') {
        $conditions = $fields = [];
        $fields = [_INDICATOR_INDICATOR_NID];
        $conditions = [_INDICATOR_INDICATOR_GID => $gid];
        if (isset($iNid) && !empty($iNid)) {
            $extra[_INDICATOR_INDICATOR_NID . ' !='] = $iNid;
            $conditions = array_merge($conditions, $extra);
        }

        $gidexits = $this->getRecords($fields, $conditions);

        if (!empty($gidexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }

    /*
     * method to add ius data  
     * 
     */

    function insertIUSdata($iNid, $unitNids, $subgrpNids) {

        foreach ($unitNids as $uNid) {
            foreach ($subgrpNids as $sNid) {
                $fieldsArray = [];
                $fieldsArray = [_IUS_INDICATOR_NID => $iNid, _IUS_UNIT_NID => $uNid, _IUS_SUBGROUP_VAL_NID => $sNid];
                $return = $this->IndicatorUnitSubgroup->insertData($fieldsArray);
            }
        }
    }

    /*
     * method to modify  ius data  
     * 
     */

    function modifyIUSdata($iNid, $unitNids, $subgrpNids) {
        
    }

    /*
     * method to get existing ius combination for specific ind nid   
     * @$unitNids array 
     * @$subgrpNids array 
     * @$iNid single of indicator nid  
     */

    function getExistCombination($iNid, $unitNids, $subgrpNids) {

        $fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_IUSNID];
        $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid, _IUS_UNIT_NID . ' IN ' => $unitNids, _IUS_SUBGROUP_VAL_NID . ' IN ' => $subgrpNids];
        $iusdetails = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions);
        $indArr = $uniArr = $sgArr = $iusNidsArr = [];
        foreach ($iusdetails as $iusDt) {
            $indArr[] = $iusDt[_IUS_INDICATOR_NID];
            $uniArr[] = $iusDt[_IUS_UNIT_NID];
            $sgArr[] = $iusDt[_IUS_SUBGROUP_VAL_NID];
            $iusNidsArr[] = $iusDt[_IUS_IUSNID];
        }
        return ['indArr' => $indArr, 'uniArr' => $uniArr, 'sgArr' => $sgArr, 'iusNidsArr' => $iusNidsArr];
    }

    public function checkCategoryTarget($indNid = '', $catNid = '') {
        $fields = [_META_REPORT_NID];
        $conditions = [_META_REPORT_CATEGORY_NID => $catNid, _META_REPORT_TARGET_NID => $indNid];

        $result = $this->Metadatareport->getRecords($fields, $conditions);
        echo 'cat target';

        if (!empty($result)) {
            return $result[0][_META_REPORT_NID];
        } else {
            return false;
        }
    }

    public function checkCategoryName($catName = '', $catNid = '') {
        $fields = [_META_CATEGORY_NID];
        $conditions = [];
        $conditions[_META_CATEGORY_NAME] = $catName;
        if (!empty($catNid)) {
            $conditions[_META_CATEGORY_NID . ' !='] = $catNid;
        }

        $result = $this->Metadata->getRecords($fields, $conditions);
        echo 'cat name';
        pr($result);
        if (!empty($result)) {
            return $result[0][_META_CATEGORY_NID];
        } else {
            return false;
        }
    }

    public function getCategorymaxOrder() {
        $fields = ['CategoryOrder'];
        $conditions = ['order' => array(' CategoryOrder desc')];
        $result = $this->Metadata->getRecords($fields, $conditions);
        pr($result);
        die;
        if (!empty($result)) {
            return $result[0][_META_CATEGORY_ORDER];
        } else {
            return false;
        }
    }

    /*

      method to add /modify metadata category
      $metaData array
      return id
     */

    function manageCategory($metaData = []) {
        $updateCategory = false;
        $metCatNid = $metaMaxNid = '';
        $metaorderNo = 0;
        $metaMaxNid = $this->Metadata->getMaxNid();
        $metaorderNo = $this->Metadata->getOrderno();
        $metaData[_META_CATEGORY_ORDER] = $metaorderNo;
        if (isset($metaData[_META_CATEGORY_NID]) && !empty($metaData[_META_CATEGORY_NID])) {

            $updateCategory = true;
            $metCatNid = $metaData[_META_CATEGORY_NID];
        }
        echo 'gid==' . $mcatGid = strtoupper($metaData[_META_CATEGORY_NAME]);
        echo 'gid==' . $mcatGid = str_replace(" ", "_", $mcatGid);
        echo 'gid==' . $metaData[_META_CATEGORY_GID] = $mcatGid . '_' . $metaMaxNid;

        if ($updateCategory == true) {
            echo 'updacate11';
            $catnameId = $this->checkCategoryName($metaData[_META_CATEGORY_NAME], $metCatNid);

            if ($catnameId == false) {
                $catConditions = [_META_CATEGORY_NID => $metCatNid];
                unset($metaData[_META_CATEGORY_NID]);
                $mcatNid = $this->Metadata->updateRecords($metaData, $catConditions); //update case 
                return $mcatNid = $metCatNid;
            } else {

                return ['error' => _ERR135]; //category already exists 
            }
        } else {

            echo 'catid--' . $catnameId = $this->checkCategoryName($metaData[_META_CATEGORY_NAME], '');
            if ($catnameId == false) {
                echo 'insert12';
                return $mcatNid = $this->Metadata->insertData($metaData); //insert case 
            } else {
                echo 'updacate12';
                $catConditions = [_META_CATEGORY_NID => $catnameId];
                unset($metaData[_META_CATEGORY_NID]);
                $this->Metadata->updateRecords($metaData, $catConditions); //update case 
                return $mcatNid = $catnameId;
            }
        }
    }

    /*

      method to add /modify metadata report
      $dataReport array
      $targetNid is indicator nid
      $catNid is meta category nid
     */

    function manageReportCategory($dataReport = [], $targetNid = '', $catNid = '') {
        echo 'targetid==' . $targetNid . '===catnid==' . $catNid;
        $metadataReport = [_META_REPORT_METADATA => $dataReport[_META_REPORT_METADATA],
            _META_REPORT_CATEGORY_NID => $catNid, _META_REPORT_TARGET_NID => $targetNid];
        $getreportId = $this->checkCategoryTarget($targetNid, $catNid);
        echo 'getreportId';
        pr($getreportId);
        if ($getreportId == false) {
            //insert report 
            $metaReportNid = $this->Metadatareport->insertData($metadataReport);
        } else {
            //update case 
            $reportConditions = [_META_REPORT_NID => $getreportId];
            unset($dataReport[_META_REPORT_NID]);
            $metaReportNid = $this->Metadatareport->updateRecords($dataReport, $reportConditions); //update case 				
        }
    }

    /*
     * method to add/ modify the indicator data  
     * @$fieldsArray array contains posted data 
     */

    public function manageIndicatorData($fieldsArray = []) {
        pr($fieldsArray);

        $indOrderNo = 0; //_INDICATOR_INDICATOR_ORDER

        $indOrderNo = $this->getOrderno();


        //pr($indOrderNo);die;
        $updateCategory = false;
        $metadata = $fieldsArray['metadata'];
        $metCatNid = '';
        $metareportdata = $fieldsArray['metareportdata'];



        $unitNids = $fieldsArray['unitNids'];
        $subgrpNids = $fieldsArray['subgrpNids'];

        unset($fieldsArray['subgrpNids']);
        unset($fieldsArray['unitNids']);
        unset($fieldsArray['metadata']);
        $gid = $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID];
        $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_ORDER] = $indOrderNo;
        $indName = $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME];
        $iNid = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID])) ? $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID] : '';

        $checkGid = $this->checkGid($gid, $iNid);

        if ($checkGid == false) {
            return ['error' => _ERR135]; //gid  exists 
        }

        $checkname = $this->checkName($indName, $iNid);

        if ($checkname == false) {
            return ['error' => _ERR136]; // name  exists 
        }
        if (empty($iNid)) {

            $returniNid = $this->insertData($fieldsArray['indicatorDetails'], 'nid'); //ind nid 
            $this->insertIUSdata($returniNid, $unitNids, $subgrpNids);
            $catNid = $this->manageCategory($metadata);
            if (isset($catNid['error']))
                return $catNid['error'];
            $this->manageReportCategory($metareportdata, $returniNid, $catNid);

            pr($metadata);
            echo 'metdatadefinition ==' . $metareportdata[_META_REPORT_METADATA];
        } else {

            $dbUniArr = $dbSgArr = [];
            $data = $this->getExistCombination($iNid, $unitNids, $subgrpNids);
            $dbUniArr = $data['uniArr'];
            $dbSgArr = $data['sgArr'];
            $dbiusNidsArr = $data['iusNidsArr'];
            if (!empty($dbSgArr) || !empty($dbUniArr)) {

                $diffUnits = array_intersect($unitNids, $dbUniArr);
                $diffSg = array_intersect($subgrpNids, $dbSgArr);

                $fields = $conditions = [];
                $conditions[_IUS_INDICATOR_NID] = $iNid;

                if (!empty($diffUnits))
                    $conditions[_IUS_UNIT_NID . ' IN '] = $diffUnits;

                if (!empty($diffSg))
                    $conditions[_IUS_SUBGROUP_VAL_NID . ' IN '] = $diffSg;

                $fields = [_IUS_IUSNID, _IUS_IUSNID];
                //pr($conditions);
                $iusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, 'list');
                //echo 'not in delete iusnids  ';
                //pr($iusNids);

                $conditions = [];
                $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid, _IUS_IUSNID . ' NOT IN ' => $iusNids];

                $this->IndicatorUnitSubgroup->deleteRecords($conditions);

                $conditions = [];
                $conditions = [_MDATA_INDICATORNID . ' IN ' => $iNid, _MDATA_IUSNID . ' NOT IN ' => $iusNids];

                $this->Data->deleteRecords($conditions);

                //echo 'not delete ';
                //pr($diffUnits);  pr($diffSg);
                $insertSg = $insertUnits = [];
                //pr($unitNids);
                //pr($subgrpNids);
                $dbSgArr = array_unique($dbSgArr);
                $dbUniArr = array_unique($dbUniArr);
                //	echo 'exist';
                //	pr($dbUniArr);
                //	pr($dbSgArr);
                $insertUnits = array_diff($unitNids, $dbUniArr);
                $insertSg = array_diff($subgrpNids, $dbSgArr);
                // echo 'insert ';
                // pr($insertUnits);  
                // pr($insertSg);
                if (!empty($insertUnits) && empty($insertSg)) {      // echo '1 case ';
                    $this->insertIUSdata($iNid, $insertUnits, $subgrpNids);
                }
                if (empty($insertUnits) && !empty($insertSg)) {      // echo '2 case ';
                    $this->insertIUSdata($iNid, $unitNids, $insertSg);
                }
                if (!empty($insertUnits) && !empty($insertSg)) {      //	echo '3 case ';
                    $this->insertIUSdata($iNid, $unitNids, $insertSg);
                    $bindOldsgswithNewUnits = array_diff($subgrpNids, $insertSg);
                    $this->insertIUSdata($iNid, $insertUnits, $bindOldsgswithNewUnits);
                }
                if (empty($insertUnits) && empty($insertSg)) {      // echo '4 case ';
                    //$this->insertIUSdata($iNid,$insertUnits,$subgrpNids);
                    // nothing					
                }
            } else {

                $this->insertIUSdata($iNid, $unitNids, $subgrpNids);
            }

            $conditions = [];
            $conditions[_INDICATOR_INDICATOR_NID] = $iNid;

            unset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID]);
            $returniNid = $this->updateRecords($fieldsArray['indicatorDetails'], $conditions);

            $catNid = $this->manageCategory($metadata);
            if (isset($catNid['error']))
                return $catNid['error'];

            $this->manageReportCategory($metareportdata, $iNid, $catNid);
        }
        if ($returniNid > 0) {

            return $returniNid;
        } else {
            return ['error' => _ERR100]; //server error 
        }
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = [], $extra = '') {
        $return = $this->IndicatorObj->insertData($fieldsArray, $extra);
        //-- TRANSACTION Log
        $LogId = $this->TransactionLogs->createLog(_INSERT, _TEMPLATEVAL, _INDICATOR, $fieldsArray[_INDICATOR_INDICATOR_GID], _DONE);
        return $return;
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->IndicatorObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->IndicatorObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * Traditional find method to get records
     *
     * @param string $type Query Type
     * @param array $options Extra options
     * @return void
     */
    public function find($type, $options = [], $extra = null) {
        $query = $this->IndicatorObj->find($type, $options);
        if (isset($extra['count'])) {
            $data = $query->count();
        } else {
            $results = $query->hydrate(false)->all();
            $data = $results->toArray();
        }
        return $data;
    }

    /**
     * to get  Indicator details of specific id 
     * 
     * @param iNid the indicator  nid. {DEFAULT : empty}
     * @return void
     */
    public function getIndicatorById($iNid = '') {

        $fields = [_INDICATOR_INDICATOR_GID, _INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_NID, _INDICATOR_SHORT_NAME, _INDICATOR_HIGHISGOOD, _INDICATOR_DATA_EXIST];
        $conditions = [_INDICATOR_INDICATOR_NID => $iNid];
        return $this->getRecords($fields, $conditions);
    }

    /**
     * to get  highest order no
     * 

     */
    public function getOrderno() {

        $query = $this->IndicatorObj->find();
        $result = $query->select(['max' => $query->func()->max('Indicator_Order'),
                ])->hydrate(false)->toArray();
        return $result = current($result)['max'];
    }

    /**
     * - For DEVELOPMENT purpose only
     * Test method to do anything based on this model (Run RAW queries or complex queries)
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->IndicatorObj->testCasesFromTable($params);
    }

}
