/**
 * Page Import VAT Files Tasks List
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var importVatFileUrl = baseUrl + 'import-vat-files/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  window.importVatCommentEditor = function importVatCommentEditor(data) {
    //var data = element.data();     console.log(data);
    var vat_reg_id = data['vat_reg_id'];
    var import_vat_file_id = data['import_vat_file_id'];
    var import_vat_line_no = data['import_vat_line_no'];

    var id = vat_reg_id + '-' + import_vat_file_id + '-' + import_vat_line_no;

    const importCommentEditors = document.querySelector('#import-vat-comment-editor-'+id);   
    // Initialize Quill Editor
    // ------------------------------

    if (importCommentEditors) {
      new Quill('#import-vat-comment-editor-'+id, {
        modules: {
          toolbar: '#import-vat-comment-editor-toolbar-'+id
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  //Add Comment in Import VAT File  
  $(document).on("submit", ".frm-import-vat-comment", function(event)
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
        var vat_reg_id = data['vat_reg_id']; 
        var import_vat_file_id = data['import_vat_file_id']; 
        var import_vat_line_no = data['import_vat_line_no']; 
        var import_vat_comment_type = (data['comment_type']) ? data['comment_type'] : ''; 

        var id = vat_reg_id + '-' + import_vat_file_id + '-' + import_vat_line_no;
            
        $("#import-vat-comment-quill-"+id).val($(this).find(".ql-editor").html());

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
                         
            var btn_import_vat_comment_save = $("#" + formId + " #btn-import-vat-comment-save-"+id);
            btn_import_vat_comment_save.attr('disabled', 'disabled');
            btn_import_vat_comment_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Saving...');
            
            $.ajax({
              url: `${importVatFileUrl}${import_vat_file_id}/comment/${import_vat_line_no}`,
              type: 'POST',
              dataType: "JSON",
              data: formData,
              processData: false,
              contentType: false,
              success: function (result) {

                if(result)    
                {                      
                  btn_import_vat_comment_save.html('Saved');
                  
                  var modalId = "onboardingSlideImportVatCommentModal-"+id;
                  if(import_vat_comment_type == 'overwrite')
                    modalId = "onboardingSlideOverwriteImportVatCommentModal-"+id;
                  
                  $('#'+ modalId).modal('hide');

                  var message = {message_title: 'Comment saved!', message_text: 'Comment has been saved.'};
                  
                  loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id, message);                 
                  //loadHistory(vat_reg_id); 
                  
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

  //Delete Import VAT Comment
  $(document).on('click', '.btn-delete-import-vat-comment', function () {
      var btn_delete_import_vat_comment = $(this);
      
      Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete the import vat comment!",
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
          var data = btn_delete_import_vat_comment.data();
          var vat_reg_id = data['vat_reg_id'];  
          var client_id = data['client_id']; 
          var import_vat_file_id = data['import_vat_file_id']; 
          //var import_vat_comment_id = data['import_vat_comment_id'];
          var import_vat_comment_line_no = data['import_vat_comment_line_no'];          

          btn_delete_import_vat_comment.attr('disabled', 'disabled');
          btn_delete_import_vat_comment.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Deleting...');
          
          var delete_import_vat_comment = btn_delete_import_vat_comment.closest('.timeline-event.card'); 
          delete_import_vat_comment.addClass("delete");

          $.ajax({           
            type: 'DELETE',
            url: `${importVatFileUrl}${import_vat_file_id}/comment`,
            data: {import_vat_file_id: import_vat_file_id, import_vat_comment_line_no: import_vat_comment_line_no},  
            success: function (result) {
              if(result['status'] == 'deleted')    
              {                    
                var message = {message_title: 'Import VAT Comment deleted!', message_text: 'Import VAT Comment has been deleted.'};
                        
                loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id, message); 
                //loadHistory(vat_reg_id);                        
              }
              else
              {     
                var message = {message_title: 'Error', message_text: 'Error in Deletion :('};
                                    
                loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id, message); 
              }
              
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
});