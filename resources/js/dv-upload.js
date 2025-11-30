/**
 * File Upload
 */

'use strict';
Dropzone.autoDiscover = false;

$(function () {
  
  // Variable declaration
  var fileUrl = baseUrl + 'file/'; 
  var fileEmailUrl = baseUrl + 'file-email/'; 
  var disregardTaskUrl = baseUrl + 'disregard-task/'; 

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  //Load File List
  window.loadVATReturnsFileDocs = function loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id, message = null, modalId = null, from = null)
  {       
    var tab_name = "documents";
    if(file_type == 'ivf')
      tab_name = "importvat";
    else if(file_type == 'vatcontrol')
      tab_name = "control";
    else if(file_type == 'ci')
      tab_name = "commercial-invoices";

    if($("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertAfter("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list");  

      $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").addClass('loader-open');      
    }    

    $.ajax({
      data: {client_id: client_id, vat_reg_id: vat_reg_id, file_type: file_type, file_type_title: file_type_title},  
      url: `${fileUrl}${vat_reg_id}`,
      type: 'GET',
      success: function (result) { 
        $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #loader").remove();    
        $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").html("");

        if(modalId)
        {
          $(modalId).removeAttr("data-upload_success");
          $(modalId).modal('hide');
          $(document.body).removeClass("modal-open");
          $(document.body).removeAttr("style");
          $(".modal-backdrop").remove();                  
        }

        if(result['view'] == "")     
          $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").html("No "+ file_type_title +" uploaded.");            
        else    
          $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").append(result['view']);

        //Re-load Submitting Fields Values
        if(file_type == 'ivf' || file_type == 'vatreturn' || file_type == 'vatcontrol')       
        { 
          //loadVATReturnsSubmittingFieldsValue(vat_reg_id);                  
        //else if(file_type == 'vatreturn') 

          if(file_type == 'ivf') 
          {
            $("#navs-vatreturns-importvat-"+ vat_reg_id +" .import-vat-file-overview").each(function () {
              var data = $(this).data();
              
              loadImportVatTabLazy(data);

              loadImportReconciliationOverviewTabLazy(vat_reg_id);  
            });    
          }

          if(file_type == 'vatreturn' || file_type == 'vatcontrol') 
          {
            //$("#excel-template-select-"+ vat_reg_id).html('');
            //$("#excel-template-select-"+ vat_reg_id).html(result['excel_template_select']);

            $("#excel-template-select-"+ file_type + "-" + vat_reg_id).html('');
            $("#excel-template-select-"+ file_type + "-" + vat_reg_id).html(result['excel_template_select']);  

// console.log($("#accordionStyleAllTasks .accordion-item").length);
//             $(".accordion-item .navs-vatreturns-documents .form-select.excel-column-template").each(function () {
//             // $("#accordionStyleAllTasks .accordion-item").each(function () {
//             //   var navs_vatreturns_documents = $(this).find(".navs-vatreturns-documents");              
//             //   var excel_template_select = navs_vatreturns_documents.find(".form-select.excel-column-template");
             
//               excel_template_select.html('');
//               excel_template_select.html(result['excel_template_select']);
//             });
          }

          if(file_type == 'vatcontrol') 
          {console.log(vat_reg_id);
            loadVatReturnControlTab(vat_reg_id);
          }

          loadVATReturnsSubmittingFields(vat_reg_id);  
        } 
        else if(file_type == 'ci')
        {          
          var missing_commercial_invoice_count = $("#load-datas-"+ file_type + "-" + vat_reg_id + " span").html();
          $("#btn-commercial-invoices-" + vat_reg_id + " span").html(missing_commercial_invoice_count);
        }
                  
        loadHistoryTabLazy(vat_reg_id);

        if(message)
          Swal.fire({
            icon: (message['message_icon']) ? message['message_icon'] : 'success',
            title: message['message_title'],
            text: message['message_text'],
            customClass: {
              confirmButton: (message['message_confirmButton']) ? message['message_confirmButton'] : 'btn btn-success'
            }
          });       

        if(file_type == 'documents' || file_type == 'vatreturn' || file_type == 'vatcontrol')
        {
          //var id = (file_type + '-' + vat_reg_id) + ((file_type == 'vatreturn') ? '-1' : '-0');  
          var id = file_type + '-' + vat_reg_id + '-0';  

          $("#uploadSingleModal-"+ id).remove();

          $(result['doc_modal']).insertBefore("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list");

          if($('#dropzone-' + id).length == 0)
            id = id + '-single'; 

          loadFileDropzone('#dropzone-' + id, file_type);

          if(file_type == 'vatreturn')
          {
            // var id = file_type + '-' + vat_reg_id + '-1';  

            // $("#uploadSingleModal-"+ id).remove();

            // $(result['doc_modal']).insertBefore("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list");

            // if($('#dropzone-' + id).length == 0)
            //   id = id + '-single'; 

            // loadFileDropzone('#dropzone-' + id, file_type);
            if(from)
            {

            }
            else
              loadOverviewTabLazy(vat_reg_id, true);  
          }
        }
        else
        {
          //loadDropzone("#dropzone-multi-"+vat_reg_id);
          if(file_type == 'cas' || file_type == 'dda')                 
            showHideCasDdaHeading(file_type, tab_name, vat_reg_id);
          
          $("#navs-vatreturns-"+ tab_name +"-" + vat_reg_id + " #load-"+ file_type + "-list" + " .dropzone-file").each(function () {
            var dropzone_data = $(this).data();            
            var file_id = (dropzone_data['d_id']) ? dropzone_data['d_id'] : ((dropzone_data['file_id']) ? dropzone_data['file_id'] : 0);  

            var id = $(this).attr('id');                 
            loadFileDropzone('#'+ id, dropzone_data['file_type']);              
          });
        }        

        $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").removeClass('loader-open');
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.log('error: ' + textStatus);
        console.log('errorThrown: ' + errorThrown);
      }
    });
  }

  //Load File List
  window.loadImportReconciliationFileDocs = function loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id, message = null, modalId = null)
  {       
    var tab_name = "documents";
    if(file_type == 'ircontrol')
      tab_name = "control";
    
    if($("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertAfter("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list");  

      $("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").addClass('loader-open');      
    }    

    $.ajax({
      data: {client_id: client_id, vat_reg_id: vat_reg_id, file_type: file_type, file_type_title: file_type_title},  
      url: `${fileUrl}${vat_reg_id}`,
      type: 'GET',
      success: function (result) { 
        $("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #loader").remove();    
        $("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").html("");

        if(modalId)
        {
          $(modalId).removeAttr("data-upload_success");
          $(modalId).modal('hide');
          $(document.body).removeClass("modal-open");
          $(document.body).removeAttr("style");
          $(".modal-backdrop").remove();                  
        }

        if(result['view'] == "")     
          $("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").html("No "+ file_type_title +" uploaded.");            
        else    
          $("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").append(result['view']);

        $("#excel-template-select-"+ file_type + "-" + vat_reg_id).html('');
        $("#excel-template-select-"+ file_type + "-" + vat_reg_id).html(result['excel_template_select']);    
                  
        loadImportReconciliationHistoryTab(vat_reg_id);

        if(message)
          Swal.fire({
            icon: (message['message_icon']) ? message['message_icon'] : 'success',
            title: message['message_title'],
            text: message['message_text'],
            customClass: {
              confirmButton: (message['message_confirmButton']) ? message['message_confirmButton'] : 'btn btn-success'
            }
          });       
              
        var id = file_type + '-' + vat_reg_id + '-0';  

        $("#uploadSingleModal-"+ id).remove();

        $(result['doc_modal']).insertBefore("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list");

        if($('#dropzone-' + id).length == 0)
          id = id + '-single'; 

        loadFileDropzone('#dropzone-' + id, file_type);          

        $("#navs-importreconciliation-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").removeClass('loader-open');
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.log('error: ' + textStatus);
        console.log('errorThrown: ' + errorThrown);
      }
    });
  }

  function showHideCasDdaHeading(file_type, tab_name, vat_reg_id)
  {    
    if($.trim($("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list ul").html()) == '')
    {
      $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" .divider." + file_type).hide();
      $("#navs-vatreturns-"+ tab_name +"-"+vat_reg_id+" #load-"+ file_type +"-list").hide();
    }    
  }  

  $(document).on('shown.bs.tab', 'button.btn-documents[data-bs-toggle="tab"]', function () {    
    var tab_name = 'documents';
    var vat_reg_id = $(this).data('vat_reg_id'); 
    
    showHideCasDdaHeading('cas', tab_name, vat_reg_id);
    showHideCasDdaHeading('dda', tab_name, vat_reg_id);
  });

  $(document).on('shown.bs.tab', 'button.btn-submitting-fields[data-bs-toggle="tab"]', function () {        
    var vat_reg_id = $(this).attr('id').replace('btn-submitting-fields-', ''); 
    
    loadVATReturnsSubmittingFields(vat_reg_id);
  });

  $(document).on('shown.bs.tab', 'button.btn-vatreturn-control[data-bs-toggle="tab"]', function () {        
    var vat_reg_id = $(this).attr('id').replace('btn-vatreturn-control-', ''); 
    
    loadVatReturnControlTab(vat_reg_id);
  });
  
  //Load Dropzone
  window.loadFileDropzone = function loadFileDropzone(element, file_type)
  {
    const previewTemplate = `<div class="dz-preview dz-file-preview">
    <div class="dz-details">
      <div class="dz-thumbnail">
        <img data-dz-thumbnail>
        <span class="dz-nopreview">No preview</span>
        <div class="dz-success-mark"></div>
        <div class="dz-error-mark"></div>
        <div class="dz-error-message"><span data-dz-errormessage></span></div>
        <div class="progress">
          <div class="progress-bar progress-bar-primary" role="progressbar" data-dz-uploadprogress></div>
        </div>
      </div>
      <div class="dz-filename" data-dz-name></div>
      <div class="dz-size" data-dz-size></div>
    </div>
    </div>`;
    
    var accepted_files = ".pdf,.xls,.xlsx,.doc,.docx,.xml,.csv";
    var max_files = 1;
    var parallel_uploads = 1;
    var upload_multiple = false;
    var success_event = "success";
    if(file_type == 'pivs' || file_type == 'c79')
      accepted_files = ".pdf";
    else if(file_type == 'documents')
    {
      accepted_files = ".pdf";
      max_files = null;
      success_event = "successmultiple";
      //parallel_uploads = 10;
      upload_multiple = true;
    }
    else if(file_type == 'cas')
      accepted_files = ".xls,.xlsx";     
      //accepted_files = ".csv,.xls,.xlsx";     
    else if(file_type == 'dda')
      accepted_files = ".pdf";
    else if(file_type == 'ivf')
    {
      accepted_files = ".xml,.pdf";
      max_files = 2;
      parallel_uploads = 2;
    }
    else if(file_type == 'ci')
    {
      accepted_files = ".pdf";
      max_files = null;
    }
    else if(file_type == 'vatreturn')
    {
      accepted_files = ".xls,.xlsx,.xml,.csv";
      max_files = null;      
    }
    else if(file_type == 'vatcontrol' || file_type == 'ircontrol')    
      accepted_files = ".xls,.xlsx";      
    else if(file_type == 'vatcheck' || file_type == 'iranyexcel')
    {
      accepted_files = ".xls,.xlsx";
      max_files = null;
    }

    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        previewTemplate: previewTemplate,
        maxFilesize: 10,
        parallelUploads: parallel_uploads,
        maxFiles: max_files,
        uploadMultiple: upload_multiple,
        // renameFile: function(file) {
        //     var dt = new Date();
        //     var time = dt.getTime();
        //     return time+file.name;
        // },
        acceptedFiles: accepted_files,        
        timeout: 180000,       
        init : function() {
          var file_type = this.element.getAttribute('data-file_type');
          var vat_reg_id = this.element.getAttribute('data-vat_reg_id');
        
          var file_id = (this.element.getAttribute('data-d_id')) ? this.element.getAttribute('data-d_id') : ((this.element.getAttribute('data-file_id')) ? this.element.getAttribute('data-file_id') : 0);     
          var modal_type = this.element.getAttribute('data-modal_type');

          var id = file_type + '-' + vat_reg_id + '-' + file_id;

          var myDropzone = this;
          var dropzoneId = myDropzone.element.getAttribute('id');
          
          var modalId = "";

          if(modal_type == 'single')
          {
            modalId = "#uploadSingleModal-" + id;            
          }
          else
          {
            if($("#uploadModal-" + id).length > 0) 
              modalId = "#uploadModal-" + id;
            //else if($("#uploadSingleModal-" + id).length > 0)
              //modalId = "#uploadSingleModal-" + id;
            else if($("#overwriteModal-" + id).length > 0)             
              modalId = "#overwriteModal-" + id;
          }

          myDropzone.on("addedfile", function() {            
            $(modalId + ' .btn-close').prop("disabled", true);

            // if(file_type == 'documents' || file_type == 'vatreturn')
            // {
            //   $(modalId + ' .btn-save-close-document-file').removeClass('btn-danger');
            //   $(modalId + ' .btn-save-close-document-file').removeClass('disabled');
            //   $(modalId + ' .btn-save-close-document-file').addClass('btn-success');
            //   $(modalId + ' .btn-save-close-document-file').removeAttr("disabled");
            // }            
          });

          if(success_event == "successmultiple")
            var success_uploaded_file = 0;
          myDropzone.on(success_event, function (file, response) {           
              if(response == "")   
              {           
                myDropzone.removeFile(file);   
                $("#dropzone-" + id).addClass("dz-started");               
                Swal.fire({
                  title: 'Error!',
                  text: 'Cannot upload ' + file_type + ' file.',
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });          
              }
              else
              {  
              console.log(success_event) ;
                if(success_event == "successmultiple")
                {
                  success_uploaded_file++;
console.log(myDropzone.files.length + ' == ' + success_uploaded_file);
                  if(myDropzone.files.length == success_uploaded_file)
                  {        
                    $(modalId).attr("data-upload_success", 1);  
                    $(modalId + ' .btn-close').removeAttr('disabled');
                   
                    if(file_type == 'documents' || file_type == 'vatreturn')
                    {
                      $(modalId + ' .btn-save-close-document-file').removeAttr("disabled");
                      if(file_type == 'vatreturn')
                        $(modalId + ' .btn-save-close-document-file').html('OK');
                      else
                        $(modalId + ' .btn-save-close-document-file').html('Save');
                      $(modalId + ' .btn-save-close-document-file').removeClass('btn-success');
                      $(modalId + ' .btn-save-close-document-file').addClass('btn-danger');
                     
                      $(modalId).modal('hide');
                    }
                  }
                }
                else
                {
                  $(modalId).attr("data-upload_success", 1);  
                  //console.log("addedd upload success");
                  //console.log($(modalId).data("upload_success"));

                  $(modalId + ' .btn-close').removeAttr('disabled');
                  if(file_type == 'documents' || file_type == 'vatreturn')
                  {
                    $(modalId + ' .btn-save-close-document-file').removeClass('btn-danger');
                    $(modalId + ' .btn-save-close-document-file').removeClass('disabled');
                    $(modalId + ' .btn-save-close-document-file').addClass('btn-success');
                    $(modalId + ' .btn-save-close-document-file').removeAttr("disabled");
                  }         

                  if($(modalId + ' #table_to_excel').val() != '')
                  {
                    //enable send button
                    $(modalId + ' .btn-send-email-file').addClass('btn-success');                
                    $(modalId + ' .btn-send-email-file').removeClass('btn-danger');

                    if($(modalId + ' .email-to-item .switch input[name="send_to"]').prop('checked'))
                      $(modalId + ' .btn-send-email-file').removeClass('disabled');                    
                  }

                  if($(modalId + ' .email-to-item .switch').length > 0)
                  {
                    //enable send button
                    $(modalId + ' .btn-send-email-file').addClass('btn-success');                
                    $(modalId + ' .btn-send-email-file').removeClass('btn-danger');

                    if($(modalId + ' .email-to-item .switch input[name="send_to"]').prop('checked'))
                      $(modalId + ' .btn-send-email-file').removeClass('disabled');
                    //$(modalId + ' .btn-send-email-file').removeAttr('disabled');     
                  }
                }

                $.each(response, function(indexr, valr) { 
                  var previewElement = $("#" + dropzoneId + " .dz-preview");//$("#dropzone-" + id + " .dz-preview");                  
                  $.each(previewElement, function(index, val) {
                    if(!$(val).attr("id"))
                    {
                      $(val).attr("id",valr['id']);
                      $(val).attr("file_type",valr['file_type']);
                      $(val).attr("file_type_title",valr['file_type_title']);

                      $(val).attr("modal_type",modal_type);

                      return false;
                    }                                                    
                  });  
                });                        
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
            $(modalId + ' .btn-close').removeAttr('disabled');

            if(file_type == 'documents' || file_type == 'vatreturn')
              $(modalId + ' .btn-save-close-document-file').removeAttr("disabled");
              
              console.log("errorrrrrrrrrrrrr");    
              console.log(file); 
              console.log(errorMessage);              
          });                
        },
        addRemoveLinks: true,
        removedfile: function(file) {           
          var file_type = file.previewElement.getAttribute('file_type');
          var file_type_title = file.previewElement.getAttribute('file_type_title');
          var file_id = file.previewElement.getAttribute('id');      
          var modal_type = file.previewElement.getAttribute('modal_type');          
         
         if(file_id != null)
         {
          //console.log(file_id);
            var modalId = '#' + $(".dz-preview#"+file_id).closest(".modal-file").attr("id");
            $(modalId + ' .btn-close').prop("disabled", true);

            if(file_type == 'documents' || file_type == 'vatreturn')
              $(modalId + ' .btn-save-close-document-file').prop("disabled", true);

           $.ajax({
             type: 'DELETE',
             url: `${fileUrl}${file_id}`,  
             data: {file_type: file_type, file_type_title: file_type_title},          
             success: function(data){    
                if(data['status'] == "deleted" || data['status'] == "error")
                {
                  $(modalId + ' .btn-close').removeAttr('disabled');

                  if(file_type == 'documents' || file_type == 'vatreturn')
                    $(modalId + ' .btn-save-close-document-file').removeAttr("disabled");
                  
                  //var file_type = data['file_type'];                
                  //var vat_reg_id = data['vat_reg_id'];
                  
                  //var id = file_type + '-' + vat_reg_id + '-' + file_id;
                 
                  //var modalId = '#' + $(".dz-preview#"+file_id).closest(".modal-file").attr("id");
                  // if(modal_type == 'single')
                  // {
                  //   modalId = "#uploadSingleModal-" + id;
                  // }
                  // else
                  // {
                  //   if($("#uploadModal-" + id).length > 0)                     
                  //     modalId = "#uploadModal-" + id;                                                     
                  //   else if($("#overwriteModal-" + id).length > 0)                     
                  //     modalId = "#overwriteModal-" + id; 
                  // }                  
                 //console.log(modalId);
                  //disable send button
                  $(modalId + ' .btn-send-email-file').removeClass('btn-success');                  
                  $(modalId + ' .btn-send-email-file').addClass('btn-danger'); 
                  $(modalId + ' .btn-send-email-file').addClass('disabled');
                  $(modalId + ' .btn-send-email-file').attr('disabled', 'disabled'); 

                }              
             },
             error: function(jqXHR, textStatus, errorThrown){
                console.log('error: ' + textStatus);
             }
           });
         }
         var _ref;
          return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
       }
    });
  }

  //Document Save & close
  $(document).on('click', '.btn-save-close-document-file', function () {
    var btn_save_close_document = $(this);
    var file_type = $(this).data('file_type');

    // btn_save_close_document.removeClass('btn-danger');
    // btn_save_close_document.addClass('btn-success');
    btn_save_close_document.attr('disabled', 'disabled');

    if(file_type == 'vatreturn')
    {
      // btn_save_close_document.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
      //   'OK...');
      var vat_reg_id = $(this).data('vat_reg_id');
      var file_id = ($(this).data('d_id')) ? $(this).data('d_id') : (($(this).data('file_id')) ? $(this).data('file_id') : 0);     
      var id = file_type + '-' + vat_reg_id + '-' + file_id;
      var modalId = "#uploadSingleModal-" + id;
      $(modalId).modal('hide');
    }
    else
      btn_save_close_document.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Saving...');
  });

  //Disregard Task
  $(document).on('click', '.disregard-task', function () {
      var btn_disregard_task = $(this);
      var data = btn_disregard_task.data();

      var file_type_title = data['file_type_title'];  
      var file_type = data['file_type']; 
      var vat_reg_id = data['vat_reg_id'];  
      var client_id = data['client_id'];  
      var month_year = data['month_year'];  
      var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0); 
      var modal_type = data['modal_type'];   

      var id = file_type + '-' + vat_reg_id + '-' + file_id;

      Swal.fire({
        title: 'Are you sure?',
        text: "You want to disregard the "+ file_type_title +" task!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Disregard!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          
          btn_disregard_task.attr('disabled', 'disabled');
          btn_disregard_task.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Disregarding...');
          
          $.ajax({           
            type: 'POST',
            url: `${disregardTaskUrl}${vat_reg_id}`,  
            data: {file_type: file_type, file_type_title: file_type_title, client_id: client_id, month_year: month_year}, 
            success: function (result) {
              if(result['status'] == 'disregarded')    
              { 
                btn_disregard_task.removeAttr('disabled');
                btn_disregard_task.html('disregard this task');

                $("#disregard-task-row-" + id).remove();  
                
                // if($("#uploadModal-" + id).length > 0)               
                //   var modalId = "uploadModal-"+ id;                 
                // else               
                //   var modalId = "overwriteModal-"+ id;  

                var modalId = "";
                if(modal_type == 'single')
                {
                  modalId = "#uploadSingleModal-" + id;

                  if($("#accord-" + id).length > 0)
                  {
                    var single_task = $("#accord-" + id).parent().parent(".accordion-item.card.sort-item");   
                    if(single_task.length > 0)     
                      single_task.hide();    
                  }
                }
                else
                {
                  if($("#uploadModal-" + id).length > 0)               
                    modalId = "uploadModal-"+ id;                 
                  else               
                    modalId = "overwriteModal-"+ id;  
                }
                              
                var message = {message_title: file_type_title + ' task disregarded!', message_text: file_type_title + ' task has been disregarded.'};

                if(file_type == 'iranyexcel')
                  loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, modalId);
                else  
                  loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, modalId);
              }
              else
              {
                btn_disregard_task.removeAttr('disabled');               
                btn_disregard_task.html("disregard this task");
              }
            },
            error: function (error) {
              console.log(error);
            }
          }); 

              
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled Disregard :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }); 
  });

  //Delete file
  $(document).on('click', '.btn-delete-file', function () {
      var btn_delete_file = $(this);
      var data = btn_delete_file.data();

      var file_type_title = data['file_type_title'];  
      var file_type = data['file_type']; 
      var vat_reg_id = data['vat_reg_id'];  
      var client_id = data['client_id'];  
      var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);          

      var id = file_type + '-' + vat_reg_id + '-' + file_id;

      Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete the "+ file_type_title +" file!",
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
          
          btn_delete_file.attr('disabled', 'disabled');
          btn_delete_file.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Deleting...');
          
          $.ajax({           
            type: 'DELETE',
            url: `${fileUrl}${file_id}`,
            data: {file_type: file_type, file_type_title: file_type_title},  
            success: function (result) {console.log(result);
              // if(result['status'] == 'deleted')    
              // {                   
              //   // if($("#uploadModal-" + id).length > 0)               
              //   //   var modalId = "#uploadModal-"+ id;                 
              //   // else               
              //   //   var modalId = "#overwriteModal-"+ id;  

                var modalId = "";
                if($("#uploadModal-" + id).length > 0) 
                  modalId = "#uploadModal-" + id;
                else if($("#uploadSingleModal-" + id).length > 0)
                  modalId = "#uploadSingleModal-" + id;
                else if($("#overwriteModal-" + id).length > 0)             
                  modalId = "#overwriteModal-" + id;

                var message = {message_title: file_type_title + ' file deleted!', message_text: file_type_title + ' file has been deleted.'};

                if(file_type == 'iranyexcel')
                  loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, modalId);   
                else
                  loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, modalId);                                                   
              // }
              // else
              // {
              //   if(result['status'] == 'error')    
              //   {
              //   }
              //   btn_delete_file.removeAttr('disabled');               
              //   btn_delete_file.html("Delete");
              // }
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

  //Download file
  $(document).on('click', '.btn-download-file', function () {
      var btn_download_file = $(this);     
      var data = btn_download_file.data();

      var file_type = data['file_type']; 
      var file_id = data['file_id'];
      var file_extension = (data['file_extension']) ? data['file_extension'] : '';

      var original_file = (data['original_file']) ? data['original_file'] : false;
      
      //var o_file_id = (btn_download_file.is('button')) ? '' : $(this).val();

      if(!original_file)
        // btn_download_file.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        //       'Downloading...');
      //else
      {
        if(file_extension == 'pdf')
        {
          // if(file_type == 'company')
          // {
          //   btn_download_file.html('<i class="tf-icons bx bxs-download me-1"></i> Downloading...');
          //   btn_download_file.attr('disabled', 'disabled');
          // }
          // else
          // {
            btn_download_file.removeClass("btn-danger");
            btn_download_file.addClass("btn-outline-danger");
          //}
        }
        else
        {
          // if(file_type == 'company')
          // {
          //   btn_download_file.html('<i class="tf-icons bx bxs-download me-1"></i> Downloading...');
          //   btn_download_file.attr('disabled', 'disabled');
          // }
          // else
          // {
            btn_download_file.removeClass("btn-primary");
            btn_download_file.addClass("btn-outline-primary");
          //}
        }

        if((file_type == 'vatcontrol' || file_type == 'ircontrol') && btn_download_file.hasClass('btn-download-missing-invoices'))
          btn_download_file.html('<i class="tf-icons bx bxs-download me-1"></i> Downloading...');
        else
        {
          //if(file_type != 'company')
            btn_download_file.html('<div class="sk-bounce sk-primary sk-center">' +
                '<div class="sk-bounce-dot"></div>' +
                '<div class="sk-bounce-dot"></div>' +
              '</div>');
        }
      }

      var isdownloadable = true;
      var o_file_id = '';
      if(!btn_download_file.is('button'))
      {
        o_file_id = btn_download_file.val();
        if(o_file_id == "")
          isdownloadable = false;
      }

      if(isdownloadable)
      {
        $.ajax({      
          url: `${fileUrl}${file_id}/download`,
          type: 'GET',       
          data: {file_type: file_type, original_file: original_file, o_file_id: o_file_id},  
          success: function (data) {

            if(original_file)
            {
              btn_download_file.val("");
              //btn_download_file.html('<span class="tf-icons bx bxs-download"></span>Original File');
            }
            else
            {
              if(file_extension == 'pdf')
              { 
                // if(file_type == 'company')
                // {
                //   btn_download_file.html('<i class="tf-icons bx bxs-download me-1"></i> Download');
                //   btn_download_file.removeAttr('disabled');
                // }
                // else
                // {               
                  btn_download_file.addClass("btn-danger");
                  btn_download_file.removeClass("btn-outline-danger");
                //}
              }
              else
              {
                // if(file_type == 'company')
                // {
                //   btn_download_file.html('<i class="tf-icons bx bxs-download me-1"></i> Download');
                //   btn_download_file.removeAttr('disabled');
                // }
                // else
                // {
                  btn_download_file.addClass("btn-primary");
                  btn_download_file.removeClass("btn-outline-primary");
                //}
              }

              if(file_type == 'vatcontrol' && btn_download_file.hasClass('btn-download-missing-invoices'))
                btn_download_file.html('<i class="tf-icons bx bxs-download me-1"></i> Download Missing Invoices');
              else  
              {
                //if(file_type != 'company')
                  btn_download_file.html('<span class="tf-icons bx bxs-download"></span>');
              }
            }

            window.open(data, '_blank');          
          },
          error: function (err) {
            console.log(err);     
          }
        });
      }
  });

  //View file
  $(document).on('click', '.btn-view-file', function () {
      $("#offcanvasDocumentView #docViewer").hide();
      $("#offcanvasDocumentView").offcanvas('show');      
      if($("#offcanvasDocumentView #loader").length == 0)
      {
        var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
                '<div class="sk-bounce-dot"></div>' +
                '<div class="sk-bounce-dot"></div>' +
              '</div>';        
        $(loadertext).insertAfter("#offcanvasDocumentView #docViewer");  
      }

      var btn_view_file = $(this);     
      var data = btn_view_file.data();

      var file_type = data['file_type']; 
      var file_id = data['file_id'];
      var file_extension = (data['file_extension']) ? data['file_extension'] : '';

      var original_file = (data['original_file']) ? data['original_file'] : false;
            
      if(!original_file)        
      {
        btn_view_file.removeClass("btn-warning");
        btn_view_file.addClass("btn-outline-warning");

        btn_view_file.html('<div class="sk-bounce sk-warning sk-center">' +
            '<div class="sk-bounce-dot"></div>' +
            '<div class="sk-bounce-dot"></div>' +
          '</div>');
      }

      var isdownloadable = true;
      var o_file_id = '';
      if(!btn_view_file.is('button'))
      {
        o_file_id = btn_view_file.val();
        if(o_file_id == "")
          isdownloadable = false;
      }

      if(isdownloadable)
      {
        $.ajax({      
          url: `${fileUrl}${file_id}/download`,
          type: 'GET',       
          data: {file_type: file_type, original_file: original_file, o_file_id: o_file_id, view_type: 'view'},  
          xhrFields: {
            responseType: 'blob' // get binary PDF
          },          
          //success: function (data) {
          success: function (data, status, xhr) {  
            if(original_file)
            {
              btn_view_file.val("");              
            }
            else
            {
              btn_view_file.addClass("btn-warning");
              btn_view_file.removeClass("btn-outline-warning");

              btn_view_file.html('<span class="fa-solid fa-eye"></span>');
            }
            
            const contentType = xhr.getResponseHeader('Content-Type') || 'application/octet-stream';
           console.log(contentType);
            // if(contentType.indexOf('xml') != -1)
            // {
            //   console.log(data);
            //   // const reader = new FileReader();
            //   // reader.onload = function () {
            //   //     const xmlText = reader.result;

            //   //     const parser = new DOMParser();
            //   //     const xmlDoc = parser.parseFromString(xmlText, "application/xml");

            //   //     const parseError = xmlDoc.getElementsByTagName("parsererror");
            //   //     if (parseError.length > 0) {
            //   //         console.error("Invalid XML:", parseError[0].textContent);
            //   //         return;
            //   //     }

            //   //     // Convert to simplified JSON
            //   //     const json = xmlToSimpleJson(xmlDoc.documentElement);
            //   //     console.log("Simplified JSON:", json);

            //   //     // Render as table
            //   //     const html = jsonToTable(json);
            //   //     $('#tableViewer').html(html);
            //   // };
            //   // reader.readAsText(data);
            // }
            // else
            // {
              const blob = new Blob([data], { type: contentType });
              const blobUrl = URL.createObjectURL(blob);
              $('#docViewer').attr('src', blobUrl); 
            //}
            
            //$("#offcanvasDocumentView").offcanvas('show');  

            $("#offcanvasDocumentView #loader").remove();
            $("#offcanvasDocumentView #docViewer").show();
          },
          // error: function (err) {
          //   console.log(err);     
          // }
          error: function(xhr) {
              console.error("Not valid XML:", xhr.responseText);
          }
        });
      }
  });

  /*
  // 🔄 Convert XML DOM to simplified JSON
  function xmlToSimpleJson(xml) {
      let obj = {};

      if (xml.children.length === 0) {
          return xml.textContent.trim();
      }

      for (let child of xml.children) {
          const key = child.nodeName;
          const value = xmlToSimpleJson(child);

          if (obj[key]) {
              if (!Array.isArray(obj[key])) obj[key] = [obj[key]];
              obj[key].push(value);
          } else {
              obj[key] = value;
          }
      }

      return obj;
  }

  // 🧾 Convert JSON to HTML Table
  function jsonToTable(data) {
      if (!data || typeof data !== 'object') return '';

      let rows = '';

      for (const key in data) {
          const value = data[key];

          if (Array.isArray(value)) {
              for (let item of value) {
                  rows += `<tr><td>${key}</td><td>${formatValue(item)}</td></tr>`;
              }
          } else {
              rows += `<tr><td>${key}</td><td>${formatValue(value)}</td></tr>`;
          }
      }

      return `<table border="1" cellpadding="8" style="border-collapse: collapse; font-family: sans-serif;">
                  <thead><tr><th>Key</th><th>Value</th></tr></thead>
                  <tbody>${rows}</tbody>
              </table>`;
  }

  // 📄 Format nested or primitive values for display
  function formatValue(value) {
      if (typeof value === 'object') {
          return '<pre>' + JSON.stringify(value, null, 2) + '</pre>';
      }
      return value;
  }
  */

  //Refresh file to load datas
  $(document).on('click', '.btn-refresh-file', function () {
      var btn_refresh_file = $(this);     
      var data = btn_refresh_file.data();
     
      var file_type_title = data['file_type_title'];  
      var file_type = data['file_type']; 
      var vat_reg_id = data['vat_reg_id'];  
      var client_id = data['client_id'];  
      var file_id = data['file_id'];
  
      btn_refresh_file.removeClass("btn-warning");
      btn_refresh_file.addClass("btn-outline-warning");

      btn_refresh_file.html('<div class="sk-bounce sk-warning sk-center">' +
          '<div class="sk-bounce-dot"></div>' +
          '<div class="sk-bounce-dot"></div>' +
        '</div>');
      
      $.ajax({      
        url: `${fileUrl}${file_id}/refresh`,
        type: 'GET',       
        data: {file_type: file_type},  
        success: function (data) {          
          btn_refresh_file.addClass("btn-warning");
          btn_refresh_file.removeClass("btn-outline-warning");

          btn_refresh_file.html('<span class="bx bx-refresh"></span>');    
          
          if(file_type == 'iranyexcel')
            loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id);
          else
            loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id);
        },
        error: function (err) {
          console.log(err);     
        }
      });     
  });

  //Update File Name
  $(document).on('click', '.btn-update-file-name', function () {
      var btn_update_file_name = $(this); 
      var data = btn_update_file_name.data();
      
      var file_type_title = data['file_type_title'];  
      var file_type = data['file_type']; 
      var vat_reg_id = data['vat_reg_id'];  
      var client_id = data['client_id'];  
      var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);  
           
      var id = file_type + '-' + vat_reg_id + '-' + file_id;        

      Swal.fire({
        title: 'Are you sure?',
        text: "You want to update the " + file_type_title + " file name!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Update!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          btn_update_file_name.attr('disabled', 'disabled');
          btn_update_file_name.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Renaming...');

          var formId = 'formFileName-' + id;

          $.ajax({      
            url: `${fileUrl}${file_id}`,
            data: $('#' + formId).serialize(),            
            type: 'PUT',       
            success: function (data) {
              
              btn_update_file_name.removeAttr('disabled'); 
              btn_update_file_name.html('<i class="bx bx-save me-1"></i>' +
                        '<span class="align-middle">Rename</span>');              
               
              $("span#display-file-name-" + id).html(' - ' + $("#file-name-" + id).val());              

              Swal.fire({
                icon: 'success',
                title: file_type_title + ' file name Updated!',
                text: file_type_title + ' file name has been Updated.',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });       
            },
            error: function (err) {
              console.log(err);     
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

  //Update File Number
  $(document).on('click', '.btn-update-file-number', function () {
      var btn_update_file_number = $(this); 
      var data = btn_update_file_number.data();
      
      var file_type_title = data['file_type_title'];  
      var file_type = data['file_type']; 
      var vat_reg_id = data['vat_reg_id'];  
      var client_id = data['client_id'];  
      var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);  
           
      var id = file_type + '-' + vat_reg_id + '-' + file_id;
      
      var number_type = "month total";
      if(file_type == 'documents')
        number_type = "number";


      Swal.fire({
        title: 'Are you sure?',
        text: "You want to update the " + file_type_title + " " + number_type + "!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Update!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          btn_update_file_number.attr('disabled', 'disabled');
          btn_update_file_number.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Updating...');

          var formId = 'formNumber-' + id;

          $.ajax({      
            url: `${fileUrl}${file_id}`,
            data: $('#' + formId).serialize(),
            //data: {'data' : $('#' + formId).serialize(), 'file_type_title' : file_type_title, 'file_type' : file_type,
            // 'vat_reg_id' : vat_reg_id, 'client_id' : client_id},
            type: 'PUT',       
            success: function (data) {
              
              btn_update_file_number.removeAttr('disabled'); 
              btn_update_file_number.html('<i class="bx bx-save me-1"></i>' +
                        '<span class="align-middle">Update</span>');              
               
              if(file_type == 'pivs')                             
              {
                //fileNumberTotal(formId, vat_reg_id); 

                loadVATReturnsSubmittingFields(vat_reg_id);               
              }

              Swal.fire({
                icon: 'success',
                title: file_type_title + ' ' + number_type + ' Updated!',
                text: file_type_title + ' ' + number_type + ' has been Updated.',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });       
            },
            error: function (err) {
              console.log(err);     
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

  //Send e-mail to Client  
  $(document).on('click', '.btn-send-email-file', function () {
    var formId = $(this).closest('.formEmail').attr('id'); 
    var modalId = $(this).closest('.modal-file').attr('id'); 
    
    var btn_send_email = $(this);   

    var data = btn_send_email.data();
   
    var file_type_title = data['file_type_title'];  
    var file_type = data['file_type']; 
    var vat_reg_id = data['vat_reg_id'];  
    var client_id = data['client_id'];  
    var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);  

    var id = file_type + '-' + vat_reg_id + '-' + file_id;

    var _valid = true;

    if($("#" + formId + " #payment_date-"+vat_reg_id).length > 0)
    {
      $("#" + formId + " #payment_date-"+vat_reg_id).removeClass("is-invalid");
      if($("#" + formId + " #payment_date-"+vat_reg_id).val() == "")
      {
        _valid = false;
        $("#" + formId + " #payment_date-"+vat_reg_id).addClass("is-invalid");
      }
    }    

    if(_valid)
    {
      Swal.fire({
        title: 'Are you sure?',
        text: (file_type == 'lock') ? "You want to lock the folder!" : "You want to send email to the selected users!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: (file_type == 'lock') ? 'Yes, Lock Folder!' : 'Yes, Send Email!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          btn_send_email.attr('disabled', 'disabled');
          btn_send_email.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Sending...');
          
          var re_send = (file_type == 'draft') ? '&re_send=1' : '';

          $.ajax({
            data: $('#' + formId).serialize() + re_send,
            type: 'POST',
            url: `${fileEmailUrl}${vat_reg_id}`,
            success: function (result) {
              if(result['success'])    
              {                            
                if(formId.indexOf('formSingleEmail-') != -1)
                {
                  //var modalId = "uploadSingleModal-"+ id;

                  var single_task = $("#accord-" + id).parent().parent(".accordion-item.card.sort-item");   
                  if(single_task.length > 0)     
                    single_task.hide();        
                }
               
                var message_title = 'Email sent!';
                var message_text = 'Email has been sent to client users';
                if(file_type == 'draft')              
                  message_text += ' for review.';             
                else if(file_type == 'lock')
                {
                  message_title = 'Email sent and Folder Locked!!';
                  message_text += '. The Folder locked and moved to archive.';
                }
                else
                  message_text += ' with '+ file_type_title + '.';
                
                var message = {message_title: message_title, message_text: message_text};                              
                //var message = {message_title: 'Email sent!', message_text: 'Email has been sent to client users with '+ file_type_title +'.'};                           
              }
              else            
                var message = {message_title: 'Error', message_text: result['message'] + ' :)', message_icon: 'error', message_confirmButton: 'btn btn-danger'};            

              if(file_type == 'draft' || file_type == 'lock')
              {
                var tab_name = 'overview';
                if(file_type == 'lock')
                {
                  tab_name = 'archive';
                  loadOverviewTabLazy(vat_reg_id, false, tab_name);

                  loadEnableDisableVatReturnsTabItems(client_id, vat_reg_id);
                  // $("#anyexcel-template-vatreturn-" + vat_reg_id).attr('disabled', 'disabled');
                  // $("#btn-upload-vatreturn-" + vat_reg_id).attr('disabled', 'disabled');

                  // $("#anyexcel-template-vatcontrol-" + vat_reg_id).attr('disabled', 'disabled');
                  // $("#btn-upload-vatcontrol-" + vat_reg_id).attr('disabled', 'disabled');
                  
                  // $("#dropzone-multi-" + vat_reg_id).addClass('dropzone-disabled');
                  // $("#btn-submitting-fields-" + vat_reg_id).addClass('disabled');
                  // $("#navs-vatreturns-documents-" + vat_reg_id + " .btn-upload-documents").attr('disabled', 'disabled');

                  // if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-pivs-list").length > 0)
                  //   loadVATReturnsFileDocs('pivs', 'Postponed import VAT statement', client_id, vat_reg_id); 

                  // if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-documents-list").length > 0)
                  //   loadVATReturnsFileDocs('documents', 'Documents', client_id, vat_reg_id);

                  // if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-c79-list").length > 0)
                  //   loadVATReturnsFileDocs('c79', 'C79', client_id, vat_reg_id);

                  // if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-cas-list").length > 0)
                  //   loadVATReturnsFileDocs('cas', 'Cash Account Statement', client_id, vat_reg_id);

                  // if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-dda-list").length > 0)
                  //   loadVATReturnsFileDocs('dda', 'Duty Deferment Account', client_id, vat_reg_id);

                  // if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-vatreturn-list").length > 0)
                  //   loadVATReturnsFileDocs('vatreturn', 'Excel/XML', client_id, vat_reg_id);

                  // // if($("#navs-vatreturns-importvat-" + vat_reg_id + " #load-ivf-list").length > 0)
                  // //   loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id);
                }

                //loadSingleVATReturn(vat_reg_id, message, modalId);
                tab_name = 'overview';
                loadOverviewTabLazy(vat_reg_id, false, tab_name, message, modalId);           

                loadHistoryTabLazy(vat_reg_id);      
              }
              else
              {
                if(file_type == 'iranyexcel')
                {
                  loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, '#'+modalId);
                  loadImportReconciliationHistoryTab(vat_reg_id);
                }
                else
                  loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, '#'+modalId);  
              }
            },
            error: function (error) {
              console.log(error);
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled Email :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }); 
    }   //valid true
  });

  //Send e-mail test to logged in user  
  $(document).on('click', '.btn-send-email-test', function () {
    var formId = $(this).closest('.formEmail').attr('id'); 
    var modalId = $(this).closest('.modal-file').attr('id'); 
    
    var btn_send_email_test = $(this);   

    var data = btn_send_email_test.data();
   
    var file_type_title = data['file_type_title'];  
    var file_type = data['file_type']; 
    var vat_reg_id = data['vat_reg_id'];  
    var client_id = data['client_id'];  
    var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);  

    var id = file_type + '-' + vat_reg_id + '-' + file_id;
    
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to send test email!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Send Test Email!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_send_email_test.attr('disabled', 'disabled');
        btn_send_email_test.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Sending...');
        
        $.ajax({
          data: $('#' + formId).serialize() + '&send_test=1',
          type: 'POST',
          url: `${fileEmailUrl}${vat_reg_id}`,
          success: function (result) {
            btn_send_email_test.removeAttr('disabled');
            btn_send_email_test.html('Send test');

            if(result['success'])    
            {   
              /*           
              if(formId.indexOf('formSingleEmail-') != -1)
              {               
                var single_task = $("#accord-" + id).parent().parent(".accordion-item.card.sort-item");   
                if(single_task.length > 0)     
                  single_task.hide();        
              }
             
              var message_title = 'Email sent!';
              var message_text = 'Email has been sent to client users';
              if(file_type == 'draft')              
                message_text += ' for review.';             
              else if(file_type == 'lock')
              {
                message_title = 'Email sent and Folder Locked!!';
                message_text += '. The Folder locked and moved to archive.';
              }
              else
                message_text += ' with '+ file_type_title + '.';
              */

              var message_title = 'Test email sent!';
              var message_text = 'Test email has been sent to logged-in user';
              var message = {message_title: message_title, message_text: message_text};                                           
            }
            else            
              var message = {message_title: 'Error', message_text: result['message'] + ' :)', message_icon: 'error', message_confirmButton: 'btn btn-danger'};            

            if(file_type == 'draft' || file_type == 'lock')
            {             
              var tab_name = 'overview';
              if(file_type == 'lock')
              {
                tab_name = 'archive';
                loadOverviewTabLazy(vat_reg_id, false, tab_name);
              }
              tab_name = 'overview';
              //loadSingleVATReturn(vat_reg_id, message, modalId);
              loadOverviewTabLazy(vat_reg_id, false, tab_name, message, modalId);              
            }
            else
            {
              if(file_type == 'iranyexcel')
                loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, '#'+modalId);  
              else
                loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id, message, '#'+modalId);  
            }
          },
          error: function (error) {
            console.log(error);
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Email :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });    
  });

  //Cancel Pending Review  
  $(document).on('click', '.btn-cancel-pending-review', function () {     
    var btn_cancel_pending_review = $(this);   
    var data = btn_cancel_pending_review.data();
   
    var vat_reg_id = data['vat_reg_id'];  
    
    Swal.fire({
      title: 'Are you sure?',
      text:  "You want to cancel the pending review!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_cancel_pending_review.attr('disabled', 'disabled');
        btn_cancel_pending_review.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Cancelling...');                  

        $.ajax({          
          type: 'GET',
          url: `${baseUrl}cancel-pending-review/${vat_reg_id}`,
          success: function (result) { 
            if(result['success'])    
            {                                           
              var client_id = result['client_id'];                              
              var message = {message_title: 'Pending Review Cancelled!', message_text: ' '};                           
            }
            else            
              var message = {message_title: 'Error', message_text: result['message'] + ' :)', message_icon: 'error', message_confirmButton: 'btn btn-danger'};            

              var tab_name = 'overview';                
              loadOverviewTabLazy(vat_reg_id, false, tab_name, message);                          
          },
          error: function (error) {
            console.log(error);
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Pending Review :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });    
  });
  // end Cancel Pending Review

  //Switch
  $(document).on('click', '.switch-send-email', function () {        
    var data = $(this).data();

    var switch_option = data['switch_option'];  
    var file_type_title = data['file_type_title'];  
    var file_type = data['file_type']; 
    var vat_reg_id = data['vat_reg_id'];  
    var client_id = data['client_id'];  
    var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);  
    
    var id = file_type + '-' + vat_reg_id + '-' + file_id;

    if($('#chk-switch-send-'+ switch_option +'-email-' + id).prop('checked'))   
    { 
      $('#show-'+ switch_option +'-email-' + id).show();   
      $('#show-'+ switch_option +'-email-comment-' + id).show();   
    }   
    else   
    { 
      $('#show-'+ switch_option +'-email-' + id).hide();      
      $('#show-'+ switch_option +'-email-comment-' + id).hide();   
    }
  });

  // //File Number Total
  // function fileNumberTotal(formId, vat_reg_id)
  // {
  //   var numbertotal = 0;
  //   $('#' + formId + ' .file_number').each(function( index ) {
  //     numbertotal += parseFloat($(this).val());
  //   });             
    
  //   $("#pivsmonthtotal-"+vat_reg_id).val(numbertotal.toFixed(2));  
  //   loadVATReturnsSubmittingFieldsValue(vat_reg_id);
  // }

  
  window.pendingSingleTasksDropzone = function pendingSingleTasksDropzone()
  {
    $(".pending-single-tasks .dropzone-file:not(.dz-clickable)").each(function () {
      var dropzone_data = $(this).data();  
      var file_id = (dropzone_data['d_id']) ? dropzone_data['d_id'] : ((dropzone_data['file_id']) ? dropzone_data['file_id'] : 0); 

      var id = $(this).attr('id');
                
      loadFileDropzone('#' + id, dropzone_data['file_type']);              
    });
  }
  pendingSingleTasksDropzone();

});