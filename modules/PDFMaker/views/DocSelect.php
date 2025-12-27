<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('include/utils/utils.php');

global $app_strings, $adb;

if (isPermitted("Documents", "EditView") == "no") {
    $PDFMaker = CRMEntity::getInstance('PDFMaker');
    $PDFMaker->DieDuePermission();
}

$language = $_SESSION['authenticated_user_language'];
$mod_strings = return_module_language($language, "Documents");
$pdf_strings = return_module_language($language, "PDFMaker");

//getting the related fields to Contacts or Accounts in order to pre-fill the fields belows
$exis_account_id = "";
$exis_account_id_display = "";
$exis_contact_id = "";
$exis_contact_id_display = "";

$tabid = getTabid($return_module);
$sql = "SELECT fieldid, fieldname, uitype, columnname
        FROM vtiger_field 
        WHERE tabid=? AND uitype IN (51, 57, 73, 10)";
$result = $adb->pquery($sql, array($tabid));
$num_rows = $adb->num_rows($result);
if ($num_rows > 0) {
    $focus = CRMEntity::getInstance($return_module);
    $focus->retrieve_entity_info($return_id, $return_module);    
    while ($row = $adb->fetchByAssoc($result)) {
        $fk_record = $focus->column_fields[$row["fieldname"]];
        switch ($row["uitype"]) {
            case "57":
                $relMod = "Contacts";
                break;

            case "51":
            case "73":
                $relMod = "Accounts";
                break;

            case "10":
                $relMod = getSalesEntityType($fk_record);
                break;

            default:
                $relMod = "";
        }
        if ($relMod == "Contacts" || $relMod == "Accounts") {
            $value = "";
            $displayValueArray = getEntityName($relMod, $fk_record);
            if (!empty($displayValueArray)) {
                foreach ($displayValueArray as $p_value) {
                    $value = $p_value;
                }
            }
            
            if($relMod == "Contacts" && $exis_contact_id == "") {
                $exis_contact_id = $fk_record;
                $exis_contact_id_display = $value;
            } elseif($relMod == "Accounts" && $exis_account_id == "") {
                $exis_account_id = $fk_record;
                $exis_account_id_display = $value;
            }   
        }
    }
}

$sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
$res = $adb->pquery($sql, array());
$options = "";
for ($i = 0; $i < $adb->num_rows($res); $i++) {
    $fid = $adb->query_result($res, $i, "folderid");
    $fldr_name = $adb->query_result($res, $i, "foldername");
    $options.='<option value="' . $fid . '">' . $fldr_name . '</option>';
}

echo '
<div xmlns="http://www.w3.org/1999/xhtml" style="min-width: 350px;" class="modelContainer" id="PDFMakerDocumentsContainer">
<div class="modal-header">
	<button title="' . vtranslate('LBL_CLOSE') . '" data-dismiss="modal" class="close">x</button>
	<h3>' . vtranslate("LBL_SAVEASDOC","PDFMaker") . '</h3>
</div>

