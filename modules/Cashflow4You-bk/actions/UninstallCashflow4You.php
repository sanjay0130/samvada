<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
error_reporting(0);
class Cashflow4You_UninstallCashflow4You_Action extends Settings_Vtiger_Basic_Action {

    var $db;
    function __construct() {
        $this->db = PearDatabase::getInstance();
        parent::__construct();
    }

    function process(Vtiger_Request $request) {
        $Vtiger_Utils_Log = true;
        include_once('vtlib/Vtiger/Module.php');
        $module = Vtiger_Module::getInstance('Cashflow4You');
        if ($module) {
            $Cashflow4You = new Cashflow4You_Module_Model();
            
            $request->set('key', $Cashflow4You->GetLicenseKey());
            
            $Cashflow4You_License_Action_Model = new Cashflow4You_License_Action();
            $Cashflow4You_License_Action_Model->deactivateLicense($request);

            $module->delete();
            @shell_exec('rm -r modules/Cashflow4You');
            @shell_exec('rm -r layouts/vlayout/modules/Cashflow4You');
            @shell_exec('rm -f languages/ar_ae/Cashflow4You.php');
            @shell_exec('rm -f languages/ar_ae/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/cz_cz/Cashflow4You.php');
            @shell_exec('rm -f languages/cz_cz/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/de_de/Cashflow4You.php');
            @shell_exec('rm -f languages/de_de/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/en_gb/Cashflow4You.php');
            @shell_exec('rm -f languages/en_gb/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/en_us/Cashflow4You.php');
            @shell_exec('rm -f languages/en_us/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/es_es/Cashflow4You.php');
            @shell_exec('rm -f languages/es_es/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/es_mx/Cashflow4You.php');
            @shell_exec('rm -f languages/es_mx/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/fr_fr/Cashflow4You.php');
            @shell_exec('rm -f languages/fr_fr/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/hi_hi/Cashflow4You.php');
            @shell_exec('rm -f languages/hi_hi/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/hu_hu/Cashflow4You.php');
            @shell_exec('rm -f languages/hu_hu/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/it_it/Cashflow4You.php');
            @shell_exec('rm -f languages/it_it/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/nl_nl/Cashflow4You.php');
            @shell_exec('rm -f languages/nl_nl/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/pl_pl/Cashflow4You.php');
            @shell_exec('rm -f languages/pl_pl/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/pt_br/Cashflow4You.php');
            @shell_exec('rm -f languages/pt_br/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/ro_ro/Cashflow4You.php');
            @shell_exec('rm -f languages/ro_ro/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/ru_ru/Cashflow4You.php');
            @shell_exec('rm -f languages/ru_ru/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/sk_sk/Cashflow4You.php');
            @shell_exec('rm -f languages/sk_sk/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/sv_se/Cashflow4You.php');
            @shell_exec('rm -f languages/sv_se/Settings/Cashflow4You.php');
            @shell_exec('rm -f languages/tr_tr/Cashflow4You.php');
            @shell_exec('rm -f languages/tr_tr/Settings/Cashflow4You.php');

            $this->db->pquery("DROP TABLE IF EXISTS its4you_cashflow4you",Array());
            $this->db->pquery("DROP TABLE IF EXISTS its4you_cashflow4youcf",Array());
            $this->db->pquery("DROP TABLE IF EXISTS its4you_cashflow4you_associatedto",Array());
            $this->db->pquery("DROP TABLE IF EXISTS its4you_cashflow4you_license",Array());
            $this->db->pquery("DROP TABLE IF EXISTS its4you_cashflow4you_version",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_cash",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_cash_seq",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_category",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_category_seq",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_paymethod",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_paymethod_seq",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_paytype",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_paytype_seq",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_status",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_status_seq",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_subcategory",Array());
            $this->db->pquery("DROP TABLE IF EXISTS vtiger_cashflow4you_subcategory_seq",Array());
            

            $result = array('success' => true);
        } else {
            $result = array('success' => false);
        }
        ob_clean();
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
