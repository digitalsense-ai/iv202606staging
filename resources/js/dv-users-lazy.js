/**
 * Page User List
 */

'use strict';

// Datatable (jquery)
$(function () {
  $(".sk-bounce.user-page").show();

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

  // Variable declaration for table
  var dt_user_table = $('.datatables-users'),
    select2 = $('.select2'),
    userView = baseUrl + 'dv-user/',
    offCanvasForm = $('#offcanvasAddUser'),
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

  // Users datatable
  if (dt_user_table.length) {
    var dt_user = dt_user_table.DataTable({
      data: user_datas,        
      processing: true,      
      // serverSide: true,
      // ajax: {
      //   url: baseUrl + 'dv-user'
      // },
      columns: [
        // columns according to JSON
        { data: 'id', className: "align-top" },
        { data: 'user', className: "align-top" },
        { data: 'name', className: "align-top" },
        { data: 'email', className: "align-top" },
        { data: 'company', className: "align-top" },
        { data: 'role', className: "align-top" },                
        { data: 'telephone', className: "align-top" },      
        { data: 'lang', className: "align-top" },        
        { data: 'status', className: "align-top" },
        { data: 'action', className: "align-top" }    
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
          // User name and email
          targets: 1,
          responsivePriority: 4,
          width: "30%",   
          render: function (data, type, full, meta) {
            var $name = full['name'],
              $email = full['email'],
              $designation = full['designation'],
              $image = full['avatar'];
            if ($image) {
              // For Avatar image
              var $output =
                '<img src="' + assetsPath + 'img/avatars/' + $image + '" alt="Avatar" class="rounded-circle">';
            } else {
              // For Avatar badge
              var stateNum = Math.floor(Math.random() * 6);
              var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              var $state = states[stateNum],
                $name = full['name'],
                $initials = $name.match(/\b\w/g) || [];
              $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
              $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';
            }
            // Creates full output for row
            var $row_output =
              '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-3">' +
              $output +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<a href="#' +
              //userView +
              '" class="text-body text-truncate"><span class="fw-semibold d-inline-block w-px-250 text-wrap text-break">' +
              $name +
              '</span></a>' +
              '<small class="text-muted">' +
              $designation +
              '</small>' +
              '<small class="text-muted">' +
              $email +
              '</small>' +
              '</div>' +
              '</div>';
            return $row_output;
          }
        },
        {
          // Name
          targets: 2,
          visible: false,
          render: function (data, type, full, meta) {
            var $name = full['name'];

            return '<span class="fw-semibold">' + $name + '</span>';
          }
        },
        {
          // Email
          targets: 3,
          visible: false,
          render: function (data, type, full, meta) {
            var $email = full['email'];

            return '<span class="fw-semibold">' + $email + '</span>';
          }
        },
        {
          // Company
          targets: 4, 
          width: "15%",         
          render: function (data, type, full, meta) {
            var $company = full['company'];

            return $company;
          }
        },
        {
          // User Role
          targets: 5,
          width: "5%",
          render: function (data, type, full, meta) {
            var $role = full['role'];           
            var roleBadgeObj = {
              'client-user':
                '<span class="badge badge-center rounded-pill bg-label-warning w-px-30 h-px-30 me-2"><i class="bx bx-user bx-xs"></i></span>',             
              'team-user':
                '<span class="badge badge-center rounded-pill bg-label-primary w-px-30 h-px-30 me-2"><i class="bx bx-group bx-xs"></i></span>',
              'company-admin':
                '<span class="badge badge-center rounded-pill bg-label-info w-px-30 h-px-30 me-2"><i class="bx bx-mobile-alt bx-xs"></i></span>',
              'super-admin':
                '<span class="badge badge-center rounded-pill bg-label-secondary w-px-30 h-px-30 me-2"><i class="bx bx-cog bx-xs"></i></span>'
            };
            return "<span class='text-truncate d-flex align-items-center text-capitalize'>" + roleBadgeObj[$role] + $role.replace(/-/g, " ") + '</span>';
          }
        },
        {
          // Telephone
          targets: 6,
          width: "10%",
          render: function (data, type, full, meta) {
            var $telephone = full['telephone'];

            if(type == 'display')
              return '<span class="fw-semibold">' + $telephone + '</span>';
            else
              return '<span class="fw-semibold">' + $telephone.replace(/\s+/g, '') + '</span>';
          }
        },  
        {
          // Language
          targets: 7,
          width: "5%",
          render: function (data, type, full, meta) {
            var $lang = (full['lang']) ? full['lang'].toUpperCase() : 'EN';
            
            var $lang_flag = '<img src="'+ flagUrl + $lang +'.png" alt="'+ $lang +'" title="'+ $lang +'" class="country-flag me-2"><span class="align-middle me-4">  ' + $lang + '</span>';
            return '<span class="user-lang_flag">' + $lang_flag + '</span>'; 
          }
        },              
        {
          // User Status
          targets: 8,
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

            if(full['role'] == 'client-user')
            {
              var disabled = (full['status'] == 0) ? "disabled=\"disabled\"" : "";  
              buttons = '<div class="d-inline-block">' +
                          '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                          '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                            '<li><a href="javascript:;" class="dropdown-item notification-settings" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#notificationSettings" title="Notification settings">Notification</a></li>' +
                            //'<li><a href="javascript:;" class="dropdown-item">Archive</a></li>' +
                            '<div class="dropdown-divider"></div>' +
                            '<li><a href="javascript:;" class="dropdown-item text-danger" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#assignClient" title="Assign Company" '+disabled+'>Assign Companies</a></li>' +
                          '</ul>' +
                        '</div>';
            }

            if(full['role'] == 'team-user' || full['role'] == 'company-admin' || full['role'] == 'client-user')
            {
              if($("#auth_role").val() == 'team-user' && full['role'] != 'client-user')
              {}
              else  
                buttons +='<button class="btn btn-sm btn-icon edit-record" data-id="'+full['id']+'" data-user_role="'+full['role']+'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser" title="Edit"><i class="bx bx-edit"></i></button>' +
                  '<button class="btn btn-sm btn-icon delete-record" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';
            }

            if(full['role'] == 'team-user') 
            {   
              var disabled = (full['status'] == 0) ? "disabled=\"disabled\"" : "";  
              buttons += '<button class="btn btn-warning assign-record" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#assignVATReg" title="Assign VAT Reg." '+disabled+'><i class="bx bxs-user-plus"></i></button>';
            }

            if(full['role'] == 'company-admin') 
            {   
              var disabled = (full['status'] == 0) ? "disabled=\"disabled\"" : "";  
              buttons += '<button class="btn btn-primary assign-record" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#assignTeamUser" title="Assign Team User" '+disabled+'><i class="bx bxs-user-plus"></i></button>';
            }

            // if(full['role'] == 'client-user') 
            // {   
            //   var disabled = (full['status'] == 0) ? "disabled=\"disabled\"" : "";  
            //   buttons += '<button class="btn btn-info assign-record" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#assignClient" title="Assign Client" '+disabled+'><i class="bx bxs-user-plus"></i></button>';
            // }

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
                columns: [2, 3, 5, 6],
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
                columns: [2, 3, 5, 6],
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
                columns: [2, 3, 5, 6],
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
                columns: [2, 3, 5, 6],
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
                columns: [2, 3, 5, 6],
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
          text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Add New User</span>',
          className: 'add-new btn btn-primary',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddUser'
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
        // Adding role filter once table initialized
        this.api()
          .columns(5)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="UserRole" class="form-select text-capitalize"><option value=""> Select Role </option></select>'
            )
              .appendTo('.user_role')
              .on('change', function () {                
                var val = $(this).val().replace(/-/g, " ");
                column.search(val ? val : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append('<option value="' + d + '">' + d.replace(/-/g, " ") + '</option>');
              });
          });     
          // Adding role filter once table initialized
        this.api()
          .columns(7)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="UserLang" class="form-select text-capitalize"><option value=""> Select Language </option></select>'
            )
              .appendTo('.user_lang')
              .on('change', function () {                
                var val = $(this).val();
                column.search(val ? val : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append(
                  '<option value="' +
                    d +
                    '" class="text-capitalize">' +
                    langObj[d].title +
                    '</option>'
                );
              });
          });      
        // Adding status filter once table initialized
        this.api()
          .columns(8)
          .every(function () {
            var column = this;
            var select = $(
              '<select id="FilterTransaction" class="form-select text-capitalize"><option value=""> Select Status </option></select>'
            )
              .appendTo('.user_status')
              .on('change', function () {               
                var val = $(this).val();
                column.search(val ? val : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append(
                  '<option value="' +
                    statusObj[d].title +
                    '" class="text-capitalize">' +
                    statusObj[d].title +
                    '</option>'
                );
              });
          });

          $(".sk-bounce.user-page").hide();
          $("#header-card").show();
          $("#table-card").show();
      }
    });
  }

  // Delete Record 
  $(document).on('click', '.delete-record', function () {
    var user_id = $(this).data('id'),
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
          url: `${baseUrl}dv-user/${user_id}`,
          success: function (result) {
            //dt_user.draw();

            user_datas = drawDtTable(result, 'user');
            dt_user.clear().rows.add(user_datas).draw();

            $('#user-email').removeAttr('readonly');  
          },
          error: function (error) {
            console.log(error);
          }
        });

        refreshSelect();

        // success sweetalert
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The user has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The user is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // edit record
  $(document).on('click', '.edit-record', function () {
    var user_id = $(this).data('id'),
      user_role = $(this).data('user_role'),
      dtrModal = $('.dtr-bs-modal.show');

    var client_id = $(this).data('client_id');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // changing the title of offcanvas
    $('#offcanvasUserLabel').html('Edit User');  

    if($(".offcanvas-body #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertBefore(".offcanvas-body #addNewUserForm");  
    }     
    $("#addNewUserForm").hide();  

    // get data
    $.get(`${baseUrl}dv-user\/${user_id}\/edit`, function (data) {    
      var user = data;
      var dvuser = user.dvuser;
      //var role = user.roles[0];

      var filter_role = user.roles.filter(function(role) {                                        
        return (role.name === user_role);
      });
      var role = filter_role[0];

      $('#user_id').val(user.id);
      $('#user-firstname').val(dvuser.firstname);
      $('#user-lastname').val(dvuser.lastname);
      $('#user-email').val(user.email);
      $('#user-telephone').val(dvuser.telephone);
      $('#user-designation').val(dvuser.designation);
      $('#user-lang').val(dvuser.lang);
      $('#user-status').val(dvuser.status);  

      if ($('#user-role').find('option[value="' + role.name + '"]').length)
        $('#user-role').val(role.name);
      else
      {
        var $newOption = $('<option>', {           
            value: role.name,
            text: capitalizeFirstLetter(role.name.replace(/-/g, " "))
        }).addClass('text-option');

        $('#user-role').append($newOption);
        $('#user-role').val(role.name);
      }

      if(client_id)
        $('#user_contact_tab_client_id').val(client_id);

      // $('#user_id').val(data.id);
      // $('#user-firstname').val(data.firstname);
      // $('#user-lastname').val(data.lastname);
      // $('#user-email').val(data.email);
      // $('#user-telephone').val(data.telephone);
      // $('#user-designation').val(data.designation);
      // $('#user-lang').val(data.lang);
      // $('#user-status').val(data.status);  
      // $('#user-role').val(data.role);  

      $('#user-role').attr('disabled', 'disabled');  
      $('#user-email').attr('readonly', 'true'); 

      $(".offcanvas-body #loader").remove();
      $("#addNewUserForm").show();  
    });
  });

  // changing the title
  $('.add-new').on('click', function () {
    $('#user_id').val(''); //reseting input field
  
    $('#user-firstname').val('');
    $('#user-lastname').val('');
    $('#user-email').val('');
    $('#user-telephone').val('');
    $('#user-designation').val('');
    $('#user-lang').val('en');
    $('#user-status').val('1');  
    //$('#user-role').val('company-admin');
    $('#user-role').find('option.text-option').remove();
    $('#user-role').prop('selectedIndex', 0);

    $('#user-email').removeAttr('readonly');  
    $('#user-role').removeAttr('disabled');  
    $('#offcanvasUserLabel').html('Add New User');
  });

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);
// });

