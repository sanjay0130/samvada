<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class Approvals extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_approvals';
	var $table_index= 'approvalsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_approvalscf', 'approvalsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_approvals', 'vtiger_approvalscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_approvals' => 'approvalsid',
		'vtiger_approvalscf'=>'approvalsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array (
		'Approval Type' => array('approvals', 'approvalfor'),
		'LBL_FINANCE_APPROVAL' => array('approvals', 'finance_approval'),
		'Approval Status' => array('approvals', 'approval_status'),
		'RelatedMonth' => array('approvals', 'monthrelated'),
		'Sales Order' => array('approvals', 'sodetails'),

);
	var $list_fields_name = array (
		'Approval Type' => 'approvalfor',
		'LBL_FINANCE_APPROVAL' => 'finance_approval',
		'Approval Status' => 'approval_status',
		'RelatedMonth' => 'monthrelated',
		'Sales Order' => 'sodetails',

);

	// Make the field link to detail view
	var $list_link_field = 'id';

	// For Popup listview and UI type support
	var $search_fields = array (
		'LBL_FINANCE_APPROVAL' => array('approvals', 'finance_approval'),
		'Sales Order' => array('approvals', 'sodetails'),
		'RelatedMonth' => array('approvals', 'monthrelated'),
		'Approval Status' => array('approvals', 'approval_status'),
		'Approval Type' => array('approvals', 'approvalfor'),

);
	var $search_fields_name = array (
		'LBL_FINANCE_APPROVAL' => 'finance_approval',
		'Sales Order' => 'sodetails',
		'RelatedMonth' => 'monthrelated',
		'Approval Status' => 'approval_status',
		'Approval Type' => 'approvalfor',

);

	// For Popup window record selection
	var $popup_fields = array('id');

	// For Alphabetical search
	var $def_basicsearch_col = 'id';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'id';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('createdtime', 'modifiedtime', 'id');

	var $default_order_by = 'id';
	var $default_sort_order='ASC';

	function Approvals() {
		$this->log =LoggerManager::getLogger('Approvals');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Approvals');
	}

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
 		if($eventType == 'module.postinstall') {
 			//Enable ModTracker for the module
 			static::enableModTracker($moduleName);
			//Create Related Lists
			static::createRelatedLists();
		} else if($eventType == 'module.disabled') {
			// Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			//Create Related Lists
			static::createRelatedLists();
		}
 	}
	
	/**
	 * Enable ModTracker for the module
	 */
	public static function enableModTracker($moduleName)
	{
		include_once 'vtlib/Vtiger/Module.php';
		include_once 'modules/ModTracker/ModTracker.php';
			
		//Enable ModTracker for the module
		$moduleInstance = Vtiger_Module::getInstance($moduleName);
		ModTracker::enableTrackingForModule($moduleInstance->getId());
	}
	
	protected static function createRelatedLists()
	{
		include_once('vtlib/Vtiger/Module.php');	

		$moduleInstance = Vtiger_Module::getInstance('SalesOrder');
		$relatedModuleInstance = Vtiger_Module::getInstance('Approvals');
		$relationLabel = 'Approvals';
		$moduleInstance->setRelatedList(
			$relatedModuleInstance, $relationLabel, array('ADD'), 'get_dependents_list'
		);

		$moduleInstance = Vtiger_Module::getInstance('Calendar');
		$relatedModuleInstance = Vtiger_Module::getInstance('Approvals');
		$relationLabel = 'Approvals';
		$moduleInstance->setRelatedList(
			$relatedModuleInstance, $relationLabel, array('ADD'), 'get_dependents_list'
		);

	}
}