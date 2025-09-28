<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */ 

vimport('~~/modules/SQLReports/ReportsSQL.php');
vimport('~~/modules/SQLReports/ReportRunSQL.php');
//vimport('~~/modules/Reports/CustomReportUtils.php');
require_once('modules/SQLReports/ReportsSQL.php');

class SQLReports_Record_Model extends Vtiger_Record_Model {

	/**
	 * Fuction to get the Name of the Report
	 * @return <String>
	 */
	function getName() {
		return $this->get('reportname');
	}

	/**
	 * Function deletes the Report
	 * @return Boolean
	 */
	function delete() {
		return $this->getModule()->deleteRecord($this);
	}

	/**
	 * Function returns the url that generates Report in Excel format
	 * @return <String>
	 */
	function getReportExcelURL() {
		return 'index.php?module='.$this->getModuleName().'&view=ExportReport&mode=GetXLS&record='. $this->getId();
	}

	/**
	 * Function returns the url that generates Report in CSV format
	 * @return <String>
	 */
	function getReportCSVURL() {
		return 'index.php?module='.$this->getModuleName().'&view=ExportReport&mode=GetCSV&record='. $this->getId();
	}

	/**
	 * Function returns the url that generates Report in printable format
	 * @return <String>
	 */
	function getReportPrintURL() {
		return 'index.php?module='.$this->getModuleName().'&view=ExportReport&mode=GetPrintReport&record='. $this->getId();
	}
        
        function getReportPDFURL() {
		return 'index.php?module='.$this->getModuleName().'&view=ExportReport&mode=GetPDF&record='. $this->getId();
	}

	/**
	 * Function returns the Reports Model instance
	 * @param <Number> $recordId
	 * @param <String> $module
	 * @return <Reports_Record_Model>
	 */
	public static function getInstanceById($recordId) {
		$db = PearDatabase::getInstance();

		$self = new self();
		$reportResult = $db->pquery('SELECT * FROM vtiger_sqlreports WHERE sqlreportsid = ?', array($recordId));
		if($db->num_rows($reportResult)) {
			$values = $db->query_result_rowdata($reportResult, 0);
			$module = Vtiger_Module_Model::getInstance('SQLReports');
			$self->setData($values)->setId($values['sqlreportsid'])->setModuleFromInstance($module);
			$self->initialize();
		}
		return $self;
	}

	/**
	 * Function creates Reports_Record_Model
	 * @param <Number> $recordId
	 * @return <Reports_Record_Model>
	 */
	public static function getCleanInstance($recordId = null) {
		if(empty($recordId)) {
			$self = new SQLReports_Record_Model();
		} else {
			$self = self::getInstanceById($recordId);
		}
		$self->initialize();
		$module = Vtiger_Module_Model::getInstance('SQLReports');
		$self->setModuleFromInstance($module);
		return $self;
	}

	/**
	 * Function initializes Report
	 */
	function initialize() {
		$reportId = $this->getId();
		//$this->report = Vtiger_Report_Model::getInstance($reportId);
	}



	/**
	 * Function returns Report Type(Summary/Tabular)
	 * @return <String>
	 */
	function getReportType() {
		$reportType = $this->get('reporttype');
		if(!empty($reportType)) {
			return $reportType;
		}
		return $this->report->reporttype;
	}

	/**
	 * Returns the Reports Owner
	 * @return <Number>
	 */
	function getOwner() {
		return $this->get('owner');
	}


	/**
	 * Function returns sql for the report
	 * @param <String> $advancedFilterSQL
	 * @param <String> $format
	 * @return <String>
	 */
	function getReportSQL($advancedFilterSQL=false, $format=false) {
		$reportRun = ReportRunSQL::getInstance($this->getId());
		$sql = $reportRun->sGetSQLforReport($this->getId(), $advancedFilterSQL, $format);
		return $sql;
	}

	/**
	 * Function returns report's data
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param <String> $filterQuery
	 * @return <Array>
	 */
	function getReportData($pagingModel = false, $filterQuery = false) {
		$reportRun = ReportRunSQL::getInstance($this->getId());
		$data = $reportRun->GenerateReport('PDF', $filterQuery, true /*,$pagingModel->getStartIndex(), $pagingModel->getPageLimit()*/);
		return $data;
	}

	function getReportCalulationData($filterQuery = false) {
		$reportRun = ReportRunSQL::getInstance($this->getId());
		$data = $reportRun->GenerateReport('TOTALXLS', $filterQuery, true);
		return $data;
	}
	/**
	 * Function exports reports data into a Excel file
	 */
	function getReportXLS() {
		$reportRun = ReportRunSQL::getInstance($this->getId());
		$rootDirectory = vglobal('root_directory');
		$tmpDir = vglobal('tmp_dir');

		$tempFileName = tempnam($rootDirectory.$tmpDir, 'xls');
		$fileName = $this->getName().'.xls';
                try {
                    $reportRun->writeReportToExcelFile($tempFileName, false);
                } catch (Exception $ex) {
                    echo '<center>'.$ex->getMessage().'</center>';
                    return;
                }		

		if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			header('Pragma: public');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		}

		header('Content-Type: application/x-msexcel');
		header('Content-Length: '.@filesize($tempFileName));
		header('Content-disposition: attachment; filename="'.$fileName.'"');

		$fp = fopen($tempFileName, 'rb');
		fpassthru($fp);
		//unlink($tempFileName);
	}

	/**
	 * Function exports reports data into a csv file
	 */
	function getReportCSV() {
		$reportRun = ReportRunSQL::getInstance($this->getId());
		$rootDirectory = vglobal('root_directory');
		$tmpDir = vglobal('tmp_dir');

		$tempFileName = tempnam($rootDirectory.$tmpDir, 'csv');
                try {
                    $reportRun->writeReportToCSVFile($tempFileName, false);
                } catch (Exception $ex) {
                    echo '<center>'.$ex->getMessage().'</center>';
                    return;
                }	
		$fileName = $this->getName().'.csv';

		if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
			header('Pragma: public');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		}

		header('Content-Type: application/csv');
		header('Content-Length: '.@filesize($tempFileName));
		header('Content-disposition: attachment; filename="'.$fileName.'"');

		$fp = fopen($tempFileName, 'rb');
		fpassthru($fp);
	}
        
        /**
	 * Function exports reports data into a csv file
	 */
	function getReportPDF() {
		$reportRun = ReportRunSQL::getInstance($this->getId());
                return $reportRun->getReportPDF();                
	}

	/**
	 * Function returns data in printable format
	 * @return <Array>
	 */
	function getReportPrint() {
		$reportRun = ReportRunSQL::getInstance($this->getId());
		$data = array();
		$data['data'] = $reportRun->GenerateReport('PRINT', false);
		$data['total'] = $reportRun->GenerateReport('PRINT_TOTAL', false);
		return $data;
	}


}
