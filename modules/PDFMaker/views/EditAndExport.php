<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_EditAndExport_View extends Vtiger_Footer_View {

    public function checkPermission(Vtiger_Request $request) {
    }

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            'modules.PDFMaker.resources.ckeditor.ckeditor',
            'modules.Vtiger.resources.validator.BaseValidator',
            'modules.Vtiger.resources.validator.FieldValidator',
            'modules.Vtiger.resources.Popup',
            'libraries.jquery.jquery_windowmsg',
            'libraries.jquery.multiplefileupload.jquery_MultiFile'
            
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
/*
    function postProcess(Vtiger_Request $request) {
        return;
    }
*/
    public function process(Vtiger_Request $request) {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $simple_html_dom_file = $this->getSimpleHtmlDomFile();
        require_once($simple_html_dom_file);
        
        $default_charset = vglobal('default_charset');
        $adb = PearDatabase::getInstance();

        $relmodule = $request->get("formodule");
        $viewer = $this->getViewer($request);

        // get ids
        if ($request->get("idslist") != "") {
            //generating from listview
            $Records = explode(";", rtrim($request->get("idslist"), ";"));
        } elseif ($request->get('record') != '') {
            $Records = array($request->get("record"));
        } elseif ($request->get('return_id') != '') {
            $Records = array($request->get("return_id"));
        }
        $viewer->assign("RECORDS", implode(';', $Records));

        // get selected templates id array
        $commontemplateids = trim($request->get("commontemplateid"), ";");
        $Templateids = explode(";", $commontemplateids);
        $viewer->assign("COMMONTEMPLATEIDS", $commontemplateids);

        // get selected templates array 
        $sql = "SELECT * FROM vtiger_pdfmaker WHERE templateid IN (" . generateQuestionMarks($Templateids) . ")";
        $result = $adb->pquery($sql,$Templateids);
        $num_rows = $adb->num_rows($result);
        $template_select = '';
        if ($num_rows > 1) {
            $template_select .= "<select onChange='changeTemplate(this.value);'>";
            while ($row = $adb->fetchByAssoc($result)) {
                if ($st == "")
                    $st = $row['templateid'];
                $template_select .= "<option value='" . $row['templateid'] . "'>" . $row['filename'] . "</option>";
            }
            $template_select .= "</select>";
        }
        else {
            $st = $adb->query_result($result, 0, "templateid");
            $template_select .= $adb->query_result($result, 0, "filename");
        }
        $viewer->assign("TEMPLATE_SELECT", $template_select);
        $viewer->assign("ST", $st);

        // get content of templates
        $PDFMaker = new PDFMaker_PDFMaker_Model('PDFMaker');
        $PDFContents = array();
        //require_once("modules/PDFMaker/InventoryPDF.php");
        foreach ($Records as $record) {
            $record_model = Vtiger_Record_Model::getInstanceById($record);
            $focus = $record_model->entity;
            foreach ($Templateids AS $templateid) {
                $PDFContent = new PDFMaker_PDFContent_Model($templateid, $relmodule, $focus, $request->get("language"));
                $pdf_content = $PDFContent->getContent();

                $body_html = $pdf_content["body"];
                $body_html = str_replace("#LISTVIEWBLOCK_START#", "", $body_html);
                $body_html = str_replace("#LISTVIEWBLOCK_END#", "", $body_html);

                $PDFContents[$templateid]["header"] = $pdf_content["header"];
                $PDFContents[$templateid]["body"] = $body_html;
                $PDFContents[$templateid]["footer"] = $pdf_content["footer"];
            }
        }
        $pdf_divs = '';
        foreach ($PDFContents AS $templateid => $templatedata) {
            $sections = array("body", "header", "footer");
            foreach ($sections as $val) {
                if ($templatedata[$val] != "") {
                    /*
                    $html = str_get_html($templatedata[$val]);
                    
                    $textTags = $html->find('text');
                    foreach ($textTags as $text) {
                        $text->outertext = str_replace("  ", "&nbsp;&nbsp;", $text->outertext);
                        $text->outertext = str_replace("&nbsp; ", "&nbsp;&nbsp;", $text->outertext);
                    }
                    $templatedata[$val] = htmlentities($html->save(), ENT_QUOTES, $default_charset);
                    */
                    $templatedata[$val] = htmlentities($templatedata[$val], ENT_QUOTES, $default_charset);
                }
            }
            $pdf_divs .= '<div style="display:none;" id="body_div' . $templateid . '"> 
		         <textarea name="body' . $templateid . '" id="body' . $templateid . '" style="width:90%;height:700px" class=small tabindex="5">' . $templatedata["body"] . '</textarea>
		       </div>
		
		       <div style="display:none;" id="header_div' . $templateid . '"> 
		         <textarea name="header' . $templateid . '" id="header' . $templateid . '" style="width:90%;height:700px" class=small tabindex="5">' . $templatedata["header"] . '</textarea>
		       </div>
		 
		       <div style="display:none;" id="footer_div' . $templateid . '"> 
		         <textarea name="footer' . $templateid . '" id="footer' . $templateid . '" style="width:90%;height:700px" class=small tabindex="5">' . $templatedata["footer"] . '</textarea>
		       </div>';

             $pdf_divs .= '<script type="text/javascript">
                            jQuery(document).ready(function(){
		          	CKEDITOR.replace( \'body' . $templateid . '\', {height: \'300\'});
                                CKEDITOR.replace( \'header' . $templateid . '\', {height: \'300\'});
                                CKEDITOR.replace( \'footer' . $templateid . '\', {height: \'300\'});
                                })
		         </script>';    
        }
        $viewer->assign("PDF_DIVS", $pdf_divs);

        $language = Vtiger_Language_Handler::getLanguage();
        $mod_strings = Vtiger_Language_Handler::getModuleStringsFromFile($language, "Documents");
        $pdf_strings = Vtiger_Language_Handler::getModuleStringsFromFile($language, "PDFMaker");

        $sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
        $res = $adb->pquery($sql, array());
        $options = "";
        for ($i = 0; $i < $adb->num_rows($res); $i++) {
            $fid = $adb->query_result($res, $i, "folderid");
            $fldr_name = $adb->query_result($res, $i, "foldername");
            $options.='<option value="' . $fid . '">' . $fldr_name . '</option>';
        }
        $viewer->assign("FOLDER_OPTIONS", $options);

        $viewer->view('EditAndExport.tpl', 'PDFMaker');
    }  
    
    private function getSimpleHtmlDomFile() {
        
        $simple_html_dom_file = "include/simplehtmldom/simple_html_dom.php";
        if(file_exists($simple_html_dom_file)) {
            return $simple_html_dom_file; 
        } else {
            return "modules/PDFMaker/resources/classes/simple_html_dom.php";  
        }
    }
}     