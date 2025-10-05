/* * *******************************************************************************
 * The content of this file is subject to the Descriptions 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

if (typeof(Cashflow4You_Uninstall_Func_Js) == 'undefined') {
    var Cashflow4You_Uninstall_Func_Js = {
        uninstallCashflow4You: function() {
            var message = app.vtranslate('LBL_UNINSTALL_CONFIRM','Cashflow4You');
            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function(data) {
                    var progressIndicatorElement = jQuery.progressIndicator({
                        'position' : 'html',
                        'blockInfo' : {
                                'enabled' : true
                        }
                    });
                    AppConnector.request('index.php?module=Cashflow4You&action=UninstallCashflow4You').then(
                    function(data) {
                        if (data.result.success == true) {
                            var params = {
                            text:  app.vtranslate('JS_ITEMS_DELETED_SUCCESSFULLY'),
                            type: 'info'
                            };
                            window.location.href = "index.php";
                        } else {
                            var params = {
                            title : app.vtranslate('JS_ERROR'),
                            type: 'error'
                            };
                        }
                        progressIndicatorElement.progressIndicator({'mode':'hide'});
                        params.animation = "show";
                        Vtiger_Helper_Js.showPnotify(params);
                    });
                },
                function(error, err) {
                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                }
            );             
	},
        registerEvents: function() {
		this.registerActions();
	},        
        registerActions : function() {
            var thisInstance = this;
		var container = jQuery('#UninstallCashflow4You');
                jQuery('#uninstall_cashflow4you_btn').click(function(e) {
			thisInstance.uninstallCashflow4You();
  		});
	}
    }
}
jQuery(document).ready(function() {
	Cashflow4You_Uninstall_Func_Js.registerEvents();
});