/**
 * VAT Return Comments
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  (function () {

    // Variable declaration for table
    var commentsUrl = baseUrl + 'vat-return/comment/',
      commentsSendEmailUrl = baseUrl + 'send-comment-email/'
      ;
     
    // ajax setup
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }); 

    window.commentEmailEditor = function commentEmailEditor(data) {
      var vat_reg_id = data['vat_reg_id'];

      //const emailEditor = document.querySelector('.email-editor');
      const emailEditor = document.querySelector('#email-editor-' + vat_reg_id);
      // Initialize Quill Editor
      // ------------------------------
      if (emailEditor) {
        new Quill('#email-editor-' + vat_reg_id, {
          modules: {
            toolbar: '#email-editor-toolbar-' + vat_reg_id
          },
          placeholder: 'Write your message... ',
          theme: 'snow'
        });
      }  
    }    

    //Comment Files
    const dtCommentFiles = new DataTransfer();
    
    $(document).on("change", ".attach-comment-file", function(event)
    {
      var data = $(this).data();          
      var vat_id = data['vatid'];  

      for(var i = 0; i < this.files.length; i++){        
        let fileBloc = $('<span/>', {class: 'd-block badge bg-label-secondary text-start my-2 file-block'}),
           fileName = $('<span/>', {class: 'name', html: this.files.item(i).name});
        fileBloc.append(fileName).append('<span class="file-delete"><i class="bx bx-trash bx-xs float-end cursor-pointer"></i></span>')
          ;        
        $("#comment-files-list-"+vat_id).append(fileBloc);
      };
      
      for (let file of this.files) {
        dtCommentFiles.items.add(file);
      }      
      this.files = dtCommentFiles.files;
      
      $('span.file-delete').click(function(){       
        let name = $(this).prev('span.name').text();       
        $(this).parent().remove();
        for(let i = 0; i < dtCommentFiles.items.length; i++){          
          if(name === dtCommentFiles.items[i].getAsFile().name){            
            dtCommentFiles.items.remove(i);
            continue;
          }
        }
              
        document.getElementById('attach-comment-file-'+vat_id).files = dtCommentFiles.files;        
      });
    });   

    //Save Comment
    $(document).on("submit", ".frm-comment", function(event)
    {
        event.preventDefault();

        //console.log($(this).find(".ql-editor").html().replace( /(<([^>]+)>)/ig, ''));
        if($(this).find(".ql-editor").html().replace( /(<([^>]+)>)/ig, '') == "")
        {
          Swal.fire({
            title: 'Error',
            text: 'Please type comments',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
          $(this).find(".ql-editor").focus();
          return false;
        }
        else
        {
          var formId = $(this).attr('id');
          var data = $(this).data();     
          var client_id = data['client_id']; 
          var vat_id = data['vatid'];      
                    
          $("#comment-quill-"+vat_id).val($(this).find(".ql-editor").html());

          var formData = new FormData(this);
  
          Swal.fire({
            title: 'Are you sure?',       
            text: "You want to save the comment!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save comment!',
            customClass: {
              confirmButton: 'btn btn-primary me-2',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          }).then(function (result) {        
            if (result.value) {
                           
              var btn_comment_save = $("#" + formId + " #btn-comment-save-" + vat_id);
              btn_comment_save.attr('disabled', 'disabled');
              btn_comment_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                      'Saving...');
              
              $.ajax({
                url: `${commentsUrl}${vat_id}`,
                type: 'POST',
                dataType: "JSON",
                data: formData,
                processData: false,
                contentType: false,
                success: function (result) {
  
                  if(result)    
                  {     console.log("inner div");               
                    $("#comment-status-"+vat_id).val(1);

                    $("#formCommentEmail-"+vat_id + " #comment_id").val(result.id);
                    
                    btn_comment_save.removeAttr('disabled');
                    btn_comment_save.html('Saved');
                    btn_comment_save.addClass('disabled');


                    $("#modalCarouselCommentControls-" + vat_id).children('.carousel-control-prev.carousel-control').hide();              
                    $("#modalCarouselCommentControls-" + vat_id).children('.carousel-control-next.carousel-control').show();
                    $("#modalCarouselCommentControls-" + vat_id).children('.carousel-indicators').show();  
                    
                    Swal.fire({
                      icon: 'success',
                      title: 'Comments saved!',
                      text: 'Comments has been saved. you can send email now.',
                      customClass: {
                        confirmButton: 'btn btn-success'
                      }
                    });
                  }
                },
                error: function (error) {
                  console.log(error);
                }
              }); 

          } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
              title: 'Cancelled',
              text: 'Cancelled comments :)',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });   
      } //null editor
    });


  //Send e-mail to Client to re-open folder  
  $(document).on('click', '.btn-comment', function () {
    var formId = $(this).closest('.formEmail').attr('id'); 
    var modalId = $(this).closest('.modal-onboarding.modal').attr('id'); 
    
    var btn_send_email = $(this);   

    var data = btn_send_email.data();
       
    var vat_reg_id = data['vat_reg_id'];  
   
    btn_send_email.attr('disabled', 'disabled');
    btn_send_email.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Sending...');
              
    $.ajax({
      data: $('#' + formId).serialize(),
      type: 'POST',
      url: `${commentsSendEmailUrl}${vat_reg_id}`,
      success: function (result) {
        if(result['success'])    
        {                                                           
          var message_title = 'Email sent!';
          var message_text = 'Email has been sent to client users to re-open';
          
          var message = {message_title: message_title, message_text: message_text};                                            
        }
        else            
          var message = {message_title: 'Error', message_text: result['message'] + ' :)', message_icon: 'error', message_confirmButton: 'btn btn-danger'};            
     
        loadOverviewTabLazy(vat_reg_id, false, null, message, modalId);  
      },
      error: function (error) {
        console.log(error);
      }
    });        
  });

  })();
});
