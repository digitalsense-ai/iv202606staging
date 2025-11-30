/**
 * VAT Check File Upload
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
      dt_vatcheck_table = $('.datatables-vatcheck'),
      dt_unmatched_invoice_table = $('.datatables-unmatched-invoice'),
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

  // UnMatched Invoices datatable
  if (dt_unmatched_invoice_table.length) {
    var dt_unmatched_invoice = dt_unmatched_invoice_table.DataTable({
      data: unmatched_invoice_datas,        
      processing: true,   
      autoWidth: false, 
      columns: [
        // columns according to JSON       
        { data: 'id' },
        { data: 'invoice_date' },                        
        { data: 'invoice_no' },
        { data: 'currency_code' },
        { data: 'total_net', className: "text-end" },   
        { data: 'vat_rate', className: "text-end" },   
        { data: 'total_vat', className: "text-end" },     
        { data: 'total_gross', className: "text-end" }
      ],
      lengthMenu: [
          [10, 25, 50, 100, -1],
          [10, 25, 50, 100, 'All']
      ],
      pageLength: -1,
      columnDefs: [
        {
          // For Checkboxes
          targets: 0,              
          searchable: false,
          orderable: false,              
          render: function (data, type, full, meta) {                
            return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'">';
          },
          checkboxes: {
            selectRow: true,
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        }
      ],
      order: [[1, 'desc']],
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
              // messageTop: function() { 
              //   return moment().format('DD-MM-YYYY');                
              // },
              // messageBottom: function() { 
              //   return dtFooterNote('print');                
              // },
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8],
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
              // messageTop: function() { 
              //   return moment().format('DD-MM-YYYY');                
              // },
              // messageBottom: function() { 
              //   return dtFooterNote('excel');                
              // },
              customize: function (xlsx) {
                  // more code
                  let sheet = xlsx.xl.worksheets['sheet1.xml'];
                  // set height for all col
                  //$('row, rels').attr('ss:Height', '200');
                  $('row', sheet).last().attr('ht', '150').attr('customHeight', "1");
                  // more code
              },
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8],
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
              pageSize: 'A4',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              customize : function(doc) {                            
                doc.content.splice(1, 0, {
                    text: moment().format('DD-MM-YYYY') + "\n",
                    alignment: 'right', // Right align the date
                    fontSize: 12, // Set the desired font size for the date
                    margin: [0, 10, 0, 0] // Optional: Add some margin
                });
               
                doc.content[2].table.widths = ['10%','10%','10%','10%','10%','10%','5%','5%','5%','25%'];                
              },
              //footer: true,                         
              // messageBottom: function() { 
              //   return dtFooterNote();                
              // },
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8],                
              }
            }            
          ]
        },
        {
          text: '<i class="bx bx-check-square me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Select Template</span>',
          // text: '<div class="btn-group">' +
          //         '<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Select Template</button>' +
          //         '<ul class="dropdown-menu">' +
          //           //'<li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#uploadSingleModal-vatcheck-"' + $("#vat_reg_id").val() + '-0">Action</a></li>' +
          //           '<li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#excelTemplateSelectionModal-276">Another action</a></li>' +
          //           '<li><a class="dropdown-item disabled" href="javascript:void(0);">Something else here</a></li>' +
          //           '<li>' +
          //             '<hr class="dropdown-divider">' +
          //           '</li>' +
          //           '<li><a class="dropdown-item" href="javascript:void(0);">Separated link</a></li>' +
          //         '</ul>' +
          //       '</div>',
          className: 'btn btn-primary me-3',
          // attr: {
          //   'data-bs-toggle': 'modal',
          //   'data-bs-target': '#uploadSingleModal-vatcheck-'+ $("#vat_reg_id").val() +'-0'
          // }
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#excelTemplateSelectionModal-'+ $("#vat_reg_id").val()
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
      initComplete: function () {console.log("inititated");
        $(".sk-bounce").hide();
        $("#unmatched-invoice-card").show();         
      }
    });
  }

  /*
  //Load Dropzone
  window.loadVATCheckUploadDropzone = function loadVATCheckUploadDropzone(element)
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
    
    var accepted_files = ".xls, .xlsx";
    
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

          myDropzone.on("success", function (file, response) {
              
              if(response == "")   
              {           
                myDropzone.removeFile(file);              
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
                
                unmatched_invoice_datas = drawDtTable(response, 'vatcheck');
                dt_unmatched_user.clear().rows.add(unmatched_invoice_datas).draw();
              } 
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {              
              console.log(errorMessage);
          });                
        },
        addRemoveLinks: true,
        removedfile: function(file) {           
          var file_type = file.previewElement.getAttribute('file_type');
          var file_type_title = file.previewElement.getAttribute('file_type_title');
          var file_id = file.previewElement.getAttribute('id');      
         
         if(file_id == null)
         {                    
         }
         else
         {          
           $.ajax({
             type: 'DELETE',
             url: `${fileUrl}${file_id}`,  
             data: {file_type: file_type, file_type_title: file_type_title},          
             success: function(data){     
                                      
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
 
  loadVATCheckUploadDropzone($("#dropzone-vatcheck"));  
  */
});