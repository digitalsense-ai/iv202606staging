/**
 * Compliance File Upload
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
      dt_compliance_table = $('.datatables-compliance'),
      dt_matched_user_table = $('.datatables-matched-user'),
      flagUrl = baseUrl + 'assets/img/flags/',   
      statusObj = {      
        0: { title: 'Inactive', class: 'bg-label-secondary' },
        1: { title: 'Active', class: 'bg-label-success' }      
      },
      langObj = {      
        'en': { title: 'English' },
        'dk': { title: 'Danish' }      
      };

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  // Matched Users datatable
  if (dt_matched_user_table.length) {
    var dt_matched_user = dt_matched_user_table.DataTable({
      data: matched_user_datas,        
      processing: true,   
      autoWidth: false, 
      columns: [
        // columns according to JSON       
        { data: 'id' },
        { data: 'client_name' },
        { data: 'company_reg_no' },
        { data: 'firstname' },        
        { data: 'middlename' },
        { data: 'lastname' },      
        { data: 'designation' },        
        { data: 'pep' },
        { data: 'eus' },
        { data: 'unsc' },
        { data: 'comment' }  
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
          // Client name                   
          targets: 1,
          width: "20%",
          responsivePriority: 1,
          className: "click",
          render: function (data, type, full, meta) {
            var $client_name = full['client_name'];

            // For Avatar badge
            var stateNum = 5;//Math.floor(Math.random() * 6);
            var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            var $state = states[stateNum],
              $client_name = full['client_name'],
              $initials = ($client_name == '-') ? $client_name : $client_name.match(/\b\w/g) || [],
              $output;
            $initials = ($initials == '-') ? '-' : (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
            $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';

            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center client-name" data-client_id="'+ full['id'] +'" data-status="'+ full['status'] +'">' +             
              '<div class="d-flex flex-column">' +              
              $client_name +            
              '</div>' +
              '</div>';
            return $row_output;
          }
        }, 
        {
          // Company reg. no
          targets: 2,          
          width: "15%", 
          responsivePriority: 2,  
          render: function (data, type, full, meta) {
            var $company_reg_no = full['company_reg_no'];
            
            return '<span>' + $company_reg_no + '</span>';
          }
        }, 
        {
          // User firstname
          targets: 3,          
          width: "10%", 
          responsivePriority: 3,  
          render: function (data, type, full, meta) {
            var $firstname = full['firstname'];
            
            return '<span>' + $firstname + '</span>';
          }
        }, 
        {
          // User middlename
          targets: 4,          
          width: "10%", 
          responsivePriority: 4,  
          render: function (data, type, full, meta) {
            var $middlename = '';
            
            return '<span>' + $middlename + '</span>';
          }
        }, 
        {
          // User lastname
          targets: 5,          
          width: "10%", 
          responsivePriority: 5,  
          render: function (data, type, full, meta) {
            var $lastname = full['lastname'];
            
            return '<span>' + $lastname + '</span>';
          }
        }, 
        {
          // Designation
          targets: 6,          
          width: "10%", 
          responsivePriority: 6,  
          render: function (data, type, full, meta) {
            var $designation = full['designation'];
            
            return '<span>' + $designation + '</span>';
          }
        }, 
        {
          // Political Exposed Person (x marks yes)
          targets: 7,          
          width: "5%", 
          responsivePriority: 7,  
          visible: false,
          className: "text-center",
          render: function (data, type, full, meta) {  
            var $compliance_status = full['compliance_status'];
            
            return ($compliance_status == 1) ? '<span>x</span>' : '';                      
          }
        }, 
        {
          // EU Sancation list (x marks yes)
          targets: 8,          
          width: "5%", 
          responsivePriority: 8,  
          visible: false,
          className: "text-center",
          render: function (data, type, full, meta) {            
            var $compliance_status = full['compliance_status'];
            
            return ($compliance_status == 2) ? '<span>x</span>' : ''; 
          }
        }, 
        {
          // UNSC  list (x marks yes)
          targets: 9,          
          width: "5%", 
          responsivePriority: 9,  
          visible: false,
          className: "text-center",
          render: function (data, type, full, meta) {            
            var $compliance_status = full['compliance_status'];
            
            return ($compliance_status == 3) ? '<span>x</span>' : ''; 
          }
        },
        {
          // Comment
          targets: 10,          
          width: "20%", 
          responsivePriority: 10,  
          visible: false,
          className: "text-center",
          render: function (data, type, full, meta) {                        
            return ''; 
          }
        }
      ],
      order: [[0, 'desc']],
      dom:
        '<"row mx-2"' +
        '<"col-md-2"<"me-3"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
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
              messageTop: function() { 
                return moment().format('DD-MM-YYYY');                
              },
              messageBottom: function() { 
                return dtFooterNote('print');                
              },
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                // prevent avatar to be print
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
              extend: 'excel',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',
              messageTop: function() { 
                return moment().format('DD-MM-YYYY');                
              },
              messageBottom: function() { 
                return dtFooterNote('excel');                
              },
              customize: function (xlsx) {
                  // more code
                  let sheet = xlsx.xl.worksheets['sheet1.xml'];
                  // set height for all col
                  //$('row, rels').attr('ss:Height', '200');
                  $('row', sheet).last().attr('ht', '150').attr('customHeight', "1");
                  // more code
              },
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
              orientation: 'landscape',
              pageSize: 'LEGAL',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              customize : function(doc) {
               
                // // Add a custom message at the top of the PDF
                // var heading = doc.content[0].text;
                // doc.content[0].text = heading + "\n";

                // Add the date in a new text object with right alignment
                // doc.content.push({
                //     text: "Print Date: " + moment().format('DD-MM-YYYY'),
                //     alignment: 'right', // Right align the date
                //     margin: [0, 10, 0, 0] // Optional: Add some margin
                // });
                doc.content.splice(1, 0, {
                    text: moment().format('DD-MM-YYYY') + "\n",
                    alignment: 'right', // Right align the date
                    fontSize: 12, // Set the desired font size for the date
                    margin: [0, 10, 0, 0] // Optional: Add some margin
                });

                //doc.defaultStyle.alignment = 'center';  
                //doc.content[1].table.widths = ['*','*','*','*','*','*','*','*','*','30%'];
                doc.content[2].table.widths = ['10%','10%','10%','10%','10%','10%','5%','5%','5%','25%'];
                //var rowCount = doc.content[1].table.body.length;console.log(rowCount);
                //console.log(doc.content[1].table.body[1][9]);
                // for (var i = 0; i < rowCount+1; i++) {
                //   doc.content[1].table.body[i][7].alignment = 'center';
                //   doc.content[1].table.body[i][8].alignment = 'center';
                //   doc.content[1].table.body[i][9].alignment = 'center';
                // };
              },
              //footer: true,                         
              messageBottom: function() { 
                return dtFooterNote();                
              },
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                // // prevent avatar to be display
                // format: {
                //   body: function (inner, coldex, rowdex) {
                //     if (inner.length <= 0) return inner;
                //     var el = $.parseHTML(inner);
                //     var result = '';
                //     $.each(el, function (index, item) {
                //       if (item.classList !== undefined && item.classList.contains('user-name')) {
                //         result = result + item.lastChild.firstChild.textContent;
                //       } else if (item.innerText === undefined) {
                //         result = result + item.textContent;
                //       } else result = result + item.innerText;
                //     });
                //     return result;
                //   }
                // }
              }
            }            
          ]
        },
        {
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Upload file</span>',
          className: 'add-new btn btn-primary me-3',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#UploadModal-compliance'
          }
        },
        {
          text: '<i class="bx bx-refresh fs-large me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Refresh CVR Datas</span>',
          className: 'refresh-cvr btn btn-primary',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#selectClientVatNos'
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['name'];
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
          $("#matched-user-card").show();         
      }
    });
  }

  function dtFooterNote(type = 'pdf')
  {    
    var htmlbr = (type == 'print') ? '<br>' : '\n\n';

    var footernote = ((type == 'excel') ? '' : (htmlbr + htmlbr + htmlbr)) + 'This report was approved by:' + htmlbr;   
    footernote += 'Name:________________________________' + htmlbr;
    footernote += 'Signature:________________________________' + htmlbr;
    footernote += 'Date of approval:________________________________' + htmlbr;

    return footernote;
  }

  function calculateComplianceDropzoneProgress(total_files, processed_files)
  {    
    //var calc_progress_percentage = Math.round(100/total_files);    
    var calc_progress_percentage = (100/total_files);    
    //var processed_files = $("#navs-notification-success .success ul li").length;

    var final_progress_percentage = 0;
    if(processed_files > 0)
      final_progress_percentage = calc_progress_percentage * processed_files;

    if(final_progress_percentage > 100)
      final_progress_percentage = 100;

//console.log($("#compliance-upload .progress.custom .progress-bar").html());
    $("#compliance-upload .progress.custom .progress-bar").attr("aria-valuenow", Math.round(final_progress_percentage) + '%');
    $("#compliance-upload .progress.custom .progress-bar").css("width", Math.round(final_progress_percentage) + '%');
    $("#compliance-upload .progress.custom .progress-bar").html(Math.round(final_progress_percentage) + '%');
    $("#compliance-upload .progress.custom").show();
//console.log($("#compliance-upload .progress.custom .progress-bar").html());
    if(final_progress_percentage == 100)
    {      
      $("#compliance-upload .progress.custom").hide();
      $("#compliance-upload .card-compliance-upload").show();
      // $('#bulkUploadModal-ivf .btn-close.bottom').removeClass('btn-danger');
      // $('#bulkUploadModal-ivf .btn-close.bottom').addClass('btn-success');
      // $('#bulkUploadModal-ivf .btn-close.bottom').removeClass('disabled');
      // $('#bulkUploadModal-ivf .btn-close.bottom').removeAttr('disabled');

      // if(response != null)
      // {
      //   bulkupload_datas = drawDtTable(response, 'bulkupload');
      //   dt_bulkupload.clear().rows.add(bulkupload_datas).draw();
      // }
    }
  }

  // Select the VAT No's
  $(document).on('click', '.select-vatnos', function () {  
    var btn_refresh_cvr = $('.refresh-cvr');
    btn_refresh_cvr.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>'+ 'Refreshing...');

    var selected_vat_no = [];
  
    $('li input:checked').each(function() {         
      var vat_no_data = $(this).data(); //console.log(vat_no_data);
      var client_name = vat_no_data['client_name'];  
      var vat_no = vat_no_data['vat_no'];     

      selected_vat_no.push({
        'client_id' : $(this).val(),
        'client_name' : client_name,
        'client_vat_no'  :  vat_no    
      });        
    });

    var vat_div = ""; 
    var selected_vat_details="";
    var falied_vatno = 0;

    $("input[name=selected_vat_nos]").val('');

    var ajaxCounter = {
        goal: selected_vat_no.length,
        total: 0,
        success: 0,
        notmodified: 0,
        error: 0,
        timeout: 0,
        abort: 0,
        parsererror: 0
    }

    $.each(selected_vat_no, function(key,value) { //console.log(value);
        $.ajax({
            type: 'GET',
             url: `${baseUrl}cvr-vat-no/refreshcvr`,
            data: {client_id: value['client_id'], vat_no: value['client_vat_no']},             
            dataType: 'json',
            error: function (xhr, status, error) {
              selected_vat_details += value['client_vat_no'] + ',';             
            } ,
            success: function (result) {    
              if(result['cvr_client_details']!= null)              
                ajaxCounter['success']++;                            
              else
              {
                ajaxCounter['error']++;
                selected_vat_details += value['client_vat_no'] + ',';   
              }
            },
            complete: function (jqXHR, textStatus) {
              if (ajaxCounter.goal == (ajaxCounter['success'] + ajaxCounter['error'])) 
              {
                if (ajaxCounter['success'] > 0) 
                {                  
                  btn_refresh_cvr.html('<i class="bx bx-refresh fs-large me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Refresh CVR Datas</span>');
                  //falied_vatno = ajaxCounter.total - ajaxCounter.success;
                  falied_vatno = ajaxCounter.goal - ajaxCounter['success'];

                  Swal.fire({
                    icon: 'success',
                    title: 'Succeded!',
                    text:  ajaxCounter.success + ' CVR VAT No Updated Successfully!' + falied_vatno + selected_vat_details.replace(/,$/, '') +  ' not updated !!',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                  });
                }
                else
                {
                  btn_refresh_cvr.html('<i class="bx bx-refresh fs-large me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Refresh CVR Datas</span>');
                  //falied_vatno = ajaxCounter.total - ajaxCounter.error;
                  falied_vatno = ajaxCounter.goal - ajaxCounter['error'];

                  Swal.fire({
                      icon: 'error',
                      title: 'Cancelled!',
                      text:  ' CVR VAT No Not Updated For !!' + selected_vat_details.replace(/,$/, ''),
                      customClass: {
                      confirmButton: 'btn btn-error'
                    }
                  });
                }
              }
            }      
        });
        // end ajax
    });// end each 
    $('#selectClientVatNos').modal('hide');  
  });
  //End Select the VAT No's

  //Load Dropzone
  window.loadComplianceUploadDropzone = function loadComplianceUploadDropzone(element)
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
    
    var accepted_files = ".xls, .xlsx, .xml, .csv";
    
    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        previewTemplate: previewTemplate,
        //maxFilesize: 10,
        parallelUploads:1,
        //maxFiles: 1,
        uploadMultiple:false,       
        acceptedFiles: accepted_files,        
        timeout: 180000,       
        init : function() {
          
          var myDropzone = this;
          var dropzoneId = myDropzone.element.getAttribute('id');
                   
          // myDropzone.on("addedfile", function(file) {    
          // console.log("addedfile")  ;      
          //   myDropzone.processQueue();
          //   console.log("processQueue")  ;    
          // });         

          // myDropzone.on("complete", function(file) {
          //     console.log(file);
          // });

          // myDropzone.on("queuecomplete", function() {
          //     //myDropzone.options.autoProcessQueue = false;
          // });

          myDropzone.on("success", function (file, response) {    //multiple 
              
              if(response == "")   
              {           
                myDropzone.removeFile(file);   
                //$("#dropzone-" + id).addClass("dz-started");               
                Swal.fire({
                  title: 'Error!',
                  text: 'Cannot upload file.',
                  icon: 'error',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });          
              }
              else
              {                
                var status = response['message'];

                if(status == 'splitted')
                {
                  $("#compliance-upload .card-compliance-upload").hide();
                  $("#compliance-upload .progress.custom").show();

                  var existingFiles = [];

                  //myDropzone.options.autoProcessQueue = false;

                  $.get(baseUrl + 'splitted-files', function(result) 
                  {
                    
                    if(result['status'] == 'success')
                    {
                      //myDropzone.autoProcessQueue = false;
                      var files = result['files'];
                        
                      // files.forEach((file) => { 
                      //   existingFiles.push(
                      //       { name: file }
                      //   );
                      // }); 

                      var total_files = files.length;
                      var last_file = false;
                      for (var i = 0; i < files.length; i++) 
                      {
                        last_file = ((i == (files.length)-1) ? true : false);
                        //var mockFile = { name: files[i].name, size: files[i].size, status: Dropzone.QUEUED, accepted: true, upload: {} };
                        var mockFile = { name: files[i].name, last_file: last_file };

                        //myDropzone.options.addedfile.call(myDropzone, mockFile);
                        var processed_files = (i+1);
                        var x = readSplitCompliance(mockFile, total_files, processed_files);
                       
                        /*
                        $.ajax({
                           type: 'POST',
                           async: false,
                           url: `${baseUrl}compliance`,  
                           data: {file_type: 'split' , file: mockFile},          
                           success: function(data){  

                              console.log(data);
                                         
                              // calculate progress           
                              calculateComplianceDropzoneProgress(total_files, processed_files);
                              // var calc_progress_percentage = Math.round(100/total_files);    
                              
                              // var final_progress_percentage = 0;
                              // if(processed_files > 0)
                              //   final_progress_percentage = calc_progress_percentage * processed_files;

                              // if(final_progress_percentage > 100)
                              //   final_progress_percentage = 100;

                              // console.log($("#compliance-upload .progress .progress-bar").html());
                              // $("#compliance-upload .progress.custom .progress-bar").attr("aria-valuenow", final_progress_percentage + '%');
                              // $("#compliance-upload .progress .progress-bar").css("width", final_progress_percentage + '%');
                              // $("#compliance-upload .progress .progress-bar").html(final_progress_percentage + '%');
                              $("#compliance-upload .progress.custom").show();
                              // console.log($("#compliance-upload .progress .progress-bar").html());
                              // if(final_progress_percentage == 100)
                              // {      
                              //   $("#compliance-upload .progress").hide();
                              //   $("#compliance-upload .card-compliance-upload").show();
                              // }
                              // calculate progress

                              if(last_file)  
                              {
                                console.log("last_file: " + last_file);

                                $("#compliance-upload .card-compliance-upload").show();
                                $("#compliance-upload .progress.custom").hide();  

                                matched_user_datas = drawDtTable(response, 'compliance');
                                dt_matched_user.clear().rows.add(matched_user_datas).draw();          
                              }

                              console.log($("#compliance-upload .progress.custom").length);
                           },
                           error: function(jqXHR, textStatus, errorThrown){
                              console.log('error: ' + textStatus);
                           }
                         });
                        */
                        // //myDropzone.displayExistingFile(mockFile, "<?php echo $pathiit; ?>");

                        // myDropzone.emit("addedfile", mockFile);
                        // myDropzone.files.push(mockFile); 
                        //myDropzone.emit("thumbnail", existingFiles[i], "/image/url");                        
                        //myDropzone.emit("success", mockFile);
                        //myDropzone.emit("complete", mockFile); 
                        

                        // var mockFile = files[i];//{ name: val.file_name, size: val.file_size };   
                        // console.log(mockFile);             
                        // myDropzone.options.addedfile.call(myDropzone, mockFile);
                        
                        // //$(mockFile.previewElement).prop('id', val.id);
                        // $(mockFile.previewElement).addClass('dz-complete');  

                        // var mockFile = files[i];
                        // myDropzone.emit("addedfile", mockFile);
                        // //myDropzone.createThumbnailFromUrl(mockFile, fileUrl);
                        // myDropzone.emit("success", mockFile);
                        // myDropzone.emit("complete", mockFile);
                        // myDropzone.files.push(mockFile); 

                        // myDropzone.processQueue();
                      }   
                               
                      // console.log("process dz-started");   
                      // console.log(myDropzone.getQueuedFiles());       
                      // //myDropzone.processQueue();
                      // console.log("process end");
                      //myDropzone.files.push(mockFile);  
                    }
                  });

                  // loadFileNames('/public/splits')
                  // .then((data) => {
                  //     console.log(data);
                  // })
                  // .catch((error) => {
                  //     alert('Files could not be loaded. please check console for details');
                  //     console.error(error);
                  // });

                  // $.get("file://D:/wamp/www/digitalvat/public/splits",function(response){
                  //     document.write(response);
                  //     //getNames();
                  // });
                  // const fs = require('fs'); 
                  // const path = require('path'); 
                   
                  // const folderPath = 'D:\\wamp\\www\\digitalvat\\public\\splits'; 
                   
                  // fs.readdir(folderPath, (err, files) => { 
                  //   if (err) { 
                  //     console.error('Error reading folder:', err); 
                  //     return; 
                  //   } 
                   
                  //   files.forEach((file) => { 
                  //     console.log(file); 
                  //     //Add existing files into dropzone
                  //     existingFiles.push(
                  //         { name: file, size: 12345678 }
                  //     );
                  //   }); 
                  // }); 
                  // //Add existing files into dropzone
                  // var existingFiles = [
                  //     { name: "Filename 1.pdf", size: 12345678 },
                  //     { name: "Filename 2.pdf", size: 12345678 },
                  //     { name: "Filename 3.pdf", size: 12345678 },
                  //     { name: "Filename 4.pdf", size: 12345678 },
                  //     { name: "Filename 5.pdf", size: 12345678 }
                  // ];

                  // for (i = 0; i < existingFiles.length; i++) {
                  //     myDropzone.emit("addedfile", existingFiles[i]);
                  //     //myDropzone.emit("thumbnail", existingFiles[i], "/image/url");
                  //     myDropzone.emit("complete", existingFiles[i]);                
                  // }
                }
                else
                {
                  matched_user_datas = drawDtTable(response, 'compliance');
                  dt_matched_user.clear().rows.add(matched_user_datas).draw();
                }  
                // var modalId = '#UploadModal-compliance';                
                // $(modalId).modal('hide');
                // $(document.body).removeClass("modal-open");
                // $(document.body).removeAttr("style");
                // $(".modal-backdrop").remove();   

                // Swal.fire({
                //   icon: 'success',
                //   title: `Successfully ${status}!`,
                //   text: `Compliance ${status} Successfully.`,
                //   customClass: {
                //     confirmButton: 'btn btn-success'
                //   }
                // });                             
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
              //console.log("errorrrrrrrrrrrrr");    
              //console.log(file); 
              console.log(errorMessage);
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
          // $(".notification .no_org_no ul li:contains("+ file.name +")").remove();
          // if($(".notification .no_org_no ul li").length == 0)
          //   $(".notification .no_org_no").remove();

          // $(".notification .no_folder ul li:contains("+ file.name +")").remove();
          // if($(".notification .no_folder ul li").length == 0)
          //   $(".notification .no_folder").remove();
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
                
                // $(".notification .success ul li:contains("+ file.name +")").remove();

                // if($(".notification .success ul li").length == 0)
                //   $(".notification .success").remove();
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
 
  function readSplitCompliance(mockFile, total_files, processed_files) {    
    return new Promise((resolve, reject) => {
      try {            
        $.ajax({
            type: 'POST',
            url: `${baseUrl}compliance`,
            data: {file_type: 'split' , file: mockFile},
            success: function (response) {
              calculateComplianceDropzoneProgress(total_files, processed_files);
              
              if(mockFile['last_file'])  
              {                      
                $("#compliance-upload .card-compliance-upload").show();
                $("#compliance-upload .progress.custom").hide();  

                matched_user_datas = drawDtTable(response, 'compliance');
                dt_matched_user.clear().rows.add(matched_user_datas).draw();          
              }

              return resolve(mockFile);
            }
        });
      } catch (ex) {
        return reject(new Error(ex));
      }
    });
  }

  function loadFileNames(dir) {
    return new Promise((resolve, reject) => {
        try {
            var fileNames = new Array();
            $.ajax({
                url: dir,
                success: function (data) {
                    for(var i = 1; i < $(data).find('li span.name').length; i++){
                        var elem = $(data).find('li span.name')[i];
                        fileNames.push(elem.innerHTML);
                    }
                    return resolve(fileNames);
                }
            });
        } catch (ex) {
            return reject(new Error(ex));
        }
    });
  }

  loadComplianceUploadDropzone($("#dropzone-compliance"));  
});