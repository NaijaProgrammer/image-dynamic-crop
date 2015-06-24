<?php
require_once('C:/wamp/www/code-libraries/PHPUtil/ini.php');
$current_script_paths = UrlInspector::get_path(dirname(__FILE__));
define('IMAGE_CROP_APP_DIR_PATH',  substr($current_script_paths['dir_path'],  0, -1));
define('IMAGE_CROP_APP_HTTP_PATH',  substr($current_script_paths['http_path'], 0, -1));
?>
<?php require_once(IMAGE_CROP_APP_DIR_PATH. '/classes/image-upload-crop.class.php'); ?>
