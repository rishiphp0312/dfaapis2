<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Subgroup Component
 */
class SubgroupComponent extends Component
{

    /**
     * getDataByIds method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByIds($ids = null, $fields = [], $type = 'all' )
    {
        
        $UTIndicatorEn = TableRegistry::get('UTIndicatorEn');

        return $UTIndicatorEn->getDataByIds($ids, $fields, $type);

    }


    /**
     * getDataByParams method
     *
     * @param array $conditions Conditions on which to search. {DEFAULT : empty}
     * @param array $fields Fields to fetch. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $conditions, array $fields)
    {
        
        App::import('Model', 'UTIndicatorEn');
        $UTIndicatorEn = new UTIndicatorEn();

        return $UTIndicatorEn->getDataByParams($conditions, $fields);

    }


}
