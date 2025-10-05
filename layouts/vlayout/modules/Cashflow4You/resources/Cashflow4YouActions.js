/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
if (typeof(Cashflow4You_Actions_Js) == 'undefined') {
    Cashflow4You_Actions_Js = {
    
        CreatePayment: function(createUrl) {
            var beforeShowCb = {'width':'65%'};
            var thisInstance = this;
            //TODO : Make the paramters as an object
            if(typeof beforeShowCb == 'undefined') {
                    beforeShowCb = function(){return true;};
            }

            if(typeof beforeShowCb == 'object') {
                    css = beforeShowCb;
                    beforeShowCb = function(){return true;};
            }
            var listInstance = Vtiger_List_Js.getInstance();
            var validationResult = listInstance.checkListRecordSelected();
            if(validationResult != true){
            // Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
            var selectedIds = listInstance.readSelectedIds(true);
            var excludedIds = listInstance.readExcludedIds(true);
            var cvId = listInstance.getCurrentCvId();
            var postData = {
                    "viewname" : cvId,
                    "selected_ids":selectedIds,
                    "excluded_ids" : excludedIds
            };

            var listViewInstance = Vtiger_List_Js.getInstance();
            var searchValue = listViewInstance.getAlphabetSearchValue();

            if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
                postData['search_key'] = listViewInstance.getAlphabetSearchField();
                postData['search_value'] = searchValue;
                postData['operator'] = "s";
            }

            var actionParams = {
                    "type":"POST",
                    "url":createUrl,
                    "dataType":"html",
                    "data" : postData
            };

            if(typeof css == 'undefined'){
                    css = {};
            }
            var css = jQuery.extend({'text-align' : 'left'},css);

            AppConnector.request(actionParams).then(
                    function(data) {
                        resp = data.split('|||###|||');
                        if( resp[0] != "0" )
                        {
                          alert(resp[1]);
                        }
                        else
                        {
                            data = resp[1];
                            if(data) {
                                    var result = beforeShowCb(data);
                                    if(!result) {
                                            return;
                                    }
                                    app.showModalWindow(data,/*function(data){
                                            if(typeof callBackFunction == 'function'){
                                                    callBackFunction(data);
                                                    //listInstance.triggerDisplayTypeEvent();
                                            }*/
                                        function(data){
                                            var massEditForm = data.find('#createPayment');
                                            massEditForm.validationEngine(app.validationEngineOptions);
                                            var listInstance = Vtiger_List_Js.getInstance();
                                            listInstance.inactiveFieldValidation(massEditForm);
                                            listInstance.registerReferenceFieldsForValidation(massEditForm);
                                            listInstance.registerFieldsForValidation(massEditForm);
                                            listInstance.registerEventForTabClick(massEditForm);
                                            listInstance.registerRecordAccessCheckEvent(massEditForm);
                                            var editInstance = Vtiger_Edit_Js.getInstance();
                                            editInstance.registerBasicEvents(massEditForm);
                                            //To remove the change happended for select elements due to picklist dependency
                                            data.find('select').trigger('change',{'forceDeSelect':true});
                                            thisInstance.postCEdit(data);

                                            listInstance.registerSlimScrollMassEdit();
                                            jQuery('#Cashflow4You_editView_fieldName_paymentamount').on('change',Cashflow4You_Actions_Js.checkPaymentAmount);
                                    },css)

                            }
                        }
                    },
                    function(error,err){

                    }
                );
            } else {
                    listInstance.noRecordSelectedAlert();
            }
        },
        
        postCEdit : function(massEditContainer) {
		var thisInstance = this;
		massEditContainer.find('form').on('submit', function(e){
			e.preventDefault();
			var form = jQuery(e.currentTarget);
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length == 0){
				form.find('[name="saveButton"]').attr('disabled',"disabled");
			}
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length > 0){
				return;
			}
			thisInstance.cActionSave(form, true).then(
				function(data) {
                                        var listViewInstance = Vtiger_List_Js.getInstance();
					listViewInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
				},
				function(error,err){
				}
			)
		});
	},
        
        cActionSave : function(form, isMassEdit){
            if(typeof isMassEdit == 'undefined') {
                isMassEdit = false;
            }
            var aDeferred = jQuery.Deferred();
            var massActionUrl = form.serializeFormData();
            if(isMassEdit) {
                //on submit form trigger the massEditPreSave event
                var massEditPreSaveEvent = jQuery.Event(Vtiger_List_Js.massEditPreSave);
                form.trigger(massEditPreSaveEvent);
                if(massEditPreSaveEvent.isDefaultPrevented()) {
                    form.find('[name="saveButton"]').removeAttr('disabled');
                    aDeferred.reject();
                    return aDeferred.promise();
                }
            }
            
            AppConnector.request(massActionUrl).then(
                function(data) {
                    var response = data['result'];
                    var result = response['success'];

                    if(result == true) {
                      //alert(response['message']); 
                      var params = {
                             text: app.vtranslate(response['message']),
                             type: 'info' 
                            };
                      Vtiger_Helper_Js.showPnotify(params);
                    } else {
                      //alert(response['message']); 
                      var params = {
                             text: app.vtranslate(response['message'])
                            };
                      Vtiger_Helper_Js.showPnotify(params);
                    }
                    app.hideModalWindow();
                    aDeferred.resolve(data);
                },
                function(error,err){
                //alert('error');	
                    app.hideModalWindow();
                        aDeferred.reject(error,err);
                }
            );
            return aDeferred.promise();
	},
        
        checkPaymentAmount: function(){
            var idstring=jQuery('#idstring').val();
            var idlist = idstring.split(';');
            var sum_open_amount = eval(jQuery('#summ_openamount_hidden').val());
            var module = jQuery('#module').val();

            if(isNaN(jQuery('#Cashflow4You_editView_fieldName_paymentamount').val())){
              alert(jQuery('#paid_is_nan').html());
            }
            else
            {
              var paid_amount = eval( jQuery('#Cashflow4You_editView_fieldName_paymentamount').val() );
              if(isNaN(paid_amount)){
                alert(jQuery('#paid_is_nan').html());
              }
            }

            if(isNaN(sum_open_amount)){
              alert(jQuery('#sumary_is_nan').html());
            }
            var open_amount = paid_amount;
            var tmp_open_amount = paid_amount;
            var tmp_sum_open_amount = sum_open_amount;
            var sum_payment = 0;
            var sum_outstandingbalance = 0;
            for(var i=0;i<idlist.length;i++){
              //partial_open_amount = eval(jQuery('#openamount_'+idlist[i]).html());
              partial_open_amount = eval(jQuery('#openamount_'+idlist[i]).val());
              j=i+1;
              if(isNaN(partial_open_amount))
              {
                alert(jQuery('#open_amount').html()+" "+j+" "+jQuery('#is_nan').html());
              }
              else 
              {
                //partial_open_amount = partial_open_amount.replace(",",".");
                if( tmp_open_amount <= 0  )
                {
                  payment = 0;
                }
                else if( tmp_open_amount < partial_open_amount )
                {
                  payment = tmp_open_amount;
                  tmp_open_amount = 0;
                }
                else
                {
                  payment = partial_open_amount;
                  tmp_open_amount -= partial_open_amount;
                }
                payment = Math.round(payment*100)/100;
                sum_payment += payment;
                jQuery('#payment_'+idlist[i]).val( payment.toFixed(2) );
                jQuery('#previous_payment_'+idlist[i]).val( payment.toFixed(2) );

                outstandingbalance = payment - partial_open_amount;
                outstandingbalance = Math.round((outstandingbalance)*100)/100;
                if( outstandingbalance.toFixed(2) != 0.00 )
                {
                  color="#FF0000";
                }
                else
                {
                  color="#009900";
                }
                jQuery('#outstandingbalance_'+idlist[i]).html( "<span style='color:"+color+";'>"+ outstandingbalance.toFixed(2)+ "</span>" );
                tmp_sum_open_amount -= payment
                sum_outstandingbalance += outstandingbalance;
              }
            }
            sum_payment = Math.round(sum_payment*100)/100;

            tmp_sum_open_amount = Math.round(tmp_sum_open_amount*100)/100;
            sum_balance_payment = Math.round((paid_amount-sum_payment)*100)/100;
            sum_outstandingbalance = Math.round((sum_outstandingbalance)*100)/100;

            jQuery('#summ_payment').html( sum_payment.toFixed(2) );
            jQuery('#summ_payment_hidden').html( sum_payment.toFixed(2) );

            if( sum_outstandingbalance.toFixed(2) != 0.00 )
            {
              color="#FF0000";
            }
            else
            {
              color="#009900";
            }
            jQuery('#summ_outstandingbalance').html( "<span style='color:"+color+";'>"+ sum_outstandingbalance.toFixed(2)+ "</span>" );
            jQuery('#summ_outstandingbalance_hidden').html( "<span style='color:"+color+";'>"+ sum_outstandingbalance.toFixed(2)+ "</span>" );

            if( tmp_sum_open_amount.toFixed(2) != 0.00 )
            {
              color="#FF0000";
            }
            else
            {
              color="#009900";
            }
            jQuery('#balance_openamount').html("<b><span style='color:"+color+";'>"+ tmp_sum_open_amount.toFixed(2)+ "</span></b>" );

            if( sum_balance_payment.toFixed(2) != 0.00 )
            {
              color="#FF0000";
            }
            else
            {
              color="#009900";
            }
            jQuery('#balance_payment').html("<b><span style='color:"+color+";'>"+ sum_balance_payment.toFixed(2)+ "</span></b>" );
            jQuery('#balance_payment_hidden').val( sum_balance_payment.toFixed(2) );
            paid_amount = Math.round((paid_amount)*100)/100;
            jQuery('#Cashflow4You_editView_fieldName_paymentamount').val( paid_amount.toFixed(2) );
            jQuery('#paymentamount').val( paid_amount.toFixed(2) );
            jQuery('#paymentamount_hidden').val( paid_amount.toFixed(2) );
            if( tmp_sum_open_amount.toFixed(2) != 0)
            {
                jQuery('#vat_amount').val( 0 );
            }
            else
            {
               jQuery('#vat_amount').val( eval(jQuery('#vat_amount_hidden').val()) ); 
            }
          },
		
        checkPayment: function( invid )
        {
          var idstring=jQuery('#idstring').val();
          var idlist = idstring.split(';');
          var paid_amount = eval(jQuery('#paymentamount_hidden').val());
          var sum_open_amount = eval(jQuery('#summ_openamount_hidden').val());

          if(isNaN(paid_amount)){
            alert(jQuery('#paid_is_nan').html());
          }
          if(isNaN(sum_open_amount)){
            alert(jQuery('#sumary_is_nan').html());
          }

          var open_amount = paid_amount;
          var tmp_open_amount = paid_amount;
          var tmp_sum_open_amount = sum_open_amount;
          var sum_payment = 0;
          var sum_outstandingbalance = 0;
          var outstandingbalance_tmp = 0;
          for(var i=0;i<idlist.length;i++)
          {
            j=i+1;
            partial_amount = eval(jQuery('#payment_'+idlist[i]).val());
            previous_partial_amount = eval(jQuery('#previous_payment_'+idlist[i]).val());
            //partial_open_amount = eval(jQuery('#openamount_'+idlist[i]).html());
            partial_open_amount = eval(jQuery('#openamount_'+idlist[i]).val());
            if(isNaN(partial_open_amount))
            {
              alert(jQuery('#open_amount').html()+" "+j+" "+jQuery('#is_nan').html());
            }
            else if(isNaN(partial_amount))
            {
              alert(jQuery('#payment').html()+" "+j+" "+jQuery('#is_nan').html());
            }
            else if( partial_amount > partial_open_amount )
            {
              payment = partial_amount;
              tmp_open_amount -= payment;

              payment = Math.round(payment*100)/100;
              sum_payment += payment;
              outstandingbalance = payment - partial_open_amount;
              outstandingbalance = Math.round((outstandingbalance)*100)/100;
              sum_outstandingbalance += outstandingbalance;
              if( invid == idlist[i] )
              {
                if( confirm(jQuery('#high_payment').html()) )
                {
                  //document.getElementById('payment_'+idlist[i]).value = payment.toFixed(2);
                  //document.getElementById('open_amount_'+idlist[i]).innerHTML = tmp_open_amount.toFixed(2);
                  //document.getElementById('previous_paymentamount_'+idlist[i]).value = payment.toFixed(2);

                  jQuery('#payment_'+idlist[i]).val( payment.toFixed(2) );
                  jQuery('#previous_payment_'+idlist[i]).val( payment.toFixed(2) );

                  if( outstandingbalance.toFixed(2) != 0.00 )
                  {
                    color="#FF0000";
                  }
                  else
                  {
                    color="#009900";
                  }
                  jQuery('#outstandingbalance_'+idlist[i]).html( "<span style='color:"+color+";'>"+ outstandingbalance.toFixed(2)+ "</span>" );
                }
                else
                {
                  payment = previous_partial_amount;
                  //document.getElementById('payment_'+idlist[i]).value = previous_partial_amount.toFixed(2);
                  jQuery('#payment_'+idlist[i]).val( payment.toFixed(2) );

                  tmp_open_amount = Math.round(tmp_partial_open_amount*100)/100;
                  sum_payment -= partial_amount;
                  sum_payment += payment;
                  sum_outstandingbalance -= outstandingbalance;
                  sum_outstandingbalance += payment;
                }
              }
              outstandingbalance_tmp += Math.abs(outstandingbalance.toFixed(2));
              //jQuery('#payment_'+idlist[i]).val( partial_open_amount.toFixed(2) );
              tmp_sum_open_amount -= payment
            }
            else 
            {
              payment = partial_amount;
              tmp_open_amount -= payment;

              payment = Math.round(payment*100)/100;
              sum_payment += payment;
              outstandingbalance = payment - partial_open_amount;
              outstandingbalance = Math.round((outstandingbalance)*100)/100;
              sum_outstandingbalance += outstandingbalance;
              if( invid == idlist[i] )
              {
                jQuery('#payment_'+idlist[i]).val( payment.toFixed(2) );
                if( outstandingbalance.toFixed(2) != 0.00 )
                {
                  color="#FF0000";
                }
                else
                {
                  color="#009900";
                }
                jQuery('#outstandingbalance_'+idlist[i]).html( "<span style='color:"+color+";'>"+ outstandingbalance.toFixed(2)+ "</span>" );
                
              }
              tmp_sum_open_amount -= payment;
              outstandingbalance_tmp += Math.abs(outstandingbalance.toFixed(2));
            }
          }
          sum_payment = Math.round(sum_payment*100)/100;

          tmp_sum_open_amount = Math.round(tmp_sum_open_amount*100)/100;
          sum_balance_payment = Math.round((paid_amount-sum_payment)*100)/100;
          sum_outstandingbalance = Math.round((sum_outstandingbalance)*100)/100;

          jQuery('#summ_payment').html( sum_payment.toFixed(2) );
          jQuery('#summ_payment_hidden').html( sum_payment.toFixed(2) );

          if( sum_outstandingbalance.toFixed(2) != 0.00 )
          {
            color="#FF0000";
          }
          else
          {
            color="#009900";
          }
          jQuery('#summ_outstandingbalance').html( "<span style='color:"+color+";'>"+ sum_outstandingbalance.toFixed(2) + "</span>" );
          jQuery('#summ_outstandingbalance_hidden').html( "<span style='color:"+color+";'>"+ sum_outstandingbalance.toFixed(2) + "</span>" );

          if( tmp_sum_open_amount.toFixed(2) != 0.00 )
          {
            color="#FF0000";
          }
          else
          {
            color="#009900";
          }
          //jQuery('#balance_openamount').html( tmp_sum_open_amount.toFixed(2) );
          jQuery('#balance_openamount').html("<b><span style='color:"+color+";'>"+ tmp_sum_open_amount.toFixed(2)+ "</span></b>" );

          if( sum_balance_payment.toFixed(2) != 0.00 )
          {
            color="#FF0000";
          }
          else
          {
            color="#009900";
          }
          jQuery('#balance_payment').html("<b><span style='color:"+color+";'>"+ sum_balance_payment.toFixed(2)+ "</span></b>" );
          jQuery('#balance_payment_hidden').val( sum_balance_payment.toFixed(2) );
          if( outstandingbalance_tmp.toFixed(2) != 0)
            {
                jQuery('#vat_amount').val( 0 );
            }
            else
            {
               jQuery('#vat_amount').val( eval(jQuery('#vat_amount_hidden').val()) ); 
            }
        },

        CheckCreatePayment: function()
        {
            var fieldsNameList = jQuery('#massEditFieldsNameList').data('value');
            for(var fieldName in fieldsNameList){
                var fieldInfo = fieldsNameList[fieldName];
                if( fieldInfo.mandatory )
                {
                    var selectElement2 = jQuery('input[name="'+fieldInfo.name+'"]');
                    var control = selectElement2.val();
                    if(control == ""){
                      var result = app.vtranslate('JS_REQUIRED_FIELD');
                      selectElement2.validationEngine('showPrompt', result , 'error','bottomLeft',true);
                      return false;
                    } else {
                      selectElement2.validationEngine('hide');
                    }
                }
            }
            var balance_payment = eval(jQuery('#balance_payment_hidden').val());
            if( balance_payment != 0.00 )
            {
              alert(jQuery('#zero_balance').html());
              return false;
            }  
            //validateInventory('Cashflow4You');
            return true;
        }
    }
}