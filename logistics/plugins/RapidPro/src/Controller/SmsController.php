<?php
namespace RapidPro\Controller;

use RapidPro\Controller\AppController;

/**
 * Sms Controller
 *
 * @property \RapidPro\Model\Table\SmsTable $Sms
 */
class SmsController extends AppController
{
    
    public $components = ['Auth', 'RapidPro.Sms'];
    
    public function validPrefixes($param) {
        return [
            'SUBSCRIBE',
            'WHAT',
            'WHEN',
            'WHERE',
            'WHO',
            'HOW',
            'COLLECTED',
            'DELIVERED',
            'CONFIRMED',
            'HELP'
        ];
    }
    
    /**
     * Webhook - Subscribe
     */
    public function webhook() {
        
        $this->autoRender = false;
        
        header('Content-type: application/json');
        header('Access-Control-Allow-Origin: *');
        //file_put_contents('rapidpro_post_data.txt', file_get_contents('php://input'));

        if(isset($_REQUEST['text']) && isset($_REQUEST['phone'])) {
            // all below is posted by RapidPro
            $text = $_REQUEST['text'];
            $flow = $_REQUEST['flow'];
            $phone = $_REQUEST['phone'];

            // explode the SMS message sent by user
            $textArray = explode(' ', $text);

            // sample data to check for valid locations.
            $validPrefixes = $this->validPrefixes();

            // check if the second word in valid locations. if not then send a 'invalid' message to RapidPro
            if (in_array($textArray[0], $validPrefixes)) {
                // do some stuff with the DB
                $response = $this->Subscription->processSmsResponse($_REQUEST);
                if($response == false) {
                    $return_str = 'error';
                } else if($response == true) {
                    $return_str = 'valid';
                } else {
                    $return_str = $response;
                }
            } else {
                $return_str = 'invalid';
            }
        } else {
            $return_str = 'invalid';
        }
        
        $arr = array('location' => $return_str);
        
        // this JSON is sent back to RapidPro
        echo json_encode($arr);
    }
}
