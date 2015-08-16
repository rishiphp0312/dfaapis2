<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * Template component
 */
class TemplateComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $components = ['DevInfoInterface.CommonInterface'];

    public function initialize(array $config) {
        parent::initialize($config);
    }
    
    /**
     * Add map for AREA/GROUP
     * 
     * @param array $inputs Input params 
     * @param array $dbConnection DB connection details
     * @return void
     */
    public function addMap($type, $inputs, $dbConnection)
    {
        if(!empty($type)) {
            
            $params = ['inputs' => $inputs];
            
            switch ($type) {
                // area
                case _MAP_TYPE_AREA:
                    $return = $this->CommonInterface->serviceInterface('Area', 'areaMap', $params, $dbConnection);
                    break;
                
                // group
                case _MAP_TYPE_GROUP:
                    $return = $this->CommonInterface->serviceInterface('Area', 'groupMap', $params, $dbConnection);
                    break;
            }
            
        } else {
            
        }
    }
}
