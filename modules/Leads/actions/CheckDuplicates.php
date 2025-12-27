<?php


class Leads_CheckDuplicates_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		global $adb;		

		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$firstname = $request->get('firstname');
		$lastname = $request->get('lastname');
		
		if($lastname != '')
		{
    		$sql = 'select l.leadid, l.firstname, l.lastname from vtiger_leaddetails l
                    inner join vtiger_crmentity c on c.crmid = l.leadid
                    where c.deleted = 0 and LOWER(l.firstname) = ? and LOWER(l.lastname) = ?';		
    		$params = [strtolower($firstname),strtolower($lastname)];
            $res = $adb->pquery($sql, $params);
    		$result = 'false';
		}
		
		if($res && $adb->num_rows($res) > 0)
		{
		    $name = $adb->query_result($res, 0, 'firstname') . ' ' . $adb->query_result($res, 0, 'lastname');
		    $url = 'index.php?module=Leads&view=Detail&record='.$adb->query_result($res, 0, 'leadid');
		    $result = array('status'=>'true','name'=>$name,'url'=>$url);
		    
		}
		
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();	
		exit;
		
	}
}

?>