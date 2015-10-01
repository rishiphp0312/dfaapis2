<?php

$errorLogFilepath =  str_replace('webroot', '', getcwd()) . 'logs' . DS . 'debug.log';
echo '<pre>';
echo file_get_contents($errorLogFilepath);
exit;