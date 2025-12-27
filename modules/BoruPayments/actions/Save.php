<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class BoruPayments_Save_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
        $invoiceId = $request->get('invoiceid');
        
		if(!empty($invoiceId)){
            $paymentStatus = $request->get('payment_status');
            $request->set('previous_status',$paymentStatus);
            
            $invoiceR = Vtiger_Record_Model::getInstanceById($invoiceId,'Invoice');
            // populate account, contact from invoice if blank
            if(empty($request->get('accountid'))) {
                $request->set('accountid',$invoiceR->get('account_id'));
            }
            if(empty($request->get('contactid'))) {
                $request->set('contactid',$invoiceR->get('contact_id'));
            }
        }
        
		parent::process($request);
	}
}
