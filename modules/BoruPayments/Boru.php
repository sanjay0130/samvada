<?php
ini_set("soap.wsdl_cache_enabled", 0);
class BoruLicense {
	var $module = "";
	var $cypher = "Boru is encrypting its files to prevent unauthorized distribution";
	var $featurestring = "";
	var $result = "";
	var $message = "";
	var $expires = "";
	var $valid = false;
	var $file = "";
	var $productName = "";
	var $who = "";
	var $license = "";
	var $server = "";
	var $url = "";
	function BoruLicense($module="",$url="") {
		global $_REQUEST,$currentModule,$root_directory,$site_URL;
		if(substr($site_URL,-1) != "/") {
			$site_URL.="/";
		}
		if($url != "") { $this->url = $url; }
		if($module!="") { $this->module = $module; }
		if($this->module == "") { $this->module = $currentModule; }
		$this->server = $site_URL;
		if($this->file == "") {
			if(substr($root_directory,-1) != "/" && substr($root_directory,-1) != "\\") { $root_directory.="/"; }
			$this->file = $root_directory."test/".$this->module.".boru";
		}
		$this->setDefaults();
		if(file_exists($this->file)) {
			if($this->readLicenseFile() === false) {
				$this->validate();
			}
		} else {
			if($url == "cron") {
				exit("License not found");
			}
			$this->install();
		}
		if(isset($this->expires) && $this->expires != "" && $this->expires != "null") {
			list($year,$month,$day) = explode("-",$this->expires);
			if($year <= date("Y")) {
				if($month <= date("m")) {
					if($day <= date("d")) {
						$filename = $this->file;
						unlink($filename);
						$this->message = "License Expired";
						if($url == "cron") {
							exit($this->message);
						}
						$this->install();
					}
				}
			}
		}
		if(isset($_REQUEST["boruDeactivate"])) {
			$this->deactivate();
			unset($_REQUEST["boruDeactivate"]);
			$this->BoruLicense();
		}
		return true;
	}
	function install() {
		global $_POST, $root_directory, $site_URL;
		$errormsg = "&nbsp;";
		if(isset($_POST["boruRegister"])) {
			$company = $_POST["company"];
			$license = $_POST["license"];
			$this->who = $company;
			$this->license = $license;
			if($company == "" || $license == "") { 
				$this->setDefaults();
			}
			$this->checkValidate();
			if($this->result == "bad" || $this->result == "invalid") {
				if($this->message != "") { $errormsg = "License Failed with message: ".$this->message."<br>"; }
				else { $errormsg = "Invalid License/Company combination or Invalid License<br>"; }
				$errormsg.="Please try again or contact <a href='http://www.boruapps.com/' target='_new'>Boru</a> for assistance.";
			} else {
				// do something
				$this->createBoruFile($this->productName, $this->who, $this->license, "",$this->message,$this->expires);
				return true;
			}
		}
		try {
			$client = new SoapClient("http://license.boruapps.com/soap.php?wsdl",array('exceptions' => True));
			$arr = $client->checkWhitelist($this->productName);
			$this->result = $arr["result"];
			if($arr["result"] == "ok") {
				return true;
			}
		}
		catch(Exception $exception) {
		}
		
		
		if($errormsg == "&nbsp;" && $this->message != "") {
			$errormsg = htmlspecialchars_decode(htmlspecialchars_decode($this->message));
		}
		if($this->url != "") { $urlstring = "action='{$this->url}'"; } else { $urlstring = ""; }
		$dirname = strtolower(dirname(__FILE__));
		echo <<<HTMLTABLE
			<br /><br /><br /><div align="center">
			<h2>Boru Module Registration</h2>
			<br />
			<form method='post' $urlstring>
			<input type='hidden' name='boruRegister' value='true'>
			<table border="0">
			<tr><th colspan='2'>Thank you for Purchasing $this->productName</th></tr>
			<tr><td colspan='2'>Please fill out the information below and click 'Activate' to begin using your module.</td></tr>
			<tr><td colspan='2'>&nbsp;</td></tr>
			<tr><td>Name or Company:</td><td><input type="text" name="company"/></td></tr>
			<tr><td>License:</td><td><input type="text" name="license"/></td></tr>
			<tr><td colspan='2' align='center'><b>$errormsg</b></td></tr>
			<tr><td colspan='2' align='center'><input type='submit' value='Activate'/></td></tr>
			<tr><td colspan='2' align='center'>&nbsp;</td></tr>
			<tr><td colspan='2' align='center'>&nbsp;</td></tr>
			<tr><th colspan='2' align='center'>On a private network or Offline?</th></tr>
			<tr><td colspan='2' align='center'>Email the information below to support@boruapps.com:</td></tr>
			<tr><td>PROD</td><td>{$this->productName}</td></tr>
			<tr><td>SNAME</td><td>{$site_URL}</td></tr>
			<tr><td>HHOST</td><td>{$_SERVER['HTTP_HOST']}</td></tr>
			<tr><td>DIRNAME</td><td>$root_directory</td></tr>
			</table>
			</form>
			</div>	
HTMLTABLE;
		exit();
	}
	
