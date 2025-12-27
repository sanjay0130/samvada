<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Relcontacts_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Relcontacts.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		global $adb;
		$db = PearDatabase::getInstance();
		/*if($value>0)
		{
		  $sql= $adb->pquery("SELECT CONCAT(conc.firstname,' ',conc.lastname) AS fullname,conc.contactid FROM vtiger_contactdetails conc INNER JOIN vtiger_crmentity crm ON conc.contactid=crm.crmid WHERE crm.deleted=? AND conc.contactid=?",array(0,$value));
		  $resultinfo = $adb->fetch_array($sql);
		  return $resultinfo["fullname"];
		}*/
		return $value;
	}
    
    public function getListSearchTemplateName() {
        return 'uitypes/PickListFieldSearchView.tpl';
    }

}