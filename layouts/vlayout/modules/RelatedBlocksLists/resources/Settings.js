/* ********************************************************************************
 * The content of this file is subject to the Related Blocks & Lists ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
jQuery.Class("RelatedBlocksLists_Settings_Js",{
    instance:false,
    getInstance: function(){
        if(RelatedBlocksLists_Settings_Js.instance == false){
            var instance = new RelatedBlocksLists_Settings_Js();
            RelatedBlocksLists_Settings_Js.instance = instance;
            return instance;
        }
        return RelatedBlocksLists_Settings_Js.instance;
    }
},{
    registerEnableModuleEvent:function() {
        jQuery('.summaryWidgetContainer').find('#enable_module').change(function(e) {
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });

            var element=e.currentTarget;
            var value=0;
            var text="Related Blocks & Lists Disabled";
            if(element.checked) {
                value=1;
                text = "Related Blocks & Lists Enabled";
            }
            var params = {};
            params.action = 'ActionAjax';
            params.module = 'RelatedBlocksLists';
            params.value = value;
            params.mode = 'enableModule';
            AppConnector.request(params).then(
                function(data){
                    progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    var params = {};
                    params['text'] = text;
                    Settings_Vtiger_Index_Js.showMessage(params);
                },
                function(error){
                    //TODO : Handle error
                    progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                }
            );
        });
    },
    registerEvents: function(){
        this.registerEnableModuleEvent();
    }
});