/**
 * Excel Column Template NEW
 */

'use strict';
//Dropzone.autoDiscover = false;

$(function () {

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
  var dt_excelcolumntemplate_table = $('.datatables-excelcolumntemplate'),
      excelColumnTemplateUrl = baseUrl + 'excel-column-templates/'; 

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 

  // Excel Column templates datatable
  if (dt_excelcolumntemplate_table.length) {
    var dt_excelcolumntemplate = dt_excelcolumntemplate_table.DataTable({
      data: excelcolumntemplate_datas,        
      processing: true,  
      autoWidth: false,   
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'name' },
        { data: 'version' },
        { data: 'columns' },
        { data: 'action' }      
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
          // Title
          targets: 1,          
          width: "20%",   
          render: function (data, type, full, meta) {
            var $title = full['name'];
            
            return '<span>' + $title + '</span>';
          }
        },
        {
          // Version
          targets: 2,          
          width: "5%",   
          render: function (data, type, full, meta) {
            var $version = full['version'];
            
            return '<span>' + $version + '</span>';
          }
        },  
        // {
        //   // Columns
        //   targets: 3,          
        //   width: "30%",   
        //   render: function (data, type, full, meta) {
        //     var $sheets = full['columns'];
            
        //     var parsed_sheets = JSON.parse($sheets);
            
        //     var htmlColumns = '<table class="table">';   
        //     $.each(parsed_sheets, function (idxsheet, sheet) {             
        //       htmlColumns += '<tr><td colspan="3"><h5 class="m-0 mx-n3">Sheet '+ (idxsheet+1) + ' :</h5></td>';
              
        //       var file_count = Object.entries(sheet).length;
        //       $.each(sheet, function (idxsheetfile, file) {
        //         if(file_count > 1)
        //           htmlColumns += '<tr><td colspan="3"><h6 class="m-0 mx-n1">File ' + (parseInt(idxsheetfile)+1) + ' - ' + file['sheet_name'] + ' :</h6></td>';
        //         else
        //           htmlColumns += '<tr><td colspan="3"><h6 class="m-0 mx-n1">'+ file['sheet_name'] + ' :</h6></td>';

        //         var parsed_columns = file['columns'];                    
        //         $.each(parsed_columns, function (idx, item) { 
        //           var column_mapping = item.columnmapping.split(':');
                 
        //           var row_class = '';
        //           if(idx == (parsed_columns.length-1))
        //             row_class = 'class="border-0"';

        //           if(item.remarks == null || item.remarks == '-')
        //             htmlColumns += '<tr><td '+ row_class +'>' + item.column + ':</td><td '+ row_class +'>' + column_mapping[1] + '</td><td '+ row_class +'></td>';                
        //           else
        //             htmlColumns += '<tr><td '+ row_class +'>' + item.column + ':</td><td '+ row_class +'>' + column_mapping[1] + '</td><td '+ row_class +'>' + item.remarks + '%</td>';
        //         });                
        //       });  
        //     });  
        //     return htmlColumns + '</table>';
        //   }
        // },   
        {
          // Columns
          targets: 3,          
          width: "30%",   
          render: function (data, type, full, meta) {
            var $files = full['columns'];
            
            var parsed_files = JSON.parse($files);
           
            var htmlColumns = '<table class="table">';   
            $.each(parsed_files, function (idxfile, file) { 
              var file_no = idxfile + 1;
              htmlColumns += '<tr><td colspan="3"><h5 class="m-0 mx-n5">File '+ file_no + ' :</h5></td>';
              
              var sheet_count = Object.entries(file).length;
              $.each(file, function (idxsheet, sheet) {
                var sheet_tab_no = idxsheet + 1;

                //if(sheet_count > 1)
                  htmlColumns += '<tr>' +
                                    '<td colspan="3">' + 
                                      '<h6 class="m-0 mx-n3">Sheet ' + sheet_tab_no + ' - ' + sheet['sheet_name'] + ':<br>' + 
                                        '<span class="fs-tiny fw-normal">' +
                                          //'(<br>' + 
                                            'Header Row: '  + sheet['header_row'] + '<br>' +
                                            'Calculation: '  + capitalizeFirstLetter(sheet['calc_type']) + '<br>' +
                                          //')' +
                                          '</span>' + 
                                      ' </h6>' +
                                      '</td>'
                                  ;
                //else
                  //htmlColumns += '<tr><td colspan="3"><h6 class="m-0 mx-n1">'+ sheet['sheet_name'] + ' :</h6></td>';

                var parsed_columns = sheet['columns'];
                $.each(parsed_columns, function (idx, item) { 
                  var column_mapping = item.columnmapping.split(':');
                 
                  var row_class = '';
                  if(idx == (parsed_columns.length-1))
                    row_class = 'class="border-0"';

                  var special_rows = '';
                  if(item.special)
                  {
                    if(item.special.prefix == null && item.special.column_1 == null && item.special.arithmetic == null && 
                      item.special.column_2 == null && item.special.columnmapping == null)
                    {

                    }
                    else
                    {
                      var special_prefix = (item.special.prefix == 1) ? '(Reverse) ' : '';
                      var special_column_mapping = item.special.columnmapping.split(':');

                      special_rows = '<span><u>Special Row</u>: ' +   
                                        special_prefix + item.special.column_1 +
                                        item.special.arithmetic + item.special.column_2 + ' = ' + 
                                        special_column_mapping[1]                                        
                                      '</span>';
                    }
                  }

                  if(item.remarks == null || item.remarks == '-')
                    htmlColumns += '<tr><td '+ row_class +'>' + item.column + ':</td><td '+ row_class +'>' + column_mapping[1] + '</td><td '+ row_class +'></td>';                
                  else
                    htmlColumns += '<tr><td '+ row_class +'>' + item.column + ':</td><td '+ row_class +'>' + column_mapping[1] + '</td><td '+ row_class +'>' + item.remarks + '%</td>';

                  if(special_rows != '')
                    htmlColumns += '<tr><td colspan="3" class="pt-0">' + special_rows + '</td></tr>';
                  
                });                
              });  
            });  
            return htmlColumns + '</table>';
          }
        },         
        {
          // Actions
          targets: -1,        
          width: "15%",
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            var buttons = "";

            var vatreg = full['vatreg'].length;

            if(vatreg == 0)
            {
              buttons +=  '<button class="btn btn-sm btn-icon edit-excel-template" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#excelColumnTemplateModal" title="Edit"><i class="bx bx-edit"></i></button>' +
                          '<button class="btn btn-sm btn-icon delete-excel-template" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';
            }
            else
              buttons +=  '<button class="btn btn-sm btn-icon edit-excel-template" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#excelColumnTemplateModal" title="Clone"><i class="bx bx-copy-alt"></i></button>';                          
             
            return (             
              buttons              
            );
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
              exportOptions: {
                columns: [1, 2, 3],
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
              extend: 'csv',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3],
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
                columns: [1, 2, 3],
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
                columns: [1, 2, 3],
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
                columns: [1, 2, 3],
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
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Create Excel Template</span>',
          className: 'add-new-excel-template btn btn-primary',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#excelColumnTemplateModal'
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['title'];
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
        $("#excelcolumntemplate-card").show();        
      }
    });
  }  

  function validateTemplate()
  {
    var fromControl = $('#formRepeater').find('.form-control, .form-select');

    var _valid = true;
    fromControl.each(function (i) {                 
      if($(this).val() == "")
      {
        if($(this).hasClass('remarks') || $(this).hasClass('special'))
        {
          if($(this).parent().is(':visible') && $(this).val() == "")
            _valid = false;
        }
        else       
          _valid = false;
      }
    });

    if(_valid)
    {
      $(".btn-save-template").removeClass('disabled');
      $(".btn-save-template").removeClass('btn-danger');
      $(".btn-save-template").addClass('btn-success');

      $(".btn-save-template").removeAttr("disabled");      
    }
    else
    {
      $(".btn-save-template").addClass('disabled');
      $(".btn-save-template").addClass('btn-danger');
      $(".btn-save-template").removeClass('btn-success');

      $(".btn-save-template").attr("disabled", "disabled");
    }
  }

  $(document).on('change keyup paste', '.form-repeater .form-control, .form-repeater .form-select', function () { 
    validateTemplate();
  });

  function showRemarks(element, value = '')
  {
    element.parent('div').next().find('.form-control.remarks').val('');
    element.parent('div').next().hide();
    
    if(element.val() == 'F:vat_rate' || element.val() == 'G:total_vat_invoice_currency')
    { 
      element.parent('div').next().find('.form-control.remarks').val(value);        
      element.parent('div').next().show(); 
    }
  }

  $(document).on('change', '.system-excel-column:not(.special)', function () { 
    showRemarks($(this));
    // $(this).parent('div').next().hide();
    
    // if($(this).val() == 'F:vat_rate' || $(this).val() == 'G:total_vat_invoice_currency')
    // { 
    //   $(this).parent('div').next().find('.form-control.remarks').val('');        
    //   $(this).parent('div').next().show(); 
    // }
  });

  function clearItems()
  {
    $('#template_id').val('');
    $('#edit_type').val('');
    
    $("#template_name").val('');    
    
    $("#no_of_files").val(1);
    
    var newFileCreate = newFile(0);
    
    $('#modalLabel').html('Add Excel Column Template');

    $(".btn-save-template").addClass('disabled');
    $(".btn-save-template").addClass('btn-danger');
    $(".btn-save-template").removeClass('btn-success');
    $(".btn-save-template").attr("disabled", "disabled");     

    $("#column_mapping .sk-bounce").show();
    $("#column_mapping .card-column-mapping").hide();  
  }

  // add excel-template
  $('.add-new-excel-template').on('click', function () {    
    clearItems();

    $("#column_mapping .sk-bounce").hide();
    $("#column_mapping .card-column-mapping").show();   
  });

  //No. of Files
  $(document).on('change paste', '.no-of-files', function () {
    var no_of_files = $(this).val();
    
    loadFileRepeater(no_of_files);
  });

  //Load File Repeater
  function loadFileRepeater(no_of_files, files = null)
  {       
    if(no_of_files >= 1)
    {     
      var exist_file_repeaters = $('.file-repeater').length;
     
      if(exist_file_repeaters <= no_of_files)
      {  
        for(var file_no = exist_file_repeaters; file_no < no_of_files; file_no++)
        {         
          var new_file_no = file_no;
         
          var newFileCreate = newFile(new_file_no, files);
        }        
      }
      else if(exist_file_repeaters > no_of_files)
      {        
        for(var repeater = (exist_file_repeaters - 1); repeater >= no_of_files; repeater--)                  
          $('#file-repeater-' + repeater).remove();
      }
    }
  }

  //Load New File
  function newFile(new_file_no, files = null) {    
    return new Promise((resolve, reject) => {
      try {   
        $.ajax({
          type: 'GET',       
          url: `${excelColumnTemplateUrl}filenew/`+new_file_no, 
          data: {file_no: new_file_no},       
          success: function(data){           
            var file_modal = data['file_modal'];

            if(new_file_no == 0)
            {
              $('.file-repeater').remove();
              $(file_modal).insertBefore($('#formRepeater div:last-child.text-end'));
            }
            else
              $(file_modal).insertAfter($('#file-repeater-' + (new_file_no - 1) ));
           
            var worksheetRowRepeater = $('#navs-worksheet-tab-' + new_file_no + '-0 .worksheet-row-repeater');

            loadExcelColumnTemplateRowRepeater(worksheetRowRepeater);             
             //console.log("start updatec controls");
            //updateControlsId(worksheetRowRepeater);
            reorderControls(worksheetRowRepeater);
              // console.log("end updatec controls");

            if(files)
            {              
              var file_no = new_file_no;            

              $.each(files[new_file_no], function (sheet_tab_no, sheet_tab) {               
                if(sheet_tab_no == 0)
                {
                  $('#sheet_name_'+ file_no +'_'+ sheet_tab_no).val(sheet_tab['sheet_name']);
                  $('#header_row_'+ file_no +'_'+ sheet_tab_no).val(sheet_tab['header_row']);
                  $('#calc_type_'+ file_no +'_'+ sheet_tab_no).val(sheet_tab['calc_type']);

                  $.each(sheet_tab['columns'], function (row_no, row) {  
                    if(row_no == 0)
                    {          
                      $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_column').val('');
                      $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_columnmapping').val('');
                      $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_remarks').val('');

                      $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_column').val(row['column']);
                      $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_columnmapping').val(row['columnmapping']);
                      $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_remarks').val(row['remarks']);

                      var has_special = 0;
                      if(row['special']['prefix'])
                      {
                        has_special++;
                        $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special_prefix').val(row['special']['prefix']);
                      }

                      if(row['special']['column_1'])
                      {
                        has_special++;
                        $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special_column_1').val(row['special']['column_1']);
                      }

                      if(row['special']['arithmetic'])
                      {
                        has_special++;  
                        $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special_arithmetic').val(row['special']['arithmetic']);
                      }

                      if(row['special']['column_2'])
                      {
                        has_special++;  
                        $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special_column_2').val(row['special']['column_2']);
                      }

                      if(row['special']['columnmapping'])
                      {
                        has_special++;  
                        $('#template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special_columnmapping').val(row['special']['columnmapping']);                      
                      }

                      if(has_special == 5)
                        $('#btn_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special').hide();
                      else
                        $('#btn_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special').show();
                    }
                    else
                    {
                      var data = {
                        'file_no' : file_no, 
                        'sheet_tab_no' : sheet_tab_no,
                        'row_no' : row_no,
                        'column' : row['column'],
                        'columnmapping' : row['columnmapping'],
                        'remarks' : row['remarks'],
                        'special' : row['special']
                      };      
                      
                      console.log("--- START: Add New Row ---" + row_no);
                      var newrow = addNewRow(data); 
                    }
                  });
                }
                else
                {                  
                  var data = {
                    'file_no' : file_no, 
                    'sheet_tab_no' : sheet_tab_no - 1,
                    'sheet_name' : sheet_tab['sheet_name'],
                    'header_row' : sheet_tab['header_row'],
                    'calc_type' : sheet_tab['calc_type'],

                    'sheet_tab_columns' : sheet_tab['columns']
                  };            
                  var newtab = addNewTab(data);                
                }
              }); 
            } 

            return resolve(new_file_no);                
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log('error: ' + textStatus);
          }
        });    
      } catch (ex) {
        return reject(new Error(ex));
      }
    });
  }

  function addNewRow(data = null)
  {     
    return new Promise((resolve, reject) => {
      try {            
        var new_data = data;

        var file_no = (data) ? data['file_no'] : 0;
        var sheet_tab_no = (data) ? data['sheet_tab_no'] : 0;
        var row_no = (data) ? data['row_no'] : 0;

        var column = (data) ? data['column'] : '';
        var columnmapping = (data) ? data['columnmapping'] : '';
        var remarks = (data) ? data['remarks'] : '';
        var special = (data) ? data['special'] : '';

        var row_count = $('#worksheet-row-repeater-'+ file_no + '-' + sheet_tab_no +' div[data-repeater-item]').length; 

        var new_row_no = (row_no) ? row_no : row_count;
        
        $.ajax({
          type: 'GET',       
          url: `${excelColumnTemplateUrl}rownew/`+new_row_no, 
          data: {file_no: file_no, sheet_tab_no: sheet_tab_no, column: column, columnmapping: columnmapping, remarks: remarks, special: special},       
          success: function(data) {
            var row_modal = data['row_modal'];
                            
            $('#worksheet-row-repeater-'+ file_no + '-' + sheet_tab_no +' div[data-repeater-list]').append(row_modal);

            
            //var worksheetRowRepeater = $('#navs-worksheet-tab-' + file_no + '-' + sheet_tab_no + ' .worksheet-row-repeater')

            //   loadExcelColumnTemplateRowRepeater(worksheetRowRepeater);
            //updateControlsId(worksheetRowRepeater);
            
            console.log("--- END: Add New Row ---" + new_row_no);
            return resolve(new_data);      
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.log('error: ' + textStatus);
          }
        });
      } catch (ex) {
        return reject(new Error(ex));
      }
    });    
  }

  //Load Row Repeater
  window.loadExcelColumnTemplateRowRepeater = function loadExcelColumnTemplateRowRepeater(element)
  {    
    var worksheetRowRepeater = element;
    if (worksheetRowRepeater.length) {          
      worksheetRowRepeater.on('submit', function (e) {
        e.preventDefault();
      });
      worksheetRowRepeater.repeater({
        //initEmpty: true,
        show: function () { 
          console.log("add clicked"); 
          //updateControlsId(worksheetRowRepeater);
          reorderControls(worksheetRowRepeater);
          validateTemplate();//element
          $(this).slideDown();          
        },
        hide: function (e) {
          console.log("delete clicked"); 
          var element = $(this);
                
          Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete the row!",
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
              element.slideUp(400, function() {
                element.remove();
                //updateControlsId(worksheetRowRepeater);
                reorderControls(worksheetRowRepeater);
                validateTemplate();
              });              
              //element.slideUp(e);
              
              //updateControlsId(worksheetRowRepeater); 
              //validateTemplate();                       
            }
            else if (result.dismiss === Swal.DismissReason.cancel) {
              Swal.fire({
                title: 'Cancelled',
                text: 'The row is not deleted!',
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          });
        }
      });
    }
  };  

  //Update Control Ids
  function updateControlsId(element)
  {  //console.log(element);
    var fromControl = element.find('.form-control, .form-select');
    var formLabel = element.find('.form-label'); 

    var formRepeaterItem = element.find('div[data-repeater-list] div[data-repeater-item]');
//console.log(formRepeaterItem);
//console.log(formRepeaterItem.length);
 
    fromControl.each(function (i) {      
        var name = $(this).attr('name');
        var data_name = $(this).attr('data-name');
        var new_data_name = (($(fromControl[i]).hasClass('special')) ? 'special_' : '') + data_name;
        
        var suffix = name.match(/\d+/g);
        var file_no = (suffix[0]) ? suffix[0] : 0;
        var sheet_tab_no = (suffix[1]) ? suffix[1] : 0;
        var row_no = (suffix[2]) ? suffix[2] : 0;

        var control_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_' + new_data_name;
        if($(fromControl[i]).hasClass('common-header'))
          control_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_' + new_data_name;
        
        $(fromControl[i]).attr('id', control_id);     
        $(formLabel[i]).attr('for', control_id);        

        if($(fromControl[i]).hasClass('system-excel-column'))
        {
          if($(fromControl[i]).hasClass('special'))
          {

          }
          else
          {
            //var remarks_value = $('#' + control_id);
            //showRemarks($('#' + control_id));
          }
        }

        //Special Controls
        if($(fromControl[i]).hasClass('special'))
        {        
          var control_name = 'template_columns['+ file_no +']['+ sheet_tab_no +'][columns]['+ row_no +'][special]['+ data_name +']';        
          
          $(fromControl[i]).attr('name', '');
          
          $(fromControl[i]).attr('name', control_name);             
        }

        //Special Button        
        var formSpecialButton = $(fromControl[i]).closest('div.row').find('.btn-special');      
        var btn_id = 'btn_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_special';
        $(formSpecialButton).attr('id', btn_id);
        $(formSpecialButton).attr('data-file_no', file_no);
        $(formSpecialButton).attr('data-sheet_tab_no', sheet_tab_no);
        $(formSpecialButton).attr('data-row_no', row_no);
        
        //Special Div 
        var formSpecialDiv = $(fromControl[i]).closest('div.row').find('.special-row'); 
        var div_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_special_rows';
        $(formSpecialDiv).attr('id', div_id);

        //Special Delete Button
        var formSpecialDeleteButton = $(fromControl[i]).closest('div.row').find('.btn-delete-special');
        $(formSpecialDeleteButton).attr('data-file_no', file_no);
        $(formSpecialDeleteButton).attr('data-sheet_tab_no', sheet_tab_no);
        $(formSpecialDeleteButton).attr('data-row_no', row_no);
    });   
  }

  function reorderControls(element)
  { 
    var fromdivControl = element.find('div[data-repeater-item');
     
      fromdivControl.each(function (index) { 
     var row_no = (index);  
          var name = $(this).find("select:eq(0)").attr('name'); 
         
          var data_name = $(this).find("select:eq(0)").attr('data-name');
          var new_data_name = (($(this).find("select:eq(0)").hasClass('special')) ? 'special_' : '') + data_name;
          
          var suffix = name.match(/\d+/g); 
          var file_no = (suffix[0]) ? suffix[0] : 0;
          var sheet_tab_no = (suffix[1]) ? suffix[1] : 0;

          var control_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_' + new_data_name; 
          var control_name = 'template_columns['+ file_no +']['+ sheet_tab_no +'][columns]['+ row_no +']['+ data_name +']';   

          var system_control_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_columnmapping'; 
          var system_control_name = 'template_columns['+ file_no +']['+ sheet_tab_no +'][columns]['+ row_no +'][columnmapping]';

          var remarks_control_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_remarks'; 
          var remarks_control_name = 'template_columns['+ file_no +']['+ sheet_tab_no +'][columns]['+ row_no +'][remarks]';


          if($(this).find("select:eq(0)").hasClass('common-header'))
            control_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_' + new_data_name;
          //column
          $(this).find("select:eq(0)").attr('id', control_id);             
          $(this).find("select:eq(0)").prev('label').attr('for', control_id);
          $(this).find("select:eq(0)").attr('name', control_name);

          //system-excel-column
          $(this).find('.system-excel-column:eq(0)').attr('id', system_control_id);             
          $(this).find('.system-excel-column:eq(0)').prev('label').attr('for', system_control_id);
          $(this).find('.system-excel-column:eq(0)').attr('name', system_control_name);

          //remarks
          $(this).find('.remarks:eq(0)').attr('id', remarks_control_id);            
          $(this).find('.remarks:eq(0)').prev('label').attr('for', remarks_control_id);
          $(this).find('.remarks:eq(0)').attr('name', remarks_control_name);
         

           //Special Controls
          if($(this).find("select:eq(0)").hasClass('special'))
          {        
             control_name = 'template_columns['+ file_no +']['+ sheet_tab_no +'][columns]['+ row_no +'][special]['+ data_name +']';        
            
            $(this).find("select:eq(0)").attr('name', '');
            
            $(this).find("select:eq(0)").attr('name', control_name);             
          }

          //Special Button        
          var formSpecialButton = $(this).find("select:eq(0)").closest('div.row').find('.btn-special');      
          var btn_id = 'btn_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_special';
          $(formSpecialButton).attr('id', btn_id);
          $(formSpecialButton).attr('data-file_no', file_no);
          $(formSpecialButton).attr('data-sheet_tab_no', sheet_tab_no);
          $(formSpecialButton).attr('data-row_no', row_no);
          
          //Special Div 
          var formSpecialDiv = $(this).find("select:eq(0)").closest('div.row').find('.special-row'); 
          var div_id = 'template_columns_'+ file_no + '_' + sheet_tab_no + '_columns_' + row_no + '_special_rows';
          $(formSpecialDiv).attr('id', div_id);

           // Special Row Controls
          
            var formSpecialPrefix = $('#' + div_id +' .row .input-group').find('select:eq(0)'); 
            var formSpecialColumn = $(formSpecialPrefix).next(); 
          
            var formSpecialArthColumn = $('#' + div_id +' .row ').find('select:eq(2)');  
            var formSpecialColumn2 = $('#' + div_id +' .row ').find('select:eq(3)');  
            var formSpecialColumnMap = $('#' + div_id +' .row ').find('select:eq(4)');
          
           
            var prefix_control_id = 'template_columns_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no ;         
            var prefix_control_name = 'template_columns['+ file_no +']['+ sheet_tab_no +'][columns]['+ row_no +']';  
            if(formSpecialPrefix.hasClass('special prefix'))  
            {           
               $(formSpecialPrefix).attr('id', prefix_control_id +'_special_prefix');
               $(formSpecialPrefix).parent().prev('label').attr('for', prefix_control_id +'_special_column_1');
               $(formSpecialPrefix).attr('name', prefix_control_name +'[special][prefix]');
            }  
            if(formSpecialColumn.hasClass('special columnname-1'))
            {           
               $(formSpecialColumn).attr('id', prefix_control_id +'_special_column_1');             
               $(formSpecialColumn).attr('name', prefix_control_name +'[special][column_1]');
            }
            if(formSpecialArthColumn.hasClass('special arithmetic'))
            {            
               $(formSpecialArthColumn).attr('id', prefix_control_id +'_special_arithmetic'); 
               $(formSpecialArthColumn).prev('label').attr('for', prefix_control_id +'_special_arithmetic');            
               $(formSpecialArthColumn).attr('name', prefix_control_name +'[special][arithmetic]');
            }
            if(formSpecialColumn2.hasClass('special columnname-2'))
            {           
               $(formSpecialColumn2).attr('id', prefix_control_id +'_special_column_2');  
               $(formSpecialColumn2).prev('label').attr('for', prefix_control_id +'_special_column_2');           
               $(formSpecialColumn2).attr('name', prefix_control_name +'[special][column_2]');
            }
            if(formSpecialColumnMap.hasClass('special system-excel-column'))
            {          
               $(formSpecialColumnMap).attr('id', prefix_control_id +'_special_columnmapping');  
               $(formSpecialColumnMap).prev('label').attr('for', prefix_control_id +'_special_columnmapping');           
               $(formSpecialColumnMap).attr('name', prefix_control_name +'[special][columnmapping]');
            }          
          // Special Row Controls

          //Special Delete Button
          var formSpecialDeleteButton = $(this).find("select:eq(0)").closest('div.row').find('.btn-delete-special');
          $(formSpecialDeleteButton).attr('data-file_no', file_no);
          $(formSpecialDeleteButton).attr('data-sheet_tab_no', sheet_tab_no);
          $(formSpecialDeleteButton).attr('data-row_no', row_no);
    });  
  }

  //Load Row Repeater
  $(".worksheet-row-repeater").each(function () {
    loadExcelColumnTemplateRowRepeater($(this));
     //console.log("start updatec controls");     
    //updateControlsId($(this));
    reorderControls($(this));
    //console.log("end updatec controls");
  });

  //Add New Tab 
  $(document).on('click', '.btn-add-worksheet-tab', function () { 
    var data = $(this).data();    
    addNewTab(data);
  });

  function addNewTab(data = null)
  { 
    return new Promise((resolve, reject) => {
      try {   
        var new_data = data;

        var file_no = (data) ? data['file_no'] : 0;
        var sheet_tab_no = (data) ? data['sheet_tab_no'] : 0;

        var sheet_name = (data) ? data['sheet_name'] : '';
        var header_row = (data) ? data['header_row'] : '';
        var calc_type = (data) ? data['calc_type'] : '';

        var sheet_tab_columns = (data) ? data['sheet_tab_columns'] : null;

        var li_count = $('#worksheet-tab-'+ file_no + '-' + sheet_tab_no +' ul.nav-tabs li:not(.add-worksheet-tab)').length; 

        var new_sheet_no = li_count;// + 1;
        
        if(new_sheet_no >= 5)
        {
          Swal.fire({
            title: 'Warning!',
            text: 'You cannot add more than 5 sheets',
            icon: 'warning',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
        else
        { 
          $.ajax({
            type: 'GET',       
            url: `${excelColumnTemplateUrl}sheetnew/`+new_sheet_no, 
            data: {file_no: file_no, sheet_name: sheet_name, header_row: header_row, calc_type: calc_type},       
            success: function(data) {
              var worksheet_tab_li_modal = data['worksheet_tab_li_modal'];
              var worksheet_tab_content_modal = data['worksheet_tab_content_modal'];
                        
              $(worksheet_tab_li_modal).insertBefore('#worksheet-tab-'+ file_no + '-' + sheet_tab_no +' ul.nav-tabs li.add-worksheet-tab'); 
             
              $('#worksheet-tab-'+ file_no + '-' + sheet_tab_no +' .tab-content').append(worksheet_tab_content_modal);
             
              var worksheetRowRepeater = $('#navs-worksheet-tab-' + file_no + '-' + new_sheet_no + ' .worksheet-row-repeater')

              loadExcelColumnTemplateRowRepeater(worksheetRowRepeater);
               //console.log("start updatec controls");
              //updateControlsId(worksheetRowRepeater);
              reorderControls(worksheetRowRepeater);
               //console.log("end updatec controls");
              if(sheet_tab_columns)
              {                
                $.each(sheet_tab_columns, function (row_no, row) {  
                  if(row_no == 0)
                  {                                
                    $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_column').val(row['column']);
                    $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_columnmapping').val(row['columnmapping']);
                    $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_remarks').val(row['remarks']);

                    var has_special = 0;                    
                    if(row['special']['prefix'])
                    {
                      has_special++;  
                      $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_special_prefix').val(row['special']['prefix']);
                    }

                    if(row['special']['column_1'])
                    {
                      has_special++;  
                      $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_special_column_1').val(row['special']['column_1']);
                    }

                    if(row['special']['arithmetic'])
                    {
                      has_special++;  
                      $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_special_arithmetic').val(row['special']['arithmetic']);
                    }

                    if(row['special']['column_2'])
                    {
                      has_special++;  
                      $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_special_column_2').val(row['special']['column_2']);
                    }

                    if(row['special']['columnmapping'])
                    {
                      has_special++;  
                      $('#template_columns_'+ file_no +'_'+ new_sheet_no +'_columns_'+ row_no +'_special_columnmapping').val(row['special']['columnmapping']);                   
                    }

                    if(has_special == 5)
                      $('#btn_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special').hide();
                    else
                      $('#btn_'+ file_no +'_'+ sheet_tab_no +'_columns_'+ row_no +'_special').show();
                  }
                  else
                  {
                    var data = {
                      'file_no' : file_no, 
                      'sheet_tab_no' : new_sheet_no,
                      'row_no' : row_no,
                      'column' : row['column'],
                      'columnmapping' : row['columnmapping'],
                      'remarks' : row['remarks'],
                      'special' : row['special']
                    };       
                  
                    var newrow = addNewRow(data); 
                  }
                });                
              } 
              else
                $("#btn-worksheet-tab-" + file_no + "-" + new_sheet_no).trigger("click");                             
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.log('error: ' + textStatus);
            }
          });
        }              
      } catch (ex) {
        return reject(new Error(ex));
      }
    });

    
  }
  
  //Delete Tab
  $(document).on('click', '.btn-delete-worksheet-tab', function () {  
    var not_first_sheet = $(this).closest('li.nav-item:not(.standard)');

    if(not_first_sheet.length > 0)
    {
      var data = $(this).data();

      var file_no = (data) ? data['file_no'] : 0;
      var sheet_tab_no = (data) ? data['sheet_tab_no'] : 0;

      $('#file-repeater-' + file_no + ' .worksheet-tab ul.nav-tabs li#nav-item-'+ file_no + '-' + sheet_tab_no).remove();
      $('#file-repeater-' + file_no + ' .worksheet-tab .tab-content #navs-worksheet-tab-'+ file_no + '-' + sheet_tab_no).remove();

      $('#file-repeater-' + file_no + ' .worksheet-tab ul.nav-tabs li.nav-item:not(.add-worksheet-tab)').each(function (index) {       

        var final_sheet_tab_no = index+1;
        $(this).attr('id', 'nav-item-'+ file_no + '-' + index);

        var button = $(this).find('button');
        button.attr('id', 'btn-worksheet-tab-'+ file_no + '-' + index);
        button.attr('data-bs-target', '#navs-worksheet-tab-'+ file_no + '-' + index);
        button.attr('aria-controls', 'navs-worksheet-tab-'+ file_no + '-' + index);

        var delete_button = (index > 0) ? '<i class="bx bx-x ms-1 text-danger btn-delete-worksheet-tab" data-file_no="'+ file_no +'" data-sheet_tab_no="'+ index +'"></i>' : '';
        //var delete_button = '<i class="bx bx-x ms-1 text-danger btn-delete-worksheet-tab" data-id="'+ index +'"></i>';
        button.html('Sheet ' + final_sheet_tab_no + delete_button);      
      });

      $('#file-repeater-' + file_no + ' .worksheet-tab .tab-content .tab-pane').each(function (index) {       
        //var data = $(this).data();

        //var file_no = (data) ? data['file_no'] : 0;

        var new_sheet_no = index;
        //var sheet_tab_no = index+1;

        $(this).attr('id', 'navs-worksheet-tab-'+ file_no + '-'+ new_sheet_no);
        $(this).attr('aria-labelledby', '#btn-worksheet-tab-'+ file_no + '-'+ new_sheet_no);
       
        //var worksheetRowRepeater = $('#navs-worksheet-tab-' + file_no + '-' + new_sheet_no + ' .worksheet-row-repeater')

        //loadExcelColumnTemplateRowRepeater(worksheetRowRepeater);
        //updateControlsId(worksheetRowRepeater);   
      });
     
      $("#btn-worksheet-tab-" + file_no + "-0").trigger("click");
    }
  });

  // document.addEventListener('DOMContentLoaded', function () {
  //   var popoverButton = document.getElementById('btn-popover-calculation-0');
  //   var dropdown = document.getElementsByClassName('calc-type');

  //   // Initialize the popover
  //   var popover = new bootstrap.Popover(popoverButton);

  //   // Change event listener for the dropdown
  //   dropdown.addEventListener('change', function() {
  //       var selectedOption = dropdown.options[dropdown.selectedIndex];
  //       var content = selectedOption.getAttribute('data-content');

        
  //       // Update popover content
  //       popover.setContent({ '.popover-body': content });

  //       // Show the popover
  //       popover.show();
  //   });

  //   dropdown.addEventListener('click', function() {
  //     // Check if popover is already shown, then hide it
  //       if (popoverButton.classList.contains('show')) {
  //           popover.hide();
  //       }
  //   });
  // });

  //Calc Type - Tooltip  
  // $(document).on('change', '.calc-type', function () {
  //   var value = $(this).val();

  //   var data = $(this).data();
  //   var file_no = (data) ? data['file_no'] : 0;
  //   var btn_id = '#btn-popover-'+ value +'-calculation-' + file_no;

  //   if($(btn_id).length);   
  //     $(btn_id).trigger('click');      
  // });

  // Function to initialize popovers
  function initializePopovers() {
    //$('[data-bs-toggle="popover"]').popover();

    var popoverButton = document.getElementById('btn-popover-calculation-0');
    var popover = new bootstrap.Popover(popoverButton);
    console.log('Popovers initialized!');
  }
  
  $('#excelColumnTemplateModal').on('shown.bs.modal', function () {
    console.log("modal show");
    // $("#btn-worksheet-tab-0-0").removeClass("active");
    // $("#btn-worksheet-tab-0-0").addClass("active");

    // $("#navs-worksheet-tab-0-0").removeClass("active");
    // $("#navs-worksheet-tab-0-0").addClass("active");
    
    //initializePopovers();
  });

  $(document).on('shown.bs.tab', '#excelColumnTemplateModal button[data-bs-toggle="tab"]', function () {
    console.log("tab show");
    //initializePopovers();
  });

  $(document).on("click", ".popover .btn-skip" , function(){
    console.log("close clicked");
    $(this).parents(".popover").hide();   
  });

  $('.popover').click(function (e) {console.log("popover clicked");
      e.stopPropagation();
  }); 

  //Clear Special Controls
  function clearSpecialControlsValue(element)      
  {    
    var specialControls = element.find('.form-control.special, .form-select.special');

    specialControls.each(function (i) {      
      specialControls[i].value = '';
    });                  
  }

  //Show/hide Special Row
  function showHideSpecialControls(element, type)
  {
    var data = element.data();

    var file_no = data['file_no'];
    var sheet_tab_no = data['sheet_tab_no'];
    var row_no = data['row_no'];

    var id = "#template_columns_"+ file_no +"_"+ sheet_tab_no +"_columns_"+ row_no +"_special_rows";
    clearSpecialControlsValue($(id)); 

    if(type == 'show')
    {
      $(id).slideDown();
      $("#btn_"+ file_no +"_"+ sheet_tab_no +"_columns_"+ row_no +"_special").hide();

      validateTemplate();
    }
    else if(type == 'hide')
    {
      //$(id).slideUp();
      $(id).slideUp(400, function() {
        validateTemplate();
      });
      $("#btn_"+ file_no +"_"+ sheet_tab_no +"_columns_"+ row_no +"_special").show();
    }

    //validateTemplate();
  }

  //Special Add Button  
  $(document).on('click', '.btn-special', function () {
    showHideSpecialControls($(this), 'show');    
  });

  //Special Delete Button  
  $(document).on('click', '.btn-delete-special', function () {
    showHideSpecialControls($(this), 'hide');   
  });

  //Submit
  $(document).on("submit", ".form-repeater", function(event)
  {
    console.log("FORM SUBMITTED !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
    event.preventDefault();

    var data = $(this).data();  
   
    //var vat_reg_id = data['vat_reg_id'];
    //var client_id = data['client_id'];

    var btn_submit = $(this).find("button.btn-save-template");
    btn_submit.attr("disabled", "disabled");
    btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Saving...');
  
    $.ajax({     
      data: $(this).serialize(),
      //data: $(this).serializeArray(),
      url: `${excelColumnTemplateUrl}`,
      type: 'POST',
      success: function (response) {

        if(response.status == 200)   
        {   
          var excelcolumntemplate = response.excelcolumntemplate;
          
          // if(vat_reg_id)
          // {
          //   btn_submit.html("Saved");  

          //   $(".form-select.excel-column-template").val(excelcolumntemplate['id']);
          //   $("#btn-upload-vatreturn-" + vat_reg_id).removeAttr('disabled');

          //   $('#excelColumnTemplateModal-'+vat_reg_id + " .form-repeater").trigger('reset');
            
          //   var message = {message_title: 'New Template created!', message_text: 'New Template has been created.'};

          //   loadVATReturnsFileDocs('vatreturn', 'Excel/XML', client_id, vat_reg_id, message, '#excelColumnTemplateModal-'+vat_reg_id);
          // }
          // else  
          // {
            $('#excelColumnTemplateModal').modal('hide');

            clearItems();

            if($('.datatables-excelcolumntemplate').length == 0)
            {              
              //btn_submit.html("Saved");  

              var option = '<option value="'+excelcolumntemplate['id']+'">'+ excelcolumntemplate['name'] +'</option>';
             
              $(option).insertBefore($(".form-select.excel-column-template optgroup"));
              $(".form-select.excel-column-template").val(excelcolumntemplate['id']);
            }
            else
            {
              excelcolumntemplate_datas = drawDtTable(response, 'excelcolumntemplate');            
              dt_excelcolumntemplate.clear().rows.add(excelcolumntemplate_datas).draw();

              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Template ` + response.message,
                text: `Template was successfully ` + response.message + '.',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }   
          //}          
        }
        else if(response.status >= 400 && response.status <= 599)  
        {     
          btn_submit.removeAttr("disabled");
          btn_submit.html('Save Template');

          Swal.fire({
            title: 'Error!',
            text: (response.message) ? response.message : response.message.message,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });  
        }
      },
      error: function (xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err);       
        btn_submit.removeAttr("disabled");
        btn_submit.html('Save Template');    
        Swal.fire({
          title: 'Error!',
          text: err.message,
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });
 
  // edit excel-template
  $(document).on('click', '.edit-excel-template', function () {
    clearItems();

    var template_id = $(this).data('id'),
      edit_type = $(this).attr('title'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // changing the title of modal
    $('#modalLabel').html('Edit Excel Column Template'); 

    // get data
    $.get(`${excelColumnTemplateUrl}${template_id}\/edit`, function (data) 
    {                  
      var excelcolumntemplate = data['excelcolumntemplate'];
      var files = JSON.parse(excelcolumntemplate['columns']);  
    
      $("#template_id").val(excelcolumntemplate['id']);
      $("#edit_type").val(edit_type);      
      $("#template_name").val(excelcolumntemplate['name']);      
      
      var no_of_files = files.length;
      $("#no_of_files").val(no_of_files);

      $('.file-repeater').remove();
      loadFileRepeater(no_of_files, files);
          
      $(".btn-save-template").removeClass('disabled');
      $(".btn-save-template").removeClass('btn-danger');
      $(".btn-save-template").addClass('btn-success');
      $(".btn-save-template").removeAttr("disabled");     
      $(".btn-save-template").html('Save Template');
      
      $("#column_mapping .sk-bounce").hide();
      $("#column_mapping .card-column-mapping").show();      
    });
  });

  // Delete excel-template 
  $(document).on('click', '.delete-excel-template', function () {
    var template_id = $(this).data('id');
    
    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${excelColumnTemplateUrl}${template_id}`,
          success: function (result) {
            
            excelcolumntemplate_datas = drawDtTable(result, 'excelcolumntemplate');
            dt_excelcolumntemplate.clear().rows.add(excelcolumntemplate_datas).draw();     

            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The template has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });       
          },
          error: function (error) {
            console.log(error);
          }
        });
        
        
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The template is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  //COMPANY VIEW - EXCEL TEMPLATE SELECTION
  $(document).on('change', '.form-select.excel-column-template', function () {  
    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    var file_type = data['file_type'];

    var template_id = $(this).val();

    if(template_id == "" || template_id == 0)  
    {
      if(file_type == 'vatreturn')
      {
        $(".navs-vatreturns-documents .dropzone-file[data-vat_reg_id='"+ vat_reg_id +"'][data-file_type='vatreturn']").each(function () {
          var original_file = $(this).find("input[name='original_file']");          
          original_file.val(0);
        });  
      }
      else if(file_type == 'iranyexcel')
      {
        $(".navs-importreconciliation-documents .dropzone-file[data-vat_reg_id='"+ vat_reg_id +"'][data-file_type='iranyexcel']").each(function () {
          var original_file = $(this).find("input[name='original_file']");          
          original_file.val(0);
        });  
      }    
    }
      
    if(template_id != "")     
    {   
      if(template_id != "any-excel")
      {
        $("#btn-upload-"+ file_type +"-" + vat_reg_id).attr('disabled', 'disabled');
        $.ajax({
          type: 'PUT',       
          url: `${excelColumnTemplateUrl}${template_id}`,  
          data: {vat_reg_id: vat_reg_id},
          success: function(data){    
            var nav_id = 'vatreturns';
            if(file_type == 'iranyexcel')
              nav_id = 'importreconciliation';

            $(".navs-"+ nav_id +"-documents .dropzone-file[data-vat_reg_id='"+ vat_reg_id +"'][data-file_type='"+ file_type +"']").each(function () {
              var original_file = $(this).find("input[name='original_file']");   

              if(template_id == 0)         
                original_file.val(0);
              else
                original_file.val(1);
            });
          
            $("#btn-upload-"+ file_type +"-" + vat_reg_id).removeAttr('disabled');         
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log('error: ' + textStatus);
          }
        });
      }
    }  
  });

  function enableSelectButton()
  {
    if($("#excel-template-selection .switch-input:checked").length == 0)
    {
      $(".btn-excel-template-select").addClass('disabled');
      $(".btn-excel-template-select").addClass('btn-danger');
      $(".btn-excel-template-select").removeClass('btn-success');
      $(".btn-excel-template-select").attr("disabled", "disabled"); 
    }
    else
    {
      $(".btn-excel-template-select").removeClass('disabled');
      $(".btn-excel-template-select").removeClass('btn-danger');
      $(".btn-excel-template-select").addClass('btn-success');
      $(".btn-excel-template-select").removeAttr("disabled", "disabled"); 
    }   
  }
  enableSelectButton();  

  $(document).on('change', '#excel-template-selection .switch-input', function () {     
    enableSelectButton();
  });

  $(document).on('click', 'button.btn-excel-template-select:not(.vatcheck)', function () {

    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    var file_type = data['file_type'];

    var template_id = $('input[name="excel_template_selection_'+ file_type + '_' + vat_reg_id + '"]:checked').val();
 
    if(template_id != "")     
    {   
      var btn_excel_template_select = $(this);
      btn_excel_template_select.attr("disabled", "disabled");
      btn_excel_template_select.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Selecting...');

      $("#btn-upload-"+ file_type +"-" + vat_reg_id).attr('disabled', 'disabled');
      $.ajax({
        type: 'PUT',       
        url: `${excelColumnTemplateUrl}${template_id}`,  
        data: {vat_reg_id: vat_reg_id, file_type: file_type},
        success: function(data){    
          var nav_id = "vatreturns";
          if(file_type == 'iranyexcel')
            nav_id = "importreconciliation";

          var excelcolumntemplate = data['excelcolumntemplate'];
          
          $(".navs-"+ nav_id +"-documents .dropzone-file[data-vat_reg_id='"+ vat_reg_id +"'][data-file_type='"+ file_type +"']").each(function () {
            var original_file = $(this).find("input[name='original_file']");   

            if(template_id == 0)         
              original_file.val(0);
            else
              original_file.val(1);

           
            $('#excel-column-template-' + file_type + '-' + vat_reg_id + ' optgroup[label="-- Select Template --"] option').each(function (i) {               
              if($(this).val() > 0)
                $(this).remove();
            });
            $('#excel-column-template-' + file_type + '-' + vat_reg_id + ' optgroup[label="-- Select Template --"]').append('<option value="'+ excelcolumntemplate.id +'" selected>'+ excelcolumntemplate.name +'</option>');

            btn_excel_template_select.html('<span class="align-middle">Select');  
            btn_excel_template_select.removeAttr('disabled');  

            var excelTemplateSelectionModal = $('#excelTemplateSelectionModal-' + file_type + '-' + vat_reg_id);
           
            if (excelTemplateSelectionModal.length) {
              excelTemplateSelectionModal.modal('hide');
            }
          }); 
          $("#btn-upload-"+ file_type +"-" + vat_reg_id).removeAttr('disabled');         
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.log('error: ' + textStatus);
        }
      });
    }
  });
  //end COMPANY VIEW - EXCEL TEMPLATE SELECTION

  //VAT REG. MAIN - EXCEL TEMPLATE CREATION
  var previousoption = $("#excel_column_template option:selected").val();
  
  $('#excel_column_template').on('change', function() {  

    if($(this).val() == '')
    {      
      $(this).find('option[value=""]').removeAttr("selected");
      $(this).val(previousoption);
      
      var excelColumnTemplateModal = $('#excelColumnTemplateModal');
      if (excelColumnTemplateModal.length)
      {
        excelColumnTemplateModal.modal('show');

        clearItems();

        $("#column_mapping .sk-bounce").hide();
        $("#column_mapping .card-column-mapping").show();
      }
    }
  });
  //end VAT REG. MAIN - EXCEL TEMPLATE CREATION

  //VAT CHECK - EXCEL TEMPLATE SELECTION
  $(document).on('click', 'button.btn-excel-template-select.vatcheck', function () {

    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    var file_type = data['file_type'];

    var template_id = $('input[name="excel_template_selection_'+ file_type + '_' + vat_reg_id + '"]:checked').val();
 
    if(template_id != "")     
    {   
      var btn_excel_template_select = $(this);
      btn_excel_template_select.attr("disabled", "disabled");
      btn_excel_template_select.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Selecting...');
      
      $.ajax({
        type: 'PUT',       
        url: `${excelColumnTemplateUrl}${template_id}`,  
        data: {vat_reg_id: vat_reg_id, file_type: file_type},
        success: function(data){    
                   
          btn_excel_template_select.html('<span class="align-middle">Select');  
          btn_excel_template_select.removeAttr('disabled');  

          //$("#anyexcel_template_id").val(template_id);
   
          var excelTemplateSelectionModal = $('#excelTemplateSelectionModal-' + file_type + '-' + vat_reg_id);
          excelTemplateSelectionModal.modal('hide');     

          var VATCheckFileModal = $('#uploadSingleModal-vatcheck-'+ vat_reg_id +'-0');  
          VATCheckFileModal.modal('show');   
               
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.log('error: ' + textStatus);
        }
      });
    }
  });
  //end VAT CHECK - EXCEL TEMPLATE SELECTION

});//();  