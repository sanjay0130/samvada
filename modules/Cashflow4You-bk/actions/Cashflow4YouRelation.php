<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
error_reporting(0);
include_once "modules/Cashflow4You/models/Utils.php";

class Cashflow4YouRelation {
    var $db, $log; // Used in class functions of CRMEntity
    
    function __construct() {
        $this->column_fields = getColumnFields('Cashflow4You');
        $this->db = PearDatabase::getInstance();
        //$this->db->setDebug(true);
        $this->log = LoggerManager::getLogger('Cashflow4You');;
    }
    
    function updateSavedRelation($module,$id,$cashflow4youId=0)
    {
        $utils = new Cashflow4You_Utils_Model();
        $current_user = Users_Record_Model::getCurrentUserModel();
        if( !key_exists($module, $utils->Entity_table))
        {
            if($cashflow4youId>0){
                    $res = $this->db->pquery("SELECT currency_id FROM its4you_cashflow4you WHERE cashflow4youid=".$cashflow4youId." AND currency_id IS NOT NULL AND currency_id != ''",Array());
                    if($this->db->num_rows($res)==0){
                            
                            $this->db->pquery("UPDATE its4you_cashflow4you SET currency_id=? WHERE cashflow4youid=?",Array($current_user->currency_id,$cashflow4youId));
                    }
            }
            return null;
        }

        $entity_type_lower = $utils->Entity_table[$module]["entity"];
        $entity_table = $utils->Entity_table[$module]["table"];
        $entity_status = $utils->Entity_table[$module]["status"];
        $entity_status_value = $utils->Entity_table[$module]["status_value"];
        $entity_status_value_back = $utils->Entity_table[$module]["status_value_back"];
        $relatedto_column = $utils->Entity_table[$module]["relatedto"];
        $total_fld = $utils->Entity_table[$module]["total_fld"];
        $focus = CRMEntity::getInstance($module);
        $entity_table_id = $focus->table_index;
        
        // get entity data
        $relatedto_row = array();
        $get_fld = $entity_type_lower.'_no';
        $sql = "SELECT ".$get_fld.", ".$relatedto_column.", ".$total_fld." AS total ";
        if( $module != "Potentials")
        {
            $sql .= ", currency_id ";
        }
        $sql .= "FROM ".$entity_table."
                INNER JOIN vtiger_crmentity ON ".$entity_table.".".$entity_table_id."=vtiger_crmentity.crmid 
                WHERE vtiger_crmentity.deleted=0
                AND ".$entity_table_id."=".$id;

        $res = $this->db->pquery($sql,Array());
        if($this->db->num_rows($res)>0){
                $relatedto_row = $this->db->fetchByAssoc($res);
        }
        // update cashflow data - relation_no & currency_id
        if($cashflow4youId>0){
                if(!empty($relatedto_row)){
                        $row = $this->db->fetchByAssoc($res);
                        $this->db->pquery("UPDATE its4you_cashflow4you SET relation_no=?, cashflow4you_associated_no=? WHERE cashflow4youid=?", Array($relatedto_row[$get_fld],$relatedto_row[$get_fld],$cashflow4youId));
                        $subsql = "SELECT relatedto FROM its4you_cashflow4you WHERE cashflow4youid=".$cashflow4youId;
                        $subres = $this->db->pquery($subsql,Array());
                        $subrow = $this->db->fetchByAssoc($subres);
                        if($subrow['relatedto']=='' || $subrow['relatedto']==0){
                                $this->db->pquery("UPDATE its4you_cashflow4you SET relatedto=? WHERE cashflow4youid=?", Array($relatedto_row[$relatedto_column],$cashflow4youId));
                        }
                }
        }
        // update relation module data - open and paid amount; if whole sum (total) is paid, then update status to Paid too (invoice only)
        $paid = 0;
        $currency_rate = 1;
        $currency_id = 1;
        if( $module != "Potentials")
        {
            $currencyRateAndSymbol = getCurrencySymbolandCRate($relatedto_row['currency_id']);
            $currency_rate =  $currencyRateAndSymbol['rate'];
            $currency_id = $relatedto_row['currency_id'];
        }
        $to_pay = abs($relatedto_row['total']);
        $total = $relatedto_row['total'];
               
        if( $module == "Invoice" )
        {
          /*$so_query = "SELECT vtiger_invoice.salesorderid, its4you_cashflow4you.cashflow4youid, its4you_cashflow4you.paymentamount, its4you_cashflow4you.payamount_main
                        FROM vtiger_invoice 
                        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                        INNER JOIN its4you_cashflow4you ON its4you_cashflow4you.relationid=vtiger_invoice.salesorderid
                        WHERE vtiger_crmentity.deleted=0
                        AND vtiger_invoice.invoiceid=?";
          $resultSO = $this->db->pquery($so_query, Array($id));
          $num = $this->db->num_rows($resultSO);
         
          if( $num > 0)
          {
            for( $i=0; $i<$num; $i++)
            {
              $salesorderid = $this->db->query_result($resultSO, $i, 'salesorderid');
              $cashflow4youid = $this->db->query_result($resultSO, $i, 'cashflow4youid');
              $paymentamount = $this->db->query_result($resultSO, $i, 'payamount_main');
              $query = "SELECT cashflow4you_associated_id 
                        FROM its4you_cashflow4you_associatedto
                        INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                        WHERE vtiger_crmentity.deleted=0
                        AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                        OR its4you_cashflow4you_associatedto.cashflow4youid=?
                        GROUP BY its4you_cashflow4you_associatedto.cashflow4youid";
              $result_rel = $this->db->pquery($query, array( $salesorderid, $cashflow4youid ) );
              $num_rel = $this->db->num_rows($result_rel);
              if( $num_rel == 0)
              {
                $insert = "INSERT INTO its4you_cashflow4you_associatedto ( cashflow4youid, cashflow4you_associated_id, partial_amount )VALUES (?, ?, ?)";
                $this->db->pquery( $insert, Array( $cashflow4youid, $id, $paymentamount ) );
                $insert = "INSERT INTO vtiger_crmentityrel ( crmid, module, relcrmid, relmodule )VALUES (?, ?, ?, ?)";
                $this->db->pquery( $insert, Array( $cashflow4youid, "Cashflow4You", $id, "Invoice" ) );
              }
            }
          }*/
            $this->setInvoiceAsociated( $module, $id );
        }
        else if( ( $module == "SalesOrder" || $module == "ITS4YouPreIncoive" ) && $cashflow4youId != 0 && $cashflow4youId != "" )
        {
            $query = "SELECT cashflow4you_associated_id FROM its4you_cashflow4you_associatedto
                      INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                      INNER JOIN vtiger_invoice on vtiger_invoice.invoiceid=its4you_cashflow4you_associatedto.cashflow4you_associated_id 
                      WHERE vtiger_crmentity.deleted=0
                      AND vtiger_invoice.salesorderid=?
                      AND its4you_cashflow4you_associatedto.cashflow4youid=?";
            $result2 = $this->db->pquery($query, array($id,$cashflow4youId ) );
            if( $this->db->num_rows($result2) == 1 )
            {
                $id = $this->db->query_result($result2, 0, 'cashflow4you_associated_id');
                $entity_type_lower = $utils->Entity_table["Invoice"]["entity"];
                $entity_table = $utils->Entity_table["Invoice"]["table"];
                $entity_status = $utils->Entity_table["Invoice"]["status"];
                $entity_status_value = $utils->Entity_table["Invoice"]["status_value"];
                $entity_status_value_back = $this->Entity_table["Invoice"]["status_value_back"];
                $relatedto_column = $utils->Entity_table["Invoice"]["relatedto"];
                $total_fld = $utils->Entity_table["Invoice"]["total_fld"];
                $focus = CRMEntity::getInstance("Invoice");
                $entity_table_id = $focus->table_index;
            }
        }
        if( $entity_type_lower == "invoice" )
        {
            $query = "SELECT its4you_cashflow4you_associatedto.cashflow4youid, partial_amount AS amount, its4you_cashflow4you.cashflow4you_paytype, its4you_cashflow4you.relationid, 
                      its4you_cashflow4you.currency_id, its4you_cashflow4you.payamount_main 
                      FROM its4you_cashflow4you_associatedto
                      INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                      INNER JOIN its4you_cashflow4you on its4you_cashflow4you.cashflow4youid=its4you_cashflow4you_associatedto.cashflow4youid 
                      WHERE vtiger_crmentity.deleted=0
                      AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                      AND its4you_cashflow4you_associatedto.cashflow4youid IN (SELECT cashflow4youid FROM its4you_cashflow4you WHERE cashflow4you_status = 'Paid' OR cashflow4you_status = 'Received')
                      GROUP BY its4you_cashflow4you_associatedto.cashflow4youid";
        }
        else if( $entity_type_lower == "potentials" )
        {
          $query = "SELECT payamount_main AS amount, cashflow4you_paytype, relationid
                    FROM its4you_cashflow4you 
                    INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you.cashflow4youid 
                    WHERE vtiger_crmentity.deleted=0 
                    AND its4you_cashflow4you.relationid=?
                    AND its4you_cashflow4you.cashflow4youid IN (SELECT cashflow4youid FROM its4you_cashflow4you WHERE cashflow4you_status = 'Paid' OR cashflow4you_status = 'Received') 
                    GROUP BY its4you_cashflow4you.cashflow4youid";
        }
        else
        {
          $query = "SELECT paymentamount AS amount, cashflow4you_paytype, relationid, currency_id, payamount_main
                    FROM its4you_cashflow4you 
                    INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you.cashflow4youid 
                    WHERE vtiger_crmentity.deleted=0 
                    AND its4you_cashflow4you.relationid=?
                    AND its4you_cashflow4you.cashflow4youid IN (SELECT cashflow4youid FROM its4you_cashflow4you WHERE cashflow4you_status = 'Paid' OR cashflow4you_status = 'Received') 
                    GROUP BY its4you_cashflow4you.cashflow4youid";
        }
         
        $result2 = $this->db->pquery($query, array($id) );
        $num = $this->db->num_rows($result2);
       
        if( $num > 0)
        {
          $sum_paymentamount = 0;
          for( $i=0; $i<$num; $i++)
          {
            if( $entity_type_lower == "invoice" )
            {
              $this->updateRelationsField($this->db->query_result($result2, $i, 'cashflow4youid'));
            }
            $curr_id = $this->db->query_result($result2, $i, 'currency_id');
            /*$payamount_main = $this->db->query_result($result2, $i, 'payamount_main');
            $paymentamount = $this->db->query_result($result2, $i, 'paymentamount');
            
            $rate = $paymentamount/$payamount_main;*/
            
            if( $curr_id == $currency_id )
            {
                $amount = abs($this->db->query_result($result2, $i, 'amount'));
            }
            else
            {
                $amount = abs($this->db->query_result($result2, $i, 'payamount_main'));
                $amount = CurrencyField::convertFromMasterCurrency($amount,$currency_rate);
            }
            
            if( $this->db->query_result($result2, $i, 'cashflow4you_paytype') == "Incoming" )
            {
                $sum_paymentamount += $amount;
            }
            else
            {
                $sum_paymentamount -= $amount;
            }
            //$sum_paymentamount += $this->db->query_result($result2, $i, 'amount');
          }
          if( $utils->getModuleById($id) == "PurchaseOrder" ||  $utils->getModuleById($id) == "CreditNotes4You")
            {
                $sum_paymentamount *= -1;
            }
          
          $paid += number_format($sum_paymentamount, 3, '.', '');
          $to_pay = number_format($to_pay-$paid, 3, '.', '');
          if($to_pay<0)
          {
              $to_pay = 0;
          }
        }
        $upd_arr = Array($to_pay,$paid);
        $upd = "UPDATE ".$entity_table." SET p_open_amount=?, p_paid_amount=?";
        if( $module =='Invoice' || $module =='ITS4YouPreInvoice' )
        {
          $upd .= ", received=?, balance=?";
          $upd_arr[]=$paid;
          $upd_arr[]=$to_pay;
        }
        if( $module =='PurchaseOrder')
        {
          $upd .= ", paid=?, balance=?";
          $upd_arr[]=$paid;
          $upd_arr[]=$to_pay;
        }
        $upd .= " WHERE ".$entity_table_id."=?";
        $upd_arr[]=$id;
        $this->db->pquery($upd, $upd_arr);

        $updateStatus = 0;
        $inv_module = Vtiger_Module::getInstance("ITS4YouPreInvoice");
        $refstring = $_REQUEST["refstring"];
       
        if( $inv_module != false && $module =='Invoice' && isset($refstring) && $refstring!="" )
        {//ak ma nainstalovane PreInvoice a uklada sa Invoice
           $IdList = explode(";",$refstring);
           $isITS4YouPreInvoice = false;
           foreach( $IdList AS $listid )
           {    
                $select = "SELECT setype FROM vtiger_crmentity WHERE crmid=?";
                $res = $this->db->pquery($select, Array( $listid ) );
                if( $this->db->query_result($res,0,"setype") == "ITS4YouPreInvoice")
                {
                   $isITS4YouPreInvoice = true; 
                }
           }
            if( $isITS4YouPreInvoice == true)
            {
                $updateStatus = 1;
            }
        }
        $paid = number_format($paid, $current_user->no_of_currency_decimals, '.', '');
        $total = number_format($total, $current_user->no_of_currency_decimals, '.', '');
        $to_pay = number_format($to_pay, $current_user->no_of_currency_decimals, '.', '');

        if( $cashflow4youId != 0 || $updateStatus == 1 )
        {
            if( ( $module=='Invoice' || $module=='ITS4YouPreInvoice' || $module=='CreditNotes4You') && $paid>=$total && $to_pay == 0 && $entity_status!="" && $entity_status_value!=""){
                    $updateinvstat = "UPDATE ".$entity_table." SET ".$entity_status."='".$entity_status_value."' WHERE ".$entity_table_id."=".$id;
                    $this->db->pquery($updateinvstat,Array());
            }
            else if( ( $module=='Invoice' || $module=='ITS4YouPreInvoice' || $module=='CreditNotes4You' ) && $entity_status!="" && $entity_status_value_back != "")
            {
                $inv_query = "SELECT ".$entity_status." AS status
                            FROM ".$entity_table."
                            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=".$entity_table.".".$entity_table_id."
                            WHERE vtiger_crmentity.deleted=0
                            AND ".$entity_table.".".$entity_table_id."=?";
                $resultINV = $this->db->pquery($inv_query, Array($id));
                $inv_status = $this->db->query_result($resultINV, 0, 'status');
                if( $inv_status == $entity_status_value )
                {
                    $updateinvstat = "UPDATE ".$entity_table." SET ".$entity_status."='".$entity_status_value_back."' WHERE ".$entity_table_id."=".$id;
                    $this->db->pquery($updateinvstat,Array());
                }
            }
        }
    }

