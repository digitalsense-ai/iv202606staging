/**
 * Page Submitting Fields
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var submittingFieldsUrl = baseUrl + 'submittingfields/',
      submittingFieldsTabUrl = baseUrl + 'vat-return-submittingfields-tab/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  //Load Submitting Fields
  window.loadVATReturnsSubmittingFields = function loadVATReturnsSubmittingFields(vat_reg_id, message = null)  
  {           
    if($("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';             
      //$("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").append(loadertext);
      //$("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").prepend(loadertext);
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").html(loadertext);
    }    

    $.ajax({
      data: {vat_reg_id: vat_reg_id},        
      //url: `${submittingFieldsUrl}${vat_reg_id}`,
      url: `${submittingFieldsTabUrl}${vat_reg_id}`,
      type: 'GET',
      success: function (result) {       
        $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").html("");
        $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").html(result['view']);
        //$("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").append(result['view']);

        // if(result['view'] == "")   
        // {    
        //   $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields #loader").remove();

        //   if(result['country'] == "GB" || result['country'] == "NO")   
        //     loadVATReturnsSubmittingFieldsValue(vat_reg_id);
        // }
        // else
        // {           
        //   $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").html("");
                  
        //   $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #load-submitting-fields").append(result['view']);
        // }        

        if(message)
          Swal.fire({
            icon: 'success',
            title: message['message_title'],
            text: message['message_text'],
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.log('error: ' + textStatus);
      }
    });
  }

  //Load Submitting Fields VALUES
  window.loadVATReturnsSubmittingFieldsValue = function loadVATReturnsSubmittingFieldsValue(vat_reg_id)
  {     
    var formID = $("#formSubmittingFields-"+vat_reg_id);
    var country = formID.data('country');    

    if(country == "GB")
    {
      var salesnetamount = $("#salestotalnet-"+vat_reg_id).val();
      var salestotalvat = $("#salestotalvat-"+vat_reg_id).val();
      var purchasetotalvat = $("#purchasetotalvat-"+vat_reg_id).val();
      var purchasenetamount = $("#purchasetotalnet-"+vat_reg_id).val();
      var pivsmonthtotal = $("#pivsmonthtotal-"+vat_reg_id).val();
      var c79numbers = $("#c79numbers-"+vat_reg_id).val();

      var box1 = parseFloat(pivsmonthtotal) + parseFloat(salestotalvat);
      var box2 = 0;
      var box3 = box1 + box2;
      var box4 = parseFloat(c79numbers) + parseFloat(pivsmonthtotal) + parseFloat(purchasetotalvat);
      var box5 = box3 - box4;
      var box6 = parseFloat(salesnetamount);
      var box7 = ((parseFloat(c79numbers)/0.2) + (parseFloat(pivsmonthtotal)/0.2) + parseFloat(purchasenetamount));
      var box8 = 0;
      var box9 = 0;

      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-1").val(parseFloat(box1).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-2").val(parseFloat(box2).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-3").val(parseFloat(box3).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-4").val(parseFloat(box4).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-5").val(parseFloat(box5).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-6").val(parseFloat(box6).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-7").val(parseFloat(box7).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-8").val(parseFloat(box8).toFixed(2)); 
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-9").val(parseFloat(box9).toFixed(2));   
    }
    else if(country == "NO")
    {
      // //Sale VAT
      // var sales_standard_totalvat = $("#sales-standard-totalvat-"+vat_reg_id).val();
      // $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-3").val(sales_standard_totalvat);  

      // var sales_medium_totalvat = $("#sales-medium-totalvat-"+vat_reg_id).val();
      // $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-31").val(sales_medium_totalvat);

      // var sales_low_totalvat = $("#sales-low-totalvat-"+vat_reg_id).val();
      // $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-33").val(sales_low_totalvat);

      // var sales_zero_totalvat = $("#sales-zero-totalvat-"+vat_reg_id).val();
      // $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-5").val(sales_zero_totalvat);

      // var sales_fish_totalvat = $("#sales-fish-totalvat-"+vat_reg_id).val();
      // $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-32").val(sales_fish_totalvat);  

      //Sale NET
      var sales_standard_totalnet = $("#sales-standard-totalnet-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-3").val(sales_standard_totalnet);  

      var sales_medium_totalnet = $("#sales-medium-totalnet-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-31").val(sales_medium_totalnet);

      var sales_low_totalnet = $("#sales-low-totalnet-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-33").val(sales_low_totalnet);

      var sales_zero_totalnet = $("#sales-zero-totalnet-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-5").val(sales_zero_totalnet);

      var sales_fish_totalnet = $("#sales-fish-totalnet-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-32").val(sales_fish_totalnet);  

      //Purchase
      var purchases_standard_totalvat = $("#purchases-standard-totalvat-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-1").val(purchases_standard_totalvat);  

      var purchases_medium_totalvat = $("#purchases-medium-totalvat-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-11").val(purchases_medium_totalvat);

      var purchases_low_totalvat = $("#purchases-low-totalvat-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-13").val(purchases_low_totalvat);

      //var purchases_zero_totalvat = $("#purchases-zero-totalvat-"+vat_reg_id).val();
      //$("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-5").val(purchases_zero_totalvat);

      var purchases_fish_totalvat = $("#purchases-fish-totalvat-"+vat_reg_id).val();
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-12").val(purchases_fish_totalvat); 

      //Import VAT - statistical_number and fee_number
      var box_52 = 0;
      $('input[name^="import_vat_e_fee_number_'+vat_reg_id+'"],input[name^="import_vat_e_statistical_number_'+vat_reg_id+'"]').each(function() {
          box_52 += Number($(this).val());
      });
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-52").val(box_52); 
               
      var box_81 = 0;
      $('input[name^="import_vat_fee_number_'+vat_reg_id+'"],input[name^="import_vat_statistical_number_'+vat_reg_id+'"]').each(function() {
          box_81 += Number($(this).val());      
      });
      $("#navs-vatreturns-submittingfields-"+vat_reg_id+" #submittingfields-box-" + vat_reg_id + "-81").val(box_81); 
    } //NO
    else if(country == "CH")
    {
    } //CH    
  }

  //Export to Excel  
  $(document).on('click', '.btn-export-excel-submittingfields', function () {
      var btn_export_excel_submittingfields = $(this);
      btn_export_excel_submittingfields.attr('disabled', 'disabled');
      btn_export_excel_submittingfields.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Exporting...');

      var vat_reg_id = btn_export_excel_submittingfields.data('vat_reg_id'); 

      var box1 = $("#submittingfields-box-"+ vat_reg_id +"-1").val();
      var box2 = $("#submittingfields-box-"+ vat_reg_id +"-2").val();
      var box3 = $("#submittingfields-box-"+ vat_reg_id +"-3").val();
      var box4 = $("#submittingfields-box-"+ vat_reg_id +"-4").val();
      var box5 = $("#submittingfields-box-"+ vat_reg_id +"-5").val();
      var box6 = $("#submittingfields-box-"+ vat_reg_id +"-6").val();
      var box7 = $("#submittingfields-box-"+ vat_reg_id +"-7").val();
      var box8 = $("#submittingfields-box-"+ vat_reg_id +"-8").val();
      var box9 = $("#submittingfields-box-"+ vat_reg_id +"-9").val();      

      $.ajax({        
        data: {box1: box1, box2: box2, box3: box3, box4: box4, box5: box5, box6: box6, box7: box7, box8: box8, box9: box9},  
        url: `${submittingFieldsUrl}${vat_reg_id}/export`,
        type: 'POST',
        xhrFields: {
          responseType: 'blob'      
        },
        success: function (data) {
          btn_export_excel_submittingfields.removeAttr('disabled'); 
          btn_export_excel_submittingfields.html('<i class="bx bx-up-arrow-circle me-1"></i>' +
                                  '<span class="align-middle">Export to excel</span>');

          //var blob=new Blob([data]);      
          var blob=new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });      
          var link=document.createElement('a');
          link.href=window.URL.createObjectURL(blob);
          link.download="submittingfields.xlsx";
          link.click();
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });

  //Check Declaration
  $(document).on('click', '.submittingfields-declaration', function () {
    var data = $(this).data();  
    var vat_reg_id = data['vatid']; 

    if ($(this).is(':checked'))     
      $("#formSubmittingFields-" + vat_reg_id + " button").removeAttr("disabled");    
    else
      $("#formSubmittingFields-" + vat_reg_id + " button").attr("disabled", "disabled");
  });  

  //Submit
  $(document).on("submit", ".formSubmittingFields", function(event)
  {
      event.preventDefault();

      var data = $(this).data();  
      var vat_reg_id = data['vatid']; 
      var country = data['country']; 

      var btn_submit = $(this).find("button.submittingfields-save");
      btn_submit.attr("disabled", "disabled");
      btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Saving...');
      
      $.ajax({
        data: $('#formSubmittingFields-'+vat_reg_id).serialize(),
        url: `${submittingFieldsUrl}${vat_reg_id}`,
        type: 'POST',
        success: function (response) {

          if(response.status >= 200 && response.status <= 299)   
          {   
            btn_submit.html("Saved");  

            if(country == 'GB')
              loadVATReturnsSubmittingFields(vat_reg_id);

            // sweetalert
            Swal.fire({
              icon: 'success',
              title: `Submitting fields saved!`,
              text: `Submitting fields was successfully saved.`,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });             
          }
          else if(response.status >= 400 && response.status <= 599)  
          {            
            Swal.fire({
              title: 'Error!',
              text: response.message.message,
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });  
          }  
          
          $('#formSubmittingFields-'+vat_reg_id).trigger('reset');
          
        },
        error: function (xhr, status, error) {
          var err = JSON.parse(xhr.responseText);
console.log(err);
          btn_submit.html("Save");      
          Swal.fire({
            title: 'Error!',
            text: err.message,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
  });

  //Submitting Fields Change
  $(document).on('change keyup paste', '.formSubmittingFields input[type="text"]', function () { 
      var formId = $(this).closest('.formSubmittingFields').attr('id');  
      var data = $("#" +formId).data();
      
      var country = data['country']; 
      var vat_reg_id = data['vatid']; 
      
      if(country == 'GB')
      {
        var txtBox = $(this).attr("id");
        //Update Box 1 & 4
        if((txtBox == "submittingfields-box-"+vat_reg_id+"-1") || 
          (txtBox == "submittingfields-box-"+vat_reg_id+"-4"))
        {        
          var box3 = Number($("#submittingfields-box-"+vat_reg_id+"-1").val()) + Number($("#submittingfields-box-"+vat_reg_id+"-2").val());
          $("#submittingfields-box-"+vat_reg_id+"-3").val(box3.toFixed(2));
         
          var box5 = Number($("#submittingfields-box-"+vat_reg_id+"-3").val()) - Number($("#submittingfields-box-"+vat_reg_id+"-4").val());        
          $("#submittingfields-box-"+vat_reg_id+"-5").val(box5.toFixed(2));
        }
      }

      //Save Button
      var btn_save = $(".formSubmittingFields button.submittingfields-save");
      btn_save.removeAttr("disabled");
      btn_save.html('Save');
  });
});