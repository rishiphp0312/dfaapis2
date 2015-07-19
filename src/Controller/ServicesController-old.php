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
 
/**
 * Services Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class ServicesController extends AppController
{
    //Loading Componenets
    public $components = ['Indicator','Timeperiod','Subgroup'];

    /**
	* 
	* @return JSON/boolean
	* @throws NotFoundException When the view file could not be found
	*	or MissingViewException in debug mode.
	*/
    public function serviceQuery($case = null)
    {
        $this->autoRender = false;

        switch($case):

            case 1:
                $returnData = $this->Indicator->getDataByIds(15);
                break;
            case 201:
			
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
				// echo   $TimePeriodvalue = $_REQUEST['TimePeriodData'];
				 $TimePeriodvalue = $this->request->query['TimePeriodData'];
				//die;
			   echo $returnData = $this->Timeperiod->savesingleTimePeriodData($TimePeriodvalue);
			   } 
			   //die('hua');               
			   break;
            case 202:
			
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
			
				echo	$TimePeriodvalue = $this->request->query['TimePeriodData'];
			
			   echo $returnData = $this->Timeperiod->deletesingleTimePeriod($TimePeriodvalue);
			   }
			
                break;

            case 203:
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
			
				$TimePeriodvalue = $this->request->query['TimePeriodData'];
			
               $getDataByTimeperiod  = $this->Timeperiod->getDataByTimeperiod($TimePeriodvalue);
			   pr($getDataByTimeperiod);die;
			}
                break;
				
				
            case 204:
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
			
				echo $TimePeriodvalue = $this->request->query['TimePeriodData'];
				//die;
			
               $getDataByTimeperiod  = $this->Timeperiod->getTimeperiodDataById($TimePeriodvalue);
			   pr($getDataByTimeperiod);die;
			}
                break;
				
                //$returnData = [];
				
				case 301:
				// service for saving subgrouptype name 
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
			
				$TimePeriodvalue = $this->request->query['TimePeriodData'];
			
               $getDataByTimeperiod  = $this->Timeperiod->getDataByTimeperiod($TimePeriodvalue);
			   pr($getDataByTimeperiod);die;
			}
                break;
				case 302:
				// service for saving subgroup name 
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
			
				$TimePeriodvalue = $this->request->query['TimePeriodData'];
			
               $getDataByTimeperiod  = $this->Timeperiod->getDataByTimeperiod($TimePeriodvalue);
			   pr($getDataByTimeperiod);die;
			}
                break;
            case 303:
				// service for getting  subgroup name 
			if(isset($_REQUEST['TimePeriodData']) && !empty($_REQUEST['TimePeriodData'])){
			
				$TimePeriodvalue = $this->request->query['TimePeriodData'];
			
               $getDataByTimeperiod  = $this->Timeperiod->getDataByTimeperiod($TimePeriodvalue);
			   pr($getDataByTimeperiod);die;
			}
                break;
				
			case 304:
				// service for saving  subgroup type name 
			if(isset($_REQUEST['subgrouptypename']) && !empty($_REQUEST['subgrouptypename'])){
			
				$subgrouptypename = $this->request->query['subgrouptypename'];
			
               $savedataforUTSubgroupTypeEn  = $this->Subgroup->savesingleSubgroupTypeName($subgrouptypename);
			   pr($savedataforUTSubgroupTypeEn);die;
			}
                break;
            default:
                //$returnData = [];
        
        endswitch;

        //print_r($returnData);exit;

        //return $returnData;

    }
}
