<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * SubgroupType Component
 */
class SubgroupTypeComponent extends Component {

    // The other component your component uses
    public $SubgroupTypeObj = NULL;
    public $components = [
        'Auth', 'UserAccess', 'MIusValidations',
        'TransactionLogs', 'DevInfoInterface.CommonInterface',
        'DevInfoInterface.IndicatorClassifications',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Indicator',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.Data',
        'DevInfoInterface.IcIus', 'Common'
    ];

    public function initialize(array $config) {
        parent::initialize($config);
        $this->SubgroupTypeObj = TableRegistry::get('DevInfoInterface.SubgroupType');
    }

    /**
     * Get records based on conditions
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->SubgroupTypeObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = []) {
        return $this->SubgroupTypeObj->deleteRecords($conditions);
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
     */
    public function insertData($fieldsArray = []) {
        // return $this->SubgroupTypeObj->insertData($fieldsArray);
        return $this->SubgroupTypeObj->insertData($fieldsArray);
    }

    /**
     * Insert multiple rows at once (runs single query for multiple records)
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($insertDataArray = []) {
        return $this->SubgroupTypeObj->insertOrUpdateBulkData($insertDataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->SubgroupTypeObj->updateRecords($fieldsArray, $conditions);
    }

    /*
      method to get the subgroup type  list
      @sgIds is array of sub group nids

     */

    function getsgNids($typeNid = '') {
        $fields = [_SUBGROUP_SUBGROUP_NID, _SUBGROUP_SUBGROUP_NID];
        $conditions = [_SUBGROUP_SUBGROUP_TYPE . ' IN ' => $typeNid];
        $resultSgIds = $this->Subgroup->getRecords($fields, $conditions, 'list');
        return $resultSgIds;
    }

    /*
      method to get the subgroup val nids list
      @sgValNids='' is array  of sub val nids

     */

    function getsgValNids($sgIds = []) {

        $fields = [_SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID, _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID];
        $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
        $sgValNids = $this->SubgroupValsSubgroup->getRecords($fields, $conditions, 'list');

        return $sgValNids;
    }

    /*
      delete subgroup and its corresponding details
      @sgId is the subgroup nid
     */