<form name="PDFDocForm" method="post" action="index.php" onSubmit="PDFMakerCommon.savePDFDoc(); return false;">
<input type="hidden" name="module" value="PDFMaker" />
<input type="hidden" name="action" value="SaveIntoDocuments" />
<input type="hidden" name="pmodule" value="' . $_REQUEST["return_module"] . '" />
<input type="hidden" name="pid" value="' . $_REQUEST["return_id"] . '" />
<input type="hidden" name="template_ids" value="" />
<input type="hidden" name="language" value="" />
<table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
    <tr><td class="small">
        <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
            <tr><td colspan="2" class="detailedViewHeader" style="padding-top:5px;padding-bottom:5px;"><b>' . vtranslate("Documents") . '</b></td></tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right"><font color="red">*</font>' . vtranslate("Title","Documents") . '</td>
                <td class="dvtCellInfo" width="80%" align="left"><input name="notes_title" type="text" class="detailedViewTextBox"></td>
            </tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . vtranslate("Folder Name","Documents") . '</td>
                <td class="dvtCellInfo" width="80%" align="left">
                  <select name="folderid" class="small">
                  ' . $options . '
                  </select>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . vtranslate("Note","Documents") . '</td>
                <td class="dvtCellInfo" width="80%" align="left"><textarea name="notecontent" class="detailedViewTextBox"></textarea></td>
            </tr>
        </table>
        <!--<table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
            <tr><td colspan="2" class="detailedViewHeader" style="padding-top:5px;padding-bottom:5px;"><b>' . vtranslate("Related To") . '</b></td></tr>
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . vtranslate("SINGLE_Contacts") . '</td>
                <td class="dvtCellInfo" width="80%" align="left">
                    <input name="pdfdoc_contact_id" type="hidden" value="'.$exis_contact_id.'"/>
                    <input name="pdfdoc_contact_id_display" type="text" value="'.$exis_contact_id_display.'" readonly />
                    <img src="themes/softed/images/select.gif" tabindex="" alt="' . vtranslate("LBL_SELECT") . '" title="' . vtranslate("LBL_SELECT") . '" language="javascript" onclick="return window.open(\'index.php?module=Contacts&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=pdfdoc_contact_id&srcmodule=' . $return_module . '&forrecord=' . $return_id . '\',\'test\',\'width=640,height=602,resizable=0,scrollbars=0,top=150,left=200\');" style="cursor:hand;cursor:pointer" align="absmiddle">
                    <input src="themes/images/clear_field.gif" alt="' . vtranslate("LBL_CLEAR") . '" title="' . vtranslate("LBL_CLEAR") . '" language="javascript" onclick="this.form.pdfdoc_contact_id.value=\'\'; this.form.pdfdoc_contact_id_display.value=\'\'; return false;" style="cursor:hand;cursor:pointer" align="absmiddle" type="image">
                </td>
            </tr>  
            <tr>
                <td class="dvtCellLabel" width="20%" align="right">' . vtranslate("SINGLE_Accounts") . '</td>
                <td class="dvtCellInfo" width="80%" align="left" nowrap>
                    <input name="pdfdoc_account_id" type="hidden" value="'.$exis_account_id.'" />
                    <input name="pdfdoc_account_id_display" type="text" value="'.$exis_account_id_display.'" readonly />
                    <img src="themes/softed/images/select.gif" tabindex="" alt="' . vtranslate("LBL_SELECT") . '" title="' . vtranslate("LBL_SELECT") . '" language="javascript" onclick="return window.open(\'index.php?module=Accounts&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=pdfdoc_account_id&srcmodule=' . $return_module . '&forrecord=' . $return_id . '\',\'test\',\'width=640,height=602,resizable=0,scrollbars=0,top=150,left=200\');" style="cursor:hand;cursor:pointer" align="absmiddle">
                    <img src="themes/softed/images/select.gif" tabindex="" alt="' . vtranslate("LBL_SELECT") . '" title="' . vtranslate("LBL_SELECT") . '" language="javascript" onclick="return window.open(\'index.php?module=Accounts&src_module=PDFMaker&src_field=pdfdoc_account_id&src_record='.$return_id.'&view=Popup&triggerEventName=pdfmaker_postSelection_accounts\',\'test\',\'width=640,height=602,resizable=0,scrollbars=0,top=150,left=200\');" style="cursor:hand;cursor:pointer" align="absmiddle">
                    <input src="themes/images/clear_field.gif" alt="' . vtranslate("LBL_CLEAR") . '" title="' . vtranslate("LBL_CLEAR") . '" language="javascript" onclick="this.form.pdfdoc_account_id.value=\'\'; this.form.pdfdoc_account_id_display.value=\'\'; return false;" style="cursor:hand;cursor:pointer" align="absmiddle" type="image">
                </td>
            </tr> 
        </table>-->
    </td></tr>
</table>
<div class="modal-footer">
	<div class=" pull-right cancelLinkContainer"><a data-dismiss="modal" type="reset" class="cancelLink">' . vtranslate('LBL_CANCEL') . '</a></div>
	<button name="saveButton" type="submit" class="btn btn-success"><strong>' . vtranslate('LBL_SAVE') . '</strong></button>
</div>
</form>

</div>

';

exit;  