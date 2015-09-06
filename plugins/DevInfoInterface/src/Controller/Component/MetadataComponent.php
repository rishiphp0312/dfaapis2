<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Metadata Component
 */
class MetadataComponent extends Component {

    // The other component your component uses
    public $components = [
        'DevInfoInterface.CommonInterface',
        'DevInfoInterface.Metadatareport',
        'DevInfoInterface.Timeperiod',
        'DevInfoInterface.Area',
        'DevInfoInterface.Indicator',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.Data',
        'TransactionLogs',
    ];
    public $delm = '{-}';
    public $MetadatacategoryObj = NULL;
    public $DbMetadataObj = NULL;
    public $MetadatareportObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->MetadatacategoryObj = TableRegistry::get('DevInfoInterface.Metadatacategory');
        $this->DbMetadataObj = TableRegistry::get('DevInfoInterface.DbMetadata');
        $this->MetadatareportObj = TableRegistry::get('DevInfoInterface.Metadatareport');
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
        $result = $this->MetadatacategoryObj->getRecords($fields, $conditions, $type);
        return $result;
    }

    /**
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        return $this->MetadatacategoryObj->deleteRecords($conditions);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {

        return $this->MetadatacategoryObj->insertData($fieldsArray);
    }

    /**
     * Insert multiple rows at once
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->MetadatacategoryObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateRecords method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->MetadatacategoryObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * to get the highest order no  
     * 
     */
    public function getOrderno() {
        $query = $this->MetadatacategoryObj->find();
        $result = $query->select(['max' => $query->func()->max(_META_CATEGORY_ORDER),
                ])->hydrate(false)->toArray();
        return $result = current($result)['max'];
    }

    /**
     * to get the highest max nid  
     * 
     */
    public function getMaxNid() {

        $query = $this->MetadatacategoryObj->find();
        $result = $query->select(['max' => $query->func()->max(_META_CATEGORY_NID),
                ])->hydrate(false)->toArray();
        return $result = current($result)['max'];
    }

    /**
     * to get  Meta data details of specific indicator nid  
     * 
     * @param iNid the indicator  nid. {DEFAULT : empty}
     * @return void
     */
    public function getMetaDataDetails($iNid = '') {

        $fields = [_META_REPORT_CATEGORY_NID, _META_REPORT_METADATA];
        $conditions[_META_REPORT_TARGET_NID] = $iNid;
        $catArr = [];
        $metaReport = $this->Metadatareport->getRecords($fields, $conditions);

        if (!empty($metaReport) && count($metaReport) > 0) {
            $fields = $conditions = [];
            foreach ($metaReport as $index => $value) {

                $catNid = $value[_META_REPORT_CATEGORY_NID];
                $catArr[$index]['categoryNid'] = $catNid;
                $catArr[$index]['description'] = $value[_META_REPORT_METADATA];
                $fields = [_META_CATEGORY_NAME, _META_CATEGORY_GID];
                $conditions[_META_CATEGORY_NID] = $catNid;
                $metaData = $this->getRecords($fields, $conditions);

                $catArr[$index]['categoryName'] = (isset($metaData[0][_META_CATEGORY_NAME])) ? $metaData[0][_META_CATEGORY_NAME] : '';
                $catArr[$index]['categoryGid'] = (isset($metaData[0][_META_CATEGORY_GID])) ? $metaData[0][_META_CATEGORY_GID] : '';
            }
        }
        return $catArr = array_values($catArr);
    }

    /*
      method to delete metaData
      $iNid indicator nid
      $nId category nid
     */

    public function deleteMetaData($iNid = '', $nId = '') {

        $metaDataCatName = '';
        $fields = $conditions = [];
        $action = _DELETE;

        if ($iNid != '')
            $conditions[_META_REPORT_TARGET_NID . ' IN '] = $iNid;

        if ($nId != '')
            $conditions[_META_REPORT_CATEGORY_NID . ' IN '] = $nId;

        $data = $this->Metadatareport->deleteRecords($conditions);
        if ($data > 0) {

            $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATAREPORT, $nId, _DONE, '', '', '', '', '');
        } else {
            //
            $getreportId = $this->Metadatareport->checkCategoryTarget($iNid, $nId);
             if ($getreportId == false) {
              $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATAREPORT, $nId, _FAILED, '', '', '', '', _ERR_RECORD_NOTFOUND);                 
             }else{
              $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATAREPORT, $nId, _FAILED, '', '', '', '', _ERR_TRANS_LOG);
                 
             }
            //
        }

        $conditions = [];
        $fields = [];

        if ($nId != '') {

            $metaDataCatName = $this->getCategoryName($nId);

            if (isset($metaDataCatName) && !empty($metaDataCatName)) {
                $conditions = [];
                $conditions[_META_CATEGORY_NID . ' IN '] = $nId;
                $rslt = $this->deleteRecords($conditions);

                if ($rslt > 0) {
                    $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATA, $nId, _DONE, '', '', $metaDataCatName, '', '');
                    return true;
                } else {
                    $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATA, $nId, _FAILED, '', '', $metaDataCatName, '', _ERR_TRANS_LOG);
                    return false;
                }  // $conditions = [_META_REPORT_TARGET_NID . ' IN ' => $iNid];
            } else {
                $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATA, $nId, _FAILED, '', '', $metaDataCatName, '', _ERR_RECORD_NOTFOUND);
                return false;
            }
        }
    }

    /**
     * Get DB Metadata records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getDbMetadataRecords(array $fields, array $conditions, $type = 'all', $extra = []) {

        $result = $this->DbMetadataObj->getRecords($fields, $conditions, $type, $extra);

        return $result;
    }

    /**
     * Get Metadata Report records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getMetadataReportRecords(array $fields, array $conditions, $type = 'all') {


        // MSSQL Compatibilty - MSSQL can't support more than 2100 params - 900 to be safe

        $result = $this->MetadatareportObj->getRecords($fields, $conditions, $type);


        return $result;
    }

    /**
     * add /modify  DB Metadata records based on conditions
     * 
     * 
     */
    public function updateDbMetadataRecords($fieldsArray = [], $conditions = []) {
        $nId = $oldvalue = '';
        $desc = $fieldsArray[_DBMETA_DESC];
        $fields = [_DBMETA_DESC];
        $dbMetdatadesc = $this->getDbMetadataRecords($fields, $conditions);
        $oldvalue = current($dbMetdatadesc)[_DBMETA_DESC];
        $result = $this->DbMetadataObj->updateRecords($fieldsArray, $conditions);
        $nId = (isset($conditions[_DBMETA_NID])) ? $conditions[_DBMETA_NID] : '';
        $action = _UPDATE;
        if ($result > 0) {
            $this->TransactionLogs->createLog($action, _DATABASEVAL, _METADATADB, $nId, _DONE, '', '', $oldvalue, $desc, '');
        } else {
            $this->TransactionLogs->createLog($action, _DATABASEVAL, _METADATADB, $nId, _FAILED, '', '', '', $desc, _ERR_TRANS_LOG);
        }


        return $result;
    }

    /*
     * 
     * update the metadata db counts as per the case
     * @ case is the case 
     */

    public function updateMetadataCount($case = 'all', $nId = '1') {

        $conditions = $data = [];

        // Area
        if ($case == _AREA_TRANSAC || $case == 'all') {
            $data[_DBMETA_AREACNT] = $this->Area->getAreasCount();
        }

        // Indicator
        if ($case == _INDICATOR || $case == 'all') {
            $data[_DBMETA_INDCNT] = $this->Indicator->getIndicatorsCount();
        }

        // IUS Data
        if ($case == _IUSDATA || $case == 'all') {
            $data[_DBMETA_IUSCNT] = $this->IndicatorUnitSubgroup->getIusCount();
        }

        // Source
        if ($case == _SOURCE || $case == 'all') {
            $data[_DBMETA_SRCCNT] = $this->IndicatorClassifications->getSourcesCount([_IC_IC_TYPE => 'SR', _IC_IC_PARENT_NID . ' <>' => '-1']);
        }

        // Form data entry
        if ($case == _SUB_MOD_DATA_ENTRY || $case == 'all') {          //data added using form data 
            $data[_DBMETA_DATACNT] = $this->Data->getDataCount();
        }

        // Time period
        if ($case == _TIMEPERIOD || $case == 'all') {
            $data[_DBMETA_TIMECNT] = $this->Timeperiod->getTimeperiodCount();
        }

        // update in database

        if (!empty($data)) {
            $conditions = [_DBMETA_NID => $nId];
            $fields=[_DBMETA_NID];
            $getdbDetails = $this->getDbMetadataRecords($fields, $conditions);
            if(empty($getdbDetails)){
                 $this->DbMetadataObj->insertData($data);
            }else{ 
                 $this->DbMetadataObj->updateRecords($data, $conditions);
            }
            
        }
    }

    /*
      method to get metadata  category name by id
     */

    public function getCategoryName($metCatNid) {
        $fields = [_META_CATEGORY_NAME];
        $conditions = [];
        $conditions[_META_CATEGORY_NID] = $metCatNid;
        $resultOld = $this->getRecords($fields, $conditions);
        return $olddataValue = $resultOld[0][_META_CATEGORY_NAME];
    }

    /**
     * to get  Meta data category list 
     * 
     * @return void
     */
    public function getMetaDataCategoryList() {
        $catArr = $prepareList = $conditions = [];
        $fields = [_META_CATEGORY_NAME, _META_CATEGORY_GID, _META_CATEGORY_NID];
        $metadataDetails = $this->getRecords($fields, $conditions);
        if (!empty($metadataDetails) && count($metadataDetails) > 0) {

            foreach ($metadataDetails as $index => $value) {


                $prepareList[$index]['categoryNid'] = $value[_META_CATEGORY_NID];
                $prepareList[$index]['categoryName'] = $value[_META_CATEGORY_NAME];
                $prepareList[$index]['categoryGid'] = $value[_META_CATEGORY_GID];
            }
        }
        return $catArr = array_values($prepareList);
    }

}
