<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Indicator Component
 */
class IndicatorComponent extends Component {

    // The other component your component uses
    public $components = ['TransactionLogs', 'Common', 'Auth',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Unit',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.Data',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Metadatareport',
        'DevInfoInterface.Metadata',
        'DevInfoInterface.CommonInterface'];
    public $IndicatorObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->IndicatorObj = TableRegistry::get('DevInfoInterface.Indicator');
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
    }

    /**
     * Get records based on conditions
     * 
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param string $type query type
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
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
                $getIndicator = $this->IndicatorObj->getRecords($fields, $conditions, $type, $extra);
                // We want to preserve the keys in list, as there will always be Nid in keys
                if ($type == 'list') {
                    $result = array_replace($result, $getIndicator);
                }// we dont need to preserve keys, just merge
                else {
                    $result = array_merge($result, $getIndicator);
                }
            }
        } else {
            $result = $this->IndicatorObj->getRecords($fields, $conditions, $type, $extra);
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
    public function deleteIndicatordata($iuNid = '') {
        $return = false;
        $status = _FAILED;
        $indName = $iNid = $uNid = $explodeIu = '';
        $getIusNids = [];
        $indName = $this->getpreviousDetails($iuNid);


        if (isset($iuNid) && !empty($iuNid)) {
            $explodeIu = explode(_DELEM1, $iuNid);
            $iNid = $explodeIu[0];
            $uNid = $explodeIu[1];
            if (!empty($indName)) {

                $conditions = [];
                $conditions = [_INDICATOR_INDICATOR_NID . ' IN ' => $iNid];
                $result = $this->deleteRecords($conditions);

                if ($result > 0) {

                    // delete data 
                    $conditions = [];
                    $conditions = [_MDATA_INDICATORNID . ' IN ' => $iNid];
                    $dataDel = $this->Data->deleteRecords($conditions);


                    $conditions = $fields = [];
                    $fields = [_IUS_IUSNID, _IUS_IUSNID];
                    $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid, _IUS_UNIT_NID . ' IN ' => $uNid];
                    $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');

                    //deleet ius             
                    $conditions = [];
                    $conditions = [_META_REPORT_TARGET_NID . ' IN ' => $iNid];
                    $dataRep = $this->Metadatareport->deleteRecords($conditions);

                    //deleet ius             
                    $conditions = [];
                    $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid, _IUS_UNIT_NID . ' IN ' => $uNid];
                    $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);


                    if (count($getIusNids) > 0) {
                        $conditions = [];
                        $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
                        $dataIus = $this->IcIus->deleteRecords($conditions);
                    }
                    $errordesc = _MSG_IND_DELETION;
                    $status = _DONE;                    
                    $return = true;
                } else {
                    $errordesc = _ERR_TRANS_LOG;                   
                }
            } else {
                $errordesc = _ERR_RECORD_NOTFOUND;             
            }
        } else {            
            $errordesc = _ERR_INVALIDREQUEST;
            
        }
         $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _INDICATOR, $iuNid, $status, '', '', $indName, '', $errordesc);

        return $return;
    }

    /*
     * check name if name exists in indicator table or not
     * return true or false
     */

    public function checkIndicatorName($indName = '', $iNid = '') {
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

    public function insertIUSdata($iNid, $unitNids, $subgrpNids, $iName) {
        if (isset($unitNids) && !empty($unitNids)) {

            foreach ($unitNids as $uNid) {
                $uname = '';
                $udetails = $this->Unit->getUnitById($uNid);
                if (!empty($udetails)) {
                    $uname = $udetails['uName'];
                }
                foreach ($subgrpNids as $sNid) {
                    $fieldsArray = [];
                    $fieldsArray = [_IUS_INDICATOR_NID => $iNid, _IUS_UNIT_NID => $uNid, _IUS_SUBGROUP_VAL_NID => $sNid, _IUS_MIN_VALUE => '0'
                        , _IUS_MAX_VALUE => '0', _IUS_SUBGROUP_NIDS => '0', _IUS_DATA_EXISTS => '0'
                        , _IUS_ISDEFAULTSUBGROUP => '0', _IUS_AVLMINDATAVALUE => '0', _IUS_AVLMAXDATAVALUE => '0'
                        , _IUS_AVLMINTIMEPERIOD => '0', _IUS_AVLMAXTIMEPERIOD => '0'];

                    $action = _INSERT;
                    $return = $this->IndicatorUnitSubgroup->insertData($fieldsArray);
                    if ($return > 0) {
                        $status = _DONE;
                        $errordesc = '';
                    } else {
                        $status = _FAILED;
                        $errordesc = _ERR_TRANS_LOG;
                    }
                    $sgValname = '';
                    $sgValdetails = $this->SubgroupVals->getSubgroupValData($sNid);
                    if (!empty($sgValdetails)) {
                        $sgValname = current($sgValdetails)[_SUBGROUP_VAL_SUBGROUP_VAL];
                    }
                    $newvalue = $iName . _DELEM6 . $uname . _DELEM6 . $sgValname;

                    $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _IUSDATA, $return, $status, '', '', '', $newvalue, $errordesc);
                }
            }
        }
    }

    /*
     * method to get existing ius combination for specific ind nid   
     * @$unitNids array 
     * @$subgrpNids array 
     * @$iNid single of indicator nid  
     */

    public function getExistCombination($iNid) {

        $fields = [_IUS_INDICATOR_NID, _IUS_UNIT_NID, _IUS_SUBGROUP_VAL_NID, _IUS_IUSNID];
        $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid];
        //$conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid, _IUS_UNIT_NID . ' IN ' => $unitNids, _IUS_SUBGROUP_VAL_NID . ' IN ' => $subgrpNids];
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

    

    /*
      method to check category name exist in category table
      returns category nid if exist
     */

    public function checkCategoryName($catName = '', $catNid = '') {
        $fields = [_META_CATEGORY_NID];
        $conditions = [];
        $conditions[_META_CATEGORY_NAME] = $catName;
        if (!empty($catNid)) {
            $conditions[_META_CATEGORY_NID . ' !='] = $catNid;
        }

        $result = $this->Metadata->getRecords($fields, $conditions);

        if (!empty($result)) {
            return $result[0][_META_CATEGORY_NID]; //already exists 
        } else {
            return false;
        }
    }

    /*

      method to add /modify metadata category
      $metaData array
      return id
     */

    public function manageCategory($metaDataArray = [], $iNid = '') {

        if (isset($metaDataArray) && !empty($metaDataArray)) {
            foreach ($metaDataArray as $value) {
                $status = _FAILED;                
                $errordesc = $metCatNid = $metaMaxNid = $identi = $newValue = $olddataValue = '';
                $metaorderNo = 0;
                $metaData = [];
               
                $metaData[_META_PARENT_CATEGORY_NID] = '-1';
                $metaData[_META_CATEGORY_TYPE] = 'I';
                $metaData[_META_CATEGORY_DESC] = '';
                $metaData[_META_CATEGORY_PRESENT] = '0';
                $metaData[_META_CATEGORY_MAND] = '0';
                $newValue = $metaData[_META_CATEGORY_NAME] = $value['category'];                
                $metaData[_META_CATEGORY_NID] = isset($value['nId']) ? $value['nId'] : '';

                if (isset($metaData[_META_CATEGORY_NID]) && !empty($metaData[_META_CATEGORY_NID])) {
                    $action = _UPDATE;

                    $metCatNid = $metaData[_META_CATEGORY_NID];
                    $catConditions = [];
                    $catConditions = [_META_CATEGORY_NID => $metCatNid];
                    //// get old value ///
                    $olddataValue = $this->Metadata->getCategoryName($metCatNid);
                    ///

                    unset($metaData[_META_CATEGORY_NID]);
                    $mcatNid = $this->Metadata->updateRecords($metaData, $catConditions); //update case 
                    if ($mcatNid > 0) {
                        $status = _DONE;                       
                    } else {                       
                        $errordesc = _ERR_TRANS_LOG;
                    }
                    $identi = $metCatNid;

                    if (isset($value['description']) && !empty($value['description']))
                      $this->manageReportCategory($value['description'], $iNid, $metCatNid);
                    
                } else {

                    $metaorderNo = $this->Metadata->getOrderno();
                    $metaData[_META_CATEGORY_ORDER] = $metaorderNo + 1;
                    $catNId = $this->checkCategoryName($metaData[_META_CATEGORY_NAME], '');
                    if ($catNId == false) {
                        $action = _INSERT;
                        
                        $mcatGid = strtoupper($metaData[_META_CATEGORY_NAME]);
                        $mcatGid = str_replace(" ", "_", $mcatGid);
                        $metaMaxNid = $this->Metadata->getMaxNid()+1;
                        
                        $metaData[_META_CATEGORY_GID] = $mcatGid . '_' . $metaMaxNid;
                        
                        $mcatNid = $this->Metadata->insertData($metaData); //insert case 
                        if ($mcatNid > 0) {
                            $status = _DONE;                         
                        } else {                            
                            $errordesc = _ERR_TRANS_LOG;
                        }
                        $identi = $mcatNid; //identifier for trans 

                        if (isset($value['description']) && !empty($value['description']))
                            $this->manageReportCategory($value['description'], $iNid, $mcatNid);
                    } else {

                        $action = _UPDATE;
                        //// get old value ///
                        $olddataValue = $this->Metadata->getCategoryName($catNId);
                        $identi = $catNId;
                        ///
                        $catConditions = [_META_CATEGORY_NID => $catNId];
                        unset($metaData[_META_CATEGORY_NID]);
                        $return = $this->Metadata->updateRecords($metaData, $catConditions); //update case

                        if ($return > 0) {
                            $status = _DONE;
                        } else {                            
                            $errordesc = _ERR_TRANS_LOG;
                        }

                        $newValue = $value['category'];

                        if (isset($value['description']) && !empty($value['description']))
                            $this->manageReportCategory($value['description'], $iNid, $catNId);
                    }
                }
                $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATA, $identi, $status, '', '', $olddataValue, $newValue, $errordesc);

                unset($metaData);
            }
        }
    }

    /*

      method to add /modify metadata report
      $dataReport array
      $targetNid is indicator nid
      $catNid is meta category nid
     */

    public function manageReportCategory($description = '', $targetNid = '', $catNid = '') {
        $errordesc = '';  $status = _FAILED;
        $metadataReport = [_META_REPORT_METADATA => $description,
            _META_REPORT_CATEGORY_NID => $catNid, _META_REPORT_TARGET_NID => $targetNid];
        $getreportId = $this->Metadatareport->checkCategoryTarget($targetNid, $catNid);
        if ($getreportId == false) {
            //insert report 
            $action = _INSERT;
            $metaReportNid = $this->Metadatareport->insertData($metadataReport);
            if ($metaReportNid > 0) {
                $status = _DONE;              
            } else {                
                $errordesc = _ERR_TRANS_LOG;
            }
        } else {
            //update case 
            $action = _UPDATE;
            unset($metadataReport[_META_REPORT_CATEGORY_NID]);
            unset($metadataReport[_META_REPORT_TARGET_NID]);
            $reportConditions = [_META_REPORT_NID => $getreportId];
            $metaReportNid = $this->Metadatareport->updateRecords($metadataReport, $reportConditions); //update case 				
            if ($metaReportNid > 0) {
                $status = _DONE;                
            } else {              
                $errordesc = _ERR_TRANS_LOG;
            }
        }
        $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _METADATAREPORT, $getreportId, $status, '', '', '', $description, $errordesc);

        unset($metadataReport);
    }

    /*
      method to add /modify IusData
      @dbSgArr existing subgroup array
      @dbUniArr existing unit array
      @dbiusNidsArr existing  ius nids array
      @unitNids  posted unit nids array
      @subgrpNids posted subgrp nids array
      @iNid indicator nid
     */

    public function manageIusData($dbSgArr, $dbUniArr, $dbiusNidsArr, $unitNids, $subgrpNids, $iNid, $iName) {

        $commnUnits = array_intersect($unitNids, $dbUniArr); //common  units
        $commnSg = array_intersect($subgrpNids, $dbSgArr); //common  subgroup 

        $fields = $conditions = [];
        $conditions[_IUS_INDICATOR_NID] = $iNid;

        if (!empty($commnUnits))
            $conditions[_IUS_UNIT_NID . ' IN '] = $commnUnits;

        if (!empty($commnSg))
            $conditions[_IUS_SUBGROUP_VAL_NID . ' IN '] = $commnSg;

        $fields = [_IUS_IUSNID, _IUS_IUSNID];
        //pr($conditions);
        $iusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, 'list');


        if (!empty($dbiusNidsArr)) {
            $rmIus = [];
            $rmIus = array_diff($dbiusNidsArr, $iusNids); //  ius will be delete
            if (empty($rmIus)) {
                if (empty($commnUnits) || empty($commnSg))
                    $rmIus = $dbiusNidsArr; //remove ius nids 
            }

            if (count($rmIus) > 0) {

                $conditions = [];
                $conditions = [_MDATA_INDICATORNID . ' IN ' => $iNid, _MDATA_IUSNID . ' IN ' => $rmIus];
                $remdata = $this->Data->deleteRecords($conditions); //delete from data table

                $conditions = [];
                $conditions = [_ICIUS_IUSNID . ' IN ' => $rmIus];
                $remIcIus = $this->IcIus->deleteRecords($conditions);

                foreach ($rmIus as $iusNidValue) {

                    $conditions = [];
                    $conditions = [_IUS_INDICATOR_NID . ' IN ' => $iNid, _IUS_IUSNID . ' IN ' => $iusNidValue];

                    $remIus = $this->IndicatorUnitSubgroup->deleteRecords($conditions); //delete from ius table
                    if ($remIus > 0) {
                        $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _IUSDATA, $iusNidValue, _DONE, '', '', '', '', _MSG_IUS_DELETION);
                    }

                    /*
                      if($remIcIus>0){
                      $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _MODULE_NAME_ICIUS, $iusNidValue, _DONE, '', '', '', '', '');
                      }
                      if($remdata>0){
                      $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _DATA_TRANSAC, $iusNidValue, _DONE, '', '', '', '', '');
                      }
                     */
                }
            }
        }

        $insertSg = $insertUnits = [];
        $dbSgArr = array_unique($dbSgArr);
        $dbUniArr = array_unique($dbUniArr);
        $insertUnits = array_diff($unitNids, $dbUniArr);
        $insertSg = array_diff($subgrpNids, $dbSgArr);
        //when Unit Is NOT Empty and subgroup Empty
        if (!empty($insertUnits) && empty($insertSg)) {
            $this->insertIUSdata($iNid, $insertUnits, $subgrpNids, $iName);
        }
        //when Unit Is  Empty and subgroup NOT Empty
        if (empty($insertUnits) && !empty($insertSg)) {
            $this->insertIUSdata($iNid, $unitNids, $insertSg, $iName);
        }

        //when Unit Is NOT Empty and subgroup NOT Empty
        if (!empty($insertUnits) && !empty($insertSg)) {
            $this->insertIUSdata($iNid, $unitNids, $insertSg, $iName);

            $oldsgNUnits = array_diff($subgrpNids, $insertSg); //bind Old sgs with New Units
            $this->insertIUSdata($iNid, $insertUnits, $oldsgNUnits, $iName);
        }

        //when Unit Is  Empty and subgroup  Empty
        if (empty($insertUnits) && empty($insertSg)) {
            // nothing					
        }
    }

    /*
     * method to validate indicator data 
     * $fieldsArray posted data 
     */

    public function validateIndicatordata($fieldsArray = []) {

        $gid = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID])) ? trim($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID]) : '';
        $indName = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME])) ? trim($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME]) : '';
        $iNid = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID])) ? $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID] : '';

        if (empty($indName)) {
            return ['error' => _ERR141]; //indName emty
        } else {
            $validlength = $this->CommonInterface->checkBoundaryLength($indName, _INDNAME_LENGTH);
            if ($validlength == false) {
                return ['error' => _ERR193];  // indName length less than defined length 255
            }
            /*
              $chkAllowchar = $this->CommonInterface->allowAlphaNumeric($indName);
              if($chkAllowchar==false){
              return ['error' => _ERR146]; //allow only space and [0-9 or a-z]
              }
             */
            $checkname = $this->checkIndicatorName($indName, $iNid);
            if ($checkname == false) {
                return ['error' => _ERR138]; // name  exists 
            }
        }

        if (empty($gid)) {
            if ($iNid == '')
                $gid = $this->CommonInterface->guid();
        }else {

            $validgidlength = $this->CommonInterface->checkBoundaryLength($gid, _GID_LENGTH);
            if ($validgidlength == false) {
                return ['error' => _ERR190];  // gid length 50
            }
            $validGid = $this->Common->validateGuid($gid);
            if ($validGid == false) {
                return ['error' => _ERR142];  // gid invalid characters 
            }

            $checkGid = $this->checkGid($gid, $iNid);
            if ($checkGid == false) {
                return ['error' => _ERR137];  // gid  exists 
            }
        }
    }

    /*
     * method to add/ modify the indicator data  
     * @$fieldsArray array contains posted data 
     */

    public function manageIndicatorData($fieldsArray = []) {

        $indOrderNo = 0; //_INDICATOR_INDICATOR_ORDER 
        $metCatNid = '';
        $unitNids = $fieldsArray['unitNids'];     // posted unit nids
        $subgrpNids = $fieldsArray['subgrpNids']; // posted sub grp  nids
        $metadataArray = [];
        $metadataArray = json_decode($fieldsArray['metadataArray'], true);// posted metadataArray

        unset($fieldsArray['subgrpNids']);
        unset($fieldsArray['unitNids']);
        unset($fieldsArray['metadataArray']);

        if (isset($fieldsArray['indicatorDetails'])) {
            $validateIndicator = $this->validateIndicatordata($fieldsArray); //validate indicator details
            if (isset($validateIndicator['error'])) {
                return ['error' => $validateIndicator['error']];
            }
        } else {
            return ['error' => _ERR141]; //indName emty
        }
        $gid = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID])) ? trim($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID]) : '';
        $indName = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME])) ? ucfirst(trim($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME])) : '';
        $iNid = (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID])) ? $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID] : '';


        if (empty($gid)) {
            if ($iNid == '')
                $gid = $this->CommonInterface->guid();
        }
        //

        $validMetacategory = $this->validateMetadata($metadataArray);
        if (isset($validMetacategory['error'])) {
            return ['error' => $validMetacategory['error']];
        }
		$fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME] = $indName;
            

        if (empty($iNid)) {
            $action = _INSERT; //
            $indOrderNo = $this->getOrderno();
            $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_ORDER] = $indOrderNo + 1;
            $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GLOBAL] = '0';
            $fieldsArray['indicatorDetails'][_INDICATOR_DATA_EXIST] = '0';
            $fieldsArray['indicatorDetails'][_INDICATOR_HIGHISGOOD] = '0';
            $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID] = $gid;
            $returniNid = $this->insertData($fieldsArray['indicatorDetails'], 'nid'); //  nid parameter to get last inserted id of indicator 
            $lastNid = $returniNid; // last inserted ind nid
            $this->insertIUSdata($returniNid, $unitNids, $subgrpNids, $indName);

            $catNid = $this->manageCategory($metadataArray, $returniNid);
            $olddataValue = '';
        } else {

            $action = _UPDATE; //
            //GET INDICATOR DETAILS BY ID 
            $olddataValue = $this->getpreviousDetails($iNid);

            $data = $dbUniArr = $dbSgArr = [];
            $data = $this->getExistCombination($iNid);
            $dbUniArr = (isset($data['uniArr'])) ? $data['uniArr'] : '';
            $dbSgArr = (isset($data['sgArr'])) ? $data['sgArr'] : '';
            $dbiusNidsArr = (isset($data['iusNidsArr'])) ? $data['iusNidsArr'] : '';  //pr($dbiusNidsArr);

            if (!empty($dbSgArr) || !empty($dbUniArr)) {
                // ///manage ius data 
                $this->manageIusData($dbSgArr, $dbUniArr, $dbiusNidsArr, $unitNids, $subgrpNids, $iNid, $indName); //$fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NAME]
                ///manage ius data 
            } else {
                $this->insertIUSdata($iNid, $unitNids, $subgrpNids, $indName);
            }
			
			$conditions = [];
            $conditions[_INDICATOR_INDICATOR_NID] = $iNid;

            if (isset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID]) && !empty($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID]))
                $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID] = $gid;
            else
                unset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID]);

            unset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID]);
            $returniNid = $this->updateRecords($fieldsArray['indicatorDetails'], $conditions); // update indicator details
            $lastNid = $iNid; //updated  ind nid

            $catNid = $this->manageCategory($metadataArray, $iNid);
        }
        if ($returniNid > 0) {
            $status = _DONE;
            $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _INDICATOR, $lastNid, $status, '', '', $olddataValue, $indName, '');
            return true;
        } else {
            $status = _FAILED;
            $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _INDICATOR, $lastNid, $status, '', '', $olddataValue, $indName, _ERR_TRANS_LOG);
            return ['error' => _ERR100]; //server error 
        }
    }

    /*
      returns count of no of specific names  meta category
     */

    function getMetacategoryName($metadataArray) {
        $categoryName = [];
        $cnt = 0;
        if (isset($metadataArray) && !empty($metadataArray)) {
            foreach ($metadataArray as $value) {
                //validate subgroup val details 
                $value['category'] = trim($value['category']);
                $categoryName[$cnt] = (isset($value['category'])) ? trim($value['category']) : '';
                $cnt++;
            }
            return ['categoryName' => array_count_values($categoryName)];
        }
    }

    /*
      method to validate the metadata category name
     */

    function validateMetadata($metadataArray) {
        ///
        if (isset($metadataArray) && !empty($metadataArray)) {
            $postedCategories = $this->getMetacategoryName($metadataArray);

            foreach ($metadataArray as $value) {

                $value['category'] = trim($value['category']);
                if ($postedCategories['categoryName'][$value['category']] > 1) {
                    return ['error' => _ERR144];  // category already exists
                }
                $validlengthcat = $this->CommonInterface->checkBoundaryLength($value['category'], _METACATEGORY_LENGTH);
                if ($validlengthcat == false) {
                    return ['error' => _ERR194];  //  Metadata category lengthless than 255 
                }
                $metCatNid = (isset($value['nId'])) ? $value['nId'] : "";
                if (!empty($metCatNid)) {
                    $checkCatName = $this->checkCategoryName($value['category'], $metCatNid);
                    if ($checkCatName != false) {
                        return ['error' => _ERR144];  // category already exists 
                    }
                }
            }
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
    public function getIndicatorById($iuNid = '') {

        $iNid = $uNid = $explodeIu = '';
        $metaData = $iDetails = [];

        if (isset($iuNid) && !empty($iuNid)) {
            $explodeIu = explode(_DELEM1, $iuNid);
            $iNid = $explodeIu[0];
            $uNid = $explodeIu[1];

            $conditions = $fields = $allrec = [];

            $ius = $this->IndicatorUnitSubgroup->getIndicatorSpecificUSDetails($iNid, $uNid);

            if (isset($ius) && !empty($ius)) {
                foreach ($ius as $value) {
                    $iDetails['iName'] = $value['indicator'][_INDICATOR_INDICATOR_NAME];
                    $iDetails['iGid'] = $value['indicator'][_INDICATOR_INDICATOR_GID];
                    $iDetails['iNid'] = $value['indicator'][_INDICATOR_INDICATOR_NID];
                    $uNids[$value['unit'][_UNIT_UNIT_NID]]['id'] = (string) $value['unit'][_UNIT_UNIT_NID];  //_UNIT_UNIT_NAME,_UNIT_UNIT_GID
                    $sNids[$value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID]]['id'] = (string) $value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_NID];
                }
                $iDetails['uNid'] = array_values($uNids);
                $iDetails['sNid'] = array_values($sNids);
                $metaData = $this->Metadata->getMetaDataDetails($iNid);
                $iDetails['metadata'] = $metaData;
            }
        }


        return $iDetails;
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
     *  method to break data into chunk for sql server compatability
     */
    public function getChunkedData() {
        $conditions = [];
        $fields = [_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NID];
        $extra['order'] = [_INDICATOR_INDICATOR_NAME => 'ASC'];

        $data = $this->getRecords($fields, $conditions, 'list', $extra);

        if (count($data) > 1000) {

            $chunkedarray = array_chunk($data, 1000);
            $indDataarray = [];
            foreach ($chunkedarray as $indNids) {
                $ius = $this->IndicatorUnitSubgroup->getIndicatorSpecificUSDetails($indNids, '');

                return $indDataarray = array_merge($indDataarray, $ius);
            }
        } else {
            return $ius = $this->IndicatorUnitSubgroup->getIndicatorSpecificUSDetails($data);
        }
    }

    /**
     * export the indicator details to excel 
     */
    public function exportIndicatorDetails($status, $dbId = '') {

        $width = 50;
        $dbId = (isset($dbId)) ? $dbId : '';
        $dbDetails = $this->Common->parseDBDetailsJSONtoArray($dbId);
        $dbConnName = $dbDetails['db_connection_name'];
        $dbConnName = str_replace(' ', '-', $dbConnName);
        $resultSet = [];

        if ($status == true) {
            $resultSet = $this->getChunkedData();
        } else {
            $conditions = [];
            $fields = [_INDICATOR_INDICATOR_GID, _INDICATOR_INDICATOR_NAME];
            $extra['order'] = [_INDICATOR_INDICATOR_NAME => 'ASC'];
            $resultSet = $this->getRecords($fields, $conditions, 'all', $extra);
        }

        $authUserId = $this->Auth->User('id');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        // $returnFilename = $dbConnName. _DELEM4 . _MODULE_NAME_UNIT ._DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = $dbConnName . _DELEM4 . _INDICATOREXPORT_FILE . _DELEM4 . date('Y-m-d-H-i-s') . '.xls';
        $returnFilename = str_replace(' ', '-', $returnFilename);
        $rowCount = 1;
        $firstRow = ['A' => 'Indicator Details'];
        $styleArray = array(
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 20,
                'name' => 'Arial',
        ));

        foreach ($firstRow as $index => $value) {

            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value)->getColumnDimension($index)->setWidth($width);
            $objPHPExcel->getActiveSheet()->getStyle($index . $rowCount)->applyFromArray($styleArray);
        }

        $rowCount = 3;
        if ($status == true) {
            $secRow = ['A' => 'Indicator Name', 'B' => 'Indicator Gid', 'C' => 'Unit Name', 'D' => 'Unit Gid', 'E' => 'Subgroup Name', 'F' => 'Subgroup Gid'];
        } else {
            $secRow = ['A' => 'Indicator Name', 'B' => 'Indicator Gid'];
        }
        //     $objPHPExcel->getActiveSheet()->getStyle("A$rowCount:B$rowCount")->getFont()->setItalic(true);

        foreach ($secRow as $index => $value) {
            $objPHPExcel->getActiveSheet()->getStyle("$index$rowCount")->getFont()->setItalic(true);
            $objPHPExcel->getActiveSheet()->SetCellValue($index . $rowCount, $value);
        }

        $returndata = $data = [];

        $startRow = 6;
        if (!empty($resultSet)) {

            foreach ($resultSet as $index => $value) {

                if ($status == true) {
                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value['indicator'][_INDICATOR_INDICATOR_NAME])) ? $value['indicator'][_INDICATOR_INDICATOR_NAME] : '' )->getColumnDimension('A')->setWidth($width);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value['indicator'][_INDICATOR_INDICATOR_GID])) ? $value['indicator'][_INDICATOR_INDICATOR_GID] : '')->getColumnDimension('B')->setWidth($width);

                    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $startRow, (isset($value['unit'][_UNIT_UNIT_NAME])) ? $value['unit'][_UNIT_UNIT_NAME] : '')->getColumnDimension('C')->setWidth($width);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $startRow, (isset($value['unit'][_UNIT_UNIT_GID])) ? $value['unit'][_UNIT_UNIT_GID] : '')->getColumnDimension('D')->setWidth($width);

                    $objPHPExcel->getActiveSheet()->SetCellValue('E' . $startRow, (isset($value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL])) ? $value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL] : '')->getColumnDimension('E')->setWidth($width);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $startRow, (isset($value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID])) ? $value['subgroup_val'][_SUBGROUP_VAL_SUBGROUP_VAL_GID] : '')->getColumnDimension('F')->setWidth($width);
                } else {

                    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $startRow, (isset($value[_INDICATOR_INDICATOR_NAME])) ? $value[_INDICATOR_INDICATOR_NAME] : '11' )->getColumnDimension('A')->setWidth($width);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $startRow, (isset($value[_INDICATOR_INDICATOR_GID])) ? $value[_INDICATOR_INDICATOR_GID] : '22')->getColumnDimension('B')->setWidth($width);
                }

                $startRow++;
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $saveFile = _INDICATOR_PATH . DS . $returnFilename;
        $saved = $objWriter->save($saveFile);
        return $saveFile;
    }

    /*
     * 
     * method to get total no of indicators 
     */

    public function getIndicatorsCount($conditions = []) {

        $count = 0;
        return $count = $this->IndicatorObj->getCount($conditions);
    }

    /*
     * 
     * method to  get previous Details of indicator before update 
     */

    public function getpreviousDetails($iNid = '') {
        $conditions = [_INDICATOR_INDICATOR_NID => $iNid];
        $fields = [_INDICATOR_INDICATOR_GID, _INDICATOR_INDICATOR_NAME];
        $IndiOldValue = $this->getRecords($fields, $conditions);
        return current($IndiOldValue)[_INDICATOR_INDICATOR_NAME];
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
