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

class PostSales extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_postsales';
	var $table_index= 'postsalesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_postsalescf', 'postsalesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_postsales', 'vtiger_postsalescf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_postsales' => 'postsalesid',
		'vtiger_postsalescf'=>'postsalesid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array (
		'LBL_APPROVAL_RELATED' => array('postsales', 'approval_related'),
		'LBL_APPROVAL_STATUS' => array('postsales', 'approval_status'),
		'LBL_SALESORDER_POSTSALES' => array('postsales', 'salesorder_postsales'),
		'LBL_APPROVEDBY' => array('postsales', 'approvedby'),
		'LBL_APPROVED_ON' => array('postsales', 'approved_on'),
		'LBL_MONTH_RELATED' => array('postsales', 'month_related'),

);
	var $list_fields_name = array (
		'LBL_APPROVAL_RELATED' => 'approval_related',
		'LBL_APPROVAL_STATUS' => 'approval_status',
		'LBL_SALESORDER_POSTSALES' => 'salesorder_postsales',
		'LBL_APPROVEDBY' => 'approvedby',
		'LBL_APPROVED_ON' => 'approved_on',
		'LBL_MONTH_RELATED' => 'month_related',

);

	// Make the field link to detail view
	var $list_link_field = 'tabid';

	// For Popup listview and UI type support
	var $search_fields = array (
		'LBL_APPROVAL_STATUS' => array('postsales', 'approval_status'),
		'LBL_MONTH_RELATED' => array('postsales', 'month_related'),
		'LBL_APPROVEDBY' => array('postsales', 'approvedby'),
		'LBL_APPROVED_ON' => array('postsales', 'approved_on'),
		'LBL_SALESORDER_POSTSALES' => array('postsales', 'salesorder_postsales'),
		'LBL_APPROVAL_RELATED' => array('postsales', 'approval_related'),

);
	var $search_fields_name = array (
		'LBL_APPROVAL_STATUS' => 'approval_status',
		'LBL_MONTH_RELATED' => 'month_related',
		'LBL_APPROVEDBY' => 'approvedby',
		'LBL_APPROVED_ON' => 'approved_on',
		'LBL_SALESORDER_POSTSALES' => 'salesorder_postsales',
		'LBL_APPROVAL_RELATED' => 'approval_related',

);

	// For Popup window record selection
	var $popup_fields = array('tabid');

	// For Alphabetical search
	var $def_basicsearch_col = 'tabid';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'tabid';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('createdtime', 'modifiedtime', 'tabid');

	var $default_order_by = 'tabid';
	var $default_sort_order='ASC';

	function PostSales() {
		$this->log =LoggerManager::getLogger('PostSales');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('PostSales');
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
		$relatedModuleInstance = Vtiger_Module::getInstance('PostSales');
		$relationLabel = 'LBL_POSTSALES_LIST';
		$moduleInstance->setRelatedList(
			$relatedModuleInstance, $relationLabel, array('ADD'), 'get_dependents_list'
		);

	}
}