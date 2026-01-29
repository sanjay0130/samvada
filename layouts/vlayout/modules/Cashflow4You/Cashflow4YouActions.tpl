{*
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
*}
<style type="text/css">
    .divTable
    {
        display:  table;
        width:100%;
        padding-right: 5px;
        padding-left: 5px;
    }

    .divRow
    {
       display:table-row;
       width:auto;
    }

    .divCell
    {
        float:left;/*fix for  buggy browsers*/
        display:table-column;
        width:auto;
        padding-top: 5px;
       padding-bottom: 5px;    
    }
</style>
<div class="row-fluid">
	
                    <table border="0" cellpadding="5" cellspacing="0" width="100%">
                        {if $PAYTYPE == 'loadPayments' }
                            TO DO LOADPAYMENTS
                        {else}
                            <tr>

                                <td style="border-bottom:1px solid #dedede; padding-left:15px;" colspan="2">
                                    <b>{vtranslate('Grand Total', 'Cashflow4You')}</b>
                                </td>
                                <td style="border-bottom:1px solid #dedede;" align='right' nowrap >
                                    <b>{$GRAND_TOTAL} {$GTOTAL_CURRENCY}</b>
                                </td>
                           </tr>
                            {foreach from="$PAYMENTS" item="PAYMENT" key="PAYMENT_ID"}
                            <tr>
                                {*<td style="border-bottom:1px dashed #dedede;" >
                                     <a href='index.php?module=Cashflow4You&view=Detail&record={$PAYMENT_ID}&return_module=Cashflow4You'>
                                               <small>{$PAYMENT.no}</small>
                                    </a>
                                </td>*}
                                <td style="border-bottom:1px dashed #dedede;" nowrap>
                                    <a href='index.php?module=Cashflow4You&view=Detail&record={$PAYMENT_ID}&return_module=Cashflow4You'>
                                               <small>{vtranslate($PAYMENT.paymentstatus, 'Cashflow4You')}</small>
                                    </a>
                                </td>
                                <td style="border-bottom:1px dashed #dedede;" nowrap>
                                    <a href='index.php?module=Cashflow4You&view=Detail&record={$PAYMENT_ID}&return_module=Cashflow4You'>
                                                <small>{$PAYMENT.paymentdate}</small>
                                    </a>
                                </td>
                                <td style="border-bottom:1px dashed #dedede;" align='right' nowrap>
                                    <a href='index.php?module=Cashflow4You&view=Detail&record={$PAYMENT_ID}&return_module=Cashflow4You'>
                                                       <small> {$PAYMENT.paymentamount} {$PAYMENT.currency}</small>
                                    </a>
                                </td>
                            </tr>
                            {/foreach}
                            <tr>
                                <td style="border-top:1px solid #dedede; padding-left:15px;" colspan="2">
                                    <b>{vtranslate('Total amount', 'Cashflow4You')}</b>
                                    <b>{vtranslate('Paid', 'Accounts')}</b>
                                </td>
                                <td style="border-top:1px solid #dedede;" align='right' nowrap>
                                    <b>{$TOTAL} {$TOTAL_CURRENCY}</b>
                                </td>
                           </tr>
                            <tr>
                                <td style=" padding-left:15px;" colspan="2">
                                    <b>{vtranslate('Balance', 'Cashflow4You')}</b>
                                </td>
                                <td style="" align='right' nowrap>
                                    <b>{$TOTAL_BALLANCE} {$TOTAL_CURRENCY}</b>
                                </td>
                           </tr>
                            
                        {/if}
                   </table>
                   <br />
                    <div align='center'><a href="index.php?module=Cashflow4You&view=Edit&relationid={$RECORD}&sourceModule={$RELATION_MODULE}&relationOperation=1&sourceRecord={$RECORD}">
                            <button class="btn addButton"> <b>{vtranslate('Create_Payment', 'Cashflow4You')}</b></button></a></div>
                            <br />


 	<div id="alert_doc_title" style="display:none;">{$CASHFLOW4YOU_MOD.ALERT_DOC_TITLE}</div>
</div>
