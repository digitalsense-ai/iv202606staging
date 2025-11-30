/**
 * Page VAT Registrations List
 */

'use strict';
Dropzone.autoDiscover = false;

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Datatable (jquery)
$(function () {
  
  window.Pusher = Pusher;
  window.Echo = new Echo({
  //const echo = new Echo({    
      broadcaster: 'pusher',
      //key: 'bc3c5712d049ef05fcb5',
      //cluster: 'eu',

      key: window.EchoConfig.pusherKey,
      cluster: window.EchoConfig.pusherCluster,
      //encrypted: true,
      //debug: true  // Enable debugging
      forceTLS: true
  });

//console.log(window.Echo);
  // //window.Echo.channel('data-channel')
  // // window.Echo.private('data-channel')
  // //   .listen('DataChanged', (event) => {
  // //     console.log(event);
  // //       // Trigger window refresh or specific actions when event is received
  // //       alert(event.message); // Show an alert as an example
  // //       //location.reload(); // Refresh the page (or update specific parts)
  // //   });

  // //document.addEventListener('DOMContentLoaded', function () {
  //   //window.Echo.private('data-channel')
  //   //window.Echo.channel('data-channel')
  // echo.channel('data-channel')    
  //     .listen('DataChanged', (event) => {console.log(event);
  //       console.log('Data changed:', event);
  //       // Handle the event
  //     });
  // //});

  // //window.Echo.private('user.1')
  // window.Echo.private('data-channel')
  //   .listen('DataChanged', (event) => {console.log(event);
  //       console.log(event.message);
  //   });

  // Date  
  $(".payment-date").flatpickr({
    monthSelectorType: 'static',
    dateFormat: 'd-m-Y'    
  });

  // Variable declaration for table
  var receiptUrl = baseUrl + 'vat-return/receipt/', 
    vatReturnsView = baseUrl + 'vat-returns-tab/',     
    importReconciliationView = baseUrl + 'import-reconciliation-tab/',  
    importReconciliationSwissUrl =  baseUrl + 'import-reconciliation-swiss/', 
    anyexcelTemplateUrl = baseUrl + 'anyexcel-template/',
    apiLedgerResult = null,
    statusAccordionObj = {  
      0: { title: 'Inactive', class: 'bg-label-dark' },         
      1: { title: 'Draft Created', class: 'bg-label-secondary' },
      2: { title: 'Draft', class: 'bg-label-primary' },
      3: { title: 'Pending review', class: 'bg-label-warning' },
      4: { title: 'Ready to submit', class: 'bg-label-success' },
      5: { title: 'Submitted', class: 'bg-label-info' },
      6: { title: 'Locked', class: 'bg-label-danger' },
      7: { title: 'Declined', class: 'bg-label-warning' },
    },
    disregardPeriodUrl = baseUrl + 'disregard-period/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 

  sortElement('desc', $("#accordionStyleAllTasks"));
  
  window.loadDropzone = function loadDropzone(element)
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
          <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
        </div>
      </div>
      <div class="dz-filename" data-dz-name></div>
      <div class="dz-size" data-dz-size></div>
    </div>
    </div>`;
    
    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        previewTemplate: previewTemplate,
        maxFilesize: 12,
        parallelUploads:10,
        uploadMultiple:true,
        // renameFile: function(file) {
        //     var dt = new Date();
        //     var time = dt.getTime();
        //     return time+file.name;
        // },
        acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xls,.xlsx,.doc,.docx",        
        timeout: 180000,       
        init : function() {
          this.on("sending", function(file, xhr, formData) {           
              // Add additional data here
              formData.append("file_type", "receipt");              
              formData.append("file_type_title", "Receipt");   
          });

          var vat_reg_id = this.element.getAttribute('data-vat_reg_id');
          
          var myDropzone = this;
          
          myDropzone.on("successmultiple", function (file, response) {   
            console.log("response"); //DON'T DELETE THIS LINE
              if(response == "")   
              {                    
                myDropzone.removeAllFiles(true);
                $("#dropzone-multi-"+vat_reg_id).removeClass("dz-started");
                                         
                Swal.fire({
                  title: 'Error!',
                  //text: 'Folder locked. Cannot upload receipt.',
                  text: 'Cannot upload receipt at this stage.',
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });          
              }
              else
              {                   
                $.each(response, function(indexr, valr) {
                  var download_options = '';
                  var previewElement = $("#dropzone-multi-" + valr['vat_reg_id'] + " .dz-preview");

                  $.each(previewElement, function(index, val) {
                    if(!$(val).attr("id"))
                    {
                      $(val).attr("id",valr['id']);

                      // var receipt_download = 'Receipt<button type="button" class="btn rounded-pill btn-icon btn-primary m-2 btn-download-file" title="Download" data-client_id="{{ $client_id }}" data-vat_reg_id="' + valr['vat_reg_id'] + '" data-file_id="'+ valr['id'] +'" data-file_type="receipt" data-file_type_title="Receipt" {{ ($receipt->file_id) ? '' : 'disabled=disabled' }}>' +
                      //                     '<span class="tf-icons bx bxs-download"></span>' +
                      //                   '</button>';
                      // $("#navs-vatreturns-documents-" + vat_reg_id +" h4.receipt-download").html(receipt_download);

                      var download_option_exists = $('#btn_download_receipt_file_'+ valr['vat_reg_id'] + ' option').filter(function() {
                        if(valr['o_file_name'])
                          return $(this).text() === valr['o_file_name'];
                        else
                          return $(this).text() === valr['file_name'];
                      }).length > 0;

                      if(!download_option_exists)
                        download_options += '<option value="'+ valr['id'] +'">'+ ((valr['o_file_name']) ? valr['o_file_name'] : valr['file_name']) +'</option>';  
                      
                      return false;
                    }                                                    
                  });  

                  if(download_options != '')
                    $("#btn_download_receipt_file_" + valr['vat_reg_id']).append(download_options); 
                  $("#btn_download_receipt_file_" + valr['vat_reg_id']).val("");
                });
                                 
                $("#btn-upload-receipt-" + vat_reg_id).html("Receipt Uploaded");
                $("#btn-upload-receipt-" + vat_reg_id).removeClass("btn-warning");
                $("#btn-upload-receipt-" + vat_reg_id).removeClass("btn-upload-receipt");
                $("#btn-upload-receipt-" + vat_reg_id).addClass("btn-info");               
                $("#btn-upload-receipt-" + vat_reg_id).addClass("disabled");  
                //$('<button type="button" id="btn-open-lock-'+ vat_reg_id +'" class="btn btn-danger float-end mx-2 btn-open-lock" data-vat_reg_id="'+ vat_reg_id +'" data-bs-toggle="modal" data-bs-target="#onboardingSlideLockModal-'+ vat_reg_id +'">Lock</button>').insertAfter("#btn-upload-receipt-" + vat_reg_id);

                if($("#navs-vatreturns-overview-" + vat_reg_id + " .btn-open-lock").length > 0)
                  $("#navs-vatreturns-overview-" + vat_reg_id + " .btn-open-lock").remove();
                $('<button type="button" id="btn-open-lock-'+ vat_reg_id +'" class="btn btn-danger float-end mx-2 btn-open-lock" data-vat_reg_id="'+ vat_reg_id +'" data-bs-toggle="modal" data-bs-target="#sendModal-lock-'+ vat_reg_id +'-0">Lock</button>').insertAfter("#btn-upload-receipt-" + vat_reg_id);

                if($("#accordionStyle"))
                {
                  $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[5].class + '">' + statusAccordionObj[5].title + '</span>');
                  $("#accordionStyle-" + vat_reg_id + " .step").removeClass("active");
                  $("#accordionStyle-" + vat_reg_id + " .step").removeClass("crossed");

                  $("#accordionStyle-" + vat_reg_id + " #step-draft-created.step").addClass("crossed");
                  $("#accordionStyle-" + vat_reg_id + " #step-draft.step").addClass("crossed");
                  $("#accordionStyle-" + vat_reg_id + " #step-pending-review.step").addClass("crossed");
                  $("#accordionStyle-" + vat_reg_id + " #step-ready-to-submit.step").addClass("crossed");                  
                
                  $("#accordionStyle-" + vat_reg_id + " .step#step-submitted").addClass("active");   


                  $(".accordion-item.active").attr("data-index", 5);
                  sortElement('desc', $("#accordionStyle"));                
                }
                
                loadHistoryTabLazy(vat_reg_id);             
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
              console.log("errorrrrrrrrrrrrr");              
          });

          $.getJSON(`${receiptUrl}${vat_reg_id}`, function(data) {  
            console.log(data); //DON'T DELETE THIS LINE          
            $.each(data, function(index, val) {
              var mockFile = { name: val.file_name, size: val.size };
              myDropzone.options.addedfile.call(myDropzone, mockFile);
              
              $(mockFile.previewElement).prop('id', val.id);
              $(mockFile.previewElement).addClass('dz-complete');
            });
          });            
        },
        addRemoveLinks: true,
        removedfile: function(file) {  
         var file_id = file.previewElement.getAttribute('id');
         
         if(file_id != null)
         {
           $.ajax({
             type: 'DELETE',
             url: `${receiptUrl}${file_id}`,          
             success: function(data){
                
                if(data['status'] == "deleted")
                {
                  var vat_reg_id = data['vat_reg_id'];                
                 
                  $("#btn-upload-receipt-" + vat_reg_id).html("Upload Receipt");
                  $("#btn-upload-receipt-" + vat_reg_id).addClass("btn-warning");
                  $("#btn-upload-receipt-" + vat_reg_id).addClass("btn-upload-receipt");
                  $("#btn-upload-receipt-" + vat_reg_id).removeClass("btn-info");               
                  $("#btn-upload-receipt-" + vat_reg_id).removeClass("disabled");  
                  
                  $("#navs-vatreturns-overview-" + vat_reg_id + " .btn-open-lock").remove();  

                  $('#btn_download_receipt_file_'+ vat_reg_id + ' option[value='+ file_id +']').remove();

                  if($("#accordionStyle"))
                  {
                    $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[4].class + '">' + statusAccordionObj[4].title + '</span>');
                    $("#accordionStyle-" + vat_reg_id + " .step").removeClass("active");
                    $("#accordionStyle-" + vat_reg_id + " .step").removeClass("crossed");

                    $("#accordionStyle-" + vat_reg_id + " #step-draft-created.step").addClass("crossed");
                    $("#accordionStyle-" + vat_reg_id + " #step-draft.step").addClass("crossed");
                    $("#accordionStyle-" + vat_reg_id + " #step-pending-review.step").addClass("crossed");
                    
                    $("#accordionStyle-" + vat_reg_id + " .step#step-ready-to-submit").addClass("active");   


                    $(".accordion-item.active").attr("data-index", 4);  
                    sortElement('desc', $("#accordionStyle"));                            
                  }
                  loadHistoryTabLazy(vat_reg_id);
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
  
  window.loadSwissDropzone = function loadSwissDropzone(element)
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
          <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
        </div>
      </div>
      <div class="dz-filename" data-dz-name></div>
      <div class="dz-size" data-dz-size></div>
    </div>
    </div>`;
    
    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        previewTemplate: previewTemplate,
        maxFilesize: 12,
        parallelUploads:1,
        uploadMultiple:true,        
        acceptedFiles: ".pdf",        
        timeout: 180000,       
        init : function() {
          this.on("sending", function(file, xhr, formData) {           
              // Add additional data here
              formData.append("file_type", "swiss_import_reconciliation");              
              formData.append("file_type_title", "Swiss Import Reconciliation");   
          });

          var vat_reg_id = this.element.getAttribute('data-vat_reg_id');
          
          var myDropzone = this;
          
          myDropzone.on("successmultiple", function (file, response) {                    
              if(response == "")   
              {                    
                myDropzone.removeAllFiles(true);
                $("#dropzone-swiss-multi-"+vat_reg_id).removeClass("dz-started");
                                         
                Swal.fire({
                  title: 'Error!',                
                  text: 'Cannot upload swiss document at this stage.',
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });          
              }
              else
              {                   
                $.each(response, function(indexr, valr) {
                  var download_options = '';
                  var previewElement = $("#dropzone-swiss-multi-" + valr['vat_reg_id'] + " .dz-preview");
                  $.each(previewElement, function(index, val) {
                    if(!$(val).attr("id"))
                    {
                      $(val).attr("id",valr['id']);

                      var download_option_exists = $('#btn_download_swiss_file_'+ valr['vat_reg_id'] + ' option').filter(function() {
                          return $(this).text() === valr['o_file_name'];
                      }).length > 0;

                      if(!download_option_exists)
                        download_options += '<option value="'+ valr['id'] +'">'+ valr['o_file_name'] +'</option>';  
                      return false;
                    }                                                    
                  }); 

                  if(download_options != '')
                    $("#btn_download_swiss_file_" + valr['vat_reg_id']).append(download_options); 
                  $("#btn_download_swiss_file_" + valr['vat_reg_id']).val("");
                });
                                 
                // $("#btn-upload-receipt-" + vat_reg_id).html("Receipt Uploaded");
                // $("#btn-upload-receipt-" + vat_reg_id).removeClass("btn-warning");
                // $("#btn-upload-receipt-" + vat_reg_id).removeClass("btn-upload-receipt");
                // $("#btn-upload-receipt-" + vat_reg_id).addClass("btn-info");               
                // $("#btn-upload-receipt-" + vat_reg_id).addClass("disabled");  
            
                //$('<button type="button" id="btn-open-lock-'+ vat_reg_id +'" class="btn btn-danger float-end mx-2 btn-open-lock" data-vat_reg_id="'+ vat_reg_id +'" data-bs-toggle="modal" data-bs-target="#sendModal-lock-'+ vat_reg_id +'-0">Lock</button>').insertAfter("#btn-upload-receipt-" + vat_reg_id);

                // if($("#accordionStyle"))
                // {
                //   $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[5].class + '">' + statusAccordionObj[5].title + '</span>');
                //   $("#accordionStyle-" + vat_reg_id + " .step").removeClass("active");
                //   $("#accordionStyle-" + vat_reg_id + " .step").removeClass("crossed");

                //   $("#accordionStyle-" + vat_reg_id + " #step-draft-created.step").addClass("crossed");
                //   $("#accordionStyle-" + vat_reg_id + " #step-draft.step").addClass("crossed");
                //   $("#accordionStyle-" + vat_reg_id + " #step-pending-review.step").addClass("crossed");
                //   $("#accordionStyle-" + vat_reg_id + " #step-ready-to-submit.step").addClass("crossed");                  
                
                //   $("#accordionStyle-" + vat_reg_id + " .step#step-submitted").addClass("active");   


                //   $(".accordion-item.active").attr("data-index", 5);
                //   sortElement('desc', $("#accordionStyle"));                
                // }
                             
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
              console.log("errorrrrrrrrrrrrr");              
          });

          // $.getJSON(`${importReconciliationSwissUrl}${vat_reg_id}`, function(data) {            
          //   $.each(data, function(index, val) {
          //     var mockFile = { name: val.file_name, size: val.size };
          //     myDropzone.options.addedfile.call(myDropzone, mockFile);
              
          //     $(mockFile.previewElement).prop('id', val.id);
          //     $(mockFile.previewElement).addClass('dz-complete');
          //   });
          // });            
        },
        addRemoveLinks: true,
        removedfile: function(file) {  
         var file_id = file.previewElement.getAttribute('id');
         
         if(file_id != null)
         {
           $.ajax({
             type: 'DELETE',
             url: `${importReconciliationSwissUrl}${file_id}`,          
             success: function(data){
                
                if(data['status'] == "deleted")
                {
                  var vat_reg_id = data['vat_reg_id'];                
                 
                  // $("#btn-upload-receipt-" + vat_reg_id).html("Upload Receipt");
                  // $("#btn-upload-receipt-" + vat_reg_id).addClass("btn-warning");
                  // $("#btn-upload-receipt-" + vat_reg_id).addClass("btn-upload-receipt");
                  // $("#btn-upload-receipt-" + vat_reg_id).removeClass("btn-info");               
                  // $("#btn-upload-receipt-" + vat_reg_id).removeClass("disabled");  
                  
                  // $("#navs-vatreturns-overview-" + vat_reg_id + " .btn-open-lock").remove();  

                  // if($("#accordionStyle"))
                  // {
                  //   $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[4].class + '">' + statusAccordionObj[4].title + '</span>');
                  //   $("#accordionStyle-" + vat_reg_id + " .step").removeClass("active");
                  //   $("#accordionStyle-" + vat_reg_id + " .step").removeClass("crossed");

                  //   $("#accordionStyle-" + vat_reg_id + " #step-draft-created.step").addClass("crossed");
                  //   $("#accordionStyle-" + vat_reg_id + " #step-draft.step").addClass("crossed");
                  //   $("#accordionStyle-" + vat_reg_id + " #step-pending-review.step").addClass("crossed");
                    
                  //   $("#accordionStyle-" + vat_reg_id + " .step#step-ready-to-submit").addClass("active");   


                  //   $(".accordion-item.active").attr("data-index", 4);  
                  //   sortElement('desc', $("#accordionStyle"));                            
                  // }
                  
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
  
  //Load Dropzone - Receipt and model popup
  window.AllTasksDropzone = function AllTasksDropzone()
  {
    $(".navs-vatreturns-documents .dropzone-multi:not(.dz-clickable)").each(function () {
      var dropzone_data = $(this).data();  
      var vat_reg_id = dropzone_data['vat_reg_id'];  

      loadDropzone("#dropzone-multi-"+vat_reg_id);
    });

    //$(".navs-vatreturns-documents .dropzone-file:not(.dz-clickable), .navs-vatreturns-importvat .dropzone-file:not(.dz-clickable), .navs-vatreturns-commercial-invoices .dropzone-file:not(.dz-clickable), #navs-pills-top-api-connection .dropzone-file:not(.dz-clickable)").each(function () {
    $(".navs-vatreturns-documents .dropzone-file:not(.dz-clickable), " +
      ".navs-vatreturns-control .dropzone-file:not(.dz-clickable), " +
      ".navs-vatreturns-importvat .dropzone-file:not(.dz-clickable), " +
      ".navs-vatreturns-commercial-invoices .dropzone-file:not(.dz-clickable), " +
      "#navs-pills-top-api-connection .dropzone-file:not(.dz-clickable), " +
      ".navs-importreconciliation-documents .dropzone-file:not(.dz-clickable), " +
      ".vatcheck .dropzone-file:not(.dz-clickable)").each(function () {
      var dropzone_data = $(this).data();  
      var file_id = (dropzone_data['d_id']) ? dropzone_data['d_id'] : ((dropzone_data['file_id']) ? dropzone_data['file_id'] : 0);  

      var id = $(this).attr('id');
      loadFileDropzone('#' + id, dropzone_data['file_type']);
    }); 

    //SWISS    
    $(".navs-importreconciliation-documents .dropzone-swiss-multi:not(.dz-clickable)").each(function () {
      var dropzone_data = $(this).data();  
      var vat_reg_id = dropzone_data['vat_reg_id'];  

      loadSwissDropzone("#dropzone-swiss-multi-"+vat_reg_id);
    });
    //SWISS      
  } 
  AllTasksDropzone();  
  //End

  // //Load Datepicker
  //payment-date
  // //loadPaymentDatepicker("#payment_date-"+ vat_reg_id);
  // //End
  
  // //Load Editor
  //if($(".email-editor").length > 0)
    //commentEmailEditor();    
  // if(result['country'] == "NO")            
  //   importVatCommentEditor(result['vat_reg_id']);
  $(".navs-vatreturns-importvat .import-vat-comment-editor").each(function () {
    // var data = $(this).data();  
    // //var country = data['country']; 
    // var vat_reg_id = data['vat_reg_id'];
    // var import_vat_id = data['import_vat_id'];
    // var import_vat_line_no = data['import_vat_line_no'];

    //if(country == "NO") 
      importVatCommentEditor($(this));
  });
  // //End  
 
  // //Load Submitting Fields
  // if(result['country'] == "GB" || result['country'] == "NO")
  // {    
  //   if (typeof loadVATReturnsSubmittingFields == 'function')
  //     loadVATReturnsSubmittingFields(result['vat_reg_id']);   
  // }

  $(".navs-vatreturns-submittingfields").each(function () {
    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
        
    if (typeof loadVATReturnsSubmittingFields == 'function')    
      loadVATReturnsSubmittingFields(vat_reg_id); 
  });  

  //Enable/Disblae VAT Retuns Tab buttons for inactive VAT reg.
  window.loadEnableDisableVatReturnsTabItems = function loadEnableDisableVatReturnsTabItems(client_id, vat_reg_id = null, vat_reg_main_id = null, enable = false)  
  {
    if(vat_reg_main_id)
    {
      $("#navs-pills-top-vatreturns .accordion-item[data-vat_reg_main_id="+ vat_reg_main_id +"]").each(function () { 
        var vat_reg_id = $(this).find('.accordion-collapse').attr('id').replace('accordionStyleAllTasks-', '');   
        var country = $(this).data('country');

        if(enable)
        { 
          $("#btn-accordion-" + vat_reg_id).removeClass('inactive');

          if($("#anyexcel-template-vatreturn-" + vat_reg_id).length == 1)          
            $("#anyexcel-template-vatreturn-" + vat_reg_id).removeAttr('disabled');

          if($("#btn-upload-vatreturn-" + vat_reg_id).length == 1)
            $("#btn-upload-vatreturn-" + vat_reg_id).removeAttr('disabled');
          
          if($("#anyexcel-template-vatcontrol-" + vat_reg_id).length == 1)
            $("#anyexcel-template-vatcontrol-" + vat_reg_id).removeAttr('disabled');

          if($("#btn-upload-vatcontrol-" + vat_reg_id).length == 1)
            $("#btn-upload-vatcontrol-" + vat_reg_id).removeAttr('disabled');
          
          $("#dropzone-multi-" + vat_reg_id).removeClass('dropzone-disabled');
          //$("#btn-submitting-fields-" + vat_reg_id).removeClass('disabled');
          $("#navs-vatreturns-documents-" + vat_reg_id + " .btn-upload-documents").removeAttr('disabled');
          
          $("#btn-vatreturn-notes-" + vat_reg_id).removeAttr('disabled');

          if($("#btn-email-sent-" + vat_reg_id).length == 1)
            $("#btn-email-sent-" + vat_reg_id).removeAttr('disabled');

          $("#navs-vatreturns-overview-" + vat_reg_id + " ul.dropdown-item li").each(function () { 
            $(this).find('a').removeClass('disabled');
          });
        } //enable
        else
        {    
          $("#btn-accordion-" + vat_reg_id).addClass('inactive');

          if($("#anyexcel-template-vatreturn-" + vat_reg_id).length == 1)
            $("#anyexcel-template-vatreturn-" + vat_reg_id).attr('disabled', 'disabled');

          if($("#btn-upload-vatreturn-" + vat_reg_id).length == 1)
            $("#btn-upload-vatreturn-" + vat_reg_id).attr('disabled', 'disabled');

          if($("#anyexcel-template-vatcontrol-" + vat_reg_id).length == 1)
            $("#anyexcel-template-vatcontrol-" + vat_reg_id).attr('disabled', 'disabled');

          if($("#btn-upload-vatcontrol-" + vat_reg_id).length == 1)
            $("#btn-upload-vatcontrol-" + vat_reg_id).attr('disabled', 'disabled');
          
          $("#dropzone-multi-" + vat_reg_id).addClass('dropzone-disabled');
          //$("#btn-submitting-fields-" + vat_reg_id).addClass('disabled');
          $("#navs-vatreturns-documents-" + vat_reg_id + " .btn-upload-documents").attr('disabled', 'disabled');          

          $("#btn-vatreturn-notes-" + vat_reg_id).attr('disabled', 'disabled');

          if($("#btn-email-sent-" + vat_reg_id).length == 1)
            $("#btn-email-sent-" + vat_reg_id).attr('disabled', 'disabled');

          $("#navs-vatreturns-overview-" + vat_reg_id + " ul.dropdown-item li").each(function () { 
            $(this).find('a').addClass('disabled');
          });
        } //disable      

        if(country == 'GB')
        {
          if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-pivs-list").length > 0)
            loadVATReturnsFileDocs('pivs', 'Postponed import VAT statement', client_id, vat_reg_id); 

          if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-c79-list").length > 0)
            loadVATReturnsFileDocs('c79', 'C79', client_id, vat_reg_id);

          if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-cas-list").length > 0)
            loadVATReturnsFileDocs('cas', 'Cash Account Statement', client_id, vat_reg_id);
        }

        if(country == 'NO')
        {
          if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-dda-list").length > 0)
            loadVATReturnsFileDocs('dda', 'Duty Deferment Account', client_id, vat_reg_id);

          if($("#navs-vatreturns-importvat-" + vat_reg_id + " #load-ivf-list").length > 0)
            loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id);
        }

        if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-documents-list").length > 0)
            loadVATReturnsFileDocs('documents', 'Documents', client_id, vat_reg_id);
              
        if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-vatreturn-list").length > 0)
          loadVATReturnsFileDocs('vatreturn', 'Excel/XML', client_id, vat_reg_id, null, null, 'enabledisable');
      });
    } //vat_reg_main_id NOT NULL
    else
    {
      var country = $("#btn-accordion-" + vat_reg_id).closest('.accordion-item.active').data('country');

      if(enable)
      {
        $("#btn-accordion-" + vat_reg_id).removeClass('inactive');

        if($("#anyexcel-template-vatreturn-" + vat_reg_id).length == 1)          
          $("#anyexcel-template-vatreturn-" + vat_reg_id).removeAttr('disabled');

        if($("#btn-upload-vatreturn-" + vat_reg_id).length == 1)
          $("#btn-upload-vatreturn-" + vat_reg_id).removeAttr('disabled');
        
        if($("#anyexcel-template-vatcontrol-" + vat_reg_id).length == 1)
          $("#anyexcel-template-vatcontrol-" + vat_reg_id).removeAttr('disabled');

        if($("#btn-upload-vatcontrol-" + vat_reg_id).length == 1)
          $("#btn-upload-vatcontrol-" + vat_reg_id).removeAttr('disabled');
        
        $("#dropzone-multi-" + vat_reg_id).removeClass('dropzone-disabled');
        //$("#btn-submitting-fields-" + vat_reg_id).removeClass('disabled');
        $("#navs-vatreturns-documents-" + vat_reg_id + " .btn-upload-documents").removeAttr('disabled');
        
        $("#btn-vatreturn-notes-" + vat_reg_id).removeAttr('disabled');

        if($("#btn-email-sent-" + vat_reg_id).length == 1)
          $("#btn-email-sent-" + vat_reg_id).removeAttr('disabled');

        $("#navs-vatreturns-overview-" + vat_reg_id + " ul.dropdown-item li").each(function () { 
          $(this).find('a').removeClass('disabled');
        });
      } //enable
      else
      {
        $("#btn-accordion-" + vat_reg_id).addClass('inactive');

        if($("#anyexcel-template-vatreturn-" + vat_reg_id).length == 1)
          $("#anyexcel-template-vatreturn-" + vat_reg_id).attr('disabled', 'disabled');

        if($("#btn-upload-vatreturn-" + vat_reg_id).length == 1)
          $("#btn-upload-vatreturn-" + vat_reg_id).attr('disabled', 'disabled');

        if($("#anyexcel-template-vatcontrol-" + vat_reg_id).length == 1)
          $("#anyexcel-template-vatcontrol-" + vat_reg_id).attr('disabled', 'disabled');

        if($("#btn-upload-vatcontrol-" + vat_reg_id).length == 1)
          $("#btn-upload-vatcontrol-" + vat_reg_id).attr('disabled', 'disabled');
        
        $("#dropzone-multi-" + vat_reg_id).addClass('dropzone-disabled');
        //$("#btn-submitting-fields-" + vat_reg_id).addClass('disabled');
        $("#navs-vatreturns-documents-" + vat_reg_id + " .btn-upload-documents").attr('disabled', 'disabled');          

        $("#btn-vatreturn-notes-" + vat_reg_id).attr('disabled', 'disabled');

        if($("#btn-email-sent-" + vat_reg_id).length == 1)
          $("#btn-email-sent-" + vat_reg_id).attr('disabled', 'disabled');

        $("#navs-vatreturns-overview-" + vat_reg_id + " ul.dropdown-item li").each(function () { 
          $(this).find('a').addClass('disabled');
        });
      } //disable

      if(country == 'GB')
      {
        if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-pivs-list").length > 0)
          loadVATReturnsFileDocs('pivs', 'Postponed import VAT statement', client_id, vat_reg_id); 

        if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-c79-list").length > 0)
          loadVATReturnsFileDocs('c79', 'C79', client_id, vat_reg_id);

        if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-cas-list").length > 0)
          loadVATReturnsFileDocs('cas', 'Cash Account Statement', client_id, vat_reg_id);
      }

      if(country == 'NO')
      {
        if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-dda-list").length > 0)
          loadVATReturnsFileDocs('dda', 'Duty Deferment Account', client_id, vat_reg_id);

        if($("#navs-vatreturns-importvat-" + vat_reg_id + " #load-ivf-list").length > 0)
          loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id);
      }

      if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-documents-list").length > 0)
          loadVATReturnsFileDocs('documents', 'Documents', client_id, vat_reg_id);
            
      if($("#navs-vatreturns-documents-" + vat_reg_id + " #load-vatreturn-list").length > 0)
        loadVATReturnsFileDocs('vatreturn', 'Excel/XML', client_id, vat_reg_id, null, null, 'enabledisable');
    } //vat_reg_main_id NULL
  };

  //Load VAT Retuns Tab
  window.loadVatReturnsTab = function loadVatReturnsTab(client_id, message = null)  
  {           
    if($("#navs-pills-top-vatreturns #accordionStyleAllTasks #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';                  
      //$("#navs-pills-top-vatreturns #accordionStyleAllTasks").prepend(loadertext);

      $("#navs-pills-top-vatreturns #accordionStyleAllTasks").html("");
      $("#navs-pills-top-vatreturns #accordionStyleAllTasks").html(loadertext);
    }    

    $.ajax({          
      url: `${vatReturnsView}${client_id}`,
      type: 'GET',
      success: function (result) {
        $("#navs-pills-top-vatreturns #accordionStyleAllTasks").html("");
        $("#navs-pills-top-vatreturns #accordionStyleAllTasks").append(result['view']);
        
        //noTasks('accordionStyleAllTasks');

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

  window.loadOverviewTabLazy = function loadOverviewTabLazy(vat_reg_id, refresh = false, tab_name = null, message = null, modalId = null)
  {      
    console.log("start Time : " + vat_reg_id + " -- " + moment().format("DD-MM-YYYY h:m:s A"));    

    if($("#navs-vatreturns-overview-"+ vat_reg_id +" #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $("#navs-vatreturns-overview-"+ vat_reg_id).html("");  
      $("#navs-vatreturns-overview-"+ vat_reg_id).append(loadertext);  

      $("#total-tax-top-"+vat_reg_id).html(loadertext);         
    }
    console.log("refresh: " + refresh);
    if(tab_name == null)
      tab_name = 'overview';
    $.ajax({              
      url: `${baseUrl}vat-return-overview-tab/${vat_reg_id}`,
      data: {refresh: refresh, tab_name: tab_name},  
      type: 'GET',
      success: function (result) {   
        
        if(tab_name == 'archive')
        {
          var li_tab = '<li class="nav-item">' +
                          '<button type="button" id="btn-archive-'+vat_reg_id+'" class="btn-archive nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-archive-'+vat_reg_id+'" aria-controls="navs-vatreturns-archive-'+vat_reg_id+'" aria-selected="false">Archive</button>' +
                        '</li>'
          //$("#vat-returns-main-"+vat_reg_id + " ul.nav-tabs").append(li_tab);
          $("#vat-returns-main-"+vat_reg_id + " ul.nav-tabs li:nth-child(3)").before($("<li>").html(li_tab));

          var tab_content = '<div class="tab-pane fade" id="navs-vatreturns-archive-'+vat_reg_id+'" role="tabpanel">'+ result['view'] +'</div>'
          $("#vat-returns-main-"+vat_reg_id + " div.tab-content").append(tab_content);

          $("#btn-accordion-"+ vat_reg_id + " table tbody tr td:last-child").html('<span class="badge bg-label-danger">Locked</span>');
          //$("#navs-vatreturns-archive-" + vat_reg_id).html(result['view']);        
        }
        else
        {
          //$("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[2].class + '">' + statusAccordionObj[2].title + '</span>');         
          if(message)         
          {
            if(message['message_title'] == 'Email sent!') 
            {      
              if($("#btn-accordion-" + vat_reg_id + " table td.status span").html() == 'Declined')
                $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[7].class + '">' + statusAccordionObj[7].title + '</span>');
              else
                $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[3].class + '">' + statusAccordionObj[3].title + '</span>');
            }
            else if(message['message_title'] == 'Pending Review Cancelled!')          
              $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[2].class + '">' + statusAccordionObj[2].title + '</span>');
            else if(message == 'Numbers has been approved.')          
              $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[4].class + '">' + statusAccordionObj[4].title + '</span>');
            else if(message == 'Numbers has been declined.')          
              $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[7].class + '">' + statusAccordionObj[7].title + '</span>');            
          }
          
          $("#navs-vatreturns-overview-" + vat_reg_id).html(result['view']);
        }
       
        $("#total-tax-top-"+vat_reg_id).html($("#total-tax-"+vat_reg_id).html());

        //Enable Download button in Document Tab
        console.log("Enable Download button in Document Tab");
        $("#navs-vatreturns-documents-" + vat_reg_id + " #load-vatreturn-list ul li:last-child .btn-download-file[disabled='disabled']").removeAttr('disabled');
            
        console.log("END Time : " + vat_reg_id + " -- " + moment().format("DD-MM-YYYY h:m:s A"));  
console.log(modalId);
        if(modalId)
        {
          if(modalId.indexOf('onboardingSlideCommentModal-') != -1)
          {console.log("Comment modal");
            $('#'+ modalId + ' .btn-comment').removeClass('btn-success');
            $('#'+ modalId + ' .btn-comment').addClass('btn-danger');
            $('#'+ modalId + ' .btn-comment').addClass('disabled');
            $('#'+ modalId + ' .btn-comment').html('Send');
          }
          else
          {console.log("no comment modal");
            $('#'+ modalId + ' .btn-send-email-file').removeClass('btn-success');
            $('#'+ modalId + ' .btn-send-email-file').addClass('btn-danger');
            $('#'+ modalId + ' .btn-send-email-file').addClass('disabled');
            $('#'+ modalId + ' .btn-send-email-file').html('Send');
          }

          $('#'+ modalId).removeAttr("data-upload_success");
          $('#'+ modalId).modal('hide');
          $(document.body).removeClass("modal-open");
          $(document.body).removeAttr("style");
          $(".modal-backdrop").remove();                  
        }

        if(message)
        {
          if(message == 'Numbers has been approved.' || message == 'Numbers has been declined.' || message == 'Update overview tab')
          {

          }
          else
            Swal.fire({
              icon: (message['message_icon']) ? message['message_icon'] : 'success',
              title: message['message_title'],
              text: message['message_text'],
              customClass: {
                confirmButton: (message['message_confirmButton']) ? message['message_confirmButton'] : 'btn btn-success'
              }
            }); 
        }

        if(result['status'] == 400)
        {          
          // Swal.fire({
          //   icon: 'error',
          //   title: "Error in reading the excel/xml file or ERP",
          //   html: result['error'],
          //   customClass: {
          //     confirmButton: 'btn btn-success'
          //   }
          // });

          Swal.fire({
            title: "Error in reading the excel/xml file or ERP",
            html: result['error'],
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Create/Edit template',
            cancelButtonText: 'Ignore error',
            customClass: {
              confirmButton: 'btn btn-primary me-2',
              cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
          }).then(function (confirmresult) { console.log(result);
              if(confirmresult.value)
                window.location.href = `${anyexcelTemplateUrl}create?id=` + result['vatreturn_file_id'];  
              //else if (result.dismiss === Swal.DismissReason.cancel)               
          });

        }                
      },
      error: function (err) {
        console.log(err);        
      }
    });
  }

  $(document).on("shown.bs.collapse", "#accordionStyleAllTasks.accordion, #accordionStyleImportReconciliationTasks.accordion", function(event) { 
    var active_accordion = $(this).find('.accordion-item.active');
    var reload = active_accordion.data("reload");
    var product_type = active_accordion.data("product_type");

    if(reload)
    {
      var btn_accordion = active_accordion.find('button.accordion-button');

      var vat_reg_id = btn_accordion.attr('id').replace('btn-accordion-','');

      active_accordion.removeData('reload');
      active_accordion.removeAttr('data-reload');

      if(product_type == 1 || product_type == 4)
        loadOverviewTabLazy(vat_reg_id, true);
      else if(product_type == 2)
        loadImportReconciliationOverviewTabLazy(vat_reg_id, true);
    }
  });
        
  // Refresh
  $(document).on('click', '.btn-refresh', function () {
    var vat_reg_id = $(this).data('vat_reg_id');
    var product_type = $(this).data('product_type');
    
    var btn_refresh = $(this);
    btn_refresh.attr('disabled', 'disabled');
    btn_refresh.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Refreshing...');
    
    if(product_type == 1 || product_type == 4)
      loadOverviewTabLazy(vat_reg_id, true);
    else if(product_type == 2)
      loadImportReconciliationOverviewTabLazy(vat_reg_id, true);
  });
  
  window.loadImportVatTabLazy = function loadImportVatTabLazy(data)
  {      
    var vat_reg_id = data['vat_reg_id'];
    var import_vat_file_id = data['import_vat_file_id'];

    $.ajax({              
      url: `${baseUrl}vat-return-importvat-tab/${vat_reg_id}`,
      type: 'GET',
      data: {import_vat_file_id: import_vat_file_id},
      success: function (result) {   
        $("#import-vat-file-overview-"+ import_vat_file_id).html("");
        $("#import-vat-file-overview-"+ import_vat_file_id).html(result['view']);

        $("#navs-vatreturns-importvat-"+vat_reg_id+" .import-vat-comment-editor").each(function () { 
            var data = $(this).data();          
            importVatCommentEditor(data);
        });
      },
      error: function (err) {
        
      }
    });
  }   

  $(".navs-vatreturns-importvat .import-vat-file-overview").each(function () {
    var data = $(this).data();  
    
    loadImportVatTabLazy(data);
  });
  
  window.loadHistoryTabLazy = function loadHistoryTabLazy(vat_reg_id)
  {          
    $.ajax({              
      url: `${baseUrl}vat-return-history-tab/${vat_reg_id}`,
      type: 'GET',      
      success: function (result) {  
        if($("#navs-vatreturns-timeline-"+ vat_reg_id).length > 0)
        { 
          $("#navs-vatreturns-timeline-"+ vat_reg_id).html("");
          $("#navs-vatreturns-timeline-"+ vat_reg_id).html(result['view']);   

          $("#navs-vatreturns-timeline-"+vat_reg_id+" .import-vat-comment-editor").each(function () {
              var data = $(this).data();          
              importVatCommentEditor(data);
          });
        }

        /*
        if($("#navs-vatreturns-timeline-ir-"+ vat_reg_id).length > 0)
        {
          $("#navs-vatreturns-timeline-ir-"+ vat_reg_id).html("");
          $("#navs-vatreturns-timeline-ir-"+ vat_reg_id).html(result['view']);   

          $("#navs-vatreturns-timeline-ir-"+vat_reg_id+" .import-vat-comment-editor").each(function () {
              var data = $(this).data();          
              importVatCommentEditor(data);
          });
        }
        */     
      },
      error: function (err) {
        
      }
    });
  }

  $(".navs-vatreturns-timeline").each(function () {
    var data = $(this).data();  
    var vat_reg_id = data['vat_reg_id'];
    
    loadHistoryTabLazy(vat_reg_id);   
  });

  window.loadImportReconciliationHistoryTab = function loadImportReconciliationHistoryTab(vat_reg_id)
  {          
    $.ajax({              
      url: `${baseUrl}import-reconciliation-history-tab/${vat_reg_id}`,
      type: 'GET',      
      success: function (result) {          
        if($("#navs-importreconciliation-timeline-"+ vat_reg_id).length > 0)
        {
          $("#navs-importreconciliation-timeline-"+ vat_reg_id).html("");
          $("#navs-importreconciliation-timeline-"+ vat_reg_id).html(result['view']);   

          $("#navs-importreconciliation-timeline-"+vat_reg_id+" .import-vat-comment-editor").each(function () {
              var data = $(this).data();          
              importVatCommentEditor(data);
          });
        }     
      },
      error: function (err) {
        
      }
    });
  }  

  $(".navs-importreconciliation-timeline").each(function () {
    var data = $(this).data();  
    var vat_reg_id = data['vat_reg_id'];
        
    loadImportReconciliationHistoryTab(vat_reg_id);    
  });

  window.loadVatReturnControlTab = function loadVatReturnControlTab(vat_reg_id)
  {  
    var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>'; 

    $("#navs-vatreturns-control-"+ vat_reg_id + " #load-vatcontrol-list").html("");
    $("#navs-vatreturns-control-"+ vat_reg_id + " #load-vatcontrol-list").html(loadertext);

    $.ajax({              
      url: `${baseUrl}vat-return-control-tab/${vat_reg_id}`,
      type: 'GET',      
      success: function (result) {                 
        // if(result['status'] == 400)
        // {
        //   console.log(result['error']);
        // }
        // else
        // {
          if($("#navs-vatreturns-control-"+ vat_reg_id + " #load-vatcontrol-list").length > 0)
          {
            $("#navs-vatreturns-control-"+ vat_reg_id + " #load-vatcontrol-list").html("");
            $("#navs-vatreturns-control-"+ vat_reg_id + " #load-vatcontrol-list").html(result['view']);             
          }
        //} 
      },
      error: function (err) {
        console.log(err);
      }
    });
  }

  window.loadImportReconciliationControlTab = function loadImportReconciliationControlTab(vat_reg_id)
  {  
    var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>'; 

    $("#navs-importreconciliation-control-"+ vat_reg_id + " #load-ircontrol-list").html("");
    $("#navs-importreconciliation-control-"+ vat_reg_id + " #load-ircontrol-list").html(loadertext);

    $.ajax({              
      url: `${baseUrl}import-reconciliation-control-tab/${vat_reg_id}`,
      type: 'GET',      
      success: function (result) {                         
        if($("#navs-importreconciliation-control-"+ vat_reg_id + " #load-ircontrol-list").length > 0)
        {
          $("#navs-importreconciliation-control-"+ vat_reg_id + " #load-ircontrol-list").html("");
          $("#navs-importreconciliation-control-"+ vat_reg_id + " #load-ircontrol-list").html(result['view']);             
        }        
      },
      error: function (err) {
        console.log(err);
      }
    });
  }

  // $(".navs-vatreturns-control").each(function () {
  //   var data = $(this).data();  
  //   var vat_reg_id = data['vat_reg_id'];
    
  //   loadVatReturnControlTab(vat_reg_id);    
  // });

  //Switch
  $(document).on('click', '.switch-input.send-to, .form-check-input.send-to', function () {    
    var formId = $(this).closest('.formEmail').attr('id');
    var data = $(this).data();
    
    $("#" + formId + ' .form-check-input.chk_cc:not(".self")').removeAttr('disabled');
    $("#" + formId + ' .form-check-input.chk_cc:not(".self")').removeAttr('checked');

    if(data['modal_for'])
    {      
      $("#" + formId + " #cc-to-"+ data['vat_reg_id'] + '-' +data['id'] + '-' +data['modal_for']).prop('checked', false);
      $("#" + formId + " #cc-to-"+ data['vat_reg_id'] + '-' +data['id'] + '-' +data['modal_for']).prop('disabled', true);    
    }
    else
    {
      if(data['file_type'])
      {
        $("#" + formId + " #cc-to-" + data['file_type'] + '-' + data['vat_reg_id'] + '-' +data['d_id'] + '-' +data['id']).prop('checked', false);
        $("#" + formId + " #cc-to-" + data['file_type'] + '-' + data['vat_reg_id'] + '-' +data['d_id'] + '-' +data['id']).prop('disabled', true);

        $("#" + formId + " .email-cc-item .form-check.custom-option.custom-option-basic").removeClass('checked');                
      }
    }      

    if(!$(this).prop('checked'))
    {
      $("#" + formId + " .btn-submit").addClass('disabled');         
      
      if(data['file_type'])
      {
        $("#" + formId + " .btn-submit").attr('disabled', 'disabled');       
        $("#" + formId + " #show-to-email-comment-" + data['file_type'] + '-' + data['vat_reg_id'] + '-' +data['d_id']).hide();  

        $("#" + formId + " .btn-send-email-file").attr('disabled', 'disabled');
        $("#" + formId + " .btn-send-email-file").addClass('disabled');   
        $("#" + formId + " .btn-send-email-file").removeClass('btn-success');                
        $("#" + formId + " .btn-send-email-file").addClass('btn-danger');            
      }
    }
    else 
    { 
      /*    
      console.log($(this).closest('.modal-file').find('.dropzone-file').length);
      if($(this).closest('.modal-file').find('.dropzone-file').length > 0)
      {
        if($(this).closest('.modal-file').find('.dropzone-file').hasClass('dz-max-files-reached'))
        {
          $("#" + formId + " .btn-submit").removeClass('disabled');  
          
          if(data['file_type'])
          {
            $("#" + formId + " .btn-submit").removeAttr('disabled');       
            $("#" + formId + " #show-to-email-comment-" + data['file_type'] + '-' + data['vat_reg_id'] + '-' +data['d_id']).show();  

            $("#" + formId + " .btn-send-email-file").removeClass('disabled'); 
            $("#" + formId + " .btn-send-email-file").addClass('btn-success');                
            $("#" + formId + " .btn-send-email-file").removeClass('btn-danger');        
          }
        }
        else
        {
          $("#" + formId + " .btn-submit").addClass('disabled');         
        
          if(data['file_type'])
          {
            $("#" + formId + " .btn-submit").attr('disabled');       
            $("#" + formId + " #show-to-email-comment-" + data['file_type'] + '-' + data['vat_reg_id'] + '-' +data['d_id']).hide();  

            $("#" + formId + " .btn-send-email-file").addClass('disabled');   
            $("#" + formId + " .btn-send-email-file").removeClass('btn-success');                
            $("#" + formId + " .btn-send-email-file").addClass('btn-danger');            
          }
        }
      } // has dropzone
      else
      {
        */
        $("#" + formId + " .btn-submit").removeClass('disabled');  
          
        if(data['file_type'])
        {
          $("#" + formId + " .btn-submit").removeAttr('disabled');       
          $("#" + formId + " #show-to-email-comment-" + data['file_type'] + '-' + data['vat_reg_id'] + '-' +data['d_id']).show();  

          $("#" + formId + " .btn-send-email-file").removeAttr('disabled');
          $("#" + formId + " .btn-send-email-file").removeClass('disabled'); 
          $("#" + formId + " .btn-send-email-file").addClass('btn-success');                
          $("#" + formId + " .btn-send-email-file").removeClass('btn-danger');        
        }
      //} // no dropzone
    }
    
  });

  //Upload Receipt
  $(document).on('click', '.btn-upload-receipt', function () {
    var data = $(this).data();     
    var vat_reg_id = data['vat_reg_id'];
    
    $("#vat-returns-main-" + vat_reg_id + " ul.nav-tabs li button").removeClass('active');
    $("#vat-returns-main-" + vat_reg_id + " .tab-content .tab-pane").removeClass('active');
    $("#vat-returns-main-" + vat_reg_id + " .tab-content .tab-pane").removeClass('show');
      
    $("#btn-receipt-" + vat_reg_id).addClass('active');   
    $("#navs-vatreturns-documents-" + vat_reg_id).addClass('active show');    
    $("#vat-returns-main-" + vat_reg_id + " ul.nav-tabs .nav-link#btn-documents-" + vat_reg_id).addClass('active');
  });

  //Sale/Purchase Box Click    
  $(document).on('click', '.btn-invoices', function () {    
      var data = $(this).data();
      var vat_reg_id = data['vat_reg_id'];
      
      var invoice_url = `${baseUrl}invoices/${vat_reg_id}`;
      
      if (("vattype" in data) && ("vatpercentage" in data) && ("vatcurrency" in data))      
        invoice_url = `${baseUrl}invoices/${vat_reg_id}`+'?type=' + data['vattype']+'&percentage=' + data['vatpercentage']+
                          '&currency=' + data['vatcurrency'];
     
      window.open(invoice_url, '_blank');//, 'noreferrer'
  });

  function sortElement(sortby, element)
  {
    // element.each(function(){
    //     var $this = $(this);
    //     $this.append($this.find('.sort-item').get().sort(function(a, b) {
    //       var aIndex = $(a).data('index');
    //       var bIndex = $(b).data('index');

    //       // Always push data-index="6" to the bottom
    //       if (aIndex === 6 && bIndex !== 6) return 1;
    //       if (bIndex === 6 && aIndex !== 6) return -1;
    //       if (aIndex === 6 && bIndex === 6) return 0;

    //       // Normal sorting
    //       if (sortby === 'asc') {
    //           return aIndex - bIndex;
    //       } else {
    //           return bIndex - aIndex;
    //       }
            
    //         // if(sortby == 'asc')
    //         //   return $(a).data('index') - $(b).data('index');
    //         // else
    //         //   return $(b).data('index') - $(a).data('index');
    //     }));
    // });    
  }

  // Disregard period
  $(document).on('click', '.btn-disregard-period', function () {    
    var btn_disregard_period = $(this);
    var data = btn_disregard_period.data();

    var disregard_period_text = (btn_disregard_period.attr('title') == 'Disregard Period') ? 'disregard' : 'enable';
    var disregard_period_suffix = (btn_disregard_period.attr('title') == 'Disregard Period') ? 'ed' : 'd';
    var disregard_period_text_capitalize = (btn_disregard_period.attr('title') == 'Disregard Period') ? 'Disregard' : 'Enable';
    var disregard_period_text_after = (btn_disregard_period.attr('title') == 'Disregard Period') ? 'Enable' : 'Disregard';
    var disregard_period_text_loading = (btn_disregard_period.attr('title') == 'Disregard Period') ? 'Disregarding' : 'Enabling';
   
    var client_id = data['client_id'];   
    var vat_reg_id = data['vat_reg_id'];    
    var vat_reg_period = data['vat_reg_period'];  

    var product_type = data['product_type'];
    var product_type_text = (product_type == 1) ? 'VAT Returns' : 'Import Reconciliation';
    var accordion_name = (product_type == 1) ? 'All' : 'ImportReconciliation';
    
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ disregard_period_text +" the "+ vat_reg_period +" period for " + product_type_text + "!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ disregard_period_text_capitalize +'!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        
        btn_disregard_period.attr('disabled', 'disabled');
        btn_disregard_period.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' + 
            disregard_period_text_loading + '...');
        
        $.ajax({           
          type: 'POST',
          url: `${disregardPeriodUrl}${vat_reg_id}`,    
          data: { product_type: product_type },      
          success: function (result) {
            if(result['message'] == 'disregarded')    
            { 
              btn_disregard_period.removeAttr('disabled');
              btn_disregard_period.html(disregard_period_text_after + ' Period');
              btn_disregard_period.attr('title', disregard_period_text_after + ' Period');
              
              
              if(disregard_period_text == 'disregard')
              {
                //only view                
                loadEnableDisableVatReturnsTabItems(client_id, vat_reg_id);

                /*
                $("#accordionStyle"+accordion_name+"Tasks-" + vat_reg_id + " #btn-accordion-" + vat_reg_id).addClass('disabled');
              
                $("#accordionStyle"+accordion_name+"Tasks-" + vat_reg_id + " .nav-item button").prop('disabled', true);
                $("#accordionStyle"+accordion_name+"Tasks-" + vat_reg_id + " .nav-item button#btn-overview-" + vat_reg_id).prop('disabled', true);

                if(product_type == 1 || product_type == 4)
                {
                  $("#navs-vatreturns-overview-" + vat_reg_id + " .table-responsive .table").addClass('disabled');

                  $("#btn-email-sent-" + vat_reg_id).prop('disabled', true);
                }
                else
                  $("#navs-import-reconciliation-overview-" + vat_reg_id + " .table-responsive .table").addClass('disabled');
                */  
              }
              else
              {
                loadEnableDisableVatReturnsTabItems(client_id, vat_reg_id, null, true);

                /*
                $("#accordionStyle"+accordion_name+"Tasks-" + vat_reg_id + " #btn-accordion-" + vat_reg_id).removeClass('disabled');
              
                $("#accordionStyle"+accordion_name+"Tasks-" + vat_reg_id + " .nav-item button").removeAttr('disabled');
                $("#accordionStyle"+accordion_name+"Tasks-" + vat_reg_id + " .nav-item button#btn-overview-" + vat_reg_id).removeAttr('disabled');

                if(product_type == 1 || product_type == 4)
                {
                  $("#navs-vatreturns-overview-" + vat_reg_id + " .table-responsive .table").removeClass('disabled');

                  $("#btn-email-sent-" + vat_reg_id).removeAttr('disabled');
                }
                else
                  $("#navs-import-reconciliation-overview-" + vat_reg_id + " .table-responsive .table").removeClass('disabled');
                */
              }
              
              
              Swal.fire({
                icon: 'success',
                title: vat_reg_period + ' period ' + disregard_period_text + disregard_period_suffix + '!',
                text: vat_reg_period + ' period has been ' + disregard_period_text + disregard_period_suffix + '.',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });                                                             
            }
            else
            {
              btn_disregard_task.removeAttr('disabled');               
              btn_disregard_task.html(disregard_period_text_capitalize + " Period");
            }
          },
          error: function (error) {
            console.log(error);
          }
        }); 

            
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled ' + disregard_period_text_capitalize + ' Period :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    }); 
  });

  // Bootstrap Datepicker
  // --------------------------------------------------------------------
  var bsDatepickerFromDate = $('#bs-datepicker-from_date'),
      bsDatepickerToDate = $('#bs-datepicker-to_date');

  // Basic
  if (bsDatepickerFromDate.length) {
    bsDatepickerFromDate.datepicker({
      format: "yyyy-mm",
      startView: "year", 
      minViewMode: "months",
      autoclose: true,
    });
  }

  // Basic
  if (bsDatepickerToDate.length) {
    bsDatepickerToDate.datepicker({
      format: "yyyy-mm",
      startView: "year", 
      minViewMode: "months",
      autoclose: true,
    });
  }

  //Filter Tasks
  $(document).on("click", ".btn-reset-filter", function(event)
  {
    $('#bs-datepicker-from_date').val('');
    $('#bs-datepicker-to_date').val('');

    var from_date = $('#bs-datepicker-from_date').val().replace('-','');
    var to_date = $('#bs-datepicker-to_date').val().replace('-','');
    if(from_date == "" && to_date == "")    
      $(".accordion-item.sort-item").show();   
  });

  //Filter Tasks
  $(document).on("submit", ".form-task-filter", function(event)
  {
    event.preventDefault();

    var form = $(this);        
    var from_date = form.find('#bs-datepicker-from_date').val().replace('-','');
    var to_date = form.find('#bs-datepicker-to_date').val().replace('-','');

    var btn_task_filter = form.find("button.btn-task-filter");
    btn_task_filter.attr('disabled', 'disabled');
    btn_task_filter.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Filtering...');

    if(from_date == "" && to_date == "")    
      $(".accordion-item.sort-item").show();    
    else
    {      
      var element = $(".accordion-item.sort-item");
      element.each(function(){    
        var element_item = $(this);    
        var data = element_item.data();
        
        if(data['range'].indexOf('***') != -1)
        {
          var item_dates = data['range'].split('***');
          
          $.each(item_dates, function(indexr, item) {
            var item_date = item.replace('-','');
            
            if(item_date >= from_date && item_date <= to_date)
              element_item.show();
            else
              element_item.hide();
          });
        }
        else
        {
          var item_date = data['range'].replace('-','');
          
          if(item_date >= from_date && item_date <= to_date)
            element_item.show();
          else
            element_item.hide();
        }            
      });
    } 

    $("#offcanvasTaskFilter").offcanvas('hide');
          
    btn_task_filter.removeAttr('disabled');               
    btn_task_filter.html("Filter");  
  }); 

  //INFINITE SCROLLING
  let morepage = 2; // Start from page 2
  var pagename = '';

  var url = window.location.href;
  if(url.substring(url.lastIndexOf('/') + 1) == "uploads")  
    pagename = 'uploads';
  else if(url.substring(url.lastIndexOf('/') + 1) == "pivs")  
    pagename = 'uploadspivs';
  else if(url.substring(url.lastIndexOf('/') + 1) == "cas")  
    pagename = 'uploadscas';
  else if(url.substring(url.lastIndexOf('/') + 1) == "dda")  
    pagename = 'uploadsdda';
  else if(url.substring(url.lastIndexOf('/') + 1) == "all-tasks")  
    pagename = 'all-tasks';
  else if(url.substring(url.lastIndexOf('/') + 1) == "clientuser-tasks")  
    pagename = 'clientuser-tasks';
  console.log(pagename);

  if($('#pivs_tasks_block').length > 0)
  {
    $('#pivs_tasks_block').block({
      message:
          '<div class="d-flex justify-content-center flex-column align-items-center">' +
            '<div class="sk-bounce sk-primary sk-center">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>' +
            
          '</div>',
      //timeout: 1000,
      css: {
        backgroundColor: 'transparent',
        color: '#000',
        border: '0'
      },
      overlayCSS: {
        backgroundColor: 'transparent', 
        color: '#000',
        opacity: 0.5
      }
    });
  }

  if($('#cas_tasks_block').length > 0)
  {
    $('#cas_tasks_block').block({
      message:
          '<div class="d-flex justify-content-center flex-column align-items-center">' +
            '<div class="sk-bounce sk-primary sk-center">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>' +
            
          '</div>',
      //timeout: 1000,
      css: {
        backgroundColor: 'transparent',
        color: '#000',
        border: '0'
      },
      overlayCSS: {
        backgroundColor: 'transparent', 
        color: '#000',
        opacity: 0.5
      }
    });
  }

  if($('#dda_tasks_block').length > 0)
  {
    $('#dda_tasks_block').block({
      message:
          '<div class="d-flex justify-content-center flex-column align-items-center">' +
            '<div class="sk-bounce sk-primary sk-center">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>' +
            
          '</div>',
      //timeout: 1000,
      css: {
        backgroundColor: 'transparent',
        color: '#000',
        border: '0'
      },
      overlayCSS: {
        backgroundColor: 'transparent', 
        color: '#000',
        opacity: 0.5
      }
    });
  }

  if($('#vatreturn_tasks_block').length > 0)
  {
    $('#vatreturn_tasks_block').block({
      message:
          '<div class="d-flex justify-content-center flex-column align-items-center">' +
            '<div class="sk-bounce sk-primary sk-center">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>' +
            
          '</div>',
      //timeout: 1000,
      css: {
        backgroundColor: 'transparent',
        color: '#000',
        border: '0'
      },
      overlayCSS: {
        backgroundColor: 'transparent', 
        color: '#000',
        opacity: 0.5
      }
    });
  }

  if(pagename != '')
    var moretasks = moreTasks(pagename, morepage); 

  function moreTasks(pagename, page) {    
    return new Promise((resolve, reject) => {
      try {   
        $.ajax({
          url: '/all-tasks/more/' + page,
          data: { pagename: pagename },
          type: 'GET',
          dataType: 'json',
          success: function(data) {               
            if (data.result_count > 0) {                        
              $('#pivs_tasks').append(data.upload_tasks_pivs);
              $('#cas_tasks').append(data.upload_tasks_cas);
              $('#dda_tasks').append(data.upload_tasks_dda);

              $('#accordionStyleAllTasks').append(data.vatreturn_tasks);
              
              pendingSingleTasksDropzone();
              AllTasksDropzone();
              
              page++;

              var moretasks = moreTasks(pagename, page);
            } else {                                  
              $('#pivs_tasks_block').unblock();
              $('#cas_tasks_block').unblock();
              $('#dda_tasks_block').unblock();

              $('#vatreturn_tasks_block').unblock();

              if($('.switch-input.all-tasks').length == 0)
              {
                noTasks('pivs_tasks');
                noTasks('cas_tasks');
                noTasks('dda_tasks');

                noTasks('accordionStyleAllTasks');
              }
              else  
                showAllTasks($('.switch-input.all-tasks')); 
            }                
            return resolve(page);   
          },
          error: function(err) {             
            $('#pivs_tasks_block').unblock();
            $('#cas_tasks_block').unblock();
            $('#dda_tasks_block').unblock();

            $('#vatreturn_tasks_block').unblock();

            if($('.switch-input.all-tasks').length == 0)
            {
              noTasks('pivs_tasks');
              noTasks('cas_tasks');
              noTasks('dda_tasks');

              noTasks('accordionStyleAllTasks');
            }
            else  
              showAllTasks($('.switch-input.all-tasks')); 
          }
        });   
      } catch (ex) {
        return reject(new Error(ex));
      }
    });
  }

  window.noTasks = function noTasks(id)
  {
    var noTaskImagePath = baseUrl + 'assets/img/illustrations/';
    var tasksTypeName = 'pending';
   
    if(id == 'pivs_tasks')
      tasksTypeName = 'PIVS';
    else if(id == 'cas_tasks')
      tasksTypeName = 'Cash Account Statement';
    else if(id == 'dda_tasks')
      tasksTypeName = 'Duty Deferment Account';
    else if(id == 'accordionStyleAllTasks')
      tasksTypeName = 'VAT Returns';
    else if(id == 'accordionStyleImportReconciliationTasks')
      tasksTypeName = 'Import Reconciliation';

    var no_tasks_div = '<!-- No Tasks -->' +
                        '<div class="container-xxl container-p-y" id="no_'+ id +'">' +
                        '<div class="misc-wrapper text-center">' +
                          '<div class="mt-5">' +
                            '<img src="'+ noTaskImagePath +'girl-doing-yoga-light.png" alt="page-misc-not-authorized-light" width="450" class="img-fluid" data-app-light-img="illustrations/girl-doing-yoga-light.png" data-app-dark-img="illustrations/girl-doing-yoga-dark.png">' +
                          '</div>' +
                          '<h1 class="mb-2 mx-2">Hooray!!</h1>' +
                          '<p class="mb-4 mx-2">You don´t have any '+ tasksTypeName +' tasks</p>' +
                        '</div>' +
                        '</div>' +
                        '<!-- No Tasks -->';

    //if($.trim($('#'+id).html()) == '')
    if($("#" + id + " .accordion-item:visible").length == 0)
      $('#'+id).append(no_tasks_div);    
  }
  //noTasks('accordionStyleAllTasks');
  //noTasks('accordionStyleImportReconciliationTasks');
  //INFINITE SCROLLING

  //Declaration Click    
  $(document).on('click', '.btn-declarations', function () {    
    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    
    var declaration_url = `${baseUrl}declarations/${vat_reg_id}`;
          
    window.open(declaration_url, '_blank');//, 'noreferrer'
  });

  //Preview Report Click    
  $(document).on('click', '.btn-preview-report', function () {    
    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    
    var preview_report_url = `${baseUrl}preview-report/${vat_reg_id}`;
          
    window.open(preview_report_url, '_blank');//, 'noreferrer'
  });

  //VAT Check Click    
  $(document).on('click', '.btn-vatcheck', function () {    
    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    
    var vatcheck_url = `${baseUrl}vatcheck/${vat_reg_id}`;
          
    window.open(vatcheck_url, '_blank');//, 'noreferrer'
  });

  //Load Import Reconciliation Tab
  window.loadImportReconciliationTab = function loadImportReconciliationTab(client_id, message = null)  
  {           
    if($("#navs-pills-top-import-reconciliation #accordionStyleImportReconciliationTasks #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';                  
      $("#navs-pills-top-import-reconciliation #accordionStyleImportReconciliationTasks").prepend(loadertext);
    }    

    $.ajax({          
      url: `${importReconciliationView}${client_id}`,
      type: 'GET',
      success: function (result) {
        $("#navs-pills-top-import-reconciliation #accordionStyleImportReconciliationTasks").html("");
        $("#navs-pills-top-import-reconciliation #accordionStyleImportReconciliationTasks").append(result['view']);
        
        noTasks('accordionStyleImportReconciliationTasks');

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

  window.loadImportReconciliationOverviewTabLazy = function loadImportReconciliationOverviewTabLazy(vat_reg_id, refresh = false, tab_name = null, message = null, modalId = null)
  {      
    console.log("import re. start Time : " + vat_reg_id + " -- " + moment().format("DD-MM-YYYY h:m:s A"));    

    if($("#navs-import-reconciliation-overview-"+ vat_reg_id +" #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $("#navs-import-reconciliation-overview-"+ vat_reg_id).html("");  
      $("#navs-import-reconciliation-overview-"+ vat_reg_id).append(loadertext);  

      //$("#total-tax-top-"+vat_reg_id).html(loadertext);         
    }
    console.log("refresh: " + refresh);
    if(tab_name == null)
      tab_name = 'overview';
    $.ajax({              
      url: `${baseUrl}import-reconciliation-overview-tab/${vat_reg_id}`,
      data: {refresh: refresh, tab_name: tab_name},  
      type: 'GET',
      success: function (result) {   
        
        // if(tab_name == 'archive')
        // {
        //   var li_tab = '<li class="nav-item">' +
        //                   '<button type="button" id="btn-archive-'+vat_reg_id+'" class="btn-archive nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-vatreturns-archive-'+vat_reg_id+'" aria-controls="navs-vatreturns-archive-'+vat_reg_id+'" aria-selected="false">Archive</button>' +
        //                 '</li>'
         
        //   $("#vat-returns-main-"+vat_reg_id + " ul.nav-tabs li:nth-child(3)").before($("<li>").html(li_tab));

        //   var tab_content = '<div class="tab-pane fade" id="navs-vatreturns-archive-'+vat_reg_id+'" role="tabpanel">'+ result['view'] +'</div>'
        //   $("#vat-returns-main-"+vat_reg_id + " div.tab-content").append(tab_content);

        //   $("#btn-accordion-"+ vat_reg_id + " table tbody tr td:last-child").html('<span class="badge bg-label-danger">Locked</span>');          
        // }
        // else
        // {
          // $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[2].class + '">' + statusAccordionObj[2].title + '</span>');         
          // if(message)         
          // {
          //   if(message['message_title'] == 'Email sent!')          
          //     $("#btn-accordion-" + vat_reg_id + " table td.status").html('<span class="badge ' + statusAccordionObj[3].class + '">' + statusAccordionObj[3].title + '</span>');
          // }
          
          $("#navs-import-reconciliation-overview-" + vat_reg_id).html(result['view']);

          $("#btn-declarations-" + vat_reg_id).removeClass('disabled');
        //}
       
        //$("#total-tax-top-"+vat_reg_id).html($("#total-tax-"+vat_reg_id).html());

        // //Enable Download button in Document Tab
        // console.log("Enable Download button in Document Tab");
        // $("#navs-vatreturns-documents-" + vat_reg_id + " #load-vatreturn-list ul li:last-child .btn-download-file[disabled='disabled']").removeAttr('disabled');
            
        console.log("import re.  END Time : " + vat_reg_id + " -- " + moment().format("DD-MM-YYYY h:m:s A"));  

        // if(modalId)
        // {
        //   $('#'+ modalId + ' .btn-send-email-file').removeClass('btn-success');
        //   $('#'+ modalId + ' .btn-send-email-file').addClass('btn-danger');
        //   $('#'+ modalId + ' .btn-send-email-file').addClass('disabled');
        //   $('#'+ modalId + ' .btn-send-email-file').html('Send');

        //   $('#'+ modalId).removeAttr("data-upload_success");
        //   $('#'+ modalId).modal('hide');
        //   $(document.body).removeClass("modal-open");
        //   $(document.body).removeAttr("style");
        //   $(".modal-backdrop").remove();                  
        // }

        // if(message)
        //   Swal.fire({
        //     icon: (message['message_icon']) ? message['message_icon'] : 'success',
        //     title: message['message_title'],
        //     text: message['message_text'],
        //     customClass: {
        //       confirmButton: (message['message_confirmButton']) ? message['message_confirmButton'] : 'btn btn-success'
        //     }
        //   }); 

        if(result['status'] == 400)
        {          
          Swal.fire({
            icon: 'error',
            title: "Error in reading the xml file from FTP",
            text: result['error'],
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }                
      },
      error: function (err) {
        console.log(err);        
      }
    });
  }

  window.loadImportReconciliationOverviewTabSalesInvoiceVatAmount = function loadImportReconciliationOverviewTabSalesInvoiceVatAmount(vat_reg_id)
  {              
    $.ajax({              
      url: `${baseUrl}import-reconciliation-overview-tab-sales-invoice-vat-amount/${vat_reg_id}`,     
      type: 'GET',
      success: function (result) {   
        
       console.log(result);
        var currency_locale = 'da-DK';
        var currency_style = 'NOK';

        var sales_invoice_vat_amounts = result['sales_invoice_vat_amount'];

        var total_sales_vat_amount = 0;
        var total_sales_invoice_vat_amount = 0;
        var total_sales_vat_vs_import_vat = 0;
        $.each(sales_invoice_vat_amounts, function (idx, sales_invoice_vat_amount) {
          var sales_vat_amount = $(".sales-invoices-vat-amount-"+ vat_reg_id + '-' + idx).data('sales_vat_amount');
          total_sales_vat_amount += sales_vat_amount;

          total_sales_invoice_vat_amount += sales_invoice_vat_amount;

          var format_sales_invoice_vat_amount = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_invoice_vat_amount);
                
          $(".sales-invoices-vat-amount-"+ vat_reg_id + '-' + idx).html(format_sales_invoice_vat_amount);

          var sales_vat_vs_import_vat = sales_vat_amount - sales_invoice_vat_amount;
          var format_sales_vat_vs_import_vat = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_vat_vs_import_vat);
          
          $(".sales-vat-vs-import-vat-"+ vat_reg_id + '-' + idx).html(format_sales_vat_vs_import_vat);

          var format_total_sales_invoice_vat_amount = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(total_sales_invoice_vat_amount);

          $(".total-sales-invoices-vat-amount-"+ vat_reg_id).html(format_total_sales_invoice_vat_amount);

          total_sales_vat_vs_import_vat = total_sales_vat_amount - total_sales_invoice_vat_amount;
          var format_total_sales_vat_vs_import_vat = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(total_sales_vat_vs_import_vat);

          $(".total-sales-vat-vs-import-vat-"+ vat_reg_id).html(format_total_sales_vat_vs_import_vat);
        });  
                      
      },
      error: function (err) {
        console.log(err);        
      }
    });
  }

  // $(document).on("shown.bs.collapse", "#accordionStyleImportReconciliationTasks.accordion", function(event) { 
  //   var active_accordion = $(this).find('.accordion-item.active');
  //   var reload = active_accordion.data("reload");

  //   if(reload)
  //   {
  //     var btn_accordion = active_accordion.find('button.accordion-button');

  //     var vat_reg_id = btn_accordion.attr('id').replace('btn-accordion-','');

  //     active_accordion.removeData('reload');
  //     active_accordion.removeAttr('data-reload');

  //     loadImportReconciliationOverviewTabLazy(vat_reg_id, true);
  //   }
  // });
      
      
  console.log("before DOMContentLoaded");
//console.log(window.Echo);
  //document.addEventListener('DOMContentLoaded', function () {console.log("DOMContentLoaded");
   window.Echo.channel('invoice-currency-channel').listen('.InvoiceCurrencyEvent', (event) => {
    console.log(event);
        console.log('Invoice Event:', event);
         console.log(event.message);
         console.log(event.vat_reg_id);
        // Handle the event
        var vat_reg_id = event.vat_reg_id;
        console.log(vat_reg_id);
        loadOverviewTabLazy(vat_reg_id);
      });
  //});


if(url.substring(url.lastIndexOf('/') + 1) != "all-tasks")  
{
  window.Echo.channel('vatreturn-channel').listen('.VATReturnEvent', (event) => {    
        console.log('VATReturn Event:', event);
        
        // Handle the event
        var vat_reg_id = event.vat_reg_id;
        
        var refresh = false;
        if(event.message == "Update overview tab") 
          refresh = true;
        loadOverviewTabLazy(vat_reg_id, refresh, null, event.message);                      
  });

  window.Echo.channel('sales-invoice-disregard-channel').listen('.ImportReconciliationSalesInvoiceDisregardEvent', (event) => {
    console.log(event);
        console.log('Import Reconciliation Sales Invoice Disregard Event:', event);
         console.log(event.message);
         console.log(event.vat_reg_id);
        // Handle the event
        var vat_reg_id = event.vat_reg_id;
        console.log(vat_reg_id);
        loadImportReconciliationOverviewTabLazy(vat_reg_id);        
  });

  window.Echo.channel('com-sales-invoices-channel').listen('.ImportReconciliationComSalesInvoicesEvent', (event) => {
    //console.log(event);
        console.log('Import Reconciliation Com./Sales Invoices Event:', event);
         // console.log(event.message);
         // console.log(event.vat_reg_id);
        // Handle the event
        var vat_reg_id = event.vat_reg_id;
        //console.log(vat_reg_id);
        
        loadOverviewTabLazy(vat_reg_id);
        loadImportReconciliationOverviewTabSalesInvoiceVatAmount(vat_reg_id);
  });
}

console.log("after DOMContentLoaded");

  if($.trim($("#accordionStyleAllTasks").html()) == '')
  {
    console.log("VAT return empty");
    //$("#btn-vatreturns").hide();
    noTasks('accordionStyleAllTasks');
  }

  if($.trim($("#accordionStyleImportReconciliationTasks").html()) == '')
  {
    console.log("Import Reconciliation empty");
    //$("#btn-import-reconciliation").hide();
    noTasks('accordionStyleImportReconciliationTasks');
  }

  // Show All Tasks     
  $(document).on('click', '.switch-input.all-tasks', function () {
    showAllTasks($(this));
  });

  function showAllTasks(element)
  {        
    if(element.prop("checked"))       
      $(".accordion-item[data-all='false']").show();                
    else   
      $(".accordion-item[data-all='false']").hide();    
           
    if($("#pivs_tasks .accordion-item:visible").length == 0)
      noTasks('pivs_tasks');
    else
      $("#pivs_tasks #no_pivs_tasks").remove();

    if($("#cas_tasks .accordion-item:visible").length == 0)
      noTasks('cas_tasks');
    else
      $("#cas_tasks #no_cas_tasks").remove();

    if($("#dda_tasks .accordion-item:visible").length == 0)
      noTasks('dda_tasks');
    else
      $("#dda_tasks #no_dda_tasks").remove();
              
    if($("#accordionStyleAllTasks .accordion-item:visible").length == 0)
      noTasks('accordionStyleAllTasks');   
    else
      $("#accordionStyleAllTasks #no_accordionStyleAllTasks").remove();   
  }

  $(".accordion-item .email-editor").each(function () {    
    commentEmailEditor($(this).data());   
  }); 
});