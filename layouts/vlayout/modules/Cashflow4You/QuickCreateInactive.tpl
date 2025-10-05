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
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
		
<div class="modelContainer">
<div class="modal-header contentsBackground">
	<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
    <h3>{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
</div>
<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="action" value="SaveAjax">
	<div class="quickCreateContent">
            <div class="modal-body">
                {vtranslate('LBL_INACTIVE', 'Cashflow4You')}
            </div>
        </div>
	<div class="modal-footer quickCreateActions">
		{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
			<a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
	</div>
</form>
</div>
{/strip}