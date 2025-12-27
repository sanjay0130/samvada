function openAddnewPaymentHandler(){

    if ($('#menubar_quickCreate_BoruPayments').length>0){
        record_id = $('input[name=record_id]').val();
        dataurl='';
        relatedcontact=0;
        relatedorganization=0
        amount='';
        var detailInstance = new Vtiger_Detail_Js();
        if (record_id>0){
            app.helper.showProgress();

            var referenceModuleName = 'BoruPayments';

            var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="BoruPayments"]');
            var recordId = record_id
            var module = app.getModuleName();
            var element = jQuery('#menubar_quickCreate_BoruPayments');

            if(quickCreateNode.length <= 0) {
                app.helper.showErrorMessage(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'));
            }
            if (module=='SalesOrder')
                fieldName='salesorderid';
            else
                fieldName = 'invoiceid';

            var customParams = {};
            customParams[fieldName] = recordId;
            customParams['parentModule'] = module;

            app.event.on("post.QuickCreateForm.show",function(event,form){
                jQuery('<input type="hidden" name="sourceModule" value="'+module+'" >').appendTo(form);
                jQuery('<input type="hidden" name="sourceRecord" value="'+recordId+'" >').appendTo(form);
                jQuery('<input type="hidden" name="relationOperation" value="true" >').appendTo(form);
                jQuery('<input type="hidden" name="'+fieldName+'" value="'+recordId+'" >').appendTo(form);
            });

            app.event.on('post.QuickCreateForm.save',function(event,data){
                var idList = new Array();
                idList.push(data._recordId);
                detailInstance.addRelationBetweenRecords(referenceModuleName,idList).then(function(data){
                });
            });

            var QuickCreateParams = {};
            QuickCreateParams['noCache'] = false;
            QuickCreateParams['data'] = customParams;
            quickCreateNode.trigger('click', QuickCreateParams);


        }else{
            $('#menubar_quickCreate_BoruPayments').click();
            if (dataurl!='')
                $('#menubar_quickCreate_BoruPayments').attr('data-url',dataurl);
        }


    }
}
jQuery(document).ready(function(){
    if (_META.view=='Detail'){
        module = _META.module;
        // open=false;
        if (module=='Invoice'){
            leftobj = jQuery('.module-action-content div.module-breadcrumb');
            rightobj = jQuery('.module-action-content div.pull-right');
            leftobj.removeClass('col-lg-7');
            leftobj.removeClass('col-md-7');
            rightobj.removeClass('col-lg-5');
            rightobj.removeClass('col-md-5');

            rightobj.addClass('col-lg-6 col-md-6');
            leftobj.addClass('col-lg-6 col-md-6');

            html = '<li><button style="background: #5cb85c;color: #FFF;" id="openAddnewPayment" onclick="openAddnewPaymentHandler()" type="button" class="btn btn-success module-buttons"><i class="fas fa-sync"></i>Add Payment</button></li>';
            // jQuery('#appnav ul').append(html);
        }
    }
});