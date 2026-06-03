'use strict';

$(function () {
  // Init custom option check
  //window.Helpers.initCustomOptionCheck();

  var crmLeadUrl = baseUrl + 'crm/leads'; 
  var crmQuoteUrl = baseUrl + 'crm/quotes';       

  let iti;

  var statusObj = {      
    'new': { title: 'New', class: 'bg-label-primary' },
    'rejected': { title: 'Rejected', class: 'bg-label-danger' },
    'converted': { title: 'Quote', class: 'bg-label-success' },
    'reminder': { title: 'Reminder', class: 'bg-label-warning' }    
  };

  var emailSentObj = {      
    0: { title: 'Pending', class: 'bg-label-danger' },
    1: { title: 'Sent', class: 'bg-label-success' } 
  };

  // Variable declaration for table
  var dt_lead_table = $('.datatables-leads');
  var dt_lead = null;

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  
  // leads datatable
  if (dt_lead_table.length) {console.log(crm_lead_datas);
    dt_lead = dt_lead_table.DataTable({
      data: crm_lead_datas,        
      processing: true,
      columns: [
        // columns according to JSON
        { data: 'id', className: "align-top" },
        { data: 'cvr_number', className: "align-top" },
        { data: 'company_name', className: "align-top" },
        { data: 'company_website', className: "align-top" },
        { data: 'contact', className: "align-top" },        
        { data: 'status', className: "align-top" },
        { data: 'action', className: "align-top" }
      ],
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
      ],
      pageLength: 100,
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          // render: function (data, type, full, meta) {
          //   var fake_id = full['fake_id'];

          //   return fake_id;
          // }
        },
        {
          // Company
          targets: 1, 
          width: "5%",         
          render: function (data, type, full, meta) {
            var cvr_number = full['cvr_number'];

            return cvr_number;
          }
        },
        {
          // Company
          targets: 2, 
          width: "10%",         
          render: function (data, type, full, meta) {
            var company_name = full['company_name'];

            return company_name;
          }
        },
        {
          // Company
          targets: 3, 
          width: "10%",         
          render: function (data, type, full, meta) {
            var company_website = full['company_website'];

            return company_website;
          }
        },
        {
          // Lead username and email
          targets: 4,
          responsivePriority: 4,
          width: "30%",   
          render: function (data, type, full, meta) {
            var $name = full['first_name'] + ' ' + full['last_name'],
              phone = full['phone'],
              $email = full['email'],
              $designation = full['designation']
              //$image = full['avatar']
              ;
            // if ($image) {
            //   // For Avatar image
            //   var $output =
            //     '<img src="' + assetsPath + 'img/avatars/' + $image + '" alt="Avatar" class="rounded-circle">';
            // } else {
            //   // For Avatar badge
            //   var stateNum = Math.floor(Math.random() * 6);
            //   var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            //   var $state = states[stateNum],
            //     $name = full['name'],
            //     $initials = $name.match(/\b\w/g) || [];
            //   $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
            //   $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';
            // }
            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +
              // '<div class="avatar-wrapper">' +
              // '<div class="avatar avatar-sm me-3">' +
              // $output +
              // '</div>' +
              // '</div>' +
              '<div class="d-flex flex-column">' +
              '<a href="#' +              
              '" class="text-body text-truncate"><span class="fw-semibold d-inline-block w-px-250 text-wrap text-break">' +
              $name +
              '</span></a>' +
              '<small class="text-muted">' +
              (($designation) ? $designation : '') +
              '</small>' +
              '<small class="text-muted">' +
              ((phone) ? phone : '') +
              '</small>' +
              '<small class="text-muted">' +
              (($email) ? $email : '') +
              '</small>' +
              '</div>' +
              '</div>';
            return $row_output;
          }
        },                                   
        {
          // Status
          targets: 5,
          width: "5%",
          render: function (data, type, full, meta) {
            var $status = full['status'];

            return '<span class="badge ' + statusObj[$status].class + '">' + statusObj[$status].title + '</span>';
          }
        },
        {
          // Actions
          targets: -1,
          width: "25%",
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            var buttons = "";            

            if(!full['has_quote'])
            {
              buttons ='<button class="btn btn-sm btn-icon edit-lead" data-id="'+full['id']+'" title="Edit"><i class="bx bx-edit"></i></button>' +
                        '<button class="btn btn-sm btn-icon delete-lead" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';           
            }

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
                columns: [1, 2, 3, 4, 5],
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
                columns: [1, 2, 3, 4, 5],
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
                columns: [1, 2, 3, 4, 5],
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
                columns: [1, 2, 3, 4, 5],
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
                columns: [1, 2, 3, 4, 5],
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
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Add Lead</span>',
          className: 'add-new-lead btn btn-primary',
          // attr: {
          //   'data-bs-toggle': 'offcanvas',
          //   'data-bs-target': '#offcanvasAddUser'
          // }
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
        // // Adding role filter once table initialized
        // this.api()
        //   .columns(5)
        //   .every(function () {
        //     var column = this;
        //     var select = $(
        //       '<select id="UserRole" class="form-select text-capitalize"><option value=""> Select Role </option></select>'
        //     )
        //       .appendTo('.user_role')
        //       .on('change', function () {                
        //         var val = $(this).val().replace(/-/g, " ");
        //         column.search(val ? val : '', true, false).draw();
        //       });

        //     column
        //       .data()
        //       .unique()
        //       .sort()
        //       .each(function (d, j) {
        //         select.append('<option value="' + d + '">' + d.replace(/-/g, " ") + '</option>');
        //       });
        //   });     
        //   // Adding role filter once table initialized
        // this.api()
        //   .columns(7)
        //   .every(function () {
        //     var column = this;
        //     var select = $(
        //       '<select id="UserLang" class="form-select text-capitalize"><option value=""> Select Language </option></select>'
        //     )
        //       .appendTo('.user_lang')
        //       .on('change', function () {                
        //         var val = $(this).val();
        //         column.search(val ? val : '', true, false).draw();
        //       });

        //     column
        //       .data()
        //       .unique()
        //       .sort()
        //       .each(function (d, j) {
        //         select.append(
        //           '<option value="' +
        //             d +
        //             '" class="text-capitalize">' +
        //             langObj[d].title +
        //             '</option>'
        //         );
        //       });
        //   });      
        // // Adding status filter once table initialized
        // this.api()
        //   .columns(8)
        //   .every(function () {
        //     var column = this;
        //     var select = $(
        //       '<select id="FilterTransaction" class="form-select text-capitalize"><option value=""> Select Status </option></select>'
        //     )
        //       .appendTo('.user_status')
        //       .on('change', function () {               
        //         var val = $(this).val();
        //         column.search(val ? val : '', true, false).draw();
        //       });

        //     column
        //       .data()
        //       .unique()
        //       .sort()
        //       .each(function (d, j) {
        //         select.append(
        //           '<option value="' +
        //             statusObj[d].title +
        //             '" class="text-capitalize">' +
        //             statusObj[d].title +
        //             '</option>'
        //         );
        //       });
        //   });

          $(".sk-bounce.lead-page").hide();
          $("#header-card").show();
          $("#table-card").show();
      }
    });
  }

  // add lead
  //$('.add-new-lead').on('click', function () {
  $(document).on('click', '.add-new-lead', function () {
    window.location.href = `${crmLeadUrl}/create`;      
  });

  // edit lead
  //$('.edit-lead').on('click', function () {
  $(document).on('click', '.edit-lead', function () {
    var lead_id = $(this).data('id');
  
    window.location.href = `${crmLeadUrl}/${lead_id}/edit`;      
  });

  // Delete lead 
  $(document).on('click', '.delete-lead', function () {
    var lead_id = $(this).data('id');

    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to delete this lead!",
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
          url: `${crmLeadUrl}/${lead_id}`,
          success: function (result) {
            
            crm_lead_datas = drawDtTable(result, 'crm_lead');
            dt_lead.clear().rows.add(crm_lead_datas).draw();

            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The lead has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (error) {
            // success sweetalert
            Swal.fire({
              icon: 'danger',
              title: 'Error!',
              text: 'Error in lead deletion!',
              customClass: {
                confirmButton: 'btn btn-danger'
              }
            });
          }
        });

        
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The lead is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  $(document).on('click', '#btn_crm_cvr_no_search', function () {  
    var btn_crm_cvr_no_search = $(this);
    btn_crm_cvr_no_search.attr('disabled', 'disabled');
    btn_crm_cvr_no_search.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Searching...');    
    
    $(".company-details").html('');

    var country = 'DK';    
    if($("#formValidationSelect2").val() != '')
      country = $("#formValidationSelect2").val();

    $.ajax({      
      //url: `${cvrDetailUrl}/` + $("#vatno").val(), 
      url: `${crmLeadUrl}/` + $("#crm_cvr_no").val() + `/company`,   
      type: 'GET',
      success: function (response) {
        //console.log(response);
        
        $(".company-details").html(response);

        if(iti != null)
          iti.destroy();
       
        initializeTel();
        
        btn_crm_cvr_no_search.removeAttr('disabled');
        btn_crm_cvr_no_search.html('Search');
      },
      error: function (data, textStatus, errorThrown) {
        btn_crm_cvr_no_search.removeAttr('disabled');
        btn_crm_cvr_no_search.html('Search');
      }
    });
  });  

  $(document).on('change', '#formUserList', function () {
    var user_id = $(this).val();

    $(".user-contact-details").html('');

    $.ajax({      
      url: `${crmLeadUrl}/` + user_id + `/user`,    
      type: 'GET',
      success: function (response) {
        console.log(response);
                
        $(".user-contact-details").html(response);
      },
      error: function (data, textStatus, errorThrown) {
        console.log(errorThrown);
      }
    });    
  });

  $(document).on('change', '#formValidationSelect2', function () {
    if($(this).val() == 'DK' || $(this).val() == 'NO')
    {
      $("#btn_crm_cvr_no_search").show();
      $("#crmClientNameHelp").show();

      $("#formClient .input-group").removeClass("mb-3");
    }
    else
    {
      $("#btn_crm_cvr_no_search").hide();
      $("#crmClientNameHelp").hide();

      $("#formClient .input-group").addClass("mb-3");
    }

    if(iti != null)
      iti.destroy();
   
    initializeTel();   
  });

  function initializeTel() {
      var input = document.getElementById("crm_telephone");
      if(input)
      {
        iti = intlTelInput(input, {
            initialCountry: $("#formValidationSelect2").val(),
            preferredCountries: ["dk"],
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"    
        });
      }
  }
  initializeTel();

  const flatpickrInline = document.querySelector('#crm_lead_date'),
        flatpickrMulti = document.querySelector('#crm_reminder_datetime');

  // Inline
  if (flatpickrInline) {
    flatpickrInline.flatpickr({
      inline: true,
      allowInput: false,
      monthSelectorType: 'static'
    });
  }

  // Multi Date Select
  if (flatpickrMulti) {
    flatpickrMulti.flatpickr({
      weekNumbers: true,
      enableTime: true,
      mode: 'multiple',
      minDate: 'today'
    });
  }

  const select2 = $('.select2');
  // Select2
  // --------------------------------------------------------------------

  // Default
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);

      var placeholder = '';
      if($this.attr('id') == 'crm_potential_countries')
        placeholder = 'Potential Countries';
      else if($this.attr('id') == 'crm_potential_products')
        placeholder = 'Potential Products';

      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select ' + placeholder,
        dropdownParent: $this.parent()
      });
    });
  }

  window.crmReminderCommentEditor = function crmReminderCommentEditor(data = null) {      
    const crmReminderCommentEditors = document.querySelector('#crm-reminder-reason-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (crmReminderCommentEditors) {
      new Quill('#crm-reminder-reason-editor', {
        modules: {
          toolbar: '#crm-reminder-reason-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  crmReminderCommentEditor();

  //No Quote
  $(document).on("click", "#no-quote", function(event)
  {
    event.preventDefault();

    let button = $(this);
    let form = button.closest('form');
    let formId = form.attr('id');

    // Trigger HTML5 validation
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }

    Swal.fire({
      title: 'Are you sure?',    
      text: "You want to send reminder for this lead!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-danger'
      },
      buttonsStyling: false
    }).then(function (result) {

      //var formId = $(this).closest('.form-crm-leads').attr('id');

      if (result.isConfirmed) 
      {
        //Store and 
        $.ajax({
          url: `${crmLeadUrl}`,
          type: 'POST',        
          data: $('#' + formId).serialize() + '&status=reminder',
          success: function (result) {
            if(result.status == 'success')    
            {      
              $('#crm_lead_id').val(result.lead_id);

              //Open Modal for Reminder
              $('#modalCrmReminder').modal('show');
            }
            else if(result.status == 'error')    
            {                               
              Swal.fire({
                icon: 'error',
                title: 'Lead reminder error!',
                text: result.message,
                customClass: {
                  confirmButton: 'btn btn-danger'
                }
              });
            }   
          },
          error: function (error) {
            console.log(error);
          }
        });
      } //Yes
      else if (result.dismiss === Swal.DismissReason.cancel) 
      {
        $.ajax({
          url: `${crmLeadUrl}`,
          type: 'POST',        
          data: $('#' + formId).serialize() + '&status=rejected',
          success: function (result) {
            if(result.status == 'success')    
            {   
              Swal.fire({
                icon: 'success',
                title: 'Lead rejected!',    
                text: "Lead has been rejected.",                
                showCancelButton: false,
                confirmButtonText: 'Ok',                
                customClass: {
                  confirmButton: 'btn btn-success'                  
                },
                buttonsStyling: false
              }).then(function (result) {
                if (result.isConfirmed) 
                  window.location.href = crmLeadUrl;
              });
            }
            else if(result.status == 'error')    
            {                               
              Swal.fire({
                icon: 'error',
                title: 'Lead error!',
                text: result.message,
                customClass: {
                  confirmButton: 'btn btn-danger'
                }
              });
            }   
          },
          error: function (error) {
            console.log(error);
          }
        });
      } //No
    });
  });

  var dt_quote_tables = $('.datatables-quote');
  var dt_quote = null;

  for (var i = 0; i < dt_quote_tables.length; i++) {

    var dt_quote_table = $(dt_quote_tables[i]); // This is a DOM element, not a jQuery object    

    if (dt_quote_table) 
    {      
      var quote_filter_class = 'd-none';
      let quote_name = '';
      var crm_quote_datas = [];
      if(i === 0)
      {
        quote_filter_class = '';
        quote_name = 'active';
        crm_quote_datas = crm_active_quote_datas;

        //columntargets = [7];
      }
      else if(i === 1)
      {
        quote_name = 'negotiate';
        crm_quote_datas = crm_negotiate_quote_datas;
      }
      else if(i === 2)
      {
        quote_name = 'approved';
        crm_quote_datas = crm_approved_quote_datas;
      }
      else if(i === 3)
      {
        quote_name = 'rejected';
        crm_quote_datas = crm_rejected_quote_datas;        
      }

      let columns = [                        
        { data: 'company_name', defaultContent: '' },
        { data: 'package', defaultContent: '' },
        { data: 'version', defaultContent: '' },
        { data: 'Price', defaultContent: '' },        
        { data: 'created_at', defaultContent: '' },
        { data: 'id', defaultContent: '' }
      ];      

      if ($.fn.DataTable.isDataTable(dt_quote_table)) {
          dt_quote_table.DataTable().destroy();
      }

      dt_quote = dt_quote_table.DataTable({  
          data: crm_quote_datas,              
          scrollCollapse: false,              
          searching: true,    
          lengthMenu: [
              [10, 25, 50, 100],
              [10, 25, 50, 100]
          ],
          pageLength: 100,     
          autoWidth: false, 
          ordering: false,                
          columns: columns,           
          // createdRow: function(row, data, dataIndex)
          // {
          //     let version = (data.version || '').toString();

          //     let depth = (data.level !== undefined && data.level !== null)
          //         ? data.level
          //         : (version.match(/\./g) || []).length;

          //     $(row).addClass('crm-level-' + depth);
          // },
          createdRow: function(row, data, dataIndex)
          {
              let version = (data.version || '').toString();

              let depth = (data.level !== undefined && data.level !== null)
                  ? data.level
                  : (version.match(/\./g) || []).length;

              $(row)
                  .addClass('crm-level-' + depth)
                  .attr('data-root', data.root_quote_id)
                  .attr('data-id', data.id);

              /**
               * Hide children initially
               */
              if(data.is_hidden)
              {
                  $(row).hide();
              }
          },
          columnDefs: [            
            {
              // For Client Name and No.
              targets:  0,         
              searchable: true,
              orderable: false, 
              render: function (data, type, full, meta)
              {
                  let company_name = full.company_name ?? '';

                  /**
                   * Non negotiation rows
                   */
                  if(full.status !== 'negotiation')
                  {
                      return `
                          <div>
                              <div class="fw-semibold">
                                  ${company_name}
                              </div>

                              <small class="text-muted">
                                  ${full.cvr_number ?? ''}
                              </small>
                          </div>
                      `;
                  }

                  /**
                   * Version depth
                   * 1       => depth 0
                   * 1.1     => depth 1
                   * 1.1.1   => depth 2
                   */
                  let version = full.version.toString();

                  let depth = (version.match(/\./g) || []).length;

                  /**
                   * Left padding
                   */
                  let padding = depth * 25;

                  /**
                   * Color by depth
                   */
                  let color = 'primary';

                  switch(depth)
                  {
                      case 0:
                          color = 'secondary';
                          break;

                      case 1:
                          color = 'success';
                          break;

                      case 2:
                          color = 'warning';
                          break;

                      case 3:
                          color = 'primary';
                          break;

                      default:
                          color = 'dark';
                  }

                  /**
                   * Tree icon
                   */
                  let tree = '';

                  if(depth > 0)
                  {
                      tree = `
                          <span class="me-2 text-${color} fw-bold">
                              └──
                          </span>
                      `;
                  }

                  let toggleBtn = '';

                  //if(data.is_latest)
                  if(full.is_latest)
                  {
                      toggleBtn = `
                          <span
                              class="crm-toggle-tree me-2 cursor-pointer"
                              data-root="${full.root_quote_id}"
                          >
                              ▶
                          </span>
                      `;
                  }

                  /**
                   * Root label
                   */                  
                  let label = full.parent_quote_id === null
                            ? `Original Negotiation`
                            : `Negotiation`;

                  return `
                      <div
                          class="d-flex align-items-start"
                          style="padding-left:${padding}px;"
                      >
                          
                          ${toggleBtn}
                          ${tree}

                          <div>

                              <div class="fw-semibold">
                                  ${company_name}
                              </div>

                              <small class="text-${color}">
                                  ${label} (V${version})
                              </small>

                          </div>

                      </div>
                  `;
              }          
            }, 
            {
              // For package
              targets:  1,         
              searchable: true,
              orderable: false,              
              render: function (data, type, full, meta) {                
                return full.package.charAt(0).toUpperCase() + full.package.slice(1);
              }
            },
            {
              // For version
              targets:  2,         
              searchable: true,
              orderable: false,              
              render: function (data, type, full, meta) {                                               
                let badge = 'primary';

                if(full.status == 'negotiation')
                    badge = 'warning';

                if(full.status == 'approved')
                    badge = 'success';

                if(full.status == 'rejected')
                    badge = 'danger';

                return `
                    <span class="badge bg-${badge}">
                        V${full.version}
                    </span>
                `;
              }
            },
            {
              // For Price
              targets:  3,         
              searchable: true,
              orderable: false,  
              render: function (data, type, full, meta)
              {
                  var registration_price = Number(full.registration_price || 0).toLocaleString('en-US', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2
                  });

                  var base_price = Number(full.base_price || 0);

                  var monthly_price = base_price.toLocaleString('en-US', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2
                  });

                  var yearly_price = (base_price * 12).toLocaleString('en-US', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2
                  });

                  var addons = full.addons || [];

                  let addons_html = '';

                  if (addons.length > 0) {

                      addons_html = `
                          <div class="mt-2">
                              <div class="fw-semibold text-muted mb-1">Add-ons</div>
                              <ul class="list-unstyled mb-0">
                      `;

                      $.each(addons, function (i, addon) {
                          addons_html += `
                              <li class="d-flex justify-content-between small">
                                  <span>• ${addon.name}</span>
                                  <span class="fw-semibold">${Number(addon.price).toLocaleString('en-US', {
                                      minimumFractionDigits: 2,
                                      maximumFractionDigits: 2
                                  })}</span>
                              </li>
                          `;
                      });

                      addons_html += `</ul></div>`;
                  }

                  return `
                      <div class="p-2">

                          <div class="d-flex justify-content-between">
                              <span class="text-muted">Registration</span>
                              <span class="fw-bold">${registration_price}</span>
                          </div>

                          <div class="d-flex justify-content-between">
                              <span class="text-muted">Monthly</span>
                              <span class="fw-bold">${monthly_price}</span>
                          </div>

                          <div class="d-flex justify-content-between">
                              <span class="text-muted">Yearly</span>
                              <span class="fw-bold">${yearly_price}</span>
                          </div>

                          ${addons_html}

                      </div>
                  `;
              } 
            },             
            {
                // Created at
                targets: 4,
                searchable: true,
                orderable: true,
                render: function(data, type, full, meta) {

                    if(!full.created_at)
                        return '';

                    return full.created_at;
                }
            },                               
            {
              // For Action
              targets: 5,              
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) {

                var buttons = "";            

                if(full['status'] == 'active' || full['status'] == 'negotiation')
                  buttons = '<button class="btn btn-sm btn-icon btn-edit-quote" data-id="'+full['id']+'" title="Edit"><i class="bx bx-edit"></i></button>';
                            //'<button class="btn btn-sm btn-icon btn-delete-quote" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';           

                var li_approve = '<li><a href="javascript:;" class="dropdown-item btn-quote-approve" data-id="'+full['id']+'">Approve</a></li>';
                var li_negotiate = '<li><a href="javascript:;" class="dropdown-item btn-quote-negotiate" data-id="'+full['id']+'" title="Negotiate Quote">Negotiate</a></li>';
                var li_reject = '<li><a href="javascript:;" class="dropdown-item btn-quote-reject text-danger" data-id="'+full['id']+'">Reject</a></li>';
                
                var enable_li_approve = false;
                var enable_li_negotiate = false;
                var enable_li_reject = false;

                if(full['status'] == 'active' || full['status'] == 'negotiation')
                {
                  enable_li_approve = true;
                  enable_li_negotiate = true;
                  enable_li_reject = true;
                }
                else if(full['status'] == 'approved')
                {
                  enable_li_approve = false;
                  enable_li_negotiate = false;
                  enable_li_reject = true;
                }
                
                // else if(full['status'] != 'rejected')
                // {
                //   enable_li_approve = true;
                //   enable_li_negotiate = true;
                //   enable_li_reject = true;
                // }

                if(!enable_li_approve && !enable_li_negotiate && !enable_li_reject)
                {

                }
                else
                {
                  buttons += '<div class="d-inline-block">' +
                              '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                              '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                                ((enable_li_approve) ? li_approve : '') +
                                ((enable_li_approve) ? '<div class="dropdown-divider"></div>' : '') +
                                ((enable_li_negotiate) ? li_negotiate : '') +
                                ((enable_li_negotiate) ? '<div class="dropdown-divider"></div>' : '') +
                                ((enable_li_reject) ? li_reject : '') +
                              '</ul>' +
                            '</div>';
                }

                return (             
                  buttons              
                );
              }
            }                     
          ],
          processing: true, 
          //order: [[1, 'asc']],         
          dom:     
            '<"row mx-0 '+ quote_name +'-search-filter '+ quote_filter_class +'"' +                      
            '<"col-sm-12 col-md-4 sub-btns text-start my-auto">' +
            '<"col-sm-12 col-md-8"lfB>' +            
            '>r' +
            '<"row mx-0"' +
            '<"col-sm-12 p-0"t' +                    
            '>>' +
            '<"row mx-2"' +
            '<"col-sm-12 col-md-6"i>' +
            '<"col-sm-12 col-md-6"p>' +
            '>',
          // select: {            
          //   style: 'multi'
          // },
          language: {
            processing: '<div class="sk-bounce sk-primary sk-center">' +
                          '<div class="sk-bounce-dot"></div>' +
                          '<div class="sk-bounce-dot"></div>' +
                        '</div>',
            sLengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: 'Search..',
            infoEmpty: 'No entries to show',
            info : '_START_ to _END_ of _TOTAL_',          
            infoFiltered: ' - filtered from _MAX_ records'
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
                    columns: [1, 2, 3, 4, 5],
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
                    columns: [1, 2, 3, 4, 5],
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
                    columns: [1, 2, 3, 4, 5],
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
                    columns: [1, 2, 3, 4, 5],
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
                    columns: [1, 2, 3, 4, 5],
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
          ],
        // rowCallback: function(row, data)
        // {
        //     if(data.is_hidden)
        //     {
        //         $(row).hide();
        //     }
        // },              
        initComplete: function (settings, json) {

          $("."+ quote_name +"-search-filter").appendTo('.dt-search-filter');

          $("."+ quote_name +"-search-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-quote-export .'+ quote_name +'-quote-export');

          var quote_total = this.api().data().length;
          $("#btn-quote-"+ quote_name +" span").html(quote_total);

          $(".card.quotes .sk-bounce").hide();
          $(".card.quotes .card-datatable").show();          
        }
      });          
    } //if dt exist
  } //for loop dt

  $(document).off('click', '.crm-toggle-tree');

  $(document).on('click', '.crm-toggle-tree', function ()
  {
      let btn = $(this);

      let rootId = btn.data('root');

      /**
       * ONLY negotiation table
       */
      let table = btn.closest('.dataTables_wrapper')
                     .find('.datatables-quote')
                     .DataTable();

      let expanded = btn.attr('data-expanded') == '1';

      table.rows().every(function ()
      {
          let rowData = this.data();

          if(!rowData)
              return;

          /**
           * Same root
           */
          if(rowData.root_quote_id == rootId)
          {
              /**
               * Never hide latest row
               */
              if(rowData.is_latest)
                  return;

              let node = $(this.node());

              if(expanded)
              {
                  node.hide();
              }
              else
              {
                  node.show();
              }
          }
      });

      /**
       * Toggle icon
       */
      if(expanded)
      {
          btn.html('▶');
          btn.attr('data-expanded', '0');
      }
      else
      {
          btn.html('▼');
          btn.attr('data-expanded', '1');
      }
  });

  //Create Quote
  $(document).on("click", "#create-quote", function(event)
  {
    event.preventDefault();

    let button = $(this);
    let form = button.closest('form');
    let formId = form.attr('id');

    // Trigger HTML5 validation
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
   
    Swal.fire({
      title: 'Are you sure?',    
      text: "You want to create quote!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      
      if (result.isConfirmed) 
      {
        if($("#crm_lead_id").val() == '')
        {
          $.ajax({
            url: `${crmLeadUrl}`,
            type: 'POST',        
            data: $('#' + formId).serialize(),
            success: function (result) {
              if(result.status == 'success')    
              {                                             
                window.location.href = crmQuoteUrl + '/create/' + result.lead_id;
              }
              else if(result.status == 'error')    
              {                               
                Swal.fire({
                  icon: 'error',
                  title: 'Quote creation error!',
                  text: result.message,
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  }
                });
              }   
            },
            error: function (error) {
              console.log(error);
            }
          });
        }
        else
          window.location.href = crmQuoteUrl + '/create/' + $("#crm_lead_id").val();
      } //Yes 
    });     
  });

  /**
   * Main Summary Calculation
   */
  function calculateSummary()
  {
      let basePrice = parseFloat($('#base_price').val()) || 0;
      let registrationPrice = parseFloat($('#registration_price').val()) || 0;

      let addonTotal = 0;

      $('.addon-price').each(function () {

          let row = $(this).closest('.row');

          let checkbox = row.find('input[type="checkbox"]');

          let addonPrice = parseFloat($(this).val()) || 0;

          /**
           * Auto checkbox toggle
           */
          if (addonPrice > 0) {
              checkbox.prop('checked', true);
          } else {
              checkbox.prop('checked', false);
          }

          /**
           * Only checked addons count
           */
          if (checkbox.is(':checked')) {
              addonTotal += addonPrice;
          }

      });

      let monthly = basePrice + addonTotal;
      let yearly = monthly * 12;

      $('#monthly_total').text(
          monthly.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          })
      );

      $('#registration_total').text(
          registrationPrice.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          })
      );

      $('#yearly_total').text(
          yearly.toLocaleString('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          })
      );
  }

  /**
   * Package selection
   */
  $('#package').on('change', function () {

      let packagePrice = $(this)
          .find(':selected')
          .data('price') || 0;

      $('#base_price').val(packagePrice);

      calculateSummary();
  });

  /**
   * Addon price typing
   */
  $(document).on(
      'keyup change input',
      '.addon-price',
      function () {

          let row = $(this).closest('.row');

          let checkbox = row.find('input[type="checkbox"]');

          let value = parseFloat($(this).val()) || 0;

          /**
           * Auto check/uncheck
           */
          checkbox.prop('checked', value > 0);

          calculateSummary();
      }
  );

  /**
   * Base price + registration
   */
  $(document).on(
      'keyup change input',
      '#base_price, #registration_price',
      function () {
          calculateSummary();
      }
  );

  /**
   * Manual checkbox toggle
   */
  $(document).on(
      'change',
      'input[type="checkbox"]',
      function () {

          let row = $(this).closest('.row');

          let priceField = row.find('.addon-price');

          /**
           * If unchecked manually
           * clear price
           */
          if (!$(this).is(':checked')) {
              priceField.val('');
          }

          calculateSummary();
      }
  );

  /**
   * Initial
   */
  calculateSummary();

  window.reloadQuoteTabs = function reloadQuoteTabs(crm_quote_datas) {  
    var dt_quote_tables = $('.datatables-quote');
    for (var i = 0; i < dt_quote_tables.length; i++) 
    {      
      var quote_name = '';        
      if(i === 0) quote_name = 'active';          
      else if(i === 1) quote_name = 'negotiate';          
      else if(i === 2) quote_name = 'approved';  
      else if(i === 3) quote_name = 'rejected';     

      var tabSelector = "#navs-quote-" + quote_name;
      var tableSelector = ".datatables-"+ quote_name +"-quote";

      if($(tableSelector).length > 0)
      {
        if ($.fn.DataTable.isDataTable(tableSelector))
        {
          var dt_quote = $(tableSelector).DataTable();
          var rowsData = crm_quote_datas['crm_'+ quote_name +'_quote_datas'];

          dt_quote.clear().rows.add(rowsData).draw();
          $("#btn-quote-"+ quote_name +" span").html(rowsData.length);

          // Enable/disable tab based on data
          if (rowsData.length > 0) {
            $(tabSelector).css({
                'pointer-events': 'auto',
                'opacity': '1',
                'cursor': 'pointer'
            });
          } else {
            $(tabSelector).css({
                'pointer-events': 'none',
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
          }
        }
      }
    }
  }

  //Approve Quote
  $(document).on("click", ".btn-quote-approve", function(event)
  {
    event.preventDefault();

    let button = $(this);
    let form = button.closest('form');
    let formId = form.attr('id');

    let quoteId = button.attr('data-id');

    // // Trigger HTML5 validation
    // if (!form[0].checkValidity()) {
    //     form[0].reportValidity();
    //     return;
    // }
   
    Swal.fire({
      title: 'Are you sure?',    
      text: "You want to approve the quote!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      
      if (result.isConfirmed) 
      {
        $.ajax({
          url: `${crmQuoteUrl}/` + quoteId+ `/status`,
          type: 'POST',        
          data: $('#' + formId).serialize() + '&status=approved&module_type=quote',
          success: function (result) {
            if(result.status == 'success')    
            {         
              var quotes_result = result; 

              Swal.fire({
                icon: 'success',
                title: 'Quote approved!',    
                text: "Quote has been approved.",                
                showCancelButton: false,
                confirmButtonText: 'Ok',                
                customClass: {
                  confirmButton: 'btn btn-success'                  
                },
                buttonsStyling: false
              }).then(function (result) {               
                if (result.isConfirmed) 
                {                  
                  var crm_quote_datas = drawDtTable(quotes_result, 'crm_quote');              
                  reloadQuoteTabs(crm_quote_datas);                 
                }
              });
            }
            else if(result.status == 'error')    
            {                               
              Swal.fire({
                icon: 'error',
                title: 'Quote approval error!',
                text: result.message,
                customClass: {
                  confirmButton: 'btn btn-danger'
                }
              });
            }   
          },
          error: function (error) {
            Swal.fire({
              icon: 'error',
              title: 'Quote approval error!',
              text: error,
              customClass: {
                confirmButton: 'btn btn-danger'
              }
            });
          }
        });
      } //Yes 
      else if (result.dismiss === Swal.DismissReason.cancel) 
      {
        Swal.fire({
          title: 'Cancelled',
          text: 'The quote is not approved!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });     
  });
  
  //Negotiate Quote
  $(document).on("click", ".btn-quote-negotiate", function(event)
  {
    event.preventDefault();

    let button = $(this);
    let form = button.closest('form');
    let formId = form.attr('id');

    let quoteId = button.attr('data-id');

    // // Trigger HTML5 validation
    // if (!form[0].checkValidity()) {
    //     form[0].reportValidity();
    //     return;
    // }
   
    // Swal.fire({
    //   title: 'Are you sure?',    
    //   text: "You want to approve the quote!",
    //   icon: 'warning',
    //   showCancelButton: true,
    //   confirmButtonText: 'Yes',
    //   cancelButtonText: 'Cancel',
    //   customClass: {
    //     confirmButton: 'btn btn-primary me-2',
    //     cancelButton: 'btn btn-label-secondary'
    //   },
    //   buttonsStyling: false
    // }).then(function (result) {
      
    //   if (result.isConfirmed) 
    //   {
        $.ajax({
          url: `${crmQuoteUrl}/` + quoteId+ `/negotiate`,
          type: 'POST',        
          data: $('#' + formId).serialize(),
          success: function (result) {
            if(result.status == 'success')    
            {         
              var quotes_result = result; 

              Swal.fire({
                icon: 'success',
                title: 'Quote negotiated!',    
                text: "Copy of quote has been created for negotiation.",                
                showCancelButton: false,
                confirmButtonText: 'Ok',                
                customClass: {
                  confirmButton: 'btn btn-success'                  
                },
                buttonsStyling: false
              }).then(function (result) {               
                if (result.isConfirmed) 
                {                  
                  var crm_quote_datas = drawDtTable(quotes_result, 'crm_quote');              
                  reloadQuoteTabs(crm_quote_datas);                 
                }
              });
            }
            else if(result.status == 'error')    
            {                               
              Swal.fire({
                icon: 'error',
                title: 'Quote negotiation error!',
                text: result.message,
                customClass: {
                  confirmButton: 'btn btn-danger'
                }
              });
            }   
          },
          error: function (error) {
            Swal.fire({
              icon: 'error',
              title: 'Quote negotiation error!',
              text: error,
              customClass: {
                confirmButton: 'btn btn-danger'
              }
            });
          }
        });
      // } //Yes 
      // else if (result.dismiss === Swal.DismissReason.cancel) 
      // {
      //   Swal.fire({
      //     title: 'Cancelled',
      //     text: 'The quote is not approved!',
      //     icon: 'error',
      //     customClass: {
      //       confirmButton: 'btn btn-success'
      //     }
      //   });
      // }
    //});     
  });

  //Reject Quote
  $(document).on("click", ".btn-quote-reject", function(event)
  {
    event.preventDefault();

    let button = $(this);
    let form = button.closest('form');
    let formId = form.attr('id');

    let quoteId = button.attr('data-id');

    // // Trigger HTML5 validation
    // if (!form[0].checkValidity()) {
    //     form[0].reportValidity();
    //     return;
    // }
   
    Swal.fire({
      title: 'Are you sure?',    
      text: "You want to reject the quote!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      
      if (result.isConfirmed) 
      {
        $('#crm_quote_id').val(quoteId);

        //Open Modal for Reminder
        $('#modalCrmReminder').modal('show');        
      } //Yes 
      else if (result.dismiss === Swal.DismissReason.cancel) 
      {
        Swal.fire({
          title: 'Cancelled',
          text: 'The quote is not rejected!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });     
  });

  // edit quote
  //$('.btn-edit-quote').on('click', function () {  
  $(document).on('click', '.btn-edit-quote', function () {  
    var quote_id = $(this).data('id');
  
    window.location.href = `${crmQuoteUrl}/${quote_id}/edit`;      
  });

  //Reminder Lead/Quote
  $(document).on("click", ".btn-crm-reminder-reason-save", function(event)
  //$(document).on("submit", ".frm-crm-reminder", function(event)
  {
    event.preventDefault();

    //let form = $(this);

    let button = $(this);
    let form = button.closest('form');
    let formId = form.attr('id');
    
    //let quoteId = button.attr('data-id');

    let formElement = document.getElementById(formId);
    console.log(formElement);
    // Trigger HTML5 validation
    if (!formElement.checkValidity()) {     
        formElement.reportValidity();
        return false;
    }

    // Validate email manually (optional extra validation)
    let emailInput = $("#" + formId).find('input[type="email"]');

    if (emailInput.length > 0) {

        let email = emailInput.val().trim();

        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email !== '' && !emailPattern.test(email)) {

            Swal.fire({
                title: 'Error',
                text: 'Please enter a valid email address',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-success'
                }
            });

            emailInput.focus();
            return false;
        }
    }

    // Validate datetime picker
    let datetimeInput = $("#" + formId).find('.flatpickr-input');

    if (datetimeInput.length > 0 && datetimeInput.val().trim() === '') {

        Swal.fire({
            title: 'Error',
            text: 'Please select reminder date & time',
            icon: 'error',
            customClass: {
                confirmButton: 'btn btn-success'
            }
        });

        datetimeInput.focus();
        return false;
    }
   
    if($("#" + formId).find(".ql-editor").html().replace( /(<([^>]+)>)/ig, '') == "")
    {      
      Swal.fire({
        title: 'Error',
        text: 'Please type reminder notes',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      $("#" + formId).find(".ql-editor").focus();
      return false;
    }
    else
    {            
      $("#crm-reminder-reason-quill").val($("#" + formId).find(".ql-editor").html());

      var formData = new FormData(document.getElementById(formId));         
                   
      var btn_crm_reminder_reason_save = $("#" + formId + " #btn-crm-reminder-reason-save");
      btn_crm_reminder_reason_save.attr('disabled', 'disabled');
      btn_crm_reminder_reason_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');
     
      var module_type = $("#module_type").val();      
      var module_id = '';
      if(module_type == 'lead')
      {
        formData.append("status", "reminder");
        module_id = $("#crm_lead_id").val();         
      }
      else if(module_type == 'quote')
      {
        formData.append("status", "rejected");
        module_id = $("#crm_quote_id").val();
      }

      if(module_id)
      {                
        $.ajax({
          url: `${crmQuoteUrl}/${module_id}/status`,
          type: 'POST',
          dataType: "JSON",
          data: formData,
          processData: false,
          contentType: false,
          success: function (result) {           
            if(result)    
            {           
              btn_crm_reminder_reason_save.removeAttr('disabled');
              btn_crm_reminder_reason_save.html('Saved');
              btn_crm_reminder_reason_save.removeClass('disabled');
              
              var title = '';      
              var text = '';
              if(module_type == 'quote')
              {
                var crm_quote_datas = drawDtTable(result, 'crm_quote');              
                reloadQuoteTabs(crm_quote_datas); 
                      
                //Clear Modal Values
                $('#crm_quote_id').val('');

                title = 'Quote rejected and ';
                text = 'Quote has been rejected and ';
              }    
              else
                $('#crm_lead_id').val('');

              $("#crm_reminder_datetime").val('');
              $("#crm-reminder-reason-editor").find(".ql-editor").html("");
              
              $('#modalCrmReminder').modal('hide');

              Swal.fire({
                icon: 'success',
                title: title + 'Reminder saved!',
                text: text + 'Reminder has been saved.',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              }).then(function (result) {
                if (result.isConfirmed) 
                {
                  if(module_type == 'lead')
                    window.location.href = crmLeadUrl;
                }
              });
            }
          },
          error: function (error) {
            console.log(error);
          }
        });
      }//module_id
    }  
  });
  
  // Variable declaration for table
  var dt_crm_reminder_table = $('.datatables-crm-reminders');
  var dt_crm_reminder = null;

  // reminders datatable
  if (dt_crm_reminder_table.length) {console.log(crm_reminder_datas);
    dt_crm_reminder = dt_crm_reminder_table.DataTable({
      data: crm_reminder_datas,        
      processing: true,
      columns: [
        // columns according to JSON
        { data: 'id', className: "align-top" },
        { data: 'cvr_number', className: "align-top" },
        { data: 'company_name', className: "align-top" },
        //{ data: 'company_website', className: "align-top" },
        { data: 'contact', className: "align-top" }, 
        { data: 'recipient', className: "align-top" }, 
        { data: 'reminder_datetime', className: "align-top" }, 
        { data: 'reminder_notes', className: "align-top" },        
        { data: 'status', className: "align-top" }
      ],
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
      ],
      pageLength: 100,
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          // render: function (data, type, full, meta) {
          //   var fake_id = full['fake_id'];

          //   return fake_id;
          // }
        },
        {
          // Company
          targets: 1, 
          width: "5%",         
          render: function (data, type, full, meta) {
            var cvr_number = full['cvr_number'];

            return cvr_number;
          }
        },
        {
          // Company
          targets: 2, 
          width: "10%",         
          render: function (data, type, full, meta) {
            var company_name = full['company_name'];

            return company_name;
          }
        },
        // {
        //   // Company
        //   targets: 3, 
        //   width: "10%",         
        //   render: function (data, type, full, meta) {
        //     var company_website = full['company_website'];

        //     return company_website;
        //   }
        // },
        {
          // Lead username and email
          targets: 3,
          //responsivePriority: 3,
          width: "30%",   
          render: function (data, type, full, meta) {
            var $name = full['first_name'] + ' ' + full['last_name'],
              phone = full['phone'],
              $email = full['email'],
              $designation = full['designation']
              ;            
            // Creates full output for row            
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +              
              '<div class="d-flex flex-column">' +
              '<a href="#' +              
              '" class="text-body text-truncate"><span class="fw-semibold d-inline-block w-px-250 text-wrap text-break">' +
              $name +
              '</span></a>' +
              '<small class="text-muted">' +
              (($designation) ? $designation : '') +
              '</small>' +
              '<small class="text-muted">' +
              ((phone) ? phone : '') +
              '</small>' +
              '<small class="text-muted">' +
              (($email) ? $email : '') +
              '</small>' +
              '</div>' +
              '</div>';  
            return $row_output;
          }
        },  
        {
          // Recipient
          targets: 4, 
          width: "10%",         
          render: function (data, type, full, meta) {
            var reminder_sent_to = full['sent_to'];

            return reminder_sent_to;
          }
        },  
        {
          // Company
          targets: 5, 
          width: "10%",         
          render: function (data, type, full, meta) { 
            var reminder_datetime = full['reminder_date'] + ' '  + full['reminder_time'];

            return reminder_datetime;
          }
        },
        {
          // Company
          targets: 6, 
          width: "10%",         
          render: function (data, type, full, meta) {
            var reminder_notes = full['reminder_notes'];

            return reminder_notes;
          }
        },                                 
        {
          // Status
          targets: 7,
          width: "5%",
          render: function (data, type, full, meta) {
            var $status = full['email_sent'];

            return '<span class="badge ' + emailSentObj[$status].class + '">' + emailSentObj[$status].title + '</span>';
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
        // {
        //   text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Add Lead</span>',
        //   className: 'add-new-lead btn btn-primary'          
        // }
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
        $(".sk-bounce.crm-reminder-page").hide();
        $("#header-card").show();
        $("#table-card").show();
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300); 

  // if (window.location.hash) 
  // {
  //   var hash = window.location.hash; 
  //   var tabItem = $(hash);//.replace('#', '#btn-accordion-'));      

  //   if(tabItem)
  //   {
  //     var tabSelector = '#navs-quote-rejected';
  //     $(tabSelector).tab('show');  

  //     // After tab is shown, remove the hash from URL
  //     $(tabSelector).on('shown.bs.tab', function () {
  //         history.replaceState(null, null, window.location.pathname);
  //     }); 

  //     // Optional: scroll to the opened section
  //     // $('html, body').animate({
  //     //     scrollTop: tabItem.offset().top
  //     // }, 500);
  //   }
  // }

});//();
