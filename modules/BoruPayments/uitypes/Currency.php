<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class BoruPayments_Currency_UIType extends Vtiger_Currency_UIType {
    public function getDisplayValue($value, $skipConversion = false, $recordInstance = false) {
        global $adb, $current_user;
        $determite = ',';
        if ($current_user != null) {
            $determite = $current_user->currency_grouping_separator;
        }

        $uiType = $this->get('field')->get('uitype');
        if ($value) {
            $value = decimalFormat($value);
            $value = str_replace($determite,'',$value);

            if ($uiType == 72 || $uiType == 71) {
                // Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
                $value = CurrencyField::convertToUserFormat($value, null, true);
            } else {
                $value = CurrencyField::convertToUserFormat($value);
            }
            return $value;
        }
        return null;
    }

}
