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
  
  var reminderQuill = null;
  var dkReminderQuill = null;
  // Full Toolbar
  // --------------------------------------------------------------------
  const fullToolbar = [
    [
      {
        font: []
      },
      {
        size: []
      }
    ],
    ['bold', 'italic', 'underline', 'strike'],
    [
      {
        color: []
      },
      {
        background: []
      }
    ],
    [
      {
        script: 'super'
      },
      {
        script: 'sub'
      }
    ],
    [
      {
        header: '1'
      },
      {
        header: '2'
      },
      'blockquote',
      'code-block'
    ],
    [
      {
        list: 'ordered'
      },
      {
        list: 'bullet'
      },
      {
        indent: '-1'
      },
      {
        indent: '+1'
      }
    ],
    // [{ direction: 'rtl' }],
    // ['link', 'image', 'video', 'formula'],
    // ['clean']
  ];

  // Variable declaration  
  var reminderUrl = baseUrl + 'reminder/',      
      dt_reminder_table = $('.datatables-reminder'),
      dt_reminder_history_table = $('.datatables-reminder-history');
   //var company_ids = 0;
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  // Reminder datatable
  if (dt_reminder_table.length) {
    var dt_reminder = dt_reminder_table.DataTable({
      data: reminder_datas,        
      processing: true,  
      autoWidth: false,   
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'title' },
        { data: 'reminder_action' },
        { data: 'schedule' },
        { data: 'start_at' },
        { data: 'user' },        
        { data: 'vat_reg_main' },
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
        // {
        //   // ID
        //   targets: 1,
        //   //responsivePriority: 4,
        //   width: "5%",   
        //   render: function (data, type, full, meta) {
        //     var $fake_id = full['fake_id'];
            
        //     return '<span>' + $fake_id + '</span>';
        //   }
        // },
        {
          // Title
          targets: 1,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $title = full['title'];
            
            return '<span>' + $title + '</span>';
          }
        },
        {
          // Reminder action
          targets: 2,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $reminder_action = full['reminder_action'];
            
            return '<span>' + $reminder_action + '</span>';
          }
        },
        {
          // Schedule
          targets: 3,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $schedule = full['schedule'];
            
            return '<span>' + $schedule + '</span>';
          }
        },
        {
          // Start At
          targets: 4,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $start_at = full['start_at'];
            
            return '<span>' + $start_at + '</span>';
          }
        },
        {
          // User name and email
          targets: 5,
          //responsivePriority: 4,
          width: "20%",   
          render: function (data, type, full, meta) {
            var $users = full['users'];

            var $row_output = "";  
            $.each($users, function (idx, user) {   
              var $name = user['name'],
                  $email = user['email'];
              
              // Creates full output for row
              $row_output +=
                '<div class="d-flex justify-content-start align-items-center user-name">' +                
                '<div class="d-flex flex-column">' +
                '<a href="#' +               
                '" class="text-body text-truncate"><span class="fw-semibold">' +
                $name +
                '</span></a>' +               
                '<small class="text-muted">' +
                $email +
                '</small>' +
                '</div>' +
                '</div>';
            });
            return $row_output;
          }
        },
        {
          // Vat Reg.
          targets: 6,
          //responsivePriority: 4,
          width: "20%",   
          render: function (data, type, full, meta) {
            var $vatregmain = full['vatregmain'],
                $client = full['client']              
              ;
             //console.log($vatregmain); 
             //console.log($client);         
            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +              
              '<div class="d-flex flex-column">' +
              '<a href="#' +             
              '" class="text-body text-truncate"><span class="fw-semibold">' +
              $client +
              '</span></a>' +            
              '<small class="text-muted">' +
              $vatregmain +
              '</small>' +
              '</div>' +
              '</div>';
            
            return $row_output;
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

            buttons +='<button class="btn btn-sm btn-icon edit-reminder" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#reminderModal" title="Edit"><i class="bx bx-edit"></i></button>' +
                '<button class="btn btn-sm btn-icon delete-reminder" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';

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
                columns: [1, 2, 3, 4, 5, 6],
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
                columns: [1, 2, 3, 4, 5, 6],
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
                columns: [1, 2, 3, 4, 5, 6],
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
                columns: [1, 2, 3, 4, 5, 6],
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
                columns: [1, 2, 3, 4, 5, 6],
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
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Create Reminder</span>',
          className: 'add-new-reminder btn btn-primary',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#reminderModal'
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
        $("#reminder-card").show();        
      }
    });
  }

  // Reminder history datatable
  if (dt_reminder_history_table.length) {
    var dt_reminder_history = dt_reminder_history_table.DataTable({
      data: reminder_history_datas,        
      processing: true,   
      autoWidth: false,  
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'title' },
        // { data: 'reminder_action' },
        // { data: 'schedule' },
        { data: 'start_at' },
        { data: 'users' },        
        { data: 'vat_reg_main' },
        { data: 'histories' }       
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
        // {
        //   // ID
        //   targets: 0,
        //   //responsivePriority: 4,
        //   width: "5%",   
        //   render: function (data, type, full, meta) {
        //     var $fake_id = full['fake_id'];
            
        //     return '<span>' + $fake_id + '</span>';
        //   }
        // },
        {
          // Title
          targets: 1,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $title = full['title'];
            
            return '<span>' + $title + '</span>';
          }
        },
        {
          // Start At
          targets: 2,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $start_at = full['start_at'];
            
            return '<span>' + $start_at + '</span>';
          }
        },
        {
          // User name and email
          targets: 3,
          //responsivePriority: 4,
          width: "20%",   
          render: function (data, type, full, meta) {
            var $users = full['users'];

            var $row_output = "";  
            $.each($users, function (idx, user) {   
              var $name = user['name'],
                  $email = user['email'];
              
              // Creates full output for row
              $row_output +=
                '<div class="d-flex justify-content-start align-items-center user-name">' +                
                '<div class="d-flex flex-column">' +
                '<a href="#' +               
                '" class="text-body text-truncate"><span class="fw-semibold">' +
                $name +
                '</span></a>' +               
                '<small class="text-muted">' +
                $email +
                '</small>' +
                '</div>' +
                '</div>';
            });
            return $row_output;
          }
        },
        {
          // Vat Reg.
          targets: 4,
          //responsivePriority: 4,
          width: "20%",   
          render: function (data, type, full, meta) {
            var $vatregmain = full['vatregmain'],
                $client = full['client']              
              ;
                      
            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +              
              '<div class="d-flex flex-column">' +
              '<a href="#' +             
              '" class="text-body text-truncate"><span class="fw-semibold">' +
              $client +
              '</span></a>' +            
              '<small class="text-muted">' +
              $vatregmain +
              '</small>' +
              '</div>' +
              '</div>';
            
            return $row_output;
          }
        },  
        {
          // Sent at
          targets: 5,
          //responsivePriority: 4,
          width: "20%",   
          render: function (data, type, full, meta) {
            var $histories = full['histories']       
              ;
                      
            var $row_output = '<div class="d-flex justify-content-start align-items-center user-name">' +                
                '<div class="d-flex flex-column">'
                ;
            $.each($histories, function (idx, history) {   
              var $sent_at = history['sent_at'];
              
              // Creates full output for row
              $row_output +=
                //'<div class="d-flex justify-content-start align-items-center user-name">' +                
                //'<div class="d-flex flex-column">' +
                '<a href="#' +               
                '" class="text-body text-truncate"><span>' +
                $sent_at +
                '</span></a>'           
                // '<small class="text-muted">' +
                // $email +
                // '</small>' +
                //'</div>' +
                //'</div>'
                ;
            });

            $row_output +=               
                '</div>' +
                '</div>';
            return $row_output;
          }
        }        
        // {
        //   // Actions
        //   //targets: -1,
        //   targets: 7,
        //   width: "15%",
        //   title: 'Actions',
        //   searchable: false,
        //   orderable: false,
        //   render: function (data, type, full, meta) {
        //     var buttons = "";

        //     buttons +='<button class="btn btn-sm btn-icon edit-reminder" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#reminderModal" title="Edit"><i class="bx bx-edit"></i></button>' +
        //         '<button class="btn btn-sm btn-icon delete-reminder" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';

        //     return (             
        //       buttons              
        //     );
        //   }
        // }
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
                columns: [0, 1, 2, 3, 4, 5],
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
                columns: [0, 1, 2, 3, 4, 5],
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
                columns: [0, 1, 2, 3, 4, 5],
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
                columns: [0, 1, 2, 3, 4, 5],
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
                columns: [0, 1, 2, 3, 4, 5],
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
        }
        // {
        //   text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Create Reminder</span>',
        //   className: 'add-new-reminder btn btn-primary',
        //   attr: {
        //     'data-bs-toggle': 'modal',
        //     'data-bs-target': '#reminderModal'
        //   }
        // }
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
        $("#reminder-history-card").show();        
      }
    });
  }

  // changing the title
  $('.add-new-reminder').on('click', function () {    
    clearReminderForm();
    validateReminderForm();
  });

  // edit reminder
  $(document).on('click', '.edit-reminder', function () {
    var reminder_id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // changing the title of offcanvas
    $('#modalLabel').html('Edit Reminder');    

    // get data
    $.get(`${reminderUrl}${reminder_id}\/edit`, function (data) {    //console.log(data);
      var reminder = data.reminder;

      var reminderusers = reminder.reminderuser;    //console.log(reminderusers);  
      var reminderactionoption = reminder.reminderactionoption;
     
      var vatregmain = ""; 
      var vat_reg_main_id = "";
      var country = ""; //console.log(reminder.vatregmain);
      //var company = "";
       // var company_data ="";
       // var company_client_id = "";

      if(reminder.vatregmain != null)
        vatregmain = reminder.vatregmain;
      if(reminder.vat_reg_main_id != null)
        vat_reg_main_id = reminder.vat_reg_main_id;
        //console.log(vat_reg_main_id);
      // if(vatregmain.country != null)
      //   country = vatregmain.country;
      if(vat_reg_main_id == "")
            vat_reg_main_id = 0;

    // console.log(country);
       var edit_client_id = "";
       if(vatregmain.client_id !=null)
          edit_client_id = vatregmain.client_id; //console.log(edit_client_id);
        if(edit_client_id == "")
            edit_client_id = 0;
      var reminder_id = reminder.id;

      //Load Editor
      //reminderEditor();
      //End

      $('#reminder_id').val(reminder.id);
      $('.formReminder').attr('action', `${reminderUrl}${reminder_id}`);

      //var user_role = (reminderusers.length > 0) ? (reminderusers[0]['user']['roles'][0]['name']) : '';
      var user_role = reminder.reminder_role; //console.log(user_role);
      $('#user_role').val(user_role);

      country = reminder.reminder_country; //console.log(user_role);     
      $('#country').val(country);
      $('#country').removeAttr("disabled");

      /*-- Load Reminder Action based on user role*/
        var user_role_selected = $('#user_role').find('option:selected');
        var user_role_value = user_role_selected.val();               
        var edit_reminder_action_id = reminder.action_id; 
        //console.log(edit_reminder_action_id);
           loadReminderActions(user_role_value, edit_reminder_action_id,edit_client_id); 
          $('#reminder_action').removeAttr("disabled");
      /*--end Load Reminder Action based on user role*/ 

      /*-- Load Companies for reminders*/
        if($('#user_role').val() == "reminder")                
         loadReminderCompanies(country,null,edit_client_id);        
        else              
          loadAllReminderCompanies(country,null,edit_client_id);       
        $('#company').removeAttr("disabled");
    /*--end Load Companies for reminders*/
      /*-- Load Users for reminders*/             
           
             //console.log(vat_reg_main_id);
             //console.log(edit_client_id);  
              loadReminderUsers(vat_reg_main_id, edit_client_id, reminderusers, 'edit');
          /*--end Load Users for reminders*/
     

      //$('#reminder_datetime').val(moment(reminder.start_at).format('Y-m-d H:i'));
      $('#reminder_datetime').val(reminder.start_at);
      //$('#reminder_datetime').removeAttr("disabled");
      $('#reminder_datetime').attr("disabled", "disabled");

      $('#schedule').val(reminder.schedule);
      //$('#schedule').removeAttr("disabled");
      
      $('#period').val(reminder.period);
      $('#period').removeAttr("disabled");

      $('#language').val("en");
      $('#language').removeAttr("disabled");

      $('#title').val(reminder.title);
      $('#title').removeAttr("disabled");

      $('#dk_title').val(reminder.dk_title);
      $('#dk_title').removeAttr("disabled");

      //$('#reminder-content-quill').val(reminder.content);
      //$('.reminder-editor').html(reminder.content);
      $(".reminder-editor").find(".ql-editor").html(reminder.content);
      reminderQuill.enable(true);
      // $('#user-status').val(dvuser.status);  
      // $('#user-role').val(role.name);
      
      // $('#user-role').attr('disabled', 'disabled');  
      // $('#user-email').attr('readonly', 'true');  

      $(".dk-reminder-editor").find(".ql-editor").html(reminder.dk_content);
      dkReminderQuill.enable(true);

      $('#btn-create-reminder').removeClass('disabled');  
      $('#btn-create-reminder').removeClass('btn-danger');  
      $('#btn-create-reminder').addClass('btn-success');   
    });
  });  

  function clearReminderForm()
  {  
    $('#modalLabel').html('Create Reminder');

    $('#user_role').val("");
    $('#reminder_id').val("");       

    $('#country').val("");
    $('#country').attr("disabled","disabled");

    $('#company').html("");          
    $('#company').html('<option value="">No company found</option>');
    $('#company').attr("disabled","disabled");
    
    $("#reminder_to_users").html("");
    $('#reminder_client_users .send-to-reminder').prop('checked', false);
    $('#reminder_team_users .send-to-reminder').prop('checked', false); 
    $('#reminder_reminder_users .send-to-reminder').prop('checked', false);   

    //console.log('clear function');
    $('#reminder_action').val("");
    $('#reminder_action').attr("disabled","disabled");
    $("#email-template span").text("");

    $('#period').val("");
    $('#period').attr("disabled","disabled");
   
    $('#language').val("en");
    $('#language').attr("disabled","disabled");    
    
    //$('#reminder_datetime').val("");
    $('#reminder_datetime').attr("disabled","disabled");

    $('#schedule').val("");
    $('#schedule').attr("disabled","disabled");

    $('#title').val("");
    $('#title').attr("disabled","disabled");

    $('#dk_title').val("");
    $('#dk_title').attr("disabled","disabled");

    $(".reminder-editor").find(".ql-editor").html("");
    reminderQuill.enable(false);

    $(".dk-reminder-editor").find(".ql-editor").html("");
    dkReminderQuill.enable(false);
  }

  function validateReminderForm($which_method = null)
  {    
    var clientUserChecked = $('#reminder_client_users .send-to-reminder:checked').length;
    var teamUserChecked = $('#reminder_team_users .send-to-reminder:checked').length;
    var reminderUserChecked = $('#reminder_reminder_users .send-to-reminder:checked').length;

    var totalUserChecked = clientUserChecked + teamUserChecked + reminderUserChecked;

    selectuserCount();

   //console.log('validate func');
    //if($('#vat_reg_main').val() != "" && $('#reminder_action').val() != "" &&       
    if($('#user_role').val() != "" && $('#country').val() != "" && 
      $('#company').val() != "" && $('#reminder_action').val() != "" &&       
      //totalUserChecked > 0 && 
      $('#reminder_datetime').val() != "" && $('#schedule').val() != "" && 
      $('#period').val() != "" && $('#language').val() != "" && 
      $('#title').val() != "" && $('#dk_title').val() != "")
    {
      if(totalUserChecked == 0)
      {
        $("#btn-send-test-reminder").removeClass("disabled");
        $("#btn-send-test-reminder").removeClass("btn-danger");
        $("#btn-send-test-reminder").addClass("btn-success");

        $("#btn-create-reminder").removeClass("disabled");
        $("#btn-create-reminder").removeClass("btn-danger");
        $("#btn-create-reminder").addClass("btn-success");

        $("#btn-send-reminder").addClass("disabled");
        $("#btn-send-reminder").addClass("btn-danger");
        $("#btn-send-reminder").removeClass("btn-success");
      }
      else
      {          
        $("#btn-create-reminder").removeClass("disabled");
        $("#btn-create-reminder").removeClass("btn-danger");
        $("#btn-create-reminder").addClass("btn-success");

        $("#btn-send-reminder").removeClass("disabled");
        $("#btn-send-reminder").removeClass("btn-danger");
        $("#btn-send-reminder").addClass("btn-success");

        $("#btn-send-test-reminder").removeClass("disabled");
        $("#btn-send-test-reminder").removeClass("btn-danger");
        $("#btn-send-test-reminder").addClass("btn-success");       
      }
    }
    else
    {
      $("#btn-create-reminder").addClass("disabled");
      $("#btn-create-reminder").addClass("btn-danger");
      $("#btn-create-reminder").removeClass("btn-success");

      $("#btn-send-test-reminder").addClass("disabled");
      $("#btn-send-test-reminder").addClass("btn-danger");
      $("#btn-send-test-reminder").removeClass("btn-success");

      $("#btn-send-reminder").addClass("disabled");
      $("#btn-send-reminder").addClass("btn-danger");
      $("#btn-send-reminder").removeClass("btn-success");
    }
  }

  // User Role
  $(document).on('change', '#user_role', function () {  
      
    var selected_option = $('#country').find('option:selected');
    var country = selected_option.val();

    if($(this).val() == "")    
      $('#country').attr("disabled", "disabled");
    else if($(this).val() == "reminder")   
    { 
      $('#country').removeAttr("disabled");
      $('#company option[value=0]').prop('selected', true);                      
    }
    else   
      $('#country').removeAttr("disabled");     
   
    /*-- Load Companies for reminders*/   
    if($('#user_role').val() == "reminder")
      loadReminderCompanies(country,null);
    else
      loadAllReminderCompanies(country,null);
    /*--end Load Companies for reminders*/

    /*-- Load Reminder Action based on user role*/
    var user_role_selected = $('#user_role').find('option:selected');
    var user_role_value = user_role_selected.val();  

    loadReminderActions(user_role_value);      
    $("#reminder_action").val("");
    /*--end Load Reminder Action based on user role*/  

    validateReminderForm();       
  });

  // Country
  $(document).on('change', '#country', function () {//console.log('country on change');   
    var selected_option = $(this).find('option:selected');
    var country = selected_option.val();
    
    var company = $('#company option:selected');
    var data = company.data();
     
    var vat_reg_main_id = "";
    if(company.val()!=null)
      vat_reg_main_id = company.val();
    else
      vat_reg_main_id = 0;
      
    var client_id = "";
    if(data.client_id!=null)
      client_id = data.client_id; 
    else
      client_id = 0;
    
    if(selected_option.val() == "")       
      $('#reminder_action').attr("disabled", "disabled");   
    else   
      $('#reminder_action').removeAttr("disabled");     
    
    /*-- Load Companies for reminders*/
    if($('#user_role').val() == "reminder")
      loadReminderCompanies(country,null);
    else
      loadAllReminderCompanies(country,null);
    /*--end Load Companies for reminders*/

    /*-- Load Reminder Action based on user role*/
    var user_role_selected = $('#user_role').find('option:selected');
    var user_role_value = user_role_selected.val();       
    loadReminderActions(user_role_value);        
    $("#reminder_action").val("");
    /*--end Load Reminder Action based on user role*/

    $("#reminder_action option").prop("disabled", false);    
    if(country == 'NO')
    { 
      $("#reminder_action option[value=3]").prop("disabled", true); //Pivs not uploaded
      $("#reminder_action option[value=4]").prop("disabled", true); //Cash Account Statement not uploaded
    }
    else if(country == 'GB')   
      $("#reminder_action option[value=5]").prop("disabled", true); //Duty Deferment Account not uploaded
         
    loadReminderUsers(vat_reg_main_id, client_id);
    /*--end Load Users for reminders*/

    /*-- Load Periods for reminders*/
    loadReminderPeriods(country);
    /*--end Load Periods for reminders*/
  });  

  // Reminder Action
  $(document).on('change', '#reminder_action', function () {    
    validateReminderForm();
    
    if($("#reminder_action").find('option:selected').val() == "")
    {    
      $('#company').attr("disabled", "disabled");   
      $('#period').attr("disabled", "disabled");      
      $('#language').attr("disabled", "disabled");    
    }
    else
    {   
      var selected_action = $("#reminder_action").find('option:selected').html(); 
      $("#email-template span").text("Reminder: " + selected_action);    
      $('#company').removeAttr("disabled");
      $('#period').removeAttr("disabled");      
      $('#language').removeAttr("disabled");          

      $('#schedule').attr("disabled", "disabled");
      $('#reminder_datetime').attr("disabled", "disabled");

      var selected_option = $('#country').find('option:selected');
      var country = selected_option.val();

      var company = $('#company option:selected');
      var data = company.data();

      /*-- Load Companies for reminders*/
      if($('#user_role').val() == "reminder")
        loadReminderCompanies(country,null);        
      else
        loadAllReminderCompanies(country,null);
      /*--end Load Companies for reminders*/

      if($('#company').val() == "")
      {
        $('#title').attr("disabled", "disabled");
        reminderQuill.enable(false); 

        $('#dk_title').attr("disabled", "disabled");
        dkReminderQuill.enable(false);
      }
      else
      {
        $('#title').removeAttr("disabled");
        reminderQuill.enable(true);

        $('#dk_title').removeAttr("disabled");
        dkReminderQuill.enable(true);  
      }
      $('#schedule').val('Does not repeat');

      /*-- Load Users for reminders*/  
      var vat_reg_main_id = ""; 
      if( company.val()!=null)          
        vat_reg_main_id = company.val();
      else
        vat_reg_main_id = 0;

      var client_id = "";
      if(data.client_id!=null)
        client_id = data.client_id;  
      else
        client_id = 0; 
     
      loadReminderUsers(vat_reg_main_id, client_id);
      /*--end Load Users for reminders*/
    }
  });

  // Language
  $(document).on('change', '#language', function () {
    if($(this).val() == "en")
    {
      $('#title').show();
      $('#dk_title').hide();

      $('#reminder_content').show();
      $('#dk_reminder_content').hide();      
    }
    else
    {
      $('#title').hide();
      $('#dk_title').show();

      $('#reminder_content').hide();
      $('#dk_reminder_content').show();
    }
  });

  // Company
  $(document).on('change', '#company', function () {
    var selected_option = $(this).find('option:selected');
       
    if($('#company').val() == "")
    {
      $('#title').attr("disabled", "disabled");
      reminderQuill.enable(false); 

      $('#dk_title').attr("disabled", "disabled");
      dkReminderQuill.enable(false); 
    }
    else
    {
      $('#title').removeAttr("disabled");
      reminderQuill.enable(true); 

      $('#dk_title').removeAttr("disabled");
      dkReminderQuill.enable(true);  
    }

    $('#schedule').attr("disabled", "disabled");
    $('#reminder_datetime').attr("disabled", "disabled");   

    $('#schedule').val('Does not repeat');

    var data = selected_option.data();
    var country = data.country;
    var client_id = "";

    if(data.client_id!=null)
     client_id = data.client_id;
    else
      client_id = 0; 

   var vat_reg_main_id = "";
   if(selected_option.val()!=null)
     vat_reg_main_id = selected_option.val();  
   else
     vat_reg_main_id = 0;

    loadReminderUsers(vat_reg_main_id, client_id);
    /*--end Load Users for reminders*/
     
    
    $('#reminder_datetime').attr("disabled", "disabled");
   
    //validateReminderForm();

  });

  // Scheduler
