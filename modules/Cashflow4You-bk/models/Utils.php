<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
define(GREEN, "#009900");
define(RED, "#FF0000");
        
class Cashflow4You_Utils_Model{
    var $current_user;
    var $db;
    
    var $Entity_table = Array(
        "Invoice" => Array("entity"=>"invoice", "table"=>"vtiger_invoice", "status"=>"invoicestatus","status_value"=>"Paid","status_value_back"=>"Created","relatedto"=>"accountid","total_fld"=>"total"),
        "PurchaseOrder" => Array("entity"=>"purchaseorder", "table"=>"vtiger_purchaseorder", "status"=>"postatus","status_value"=>"","status_value_back"=>"","relatedto"=>"vendorid","total_fld"=>"total"),
        "SalesOrder" => Array("entity"=>"salesorder", "table"=>"vtiger_salesorder","status"=>"sostatus","status_value"=>"","status_value_back"=>"","relatedto"=>"accountid","total_fld"=>"total"),
        "Potentials" => Array("entity"=>"potential", "table"=>"vtiger_potential","status"=>"sales_stage","status_value"=>"","status_value_back"=>"","relatedto"=>"related_to","total_fld"=>"amount"),
        "CreditNotes4You" => Array("entity"=>"creditnotes4you", "table"=>"vtiger_creditnotes4you","status"=>"creditnotes4youstatus","status_value"=>"Refunded","status_value_back"=>"Created","relatedto"=>"accountid","total_fld"=>"total"),
        "ITS4YouPreInvoice" => Array("entity"=>"preinvoice", "table"=>"its4you_preinvoice","status"=>"preinvoicestatus","status_value"=>"Paid","status_value_back"=>"Created","relatedto"=>"accountid","total_fld"=>"total"),
    );
    
    function __construct(){
        $this->current_user = Users_Record_Model::getCurrentUserModel();
        $this->db = PearDatabase::getInstance();
    }
    
    function formatNumber( $number, $groupseparator = true )
    {
        $decimal_sep = $this->current_user->column_fields["currency_decimal_separator"];
        $group_sep = $this->current_user->column_fields["currency_grouping_separator"];
        $number = $number * 1;
        if( $groupseparator )
        {
            return number_format($number, getCurrencyDecimalPlaces(),$decimal_sep,$group_sep); 
        }
        else
        {
            return number_format($number, getCurrencyDecimalPlaces(),$decimal_sep,""); 
        }
    }
    
    function getUserDateFormat()
    {
        return $this->current_user->column_fields["date_format"]; 
    }
    
    function getModuleById( $crmid ) 
    {
        $slect = "SELECT setype FROM vtiger_crmentity WHERE crmid=?";
        $result = $this->db->pquery($slect, Array($crmid) );
        return $this->db->query_result($result,0,'setype');
    }
    
    function getCollor( $collor = "green" ) 
    {
        if( $collor == "green" )
        {
           return GREEN;
        }
        else if( $collor == "red" )
        {
           return RED;
        }
        return null;
    }
}