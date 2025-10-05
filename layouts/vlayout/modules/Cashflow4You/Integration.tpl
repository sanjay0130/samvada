{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <form name="allowed_modules_form" id="updateAllowedModulesForm" method="post" action="index.php">
        <input type="hidden" name="module" value="Cashflow4You">
        <input type="hidden" name="action" value="Integration">
        <div class="padding-left1per container-fluid settingsIndexPage">
            <div class="widget_header row-fluid settingsHeader">
                <h3><a href="index.php?module={$CURRENT_MODULE}&view=List">{vtranslate('Cashflow4You', 'Cashflow4You')} {vtranslate('LBL_INTEGRATION','Cashflow4You')}</a></h3>
                <hr>
            </div>
            {include file="ModalFooter.tpl"|@vtemplate_path:$CURRENT_MODULE}
            <div  id="CompanyDetailsContainer" class="{if !empty($ERROR_MESSAGE)}hide{/if}">
                <div class="row-fluid">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="blockHeader">
                                <th colspan="2"><strong>{vtranslate('LBL_AVAILABLE_MODULES','Cashflow4You')}</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=MODULES_VALUE key=MODULES_NAME  from=$MODUES_INTERGATION}
                                <tr>
                                    <td >
                                        <input type="hidden" name="module_{$MODULES_NAME}" id="module_{$MODULES_NAME}" value="{$MODULES_VALUE}"/>
                                        <input type="checkbox" name="chx_{$MODULES_NAME}" id="chx_{$MODULES_NAME}" {if $MODULES_VALUE eq 1 }checked{/if}/>
                                    </td>
                                    <td >
                                       {vtranslate($MODULES_NAME, 'Vtiger')}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>

                </div>
            </div>
            {include file="ModalFooter.tpl"|@vtemplate_path:$CURRENT_MODULE}
        </div>
    </form>
<br>
<div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate("Cashflow4You","Cashflow4You")} {$VERSION} {vtranslate("COPYRIGHT","Cashflow4You")}</div>
{/strip}