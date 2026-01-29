<?php

/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

include "modules/Cashflow4You/models/Utils.php";
        
class Cashflow4You_Cashflow4YouActions_View extends Vtiger_BasicAjax_View {
    
    var $db;
    
    public function process(Vtiger_Request $request) {
        
        $this->db = PearDatabase::getInstance();
     // $this->db->setDebug(true);
        $viewer = $this->getViewer($request);
        $current_user = Users_Record_Model::getCurrentUserModel();
        $utils = new Cashflow4You_Utils_Model();

        if(!isset($_REQUEST['loadPayments']))
                $_REQUEST['loadPayments'] = '';

        $relation_module = $_REQUEST['source_module'];
        if((!isset($relation_module) || $relation_module=='') && isset($_REQUEST['record']) && $_REQUEST['record']!=''){
            $relation_module = $utils->getModuleById($_REQUEST['record']);
        }
        
        $focus = CRMEntity::getInstance($relation_module);
        $moduletable = $focus->table_name;
        $moduleid = $focus->table_index;
        $module_no = strtolower($relation_module)."_no";
        $total_fld = 'total';
        if($relation_module == "Potentials")
        {
           $module_no = "potential_no";
           $total_fld = 'amount';
        }
        else if( $relation_module == "ITS4YouPreInvoice")
        {
            $module_no = "preinvoice_no";
        }
                
        if(isset($moduletable)){
            $go_more = $this->db->getOne("SELECT $moduleid 
                                    FROM $moduletable
                                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=$moduletable.$moduleid
                                    WHERE deleted=0 
                                    AND p_open_amount>=0 
                                    AND $moduletable.$moduleid=".$_REQUEST['record'],0,$moduleid);
        } else {
            $go_more = ' ';
        }
        $viewer->assign('GO_MORE', $go_more);
        $viewer->assign('RECORD', $_REQUEST['record']);
        $viewer->assign('RELATION_MODULE', $relation_module);
        $viewer->assign('LOAD_PAYMENTS', $_REQUEST['loadPayments']);
        $viewer->assign('PAYTYPE', $_REQUEST['paytype']);
        
        
        /*if(isset($go_more) && $go_more!='' && $_REQUEST['paytype']!='loadPayments'){
            echo '<a href="index.php?module=Cashflow4You&parenttab=Sales&action=EditView&relationid='.$_REQUEST['record'].'&return_module='.$relation_module.'&return_id='.$_REQUEST['relationid'].'">
                            <img src="themes/images/actionGenerateInvoice.gif" align="absmiddle" border="0" hspace="5"></a>';

            echo '<a href="index.php?module=Cashflow4You&parenttab=Sales&action=EditView&relationid='.$_REQUEST['record'].'&return_module='.$relation_module.'&return_id='.$_REQUEST['relationid'].'">
                            '.getTranslatedString('Create Payment',$currentModule).'</a>';
            echo "<br />&nbsp;";
        }*/

        if( $relation_module == "Potentials" )
        {
            $select_inv = "SELECT ".$moduletable.".".$total_fld." AS total FROM ".$moduletable."
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=".$moduletable.".".$moduleid."
                    WHERE vtiger_crmentity.deleted=0
                    AND ".$moduletable.".".$moduleid."=?";
            $result_inv = $this->db->pquery($select_inv, Array($_REQUEST['record']));
            $grand_total = $this->db->query_result($result_inv, 0, 'total');
            $grand_total_show = CurrencyField::convertToUserFormat($grand_total, $current_user, false);

            $select_inv = "SELECT currency_symbol FROM vtiger_currency_info
                    WHERE id=?";
            $result_inv = $this->db->pquery($select_inv, Array($current_user->currency_id));

            $gtotal_curr = $this->db->query_result($result_inv, 0, 'currency_symbol');
            $total_curr = $gtotal_curr;
            $currency_id = 1;
            $currency_rate = 1;
        }
        else
        {
            $select_inv = "SELECT ".$moduletable.".".$total_fld." AS total, vtiger_currency_info.currency_symbol, ".$moduletable.".currency_id "
                    . "FROM ".$moduletable."
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=".$moduletable.".".$moduleid."
                    INNER JOIN vtiger_currency_info ON vtiger_currency_info.id = ".$moduletable.".currency_id 
                    WHERE vtiger_crmentity.deleted=0
                    AND ".$moduletable.".".$moduleid."=?";
            $result_inv = $this->db->pquery($select_inv, Array($_REQUEST['record']));
            $grand_total = abs($this->db->query_result($result_inv, 0, 'total'));
            $grand_total_show = CurrencyField::convertToUserFormat($grand_total, $current_user, true);
            
            $gtotal_curr = $this->db->query_result($result_inv, 0, 'currency_symbol');

            /*$select_inv = "SELECT currency_symbol FROM vtiger_currency_info
                    WHERE id=?";
            $result_inv = $this->db->pquery($select_inv, Array($current_user->currency_id));

            $gtotal_curr = $this->db->query_result($result_inv, 0, 'currency_symbol');*/
            $total_curr = $gtotal_curr;
            $currency_id = $this->db->query_result($result_inv, 0, 'currency_id');
            $currencyRateAndSymbol = getCurrencySymbolandCRate($currency_id);
            $currency_rate =  $currencyRateAndSymbol['rate'];
        }
            
        if( $relation_module == "SalesOrder" || $relation_module == "ITS4YouPreInvoice")
        { 
          $query = "SELECT vtiger_crmentity.*, its4you_cashflow4you.*, $moduletable.$module_no AS relation_no, $moduletable.$total_fld as relation_am,
                    vtiger_currency_info.currency_symbol 
                    FROM its4you_cashflow4you 
                    INNER JOIN vtiger_crmentityrel ON its4you_cashflow4you.cashflow4youid=vtiger_crmentityrel.crmid
                    INNER JOIN $moduletable ON $moduletable.$moduleid=vtiger_crmentityrel.relcrmid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=its4you_cashflow4you.cashflow4youid
                    INNER JOIN vtiger_currency_info ON vtiger_currency_info.id = its4you_cashflow4you.currency_id 
                    WHERE vtiger_crmentity.deleted=0
                    AND vtiger_crmentityrel.relcrmid=?
                    ORDER BY paymentdate, cashflow4youid  ASC";
          $result1 = $this->db->pquery($query, Array($_REQUEST['record']));
        }
        else
        {
          $query = "SELECT vtiger_crmentity.*, its4you_cashflow4you.*, $moduletable.$module_no AS relation_no, $moduletable.$total_fld as relation_am,
                    its4you_cashflow4you_associatedto.partial_amount, vtiger_currency_info.currency_symbol 
                    FROM its4you_cashflow4you 
                    INNER JOIN vtiger_crmentityrel ON its4you_cashflow4you.cashflow4youid=vtiger_crmentityrel.crmid
                    INNER JOIN $moduletable ON $moduletable.$moduleid=vtiger_crmentityrel.relcrmid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=its4you_cashflow4you.cashflow4youid
                    INNER JOIN its4you_cashflow4you_associatedto ON its4you_cashflow4you_associatedto.cashflow4youid=its4you_cashflow4you.cashflow4youid  
                    INNER JOIN vtiger_currency_info ON vtiger_currency_info.id = its4you_cashflow4you.currency_id 
                    WHERE vtiger_crmentity.deleted=0
                    AND vtiger_crmentityrel.relcrmid=?
                    AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                    ORDER BY paymentdate, cashflow4youid ASC";
          
          $result1 = $this->db->pquery($query, Array($_REQUEST['record'], $_REQUEST['record']));
        }
        if(isset($_REQUEST['paytype']) && $_REQUEST['paytype']=='loadPayments' ){
            /*$noofrows = $this->db->num_rows($result1);
            if(isset($noofrows) && $noofrows>0 ){
                while($row1 = $this->db->fetchByAssoc($result1))
                {
                    // index.php?module=Cashflow&action=DetailView&record=$pay_mnt_id 
                    $pay_mnt_id = $row1['crmid'];

                    $pay_mnt_paid += number_format($row1['paymentamount'], 3, '.', '');
                    $pay_mnt['altotal']['relation_am'] = number_format($row1['relation_am'], 3, '.', '');
                    $pay_mnt['altotal']['paid'] = number_format($pay_mnt_paid, 3, '.', '');
                    $to_pay = number_format($pay_mnt['altotal']['relation_am']-$pay_mnt['altotal']['paid'], 3, '.', '');
                    $pay_mnt['altotal']['to_pay'] = $to_pay;

                    $pay_table = '<tr style="height:25px">
                                            <td class="dvtCellInfo" align="left" width="10%">'."<a href='index.php?module=Cashflow4You&action=DetailView&record=".$pay_mnt_id."'>".$row1['cashflow4you_no']."</a>".'</td>
                                            <td class="dvtCellInfo" align="left" width="50%">'."<a href='index.php?module=Cashflow4You&action=DetailView&record=".$pay_mnt_id."'>".$row1['cashflow4youname']."</a>".'</td>
                                            '; 
                    if(isset($row1['paymentdate']) && $row1['paymentdate']!=''){
                            $pay_table .= '<td class="dvtCellInfo" align="left" width="10%">'.getValidDisplayDate($row1['paymentdate']).'</td>';
                    }else{
                            $pay_table .= '<td class="dvtCellInfo" align="left" width="10%">'.$mod_strings['p_pending'].'</td>';
                    }
                    $pay_table .= '		<td class="dvtCellInfo" align="left" width="15%">'.$row1['relation_no'].'</td>
                                            <td class="dvtCellInfo" align="right" width="15%">'.number_format($row1['paymentamount'], 3, '.', '').'</td>
                                            </tr>';
                    echo $pay_table;
                }
            }else{
                echo '<tr style="height:25px">
                                        <td class="dvtCellInfo" colspan=5 align="left">'.$app_strings['LBL_NO_RECORD'].'</td>
                          </tr>';
                $to_pay = $this->db->getOne("select total from $moduletable where $moduleid=".$_REQUEST['record'],0,"total");
                $pay_mnt_paid = 0;
                $pay_mnt['altotal']['relation_am'] = number_format($to_pay, 3, '.', '');
                $pay_mnt['altotal']['paid'] = number_format($pay_mnt_paid, 3, '.', '');
                $pay_mnt['altotal']['to_pay'] = number_format($to_pay, 3, '.', '');
                $_REQUEST['paymentamount'] = number_format($to_pay, 2, '.', '');
            }
            global $mod_strings;
            echo '<tr style="height:25px">
                                    <td class="dvtCellInfo" colspan=4 align="right" width="20%">'.$mod_strings[$module_am].'</td>
                                    <td class="dvtCellInfo" align="right" width="30%">'.$pay_mnt['altotal']['relation_am'].'</td>
                      </tr>';
              echo '<tr style="height:25px">
                                    <td class="dvtCellInfo" colspan=4 align="right" width="20%">'.$mod_strings['paid'].'</td>
                                    <td class="dvtCellInfo" align="right" width="30%">'.$pay_mnt['altotal']['paid'].'</td>
                      </tr>';
            echo '<tr style="height:25px">
                                    <td class="dvtCellInfo" colspan=4 align="right" width="20%">'.$mod_strings['to_pay'].'</td>
                                    <td class="dvtCellInfo" align="right" width="30%">'.$pay_mnt['altotal']['to_pay'].'</td>
                      </tr>';
            $pay_mnt['altotal']['paid'] = number_format($pay_mnt_paid, 3, '.', '');
            $to_pay = number_format($pay_mnt['altotal']['relation_am']-$pay_mnt['altotal']['paid'], 3, '.', '');
            $pay_mnt['altotal']['to_pay'] = $to_pay;
            $_REQUEST['paymentamount'] = $to_pay;*/
        }else{
            $Payments = Array();
            $total = 0;
            //$total_curr = "";
            $i = 1;
            while($row1 = $this->db->fetchByAssoc($result1))
            {  
                $setype = $utils->getModuleById($row1['relationid']);
                $Payments[ $row1['cashflow4youid'] ]['paymentstatus'] = $row1['cashflow4you_status'];
                $Payments[ $row1['cashflow4youid'] ]['currency'] = $row1['currency_symbol'];
                //$total_curr = $row1['currency_symbol'];
                $Payments[ $row1['cashflow4youid'] ]['no'] = $i++;
                if( $row1['paymentdate'] != "")
                {
                    $Payments[ $row1['cashflow4youid'] ]['paymentdate'] = getValidDisplayDate($row1['paymentdate']);
                }
                else
                {
                    $Payments[ $row1['cashflow4youid'] ]['paymentdate'] = "";
                }
                if( $relation_module == "SalesOrder" || $relation_module == "ITS4YouPreInvoice" )
                { 
                    $Payments[ $row1['cashflow4youid'] ]['paymentamount'] = CurrencyField::convertToUserFormat($row1['paymentamount'], $current_user, true);//$utils->formatNumber($row1['paymentamount']);
                    //$total += $row1['paymentamount'];
                    $partial_amount = $row1['paymentamount'];
                }
                else
                {
                    $Payments[ $row1['cashflow4youid'] ]['paymentamount'] = CurrencyField::convertToUserFormat($row1['partial_amount'], $current_user, true);//$utils->formatNumber($row1['partial_amount']);
                    if( $relation_module == "Potentials" )
                    {
                        //$total_curr = $current_user->currency_symbol;
                        if( $row1['currency_id'] != $current_user->currency_id )
                        {
                            $currencyRateAndSymbol = getCurrencySymbolandCRate($row1['currency_id']);
                            if( CurrencyField::getDBCurrencyId() != $row1['currency_id'] )
                            {
                                $tmp_total = CurrencyField::convertToDollar($row1['partial_amount'], $currencyRateAndSymbol['rate']);
                                $partial_amount = CurrencyField::convertToDollar($tmp_total, $current_user->conv_rate);
                            }
                            else
                            {
                                $tmp_total = CurrencyField::convertFromDollar($row1['partial_amount'], $currencyRateAndSymbol['rate']);
                                $partial_amount = CurrencyField::convertFromDollar($tmp_total, $current_user->conv_rate);
                            }
                        }
                        else
                        {
                            $partial_amount = $row1['partial_amount'];
                        }
                    }
                    else
                    {
                        if( $row1['currency_id'] == $currency_id )
                        {
                            $partial_amount = $row1['partial_amount'];
                        }
                        else
                        {
                            $rate = $row1['paymentamount']/$row1['payamount_main'];
                            $partial_amount = CurrencyField::convertToDollar($row1['partial_amount'], $rate); 
                            $partial_amount = CurrencyField::convertFromMasterCurrency($partial_amount,$currency_rate);
                        }
                    }
                }
                if( $row1['cashflow4you_paytype'] == "Incoming" )
                {
                    $total += abs( $partial_amount );
                }
                else
                {
                    $total -= abs( $partial_amount );
                }
            }
            $viewer->assign('PAYMENTS', $Payments);
          
            if( $setype == "PurchaseOrder" || $setype == "CreditNotes4You")
            {
                $total *= -1;
            }
            $viewer->assign('TOTAL', CurrencyField::convertToUserFormat($total, $current_user, true));//$utils->formatNumber($total));

            if( $relation_module == "Potentials")
            {
                $balance_total_show = CurrencyField::convertToUserFormat($grand_total - $total, $current_user, false);
            }
            else
            {
                $balance_total_show = CurrencyField::convertToUserFormat($grand_total - $total, $current_user, true);
            }
            
            $viewer->assign('GTOTAL_CURRENCY', $gtotal_curr);
            $viewer->assign('TOTAL_CURRENCY', $total_curr != "" ? $total_curr : $gtotal_curr);
            $viewer->assign('GRAND_TOTAL', $grand_total_show);//$utils->formatNumber($grand_total));
            $viewer->assign('TOTAL_BALLANCE', $balance_total_show);//$utils->formatNumber($grand_total - $total));
        }   $viewer->assign('CASHFLOW4YOU_MOD', return_module_language($currentLanguage, "Cashflow4You"));

        if(isset($_REQUEST['mode']) && $_REQUEST['mode']!='edit' && isset($_REQUEST['paytype']) && $_REQUEST['paytype']=='loadPayments'){
            echo "::||@#@||::";
            echo number_format($to_pay, 2, '.', '');
        }
                
        $viewer->view("Cashflow4YouActions.tpl", 'Cashflow4You');
    }
}    