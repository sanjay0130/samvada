<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class SQLReports_RunReport_View extends Vtiger_Index_View {
    
        protected $reportData;
	protected $calculationFields;
        protected $error;

	const REPORT_LIMIT = 10000;

	function preProcess(Vtiger_Request $request) {
		parent::preProcess($request);
                
                $this->error = '';

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$page = $request->get('page');

		$runModel = SQLReports_RunReport_Model::getInstance($moduleName, $recordId);
		$reportModel = $runModel->getRecord();
		$reportModel->setModule('SQLReports');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', self::REPORT_LIMIT);

                try
                {
                    $this->reportData = $reportModel->getReportData($pagingModel);
                }
                catch(Exception $e) {
                     $this->error = $e->getMessage();
                }

                if($this->error == '')
                {
                    $runLinks = $runModel->getDetailViewLinks();
                    $viewer->assign('DETAILVIEW_LINKS', $runLinks);
                }
                $count = count($this->reportData);
                if($count == 1)
                {
                    foreach($this->reportData[0] as $col)
                    {
                        if($col != '-')
                            break;
                    }
                    $count = 0;
                }
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('COUNT',$count);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ReportHeaderSQL.tpl', $moduleName);
	}

        /*
	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		echo $this->getReport($request);
	}*/

	function getReport(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->get('record');
		$page = $request->get('page');

		$data = $this->reportData;
		$calculation = $this->calculationFields;

		if(empty($data)){
			$reportModel = Reports_Record_Model::getInstanceById($record);
			$reportModel->setModule('Reports');

			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $page);
			$pagingModel->set('limit', self::REPORT_LIMIT+1);

			$data = $reportModel->getReportData($pagingModel);
			$calculation = $reportModel->getReportCalulationData();
		}

		$viewer->assign('CALCULATION_FIELDS',$calculation);
		$viewer->assign('DATA', $data);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE', $moduleName);

		if (count($data) > self::REPORT_LIMIT) {
			$viewer->assign('LIMIT_EXCEEDED', true);
		}

		$viewer->view('ReportContentsSQL.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.Detail',
			"modules.$moduleName.resources.Detail"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function process(Vtiger_Request $request) {
                if($this->error != '')
                    echo '<center>'.$this->error.'</center>';
                else
                    echo $this->getReport($request);
	}
}