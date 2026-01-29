<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once 'modules/Cashflow4You/models/Utils.php';

class Cashflow4You_CreatePaymentActionAjax_View extends Vtiger_IndexAjax_View {
    var $db;
    
    function __construct() {
		parent::__construct();
		$this->exposeMethod('showCreatePaymentForm');
		$this->exposeMethod('showAddCommentForm');
		$this->exposeMethod('showComposeEmailForm');
		$this->exposeMethod('showSendSMSForm');
		$this->exposeMethod('showDuplicatesSearchForm');
		$this->exposeMethod('transferOwnership');
                $this->db = PearDatabase::getInstance();
	}
        
        function preProcess(Vtiger_Request $request, $display=true) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$viewer->assign('PAGETITLE', $this->getPageTitle($request));
		$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
		$viewer->assign('STYLES',$this->getHeaderCss($request));
		$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
		$viewer->assign('LANGUAGE_STRINGS', Vtiger_Language_Handler::export("Cashflow4You", 'jsLanguageStrings'));
		$viewer->assign('LANGUAGE', $currentUser->get('language'));
		if($display) {
			$this->preProcessDisplay($request);
		}
	}
        
	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function returns the mass edit form
	 * @param Vtiger_Request $request
	 */
        function showCreatePaymentForm (Vtiger_Request $request){
                require_once('modules/Cashflow4You/models/Utils.php');
                
                $utils = new Cashflow4You_Utils_Model();
		$moduleName = $request->getModule();
                $current_user = Users_Record_Model::getCurrentUserModel();
                
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$viewer = $this->getViewer($request);
                
                sort($selectedIds);
                $idstring = implode(';',$selectedIds);
                $viewer->assign("IDSTRING", $idstring); 

                foreach($selectedIds as $invid){
                    $utils = new Cashflow4You_Utils_Model();
                    $sourcemodule = $utils->getModuleById($invid);

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
                                echo "1|||###|||".  vtranslate('LBL_CASHFLOW_SAME_ORGA', 'Cashflow4You');
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
                $Invoices = Array();
                $open_sum = 0;
                $total_sum = 0;
                $paid_sum = 0;
                $vat_sum = 0;
                $outstanding_sum = 0;
                $balance_open_amount_sum = 0;
                $balance_payment_sum = 0;
                $accountid = NULL;
                $viewer->assign("RELATEDTO", $focusInstance->column_fields['account_id']);
                $viewer->assign("CONTACT", $focusInstance->column_fields['contact_id']);
                $invoices_num=0;
                $relations = "";
                foreach($selectedIds as $invid){
                    $focusInstance = CRMEntity::getInstance($sourcemodule);
                    $focusInstance->retrieve_entity_info($invid, $sourcemodule);
                    switch($sourcemodule){
                        case "Invoice":
                            //if($focusInstance->column_fields['invoicestatus']!='Paid'){
                            $outstanding = 0.00;
                            $hdnGrandTotal = CurrencyField::convertToUserFormat($focusInstance->column_fields['hdnGrandTotal'], $current_user, true);
                            $select = "SELECT SUM(partial_amount) AS partial_amount FROM its4you_cashflow4you_associatedto 
                                        INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                                        WHERE vtiger_crmentity.deleted=0 AND cashflow4you_associated_id = ?";
                            $res = $this->db->pquery($select, Array($invid));
                            $paid = $this->db->query_result($res, 0, "partial_amount");
                            $paidamount = CurrencyField::convertToUserFormat($paid, $current_user, true);
                            $openamount = CurrencyField::convertToUserFormat($focusInstance->column_fields['hdnGrandTotal'] - $paid, $current_user, true);
                            $outstanding = CurrencyField::convertToUserFormat($outstanding, $current_user, true);
//                            $Invoices[$invid]=Array('subject'=>$focusInstance->column_fields['subject'], 'hdnGrandTotal'=>$hdnGrandTotal, 'openamount'=>$openamount, 'paidamount'=>$paidamount, 'outstandingbalance'=>$outstanding);
                            $Invoices[$invid]=Array('subject'=>$focusInstance->column_fields['subject'], 'hdnGrandTotal'=>$hdnGrandTotal, 'openamount'=>sprintf("%.02f", ($focusInstance->column_fields['hdnGrandTotal'] - $paid) ), 'show_openamount' => $openamount, 'paidamount'=>$paidamount, 'outstandingbalance'=>sprintf("%.02f", $outstanding));
                            $open_sum += $focusInstance->column_fields['openamount'];
                            $total_sum += $focusInstance->column_fields['hdnGrandTotal'];
                            //$paid_sum += $focusInstance->column_fields['paidamount'];
                            $paid_sum += $paid;
                            $vat_sum += $focusInstance->column_fields['hdnGrandTotal']-$focusInstance->column_fields['hdnSubTotal'];
                            $outstanding_sum += $outstanding;
                            if( $invoices_num == 0 )
                            {
                              $relationid = $invid;
                            }
                            /*if(strlen($relations)!= 0)
                            {
                              $relations .= ";";
                            }
                            $relations .= $focusInstance->column_fields['invoice_no'];*/
                            $invoices_num++;
                            // }
                            break;
                        case "PurchaseOrder":
                            $outstanding = 0.00;
                            $hdnGrandTotal = CurrencyField::convertToUserFormat($focusInstance->column_fields['hdnGrandTotal'], $current_user, true);
                            $openamount = CurrencyField::convertToUserFormat($focusInstance->column_fields['openamount'], $current_user, true);
                            $paidamount = CurrencyField::convertToUserFormat($focusInstance->column_fields['paidamount'], $current_user, true);
                            $outstanding = CurrencyField::convertToUserFormat($outstanding, $current_user, true);
                            $Invoices[$invid]=Array('subject'=>$focusInstance->column_fields['subject'], 'hdnGrandTotal'=>$hdnGrandTotal, 'openamount'=>$openamount, 'paidamount'=>$paidamount, 'outstandingbalance'=>$outstanding);
                            $open_sum += $focusInstance->column_fields['openamount'];
                            $total_sum += $focusInstance->column_fields['hdnGrandTotal'];
                            $paid_sum += $focusInstance->column_fields['paidamount'];
                            $outstanding_sum += $outstanding;
                            $invoices_num++;
                            break;
                    }
                    $due_date = $focusInstance->column_fields['duedate'];
                    $currency_id = $focusInstance->column_fields['currency_id'];
                }
                $open_sum = $total_sum - $paid_sum;
                $cursym_convrate = getCurrencySymbolandCRate($currency_id);
                //$open_sum = CurrencyField::convertToUserFormat($open_sum, $current_user, true);
                if( $paid_sum == 0)
                {
                  $viewer->assign("VAT_AMOUNT", $vat_sum);
                }
                else
                {
                  $viewer->assign("VAT_AMOUNT", 0);
                }
                if($invoices_num == 1 )
                {
                  $viewer->assign("RELATIONID", $relationid);
                }
                //$viewer->assign("RELATIONS", $relations);
                $viewer->assign("INVOICES_NUM", $invoices_num);
                $viewer->assign("CURRENCY_SYMBOL", $cursym_convrate["symbol"]);
                $viewer->assign("SOURCEMODULE",$sourcemodule);
                $viewer->assign("TODAY",getValidDisplayDate(date("Y-m-d")));
                $viewer->assign("INVOICES",$Invoices);
                $viewer->assign("OPEN_SUM",sprintf("%.02f", $open_sum ));
                //$viewer->assign("OPEN_SUM",$utils->formatNumber($open_sum, false ) ) ;
                 
                //$viewer->assign("OPEN_SUM",$open_sum );
                $viewer->assign("REMAINING_SUM",CurrencyField::convertToUserFormat($open_sum, $current_user, true) );
                $viewer->assign("TOTAL_SUM",CurrencyField::convertToUserFormat($total_sum, $current_user, true) );
                $viewer->assign("PAID_SUM",CurrencyField::convertToUserFormat($paid_sum, $current_user, true) );
                $viewer->assign("OUTSTANDING_SUM",$utils->formatNumber($outstanding_sum, false ) );

                $viewer->assign("BALANCE_OPEN_AMOUNT_SUM",$utils->formatNumber($balance_open_amount_sum, false ) );
                $viewer->assign("BALANCE_PAYMENT_SUM",$utils->formatNumber($balance_payment_sum, false ) );
                
                switch( $utils->getUserDateFormat() )
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
                

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName); 
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
                $fieldInfo = array();
		$fieldList = $moduleModel->getFields();
		foreach ($fieldList as $fieldName => $fieldModel) {
      $fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
      if( $fieldName=='paymentamount')
      {
          //$fieldModel->set('fieldvalue', $utils->formatNumber($open_sum, false));
          //$fieldInfo[$fieldName]["defaultvalue"]=$utils->formatNumber($open_sum, false);
          $fieldModel->set('fieldvalue', $open_sum);
          $fieldInfo[$fieldName]["defaultvalue"]=$open_sum;
      }
      if( $fieldName=='paymentdate' || $fieldName=='accountingdate')
      {
          $fieldModel->set('fieldvalue', date("Y-m-d"));
          $fieldInfo[$fieldName]["defaultvalue"]=date("Y-m-d");
      }
      if( $fieldName=='assigned_user_id')
      {
          $fieldModel->set('fieldvalue', $current_user->getId());	
          //$fieldInfo[$fieldName]["defaultvalue"]=getValidDisplayDate(date($datefirmat2));
      }
      if( $fieldName=='due_date' && count( $selectedIds)== 1) 
      {
          $fieldModel->set('fieldvalue', $due_date);	
          $fieldInfo[$fieldName]["defaultvalue"]=$due_date;
      }
		}

		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODE', 'createPayment');
 
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('MODULE_MODEL',$moduleModel); 
		$viewer->assign('MASS_EDIT_FIELD_DETAILS',$fieldInfo); 
                
                $recordStructure = $recordStructureInstance->getStructure();
       
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
                $viewer->assign('MODULE_MODEL', $moduleModel);
                $searchKey = $request->get('search_key');
                $searchValue = $request->get('search_value');
                $operator = $request->get('operator');
                if(!empty($operator)) {
                    $viewer->assign('OPERATOR',$operator);
                    $viewer->assign('ALPHABET_VALUE',$searchValue);
                    $viewer->assign('SEARCH_KEY',$searchKey);
                }
                                
		echo $viewer->view('Cashflow4YouSelectWizard.tpl',$moduleName,true);
	}
	
	/**
	 * Function returns the Add Comment form
	 * @param Vtiger_Request $request
	 */
	function showAddCommentForm(Vtiger_Request $request){
            $sourceModule = $request->getModule();
            $moduleName = 'ModComments';
            $cvId = $request->get('viewname');
            $selectedIds = $request->get('selected_ids');
            $excludedIds = $request->get('excluded_ids');

            $viewer = $this->getViewer($request);
            $viewer->assign('SOURCE_MODULE', $sourceModule);
            $viewer->assign('MODULE', $moduleName);
            $viewer->assign('CVID', $cvId);
            $viewer->assign('SELECTED_IDS', $selectedIds);
            $viewer->assign('EXCLUDED_IDS', $excludedIds);
            $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
                    $operator = $request->get('operator');
            if(!empty($operator)) {
                            $viewer->assign('OPERATOR',$operator);
                            $viewer->assign('ALPHABET_VALUE',$searchValue);
                $viewer->assign('SEARCH_KEY',$searchKey);
                    }

                    echo $viewer->view('AddCommentForm.tpl',$moduleName,true);
        }

	
}
