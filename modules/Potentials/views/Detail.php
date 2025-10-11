<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Potentials_Detail_View extends Vtiger_Detail_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
	}
	/**
	 * Function to get activities
	 * @param Vtiger_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Vtiger_Request $request) {
		$moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if($currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			$moduleName = $request->getModule();
			$recordId = $request->get('record');

			$pageNumber = $request->get('page');
			if(empty ($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('limit', 10);

			if(!$this->record) {
				$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			}
			$recordModel = $this->record->getRecord();
			$moduleModel = $recordModel->getModule();

			$relatedActivities = $moduleModel->getCalendarActivities('', $pagingModel, 'all', $recordId);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER', $pageNumber);
			$viewer->assign('ACTIVITIES', $relatedActivities);


			return $viewer->view('RelatedActivities.tpl', $moduleName, true);
		}
	}

	/**
	 * Added to support Engagements view in Vtiger7
	 * @param Vtiger_Request $request
	 */
	function _showRecentActivities(Vtiger_Request $request){
		$parentRecordId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$moduleName = $request->getModule();

		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		$recentActivities = ModTracker_Record_Model::getUpdates($parentRecordId, $pagingModel,$moduleName);
		$pagingModel->calculatePageRange($recentActivities);

		// Use instance instead of static call
		$modTrackerModel = new ModTracker_Record_Model();
		$totalCount = $modTrackerModel->getTotalRecordCount($parentRecordId);

		if($pagingModel->getCurrentPage() == $totalCount / $pagingModel->getPageLimit()) {
			$pagingModel->set('nextPageExists', false);
		}

		$recordModel = Vtiger_Record_Model::getInstanceById($parentRecordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE', $recordModel->get('source'));

		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $totalCount / (int) $pageLimit);
        if($pageCount - $pagingModel->getCurrentPage() == 0) {
            $pagingModel->set('nextPageExists', false);
        } else {
            $pagingModel->set('nextPageExists', true);
        }

		//echo '<pre>'; print_r($recentActivities); die;

		$viewer->assign('RECENT_ACTIVITIES', $recentActivities);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('RECORD_ID',$parentRecordId);
	}

	/**
	 * Function returns recent changes made on the record
	 * @param Vtiger_Request $request
	 */
	function showRecentActivities (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$this->_showRecentActivities($request);

		$viewer = $this->getViewer($request);
		echo $viewer->view('RecentActivities.tpl', $moduleName, true);
	}
	
}
