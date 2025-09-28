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
	<div class="CustomLabelModalContainer">
		<div class="modal-header">
			<button class="close vtButton" data-dismiss="modal">×</button>
			{if $TYPE eq "reactivate"}
				<h3>{vtranslate('LBL_REACTIVATE', 'Cashflow4You')}</h3>
			{else}
				<h3>{vtranslate('LBL_ACTIVATE_KEY', 'Cashflow4You')}</h3>
			{/if}
		</div>
		<form id="editLicense" class="form-horizontal contentsBackground">
                    <input type="hidden" name="module" value="Cashflow4You">
                    <input type="hidden" name="action" value="License">
                    <input type="hidden" name="mode" value="editLicense">
                    <input type="hidden" name="type" value="{$TYPE}">
                    <div class="modal-body">
				<div class="row-fluid">
					<div class="control-group">
						<label class="muted control-label">{vtranslate('LBL_LICENSE_KEY', 'Cashflow4You')}</label>
						<div class="controls"><input type="text" name="licensekey" value="{$LICENSEKEY}" data-validation-engine='validate[required]' /></div>	
					</div>
				</div>
                    </div>
                    {if $LABELID eq ""}<input type="hidden" class="addCustomLabelView" value="true" />{/if}                        
                    {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}