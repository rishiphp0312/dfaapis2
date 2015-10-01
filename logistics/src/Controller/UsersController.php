<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController {

    var $layout = 'home';
    var $Users = '';
    public $components = ['Common', 'UserCommon'];
    public $UserLog = NULL;

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginRedirect' => [
                'controller' => 'Users',
                'action' => 'view'
            ],
            'logoutRedirect' => [
                'controller' => 'Users',
                'action' => 'login'
            ],
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'username']
                ]]
        ]);
        $this->session = $this->request->session();
        $this->UserLog = TableRegistry::get('UsersLog');
       
    }

    //services/serviceQuery
    public function beforeFilter(Event $event) {

        parent::beforeFilter($event);
        $this->Auth->allow(['logout', 'login']);
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     *  Function is basically used for user login functionality
     */
    public function login() {
        
        $this->autoLayout = false;
        $this->autoRender = false;
        
        try {

            $user = $this->Auth->identify();
            $returnData = array();

            if ($user) {

                if ($user['status_id'] != 1) {
                    $returnData['success'] = false;
                }

                $this->Auth->setUser($user);
                $returnData['success'] = true;
                $returnData['data']['id'] = session_id();
                $returnData['data']['user']['id'] = $this->Auth->user('id');
                $returnData['data']['user']['name'] = $this->Auth->user('first_name') . ' ' . $this->Auth->user('last_name');
                $returnData['data']['user']['email'] = $this->Auth->user('email');
                $returnData['data']['user']['username'] = $this->Auth->user('username');
                // Permissions
                $permissions = $this->Common->getModulesPermissions($this->Auth->user('role_id'));
                $returnData['data']['user']['permission'] = $permissions['permission'];
                $roles = $this->Common->getRoles(['id' => $this->Auth->user('role_id')]);
                $returnData['data']['user']['roleId'] = $this->Auth->user('role_id');
                $returnData['data']['user']['roleName'] = reset($roles)['role_name'];
                
                $this->session->write('permissions', $permissions['permission']);
                
                $loginDetails=[];
                $loginDetails['user_id']    = $this->Auth->user('id');
                $loginDetails['loggedin']   = date('Y-m-d H:i:s');
                $loginDetails['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $this->UserCommon->logindetails($loginDetails);
                echo json_encode($returnData);
                exit;
            } else {
                $returnData['success'] = false;
            }
        } catch (MissingTemplateException $e) {

            if (Configure::read('debug')) {
                throw $e;
            }
            throw new NotFoundException();
        }
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     *  Function is basically used for user logout functionality 
     */
    public function logout() {
        $returnData = array();
        $returnData['isAuthenticated'] = false;

        if ($this->Auth->logout()) {
            session_unset();
            $returnData['success'] = true;
        }
        echo json_encode($returnData);
        exit;
    }

}
