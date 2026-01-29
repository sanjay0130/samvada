{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<div class="container-fluid">
    <form action="" method="post" name="previewdocuments_settings" id="previewdocuments_settings">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
            <tr>
                <td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
                    <div align=center>
                        <table class="settingsSelUITopLine" align="center" border="0" cellpadding="5" cellspacing="0" width="100%">
                            <tr>
                                <td class="heading2" valign="bottom"> <b>{vtranslate('Preview Documents', $MODULE)}</b></td>
                            </tr>
                            <tr>
                                <td><hr/><br/>External storage URL</td>
                            </tr>
                            <tr>
                                <td>
                                    {assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}
                                    <input type="text"  value="{$url}" name="url" data-fieldinfo="{$FIELD_INFO}" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" class="input-large nameField" id="">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="submit" class="btn btn-success" name="save" value="save"><strong>{vtranslate('Save', $MODULE)}</strong></button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
{literal}
    <script type="text/javascript" src="resources/app.js"></script>
    <script type="text/javascript" src="libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js" ></script>
    <script type="text/javascript" src="libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine-en.js" ></script>
    <script>

        editViewForm = jQuery('form[name="previewdocuments_settings"');
        var params = app.validationEngineOptions;
        params.onValidationComplete = function(element,valid){
            if(valid){
                var ckEditorSource = editViewForm.find('.ckEditorSource');
                if(ckEditorSource.length > 0){
                    var ckEditorSourceId = ckEditorSource.attr('id');
                    var fieldInfo = ckEditorSource.data('fieldinfo');
                    var isMandatory = fieldInfo.mandatory;
                    var CKEditorInstance = CKEDITOR.instances;
                    var ckEditorValue = jQuery.trim(CKEditorInstance[ckEditorSourceId].document.getBody().getText());
                    if(isMandatory && (ckEditorValue.length === 0)){
                        var ckEditorId = 'cke_'+ckEditorSourceId;
                        var message = app.vtranslate('JS_REQUIRED_FIELD');
                        jQuery('#'+ckEditorId).validationEngine('showPrompt', message , 'error','topLeft',true);
                        return false;
                    }else{
                        return valid;
                    }
                }
                return valid;
            }
            return valid
        }
        editViewForm.validationEngine(params);

    </script>
{/literal}