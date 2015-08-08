<?php
namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Indicator Component
 */
class IndicatorComponent extends Component 
{

    // The other component your component uses
    public $components = ['TransactionLogs',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Data',
        'DevInfoInterface.CommonInterface'];
    public $IndicatorObj = NULL;

    public function initialize(array $config)
    {
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
    public function getRecords(array $fields, array $conditions, $type = 'all')
    {
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
    public function deleteRecords($conditions = [])
    {
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
            $conditions = [_IUS_UNIT_NID . ' IN ' => $uNid];
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
     * check name if name exists in unit table or not
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
     * check gid if exists in unit table or not
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
    
    function addIUSdata($iNid,$unitNids,$subgrpNids){
        
        foreach($unitNids  as $uNid){
            foreach($subgrpNids  as $sNid){
                $fieldsArray = [];
                $fieldsArray = [_IUS_INDICATOR_NID=>$iNid,_IUS_UNIT_NID=>$uNid,_IUS_SUBGROUP_VAL_NID=>$sNid];
                $return = $this->IndicatorUnitSubgroup->insertData($fieldsArray);
              }
        }
    }
    
    function getExistCombination($iNid,$unitNids,$subgrpNids){
        $fields     = [_IUS_INDICATOR_NID,_IUS_UNIT_NID,_IUS_SUBGROUP_VAL_NID];
        $conditions = [_IUS_INDICATOR_NID.' IN ' =>$iNid,_IUS_UNIT_NID.' IN ' =>$unitNids,_IUS_SUBGROUP_VAL_NID.' IN ' =>$subgrpNids];
        $iusdetails = $this->IndicatorUnitSubgroup->getRecords($fields,$conditions );
        foreach($iusdetails as $iusNids){
            $indArr[] = $iusNids[_IUS_INDICATOR_NID];
            $uniArr[] = $iusNids[_IUS_UNIT_NID];
            $sgArr[]  = $iusNids[_IUS_SUBGROUP_VAL_NID];
        }
        return ['indArr'=>$indArr,'uniArr'=>$uniArr,'sgArr'=>$sgArr];
    }
    
    
    public function manageIndicatorData($fieldsArray=[]){
        $unitNids = $fieldsArray['unitNids'];   
        $subgrpNids = $fieldsArray['subgrpNids'];
        unset($fieldsArray['subgrpNids']); 
        unset($fieldsArray['unitNids']);
        $gid = $fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_GID];
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
            
            $return = $this->insertData($fieldsArray['indicatorDetails']);
            $this->addIUSdata($return,$unitNids,$subgrpNids);
           
        } else {
            $conditions[_INDICATOR_INDICATOR_NID] = $iNid;
            $data =  $this->getExistCombination($iNid,$unitNids,$subgrpNids);
            $diffUnits = array_intersect($unitNids, $data['uniArr']);
            $diffSg    = array_intersect($subgrpNids, $data['sgArr']);
            pr($diffUnits);  pr($diffSg);//donot insert these 
            $insUnits = array_diff($unitNids, $data['uniArr']);
            $insSg    = array_intersect($subgrpNids, $data['sgArr']);
             pr($insUnits);  pr($insSg);
            
            die;
            unset($fieldsArray['indicatorDetails'][_INDICATOR_INDICATOR_NID]);        
            $return = $this->updateRecords($fieldsArray['indicatorDetails'], $conditions);
        }
        if ($return > 0) {
            
            return $return;
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
    public function insertData($fieldsArray = [])
    {
        $return = $this->IndicatorObj->insertData($fieldsArray);
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
    public function insertOrUpdateBulkData($dataArray = [])
    {
        return $this->IndicatorObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * Update records based on conditions
     *
     * @param array $fieldsArray Fields to update with their Data. {DEFAULT : empty}
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = [])
    {
        return $this->IndicatorObj->updateRecords($fieldsArray, $conditions);
    }

    /**
     * Traditional find method to get records
     *
     * @param string $type Query Type
     * @param array $options Extra options
     * @return void
     */
    public function find($type, $options = [], $extra = null)
    {
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

        $fields = [_INDICATOR_INDICATOR_GID, _INDICATOR_INDICATOR_NAME, _INDICATOR_INDICATOR_NID,_INDICATOR_SHORT_NAME,_INDICATOR_HIGHISGOOD,_INDICATOR_DATA_EXIST];
        $conditions = [_INDICATOR_INDICATOR_NID => $iNid];
        return $this->getRecords($fields, $conditions);
    }

    /**
     * - For DEVELOPMENT purpose only
     * Test method to do anything based on this model (Run RAW queries or complex queries)
     * 
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = [])
    {
        return $this->IndicatorObj->testCasesFromTable($params);
    }

}