$(document).on('change', '#schedule', function () {

    // if($("#schedule").find('option:selected').val() == "")  
    // {  
    //   $('#title').attr("disabled", "disabled");
    //    reminderQuill.enable(false); 
    // }
    // else
    // {
    //   $('#title').removeAttr("disabled");
    //   reminderQuill.enable(true);  
    // }
    
    validateReminderForm();   
  });
  // Input
  $(document).on('keypress', '#reminder_datetime, #title, #dk_title', function () {
    validateReminderForm();
  });

  $(document).on('change', '#reminder_datetime', function () {     // console.log('reminder_datetime on change');   
    // if($(this).val() == "")    
    //   $('#schedule').attr("disabled", "disabled");
    // else
    //   $('#schedule').removeAttr("disabled");    

    $('#schedule').attr("disabled", "disabled");
  });
// Tttle
  $(document).on('change', '#title', function () {    //console.log('title on change');     
    if($(this).val() == "")  
      reminderQuill.enable(false);      
    else
      reminderQuill.enable(true);  
  });

  // DK Tttle
  $(document).on('change', '#dk_title', function () {    //console.log('title on change');     
    if($(this).val() == "")  
      dkReminderQuill.enable(false);      
    else
      dkReminderQuill.enable(true);  
  });

 function loadReminderActions(user_role, action_id = null)
  {   //console.log('reminder action load');  
  //console.log(edit_client_id);
    $.ajax( {   
        url: `${reminderUrl}${user_role}/reminderactions`,      
        type: 'GET',
        success: function (result) { 
          var reminder_actions = result.reminder_actions;
         
          $('#reminder_action').html("");
          if(reminder_actions.length == 0)
          {                    
            $('#reminder_action').html('<option value="">No actions found</option>');
            $('#reminder_action').attr("disabled","disabled");
          }
          else
          {                 
            //var options = '<option value="" selected="selected">Select Action</option>';
            var options = '<option value="">Select Action</option>';             
            $.each(reminder_actions, function (index, reminder_action) {   
            var select_classname = ""; 
            if(action_id!=null)  
            {
              if(action_id == reminder_action['id']) 
              select_classname = 'selected="selected"';              
            }
              options += '<option value="'+reminder_action['id']+'"' + select_classname +'>'+reminder_action['action_name']+'</option>';
            });            
            
            $('#reminder_action').append(options);              
          } 
        //  var country = $('#country').val(); console.log(country);
        //      /*-- Load Companies for reminders*/
        // if($('#user_role').val() == "reminder")                
        //   loadReminderCompanies(country,null,edit_client_id);        
        // else              
        //   loadAllReminderCompanies(country,null,edit_client_id); 
        
       // else
          //$('#company').val(vat_reg_main_id);
          // console.log(x);
          // console.log($('#company').val());
          // console.log($('#company :selected'));
          //console.log($('#company :selected').val());
          //console.log($('#company :selected').data());
        //$('#company').removeAttr("disabled");
    /*--end Load Companies for reminders*/

        },
        error: function (err) {
          console.log(err);
        }
      });
  }

  function loadReminderCompanies(country, vat_reg_main_id = null, client_id = null)
  {    //console.log('comp load');
    if(country == "")
    {     
          $('#company').html("");          
          $('#company').html('<option value="">No company found</option>');
          $('#company').attr("disabled","disabled");       
    }
    else
    {

      $.ajax({   
        url: `${reminderUrl}${country}/companies`,      
        type: 'GET',
        success: function (result) { 
          var vat_reg_mains_active = result.vat_reg_mains_active;
          var vat_reg_mains_inactive = result.vat_reg_mains_inactive;         
          var options_active='';
          var options_inactive='';
          var options = '<option value="0" data-client_id="0">All</option>';

          $('#company').html("");

          if(vat_reg_mains_active.length == 0 && vat_reg_mains_inactive.length == 0)
          {                    
            $('#company').html('<option value="">No company found</option>');
            $('#company').attr("disabled","disabled");
          }
          else
          {       
            if(vat_reg_mains_active.length > 0)
            {
              $.each(vat_reg_mains_active, function (index, vat_reg_main_active) { 
                var company = vat_reg_main_active.client; 
                var select_classname = ""; 
                if(client_id!=null)  
                {
                  if(client_id==vat_reg_main_active['client_id']) 
                  select_classname = 'selected="selected"';              
                }                  
                options_active += '<option value="'+vat_reg_main_active['id']+'"' + select_classname +' data-client_id="'+vat_reg_main_active['client_id']+'" data-country="'+vat_reg_main_active['country']+'">'+ company['client_name'] +'</option>';
              });
            }  

            if(vat_reg_mains_inactive.length > 0)
            {
              $.each(vat_reg_mains_inactive, function (index, vat_reg_main_inactive) { 
                var inactive_company = vat_reg_main_inactive.client;        
                 var inact_select_classname = ""; 
                if(client_id!=null)  
                {
                  if(client_id == vat_reg_mains_active['client_id']) 
                  inact_select_classname = 'selected="selected"';              
                }                 
                options_inactive += '<option value="'+vat_reg_main_inactive['id']+'"' + inact_select_classname +' data-client_id="'+vat_reg_main_inactive['client_id']+'" data-country="'+vat_reg_main_inactive['country']+'">'+ inactive_company['client_name'] +'</option>';
              });
            }            
          }
                                          
          var option_active_group = '<optgroup label="Active Companies">';
          var option_inactive_group = '<optgroup label="Inactive Companies">';
          var optgroup_end = '</optgroup>';

          $('#company').append(options);

          if(options_active != "")
            $('#company').append(option_active_group + options_active + optgroup_end);

          if(options_inactive != "")
            $('#company').append(option_inactive_group + options_inactive + optgroup_end);
         
         // console.log($('#company').val());
         //  console.log($('#company :selected'));
          // console.log($('#company :selected').val());
          // console.log($('#company :selected').data());
          // if(vat_reg_mains_active.length == 0)
          // {                    
          //   $('#company').html('<option value="">No company found</option>');
          //   $('#company').attr("disabled","disabled");
          // }
          // else
          // {                 
          //    $.each(vat_reg_mains_active, function (index, vat_reg_mains_active) { 
          //     var company = vat_reg_mains_active.client;                   
          //     options_active += '<option value="'+vat_reg_mains_active['id']+'" data-client_id="'+vat_reg_mains_active['client_id']+'" data-country="'+vat_reg_mains_active['country']+'">'+ company['client_name'] +'</option>';
          //   });              
          // }

          // if(vat_reg_mains_inactive.length == 0)
          // {                    
          //   $('#company').html('<option value="">No company found</option>');
          //   $('#company').attr("disabled","disabled");
          // }
          // else
          // {   

          //   $.each(vat_reg_mains_inactive, function (index, vat_reg_mains_inactive) { 
          //     var inactive_company = vat_reg_mains_inactive.client;   
                
          //     options_inactive += '<option value="'+vat_reg_mains_inactive['id']+'" data-client_id="'+vat_reg_mains_inactive['client_id']+'" data-country="'+vat_reg_mains_inactive['country']+'">'+ inactive_company['client_name'] +'</option>';
          //   });            
          // }           
          //   var options = '<option value="0" data-client_id="0">All</option>';           
          //  var option_active_group = '<optgroup label="Active Companies">';
          //  var optgroup_end = '</optgroup>';

          //  $('#company').append(options);
          //  $('#company').append(option_active_group);
          //     $('#company').append(options_active);
          //  $('#company').append(optgroup_end);
          //  var option_inactive_group = '<optgroup label="Inactive Companies">';
          //  //var optgroup_end = '</optgroup>';

          //   $('#company').append(option_inactive_group);
          //     $('#company').append(options_inactive);
          //   $('#company').append(optgroup_end);    
        },
        error: function (err) {
          console.log(err);
        }
      });
    }
  }
  function loadAllReminderCompanies(country, vat_reg_main_id = null, client_id)
  {   //console.log(client_id); 


    if(country == "")
    {
      $('#company').html("");          
      $('#company').html('<option value="">No company found</option>');
      $('#company').attr("disabled","disabled");
    }
    else
    {

      $.ajax( {   
        url: `${reminderUrl}${country}/allcompanies`,      
        type: 'GET',
        success: function (result) {  
          var vat_reg_mains = result.vat_reg_mains;
         
          $('#company').html("");
          if(vat_reg_mains.length == 0)
          {                    
            $('#company').html('<option value="">No company found</option>');
            $('#company').attr("disabled","disabled");
          }
          else
          {                 
            var options = '<option value="">Select company</option>';            
            $.each(vat_reg_mains, function (index, vat_reg_main) { 
              var company = vat_reg_main.client;   
              var select_classname = ""; //console.log(client_id);console.log(vat_reg_main['client_id']);
                if(client_id!= null)  
                {
                  if(client_id == vat_reg_main['client_id']) 
                  select_classname = 'selected="selected"';              
                }                
              options += '<option value="'+vat_reg_main['id']+'"' + select_classname +' data-client_id="'+vat_reg_main['client_id']+'" data-country="'+vat_reg_main['country']+'">'+ company['client_name'] +'</option>';
            });            
            
            $('#company').append(options);           
            if(vat_reg_main_id != null)
              $('#company').val(vat_reg_main_id);


          }          
        },
        error: function (err) {
          console.log(err);
        }
      });
    }
  }
  
  function loadReminderPeriods(country)
  {    
    if(country == "")
      $('#period optgroup').removeClass('d-none');    
    else
    {
      if(country == "NO")
      {
        $('#period optgroup[label="NO"]').removeClass('d-none');
        $('#period optgroup[label="UK"]').addClass('d-none');
      }
      else if(country == "GB")
      {
        $('#period optgroup[label="UK"]').removeClass('d-none');
        $('#period optgroup[label="NO"]').addClass('d-none');
      }
      else
        $('#period optgroup').removeClass('d-none');
    }
  }

  function loadReminderUsers(vat_reg_main_id, client_id, reminderusers =  null, pageaction = null)
  {
    var reminder_send_to = "";
    $.each(reminderusers, function(key,value) { 
      reminder_send_to +=  value['user']['email'] + ',';
    });
    
    $("input[name=reminder_send_to]").val(reminder_send_to.replace(/,$/, ''));    

    var loadertext = '<div class="sk-bounce sk-primary sk-center">' +
            '<div class="sk-bounce-dot"></div>' +
            '<div class="sk-bounce-dot"></div>' +
          '</div>'; 

    $("#reminder_to_users").html(loadertext);
   
        var user_role_selected = $('#user_role').find('option:selected');
        var user_role_value = user_role_selected.val();
     
    var country_selected = $('#country').find('option:selected');
    var country_value = country_selected.val();
            
    if(vat_reg_main_id != "" || vat_reg_main_id == 0)
    {          
      $.ajax({      
        //url: `${reminderUrl}${vat_reg_main_id}/users`,
        url: `${reminderUrl}users`,
        data: {user_role: user_role_value,vat_reg_main_id: vat_reg_main_id, client_id: client_id, reminderusers: reminderusers, country: country_value}, 
        type: 'GET',
        success: function (result) {//console.log(result); 
          $("#reminder_to_users").html(result.view); 

          if(pageaction == 'edit')
          {
            // $('#reminder_team_users input[type=checkbox]').prop('disabled',true);
            // $('#reminder_client_users input[type=checkbox]').prop('disabled',true);          
            // $('#reminder_reminder_users input[type=checkbox]').prop('disabled',true);

            validateReminderForm('edit');
          }
          else
            validateReminderForm();        
        },
        error: function (err) {
          console.log(err);
        }
      });
    }
  }

  function selectuserCount()
  {
    var clientUserChecked = $('#reminder_client_users .send-to-reminder:checked').length;
    $(".client-user-count").hide();
    if(clientUserChecked > 0)
    {
      $(".client-user-count").show();
      $(".client-user-count").text(clientUserChecked); 
      $(".btn-send-reminder").text('Send Reminder (' + clientUserChecked + ')'); 
    }
    else
      $(".btn-send-reminder").text('Send Reminder (0)'); 

    var teamUserChecked = $('#reminder_team_users .send-to-reminder:checked').length;
    $(".team-user-count").hide();
    if(teamUserChecked > 0)
    {
      $(".team-user-count").show();
      $(".team-user-count").text(teamUserChecked);    
      $(".btn-send-reminder").text('Send Reminder (' + teamUserChecked + ')'); 
    }

    var reminderUserChecked = $('#reminder_reminder_users .send-to-reminder:checked').length;
    $(".reminder-user-count").hide();
    if(reminderUserChecked > 0)
    {
      $(".reminder-user-count").show();
      $(".reminder-user-count").text(reminderUserChecked);   
      $(".btn-send-reminder").text('Send Reminder (' + reminderUserChecked + ')');   
    }
  }

  // User Count
  $(document).on('click', '.send-to-reminder', function () {
    // var clientUserChecked = $('#reminder_client_users .send-to-reminder:checked').length;
    // $(".client-user-count").hide();
    // if(clientUserChecked > 0)
    // {
    //   $(".client-user-count").show();
    //   $(".client-user-count").text(clientUserChecked); 
    //   $(".btn-send-reminder").text('Send Reminder (' + clientUserChecked + ')'); 
    // }
    // else
    //   $(".btn-send-reminder").text('Send Reminder (0)'); 

    // var teamUserChecked = $('#reminder_team_users .send-to-reminder:checked').length;
    // $(".team-user-count").hide();
    // if(teamUserChecked > 0)
    // {
    //   $(".team-user-count").show();
    //   $(".team-user-count").text(teamUserChecked);    
    //   $(".btn-send-reminder").text('Send Reminder (' + teamUserChecked + ')'); 
    // }

    // var reminderUserChecked = $('#reminder_reminder_users .send-to-reminder:checked').length;
    // $(".reminder-user-count").hide();
    // if(reminderUserChecked > 0)
    // {
    //   $(".reminder-user-count").show();
    //   $(".reminder-user-count").text(reminderUserChecked);   
    //   $(".btn-send-reminder").text('Send Reminder (' + reminderUserChecked + ')');   
    // }

    selectuserCount();
    validateReminderForm();
  });

  // Date    
  if ($("#reminder_datetime").length) {
    $("#reminder_datetime").flatpickr({
      enableTime: true,
      dateFormat: 'Y-m-d H:i',
      defaultDate: new Date()
    });      
  }

  window.reminderEditor = function reminderEditor() {
    const reminderEditor = document.querySelector('.reminder-editor');
    const dkReminderEditor = document.querySelector('.dk-reminder-editor');

    // Initialize Quill Editor
    // ------------------------------
    if (reminderEditor) {
      reminderQuill = new Quill('.reminder-editor', {       
        modules: {
          formula: false,
          toolbar: fullToolbar
        },
        placeholder: 'Write your message... ',
        theme: 'snow',
        //enable: ($("#title").val() == "") ? false : true
      });
      reminderQuill.enable($("#title").val() == "" ? false : true);

      //reminderQuill.root.contentEditable = $("#title").val() == "" ? false : true;
    }

    // Initialize Quill Editor
    // ------------------------------
    if (dkReminderEditor) {
      dkReminderQuill = new Quill('.dk-reminder-editor', {       
        modules: {
          formula: false,
          toolbar: fullToolbar
        },
        placeholder: 'Write your message... ',
        theme: 'snow',        
      });
      dkReminderQuill.enable($("#dk_title").val() == "" ? false : true);
    }  
  }     

  //Load Editor
  reminderEditor();
  //End  

  //Send Test
  $(document).on('click', '.btn-send-test-reminder', function ()  
  {  
    var btn_send_test_reminder = $('.btn-send-test-reminder');
    btn_send_test_reminder.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
    'Sending...');

    var sel_action_name = $("#reminder_action").find('option:selected').html();
    $("input[name=send_test_reminder]").val('send_test_reminder');  
    $("input[name=sel_action_name]").val(sel_action_name);  

    var formId = $(this).closest('.formReminder').attr('id'); 
    var modalId = $(this).closest('.modal-reminder').attr('id'); 

    var btn_send_test_reminder = $(this);   

    var ql_editor = $("#"+formId).find(".reminder-editor .ql-editor");    
    var dk_ql_editor = $("#"+formId).find(".dk-reminder-editor .ql-editor");    

    var schedule_value = $("#schedule").find('option:selected').val();
    var datetime_value = $("#reminder_datetime").val();

    if(ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "" && dk_ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "")
    {
      Swal.fire({
          title: 'Error',
          text: 'Please type content',
          icon: 'error',
          customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      $(this).find(".reminder-editor .ql-editor").focus();
      $(this).find(".dk-reminder-editor .ql-editor").focus();
      return false;
    }
    else
    {        
      $("#reminder-content-quill").val(ql_editor.html());
      $("#dk-reminder-content-quill").val(dk_ql_editor.html());

      Swal.fire({
        title: 'Are you sure?',       
        text: "You want to save the reminder!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Save reminder!',
        cancelButtonText: "No",
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) 
      {      
        if (result.value) 
        {          
          $.ajax({           
            url: `${reminderUrl}`,
            type: 'POST',
            data: $('#' + formId).serialize()+ '&schedule_value=' + schedule_value + '&datetime_value=' + datetime_value + '&action_name=' + sel_action_name,        
            success: function (result) {            
              if(result)    
              {        
                var status = result['message'];                
                reminder_datas = drawDtTable(result, 'reminder');
                dt_reminder.clear().rows.add(reminder_datas).draw();

                var modalId = "reminderModal";
                $('#'+ modalId).modal('hide');

                clearReminderForm();

                Swal.fire({
                  icon: 'success',
                  title: `Reminder saved!`,
                  text: `Test email has been sent.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
                btn_send_test_reminder.html('Send Test');
              }
            },
            error: function (error) {
              console.log(error);
            }
          }); 
        } 
        else if (result.dismiss === Swal.DismissReason.cancel) 
        {
          $.ajax({
            url: `${reminderUrl}sendtestemail`,
            type: 'GET',      
            data: $('#' + formId).serialize()+ '&schedule_value=' + schedule_value + '&datetime_value=' + datetime_value + '&save_status= nosave',              
            success: function (result) {       
              if(result)    
              {          
                Swal.fire({
                  icon: 'success',
                  title: `Sent Successfully!`,
                  text: `Test email has been sent.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
                btn_send_test_reminder.html('Send Test');
              }
            },
            error: function (error) {
              console.log(error);
            }
          }); 
          // if no just send email for this reminder
        }
      });   
    } //null editor
  });
  //End Send Test

  //Send Reminder
  $(document).on('click', '.btn-send-reminder', function ()  
  {
    var btn_send_reminder = $('.btn-send-reminder');
    btn_send_reminder.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                    'Sending...');

    SaveAndSend($(this),'send_reminder');
  });
  //End Send Reminder

  //Save Reminder 
  $(document).on('click', '.btn-create-reminder', function ()  
  {
    var btn_create_reminder = $('.btn-create-reminder');
    btn_create_reminder.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
      'Saving...');

    SaveAndSend($(this),'create_reminder');

    /*
    //event.preventDefault();
    var formId = $(this).closest('.formReminder').attr('id'); 
    var modalId = $(this).closest('.modal-reminder').attr('id'); 
    
    var btn_create_reminder = $(this);   

    var ql_editor = $("#"+formId).find(".ql-editor");
    
    if(ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "")
    {
      Swal.fire({
        title: 'Error',
        text: 'Please type content',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      $(this).find(".ql-editor").focus();
      return false;
    }
    else
    {        
      $("#reminder-content-quill").val(ql_editor.html());

      Swal.fire({
        title: 'Are you sure?',       
        text: "You want to save the reminder!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Save reminder!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) 
      {      
        if (result.value) 
        {                                
          btn_create_reminder.attr('disabled');
          btn_create_reminder.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
                  'Saving...');
          
          $.ajax({
            url: `${reminderUrl}`,
            type: 'POST',
            data: $('#' + formId).serialize(),           
            success: function (result) {
             //console.log(result);
              if(result)    
              {  
                var status = result['message'];                
                reminder_datas = drawDtTable(result, 'reminder');
                dt_reminder.clear().rows.add(reminder_datas).draw();

                btn_create_reminder.html('Save');
                btn_create_reminder.addClass('disabled');
                btn_create_reminder.addClass('btn-danger');
                btn_create_reminder.removeClass('btn-success');
                
                var modalId = "reminderModal";
                $('#'+ modalId).modal('hide');

                clearReminderForm();
                            
                Swal.fire({
                  icon: 'success',
                  title: `Reminder saved!`,
                  text: `Reminder has been saved.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });

              }
            },
            error: function (error) {
              console.log(error);
            }
          }); 
        } 
        else if (result.dismiss === Swal.DismissReason.cancel) 
        {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled reminder :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });   
    } //null editor
    */
  });

  function SaveAndSend($_this,$which_btn)
  {
    $("input[name=send_test_reminder]").val('');  
    var sel_action_name = $("#reminder_action").find('option:selected').html();   
    $("input[name=sel_action_name]").val(sel_action_name);  

    var formId = $_this.closest('.formReminder').attr('id');
    var modalId = $_this.closest('.modal-reminder').attr('id'); 

    var btn_savesend_reminder = $_this;   

    var ql_editor = $("#"+formId).find(".reminder-editor .ql-editor");
    var dk_ql_editor = $("#"+formId).find(".dk-reminder-editor .ql-editor");
    var schedule_value = $("#schedule").find('option:selected').val();
    var datetime_value = $("#reminder_datetime").val();

    var edit_sent_to = $("input[name=reminder_send_to]").val();  
    if(ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "" || dk_ql_editor.html().replace( /(<([^>]+)>)/ig, '') == "")
    {
      Swal.fire({
        title: 'Error',
        text: 'Please type content',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      $_this.find(".reminder-editor .ql-editor").focus();
      $_this.find(".dk-reminder-editor .ql-editor").focus();
      return false;
    }
    else
    {        
      $("#reminder-content-quill").val(ql_editor.html());
      $("#dk-reminder-content-quill").val(dk_ql_editor.html());

      Swal.fire({
        title: 'Are you sure?',       
        text: "You want to save the reminder!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Save reminder!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) 
      {      
        if (result.value) 
        {       
          var success_text = "";                                     
          $.ajax({
            url: `${reminderUrl}`,
            type: 'POST',
            data: $('#' + formId).serialize() + '&schedule=' + schedule_value + 
            '&reminder_datetime=' + datetime_value +  '&action_name=' + sel_action_name + 
            '&edit_sent_to=' + edit_sent_to,       
            success: function (result) {             
              if(result)    
              {  
                if($which_btn == 'send_reminder')
                {
                  btn_savesend_reminder.html('Send Reminder (0)');                
                  success_text = 'Reminder Email Sent !!';
                }
                else if($which_btn == 'create_reminder')
                {
                  btn_savesend_reminder.html('Save');                
                  success_text = 'Reminder Saved !!';
                }

                var status = result['message'];                                       
                reminder_datas = drawDtTable(result, 'reminder');
                dt_reminder.clear().rows.add(reminder_datas).draw();
                
                var modalId = "reminderModal";
                $('#'+ modalId).modal('hide');

                clearReminderForm();

                 Swal.fire({
                  icon: 'success',
                  title: `Success!`,                  
                  text: success_text,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
            },
            error: function (error) {
              console.log(error);
            }
          }); 
        } 
        else if (result.dismiss === Swal.DismissReason.cancel) 
        {
          if($which_btn == 'send_reminder')          
            btn_savesend_reminder.html('Send Reminder (0)');                              
          else if($which_btn == 'create_reminder')         
            btn_savesend_reminder.html('Save');                             
       
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled reminder :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });   
    } //null editor
  }

  // Delete Reminder
  $(document).on('click', '.delete-reminder', function () {
    var reminder_id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

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
          url: `${reminderUrl}${reminder_id}`,
          success: function (result) {
            var status = result['message'];
            reminder_datas = drawDtTable(result, 'reminder');
            dt_reminder.clear().rows.add(reminder_datas).draw();
          },
          error: function (error) {
            console.log(error);
          }
        });

        // success sweetalert
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The reminder has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The reminder is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

});