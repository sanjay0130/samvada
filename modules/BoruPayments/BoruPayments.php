<?php
/***********************************************************************************************
** The contents of this file are subject to the Vtiger Module-Builder License Version 1.3
 * ( "License" ); You may not use this file except in compliance with the License
 * The Original Code is:  Technokrafts Labs Pvt Ltd
 * The Initial Developer of the Original Code is Technokrafts Labs Pvt Ltd.
 * Portions created by Technokrafts Labs Pvt Ltd are Copyright ( C ) Technokrafts Labs Pvt Ltd.
 * All Rights Reserved.
**
*************************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';
require_once("vtlib/Vtiger/Module.php");
require_once("vtlib/Vtiger/Block.php");
require_once("vtlib/Vtiger/Field.php");
class BoruPayments extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_payments';
	var $table_index= 'paymentid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_paymentscf', 'paymentid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_payments', 'vtiger_paymentscf', 'vtiger_borupayments_user_field');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_payments'   => 'paymentid',
	    'vtiger_paymentscf' => 'paymentid',
		'vtiger_borupayments_user_field' => 'recordid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
/*FIELDSTART*/'Reference Number'=> Array('payments', 'reference_number'),/*FIELDEND*/
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
/*FIELDSTART*/'Reference Number'=> 'reference_number',/*FIELDEND*/
		'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view
	var $list_link_field = 'reference_number';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
