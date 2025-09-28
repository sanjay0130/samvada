<?php

/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Cashflow4You_License_View extends Vtiger_Index_View {

    
    public function preProcess(Vtiger_Request $request, $display = true) {
        
        $Cashflow4You = new Cashflow4You_Module_Model();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        Vtiger_Basic_View::preProcess($request, false);
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        
        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
       
        $linkModels = $Cashflow4You->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);
        
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }
    
    public function process(Vtiger_Request $request) {
        $Cashflow4You = new Cashflow4You_Module_Model();

        $viewer = $this->getViewer($request);

        $mode = $request->get('mode');
        
        $viewer->assign("MODE", $mode);             
        
        $viewer->assign("LICENSE", $Cashflow4You->GetLicenseKey());
        $viewer->assign("VERSION_TYPE", $Cashflow4You->GetVersionType());            
        
        $viewer->view('License.tpl', 'Cashflow4You');         
    }
    
    function getHeaderScripts(Vtiger_Request $request) {
            $headerScriptInstances = parent::getHeaderScripts($request);
            $moduleName = $request->getModule();

            $jsFileNames = array(
                "layouts.vlayout.modules.Cashflow4You.resources.License",
            );
            $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
            $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
            return $headerScriptInstances;
        }
}     