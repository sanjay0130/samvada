<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/


class Potentials_CreateClaim_View extends Vtiger_IndexAjax_View {
	
	function __construct() {
		$this->exposeMethod('AddClaim');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	public function AddClaim(Vtiger_Request $request)
	{
		$poid = $request->get('poid');
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$result=$moduleModel->create_claims($poid);
		//$result=array("claim 01 created","<font color=\"#FF0000\">claim 02 exists</font>","claim 03 created");
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult(array("send_success"=>$result));
		$response->emit();
		
	}
	
}