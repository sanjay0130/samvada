<?php
/***********************************************************************************
* CRM Extension for webservice access using curl
* Version: 1.0
* Copyright (C) crm-now
* All Rights Reserved
* www.crm-now.de
************************************************************************************/

class WS_Curl_Class {
	var $endpointUrl;
	var $userId;
	var $userName;
	var $userKey;
	var $token;
	var $curl_handler;
	
	var $defaults = array(
			CURLOPT_HEADER => 0,
			// CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => false,	// ssl fix
			CURLOPT_SSL_VERIFYHOST => false	// ssl fix
		);
	
	//constructor saves the values
	function __construct($url, $name, $key) {
		$this->endpointUrl=$url;
		$this->userId=0;
		$this->userName=$name;
		$this->userKey=$key;
		$this->token=0;
	}

	function getChallenge() {
		$curl_handler = curl_init();
		$params = array("operation" => "getchallenge", "username" => $this->userName);
		$options = array(CURLOPT_URL => $this->endpointUrl."?".http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		
		if($jsonResponse["success"]==false) {
			//exit if something went wrong
			//die("getChallenge failed: ".$jsonResponse["error"]["message"]."<br>");
            die(mail("snoopygarcha@gmail.com", "getchallenge error", "getchallenge failed:".$result, "snoopygarcha@gmail.com"));
           
		}

		$challengeToken = $jsonResponse["result"]["token"];

		return $challengeToken;
	}

	function login() {
		$curl_handler = curl_init();
		$token = $this->getChallenge();
		//create md5 string containing user accesskey from my preference page
		//and the challenge token obtained from get challenge result
		$generatedKey = md5($token.$this->userKey);
		
		$params = array("operation" => "login", "username" => $this->userName, "accessKey" => $generatedKey);
		$options = array(CURLOPT_URL => $this->endpointUrl, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);

		$this->userId = $jsonResponse["result"]["userId"];
		if($jsonResponse["success"]==false) {
			//die("Login failed: ".$jsonResponse["error"]["message"]."<br>".$token."<br>");
            die(mail("snoopygarcha@gmail.com", "login error", "login failed: ".$result, "snoopygarcha@gmail.com"));
            
		}
		
		$sessionId = $jsonResponse["result"]["sessionName"];
		//save session id
		$this->token=$sessionId;
		return true;
	}
	
	function query($query) {
		$curl_handler = curl_init();
		$params = array("operation" => "query", "sessionName" => $this->token, "query" => $query);
		$options = array(CURLOPT_URL => $this->endpointUrl."?".http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		if($jsonResponse["success"]==false) {
			//die("Query failed: ".$jsonResponse["error"]["message"]);
            
			$email_to 		= "snoopygarcha@gmail.com";
			$email_subject 	= "Query Failed";			
			$email_headers = "MIME-Version: 1.0" . "\r\n";
			$email_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$email_headers .= 'From: "SleepWorks Medical" <admin@sleepwm.com>' . "\r\n";

			$email_message = '<html><body><table width="100%" cellspacing="2" cellpadding="2">';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">jsonResponse: </td><td style="background-color: #f5f5f5; padding:10px;">'.$jsonResponse["error"]["message"].'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Result: </td><td style="background-color: #fcfcfc; padding:10px;">'.$result.'</td></tr>';
			$email_message .= '</body></html>';
			
            //mail($email_to, $email_subject, $email_message, $email_headers);
			die(mail($email_to, $email_subject, $email_message, $email_headers));
            
		}
		//Array of retrieved objects
		$retrievedObjects = $jsonResponse["result"];
		
		return $retrievedObjects;
	}
	
	function listtypes() {
		$curl_handler = curl_init();
		$params = array("operation" => "listtypes", "sessionName" => $this->token);
		$options = array(CURLOPT_URL => $this->endpointUrl."?".http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		
		if($jsonResponse["success"]==false) {
			//exit if something went wrong
			die("Listtypes failed: ".$jsonResponse["error"]["message"]."<br>");
		}

		$description = $jsonResponse["result"];

		return $description;
	}	
	
	function describe($type) {
		$curl_handler = curl_init();
		$params = array("operation" => "describe", "sessionName" => $this->token, "elementType" => $type);
		$options = array(CURLOPT_URL => $this->endpointUrl."?".http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		
		if($jsonResponse["success"]==false) {
			//exit if something went wrong
			die("Describe failed: ".$jsonResponse["error"]["message"]."<br>");
		}

		$description = $jsonResponse["result"];

		return $description;
	}	
	
	function retrieve($objectId) {
		$curl_handler = curl_init();
		$params = array("operation" => "retrieve", "sessionName" => $this->token, "id" => $objectId);
		$options = array(CURLOPT_URL => $this->endpointUrl."?".http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		
		if($jsonResponse["success"]==false) {
			//exit if something went wrong
			//die("Retrieve failed: ".$jsonResponse["error"]["message"]."<br>");
            die(mail("snoopygarcha@gmail.com", "Retrieve error", "Retrieve failed: ".$result, "snoopygarcha@gmail.com"));
            
		}

		$description = $jsonResponse["result"];

		return $description;
	}
	
	function create($type, $element, $filepath = '') {
		$curl_handler = curl_init();
		$params = array("operation" => "create", "format" => "json", "sessionName" => $this->token, "elementType" => $type, "element" => json_encode($element));
		$options = array(CURLOPT_URL => $this->endpointUrl, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($params));
		if ($filepath != '') {
				$filename = pathinfo($filepath, PATHINFO_BASENAME);
				$size = filesize($filepath);
				$add_options = array(CURLOPT_HTTPHEADER => "Content-Type: multipart/form-data", CURLOPT_INFILESIZE => $size);
				$add_params = array("filedata" => "@$filepath", "filename" => $filename);
				
				$options += $add_options;
				$this->defaults[CURLOPT_HEADER] = 1;
				$options[CURLOPT_POSTFIELDS] = $params + $add_params;
		}
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		$this->defaults[CURLOPT_HEADER] = 0;
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		if($jsonResponse["success"]==false) {
			//print_r($jsonResponse);
			//die("Create failed: ".$jsonResponse["error"]["message"]."<br>");
            mail("snoopygarcha@gmail.com", "Create Error", "create failed:".$result, "snoopygarcha@gmail.com");
            
		}
		
		return $jsonResponse["result"];
	}
	
	function update($element) {
		$curl_handler = curl_init();
		$params = array("operation" => "update", "format" => "json", "sessionName" => $this->token, "element" => json_encode($element));
		$options = array(CURLOPT_URL => $this->endpointUrl, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		if($jsonResponse["success"]==false) {
			//die("Update failed: ".$jsonResponse["error"]["message"]."<br>");
            mail("snoopygarcha@gmail.com", "Update Error", "Update failed:".$result, "snoopygarcha@gmail.com");
            
		}
		
		return $jsonResponse["result"];
	}
	
	function updateDocRel($docid, $relids, $preserve = true) {
		$curl_handler = curl_init();
		$params = array("operation" => "update_document_relations", "docid" => $docid, "sessionName" => $this->token, "relids" => $relids, "preserve" => var_export($preserve, true));
		$options = array(CURLOPT_URL => $this->endpointUrl, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($params));
		curl_setopt_array($curl_handler, ($this->defaults + $options));
		$result = curl_exec($curl_handler);
		if (!$result) {
			die(curl_error($curl_handler));
		}
		$jsonResponse = json_decode($result, true);
		if($jsonResponse["success"]==false) {
			die("updateDocRel failed: ".$jsonResponse["error"]["message"]."<br>");
			mail("snoopygarcha@gmail.com", "Update Doc Rel", "Update Doc Rel:".$result, "snoopygarcha@gmail.com");
		}
		
		return $jsonResponse["result"];
	}
	
	
    
    // Create contacts in vtiger
    function create_vtiger_contact($result){
        
        //contacts param
        $email = $result['client']['emailAddress'];
        $dateofbirthyyyymmdd = $result['client']['dateOfBirth'];
        $name = $result['client']['fullName'];
        $address = $result['client']['address1'];
        $city = $result['client']['city'];
        
        $phone = $result['client']['cellPhone'];
        $homePhone = $result['client']['homePhone'];
        
        foreach($result['client']['fields'] as $keyf=>$valf){
            if($valf['schedulerPreferenceFieldDefnId'] == 966422){
                $referringdoctor = $valf['value'];
            }
            if($valf['schedulerPreferenceFieldDefnId'] == 966423){
                $extendedmedicalcompany = $valf['value'];
            }
            if($valf['schedulerPreferenceFieldDefnId'] == 966331){
                $dateofbirthyyyymmdd = $valf['value'];
            }
            
        }
        
//        $referringdoctor = $result['fieldDataMap']['966422'];
//        $extendedmedicalcompany = $result['fieldDataMap']['966423'];
        
        if (strpos($name, ' ') !== false) {
            $name = explode(' ', $name);
            $firstname = $name[0];
            for($i = 1; $i < count($name); $i++) {
                $lastname .= $name[$i] . ' ';
            }
            $lastname = trim($lastname);
        } else {
            $lastname = trim($name);
        }
        
        $assigned_to = '19x1';
        $firstname = $firstname;
        $lastname = $lastname;
        $email = $email;
        $phone = $phone;
        $address = $address;
        $city = $city;
        
        $contactParams  = array(
            'assigned_user_id'=>$assigned_to,
            'firstname'=>$firstname,
            'lastname'=>$lastname,
            //'time_start'=>$time_start,
            'email'=>$email,
            'mobile'=>$phone,
            'mailingstreet'=>$address,
            'mailingcity'=>$city,
            'birthday'=>date('Y-m-d', strtotime($dateofbirthyyyymmdd)),
            'cf_703'=>$referringdoctor,
            'cf_707'=>$extendedmedicalcompany
        );
        
        $response = $this->create("Contacts", $contactParams);
        if($response){
            $contact_id = $response['id'];
			
			$email_to 		= "snoopygarcha@gmail.com";
			$email_subject 	= "Timetap - NEW CONTACT IN CRM - ".$firstname. " ".$lastname;			
			
			$email_headers = "MIME-Version: 1.0" . "\r\n";
			$email_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$email_headers .= 'From: "SleepWorks Medical" <admin@sleepwm.com>' . "\r\n";

			$email_message = '<html><body><table width="100%" cellspacing="2" cellpadding="2">';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">First Name: </td><td style="background-color: #f5f5f5; padding:10px;">'.$firstname.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Last Name: </td><td style="background-color: #fcfcfc; padding:10px;">'.$lastname.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">E-mail: </td><td style="background-color: #f5f5f5; padding:10px;">'.$email.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Phone: </td><td style="background-color: #fcfcfc; padding:10px;">'.$phone.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Address: </td><td style="background-color: #f5f5f5; padding:10px;">'.$address.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">City: </td><td style="background-color: #fcfcfc; padding:10px;">'.$city.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">DOB: </td><td style="background-color: #f5f5f5; padding:10px;">'.$dateofbirthyyyymmdd.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Referring Doctor: </td><td style="background-color: #fcfcfc; padding:10px;">'.$referringdoctor.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Extended Medical: </td><td style="background-color: #f5f5f5; padding:10px;">'.$extendedmedicalcompany.'</td></tr>';
			$email_message .= '</body></html>';
			
            mail($email_to, $email_subject, $email_message, $email_headers);
			
            return $contact_id;
        }else{
            return 0;
        }
        
        
    }
    
    // Update contacts in vtiger
    function update_vtiger_contact($query_result, $result){
        $contactid = $query_result[0]['id'];
        $retrive_result = $this->retrieve($contactid);
        
        //contacts param
        //$email = $result['client']['emailAddress'];
        $dateofbirthyyyymmdd = $result['client']['dateOfBirth'];
        $firstName = $result['client']['firstName'];
        $lastName = $result['client']['lastName'];
        $address = $result['client']['address1'];
        $city = $result['client']['city'];
        
        $phone = $result['client']['cellPhone'];
        $homePhone = $result['client']['homePhone'];
        
        foreach($result['client']['fields'] as $keyf=>$valf){
            if($valf['schedulerPreferenceFieldDefnId'] == 966422){
                $referringdoctor = $valf['value'];
            }
            if($valf['schedulerPreferenceFieldDefnId'] == 966423){
                $extendedmedicalcompany = $valf['value'];
            }
            if($valf['schedulerPreferenceFieldDefnId'] == 966331){
                $dateofbirthyyyymmdd = $valf['value'];
            }
        }
        
//        $referringdoctor = $result['fieldDataMap']['966422'];
//        $extendedmedicalcompany = $result['fieldDataMap']['966423'];
        
        if (strpos($name, ' ') !== false) {
            $name = explode(' ', $name);
            $firstname = $name[0];
            for($i = 1; $i < count($name); $i++) {
                $lastname .= $name[$i] . ' ';
            }
            $lastname = trim($lastname);
        } else {
            $lastname = trim($name);
        }
        
        if($retrive_result){
            $retrive_result['firstname'] = $firstName;
            $retrive_result['lastname'] = $lastName;
            $retrive_result['mailingstreet'] = $address;
            $retrive_result['mailingcity'] = $city;
            //$retrive_result['mobile'] = $phone;
            //$retrive_result['birthday'] = date('Y-m-d', strtotime($dateofbirthyyyymmdd));
            //$retrive_result['cf_703'] = $referringdoctor;
            //$retrive_result['cf_707'] = $extendedmedicalcompany;
            
            $response = $this->update($retrive_result);
            if($response ){
                 
				$email_to 		= "snoopygarcha@gmail.com";
				$email_subject 	= "TimeTap Updated Contact in CRM - ".$firstName. " ".$lastName;			
				
				$email_headers = "MIME-Version: 1.0" . "\r\n";
				$email_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$email_headers .= 'From: "SleepWorks Medical" <admin@sleepwm.com>' . "\r\n";

				$email_message = '<html><body><table width="100%" cellspacing="2" cellpadding="2">';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">First Name: </td><td style="background-color: #f5f5f5; padding:10px;">'.$firstName.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Last Name: </td><td style="background-color: #fcfcfc; padding:10px;">'.$lastName.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Phone: </td><td style="background-color: #f5f5f5; padding:10px;">'.$phone.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Address: </td><td style="background-color: #fcfcfc; padding:10px;">'.$address.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">City: </td><td style="background-color: #f5f5f5; padding:10px;">'.$city.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">DOB: </td><td style="background-color: #fcfcfc; padding:10px;">'.$dateofbirthyyyymmdd.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Referring Doctor: </td><td style="background-color: #f5f5f5; padding:10px;">'.$referringdoctor.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Extended Medical: </td><td style="background-color: #fcfcfc; padding:10px;">'.$extendedmedicalcompany.'</td></tr>';
				$email_message .= '</body></html>';
				
				mail($email_to, $email_subject, $email_message, $email_headers);

				  
            }
            
        }
    }
    
    // create events in vtiger
    public function create_vtiger_event($result, $contactid){
        //Event Param
        $service = $result['reason']['reasonDesc'];
        $location = $result['location']['locationName'];
        $description = $result['note'];
        $start = strtotime($result['startDateTime']);
        $end = strtotime($result['endDateTime']);
        $calendarid = $result['calendarid'];
        
        $assigned_to = '19x1';
        $subject = $service;
        $date_start = date ("Y-m-d", $start);
        $time_start = date ("H:i:s", $start);
        $due_date = date ("Y-m-d", $end);
        $time_end = date ("H:i:s", $end);
        $location = $location;
        $activitytype = 'Meeting';
        $eventstatus = 'Planned';
        //$contact_id = '12x'.$contactid;
        $contact_id = $contactid;
        
        $eventParams  = array(
            'assigned_user_id'=>$assigned_to,
            'subject'=>$subject,
            'date_start'=>$date_start,
            'time_start'=>$time_start,
            'due_date'=>$due_date,
            'time_end'=>$time_end,
            'location'=>$location,
            'eventstatus'=>$eventstatus,
            'activitytype'=>$activitytype,
            'duration_hours'=>0,
            'visibility'=>'Public',
            'description'=>$description,
            'contact_id'=>$contact_id,
            'cf_950'=>$calendarid
        );
        $response = $this->create("Events", $eventParams);
        if($response){
            $eventid = $response['id'];
			
			$email_to 		= "snoopygarcha@gmail.com";
			$email_subject 	= "TimeTap Created New Event in CRM - ".$service." at ".$location." on ".$date_start." ".$time_start." ".$time_end;			
			
			$email_headers = "MIME-Version: 1.0" . "\r\n";
			$email_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$email_headers .= 'From: "SleepWorks Medical" <admin@sleepwm.com>' . "\r\n";

			$email_message = '<html><body><table width="100%" cellspacing="2" cellpadding="2">';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Service: </td><td style="background-color: #f5f5f5; padding:10px;">'.$service.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Location: </td><td style="background-color: #fcfcfc; padding:10px;">'.$location.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Clinic Notes: </td><td style="background-color: #f5f5f5; padding:10px;">'.$description.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Contact ID: </td><td style="background-color: #fcfcfc; padding:10px;">'.$contact_id.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Time Tap Appointment ID: </td><td style="background-color: #f5f5f5; padding:10px;">'.$calendarid.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Appointment Date: </td><td style="background-color: #fcfcfc; padding:10px;">'.$date_start.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Start Time: </td><td style="background-color: #f5f5f5; padding:10px;">'.$time_start.'</td></tr>';
			$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">End Time: </td><td style="background-color: #fcfcfc; padding:10px;">'.$time_end.'</td></tr>';
			$email_message .= '</body></html>';
			
            mail($email_to, $email_subject, $email_message, $email_headers);
            
        }
    }
    
    // create events in vtiger
    public function update_vtiger_event($result, $result_event){
        
        //Update event
        $retrive_event_result = $this->retrieve($result_event[0]['id']);
        //Event Param
        $service = $result['reason']['reasonDesc'];
        $location = $result['location']['locationName'];
        $description = $result['note'];
        $start = strtotime($result['startDateTime']);
        $end = strtotime($result['endDateTime']);
        
        $subject = $service;
        $date_start = date ("Y-m-d", $start);
        $time_start = date ("H:i:s", $start);
        $due_date = date ("Y-m-d", $end);
        $time_end = date ("H:i:s", $end);
        $location = $location;
        //$contact_id = $contactid;
        
        if($retrive_event_result){
            $retrive_event_result['subject'] = $subject;
            $retrive_event_result['date_start'] = $date_start;
            $retrive_event_result['time_start'] = $time_start;
            $retrive_event_result['due_date'] = $due_date;
            $retrive_event_result['time_end'] = $time_end;
            $retrive_event_result['location'] = $location;
            $retrive_event_result['description'] = $description;
            //$retrive_event_result['contact_id'] = $result_event[0]['contact_id'];
            

            
            $response = $this->update($retrive_event_result);
            if($response ){
                
                //mail("swmlog@gmail.com", "Event Update Successfully", "Event Update Successfully:".$response['id'], "swmlog@gmail.com");
				//mail("snoopygarcha@gmail.com", "TimeTap Updated Event in CRM", "Service: ".$service." | Clinic Notes: ".$description." | Appointment Date: ".$date_start." | Start Time: ".$time_start." | End Time: ".$time_end." | Location: ".$location." | Assigned To: ".$location, "snoopygarcha@gmail.com");
				
				$email_to 		= "snoopygarcha@gmail.com";
				$email_subject 	= "TimeTap Existing Event Updated in CRM - ".$service." at ".$location." on ".$date_start." ".$time_start." ".$time_end;			
				
				$email_headers = "MIME-Version: 1.0" . "\r\n";
				$email_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$email_headers .= 'From: "SleepWorks Medical" <admin@sleepwm.com>' . "\r\n";

				$email_message = '<html><body><table width="100%" cellspacing="2" cellpadding="2">';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Service: </td><td style="background-color: #f5f5f5; padding:10px;">'.$service.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Location: </td><td style="background-color: #fcfcfc; padding:10px;">'.$location.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Clinic Notes: </td><td style="background-color: #f5f5f5; padding:10px;">'.$description.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">Appointment Date: </td><td style="background-color: #fcfcfc; padding:10px;">'.$date_start.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #f5f5f5; padding:10px;">Start Time: </td><td style="background-color: #f5f5f5; padding:10px;">'.$time_start.'</td></tr>';
				$email_message .= '<tr><td style="background-color: #fcfcfc; padding:10px;">End Time: </td><td style="background-color: #fcfcfc; padding:10px;">'.$time_end.'</td></tr>';
				$email_message .= '</body></html>';
				
				mail($email_to, $email_subject, $email_message, $email_headers);
			
			
                
            }
            
        }
       
    }
    

}
?>
