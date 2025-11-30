/**
 * Commercial Invoices Upload
 */

'use strict';
Dropzone.autoDiscover = false;

$(function () {
  
  // Variable declaration  
  var fileUrl = baseUrl + 'file/';  
  //var fileEmailUrl = baseUrl + 'file-email/'; 
  //var disregardTaskUrl = baseUrl + 'disregard-task/'; 

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  //Load Dropzone
  window.loadBulkUploadDropzone = function loadBulkUploadDropzone(element)
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
    
    var accepted_files = ".pdf";
    // if(file_type == 'pivs' || file_type == 'documents' || file_type == 'c79')
    //   accepted_files = ".pdf";
    // else if(file_type == 'cas' || file_type == 'dda')
    //   accepted_files = ".csv";
    // else if(file_type == 'ivf')
    //   accepted_files = ".xml";

    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        previewTemplate: previewTemplate,
        //maxFilesize: 10,
        parallelUploads:1,
        //maxFiles: 1,
        uploadMultiple:true,
        // renameFile: function(file) {
        //     var dt = new Date();
        //     var time = dt.getTime();
        //     return time+file.name;
        // },
        acceptedFiles: accepted_files,        
        timeout: 180000,       
        init : function() {
          // var file_type = this.element.getAttribute('data-file_type');
          // var vat_reg_id = this.element.getAttribute('data-vat_reg_id');
        
          // var file_id = (this.element.getAttribute('data-d_id')) ? this.element.getAttribute('data-d_id') : ((this.element.getAttribute('data-file_id')) ? this.element.getAttribute('data-file_id') : 0);     
          // var modal_type = this.element.getAttribute('data-modal_type');

          // var id = file_type + '-' + vat_reg_id + '-' + file_id;

          var myDropzone = this;
          var dropzoneId = myDropzone.element.getAttribute('id');
          
          // var modalId = "";

          // if(modal_type == 'single')
          // {
          //   modalId = "#uploadSingleModal-" + id;            
          // }
          // else
          // {
          //   if($("#uploadModal-" + id).length > 0) 
          //     modalId = "#uploadModal-" + id;
          //   //else if($("#uploadSingleModal-" + id).length > 0)
          //     //modalId = "#uploadSingleModal-" + id;
          //   else if($("#overwriteModal-" + id).length > 0)             
          //     modalId = "#overwriteModal-" + id;
          // }

          // myDropzone.on("addedfile", function() {            
          //   $(modalId + ' .btn-close').prop("disabled", true);
          // });

          myDropzone.on("success", function (file, response) {    //multiple 
              if(response == "")   
              {           
                myDropzone.removeFile(file);   
                //$("#dropzone-" + id).addClass("dz-started");               
                Swal.fire({
                  title: 'Error!',
                  text: 'Cannot upload files.',
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });          
              }
              else
              {  
                //console.log(file)    ;
              //console.log(response)    ;
                // if(response['error'])
                // {         
                //   // var previewElement = $("#" + dropzoneId + " .dz-preview.dz-complete");    
                //   // $.each(previewElement, function(index, val) {
                //   //   if(!$(this).hasClass('bulk-complete'))
                //   //   {

                //   //   }
                //   // });

                //   if(response['error']['no_org_no'])
                //   {
                //     if($(".notification .no_org_no").length == 0)
                //       $(".notification").append('<div class="alert alert-danger no_org_no" role="alert">' +
                //                                 '<h6 class="alert-heading mb-1">No Organization number exist!</h6>'+
                //                                 '<ul>' +
                //                                   '<li>' + response['error']['no_org_no'] + '</li>' +
                //                                 '</ul>' +                                                 
                //                             '</div>');
                //     else
                //       $(".notification .no_org_no ul").append('<li>' + response['error']['no_org_no'] + '</li>');
                //   }
                //   else if(response['error']['no_folder'])
                //   {
                //     if($(".notification .no_folder").length == 0)
                //       $(".notification").append('<div class="alert alert-danger no_folder" role="alert">' +
                //                                 '<h6 class="alert-heading mb-1">No folder exist!</h6>'+
                //                                 '<ul>' +
                //                                   '<li>' + response['error']['no_folder'] + '</li>' +
                //                                 '</ul>' +                                                 
                //                             '</div>');
                //     else
                //       $(".notification .no_folder ul").append('<li>' + response['error']['no_folder'] + '</li>');
                //   }
                // }
                // else if(response['success'])
                if(response['message'] == 'success')  
                {                           
                  var uploaded_file = response['uploaded_file'][0];    
                  
                  file.previewElement.id = uploaded_file['id'];
                  file.previewElement.setAttribute("file_type", uploaded_file['file_type']);
                  file.previewElement.setAttribute("file_type_title", uploaded_file['file_type_title']);

                  if($(".notification .success").length == 0)
                    $(".notification").append('<div class="alert alert-success success" role="alert">' +
                                              '<h6 class="alert-heading mb-1">Successfully uploaded!</h6>'+
                                              '<ul>' +
                                                '<li>' + response['file'] + '</li>' +
                                              '</ul>' +                                                 
                                          '</div>');
                  else
                    $(".notification .success ul").append('<li>' + response['file'] + '</li>');

                  //file.previewElement.setAttribute("id", response['uploaded_file']['id']);
                  //$('.dz-preview').attr("id",response['uploaded_file']['id']);
                  //$('.dz-preview').attr("file_type",response['uploaded_file']['file_type']);
                 // $('.dz-preview').attr("file_type_title",response['uploaded_file']['file_type_title']);

                  // $.each(response, function(indexr, valr) {                  
                  //   console.log(valr)    ;
                  //   var previewElement = $("#" + dropzoneId + " .dz-preview");
                  //   $.each(previewElement, function(index, val) {
                  //     if(!$(val).attr("id"))
                  //     {
                  //       $(val).attr("id",valr['id']);
                  //       $(val).attr("file_type",valr['file_type']);
                  //       $(val).attr("file_type_title",valr['file_type_title']);

                  //       //$(val).attr("modal_type",modal_type);

                  //       return false;
                  //     }                                                    
                  //   });  
                  // }); 
                }                       
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
              //console.log("errorrrrrrrrrrrrr");    
              //console.log(file); 
              //console.log(errorMessage);  

              var errorDivSpan = file.previewElement.getElementsByClassName("dz-error-message")[0].getElementsByTagName("span")[0];              
              

              if(errorMessage['message'] == 'no_org_no')  
              {
                if($(".notification .no_org_no").length == 0)
                  $(".notification").append('<div class="alert alert-danger no_org_no" role="alert">' +
                                            '<h6 class="alert-heading mb-1">No organization number exist!</h6>'+
                                            '<ul>' +
                                              '<li>' + errorMessage['file'] + '</li>' +
                                            '</ul>' +                                                 
                                        '</div>');
                else
                  $(".notification .no_org_no ul").append('<li>' + errorMessage['file'] + '</li>'); 

                //$('.dz-error-message span').text("No Organization number exist");      
                errorDivSpan.innerHTML = 'No organization number exist';                      
              } 
              else if(errorMessage['message'] == 'no_folder')  
              {
                if($(".notification .no_folder").length == 0)
                  $(".notification").append('<div class="alert alert-danger no_folder" role="alert">' +
                                            '<h6 class="alert-heading mb-1">No VAT reg. folder exist!</h6>'+
                                            '<ul>' +
                                              '<li>' + errorMessage['file'] + '</li>' +
                                            '</ul>' +                                                 
                                        '</div>');
                else
                  $(".notification .no_folder ul").append('<li>' + errorMessage['file'] + '</li>');

                errorDivSpan.innerHTML = 'No VAT reg. folder exist';                      

                //$('.dz-error-message span').text("No folder exist");                            
                //console.log(file.previewElement);
                //console.log(file.previewElement.getElementsByClassName("dz-error-message")[0]);
                
                // var errorDiv = file.previewElement.getElementsByClassName("dz-error-message")[0];
                // console.log(errorDiv.getElementsByTagName("span")[0]);

                // errorDiv.getElementsByTagName("span")[0].innerHTML = '"No folder exist"';
                //file.previewElement.classList.add("dz-error");
              } 

              // let response = xhr.response;console.log(response);
              // let parse = JSON.parse(response, (key, value)=>{
              //   return value;
              // });console.log(parse);
                   
          });                
        },
        addRemoveLinks: true,
        removedfile: function(file) {           
          var file_type = file.previewElement.getAttribute('file_type');
          var file_type_title = file.previewElement.getAttribute('file_type_title');
          var file_id = file.previewElement.getAttribute('id');      
          //var modal_type = file.previewElement.getAttribute('modal_type');          
         
         if(file_id == null)
         {          
          $(".notification .no_org_no ul li:contains("+ file.name +")").remove();
          if($(".notification .no_org_no ul li").length == 0)
            $(".notification .no_org_no").remove();

          $(".notification .no_folder ul li:contains("+ file.name +")").remove();
          if($(".notification .no_folder ul li").length == 0)
            $(".notification .no_folder").remove();
         }
         else
         {
          //console.log(file_id);
            // var modalId = '#' + $(".dz-preview#"+file_id).closest(".modal-file").attr("id");
            // $(modalId + ' .btn-close').prop("disabled", true);

           $.ajax({
             type: 'DELETE',
             url: `${fileUrl}${file_id}`,  
             data: {file_type: file_type, file_type_title: file_type_title},          
             success: function(data){     
                
                $(".notification .success ul li:contains("+ file.name +")").remove();

                if($(".notification .success ul li").length == 0)
                  $(".notification .success").remove();
                // if(data['status'] == "deleted" || data['status'] == "error")
                // {
                //   $(modalId + ' .btn-close').removeAttr('disabled');
                                   
                //   //disable send button
                //   $(modalId + ' .btn-send-email-file').removeClass('btn-success');                  
                //   $(modalId + ' .btn-send-email-file').addClass('btn-danger'); 
                //   $(modalId + ' .btn-send-email-file').addClass('disabled');
                //   $(modalId + ' .btn-send-email-file').attr('disabled'); 

                // }              
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

  loadBulkUploadDropzone($("#dropzone-bulk-upload"));
 
  // $(".ci-missing-datas span").each(function () {    
  //   var vat_reg_id = $(this).data('vat_reg_id');
  //   var missing_commercial_invoice_count = $(this).html();                 
  //   $("#btn-commercial-invoices-" + vat_reg_id + " span").html(missing_commercial_invoice_count);
  // });

  // window.loadCommercialInvoiceDatas = function loadCommercialInvoiceDatas(vat_reg_id)
  // {          
  //   $.ajax({              
  //     url: `${baseUrl}commercial-invoice-datas/${vat_reg_id}`,
  //     type: 'GET',      
  //     success: function (result) {   
  //       console.log(result);
  //       $("#load-datas-ci-"+ vat_reg_id).html("");
  //       $("#load-datas-ci-"+ vat_reg_id).html(result['view']);
  //     },
  //     error: function (err) {
  //       console.log(err);
  //     }
  //   });
  // }

});