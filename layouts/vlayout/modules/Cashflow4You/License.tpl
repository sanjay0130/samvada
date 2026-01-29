{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
<script type="text/javascript" src="layouts/vlayout/modules/Cashflow4You/resources/License.js"></script>
<div class="container-fluid" id="licenseContainer">
    
    <form name="profiles_privilegies" action="index.php" method="post" class="form-horizontal">
    <br>
    <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_LICENSE','Cashflow4You')}</label>
    <br clear="all">
    <hr>

    <input type="hidden" name="module" value="Cashflow4You" />
    <input type="hidden" name="view" value="" />
    <input type="hidden" name="license_key_val" id="license_key_val" value="{$LICENSE}" />
     <br />
    <div class="row-fluid">
        <label class="fieldLabel"><strong>{vtranslate('LBL_LICENSE_DESC','Cashflow4You')}:</strong></label><br>

        <table class="table table-bordered table-condensed themeTableColor">
            <thead>
                    <tr class="blockHeader">
                            <th colspan="2" class="mediumWidthType">
                                    <span class="alignMiddle">{vtranslate('LBL_LICENSE', 'Cashflow4You')}</span>
                            </th>
                    </tr>
            </thead>
            <tbody>
                    <tr>
                        <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('LBL_LICENSE_KEY', 'Cashflow4You')}:</label></td>
                        <td style="border-left: none;">
                            <div class="pull-left" id="license_key_label">{$LICENSE}</div>
                            <div id="divgroup1" class="btn-group pull-left paddingLeft10px" {if $VERSION_TYPE eq "basic" || $VERSION_TYPE eq "professional"}style="display:none"{/if}>
                                <button id="activate_license_btn"  class="btn addButton" title="{vtranslate('LBL_ACTIVATE_KEY_TITLE','Cashflow4You')}" type="button"><strong>{vtranslate('LBL_ACTIVATE_KEY','Cashflow4You')}</strong></button>
                            </div>
                            <div id="divgroup2" class="pull-left paddingLeft10px" {if $VERSION_TYPE neq "basic" && $VERSION_TYPE neq "professional"}style="display:none"{/if}>
                                <button id="reactivate_license_btn"  class="btn btn-success" title="{vtranslate('LBL_REACTIVATE_DESC','Cashflow4You')}" type="button">{vtranslate('LBL_REACTIVATE','Cashflow4You')}</button>
                                <button id="deactivate_license_btn" type="button" class="btn btn-danger marginLeftZero">{vtranslate('LBL_DEACTIVATE','Cashflow4You')}</button>
                            </div>
                        </td>
                    </tr>
             </tbody>
        </table>
    </div>
    {if $MODE eq "edit"}        
        <div class="pull-right">
            <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
            <a class="cancelLink" onclick="javascript:window.history.back();" type="reset">Cancel</a>
        </div> 
    {/if}
    </form>        

</div>
      
{literal}
<script language="javascript" type="text/javascript">
Cashflow4You_License_Js.registerEvents();
</script>
{/literal}    