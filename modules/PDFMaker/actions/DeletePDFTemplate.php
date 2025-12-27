<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_DeletePDFTemplate_Action extends Vtiger_Save_Action {

    public function checkPermission(Vtiger_Request $request) {
    }

    public function process(Vtiger_Request $request) {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        if ($PDFMaker->CheckPermissions("DELETE") == false)
            $PDFMaker->DieDuePermission();

        $adb = PearDatabase::getInstance();
        
        $id_array = array();

        if ($request->has('templateid') && !$request->isEmpty('templateid')) {
            $templateid = $request->get('templateid');

            $checkSql = "select module from vtiger_pdfmaker where templateid=?";
            $checkRes = $adb->pquery($checkSql, array($templateid));
            $checkRow = $adb->fetchByAssoc($checkRes);
            //if we are trying to delete template that is not allowed for current user then die because user should not be able to see the template
            //$PDFMaker->CheckTemplatePermissions($checkRow["module"], $templateid);

            $Template_Permissions_Data = $PDFMaker->returnTemplatePermissionsData($checkRow["module"], $templateid);
        
            if ($Template_Permissions_Data["delete"] === false) {
                $this->DieDuePermission();
            }
            
            
            $sql = "delete from vtiger_pdfmaker where templateid=?";
            $adb->pquery($sql, array($templateid));

            $sql = "delete from vtiger_pdfmaker_settings where templateid=?";
            $adb->pquery($sql, array($templateid));
        } else {
            $idlist = $request->get('idlist');
            $id_array = explode(';', $idlist);

            $checkSql = "select templateid, module from vtiger_pdfmaker where templateid IN (" . generateQuestionMarks($id_array) . ")";
            $checkRes = $adb->pquery($checkSql, $id_array);
            $checkArr = array();
            while ($checkRow = $adb->fetchByAssoc($checkRes)) {
                $checkArr[$checkRow["templateid"]] = $checkRow["module"];
            }

            for ($i = 0; $i < count($id_array) - 1; $i++) {
                //if we are trying to delete template that is not allowed for current user then die because user should not be able to see the template
                //$PDFMaker->CheckTemplatePermissions($checkArr[$id_array[$i]], $id_array[$i]);
                
                $Template_Permissions_Data = $PDFMaker->returnTemplatePermissionsData($checkArr[$id_array[$i]], $id_array[$i]);
        
                if ($Template_Permissions_Data["delete"] === false) {
                    $this->DieDuePermission();
                }
                
                
                $sql = "delete from vtiger_pdfmaker where templateid=?";
                $adb->pquery($sql, array($id_array[$i]));

                $sql = "delete from vtiger_pdfmaker_settings where templateid=?";
                $adb->pquery($sql, array($id_array[$i]));
            }
        }
        $ajaxDelete = $request->get('ajaxDelete');
        $listViewUrl = "index.php?module=PDFMaker&view=List";
        if($ajaxDelete) {
                $response = new Vtiger_Response();
                $response->setResult($listViewUrl);
                return $response;
        } else {
                header("Location: $listViewUrl");
        }
    }
}