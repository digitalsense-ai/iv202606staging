/**
 * Select Account No.'s
 */

'use strict';
$(function () {
  
  var selectAccountNos = document.getElementById('selectAccountNos');
  selectAccountNos.addEventListener('show.bs.modal', function (event) {
    
    $("#selectAccountNosForm .sk-bounce").show();

    $('#selectAccountNosForm').find('h4').hide();
    $('#selectAccountNosForm').find('ul').hide();

    var client_id = event.relatedTarget.dataset.client_id;
    
    var api_name = "";
    var selected_connection = "";
    var selected_connection_type = "";

    if($('#erp_options').length > 0 )  
      api_name = $("#erp_options").val();

    if($('#established_connection').length > 0 )  
    {       
      selected_connection = $('#established_connection').val();
      if(selected_connection != "")
      {
        selected_connection_type = selected_connection.split(',');       
        api_name = selected_connection_type[1];  
      }
    }

    var api_client_id = $("#api_client_id").val();

    var accnoCheckbox = $('#account_nos');
    var acc_checkbox_data = accnoCheckbox.data();
    var acc_vat_reg_main_id = acc_checkbox_data['vat_reg_main_id'];
    
    var selected_acc_details = $("input[name=selected_acc_nos]").val();       
    var selected_account_datas = selected_acc_details.split('***');
    var selected_acc_no = [];

    var acc_no;
    var acc_name; 
    var acc_type; 
    var acc_reverse; 
    var acc_auto_vat_check; 
    var acc_map_column; 
    var selected_account_details;

    $.each(selected_account_datas, function(strkey,strvalue) {
      if(strvalue != "")
      {
        selected_account_details = strvalue.split('%%%');

        acc_no = (selected_account_details[0]) ? selected_account_details[0] : null;
        acc_name = (selected_account_details[1]) ? selected_account_details[1] : null;
        acc_type = (selected_account_details[2]) ? selected_account_details[2] : null;
        acc_reverse = (selected_account_details[3]) ? selected_account_details[3] : null;
        acc_auto_vat_check = (selected_account_details[4]) ? selected_account_details[4] : null;
        acc_map_column = (selected_account_details[5]) ? selected_account_details[5] : null;

        selected_acc_no.push({
          'vat_account_no' : acc_no,
          'vat_account_name' : acc_name,
          'vat_account_type' : acc_type,
          'acc_reverse' : acc_reverse,
          'acc_auto_vat_check' : acc_auto_vat_check,
          'acc_map_column' : acc_map_column
        });  
      }                    
     });

    $.ajax({      
      url: `${baseUrl}accountnos/${client_id}`,
      type: 'GET',
      data: {api_name: api_name, api_client_id: api_client_id, acc_vat_reg_main_id: acc_vat_reg_main_id},
      success: function (result) { 
        console.log(result);

        if(result['allaccountnos']['error'])
        {        
          Swal.fire({
            title: 'Error',
            text: 'Error in E-conomic credential :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then(function (result) {
            $("#api_client_id").val('');
            $("#parent_account_nos").hide();
            $("#selectAccountNos").modal('hide');
          });         
        }
        else
        {
          $('#selectAccountNosForm').find('h4').text(result['allaccountnos'].length + " Account No.'s");

          var li = ''; 
          $('input.chk-accoutno:checkbox').prop('checked', false);  

          $.each(result['allaccountnos'], function(key,value) {
           
            var checked_status = '';     
            var checked_acc_reverse = ''; 
            var selected_auto_vat_check = ''; 
            var selected_map_column = '';           
            $.each(selected_acc_no, function(selkey,selvalue) {           
              if(value['account_no'] == selvalue['vat_account_no'])
              { 
                checked_status = 'checked';

                checked_acc_reverse = (selvalue['acc_reverse'] == "1") ? 'checked' : ''; 
                selected_auto_vat_check = selvalue['acc_auto_vat_check'];
                selected_map_column = selvalue['acc_map_column'];
              }
            });
            // $.each(result['selectedaccountnos'], function(selkey,selvalue) {           
            //   if(value['account_no'] == selvalue['acc_no']) 
            //     checked_status = 'checked';           
            // });

            li += '<li class="d-inline-flex mb-3 w-50">' +   
                      '<div class="align-items-center">' +
                        '<div class="form-check form-check-inline">' +
                          //'<input name="chk_accoutno[]" class="form-check-input chk-accoutno" type="checkbox" value="'+ value['account_no'] +'" data-acc_name="'+ value['account_name'] +'" data-acc_type="'+ value['account_type'] +'" />' +
                          '<input  name="chk_accoutno[]" class="form-check-input chk-accoutno" type="checkbox"  value="'+ value['account_no'] +'" data-acc_name="'+ value['account_name'] +'" data-acc_type="'+ value['account_type'] +'" ' + checked_status +' />' +
                        '</div>' +
                      '</div>' +                 
                      '<div class="justify-content-between flex-grow-1">' +
                        '<div class="me-2">' +
                          '<div class="mb-0">'+ value['account_no'] +
                            '<select id="acc_map_column" class="form-select d-inline-block mx-2 w-px-100 form-select-sm" data-allow-clear="true" name="acc_map_column[]">' +
                              '<option value="">Select</option>' +
                              '<option value="net_sales" '+ ((selected_map_column == 'net_sales') ? 'selected="selected"' : '') +'>Net Sales</option>' +
                              '<option value="vat_sales" '+ ((selected_map_column == 'vat_sales') ? 'selected="selected"' : '') +'>Output VAT (on Sales)</option>' +
                              '<option value="net_purchases" '+ ((selected_map_column == 'net_purchases') ? 'selected="selected"' : '') +'>Net Purchases</option>' +
                              '<option value="vat_purchases" '+ ((selected_map_column == 'vat_purchases') ? 'selected="selected"' : '') +'>Input VAT (on Purchases)</option>' +
                            '</select>' +  

                            '<!-- Reverse Toggle -->' +
                            '<div class="form-check form-switch form-check-inline">' +
                              '<input class="form-check-input acc_reverse" name="acc_reverse[]" type="checkbox" value="'+ value['account_no'] +'" id="acc_reverse_'+ value['account_no'] +'" ' + checked_acc_reverse +'>' +
                              '<label class="form-check-label text-muted" for="acc_reverse_'+ value['account_no'] +'" style="font-size: 12px;">Reverse</label>' +
                            '</div>' +

                          '</div>' +

                          '<!-- Auto VAT Check Toggle -->' +
                          '<div class="my-2 '+ ((selected_map_column == 'vat_sales' || selected_map_column == 'vat_purchases') ? '' : 'd-none') +'">' +
                            '<label class="text-decoration-underline">Account used for: </label>' +
                            '<select id="acc_auto_vat_check" class="form-select d-inline-block ms-2 w-px-100 form-select-sm" data-allow-clear="true" name="acc_auto_vat_check[]">' +
                              '<option value="">Select</option>' +
                              '<option value="0" '+ ((selected_auto_vat_check == '0') ? 'selected="selected"' : '') +'>Overview only</option>' +
                              '<option value="1" '+ ((selected_auto_vat_check == '1') ? 'selected="selected"' : '') +'>VAT check only</option>' +
                              '<option value="2" '+ ((selected_auto_vat_check == '2') ? 'selected="selected"' : '') +'>Both</option>' +                            
                            '</select>' + 
                          '</div>' +  

                          // '<div class="form-check form-switch form-check-inline '+ ((selected_map_column == 'vat_sales' || selected_map_column == 'vat_purchases') ? '' : 'd-none') +'">' +
                          //   '<input class="form-check-input acc_auto_vat_check" name="acc_auto_vat_check[]" type="checkbox" value="'+ value['account_no'] +'" id="acc_auto_vat_check_'+ value['account_no'] +'" ' + checked_acc_auto_vat_check +'>' +
                          //   '<label class="form-check-label text-muted" for="acc_auto_vat_check_'+ value['account_no'] +'" style="font-size: 12px;">Auto VAT check</label>' +
                          // '</div>' +

                          '<p class="mb-0 text-muted">'+ value['account_name'] +'</p>' +
                        '</div>' +
                      '</div>' +                    
                    '</li>';
          });

          $('#selectAccountNosForm').find('ul').append(li);

          $('#selectAccountNosForm').find('h4').show();
          $('#selectAccountNosForm').find('ul').show();
          $("#selectAccountNosForm .sk-bounce").hide();

        }
      },
      error: function (err) {
        console.log(err);        

        Swal.fire({
          title: 'Error',
          text: 'Error in E-conomic credential :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(function (result) {
          $("#api_client_id").val('');
          $("#parent_account_nos").hide();
          $("#selectAccountNos").modal('hide');
        });
      }
    });
  }); 
  
  // Dropdown change
  $(document).on('change', 'select#acc_map_column', function () {
    let li = $(this).closest('li');
    li.find("select#acc_auto_vat_check").closest('div').addClass('d-none');

    if($(this).val() == 'vat_sales' || $(this).val() == 'vat_purchases')
      li.find("select#acc_auto_vat_check").closest('div').removeClass('d-none');
  });

  // Checkbox
  $(document).on('click', '.chk-accoutno', function () { 
    if(!$(this).is(":checked")) 
    {
      let li = $(this).closest('li');
      li.find("select#acc_map_column").prop('selectedIndex', 0);
      li.find("input.acc_reverse").prop('checked', false);     
      li.find("select#acc_auto_vat_check").prop('selectedIndex', 0);
    }  
    selectedLength('.chk-accoutno');
  });

  $(document).on("hide.bs.modal", "#selectAccountNos", function(event) {
    if($("#selected_account_nos:has(li)").length)      
      $('#account_nos').prop('checked', true); 
    else     
      $('#account_nos').prop('checked', false); 
  });
});
