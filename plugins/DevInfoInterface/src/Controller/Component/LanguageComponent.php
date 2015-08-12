<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Unit Component
 */
class LanguageComponent extends Component {

    // The other component your component uses
    public $LangObj = NULL;
    public $components = ['TransactionLogs','Common','Auth',
        'DevInfoInterface.IndicatorUnitSubgroup',
        'DevInfoInterface.Data',
        'DevInfoInterface.Area',
        'DevInfoInterface.IcIus',
        'DevInfoInterface.Metadatareport',
        'DevInfoInterface.Metadata',
        'DevInfoInterface.SubgroupValsSubgroup',
        'DevInfoInterface.Subgroup',
        'DevInfoInterface.SubgroupType',
        'DevInfoInterface.CommonInterface'];
	
    public function initialize(array $config) {
        parent::initialize($config);
        $this->LangObj = TableRegistry::get('DevInfoInterface.Language');

        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
		
    }

    /**
     * Get records based on conditions
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return array fetched records
     */
    public function getRecords(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->LangObj->getRecords($fields, $conditions, $type, $extra);
    }

    /**
     * Delete records using conditions
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return string deleted records count
     */
    public function deleteRecords($conditions = []) {
        return $this->UnitObj->deleteRecords($conditions);
    }

    public function export_lang_database()
    {
              
            //IC DATA
            $params = [];
            $params['fields'] = array(_IC_IC_NID,_IC_IC_PARENT_NID,_IC_IC_GID,_IC_IC_NAME);
            $params['conditions'] = [];
           $IClist = $this->CommonInterface->serviceInterface('IndicatorClassifications', 'getRecords', $params);
           
             //Indicator list DATA
            $params = [];
            $params['fields'] = array(_INDICATOR_INDICATOR_NID,_INDICATOR_INDICATOR_NAME,_INDICATOR_INDICATOR_GID);
            $params['conditions'] = [];
            $Indicatorlist = $this->CommonInterface->serviceInterface('Indicator', 'getRecords', $params); 
           
            //Unit list DATA
            $params = [];
            $params['fields'] = array(_UNIT_UNIT_NID,_UNIT_UNIT_NAME,_UNIT_UNIT_GID);
            $params['conditions'] = [];
           $Unitlist = $this->CommonInterface->serviceInterface('Unit', 'getRecords', $params, $dbConnection); 

           //Area list DATA
            $params = [];
            $params['fields'] = array(_AREA_AREA_NID,_AREA_PARENT_NId,_AREA_AREA_ID,_AREA_AREA_NAME,_AREA_AREA_GID,_AREA_AREA_LEVEL);
            $params['conditions'] = [];
            $Arealist = $this->CommonInterface->serviceInterface('Area', 'getRecords', $params, $dbConnection); 


            //Area Level list DATA
            $params = [];
            $params['fields'] = array(_AREALEVEL_LEVEL_NID,_AREALEVEL_AREA_LEVEL,_AREALEVEL_LEVEL_NAME);
            $params['conditions'] = [];
            $AreaLevellist = $this->CommonInterface->serviceInterface('Area', 'getRecordsAreaLevel', $params, $dbConnection); 

            //Subgroup list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_SUBGROUP_NID,_SUBGROUP_SUBGROUP_NAME,_SUBGROUP_SUBGROUP_GID,_SUBGROUP_SUBGROUP_TYPE);
            $params['conditions'] = [];
           $Subgrouplist = $this->CommonInterface->serviceInterface('Subgroup', 'getRecords', $params, $dbConnection); 


            //Subgroup type list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUPTYPE_SUBGROUP_TYPE_NID,_SUBGROUPTYPE_SUBGROUP_TYPE_NAME,_SUBGROUPTYPE_SUBGROUP_TYPE_GID);
            $params['conditions'] = [];
            $SubgroupTypelist = $this->CommonInterface->serviceInterface('SubgroupType', 'getRecords', $params, $dbConnection); 


             //Subgroup values list DATA
            $params = [];
            $params['fields'] = array(_SUBGROUP_VAL_SUBGROUP_VAL_NID,_SUBGROUP_VAL_SUBGROUP_VAL,_SUBGROUP_VAL_SUBGROUP_VAL_GID);
            $params['conditions'] = [];
            $SubgroupValslist = $this->CommonInterface->serviceInterface('SubgroupVals', 'getRecords', $params); 
                

             //Footnote values list DATA
            $params = [];
            $params['fields'] = array(_FOOTNOTE_NId,_FOOTNOTE_VAL,_FOOTNOTE_GID);
            $params['conditions'] = [];
           $Footnotelist = $this->CommonInterface->serviceInterface('Footnote', 'getRecords', $params); 
                
            
        $objPHPExcel 	= new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $startRow = $objPHPExcel->getActiveSheet()->getHighestRow();

     


    }

    
	

}
