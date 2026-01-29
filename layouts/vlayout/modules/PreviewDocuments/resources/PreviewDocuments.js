jQuery(document).ready(function(){

    var current_url = jQuery.url();
    var module = current_url.param('module');
    var view = current_url.param('view');
    var action = current_url.param('action');

    if(module=='Documents' && view=='List') {
        jQuery('.listViewEntriesDiv').append(getPreviewDocumentsModalHTML());
        createPreviewLinks('ListView');
        var listViewInstance = Vtiger_List_Js.getInstance();
        var listViewContentDiv = listViewInstance.getListViewContentContainer();
        listViewContentDiv.on('click','.preview_link',function(e){
            e.stopPropagation();
        });
    }

    if(module=='Documents' && view=='Popup') {
        if(jQuery('#document_preview_modal').length==0)
            jQuery('#popupContents').append(getPreviewDocumentsModalHTML());

        createPreviewLinks('Popup');

        jQuery('.relatedContents').on('click','.preview_link',function(e){
            e.stopPropagation();
        });
    }

});

jQuery(document).ajaxComplete(function() {
    var current_url = jQuery.url();
    var module = current_url.param('module');
    var view = current_url.param('view');
    var action = current_url.param('action');
    var relatedModule = current_url.param('relatedModule');

    if(module=='Documents' && view=='List') {
        if(jQuery('#document_preview_modal').length==0)
            jQuery('.listViewEntriesDiv').append(getPreviewDocumentsModalHTML());

        createPreviewLinks('ListView');
    }

    if(relatedModule=='Documents') {
        jQuery('.relatedContents').on('click','.preview_link',function(e){
            e.stopPropagation();
        });
        createPreviewLinks('RelatedList');

        if(jQuery('#document_preview_modal').length==0)
            jQuery('.relatedContainer').append(getPreviewDocumentsModalHTML());
    }

    if(module=='Documents' && view=='Popup') {

        if(jQuery('#document_preview_modal').length==0)
            jQuery('#popupContents').append(getPreviewDocumentsModalHTML());

        createPreviewLinks('Popup');

        jQuery('.relatedContents').on('click','.preview_link',function(e){
            e.stopPropagation();
        });
    }

});

function createPreviewLinks(mode) {
    switch (mode) {
        case 'ListView':
            jQuery('.listViewEntries').each(function(){
                var document_id = jQuery(this).data('id');
                if(jQuery(this).find('i.icon-search').length == 0) {
                    var preview_link = "<a class=\"preview_link\" href=\"javascript: void(0);\" onclick=\"previewDoc("+document_id+")\"><i title=\"Preview\" class=\"icon-search alignMiddle\"></i></a>";
                    jQuery(this).find('.actionImages').prepend(preview_link);
                }
            });
        break;
        case 'RelatedList':
            jQuery('.relatedContents .listViewEntries').each(function(){
                var document_id = jQuery(this).data('id');
                if(jQuery(this).find('i.icon-search').length == 0) {
                    var preview_link = "<a class=\"preview_link\" href=\"javascript: void(0);\" onclick=\"previewDoc("+document_id+")\"><i title=\"Preview\" class=\"icon-search alignMiddle\"></i></a>";
                    jQuery(this).find('.actionImages').prepend(preview_link);
                }
            });
        break;
        case 'Popup':
            if(jQuery('.additional_td').length == 0) {
                jQuery('.relatedContents .listViewHeaders').each(function(){
                     jQuery(this).append('<th class="medium additional_td">&nbsp;</th>');
                });
            }
            jQuery('.relatedContents .listViewEntries').each(function(){
                var document_id = jQuery(this).data('id');
                if(jQuery(this).find('i.icon-search').length == 0) {
                    var new_td = '<td nowrap="" class="medium"><div class="actions pull-right"><span class="actionImages"><a onclick="previewDoc('+document_id+')" href="javascript: void(0);" class="preview_link"><i class="icon-search alignMiddle" title="Preview"></i></span></div></td>'
                    jQuery(this).append(new_td);
                }
            });
        break;
    }
}

function getPreviewDocumentsModalHTML() {
    var html = '<div class="modal fade" id="document_preview_modal" style="width: auto!important; height: auto!important;">'+
        '<div class="modal-dialog">'+
        '<div class="modal-content">'+
        '<div class="modal-header">'
        +'<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>'
        +'<h4 class="modal-title" id="document_preview_title">Document Preview</h4></div>'+
        '<div class="modal-body"></div></div></div></div>';
    return html;
}

function previewDoc(doc_id) {

    var actionParams = {
        "action":"Search",
        "module": "PreviewDocuments",
        "doc_id": doc_id
    };
    AppConnector.request(actionParams).then(
        function(data) {
            if(data) {
                if(data.result.type!=null && data.result.link!=null) {
                    if(data.result.type=='image') {
                        preview_iframe = '<img src="' + data.result.link + '" width="500px;"></img>';
                    } else if(data.result.type=='html') { // haph86@gmail.com - #8362 - 05052015
                        window.open(data.result.link, "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, width=700, height=500");
                        return;
                    } else {
                        if(window.location.protocol=='http:')
                            preview_iframe = '<iframe src="http://docs.google.com/viewer?url='+data.result.link+'&embedded=true" width="700" height="500" style="border: none;"></iframe>';
                        else if(window.location.protocol=='https:')
                            preview_iframe = '<iframe src="https://docs.google.com/viewer?url='+data.result.link+'&embedded=true" width="700" height="500" style="border: none;"></iframe>';
                    }

                    jQuery('#document_preview_modal .modal-body').html(preview_iframe);
                    if(data.result.name!=null) {
                        jQuery('#document_preview_modal #document_preview_title').html(data.result.name);
                    } else {
                        jQuery('#document_preview_modal #document_preview_title').html("Document Preview");
                    }
                    jQuery('#document_preview_modal').width('auto').css('z-index',999999999999);
                    jQuery('#document_preview_modal').modal('show');
                    return;
                } else {
                    jQuery('#document_preview_modal').width('500px');
                    jQuery('#document_preview_modal .modal-body').html('');
                    jQuery('#document_preview_modal #document_preview_title').html("No File");
                    jQuery('#document_preview_modal').modal('show');
                }
            }
        },
        function(error,err){

        }
    );

}

