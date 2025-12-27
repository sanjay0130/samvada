<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class BoruPayments_MassDelete_Action extends Vtiger_MassDelete_Action {

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $recordIds = $this->getRecordsListFromRequest($request);

        foreach ($recordIds as $recordId) {
            if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {

                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
                //# Lee: update related invoice balance before deleted
                $recordModel->updateInvoiceAmount4Delete();
                $recordModel->delete();
            } else {
                $permission = 'No';
            }
        }

        if ($permission === 'No') {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $cvId = $request->get('viewname');
        $response = new Vtiger_Response();
        $response->setResult(array('viewname' => $cvId, 'module' => $moduleName));
        $response->emit();
    }

}
