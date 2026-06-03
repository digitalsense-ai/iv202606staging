/**
 * Import Reconciliation Notes
 */

'use strict';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

document.addEventListener('DOMContentLoaded', function () {
  (function () {

    window.Pusher = Pusher;
    window.Echo = new Echo({   
        broadcaster: 'pusher',
        key: window.EchoConfig.pusherKey,
        cluster: window.EchoConfig.pusherCluster,
        forceTLS: true
    });

    // Variable declaration for table
    var importReconciliationNotesUrl = baseUrl + 'import-reconciliation-notes-tab/'
      ;
     
    // ajax setup
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }); 

    window.importReconciliationNoteEditor = function importReconciliationNoteEditor(data) {
      var vat_reg_id = data['vat_reg_id'];

      const importReconciliationNoteEditor = document.querySelector('#importreconciliation-note-editor-'+vat_reg_id); 
      // Initialize Quill Editor
      // ------------------------------
      if (importReconciliationNoteEditor) {
        var quill = new Quill('#importreconciliation-note-editor-'+vat_reg_id, {
          modules: {
            toolbar: '#importreconciliation-note-editor-toolbar-'+vat_reg_id
          },
          placeholder: 'Write your message... ',
          theme: 'snow'
        }); 

        quill.setText('');       
      }  
    }  

    //Load ImportReconciliation Notes
    window.loadImportReconciliationNotesTab = function loadImportReconciliationNotesTab(client_id, vat_reg_id, message = null)
    {       
      if($("#navs-importreconciliation-notes-"+ vat_reg_id + " #loader").length == 0)
      {
        var loadertext = '<!-- Bounce -->' +
              '<div class="sk-bounce sk-primary sk-center" id="loader">' +
                '<div class="sk-bounce-dot"></div>' +
                '<div class="sk-bounce-dot"></div>' +
              '</div>';        
        $(loadertext).insertAfter("#navs-importreconciliation-notes-"+ vat_reg_id + " #load-importreconciliation-notes");        
      }    

      $.ajax({        
        url: `${baseUrl}import-reconciliation-notes-tab/${vat_reg_id}`,
        type: 'GET',
        data: {client_id : client_id},
        success: function (result) { 
          $("#navs-importreconciliation-notes-"+ vat_reg_id + " #loader").remove();    
          $("#navs-importreconciliation-notes-"+ vat_reg_id + " #load-importreconciliation-notes").html("");

          if(result['view'] == "")     
            $("#navs-importreconciliation-notes-"+ vat_reg_id + " #load-importreconciliation-notes").html("No Notes.");            
          else    
            $("#navs-importreconciliation-notes-"+ vat_reg_id + " #load-importreconciliation-notes").append(result['view']);

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

    function loadAllImportReconciliationNotes(except_id = null)
    {
      $(".navs-importreconciliation-notes").each(function () {
        var data = $(this).data();  
        var client_id = data.client_id;
        var vat_reg_id = data.vat_reg_id;
        
        var refreshTab = true;
        if(except_id == vat_reg_id)
          refreshTab = false;

        if(refreshTab)
        {
          importReconciliationNoteEditor(data);   
          loadImportReconciliationNotesTab(client_id, vat_reg_id);
        }
      }); 
    }

    loadAllImportReconciliationNotes();

    function clearImportReconciliationNotes(vat_reg_id = null)
    {
      $('#note-id-' + vat_reg_id).val('');
      $('#importreconciliation-note-type-' + vat_reg_id).val('');      
      $('#importreconciliation-note-editor-'+ vat_reg_id+' .ql-editor').html('');

      $('#importreconciliation-selectedCountries-' + vat_reg_id).selectpicker('deselectAll');

      $('#btn-importreconciliation-note-save-' + vat_reg_id).html('Save');
    }

    //Open ImportReconciliation Notes Modal
    $(document).on("click", ".btn-open-importreconciliation-notes", function(event)
    {
      var btn_importreconciliation_note_open = $(this);
      var data = $(this).data();
     
      var vat_reg_id = data.vat_reg_id;
      
      clearImportReconciliationNotes(vat_reg_id);
      
      $('#importreconciliationNotesModal-' + vat_reg_id).modal('show');
    });

    //Save ImportReconciliation Notes
    $(document).on("submit", ".frm-importreconciliation-notes", function(event)
    {
      event.preventDefault();

      var ql_editor = $(this).find(".ql-editor");
      
      if(ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "")
      {
        Swal.fire({
          title: 'Error',
          text: 'Please type note',
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
        var vat_reg_id = data['vat_reg_id']; 

        $("#importreconciliation-note-quill-"+vat_reg_id).val(ql_editor.html());

        var formData = new FormData(this);

        // Swal.fire({
        //   title: 'Are you sure?',       
        //   text: "You want to save the note!",
        //   icon: 'warning',
        //   showCancelButton: true,
        //   confirmButtonText: 'Yes, Save note!',
        //   customClass: {
        //     confirmButton: 'btn btn-primary me-2',
        //     cancelButton: 'btn btn-label-secondary'
        //   },
        //   buttonsStyling: false
        // }).then(function (result) {        
        //   if (result.value) {
                         
            var btn_importreconciliation_note_save = $("#" + formId + " #btn-importreconciliation-note-save-" + vat_reg_id);
            btn_importreconciliation_note_save.attr('disabled', 'disabled');
            btn_importreconciliation_note_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Saving...');
            
            $.ajax({            
              url: `${baseUrl}import-reconciliation-notes-tab/${vat_reg_id}`,
              type: 'POST',
              dataType: "JSON",
              data: formData,
              processData: false,
              contentType: false,
              success: function (result) {

                if(result)    
                {    
                  var vat_reg_id = result.vat_reg_id;
                  var client_id = result.client_id;

                  btn_importreconciliation_note_save.removeAttr('disabled');
                  btn_importreconciliation_note_save.html('Saved');
                  
                  var modalId = "importreconciliationNotesModal-"+ vat_reg_id;
                  $('#'+ modalId).modal('hide');

                  //ql_editor.html('');
                  $(".navs-importreconciliation-notes").each(function () {
                    var data = $(this).data();
                    importReconciliationNoteEditor(data);    
                  }); 

                  //$("#importreconciliation-note-type-" + vat_reg_id).val('');
                  clearImportReconciliationNotes(vat_reg_id);

                  //var message = {message_title: 'Notes saved!', message_text: 'Notes has been saved.'};
                  //loadImportReconciliationNotesTab(client_id, vat_reg_id, message);                
                  //loadImportReconciliationNotesTab(client_id, vat_reg_id);

                  loadAllImportReconciliationNotes();
                }
              },
              error: function (error) {
                console.log(error);
              }
            }); 

          // } else if (result.dismiss === Swal.DismissReason.cancel) {
          //   Swal.fire({
          //     title: 'Cancelled',
          //     text: 'Cancelled notes :)',
          //     icon: 'error',
          //     customClass: {
          //       confirmButton: 'btn btn-success'
          //     }
          //   });
          // }
        //});   
      } //null editor
    });

    //Edit ImportReconciliation Notes
    $(document).on("click", ".btn-edit-importreconciliation-note", function(event)
    {
      var btn_importreconciliation_note_edit = $(this);
      var data = $(this).data();

      var note_id = data.note_id;
      var vat_reg_id = data.vat_reg_id;
      var note_type = data.note_type;
      var note_comment = data.note_comment;
      var note_countries = data.note_countries;

      clearImportReconciliationNotes(vat_reg_id);

      $('#ir-note-id-' + vat_reg_id).val(note_id);
      $('#importreconciliation-note-type-' + vat_reg_id).val(note_type);      
      $('#importreconciliation-note-editor-'+ vat_reg_id+' .ql-editor').html(note_comment);
      
      // Convert to array and remove extra spaces
      var arr_note_countries = note_countries.split(',').map(function(item) {
          return item.trim();
      });
      $('#importreconciliation-selectedCountries-' + vat_reg_id).selectpicker('deselectAll');
      $('#importreconciliation-selectedCountries-'+ vat_reg_id).selectpicker('val', arr_note_countries);
      
      $('#importreconciliationNotesModal-' + vat_reg_id).modal('show');
    });
    
    //Delete ImportReconciliation Notes
    $(document).on("click", ".btn-delete-importreconciliation-note", function(event)
    {
      var btn_importreconciliation_note_delete = $(this);
      var data = $(this).data();

      var note_id = data.note_id;
      var vat_reg_id = data.vat_reg_id;

      Swal.fire({
          title: 'Are you sure?',       
          text: "You want to delete the note!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Delete note!',
          customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {        
          if (result.value) {
                                     
            btn_importreconciliation_note_delete.attr('disabled', 'disabled');
            btn_importreconciliation_note_delete.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Deleting...');
            
            $.ajax({            
              url: `${baseUrl}import-reconciliation-notes-tab/${note_id}`,
              type: 'DELETE',    
              data: { vat_reg_id: vat_reg_id },            
              success: function (result) { 
                if(result) 
                {
                  var client_id = result.client_id;

                  //var message = {message_title: 'Notes deleted!', message_text: 'Notes has been deleted.'};
                  //loadImportReconciliationNotesTab(client_id, vat_reg_id, message);

                  loadImportReconciliationNotesTab(client_id, vat_reg_id);
                }
              },
              error: function (error) {
                console.log(error);
              }
            }); 

        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled delete note :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }); 
    });

    window.Echo.channel('importreconciliation-notes-channel').listen('.ImportReconciliationNotesEvent', (event) => {  
        console.log('ImportReconciliation Notes Event:', event);        
        var vat_reg_id = event.vat_reg_id;
        
        loadAllImportReconciliationNotes();
    });

  })();
});
