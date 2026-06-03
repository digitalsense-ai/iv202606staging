/**
 * VAT Return Notes
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
    var vatReturnNotesUrl = baseUrl + 'vat-return-notes-tab/'
      ;
     
    // ajax setup
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    }); 

    window.vatReturnsNoteEditor = function vatReturnsNoteEditor(data) {
      var vat_reg_id = data['vat_reg_id'];

      const vatReturnsNoteEditor = document.querySelector('#vatreturn-note-editor-'+vat_reg_id); 
      // Initialize Quill Editor
      // ------------------------------
      if (vatReturnsNoteEditor) {
        var quill = new Quill('#vatreturn-note-editor-'+vat_reg_id, {
          modules: {
            toolbar: '#vatreturn-note-editor-toolbar-'+vat_reg_id
          },
          placeholder: 'Write your message... ',
          theme: 'snow'
        }); 

        quill.setText('');       
      }  
    }  

    //Load VATReturns Notes
    window.loadVATReturnsNotesTab = function loadVATReturnsNotesTab(client_id, vat_reg_id, message = null)
    {       
      if($("#navs-vatreturns-notes-"+ vat_reg_id + " #loader").length == 0)
      {
        var loadertext = '<!-- Bounce -->' +
              '<div class="sk-bounce sk-primary sk-center" id="loader">' +
                '<div class="sk-bounce-dot"></div>' +
                '<div class="sk-bounce-dot"></div>' +
              '</div>';        
        $(loadertext).insertAfter("#navs-vatreturns-notes-"+ vat_reg_id + " #load-vatreturn-notes");        
      }    

      $.ajax({        
        url: `${baseUrl}vat-return-notes-tab/${vat_reg_id}`,
        type: 'GET',
        data: {client_id : client_id},
        success: function (result) { 
          $("#navs-vatreturns-notes-"+ vat_reg_id + " #loader").remove();    
          $("#navs-vatreturns-notes-"+ vat_reg_id + " #load-vatreturn-notes").html("");

          if(result['view'] == "")     
            $("#navs-vatreturns-notes-"+ vat_reg_id + " #load-vatreturn-notes").html("No Notes.");            
          else    
            $("#navs-vatreturns-notes-"+ vat_reg_id + " #load-vatreturn-notes").append(result['view']);

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

    function loadAllVATReturnsNotes(except_id = null)
    {
      $(".navs-vatreturns-notes").each(function () {
        var data = $(this).data();  
        var client_id = data.client_id;
        var vat_reg_id = data.vat_reg_id;
        
        var refreshTab = true;
        if(except_id == vat_reg_id)
          refreshTab = false;

        if(refreshTab)
        {
          vatReturnsNoteEditor(data);   
          loadVATReturnsNotesTab(client_id, vat_reg_id);
        }
      }); 
    }

    loadAllVATReturnsNotes();

    function clearVATReturnsNotes(vat_reg_id = null)
    {
      $('#note-id-' + vat_reg_id).val('');
      $('#vatreturn-note-type-' + vat_reg_id).val('');      
      $('#vatreturn-note-editor-'+ vat_reg_id+' .ql-editor').html('');
      $('#vatreturn-selectedCountries-' + vat_reg_id).selectpicker('deselectAll');

      $('#btn-vatreturn-note-save-' + vat_reg_id).html('Save');
    }

    //Open VATReturn Notes Modal
    $(document).on("click", ".btn-open-vatreturn-notes", function(event)
    {
      var btn_vatreturn_note_open = $(this);
      var data = $(this).data();
     
      var vat_reg_id = data.vat_reg_id;
      
      clearVATReturnsNotes(vat_reg_id);
      
      $('#vatreturnNotesModal-' + vat_reg_id).modal('show');
    });

    //Save VATReturn Notes
    $(document).on("submit", ".frm-vatreturn-notes", function(event)
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

        $("#vatreturn-note-quill-"+vat_reg_id).val(ql_editor.html());

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
                         
            var btn_vatreturn_note_save = $("#" + formId + " #btn-vatreturn-note-save-" + vat_reg_id);
            btn_vatreturn_note_save.attr('disabled', 'disabled');
            btn_vatreturn_note_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Saving...');
            
            $.ajax({            
              url: `${baseUrl}vat-return-notes-tab/${vat_reg_id}`,
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

                  btn_vatreturn_note_save.removeAttr('disabled');
                  btn_vatreturn_note_save.html('Saved');
                  
                  var modalId = "vatreturnNotesModal-"+ vat_reg_id;
                  $('#'+ modalId).modal('hide');

                  //ql_editor.html('');
                  $(".navs-vatreturns-notes").each(function () {
                    var data = $(this).data();
                    vatReturnsNoteEditor(data);    
                  }); 

                  //$("#vatreturn-note-type-" + vat_reg_id).val('');
                  clearVATReturnsNotes(vat_reg_id);

                  //var message = {message_title: 'Notes saved!', message_text: 'Notes has been saved.'};
                  //loadVATReturnsNotesTab(client_id, vat_reg_id, message);                
                  loadVATReturnsNotesTab(client_id, vat_reg_id);

                  //loadAllVATReturnsNotes();
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

    //Edit VATReturn Notes
    $(document).on("click", ".btn-edit-vatreturn-note", function(event)
    {
      var btn_vatreturn_note_edit = $(this);
      var data = $(this).data();

      var note_id = data.note_id;
      var vat_reg_id = data.vat_reg_id;
      var note_type = data.note_type;
      var note_comment = data.note_comment;
      var note_countries = data.note_countries;

      clearVATReturnsNotes(vat_reg_id);

      $('#note-id-' + vat_reg_id).val(note_id);
      $('#vatreturn-note-type-' + vat_reg_id).val(note_type);      
      $('#vatreturn-note-editor-'+ vat_reg_id+' .ql-editor').html(note_comment);

      // Convert to array and remove extra spaces
      var arr_note_countries = note_countries.split(',').map(function(item) {
          return item.trim();
      });
      $('#vatreturn-selectedCountries-' + vat_reg_id).selectpicker('deselectAll');
      $('#vatreturn-selectedCountries-'+ vat_reg_id).selectpicker('val', arr_note_countries);

      $('#vatreturnNotesModal-' + vat_reg_id).modal('show');      
    });
    
    //Delete VATReturn Notes
    $(document).on("click", ".btn-delete-vatreturn-note", function(event)
    {
      var btn_vatreturn_note_delete = $(this);
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
                                     
            btn_vatreturn_note_delete.attr('disabled', 'disabled');
            btn_vatreturn_note_delete.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Deleting...');
            
            $.ajax({            
              url: `${baseUrl}vat-return-notes-tab/${note_id}`,
              type: 'DELETE',    
              data: { vat_reg_id: vat_reg_id },            
              success: function (result) { 
                if(result) 
                {
                  var client_id = result.client_id;

                  //var message = {message_title: 'Notes deleted!', message_text: 'Notes has been deleted.'};
                  //loadVATReturnsNotesTab(client_id, vat_reg_id, message);

                  loadVATReturnsNotesTab(client_id, vat_reg_id);
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

    window.Echo.channel('vatreturn-notes-channel').listen('.VATReturnNotesEvent', (event) => {  
        console.log('VATReturn Notes Event:', event);        
        var vat_reg_id = event.vat_reg_id;
        
        loadAllVATReturnsNotes();
    });

  })();
});
