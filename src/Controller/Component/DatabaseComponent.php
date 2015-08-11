<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
/**
 * TransactionLogs Component
 */
class DatabaseComponent extends Component {

    //Loading Components
    public $components = ['Auth'];
    
    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
     }

}
