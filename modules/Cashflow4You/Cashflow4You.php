<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
error_reporting(0);
include_once 'modules/Vtiger/CRMEntity.php';
include_once('modules/Cashflow4You/actions/Cashflow4YouRelation.php');

class Cashflow4You extends CRMEntity {
    private $version_type;
    private $license_key;
    private $version_no;
    
    var $db, $log; // Used in class functions of CRMEntity
  
    var $table_name = 'its4you_cashflow4you';
    var $table_index= 'cashflow4youid';
    var $column_fields = Array();
  
    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = Array('its4you_cashflow4youcf', 'cashflow4youid');

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    var $tab_name = Array('vtiger_crmentity', 'its4you_cashflow4you', 'its4you_cashflow4youcf');

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    var $tab_name_index = Array(
        'vtiger_crmentity' => 'crmid',
        'its4you_cashflow4you' => 'cashflow4youid',
        'its4you_cashflow4youcf'=>'cashflow4youid');

    /**
     * Mandatory for Listing (Related listview)
     */
    var $list_fields = Array (
	/* Format: Field Label => Array(tablename, columnname) */
	// tablename should not have prefix 'vtiger_'
        'Cashflow4You No'=> Array('cashflow' => 'cashflow4you_no'),
        'Cashflow4You Name'=> Array('cashflow' => 'cashflow4youname'),
        'Relation'=> Array('cashflow' => 'relationid'),
        'Paid Amount'=> Array('cashflow' => 'paymentamount'),
        'Due Date'=> Array('cashflow' => 'due_date'),
        'Payment Date'=> Array('cashflow' => 'paymentdate'),
        'Payment Status'=> Array('cashflow' => 'cashflow4you_status'),
        'Payment Method'=> Array('cashflow' => 'cashflow4you_paymethod'),
		//'Assigned To' => Array('crmentity','smownerid')
	);
  
