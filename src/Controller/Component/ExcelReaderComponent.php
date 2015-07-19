<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * ExcelReader Component for reading excel file 
 */
class ExcelReaderComponent extends Component
{

   protected $PHPExcelReader; //variable to hold reference to the PHPExcel instance  
   protected $PHPExcelLoaded = false;    
   public $dataArray=array(); // variable to hold data of the excel sheet.  
  
    public function beforeFilter() {
      
      
    }

	public function loadExcelFile($filename) {  
       
	   //App::import('Vendor','PHPExcel', array('file' => 'PHPExcel/PHPExcel.php'));  
       require_once(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
      // echo file_exists(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
	  
	   if (!class_exists('PHPExcel'))  
       throw new CakeException('Vendor class PHPExcel not found!');  
	    // $objPHPExcel = \PHPExcel_IOFactory::load($filename);
	    $this->PHPExcelReader = \PHPExcel_IOFactory::createReaderForFile($filename);  
        $this->PHPExcelLoaded = true;  
        $this->PHPExcelReader->setReadDataOnly(true);   
        // echo   $this->PHPExcelReader->getSheetCount();  
        $excel = $this->PHPExcelReader->load($filename);
	    $CurrentWorkSheetIndex = 0; 
        
		foreach ($excel->getWorksheetIterator() as $worksheet) {
			  
			  echo  $worksheetTitle          = $worksheet->getTitle();			  
			  echo '<br/>';             
			  echo  $highestRow              = $worksheet->getHighestRow(); // e.g. 10
			  echo '<br/>';
			  echo  $highestColumn           = $worksheet->getHighestColumn(); // e.g 'F'
			  echo '<br/>';
		      echo '<br/>';
              echo   $highestColumnIndex     = \PHPExcel_Cell::columnIndexFromString($highestColumn);
			  echo '<br/>';
			  for ($row = 1; $row <= $highestRow; ++ $row) {

                        for ($col = 0; $col < $highestColumnIndex; ++ $col) {

                            $cell = $worksheet->getCellByColumnAndRow($col, $row);
                            $val = $cell->getValue();
                            $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);
                            if($row!= 1 && $row!= 2 && $row!= 3){
									
								if($row == 4){
									$insertFieldsArr[] = $val;
									$datacolumnnames['columndetails'][$col]= $val;
								}else{									
								    $insertDataArr['exceldata'][$row][$insertFieldsArr[$col]] = $val;								    	
							    }
                            }

                    }
	            }
		}	  
		//pr($datacolumnnames);	 
        //return $this->dataArray = $excel->getSheet(0)->toArray();  
		$insertDataArr = array_merge($insertDataArr,$datacolumnnames);
		//pr($insertDataArr);	  

		return $insertDataArr;
		
    }     
	
	
	public function exportExcelToCSVFile($filename) {  
       
        require_once(ROOT . DS . 'vendor' . DS  . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
	  
	    if (!class_exists('PHPExcel'))  
        throw new CakeException('Vendor class PHPExcel not found!');  
	
	    $this->PHPExcelReader = \PHPExcel_IOFactory::createReaderForFile($filename);  
        $this->PHPExcelLoaded = true;  
        $this->PHPExcelReader->setReadDataOnly(true);   
    
		$objPHPExcelReader = $this->PHPExcelReader->load($filename);

		$loadedSheetNames = $objPHPExcelReader->getSheetNames();

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcelReader, 'CSV');

		foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
			$objWriter->setSheetIndex($sheetIndex);
			$result = $objWriter->save($loadedSheetName.'-rishi.csv');
		}
pr($result);die;
		
		
    } 


}
