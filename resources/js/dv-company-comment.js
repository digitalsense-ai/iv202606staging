/**
 * Client Comment
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  (function () {

    // Variable declaration for table
    var clientCommentUrl = baseUrl + 'company/comment/'
      ;
     
    // ajax setup
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }); 

    window.clientCommentEditor = function clientCommentEditor() {
      const commentEditor = document.querySelector('.client-comment-editor');
      // Initialize Quill Editor
      // ------------------------------
      if (commentEditor) {
        new Quill('.client-comment-editor', {
          modules: {
            toolbar: '.client-comment-editor-toolbar'
          },
          placeholder: 'Write your message... ',
          theme: 'snow'
        });
      }  
    }     

    //Load Editor
    clientCommentEditor();
    //End  

    //Load Client Comment
    window.loadClientComment = function loadClientComment(client_id, message = null)
    {        
      if($("#navs-pills-top-client-comment #loader").length == 0)
      {
        var loadertext = '<!-- Bounce -->' +
              '<div class="sk-bounce sk-primary sk-center" id="loader">' +
                '<div class="sk-bounce-dot"></div>' +
                '<div class="sk-bounce-dot"></div>' +
              '</div>';        
        $(loadertext).insertAfter("#navs-pills-top-client-comment #load-client-comment");        
      }    

      $.ajax({        
        url: `${clientCommentUrl}${client_id}`,
        type: 'GET',
        success: function (result) { 
          $("#loader").remove();    
          $("#navs-pills-top-client-comment #load-client-comment").html("");

          if(result['view'] == "")     
            $("#navs-pills-top-client-comment #load-client-comment").html("No comments.");            
          else    
            $("#navs-pills-top-client-comment #load-client-comment").append(result['view']);

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

    //Save Client Comment
    $(document).on("submit", ".frm-client-comment", function(event)
    {
        event.preventDefault();

        var ql_editor = $(this).find(".ql-editor");
        
        if(ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "")
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
                   
          $("#client-comment-quill-"+client_id).val(ql_editor.html());

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
                           
              var btn_client_comment_save = $("#" + formId + " #btn-client-comment-save-" + client_id);
              btn_client_comment_save.attr('disabled', 'disabled');
              btn_client_comment_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                      'Saving...');
              
              $.ajax({
                url: `${clientCommentUrl}${client_id}`,
                type: 'POST',
                dataType: "JSON",
                data: formData,
                processData: false,
                contentType: false,
                success: function (result) {

                  if(result)    
                  {    
                    btn_client_comment_save.removeAttr('disabled');
                    btn_client_comment_save.html('Saved');
                    
                    var modalId = "onboardingSlideClientCommentModal-"+ client_id;
                    $('#'+ modalId).modal('hide');

                    ql_editor.html('');

                    var message = {message_title: 'Comment saved!', message_text: 'Comment has been saved.'};
                    loadClientComment(client_id, message);                   
                  }
                },
                error: function (error) {
                  console.log(error);
                }
              }); 

          } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
              title: 'Cancelled',
              text: 'Cancelled comment :)',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });   
      } //null editor
    });

    //Delete Client Comment
    $(document).on("click", ".btn-delete-client-comment", function(event)
    {
      var btn_client_comment_delete = $(this);
      var data = $(this).data();

      var comment_id = data.comment_id;
      var client_id = data.client_id;

      Swal.fire({
          title: 'Are you sure?',       
          text: "You want to delete the comment!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Delete comment!',
          customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {        
          if (result.value) {
                                     
            btn_client_comment_delete.attr('disabled', 'disabled');
            btn_client_comment_delete.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Deleting...');
            
            $.ajax({
              url: `${clientCommentUrl}${comment_id}`,
              type: 'DELETE',    
              data: { client_id: client_id },            
              success: function (result) {

                if(result)    
                {                      
                  //var message = {message_title: 'Comment deleted!', message_text: 'Comment has been deleted.'};
                  loadClientComment(client_id);                   
                }
              },
              error: function (error) {
                console.log(error);
              }
            }); 

        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled delete comment :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }); 
    });

  })();
});
