<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Common period Component
 */
class DatabaseComponent extends Component
{

    
	/*
	 guid is function which returns gid 	 
	*/
	
	public function guid(){
		
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}else{
				mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
				$charid = strtoupper(md5(uniqid(rand(), true)));
				$hyphen = chr(45);// "-"
				//$uuid =// chr(123)// "{"
                $uuid =substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
                //.chr(125);// "}"
                return $uuid;
        }
	}
	
	
	/*
	 Cleandata is function which returns the passed parameter after
 	 removing whitespace or unnecesary characters with clean data  	 
	 mysql_real_escape_string($user)
	*/
	
	public function cleandata($data){		
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  return $data;		
	}
	
	/*
	 Cleandata is function which returns the passed parameter after
 	 removing whitespace or unnecesary characters with clean data  	 
	 mysql_real_escape_string($user)
	*/
	
	public function saveData($data){		
		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  return $data;		
	}
	
	
	


}