    function QuickCashflow4YouCreate( $DefaultValue ) {
            $this->log->debug("Entering QuickCreate(" . $module . ") method ...");
            $current_user = Users_Record_Model::getCurrentUserModel();

            $module = "Cashflow4You";
            $tabid = getTabid($module);

            //Adding Security Check
            require('user_privileges/user_privileges_' . $current_user->id . '.php');
            if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
                    $quickcreate_query = "select * from vtiger_field where quickcreate in (0,2) and tabid = ? and vtiger_field.presence in (0,2) and displaytype != 2 order by sequence";
                    $params = array($tabid);
            } else {
                    $profileList = getCurrentUserProfileList();
                    $quickcreate_query = "SELECT vtiger_field.* FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid WHERE vtiger_field.tabid=? AND quickcreate in (0,2) AND vtiger_profile2field.visible=0 AND vtiger_profile2field.readonly = 0 AND vtiger_def_org_field.visible=0  AND vtiger_profile2field.profileid IN (" . generateQuestionMarks($profileList) . ") and vtiger_field.presence in (0,2) and displaytype != 2 GROUP BY vtiger_field.fieldid ORDER BY sequence";
                    $params = array($tabid, $profileList);
                    //Postgres 8 fixes
                    if ($this->db->dbType == "pgsql")
                            $quickcreate_query = fixPostgresQuery($quickcreate_query, $this->log, 0);
            }
            $category = getParentTab();
            $result = $this->db->pquery($quickcreate_query, $params);
            $noofrows = $this->db->num_rows($result);
            $fieldName_array = Array();
            for ($i = 0; $i < $noofrows; $i++) {
                    $fieldtablename = $this->db->query_result($result, $i, 'tablename');
                    $uitype = $this->db->query_result($result, $i, "uitype");
                    $fieldname = $this->db->query_result($result, $i, "fieldname");
                    $fieldlabel = $this->db->query_result($result, $i, "fieldlabel");
                    $maxlength = $this->db->query_result($result, $i, "maximumlength");
                    $generatedtype = $this->db->query_result($result, $i, "generatedtype");
                    $typeofdata = $this->db->query_result($result, $i, "typeofdata");
                    $defaultvalue = $this->db->query_result($result, $i, "defaultvalue");
                    if( array_key_exists( $fieldlabel, $DefaultValue )){
                      $defaultvalue = $DefaultValue[$fieldlabel]; 
                    }     
                    $col_fields[$fieldname] = $defaultvalue;

                    //to get validationdata
                    $fldLabel_array = Array();
                    $fldLabel_array[getTranslatedString($fieldlabel)] = $typeofdata;
                    $fieldName_array[$fieldname] = $fldLabel_array;

                    // These fields should not be shown in the UI as they are already shown as part of other fields, but are required for validation.
                    if ($fieldname == 'time_start' || $fieldname == 'time_end')
                            continue;

                    $custfld = getOutputHtml($uitype, $fieldname, $fieldlabel, $maxlength, $col_fields, $generatedtype, $module, '', $typeofdata);
                    $qcreate_arr[] = $custfld;
            }
            for ($i = 0, $j = 0; $i < count($qcreate_arr); $i = $i + 2, $j++) {
                    $key1 = $qcreate_arr[$i];
                    if (is_array($qcreate_arr[$i + 1])) {
                            $key2 = $qcreate_arr[$i + 1];
                    } else {
                            $key2 = array();
                    }
                    $return_data[$j] = array(0 => $key1, 1 => $key2);
            }
            $form_data['form'] = $return_data;
            $form_data['data'] = $fieldName_array;
            $log->debug("Exiting QuickCreate method ..." . print_r($form_data, true));
            return $form_data;
    }

    function updateRelations( $destination_module, $destination_id, $entityid ) 
    {
      require_once('include/database/PearDatabase.php');
      @include_once('user_privileges/default_module_view.php');

      global $singlepane_view, $currentModule;
      $idlist            = vtlib_purify($_REQUEST['idstring']);
      $destinationModule = vtlib_purify( $destination_module );
      $parenttab         = getParentTab();

      $forCRMRecord = vtlib_purify( $destination_id );
      $mode = $_REQUEST['mode'];

      $focus = CRMEntity::getInstance("Cashflow4You");

      if($mode == 'delete') {
        // Split the string of ids
        if(empty($_REQUEST['idstring'])) {
          $currentModule = $destinationModule;
          $destinationModule = 'Cashflow4You';
        }
            $ids = explode (";",$idlist);
        if(!empty($ids)) {
                    $focus->delete_related_module($currentModule, $forCRMRecord, $destinationModule, $ids);
        }
      } else {
        if(!empty($_REQUEST['idstring'])) {
            // Split the string of ids
            $ids = explode (";",trim($idlist,";"));
        } else if(!empty( $entityid )){
            $ids = $entityid;
        }

        if(!empty($ids)) 
        {
            $focus->save_related_module("Cashflow4You", $forCRMRecord, $destinationModule, $ids);
        }
      }
    }

    function SavePaymentFromRelation() 
    {
        $_REQUEST['ajxaction'] = 'DETAILVIEW';
        if( !isset($_REQUEST['sourcemodule']) || $_REQUEST['sourcemodule'] == '' )
        {
          header("Location: ".$_SERVER['HTTP_REFERER']);
          exit; 
        }
        $utils = new Cashflow4You_Utils_Model();
        $module = $_REQUEST['sourcemodule'];
        $entity_type_lower = $utils->Entity_table[$module]["entity"];
        $entity_table = $utils->Entity_table[$module]["table"];
        $entity_status = $utils->Entity_table[$module]["status"];
        $entity_status_value = $utils->Entity_table[$module]["status_value"];
        $entity_status_value_back = $utils->Entity_table[$module]["status_value_back"];
        $relatedto_column = $utils->Entity_table[$module]["relatedto"];
        $total_fld = $utils->Entity_table[$module]["total_fld"];
        $focus = CRMEntity::getInstance($module);
        $entity_table_id = $focus->table_index;
        
        $idstring = vtlib_purify($_REQUEST['idstring']);
        $idstring = trim($idstring, ';');
        $idlist = explode(';', $idstring);

        foreach($idlist as $invid)
        {
          $paid = 0;

          $slect = "SELECT p_open_amount, p_paid_amount, total FROM ".$entity_table." WHERE ".$entity_table_id."=?";
                $result1 = $this->db->pquery($slect, Array($invid) );
          $p_open_amount = $this->db->query_result($result1,0,'p_open_amount');
          $p_paid_amount = $this->db->query_result($result1,0,'p_paid_amount');
          $to_pay = $this->db->query_result($result1,0,'total');
          
          $query = "SELECT cashflow4youid, partial_amount AS amount FROM its4you_cashflow4you_associatedto
                      INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                    WHERE vtiger_crmentity.deleted=0
                    AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                      AND its4you_cashflow4you_associatedto.cashflow4youid IN (SELECT cashflow4youid FROM its4you_cashflow4you WHERE cashflow4you_status = 'Paid' OR cashflow4you_status = 'Received')
                    GROUP BY its4you_cashflow4you_associatedto.cashflow4youid";

          $result2 = $this->db->pquery($query, array($invid ) );

          $num = $this->db->num_rows($result2);
          if( $num > 0)
          {
            $sum_paymentamount = 0;
            for( $i=0; $i<$num; $i++)
            {
              $sum_paymentamount += $this->db->query_result($result2, $i, 'amount');
            }
            $p_paid_amount = number_format($sum_paymentamount, 3, '.', '');
            $p_open_amount = number_format($to_pay-$p_paid_amount, 3, '.', '');
            if($p_open_amount<0)
            {
                $p_open_amount = 0;
            }
          }
          $upd_arr = Array($p_open_amount, $p_paid_amount);
          $upd = "UPDATE ".$entity_table." SET p_open_amount=?, p_paid_amount=?";
          if( $module=='Invoice' || $module =='ITS4YouPreInvoice')
          {
            $upd .= ", balance=?, received=?";
            $upd_arr[] = $p_open_amount;
            $upd_arr[] = $p_paid_amount;
          }
          else if( $module =='PurchaseOrder')
          {
            $upd .= ", paid=?, balance=?";
            $upd_arr[]=$p_open_amount;
            $upd_arr[]=$p_paid_amount;
          }
          $upd .= " WHERE ".$entity_table_id."=?";
          $upd_arr[] = $invid;
          $this->db->pquery($upd, $upd_arr );
                
          /*$upd = "UPDATE ".$entity_table." SET p_open_amount=?, p_paid_amount=?,balance=?, received=? WHERE ".$entity_table_id."=?";
                $this->db->pquery($upd, Array($p_open_amount, $p_paid_amount, $p_open_amount, $p_paid_amount, $invid) );*/

            if($module=='Invoice' && $p_open_amount <= 0 && $entity_status!="" && $entity_status_value!=""/* && $datecheck!='0001-01-01'*/){
                    $updateinvstat = "UPDATE ".$entity_table." SET ".$entity_status."='".$entity_status_value."' WHERE ".$entity_table_id."=".$invid;
                    $this->db->pquery($updateinvstat,Array());
            }
            else if( $module=='Invoice' && $entity_status!="" && $entity_status_value_back != "")
            {
                $inv_query = "SELECT invoicestatus
                            FROM vtiger_invoice 
                            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                            WHERE vtiger_crmentity.deleted=0
                            AND vtiger_invoice.invoiceid=?";
                $resultINV = $this->db->pquery($inv_query, Array($invid));
                $inv_status = $this->db->query_result($resultINV, 0, 'invoicestatus');
                if( $inv_status == $entity_status_value )
                {
                    $updateinvstat = "UPDATE ".$entity_table." SET ".$entity_status."='".$entity_status_value_back."' WHERE ".$entity_table_id."=".$invid;
                    $this->db->pquery($updateinvstat,Array());
                }
            }
        }
    }
    
    private function setInvoiceAsociated( $module, $id )
    {
        $so_query = "SELECT vtiger_invoice.salesorderid, its4you_cashflow4you.cashflow4youid, its4you_cashflow4you.paymentamount, its4you_cashflow4you.payamount_main
                    FROM vtiger_invoice 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                    INNER JOIN its4you_cashflow4you ON its4you_cashflow4you.relationid=vtiger_invoice.salesorderid
                    WHERE vtiger_crmentity.deleted=0
                    AND vtiger_invoice.invoiceid=?";
        $resultSO = $this->db->pquery($so_query, Array($id));
        $num = $this->db->num_rows($resultSO);
        if( $num > 0)
        {
          for( $i=0; $i<$num; $i++)
          {
            $salesorderid = $this->db->query_result($resultSO, $i, 'salesorderid');
            $cashflow4youid = $this->db->query_result($resultSO, $i, 'cashflow4youid');
            $paymentamount = $this->db->query_result($resultSO, $i, 'paymentamount');
            $query = "SELECT partial_amount 
                      FROM its4you_cashflow4you_associatedto
                      INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                      WHERE vtiger_crmentity.deleted=0
                      AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                      OR its4you_cashflow4you_associatedto.cashflow4youid=?
                      GROUP BY its4you_cashflow4you_associatedto.cashflow4youid";
            $result_rel = $this->db->pquery($query, array( $salesorderid, $cashflow4youid ) );
            $num_rel = $this->db->num_rows($result_rel);
           
            if( $num_rel == 0)
            {
              $insert = "INSERT INTO its4you_cashflow4you_associatedto ( cashflow4youid, cashflow4you_associated_id, partial_amount )VALUES (?, ?, ?)";
              $this->db->pquery( $insert, Array( $cashflow4youid, $id, $paymentamount ) );
              $insert = "INSERT INTO vtiger_crmentityrel ( crmid, module, relcrmid, relmodule )VALUES (?, ?, ?, ?)";
              $this->db->pquery( $insert, Array( $cashflow4youid, "Cashflow4You", $id, "Invoice" ) );
            }
          }
        }
        if( Vtiger_Module::getInstance("ITS4YouPreInvoice") != false )
        {
            $result = $this->db->pquery('SELECT presence FROM vtiger_tab WHERE name=?', array("ITS4YouPreInvoice"));
            $presence = $this->db->query_result($result,0,'presence');
            if( $presence == 0)
            {
                $query = "SELECT cashflow4youid, paymentamount, its4you_cashflow4you.relationid
                        FROM  its4you_cashflow4you
                        INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you.cashflow4youid 
                        INNER JOIN its4you_preinvoice ON its4you_preinvoice.preinvoiceid=its4you_cashflow4you.relationid
                        WHERE vtiger_crmentity.deleted=0
                        AND  its4you_preinvoice.rel_invoiceid=?";
                $result = $this->db->pquery($query, array( $id ) );
                $num = $this->db->num_rows($result);
            
                if( $num > 0)
                {
                    for( $i=0; $i<$num; $i++)
                    {
                      $cashflow4youid = $this->db->query_result($result, $i, 'cashflow4youid');
                      $paymentamount = $this->db->query_result($result, $i, 'paymentamount');
                      $relationid = $this->db->query_result($result, $i, 'relationid');
                      $query = "SELECT partial_amount 
                                FROM its4you_cashflow4you_associatedto
                                INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                                WHERE vtiger_crmentity.deleted=0
                                AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                                AND its4you_cashflow4you_associatedto.cashflow4youid=?";
                      $result_rel = $this->db->pquery($query, array( $id, $cashflow4youid ) );
                      $num_rel = $this->db->num_rows($result_rel);
                      if( $num_rel == 0)
                      {
                        $insert = "INSERT INTO its4you_cashflow4you_associatedto ( cashflow4youid, cashflow4you_associated_id, partial_amount )VALUES (?, ?, ?)";
                        $this->db->pquery( $insert, Array( $cashflow4youid, $id, $paymentamount ) );
                        $insert = "INSERT INTO vtiger_crmentityrel ( crmid, module, relcrmid, relmodule )VALUES (?, ?, ?, ?)";
                        $this->db->pquery( $insert, Array( $cashflow4youid, "Cashflow4You", $id, "Invoice" ) );
                      }
                    } 
                }
            }
        }
    }
    
    function updateRelationsField( $cash_id )
    {

       $query = "SELECT its4you_cashflow4you.relationid, vtiger_crmentity.setype FROM its4you_cashflow4you "
                . "INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you.relationid "
                . "WHERE cashflow4youid=? AND relationid != 0";
        $result = $this->db->pquery($query,Array($cash_id));
        $i=0;
        while( $row =$this->db->query_result($result, $i, "relationid"))
        {
          $Rel[$row]=$this->db->query_result($result, $i++, "setype");
        }
                        
        $query = "SELECT its4you_cashflow4you_associatedto.cashflow4you_associated_id,vtiger_crmentity.setype FROM its4you_cashflow4you_associatedto "
                . "INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4you_associated_id "
                . "WHERE cashflow4youid=?";
        $result = $this->db->pquery($query,Array($cash_id));
        $i=0;
        while( $row =$this->db->query_result($result, $i, "cashflow4you_associated_id"))
        {
          $Rel[$row]=$this->db->query_result($result, $i++, "setype");
        }
        $relations="";
        
        foreach($Rel AS $id=>$module)
        {
          if($module == "Invoice")
          {
            $rel_table = "vtiger_invoice";
            $rel_no = "invoice_no";
            $rel_id = "invoiceid";
          }
          else if($module == "PurchaseOrder")
          {
            $rel_table = "vtiger_purchaseorder";
            $rel_no = "purchaseorder_no";
            $rel_id = "purchaseorderid";
          }
          else if($module == "SalesOrder")
          {
            $rel_table = "vtiger_salesorder";
            $rel_no = "salesorder_no";
            $rel_id = "salesorderid";
          }
          else if($module == "Potentials")
          {
            $rel_table = "vtiger_potential";
            $rel_no = "potential_no";
            $rel_id = "potentialid";
          }
          else if($module == "ITS4YouPreInvoice")
          {
            $rel_table = "its4you_preinvoice";
            $rel_no = "preinvoice_no";
            $rel_id = "preinvoiceid";
          }
          else if($module == "CreditNotes4You")
          {
            $rel_table = "vtiger_creditnotes4you";
            $rel_no = "creditnotes4you_no";
            $rel_id = "creditnotes4you_id";
          }
          $query = "SELECT ".$rel_no." AS no FROM ".$rel_table. " WHERE ".$rel_id."=?";
          $result = $this->db->pquery($query,Array($id));
          
          if(strlen($relations)!=0)
          {
            $relations.=" ";
          }
          $relations .= $this->db->query_result($result, 0, "no");
        }
        $update_query = "update  its4you_cashflow4you set relations=? WHERE cashflow4youid 	=?";
        $this->db->pquery($update_query,Array($relations, $cash_id));

    }
}