	function printDeactivateLink($type="echo") {
		global $_SERVER;
		$urlstring=end( explode( '/', $_SERVER['REQUEST_URI'] ) );
		$link = "<a href='$urlstring"."&boruDeactivate=1'>Deactivate License</a>";
		if($type == "echo") { echo $link; }
		else { return $link; }
	}
	function deactivate() {
		global $site_URL;
		global $root_directory;
		$data = "<data>
		<license>{$this->license}</license>
		<who>{$this->who}</who>
		<product>{$this->productName}</product>
		</data>";
		try {
			$client = new SoapClient("http://license.boruapps.com/soap.php?wsdl",array('exceptions' => True));
			$arr = $client->deactivate($data);
			$this->result = $arr["result"];
			$this->message = $arr["message"];
			unlink($this->file);
		}
		catch(Exception $exception) {
			$this->result = "bad";
			$this->message = "Error.<br>";
		}
	}
	function setDefaults() {
		global $currentModule;
		if($this->module == "") {
			$this->module = $currentModule;
		}
		$this->license = "";
		$this->who = "";
		$this->productName = $this->module;
	}
	function readLicenseFile() {
		global $root_directory, $site_URL;
		if(substr($site_URL,-1) != "/") {
			$site_URL.="/";
		}
		$input = $this->decrypt(file_get_contents($this->file));
		$product = $this->gssX($input,"<product>","</product>");
		$server = $this->gssX($input,"<server>","</server>");
		$dirname = $this->gssX($input,"<dirname>","</dirname>");
		if(substr($root_directory,-1) != "/" && substr($root_directory,-1) != "\\") {
			$root_directory.="/";
		}
		if(strtolower($product) != strtolower($this->productName) || $this->urlClean(strtolower($server)) != $this->urlClean(strtolower($this->server)) || $this->slashClean(strtolower($dirname)) != $this->slashClean(strtolower($root_directory))) {
			$this->setDefaults();
			return false;
		}
		$this->featurestring = $this->gssX($input,"<features>","</features>");
		$this->license = $this->gssX($input,"<license>","</license>");
		$this->who = $this->gssX($input,"<who>","</who>");
		$this->message = $this->gssX($input,"<message>","</message>");
		$this->expires = $this->gssX($input,"<expires>","</expires>");
		return true;
	}
	function validate() {
		$this->checkValidate();
		if($this->result == "ok" || $this->result == "valid") {
			return true;
		} else {
			$this->install();
			return false;
		}
		//if($this->result == "bad" || $this->result == "invalid") {
		//	
		//} else {
		//	
		//}
	}
	function checkValidate() {
		global $site_URL;
		global $root_directory;
		$data = "<data>
		<license>{$this->license}</license>
		<who>{$this->who}</who>
		<product>{$this->productName}</product>
		<uri>{$_SERVER['REQUEST_URI']}</uri>
		<host>{$_SERVER['HTTP_HOST']}</host>
		<sig>{$_SERVER["SERVER_SIGNATURE"]}</sig>
		<servername>$site_URL</servername>
		<dirname>$root_directory</dirname>
		</data>";
		try {
			$client = new SoapClient("http://license.boruapps.com/soap.php?wsdl",array('exceptions' => True));
			$arr = $client->validate($data);
			$this->result = $arr["result"];
			$this->message = $arr["message"];
			if(isset($arr["expires"])) { $this->expires = $arr["expires"]; }
		}
		catch(Exception $exception) {
			$this->result = "bad";
			$this->message = "Unable to connect to licensing service. Please either check the server's internet connection, or proceed with offline licensing.<br>";
		}
		
	}
	function createBoruFile($product,$who,$license,$features,$message="",$expires="") {
		global $site_URL,$root_directory;
		if(substr($site_URL,-1) != "/") {
			$site_URL.="/";
		}
		$filename = $this->file;
		$dirname = $root_directory;
		if(file_exists($filename)) { unlink($filename); }
		$string =<<<EOF
<data>
	<product>$product</product>
	<who>$who</who>
	<license>$license</license>
	<features>$features</features>
	<server>$site_URL</server>
	<dirname>$dirname</dirname>
	<message>$message</message>
	<expires>$expires</expires>
</data>
EOF;
		$data = $this->encrypt($string);
		$this->write_file($filename,$data);
	}
	function encrypt($str){
		$key = $this->cypher;
		for($i=0; $i<strlen($str); $i++) {
			$char = substr($str, $i, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}
		return urlencode(base64_encode($result));
	}
	function decrypt($str){
		$str = base64_decode(urldecode($str));
		$result = '';
		$key = $this->cypher;
		for($i=0; $i<strlen($str); $i++) {
			$char = substr($str, $i, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
		}
		return $result;
	}
	function write_file ($filename,$content) {
		if(!file_exists($filename)) {
			$fh = fopen($filename,'w'); fclose($fh);
		}
		if (is_writable($filename)) {
			if (!$handle = fopen($filename, 'a')) {
				print "Cannot open file ($filename)";
				exit;
			}
			if (!fwrite($handle, $content)) {
				print "Cannot write to file ($filename)";
				exit;
			}
			fclose($handle);
		}
		else {
			print "The file $filename is not writable";
		}
	}
	function urlClean($string) {
		$string = str_replace("https://","",$string);
		$string = str_replace("HTTPS://","",$string);
		$string = str_replace("http://","",$string);
		$string = str_replace("HTTP://","",$string);
		if(strtolower(substr($string,0,4)) == "www.") {
			$string = substr($string,4);
		}
		return $string;
	}
	function slashClean($string) {
		$string = str_replace("\\","",$string);
		$string = str_replace("/","",$string);
		return $string;
	}
	function gssX($str_All, $start_str="included in output", $end_str="included in output") {
		$str_return = "";
		$start_str_match_post = strpos($str_All, $start_str);
		if($start_str_match_post !== false) {
			$end_str_match_post = strpos($str_All, $end_str, $start_str_match_post);
			if ($end_str_match_post !== false) {
				//$end_str_match_post = $end_str_match_post + strlen($end_str);
				$start_str_get = $start_str_match_post;
				$length_str_get = $end_str_match_post + strlen($end_str) - $start_str_get;
				$str_return = substr($str_All, $start_str_get, $length_str_get);
			}	// + strlen($start_str)
		}
		$str_return = substr($str_return,strlen($start_str));
		$len = strlen($str_return) - strlen($end_str);
		$str_return = substr($str_return,0,$len);
		return $str_return;
	}
}
?>