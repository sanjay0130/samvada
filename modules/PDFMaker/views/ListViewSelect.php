<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_ListViewSelect_View extends Vtiger_IndexAjax_View {

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        PDFMaker_Debugger_Model::GetInstance()->Init();
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        if ($PDFMaker->CheckPermissions("DETAIL") == false) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $for_module = $request->get('return_module');
        $recordIds = $this->getRecordsListFromRequest($request);

        $adb = PearDatabase::getInstance();
        
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        if ($PDFMaker->CheckPermissions("DETAIL") == false) {
            
            $close_img = vimage_path('close.gif');
            
            $output = '
                <table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
                <tr>
                      <td width="90%" align="left" class="genHeaderSmall" id="PDFListViewDivHandle" style="cursor:move;">' . vtranslate("LBL_PDF_ACTIONS", "PDFMaker") . '                 			
                      </td>
                      <td width="10%" align="right">
                              <a href="javascript:fninvsh(\'PDFListViewDiv\');"><img title="' . vtranslate("LBL_CLOSE") . '" alt="' . vtranslate("LBL_CLOSE") . '" src="'.$close_img.'" border="0"  align="absmiddle" /></a>
                      </td>
                </tr>
                </table>
                <table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
                    <tr><td class="small">
                        <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
                        <tr>
                          <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;text-align:center;">
                            <strong>' . vtranslate("LBL_PERMISSION_DENIED") . '</strong>
                          </td>
                        </tr>
                        <tr>
                                      <td class="dvtCellInfo" style="width:100%;" align="center">
                            <input type="button" class="crmbutton small cancel" value="' . vtranslate("LBL_CANCEL") . '" onclick="fninvsh(\'PDFListViewDiv\');" />      
                          </td>
                              </tr>      		
                        </table>
                    </td></tr>
                </table>
                ';
            die($output);
        }

        $_REQUEST['idslist'] = implode(";", $recordIds);
        $request->set('idlist', $_REQUEST['idslist']);

 
        //$language = $_SESSION['authenticated_user_language'];
        $current_language = Vtiger_Language_Handler::getLanguage();

        $templates = $PDFMaker->GetAvailableTemplates($for_module, true);
        $options = "";
        foreach ($templates as $templateid => $valArr) {
            if ($valArr["is_default"] == "2" || $valArr["is_default"] == "3")
                $selected = ' selected="selected" ';
            else
                $selected = "";

            $options.='<option value="' . $templateid . '"' . $selected . '>' . $valArr['templatename'] . '</option>';
        }

        $template_output = "";
        $language_output = "";
        $generate_pdf = "";
        if ($options != "") {
            $template_output = '
		    <tr>
		  		<td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;">
		  			<select name="use_common_template" id="use_common_template" class="detailedViewTextBox" multiple style="width:90%;" size="5">
		        ' . $options . '
		        </select>
		  		</td>
				</tr>
		  ';
            $templates_select = '<select name="use_common_template" id="use_common_template" style="display: none;" id="fieldList" class="select2 row-fluid" multiple="true" size="5" data-validation-engine="validate[required]">
		        ' . $options . '
		        </select>';
            $temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active=?", array("1"));
            while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                $template_languages[$temp_row["prefix"]] = $temp_row["label"];
            }

            //LANGUAGES BLOCK  
            if (count($template_languages) > 1) {
                $options = "";
                foreach ($template_languages as $prefix => $label) {
                    if ($current_language != $prefix)
                        $options.='<option value="' . $prefix . '">' . $label . '</option>';
                    else
                        $options.='<option value="' . $prefix . '" selected="selected">' . $label . '</option>';
                }

                $language_output = '
		      <tr>
		  		<td class="dvtCellInfo" style="width:100%;">    	
		          <select name="template_language" id="template_language" class="detailedViewTextBox" style="width:90%;" size="1">
		  		    ' . $options . '
		          </select>
		  		</td>
		      </tr>';
                $languages_select = '<select name="template_language" id="template_language" class="select2 row-fluid" style="display: none;">
		  		    ' . $options . '
		          </select>';
            }
            else {
                foreach ($template_languages as $prefix => $label)
                    $languages_select.='<input type="hidden" name="template_language" id="template_language" value="' . $prefix . '"/>';
            }
        } else {
            $template_output = '<tr>
		                		<td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;">
		                		  ' . vtranslate("CRM_TEMPLATES_DONT_EXIST","PDFMaker");


            //   if(isPermitted("PDFMaker","EditView") == 'yes')
            if ($PDFMaker->CheckPermissions("EDIT")) {
                $template_output.='<br />' . vtranslate("CRM_TEMPLATES_ADMIN","PDFMaker") . '
		                      <a href="index.php?module=PDFMaker&action=EditPDFTemplate&return_module=' . $request->get("return_module") . '&parenttab=Tools" class="webMnu">' . vtranslate("TEMPLATE_CREATE_HERE","PDFMaker") . '</a>';
            }

            $template_output.='</td></tr>';
        }

        $viewer = $this->getViewer($request);
        //$viewer->assign('MODULE', $relmodule);
        //$viewer->assign('ID', $record);

        $viewer->assign('templates_select', $templates_select);
        $viewer->assign('languages_select', $languages_select);
        $viewer->assign('idslist', $_REQUEST['idslist']);
        $viewer->assign('relmodule', $request->get('return_module'));

        $viewer->view("ListViewSelect.tpl", 'PDFMaker');
    }  
    
    function getRecordsListFromRequest(Vtiger_Request $request) {
        $cvId = $request->get('viewname');
        if ($cvId == "") $cvId = $request->get('cvid');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');

        if(!empty($selectedIds) && $selectedIds != 'all') {
            if(!empty($selectedIds) && count($selectedIds) > 0) {
                return $selectedIds;
            }
        }

        $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
        if($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');
            if(!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }
            return $customViewModel->getRecordIds($excludedIds);
        }
    }
} 