  var $list_fields_name = Array(
	/* Format: Field Label => fieldname */
	'Cashflow4You No'=> 'cashflow4you_no',
        'Cashflow4You Name'=> 'cashflow4youname',
        'Relation'=> 'relationid',
        'Paid Amount'=> 'paymentamount',
        'Due Date'=> 'due_date',
        'PaymentDate'=> 'paymentdate',
        'Payment Status'=> 'cashflow4you_status',
        'Payment Method'=> 'cashflow4you_paymethod',
		//'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view
    var $list_link_field = 'cashflow4youname';

	// For Popup listview and UI type support
    var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
        'Cashflow4You No' => Array('cashflow4you', 'cashflow4you_no'),
	'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);

    var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
	'Cashflow4You Name'=> 'cashflow4youname',
        'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
    var $popup_fields = Array('cashflow4youname');

	// For Alphabetical search
    var $def_basicsearch_col = 'cashflow4youname';

	// Column value to use on detail view record text display
    var $def_detailview_recname = 'cashflow4youname';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
    var $mandatory_fields = Array('createdtime', 'modifiedtime', 'cashflow4youname');

    var $default_order_by = 'paymentdate';
    var $default_sort_order='DESC';

    function __construct() {
        $this->column_fields = getColumnFields('Cashflow4You');
        $this->db = PearDatabase::getInstance();
        $this->log = LoggerManager::getLogger('Cashflow4You');;
    }
  
    /*static function getListFields() {
        $cashflow = new Cashflow4You;
        return $cashflow->list_fields_name;
    }*/
    //Getters and Setters
    public function GetVersionType() {
        return $this->version_type;
    }

    public function GetLicenseKey() {
        return $this->license_key;
    }
    
    //PRIVATE METHODS SECTION
    private function setLicenseInfo() {

        $this->version_no = Cashflow4You_Version_Helper::$version;

        $sql = "SELECT version_type, license_key FROM its4you_cashflow4you_license";
        $result = $this->db->pquery($sql,Array());
        if ($this->db->num_rows($result) > 0) {
            $this->version_type = $this->db->query_result($result, 0, "version_type");
            $this->license_key = $this->db->query_result($result, 0, "license_key");
        } else {
            $this->version_type = "";
            $this->license_key = "";
        }
    }
    
    function save_module($module) {
        $this->db = PearDatabase::getInstance();

        $this->column_fields['paymentamount']=str_replace(' ','',$this->column_fields['paymentamount']);
        $query = "SELECT uitype FROM vtiger_field WHERE `columnname` LIKE 'paymentamount' AND `tablename` LIKE 'its4you_cashflow4you'";
        $res = $this->db->pquery($query, Array());
        $uitype = $this->db->query_result($res, 0, 'uitype');
        if($uitype == "72" )
        {
          $paymentamount = CurrencyField::convertToDBFormat($this->column_fields['paymentamount'], null, true);
        }
        else
        {
          $paymentamount = $this->column_fields['paymentamount'];
        }

        $without_tax = abs( $paymentamount) - abs($this->column_fields['vat_amount']);
        $payamount_main = $paymentamount;
        if( $this->column_fields["currency_id"] != CurrencyField::getDBCurrencyId() )
        {
            $currencyRateAndSymbol = getCurrencySymbolandCRate($this->column_fields["currency_id"]);
            $payamount_main = CurrencyField::convertToDollar($payamount_main, $currencyRateAndSymbol["rate"]);
        }

        if ($this->column_fields["paymentdate"] != NULL && date_create( DateTimeField::convertToDBFormat($this->column_fields["paymentdate"])) <= date_create("now")) {
            $status = ( $this->column_fields['cashflow4you_paytype'] == 'Incoming') ? 'Received' : 'Paid';
        }else if ($this->column_fields["paymentdate"] != NULL && date_create( DateTimeField::convertToDBFormat($this->column_fields["paymentdate"])) > date_create("now")) {
            //$status = ( $this->column_fields['cashflow4you_paytype'] == 'Incoming') ? 'Waiting!' : 'Waiting!';
            $status = ( $this->column_fields['cashflow4you_paytype'] == 'Incoming') ? 'Received' : 'Paid';
        } else if ($this->column_fields["due_date"] != NULL && date_create( DateTimeField::convertToDBFormat($this->column_fields["due_date"])) >= date_create("now")) {
            $status = ( $this->column_fields['cashflow4you_paytype'] == 'Incoming') ? 'Waiting...' : 'Waiting.:.:';
        } else if ($this->column_fields["due_date"] != NULL && date_create( DateTimeField::convertToDBFormat($this->column_fields["due_date"])) < date_create("now")) {
            $status = ( $this->column_fields['cashflow4you_paytype'] == 'Incoming') ? 'Pending' : 'Pending';
        } else {
            $status = 'Created';
        }

        $without_tax = number_format($without_tax, 2, '.', '');
        $upd_total = "UPDATE its4you_cashflow4you 
                      SET total_without_vat=?,
                      cashflow4you_status=?,
                      payamount_main=?
                      WHERE cashflow4youid=?";
        $this->db->pquery($upd_total, Array($without_tax,$status,$payamount_main,$this->id ));
        $cashflow_utils = new Cashflow4YouRelation();
        // set relation data
        if(isset($this->column_fields['relationid']) && $this->column_fields['relationid']!='' && $this->column_fields['relationid']!=0){
            $entity_type = getSalesEntityType($this->column_fields['relationid']);
            
            $cashflow_utils->updateRelations( $entity_type, $this->id , $this->column_fields['relationid'] );

            $select = "SELECT COUNT(*) AS count FROM its4you_cashflow4you_associatedto 
                        INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4you_associated_id 
                        WHERE vtiger_crmentity.deleted=0 
                        AND its4you_cashflow4you_associatedto.cashflow4youid=?";
            $select_res = $this->db->pquery( $select, Array( $this->id ) );
            $num = $this->db->query_result($select_res, 0, 'count');
                
            if( $entity_type != "SalesOrder" && $entity_type != "ITS4YouPreInvoice")
            {
                if( $num == 0 )
                {
                    $insert = "INSERT INTO its4you_cashflow4you_associatedto ( cashflow4youid, cashflow4you_associated_id, partial_amount )VALUES (?, ?, ?)";
                    $this->db->pquery( $insert, Array($this->id, $this->column_fields['relationid'], $paymentamount ) );
                }
                else
                {
                    $insert = "UPDATE its4you_cashflow4you_associatedto SET partial_amount = ?, cashflow4you_associated_id = ? 
                                WHERE its4you_cashflow4you_associatedto.cashflow4youid=?";
                    $this->db->pquery( $insert, Array( $paymentamount, $this->column_fields['relationid'], $this->id,  ) );
                }
            }
            else {
                if( $num > 0 )
                {
                    $insert = "UPDATE its4you_cashflow4you_associatedto SET partial_amount = ? 
                                WHERE its4you_cashflow4you_associatedto.cashflow4youid=?";
                    $this->db->pquery( $insert, Array( $paymentamount, $this->id,  ) );

                    $select = "SELECT cashflow4you_associated_id  
                                FROM its4you_cashflow4you_associatedto 
                                INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you_associatedto.cashflow4you_associated_id 
                                WHERE vtiger_crmentity.deleted=0 
                                AND its4you_cashflow4you_associatedto.cashflow4youid=?";
                    $select_res = $this->db->pquery( $select, Array( $this->id ) );
                    $num = $this->db->num_rows($select_res);
    
                    for($i=0;$i<$num;$i++)
                    {
                        
                        $associated_id = $this->db->query_result($select_res, $i, 'cashflow4you_associated_id');
                        $asoc_entity_type = getSalesEntityType($associated_id);
                        
                        $cashflow_utils->updateSavedRelation($asoc_entity_type,$associated_id,$this->id);
                    }
                }
            }
            $cashflow_utils->updateSavedRelation($entity_type,$this->column_fields['relationid'],$this->id);
        }
        else if( isset($_REQUEST['idstring']) && $_REQUEST['idstring'] != '' /*&& strpos($_REQUEST['idstring'],';') !== false*/ ){
            $Idlist = explode(';', $_REQUEST["idstring"]);
 
            $cashflow_utils->updateRelations( $_REQUEST["sourcemodule"],$_REQUEST["currentid"], $this->column_fields['relationid']  );
          
            foreach( $Idlist AS $invoiceId )
            {
                if( $_REQUEST['record'] != "" )
                {
                  $insert = "UPDATE its4you_cashflow4you_associatedto SET partial_amount = ? 
                              WHERE its4you_cashflow4you_associatedto.cashflow4youid=?
                              AND its4you_cashflow4you_associatedto.cashflow4you_associated_id=?";
                  $this->db->pquery( $insert, Array( $_REQUEST["payment_".$invoiceId], $_REQUEST["record"], $invoiceId ) );
                }
                else
                {
                  $insert = "INSERT INTO its4you_cashflow4you_associatedto ( cashflow4youid, cashflow4you_associated_id, partial_amount )VALUES (?, ?, ?)";
                  $this->db->pquery( $insert, Array($_REQUEST["currentid"], $invoiceId, $_REQUEST["payment_".$invoiceId] ) );
                }
            }

            $cashflow_utils->SavePaymentFromRelation();

        }  
        $cashflow_utils->updateRelationsField($this->id);

        if( $_REQUEST["return_module"] == "" )
        {
            $_REQUEST["return_module"] = $_REQUEST["module"];
        } 
        if( $_REQUEST["return_id"] == "" )
        {
            $_REQUEST["return_id"] = $_REQUEST["currentid"];
        }
      }
      
    /** 
       * Handle saving related module information.
       * NOTE: This function has been added to CRMEntity (base class).
       * You can override the behavior by re-defining it here.
       */
    function save_related_module($module, $crmid, $with_module, $with_crmid) {
         if (!in_array($with_module, array(''))) {
             parent::save_related_module($module, $crmid, $with_module, $with_crmid);
             return;
         }
        /** 
         * $_REQUEST['action']=='Save' when choosing ADD from Related list.
         * Do nothing on the payment's entity when creating a related new child using ADD in relatedlist
         * by doing nothing we do not insert any line in the crmentity's table when
         * we are relating a module to this module
         */
        if ($_REQUEST['action'] != 'updateRelations') {
            return;
        }
        $_REQUEST['submode'] = 'no_html_conversion';
        //update the child elements' column value for uitype10
        $destinationModule = vtlib_purify($_REQUEST['destination_module']);
        if (!is_array($with_crmid)) $with_crmid = Array($with_crmid);
        foreach($with_crmid as $relcrmid) {
            $child = CRMEntity::getInstance($destinationModule);
            $child->retrieve_entity_info($relcrmid, $destinationModule);
            $child->mode='edit';
            $child->column_fields['cashflow4youid']=$crmid;
            $child->save($destinationModule,$relcrmid);
        }
    }
    
    function get_invoice($id, $cur_tab_id, $rel_tab_id, $actions=false)
    {
        global $log, $singlepane_view,$currentModule,$current_user;
        $log->debug("Entering get_invoice(".$id.") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);		
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if($singlepane_view == 'true')
            $returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
        else
            $returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

        $button = '';

        if($actions) {
            if(is_string($actions)) 
                $actions = explode(',', strtoupper($actions));
            if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
                $button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
            }
            if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
                $button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
                           " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                           " value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
            }
        } 

      $query = "SELECT vtiger_crmentity.*, vtiger_invoice.*, its4you_cashflow4you.cashflow4youname, 
                CASE WHEN (vtiger_users.user_name not like '') 
                THEN vtiger_users.user_name 
                ELSE vtiger_groups.groupname 
                END
                AS user_name FROM vtiger_invoice 
                LEFT JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			          LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
			          LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid=vtiger_crmentity.crmid 
                INNER JOIN its4you_cashflow4you ON vtiger_crmentityrel.crmid=its4you_cashflow4you.cashflow4youid
                LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
                LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid 
                WHERE vtiger_crmentity.deleted=0 AND its4you_cashflow4you.cashflow4youid=".$id;
        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 

        if($return_value == null) $return_value = Array();
            $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_invoice method ...");		
        return $return_value;

    }

