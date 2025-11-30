/**
 * Page Client User List For Contacts
 */

'use strict';

// Datatable (jquery)
$(function () {
  $("#navs-pills-top-contacts .sk-bounce").show();

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
  var dt_contacts_table = $('.datatables-contacts'),
    select2 = $('.select2'),  
    userView = baseUrl + 'dv-user/',  
    statusObj = {      
      0: { title: 'Inactive', class: 'bg-label-secondary' },
      1: { title: 'Active', class: 'bg-label-success' }      
    };

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Contacts datatable
  if (dt_contacts_table.length) {
    var dt_contacts = dt_contacts_table.DataTable({  
      data: contact_datas,    
      processing: true,
      // serverSide: true,
      // ajax: {      
      //   url: baseUrl + 'contacts/' + $('#client_id').val()
      // },
      searching: false,
      columns: [
        // columns according to JSON  
        { data: 'Id' },            
        { data: 'user' },        
        { data: 'telephone' },        
        { data: 'status' },
        { data: 'action' } 
      ],
      lengthMenu: [
          [10, 25, 50, 100, -1],
          [10, 25, 50, 100, 'All']
      ],
      pageLength: -1,
      columnDefs: [       
        {
          searchable: false,
          orderable: false,
          targets: 0,   
          visible: false,       
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },          
        {
          // User name and email
          targets: 1,
          responsivePriority: 4,
          width: "30%",   
          orderable: false,
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
            var $row_output = '-';
            if($name != "")
              $row_output =
                '<div class="d-flex justify-content-start align-items-center user-name">' +
                '<div class="avatar-wrapper">' +
                '<div class="avatar avatar-sm me-3">' +
                $output +
                '</div>' +
                '</div>' +
                '<div class="d-flex flex-column">' +
                //'<a href="' +
                //userView +
                //'" class="text-body text-truncate">' +
                '<span class="fw-semibold">' +
                $name +
                //'</span></a>' +
                '</span>' +
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
          // Telephone
          targets: 2,
          orderable: false,
          width: "10%",
          render: function (data, type, full, meta) {
            var $telephone = full['telephone'];

            return '<span class="fw-semibold">' + $telephone + '</span>';
          }
        },              
        {
          // User Status
          targets: 3,
          orderable: false,
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
           
            //if(full['role'] == 'team-user' || full['role'] == 'company-admin' || full['role'] == 'client-user')
              buttons +='<button class="btn btn-sm btn-icon edit-record" data-id="'+full['id']+'" data-user_role="client-user" data-client_id="'+full['client_id']+'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser" title="Edit"><i class="bx bx-edit"></i></button>';
                  //'<button class="btn btn-sm btn-icon delete-record" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';
            
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
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column m-3 mb-md-0"fB>>' +
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
          text: '<i class="bx bx-plus me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Assign Client Users</span>',
          className: 'assign-client-users btn btn-primary mx-3', 
          action: function (e, node, config){
            $('#assignClientUser').modal('show')
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
        // Adding status filter once table initialized
        this.api()
          .columns(3)
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

          $("#navs-pills-top-contacts .sk-bounce").hide();
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
   
  // Assign Client
  $(document).on('click', '.assign-client-user', function () {
    $.ajax({
      data: $('#assignClientUserForm').serialize(),
      url: `${baseUrl}client-user/assign`,
      type: 'POST',
      success: function (result) {
        var status = result['message'];

        contact_datas = drawDtTable(result, 'contacts');
        /*
        var client = result['client'];
        var userclient = client['userclient'];

        contact_datas = [];
        var contact_start = 1;
        $.each(userclient, function (idx, contact) {
          var user = contact['user'];      
          var dvuser = user['dvuser'];

          contact_datas.push({         
                'id' : contact['id'],
                'fake_id' : contact_start,
                'name' : dvuser['firstname'] + ' ' + dvuser['lastname'],       
                'firstname' : dvuser['firstname'],           
                'lastname' : dvuser['lastname'],
                'email' : user['email'],
                'telephone' : (dvuser['telephone'] == null) ? "-" : dvuser['telephone'],            
                'status' : parseInt(dvuser['status'])
              });
          contact_start = contact_start + 1;                    
        });
        */
        dt_contacts.clear().rows.add(contact_datas).draw();
        //dt_contacts.draw();        
        $("#assignClientUser").modal('hide');

        if(status == "Not Selected")
          // sweetalert
          Swal.fire({
            icon: 'error',
            title: `${status}!`,
            text: `Client user ${status}`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        else
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `Company ${status} to Client User Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });  
      },
      error: function (err) {
        $("#assignClientUser").modal('hide');        
      }
    });
  });
   
});//();
