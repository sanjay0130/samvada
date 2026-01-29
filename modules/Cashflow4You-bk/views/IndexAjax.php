<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Cashflow4You_IndexAjax_View extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showSettingsList');
        $this->exposeMethod('editLicense');

    }
    
    function showSettingsList(Vtiger_Request $request) {

        $Cashflow4You = new Cashflow4You_Module_Model();

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $viewer->assign('MODULE', $moduleName);

        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'MODE' => $request->get('mode'));
        $linkModels = $Cashflow4You->getSideBarLinks($linkParams);
        
        $viewer->assign('QUICK_LINKS', $linkModels);

        $parent_view = $request->get('pview');
        
        if ($parent_view == "EditProductBlock") $parent_view = "ProductBlocks";
        
        $viewer->assign('CURRENT_PVIEW', $parent_view);
        
        echo $viewer->view('SettingsList.tpl', 'Cashflow4You', true);

    }
    
    function editLicense(Vtiger_Request $request) {
        
        $Cashflow4You = new Cashflow4You_Module_Model();

        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
       
        $type = $request->get('type');
        $viewer->assign("TYPE", $type);
        
        $key = $request->get('key');
        $viewer->assign("LICENSEKEY", $key);
        
        echo $viewer->view('EditLicense.tpl', 'Cashflow4You', true);
    }
    
    function integration(Vtiger_Request $request) {
        
        $Cashflow4You = new Cashflow4You_Module_Model();
        $adb = PearDatabase::getInstance();
        
        $viewer = $this->getViewer($request);

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
        
        echo $viewer->view('Integration.tpl', 'Cashflow4You');  
    }
}