    // ITS4YOU-CR SlOl 7/26/2011 
    function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) 
    {
        global $log, $singlepane_view,$currentModule,$current_user;
        $log->debug("Entering get_purchase_orders(".$id.") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);		
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if($singlepane_view == 'true')
            $returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
        else
            $returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

        $button = '';

        if($actions) {
            if(is_string($actions)) 
                $actions = explode(',', strtoupper($actions));
            if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
                $button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
            }
            if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
                $button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
                           " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                           " value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
            }
        } 

        $query = "select vtiger_crmentity.*, vtiger_purchaseorder.*, its4you_cashflow4you.cashflow4youname, 
                  case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
                  from vtiger_purchaseorder 
                  LEFT JOIN vtiger_purchaseordercf ON vtiger_purchaseordercf.purchaseorderid = vtiger_invoice.invoiceid
                  inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_purchaseorder.purchaseorderid 
                  inner join vtiger_crmentityrel on vtiger_crmentityrel.crmid=vtiger_crmentity.crmid 
                  inner join its4you_cashflow4you on vtiger_crmentityrel.relcrmid=its4you_cashflow4you.cashflow4youid 
                  left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
                  left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid 
                  where vtiger_crmentity.deleted=0 and its4you_cashflow4you.cashflow4youid=".$id;

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 

        if($return_value == null) $return_value = Array();
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_purchase_orders method ...");		
        return $return_value;
    }

    function get_sales_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) 
    {
            global $log, $singlepane_view,$currentModule,$current_user;
            $log->debug("Entering get_sales_orders(".$id.") method ...");
            $this_module = $currentModule;

            $related_module = vtlib_getModuleNameById($rel_tab_id);
            require_once("modules/$related_module/$related_module.php");
            $other = new $related_module();
            vtlib_setup_modulevars($related_module, $other);		
            $singular_modname = vtlib_toSingular($related_module);

            $parenttab = getParentTab();

            if($singlepane_view == 'true')
                $returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
            else
                $returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

            $button = '';

            if($actions) {
                if(is_string($actions)) 
                    $actions = explode(',', strtoupper($actions));
                if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
                    $button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
                }
                if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
                    $button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
                               " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                               " value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
                }
            } 

            $query = "select vtiger_crmentity.*, vtiger_salesorder.*, vtiger_cashflow4you.cashflow4youname, 
                      case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
                      from vtiger_salesorder 
                      LEFT JOIN vtiger_salesordercf ON vtiger_salesordercf.salesorderid = vtiger_invoice.invoiceid
                      inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_salesorder.salesorderid 
                      inner join vtiger_crmentityrel on vtiger_crmentityrel.crmid=vtiger_crmentity.crmid 
                      inner join vtiger_cashflow4you on vtiger_crmentityrel.relcrmid=vtiger_cashflow4you.cashflow4youid 
                      left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
                      left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid 
                      where vtiger_crmentity.deleted=0 and vtiger_cashflow4you.cashflow4youid=".$id;

            $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 

            if($return_value == null) 
                $return_value = Array();
            $return_value['CUSTOM_BUTTON'] = $button;

            $log->debug("Exiting get_sales_orders method ...");		
            return $return_value;
        }
	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
    function vtlib_handler($moduleName, $eventType) {
        $Cashflow4You = new Cashflow4You_Module_Model();
    
 	if($eventType == 'module.postinstall') {
            // TODO Handle actions after this module is installed.
// add block, custom fields and widgets into module Invoice
            $Cashflow4YouModule  = Vtiger_Module::getInstance('Cashflow4You');
            Vtiger_Access::setDefaultSharing($Cashflow4YouModule);

            $this->addCashflowInformation();
            $Cashflow4You->addEventHandler();
            
            // set numbering for Cashflow4You
            $this->setNumbering();
            
            //set picklist dependency 
            $this->setPicklistDependency();
            
            $this->db->pquery("UPDATE vtiger_cashflow4you_paytype SET `presence`=0 WHERE cashflow4you_paytype='Incoming' OR cashflow4you_paytype='Outgoing'",Array());
            
        } else if($eventType == 'module.disabled') {
            $Cashflow4You->DeleteAllRefLinks();
            $Cashflow4You->disableEventHandler();
        } else if($eventType == 'module.enabled') {
            $Cashflow4You->actualizeLinks();
            $Cashflow4You->enableEventHandler();
	} else if($eventType == 'module.preuninstall') {
            $Cashflow4You->DeleteAllRefLinks();
            $Cashflow4You->delEventHandler();
            $this->DeleteDB();
	} else if($eventType == 'module.postupdate') {
            // cast kodu z post install
            // zopakuje sa este aj pri post update ak by sa to nahodov nevykonalo pri instalacii
            $Cashflow4YouModule  = Vtiger_Module::getInstance('Cashflow4You');
            Vtiger_Access::setDefaultSharing($Cashflow4YouModule);

            $this->addCashflowInformation();
            $Cashflow4You->addEventHandler();
            
            // set numbering for Cashflow4You
            $this->setNumbering();
            
            //set picklist dependency 
            $this->setPicklistDependency();

            $this->db->pquery("UPDATE vtiger_cashflow4you_paytype SET `presence`=0 WHERE cashflow4you_paytype='Incoming' OR cashflow4you_paytype='Outgoing'",Array());
            // koniec kodu z post install

            $inv_module = Vtiger_Module::getInstance('Invoice');
            
            $sql = "SELECT version FROM vtiger_tab WHERE name = 'Cashflow4You'";
            $result = $this->db->pquery($sql,Array());
            $version = $this->db->query_result($result, 0, "version");
            $version = substr($version, strpos($version, '.')+1) * 1;

            $tabid = getTabId("Cashflow4You");

            $Field = Array( "cashflow4youname" => "Cashflow4You Name",
                            "cashflow4you_no" => "Cashflow4You No",
                            "paymentdate" => "Payment Date",
                            "cashflow4you_paymethod" => "Payment Method",
                            "paymentamount" => "Paid Amount",
                            "relationid" => "Relation",
                            "transactionid" => "Transaction ID",
                            "due_date" => "Due Date", 
                            "currency_id" => "Currency",
                            "cashflow4you_paytype" => "Payment Type",
                            "cashflow4you_category" => "Payment Category",
                            "relatedto" => "Related To",
                            "cashflow4you_cash" => "Payment Mode",
                            "cashflow4you_subcategory" => "Payment Subcategory",
                            "cashflow4you_status" => "Payment Status",
                            "accountingdate" => "Accounting Date",
                            "vat_amount" => "VAT",
                            "total_without_vat" => "Price without VAT",
                            "tax_expense" => "Tax Component",
                            "cashflow4you_associated_no" => "Associated No",
                            "createdtime" => "Created Time",
                            "modifiedtime" => "Modified Time",
                            "description" => "Description",
                            "smownerid" => "Assigned To",
                            );
            foreach($Field AS $colomnname=>$fieldlabel)
            {
                $update = "UPDATE vtiger_field SET fieldlabel=?
                            WHERE tabid=? AND vtiger_field.columnname=? AND fieldname!=?";
                $this->db->pquery($update, Array($fieldlabel,$tabid,$colomnname,$fieldlabel));
            }
            $update = "UPDATE vtiger_field SET uitype=?, fieldlabel=?, typeofdata=? 
                       WHERE vtiger_field.columnname='p_paid_amount' AND uitype=8 AND fieldname='paidamount'";
            $this->db->pquery($update, Array(72,"Paid Amount","N~O"));

            $update = "UPDATE vtiger_field SET uitype=?, fieldlabel=?, typeofdata=? 
                       WHERE vtiger_field.columnname='p_open_amount' AND uitype=8 AND fieldname='openamount'";
            $this->db->pquery($update, Array(72,"Remaining Amount","N~O"));
            
            $select = "SELECT tabid FROM vtiger_tab WHERE name='Cashflow4You'";
            $result = $this->db->pquery($select, Array());
            $tabid = $this->db->query_result($result, 0, "tabid");
            
            $update = "UPDATE vtiger_relatedlists SET actions=?
                       WHERE (label='Invoice' OR label='Purchase Order' OR label='Purchase Order') AND tabid =?";
            $this->db->pquery($update, Array("",$tabid));
            
            $update = "UPDATE vtiger_relatedlists SET actions=?
                       WHERE label='Documents' AND tabid =?";
            $this->db->pquery($update, Array("SELECT,ADD",$tabid));
            
            $select = "SELECT linkid FROM vtiger_links WHERE linktype='HEADERSCRIPT' AND linklabel='Vtiger_BaseValidator_Js' "
                    . "AND linkurl='modules/Vtiger/resources/validator/BaseValidator.js'";
            $result = $this->db->pquery($select, Array());
            if( $this->db->num_rows($result) == 0 )
            {
                $inv_module->addLink('HEADERSCRIPT','Vtiger_BaseValidator_Js','modules/Vtiger/resources/validator/BaseValidator.js');
            }
            $select = "SELECT linkid FROM vtiger_links WHERE linktype='HEADERSCRIPT' AND linklabel='Vtiger_Number_Validator_Js' "
                    . "AND linkurl='modules/Cashflow4You/resources/validator/FieldValidator.js'";
            $result = $this->db->pquery($select, Array());
            if( $this->db->num_rows($result) == 0 )
            {
                $inv_module->addLink('HEADERSCRIPT','Vtiger_Number_Validator_Js','modules/Cashflow4You/resources/validator/FieldValidator.js');
            }
            $insert = 'UPDATE vtiger_picklist_dependency  SET sourcevalue=? WHERE tabid=? AND sourcefield=? AND targetfield=? AND sourcevalue=?';
            $this->db->pquery($insert, Array("Cashflow",$tabid,"cashflow4you_cash","cashflow4you_paymethod","Cash"));
            
            if( $version <= 1.6)
            {
                $Modules = Array('SalesOrder'=>"vtiger_salesoreder", 'Potentials'=>"vtiger_potential", 'ITS4YouPreInvoice'=>"its4you_preinvoice", 
                                'CreditNotes4You'=>"vtiger_creditnotes4you");

                foreach( $Modules AS $module=>$table )
                {
                    $inv_module = Vtiger_Module::getInstance($module);
                    if( $inv_module != false )
                    {
                        $uitype = 72;
                        $cashflow_block = new Vtiger_Block();
                        $cashflow_block->label = 'Cashflow Information';
                        $inv_module->addBlock($cashflow_block);

                        $paid_amount = new Vtiger_Field();
                        $paid_amount->name = 'paidamount';
                        $paid_amount->label = 'Paid Amount';
                        $paid_amount->table = $inv_module->basetable;
                        $paid_amount->column = 'p_paid_amount';
                        $paid_amount->columntype = 'DECIMAL(25,3)';
                        $paid_amount->uitype = $uitype;
                        $paid_amount->displaytype = 3;
                        $paid_amount->typeofdata = 'N~O';
                        $cashflow_block->addField($paid_amount);

                        $open_amount = new Vtiger_Field();
                        $open_amount->name = 'openamount';
                        $open_amount->label = 'Remaining Amount';
                        $open_amount->table = $inv_module->basetable;
                        $open_amount->column = 'p_open_amount';
                        $open_amount->columntype = 'DECIMAL(25,3)';
                        $open_amount->uitype = $uitype;
                        $open_amount->displaytype = 3;
                        $open_amount->typeofdata = 'N~O';
                        $cashflow_block->addField($open_amount);

                        if( $module == "ITS4YouPreInvoice" || $module == "CreditNotes4You" || $module == "Potentials")
                        {
                            $this->db->pquery("ALTER TABLE `".$table."` ADD `p_open_amount` DECIMAL(25, 3) NOT NULL",Array());
                            $this->db->pquery("ALTER TABLE `".$table."` ADD `p_paid_amount` DECIMAL(25, 3) NOT NULL",Array());
                            $total = "total";
                            if( $module == "Potentials" )
                            {
                                $total = "amount";
                            }
                            $this->db->pquery("UPDATE ".$table." SET p_open_amount=".$total.", p_paid_amount='0.000'",Array());
                            $modulename = "Cashflow4You";
                            $linked_module = Vtiger_Module::getInstance($modulename);
                            $potentials = Vtiger_Module::getInstance($module);
                            $potentials->setRelatedList($linked_module, $modulename, array(), "get_dependents_list");
                            
                            $opp_module = Vtiger_Module::getInstance($module);
                            $opp_module->addLink('DETAILVIEWSIDEBARWIDGET','Payment','module=Cashflow4You&view=Cashflow4YouActions&record=$RECORD$','themes/images/actionGenerateInvoice.gif');
                        }
                    }
               }
            }
           
            $result = $this->db->pquery("SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'relationid' AND `tablename` = 'its4you_cashflow4you' AND `uitype` = '10'",Array());
            $fieldid = $this->db->query_result($result, 0, 'fieldid');
            $insert = "INSERT INTO `vtiger_fieldmodulerel` (`fieldid` , `module` , `relmodule` , `status` , `sequence` )
                        VALUES ( ?, 'Cashflow4You', 'Potentials', NULL , NULL ),
                               ( ?, 'Cashflow4You', 'ITS4YouPreInvoice', NULL , NULL ),
                               ( ?, 'Cashflow4You', 'CreditNotes4You', NULL , NULL )";
            $fieldid = $this->db->pquery($insert, Array($fieldid,$fieldid,$fieldid));
            
            $result = $this->db->pquery("SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'contactid' AND `tablename` = 'its4you_cashflow4you' AND `uitype` = '57'",Array());
            if( $this->db->num_rows($result) != 0 )
            {
              $fieldid = $this->db->query_result($result, 0, 'fieldid');
              $this->db->pquery("UPDATE `vtiger_field` SET uitype=? WHERE `fieldid` = ?",Array("10",$fieldid));
              $insert = "INSERT INTO `vtiger_fieldmodulerel` (`fieldid` , `module` , `relmodule` , `status` , `sequence` )
                        VALUES ( ?, 'Cashflow4You', 'Contacts', NULL , NULL )";
              $fieldid = $this->db->pquery($insert, Array($fieldid));
            }
            
	} else if($eventType == 'module.preupdate') {
            // TODO Handle actions after this module is updated.
	}
    }
    
    private function addCashflowInformation()
    {
        $Module = Array('Invoice' => "vtiger_invoice", 'SalesOrder'=>"vtiger_salesorder", 'PurchaseOrder'=>"vtiger_purchaseorder", 'Potentials'=>"vtiger_potential",
                        'ITS4YouPreInvoice'=>"its4you_preinvoice", 'CreditNotes4You'=>"vtiger_creditnotes4you");

        foreach( $Module AS $module=>$table )
        {
            $inv_module = Vtiger_Module::getInstance($module);
            if( $inv_module != false )
            {
              $uitype = 72;
              $cashflow_block = new Vtiger_Block();
              $cashflow_block->label = 'Cashflow Information';
              
              $query="SELECT * FROM `vtiger_blocks` WHERE `tabid` = ? AND `blocklabel` = 'Cashflow Information'";
              $result1 = $this->db->pquery($query, Array($inv_module->id ));
              if( $this->db->num_rows($result1) == 0 )
              {
                $inv_module->addBlock($cashflow_block);
              }

              $query="SELECT * FROM `vtiger_field` WHERE `columnname` = ? AND `tablename` = ? AND `fieldname` = ?";
              $result1 = $this->db->pquery($query, Array("p_paid_amount",$table,"paidamount" ));
              if( $this->db->num_rows($result1) == 0 )
              {
                $paid_amount = new Vtiger_Field();
                $paid_amount->name = 'paidamount';
                $paid_amount->label = 'Paid Amount';
                $paid_amount->table = $inv_module->basetable;
                $paid_amount->column = 'p_paid_amount';
                $paid_amount->columntype = 'DECIMAL(25,3)';
                $paid_amount->uitype = $uitype;
                $paid_amount->displaytype = 2;
                $paid_amount->typeofdata = 'N~O';
                $cashflow_block->addField($paid_amount);
              }

              $this->db->pquery("ALTER TABLE `".$table."` ADD `p_paid_amount` DECIMAL(25, 3) NOT NULL", Array());
              
              $result1 = $this->db->pquery($query, Array("p_open_amount",$table,"openamount" ));
              
              if( $this->db->num_rows($result1) == 0 )
              {
                $open_amount = new Vtiger_Field();
                $open_amount->name = 'openamount';
                $open_amount->label = 'Remaining Amount';
                $open_amount->table = $inv_module->basetable;
                $open_amount->column = 'p_open_amount';
                $open_amount->columntype = 'DECIMAL(25,3)';
                $open_amount->uitype = $uitype;
                $open_amount->displaytype = 2;
                $open_amount->typeofdata = 'N~O';
                $cashflow_block->addField($open_amount);
              }
                $this->db->pquery("ALTER TABLE `".$table."` ADD `p_open_amount` DECIMAL(25, 3) NOT NULL", Array());
            }
        }
        $this->db->pquery("UPDATE vtiger_invoice SET p_open_amount='0.000', p_paid_amount=total, balance='0.000', received=total WHERE invoicestatus='Paid' AND p_open_amount IS NULL AND p_paid_amount IS NULL", Array());
        $this->db->pquery("UPDATE vtiger_invoice SET p_open_amount=total, p_paid_amount='0.000',balance=total, received='0.000' WHERE invoicestatus!='Paid' AND p_open_amount IS NULL AND p_paid_amount IS NULL", Array());
        $this->db->pquery("UPDATE vtiger_purchaseorder SET p_open_amount=total, p_paid_amount='0.000',balance=total, received='0.000' WHERE postatus!='Cancelled' AND p_open_amount IS NULL AND p_paid_amount IS NULL", Array());
        $this->db->pquery("UPDATE vtiger_salesorder SET p_open_amount=total, p_paid_amount='0.000' WHERE sostatus!='Cancelled' AND p_open_amount IS NULL AND p_paid_amount IS NULL", Array());
        $this->db->pquery("UPDATE vtiger_potential SET p_open_amount=amount, p_paid_amount='0.000'");
        
        if( Vtiger_Module::getInstance("ITS4YouPreInvoice") != false )
        {
            $this->db->pquery("UPDATE its4you_preinvoice SET p_open_amount=total, p_paid_amount='0.000' WHERE postatus!='Cancelled' AND p_open_amount IS NULL AND p_paid_amount IS NULL", Array());
        }
        if( Vtiger_Module::getInstance("CreditNotes4You") != false )
        {
            $this->db->pquery("UPDATE vtiger_creditnotes4you SET p_open_amount=total, p_paid_amount='0.000' WHERE postatus!='Cancelled' AND p_open_amount IS NULL AND p_paid_amount IS NULL", Array());
        }
        
    }
    
    /*
    * Function to get the secondary query part of a report
    * @param - $module primary module name
    * @param - $secmodule secondary module name
    * returns the query string formed on fetching the related data for report for secondary module
    */
   function generateReportsSecQuery($module,$secmodule,$queryPlanner){

           $matrix = $queryPlanner->newDependencyMatrix();
           $matrix->setDependency('vtiger_crmentityCashflow4You', array('vtiger_groupsCashflow4You', 'vtiger_usersCashflow4You', 'vtiger_lastModifiedByCashflow4You'));
           $matrix->setDependency('its4you_cashflow4you', array('vtiger_crmentityCashflow4You','its4you_cashflow4youcf', 'its4you_cashflow4youCashflow4You', ' its4you_cashflow4you_associatedto'));

           if (!$queryPlanner->requireTable('its4you_cashflow4you', $matrix)) {
                   return '';
           }

           $query = $this->getRelationQuery($module,$secmodule,"its4you_cashflow4you","cashflow4youid", $queryPlanner);

           $module_low = strtolower($module);

           if ($queryPlanner->requireTable('vtiger_crmentityCashflow4You', $matrix)) {
                   $query .= " left join vtiger_crmentity as vtiger_crmentityCashflow4You on vtiger_crmentityCashflow4You.crmid=its4you_cashflow4you.cashflow4youid and vtiger_crmentityCashflow4You.deleted=0";
           }
           if ($queryPlanner->requireTable('its4you_cashflow4youcf')) {
                   $query .= " left join its4you_cashflow4youcf on its4you_cashflow4you.cashflow4youid = its4you_cashflow4youcf.cashflow4youid";
           }
           if ($queryPlanner->requireTable('its4you_cashflow4you_associatedto')) {
                   $query .= "	left join its4you_cashflow4you_associatedto as its4you_cashflow4you_associatedtoCashflow4You on its4you_cashflow4you_associatedtoCashflow4You.cashflow4youid = vtiger_crmentityCashflow4You.cashflow4youid";
           }
           if ($queryPlanner->requireTable('its4you_cashflow4youCashflow4You')) {
                   $query .= "	left join its4you_cashflow4you as its4you_cashflow4youCashflow4You on its4you_cashflow4youCashflow4You.cashflow4youid = vtiger_crmentityCashflow4You.crmid";
           }
           if ($queryPlanner->requireTable('vtiger_groupsCashflow4You')) {
			$query .= "	left join vtiger_groups as vtiger_groupsCashflow4You on vtiger_groupsCashflow4You.groupid = vtiger_crmentityCashflow4You.smownerid";
           }
           if ($queryPlanner->requireTable('vtiger_usersCashflow4You')) {
                   $query .= " left join vtiger_users as vtiger_usersCashflow4You on vtiger_usersCashflow4You.id = vtiger_crmentityCashflow4You.smownerid";
           }
           if ($queryPlanner->requireTable('vtiger_lastModifiedByCashflow4You')) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByCashflow4You on vtiger_lastModifiedByCashflow4You.id = vtiger_crmentityCashflow4You.modifiedby ";
           }
