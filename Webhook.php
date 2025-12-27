<?php
require_once("WS_Curl_Class.php");

$data = file_get_contents("php://input");
//mail("seoguruvelu@gmail.com", "Response", "Response:".$data, "seoguruvelu@gmail.com");
$result = json_decode($data, true);

// Web address to connect to 
define("CONFIG_URL", "https://crm.sleepworksmedical.com/");

// Username in CRM
define("CONFIG_NAME", "larry");

// Access Key for given username (found under "My Preferences")
define("CONFIG_KEY", "3w02ufmmp5UN9Znp");
    
// If notification comes from webhook and result available
if($result){
    
    // query to contacts using webservice
    $ws = new WS_Curl_Class(CONFIG_URL . "/webservice.php", CONFIG_NAME, CONFIG_KEY);
    $ws->login();
    $email = $result['client']['emailAddress'];
    $query = "SELECT * FROM Contacts WHERE email='$email';";
    
    $query_result = $ws->query($query);
    if($query_result){
        //update contacts
        $ws->update_vtiger_contact($query_result, $result);
        // update or create events
        $calendarid = $result['calendarid'];
        $query_event = "SELECT * FROM Events WHERE cf_950='$calendarid';";
        
        $query_event_result = $ws->query($query_event);
        if($query_event_result){
           
            //update event
            $ws->update_vtiger_event($result, $query_event_result);
        }else{
            //create events
            $ws->create_vtiger_event($result, $query_result[0]['id']);
        }
        
        
    }else{
        // create contacts
        $contactid = $ws->create_vtiger_contact($result);
        //create events
        $ws->create_vtiger_event($result, $contactid);
    }
}

?>
