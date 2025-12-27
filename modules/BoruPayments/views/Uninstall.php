<?php

class BoruPayments_Uninstall_View extends Vtiger_Index_View {
    function process(Vtiger_Request $request) {
       $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $this->viewName = $request->get('viewname');

        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->view('Uninstall.tpl', $moduleName);  
    }
}

?>

