<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BoruPaymentsHandler extends VTEventHandler
{
    public function formatNumber($value = 0)
    {
        global $adb, $current_user;
        $determite = ',';
        if ($current_user != null) {
            $determite = $current_user->currency_grouping_separator;
        }
        $value = str_replace($determite, '', $value);
        return floatval($value);
    }
    public function handleEvent($eventName, $data)
    {
        global $adb,$currentModule,$divertSave,$VTIGER_BULK_SAVE_MODE;
        $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
        $moduleName = $data->getModuleName();
        $surcharge = $data->focus->column_fields['surcharge'];
        $payment_status = $data->focus->column_fields['payment_status'];
        if ($eventName == 'vtiger.entity.beforesave') {
            $id             = $data->focus->id;
            $focus          = $data->focus;
            $currentBalance = 0;
            $total_payments = 0;
            switch ($moduleName) {
                case 'BoruPayments':
                    if ($data->focus->column_fields['invoiceid'] > 0) {
                        if (isRecordExists($data->focus->column_fields['invoiceid'])) {
                            $focus    = $data->focus;
                            $invoiceR = Vtiger_Record_Model::getInstanceById($focus->column_fields['invoiceid']);
                            if ($focus->column_fields['payment_status'] == 'Paid') {
                                if ($focus->column_fields['previous_status'] != 'Paid') {
                                    $hdnGrandTotal = $this->formatNumber($invoiceR->get('hdnGrandTotal'));

                                    $currentBalance       = $this->formatNumber($invoiceR->get('cf_balance'));
                                    $percent_paid_balance = $this->formatNumber($focus->column_fields['cf_percent_paid']);
                                    if ($data->isNew() && $percent_paid_balance > 0) {
                                        $amount                         = $percent_paid_balance * $currentBalance / 100;
                                        $focus->column_fields['amount'] = $amount;
                                    } else {
                                        $amount = $this->formatNumber($focus->column_fields['amount']);
                                    }
                                }
                                if ($focus->column_fields['date_paid'] == '') {
                                    $focus->column_fields['date_paid'] = date('Y-m-d');
                                }
                            }
                        }
                    }
                    $focus->column_fields['cf_percent_paid'] = 0;
                    break;
            }

        }

        if ($eventName == 'vtiger.entity.aftersave.final') {
            $VTIGER_BULK_SAVE_MODE = true;
            switch ($moduleName) {
                case 'Invoice':
                    $id = $data->focus->id;
                    if(!empty($divertSave))
                        return;
                    $divertSave = true;
                    break;
                case 'SalesOrder':
                    $soid = $data->focus->id;
                    if(!empty($divertSave))
                        return;
                    $divertSave = true;
                    break;
                case 'BoruPayments':
                    $id = $data->focus->column_fields['invoiceid'];
                    $soid = $data->focus->column_fields['salesorderid'];
                    $_REQUEST['action'] = 'InvoiceAjax';
                    // if(!empty($divertSave))
                    //     return;
                    $divertSave = true;
                    break;
            }
            if ($id > 0) {
               
                $invoiceR      = Vtiger_Record_Model::getInstanceById($id);
                $hdnGrandTotal = $invoiceR->get('hdnGrandTotal');
                $subtotal = $invoiceR->get('hdnSubTotal');
                $pre_tax_total = $invoiceR->get('pre_tax_total');
                $sql           = "SELECT amount,surcharge FROM vtiger_payments
                                                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentid AND vtiger_crmentity.deleted=0
                                                WHERE payment_status IN ('Approved','Void','Refund') AND invoiceid=?
                                                ";
                $rs = $adb->pquery($sql, array($id));
                if ($adb->num_rows($rs) > 0) {
                    $total_payments = 0;
                    while ($row = $adb->fetchByAssoc($rs)) {
                        $amount = $this->formatNumber($row['amount']);
                        $tabsurcharge = $this->formatNumber($row['surcharge']);
                        $total_amount += $amount;
                        $total_surcharge +=  $tabsurcharge;
                        $total_payments = $total_amount + $total_surcharge; 
                    }
                }
                $cf_percent_paid = ($hdnGrandTotal > 0) ? ($total_payments / $hdnGrandTotal) * 100 : (($total_payments > 0) ? 100 : 0);
                $cf_percent_paid = round($cf_percent_paid, 1);
                $chkpercent = round($cf_percent_paid, 1);
                if($moduleName == 'BoruPayments'){
                    if($chkpercent >= 100){
                       $invoiceR->set('invoicestatus','Paid');
                    }else if($chkpercent > 0){
                        $invoiceR->set('invoicestatus','Partial Payment');
                    }
                }
                $invoiceR->set('received',$total_payments);
                $invoiceR->set('balance',$hdnGrandTotal - $total_payments);
                $invoiceR->set('cf_balance',$hdnGrandTotal - $total_payments);
                $invoiceR->set('cf_total_payments',$total_payments);
                $invoiceR->set('cf_percent_paid',$cf_percent_paid);
                $invoiceR->set('id', $id);
                $invoiceR->set('mode', 'edit');
                $invoiceR->save();
                if($surcharge > 0 && ($payment_status == 'Approved' || $payment_status =='Void' ||$payment_status =='Refund')) {
                    $sql = "SELECT * FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid WHERE deleted=0 AND (servicename = 'Surcharge' OR servicename = 'surcharge') LIMIT 0,1 ";
                    $res = $adb->pquery($sql,array());
                    if($adb->num_rows($res) > 0){
                        $serviceid = $adb->query_result($res,0,'serviceid');
                        $insert_service = "INSERT INTO vtiger_inventoryproductrel (id,productid,quantity,listprice) VALUES (?,?,?,?)";
                        $res = $adb->pquery($insert_service,array($id,$serviceid,'1',$surcharge));
                        $invoice_update_lineitem      = Vtiger_Record_Model::getInstanceById($id);
                        $total_balance = $hdnGrandTotal - $total_payments + $surcharge;
                        $invoice_update_lineitem->set('balance',$total_balance);
                        $invoice_update_lineitem->set('cf_balance',$total_balance);
                        $invoice_update_lineitem->set('hdnSubTotal',$subtotal+$surcharge);
                        $invoice_update_lineitem->set('hdnGrandTotal',$hdnGrandTotal+$surcharge);
                        $invoice_update_lineitem->set('pre_tax_total',$pre_tax_total+$surcharge);
                        $invoice_update_lineitem->set('id', $id);
                        $invoice_update_lineitem->set('mode', 'edit');
                        $invoice_update_lineitem->save();
                        $update_mode = "UPDATE vtiger_invoice SET taxtype =? WHERE invoiceid = ?";
                        $res = $adb->pquery($update_mode,array('individual',$id));
                    }
                }
            }
            if ($soid > 0) {
                $invoiceR      = Vtiger_Record_Model::getInstanceById($soid);
                $hdnGrandTotal = $invoiceR->get('hdnGrandTotal');
                $subtotal = $invoiceR->get('hdnSubTotal');
                $pre_tax_total = $invoiceR->get('pre_tax_total');
                $sql           = "SELECT amount,surcharge FROM vtiger_payments
                                                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_payments.paymentid AND vtiger_crmentity.deleted=0
                                                WHERE payment_status IN ('Approved','Void','Refund') AND salesorderid=?
                                                ";
                $rs = $adb->pquery($sql, array($soid));
                if ($adb->num_rows($rs) > 0) {
                    $total_payments = 0;
                    while ($row = $adb->fetchByAssoc($rs)) {
                        $amount = $this->formatNumber($row['amount']);
                        $tabsurcharge = $this->formatNumber($row['surcharge']);
                        $total_amount += $amount;
                        $total_surcharge +=  $tabsurcharge;
                        $total_payments = $total_amount + $total_surcharge; 
                    }
                }
                $cf_percent_paid = ($hdnGrandTotal > 0) ? ($total_payments / $hdnGrandTotal) * 100 : (($total_payments > 0) ? 100 : 0);
                $cf_percent_paid = round($cf_percent_paid, 1);
                $chkpercent = round($cf_percent_paid, 1);
                // if($chkpercent >= 100)
                // {
                //     $query1 = "UPDATE vtiger_salesorder SET sostatus = ? WHERE salesorderid = ?";
                //     $res = $adb->pquery($query1,array('Paid',$soid));
                // }   
                $bal = $hdnGrandTotal - $total_payments ;
                    $query = "UPDATE vtiger_salesordercf SET cf_balance = ?,cf_total_payments =?,cf_percent_paid=? WHERE salesorderid = ?";
                    $res = $adb->pquery($query,array($bal,$total_payments,$cf_percent_paid,$soid));

                if($surcharge > 0 && $payment_status == 'Approved'){
                    $sql = "SELECT * FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid WHERE deleted=0 AND (servicename = 'Surcharge' OR servicename = 'surcharge') LIMIT 0,1 ";
                    $res = $adb->pquery($sql,array());
                    if($adb->num_rows($res) > 0){
                        $serviceid = $adb->query_result($res,0,'serviceid');
                        $insert_service = "INSERT INTO vtiger_inventoryproductrel (id,productid,quantity,listprice) VALUES (?,?,?,?)";
                        $res = $adb->pquery($insert_service,array($soid,$serviceid,'1',$surcharge));

                        $hdnGrandTotal = $subtotal+$surcharge;
                        $pre_tax = $pre_tax_total+$surcharge;
                        $query1 = "UPDATE vtiger_salesorder SET total = ?,subtotal=?,pre_tax_total=? WHERE salesorderid = ?";
                        $res = $adb->pquery($query1,array($hdnGrandTotal,$hdnGrandTotal,$pre_tax,$soid));
                        $total_balance = $hdnGrandTotal - $total_payments + $surcharge;
                        $query_cf = "UPDATE vtiger_salesordercf SET cf_balance = ? WHERE salesorderid = ?";
                        $res = $adb->pquery($query_cf,array($total_balance,$soid));
                    }
                }
            }
            $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
        }
    }
}
