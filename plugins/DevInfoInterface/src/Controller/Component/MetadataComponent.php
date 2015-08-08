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
        'DevInfoInterface.CommonInterface'
        ];
	
    public $delm ='{-}'; 	
    public $MetadatacategoryObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->MetadatacategoryObj = TableRegistry::get('DevInfoInterface.Metadatacategory');
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
	
	public function getOrderno(){
		
		$query = $this->MetadatacategoryObj->find();
		$result = $query->select(['max' => $query->func()->max(_META_CATEGORY_ORDER),
		])->hydrate(false)->toArray();
		return $result = current($result)['max'];
		
	}
	
	
	/**
     * to get the highest max nid  
     * 
     */
	public function getMaxNid(){
		
		$query = $this->MetadatacategoryObj->find();
		$result = $query->select(['max' => $query->func()->max(_META_CATEGORY_NID),
		])->hydrate(false)->toArray();
		return $result = current($result)['max'];
		
	}
	
	
	

    


}
