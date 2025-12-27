<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class BoruPayments_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {

        if($request->get('field') == 'invoiceid' && $request->get('value')){
            $request->set('invoiceid',$request->get('value'));
            $invoiceId = $request->get('invoiceid');
        }
        
        if(!empty($invoiceId)){
            $invoiceR = Vtiger_Record_Model::getInstanceById($invoiceId,'Invoice');
            if(empty($request->get('accountid'))) {
                $invoiceR->get('account_id');
                $request->set('accountid',$invoiceR->get('account_id'));
            }
            if(empty($request->get('contactid'))) {
                $invoiceR->get('contact_id');
                $request->set('contactid',$invoiceR->get('contact_id'));
            }
        }
        
		parent::process($request);
        
        if($request->get('field') == 'payment_status'){
            $recordId = $request->get('record');
            $paymentR = Vtiger_Record_Model::getInstanceById($recordId,'BoruPayments');
            $paymentR->set('previous_status',$request->get('value'));
            $paymentR->set('mode','edit');
            $paymentR->save();
        }
	}
}
