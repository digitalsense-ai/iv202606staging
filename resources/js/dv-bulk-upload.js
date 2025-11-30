/**
 * File Upload
 */

'use strict';
Dropzone.autoDiscover = false;

$(function () {
  $(".sk-bounce").show();

  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }
  
  // Variable declaration  
  var fileUrl = baseUrl + 'file/',
      dt_bulkupload_table = $('.datatables-bulkupload'),
      bulkEmailUrl = baseUrl + 'bulk-email/';
  //var fileEmailUrl = baseUrl + 'file-email/'; 
  
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  // Bulkupload datatable
  if (dt_bulkupload_table.length) {
    var dt_bulkupload = dt_bulkupload_table.DataTable({
      data: bulkupload_datas,        
      processing: true,      
      columns: [
        // columns according to JSON
        { data: 'vat_reg_id' },  
        { data: 'fake_id' },       
        { data: 'client_name' },     
        { data: 'vat_period' },  
        { data: 'month_year' },            
        { users: 'users' },  
        { files: 'files' }       
      ],
      lengthMenu: [
          [10, 25, 50, 100, -1],
          [10, 25, 50, 100, 'All']
      ],
      pageLength: -1,      
      columnDefs: [          
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,              
          searchable: false,
          orderable: false,              
          render: function (data, type, full, meta) {    
            var $files = full['files'];  
            var month_year = ($files.length > 0) ? $files[0]['month_year'] : '';

            return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.vat_reg_id +'" data-month_year="'+ month_year +'">';
          },
          checkboxes: {
            selectRow: true,
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        }, 
        {
          // Client name
          targets: 2,
          width: "20%",
          responsivePriority: 2,
          className: "click",
          render: function (data, type, full, meta) {
            var $client_name = full['client_name'];

            // For Avatar badge
            var stateNum = 5;//Math.floor(Math.random() * 6);
            var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            var $state = states[stateNum],
              $client_name = full['client_name'],
              $initials = $client_name.match(/\b\w/g) || [],
              $output;
            $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
            $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';

            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center client-name">' +    
                '<div class="avatar-wrapper">' +
                  '<div class="avatar avatar-sm me-3">' +
                    $output +
                  '</div>' +
                '</div>' +         
                '<div class="d-flex flex-column">' +              
                  $client_name + '<br>' +
                  //'<small>' + full['vat_reg'] + '</small>'
                '</div>' +
              '</div>';
            return $row_output;
          }
        }, 
        {
          // VAT Period
          targets: 3,
          width: "10%",
          responsivePriority: 3,
          className: "click",
          render: function (data, type, full, meta) {
            var $vat_period = full['vat_period'];
            
            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center vat-reg-period">' +                        
                '<div class="d-flex flex-column">' +              
                  $vat_period + '<br>' +                 
                '</div>' +
              '</div>';
            return $row_output;
          }
        }, 
        {
          // Month Year
          targets: 4,
          width: "10%",
          responsivePriority: 4,
          className: "click",
          render: function (data, type, full, meta) {
            var $files = full['files'];
            
            var $row_output = "";
            if($files.length > 0)
              // Creates full output for row
              $row_output =
                '<div class="d-flex justify-content-start align-items-center vat-reg-period">' +                        
                  '<div class="d-flex flex-column">' +              
                    $files[0]['month_year'] + '<br>' +                 
                  '</div>' +
                '</div>';
            return $row_output;
          }
        }, 
        {
          // Users
          targets: 5,
          width: "20%",
          responsivePriority: 5,
          //className: "click",
          render: function (data, type, full, meta) {
            var $users = full['users'],
                $file_type = 'ivf',
                $vat_reg_id = full['vat_reg_id'],
                $client_id = full['client_id'];

            var $row_output = "";  
            if($users.length > 0)
            {
              $row_output +=
                  '<div class="d-flex justify-content-start align-items-center user-name">' +                
                    '<div class="col-sm-12">';

              $.each($users, function (idx, user) {   
                var $user_id = user['id'],
                    $name = user['name'],
                    $email = user['email'];

                // Creates full output for row
                $row_output +=                  
                  '<div class="form-check custom-option custom-option-basic my-2">' +
                    '<label class="switch form-check-label custom-option-content text-end px-3 fs-big" for="cc-to-'+ $file_type +'-'+ $vat_reg_id +'-0-'+ $user_id +'">' +
                      '<input name="chk_cc[]" class="switch-input form-check-input chk_cc cc-to-'+ $file_type +'-'+ $vat_reg_id +'-0" type="checkbox" data-id="'+ $user_id +'" data-vat_reg_id="'+ $vat_reg_id +'" value="'+ $email +'" id="cc-to-'+ $file_type +'-'+ $vat_reg_id +'-0-'+ $user_id +'" />' +
                      '<span class="switch-toggle-slider right-3">' +
                        '<span class="switch-on"></span>' +
                        '<span class="switch-off"></span>' +
                      '</span>' +
                      '<span class="custom-option-header switch-label px-0">' +
                        '<span class="h6 mb-0">'+ $name +'</span>' +
                      '</span>' +
                      '<span class="custom-option-body switch-label text-start px-0 w-100">' +
                        '<small>'+ $email +'</small>' +
                      '</span>' +
                    '</label>' +
                  '</div>';                      
              });

              $row_output +=  '</div>' +
                            '</div>';
            }
            return $row_output;
          }
        }, 
        {
          // Files
          targets: 6,
          width: "15%",
          responsivePriority: 6,
          //className: "click",
          render: function (data, type, full, meta) {
            var $files = full['files'];
            var $vat_reg_id = full['vat_reg_id'];
            var $client_id = full['client_id'];
                       
            var $row_output = "";  
            $.each($files, function (idx, file) {   
              var $file_id = file['id'];
              var $file_type = file['file_type'];
              var $className = ($file_type == 'pdf') ? 'btn-danger' : 'btn-primary';
              
              $row_output +=
                '<span class="btn rounded-pill btn-icon '+ $className +' m-2 btn-download-file" ' +
                  'data-client_id="'+ $client_id +'" data-vat_reg_id="'+ $vat_reg_id +'" data-file_id="'+ $file_id +'" ' +
                  'data-file_type="ivf" data-file_type_title="Import VAT file" title="'+ $file_type +'">' +
                  '<span class="tf-icons bx bxs-download"></span>' +
                '</span>';             
            });
            return $row_output;
          }
        }         
      ],
      order: [[2, 'desc']],
      dom:
        '<"row mx-2"' +
        '<"col-md-2"<"me-3"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      select: {            
        style: 'multi'
      },  
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      },
      // Buttons with Dropdown
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle mx-3',
          text: '<i class="bx bx-upload me-1"></i>Export',
          buttons: [
            {
              extend: 'print',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [2, 3, 4, 5],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('client-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', headingColor)
                  .css('border-color', borderColor)
                  .css('background-color', bodyBg);
                $(win.document.body)
                  .find('table')
                  .addClass('compact')
                  .css('color', 'inherit')
                  .css('border-color', 'inherit')
                  .css('background-color', 'inherit');
              }
            },
            {
              extend: 'csv',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                columns: [2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'excel',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',
              exportOptions: {
                columns: [2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'pdf',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {
                columns: [2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'copy',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {
                columns: [2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('user-name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            }
          ]
        },
        {
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Upload Files</span>',
          className: 'add-new btn btn-primary mx-2',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#bulkUploadModal-ivf'
          }
        },
        {
          text: '<i class="bx bx-envelope me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Send email</span>',
          className: 'btn-send-bulk-email btn btn-secondary',
          attr: {
            'disabled': 'disabled'            
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['client_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      },
      initComplete: function () {
        
          $(".sk-bounce").hide();
          //$("#header-card").show();
          $("#bulkupload-card").show();
      }
    });
  }

  dt_bulkupload.on('select', function (e, dt, type, indexes) {        
    selectDeselectRows(e, dt, type, indexes);
  });

  dt_bulkupload.on('deselect', function (e, dt, type, indexes) {    
    selectDeselectRows(e, dt, type, indexes);
  });

  function selectDeselectRows(e, dt, type, indexes)
  {  
    if (type === 'row') 
    {
      var data = dt_bulkupload
        .rows({ page: 'current', selected: true, indexes: indexes })
        .data()
        .pluck('vat_reg_id')
      ;

      var selected_rows = $.map(data, function(value, index){       
          return [value];
      });
     
      var enable_send_email = [];
      $.each(selected_rows, function (index, row) {  
       
        var chk_checked = 0;
        $.each($('[id^="cc-to-ivf-'+ row +'-0-"]'), function (index1, chk) {
          if($(chk).prop('checked'))
            chk_checked++;           
        }); 

        if(chk_checked > 0)
          enable_send_email.push(row);       
      });
      
      if(selected_rows.length == 0)
        $('.btn-send-bulk-email').prop('disabled', 'disabled'); 
      else
      { 
        if(selected_rows.length == enable_send_email.length)
          $('.btn-send-bulk-email').removeAttr('disabled');         
        else
          $('.btn-send-bulk-email').prop('disabled', 'disabled'); 
      }
    }
  }

  function calculateDropzoneProgress(total_files, response = null)
  {
    var calc_progress_percentage = (100/total_files);
    console.log(total_files);
    console.log("calc_progress_percentage " + calc_progress_percentage);
    var processed_files = $("#navs-notification-success .success ul li").length + 
                          $("#navs-notification-failed .no_org_no ul li").length + 
                          $("#navs-notification-failed .no_folder ul li").length +
                          $("#navs-notification-failed .error ul li").length;

console.log("processed_files " + processed_files);

    var final_progress_percentage = 0;
    if(processed_files > 0)
      final_progress_percentage = calc_progress_percentage * processed_files;

    if(final_progress_percentage > 100)
      final_progress_percentage = 100;

    $("#bulk-upload .progress .progress-bar").attr("aria-valuenow", Math.round(final_progress_percentage) + '%');
    $("#bulk-upload .progress .progress-bar").css("width", Math.round(final_progress_percentage) + '%');
    $("#bulk-upload .progress .progress-bar").html(Math.round(final_progress_percentage) + '%');
    $("#bulk-upload .progress").show();
console.log("final_progress_percentage " + final_progress_percentage);
    if(final_progress_percentage == 100)
    {
      $('#bulkUploadModal-ivf .btn-close.bottom').removeClass('btn-danger');
      $('#bulkUploadModal-ivf .btn-close.bottom').addClass('btn-success');
      $('#bulkUploadModal-ivf .btn-close.bottom').removeClass('disabled');
      $('#bulkUploadModal-ivf .btn-close.bottom').removeAttr('disabled');

      if(response != null)
      {  
        window.location.reload();  
        //bulkupload_datas = drawDtTable(response, 'bulkupload');
        //dt_bulkupload.clear().rows.add(bulkupload_datas).draw();
      }
    }
  }

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
    
    var accepted_files = ".pdf,.xml";
   
    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        //previewTemplate: previewTemplate,
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
          
          var myDropzone = this;
          var dropzoneId = myDropzone.element.getAttribute('id');              

          myDropzone.on("addedfiles", function (file) {   
            //$('.add-new').attr('disabled', 'disabled');
            //$('.add-new').addClass('disabled');                      
            $("#bulk-upload .card-bulk-upload").hide();
            var total_files = myDropzone.files.length;   console.log(total_files)        ;
            calculateDropzoneProgress(total_files);
          });

          myDropzone.on("success", function (file, response) {    //multiple             
              if(response == "")   
              {           
                myDropzone.removeFile(file);

                $("#bulk-upload .progress .progress-bar").attr("aria-valuenow", '0%');
                $("#bulk-upload .progress .progress-bar").css("width", '0%');
                $("#bulk-upload .progress .progress-bar").html('0%');
                $("#bulk-upload .progress").hide();

                $("#bulk-upload .card-bulk-upload").show();
                $("#dropzone-bulk-upload").removeClass("dz-started");
                $("#dropzone-bulk-upload .dz-preview").remove();

                $('#bulkUploadModal-ivf .btn-close.bottom').removeClass('btn-success');
                $('#bulkUploadModal-ivf .btn-close.bottom').addClass('btn-danger');
                $('#bulkUploadModal-ivf .btn-close.bottom').addClass('disabled');
                $('#bulkUploadModal-ivf .btn-close.bottom').prop("disabled", true);

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
                if(response['message'] == 'success')  
                {                           
                  var uploaded_file = response['uploaded_file'][0];    
                  
                  file.previewElement.id = uploaded_file['id'];
                  file.previewElement.setAttribute("file_type", uploaded_file['file_type']);
                  file.previewElement.setAttribute("file_type_title", uploaded_file['file_type_title']);
                 
                  if($("#navs-notification-success .success").length == 0)                 
                    $("#navs-notification-success").append('<div class="alert alert-success success" role="alert">' +
                                              '<h6 class="alert-heading mb-1">Successfully uploaded!</h6>'+
                                              '<ul>' +
                                                '<li>' + response['file'] + '</li>' +
                                              '</ul>' +                                                 
                                          '</div>');                    
                  else                  
                    $("#navs-notification-success .success ul").append('<li>' + response['file'] + '</li>');

                  var total_files = myDropzone.files.length;                    
                  calculateDropzoneProgress(total_files, response);                    
                }                       
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
             let response = xhr.response;             
             let parseresponse = JSON.parse(response, (key, value)=>{
                return value;
             });
             
              var errorDivSpan = file.previewElement.getElementsByClassName("dz-error-message")[0].getElementsByTagName("span")[0];
              
              if(errorMessage['message'] == 'no_org_no')  
              {
                if($("#navs-notification-failed .no_org_no").length == 0)
                  $("#navs-notification-failed").append('<div class="alert alert-danger no_org_no" role="alert">' +
                                            '<h6 class="alert-heading mb-1">No organization number exist!</h6>'+
                                            '<ul>' +
                                              '<li>' + errorMessage['file'] + '</li>' +
                                            '</ul>' +                                                 
                                        '</div>');
                else
                  $("#navs-notification-failed .no_org_no ul").append('<li>' + errorMessage['file'] + '</li>'); 
                
                errorDivSpan.innerHTML = 'No organization number exist';                      
              } 
              else if(errorMessage['message'] == 'no_folder')  
              {
                if($("#navs-notification-failed .no_folder").length == 0)
                  $("#navs-notification-failed").append('<div class="alert alert-danger no_folder" role="alert">' +
                                            '<h6 class="alert-heading mb-1">No VAT reg. folder exist!</h6>'+
                                            '<ul>' +
                                              '<li>' + errorMessage['file'] + '</li>' +
                                            '</ul>' +                                                 
                                        '</div>');
                else
                  $("#navs-notification-failed .no_folder ul").append('<li>' + errorMessage['file'] + '</li>');

                errorDivSpan.innerHTML = 'No VAT reg. folder exist';
              } 
              else if(errorMessage['message'] == 'error')  
              {
                if($("#navs-notification-failed .error").length == 0)
                  $("#navs-notification-failed").append('<div class="alert alert-danger error" role="alert">' +
                                            '<h6 class="alert-heading mb-1">Error: Re-upload the file below after the progress bar reaches 100%!</h6>'+
                                            '<ul>' +
                                              '<li>' + errorMessage['file'] + ' : ' + errorMessage['err_message'] + '</li>' +
                                            '</ul>' +                                                 
                                        '</div>');
                else
                  $("#navs-notification-failed .error ul").append('<li>' + errorMessage['file'] + ' : ' + errorMessage['err_message'] + '</li>');

                errorDivSpan.innerHTML = 'Error: Re-upload the file below after the progress bar reaches 100%';
              } 
              
              var total_files = myDropzone.files.length;                  
              calculateDropzoneProgress(total_files, parseresponse);
          });   

          // myDropzone.on("complete", function(file) {
          //   // Check if all files are uploaded
          //   if (myDropzone.getUploadingFiles().length === 0 && myDropzone.getQueuedFiles().length === 0) {
          //     // Clear all files once all are uploaded
          //     //myDropzone.removeAllFiles();
          //     Dropzone.instances[0].files = [];
          //   }
          // });

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

          $(".notification .error ul li:contains("+ file.name +")").remove();
          if($(".notification .error ul li").length == 0)
            $(".notification .error").remove();
         }
         else
         {         
           $.ajax({
             type: 'DELETE',
             url: `${fileUrl}${file_id}`,  
             data: {file_type: file_type, file_type_title: file_type_title},          
             success: function(data){     
                
                $(".notification .success ul li:contains("+ file.name +")").remove();

                if($(".notification .success ul li").length == 0)
                  $(".notification .success").remove();
                            
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

  // Close
  $(document).on('click', '.btn-close.bottom', function () {
    $('#bulkUploadModal-ivf').modal('hide');
    $(document.body).removeClass("modal-open");
    $(document.body).removeAttr("style");
    $(".modal-backdrop").remove();  

    $("#navs-notification-failed").html('');
    $("#navs-notification-success").html('');

    $("#bulk-upload .progress .progress-bar").attr("aria-valuenow", '0%');
    $("#bulk-upload .progress .progress-bar").css("width", '0%');
    $("#bulk-upload .progress .progress-bar").html('0%');
    $("#bulk-upload .progress").hide();

    $("#bulk-upload .card-bulk-upload").show();
    $("#dropzone-bulk-upload").removeClass("dz-started");
    $("#dropzone-bulk-upload .dz-preview").remove();

    $('#bulkUploadModal-ivf .btn-close.bottom').removeClass('btn-success');
    $('#bulkUploadModal-ivf .btn-close.bottom').addClass('btn-danger');
    $('#bulkUploadModal-ivf .btn-close.bottom').addClass('disabled');
    $('#bulkUploadModal-ivf .btn-close.bottom').prop("disabled", true);

    //$('.add-new').removeAttr('disabled');
    //$('.add-new').removeClass('disabled');
  });

  //Download file
  $(document).on('click', '.btn-download-file', function () {
      var btn_download_file = $(this);
      var data = btn_download_file.data();

      var file_type = data['file_type']; 
      var file_id = data['file_id'];
      var file_extension = $(this).attr("title");
     
      if(file_extension == 'xml')
      {
        btn_download_file.removeClass("btn-primary");
        btn_download_file.addClass("btn-outline-primary");
      }
      else
      {
        btn_download_file.removeClass("btn-danger");
        btn_download_file.addClass("btn-outline-danger");
      }

      btn_download_file.html('<div class="sk-bounce sk-primary sk-center">' +
            '<div class="sk-bounce-dot"></div>' +
            '<div class="sk-bounce-dot"></div>' +
          '</div>');

      $.ajax({      
        url: `${fileUrl}${file_id}/download`,
        type: 'GET',       
        data: {file_type: file_type},  
        success: function (data) {
          if(file_extension == 'xml')
          {
            btn_download_file.addClass("btn-primary");
            btn_download_file.removeClass("btn-outline-primary");
          }
          else
          {
            btn_download_file.addClass("btn-danger");
            btn_download_file.removeClass("btn-outline-danger");
          }  

          btn_download_file.html('<span class="tf-icons bx bxs-download"></span>');

          window.open(data, '_blank');          
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });

  //Send e-mail to selected Client  
  $(document).on('click', '.btn-send-bulk-email', function () {
   
    var btn_send_bulk_email = $(this);      

    var selected_rows = $.map($('#bulkupload-card .form-check-input.dt-checkboxes:checked'), function(c){       
      return {vat_reg_id: c.value, month_year: $(c).data('month_year'), users: []}; 
    });
    
    $.each(selected_rows, function (index, row) {       
      var vat_reg_id = row.vat_reg_id;
      var selected_users = $.map($('#bulkupload-card .form-check-input.switch-input.cc-to-ivf-'+ vat_reg_id +'-0:checked'), function(c){
        return { user_id: $(c).data('id'), email: c.value };       
      });
      
      selected_rows[index]['users'] = selected_users; 
    });
     
      Swal.fire({
        title: 'Are you sure?',
        text: "You want to send email to the selected users!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Send Email!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {        
        if (result.value) {
          btn_send_bulk_email.attr('disabled', 'disabled');
          btn_send_bulk_email.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Sending...');
                   
          $.ajax({           
            data: {selected_rows: selected_rows},     
            type: 'POST',
            url: `${bulkEmailUrl}`,
            success: function (result) {
              
              var messageHtml = '';
              $.each(result.bulk_result, function (index, item) {                
                var className = (item.success) ? 'success' : 'danger';
                messageHtml += '<div class="alert alert-' + className +'" role="alert">' +       
                                item.message +                                                                   
                              '</div>';              
              });
             
              $(messageHtml).insertBefore("#bulkupload-card"); 
              
              bulkupload_datas = drawDtTable(result, 'bulkupload');
              dt_bulkupload.clear().rows.add(bulkupload_datas).draw();

              btn_send_bulk_email.removeAttr('disabled');
              btn_send_bulk_email.html('<span><i class="bx bx-envelope me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Send email</span></span>');
 
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
});