<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Metadata report Component
 */
class MetadatareportComponent extends Component {

    // The other component your component uses
    public $components = [        
        'DevInfoInterface.CommonInterface'
        ];
	
    public $delm ='{-}'; 	
    public $MetadatareportObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->MetadatareportObj   = TableRegistry::get('DevInfoInterface.Metadatareport');
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
        
        $result = $this->MetadatareportObj->getRecords($fields, $conditions, $type);            
        
        return $result;
        
    }
	

    /**
     * deleteRecords method
     *
     * @param array $conditions Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function deleteRecords($conditions = []) {
        return $this->MetadatareportObj->deleteRecords($conditions);
    }

	
	 
    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        return $this->MetadatareportObj->insertData($fieldsArray);
    }

    /**
     * Insert multiple rows at once
     *
     * @param array $dataArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertOrUpdateBulkData($dataArray = []) {
        return $this->MetadatareportObj->insertOrUpdateBulkData($dataArray);
    }

    /**
     * updateRecords method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function updateRecords($fieldsArray = [], $conditions = []) {
        return $this->MetadatareportObj->updateRecords($fieldsArray, $conditions);
    }
    
    

    


}