// // Validation & Phone mask
// (function () {
  const phoneMaskList = document.querySelectorAll('.phone-mask'),
    addNewUserForm = document.getElementById('addNewUserForm');

  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      new Cleave(phoneMask, {
        phone: true,
        phoneRegionCode: 'US'
      });
    });
  }
  // Add New User Form Validation
  const fv = FormValidation.formValidation(addNewUserForm, {
    fields: {
      firstname: {
        validators: {
          notEmpty: {
            message: 'Please enter first name '
          }
        }
      },
      lastname: {
        validators: {
          notEmpty: {
            message: 'Please enter last name '
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'The value is not a valid email address'
          }
        }
      },
      telephone: {
        validators: {
          notEmpty: {
            enabled: ($('#addNewUserForm [name="role"]').val() == 'client-user') ? false : true,
            message: 'Please enter telephone'
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
      
      // $("#addNewUserForm").find('button.data-submit').html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
      //         'Submitting...');

      var btn_submit = $('#addNewUserForm').find("button.data-submit");
      btn_submit.attr("disabled", "disabled");
      btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Submitting...');

    // adding or updating user when form successfully validate
    $.ajax({
      data: $('#addNewUserForm').serialize(),
      url: `${baseUrl}dv-user`,
      type: 'POST',
      success: function (result) {
        //dt_user.draw();
        
        var status = result['message'];

        if($('#user_contact_tab').val() == "0")
        {
          user_datas = drawDtTable(result, 'user');
          dt_user.clear().rows.add(user_datas).draw();
        }
        else
        {          
          contact_datas = drawDtTable(result, 'contacts');
          //dt_contacts.clear().rows.add(contact_datas).draw();
          $(".datatables-contacts").DataTable().clear().rows.add(contact_datas).draw();
        }

        $('#user-email').removeAttr('readonly');  
        offCanvasForm.offcanvas('hide');

        refreshSelect();

        //$("#addNewUserForm").find('button.data-submit').html('Submit');

        btn_submit.removeAttr("disabled");
        btn_submit.html('Submit');

        if(result['status'] == 200)
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `User ${status} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        else
        { console.log(result);
          if(result['message'] == "Already exists")
            Swal.fire({
              title: 'Duplicate Entry!',
              text: 'Your email should be unique.',              
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          else if(result['message'] == "Deleted user")
            Swal.fire({
              title: 'Duplicate Entry!',
              text: 'The email address already exists and is linked to a deleted user account. Kindly reach out to the development team for support.',              
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          else
            Swal.fire({            
              title: 'Error!',
              text: 'Invalid email. Cannot send email.' + result['message'],
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
        }
      },
      error: function (result) {
        
        //dt_user.draw();
        var err = result['message'];
        user_datas = drawDtTable(result, 'user');
        dt_user.clear().rows.add(user_datas).draw();

        $('#user-email').removeAttr('readonly');  
        offCanvasForm.offcanvas('hide');
        refreshSelect();
        //$("#addNewUserForm").find('button.data-submit').html('Submit');

        btn_submit.removeAttr("disabled");
        btn_submit.html('Submit');

        Swal.fire({
          title: (err) ? 'Cannot send email' : 'Duplicate Entry!',
          text: (err) ? err.responseJSON.message : 'Your email should be unique.',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

    addNewUserForm.querySelector('[name="role"]').addEventListener('change', function (e) {
      
        // Enable or disable validators for the `telephone` field
        (e.target.value == 'client-user') ? fv.disableValidator('telephone') : fv.enableValidator('telephone');

        // Revalidate the `telephone` field
        fv.revalidateField('telephone');
    });

  function refreshSelect()
  {
    //Refresh VAT Reg. Modal       
    $.get(`${baseUrl}team-user`, function (data) { 
      $('#select2Basic').html("");
      var options = "";
      $.each(data, function (index, item) {            
        options += '<option value="'+item['id']+'" data-id="'+item['id']+'" data-name="'+item['firstname'] + ' ' + item['lastname'] +'" data-image="'+item['name'].substring(0,2)+'">'+item['firstname'] + ' ' + item['lastname'] +'</option>';
      });
      $('#select2Basic').html(options);    
    });

    //Refresh Team User Modal       
    $.get(`${baseUrl}company-admin`, function (data) { 
      $('#select2Company').html("");
      var options = "";
      $.each(data, function (index, item) {            
        options += '<option value="'+item['id']+'" data-id="'+item['id']+'" data-name="'+item['firstname'] + ' ' + item['lastname'] +'" data-image="'+item['name'].substring(0,2)+'">'+item['firstname'] + ' ' + item['lastname'] +'</option>';
      });
      $('#select2Company').html(options);    
    });

    //Refresh Client Modal       
    $.get(`${baseUrl}client-user`, function (data) { 
      $('#select2Client').html("");
      var options = "";
      $.each(data, function (index, item) {            
        options += '<option value="'+item['id']+'" data-id="'+item['id']+'" data-name="'+item['firstname'] + ' ' + item['lastname'] +'" data-image="'+item['name'].substring(0,2)+'">'+item['firstname'] + ' ' + item['lastname'] +'</option>';
      });
      $('#select2Client').html(options);    
    });
  } 

  // clearing form data when offcanvas hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
  });

  // Assign VAT Reg.
  $(document).on('click', '.assign-vatreg', function () {
    var btn_assign_vatreg = $(this);
    btn_assign_vatreg.attr('disabled', 'disabled');
    btn_assign_vatreg.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Assigning...');

    $.ajax({
      data: $('#assignVATRegForm').serialize(),
      url: `${baseUrl}dv-user/assign`,
      type: 'POST',
      success: function (result) {
        //dt_user.draw();        

        var status = result['message'];
        user_datas = drawDtTable(result, 'user');
        dt_user.clear().rows.add(user_datas).draw();

        $("#assignVATReg").modal('hide');

        btn_assign_vatreg.removeAttr('disabled');               
        btn_assign_vatreg.html("Assign");

        if(status == "Not Selected")
          // sweetalert
          Swal.fire({
            icon: 'error',
            title: `${status}!`,
            text: `VAT Reg. ${status}`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        else
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Team User ${status} to VAT Reg. Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });  
      },
      error: function (err) {
        $("#assignVATReg").modal('hide');        
      }
    });
  });

  // Assign Team User
  $(document).on('click', '.assign-team-user', function () {
    var btn_assign_team_user = $(this);
    btn_assign_team_user.attr('disabled', 'disabled');
    btn_assign_team_user.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Assigning...');

    $.ajax({
      data: $('#assignTeamUserForm').serialize(),
      url: `${baseUrl}company/assign`,
      type: 'POST',
      success: function (result) {
        //dt_user.draw();        

        var status = result['message'];
        user_datas = drawDtTable(result, 'user');
        dt_user.clear().rows.add(user_datas).draw();

        $("#assignTeamUser").modal('hide');

        btn_assign_team_user.removeAttr('disabled');               
        btn_assign_team_user.html("Assign");

        if(status == "Not Selected")
          // sweetalert
          Swal.fire({
            icon: 'error',
            title: `${status}!`,
            text: `Team User ${status}`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        else
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Company ${status} to Team User Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });  
      },
      error: function (err) {
        $("#assignTeamUser").modal('hide');       
      }
    });
  });

  // Assign Client
  $(document).on('click', '.assign-client', function () {
    var btn_assign_client = $(this);
    btn_assign_client.attr('disabled', 'disabled');
    btn_assign_client.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Assigning...');

    $.ajax({
      data: $('#assignClientForm').serialize(),
      url: `${baseUrl}client/assign`,
      type: 'POST',
      success: function (result) {
        //dt_user.draw();        

        var status = result['message'];
        user_datas = drawDtTable(result, 'user');
        dt_user.clear().rows.add(user_datas).draw();

        $("#assignClient").modal('hide');

        btn_assign_client.removeAttr('disabled');               
        btn_assign_client.html("Assign");

        if(status == "Not Selected")
          // sweetalert
          Swal.fire({
            icon: 'error',
            title: `${status}!`,
            text: `Client ${status}`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        else
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Client User ${status} to Client Company Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });  
      },
      error: function (err) {
        $("#assignClient").modal('hide');        
      }
    });
  });
  
  let iti;
  if(iti != null){
    iti.destroy();
  }
  function initializeUserTel() {
      var input = document.getElementById("user-telephone");
      iti = intlTelInput(input, {
          initialCountry: "DK",
          preferredCountries: ["dk"],
          separateDialCode: true,
          utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"    
      });
  }
  initializeUserTel();

  // notification settings
  $(document).on('click', '.notification-settings', function () {
    var user_id = $(this).data('id');   
    $(".form-notification #user-id").val(user_id);   

    if($(".form-notification #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertBefore(".form-notification .table-responsive");  
    } 

    $(".form-notification .table-responsive").hide();

    $.ajax({      
      url: `${userView}notification/${user_id}`,
      type: 'GET',
      success: function (result) {        
        $('input.chk-email-notification:checkbox').prop('checked', false);
        $.each(result, function(key, item) {          
          if(item['email_notification'])
            $("input#chk-email-notification-"+ item['file_type'] +":checkbox").prop('checked', true);
          else
            $("input#chk-email-notification-"+ item['file_type'] +":checkbox").prop('checked', false);          
        });

        $(".form-notification #loader").remove();
        $(".form-notification .table-responsive").show();
      },
      error: function (err) {
        console.log(err);
      }
    });
  });

  // Checkbox - notification
  $(document).on('click', '#chk-email-notification', function () {
    var checked = $(this).prop('checked');
    $('input.chk-email-notification:checkbox').prop('checked', checked);   
  });
  
  //Form  - notification
  $(document).on("submit", ".form-notification", function(event)
  {
    event.preventDefault();

    var form = $(this);        
    var user_id = form.find('#user-id').val();

    var btn_save_notification = form.find("button");
    btn_save_notification.attr('disabled', 'disabled');
    btn_save_notification.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');

    $.ajax({
      url: `${userView}notification/${user_id}`,
      type: 'POST',     
      data: form.serialize(),     
      success: function (result) {
        //dt_user.draw();        

        var status = result['message'];
        user_datas = drawDtTable(result, 'user');
        dt_user.clear().rows.add(user_datas).draw();

        $("#notificationSettings").modal('hide');
        
        btn_save_notification.removeAttr('disabled');               
        btn_save_notification.html("Save changes");
        
        if(status == "Updated")
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `User Notification ${status} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        else  
          // sweetalert
          Swal.fire({
            icon: 'error',
            title: `${status}!`,
            text: `User Notification ${status}`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
      },
      error: function (error) {
        console.log(error);
      }
    }); 
  });  

});//();
