'use strict';

if($("#dropzone-multi-register-"+$("#client_id").val()).length > 0 || $("#dropzone-multi-register-0").length > 0)
  Dropzone.autoDiscover = false;

(function () {
  // Init custom option check
  window.Helpers.initCustomOptionCheck();

  var clientUrl = baseUrl + 'register';      

  $(document).on('change', '#multiStepsState', function () {
    if($(this).val() == 'DK' || $(this).val() == 'NO')
    {
      $("#btn_vat_search").show();
      $("#clientNameHelp").show();

      $("#formClient .input-group").removeClass("mb-3");
    }
    else
    {
      $("#btn_vat_search").hide();
      $("#clientNameHelp").hide();

      $("#multiStepsRegForm .input-group").addClass("mb-3");
    }

    if(iti != null){
      iti.destroy();
    }
    initializeTel();   
  });

  $(document).on('click', '#btn_company_search', function () {        
    var country = 'DK';
    if($("#multiStepsState").val() != '')
      country = $("#multiStepsState").val();
    
    $.ajax({      
      url: `https://cvrapi.dk/api?country=`+ country +`&search=` + $("#multiStepsCompanyName").val(),      
      type: 'GET',
      success: function (response) {        
        fillCompanyDetails(country, response);
      },
      error: function (data, textStatus, errorThrown) {
        console.log(data);
      }
    });
  });  

  $(document).on('click', '#btn_vat_search', function () {  
    var btn_vat_search = $(this);
    btn_vat_search.attr('disabled', 'disabled');
    btn_vat_search.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Searching...');

    var country = 'DK';    
    if($("#multiStepsState").val() != '')
      country = $("#multiStepsState").val();
    
    $.ajax({      
      url: `https://cvrapi.dk/api?country=`+ country +`&search=` + $("#multiStepsVatNo").val(),      
      type: 'GET',
      success: function (response) {
        console.log(response);

        console.log(response.vat);
        fillCompanyDetails(country, response);

        btn_vat_search.removeAttr('disabled');
        btn_vat_search.html('Search');
      },
      error: function (data, textStatus, errorThrown) {
        btn_vat_search.removeAttr('disabled');
        btn_vat_search.html('Search');
      }
    });
  });  

  function fillCompanyDetails(country, response)
  {   
    $("#multiStepsState").val(country);

    $("#multiStepsCompanyName").val(response.name);
    $("#multiStepsAddress").val(response.address);    
    $("#multiStepsCity").val(response.city);
    $("#multiStepsZipcode").val(response.zipcode);
    $("#multiStepsVatNo").val(response.vat);
    $("#multiStepsCompDesc").val(response.companydesc);    
    $("#multiStepsCompEmail").val(response.email);

    if(response.startdate != null)
    {
      var sdate = response.startdate.replace(/ - /g, "/");
     
      $("#multiStepsStartDate").val(moment(sdate, "DDMMYYYY").format('DD-MM-YYYY'));
    }
    if(response.enddate != null)
    {
      var edate = response.enddate.replace(/ - /g, "/");
     
      $("#multiStepsEndDate").val(moment(edate, "DDMMYYYY").format('DD-MM-YYYY'));
    }
    $("#multiStepsEmployees").val(response.employees);
  }

  // Bootstrap Datepicker
  // --------------------------------------------------------------------
  var bsDatepickerStartDate = $('#multiStepsStartDate'),
      bsDatepickerEndDate = $('#multiStepsEndDate');

  // Basic
  if (bsDatepickerStartDate.length) {
    bsDatepickerStartDate.datepicker({
      format: "dd-mm-yyyy",
      todayHighlight: true,
      autoclose: true,
      orientation: 'auto left'     
    });
  }

  // Basic
  if (bsDatepickerEndDate.length) {
    bsDatepickerEndDate.datepicker({
      format: "dd-mm-yyyy",
      todayHighlight: true,
      autoclose: true,
      orientation: 'auto left'     
    });
  }  
  
  window.loadCompanyDropzone = function loadCompanyDropzone(element)
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
        autoProcessQueue: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        previewTemplate: previewTemplate,
        maxFilesize: 12,
        parallelUploads:10,
        uploadMultiple:true,
        renameFile: function(file) {
            var dt = new Date();
            var time = dt.getTime();
            return time+file.name;
        },
        acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xls,.xlsx,.doc,.docx",        
        timeout: 180000,       
        init : function() {
          
          this.on("processing", function(file) {
            var client_id = $("#dropzone-multi-register-0").attr('data-clientid');            
            this.options.url = `${clientUrl}/files/` + client_id;            
          });
          
          var client_id = this.element.getAttribute('data-clientid');
          
          var myDropzone = this;
          
          // myDropzone.on("addedfiles", function (file) {                        
          //   $(".card-company-file-upload .progress").show();
          // });

          myDropzone.on("successmultiple", function (file, response) {     
              //$(".card-company-file-upload .progress").hide();
              if(response == "")   
              {           
                myDropzone.removeFile(file);   
                $("#dropzone-multi-register-" + client_id).addClass("dz-started");               
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
                $.each(response, function(indexr, valr) { 
                  var previewElement = $("#dropzone-multi-register-" + valr['client_id'] + " .dz-preview");
                  $.each(previewElement, function(index, val) {
                    if(!$(val).attr("id"))
                    {
                      $(val).attr("id",valr['id']);
                      return false;
                    }                                                    
                  });  
                });   
                
                var btn_submit = $("#multiStepsRegForm").find('button.btn-submit');
                btn_submit.html('Submitted');   

                // sweetalert
                Swal.fire({
                  icon: 'success',
                  title: `Successfully Created!`,
                  text: `User and company created successfully.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then(function() {
                    // Redirect the user
                    window.location.href = `${baseUrl}signin`;                
                });                                             
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {              
              console.log("errorrrrrrrrrrrrr");              
          });

          $.getJSON(`${clientUrl}/files/${client_id}`, function(data) {             
            if(data.length > 0)
            {
              $.each(data[0].clientfiles, function(index, val) {
                var mockFile = { name: val.file_name, size: val.file_size };                
                myDropzone.options.addedfile.call(myDropzone, mockFile);
                
                $(mockFile.previewElement).prop('id', val.id);
                $(mockFile.previewElement).addClass('dz-complete');
              });
            }
          });            
        },
        addRemoveLinks: true,
        removedfile: function(file) {         
          // var total_files = this.files.length;  
          // if(total_files == 0)
          //   $(".card-company-file-upload .progress").hide();          
         var _ref;
          return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
       }
    });
  } 
 
  if($("#dropzone-multi-register-0").length > 0)
    loadCompanyDropzone("#dropzone-multi-register-0"); 

})();
