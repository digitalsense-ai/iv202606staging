/**
 * Page VAT Return Files List
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var vatReturnFilesUrl = baseUrl + 'vat-return/filelazy/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });   

  //Load Excel File List
  window.loadVATReturnsFile = function loadVATReturnsFile(client_id, vat_reg_id, message = null)
  {        
    if($("#navs-vatreturns-documents-"+vat_reg_id+" #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertAfter("#navs-vatreturns-documents-"+vat_reg_id+" #load-uploaded-excel-file-list");        
    }    

    $.ajax({
      data: {client_id: client_id, vat_reg_id: vat_reg_id},  
      url: `${vatReturnFilesUrl}${vat_reg_id}`,
      type: 'GET',
      success: function (result) { 
        $("#navs-vatreturns-documents-"+vat_reg_id+" #loader").remove();    
        $("#navs-vatreturns-documents-"+vat_reg_id+" #load-uploaded-excel-file-list").html("");

        if(result['view'] == "")     
          $("#navs-vatreturns-documents-"+vat_reg_id+" #load-uploaded-excel-file-list").html("No documents uploaded.");            
        else    
          $("#navs-vatreturns-documents-"+vat_reg_id+" #load-uploaded-excel-file-list").append(result['view']);

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

  //Upload Excel File
  $(document).on("submit", ".frm-vatreturn-file", function(event)
  {
      event.preventDefault();

      var formId = $(this).attr('id');
      var data = $(this).data();     
      var client_id = data['client_id'];  
      var vat_reg_id = data['vat_reg_id'];
      
      var vatreturn_file_id = (data['vatreturn_file_id']) ? data['vatreturn_file_id'] : 0;
      
      var formData = new FormData(this);

      Swal.fire({
        title: 'Are you sure?',       
        text: "You want to "+ ((vatreturn_file_id === 0) ? "upload" : "overwrite") +" VAT Return file!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, '+ ((vatreturn_file_id === 0) ? 'Upload' : 'Overwrite') +' VAT Return file!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          
          var btn_vatreturn_upload = $("#" + formId + " #btn-vatreturn-upload-" + vat_reg_id);
          btn_vatreturn_upload.attr('disabled', 'disabled');
        
          var btn_vatreturn_uploading = '<span class="spinner-border me-1" role="status" aria-hidden="true"></span> Overwriting...';          
          if((vatreturn_file_id === 0))         
            btn_vatreturn_uploading = '<span class="spinner-border me-1" role="status" aria-hidden="true"></span> Uploading...';            
 
          btn_vatreturn_upload.html(btn_vatreturn_uploading);
          
          $.ajax({
            url: `${vatReturnFilesUrl}${vat_reg_id}`,
            type: 'POST',
            dataType: "JSON",
            data: formData,
            processData: false,
            contentType: false,
            success: function (result) {             
              if(result.length > 0)    
              {    
                var btn_vatreturn_uploaded = (vatreturn_file_id === 0) ? 'Uploaded' : 'Overwritten';      
                //console.log(btn_vatreturn_uploaded);

                var message = {message_title: 'VAT Return file '+ btn_vatreturn_uploaded +'!', message_text: 'VAT Return file has been '+ btn_vatreturn_uploaded +'.'};
                loadVATReturnsFile(client_id, vat_reg_id, message);
                //loadSingleVATReturn(vat_id);
                loadOverviewTabLazy(vat_reg_id, true);
                
                var modalId = "onboardingSlideExcelModal-"+ vat_reg_id;                               

                $('#'+ modalId).removeAttr("data-upload_success");                
                $('#'+ modalId).modal('hide');

                // var btn_vatreturn_uploaded = (vatreturn_file_id === 0) ? 'Uploaded' : 'Overwritten';               
                btn_vatreturn_upload.html(btn_vatreturn_uploaded);                
                btn_vatreturn_upload.addClass('disabled');
               
                $('#' + formId).trigger('reset');                            
              }
              else
              {
                btn_vatreturn_upload.removeClass('btn-vatreturn-upload');                
                btn_vatreturn_upload.html(result['message']);
              }
            },
            error: function (error) {
              console.log(error);
            }
          }); 

    } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled VAT Return file '+ ((vatreturn_file_id === 0) ? 'upload' : 'overwrite') +' :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });               
  });
  
  //Delete VAT Return file
  $(document).on('click', '.btn-delete-excel-file', function () {
      var btn_delete_excel_file = $(this);
      
      Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete the VAT Return file!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          var data = btn_delete_excel_file.data();
          var client_id = data['client_id']; 
          var vat_reg_id = data['vat_reg_id'];   
          var excel_file_id = (data['excelid']) ? data['excelid'] : 0;     

          btn_delete_excel_file.attr('disabled', 'disabled');
          btn_delete_excel_file.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Deleting...');
          
          $.ajax({           
            type: 'DELETE',
            url: `${vatReturnFilesUrl}${excel_file_id}`,
            success: function (result) {

              var message = {message_title: 'VAT Return file deleted!', message_text: 'VAT Return file has been deleted.'};
              loadVATReturnsFile(client_id, vat_reg_id, message);
              //loadSingleVATReturn(vat_id);   
              loadOverviewTabLazy(vat_reg_id, true);           
            },
            error: function (error) {
              console.log(error);
            }
          }); 

              
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled Deletion :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }); 
  });

  //Download VAT Return file
  $(document).on('click', '.btn-download-excel-file', function () {
      var btn_download_excel_file = $(this);
      var data = btn_download_excel_file.data();

      var excel_file_id = data['excelid'];
    
      btn_download_excel_file.removeClass("btn-primary");
      btn_download_excel_file.addClass("btn-outline-primary");

      btn_download_excel_file.html('<!-- Bounce -->' +
          '<div class="sk-bounce sk-primary sk-center">' +
            '<div class="sk-bounce-dot"></div>' +
            '<div class="sk-bounce-dot"></div>' +
          '</div>');

      $.ajax({      
        url: `${vatReturnFilesUrl}${excel_file_id}/download`,
        type: 'GET',       
        success: function (data) {
          
          btn_download_excel_file.addClass("btn-primary");
          btn_download_excel_file.removeClass("btn-outline-primary");

          btn_download_excel_file.html('<span class="tf-icons bx bxs-download"></span>');

          window.open(data, '_blank');          
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });
});