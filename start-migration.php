<?php

echo '----'; die;

require_once("config.php");
    /**
    * URL Verfication - Required to overcome Apache mis-configuration and leading to shared setup mode.
    */
    if (file_exists('config_override.php')) {
        include_once 'config_override.php';
    }

   // require_once "vendor/autoload.php";

    //Overrides GetRelatedList : used to get related query
    //TODO : Eliminate below hacking solution
    include_once 'include/Webservices/Relation.php';

    include_once 'vtlib/Vtiger/Module.php';
    include_once 'includes/main/WebUI.php';

    require_once("libraries/HTTP_Session2/HTTP/Session2.php");
    require_once 'include/Webservices/Utils.php';
    require_once("include/Webservices/State.php");
    require_once("include/Webservices/OperationManager.php");
    require_once("include/Webservices/SessionManager.php");
    require_once("include/Zend/Json.php");
    require_once('include/logging.php');



    global $adb, $current_user;
    $db = PearDatabase::getInstance();



    require_once('modules/Users/Users.php');

    // Set current user to admin (id = 1)
    $current_user = new Users();
    $current_user->retrieveCurrentUserInfoFromFile(1);

    echo 'Current User ID: '.$current_user->id.PHP_EOL;
    echo 'User Name: '.$current_user->user_name.PHP_EOL;
    echo 'Is Admin: '.$current_user->is_admin.PHP_EOL; 



    $db->pquery("ALTER TABLE vtiger_inventorychargesrel ADD KEY record_idx (recordid)", array());	



echo '----------- END -------********************************------------';



?>