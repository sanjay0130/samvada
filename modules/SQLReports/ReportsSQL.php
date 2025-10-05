<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source in cooperation with a-g-c (Andreas Goebel)
 * The Initial Developer of the Original Code is vtiger and a-g-c (Andreas Goebel)
 * Portions created by vtiger are Copyright (C) vtiger, portions created by a-g-c are Copyright (C) a-g-c.
 * www.a-g-c.de
 * All Rights Reserved.
 ************************************************************************************/
require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once('include/utils/UserInfoUtil.php');
global $app_strings,$mod_strings;
global $log;


class ReportsSQL extends CRMEntity
{


	var $repname;
	var $repid;
	var $repdesc;
	var $sqlcode;

	function ReportsSQL($reportid="")
	{
		global $adb,$current_user,$theme,$mod_strings;
		if($reportid != "")
		{
                        $sql = "select * from vtiger_sqlreports where sqlreportsid = ?";
                        $result = $adb->pquery($sql, array($reportid));
                        $report = $adb->fetch_array($result);
                        if(count($report)>0)
                        {
                            $this->repid = $report['sqlreportsid'];
                            $this->repname = html_entity_decode($report['reportname'], ENT_QUOTES);
                            //$this->repdesc = html_entity_decode($report['repdesc']);
                            $this->sqlcode = html_entity_decode($report['reportsql'], ENT_QUOTES);

                            $this->sqlcode = str_replace("&lt;","<",$this->sqlcode);
                            $this->sqlcode = str_replace("&gt;",">",$this->sqlcode);
                            $this->sqlcode = $this->unscriptSQL($this->sqlcode);
    
                        }
                }
	}

        function unscriptSQL($sqlcode)
        {
            $elements = explode('#DEFINE_END#', $sqlcode);
            if(count($elements) == 1)
            {
                //there are no script elements, just plain sql
                //return everything unchanged
                return $elements[0];
            }

            $variables = explode("';",$elements[0]);
            $rawSQL = $elements[1];
            
            foreach($variables as $variable)
            {
                $var_elements = explode(":'",$variable);
                $var_name = $var_elements[0];
                $var_name = str_replace("var ", "", $var_name);
                $var_name = str_replace("\r\n", "", $var_name);
                $var_name = str_replace("\n", "", $var_name);                       
                $var_name = '#'.$var_name.'#';

                $var_value = $var_elements[1];
                $rawSQL = str_replace($var_name, $var_value, $rawSQL);
            }
            return $rawSQL;
        }

}

?>
