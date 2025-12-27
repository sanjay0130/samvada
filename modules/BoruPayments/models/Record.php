<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 */
class BoruPayments_Record_Model extends Vtiger_Record_Model {

        function formatNumber($value=0){
                global $adb, $current_user;
                $determite = ',';
                if ($current_user!=NULL)
                {
                        $determite = $current_user->currency_grouping_separator;
                }
                $value = str_replace($determite,'',$value);
                return floatval($value);
        }        
	function updateInvoiceAmount4Delete(){
                global $adb;
                $paymentR = $this;
                $paymentid = $this->get('paymentid');
                $invoiceid = $paymentR->get('invoiceid');;
                if ($invoiceid>0 && isRecordExists($invoiceid))
                {
                        $invoiceR = Vtiger_Record_Model::getInstanceById($invoiceid,'Invoice');
                        if ($paymentR->get('payment_status')=='Paid'){
                                $hdnGrandTotal = $this->formatNumber($invoiceR->get('hdnGrandTotal'));
                                $amount = $this->formatNumber($paymentR->get('amount'));
                                $currentBalance = $this->formatNumber($invoiceR->get('cf_balance'));
                                $percent_paid_balance = $this->formatNumber($paymentR->get('cf_percent_paid'));
                                $amount = $this->formatNumber($paymentR->get('amount'));
                                $total_payments= $this->formatNumber($invoiceR->get('cf_total_payments'));
                                $total_payments -= $amount;
                                $currentBalance +=$amount;
                                $cf_percent_paid = ($hdnGrandTotal>0)?$total_payments/$hdnGrandTotal*100:($total_payments>0?100:0);
                                $cf_percent_paid= round($cf_percent_paid,1);
                                $invoiceR->set('cf_total_payments',$total_payments);
                                $invoiceR->set('cf_percent_paid',$cf_percent_paid);
                                $invoiceR->set('cf_balance',($hdnGrandTotal - $total_payments));
                                $invoiceR->save();
                                //$sql="UPDATE vtiger_invoicecf SET cf_total_payments=?,cf_percent_paid=?,cf_balance=? WHERE invoiceid = ?";
                                //$adb->pquery($sql,array($total_payments,$cf_percent_paid,($hdnGrandTotal - $total_payments),$invoiceid));

                        }
                }
        }
        
        public function getCurrencySymbol() {
                global $current_user;
		$baseCurrencyDetails = $this->get('baseCurrencyDetails');
		if (!empty($baseCurrencyDetails)) {
			return $baseCurrencyDetails;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
                $baseCurrency = fetchCurrency($current_user->id);
		$baseCurrencyDetails = array('currencyid' => $baseCurrency);

		$baseCurrencySymbolDetails = getCurrencySymbolandCRate($baseCurrency);
		$baseCurrencyDetails = array_merge($baseCurrencyDetails, $baseCurrencySymbolDetails);
		$this->set('baseCurrencyDetails', $baseCurrencyDetails);                
		return $baseCurrencyDetails['symbol'];
	}
}
