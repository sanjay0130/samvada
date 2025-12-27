<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class BoruPayments_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $moduleModel = $recordModel->getModule();
        $fieldList = $moduleModel->getFields();
        $requestFieldList = array_intersect_key($request->getAll(), $fieldList);

        foreach ($requestFieldList as $fieldName => $fieldValue) {
            $fieldModel = $fieldList[$fieldName];
            if ($fieldModel->isEditable()) {
                $recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
            }
        }
        $fieldsInfo = array();
        foreach ($fieldList as $name => $model) {
            $fieldsInfo[$name] = $model->getFieldInfo();
        }

        //Lee 53123
        $returnmodule = $request->get('returnmodule');
        $returnrecord = $request->get('returnrecord');
        if ($returnmodule == 'Invoice' && !empty($returnrecord)) {
            $recordModel->set('invoiceid', $returnrecord);
        }
        if ($returnmodule == 'SalesOrder' && !empty($returnrecord)) {
            $recordModel->set('salesorderid', $returnrecord);
        }

        $invoiceID = $recordModel->get('invoiceid');
        if ($invoiceID > 0 && isRecordExists($invoiceID)) {
            $invoiceRecord = Vtiger_Record_Model::getInstanceById($invoiceID);
            $cf_balance = $invoiceRecord->get('cf_balance');
            $recordModel->set('amount', $cf_balance);
            //task_id=37377
            $recordModel->set('accountid', $invoiceRecord->get('account_id'));
            $recordModel->set('contactid', $invoiceRecord->get('contact_id'));
        }
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode($picklistDependencyDatasource));
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('INVOICEID', $request->get('returnrecord'));
        $viewer->assign('ACCOUNTID', $request->get('relatedorganization'));
        $viewer->assign('CONTACTID', $request->get('relatedcontact'));
        $viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
        echo $viewer->view('QuickCreate.tpl', $moduleName, true);
    }


}
