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
        {assign var="SELECTED_FIELDS" value=$BLOCKDATA['selected_fields']}
        {assign var="MULTIPICKLIST_FIELDS" value=$BLOCKDATA['multipicklist_fields']}
        {assign var="REFERENCE_FIELDS" value=$BLOCKDATA['reference_fields']}
        <br>
        <table class="table table-bordered blockContainer showInlineTable equalSplit">
            <thead>
            <tr>
                <th class="blockHeader" colspan="4">{vtranslate('LBL_RELATED', 'RelatedBlocksLists')} {if $BLOCKDATA['type'] eq 'block'}{vtranslate('LBL_BLOCK', 'RelatedBlocksLists')}{else}{vtranslate('LBL_LIST', 'RelatedBlocksLists')}{/if} : {vtranslate($RELMODULE_NAME, $RELMODULE_NAME)}</th>
            </tr>
            </thead>
        </table>
        <div class="relatedblockslists_records relatedblockslists{$BLOCKID}" data-block-id="{$BLOCKID}" data-rel-module="{$RELMODULE_NAME}">
            <input type="hidden" id="selected_fields{$BLOCKID}" value="{$SELECTED_FIELDS}"/>
            <input type="hidden" id="multipicklist_fields{$BLOCKID}" value="{$MULTIPICKLIST_FIELDS}"/>
            <input type="hidden" id="reference_fields{$BLOCKID}" value="{$REFERENCE_FIELDS}"/>
            {if $BLOCKDATA['type'] eq 'block'}
                {foreach from=$RELATED_RECORDS item=RELATED_RECORD_MODEL name=related_records_block}
                    <div class="relatedRecords" data-row-no="{$smarty.foreach.related_records_block.iteration}">
                        <input type="hidden" name="relatedblockslists[{$BLOCKID}][{$smarty.foreach.related_records_block.iteration}][module]" value="{$RELMODULE_NAME}"/>
                        <input type="hidden" name="relatedblockslists[{$BLOCKID}][{$smarty.foreach.related_records_block.iteration}][recordId]" value="{$RELATED_RECORD_MODEL->getId()}"/>
                        {include file=vtemplate_path('BlockEditFields.tpl',$QUALIFIED_MODULE) RELMODULE_MODEL=$RELMODULE_MODEL RELMODULE_NAME=$RELMODULE_NAME FIELDS_LIST=$FIELDS_LIST RELATED_RECORD_MODEL=$RELATED_RECORD_MODEL BLOCKID=$BLOCKID}
                        {*{if $smarty.foreach.related_records_block.last}{else}<hr style="border: 10px solid #cccccc; margin: 0px;" />{/if}*}
                    </div>
                {/foreach}
            {else}
                <table class="table table-bordered table-condensed listViewEntriesTable">
                    <thead>
                        <tr class="listViewHeaders">
                            {assign var=COUNT value=$FIELDS_LIST|count}
                            {assign var=CELLWIDTH value=95/($COUNT+1)}
                            {foreach item=FIELD_MODEL from=$FIELDS_LIST name=fields_list_header}
                                {if $FIELD_MODEL->isEditable() eq 'true'}
                                    <th class="fieldLabel {$WIDTHTYPE}" {if $FIELD_MODEL@last} colspan="2" style="width:{$CELLWIDTH+5}%;" {else} style="width:{$CELLWIDTH}%;"  {/if} ><strong>{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $RELMODULE_NAME)}</strong></th>
                                {/if}
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                    <tr class="relatedRecordsClone hide">
                        {foreach item=FIELD_MODEL from=$FIELDS_LIST name=fields_list_clone}
                            {if $FIELD_MODEL->isEditable() eq 'true'}
                                <td class="fieldValue">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$RELMODULE_NAME) BLOCK_FIELDS=$FIELDS_LIST MODULE=$RELMODULE_NAME}
                                    {*{if $FIELD_MODEL@last}
                                        <div class="actions pull-right" style="padding-top:7px; padding-right:10px;">
                                            &nbsp;<a class="relatedBtnDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
                                        </div>
                                    {/if}*}
                                </td>
                            {/if}
                        {/foreach}
                        <td>
                            <div class="actions pull-right" style="padding-top:7px; padding-right:10px;">
                                &nbsp;<a class="relatedBtnDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
                            </div>
                        </td>
                    </tr>
                    {foreach from=$RELATED_RECORDS item=RELATED_RECORD_MODEL name=related_records_list}
                        <tr class="relatedRecords" data-row-no="{$smarty.foreach.related_records_list.iteration}">
                            <input type="hidden" name="relatedblockslists[{$BLOCKID}][{$smarty.foreach.related_records_list.iteration}][module]" value="{$RELMODULE_NAME}"/>
                            <input type="hidden" name="relatedblockslists[{$BLOCKID}][{$smarty.foreach.related_records_list.iteration}][recordId]" value="{$RELATED_RECORD_MODEL->getId()}"/>
                            {foreach item=FIELD_MODEL from=$FIELDS_LIST name=fields_list_data}
                                {assign var=LAST_FIELD value=$FIELD_MODEL@last}
                                {assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue',$RELATED_RECORD_MODEL->get($FIELD_MODEL->getFieldName()))}
                                {if $FIELD_MODEL->isEditable() eq 'true'}
                                    <td class="fieldValue">
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$RELMODULE_NAME) BLOCK_FIELDS=$RELATED_RECORDS MODULE=$RELMODULE_NAME}
                                        {*{if $smarty.foreach.fields_list_data.iteration eq $FIELDS_LIST|count}
                                            <div class="actions pull-right" style="padding-top:7px; padding-right:10px;">
                                                &nbsp;<a class="relatedBtnDelete" data-record-id="{$RELATED_RECORD_MODEL->getId()}" data-rel-module="{$RELMODULE_NAME}"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
                                            </div>
                                        {/if}*}
                                    </td>
                                {/if}
                            {/foreach}

                            <td>
                                <div class="actions pull-right" style="padding-top:7px; padding-right:10px;">
                                    &nbsp;<a class="relatedBtnDelete" data-record-id="{$RELATED_RECORD_MODEL->getId()}" data-rel-module="{$RELMODULE_NAME}"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {/if}
            {*<br>*}
            <div class="row-fluid">
                <div style="text-align: center">
                    <button type="button" class="btn btn-success relatedBtnAddMore" data-rel-module="{$RELMODULE_NAME}" data-block-id="{$BLOCKID}" data-type="{$BLOCKDATA['type']}" style="padding: 0px 10px;{if $BLOCKDATA['type'] eq 'block'}margin-top:-43px;{/if}"><strong>{vtranslate('LBL_CREATE_ANOTHER', 'RelatedBlocksLists')}</strong></button>
                </div>
            </div>
        </div>
    {/foreach}
{/strip}