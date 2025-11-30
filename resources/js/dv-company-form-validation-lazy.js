'use strict';
if($("#dropzone-multi-company-"+$("#client_id").val()).length > 0 || $("#dropzone-multi-company-0").length > 0)
  Dropzone.autoDiscover = false;
(function () {
  // Init custom option check
  window.Helpers.initCustomOptionCheck();

  var clientUrl = baseUrl + 'company';      
  var cvrDetailUrl = baseUrl + 'cvr-details';   
  let fileIndex = 0;

  let iti;

  //Load Q & A
  window.loadCompanyQATab = function loadCompanyQATab(client_id, message = null)  
  {           
    if($("#navs-pills-top-qa #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';

      $("#navs-pills-top-qa").html("");                        
      $("#navs-pills-top-qa").append(loadertext);
    }    

    $.ajax({          
      url: `${clientUrl}/qa/${client_id}`,
      type: 'GET',
      success: function (result) {
        $("#navs-pills-top-qa").html("");
        $("#navs-pills-top-qa").append(result['view']);
                
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

  //Load Company History
  window.loadCompanyHistoryTab = function loadCompanyHistoryTab(client_id, message = null)  
  {           
    if($("#navs-pills-top-client-history #load-client-history #loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';

      $("#navs-pills-top-client-history #load-client-history").html("");                        
      $("#navs-pills-top-client-history #load-client-history").append(loadertext);
    }    

    $.ajax({          
      url: `${clientUrl}/history/${client_id}`,
      type: 'GET',
      success: function (result) {
        $("#navs-pills-top-client-history #load-client-history").html("");
        $("#navs-pills-top-client-history #load-client-history").append(result['view']);
                
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

  $(document).on('change', '#formValidationSelect2', function () {
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

      $("#formClient .input-group").addClass("mb-3");
    }

    if(iti != null)
      iti.destroy();
   
    initializeTel();   
  });

  $(document).on('click', '#btn_company_search', function () {    
    var btn_company_search = $(this);
    btn_company_search.attr('disabled', 'disabled');
    btn_company_search.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Searching...');

    var country = 'DK';
    if($("#formValidationSelect2").val() != '')
      country = $("#formValidationSelect2").val();
    
    $.ajax({      
      url: `https://cvrapi.dk/api?country=`+ country +`&search=` + $("#client_name").val(),      
      type: 'GET',
      success: function (response) {
        //console.log(response);

        //console.log(response.vat);
        fillCompanyDetails(country, response);

        btn_company_search.removeAttr('disabled');
        btn_company_search.html('Search');
      },
      error: function (data, textStatus, errorThrown) {
        btn_company_search.removeAttr('disabled');
        btn_company_search.html('Search');
      }
    });
  });  

  $(document).on('click', '#btn_vat_search', function () {  
    var btn_vat_search = $(this);
    btn_vat_search.attr('disabled', 'disabled');
    btn_vat_search.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Searching...');

    var country = 'DK';    
    if($("#formValidationSelect2").val() != '')
      country = $("#formValidationSelect2").val();
    
    $.ajax({      
      url: `https://cvrapi.dk/api?country=`+ country +`&search=` + $("#vatno").val(),      
      type: 'GET',
      success: function (response) {
        console.log(response);

        $.ajax({      
          url: `${cvrDetailUrl}/` + $("#vatno").val(),    
          type: 'GET',
          success: function (cvrresponse) {
            console.log(cvrresponse);
            
            fillCompanyDetails(country, response);

            fillCVRDetails(cvrresponse);

            btn_vat_search.removeAttr('disabled');
            btn_vat_search.html('Search');
          },
          error: function (data, textStatus, errorThrown) {
            btn_vat_search.removeAttr('disabled');
            btn_vat_search.html('Search');
          }
        });

        
      },
      error: function (data, textStatus, errorThrown) {
        btn_vat_search.removeAttr('disabled');
        btn_vat_search.html('Search');
      }
    });
  });  

  function fillCompanyDetails(country, response)
  {   
    $("#formValidationSelect2").val(country);
    //$("#off_country_subject").val("Country: " + $("#formValidationSelect2").find('option:selected').text());

    $("#client_name").val(response.name);
    $("#off_address").val(response.address);    
    $("#off_city").val(response.city);
    $("#off_postcode").val(response.zipcode);
    $("#vatno").val(response.vat);
    $("#short_desc").val(response.companydesc);    
    $("#lrep_email").val(response.email);

    // $("#client_name_subject").val("Company Name: " + response.name);
    // $("#off_address_subject").val("Address: " + response.address);    
    // $("#off_city_subject").val("City: " + response.city);
    // $("#off_postcode_subject").val("Zipcode: " + response.zipcode);    
    // $("#short_desc_subject").val("Company Desc.: " + response.companydesc);    
    // $("#lrep_email_subject").val("Email: " + response.email);

    if(response.startdate != null)
    {
      var sdate = response.startdate.replace(/ - /g, "/");
     
      $("#start_date").val(moment(sdate, "DDMMYYYY").format('DD-MM-YYYY'));
      //$("#start_date_subject").val("Start Date: " + moment(sdate, "DDMMYYYY").format('DD-MM-YYYY'));
    }
    if(response.enddate != null)
    {
      var edate = response.startdate.replace(/ - /g, "/");
     
      $("#end_date").val(moment(edate, "DDMMYYYY").format('DD-MM-YYYY'));
      //$("#end_date_subject").val("End Date: " + moment(edate, "DDMMYYYY").format('DD-MM-YYYY'));
    }
    $("#employees").val(response.employees);
    //$("#employees_subject").val("Employees: " + response.employees);
  }

  function fillCVRDetails(response)
  {
    $('#legalRepRepeater div[data-repeater-list="legalrep"]').html('');
    $('#legalRepRepeater div[data-repeater-list="legalrep"]').html(response.client_cvr_view);     
  }

  // $(document).on('change keyup paste', '.input-group .form-control:not(.subject)', function () { 
  //   var id = $(this).attr("id");
  //   var label_text = $(this).parent("div").find("label").text();

  //   $("#" + id + "_subject").val(label_text + ": " + $(this).val());
  // });

  // $(document).on('change', '.input-group .form-select:not(.subject)', function () { 
  //   var id = $(this).attr("name");
  //   var label_text = $(this).parent("div").find("label").text();

  //   $("#" + id + "_subject").val(label_text + ": " + $(this).find('option:selected').text());
  // });

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const bsValidationForms = document.querySelectorAll('.needs-validation');

  // Loop over them and prevent submission
  Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
    
    form.addEventListener(
      'submit',
      function (event) {
        const submitter = event.submitter;
        
        if(form.id == 'frmAbout' || form.id == 'frmExtraField')
        {
          if (submitter && submitter.hasAttribute('data-repeater-create')) {  
            event.preventDefault();
            return;
          }
        }
                
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        } else {
          event.preventDefault();
          
          if(form.id == 'frmClient' || form.id == 'frmLegalRep' || form.id == 'frmAdditional' || form.id == 'frmBilling' || form.id == 'frmAbout' || form.id == 'frmExtraField')
          {
            var btn_save = $("#"+form.id + " button[type='submit']");
            btn_save.attr('disabled', 'disabled');
            btn_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');

            var client_id = $('#' + form.id + " #" + form.id + "_client_id").val();
            
            var formData = $('#' + form.id).serialize();
          
            if(form.id == 'frmAbout' || form.id == 'frmExtraField')
              formData = new FormData(document.getElementById(form.id));

            // Submit your form          
            // adding or updating user when form successfully validate
            $.ajax({
              //data: $('#' + form.id).serialize(), 
              data: formData,
              method: 'POST', // Important: always POST when using FormData
              processData: (form.id == 'frmAbout' || form.id == 'frmExtraField') ? false : true,
              contentType: (form.id == 'frmAbout' || form.id == 'frmExtraField') ? false : 'application/x-www-form-urlencoded',           
              url: `${clientUrl}/` + client_id,
              //type: 'PUT',
              success: function (result) {
                
                if(result.message != 'Deleted')
                {
                  // sweetalert
                  Swal.fire({
                    icon: 'success',
                    title: `Successfully ${result.message}!`,
                    text: `Company ${result.message} Successfully.`,
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  }).then(function() {                    
                      btn_save.removeAttr('disabled');
                      btn_save.html('Save'); 

                      if(form.id == 'frmAbout')
                        loadCompanyQATab(result.client_id);

                      if(form.id == 'frmExtraField')
                        loadCompanyHistoryTab(result.client_id);
                  });
                }
              },
              error: function (data, textStatus, errorThrown) {
                       console.log(data);
                       console.log(textStatus);
                       console.log(errorThrown);
                Swal.fire({
                  title: 'Update Failed!',                  
                  text: textStatus,
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then(function() {                    
                    btn_save.removeAttr('disabled');
                    btn_save.html('Save');       
                });
              }
            });
          }
          else
          {
            var btn_save = $("#formClient #btn-save");
            btn_save.attr('disabled', 'disabled');
            btn_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Saving...');
           
            // adding or updating user when form successfully validate
            $.ajax({
              data: $('#formClient').serialize(),              
              url: `${clientUrl}`,
              type: 'POST',
              success: function (result) {
                
                var myDropzone = Dropzone.forElement(".dropzone");

                if(myDropzone.files.length > 0)
                {
                  var client_id = result.client_id;
                  $("#dropzone-multi-company-0").attr('data-clientid', client_id);
                  $("#dropzone-multi-company-0").attr('action', `${clientUrl}/files/` + client_id);
                                  
                  myDropzone.processQueue();
                }
                else
                {            
                  btn_save.removeAttr('disabled');    
                  btn_save.html('Saved');                
                  btn_save.addClass('disabled');

                  // sweetalert
                  Swal.fire({
                    icon: 'success',
                    title: `Successfully ${result.message}!`,
                    text: `Company ${result.message} Successfully.`,
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  }).then(function() {
                      // Redirect the user
                      window.location.href = `${baseUrl}companies`;                
                  });
                }
              },
              error: function (data, textStatus, errorThrown) {
                
                Swal.fire({
                  title: 'Duplicate Entry!',                  
                  text: textStatus,
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
            });
          } 
          
        }

        form.classList.add('was-validated');
      },
      false
    );
  });

  $(document).on('click', '.btn-delete-qa-file', function () { 
    var btn_qa_file_delete =  $(this);
    btn_qa_file_delete.attr('disabled', 'disabled');
    btn_qa_file_delete.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Deleting...');

    var data = btn_qa_file_delete.data();
    var file_id = data.file_id;
    var file_type = data.file_type;
    var file_type_title = data.file_type_title;
         
    if(file_id)
    {
      $.ajax({
          type: 'DELETE',
          url: `${clientUrl}/files/${file_id}`,  
          data: {file_type: file_type, file_type_title: file_type_title},      
          success: function(data){            
            if(data['status'] == "deleted")
            {
              var client_id = data['client_id'];

              btn_qa_file_delete.removeAttr('disabled');
              btn_qa_file_delete.html('<i class="bx bx-x me-1"></i> <span class="align-middle">Delete</span>');

              btn_qa_file_delete.parent('li').remove();

              loadCompanyQATab(client_id);
            }              
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log('error: ' + textStatus);
          }
      });
    }    
  });

  // Bootstrap Datepicker
  // --------------------------------------------------------------------
  var bsDatepickerStartDate = $('#start_date'),
      bsDatepickerEndDate = $('#end_date');

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
    // const previewTemplate = `<div class="dz-preview dz-file-preview">
    // <div class="dz-details">
    //   <div class="dz-thumbnail">
    //     <img data-dz-thumbnail>
    //     <span class="dz-nopreview">No preview</span>
    //     <div class="dz-success-mark"></div>
    //     <div class="dz-error-mark"></div>
    //     <div class="dz-error-message"><span data-dz-errormessage></span></div>
    //     <div class="progress">
    //       <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
    //     </div>
    //   </div>
    //   <div class="dz-filename" data-dz-name></div>
    //   <div class="dz-size" data-dz-size></div>
    // </div>
    // </div>`;

    const previewTemplate = `<div class="dz-preview dz-file-preview d-flex justify-content-between align-items-center border w-100 m-0 mb-4 p-0">

    <!-- LEFT SIDE: File Info -->
    <div class="dz-details w-50">      
      <div class="dz-filename position-relative" data-dz-name></div>
      <div class="dz-size py-0" data-dz-size></div>
      
      <div class="progress w-50" style="left: 0;">
        <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
      </div>

      <a class="dz-remove bg-label-danger mx-2 border-top-0 rounded-0" href="javascript:void(0);" data-dz-remove>Remove file</a>      
    </div>

    <!-- RIGHT SIDE: Inputs -->
    <div class="dz-inputs w-50">
      <div class="row g-2 w-px-250 m-4">
        <div class="col-12">
          <label class="form-label" for="subject_input">Subject</label>
          <input type="text" class="form-control border-danger subject-input" name="subject_input" placeholder="Enter subject" required />
        </div>        
        <div class="col-12">
          <label class="form-label" for="file_for">Type</label>
          <select class="form-select file-for" name="file_for">
            <option value="">Select type</option>
            <option value="profile">Profile Picture</option>
            <option value="cover">Cover Picture</option>
            <option value="passport">Passport</option>
            <option value="poa">POA</option>
            <option value="companyreg">Company Reg.</option>
            <option value="other" selected="selected">Other</option>
          </select>
        </div>
        <div class="col-12 d-flex align-items-center">          
          <input class="form-check-input me-1 chk-lock" type="checkbox" name="chk_lock">
          <label class="form-check-label" for="chk_lock">Lock</label>
        </div>
      </div>
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
            var client_id = $("#dropzone-multi-company-0").attr('data-clientid');            
            this.options.url = `${clientUrl}/files/` + client_id;            
          });
          
          var client_id = this.element.getAttribute('data-clientid');
          
          var myDropzone = this;
          
          // Validate subject before uploading
          $("#btn-upload").on("click", function () {
            $("#btn-upload").html('Uploading...');
            $("#btn-upload").attr('disabled', 'disabled');

            let valid = true;

            myDropzone.files.forEach((file) => {
              const preview = file.previewElement;
              const subject = $(preview).find(".subject-input").val();

              if (!subject) {
                valid = false;
                $(preview).find(".subject-input").addClass("border-danger");
              } else {
                file.subject = subject; // attach subject to file object
                $(preview).find(".subject-input").removeClass("border-danger");
              }
            });

            if (valid) {
              myDropzone.processQueue();
            } else {
              alert("Please enter a subject for each file before uploading.");
            }
          });
         
          myDropzone.on("addedfile", function (file) {            
            const index = fileIndex++;
            const previewElement = file.previewElement;

            // Dynamically set the input names to indexed format
            previewElement.querySelector('.file-for').setAttribute('name', `uploads[${index}][file_for]`);
            previewElement.querySelector('.subject-input').setAttribute('name', `uploads[${index}][subject]`);
            previewElement.querySelector('.chk-lock').setAttribute('name', `uploads[${index}][chk_lock]`);
          });

          myDropzone.on("addedfiles", function (file) {                        
            $(".card-company-file-upload .progress").show();
          });

          myDropzone.on("successmultiple", function (file, response) {     
              $(".card-company-file-upload .progress").hide();
              if(response == "")   
              {           
                myDropzone.removeFile(file);   
                $("#dropzone-multi-company-" + client_id).addClass("dz-started");               
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
                  var previewElement = $("#dropzone-multi-company-" + valr['client_id'] + " .dz-preview");
                  $.each(previewElement, function(index, val) {
                    if(!$(val).attr("id"))
                    {
                      $(val).attr("id",valr['id']);
                      return false;
                    }                                                    
                  });  
                });   
                
                $("#btn-upload").html('Upload');
                $("#btn-upload").removeAttr('disabled');

                // sweetalert
                Swal.fire({
                  icon: 'success',
                  title: `Successfully saved!`,
                  text: `Client saved successfully.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then(function() {
                    // Redirect the user
                    window.location.href = `${baseUrl}companies`;                
                });                                             
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {  
            $("#btn-upload").html('Upload');
            $("#btn-upload").removeAttr('disabled');            
              console.log("errorrrrrrrrrrrrr");              
          });

          $.getJSON(`${clientUrl}/files/${client_id}`, function(data) { 
            //$.each(data, function(index, val) {
            //  var mockFile = { name: val.file_name, size: val.size };

            if(data.length > 0)
            {
              $.each(data[0].clientfiles, function(index, val) {
                var mockFile = { name: val.file_name, size: val.file_size };                
                myDropzone.options.addedfile.call(myDropzone, mockFile);
                
                $(mockFile.previewElement).prop('id', val.id);
                $(mockFile.previewElement).addClass('dz-complete');

                $(mockFile.previewElement).find('.subject-input').removeClass('border-danger');
                $(mockFile.previewElement).find('.subject-input').val(val.subject);
                $(mockFile.previewElement).find('.file-for').val(val.file_for);
                if(val.is_locked)
                  $(mockFile.previewElement).find('.chk-lock').prop('checked', true);
              });
            }
          });            
        },
        addRemoveLinks: true,
        removedfile: function(file) {         
          var total_files = this.files.length;  
          if(total_files == 0)
            $(".card-company-file-upload .progress").hide();          
         var _ref;
          return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
       }
    });
  } 

  window.loadCompanyUpdateDropzone = function loadCompanyUpdateDropzone(element)
  {  
    // const previewTemplate = `<div class="dz-preview dz-file-preview">
    // <div class="dz-inputs m-2">
    //   <div class="mb-2">
    //     <input type="text" class="form-control border-danger subject-input" placeholder="Enter subject" required />
    //   </div>
    //   <div class="mb-2">
    //     <input class="form-control file-for" type="hidden" value="other">
    //     <input class="form-check-input me-1 chk-lock" type="checkbox">
    //     <label class="form-check-label" for="chk_lock">Lock</label>
    //   </div>
    // </div>

    // <div class="dz-details">
    //   <div class="dz-thumbnail">
    //     <img data-dz-thumbnail>
    //     <span class="dz-nopreview">No preview</span>
    //     <div class="dz-success-mark"></div>
    //     <div class="dz-error-mark"></div>
    //     <div class="dz-error-message"><span data-dz-errormessage></span></div>
    //     <div class="progress">
    //       <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
    //     </div>
    //   </div>
    //   <div class="dz-filename" data-dz-name></div>
    //   <div class="dz-size" data-dz-size></div>
    // </div>    
    // </div>`;

    const previewTemplate = `<div class="dz-preview dz-file-preview d-flex justify-content-between align-items-center border w-100 m-0 mb-4 p-0">

    <!-- LEFT SIDE: File Info -->
    <div class="dz-details w-50">      
      <div class="dz-filename position-relative" data-dz-name></div>
      <div class="dz-size py-0" data-dz-size></div>
      
      <div class="progress w-50" style="left: 0;">
        <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
      </div>

      <div class="buttons d-flex mt-2">
        <a class="dz-remove bg-label-danger m-2 px-3 border-top-0 rounded-0 fs-6" href="javascript:void(0);" data-dz-remove><i class="tf-icons bx bx-x me-1"></i>Remove</a>
        <!-- <button type="button" class="btn btn-label-primary mx-2 border-top-0 rounded-0 btn-download-file btn-download-company-file" data-file_type="company"><i class="tf-icons bx bxs-download me-1"></i> Download</button>-->
        <button type="button" class="btn rounded-pill btn-icon btn-primary m-2 btn-download-file btn-download-company-file d-none" data-file_type="company"><i class="tf-icons bx bxs-download"></i></button>
        <button type="button" class="btn rounded-pill btn-icon btn-warning m-2 btn-view-file btn-view-company-file d-none" data-file_type="company"><span class="fa-solid fa-eye"></span></button>
      </div>
    </div>

    <!-- RIGHT SIDE: Inputs -->
    <div class="dz-inputs w-50">
      <div class="row g-2 w-px-250 m-4">
        <div class="col-12">
          <label class="form-label" for="subject_input">Subject</label>
          <input type="text" class="form-control border-danger subject-input" name="subject_input" placeholder="Enter subject" required />
        </div>        
        <div class="col-12">
          <label class="form-label" for="file_for">Type</label>
          <select class="form-select file-for" name="file_for">
            <option value="">Select type</option>
            <option value="profile">Profile Picture</option>
            <option value="cover">Cover Picture</option>
            <option value="passport">Passport</option>
            <option value="poa">POA</option>
            <option value="companyreg">Company Reg.</option>
            <option value="other" selected="selected">Other</option>
          </select>
        </div>
        <div class="col-12 d-flex align-items-center">          
          <input class="form-check-input me-1 chk-lock" type="checkbox" name="chk_lock">
          <label class="form-check-label" for="chk_lock">Lock</label>
        </div>
      </div>
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
        autoProcessQueue: false,        
        // renameFile: function(file) {
        //     var dt = new Date();
        //     var time = dt.getTime();
        //     return time+file.name;
        // },
        acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xls,.xlsx,.doc,.docx",        
        timeout: 180000,       
        init : function() {                

          var client_id = this.element.getAttribute('data-clientid');
          
          var myDropzone = this;

          //$("#btn-upload").attr('disabled', 'disabled');
          
          // Validate subject before uploading
          $("#btn-upload").on("click", function () {
            $("#btn-upload").html('Uploading...');
            $("#btn-upload").attr('disabled', 'disabled');

            let valid = true;

            myDropzone.files.forEach((file) => {
              const preview = file.previewElement;
              const subject = $(preview).find(".subject-input").val();

              if (!subject) {
                valid = false;
                $(preview).find(".subject-input").addClass("border-danger");
              } else {
                file.subject = subject; // attach subject to file object
                $(preview).find(".subject-input").removeClass("border-danger");
              }
            });

            if (valid) {
              myDropzone.processQueue();
            } else {
              alert("Please enter a subject for each file before uploading.");
            }
          });

          // // Send subject along with file
          // myDropzone.on("sending", function (file, xhr, formData) { console.log(file);
          //   formData.append("subject", file.subject || "");
          //   formData.append("chk_lock", file.chk_lock || "");
          // });

          myDropzone.on("addedfile", function (file) {
            const index = fileIndex++;
            const previewElement = file.previewElement;

            // Dynamically set the input names to indexed format
            previewElement.querySelector('.file-for').setAttribute('name', `uploads[${index}][file_for]`);
            previewElement.querySelector('.subject-input').setAttribute('name', `uploads[${index}][subject]`);
            previewElement.querySelector('.chk-lock').setAttribute('name', `uploads[${index}][chk_lock]`);
          });

          myDropzone.on("successmultiple", function (file, response) {  
            if(response == "")   
            {           
              myDropzone.removeFile(file);   
              $("#dropzone-multi-company-" + client_id).addClass("dz-started");               
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
                var previewElement = $("#dropzone-multi-company-" + valr['client_id'] + " .dz-preview");
                $.each(previewElement, function(index, val) {
                  if(!$(val).attr("id"))
                  {
                    $(val).attr("id",valr['id']);

                    $(val).find('.btn-download-company-file').attr('data-file_id', valr['id']);
                    $(val).find('.btn-download-company-file').removeClass('d-none');

                    var file_type = valr['file_name'].split('.'); 
                    if(file_type[1] == 'pdf' || file_type[1] == 'jpeg' || file_type[1] == 'jpg' || 
                      file_type[1] == 'png' || file_type[1] == 'gif')   
                    {
                      $(val).find('.btn-view-company-file').attr('data-file_id', valr['id']);
                      $(val).find('.btn-view-company-file').removeClass('d-none');
                    }

                    return false;
                  }                                                    
                });  
              }); 

              $("#btn-upload").html('Upload');
              $("#btn-upload").removeAttr('disabled');

              loadCompanyHistoryTab(client_id);                                                                            
            } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
            $("#btn-upload").html('Upload');
            $("#btn-upload").removeAttr('disabled');
              console.log("errorrrrrrrrrrrrr");              
          });

          $.getJSON(`${clientUrl}/files/${client_id}`, function(data) {            
            //$.each(data, function(index, val) {
             
            if(data.length > 0)
            {  
              $.each(data[0].clientfiles, function(index, val) { 
                var mockFile = { name: val.file_name, size: val.file_size };
                myDropzone.options.addedfile.call(myDropzone, mockFile);
                
                $(mockFile.previewElement).prop('id', val.id);
                $(mockFile.previewElement).addClass('dz-complete');

                var file_type = val.file_name.split('.'); 
                $(mockFile.previewElement).find(".btn-download-company-file").attr('data-file_id', val.id);  
                $(mockFile.previewElement).find(".btn-download-company-file").removeClass('d-none');    
                if(file_type[1] == 'pdf' || file_type[1] == 'jpeg' || file_type[1] == 'jpg' || 
                  file_type[1] == 'png' || file_type[1] == 'gif')   
                {         
                  $(mockFile.previewElement).find(".btn-view-company-file").attr('data-file_id', val.id);
                  $(mockFile.previewElement).find(".btn-view-company-file").removeClass('d-none');
                }

                $(mockFile.previewElement).find('.subject-input').removeClass('border-danger');
                $(mockFile.previewElement).find('.subject-input').val(val.subject);
                $(mockFile.previewElement).find('.file-for').val(val.file_for);
                if(val.is_locked)
                  $(mockFile.previewElement).find('.chk-lock').prop('checked', true);
              });
            }
          });            
        },
        addRemoveLinks: true,
        removedfile: function(file) {  
          var file_id = file.previewElement.getAttribute('id');
         
         if(file_id)
         {
           $.ajax({
             type: 'DELETE',
             url: `${clientUrl}/files/${file_id}`,          
             success: function(data){
                
                if(data['status'] == "deleted")
                {
                  var client_id = data['client_id'];                                                            
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

  if($("#dropzone-multi-company-"+$("#client_id").val()).length > 0)
    loadCompanyUpdateDropzone("#dropzone-multi-company-"+$("#client_id").val()); 

  if($("#dropzone-multi-company-0").length > 0)
    loadCompanyDropzone("#dropzone-multi-company-0"); 

  // let iti;
  if(iti != null)
    iti.destroy();
  
  function initializeTel() {
      var input = document.getElementById("telephone");
      iti = intlTelInput(input, {
          initialCountry: $("#formValidationSelect2").val(),
          preferredCountries: ["dk"],
          separateDialCode: true,
          utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"    
      });
  }
  initializeTel();

  var formAboutRepeater = $('#aboutRepeater, #frmAbout'); //$('.form-about-repeater');

  if (formAboutRepeater.length) {
    var row = ($('div[data-repeater-list="about"] div[data-repeater-item=""]').length == 0) ? 1 : ($('div[data-repeater-list="about"] div[data-repeater-item=""]').length + 1);//2;
    //var col = 1;
    formAboutRepeater.on('submit', function (e) {
      e.preventDefault();
    });
    formAboutRepeater.repeater({
      show: function () {        
        $(this).attr('data-qa_id','');
        var fromControl = $(this).find('.form-control, .form-select, .form-div, .accordion-button');
        var formLabel = $(this).find('.form-label');
        
        var col = 1;
        fromControl.each(function (i) {
          var id = 'form-about-repeater-' + row + '-' + col;

          if(!$(fromControl[i]).hasClass('accordion-button'))
          {
            $(fromControl[i]).attr('id', id);
            $(fromControl[i]).attr('data-row', row);
            $(fromControl[i]).attr('data-col', col);
          }

          if($(fromControl[i]).hasClass('accordion-button'))
          {
            var id = 'accordionAboutCountry-' + row + '-' + col;

            $(fromControl[i]).attr('data-bs-target', '#' + id);
            console.log($(fromControl[i]).closest('div.accordion-item'));
            console.log($(fromControl[i]).closest('div.accordion-item').find('.accordion-collapse'));
            $(fromControl[i]).closest('div.accordion-item').find('.accordion-collapse').attr('id', id);
          }

          $(formLabel[i]).attr('for', id);
          col++;
        });

        row++;

        //restrictColumnOptions();

        $(this).slideDown();
      },
      hide: function (e) {
        //confirm('Are you sure you want to delete this row?') && $(this).slideUp(e);

        var $item = $(this);
        console.log($(this).data('qa_id'));
        $item.slideUp(function () {
          var qa_id = $(this).data('qa_id');
          if(qa_id != '')
          {
            $.ajax({
               type: 'DELETE',
               url: `${clientUrl}/qa/${qa_id}`,          
               success: function(data){
                  $item.remove();            
               },
               error: function(jqXHR, textStatus, errorThrown){
                  console.log('error: ' + textStatus);
               }
            });
          }
          else
            $item.remove();
          //endFormula();
        });
      }
    });
  }

  $(document).on('change', '.about-country', function () {
    var data = $(this).data();
    
    if($(this).val() == '')
      //$('#form-about-repeater-' + data.row + '-2').hide();
      $('#accordionAboutCountry-' + data.row + '-2').hide();    
    else
      $('#accordionAboutCountry-' + data.row + '-2').show();
  });

  /*Extra*/
  var formExtraRepeater = $('#extraRepeater, #frmExtraField');

  if (formExtraRepeater.length) { 
    var row = ($('div[data-repeater-list="extra"] div[data-repeater-item=""]').length == 0) ? 0 : $('div[data-repeater-list="extra"] div[data-repeater-item=""]').length - 1;
    //var col = 1;
    formExtraRepeater.on('submit', function (e) {
      e.preventDefault();
    });
    formExtraRepeater.repeater({
      initEmpty: ($('#frmExtraField').length == 1) ? false : true,
      show: function () {
        $(this).attr('data-extra_id','');
        var fromControl = $(this).find('.form-control');
        
        var col = 1;
        fromControl.each(function (i) {
          var id = 'form-extra-repeater-' + row + '-' + col;
          
          $(fromControl[i]).attr('id', id);
          $(fromControl[i]).attr('data-row', row);
          $(fromControl[i]).attr('data-col', col);
         
          col++;
        });

        row++;
       
        $(this).slideDown();
      },
      hide: function (e) {        
        var $item = $(this);
      console.log($item);
        $item.slideUp(function () {
          var client_id = $("#client_id").val();
          var extra_id = $(this).data('extra_id');
          console.log(client_id + "  " + extra_id);
          if(extra_id != '')
          {
            $.ajax({
               type: 'DELETE',
               url: `${clientUrl}/extrafield/${extra_id}`,          
               success: function(data){
                  $item.remove(); 

                  loadCompanyHistoryTab(client_id);           
               },
               error: function(jqXHR, textStatus, errorThrown){
                  console.log('error: ' + textStatus);
               }
            });
          }
          else
            $item.remove();         
        });
      }
    });
  }
  /*Extra*/

  /*History Click*/
  $(document).on('click', '.extra-field, .upload-field', function () {
    var client_id = $("#client_id").val();

    var extra_id = '';
    var upload_id = '';
    if ($(this).is('[data-extra_id]'))
      extra_id = $(this).data('extra_id');
    else
      upload_id = $(this).data('upload_id');
 
    var tabSelector = '#btn-client';
    $(tabSelector).tab('show');  

    let $input;
    if (extra_id)
    { 
      $input = $('div[data-repeater-list="extra"] div[data-repeater-item=""][data-extra_id="'+extra_id+'"]').find('.form-control.subject');
    }
    else  
    {     
      $input = $("#dropzone-multi-company-" + client_id + " #" + upload_id).find('.form-control.subject-input');
    }
    
    if ($input.length) 
    {
      $('html, body').animate({
          scrollTop: $input.offset().top - 100 // adjust offset as needed
      }, 400, function() {
          $input.focus();
      });
    }

    // // After tab is shown, remove the hash from URL
    // $(tabSelector).on('shown.bs.tab', function () { 
    //   console.log("tab open");
      
    // }); 

  });
  /*History Click*/

  /*Legal Rep.*/
  var formLegalRepRepeater = $('#legalRepRepeater, #frmLegalRep');

  if (formLegalRepRepeater.length) { 
    var row = ($('div[data-repeater-list="legalrep"] div[data-repeater-item=""]').length == 0) ? 0 : $('div[data-repeater-list="legalrep"] div[data-repeater-item=""]').length - 1;
    //var col = 1;
    formLegalRepRepeater.on('submit', function (e) {
      e.preventDefault();
    });
    formLegalRepRepeater.repeater({
      //initEmpty: ($('#frmLegalRep').length == 1) ? false : true,
      show: function () {
        $(this).attr('data-lrep_id','');
        var fromControl = $(this).find('.form-control');
        var slno = $(this).find('.sl-no');
        $(slno).html((row +2) + '.');

        var col = 1;
        fromControl.each(function (i) {
          var id = 'form-lrep-repeater-' + row + '-' + col;
          
          $(fromControl[i]).attr('id', id);
          $(fromControl[i]).attr('data-row', row);
          $(fromControl[i]).attr('data-col', col);
         
          col++;
        });

        row++;
       
        $(this).slideDown();
      },
      hide: function (e) {        
        var $item = $(this);
      console.log($item);
        $item.slideUp(function () {
          var client_id = $("#client_id").val();
          var lrep_id = $(this).data('lrep_id');
          console.log(client_id + "  " + lrep_id);
          if(lrep_id != '')
          {
            $.ajax({
               type: 'DELETE',
               url: `${clientUrl}/lrepfield/${lrep_id}`,          
               success: function(data){
                  $item.remove(); 

                  loadCompanyHistoryTab(client_id);           
               },
               error: function(jqXHR, textStatus, errorThrown){
                  console.log('error: ' + textStatus);
               }
            });
          }
          else
            $item.remove();         
        });
      }
    });
  }
  /*Legal Rep.*/
})();
