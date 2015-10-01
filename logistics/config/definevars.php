<?php

// Defining CONSTANTS
$website_base_url = "http://" . $_SERVER['HTTP_HOST'];
$website_base_url .= preg_replace('@/+$@', '', dirname($_SERVER['SCRIPT_NAME'])) . "/";
$website_base_url = str_replace('webroot/', '', $website_base_url);
$extra_folder = str_replace('webroot', '', getcwd()) . 'extra';

return [
    define('_WEBSITE_URL', $website_base_url),
    // APPLICATION
    define('_APP_TITLE', 'OpenEMIS Logistics'),
    // SERVICE ERROR CODES
    define('_ERR', 'ERR'), //  Error code prefix 
    define('_ERR100', _ERR . '100'), //   Operation not completed due to server error.
    define('_ERR101', _ERR . '101'), //   Email is empty
    define('_ERR102', _ERR . '102'), //   Email not found in database.
    define('_ERR103', _ERR . '103'), //   Invalid Email
    define('_ERR104', _ERR . '104'), //   Email length
    define('_ERR105', _ERR . '105'), //   Missing parameters
    define('_ERR106', _ERR . '106'), //   Username empty
    define('_ERR107', _ERR . '107'), //   First name empty
    define('_ERR108', _ERR . '108'), //   Last name empty
    define('_ERR109', _ERR . '109'), //   Username length
    define('_ERR110', _ERR . '110'), //   First name length
    define('_ERR111', _ERR . '111'), //   Last name length
    define('_ERR112', _ERR . '112'), //   Username already exists 
    define('_ERR113', _ERR . '113'), //   Role is empty
    define('_ERR114', _ERR . '114'), //   Role is invalid
    define('_ERR115', _ERR . '115'), //   Activation link already used 
    define('_ERR117', _ERR . '117'), //   Invalid activation key    service 1204
    define('_ERR118', _ERR . '118'), //   Key is empty 
    define('_ERR119', _ERR . '119'), //   Password is empty
    define('_ERR120', _ERR . '120'), //   Email already exists 
    define('_ERR121', _ERR . '121'), //   Password length is greater than 765 
    define('_ERR122', _ERR . '122'), //   Password not matched with confirm password  
    define('_ERR123', _ERR . '123'), //   Package code empty
    define('_ERR124', _ERR . '124'), //   Package code length is greater than 17
    define('_ERR125', _ERR . '125'), //   Package code already exists 
    define('_ERR126', _ERR . '126'), //   Ashish add comment 
    define('_ERR127', _ERR . '127'), //   Invalid request 
    define('_ERR128', _ERR . '128'), //   Weigth package length is greater than 20
    define('_ERR129', _ERR . '129'), //   Package item qunatity  length is greater than 5   
    define('_ERR130', _ERR . '130'), //   Item code is empty   
    define('_ERR131', _ERR . '131'), //   From Area is required
    define('_ERR132', _ERR . '132'), //   From Location is required
    define('_ERR133', _ERR . '133'), //   To Area is required
    define('_ERR134', _ERR . '134'), //   To Location is required
    define('_ERR135', _ERR . '135'), //   Shipment code is empty
    define('_ERR136', _ERR . '136'), //   Weight is empty
    define('_ERR137', _ERR . '137'), //   No records found
    define('_ERR138', _ERR . '138'), //   Empty package items
    define('_ERR139', _ERR . '139'), //   comments length  greater than 65535
    define('_ERR140', _ERR . '140'), //   No package found for this shipment
    define('_ERR142', _ERR . '142'), //   Username does not exists
    define('_ERR143', _ERR . '143'), //   Activation link is expired
    define('_ERR144', _ERR . '144'), //  Duplicate items exists.
    define('_ERR145', _ERR . '145'), //  File is empty.
    define('_ERR146', _ERR . '146'), // Invalid columns format
       //-------Area level
    define('_ERR147', _ERR . '147'), // level code is empty 
    define('_ERR148', _ERR . '148'), // level name is empty 
    define('_ERR149', _ERR . '149'), // level code already exist 
    define('_ERR150', _ERR . '150'), //   //level code length exceeded
    define('_ERR151', _ERR . '151'), // level name  already exist 
    define('_ERR152', _ERR . '152'), // level name  length exceeded
     //-------Area 
    define('_ERR153', _ERR . '153'), // area code  is empty
    define('_ERR154', _ERR . '154'), // area name  length exceeded
    define('_ERR155', _ERR . '155'), // area name  is empty
    define('_ERR156', _ERR . '156'), // area code  length exceeded
    define('_ERR157', _ERR . '157'), // area code  already exist 
     //-------Items 
    define('_ERR158', _ERR . '158'), //   Item code length exceeded than 20  
    define('_ERR159', _ERR . '159'), //   Item code already exists
    define('_ERR160', _ERR . '160'), //   Item Name length exceeded than 50  
    define('_ERR161', _ERR . '161'), //   Item Name already exists
    //-------courier 
    define('_ERR162', _ERR . '162'), //   Courier Code already exists
    define('_ERR163', _ERR . '163'), //   Courier Name length exceeded than 50
    define('_ERR164', _ERR . '164'), //   Courier Code length exceeded than 20
    define('_ERR165', _ERR . '165'), //   Courier Contact length exceeded than 50
    define('_ERR166', _ERR . '166'), //   Courier Phone length exceeded than 20
    define('_ERR167', _ERR . '167'), //   Courier Email length exceeded than 50
    define('_ERR168', _ERR . '168'), //   Courier Name already exists
    define('_ERR169', _ERR . '169'), // Courier code  is empty
    define('_ERR170', _ERR . '170'), // Courier name  is empty
    define('_ERR171', _ERR . '171'), // Contact is  empty
    define('_ERR172', _ERR . '172'), // Contact length exceeded than 50
    define('_ERR173', _ERR . '173'), // Phone is  empty
    define('_ERR174', _ERR . '174'), // Phone length exceeded than 20   
    define('_ERR175', _ERR . '175'), // Email is  empty
    define('_ERR176', _ERR . '176'), // Email length exceeded than 50
    define('_ERR177', _ERR . '177'), // Fax length exceeded than 20
    // TREE VIEW
    define('_TV_AREA', 'area'),
    // types list
    define('_PACKAGE_LIST_TYPES', 'Package'),
    define('_ITEM_LIST_TYPES', 'Item'),
    define('_ITEM_LIST_TYPES_CODE', 'ItemType'),
    define('_PACKAGE_LIST_TYPES_CODE', 'PackageType'),
    define('_LOCATION_LIST_TYPES', 'Location'),
    define('_CONFIRMATION_LIST_TYPES', 'Confirmation'),
    define('_CONFIRMATION_LIST_TYPES_CODE', 'ConfirmationType'),
    define('_TYPE', 'Type'),
    // SUCCESS/WARNING/ERROR
    define('_SUCCESS', 'SUCCESS'),
    define('_FAILED', 'FAILED'),
    define('_WARNING', 'WARNING'),
    define('_STARTED', 'STARTED'),
    define('_YES', 'yes'),
    define('_NO', 'no'),
    define('_INACTIVE', '0'),
    define('_ACTIVE', '1'),
    define('_VISBLE', '1'), //if 1 then visible 
    define('_ORDER', '1'), //if 1 then visible 
    define('_OK', 'OK'),
    define('_DONE', 'DONE'),
    define('_STATUS', 'STATUS'),
    define('_DEF_PAGE_SIZE', 100),
    define('_GLOBALPARENT_ID', '-1'), //parent id is always  -1
    define('_AREAPARENT_LEVEL', '1'), //area level of parent is always 1
    define('_LevelName', 'Level-'), // for area level name 
    // UPLOADS
    define('_LOGS_PATH_WEBROOT', 'uploads' . '/' . 'logs'),
    define('_LOGS_PATH', WWW_ROOT . _LOGS_PATH_WEBROOT),
    define('_TMP_PATH', WWW_ROOT . 'tmp'),
    define('_CHUNKS_PATH_WEBROOT', 'uploads' . '/' . 'chunks'),
    define('_CHUNKS_PATH', WWW_ROOT . _CHUNKS_PATH_WEBROOT),
    define('_XLS_PATH_WEBROOT', 'uploads' . '/' . 'xls'),
    define('_XLS_PATH', WWW_ROOT . _XLS_PATH_WEBROOT),
    define('_CUSTOMLOG_FILE', 'Log_Import_'), // User status inactive 
    define('_AREA_PATH_WEBROOT', 'uploads' . '/' . 'AREA'), //folder for export of area
    define('_AREA_PATH', WWW_ROOT . _AREA_PATH_WEBROOT),
    // DELIMETER
    define('_DELEM5', '{-}'),
    define('_DELEM3', '-'),
    define('_DELEM7', ' '),
    define('_UNAUTHORIZED_ACCESS', 'Unauthorized Access'),
    // ERROR
    define('_ERROR_6', 'Some Custom Error for internal use'),
    define('_FORGOTPASSWORD_SUBJECT', 'Open EMIS Logistics Admin - Reset your password'), //forgot password 
    define('_SALTPREFIX1', 'abcd#####'), // used in  activation key 
    define('_SALTPREFIX2', 'abcd###*99*'), // used in   activation key
    define('_ADMIN_EMAIL', 'vpdwivedi@dataforall.com'),
    define('_ACTIVATIONEMAIL_SUBJECT', 'Open EMIS Logistics Admin - Registration Details'),
    define('_MODIFYUSEREMAIL_SUBJECT', 'Open EMIS Logistics Admin - User Profile Updated Notification'),
    define('_INVALID_INPUT', 'Invalid Input(s)'),
    // LENGTH
    define('_EMAIL_LENGTH', 100),
    define('_FIRSTNAME_LENGTH', 50),
    define('_LASTNAME_LENGTH', 50),
    define('_USERNAME_LENGTH', 50),
    define('_PASSWORD_LENGTH', 50),
    define('_PKG_CODE_LENGTH', 17),
    define('_COURIER_CODE_LENGTH', 20),
    define('_COURIER_NAME_LENGTH', 50),
    define('_COURIER_CONTACT_LENGTH', 50),
    define('_COURIER_PHONE_LENGTH', 20),
    define('_COURIER_EMAIL_LENGTH', 100),
    define('_COURIER_FAX_LENGTH', 20),
    
    define('_LEVEL_CODE_LENGTH', 3),
    define('_LEVEL_NAME_LENGTH', 50),
    define('_AREA_CODE_LENGTH', 20),
    define('_AREA_NAME_LENGTH', 50),
    // define('_PKGITEM_CODE_LENGTH', 50),
    define('_ITEM_CODE_LENGTH', 20),
    define('_ITEM_NAME_LENGTH', 50),
    define('_PKG_WGHT_LENGTH', 20),
    define('_PKG_ITEMQTY_LENGTH', 5), //Package Item Quantity length is 5
    define('_COMMENTS_LENGTH', 65535), //comments length is greater than 65535
    define('_ATTACHMENT_TYPE_DELIVERY', 'DELIVERY'),
    define('_ATTACHMENT_TYPE_CONFIRMATION', 'CONFIRMATION'),
    // Shipment Label
    define('_SHIP_LABEL_SHIPMENT_CODE', 'Shipment Code : '),
    define('_SHIP_LABEL_PACKAGE_CODE', 'Package Code : '),
    define('_SHIP_LABEL_PACKAGE_COUNT', 'Package : '),
    define('_SHIP_LABEL_PACKAGE_WEIGHT', 'Weight : '),
    define('_SHIP_LABEL_POSTAL_CODE', 'Postal Code - '),
    // Package Label
    define('_PACKAGE_LABEL_SHIPMENT_CODE', 'Shipment Code : '),
    define('_PACKAGE_LABEL_SHIPMENT_DATE', 'Ship Date : '),
    define('_PACKAGE_LABEL_HEADING', 'Packing List'),
    define('_PACKAGE_LABEL_PACKAGE_CODE', 'Package Code'),
    define('_PACKAGE_LABEL_PACKAGE_TYPE', 'Package Type'),
    define('_PACKAGE_LABEL_PACKAGE_WEIGHT', 'Package Weight'),
    define('_PACKAGE_LABEL_ITEM_CODE', 'Item Code'),
    define('_PACKAGE_LABEL_ITEM_NAME', 'Item Name'),
    define('_PACKAGE_LABEL_ITEM_TYPE', 'Item Type'),
    define('_PACKAGE_LABEL_QUANTITY', 'Qty'),
    define('_INSERTKEYS_AREACODE', 'area_code'),
    define('_INSERTKEYS_NAME', 'area_name'),
    define('_INSERTKEYS_LEVEL', 'area_level_id'),
    define('_INSERTKEYS_PARENTNID', 'parent_id'),
    define('_INSERTKEYS_ORDER', 'order'),
    define('_INSERTKEYS_VISIBLE', 'visible'),
    //columns names of Area in Excel sheet
    define('_EXCEL_AREA_CODE', 'AreaCode'),
    define('_EXCEL_AREA_NAME', 'AreaName'),
    define('_EXCEL_AREA_LEVEL', 'AreaLevel'),
    define('_EXCEL_AREA_PARENTID', 'Parent AreaId'),
    define('_MODULE_NAME_AREA', 'AREA'),
    //Area Error log comments names
    define('_AREA_LOG_AREAID_EMPTY', 'Area Code is  empty!!'), //area id is empty  //_AREA_LOGCOMMENT1 //
    define('_AREA_LOG_OPERATION_FAILED', 'Record not saved'), // error in insert //_AREA_LOGCOMMENT2  
    define('_AREA_LOG_PARENTID_MISSING', 'Parent id not found!!'), // error Parent id not found //_AREA_LOGCOMMENT3
    define('_AREA_LOG_INVALIDDETAILS', 'Invalid Details'), // error Invalid details //_AREA_LOGCOMMENT4 //
    define('_AREA_LOG_INVALID_LEVEL', 'Invalid Area Level'), // error Invalid details //_AREA_LOGCOMMENT5
    define('_AREA_LOG_AREAID_DUPLICATE', 'Duplicate entry of Area Code '), // error Invalid details //_AREA_LOGCOMMENT6
    //File upload error
    define('_ERROR_UNACCEPTED_METHOD', 'File uploaded via unaccepted method.'),
    define('_ERROR_UPLOAD_FAILED', 'File upload failed.'),
    define('_ERROR_LOCATION_INACCESSIBLE', 'This location cannot be accessed.'),
];
?>
