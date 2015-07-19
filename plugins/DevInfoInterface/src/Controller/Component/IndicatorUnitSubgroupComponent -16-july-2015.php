<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * IndicatorUnitSubgroup Component
 */
class IndicatorUnitSubgroupComponent extends Component {

    // The other component your component uses
    public $components = [
        'DevInfoInterface.Indicator',
        'DevInfoInterface.Unit',
        'DevInfoInterface.SubgroupVals',
        'DevInfoInterface.CommonInterface'
        ];
	
    public $delm ='{-}'; 	
    public $IndicatorUnitSubgroupObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->IndicatorUnitSubgroupObj = TableRegistry::get('DevInfoInterface.IndicatorUnitSubgroup');
    }

    /**
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all') {
        return $this->IndicatorUnitSubgroupObj->getDataByIds($ids, $fields, $type);
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all') {
        return $this->IndicatorUnitSubgroupObj->getDataByParams($fields, $conditions, $type);
    }

    /**
     * getGroupedList method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getGroupedList(array $fields, array $conditions) {
        return $this->IndicatorUnitSubgroupObj->getGroupedList($fields, $conditions);
    }

    /**
     * deleteByIds method
     *
     * @param array $ids Fields to fetch. {DEFAULT : null}
     * @return void
     */
    public function deleteByIds($ids = null) {
        return $this->IndicatorUnitSubgroupObj->deleteByIds($ids);
    }

    /**
     * deleteByParams method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteByParams($conditions = []) {
        return $this->IndicatorUnitSubgroupObj->deleteByParams($conditions);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        return $this->IndicatorUnitSubgroupObj->insertData($fieldsArray);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->IndicatorUnitSubgroupObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * bulkInsert method
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function bulkInsert($dataArray = []) {
        return $this->IndicatorUnitSubgroupObj->bulkInsert($dataArray);
    }

    /**
     * updateDataByParams method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateDataByParams($fieldsArray = [], $conditions = []) {
        return $this->IndicatorUnitSubgroupObj->updateDataByParams($fieldsArray, $conditions);
    }
    
    /**
     * getConcatedFields method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedFields(array $fields, array $conditions, $type = null)
    {
        if($type == 'list' && array_key_exists(2, $fields)){
            $result = $this->IndicatorUnitSubgroupObj->getConcatedFields($fields, $conditions, 'all');
            if(!empty($result)){
                return array_column($result, 'concatinated', $fields[2]);
            }else{
                return [];
            }
        }else{
            return $this->IndicatorUnitSubgroupObj->getConcatedFields($fields, $conditions, $type);
        }
    }
    
    /**
     * getConcatedIus method
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getConcatedIus(array $fields, array $conditions, $type = null)
    {
        $result = $this->IndicatorUnitSubgroupObj->getConcatedIus($fields, $conditions, 'all');
        if($type == 'list'){            
            if(!empty($result)){
                $result = array_column($result, 'concatinated', _IUS_IUSNID);
            }
        }
        
        return $result;
    }

    /**
     * getAllIUConcatinated method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function getAllIUConcatinated($fields = [], $conditions = [], $extra = []) {
        
        $result = $this->IndicatorUnitSubgroupObj->getAllIUConcatinated($fields, $conditions, $extra);
        if(isset($extra['type']) && $extra['type'] == 'list'){   
            if(!empty($result)){
                $result = array_column($result, 'concatinated', _IUS_IUSNID);
                if(isset($extra['unique']) && $extra['unique'] == true){
                    $result = array_unique($result);
                }
            }
        }else{
            if(isset($extra['unique']) && $extra['unique'] == true){
                $result = array_intersect_key($result, array_unique(array_column($result, 'concatinated')));
            }
        }
        return $result;
    }

    /**
     * getAllIU method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function getAllIU($fields = [], $conditions = [], $extra = []) {
        
        //Get IU Nids list
        $result = $this->getAllIUConcatinated($fields, $conditions, $extra);
        
        //Get Indicator Details From Nid
        $IndicatorField[0] = _INDICATOR_INDICATOR_NID;
        $IndicatorField[1] = _INDICATOR_INDICATOR_GID;
        $IndicatorField[2] = _INDICATOR_INDICATOR_NAME;
        $IndicatorCondition = [_INDICATOR_INDICATOR_NID . ' IN' => array_column($result, _INDICATOR_INDICATOR_NID)];
        $IndicatorGidList = $this->Indicator->getDataByParams($IndicatorField, $IndicatorCondition, 'all');
        $IndicatorGidList = array_combine(array_column($IndicatorGidList, _INDICATOR_INDICATOR_NID), $IndicatorGidList);
        
        //Get Unit Details From Nid
        $unitField[0] = _UNIT_UNIT_NID;
        $unitField[1] = _UNIT_UNIT_GID;
        $unitField[2] = _UNIT_UNIT_NAME;
        $unitCondition = [_UNIT_UNIT_NID . ' IN' => array_column($result, _UNIT_UNIT_NID)];
        $unitGidList = $this->Unit->getDataByParams($unitField, $unitCondition, 'all');
        $unitGidList = array_combine(array_column($unitGidList, _UNIT_UNIT_NID), $unitGidList);
        
        //Get SubgroupVals Details From Nid
        $subgroupValsField[0] = _SUBGROUP_VAL_SUBGROUP_VAL_NID;
        $subgroupValsField[1] = _SUBGROUP_VAL_SUBGROUP_VAL_GID;
        $subgroupValsField[2] = _SUBGROUP_VAL_SUBGROUP_VAL;
        $subgroupValsCondition = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN' => array_column($result, _SUBGROUP_VAL_SUBGROUP_VAL_NID)];
        $subgroupValsGidList = $this->SubgroupVals->getDataByParams($subgroupValsField, $subgroupValsCondition, 'all');
        $subgroupValsGidList = array_combine(array_column($subgroupValsGidList, _SUBGROUP_VAL_SUBGROUP_VAL_NID), $subgroupValsGidList);
        
        $preparedData = [];
        if($extra['onDemand'] == true){
            $childExists = true;
            $nodes = [];
        }else{
            $childExists = false;
        }
        
        foreach($result as $key => $value){
            if($extra['onDemand'] == true){
                $preparedData[$value[_INDICATOR_INDICATOR_NID] . '_' . $value[_UNIT_UNIT_NID]] = [
                    'iGid' => $IndicatorGidList[$value[_INDICATOR_INDICATOR_NID]][_INDICATOR_INDICATOR_GID],
                    'uGid' => $unitGidList[$value[_UNIT_UNIT_NID]][_UNIT_UNIT_GID],
                    'iName' => $IndicatorGidList[$value[_INDICATOR_INDICATOR_NID]][_INDICATOR_INDICATOR_NAME],
                    'uName' => $unitGidList[$value[_UNIT_UNIT_NID]][_UNIT_UNIT_NAME],
                    'childExists' => true,
                    'nodes' => []
                ];
            }else{            
                // Prepare Subgroup Node
                $subGroupNode = [
                        _IUS_IUSNID => $value[_IUS_IUSNID],
                        'iusGid' => $value[_INDICATOR_INDICATOR_NID] . '{~}' . $value[_UNIT_UNIT_NID] . '{~}' . $subgroupValsGidList[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]][_SUBGROUP_VAL_SUBGROUP_VAL_GID],
                        'sName' => $subgroupValsGidList[$value[_SUBGROUP_VAL_SUBGROUP_VAL_NID]][_SUBGROUP_VAL_SUBGROUP_VAL],
                        'childExists' => false,
                        'nodes' => []
                    ];

                // Attach Subgroup to IU Node
                if(array_key_exists($value[_INDICATOR_INDICATOR_NID] . '_' . $value[_UNIT_UNIT_NID], $preparedData)){
                    $preparedData[$value[_INDICATOR_INDICATOR_NID] . '_' . $value[_UNIT_UNIT_NID]]['nodes'][] = $subGroupNode;
                }else{
                    $preparedData[$value[_INDICATOR_INDICATOR_NID] . '_' . $value[_UNIT_UNIT_NID]] = [
                        'iGid' => $IndicatorGidList[$value[_INDICATOR_INDICATOR_NID]][_INDICATOR_INDICATOR_GID],
                        'uGid' => $unitGidList[$value[_UNIT_UNIT_NID]][_UNIT_UNIT_GID],
                        'iName' => $IndicatorGidList[$value[_INDICATOR_INDICATOR_NID]][_INDICATOR_INDICATOR_NAME],
                        'uName' => $unitGidList[$value[_UNIT_UNIT_NID]][_UNIT_UNIT_NAME],
                        'childExists' => true,
                        'nodes' => [$subGroupNode]
                    ];
                }
            }
        }
        
        return $preparedData;
    }

    /**
     * getAllSubgroupsFromIUGids method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function getAllSubgroupsFromIUGids($fields = [], $conditions = [], $extra = []) {
        
        //Get Indicator Details From Gid
        $IndicatorField[0] = _INDICATOR_INDICATOR_NID;
        $IndicatorCondition = [_INDICATOR_INDICATOR_GID . ' IN' => $conditions['iGid']];
        $IndicatorGidList = $this->Indicator->getDataByParams($IndicatorField, $IndicatorCondition, 'all');
        
        //Get Unit Details From Gid
        $unitField[0] = _UNIT_UNIT_NID;
        $unitCondition = [_UNIT_UNIT_GID . ' IN' => $conditions['uGid']];
        $unitGidList = $this->Unit->getDataByParams($unitField, $unitCondition, 'all');
        
        //Get Unit Details From Gid
        $IusField = [_IUS_IUSNID, _IUS_SUBGROUP_VAL_NID];
        $IusCondition = [_IUS_INDICATOR_NID => $IndicatorGidList[0][_INDICATOR_INDICATOR_NID] , _IUS_UNIT_NID => $unitGidList[0][_UNIT_UNIT_NID]];
        $IusList = $this->getDataByParams($IusField, $IusCondition, 'list');
        
        //Get Subgroup Details From Nid
        $subgroupField = [_SUBGROUP_VAL_SUBGROUP_VAL_NID, 'sName' => _SUBGROUP_VAL_SUBGROUP_VAL, 'sGid' => _SUBGROUP_VAL_SUBGROUP_VAL_GID];
        $subgroupCondition = [_SUBGROUP_VAL_SUBGROUP_VAL_NID . ' IN' => $IusList];
        $subgroupList = $this->SubgroupVals->getDataByParams($subgroupField, $subgroupCondition, 'all');
        
        foreach($subgroupList as $key => &$value){
            $value = [
                    _IUS_IUSNID => array_search($value[_SUBGROUP_VAL_SUBGROUP_VAL_NID], $IusList),
                    'iusGid' => $conditions['iGid'] . '{~}' . $conditions['uGid'] . '{~}' . $value['sGid'],
                    'sName' => $value['sName'],
                    'childExists' => false,
                    'nodes' => []
                ];
        }
        
        return $subgroupList;
    }
    

    /**
     * getAllSubgroupsFromIUGids method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function getIusNameAndGids($conditions = [], $extra = []) {
        
        //Get Indicator Details From Gid
        $IndicatorField = [_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME];
        $IndicatorCondition = [_INDICATOR_INDICATOR_GID => $conditions['iGid']];
        $IndicatorGidList = $this->Indicator->getDataByParams($IndicatorField, $IndicatorCondition, 'all');
        
        if(!isset($IndicatorGidList[0])) return ['error' => _INDICATOR_IS_EMPTY];
        
        //Get Unit Details From Gid
        $unitField = [_UNIT_UNIT_NID, _UNIT_UNIT_NAME];
        $unitCondition = [_UNIT_UNIT_GID => $conditions['uGid']];
        $unitGidList = $this->Unit->getDataByParams($unitField, $unitCondition, 'all');
        
        if(!isset($unitGidList[0])) return ['error' => _UNIT_IS_EMPTY];
        
        if(isset($conditions['sGid']) && !empty($conditions['sGid'])){
            //Get Subgroup Details From GId
            $subgroupField = [_SUBGROUP_VAL_SUBGROUP_VAL_NID, 'sName' => _SUBGROUP_VAL_SUBGROUP_VAL, 'sGid' => _SUBGROUP_VAL_SUBGROUP_VAL_GID];
            $subgroupCondition = [_SUBGROUP_VAL_SUBGROUP_VAL_GID => $conditions['sGid']];
            $subgroupList = $this->SubgroupVals->getDataByParams($subgroupField, $subgroupCondition, 'all');
            
        }else{
            //Get Unit Details From Gid
            $IusField = [_IUS_IUSNID, _IUS_SUBGROUP_VAL_NID, _IUS_ISDEFAULTSUBGROUP];
            $IusCondition = [_IUS_INDICATOR_NID => $IndicatorGidList[0][_INDICATOR_INDICATOR_NID] , _IUS_UNIT_NID => $unitGidList[0][_UNIT_UNIT_NID]];
            $IusResult = $this->getDataByParams($IusField, $IusCondition, 'all');
            $IusList = array_column($IusResult, _IUS_ISDEFAULTSUBGROUP);
            
            if(array_search(true, $IusList)){
                $sNid = $IusResult[array_search(true, $IusList)][_IUS_SUBGROUP_VAL_NID];
            }else{
                $sNid = $IusResult[array_search(false, $IusList)][_IUS_SUBGROUP_VAL_NID];
            }
            
            //Get Subgroup Details From Nid
            $subgroupField = [_SUBGROUP_VAL_SUBGROUP_VAL_NID, 'sName' => _SUBGROUP_VAL_SUBGROUP_VAL, 'sGid' => _SUBGROUP_VAL_SUBGROUP_VAL_GID];
            $subgroupCondition = [_SUBGROUP_VAL_SUBGROUP_VAL_NID => $sNid];
            $subgroupList = $this->SubgroupVals->getDataByParams($subgroupField, $subgroupCondition, 'all');
        }

        if(!empty($subgroupList)){
            $return = [
                'iGid' => $conditions['iGid'],
                'iName' => $IndicatorGidList[0][_INDICATOR_INDICATOR_NAME],
                'uGid' => $conditions['uGid'],
                'uName' => $unitGidList[0][_UNIT_UNIT_NAME],
                'sGid' => $subgroupList[0]['sGid'],
                'sName' => $subgroupList[0]['sName'],
                'iusGid' => $conditions['iGid'] . '{~}' . $conditions['uGid'] . '{~}' . $subgroupList[0]['sGid'],
            ];
        }else{
            return ['error' => _SUBGROUP_IS_EMPTY];
        }
        return $return;
    }

    /**
     * testCasesFromTable method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function testCasesFromTable($params = []) {
        return $this->IndicatorUnitSubgroupObj->testCasesFromTable($params);
    }
	
	
	/*
	function to get ius nids on passed ius gids 
	*/
	public function getIusDataCollection($iusArray) {

		

		$tempDataAr = array(); // temproryly store data for all element name		

		foreach($iusArray as $ius) {

			$iusAr = explode($this->delm, $ius);
			$iGid = $iusAr[0];
			$uGid = $iusAr[1];
			$sGid = $iusAr[2];
			$conditions = array(
				'conditions'=>array(
					'Indicator.Indicator_GId'=>$iGid, 
					'Unit.Unit_GId'=>$uGid, 
					'SubgroupVal.Subgroup_Val_GId'=>$sGid
				));
			// --------------------- GET IUSNIds
			 $options = [];

        if (!empty($fields))
            $options['fields'] = $fields;
        if (!empty($conditions))
            $options['conditions'] = $conditions;
		//pr($options);die;
        if ($type == 'list')
            $this->setListTypeKeyValuePairs($fields);

        $data = $this->find($type, $options);    
			/*
			$IUSNidDt = $IndicatorUnitSubgroup->find('first', , 
				'fields'=>array('IndicatorUnitSubgroup.IUSNId', 'Indicator.Indicator_NId', 'Indicator.Indicator_Name', 'Unit.Unit_NId', 'Unit.Unit_Name', 'SubgroupVal.Subgroup_Val_NId', 'SubgroupVal.Subgroup_Val'));
			*/
			$tempDataAr['ind'][$IUSNidDt['Indicator']['Indicator_NId']][0] = $iGid;
			$tempDataAr['ind'][$IUSNidDt['Indicator']['Indicator_NId']][1] = $IUSNidDt['Indicator']['Indicator_Name'];

			$tempDataAr['unit'][$IUSNidDt['Unit']['Unit_NId']][0] = $uGid;
			$tempDataAr['unit'][$IUSNidDt['Unit']['Unit_NId']][1] = $IUSNidDt['Unit']['Unit_Name'];

			$tempDataAr['sg'][$IUSNidDt['SubgroupVal']['Subgroup_Val_NId']][0] = $sGid;
			$tempDataAr['sg'][$IUSNidDt['SubgroupVal']['Subgroup_Val_NId']][1] = $IUSNidDt['SubgroupVal']['Subgroup_Val'];

			$tempDataAr['iusnids'][] = $IUSNidDt['IndicatorUnitSubgroup']['IUSNId'];							
		}

		return $tempDataAr;

	}

}
