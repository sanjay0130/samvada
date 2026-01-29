{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    <div class="recentActivitiesContainer" id="updates">
        <input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
        <div class='history'>
            {if !empty($RECENT_ACTIVITIES)}
                <ul class="updates_timeline">
                    {foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES}
                        {assign var=PROCEED value= TRUE}
                        {if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
                            {assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
                            {if !($RELATION->getLinkedRecord())}
                                {assign var=PROCEED value= FALSE}
                            {/if}
                        {/if}
                        {if $PROCEED}
                            {if $RECENT_ACTIVITY->isCreate()}
                                <li class="activity-item">
                                    <time class="update_time cursorDefault">
                                        <small
                                            title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getParent()->get('createdtime'))}">
                                            {Vtiger_Datetime_UIType::getDisplayDateTimeValue($RECENT_ACTIVITY->getParent()->get('createdtime'))}
                                        </small>
                                    </time>
                                    {assign var=USER_MODEL value=$RECENT_ACTIVITY->getModifiedBy()}
                                    {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                    {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].url eq ''}
                                        <div class="update_icon bg-info">
                                            <i class='update_image vicon-vtigeruser'></i>
                                        </div>
                                    {else}
                                        {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                            {if !empty($IMAGE_INFO.url)}
                                                <div class="update_icon">
                                                    <img class="update_image" src="{$IMAGE_INFO.url}">
                                                </div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                    <div class="update_info">
                                        <h5>
                                            <span class="field-name">{$RECENT_ACTIVITY->getModifiedBy()->getName()}</span>&nbsp;
                                            {vtranslate('LBL_CREATED', $MODULE_NAME)}
                                        </h5>
                                    </div>
                                </li>
                            {else if $RECENT_ACTIVITY->isUpdate()}
                                <li class="activity-item">
                                    <time class="update_time cursorDefault">
                                        <small
                                            title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getActivityTime())}">
                                            {Vtiger_Datetime_UIType::getDisplayDateTimeValue($RECENT_ACTIVITY->getActivityTime())}
                                        </small>
                                    </time>
                                    {assign var=USER_MODEL value=$RECENT_ACTIVITY->getModifiedBy()}
                                    {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                    {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].url eq ''}
                                        <div class="update_icon bg-info">
                                            <i class='update_image vicon-vtigeruser'></i>
                                        </div>
                                    {else}
                                        {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                            {if !empty($IMAGE_INFO.url)}
                                                <div class="update_icon">
                                                    <img class="update_image" src="{$IMAGE_INFO.url}">
                                                </div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                    <div class="update_info">
                                        <div>
                                            <h5>
                                                <span class="field-name">{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()} </span>
                                                {vtranslate('LBL_UPDATED', $MODULE_NAME)}
                                            </h5>
                                        </div>
                                        {foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}

                                            {assign var=FIELD_NAME value=$FIELDMODEL->getFieldInstance()->getName()}
                                            {assign var=FIELD_DATA_TYPE value=$FIELDMODEL->getFieldInstance()->getFieldDataType()}
                                            {assign var=PRE_DISPLAY_VALUE value=$FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue')))}
                                            {assign var=POST_DISPLAY_VALUE value=$FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue')))}
                                            {assign var=TIME_PRE_DISPLAY_VALUE value=$FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue')))}
                                            {assign var=TIME_POST_DISPLAY_VALUE value=$FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue')))}

                                            {if in_array($FIELD_NAME,array('time_start','time_end')) && in_array($MODULE_NAME,array('Events','Calendar'))}
                                                {assign var=CALENDAR_RECORD_MODEL value =Vtiger_Record_Model::getInstanceById($RECORD_ID)}
                                                {assign var=TIME_PRE_DISPLAY_VALUE value={Calendar_Time_UIType::getModTrackerDisplayValue($FIELD_NAME,$FIELDMODEL->get('prevalue'),$CALENDAR_RECORD_MODEL)}}
                                                {assign var=TIME_POST_DISPLAY_VALUE value={Calendar_Time_UIType::getModTrackerDisplayValue($FIELD_NAME,$FIELDMODEL->get('postvalue'),$CALENDAR_RECORD_MODEL)}}
                                                {assign var=PRE_DISPLAY_VALUE value=$TIME_PRE_DISPLAY_VALUE}
                                                {assign var=POST_DISPLAY_VALUE value=$TIME_POST_DISPLAY_VALUE}
                                            {/if}
                                            {if isset($TIME_PRE_DISPLAY_VALUE)}
                                                {assign var=PRE_DISPLAY_TITLE value=$TIME_PRE_DISPLAY_VALUE}

                                            {else}
                                                {assign var=PRE_DISPLAY_TITLE value=''}
                                            {/if}



                                            {if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
                                                <div class='font-x-small updateInfoContainer textOverflowEllipsis'>
                                                    <div class='update-name'><span
                                                            class="field-name">{vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}</span>
                                                        {if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
                                                            <span> &nbsp;{vtranslate('LBL_CHANGED')}</span>
                                                        </div>
                                                        <div class='update-from'><span class="field-name">{vtranslate('LBL_FROM')}</span>&nbsp;
                                                            <em style="white-space:pre-line;"
                                                                title="{strip_tags({Vtiger_Util_Helper::toVtiger6SafeHTML($PRE_DISPLAY_TITLE)})}">{Vtiger_Util_Helper::toVtiger6SafeHTML($PRE_DISPLAY_VALUE)}</em>
                                                        </div>
                                                    {else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                        &nbsp;(<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($PRE_DISPLAY_VALUE)})</del> )
                                                        {vtranslate('LBL_IS_REMOVED')}
                                                    </div>
                                                {else if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                    &nbsp;{vtranslate('LBL_UPDATED')}
                                                </div>
                                            {else}
                                                &nbsp;{vtranslate('LBL_CHANGED')}
                                    </div>
                                {/if}
                                {if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                    <div class="update-to"><span class="field-name">{vtranslate('LBL_TO')}</span>&nbsp;<em
                                            style="white-space:pre-line;">{Vtiger_Util_Helper::toVtiger6SafeHTML($POST_DISPLAY_VALUE)}</em>
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    {/foreach}
                    </div>
                    </li>

                {else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
                    {assign var=RELATED_MODULE value= $RELATION->getLinkedRecord()->getModuleName()}


                    {literal}
                        <style>
                            /* Style for each activity list item */
                            li.activity-item {
                                border: 1px solid #e5e5e5;
                                border-radius: 6px;
                                padding: 10px 12px;
                                margin-bottom: 15px;
                                /* 👈 Adds vertical space between li elements */
                                background-color: #fafafa;
                                list-style: none;
                            }

                            .update_icon {
                                display: inline-block;
                                margin-right: 10px;
                                vertical-align: middle;
                            }

                            .update_info {
                                display: inline-block;
                                vertical-align: middle;
                                width: calc(100% - 60px);
                            }

                            .update_time small {
                                color: #888;
                            }
                        </style>
                    {/literal}

                    <li class="activity-item">
                        {assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
                        {assign var=RELATED_RECORD value=$RELATION->getLinkedRecord()}
                        {assign var=RELATED_MODULE value=$RELATED_MODULE|capitalize}

                        {* Fallback-safe user who did the action *}
                        {assign var=USER_ID value=$RECENT_ACTIVITY->get('whodid')}
                        {if !$USER_ID}
                            {assign var=USER_ID value=$RELATION->get('whodid')}
                        {/if}
                        {assign var=USER_NAME value=Vtiger_Functions::getOwnerRecordLabel($USER_ID)}

                        {* Date & Time of change *}
                        <time class="update_time cursorDefault">
                            <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RELATION->get('changedon'))}">
                                {Vtiger_Datetime_UIType::getDisplayDateTimeValue($RELATION->get('changedon'))}
                            </small>
                        </time>

                        {* Module icon *}
                        <div class="update_icon bg-info-{$RELATED_MODULE|lower}">
                            {if $RELATED_MODULE|lower eq 'modcomments'}
                                <i class="update_image vicon-chat"></i>
                            {else}
                                <span class="update_image">{Vtiger_Module_Model::getModuleIconPath($RELATED_MODULE)}</span>
                            {/if}
                        </div>

                        {* Readable sentence *}
                        <div class="update_info">

                            <h5 class="mb-1">
                                <span style="color:#007bff;font-weight:600;">
                                    {$USER_NAME|default:'Unknown User'}
                                </span> &nbsp;

                                {if $RECENT_ACTIVITY->isRelationLink()}
                                    {vtranslate('LBL_LINKED_FORMAT', $MODULE_NAME)}
                                {else}
                                    {vtranslate('LBL_UNLINKED_FORMATTED', $MODULE_NAME)}
                                {/if}
                                <strong>
                                    &nbsp;


                                    {if $RELATED_MODULE == 'Calendar'}


                                        {$summary = $RELATION->getCalendarActivityUpdateSummary($RELATION->getLinkedRecord()->getId())}
                                        {* Inside your record loop *}
                                        {assign var=activityType value=strtolower($summary.activityType)}

                                        {if $activityType == 'call'}
                                            {assign var=bgColor value='#abff9e'} {* Green *}
                                        {elseif $activityType == 'meeting'}
                                            {assign var=bgColor value='#ff9696'} {* Teal *}
                                        {elseif $activityType == 'task'}
                                            {assign var=bgColor value='#ffc107'} {* Yellow *}
                                        {elseif $activityType == 'email'}
                                            {assign var=bgColor value='#6f42c1'} {* Purple *}
                                        {else}
                                            {assign var=bgColor value='#ffcb8c'} {* Default Blue *}
                                        {/if}


                                        <i>"{$summary.subject}" on {$summary.startDateTime} </i>
                                        &nbsp;&nbsp;
                                        <span style="background-color: {$bgColor};">
                                            {$activityType|capitalize}
                                        </span>

                                    {else}

                                        <span style="color:#28a745;font-weight:600;">
                                            {vtranslate($RELATED_MODULE, $RELATED_MODULE)}
                                        </span>
                                        &nbsp;
                                        {if $RELATED_RECORD && $RELATED_RECORD->getId() neq ''}
                                            {assign var=DETAILVIEW_URL value=$RELATION->getRecordDetailViewUrl()}
                                            {if $DETAILVIEW_URL}
                                                <a href="{$DETAILVIEW_URL}">
                                                    <i>{$RELATED_RECORD->getName()}</i>
                                                </a>
                                            {else}
                                                <i>{$RELATED_RECORD->getName()}</i>
                                            {/if}
                                        {else}
                                            <strong>Record deleted</strong>
                                        {/if}

                                    {/if}
                                </strong>


                            </h5>

                            {*
        <div class="font-x-small updateInfoContainer textOverflowEllipsis">
            {if $RELATED_RECORD && $RELATED_RECORD->getId() neq ''}
                {assign var=DETAILVIEW_URL value=$RELATION->getRecordDetailViewUrl()}
                {if $DETAILVIEW_URL}
                    <a href="{$DETAILVIEW_URL}">
                        <strong>{$RELATED_RECORD->getName()}</strong>
                    </a>
                {else}
                    <strong>{$RELATED_RECORD->getName()}</strong>
                {/if}
            {else}
                <strong>Record deleted</strong>
            {/if}
        </div>
        *}
                        </div>
                    </li>










                {else if $RECENT_ACTIVITY->isRestore()}
                {/if}
            {/if}
        {/foreach}
        {if $PAGING_MODEL->isNextPageExists()}
            <li id='more_button'>
                <div class='update_icon' id="moreLink">
                    <button type="button" class="btn btn-success moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</button>
                </div>
            </li>
        {/if}
        </ul>
    {else}
        <div class="summaryWidgetContainer">
            <p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
        </div>
    {/if}
    </div>
    </div>
{/strip}