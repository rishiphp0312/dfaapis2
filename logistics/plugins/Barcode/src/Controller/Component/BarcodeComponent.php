<?php
namespace Barcode\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * Barcode component
 */
class BarcodeComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $font = null;
    public $color_black = null;
    public $color_white = null;
    public $drawException = null;
    
    public function initialize(array $config) {
        parent::initialize($config);
        $this->session = $this->request->session();
        
        /*
         * COMMENT $this->labels in function addLabel in file BCGBarcode.php
         * to remove the text appearing below barcode
         * OR
         * Simply DON'T set $this->font
         */
        
        // Barcode required Classes
        require_once(ROOT . DS . 'vendor' . DS . 'Barcode' . DS . 'lib' . DS . 'BCGFontFile.php');
        require_once(ROOT . DS . 'vendor' . DS . 'Barcode' . DS . 'lib' . DS . 'BCGColor.php');
        require_once(ROOT . DS . 'vendor' . DS . 'Barcode' . DS . 'lib' . DS . 'BCGDrawing.php');
        
        // Loading Font
        //$this->font = new \BCGFontFile(ROOT . DS . 'vendor' . DS . 'Barcode' . DS . 'font' . DS . 'Arial.ttf', 18);
        //$this->font = new \BCGFontFile('.' . DS . 'font' . DS . 'Arial.ttf', 18);
        
        // Color Arguments - new \BCGColor(R, G, B)
        $this->color_black = new \BCGColor(0, 0, 0);
        $this->color_white = new \BCGColor(255, 255, 255);
        
        $this->drawException = null;
    }

    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
    }
    
    /**
     * GENERATE BARCODE
     */
    public function generateBarcode($text, $type = 'BCGcode128', $params = [])
    {
        // Including Barcode Technology
        require_once(ROOT . DS . 'vendor' . DS . 'Barcode' . DS . 'lib' . DS . $type . '.barcode.php');
        
        $resolution = isset($params['scale']) ? $params['scale'] : 1.5 ;
        $thickness = isset($params['thickness']) ? $params['thickness'] : 40 ;
        $imagePath = _TMP_PATH . DS . time().rand(999,999999) . '.png';
        
        try {
            //$code = new \BCGcode128();
            $code = new $type();
            $code->setScale($resolution); // Resolution
            $code->setThickness($thickness); // Thickness
            $code->setForegroundColor($this->color_black); // Color of bars
            $code->setBackgroundColor($this->color_white); // Color of spaces
            $code->setFont($this->font); // Font (or 0)
            $code->parse($text); // Text
        } catch(Exception $exception) {
            $this->drawException = $exception;
        }
        
        /* Here is the list of the arguments
        1 - Filename (empty : display on screen)
        2 - Background color */
        $drawing = new \BCGDrawing($imagePath, $this->color_white);
        if($this->drawException) {
            $drawing->drawException($this->drawException);
        } else {
            $drawing->setDPI(150); // DEFAULT: 72
            $drawing->setBarcode($code);
            $drawing->draw();
        }
        
        // HEADER to display image on browser when no path is given
        if(empty($imagePath)) {
            // Header that says it is an image (remove it if you save the barcode to a file)
            header('Content-Type: image/png');
            header('Content-Disposition: inline; filename="barcode.png"');
        }
        // Draw (or save) the image into PNG format.
        $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
        
        // GET content and UNLINK
        $imageContent = file_get_contents($imagePath);
        @unlink($imagePath);
        
        return $imageContent;
    }
}
