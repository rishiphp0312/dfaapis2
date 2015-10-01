<?php
namespace RapidPro\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * Sms component
 */
class SmsComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $ConfigItemOptions = null;
    public $components = [];

    public function initialize(array $config) {
        parent::initialize($config);
        //$this->Areas = TableRegistry::get('Areas');
    }
    
    /**
     * Check and return valid phone numbers
     * 
     * @param array/string $phoneNumbers phone numbers to be checked
     * @return array Valid phone numbers array
     */
    public function checkValidPhoneNumbers($phoneNumbers) {
        
        $telephone = $errorNumbers = [];
        
        if(is_array($phoneNumbers)) {
            foreach($phoneNumbers as $phoneNumber) {
                $pattern = '/^[\+]?[91]?[0-9]{10}$/';
                if(preg_match($pattern, $phoneNumber)) {
                    if(strpos($phoneNumber, '+91') !== false) {
                        $telephone[] = "tel:".$phoneNumber;
                    } else {
                        $telephone[] = "tel:+91".$phoneNumber;
                    }
                } else {
                    $errorNumbers[] = $phoneNumber;
                }
            }
        } else {
            $pattern = '/^[\+]?[91]?[0-9]{10}$/';
            if(preg_match($pattern, $phoneNumbers) === 0) {
                return ['error' => _ERR555]; // Invalid number
            } else {
                if(strpos($phoneNumbers, '+91') !== false) {
                    $telephone[] = "tel:".$phoneNumbers;
                } else {
                    $telephone[] = "tel:+91".$phoneNumbers;
                }
            }
        }
        
        return ['validNum' => $telephone, 'invalidNum' => $errorNumbers];
    }
    
    /**
     * SEND SMS to phone numbers
     * 
     * @param array/string $phoneNumbers Phone numbers to send sms to
     * @param string $message Text to be sent
     * @return JSON output from rapidPro
     */
    public function sendSms($phoneNumbers, $message) {
        
        $telephone = [];
        if(empty($message)) {
            return false; // Blank message
        }
        
        // check and get valid numbers
        $telephone = $this->checkValidPhoneNumbers($phoneNumbers);
        
        if(isset($telephone['error'])) {
            return ['error' => $telephone['error']];
        } else if(empty($telephone['validNum'])) {
            return false; // Invalid number
        }
        
        // preparing data to be sent
        $json_data_array = [
            'urns' => $telephone['validNum'],
            'text' => $message
        ];
        $json_data = json_encode($json_data_array);
        
        // post the json data to RapidPro using Curl.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://118.102.190.90:1712/api/v1/broadcasts.json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token 54f017169895e9ffe745fd9a5eea05cc95ccf4b1',
            'Content-type: application/json'
        ));
        
        $rest = curl_exec($ch);

        // print the response back from RapidPro
        //print_r($rest);
        return $rest;
    }
}
