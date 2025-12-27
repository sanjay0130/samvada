<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$massActionLinks = parent::getListViewMassActions($linkParams);

		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');

		if ($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendEmail("index.php?module=' . $this->getModule()->getName() . '&view=MassActionAjax&mode=showComposeEmailForm&step=step1","Emails");',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if ($SMSNotifierModuleModel && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendSms("index.php?module=' . $this->getModule()->getName() . '&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		$moduleModel = $this->getModule();
		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $massActionLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	function getListViewLinks($linkParams)
	{
		$links = parent::getListViewLinks($linkParams);

		$index = 0;
		foreach ($links['LISTVIEWBASIC'] as $link) {
			if ($link->linklabel == 'Send SMS') {
				unset($links['LISTVIEWBASIC'][$index]);
			}
			$index++;
		}
		return $links;
	}







	// /**
	//  * Build an ID-only query based on the full ListView query.
	//  * This keeps all filters, search, orderby/sortby, permissions, etc.
	//  * from the parent ListView logic, but only selects crmid.
	//  */
	// protected function getIdListQuery()
	// {
	// 	// Let vtiger generate the full query with all joins/filters/sorting
	// 	$fullQuery = parent::getQuery();  // e.g. SELECT ... FROM vtiger_contactdetails ...

	// 	// Find the FROM clause
	// 	$fromPos = stripos($fullQuery, ' from ');
	// 	if ($fromPos === false) {
	// 		return '';
	// 	}

	// 	// Everything from "FROM ..." onwards, including WHERE, ORDER BY, etc.
	// 	$fromClause = substr($fullQuery, $fromPos);

	// 	// Use crmid as the only selected column — this avoids the ADODB bug
	// 	// ORDER BY / WHERE / joins / filters are preserved in $fromClause
	// 	$idQuery = 'SELECT vtiger_crmentity.crmid ' . $fromClause;

	// 	return $idQuery;
	// }

	// /**
	//  * Fetch listview entries by:
	//  *  - running the ID-only query (with proper LIMIT/OFFSET),
	//  *  - loading each record via Vtiger_Record_Model.
	//  * This preserves sorting (orderby/sortby), filters, and permissions.
	//  */
	// public function getListViewEntries($pagingModel)
	// {
	// 	$db          = PearDatabase::getInstance();
	// 	$moduleModel = $this->getModule();
	// 	$moduleName  = $moduleModel->getName();

	// 	$pageLimit  = (int) $pagingModel->getPageLimit();
	// 	$startIndex = (int) $pagingModel->getStartIndex();

	// 	$idQuery = $this->getIdListQuery();
	// 	if (empty($idQuery)) {
	// 		return [];
	// 	}

	// 	// Fetch one extra row to detect "next page"
	// 	$limitCount = $pageLimit + 1;
	// 	$pagedQuery = $idQuery . " LIMIT {$startIndex}, {$limitCount}";

	// 	// IMPORTANT: use query(), not pquery(), to avoid the metadata bug
	// 	$result   = $db->query($pagedQuery);
	// 	$rowCount = $db->num_rows($result);

	// 	$listViewRecordModels = [];
	// 	$recordsFetched       = 0;

	// 	// First (and only) column is crmid
	// 	for ($i = 0; $i < $rowCount && $recordsFetched < $pageLimit; $i++) {
	// 		$crmid = $db->query_result($result, $i, 0);
	// 		if (empty($crmid)) {
	// 			continue;
	// 		}

	// 		$recordModel = Vtiger_Record_Model::getInstanceById($crmid, $moduleName);
	// 		$listViewRecordModels[$crmid] = $recordModel;

	// 		$recordsFetched++;
	// 	}

	// 	// Let Paging_Model compute start/end range (1–20, 21–40, etc.)
	// 	$pagingModel->calculatePageRange($listViewRecordModels);

	// 	// Then set whether a next page exists, based on the extra row we fetched
	// 	if ($rowCount > $pageLimit) {
	// 		$pagingModel->set('nextPageExists', true);
	// 	} else {
	// 		$pagingModel->set('nextPageExists', false);
	// 	}

	// 	return $listViewRecordModels;
	// }

	// /**
	//  * Compute total record count for Contacts list, based on the same
	//  * ID-only query (without ORDER BY), so page count / "Page X of Y"
	//  * is correct even with filters and search.
	//  */
	// public function getListViewCount()
	// {
	// 	$db = PearDatabase::getInstance();

	// 	$idQuery = $this->getIdListQuery();
	// 	if (empty($idQuery)) {
	// 		return 0;
	// 	}

	// 	// Strip ORDER BY from the ID query – not needed for count
	// 	$upper    = strtoupper($idQuery);
	// 	$orderPos = strripos($upper, ' ORDER BY ');
	// 	if ($orderPos !== false) {
	// 		$idQueryNoOrder = substr($idQuery, 0, $orderPos);
	// 	} else {
	// 		$idQueryNoOrder = $idQuery;
	// 	}

	// 	// Wrap as a subquery so we count the same rows the list would show
	// 	$countQuery = "SELECT COUNT(*) AS count FROM ({$idQueryNoOrder}) AS t";

	// 	$result = $db->query($countQuery);
	// 	if (!$result) {
	// 		return 0;
	// 	}

	// 	$count = (int) $db->query_result($result, 0, 'count');

	// 	return $count;
	// }









}
