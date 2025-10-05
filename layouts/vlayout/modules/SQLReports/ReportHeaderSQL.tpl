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
{strip}
	<div id="toggleButton" class="toggleButton" title="Left Panel Show/Hide"> 
		<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
	</div>
	<div class="reportsDetailHeader row-fluid">
        <input type="hidden" name="date_filters" data-value='{ZEND_JSON::encode($DATE_FILTERS)}' />
		<div class="reportHeader row-fluid span12">
			<div class='span4' style="position:relative;left:10px">
				{if $REPORT_MODEL->isEditable() eq true}
					<button onclick='window.location.href="{$REPORT_MODEL->getEditViewUrl()}"' type="button" class="cursorPointer btn"><strong>{vtranslate('LBL_CUSTOMIZE',$MODULE)}</strong>&nbsp;<i class="icon-pencil"></i></button>
					&nbsp;
				{/if}
			</div>
			<div class='span4 textAlignCenter'>
				<h3>{$REPORT_MODEL->getName()}</h3>
				<div id="noOfRecords">{vtranslate('LBL_NO_OF_RECORDS',$MODULE)} <span id="countValue">{$COUNT}</span></div>
			</div>
			<div class='span4'>
				<span class="pull-right" style='margin-right:50px;'>
					{foreach item=DETAILVIEW_LINK from=$DETAILVIEW_LINKS}
						<img class="cursorPointer alignBottom" onclick='window.location.href="{$DETAILVIEW_LINK->getUrl()}"' src="{vimage_path({$DETAILVIEW_LINK->get('linkicon')})}" alt="{vtranslate($DETAILVIEW_LINK->getLabel(), $MODULE)}" title="{vtranslate($DETAILVIEW_LINK->getLabel(), $MODULE)}" />&nbsp;
					{/foreach}
				</span>
			</div>
		</div>		
	</div>
	<div id="reportContentsDiv">
{/strip}