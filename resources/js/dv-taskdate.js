/**
 * Reminder
 */

'use strict';

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
  var taskdatesUrl = baseUrl + 'taskdates/',  
      dt_taskdates_table = $('.datatables-taskdate'),
       offCanvasForm = $('#offcanvasAddTaskDate'); 

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  // Task Dates datatable
  if (dt_taskdates_table.length) {
    var dt_taskdate = dt_taskdates_table.DataTable({
      data: taskdate_datas,        
      processing: true,  
      autoWidth: false,   
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'taskname' },       
        { data: 'task_date' },                  
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
          // Task Name
          targets: 1,          
          width: "20%",   
          render: function (data, type, full, meta) {
            var $taskname = full['taskname'];
            
            return '<span>' + $taskname + '</span>';
          }
        },                
        {
          // Task Date/Desc
          targets: 2,                   
          render: function (data, type, full, meta) {                  
            var $task_value = "";  
            var currdate = moment().format("YYYY-MM");                     
            if(full['task_date'] != 0)
            {
              if(full['task_date'] == 30 || full['task_date'] == 31)              
                $task_value = "Last day of month";               
              else              
                $task_value = moment(currdate + '-' + full['task_date']).format('Do') + " of every month";              
            }                                                                 
            else            
              $task_value = full['task_description'];

            return '<span>' + $task_value + '</span>';
          }
        },        
        {
          // Actions
          targets: -1,
          //targets: 8,
          width: "15%",
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            var buttons = "";

            buttons +='<button class="btn btn-sm btn-icon edit-taskdate" data-id="'+full['id']+'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddTaskDate" title="Edit"><i class="bx bx-edit"></i></button>' +
                '<button class="btn btn-sm btn-icon delete-taskdate" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';

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
                columns: [1, 2],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('taskname')) {
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
                columns: [1, 2],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('taskname')) {
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
                columns: [1, 2],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('taskname')) {
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
                columns: [1, 2],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('taskname')) {
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
                columns: [1, 2],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('taskname')) {
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
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Create Task Date</span>',
          className: 'add-new-taskdate btn btn-primary',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddTaskDate'
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['taskname'];
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
        $("#taskdate-card").show();        
      }
    });
  }
  
  // Delete taskdate 
  $(document).on('click', '.delete-taskdate', function () {
    var taskdate_id = $(this).data('id'),
      dtrModal = $('.offcanvas.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

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
          url: `${baseUrl}taskdates\/${taskdate_id}`,
          success: function (result) {            
            taskdate_datas = drawDtTable(result, 'taskdate');
            dt_taskdate.clear().rows.add(taskdate_datas).draw();
            
          },
          error: function (error) {
            console.log(error);
          }
        });
       
        // success sweetalert
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The task date has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The task date is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });
  // Delete taskdate

  // edit taskdate
  $(document).on('click', '.edit-taskdate', function () {
    var taskdate_id = $(this).data('id'),
      dtrModal = $('.offcanvas.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }
   
    // changing the title of offcanvas
    $('#offcanvasTaskNameLabel').html('Edit Task Date');    

    // get data
    $.get(`${baseUrl}taskdates\/${taskdate_id}\/edit`, function (data) {    
      var taskdates = data.taskdate;
      
      $('#taskdate_id').val(taskdates.id); 
      $('#task_name').val(taskdates.task_name); 

      if(taskdates.task_date !=0)
      {              
        var currdate = moment().format('YYYY-MM');
        var contaskdate = moment(currdate+'-'+ taskdates.task_date).format('YYYY-MM-DD');

        $('#task_date').val(contaskdate);
        $('#task_description').val("");

        $('#lbl_taskdate').show(); 
        $('#task_date').show();

        $('#lbl_taskdescription').hide();
        $('#task_description').hide();
      }
      else
      {
        $('#task_date').val(0);            
        $('#task_description').val(taskdates.task_description);   
        $('#lbl_taskdate').hide(); 
        $('#task_date').hide();
        $('#lbl_taskdescription').show();
        $('#task_description').show();
      }
           
      // var currdate = moment().format('YYYY-MM');
      // var contaskdate = moment(currdate+'-'+ taskdates.task_date).format('YYYY-MM-DD');
     
      // $('#taskdate_id').val(taskdates.id);     
      // $('#task_date').val(contaskdate);      
      // $('#task_name').val(taskdates.task_name); 
      // $('#task_description').val(taskdates.task_description);    

      $('#task_name').attr('disabled', 'disabled');          
    });// get data
 
  }); // edit taskdate

  // changing the title
  $('.add-new-taskdate').on('click', function () {
    clearTaskdateForm();
  });

  // Show/Hide textbox / Datepicker on dropdown select
  $('#task_name').on('change', function () {                  
    if ($("#task_name").val() == "VAT Reg. Folder" || $("#task_name").val() == "Client View" || 
        $("#task_name").val() == "Api Scheduler" || $("#task_name").val() == "Exchange Rate" || 
        $("#task_name").val() == "Reminder Scheduler")   
    {
      $('#lbl_taskdate').hide(); 
      $('#task_date').hide();
      $('#lbl_taskdescription').show();
      $('#task_description').show();
    }
    else
    {
      $('#lbl_taskdate').show(); 
      $('#task_date').show();
      $('#lbl_taskdescription').hide();
      $('#task_description').hide();
    }
  });
   // end Show/Hide textbox / Datepicker on dropdown select

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  // Validation 
  const 
    addNewTaskDateForm = document.getElementById('addNewTaskDateForm');
  
  // Add New Task Date Form Validation
  const fv = FormValidation.formValidation(addNewTaskDateForm, {
    fields: {
      taskname: {
        validators: {
          notEmpty: {            
            message: 'Please select task name'
          }
        }
      },
      task_date: {
        validators: {
          notEmpty: {            
            //enabled: ($('#addNewTaskDateForm [name="task_name"]').val() == 'VAT Reg. Folder' || $('#addNewTaskDateForm [name="task_name"]').val() == 'Client View' || $('#addNewTaskDateForm [name="task_name"]').val() == "Api Scheduler" || $('#addNewTaskDateForm [name="task_name"]').val() == "Exchange Rate" || $('#addNewTaskDateForm [name="task_name"]').val() == "Reminder Scheduler") ? false : true,            
            enabled: ($('#addNewTaskDateForm [name="task_name"]').val() != 'VAT Reg. Folder' && $('#addNewTaskDateForm [name="task_name"]').val() != 'Client View'  && $('#addNewTaskDateForm [name="task_name"]').val() != "Api Scheduler" && $('#addNewTaskDateForm [name="task_name"]').val() != "Exchange Rate" && $('#addNewTaskDateForm [name="task_name"]').val() != "Reminder Scheduler") ? false : true, 
            message: 'Please enter task date'
          }
        }
      },
      task_description: {
        validators: {
          notEmpty: {            
            //enabled: ($('#addNewTaskDateForm [name="task_name"]').val() != 'VAT Reg. Folder' && $('#addNewTaskDateForm [name="task_name"]').val() != 'Client View'  && $('#addNewTaskDateForm [name="task_name"]').val() != "Api Scheduler" && $('#addNewTaskDateForm [name="task_name"]').val() != "Exchange Rate" && $('#addNewTaskDateForm [name="task_name"]').val() != "Reminder Scheduler") ? false : true, 
            enabled: ($('#addNewTaskDateForm [name="task_name"]').val() == 'VAT Reg. Folder' || $('#addNewTaskDateForm [name="task_name"]').val() == 'Client View' || $('#addNewTaskDateForm [name="task_name"]').val() == "Api Scheduler" || $('#addNewTaskDateForm [name="task_name"]').val() == "Exchange Rate" || $('#addNewTaskDateForm [name="task_name"]').val() == "Reminder Scheduler") ? false : true,            
            message: 'Please enter task description'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.mb-3';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
    }).on('core.form.valid', function () {
     
      $("#addNewTaskDateForm").find('button.data-submit').html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Submitting...');

    // adding or updating user when form successfully validate
    $.ajax({
      data: $('#addNewTaskDateForm').serialize(),
      url: `${baseUrl}taskdates`,
      type: 'POST',
      success: function (result) {
    
        var status = result['message'];
        taskdate_datas = drawDtTable(result, 'taskdate');
        dt_taskdate.clear().rows.add(taskdate_datas).draw();
        
        offCanvasForm.offcanvas('hide');
        
        $("#addNewTaskDateForm").find('button.data-submit').html('Submit');

        if(result['status'] == 200)
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Taskdate ${status} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });        
      },
      error: function (result) {
             
        var err = result['message'];
        taskdate_datas = drawDtTable(result, 'taskdate');
        dt_taskdate.clear().rows.add(taskdate_datas).draw();
        
        offCanvasForm.offcanvas('hide');

        $("#addNewTaskDateForm").find('button.data-submit').html('Submit');
      
      }
    });
  });
  
  addNewTaskDateForm.querySelector('[name="task_name"]').addEventListener('change', function (e) {
      // Enable or disable validators for the `task date` field            
      //(e.target.value == 'VAT Reg. Folder' || e.target.value == 'Client View'  || e.target.value == "Api Scheduler" || e.target.value == "Exchange Rate" || e.target.value == "Reminder Scheduler") ? fv.disableValidator('task_date') : fv.enableValidator('task_date');
      (e.target.value != 'VAT Reg. Folder' && e.target.value != 'Client View'  && e.target.value != "Api Scheduler" && e.target.value != "Exchange Rate" && e.target.value != "Reminder Scheduler") ? fv.disableValidator('task_description') : fv.enableValidator('task_description');

      // Revalidate the `task date` field
      fv.revalidateField('task_date');

      // Enable or disable validators for the `task desc` field            
      //(e.target.value != 'VAT Reg. Folder' && e.target.value != 'Client View'  && e.target.value != "Api Scheduler" && e.target.value != "Exchange Rate" && e.target.value != "Reminder Scheduler") ? fv.disableValidator('task_description') : fv.enableValidator('task_description');
      (e.target.value == 'VAT Reg. Folder' || e.target.value == 'Client View'  || e.target.value == "Api Scheduler" || e.target.value == "Exchange Rate" || e.target.value == "Reminder Scheduler") ? fv.disableValidator('task_date') : fv.enableValidator('task_date');

      // Revalidate the `task desc` field
      fv.revalidateField('task_description');
  });

  //  // clearing form data when offcanvas hidden
  // offCanvasForm.on('hidden.bs.offcanvas', function () {
  //   console.log(fv);
  //   fv.resetForm(true);
  // });

  function clearTaskdateForm()
  {   
    $('#offcanvasTaskNameLabel').html('Add Task Date');   

    $('#taskdate_id').val("");        
    $('#task_date').val("");   
    $('#task_name').val("");
    $('#task_description').val("");

    $('#task_name').removeAttr('disabled');     

    $('#lbl_taskdate').show(); 
    $('#task_date').show();
    $('#lbl_taskdescription').hide();
    $('#task_description').hide();    
  }

  // Date    
  if ($("#task_date").length) {
    $("#task_date").flatpickr({   
      dateFormat: 'Y-m-d'
    });      
  }

});//();  