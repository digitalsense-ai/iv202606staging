'use strict';

(function () {
  var vatRegistrationMainViewUrl = baseUrl + 'vat-registration-main';      

  //Load ERP Fields
  window.loadERPFields = function loadERPFields(erp_id)
  {         
      if($("#load-erp-fields #loader").length == 0)
      {
        var loadertext = '<!-- Bounce -->' +
              '<div class="sk-bounce sk-primary sk-center" id="loader">' +
                '<div class="sk-bounce-dot"></div>' +
                '<div class="sk-bounce-dot"></div>' +
              '</div>';                  
        $("#load-erp-fields").html(loadertext);       
      }

      $("#parent_account_nos").hide(); 
      
      if(erp_id == "")
        $("#load-erp-fields").html("");   
      else
      {
        $.ajax({        
          url: `${vatRegistrationMainViewUrl}/erp-fields/${erp_id}`,
          data: {vat_reg_main_id: $("#vat_reg_main_id").val()},  
          type: 'GET',
          success: function (result) {
            $("#load-erp-fields").html("");     
            if(result['view'] != "")               
              $("#load-erp-fields").html(result['view']);
          },
          error: function (err) {
            
          }
        });
      }
  } 

  var user_type = "";
  if($('#erp_options').length > 0 )    
    user_type = "admin";    

  if($('#established_connection').length > 0 )  
    user_type = "client";

  erpChange(user_type);

  $(document).on('change', '#erp_options', function () {console.log("loading");    
    erpChange(user_type);
  });

  $(document).on('keyup', '#api_client_id', function () {
    //console.log("keyuppppp");    
    
    if(url.substring(url.lastIndexOf('/') + 1) == "create" || url.substring(url.lastIndexOf('/') + 1) == "edit") 
      showAccountNos(user_type);

    // var value = $('#erp_options').val();
    // var erp_id = value.toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');

    // var accnoCheckbox = $('#account_nos');
    // var acc_checkbox_data = accnoCheckbox.data();
    // var acc_vat_reg_main_id = acc_checkbox_data['vat_reg_main_id'];

    // if(erp_id == 'e_conomic')  
    // {      
    //   if($("#api_client_id").val() == "")
    //     $("#parent_account_nos").hide();    
    //   else  
    //     $("#parent_account_nos").show(); 

    //   if(acc_vat_reg_main_id!="")          
    //     $('#account_nos').prop('checked', true); 
    //   else
    //     $('#account_nos').prop('checked', false);         
    // }
    // else
    //   $("#parent_account_nos").hide();    
  });

  var url = window.location.href;  
  if(url.substring(url.lastIndexOf('/') + 1) == "edit")  
    showAccountNos(user_type);

  // $("#account_nos").change(function() {
  //   showAccountNos();
  //   // if(acc_vat_reg_main_id!="")
  //   //   $('#account_nos').prop('checked', true); 
  //   // else
  //   //   $('#account_nos').prop('checked', false); 
  // });

  // if($('#erp_options').length > 0)
  //   erpChange();

  function erpChange(user_type)
  { 
    //var value = $('#erp_options').val();
    //var erp_id = value.toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');

    var value;
    var erp_id = "";
    var selected_connection = "";
    var selected_connection_type = "";   
    if(user_type == "admin")
    {
       value = $('#erp_options').val();
       erp_id = value.toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');
    }
    else
    {  
      if($('#established_connection').val() != null)             
      {
        selected_connection = $('#established_connection').val();
        if(selected_connection != "")
        {
          selected_connection_type = selected_connection.split(',');        
          erp_id = selected_connection_type[1].toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');
        }
      }
    }

    loadERPFields(erp_id);

    if(erp_id == 'excel_upload')
      //$("#parent_excel_column_template").show();
      $("#parent_anyexcel_template").show();
    else    
      //$("#parent_excel_column_template").hide();
      $("#parent_anyexcel_template").hide();
  }

  /* Show/Hide the Account no switch  */
  function showAccountNos(user_type)
  {
    //var value = $('#erp_options').val();
    //var erp_id = value.toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');

    var value;
    var erp_id = "";
    var selected_connection="";
    var selected_connection_type = "";   
    if(user_type == "admin")
    {
      value = $('#erp_options').val();
      erp_id = value.toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');
    }
    else
    { 
      selected_connection = $('#established_connection').val();
      if(selected_connection != "")
      {
        selected_connection_type = selected_connection.split(',');            
        erp_id = selected_connection_type[1].toLowerCase().replace(/ /g, '_').replace(/\-/g, '_');
      }
    }

    var accnoCheckbox = $('#account_nos');
    var acc_checkbox_data = accnoCheckbox.data();
    var acc_vat_reg_main_id = acc_checkbox_data['vat_reg_main_id'];

    var selected_acc_details="";

    if(erp_id == 'e_conomic')  
    {      
      if($("#api_client_id").val() == "")
        $("#parent_account_nos").hide();    
      else  
        $("#parent_account_nos").show();  
      
      if(acc_vat_reg_main_id!="") 
      {         
        $('#account_nos').prop('checked', true);         

        $.ajax({
          url: `${baseUrl}editaccountnos`,
          type: 'GET',
          data: {api_name: erp_id,acc_vat_reg_main_id: acc_vat_reg_main_id},
          success: function (result) { 
            var acc_div = "";           
            $.each(result, function(key,value) { 
              var acc_reverse  = ((value['is_reverse']) ? '<br><u>Reverse:</u><i class="bx bx-check"></i>' : '');
              
              var acc_auto_vat_check_text = '';
              var acc_auto_vat_check  = '';
              if(value['map_column'] == 'vat_sales' || value['map_column'] == 'vat_purchases')
              {
                if(value['is_auto_vat_check'] == 0)
                  acc_auto_vat_check_text = 'Overview only';
                else if(value['is_auto_vat_check'] == 1)
                  acc_auto_vat_check_text = 'VAT check only';
                else if(value['is_auto_vat_check'] == 2)
                  acc_auto_vat_check_text = 'Overview and VAT check';
                acc_auto_vat_check  = (acc_auto_vat_check_text) ? ('<br><u>Account used for:</u> ' + acc_auto_vat_check_text) : '';
              }
      
              var map_column = '';
              if(value['map_column'] == 'net_sales')
                map_column = "Net Sales";
              else if(value['map_column'] == 'vat_sales')
                map_column = "Output VAT (on Sales)";
              else if(value['map_column'] == 'net_purchases')
                map_column = "Net Purchases";
              else if(value['map_column'] == 'vat_purchases')
                map_column = "Input VAT (on Purchases)";

              acc_div += '<li class="d-inline-flex mb-3 w-50">' +                                     
                          '<div class="justify-content-between flex-grow-1">' +
                            '<div class="me-2">' +
                              '<p class="mb-0">'+ value['acc_no'] + ((map_column) ? (' - ' + map_column) : '') + acc_reverse + acc_auto_vat_check +'</p>' +
                              '<p class="mb-0 text-muted">'+ value['acc_name'] +'</p>' +
                            '</div>' +
                          '</div>' +                    
                        '</li>';

              selected_acc_details +=  value['acc_no'] + '%%%' + value['acc_name'] + '%%%' + value['acc_type'] + '%%%' + value['is_reverse'] + '%%%' + value['is_auto_vat_check'] + '%%%' + value['map_column'] + '***';            
            });  

            $("input[name=selected_acc_nos]").val('');    
            $("input[name=selected_acc_nos]").val(selected_acc_details);

            $("#selected_account_nos").html('');
            $('#selected_account_nos').append(acc_div);  
            $('#selected_account_nos').show();    
          },
          error: function (err) {
            console.log(err);
          }
        });
      }
      else
      {
        $('#account_nos').prop('checked', false); 
        $("input[name=selected_acc_nos]").val('');
        $('#selectAccountNosForm').find('ul').empty();
      }
    }
    else
      $("#parent_account_nos").hide(); 
  }
  /* End Show/Hide the Account no switch  */

})();
