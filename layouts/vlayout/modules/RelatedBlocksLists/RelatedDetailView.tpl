{*/* ********************************************************************************
* The content of this file is subject to the Related Blocks & Lists ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

{strip}
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    {foreach from=$BLOCKS_LIST key=BLOCKID item=BLOCKDATA}
        {assign var="RELMODULE_MODEL" value=$BLOCKDATA['relmodule']}
        {assign var="RELMODULE_NAME" value=$RELMODULE_MODEL->getName()}
        {assign var="FIELDS_LIST" value=$BLOCKDATA['fields']}
        {assign var="RELATED_RECORDS" value=$BLOCKDATA['data']}
        {if $AJAX neq '1'}
        <br>
        <table class="table table-bordered blockContainer showInlineTable equalSplit">
            <thead>
            <tr>
                <th class="blockHeader" colspan="4">{vtranslate('LBL_RELATED', 'RelatedBlocksLists')} {if $BLOCKDATA['type'] eq 'block'}{vtranslate('LBL_BLOCK', 'RelatedBlocksLists')}{else}{vtranslate('LBL_LIST', 'RelatedBlocksLists')}{/if} : {vtranslate($RELMODULE_NAME, $RELMODULE_NAME)}</th>
            </tr>
            </thead>
        </table>
        <div class="relatedblockslists_records recordEditView relatedblockslists{$BLOCKID}" data-block-id="{$BLOCKID}" data-rel-module="{$RELMODULE_NAME}">
        {/if}
            {if $BLOCKDATA['type'] eq 'block'}
                {foreach from=$RELATED_RECORDS item=RELATED_RECORD_MODEL name=related_records_block}
                    <div class="relatedRecords">
                        {include file=vtemplate_path('RelatedRecordDetail.tpl',$QUALIFIED_MODULE) RELMODULE_MODEL=$RELMODULE_MODEL RELMODULE_NAME=$RELMODULE_NAME FIELDS_LIST=$FIELDS_LIST RELATED_RECORD_MODEL=$RELATED_RECORD_MODEL BLOCKID=$BLOCKID BLOCKTYPE='block'}
                    </div>
                {/foreach}
            {else}
                {*<div class="contents-topscroll" style="display: none;"> *}{*style="visibility: collapse;"*}{*
                    <div class="topscroll-div">
                        &nbsp;
                    </div>
                </div>*}
                {*<div class="relatedContents contents-bottomscroll" style="overflow-y:hidden;">
                    <div class="bottomscroll-div">*}
                        <div class="relatedContents">
                        <table class="table table-bordered table-condensed listViewEntriesTable">
                            <thead>
                            <tr class="listViewHeaders">
                                {assign var=COUNT value=$FIELDS_LIST|count}
                                {assign var=CELLWIDTH value=93/($COUNT)}
                                {foreach item=FIELD_MODEL from=$FIELDS_LIST name=fields_list_header}
                                    {if $FIELD_MODEL->isEditable() eq 'true'}
                                        <th class="fieldLabel {$WIDTHTYPE}" {if $FIELD_MODEL@last} colspan="2" style="width:{$CELLWIDTH+5}%;" {else} style="width:{$CELLWIDTH}%;" {/if} ><strong>{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $RELMODULE_NAME)}</strong></th>
                                    {/if}
                                {/foreach}
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="relatedRecordsClone hide">
                                {foreach item=FIELD_MODEL from=$FIELDS_LIST name=fields_list_clone}
                                    {if $FIELD_MODEL->isEditable() eq 'true'}
                                        <td class="fieldValue {$WIDTHTYPE}">
                                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$RELMODULE_NAME) BLOCK_FIELDS=$FIELDS_LIST MODULE=$RELMODULE_NAME}
                                        </td>
                                    {/if}
                                {/foreach}
                                <td>
                                    <div class="actions pull-right" style="padding-top:7px; padding-right:10px;">
                                        &nbsp;<a class="relatedBtnSave" data-block-id="{$BLOCKID}"><i title="{vtranslate('LBL_SAVE', $MODULE)}" class="icon-ok alignMiddle"></i></a>
                                        &nbsp;<a class="relatedBtnDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
                                    </div>
                                </td>
                            </tr>
                            {foreach from=$RELATED_RECORDS item=RELATED_RECORD_MODEL name=related_records_list}
                                <tr class="relatedRecords">
                                    {include file=vtemplate_path('RelatedRecordDetail.tpl',$QUALIFIED_MODULE) RELMODULE_MODEL=$RELMODULE_MODEL RELMODULE_NAME=$RELMODULE_NAME FIELDS_LIST=$FIELDS_LIST RELATED_RECORD_MODEL=$RELATED_RECORD_MODEL BLOCKID=$BLOCKID BLOCKTYPE='list'}
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                        </div>
                    {*</div>
                </div>*}
            {/if}
            <div class="row-fluid relatedAddMoreBtn">
                <div style="text-align: center">
                    <button type="button" class="btn btn-success relatedBtnAddMore" data-rel-module="{$RELMODULE_NAME}" data-block-id="{$BLOCKID}" data-type="{$BLOCKDATA['type']}" style="padding: 0px 10px;{if $BLOCKDATA['type'] eq 'block'}margin-top:-43px;{/if}"><strong>{vtranslate('LBL_CREATE_ANOTHER', 'RelatedBlocksLists')}</strong></button>
                </div>
            </div>
        {if $AJAX neq '1'}
        </div>
        {/if}
    {/foreach}
{/strip}