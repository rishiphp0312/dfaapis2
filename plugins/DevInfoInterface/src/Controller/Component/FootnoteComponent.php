<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Footnote Component
 */
class FootnoteComponent extends Component {

    // The other component your component uses
    public $components = ['DevInfoInterface.CommonInterface', 'TransactionLogs'];
    public $footnoteObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->FootnoteObj = TableRegistry::get('DevInfoInterface.Footnote');
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all', $extra = []) {
        return $this->FootnoteObj->getDataByParams($fields, $conditions, $type, $extra);
    }

    /**
     * insertData method
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return void
     */
    public function insertData($fieldsArray = []) {
        return $this->FootnoteObj->insertData($fieldsArray);
    }

    /**
     * insertBulkData method
     *
     * @param array $insertDataArray Data to insert. {DEFAULT : empty}
     * @param array $insertDataKeys Columns to insert. {DEFAULT : empty}
     * @return void
     */
    public function insertBulkData($insertDataArray = [], $insertDataKeys = []) {
        return $this->FootnoteObj->insertBulkData($insertDataArray, $insertDataKeys);
    }

    /**
     * saveAndGetFootnoteRecWithNids
     * 
     * @param array $indicatorArray Indicator data Array
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function saveAndGetFootnoteRec($footnotes, $extra = []) {
        
        $footnotes = array_unique($footnotes);
        
        $fields = (isset($extra['fields'])) ? $extra['fields'] : [_FOOTNOTE_NId, _FOOTNOTE_VAL, _FOOTNOTE_GID] ;
        $conditions = (isset($extra['conditions'])) ? $extra['conditions'] : [_FOOTNOTE_VAL . ' IN' => $footnotes] ;
        $type = (isset($extra['type'])) ? $extra['type'] : 'all' ;
        $existingRec = $this->getDataByParams($fields, $conditions, $type);
        
        // Some records exists
        if(!empty($existingRec)){
            // Get new records
            $insertRec = array_diff($footnotes, $existingRec);
            
            // We have new records to insert
            if(!empty($insertRec)){
                $insertDataKeys = [_FOOTNOTE_VAL, _FOOTNOTE_GID];
                
                foreach($insertRec as $footnoteVal){
                    //$insertDataArray[] = [
                    $insertDataArray = [
                        _FOOTNOTE_VAL => $footnoteVal,
                        _FOOTNOTE_GID => $this->CommonInterface->guid()
                    ];
                    // Insert New Records
                    if($this->insertData($insertDataArray)){
                        //-- TRANSACTION Log
                        $LogId = $this->TransactionLogs->createLog(_INSERT, _DATAENTRYVAL, _FOOTNOTE, $insertDataArray[_FOOTNOTE_GID], _DONE);
                    }
                }
                
                // Get all requested Footnotes
                $existingRec = $this->getDataByParams($fields, $conditions, $type, ['debug' => false]);
            }
        }
        
        return $existingRec;
    }
    
}