    public function deleteSubgroupdata($sgId = '') {

        
        $return = false;
        $status = _FAILED;
        $olddataValue = '';
        if ($sgId) {

            $sgData = $this->getSubgroupName($sgId);
            if (!empty($sgData)) {

                $olddataValue = $sgData[0][_SUBGROUP_SUBGROUP_NAME];
                if (isset($olddataValue) && !empty($olddataValue)) {
                    // get subgroup vals subgroups  records
                    $sgvalsgIds = $this->getsgValNids([$sgId]); //get subgroup val nids

                    $conditions = $fields = [];
                    $fields = [_IUS_IUSNID, _IUS_IUSNID];
                    $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
                    $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');

                    //delete them  from sg 			
                    $conditions = [];
                    $conditions = [_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgId];
                    $rsltsgId = $this->Subgroup->deleteRecords($conditions);

                    if ($rsltsgId > 0) {

                        $conditions = [];
                        $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
                        $rslt = $this->SubgroupVals->deleteRecords($conditions);

                        $conditions = [];

                        $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgId]; //CHECK AGAIN THIS 
                        $rslt = $this->SubgroupValsSubgroup->deleteRecords($conditions);

                        //deleete ius     
                        $conditions = [];
                        $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
                        $rslt = $this->IndicatorUnitSubgroup->deleteRecords($conditions);

                        //deleete data    

                        $conditions = [];
                        $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalsgIds];
                        $rslt = $this->Data->deleteRecords($conditions);

                        if (count($getIusNids) > 0) {
                            $conditions = [];
                            $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];
                            //deleete icius    
                            $rslt = $this->IcIus->deleteRecords($conditions);
                        }
                        $status = _DONE;
                        $errordesc = _MSG_SUBGROUPTYPEVALUE_DELETION;
                        $return = true;
                    } else {
                        
                        $errordesc = _ERR_TRANS_LOG;
                       
                    }
                } else {
                    
                    $errordesc = _ERR_RECORD_NOTFOUND;
                    
                }
            } else {
               
                $errordesc = _ERR_RECORD_NOTFOUND;
               
            }
        } else {
            
            $errordesc = _ERR_INVALIDREQUEST;
            
        }
        $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _SUBGROUP, $sgId, $status, '', '', $olddataValue, '', $errordesc);
       
        return $return;
    }

    /*
      delete subgroup type and its corresponding details
      @nid is the subgroup type nid
     */

    public function deleteSubgroupTypedata($nId = '') {
        
        $status = _FAILED;
        $return = false;
        $olddataValue = '';
        if ($nId) {
            // delete data 

            $sgTypeData = $this->getSubgroupTypeList([_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $nId]);
            if (!empty($sgTypeData)) {

                $olddataValue = current($sgTypeData)['name'];
                if (isset($olddataValue) && !empty($olddataValue)) {
                    $sgIds = $this->getsgNids($nId);          //get subgroup nids
                    // get subgroup vals subgroups  records 			
                    $sgvalsgIds = $this->getsgValNids($sgIds); //get subgroup val nids

                    $conditions = $fields = [];
                    $fields = [_IUS_IUSNID, _IUS_IUSNID];
                    $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
                    $getIusNids = $this->IndicatorUnitSubgroup->getRecords($fields, $conditions, $type = 'list');

                    //delete them  from sg type			
                    $conditions = [];
                    $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' IN ' => $nId];
                    $sgtype = $this->deleteRecords($conditions);

                    if ($sgtype > 0) {

                        //delete them  from sg 			
                        $conditions = [];
                        $conditions = [_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
                        $data = $this->Subgroup->deleteRecords($conditions);

                        //delete them  from sg val

                        $conditions = [];
                        $conditions = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
                        $data = $this->SubgroupVals->deleteRecords($conditions);

                        //delete them  from sg val sg val 

                        $conditions = [];
                        $conditions = [SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID . ' IN ' => $sgIds];
                        $data = $this->SubgroupValsSubgroup->deleteRecords($conditions);

                        //deleet ius     

                        $conditions = [];
                        $conditions = [_IUS_SUBGROUP_VAL_NID . ' IN ' => $sgvalsgIds];
                        $data = $this->IndicatorUnitSubgroup->deleteRecords($conditions);

                        //deleet ius  						 
                        $conditions = [];
                        $conditions = [_MDATA_SUBGRPNID . ' IN ' => $sgvalsgIds];
                        $data = $this->Data->deleteRecords($conditions);

                        if (count($getIusNids) > 0) {
                            $conditions = [];
                            $conditions = [_ICIUS_IUSNID . ' IN ' => $getIusNids];

                            $data = $this->IcIus->deleteRecords($conditions);
                        }


                        $errordesc = _MSG_SUBGROUPTYPE_DELETION;
                        $status = _DONE;
                         $return = true;
                    } else {
                        $errordesc = _ERR_TRANS_LOG;
                        
                    }
                } else {
                    $errordesc = _ERR_RECORD_NOTFOUND;
                 
                }
            } else {
                $errordesc = _ERR_RECORD_NOTFOUND;
                
            }
        } else {
            $errordesc = _ERR_INVALIDREQUEST;            
        }
         $this->TransactionLogs->createLog(_DELETE, _TEMPLATEVAL, _SUBGROUPTYPE, $nId, $status, '', '', $olddataValue, '', $errordesc);
                      
        
        return $return;
    }

    /*
     * check sg type name  in subgroup type  table 
     * return true or false
     */

    public function checkDmTypeName($sgTypeName = '', $sgtypeNid = '') {

        $conditions = $fields = [];
        $fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
        $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NAME => $sgTypeName];
        if (isset($sgtypeNid) && !empty($sgtypeNid)) {
            $extra[_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' !='] = $sgtypeNid;
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
     * check sg type gid  in subgroup type table 
     * return true or false
      @sgtypeGid gid
      @sgtypeNid  sg type nidd
     */

    public function checkDmTypeGid($sgtypeGid = '', $sgtypeNid = '') {

        $conditions = $fields = [];
        $fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
        $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_GID => $sgtypeGid];
        if (isset($sgtypeNid) && !empty($sgtypeNid)) {
            $extra[_SUBGROUPTYPE_SUBGROUP_TYPE_NID . ' !='] = $sgtypeNid;
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
     * check gid  exists in subgroup  table 
     * return true or false
      @sgGid gid
      @sgNid  sg  nid
     */

    public function checkGidSg($sgGid = '', $sgNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_SUBGROUP_NID];
        $conditions = [_SUBGROUP_SUBGROUP_GID => $sgGid];
        if (isset($sgNid) && !empty($sgNid)) {
            $extra[_SUBGROUP_SUBGROUP_NID . ' !='] = $sgNid;
            $conditions = array_merge($conditions, $extra);
        }

        $gidexits = $this->Subgroup->getRecords($fields, $conditions);

        if (!empty($gidexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }

    /*
     * check subgroup name  in subgroup  table 
     * return true or false
      @sgName sub group name
      @sgNid  sg  nid
     */

    public function checkNameSg($sgName = '', $sgNid = '') {
        $conditions = $fields = [];
        $fields = [_SUBGROUP_SUBGROUP_NID];
        $conditions = [_SUBGROUP_SUBGROUP_NAME => $sgName];
        if (isset($sgNid) && !empty($sgNid)) {
            $extra[_SUBGROUP_SUBGROUP_NID . ' !='] = $sgNid;
            $conditions = array_merge($conditions, $extra);
        }

        $nameexits = $this->Subgroup->getRecords($fields, $conditions);

        if (!empty($nameexits)) {
            return false; //already exists
        } else {
            return true;
        }
    }

    /*
     * method returns subgroup name 
      subgroup name   from subgroup table
      @sgNid  sg  nid
     */

    public function getSubgroupName($sgNid) {
        $name = [];
        if (isset($sgNid) && !empty($sgNid)) {

            $conditions = $fields = [];
            $fields = [_SUBGROUP_SUBGROUP_NID, _SUBGROUP_SUBGROUP_NAME];
            $conditions = [_SUBGROUP_SUBGROUP_NID => $sgNid];
            //   $extra[_SUBGROUP_SUBGROUP_NID] = $sgNid;
            //  $conditions = array_merge($conditions, $extra);

            $details = $this->Subgroup->getRecords($fields, $conditions);
        }
        return $details;
    }

    /*
     * 
     * get subgroup nid on basis of name 
     */

    public function getSubgroupNid($sgName = '') {
        $sgNid = [];
        if (isset($sgName) && !empty($sgName)) {
            $sgName = trim($sgName);
            $conditions = $fields = [];
            $fields = [_SUBGROUP_SUBGROUP_NID];
            $conditions = [_SUBGROUP_SUBGROUP_NAME => $sgName];
            //   $extra[_SUBGROUP_SUBGROUP_NID] = $sgNid;
            //  $conditions = array_merge($conditions, $extra);

            $sgNid = $this->Subgroup->getRecords($fields, $conditions);
        }
        return $sgNid;
    }

    /*
      method to add modify subgroup
      @ sgData array subgroup data
      @ sgTypeNid sg type nid
     */

    public function manageSubgroup($sgData = [], $sgTypeNid) {

        $orderNo = 0;
        $orderNo = $this->Subgroup->getMax(_SUBGROUP_SUBGROUP_ORDER, []);
        $orderNo = $orderNo + 1;
        if (isset($sgData) && !empty($sgData)) {
            foreach ($sgData as $value) {
				$errordesc = '';
				$status = _FAILED;
                $sgNid = '';
                $subgrpdetails = [];
                $sgNid = $value['nId'];
                $sgName = trim($value['val']);

                if ($sgName != '') {

                    if (isset($value['gId']) && !empty($value['gId']))
                        $subgrpdetails[_SUBGROUP_SUBGROUP_GID] = trim($value['gId']);

                    $subgrpdetails[_SUBGROUP_SUBGROUP_NAME] = $sgName;
                    $subgrpdetails[_SUBGROUP_SUBGROUP_NID] = $sgNid;
                    $subgrpdetails[_SUBGROUP_SUBGROUP_TYPE] = $sgTypeNid;

                    if (!empty($sgNid)) {

                        $action = _UPDATE;
                        $catConditions = [];
                        $catConditions = [_SUBGROUP_SUBGROUP_NID => $sgNid];

                        $oldsgname = $this->getSubgroupName($sgNid);
                        $olddataValue = $oldsgname[0][_SUBGROUP_SUBGROUP_NAME];

                        unset($subgrpdetails[_SUBGROUP_SUBGROUP_NID]);
                        //pr($subgrpdetails);
                        $lastId = $this->Subgroup->updateRecords($subgrpdetails, $catConditions); //update case
                        if ($lastId > 0) {
                            
                            $status = _DONE;
                        } else {
                            $errordesc = _ERR_TRANS_LOG;
                            
                        }
                        $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _SUBGROUP, $sgNid, $status, '', '', $olddataValue, $sgName, $errordesc);
                    } else {
                        $action = _INSERT;
                        $subgrpdetails[_SUBGROUP_SUBGROUP_GID] = (isset($value['gId']) && !empty($value['gId'])) ? trim($value['gId']) : $this->CommonInterface->guid();
                        $subgrpdetails[_SUBGROUP_SUBGROUP_ORDER] = $orderNo;
                        $subgrpdetails[_SUBGROUP_SUBGROUP_GLOBAL] = '0';
                        //pr($subgrpdetails);
                        $lastId = $this->Subgroup->insertData($subgrpdetails);

                        if ($lastId > 0) {                          
                            $status = _DONE;
                        } else {
                            $errordesc = _ERR_TRANS_LOG;                           
                        }
                        $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _SUBGROUP, $lastId, $status, '', '', '', $sgName, $errordesc);


                        $orderNo++;
                    }
                }
            }
        }
    }

    /*
     * method used in validation of  posted data 
      returns  array of name and gids with their counts
     */

    function getNameGids($subgroupValData) {
        $sName = $sGid = [];
        $cnt = 0;

        foreach ($subgroupValData as $value) {

            //validate subgroup val details 
            $sName[$cnt] = (isset($value['val'])) ? trim($value['val']) : ''; //sbgrp  name   
            $sGid[$cnt] = (isset($value['gId'])) ? trim($value['gId']) : '';  //sbgrp gid 
            $cnt++;
        }
        return $data = ['sName' => array_count_values($sName), 'sGid' => array_count_values($sGid)];
    }

    /*
     * 
     * method to validate the input data 
     */

    public function validateInputDetails($subgroupData) {

        if (isset($subgroupData) && !empty($subgroupData)) {
            $sgTypeNid = (isset($subgroupData['nId'])) ? $subgroupData['nId'] : '';
            $subgroupData['dName'] = trim($subgroupData['dName']);
            /// validate sg dimension  
            if (empty($subgroupData['dName'])) {
                return ['error' => _ERR147]; //sg type  empty
            } else {

                $validlength = $this->CommonInterface->checkBoundaryLength($subgroupData['dName'], _SGTYPENAME_LENGTH); //128 only
                if ($validlength == false) {
                    return ['error' => _ERR198];  // sbgrp  type name  length 
                }

                $sgTypeName = $this->checkDmTypeName($subgroupData['dName'], $sgTypeNid); //check subgrpType name 
                if ($sgTypeName == false) {
                    return ['error' => _ERR149]; //type name already exists 
                }

                if (empty($subgroupData['dGid'])) {
                    if ($sgTypeNid == '')
                        $subgroupData['dGid'] = $this->CommonInterface->guid();
                }else {

                    $validgidlength = $this->CommonInterface->checkBoundaryLength(trim($subgroupData['dGid']), _GID_LENGTH);
                    if ($validgidlength == false) {
                        return ['error' => _ERR190];  // gid length 
                    }

                    $sgTypeGid = $this->checkDmTypeGid(trim($subgroupData['dGid']), $sgTypeNid); //check subgrpType gId 
                    if ($sgTypeGid == false) {
                        return ['error' => _ERR137]; //gid already exists
                    }

                    $validGid = $this->Common->validateGuid(trim($subgroupData['dGid']));



                    if ($validGid == false) {
                        return ['error' => _ERR142];  // gid emty
                    }
                }
            }

            /// validate sg dimension value 

            if (isset($subgroupData['dValues']) && !empty($subgroupData['dValues'])) {

                $posetdNameandGid = $this->getNameGids($subgroupData['dValues']);
                $posetdsName = $posetdNameandGid['sName']; //no of duplicate  sname  
                $posetdsGid = $posetdNameandGid['sGid']; // no of duplicate sgid 

                foreach ($subgroupData['dValues'] as $value) {

                    $sgNameval = trim($value['val']);
                    if (empty($sgNameval)) {
                        // return ['error' => _ERR148]; //sg name is  empty
                    } else {
                        //
                        $validlengthsg = $this->CommonInterface->checkBoundaryLength($sgNameval, _SGNAME_LENGTH); //128 only
                        if ($validlengthsg == false) {
                            return ['error' => _ERR199];  // sbgrp  name  length 
                        }

                        if ($posetdsName[$sgNameval] > 1) {
                            return ['error' => _ERR175]; // sg name  already exists
                        }

                        $sgName = $this->checkNameSg($sgNameval, $value['nId']);
                        if ($sgName == false) {
                            return ['error' => _ERR150]; // sg name  already exists
                        }
                    }
                    $value['gId'] = (isset($value['gId'])) ? trim($value['gId']) : '';
                    if (empty($value['gId'])) {
                        // nothing 
                    } else {
                        $validgidlength = $this->CommonInterface->checkBoundaryLength($value['gId'], _GID_LENGTH);
                        if ($validgidlength == false) {
                            return ['error' => _ERR190];  // gid length 
                        }
                        if ($posetdsGid[$value['gId']] > 1) {
                            return ['error' => _ERR137]; // sg name  already exists
                        }
                        $sgGid = $this->checkGidSg($value['gId'], $value['nId']);
                        if ($sgGid == false) {
                            return ['error' => _ERR137]; //already exists sg  gid 
                        }

                        $validGidsg = $this->Common->validateGuid($value['gId']);
                        if ($validGidsg == false) {
                            return ['error' => _ERR142];  // gid emty
                        }
                    }
                }
            }
        }

        ///
    }

    /*
      method  to add modify the subgroup type
      @subgroupData array
     */

    public function manageSubgroupTypeData($subgroupData = []) {

        $subgrpTypedetails = [];
        $subgroupData = json_decode($subgroupData['subgroupData'], true);

        if (isset($subgroupData) && !empty($subgroupData)) {
            // check sg type name 
            //
            $sgTypeNid = (isset($subgroupData['nId'])) ? $subgroupData['nId'] : '';
            $newValue = $subgroupData['dName'] = trim($subgroupData['dName']);

            //validation starts here 
            $validateReturn = $this->validateInputDetails($subgroupData); //method to validate input details
            if (isset($validateReturn['error'])) {
                return ['error' => $validateReturn['error']];
            }
            $subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME] = $subgroupData['dName'];
            $subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NID] = $sgTypeNid;


            if (isset($subgroupData['nId']) && !empty($subgroupData['nId'])) {
                // modify 	
                $action = _UPDATE; //					

                if (isset($subgroupData['dGid']) && !empty($subgroupData['dGid']))
                    $subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_GID] = $subgroupData['dGid'];

                $catConditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $sgTypeNid];
                $lastNid = $sgTypeNid;
                $oldfields = [ _SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
                $dataOld = $this->getRecords($oldfields, $catConditions);
                unset($subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_NID]);
                $result = $this->updateRecords($subgrpTypedetails, $catConditions); //update case 
                $this->manageSubgroup($subgroupData['dValues'], $sgTypeNid);
                $errordesc = '';

                $olddataValue = $dataOld[0][_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
                //
            } else {

                $action = _INSERT; //

                $subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_GID] = (isset($subgroupData['dGid']) && !empty($subgroupData['dGid'])) ? $subgroupData['dGid'] : $this->CommonInterface->guid();
                $orderNo = $this->getMax(_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER, []);
                $subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER] = $orderNo + 1;
                $subgrpTypedetails[_SUBGROUPTYPE_SUBGROUP_TYPE_GLOBAL] = '0';
                $errordesc = '';
                $olddataValue = '';
                $result = $sgTypeNid = $this->insertData($subgrpTypedetails);
                $lastNid = $result;
                $this->manageSubgroup($subgroupData['dValues'], $sgTypeNid);
                //Subgroup_Global
            }
            //$returnData =['dName'=> $subgroupData['dName'],'id'=>$sgTypeNid];
            if ($result > 0) {
                $status = _DONE;
                $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _SUBGROUPTYPE, $lastNid, $status, '', '', $olddataValue, $newValue, $errordesc);
                return ['success' => true, 'name' => $subgroupData['dName'], 'id' => $sgTypeNid];
            } else {
                $status = _FAILED;
                $errordesc = _ERR_TRANS_LOG;
                $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _SUBGROUPTYPE, $lastNid, $status, '', '', $olddataValue, $newValue, $errordesc);
                return ['error' => _ERR100]; //server error 
            }
        }
    }

    /*

      method to get the all subgroup type  details
      return array
     */

    public function getSubgroupTypeList($conditions = []) {
        $data = [];
        $sgType = [];
        $fields = [ _SUBGROUPTYPE_SUBGROUP_TYPE_NID,
            _SUBGROUPTYPE_SUBGROUP_TYPE_GID,
            _SUBGROUPTYPE_SUBGROUP_TYPE_NAME];

        $data = $this->getRecords($fields, $conditions);
        if (!empty($data)) {

            foreach ($data as $index => $value) {
                $sgType[$index]['nId'] = $value[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
                $sgType[$index]['gid'] = $value[_SUBGROUPTYPE_SUBGROUP_TYPE_GID];
                $sgType[$index]['name'] = $value[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
            }
        }
        return $sgType;
    }

    /*

      method to get the subgroup type with subgroup details
     * sgTypeNid subgroup type nid 
      return array
     */

    public function getSubgroupTypeDetailsById($sgTypeNid = '') {

        $data = [];
        $sgType = [];

        $fields = [ _SUBGROUPTYPE_SUBGROUP_TYPE_NID,
            _SUBGROUPTYPE_SUBGROUP_TYPE_GID,
            _SUBGROUPTYPE_SUBGROUP_TYPE_NAME];
        $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $sgTypeNid];
        $data = $this->getRecords($fields, $conditions);

        if (!empty($data)) {

            foreach ($data as $value) {

                $sgType['nId'] = $value[_SUBGROUPTYPE_SUBGROUP_TYPE_NID];
                $sgType['dGid'] = $value[_SUBGROUPTYPE_SUBGROUP_TYPE_GID];
                $sgType['dName'] = $value[_SUBGROUPTYPE_SUBGROUP_TYPE_NAME];

                $fields1 = [ _SUBGROUP_SUBGROUP_NAME, _SUBGROUP_SUBGROUP_NID, _SUBGROUP_SUBGROUP_TYPE, _SUBGROUP_SUBGROUP_GID];
                $conditions1 = [_SUBGROUP_SUBGROUP_TYPE => $sgTypeNid];
                $sgdata = $this->Subgroup->getRecords($fields1, $conditions1);

                foreach ($sgdata as $ind => $value) {
                    $sgType['dValues'][$ind]['nId'] = $value[_SUBGROUP_SUBGROUP_NID];
                    $sgType['dValues'][$ind]['gId'] = $value[_SUBGROUP_SUBGROUP_GID];
                    $sgType['dValues'][$ind]['val'] = $value[_SUBGROUP_SUBGROUP_NAME];
                }
            }
        }

        return $sgType;
    }

    /**
     * to get the highest value
     * 
     */
    public function getMax($column = '', $conditions = []) {
        return $this->SubgroupTypeObj->getMax($column, $conditions);
    }

    /*
     * method to modify the order of subgroup dimensions 
     * @orderData posted data of subgrp type nids with their new order
     * @dbId is the database Id 
     */

    public function manageSubgroupTypeOrder($orderData) {
        if (isset($orderData)) {
            // prepare sg type order data to get difference 
            $sbgrpTypeOrder = [];
            foreach ($orderData as $value) {
                if (isset($value['nId']) && !empty($value['nId']))
                    $sbgrpTypeOrder[$value['nId']] = (isset($value['index'])) ? $value['index'] : '';
            }

            // get all existing records 
            $sgType = [];
            $fields = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID, _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER];
            $conditions = [];
            $getSgTypeOrders = $this->getRecords($fields, $conditions, 'list');

            //get diff between posted and existing one 
            $diffDataOrder = array_diff_assoc($sbgrpTypeOrder, $getSgTypeOrders);
            $returndata = [];
            if (!empty($diffDataOrder) && count($diffDataOrder) > 0) {
                $resultset = [];
                $cnt = 0; //store this array in job database  
                foreach ($diffDataOrder as $nid => $order) {
                    $resultset[$cnt]['nId'] = $nid;
                    $resultset[$cnt]['index'] = $order;
                    $conditions = [];
                    $updatefields = [_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => $order];
                    $conditions = [_SUBGROUPTYPE_SUBGROUP_TYPE_NID => $nid];
                    $result = $this->updateRecords($updatefields, $conditions); //update order 
                    $cnt++;
                }
                $returndata['resultset'] = $resultset;
            }
            $status = _DONE;
            $errordesc = '';
            $action = _MOVEORDER;
            $this->TransactionLogs->createLog($action, _TEMPLATEVAL, _SUBGROUPTYPE, '', $status, '', '', '', '', $errordesc);

            $returndata['status'] = true;
            return $returndata;
        }
    }

    /*
      public function getOrderno(){

      $query = $this->SubgroupTypeObj->find();
      $result = $query->select(['max' => $query->func()->max(_SUBGROUPTYPE_SUBGROUP_TYPE_ORDER),
      ])->hydrate(false)->toArray();
      return $result = current($result)['max'];

      }
     */

    /**
     * - For DEVELOPMENT purpose only
     * Test method to do anything based on this model (Run RAW queries or complex queries)
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->SubgroupTypeObj->testCasesFromTable($params);
    }

}
