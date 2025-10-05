<?php

/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Cashflow4You_Integration_View extends Vtiger_Index_View {

    
    public function process(Vtiger_Request $request) {
        $Cashflow4You = new Cashflow4You_Module_Model();
        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);

        $viewer->assign("VERSION", Cashflow4You_Version_Helper::getVersion());
        $mode = $request->get('mode');
        
        $viewer->assign("MODE", $mode);             
        $Modules = Array('Invoice' => 0, 'SalesOrder'=> 0, 'PurchaseOrder'=>0, 'Potentials'=>0 );
        
        if( Vtiger_Module::getInstance("ITS4YouPreInvoice") != false )
        {
          $Modules['ITS4YouPreInvoice']= 0;
        }
        if( Vtiger_Module::getInstance("CreditNotes4You") != false )
        {
          $Modules['CreditNotes4You']= 0;
        }
        $sql = "SELECT name FROM `vtiger_tab` "
              . "INNER JOIN vtiger_links ON vtiger_links.tabid=vtiger_tab.tabid "
              . "WHERE `linkurl` LIKE '%cashflow4you%' AND linktype != 'HEADERSCRIPT'";
        $result = $adb->pquery($sql,Array());
        $i=0;
        while($row = $adb->fetchByAssoc($result)) {
            $Modules[$row["name"]] = 1;
        }
        $viewer->assign("MODUES_INTERGATION", $Modules);   
        
        $viewer->assign("LICENSE", $Cashflow4You->GetLicenseKey());
        $viewer->assign("VERSION_TYPE", $Cashflow4You->GetVersionType());  
        
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_MODULE', $request->get('module'));
        
        $viewer->view('Integration.tpl', 'Cashflow4You');         
    }
    
    function getHeaderScripts(Vtiger_Request $request) {
            $headerScriptInstances = parent::getHeaderScripts($request);
            $moduleName = $request->getModule();

            $jsFileNames = array(
                "layouts.vlayout.modules.Cashflow4You.resources.License",
                "layouts.vlayout.modules.Cashflow4You.resources.Integration",
            );
            $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
            $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
            return $headerScriptInstances;
        }
}     