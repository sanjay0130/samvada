<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
error_reporting(0);
class Cashflow4You_Detail_View extends Vtiger_Detail_View {

	function process(Vtiger_Request $request) {

            $viewer = $this->getViewer($request);
            $viewer->assign("VERSION", Cashflow4You_Version_Helper::$version);
            parent::process($request);
	}
}
