/**
 * Page Client List
 */

'use strict';

// Datatable (jquery)
$(function () {  
  $(".sk-bounce").show();
  
  // Variable declaration for table
  var dt_client_table = $('.datatables-clients'),
    //dt_my_company_table = $('.datatables-my-companies'),
    dt_other_company_table = $('.datatables-other-companies'),
    select2 = $('.select2'),
    clientView = baseUrl + 'company/',
    flagUrl = baseUrl + 'assets/img/flags/',
    //offCanvasForm = $('#offcanvasAddClient'),
    statusObj = {     
      0: { title: 'Inactive', class: 'bg-label-secondary' },
      1: { title: 'Active', class: 'bg-label-success' }
    };

  if (select2.length) {
    var $this = select2;
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'Select Country',
      dropdownParent: $this.parent()
    });
  }

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Clients datatable
  if (dt_client_table.length) {    
    
    var dt_client = dt_client_table.DataTable({
      data: company_datas, 
      processing: true,
      //serverSide: true,
      autoWidth : false,
      // ajax: {
      //   url: baseUrl + 'client'
      // },
      columns: [
        // columns according to JSON      
        { data: 'id' },
        { data: 'vat_country' },        
        { data: 'client_name' },       
        { data: 'trading_name' },    
        { data: 'status', visible: ($("#auth_role").val() == 'super-admin' || $("#auth_role").val() == 'client-user') ? true : false },
        { data: 'vat_country_status' }
      ],
      //pageLength: 100,
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
        //   searchable: false,
        //   orderable: false,
        //   targets: 0,         
        //   visible: false,
        //   render: function (data, type, full, meta) {
        //     return `<span>${full.fake_id}</span>`;
        //   }
        // },       
        {
          // Client name         
          //targets: 15,
          targets: 2,
          width: "40%",
          responsivePriority: 4,
          className: "click",
          render: function (data, type, full, meta) {
            var $client_name = full['client_name'];

            // For Avatar badge
            var stateNum = Math.floor(Math.random() * 6);
            var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            var $state = states[stateNum],
              $client_name = full['client_name'],
              $initials = $client_name.match(/\b\w/g) || [],
              $output;
            $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
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
          //VAT Country.          
          targets: 1,
          width: "30%",
          searchable: false,     
          className: "click",     
          render: function (data, type, full, meta) {
            var $vat_country = '';
            if(full['vat_country'] == '-')
              $vat_country = full['vat_country'];
            else
            {
              var vat_countries = full['vat_country'].split(' '); 

              var vat_countries_status = full['vat_country_status']; 
              
              $.each(vat_countries, function (index, item) {  
                var status_style = (vat_countries_status[index]) ? '' : 'style="opacity: 0.3;"';

                $vat_country += '<div class="d-inline-flex align-middle my-1" '+ status_style +'>' +
                                  '<img src="'+ flagUrl + item +'.png" alt="'+ item +'" title="'+ item +'" class="country-flag me-2"><span class="align-middle me-4">  ' + item + '</span>' +
                                '</div>';
              });
            }
            return '<span class="client-vat_country">' + $vat_country + '</span>';           
          }
        },  
        {
          // Trading Name
          targets: 3,  
          width: "20%",   
          className: "click",             
          render: function (data, type, full, meta) {
            var $trading_name = (full['trading_name']) ? full['trading_name'] : '-';

            return '<span class="client-trading_name">' + $trading_name + '</span>';
          }
        },        
        {
          // User Status
          //targets: 17, 
          targets: 4, 
          width: "10%",  
          visible: ($("#auth_role").val() == 'super-admin' || $("#auth_role").val() == 'client-user') ? true : false,      
          orderable: false,                     
          render: function (data, type, full, meta) {           
            var $status = full['status'];
            var client_id = full['id'];

            var statuscheck = ($status) ? 'checked=checked' : "";
            //var statusdisable = (full['role'] == 'super-admin') ? '' : ' disabled=disabled';
            var statusdisable = ($("#auth_role").val() == 'super-admin' || $("#auth_role").val() == 'client-user') ? '' : ' disabled=disabled';
            var switchtext = '<label class="switch">' +
              '<input type="checkbox" class="switch-input status" '+ statuscheck + statusdisable + '  data-client_id="'+ client_id +'" />' +
              '<span class="switch-toggle-slider">' +
                '<span class="switch-on"></span>' +
                '<span class="switch-off"></span>' +
              '</span>' +              
            '</label>';       

            return switchtext;                    
          }
        },
        {
          // VAT reg. main Status
          targets: 5, 
          visible: false
        }          
      ],
      order: [[2, 'asc']],
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
          text: '<i class="bx bx-plus me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add New Company</span>',
          className: 'add-new btn btn-primary mx-3',          
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
        if($("#btn-my-companies span").length > 0)
        {
          var company_total = this.api().data().length;
          $("#btn-my-companies span").html(company_total);
        }

        $(".sk-bounce").hide();
        $("#client-page").show();
        dt_client_table.show();    
      },      
      drawCallback: function(settings){ 
        if($("#auth_role").val() == 'team-user' || $("#auth_role").val() == 'client-user') 
          $('#DataTables_Table_0_length, #DataTables_Table_0_paginate, #DataTables_Table_0_info, .dt-buttons').hide();
        // if(settings.json.data.length > 0)
        // { 
        //   if(settings.json.data[0].role == 'team-user'){              
        //       $('#DataTables_Table_0_length, #DataTables_Table_0_paginate, #DataTables_Table_0_info, .dt-buttons').hide();
        //   }
        // }
      }
    });
    
  }

  //Row Click  
  dt_client_table.on('click', 'tbody tr td.click', function (e) {  
    var data = $(this).parent().find("td").find("div.client-name").data();    
       
    //if(data['status'] == 1)     
      window.location.href = `${clientView}` + data['client_id']; 
  });

  // Update Status
  $(document).on('click', '.switch-input.status', function () {
    
    var statusCheckbox = $(this);
    var status = statusCheckbox.prop("checked");
    var statustext = status ? "Activate" : "Deactivate";

    var data = statusCheckbox.data();
    var client_id = data['client_id'];
    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ statustext +" the company!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ statustext +' it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        //update the status
        $.ajax({
          type: 'PUT',
          data: {status: status, statustext: statustext},  
          url: `${baseUrl}company/${client_id}/updatestatus`,
          dataType: 'json',
          success: function (result) {
            var status = result['message'];
            company_datas = drawDtTable(result, 'companies');
            dt_client.clear().rows.add(company_datas).draw();

            //dt_client.draw();
            
            $('[data-client_id="'+ client_id +'"]').removeAttr('data-status');
            $('[data-client_id="'+ client_id +'"]').attr('data-status', ((status) ? 1 : 0));

            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: statustext + 'd!',
              text: 'The company has been '+ statustext +'d!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (xhr, status, error) {
            var err = JSON.parse(xhr.responseText);

            dt_client.draw();
            Swal.fire({
              title: 'Cancelled',
              text: err.message,
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }          
        });

        
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        statusCheckbox.prop("checked", !status);

        Swal.fire({
          title: 'Cancelled',
          text: 'The company is not '+ statustext +'d!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Add New Record
  $(document).on('click', '.add-new', function () {
    window.location.href = `${baseUrl}company/create`; 
  });

  // // Delete Record
  // $(document).on('click', '.delete-record', function () {
  //   var client_id = $(this).data('id'),
  //     dtrModal = $('.dtr-bs-modal.show');

  //   // hide responsive modal in small screen
  //   if (dtrModal.length) {
  //     dtrModal.modal('hide');
  //   }

  //   // sweetalert for confirmation of delete
  //   Swal.fire({
  //     title: 'Are you sure?',
  //     text: "You won't be able to revert this!",
  //     icon: 'warning',
  //     showCancelButton: true,
  //     confirmButtonText: 'Yes, delete it!',
  //     customClass: {
  //       confirmButton: 'btn btn-primary me-3',
  //       cancelButton: 'btn btn-label-secondary'
  //     },
  //     buttonsStyling: false
  //   }).then(function (result) {
  //     if (result.value) {
  //       // delete the data
  //       $.ajax({
  //         type: 'DELETE',
  //         url: `${baseUrl}company/${client_id}`,
  //         success: function () {
  //           dt_client.draw();
  //         },
  //         error: function (error) {
  //           console.log(error);
  //         }
  //       });

  //       // success sweetalert
  //       Swal.fire({
  //         icon: 'success',
  //         title: 'Deleted!',
  //         text: 'The company has been deleted!',
  //         customClass: {
  //           confirmButton: 'btn btn-success'
  //         }
  //       });
  //     } else if (result.dismiss === Swal.DismissReason.cancel) {
  //       Swal.fire({
  //         title: 'Cancelled',
  //         text: 'The company is not deleted!',
  //         icon: 'error',
  //         customClass: {
  //           confirmButton: 'btn btn-success'
  //         }
  //       });
  //     }
  //   });
  // });

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  // Assign
  $(document).on('click', '.assign-team-user', function () {
    $.ajax({
      data: $('#assignTeamUserForm').serialize(),
      url: `${baseUrl}company/assign`,
      type: 'POST',
      success: function (status) {
        dt_client.draw();        
        $("#assignTeamUser").modal('hide');

        // sweetalert
        Swal.fire({
          icon: 'success',
          title: `Successfully ${status}!`,
          text: `Company ${status} to Team Users Successfully.`,
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

  //Companies - For TEAM USER
  // Other Companies datatable
  if (dt_other_company_table.length) {    
    
    var dt_other_company = dt_other_company_table.DataTable({
      data: other_company_datas, 
      processing: true,     
      autoWidth : false,      
      columns: [
        // columns according to JSON      
        { data: 'id' },
        { data: 'vat_country' },
        { data: 'client_name' },       
        { data: 'trading_name' },   
        { data: 'vat_country_status' }         
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
          //responsivePriority: 2,
          targets: 0,
          //visible: false
          render: function (data, type, full, meta) {
            return '';
          }
        }, 
        {
          //VAT Country.          
          targets: 1,
          width: "30%",
          searchable: false,     
          className: "click",     
          render: function (data, type, full, meta) {
            var $vat_country = '';
            if(full['vat_country'] == '-')
              $vat_country = full['vat_country'];
            else
            {
              var vat_countries = full['vat_country'].split(' ');

              var vat_countries_status = full['vat_country_status']; 

              $.each(vat_countries, function (index, item) {   
                var status_style = (vat_countries_status[index]) ? '' : 'style="opacity: 0.3;"';
                
                $vat_country += '<div class="d-inline-flex align-middle my-1" '+ status_style +'>' +
                                  '<img src="'+ flagUrl + item +'.png" alt="'+ item +'" title="'+ item +'" class="country-flag me-2"><span class="align-middle me-4">  ' + item + '</span>' +
                                '</div>';
              });
            }
            return '<span class="client-vat_country">' + $vat_country + '</span>';           
          }
        },              
        {
          // Client name                   
          targets: 2,
          width: "40%",
          responsivePriority: 4,
          className: "click",
          render: function (data, type, full, meta) {
            var $client_name = full['client_name'];

            // For Avatar badge
            var stateNum = Math.floor(Math.random() * 6);
            var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            var $state = states[stateNum],
              $client_name = full['client_name'],
              $initials = $client_name.match(/\b\w/g) || [],
              $output;
            $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
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
          // Trading Name
          targets: 3,  
          width: "20%",   
          className: "click",             
          render: function (data, type, full, meta) {
            var $trading_name = (full['trading_name']) ? full['trading_name'] : '-';

            return '<span class="client-trading_name">' + $trading_name + '</span>';
          }
        },  
        {
          // VAT reg. main Status
          targets: 4, 
          visible: false
        }             
      ],
      order: [[2, 'asc']],
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
        var other_company_total = this.api().data().length;
        $("#btn-other-companies span").html(other_company_total);

        $(".sk-bounce").hide();
        $("#client-page").show();
        dt_other_company_table.show();    
      },      
      drawCallback: function(settings){ 
        if($("#auth_role").val() == 'team-user' || $("#auth_role").val() == 'client-user') 
          $('#DataTables_Table_1_length, #DataTables_Table_1_paginate, #DataTables_Table_1_info, .dt-buttons').hide();        
      }
    });
    
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (event) {
      var tabID = $(event.target).attr('data-bs-target');
      if (tabID === '#navs-other-companies' ) {
        dt_other_company.columns.adjust().responsive.recalc();
      }
    });
  }

  //Row Click  
  dt_other_company_table.on('click', 'tbody tr td.click', function (e) {  
    var data = $(this).parent().find("td").find("div.client-name").data();    
       
    if(data['status'] == 1)     
      window.location.href = `${clientView}` + data['client_id']; 
  });
});
