/**
 * Page Declaration List
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var declarationUrl = baseUrl + 'declaration/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  /*
  window.declarationCommentEditor = function declarationCommentEditor(data = null) {  
    // var vat_reg_id = data['vat_reg_id'];
    // var import_vat_file_id = data['import_vat_file_id'];
    // var import_vat_line_no = data['import_vat_line_no'];

    // var id = vat_reg_id + '-' + import_vat_file_id + '-' + import_vat_line_no;

    const declarationCommentEditors = document.querySelector('#declaration-comment-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (declarationCommentEditors) {
      new Quill('#declaration-comment-editor', {
        modules: {
          toolbar: '#declaration-comment-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  declarationCommentEditor();

  window.declarationComInvoiceCommentEditor = function declarationComInvoiceCommentEditor(data = null) {  
    // var vat_reg_id = data['vat_reg_id'];
    // var import_vat_file_id = data['import_vat_file_id'];
    // var import_vat_line_no = data['import_vat_line_no'];

    // var id = vat_reg_id + '-' + import_vat_file_id + '-' + import_vat_line_no;

    const declarationComInvoiceCommentEditors = document.querySelector('#declaration-com-invoice-comment-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (declarationComInvoiceCommentEditors) {
      new Quill('#declaration-com-invoice-comment-editor', {
        modules: {
          toolbar: '#declaration-com-invoice-comment-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  declarationComInvoiceCommentEditor();

  window.declarationInvoiceCommentEditor = function declarationInvoiceCommentEditor(data = null) {  
    // var vat_reg_id = data['vat_reg_id'];
    // var import_vat_file_id = data['import_vat_file_id'];
    // var import_vat_line_no = data['import_vat_line_no'];

    // var id = vat_reg_id + '-' + import_vat_file_id + '-' + import_vat_line_no;

    const declarationInvoiceCommentEditors = document.querySelector('#declaration-invoice-comment-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (declarationInvoiceCommentEditors) {
      new Quill('#declaration-invoice-comment-editor', {
        modules: {
          toolbar: '#declaration-invoice-comment-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  declarationInvoiceCommentEditor();
*/
  window.declarationInvoiceDisregardCommentEditor = function declarationInvoiceDisregardCommentEditor(data = null) {      
    const declarationInvoiceDisregardCommentEditors = document.querySelector('#declaration-invoice-disregard-comment-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (declarationInvoiceDisregardCommentEditors) {
      new Quill('#declaration-invoice-disregard-comment-editor', {
        modules: {
          toolbar: '#declaration-invoice-disregard-comment-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  declarationInvoiceDisregardCommentEditor();

  //Switch  
  //$(document).on('click', '.switch-input.declaration-switch', function () {    
  $(document).on('click', '.switch-input.declaration-invoice-visible-switch', function () {          
    if($(this).prop('checked'))
    {
      //$(".declaration-compose-switch label span.switch-label").html('Public'); 
      $(this).parent('label.switch').find('span.switch-label').html('Public');

      $("#comment_visiblity").val('Public');
    }
    else
    {
      //$(".declaration-compose-switch label span.switch-label").html('Team');   
      $(this).parent('label.switch').find('span.switch-label').html('Team');   
      $("#comment_visiblity").val('Team');
    }
  });  

/*
  //Add Comment for Declaration  
  $(document).on("submit", ".frm-declaration-comment", function(event)
  {
      event.preventDefault();
    
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
        // var client_id = data['client_id']; 
        // var vat_reg_id = data['vat_reg_id']; 
        var declaration_id = data['declaration_id']; 
        // var import_vat_line_no = data['import_vat_line_no']; 
        // var import_vat_comment_type = (data['comment_type']) ? data['comment_type'] : ''; 

        // var id = vat_reg_id + '-' + import_vat_file_id + '-' + import_vat_line_no;
            
        $("#declaration-comment-quill").val($(this).find(".ql-editor").html());

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
                         
            var btn_declaration_comment_save = $("#" + formId + " #btn-declaration-comment-save");
            btn_declaration_comment_save.attr('disabled');
            btn_declaration_comment_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Saving...');
            
            $.ajax({
              url: `${declarationUrl}${declaration_id}/comment`,
              type: 'POST',
              dataType: "JSON",
              data: formData,
              processData: false,
              contentType: false,
              success: function (result) {

                if(result)    
                {                      
                  btn_declaration_comment_save.html('Saved');
                  
                  var modalId = "onboardingSlideDeclarationCommentModal-"+id;
                  if(declaration_comment_type == 'overwrite')
                    modalId = "onboardingSlideOverwriteDeclarationCommentModal-"+id;
                  
                  $('#'+ modalId).modal('hide');

                  var message = {message_title: 'Comment saved!', message_text: 'Comment has been saved.'};
                  
                  //loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id, message);
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
*/

});