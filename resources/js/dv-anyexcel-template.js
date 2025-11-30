/**
 * Any Excel Template
 */

'use strict';
if($("#upload_anyexcel_template_file").length > 0)
  Dropzone.autoDiscover = false;

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
  var dt_anyexcel_template_table = $('.datatables-anyexceltemplate'),
      anyexcelTemplateUrl = baseUrl + 'anyexcel-template/',
      clientUrl = baseUrl + 'company';       

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 

  // Excel Column templates datatable
  if (dt_anyexcel_template_table.length) {
    var dt_anyexcel_template = dt_anyexcel_template_table.DataTable({
      data: anyexcel_template_datas,        
      processing: true,  
      autoWidth: false,   
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'name' },
        //{ data: 'version' },
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
            var $client_name = full['client_name'];
            
            return '<span>' + $title + '<br>' + $client_name + '</span>';
          }
        },
        // {
        //   // Version
        //   targets: 2,          
        //   width: "5%",   
        //   render: function (data, type, full, meta) {
        //     var $version = full['version'];
            
        //     return '<span>' + $version + '</span>';
        //   }
        // },          
        {
          // Columns
          targets: 2,          
          width: "30%",   
          render: function (data, type, full, meta) {
            var $files = full['columns'];
            
            var parsed_files = JSON.parse($files);
          
            var htmlColumns = '<table class="table">';

            $.each(parsed_files, function (idxfile, sheet) {              
              var sheet_index = idxfile + 1;

              if(sheet.data_index)
              {
                htmlColumns += '<tr>' +
                                  '<td colspan="2">' + 
                                    '<h5 class="m-0 mx-n3">Sheet ' + sheet_index + ':<br>' + 
                                      '<span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Sheet Name: </span>' +  
                                      '<span class="fs-6 fw-normal">' + sheet.sheet_name + '</span><br>' +   
                                      '<span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Data Row: </span>' +  
                                      '<span class="fs-6 fw-normal">' + sheet.data_index + '</span><br>' +                                          
                                    ' </h5>' +
                                    '</td>' +
                                '</tr>';

                var parsed_columns = sheet['columns'];
                $.each(parsed_columns, function (idx, item) { 

                  if(item.mapped_column)
                  {
                    var column_mapping = item.mapped_column.split(':');
                   
                    var reverse_rows = '';                
                    if(item.reverse)                 
                      reverse_rows = '<br><u>Reverse:</u><i class="bx bx-check"></i>';

                    var formula_rows = '';                
                    if(item.formula)                 
                      formula_rows = '<br><u>Formula:</u> ' + item.formula;
                    
                    htmlColumns += '<tr><td>' + item.column + ':</td><td>' + column_mapping[1] + reverse_rows + formula_rows + '</td></tr>';                                                
                  }
                }); 
              }            
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

            //var vatreg = full['vatreg'].length;
            var vatreturnfiles = full['vatreturnfiles'].length;

            if(vatreturnfiles == 0)
              buttons =  '<button class="btn btn-sm btn-icon edit-anyexcel-template" data-id="'+full['id']+'" title="Edit"><i class="bx bx-edit"></i></button>' +
                          '<button class="btn btn-sm btn-icon delete-anyexcel-template" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';

            // if(vatreg == 0)
            // {
            //   buttons +=  //'<button class="btn btn-sm btn-icon edit-anyexcel-template" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#excelColumnTemplateModal" title="Edit"><i class="bx bx-edit"></i></button>' +
            //               '<button class="btn btn-sm btn-icon delete-anyexcel-template" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';
            // }
            //else
              //buttons +=  '<button class="btn btn-sm btn-icon edit-anyexcel-template" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#excelColumnTemplateModal" title="Clone"><i class="bx bx-copy-alt"></i></button>';                          
            
            
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
          className: 'add-new-anyexcel-template btn btn-primary',
          // attr: {
          //   'data-bs-toggle': 'modal',
          //   'data-bs-target': '#excelColumnTemplateModal'
          // }
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
        $("#anyexceltemplate-card").show();        
      }
    });
  }  

  // add excel-template
  $('.add-new-anyexcel-template').on('click', function () {      
    window.location.href = `${anyexcelTemplateUrl}create`;      
  });

  // edit excel-template
  $('.edit-anyexcel-template').on('click', function () {  
    var template_id = $(this).data('id');
  
    window.location.href = `${anyexcelTemplateUrl}${template_id}/edit`;      
  });

  // Wizard Validation
  // --------------------------------------------------------------------
  const anyexcelWizardValidation = document.querySelector('#anyexcel-wizard-validation');
  if (typeof anyexcelWizardValidation !== undefined && anyexcelWizardValidation !== null) {
    // Wizard form
    const anyexcelWizardValidationForm = anyexcelWizardValidation.querySelector('#anyexcel-wizard-validation-form');
    // Wizard steps
    const anyexcelWizardValidationFormStep1 = anyexcelWizardValidationForm.querySelector('#basic-details-validation');
    const anyexcelWizardValidationFormStep2 = anyexcelWizardValidationForm.querySelector('#file-upload-mapping-validation');
    const anyexcelWizardValidationFormStep3 = anyexcelWizardValidationForm.querySelector('#overview-validation');
    // Wizard next prev button
    const anyexcelWizardValidationNext = [].slice.call(anyexcelWizardValidationForm.querySelectorAll('.btn-next'));
    const anyexcelWizardValidationPrev = [].slice.call(anyexcelWizardValidationForm.querySelectorAll('.btn-prev'));

    const validationStepper = new Stepper(anyexcelWizardValidation, {
      linear: true
    });

    // Basic details
    const FormValidation1 = FormValidation.formValidation(anyexcelWizardValidationFormStep1, {
      fields: {
        template_name: {
          validators: {
            notEmpty: {
              message: 'The template name is required'
            },
            stringLength: {
              min: 6,
              max: 100,
              message: 'The template name must be more than 6 and less than 100 characters long'
            },
            regexp: {
              regexp: /^[\p{L}0-9 ]+$/u, ///^[a-zA-Z0-9 æøåÆØÅ]+$/, ///^[a-zA-Z0-9 ]+$/,
              message: 'The template name can only contain letters, number and space'
            }
          }
        },
        client_id: {
          validators: {
            notEmpty: {
              message: 'The client name is required'
            }            
          }
        }        
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          //rowSelector: '.col-sm-6'
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            switch (field) {
              case 'client_id':
                return '.col-sm-4';                
              default:
                return '.col-sm-6';
            }            
          }
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton()
      },
      init: instance => {
        instance.on('plugins.message.placed', function (e) {
          //* Move the error message out of the `input-group` element
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    }).on('core.form.valid', function () {      console.log("dsdssssss");
      // Jump to the next step when all fields in the current step are valid
      validationStepper.next();
      // Always initialize on load
      setupScrollSync(); 
    });

    // File Upload and Mapping
    const FormValidation2 = FormValidation.formValidation(anyexcelWizardValidationFormStep2, {
      fields: {
        anyexcel_template_file: {
          validators: {
            callback: {
              message: 'Please upload a file',
              callback: function (input) {                 
                return ($('#anyexcel_template_file').val() !== '' && $('#anyexcel_template_file').val() !== '0');
              }
            }
          }
        },
        'input[id^="data_index_"]': {
          validators: {
            notEmpty: {
              message: 'This field is required'
            }
          }
        },
        'input[id^="sheet_name_"]': {
          validators: {
            notEmpty: {
              message: 'This field is required'
            }
          }
        }        
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          //rowSelector: '.col-sm-12'
          rowSelector: function (field, ele) {console.log(field);
            // field is the field name & ele is the field element
            switch (field) {
              case 'anyexcel_template_file':
                return '.col-sm-12';
              case 'input[id^="data_index_"]':
                return '.col-1';
              case 'input[id^="sheet_name_"]':
                return '.col-2';                    
              default:
                return '.col-sm-6';
            }            
          }
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton()
      }
    }).on('core.form.valid', function () {
//console.log("12111111");
      var validateMessage = validateOverview();
      if(validateMessage == '')
      {//console.log("validate yes");
        $("#file-upload-mapping-validation #validate-message").remove();
        showOverview();
        // Jump to the next step when all fields in the current step are valid
        validationStepper.next();
      }
      else
      {
        $("#file-upload-mapping-validation #validate-message").remove();
        $("#excel-preview").prepend(`<div id="validate-message" class="text-danger px-4">` + validateMessage + `</div>`);
        $("#file-upload-mapping-validation #validate-message").focus();
      }
    });
  
    // Social links
    const FormValidation3 = FormValidation.formValidation(anyexcelWizardValidationFormStep3, {
      fields: {
        
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.col-sm-12'
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton()
      }
    }).on('core.form.valid', function () {console.log("333333333");
      // You can submit the form
      // anyexcelWizardValidationForm.submit()
      // or send the form data to server via an Ajax request
      formSubmit();
      // To make the demo simple, I just placed an alert
      //alert('Submitted..!!');
    });

    anyexcelWizardValidationNext.forEach(item => {console.log("item");
      item.addEventListener('click', event => {console.log("item click" + validationStepper._currentIndex);
        // When click the Next button, we will validate the current step
        switch (validationStepper._currentIndex) {
          case 0:
            FormValidation1.validate();
            break;

          case 1:
            FormValidation2.validate();
            break;

          case 2:
            FormValidation3.validate();
            break;

          default:
            break;
        }
      });
    });

    anyexcelWizardValidationPrev.forEach(item => {console.log("prev item");
      item.addEventListener('click', event => {console.log("prev item click");
        switch (validationStepper._currentIndex) {
          case 2:
            validationStepper.previous();
            break;

          case 1:
            validationStepper.previous();
            break;

          case 0:

          default:
            break;
        }
      });
    });
  }

  function validateOverview()
  {    
    var activeSheets = $("#excel-preview .tab-content .tab-pane table").find(".disable-sheet").length;
    var inactiveSheets = $("#excel-preview .tab-content .tab-pane table").find(".enable-sheet").length;

    const data = $('#anyexcel-wizard-validation-form').serializeArray();

    let templates = {};

    data.forEach(({ name, value }) => {
      const keys = name
          .replace(/\]/g, '')    // remove closing brackets
          .split(/\[|\]/)        // split by [
          .filter(k => k.length); // remove empty strings

      if (keys[0] === 'template') {
          const [_, templateIndex, field1, index2, field2] = keys;

          // Ensure the template index exists
          if (!templates[templateIndex]) {
              templates[templateIndex] = { columns: {} };
          }

          if (field1 === 'columns') {
              if (!templates[templateIndex].columns[index2]) {
                  templates[templateIndex].columns[index2] = {};
              }

              templates[templateIndex].columns[index2][field2] = value;
          } else {
              templates[templateIndex][field1] = value;
          }
      } 
      else if (keys[0] === 'template_name') {
          templates.name = value;
      }
      else if (keys[0] === 'client_name') {
          templates.client_name = value;
      }
    });
console.log(templates);
    // Loop through each sheet
    var disabledSheets = 0;  
    var sheetCount = 0;  
    Object.entries(templates).forEach(([sheetIndex, sheet]) => {
        if (sheetIndex === 'name' || sheetIndex === 'client_name')
        { 
          sheetCount++;

          return; // Skip the template name key
        }

        const sheetTitle = sheet.sheet_title || `Sheet ${+sheetIndex + 1}`;
        const sheetName = sheet.sheet_name || `Sheet ${+sheetIndex + 1}`;
        const dataRow = sheet.data_index || '';
        const columns = sheet.columns || {};

        // Check if all mapped_column values are empty
        const allEmpty = Object.values(columns).every(col => !col.mapped_column?.trim());
        if (allEmpty)
          disabledSheets++;
    });
console.log("sheetCount: " + sheetCount);
console.log("disabledSheets: " + disabledSheets);
console.log("activeSheets: " + activeSheets);
console.log("inactiveSheets: " + inactiveSheets);

    var validateSheets = '';
    if(sheetCount == disabledSheets)
      validateSheets = 'Enabled sheets cannot be blank.';
    else if(sheetCount == activeSheets)
    {
      Object.entries(templates).forEach(([sheetIndex, sheet]) => {
        if (sheetIndex === 'name' || sheetIndex === 'client_name') return; // Skip the template name key

        const sheetTitle = sheet.sheet_title || `Sheet ${+sheetIndex + 1}`;
        const sheetName = sheet.sheet_name || `Sheet ${+sheetIndex + 1}`;
        const dataRow = sheet.data_index || '';
        const columns = sheet.columns || {};

        if(dataRow == '' || sheetName == '')          
          validateSheets = "Please fill in the ‘Data Start’ and ‘Sheet Name’ fields in all enabled sheets.";
        else
        {
          // Check if all mapped_column values are empty
          const allEmpty = Object.values(columns).every(col => !col.mapped_column?.trim());
          if (allEmpty)
            validateSheets = "Please map the required columns in all enabled sheets.";
        }    
      });
    }

    return validateSheets;
  }

  function showOverview()
  {    
    const data = $('#anyexcel-wizard-validation-form').serializeArray();

    // let columns = {};
    // let sheetName = '';
    // let dataRow = '';
    // let templateName = '';

    // // Group and extract values
    // data.forEach(({ name, value }) => {
    //     if (name === 'template_name') {
    //         templateName = value;
    //     } else if (name.match(/template\[0]\[sheet_name]/)) {
    //         sheetName = value;
    //     } else if (name.match(/template\[0]\[data_index]/)) {
    //         dataRow = value;
    //     } else {
    //         const match = name.match(/template\[0]\[columns]\[(\d+)]\[(\w+)]/);
    //         if (match) {
    //             const index = match[1];
    //             const field = match[2];
    //             if (!columns[index]) columns[index] = {};
    //             columns[index][field] = value;
    //         }
    //     }
    // });

    // // Build table HTML
    // let html = `<table class="table"><tbody>
    //     <tr><td colspan="2">
    //       <h3>${templateName}</h3>
    //     </td></tr>
    //     <tr><td colspan="2">
    //         <h5 class="m-0">
    //             Sheet 1:<br>
    //             <span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Sheet Name: </span>
    //             <span class="fs-6 fw-normal">${sheetName}</span><br>
    //             <span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Data Row: </span>
    //             <span class="fs-6 fw-normal">${dataRow}</span>
    //         </h5>
    //     </td></tr>`;

    // Object.values(columns).forEach(col => {
    //     if (!col.column || !col.mapped_column) return;

    //     const colLetter = col.column;
    //     const mappedParts = col.mapped_column.split(':');
    //     const mappedVal = mappedParts.length === 2 ? mappedParts[1] : '';

    //     html += `<tr><td>${colLetter}:</td><td>${mappedVal}`;

    //     if (col.formula) {
    //         html += `<br><u>Formula:</u> ${col.formula}`;
    //     }

    //     if (col.reverse) {
    //         html += `<br><u>Reverse:</u> ${col.reverse}`;
    //     }

    //     html += `</td></tr>`;
    // });

    // html += '</tbody></table>';

    let templates = {};

    // // Parse form data
    // data.forEach(({ name, value }) => {
    //     const match = name.match(/template\[(\d+)](?:\[columns])?\[(\d+)]?\[?(\w+)?]?\[?(\w+)?]?/);
    //     if (match) {
    //         const templateIndex = match[1];
    //         const columnIndex = match[2];
    //         const field = match[3];
    //         const subField = match[4];

    //         if (!templates[templateIndex]) {
    //             templates[templateIndex] = { columns: {} };
    //         }

    //         if (field === 'data_index' || field === 'sheet_name') {
    //             templates[templateIndex][field] = value;
    //         } else if (field === 'columns') {
    //             if (!templates[templateIndex].columns[columnIndex]) {
    //                 templates[templateIndex].columns[columnIndex] = {};
    //             }

    //             if (subField) {
    //                 templates[templateIndex].columns[columnIndex][subField] = value;
    //             }
    //         }
    //     } else if (name === 'template_name') {
    //         templates['name'] = value;
    //     }
    // });

    data.forEach(({ name, value }) => {
      const keys = name
          .replace(/\]/g, '')    // remove closing brackets
          .split(/\[|\]/)        // split by [
          .filter(k => k.length); // remove empty strings

      if (keys[0] === 'template') {
          const [_, templateIndex, field1, index2, field2] = keys;

          // Ensure the template index exists
          if (!templates[templateIndex]) {
              templates[templateIndex] = { columns: {} };
          }

          if (field1 === 'columns') {
              if (!templates[templateIndex].columns[index2]) {
                  templates[templateIndex].columns[index2] = {};
              }

              templates[templateIndex].columns[index2][field2] = value;
          } else {
              templates[templateIndex][field1] = value;
          }
      } 
      else if (keys[0] === 'template_name') {
          templates.name = value;
      }
      else if (keys[0] === 'client_name') {
          templates.client_name = $("#client_name").val();
      }
    });

    // Start building the full HTML
    let fullHtml = `<table class="table w-50"><tbody>
    <tr>
      <td colspan="2">        
        <h4 class="m-0">Template Name: <span class="fw-normal">${templates.name || ''}</span></h4>
        <h6 class="m-0">Client Name: <span class="fw-normal">${templates.client_name || ''}</span></h6>
      </td>
    </tr>
    `;
console.log(templates);
    // Loop through each sheet
    Object.entries(templates).forEach(([sheetIndex, sheet]) => {
        if (sheetIndex === 'name' || sheetIndex === 'client_name') return; // Skip the template name key

        const clientName = sheet.client_name || '';
        const sheetTitle = sheet.sheet_title || `Sheet ${+sheetIndex + 1}`;
        const sheetName = sheet.sheet_name || `Sheet ${+sheetIndex + 1}`;
        const dataRow = sheet.data_index || '';
        const columns = sheet.columns || {};

        // Check if all mapped_column values are empty
        const allEmpty = Object.values(columns).every(col => !col.mapped_column?.trim());

        if (allEmpty) {
            fullHtml += `
            <tr>
                <td colspan="2">
                  <h5 class="m-0 mx-n3 text-danger">${sheetTitle}: <strong>Disabled</strong></h5>                    
                </td>
            </tr>
            `;
            return;
        }

        // Sheet Header
        fullHtml += `
            <tr>
                <td colspan="2">
                    <h5 class="m-0 mx-n3">${sheetTitle}:<br>
                    <span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Sheet Name: </span><span class="fs-6 fw-normal">${sheetName}</span><br>                    
                    <span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Data Row: </span><span class="fs-6 fw-normal">${dataRow}</span><br>
                    </h5>
                </td>
            </tr>
        `;

        // Sheet Columns
        Object.values(columns).forEach(col => {
            if (!col.column || !col.mapped_column?.trim()) return;

            const [letter, label] = col.mapped_column.split(':');
            let value = label || '';

            if (col.formula) {
                value += `<br><u>Formula:</u> ${col.formula}`;
            }

            if (col.reverse) {
                value += `<br><u>Reverse:</u><i class="bx bx-check"></i>`;
            }

            fullHtml += `<tr><td>${col.column}:</td><td>${value}</td></tr>`;
        });        
    });
    fullHtml += `</tbody></table>`;

    $("#excel-overview").html(fullHtml);
  }

  function formSubmit()
  {
    var btn_submit = $('#anyexcel-wizard-validation-form').find("button.btn-submit");
    btn_submit.attr("disabled", "disabled");
    btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Submitting...');

    // adding or updating user when form successfully validate
    $.ajax({
      data: $('#anyexcel-wizard-validation-form').serialize(),
      url: `${anyexcelTemplateUrl}`,
      type: 'POST',
      success: function (result) {   
        btn_submit.html("Saved");            

        // sweetalert
        Swal.fire({
          icon: 'success',
          title: `Successfully saved!`,
          text: `Any excel template saved successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(function() {            
          if(window.location.href.indexOf('create') > -1 && window.location.href.indexOf('?') > -1)           
            window.location.href = `${clientUrl}/` + result.client_id + '#' + result.vat_reg_id;            
          else       
            window.location.href = `${anyexcelTemplateUrl}`;     
          //alert('Saved..!!');
        });
      },
      error: function (xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        
        btn_submit.removeAttr("disabled");
        btn_submit.html('Submit');

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
  }
 
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

  // Basic Dropzone
  // --------------------------------------------------------------------
  const dropzoneBasic = document.querySelector('#upload_anyexcel_template_file');
  if (dropzoneBasic) {
    const myDropzone = new Dropzone(dropzoneBasic, {
      url: `${anyexcelTemplateUrl}upload`,
      method: 'post',
      previewTemplate: previewTemplate,
      parallelUploads: 1,
      //maxFilesize: 5,
      acceptedFiles: ".xlsx,.xls",
      addRemoveLinks: true,
      maxFiles: 1,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      init : function() {
        this.on("sending", function(file, xhr, formData) {   
          formData.append("client_id", $("#client_id").val());         
          formData.append("template_name", $("#template_name").val());  
        });
      }
    });
    
    var vatreturn_file_id = '';
    let anyexceltemplate = '';
    let uploaded_file_name = '';
    var url = window.location.href;
    if((url.indexOf('create') > -1 && url.indexOf('?') > -1) || url.indexOf('edit') > -1)
    {
      if(url.indexOf('edit') > -1)
      {
        //const url = window.location.pathname;
        //vatreturn_file_id = url.match(/\/(\d+)\//)[1];//its the anyexcel_template id        
        vatreturn_file_id = $("#anyexcel_template_id").val();//its the anyexcel_template id        
      }
      else
      {
        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        vatreturn_file_id = urlParams.get('id');
      }

      $.getJSON(`${anyexcelTemplateUrl}${vatreturn_file_id}`, function(data) {  
        console.log(data);
       
        anyexceltemplate = data.anyexceltemplate;

        if(anyexceltemplate)
        {
          let file = data.file;
          uploaded_file_name = file.name;        

          let mockFile = {
              name: file.name,
              size: file.size,
              type: file.type,
              accepted: true
          };

          myDropzone.emit("addedfile", mockFile);
          //myDropzone.emit("thumbnail", mockFile, file.url);
          myDropzone.emit("complete", mockFile);
          myDropzone.files.push(mockFile);
        }
      });
      // // Loop through existing files and display them
      // existingFiles.forEach(function (file) {
      //     let mockFile = {
      //         name: file.name,
      //         size: file.size,
      //         type: file.type || 'image/jpeg', // adjust as needed
      //         accepted: true
      //     };

      //     myDropzone.emit("addedfile", mockFile);
      //     myDropzone.emit("thumbnail", mockFile, file.url); // thumbnail or preview URL
      //     myDropzone.emit("complete", mockFile);
      //     myDropzone.files.push(mockFile);

      //     // Optional: Attach server ID or DB record ID
      //     mockFile.serverId = file.id;
      // });
    }

    myDropzone.on("success", function (file, response) { 
      if(response == "")   
      {
        
      }
      else
      {
        $("#anyexcel_template_id").val(response.anyexcel_template_id);
        $("#basic-details-validation .btn-next").removeAttr('disabled');

        $('.data-index').val('');        
        
        $('#excel-preview').show();
        //$('#excel-preview').html(response.excel_preview);

        $('#excel-preview .anyexcel-template-preview-tab ul.nav-tabs').html(response.anyexcel_template_preview_tab_li); 
        $('#excel-preview .anyexcel-template-preview-tab div.tab-content.scroll-bottom').html(response.anyexcel_template_preview_tab_content);         

        $('.data-index').focus(); 

        // Always initialize on load
        setupScrollSync();      
      }
    });

    myDropzone.on("addedfile", function () {console.log("added file");
      //FormValidation2.revalidateField('anyexcel_template_file');      
      document.getElementById('anyexcel_template_file').value = '1';
      document.getElementById('vatreturn_file_id').value = '';
      
      if((url.indexOf('create') > -1 && url.indexOf('?') > -1) || url.indexOf('edit') > -1) 
      {        
        $.ajax({        
          data: {vatreturn_file_id: vatreturn_file_id, uploaded_file_name: uploaded_file_name, 
            anyexceltemplate: anyexceltemplate, anyexcel_template_id: $("#anyexcel_template_id").val()},  
          url: `${anyexcelTemplateUrl}upload`,
          type: 'POST',
          success: function (response) {   
            console.log(response);

            if(response.status != 'Error')
            {
              document.getElementById('vatreturn_file_id').value = vatreturn_file_id;

              $("#anyexcel_template_id").val(response.anyexcel_template_id);
              $("#basic-details-validation .btn-next").removeAttr('disabled');

              $('.data-index').val('');        
          
              $('#excel-preview').show();
             
              $('#excel-preview .anyexcel-template-preview-tab ul.nav-tabs').html(response.anyexcel_template_preview_tab_li); 
              $('#excel-preview .anyexcel-template-preview-tab div.tab-content.scroll-bottom').html(response.anyexcel_template_preview_tab_content);         

              $('.data-index').focus();

              // Always initialize on load
              setupScrollSync(); 
            }              
          },
          error: function (xhr, status, error) {
            //var err = JSON.parse(xhr.responseText);
            console.log(error);
          }
        });
      }
    });

    myDropzone.on("removedfile", function () {     
      document.getElementById('anyexcel_template_file').value = '';
          
      $('.data-index').val('');      
     
      $('#excel-preview .anyexcel-template-preview-tab ul.nav-tabs').html('');
      $('#excel-preview .anyexcel-template-preview-tab div.tab-content').html('');
    });    
  }
      
  $(document).on('change', '#client_id', function () { 
    var selected_option = $(this).find('option:selected');   
    $('#client_name').val(selected_option.text());    
  });

  $(document).on('change keypress keyup', '.data-index', function () {
    var data = $(this).data();
    var sheet_index = data.sheet_index;

    var column_index = 0;
    if(!$("#excel-preview table#sheet_"+ sheet_index +" tbody tr td:nth-child("+ (column_index + 1)  +")").hasClass('disabled'))
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr td:nth-child("+ (column_index + 1) +")").removeClass('bg-primary').removeClass('text-white').addClass('disabled');

    $("#excel-preview table#sheet_"+ sheet_index +" tbody tr").removeClass('bg-primary text-white header-row');
    $("#excel-preview table#sheet_"+ sheet_index +" tbody tr td").removeClass('bg-primary text-white').addClass('disabled');
    if($(this).val())
    {
      //$("#excel-preview table#sheet_"+ sheet_index +" tbody tr:nth-child("+ ($(this).val() - 1) +")").addClass('bg-primary text-white');
      // $("#excel-preview table#sheet_"+ sheet_index +" tbody tr.bg-primary")
      //   .nextAll('tr:not(:last-child)')
      //   .add("#excel-preview table#sheet_"+ sheet_index +" tbody tr.bg-primary")
      //   .find("td:not(:first-child):nth-child("+ (column_index + 1) +")")
      //   .removeClass('disabled')
      //   .addClass('bg-primary')
      //   .addClass('text-white');
     
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr:nth-child("+ $(this).val() +")").addClass('header-row');
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr:nth-child("+ $(this).val() +") td:first-child")
        .removeClass('disabled')
        .addClass('bg-primary')
        .addClass('text-white');

      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr.header-row")
        .nextAll('tr:not(:last-child)')
        .add("#excel-preview table#sheet_"+ sheet_index +" tbody tr.header-row")
        .find("td:not(:first-child):nth-child("+ (column_index + 1) +")")
        .removeClass('disabled')
        .addClass('bg-primary')
        .addClass('text-white');
    }
    else
    {
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr").removeClass('header-row');
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr td:first-child")        
        .removeClass('bg-primary')
        .removeClass('text-white')
        .addClass('disabled');
    }      
  });

  $(document).on('change', '.system-column', function () {
    var data = $(this).data();
    var sheet_index = data.sheet_index;
    var column_index = data.column_index;

    if(!$("#excel-preview table#sheet_"+ sheet_index +" tbody tr td:nth-child("+ (column_index + 2) +")").hasClass('disabled'))
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr td:nth-child("+ (column_index + 2) +")").removeClass('bg-primary').removeClass('bg-warning').removeClass('text-white').addClass('disabled');

    if($(this).val())     
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr.header-row")
        .nextAll('tr:not(:last-child)')
        .add("#excel-preview table#sheet_"+ sheet_index +" tbody tr.header-row")
        .find("td:not(:first-child):nth-child("+ (column_index + 2) +")")
        .removeClass('disabled')
        .addClass('bg-primary')
        .addClass('text-white');
    else
      $("#excel-preview table#sheet_"+ sheet_index +" tbody tr.header-row")
        .nextAll('tr:not(:last-child)')
        .add("#excel-preview table#sheet_"+ sheet_index +" tbody tr.header-row")
        .find("td:not(:first-child):nth-child("+ (column_index + 2) +")")        
        .removeClass('bg-primary')
        .removeClass('bg-warning')
        .removeClass('text-white')
        .addClass('disabled');    
  });

  $(document).on('click', '.btn-formula', function () {
    var data = $(this).data();
    
    var filldata = {                 
      "sheet_index": data['sheet_index'],
      "column_index": data['column_index'],
      "column_header": data['column_header'],
      "last_column": data['last_column']
    };
    fillFormulaModal(filldata);
  });

  $(document).on('change', '.arithmetic', function () {
    endFormula();
  });

  $(document).on('change', '.column', function () {    
    const $textInput = $(this).parent().next('.formula-column-value-div');

    $textInput.hide(); 
    if($(this).val() === 'type_value')
      $textInput.show();
    
    endFormula();
  });

  $(document).on('change keypress keyup', '.value, #initial_column_or_value', function () {
    endFormula();
  });

  $(document).on('click', '.btn-add-formula', function () {
    var sheet_index = $("#sheet_index").val();
    var column_index = $("#column_index").val();

    var end_formula = $('#end_formula').html();

    $("#formula_" + sheet_index + "_" + column_index + " span").html(end_formula);

    $("#formula_" + sheet_index + "_" + column_index + " input").val(end_formula);  

    btnFormula(sheet_index, column_index);
    
    $('#formulaModal').modal('hide');    
  });

  $(document).on('click', '.btn-remove-formula', function () {      
    $('#formulaModal').modal('hide');

    $('#formula_' + $("#sheet_index").val() + '_' + $("#column_index").val() + ' span').html('');
    $('#formula_' + $("#sheet_index").val() + '_' + $("#column_index").val() + ' input').val('');
    
    btnFormula($("#sheet_index").val(), $("#column_index").val());

    clearFormulaModal();
  });

  $(document).on('click', '.btn.disable-sheet', function () {  
    var data = $(this).data();
    var sheet_index = data.sheet_index;

    let sheet_count = $("#excel-preview ul.nav-tabs li").length;
    let disabled_sheet_count = $("#excel-preview .tab-content .tab-pane table.disabled").length;    
    
    $("#sheet_" + sheet_index + " .data-index").removeAttr('required');
    $("#sheet_" + sheet_index + " .data-index").attr('disabled', 'disabled');

    $("#sheet_" + sheet_index + " .sheet-name").attr('disabled', 'disabled');
    $("#sheet_" + sheet_index + " .sheet-name").removeAttr('required');

    $("#sheet_" + sheet_index).addClass('disabled'); 

    if((disabled_sheet_count + 1) == (sheet_count - 1))    
      $("#excel-preview .tab-content .tab-pane table:not(.disabled)").find(".disable-sheet").hide();  

    $(this).removeClass('btn-outline-danger');
    $(this).addClass('btn-outline-success');

    $(this).removeClass('disable-sheet');
    $(this).addClass('enable-sheet');

    $(this).html('<i class="bx bx-plus me-1"></i> Enable Sheet');
  });

  $(document).on('click', '.btn.enable-sheet', function () {  
    var data = $(this).data();
    var sheet_index = data.sheet_index;

    //let sheet_count = $("#excel-preview ul.nav-tabs li").length;
    //let disabled_sheet_count = $("#excel-preview .tab-content .tab-pane table.disabled").length;    
    
    $("#sheet_" + sheet_index + " .data-index").attr('required', true);
    $("#sheet_" + sheet_index + " .data-index").removeAttr('disabled');

    $("#sheet_" + sheet_index + " .sheet-name").attr('required', true);
    $("#sheet_" + sheet_index + " .sheet-name").removeAttr('disabled');

    $("#sheet_" + sheet_index).removeClass('disabled'); 

    //if((disabled_sheet_count + 1) == (sheet_count - 1))    
    //  $("#excel-preview .tab-content .tab-pane table:not(.disabled)").find(".disable-sheet").hide();  

    $("#excel-preview .tab-content .tab-pane table:not(.disabled)").find(".disable-sheet").show();  

    $(this).removeClass('btn-outline-success');
    $(this).addClass('btn-outline-danger');

    $(this).removeClass('enable-sheet');
    $(this).addClass('disable-sheet');

    $(this).html('<i class="bx bx-x me-1"></i> Disable Sheet');
  });

  function btnFormula(sheet_index, column_index)
  {    
    var end_formula = $.trim($('#formula_' + sheet_index + '_' + column_index + ' span').html());

    if(end_formula == '' || end_formula.length === 1)
    {
      $("#btn_formula_" + sheet_index + "_" + column_index).removeClass("text-warning");  
      $("#btn_formula_" + sheet_index + "_" + column_index).attr("title", "Add Formula");  

      $("#btn_formula_" + sheet_index + "_" + column_index + " i").removeClass("bx-edit");
      $("#btn_formula_" + sheet_index + "_" + column_index + " i").addClass("bx-plus");    
    }
    else
    {
      $("#btn_formula_" + sheet_index + "_" + column_index).addClass("text-warning");  
      $("#btn_formula_" + sheet_index + "_" + column_index).attr("title", "Edit Formula");  

      $("#btn_formula_" + sheet_index + "_" + column_index + " i").removeClass("bx-plus");
      $("#btn_formula_" + sheet_index + "_" + column_index + " i").addClass("bx-edit");
    } 

    formulaRow();   
  }
  
  function clearFormulaModal()
  {    
    $("#sheet_index").val('');
    $("#column_index").val('');
    $('#initial_column_or_value').val('');

    $('div[data-repeater-item=""]').slice(1).remove();

    $('.arithmetic').val('');
    $('.column').val('');
    $('.value').val('');
    $('.formula-column-value-div').hide();
    $('#end_formula').html('');

    formulaRow();
  }

  function formulaRow()
  {
    let row_has_formula = false;
    $('.formula-row td:not(:first-child)').each(function() {
      if($.trim($(this).find('span').html()) != '')
        row_has_formula = true;    
    });

    if(row_has_formula)
      $('.formula-row').show();
    else
      $('.formula-row').hide();
  }

  function fillFormulaModal(data)
  {    
    clearFormulaModal();

    $("#sheet_index").val(data.sheet_index);
    $("#column_index").val(data.column_index);

    $('#mapped_column').html('Column ' + data.column_header);

    restrictColumnOptions();

    var end_formula = $("td#formula_" + data.sheet_index + "_" + data.column_index + " span").html();
    if($.trim(end_formula) === '')
    {      
    }
    else
    {      console.log(end_formula);      
      let arr_end_formula = end_formula.split(' ');
      console.log(arr_end_formula);

      let row = 0;
      let row_added = false;
      $.each(arr_end_formula, function(index, value) {
        if(index === 0)
        {
          var newValue = value.replace(/[()]/g, '');

          if(!/^[A-Z]$/.test(newValue))
          {
            $('#initial_column_or_value').val(newValue);
            row_added = true;
          }
        }
        else
        {
          var newValue = value.replace(/[()]/g, '');

          if(row > 0 && !row_added && (/[+\-/*]/.test(newValue)))
          { 
            var $repeaterList = $('.form-formula-repeater [data-repeater-list]');
            var $firstItem = $repeaterList.find('[data-repeater-item]').first();
            var $clone = $firstItem.clone();
          
            // Get current number of items to generate new index
            var newIndex = $repeaterList.find('[data-repeater-item]').length;

            // Rename inputs/selects inside the clone
            $clone.find('.form-control, .form-select').each(function(i) {
              // Rename the 'name' attribute, e.g. formulas[0][value] => formulas[1][value]
              var oldName = $(this).attr('name');
              if (oldName) {
                var newName = oldName.replace(/\[\d+\]/, '[' + newIndex + ']');
                $(this).attr('name', newName);
              }

              // Rename the 'id' attribute, e.g. form-formula-repeater-0-0 => form-formula-repeater-1-0
              var oldId = $(this).attr('id');
              if (oldId) {
                var newId = oldId.replace(/-\d+-/, '-' + newIndex + '-');
                $(this).attr('id', newId);
              }
            });
           
            $repeaterList.append($clone);

            row_added = true;
          }

          
          if (/[+\-/*]/.test(newValue) && !/<span>/i.test(newValue))
          {            
            $('#form-formula-repeater-' + row + '-1').val(newValue);
          }
          else if(/^[A-Z]$/.test(newValue))
          {            
            $('#form-formula-repeater-' + row + '-2').val(newValue);

            $('#form-formula-repeater-' + row + '-3').val('');
            $('#form-formula-repeater-' + row + '-3').parent('div.formula-column-value-div').hide();

            row++;

            row_added = false;
          }
          else
          {
            if(/<span>/i.test(newValue))            
              newValue = newValue.replace(/<\/?span[^>]*>/g, '');
            
            $('#form-formula-repeater-' + row + '-2').val('type_value');

            $('#form-formula-repeater-' + row + '-3').val(newValue);
            $('#form-formula-repeater-' + row + '-3').parent('div.formula-column-value-div').show();

            row++;

            row_added = false;
          }
        }      
      });
    }
    endFormula();
    $('#formulaModal').modal('show');
  }
     
  function restrictColumnOptions()
  {    
    var data = $(".btn-formula").data();

    const cutoff = data.last_column.toUpperCase();

    $('.column option').each(function () {
        const value = $(this).val();

        // Skip empty/default option
        if (value && value > cutoff  && value != 'type_value') {
            $(this).prop('disabled', true);
        }
    });
  }  

  var formFormulaRepeater = $('.form-formula-repeater');

  if (formFormulaRepeater.length) {
    var row = 1;//2;
    //var col = 1;
    formFormulaRepeater.on('submit', function (e) {
      e.preventDefault();
    });
    formFormulaRepeater.repeater({
      show: function () {
        var fromControl = $(this).find('.form-control, .form-select');
        var formLabel = $(this).find('.form-label');

        var col = 1;
        fromControl.each(function (i) {
          var id = 'form-formula-repeater-' + row + '-' + col;
          $(fromControl[i]).attr('id', id);
          $(formLabel[i]).attr('for', id);
          col++;
        });

        row++;

        restrictColumnOptions();

        $(this).slideDown();
      },
      hide: function (e) {
        //confirm('Are you sure you want to delete this row?') && $(this).slideUp(e);

        var $item = $(this);
        $item.slideUp(function () {
          $item.remove();
          endFormula();
        });
      }
    });
  }

  function endFormula()
  {
    var prefix = '';
    var suffix = ')';
    let suffix_count = 0;

    var end_formula = ($('#initial_column_or_value').val())  ? $('#initial_column_or_value').val() : $('#mapped_column').html().replace('Column ', '');

    $('.formula-row select').each(function () {
      var input_value = $(this).val();
      
      if(input_value != '')
      {
        if(!/[+\-/*]/.test(input_value))
        {
          if(input_value === 'type_value')     
            input_value = '<span>' +  $(this).parent().next('.formula-column-value-div').find('input.value').val() + '</span>';

            input_value = input_value + suffix;      
          suffix_count++;
        }
        
        end_formula += ' ' + input_value;
      }
    });
 
    for (let i = 0; i < suffix_count; i++)
      prefix += '(';
    
    $('#end_formula').html(prefix + end_formula);
  }

  // Delete anyexcel-template 
  $(document).on('click', '.delete-anyexcel-template', function () {
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
          url: `${anyexcelTemplateUrl}${template_id}`,
          success: function (result) {
                        
            // var dt_anyexcel_template = $.fn.dataTable.isDataTable('.datatables-anyexceltemplate') 
            //   ? $('.datatables-anyexceltemplate').DataTable() 
            //   : $('.datatables-anyexceltemplate').DataTable({ /* your init options */ });

            // if (dt_anyexcel_template.rows().any())
            // {
            //   anyexcel_template_datas = drawDtTable(result, 'anyexceltemplate');
            //   dt_anyexcel_template.clear().rows.add(anyexcel_template_datas).draw();
            // }  

            // if($('.datatables-anyexceltemplate').length > 0)
            // {
            //   if ($.fn.DataTable.isDataTable('.datatables-anyexceltemplate'))
            //   {  
            //     var dt_anyexcel_template = $('.datatables-anyexceltemplate').DataTable(); // safely get it
          
            //     if (dt_anyexcel_template.rows().any())
            //     {              
            //       anyexcel_template_datas = drawDtTable(result, 'anyexceltemplate');
            //       dt_anyexcel_template.clear().rows.add(anyexcel_template_datas).draw();
            //     }
            //   }
            // }

            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The template has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then(function (result) {
              window.location.reload();
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

  //Scrollbar
  //document.addEventListener('DOMContentLoaded', function () {
    

    function setupScrollSync() {
      const previewContainer = document.querySelector('#excel-preview .anyexcel-template-preview-tab');
      const topScroll = previewContainer.querySelector('.scroll-top');
      const bottomContainer = previewContainer.querySelector('.scroll-bottom');

      // Find active tab content
      const activePane = previewContainer.querySelector('.tab-pane.active');//, .tab-pane.show
      const activeTable = activePane ? activePane.querySelector('table') : null;

      if (!activeTable || !topScroll || !bottomContainer) return;

      // Wait a micro delay for DOM to paint after tab becomes visible
      setTimeout(() => {
        const scrollWidth = activeTable.scrollWidth;
        const clientWidth = previewContainer.clientWidth;

        //const outerWidth = document.querySelector("#file-upload-mapping-validation").clientWidth;

        //console.log('Active Pane:', activePane);
        //console.log('Active table:', activeTable);
        //console.log('Width check:', scrollWidth, '>', clientWidth);

        const hasHorizontalScroll = scrollWidth > clientWidth;

        if (!hasHorizontalScroll) {console.log("not true");
          topScroll.style.display = 'none';
          return;
        }

        topScroll.style.display = 'block';
        topScroll.innerHTML = '';

        const fake = document.createElement('div');
        fake.style.width = scrollWidth + 'px';
        fake.style.height = '1px';
        topScroll.appendChild(fake);

        // Sync scrollbars
        topScroll.onscroll = () => (bottomContainer.scrollLeft = topScroll.scrollLeft);
        bottomContainer.onscroll = () => (topScroll.scrollLeft = bottomContainer.scrollLeft);

        // Reset scroll
        topScroll.scrollLeft = bottomContainer.scrollLeft = 0;
      }, 100); // ← Small delay ensures rendering finished (50–100 ms usually enough)
    }

    // ✅ Listen globally for Bootstrap tab activation
    document.addEventListener('shown.bs.tab', function (event) {
      //console.log('Tab activated:', event.target);
      setupScrollSync();
    });

    // Run once on load (for first visible tab)
    //setupScrollSync();

    // Optional: Update on window resize
    window.addEventListener('resize', setupScrollSync);
  //});







  // const topScroll = document.querySelector('#excel-preview .anyexcel-template-preview-tab .scroll-top');
  // const bottomContainer = document.querySelector('#excel-preview .anyexcel-template-preview-tab .scroll-bottom');

  // function setupScrollSync() {
  //   requestAnimationFrame(() => {
  //     const activeContent = document.querySelector('#excel-preview .anyexcel-template-preview-tab .tab-pane.active table') ||
  //                           document.querySelector('#excel-preview .anyexcel-template-preview-tab .tab-pane.show table') ||
  //                           document.querySelector('#excel-preview .anyexcel-template-preview-tab .tab-pane table');
  //     //if (!activeContent) return;
  //     if (!activeContent || !topScroll || !bottomContainer) return;

  //     // Check if there is horizontal scroll
  //     const hasHorizontalScroll = activeContent.scrollWidth > activeContent.clientWidth;

  //     if (!hasHorizontalScroll) {
  //       // Hide top scrollbar if not needed
  //       topScroll.style.display = 'none';
  //       return;
  //     } else {
  //       // Show top scrollbar if needed
  //       topScroll.style.display = 'block';
  //     }

  //     // Create fake scrollbar element with the same width
  //     topScroll.innerHTML = '';
  //     const fake = document.createElement('div');
  //     fake.style.width = activeContent.scrollWidth + 'px';
  //     fake.style.height = '1px';
  //     topScroll.appendChild(fake);

  //     // Sync positions
  //     topScroll.onscroll = () => bottomContainer.scrollLeft = topScroll.scrollLeft;
  //     bottomContainer.onscroll = () => topScroll.scrollLeft = bottomContainer.scrollLeft;

  //     // Reset scroll
  //     topScroll.scrollLeft = bottomContainer.scrollLeft = 0;
  //   });
  // }

  // // Attach only if tabs exist
  // const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
  // if (tabLinks.length > 0) {
  //   tabLinks.forEach(tab => {
  //     tab.addEventListener('shown.bs.tab', setupScrollSync);
  //   });
  // }

  // // Always initialize on load
  // setupScrollSync();

  //Scrollbar
});//();  