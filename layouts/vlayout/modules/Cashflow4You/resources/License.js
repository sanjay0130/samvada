/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

if (typeof(Cashflow4You_License_Js) == 'undefined') {
    
    Cashflow4You_License_Js = {
	
	editLicense : function($type) {
            var aDeferred = jQuery.Deferred();
            var thisInstance = this;

            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                        'enabled' : true
                }
            });
            
            var license_key = jQuery('#license_key_val').val();
            url = "index.php?module=Cashflow4You&view=IndexAjax&mode=editLicense&type="+$type+"&key="+license_key;

            AppConnector.request(url).then(
                function(data) {
                    var callBackFunction = function(data) {
                            //cache should be empty when modal opened 
                            var form = jQuery('#editLicense');

                            var params = app.validationEngineOptions;
                            params.onValidationComplete = function(form, valid){
                                    if(valid) {
                                            
                                            thisInstance.saveLicenseKey(form,false);
                                            return valid;
                                    }
                            }
                            form.validationEngine(params);
                            form.submit(function(e) {
                                    e.preventDefault();
                            })
                    }

                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                    app.showModalWindow(data,function(data){
                            if(typeof callBackFunction == 'function'){
                                    callBackFunction(data);
                            }
                    }, {'width':'500px'});
                },
                function(error) {
                    //TODO : Handle error
                    aDeferred.reject(error);
                }
            );
            return aDeferred.promise();
	},
	/*
	* Function to Save the CustomLabel Details
	*/
        
	saveLicenseKey : function(form,is_install) {
		var thisInstance = this;
                if (is_install)
                {
                    var licensekey_val = jQuery('#licensekey').val()
                    params = {};
                    params.module = "Cashflow4You";
                    params.licensekey = licensekey_val;
                    params.action = "License";
                    params.mode = "editLicense";
                    params.type = "activate"; 
                    //params.dataType='json';
                
                }
                else
                {
                    if(typeof params == 'undefined' ) {
			params = {};
                    }
                
                    var params = form.serializeFormData();
                }    
		thisInstance.validateLicenseKey(params).then(
			function(data) {
				
                            if (!is_install) 
                            {
                                app.hideModalWindow();
                            
                                var params = {
                                        text: app.vtranslate(data['message'])
                                };
                                thisInstance.showMessage(params);
                            
                                jQuery('#license_key_val').val(data.licensekey);
                                jQuery('#license_key_label').html(data.licensekey);

                                jQuery('#divgroup1').hide();
                                jQuery('#divgroup2').show();
                            }   
                            else
                            {
                                jQuery('#step1').hide();
                                jQuery('#step2').show();
                                
                                jQuery('#steplabel1').removeClass("active");
                                jQuery('#steplabel2').addClass("active");
                            }    
			},
			function(data,err) {
			}
		);
	},
	
	
        saveCustomLabelValues : function(form) {
            var thisInstance = this;
            var params = form.serializeFormData();

            if(typeof params == 'undefined' ) {
                params = {};
            }
            
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });

            params.module = app.getModuleName();
            params.action = 'IndexAjax';
            params.mode = 'SaveCustomLabelValues';
            
            AppConnector.request(params).then(
                function(data) {
                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                    app.hideModalWindow();
                    
                    var params = {
                            text: app.vtranslate(data)
                    };
                    thisInstance.showMessage(params);
                }
            );
			
	},

	validateLicenseKey : function(data) {
            var thisInstance = this;
            var aDeferred = jQuery.Deferred();

            var form = jQuery('#editLicense');
            var CustomLabelElement = form.find('[name="licensekey"]');
                thisInstance.checkLicenseKey(data).then(
                    function(data){
                        aDeferred.resolve(data);
                    },
                    function(data, err){
                        CustomLabelElement.validationEngine('showPrompt', data['message'] , 'error','bottomLeft',true);
                        aDeferred.reject(data);
                    }
                );

            return aDeferred.promise();
            
	},
	
	/*
	 * Function to check Duplication of Tax Name
	 */
	
    checkLicenseKey : function(params) {
		var aDeferred = jQuery.Deferred();
                /*
                 var progressIndicatorElement = jQuery.progressIndicator({
                    'position' : 'html',
                    'blockInfo' : {
                            'enabled' : true
                    }
                });
                */
		AppConnector.request(params).then(
			function(data) {
                                var response = data['result'];
                                var result = response['success'];
  				if(result == true) {
                                    aDeferred.resolve(response);
				} else {
					
                                    aDeferred.reject(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
        
                //progressIndicatorElement.progressIndicator({'mode':'hide'});
		return aDeferred.promise();
	},
	
	registerActions : function() {
		
            var thisInstance = this;
		var container = jQuery('#LicenseContainer');

                jQuery('#activate_license_btn').click(function(e) {
			thisInstance.editLicense('activate');
  		});
            
                jQuery('#reactivate_license_btn').click(function(e) {
			thisInstance.editLicense('reactivate');
  		});
                
                jQuery('#deactivate_license_btn').click(function(e) {
			thisInstance.deactivateLicense();
  		});
                

	},

        deactivateLicense: function() {
 
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                        'enabled' : true
                }
            });
 
            
            var license_key = jQuery('#license_key_val').val();
            var deactivateActionUrl = 'index.php?module=Cashflow4You&action=License&mode=deactivateLicense&key='+license_key;

            AppConnector.request(deactivateActionUrl + '&type=control').then(
                            function(data) {
                                if (data.success == true) {
                                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                                    if (data.result.success)
                                    {
                                        var message = app.vtranslate('LBL_DEACTIVATE_QUESTION','Cashflow4You');
                                        Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(function(data) {

                                            var progressIndicatorElement = jQuery.progressIndicator({
                                                'position' : 'html',
                                                'blockInfo' : {
                                                        'enabled' : true
                                                }
                                            });

                                            AppConnector.request(deactivateActionUrl).then(
                                            function(data2) {
                                                  
                                                if (data2.result.success == true) {
                                                    var params2 = {
                                                    text: data2.result.deactivate,
                                                    type: 'info'
                                                    };
                                                    
                                                    jQuery('#license_key_val').val("");
                                                    jQuery('#license_key_label').html("");
                                                    
                                                    jQuery('#divgroup1').show();
                                                    jQuery('#divgroup2').hide();
                                                } else {
                                                    var params2 = {
                                                    title : app.vtranslate('JS_ERROR'),
                                                    text: data2.result.deactivate,
                                                    type: 'error'
                                                    };
                                                }
                                                progressIndicatorElement.progressIndicator({'mode':'hide'});
                                                Vtiger_Helper_Js.showMessage(params2);
                                            });
                                        },
                                            function(error, err) {
                                                progressIndicatorElement.progressIndicator({'mode':'hide'});
                                            }
                                        );
                                    }
                                    else
                                    {    
                                        var params = {
                                        title : app.vtranslate('JS_ERROR'),
                                        text: data.result.deactivate,
                                        type: 'error'
                                        };
                                        Vtiger_Helper_Js.showMessage(params);
                                    }
                                    
                                    
                                    
                                    
                                } else {
                                    progressIndicatorElement.progressIndicator({'mode':'hide'});
                                    Vtiger_Helper_Js.showPnotify(data.error.message);
                                }
                            });
	},
        
	registerEvents: function() {
		this.registerActions();
	},
        
        registerInstallEvents: function() {
            var thisInstance = this;

            this.registerInstallActions();

            var form = jQuery('#editLicense');
            var params = app.validationEngineOptions;

            params.onValidationComplete = function(form, valid){

                if(valid) {
                    thisInstance.saveLicenseKey(form,true);
                    return valid;
                }
            }
            form.validationEngine(params);
            form.submit(function(e) {
                    e.preventDefault();
            })
	},
        
        registerInstallActions : function() {
		
            var thisInstance = this;

            jQuery('#next_button').click(function(e) {
                    window.location.href = "index.php?module=Cashflow4You&view=List";
            });
            
            
	},
        
        showMessage : function(customParams){
		var params = {};
		params.animation = "show";
		params.type = 'info';
		params.title = app.vtranslate('JS_MESSAGE');
		
		if(typeof customParams != 'undefined') {
			var params = jQuery.extend(params,customParams);
		}
		Vtiger_Helper_Js.showPnotify(params);
	}
}

}