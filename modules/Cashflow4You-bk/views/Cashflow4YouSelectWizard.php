<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once('Smarty_setup.php');
require_once('modules/Users/Users.php');
require_once('modules/Cashflow4You/Cashflow4YouUtils.php');
require_once('modules/Cashflow4You/models/Utils.php');

include_once dirname(__FILE__) . '/Cashflow4You.php';

global $theme, $currentModule, $mod_strings, $app_strings, $current_user, $adb;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$utils = new Cashflow4You_Utils_Model();
$CashflowUtils = new Cashflow4You_Cashflow4YouUtils_Action();
        
$smarty = new vtigerCRM_Smarty();

$smarty->assign("THEME",$theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("MOD", $mod_strings);

$smarty->assign("MOD_STRING", $idstring = implode(';',$mod_strings));
$smarty->assign("MODULE", $currentModule);
$smarty->assign("IS_ADMIN", is_admin($current_user));

$smarty->assign("CALENDAR_LANG", $app_strings['LBL_JSCALENDAR_LANG']);
$smarty->assign("CALENDAR_DATEFORMAT", parse_calendardate($app_strings['NTC_DATE_FORMAT']));

/*$sourcemodule = vtlib_purify($_REQUEST['sourcemodule']);
$smarty->assign("SOURCEMODULE", $sourcemodule);*/
$idstring = vtlib_purify($_REQUEST['idstring']);
$idstring = trim($idstring, ';');
$idlist = explode(';', $idstring);
sort($idlist);
$idstring = implode(';',$idlist);
$smarty->assign("IDSTRING", $idstring);
if( strpos($idstring,';') === false )
{ 
  $smarty->assign("RELATIONID", $idstring);
}
else
{
  $smarty->assign("RELATIONID", "");
}
 
/*$PaymentType = getOutputHtml(15,'cashflow4you_paymethod','cashflow4you_paymethod',100,array(),1,'Cashflow4You','','V~O');
$smarty->assign("PAYMENTTYPE", $PaymentType[3][0]);*/

$Invoices = Array();
$open_sum = 0;
$total_sum = 0;
$paid_sum = 0;
$outstanding_sum = 0;
$balance_open_amount_sum = 0;
$balance_payment_sum = 0;
$accountid = NULL;

foreach($idlist as $invid){
    $focusInstance = CRMEntity::getInstance($sourcemodule);
    $focusInstance->retrieve_entity_info($invid, $sourcemodule);
    switch($sourcemodule){
        case "Invoice":
            if( $accountid == NULL )
            {
            $accountid = $focusInstance->column_fields['account_id'];
            }
            if($focusInstance->column_fields['account_id'] != $accountid)
            {
            echo "1|||###|||".$mod_strings['LBL_CASHFLOW_SAME_ORGA'];
            exit;
            }
        break;
        case "PurchaseOrder":
                if( $accountid == NULL )
        {
        $accountid = $focusInstance->column_fields['vendorid'];
        }
        if($focusInstance->column_fields['vendorid'] != $accountid)
        {
        echo "1|||###|||".$mod_strings['LBL_CASHFLOW_SAME_ORGA'];
        exit;
        }
    break;
    }
}
echo "0|||###|||";
 
$smarty->assign("RELATEDTO", $focusInstance->column_fields['account_id']);
$invoices_num=0;
foreach($idlist as $invid){
    $focusInstance = CRMEntity::getInstance($sourcemodule);
    $focusInstance->retrieve_entity_info($invid, $sourcemodule);
    switch($sourcemodule){
        case "Invoice":
      //if($focusInstance->column_fields['invoicestatus']!='Paid'){
        $outstanding = 0.00;
      	$Invoices[$invid]=Array('subject'=>$focusInstance->column_fields['subject'], 'hdnGrandTotal'=>sprintf("%.02f", $focusInstance->column_fields['hdnGrandTotal'] ), 'openamount'=>sprintf("%.02f", $focusInstance->column_fields['openamount'] ), 'paidamount'=>sprintf("%.02f", $focusInstance->column_fields['paidamount']), 'outstandingbalance'=>sprintf("%.02f", $outstanding));
      	$open_sum += $focusInstance->column_fields['openamount'];
        $total_sum += $focusInstance->column_fields['hdnGrandTotal'];
        $paid_sum += $focusInstance->column_fields['paidamount'];
        $outstanding_sum += $outstanding;
        $invoices_num++;
     // }
        break;
    case "PurchaseOrder":
        $Invoices[$invid]=Array('subject'=>$focusInstance->column_fields['subject'], 'hdnGrandTotal'=>$focusInstance->column_fields['hdnGrandTotal'], 'openamount'=>$focusInstance->column_fields['openamount']);
        $open_sum += $focusInstance->column_fields['openamount'];
        $total_sum += $focusInstance->column_fields['hdnGrandTotal'];
        $paid_sum += $focusInstance->column_fields['paidamount'];
        $outstanding_sum += 0.00;
        $invoices_num++;
        break;
    }
}
//$open_sum = $utils->formatNumber($open_sum, false);
//$open_sum = CurrencyField::convertToUserFormat($open_sum, null, true);
$smarty->assign("INVOICES_NUM", $invoices_num);
$smarty->assign("SOURCEMODULE",$sourcemodule);
$smarty->assign("TODAY",getValidDisplayDate(date("Y-m-d")));
$smarty->assign("INVOICES",$Invoices);
$smarty->assign("OPEN_SUM",sprintf("%.02f", $open_sum ));
$smarty->assign("TOTAL_SUM",sprintf("%.02f", $total_sum ));
$smarty->assign("PAID_SUM",sprintf("%.02f", $paid_sum ));
$smarty->assign("OUTSTANDING_SUM",sprintf("%.02f", $outstanding_sum ));

$smarty->assign("BALANCE_OPEN_AMOUNT_SUM",sprintf("%.02f", $balance_open_amount_sum ));
$smarty->assign("BALANCE_PAYMENT_SUM",sprintf("%.02f", $balance_payment_sum ));

switch( $user_focus->column_fields[ "date_format" ] )
{
  case "yyyy-mm-dd":
    $datefirmat = "%Y-%m-%d";
    $datefirmat2 = "Y-m-d";
    break;
  case "dd-mm-yyyy":
    $datefirmat = "%d-%m-%Y";
    $datefirmat2 = "d-m-Y";
    break;
  case "mm-dd-yyyy":
    $datefirmat = "%m-%d-%Y";
    $datefirmat2 = "m-d-Y";
    break;
  default:
    $datefirmat = "%Y-%m-%d";
    $datefirmat2 = "Y-m-d";
    break;
}
$smarty->assign("DATEFIRMAT", $datefirmat );
$userCurrenDate = new DateTimeField(date($datefirmat2));
$today = $userCurrenDate->getDisplayDate();

$DefaulValue = Array("paymentamount"=>$open_sum, "accountingdate"=>$today, "Due date"=>$today, "Payment date"=>$today );
$qcreate_array = $CashflowUtils->QuickCashflow4YouCreate( $DefaulValue );
$validationData = $qcreate_array['data'];
$data = split_validationdataArray($validationData);

$smarty->assign("VALIDATION_DATA_FIELDNAME",$data['fieldname']);
$smarty->assign("VALIDATION_DATA_FIELDDATATYPE",$data['datatype']);
$smarty->assign("VALIDATION_DATA_FIELDLABEL",$data['fieldlabel']);
$smarty->assign("QUICKCREATE", $qcreate_array['form']);

$user_focus = CRMEntity::getInstance( "Users" );
$user_focus->retrieve_entity_info( $_SESSION['authenticated_user_id'], "Users" );

$smarty->display(vtlib_getModuleTemplate($currentModule, 'Cashflow4YouSelectWizard.tpl'));
