<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class SQLReports_ExportReport_View extends Vtiger_View_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('GetPrintReport');
		$this->exposeMethod('GetXLS');
		$this->exposeMethod('GetCSV');
                $this->exposeMethod('GetPDF');
	}
        
        function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$record = $request->get('record');
		$reportModel = SQLReports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcess(Vtiger_Request $request) {
		return false;
	}

	function postProcess(Vtiger_Request $request) {
		return false;
	}

	function process(Vtiger_request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function exports the report in a Excel sheet
	 * @param Vtiger_Request $request
	 */
	function GetXLS(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$reportModel = SQLReports_Record_Model::getInstanceById($recordId);
		$reportModel->getReportXLS();
	}

	/**
	 * Function exports report in a CSV file
	 * @param Vtiger_Request $request
	 */
	function GetCSV(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$reportModel = SQLReports_Record_Model::getInstanceById($recordId);
		$reportModel->getReportCSV();
	}
        
        function GetPDF(Vtiger_Request $request) {
            $recordId = $request->get('record');
            $reportModel = SQLReports_Record_Model::getInstanceById($recordId);
            try {
                $pdf = $reportModel->getReportPDF();
            } catch (Exception $ex) {
                    echo '<center>'.$ex->getMessage().'</center>';
                    return;
            }	
            $pdf->Output($reportModel->getName());
        }

	/**
	 * Function displays the report in printable format
	 * @param Vtiger_Request $request
	 */
	function GetPrintReport(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$recordId = $request->get('record');
		$reportModel = SQLReports_Record_Model::getInstanceById($recordId);
                try {
                    $printData = $reportModel->getReportPrint();
                } catch (Exception $ex) {
                    echo '<center>'.$ex->getMessage().'</center>';
                    return;
                }

		$viewer->assign('REPORT_NAME', $reportModel->getName());
		$viewer->assign('PRINT_DATA', $printData['data'][0]);
		$viewer->assign('TOTAL', $printData['total']);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ROW', $printData['data'][1]);

		$viewer->view('PrintReport.tpl', $moduleName);
	}
}