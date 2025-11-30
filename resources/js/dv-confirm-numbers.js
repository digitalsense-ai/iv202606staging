/**
 * Page Confirm Numbers
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var confirmNumbersUrl = baseUrl + 'confirm-numbers/',
      exportPdfConfirmViewUrl = baseUrl + 'pdf-confirm-view/'
    ;

   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  // Update Status
  $(document).on('click', '#confirm-data', function () {
    var statusCheckbox = $(this);
    var status = statusCheckbox.prop("checked");

    var btn_submit = $("#formConfirmNumbers").find("button.btn-accept-numbers");
    if(status)
    {
      btn_submit.removeAttr("disabled");
      btn_submit.removeClass("disabled");
      btn_submit.removeClass("btn-label-success");
      btn_submit.addClass("btn-success");
    }
    else
    {
      btn_submit.attr("disabled", "disabled");
      btn_submit.addClass("disabled");
      btn_submit.addClass("btn-label-success");
      btn_submit.removeClass("btn-success");
    }
  });

  //Approve Numbers
  $(document).on("submit", "#formConfirmNumbers", function(event)
  {
      event.preventDefault();

      var form = $(this);     
      var data = $(this).data();           
      var vat_reg_id = data['vatid'];      
     
      var btn_submit = $(this).find("button.btn-accept-numbers");

      Swal.fire({
        title: 'Are you sure?',       
        text: "You want to approve the numbers!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve numbers!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
                          
          btn_submit.attr("disabled", "disabled");
          btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Submitting...');
          
          $.ajax({
            data: form.serialize(),
            url: `${confirmNumbersUrl}${vat_reg_id}`,
            type: 'POST',
            success: function (response) {
                           

              var className = '';
              var messageText = '';
              if(response.message.error_message != '') 
              {
                className = 'danger';
                messageText = response.message.error_message;
              }

              if(response.message.success_message != '') 
              {
                className = 'success';
                messageText = response.message.success_message;
              }

              if(response.message.warning_message != '') 
              {
                className = 'warning';
                messageText = response.message.warning_message;
              }
              
              var messageHtml = '<div class="alert alert-' + className +'" role="alert">' +       
                                      messageText +                                                                   
                                '</div>';
                            
              $(".alert[role='alert']").remove();                      
              $(messageHtml).insertBefore(".accept-numbers-card");             

              if(className == "success")
              {
                $("#formConfirmNumbers").hide();

                var approvedDetails = '<div class="mb-3 d-flex flex-wrap">' +
                                        '<button class="btn btn-success w-100 btn-accept-numbers" disabled="disabled">' +
                                          '<span class="d-flex align-items-center justify-content-center text-nowrap">Approved</span>' +
                                        '</button>' +
                                      '</div>' +
                                      '<div id="load-approved-details">' +
                                        '<ul class="list-unstyled m-0">' +
                                          '<li class="d-flex align-items-center">' +
                                            '<span class="fw-semibold mx-2">Date Time:</span> <span class="text-end p-2 m-0">'+ moment(response.approved_by.approved_at).format("DD-MM-YYYY HH:mm") +'</span>' +
                                          '</li>' +
                                          '<li class="d-flex align-items-center">' +
                                            '<span class="fw-semibold mx-2">Approve By:</span> <span class="text-end p-2 m-0">'+ response.approved_by.firstname + ' ' + response.approved_by.lastname +'</span>' +
                                          '</li>' +
                                        '</ul>' +
                                      '</div>';
                $(approvedDetails).insertAfter("#formConfirmNumbers");

                $("#formConfirmNumbers").remove();   
               
                $("button.btn-decline-numbers").remove();                               
              }
              else
              {
                btn_submit.html('Approve');             
                btn_submit.removeAttr('disabled');                
              }

              form.trigger('reset');                        
            },
            error: function (xhr, status, error) {
              var err = JSON.parse(xhr.responseText);
              console.log(err);
              btn_submit.html("Accept numbers");      
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

    } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled confirmation :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  //Decline Numbers
  $(document).on("submit", "#formDeclineNumbers", function(event)
  {
      event.preventDefault();

      var form = $(this);     
      var data = $(this).data();           
      var vat_reg_id = data['vatid'];      
      
      var btn_submit = $(this).find("button.btn-decline-numbers");

      Swal.fire({
        title: 'Are you sure?',       
        text: "You want to decline the numbers!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Decline numbers!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
                          
          btn_submit.attr("disabled", "disabled");
          btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Submitting...');
          
          $.ajax({
            data: form.serialize(),
            url: `${confirmNumbersUrl}${vat_reg_id}`,
            type: 'DELETE',
            success: function (response) {
    
              var className = '';
              var messageText = '';
              if(response.message.error_message != '') 
              {
                className = 'danger';
                messageText = response.message.error_message;
              }

              if(response.message.success_message != '') 
              {
                className = 'success';
                messageText = response.message.success_message;
              }

              if(response.message.warning_message != '') 
              {
                className = 'warning';
                messageText = response.message.warning_message;
              }
              
              var messageHtml = '<div class="alert alert-' + className +'" role="alert">' +                                
                                      messageText +                                                                           
                                '</div>';
                            
              $(".alert[role='alert']").remove();                     
              $(messageHtml).insertBefore(".accept-numbers-card");

              if(className == "success")
              {
                $("#formConfirmNumbers").hide();

                var declineDetails = '<span class="cursor-pointer text-decoration-underline d-grid text-center">Decline</span>' +
                                      '<div id="load-approved-details">' +
                                        '<ul class="list-unstyled m-0">' +
                                          '<li class="d-flex align-items-center">' +
                                            '<span class="fw-semibold mx-2">Date Time:</span> <span class="text-end p-2 m-0">'+ moment(response.declined_by.declined_at).format("DD-MM-YYYY HH:mm") +'</span>' +
                                          '</li>' +
                                          '<li class="d-flex align-items-center">' +
                                            '<span class="fw-semibold mx-2">Decline By:</span> <span class="text-end p-2 m-0">'+ response.declined_by.firstname + ' ' + response.declined_by.lastname +'</span>' +
                                          '</li>' +
                                        '</ul>' +
                                      '</div>';
                $(declineDetails).insertAfter("#formConfirmNumbers");

                $("#formConfirmNumbers").remove();   
               
                $("button.btn-decline-numbers").remove(); 
                
              }
              else
              {
                btn_submit.html('Decline');             
                btn_submit.removeAttr('disabled');     
              }
              
              document.querySelector('#declineNumbersOffcanvas .btn-close[data-bs-dismiss="offcanvas"]').click();
              
              Swal.fire({
                title: className + '!',
                text: messageText,
                icon: className,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });              
            },
            error: function (xhr, status, error) {
              var err = JSON.parse(xhr.responseText);
              console.log(err);
              btn_submit.html("Decline numbers");      
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

    } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled declination :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  //Export to PDF  
  $(document).on('click', '.btn-export-pdf-confirmview', function () {
      var btn_export_pdf_confirmview = $(this);
      btn_export_pdf_confirmview.attr('disabled', 'disabled');
      btn_export_pdf_confirmview.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Exporting...');

      var vat_reg_id = btn_export_pdf_confirmview.data('vat_reg_id'); 
     
      $.ajax({        
        //data: {box1: box1, box2: box2, box3: box3, box4: box4, box5: box5, box6: box6, box7: box7, box8: box8, box9: box9},  
        url: `${exportPdfConfirmViewUrl}${vat_reg_id}/export`,
        type: 'POST',
        xhrFields: {
          responseType: 'blob'      
        },
        success: function (data) {
          btn_export_pdf_confirmview.removeAttr('disabled'); 
          btn_export_pdf_confirmview.html('<i class="bx bx-up-arrow-circle me-1"></i>' +
                                  '<span class="align-middle">Export to PDF</span>');

          var blob=new Blob([data]);      
          var link=document.createElement('a');
          link.href=window.URL.createObjectURL(blob);
          link.download="confirmview.pdf";
          link.click();
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });

  $("#confirm-vatreturns-footer div.card").clone().appendTo('#load-confirm-vatreturns-footer');  
  $("#load-confirm-vatreturns-footer div.card").addClass('w-75 float-end');
});