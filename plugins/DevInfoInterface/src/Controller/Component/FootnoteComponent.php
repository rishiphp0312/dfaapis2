<?php

namespace DevInfoInterface\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Footnote Component
 */
class FootnoteComponent extends Component {

    // The other component your component uses
    public $components = [];
    public $footnoteObj = NULL;

    public function initialize(array $config) {
        parent::initialize($config);
        $this->FootnoteObj = TableRegistry::get('DevInfoInterface.Footnote');
    }

    /**
     * saveAndGetFootnoteRecWithNids
     * 
     * @param array $indicatorArray Indicator data Array
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function saveAndGetFootnoteRec($fields = [], $conditions = [], $extra = []) {
        
        $fields = [_FOOTNOTE_NId, _FOOTNOTE_VAL];
        $type = isset($extra['type']) ? $extra['type'] : 'all' ;
        $existingRec = $this->FootnoteObj->getDataByParams($fields, $conditions, $type);
        debug($existingRec);exit;
        
        
        $insertDataKeys = ['name' => _INDICATOR_INDICATOR_NAME, 'gid' => _INDICATOR_INDICATOR_GID, 'IndiGlobal' => _INDICATOR_INDICATOR_GLOBAL];
        $divideNameAndGids = $this->divideNameAndGids($insertDataKeys, $indicatorArray);

        $params['nid'] = _INDICATOR_INDICATOR_NID;
        $params['insertDataKeys'] = $insertDataKeys;
        $params['updateGid'] = TRUE;
        $component = 'Indicator';

        $this->nameGidLogic($divideNameAndGids, $component, $params);

        $fields = [_INDICATOR_INDICATOR_NID, _INDICATOR_INDICATOR_NAME];
        $conditions = [_INDICATOR_INDICATOR_NAME . ' IN' => array_filter(array_unique(array_column($indicatorArray, 0)))];
        return $indicatorRecWithNids = $this->Indicator->getDataByParams($fields, $conditions, 'list');
    }
    
}