/*           if ($module == "Invoice") {
            $query .= " left join its4you_cashflow4you_associatedto as its4you_cashflow4you_associatedtoInvoice on its4you_cashflow4you_associatedtoInvoice.cashflow4you_associated_id = vtiger_invoice.invoiceid ";
            $query .= " left join its4you_cashflow4you as its4you_cashflow4youInvoice on its4you_cashflow4youInvoice.cashflow4youid = its4you_cashflow4you_associatedtoInvoice.cashflow4youid ";
           }*/
           return $query;
   }
   /*
    * Function to get the primary query part of a report
    * @param - $module primary module name
    * returns the query string formed on fetching the related data for report for secondary module
    */
   function generateReportsQuery($module,$queryPlanner){
           global $current_user;

                   $matrix = $queryPlanner->newDependencyMatrix();
                   $matrix->setDependency('its4you_cashflow4you',array('vtiger_crmentityCashflow4You','vtiger_accountCashflow4You','vtiger_leaddetailsCashflow4You','its4you_cashflow4youcf','vtiger_potentialCashflow4You'));
                   $query = "from its4you_cashflow4you
                           inner join vtiger_crmentity on vtiger_crmentity.crmid=its4you_cashflow4you.cashflow4youid";
                   if ($queryPlanner->requireTable("its4you_cashflow4youcf")){
                       $query .= " left join its4you_cashflow4youcf on its4you_cashflow4you.cashflow4youid = its4you_cashflow4youcf.cashflow4youid";
                   }
                   if ($queryPlanner->requireTable("vtiger_usersCashflow4You")){
                       $query .= " left join vtiger_users as vtiger_usersCashflow4You on vtiger_usersCashflow4You.id = vtiger_crmentity.smownerid";
                   }
                   if ($queryPlanner->requireTable("vtiger_groupsCashflow4You")){
                       $query .= " left join vtiger_groups as vtiger_groupsCashflow4You on vtiger_groupsCashflow4You.groupid = vtiger_crmentity.smownerid";
                   }
                   
                   return $query;
   }
   
   private function setNumbering(){
    // set numbering for Cashflow4You
    $query="SELECT * FROM vtiger_modentity_num WHERE semodule='Cashflow4you'";
    $result1 = $this->db->pquery($query, Array());
    if( $this->db->num_rows($result1) == 0 )
    {
      $num_id = $this->db->getUniqueId('vtiger_modentity_num');
      $ins_001 = "INSERT INTO vtiger_modentity_num (num_id,semodule,prefix,start_id,cur_id,active) VALUES ('$num_id','Cashflow4you','PAY','001','001','1');";
      $this->db->pquery($ins_001,Array());
    }
  }
  
  private function setPicklistDependency()
  {
    //set picklist dependency 
    $DependencyTab = Array( 01=> Array( "sourcefield"=>"cashflow4you_cash","targetfield"=>"cashflow4you_paymethod", "sourcevalue"=>"Cashflow","targetvalues"=>'["Cash","Other"]' ),
                            02=> Array( "sourcefield"=>"cashflow4you_cash","targetfield"=>"cashflow4you_paymethod", "sourcevalue"=>"Bank account","targetvalues"=>'["Bank Transfer","Credit card","Google Checkout","Paypal","Wire transfer"]' ),
                            03=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Income for services","targetvalues"=>'["Extensions"]' ),
                            04=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Income for products","targetvalues"=>'["Programming"]' ),
                            05=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Office cost","targetvalues"=>'["Telephone"]' ),
                            06=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Telephone","targetvalues"=>'["none"]' ),
                            07=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Salaries","targetvalues"=>'["none"]' ),
                            08=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Wages","targetvalues"=>'["none"]' ),
                            09=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Rent","targetvalues"=>'["none"]' ),
                            10=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Fuel","targetvalues"=>'["Auto"]' ),
                            11=> Array( "sourcefield"=>"cashflow4you_category","targetfield"=>"cashflow4you_subcategory", "sourcevalue"=>"Other","targetvalues"=>'[]' )
                          );
    $moduleCashflow = Vtiger_Module::getInstance('Cashflow4You');
    $id = $moduleCashflow->id;

    $insert = 'INSERT INTO vtiger_picklist_dependency (id,tabid,sourcefield,targetfield,sourcevalue,targetvalues) VALUES (?,?,?,?,?,?)';

    foreach($DependencyTab AS $key=>$Value )
    {
      $query="SELECT * FROM vtiger_picklist_dependency WHERE tabid=? AND sourcefield=? AND targetfield=? AND sourcevalue=? AND targetvalues=?";
      $result1 = $this->db->pquery($query, Array($id,$Value["sourcefield"],$Value["targetfield"],$Value["sourcevalue"],$Value["targetvalues"] ));
      if( $this->db->num_rows($result1) == 0 )
      {
        $curr_id = $this->db->getUniqueID("vtiger_picklist_dependency");
        $this->db->pquery($insert, Array($curr_id,$id,$Value["sourcefield"],$Value["targetfield"],$Value["sourcevalue"],$Value["targetvalues"]));
      }
    }
    $this->db->pquery("UPDATE vtiger_picklist_dependency_seq SET id=?" ,Array($curr_id));
  }
  
  private function DeleteDB()
  {
    $query="DELETE FROM `vtiger_fieldmodulerel` WHERE module='Cashflow4You'";
    $this->db->pquery($query, Array());
    
    $query="DELETE FROM `vtiger_modentity_num` WHERE `semodule` = 'Cashflow4you'";
    $this->db->pquery($query, Array());
    
    $query="DELETE FROM `vtiger_modtracker_relations` WHERE `targetmodule` = 'Cashflow4you'";
    $this->db->pquery($query, Array());
    
    $query="DELETE FROM `vtiger_picklist` WHERE `name` = 'cashflow4you_cash' OR `name` = 'cashflow4you_category' OR `name` = 'cashflow4you_paymethod' OR "
            . "`name` = 'cashflow4you_paytype' OR `name` = 'cashflow4you_status' OR `name` = 'cashflow4you_subcategory'";
    $this->db->pquery($query, Array());
    
    $query="DELETE FROM `vtiger_picklist_dependency` WHERE `sourcefield` = 'cashflow4you_cash' OR `sourcefield` = 'cashflow4you_category' OR `sourcefield` = 'cashflow4you_paymethod' OR "
            . "`sourcefield` = 'cashflow4you_paytype' OR `sourcefield` = 'cashflow4you_status' OR `sourcefield` = 'cashflow4you_subcategory'";
    $this->db->pquery($query, Array());
  }
  
  function fix()
  {
    $Cashflow4You = new Cashflow4You_Module_Model();
    $Cashflow4YouModule  = Vtiger_Module::getInstance('Cashflow4You');
    Vtiger_Access::setDefaultSharing($Cashflow4YouModule);

    $this->addCashflowInformation();
    $Cashflow4You->addEventHandler();

    // set numbering for Cashflow4You
    $this->setNumbering();

    //set picklist dependency 
    $this->setPicklistDependency();

    $this->db->pquery("UPDATE vtiger_cashflow4you_paytype SET `presence`=0 WHERE cashflow4you_paytype='Incoming' OR cashflow4you_paytype='Outgoing'",Array()); 
  }
}