<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Cashflow4You_Cashflow4YouUtils_Action {
    var $db, $log; // Used in class functions of CRMEntity
    
    function __construct() {
        $this->column_fields = getColumnFields('Cashflow4You');
        $this->db = PearDatabase::getInstance();
        $this->log = LoggerManager::getLogger('Cashflow4You');;
    }
    
    function updateSavedRelation($module,$id,$cashflow4youId=0)
    {
        $entity_type_lower = strtolower($module);
        switch($entity_type_lower){
        case 'invoice':
            $entity_status = "invoicestatus";
            $entity_status_value = "Paid";
            $entity_status_value_back = "Created";
            $relatedto_column = 'accountid';
            break;
        case 'purchaseorder':
            $entity_status = "postatus";
            $entity_status_value = "";
            $entity_status_value_back = "";
            $relatedto_column = 'vendorid';
            break;
        case 'salesorder':
            $entity_status = "sostatus";
            $entity_status_value = "";
            $entity_status_value_back = "";
            $relatedto_column = 'accountid';
            break;
        default:
            if($cashflow4youId>0){
                    $res = $this->db->pquery("SELECT currency_id FROM its4you_cashflow4you WHERE cashflow4youid=".$cashflow4youId." AND currency_id IS NOT NULL AND currency_id != ''",Array());
                    if($this->db->num_rows($res)==0){
                            $current_user = Users_Record_Model::getCurrentUserModel();
                            $this->db->pquery("UPDATE its4you_cashflow4you SET currency_id='".$current_user->currency_id."' WHERE cashflow4youid=".$cashflow4youId,Array());
                    }
            }
            return null;
            break;
        }
  
        // get entity data
        $relatedto_row = array();
        $get_fld = $entity_type_lower.'_no';
        $sql = "SELECT $get_fld, currency_id, $relatedto_column, total
                FROM vtiger_".$entity_type_lower."
                INNER JOIN vtiger_crmentity ON vtiger_".$entity_type_lower.".".$entity_type_lower."id=vtiger_crmentity.crmid 
                WHERE vtiger_crmentity.deleted=0
                AND ".$entity_type_lower."id=".$id;

        $res = $this->db->pquery($sql,Array());
        if($this->db->num_rows($res)>0){
                $relatedto_row = $this->db->fetchByAssoc($res);
        }

        // update cashflow data - relation_no & currency_id
        if($cashflow4youId>0){
                if(!empty($relatedto_row)){
                        $row = $this->db->fetchByAssoc($res);
                        $this->db->pquery("UPDATE its4you_cashflow4you SET relation_no='".$relatedto_row[$get_fld]."', cashflow4you_associated_no='".$relatedto_row[$get_fld]."', currency_id='".$relatedto_row['currency_id']."' WHERE cashflow4youid=".$cashflow4youId,Array());
                        $subsql = "SELECT relatedto FROM its4you_cashflow4you WHERE cashflow4youid=".$cashflow4youId;
                        $subres = $this->db->pquery($subsql,Array());
                        $subrow = $this->db->fetchByAssoc($subres);
                        if($subrow['relatedto']=='' || $subrow['relatedto']==0){
                                $this->db->pquery("UPDATE its4you_cashflow4you SET relatedto='".$relatedto_row[$relatedto_column]."' WHERE cashflow4youid=".$cashflow4youId,Array());
                        }
                }
        }
        // update relation module data - open and paid amount; if whole sum (total) is paid, then update status to Paid too (invoice only)
        $paid = 0;
        $to_pay = $relatedto_row['total'];
        $total = $relatedto_row['total'];
        if( $entity_type_lower == "invoice" )
        {
          $so_query = "SELECT vtiger_invoice.salesorderid, its4you_cashflow4you.cashflow4youid, its4you_cashflow4you.paymentamount
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
          }
        }
        else if( $entity_type_lower == "salesorder" && $cashflow4youId != 0 && $cashflow4youId != "" )
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
                $entity_type_lower = 'invoice';
                $entity_status = "invoicestatus";
                $entity_status_value = "Paid";
                $entity_status_value_back = "Created";
            }
        }
        if( $entity_type_lower == "invoice" )
        {
              $query = "SELECT cashflow4youid, partial_amount AS amount FROM its4you_cashflow4you_associatedto
                      INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4youid 
                    WHERE vtiger_crmentity.deleted=0
                    AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?
                      AND its4you_cashflow4you_associatedto.cashflow4youid IN (SELECT cashflow4youid FROM its4you_cashflow4you WHERE cashflow4you_status = 'Paid' OR cashflow4you_status = 'Received')
                    GROUP BY its4you_cashflow4you_associatedto.cashflow4youid";
        }
        else
        {
          $query = "SELECT paymentamount AS amount
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
            $sum_paymentamount += $this->db->query_result($result2, $i, 'amount');
          }
          $paid += number_format($sum_paymentamount, 3, '.', '');
          $to_pay = number_format($to_pay-$paid, 3, '.', '');
          if($to_pay<0)
          {
              $to_pay = 0;
          }
        }
        $upd = "UPDATE vtiger_".$entity_type_lower." SET p_open_amount=?, p_paid_amount=?";
        $upd_arr = Array($to_pay,$paid);
        if( $entity_type_lower =='invoice' || $entity_type_lower =='its4youpreinvoice' )
        {
          $upd .= ", received=?, balance=?";
          $upd_arr[]=$paid;
          $upd_arr[]=$to_pay;
        }
        if( $entity_type_lower =='purchaseorder')
        {
          $upd .= ", paid=?, balance=?";
          $upd_arr[]=$paid;
          $upd_arr[]=$to_pay;
        }
        $upd .= " WHERE ".$entity_type_lower."id=?";
        $upd_arr[]=$id;
        $this->db->pquery($upd,$upd_arr);
        if( $cashflow4youId != 0 )
        {
            if($entity_type_lower=='invoice' && $paid>=$total && $to_pay == 0 && $entity_status!="" && $entity_status_value!=""/* && $datecheck!='0001-01-01'*/){
                    $updateinvstat = "UPDATE vtiger_".$entity_type_lower." SET ".$entity_status."='".$entity_status_value."' WHERE ".$entity_type_lower."id=".$id;
                    $this->db->pquery($updateinvstat,Array());
            }
            else if( $entity_type_lower=='invoice' && $entity_status!="" && $entity_status_value_back != "")
            {
                $inv_query = "SELECT invoicestatus
                            FROM vtiger_invoice 
                            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                            WHERE vtiger_crmentity.deleted=0
                            AND vtiger_invoice.invoiceid=?";
                $resultINV = $this->db->pquery($inv_query, Array($id));
                $inv_status = $this->db->query_result($resultINV, 0, 'invoicestatus');
                if( $inv_status == $entity_status_value )
                {
                    $updateinvstat = "UPDATE vtiger_".$entity_type_lower." SET ".$entity_status."='".$entity_status_value_back."' WHERE ".$entity_type_lower."id=".$id;
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
        $entity_type_lower = strtolower($_REQUEST['sourcemodule']);
        switch($entity_type_lower){
          case 'invoice':
            $entity_status = "invoicestatus";
            $entity_status_value = "Paid";
            $entity_status_value_back = "Created";
            $relatedto_column = 'accountid';
            break;
        case 'purchaseorder':
            $entity_status = "postatus";
            $entity_status_value = "";
            $entity_status_value_back = "";
            $relatedto_column = 'vendorid';
            break;
        case 'salesorder':
            $entity_status = "sostatus";
            $entity_status_value = "";
            $entity_status_value_back = "";
            $relatedto_column = 'accountid';
            break;
        }
        $idstring = vtlib_purify($_REQUEST['idstring']);
        $idstring = trim($idstring, ';');
        $idlist = explode(';', $idstring);

        foreach($idlist as $invid)
        {
          $paid = 0;
          $slect = "SELECT p_open_amount, p_paid_amount, total FROM vtiger_".$entity_type_lower." WHERE ".$entity_type_lower."id=?";
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

          $upd = "UPDATE vtiger_".$entity_type_lower." SET p_open_amount=?, p_paid_amount=?";
          
          $upd_arr = Array($p_open_amount,$p_paid_amount);
          if( $entity_type_lower =='invoice' || $entity_type_lower =='its4youpreinvoice')
          {
            $upd .= ", received=?, balance=?";
            $upd_arr[]=$p_paid_amount;
            $upd_arr[]=$p_open_amount;
          }
          if( $entity_type_lower =='purchaseorder')
          {
            $upd .= ", paid=?, balance=?";
            $upd_arr[]=$p_paid_amount;
            $upd_arr[]=$p_open_amount;
          }
          $upd .= "WHERE ".$entity_type_lower."id=?";
          $upd_arr[]=$invid;
          
            $this->db->pquery($upd, Array($p_open_amount, $p_paid_amount, $invid) );

            if($entity_type_lower=='invoice' && $p_open_amount <= 0 && $entity_status!="" && $entity_status_value!=""/* && $datecheck!='0001-01-01'*/){
                    $updateinvstat = "UPDATE vtiger_".$entity_type_lower." SET ".$entity_status."='".$entity_status_value."' WHERE ".$entity_type_lower."id=".$invid;
                    $this->db->pquery($updateinvstat,Array());
            }
            else if( $entity_type_lower=='invoice' && $entity_status!="" && $entity_status_value_back != "")
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
                    $updateinvstat = "UPDATE vtiger_".$entity_type_lower." SET ".$entity_status."='".$entity_status_value_back."' WHERE ".$entity_type_lower."id=".$invid;
                    $this->db->pquery($updateinvstat,Array());
                }
            }
        }
    }
}