/*FIELDSTART*/'Reference Number'=> Array('payments', 'reference_number'),/*FIELDEND*/
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
/*FIELDSTART*/'Reference Number'=> 'reference_number',/*FIELDEND*/
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('reference_number');

	// For Alphabetical search
	var $def_basicsearch_col = 'reference_number';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'reference_number';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('reference_number','assigned_user_id');

	var $default_order_by = 'reference_number';
	var $default_sort_order='ASC';

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
			self::checkWebServiceEntry();                          
                        self::createCustomField4Invoice();
                        self::updateRelatedListSetting();
                        self::enableTrackingForModule();
                        self::updateAutoGenerateField();
                        self::addWidget();
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
                } else if($eventType == 'module.enabled') {
                        self::createCustomField4Invoice();
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
                        self::createCustomField4Invoice();
                        self::updateRelatedListSetting();
                        self::enableTrackingForModule();
                        self::updateAutoGenerateField();
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.                        
			self::checkWebServiceEntry();
            self::addWidget();
		}
 	}
	
	/*
	 * Function to handle module specific operations when saving a entity
	 */
	static function addWidget(){
        $module1 = Vtiger_Module::getInstance('Invoice');
        $linkType = 'HEADERSCRIPT';
        $linklabel2 = 'BoruPayments';
        $link2 = "layouts/v7/modules/BoruPayments/resources/BoruPayments.js";
        $module1->addLink($linkType, $linklabel2, $link2);

    }
	function save_module($module){}
                
    static function updateRelatedListSetting() {
        global $adb;
        $sql = "SELECT count(id) AS cnt FROM vtiger_ws_entity WHERE name = 'BoruPayments'";
        $result = $adb->pquery($sql,array());
        if ($adb->num_rows($result) > 0) {
            $no = $adb->query_result($result, 0, 'cnt');
            $tabid = 0;
            if ($no != 0) {
                $sql = "SELECT tabid FROM vtiger_tab WHERE name = 'BoruPayments' LIMIT 1";
                $result = $adb->pquery($sql, array());
                if ($adb->num_rows($result) > 0) {
                    $tabid = $adb->query_result($result, 0, 'tabid');
                }
            }
            //Lee add handler
            if ($tabid > 0) {
                //add related list
                self::addRelatedListForModule();
                
                //add event handdler
                $moduleInstance = Vtiger_Module::getInstance('BoruPayments');
                Vtiger_Event::register($moduleInstance, 'vtiger.entity.beforesave', 'BoruPaymentsHandler', 'modules/BoruPayments/BoruPaymentsHandler.php');
                //Vtiger_Event::register($moduleInstance, 'vtiger.entity.aftersave', 'BoruPaymentsHandler', 'modules/BoruPayments/BoruPaymentsHandler.php');
                Vtiger_Event::register($moduleInstance, 'vtiger.entity.aftersave.final', 'BoruPaymentsHandler', 'modules/BoruPayments/BoruPaymentsHandler.php');
                
            }
            //END
        }
    }

    static function createCustomField4Invoice(){
        global $adb;
        $sql = "SELECT fieldid FROM vtiger_field WHERE tablename=? AND fieldname=? LIMIT 1";
        $rs = $adb->pquery($sql, array('vtiger_payments','payment_no'));
        $addfieldmanual = false;
        if ($adb->num_rows($rs)==0){
            $fields = array(
                'BoruPayments' => array(
                    'Payment Information' => array(
                        'previous_status' => array(
                            'label' => 'Previous status',
                            'uitype' => 1,
                            'displaytype'=>3,
                            'table' => 'vtiger_paymentscf',
                        ),
                        'invoiceid' => array(
                            'label' => 'Invoice',
                            'uitype' => 10,
                            'relatedModules'=>array('Invoice'),
                            'table' => 'vtiger_payments',
                        ),
                        'cf_percent_paid' => array(
                            'label' => '% of Invoice Balance',
                            'uitype' => 9,
                            'table' => 'vtiger_paymentscf',
                            'displaytype' => 3,
                        ),
                        'payment_no' => array(
                            'label' => 'Payment No',
                            'uitype' => 4,
                            'table' => 'vtiger_payments',
                        ),
                    ),
                ),
                'Invoice' => array(
                    'LBL_CUSTOM_INFORMATION' => array(
                        'cf_balance' => array(
                            'label' => 'Balance',
                            'uitype' => 71,
                            'table' => 'vtiger_invoicecf',
                        ),
                        'cf_total_payments' => array(
                            'label' => 'Total Payment',
                            'uitype' => 71,
                            'table' => 'vtiger_invoicecf',
                        ),
                        'cf_percent_paid' => array(
                            'label' => '% Paid',
                            'uitype' => 9,
                            'table' => 'vtiger_invoicecf',
                        ),
                    ),
                ),
                'SalesOrder' => array(
                    'LBL_CUSTOM_INFORMATION' => array(
                        'cf_balance' => array(
                            'label' => 'Balance',
                            'uitype' => 71,
                            'table' => 'vtiger_salesordercf',
                        ),
                        'cf_total_payments' => array(
                            'label' => 'Total Payment',
                            'uitype' => 71,
                            'table' => 'vtiger_salesordercf',
                        ),
                        'cf_percent_paid' => array(
                            'label' => '% Paid',
                            'uitype' => 9,
                            'table' => 'vtiger_salesordercf',
                        ),
                    ),
                )
            );
        }else{
            $addfieldmanual = true;
            $fields = array(
                'BoruPayments' => array(
                    'Payment Information' => array(
                        'previous_status' => array(
                            'label' => 'Previous status',
                            'uitype' => 1,
                            'displaytype'=>3,
                            'table' => 'vtiger_paymentscf',
                        ),
                        'invoiceid' => array(
                            'label' => 'Invoice',
                            'uitype' => 10,
                            'relatedModules'=>array('Invoice'),
                            'table' => 'vtiger_payments',
                        ),
                        'cf_percent_paid' => array(
                            'label' => '% of Invoice Balance',
                            'uitype' => 9,
                            'table' => 'vtiger_paymentscf',
                            'displaytype' => 3,
                        ),
                    ),
                ),
                'Invoice' => array(
                    'LBL_CUSTOM_INFORMATION' => array(
                        'cf_balance' => array(
                            'label' => 'Balance',
                            'uitype' => 71,
                            'table' => 'vtiger_invoicecf',
                        ),
                        'cf_total_payments' => array(
                            'label' => 'Total Payment',
                            'uitype' => 71,
                            'table' => 'vtiger_invoicecf',
                        ),
                        'cf_percent_paid' => array(
                            'label' => '% Paid',
                            'uitype' => 9,
                            'table' => 'vtiger_invoicecf',
                        ),
                    ),
                ),
                'SalesOrder' => array(
                    'LBL_CUSTOM_INFORMATION' => array(
                        'cf_balance' => array(
                            'label' => 'Balance',
                            'uitype' => 71,
                            'table' => 'vtiger_salesordercf',
                        ),
                        'cf_total_payments' => array(
                            'label' => 'Total Payment',
                            'uitype' => 71,
                            'table' => 'vtiger_salesordercf',
                        ),
                        'cf_percent_paid' => array(
                            'label' => '% Paid',
                            'uitype' => 9,
                            'table' => 'vtiger_salesordercf',
                        ),
                    ),
                )
            );
        }
        foreach ($fields as $ml => $mv) {
            $module = Vtiger_Module::getInstance($ml);
            if ($module) {
                foreach ($mv as $bl => $bv) {
                    $block = Vtiger_Block::getInstance($bl, $module);
                    if (!$block) {
                            $block = new Vtiger_Block();
                            $block->label = $bl;
                            $block->__create($module);
                    } else {
                            // $block->__delete();
                    }
                    foreach ($bv as $fn => $fv) {
                        $field = Vtiger_Field::getInstance($fn, $module);
                        if (!$field) {
                            $field = new Vtiger_Field();
                            $field->name = $fn;
                            $field->label = $fv['label'];
                            $field->uitype = $fv['uitype'];
                            $field->table = $fv['table'];
                            if (isset($fv['displaytype'])){
                                    $field->displaytype= $fv['displaytype'];
                            }
                            $field->__create($block);
                            // uitype
                            if ($fv['uitype'] == 15 || $fv['uitype'] == 16) {
                                    $field->setPicklistValues($fv['picklistvalues']);
                            }
                            if ($fv['uitype'] == 10) {
                                    $field->setRelatedModules($fv['relatedModules']);
                            }
                        } else {
                        }
                    }
                }
            }
        }


        if ($addfieldmanual==true){

            $sql = "SELECT fieldid FROM vtiger_field WHERE tablename=? AND fieldname=? LIMIT 1";
            $rs = $adb->pquery($sql, array('vtiger_payments','payment_no'));
            if ($adb->num_rows($rs)<=0){
                $sql = "ALTER TABLE vtiger_payments ADD payment_no VARCHAR (100) NULL;";
                @$adb->pquery($sql,array());
            }

        }
                
    }
    static function addRelatedListForModule(){
        global $adb;
        $relatedTabid = getTabid('BoruPayments');
        $tabid = getTabid('Invoice');

        $result = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?;", Array($tabid,$relatedTabid));
        if($adb->num_rows($result) == 0){
            $moduleInstance = Vtiger_Module::getInstance('Invoice');
            $relatedListModule = Vtiger_Module::getInstance('BoruPayments');
            $relationLabel  = 'BoruPayments';
            $moduleInstance->setRelatedList($relatedListModule, $relationLabel, Array('ADD'),'get_dependents_list');
        }

        $tabid_so = getTabid('SalesOrder');

        $result_so = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?;", Array($tabid_so,$relatedTabid));
        if($adb->num_rows($result_so) == 0){
            $moduleInstance_so = Vtiger_Module::getInstance('SalesOrder');
            $relatedListModule_so = Vtiger_Module::getInstance('BoruPayments');
            $relationLabel_so  = 'BoruPayments';
            $moduleInstance_so->setRelatedList($relatedListModule_so, $relationLabel_so, Array('ADD'),'get_dependents_list');
        }


        $tabid_so = getTabid('Accounts');

        $result_so = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?;", Array($tabid_so,$relatedTabid));
        if($adb->num_rows($result_so) == 0){
            $moduleInstance_so = Vtiger_Module::getInstance('Accounts');
            $relatedListModule_so = Vtiger_Module::getInstance('BoruPayments');
            $relationLabel_so  = 'BoruPayments';
            $moduleInstance_so->setRelatedList($relatedListModule_so, $relationLabel_so, Array('ADD'),'get_dependents_list');
        }


        $tabid_so = getTabid('Contacts');

        $result_so = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?;", Array($tabid_so,$relatedTabid));
        if($adb->num_rows($result_so) == 0){
            $moduleInstance_so = Vtiger_Module::getInstance('Contacts');
            $relatedListModule_so = Vtiger_Module::getInstance('BoruPayments');
            $relationLabel_so  = 'BoruPayments';
            $moduleInstance_so->setRelatedList($relatedListModule_so, $relationLabel_so, Array('ADD'),'get_dependents_list');
        }



    }
	/**
	 * Function to check if entry exsist in webservices if not then enter the entry
	 */
	static function checkWebServiceEntry() {				
		global $adb;
		$sql       =  "SELECT count(id) AS cnt FROM vtiger_ws_entity WHERE name = 'BoruPayments'";
		$result   	= $adb->pquery($sql,array());
		if($adb->num_rows($result) > 0){
			$no = $adb->query_result($result, 0, 'cnt');
            $tabid = 0;
			if($no == 0){
				$tabid = $adb->getUniqueID("vtiger_ws_entity");
				$ws_entitySql = "INSERT INTO vtiger_ws_entity ( id, name, handler_path, handler_class, ismodule ) VALUES".
						  " (?, 'BoruPayments','include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation' , 1)";
				$res = $adb->pquery($ws_entitySql, array($tabid));                                			
			}else{
                $sql       =  "SELECT tabid FROM vtiger_tab WHERE name = 'BoruPayments' LIMIT 1";
                $result   	= $adb->pquery($sql,array());
                if ($adb->num_rows($result)>0){
                    $tabid = $adb->query_result($result,0,'tabid');
                }
            }
		}					
	}
        
    function get_invoices($id){
		global $log,$singlepane_view;		
		require_once('modules/Invoice/Invoice.php');

		$focus = new Invoice();

		$button = '';
		if($singlepane_view == 'true')
			$returnset = '&return_module=BoruPayments&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=BoruPayments&return_action=CallRelatedList&return_id='.$id;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "select vtiger_crmentity.*, vtiger_invoice.*, vtiger_account.accountname,
			vtiger_salesorder.subject as salessubject, case when
			(vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname
			end as user_name from vtiger_invoice
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid AND vtiger_crmentity.deleted=0
                        inner join vtiger_payments on vtiger_payments.invoiceid=vtiger_invoice.invoiceid
			left outer join vtiger_account on vtiger_account.accountid=vtiger_invoice.accountid
			left join vtiger_salesorder on vtiger_salesorder.salesorderid=vtiger_invoice.salesorderid
                        left join vtiger_crmentity  vtiger_crmentity2 on vtiger_crmentity2.crmid=vtiger_salesorder.salesorderid AND vtiger_crmentity2.deleted=0
                        LEFT JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
			left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
			left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			where vtiger_crmentity.deleted=0 and vtiger_payments.paymentid=".$id;
		
		return GetRelatedList('SalesOrder','Invoice',$focus,$query,$button,$returnset);
	}
    static function enableTrackingForModule() {
            $currentModule = 'BoruPayments';
            $tabid = getTabid($currentModule);
            require_once 'modules/ModTracker/ModTracker.php';
            ModTracker::enableTrackingForModule($tabid);
    }
        /* static */
    static function updateAutoGenerateField() {
        global $currentModule, $adb;
        $sql = "SELECT * FROM vtiger_modentity_num WHERE semodule=? LIMIT 1";
        $res = $adb->pquery($sql,array('BoruPayments'));
        if ($adb->num_rows($res)==0){
            $res = $adb->pquery("SELECT MAX(num_id) num_id FROM `vtiger_modentity_num`;",array());
            $num_id = $adb->query_result($res, 0, 'num_id');
            $num_id++;
            $adb->pquery("INSERT INTO `vtiger_modentity_num` (`num_id`, `semodule`, `prefix`, `start_id`, `cur_id`, `active`) VALUES ('$num_id', 'BoruPayments', 'PAY', '1', '1', '1')",array());
            $adb->pquery("UPDATE `vtiger_modentity_num_seq` SET `id`='$num_id'",array());
        }
    }
}
