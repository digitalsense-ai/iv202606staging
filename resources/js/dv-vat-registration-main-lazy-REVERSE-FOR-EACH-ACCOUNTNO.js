/**
 * Page VAT Registrations Main List
 */

'use strict';

// Datatable (jquery)
$(function () {
  $("#navs-pills-top-vatregistrations .sk-bounce").show();

  // Variable declaration for table
  var dt_vat_registration_main_table = $('.datatable-vat-registration-main'),
    vatRegistrationMainView = baseUrl + 'vat-registration-main/',               
    flagUrl = baseUrl + 'assets/img/flags/',   
    statusObj = {        
      0: { title: 'Inactive', class: 'bg-label-dark' },         
      1: { title: 'Active', class: 'bg-label-secondary' },      
    },
    typeObj = {        
      'Basic': { title: 'Basic', class: 'bg-label-primary' },         
      'Pro': { title: 'Pro', class: 'bg-label-danger' },      
    },
    productTypeObj = {        
      1: { title: 'NUF VAT Return', class: 'bg-label-primary' },         
      2: { title: 'Import Reconciliation', class: 'bg-label-primary' },
      3: { title: 'NUF VAT Return & Import Reconciliation', class: 'bg-label-danger' },     
      4: { title: 'VOEC VAT Return', class: 'bg-label-primary' },     
      5: { title: 'VOEC VAT Return & Import Reconciliation', class: 'bg-label-secondary' }
    };
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  

  // VAT Registrations datatable
  if (dt_vat_registration_main_table.length) {
    var dt_vat_registration_main = dt_vat_registration_main_table.DataTable({
      data: vat_reg_main_datas, 
      processing: true,
      //autoWidth : false,
      // serverSide: true,
      // ajax: {
      //   url: baseUrl + 'client-vat-registration-main/' + $('#client_id').val()
      // },
      columns: [
        // columns according to JSON       
        { data: 'id' },
        //{ data: 'client_id' },
        //{ data: 'client_name' },       
        { data: 'country' },
        { data: 'team_users' },                        
        { data: 'service_start' },
        //{ data: 'turnover_date' },
        { data: 'general_periods' },   
        //{ data: 'vat_reg_type' },   
        { data: 'product_type' },   
        { data: 'status' }, 
        { data: 'oss' },        
        { data: 'excise_duty', visible: (excise_duty_onoff == 1) ? true : false }, 
        { data: 'cash_account_statement', visible: (cas_dda_onoff == 1) ? true : false , title: cas_dda_title},          
        { data: 'action' }
      ],
      lengthMenu: [
          [10, 25, 50, 100, -1],
          [10, 25, 50, 100, 'All']
      ],
      pageLength: -1,
      columnDefs: [        
        // {
        //   searchable: false,
        //   orderable: false,
        //   targets: 0,
        //   visible: false,
        //   className: "click",     
        //   render: function (data, type, full, meta) {
        //     return `<span>${full.fake_id}</span>`;
        //   }
        // },
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
        //   //Client ID
        //   targets: 1,
        //   searchable: false,
        //   visible: false,
        //   render: function (data, type, full, meta) {
        //     var $client_id = full['client_id'];

        //     return '<span class="vat-client_id">' + $client_id + '</span>';
        //   }
        // },  
        // {
        //   //Client Name
        //   targets: 2,
        //   searchable: false,
        //   visible: false,
        //   render: function (data, type, full, meta) {
        //     var $client_name = full['client_name'];

        //     return '<span class="vat-client_name">' + $client_name + '</span>';
        //   }
        // },   
        {
          // Country
          targets: 1,
          responsivePriority: 4,
          orderable: false,
          className: "click", 
          width: "10%",
          render: function (data, type, full, meta) {
            var $country = full['country'];


            var $vat_country = '<img src="'+ flagUrl + $country +'.png" alt="'+ $country +'" title="'+ $country +'" class="country-flag me-2"><span class="align-middle me-4">  ' + $country + '</span>';
            return '<span class="client-vat_country">' + $vat_country + '</span>'; 
          }
        },   
        {
          //Team Users
          targets: 2,   
          searchable: false,    
          orderable: false,   
          className: "click",          
          render: function (data, type, full, meta) {
            var $team_users = full['team_users'];

            if($team_users == '')
              return '<span class="vat-team_users">-</span>';
            else  
              return '<span class="vat-team_users">' + $team_users.join(',<br>') + '</span>';
          }
        },       
        {
          //Service Start
          targets: 3,
          searchable: false,    
          orderable: false,   
          className: "click",        
          render: function (data, type, full, meta) {
            var $service_start = full['service_start'];

            return '<span class="vat-service_start">' + $service_start + '</span>';
          }
        },  
        // {
        //   //Turnover Date
        //   targets: 4,
        //   searchable: false,  
        //   orderable: false,   
        //   visible: false,
        //   className: "click",          
        //   render: function (data, type, full, meta) {
        //     var $turnover_date = full['turnover_date'];

        //     return '<span class="vat-turnover_date">' + $turnover_date + '</span>';
        //   }
        // },  
        {
          //General Periods
          targets: 4,
          searchable: false,   
          orderable: false,   
          className: "click",         
          render: function (data, type, full, meta) {
            var $general_periods = full['general_periods'];

            return '<span class="vat-general_periods">' + $general_periods + '</span>';
          }
        },  
        // {
        //   //Vat reg. Type
        //   targets: 5,
        //   searchable: false,   
        //   orderable: false,   
        //   className: "click",         
        //   render: function (data, type, full, meta) {
        //     var $vat_reg_type = full['vat_reg_type'];

        //     return '<span class="badge rounded-pill '+ typeObj[$vat_reg_type].class +' vat-vat_reg_type">' + $vat_reg_type + '</span>';
        //   }
        // },       
        {
          //Product Type
          targets: 5,
          searchable: false,   
          orderable: false,   
          className: "click",         
          render: function (data, type, full, meta) {
            var $product_type = full['product_type'];
            var $country = full['country'];

            // if($product_type == 3 || $product_type == 5)  
            // {           
            //   return '<span class="badge rounded-pill '+ productTypeObj[$product_type].class +' vat-product_type">' + 
            //             productTypeObj[1].title + ' & ' + productTypeObj[2].title + 
            //           '</span>';
            // }
            // else
              return '<span class="badge rounded-pill '+ productTypeObj[$product_type].class +' vat-product_type">' + 
                        (($country == 'NO') ? productTypeObj[$product_type].title : productTypeObj[$product_type].title.replace('NUF', '')) + 
                      '</span>';
          }
        },    
        {
          // Status
          targets: 6,
          searchable: false, 
          orderable: false,                          
          render: function (data, type, full, meta) {           
            var $status = full['status'];
            var vat_reg_main_id = full['id'];

            var statuscheck = ($status) ? 'checked=checked' : "";           
            var switchtext = '<label class="switch">' +
              '<input type="checkbox" class="switch-input status" '+ statuscheck + '  data-id="'+ vat_reg_main_id +'" />' +
              '<span class="switch-toggle-slider">' +
                '<span class="switch-on"></span>' +
                '<span class="switch-off"></span>' +
              '</span>' +              
            '</label>';       

            return switchtext;                  
          }
        }, 
        {
          // OSS
          targets: 7,
          searchable: false, 
          orderable: false,                          
          render: function (data, type, full, meta) {
            var $oss = full['oss'];
            var vat_reg_main_id = full['id'];

            var oss_check = ($oss) ? 'checked=checked' : "";             
            var oss_text = '';        
            
            oss_text = '<div class="form-check m-0">' +
                              '<input class="form-check-input chk_oss" type="checkbox" value="" '+ oss_check + ' data-id="'+ vat_reg_main_id +'">' +
                              '<label class="form-check-label" for="chk_oss"></label>' +
                            '</div>';
            
            return oss_text;
          }
        }, 
        {
          // Excise Duty
          targets: 8,
          searchable: false, 
          orderable: false,                          
          render: function (data, type, full, meta) {    
            var country = full['country'];

            if(country == 'DK')  
            {     
              var $excise_duty = full['excise_duty'];
              var vat_reg_main_id = full['id'];

              var excise_duty_check = ($excise_duty) ? 'checked=checked' : "";             
              var excise_duty_text = '';        
              
              excise_duty_text = '<div class="form-check m-0">' +
                                '<input class="form-check-input chk_excise_duty" type="checkbox" value="" '+ excise_duty_check + ' data-id="'+ vat_reg_main_id +'">' +
                                '<label class="form-check-label" for="chk_excise_duty"></label>' +
                              '</div>';
              
              return excise_duty_text;
            }            
            else
              return '';
          }
        }, 
        {
          // Cash Account Statement/Duty Deferment Account
          targets: 9,
          searchable: false, 
          orderable: false,                          
          render: function (data, type, full, meta) {    
            var country = full['country'];

            if(country == 'GB')  
            {     
              var $cash_account_statement = full['cash_acc_stmt'];
              var vat_reg_main_id = full['id'];
              

              var cash_account_statement_check = ($cash_account_statement) ? 'checked=checked' : "";             
              var cash_account_statement_text = '';        
              
              cash_account_statement_text = '<div class="form-check m-0">' +
                                '<input class="form-check-input chk_cash_account_statement" type="checkbox" value="" '+ cash_account_statement_check + ' data-id="'+ vat_reg_main_id +'">' +
                                '<label class="form-check-label" for="chk_cash_account_statement"></label>' +
                              '</div>';
              
              return cash_account_statement_text;
            }
            else if(country == 'NO')  
            {
              var $duty_deferment_account = full['duty_defer_acc'];
              var vat_reg_main_id = full['id'];
              

              var duty_deferment_account_check = ($duty_deferment_account) ? 'checked=checked' : "";             
              var duty_deferment_account_text = '';        
              
              duty_deferment_account_text = '<div class="form-check m-0">' +
                                '<input class="form-check-input chk_duty_deferment_account" type="checkbox" value="" '+ duty_deferment_account_check + ' data-id="'+ vat_reg_main_id +'">' +
                                '<label class="form-check-label" for="chk_duty_deferment_account"></label>' +
                              '</div>';
              
              return duty_deferment_account_text;
            }
            else
              return '';
          }
        }, 
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            var btns = '';

            //if(full['status'] == 1)                     
              btns = '<button class="btn btn-sm btn-icon edit-vatregmain" data-id="'+full['id']+'"><i class="bx bx-edit"></i></button>' +
                '<button class="btn btn-sm btn-icon delete-vatregmain" data-id="'+full['id']+'" data-client_id="'+full['client_id']+'"><i class="bx bx-trash"></i></button>';
            
            return (      
              btns
            );            
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
          text: '<i class="bx bx-plus me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">VAT Registration</span>',
          className: 'add-new btn btn-primary mx-3',
          attr: {            
            'data-client_id': $("#client_id").val()
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['client_name'] + data['country'];
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
          $("#navs-pills-top-vatregistrations .sk-bounce").hide();
          $("#navs-pills-top-vatregistrations .card").show();
          dt_vat_registration_main_table.show();          
      }
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (event) {
      var tabID = $(event.target).attr('data-bs-target');
      if (tabID === '#navs-pills-top-vatregistrations' ) {
        dt_vat_registration_main.columns.adjust().responsive.recalc();
      }
    });
  }

  //Row Click    
  dt_vat_registration_main_table.on('click', 'tbody tr td.click', function () {   
    
    dt_vat_registration_main_table.find("tbody tr").each(function( index ) {
      $(this).removeClass('selected');
      $(this).css('cursor','pointer');
    });
    $(this).addClass('selected');
    $(this).css('cursor','text'); 
  
    var parenttr = $(this).parent();
    
    //Redirect to New Page    
    if(parenttr.find("td .edit-vatregmain").length > 0)
    {
      var vat_registration_main_id = parenttr.find("td .edit-vatregmain").data('id');
      window.location.href = `${baseUrl}vat-registration-main/${vat_registration_main_id}/edit`;      
    }  //if edit exists
  });

  // Update Status
  $(document).on('click', '.switch-input.status', function () {
    
    var statusCheckbox = $(this);
    var status = statusCheckbox.prop("checked");
    var statustext = status ? "Activate" : "Deactivate";

    var data = statusCheckbox.data();
    var vat_reg_main_id = data['id'];
    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ statustext +" the VAT Registration!",
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
        $("#navs-pills-top-vatregistrations .sk-bounce").show();
        $("#vat-reg-lists").hide();

        //update the status
        $.ajax({
          type: 'PUT',
          data: {status: status, statustext: statustext},  
          url: `${baseUrl}vat-registration-main/${vat_reg_main_id}/updatestatus`,
          dataType: 'json',
          success: function (result) {
            vat_reg_main_datas = drawDtTable(result, 'vatregmain');
            dt_vat_registration_main.clear().rows.add(vat_reg_main_datas).draw();
            //dt_vat_registration_main.draw();
            
            $('[data-vat_reg_main_id="'+ vat_reg_main_id +'"]').attr('data-status', !status);

            $("#navs-pills-top-vatregistrations .sk-bounce").hide();
            $("#vat-reg-lists").show();

            if(result.status == 200)
              // success sweetalert
              Swal.fire({
                icon: 'success',
                title: statustext + 'd!',
                text: 'The VAT Registration has been '+ statustext +'d!',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            else
              Swal.fire({
                title: 'Cancelled',
                text: result.message,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });  
          },
          error: function (xhr, status, error) {
            var err = JSON.parse(xhr.responseText);

            dt_vat_registration_main.draw();
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

        $("#navs-pills-top-vatregistrations .sk-bounce").hide();
        $("#vat-reg-lists").show();

        Swal.fire({
          title: 'Cancelled',
          text: 'The VAT Registration is not '+ statustext +'d!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Update OSS
  $(document).on('click', '.form-check-input.chk_oss', function () {
    
    var ossCheckbox = $(this);
    var ossValue = ossCheckbox.prop("checked");
    var ossText = ossValue ? "Check" : "Uncheck";

    var data = ossCheckbox.data();
    var vat_reg_main_id = data['id'];

    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ ossText +" the oss for the VAT Registration!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ ossText +' it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $("#navs-pills-top-vatregistrations .sk-bounce").show();
        $("#vat-reg-lists").hide();

        //update the status
        $.ajax({
          type: 'PUT',
          data: {oss: ossValue, oss_text: ossText},  
          url: `${baseUrl}vat-registration-main/${vat_reg_main_id}/updateoss`,
          dataType: 'json',
          success: function (result) {
            vat_reg_main_datas = drawDtTable(result, 'vatregmain');
            dt_vat_registration_main.clear().rows.add(vat_reg_main_datas).draw();
            //dt_vat_registration_main.draw();
            
            $('[data-vat_reg_main_id="'+ vat_reg_main_id +'"]').attr('data-oss', !ossValue);

            $("#navs-pills-top-vatregistrations .sk-bounce").hide();
            $("#vat-reg-lists").show();    

            if(result.status == 200)
              // success sweetalert
              Swal.fire({
                icon: 'success',
                title: ossText + 'ed!',
                text: 'The oss for the VAT Registration has been '+ ossText +'ed!',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            else
              Swal.fire({
                title: 'Cancelled',
                text: result.message,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });            
          },
          error: function (xhr, status, error) {
            var err = JSON.parse(xhr.responseText);

            dt_vat_registration_main.draw();
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
        ossCheckbox.prop("checked", !ossValue);

        $("#navs-pills-top-vatregistrations .sk-bounce").hide();
        $("#vat-reg-lists").show();    

        Swal.fire({
          title: 'Cancelled',
          text: 'The oss for the VAT Registration is not '+ ossText +'ed!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });
  // OSS

// Update Excise Duty
  $(document).on('click', '.form-check-input.chk_excise_duty', function () {
    
    var excise_dutyCheckbox = $(this);
    var excise_dutyValue = excise_dutyCheckbox.prop("checked");
    var excise_dutyText = excise_dutyValue ? "Check" : "Uncheck";

    var data = excise_dutyCheckbox.data();
    var vat_reg_main_id = data['id'];
    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ excise_dutyText +" the excise duty for the VAT Registration!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ excise_dutyText +' it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $("#navs-pills-top-vatregistrations .sk-bounce").show();
        $("#vat-reg-lists").hide();

        //update the status
        $.ajax({
          type: 'PUT',
          data: {excise_duty: excise_dutyValue, excise_duty_text: excise_dutyText},  
          url: `${baseUrl}vat-registration-main/${vat_reg_main_id}/updateexciseduty`,
          dataType: 'json',
          success: function (result) {
            vat_reg_main_datas = drawDtTable(result, 'vatregmain');
            dt_vat_registration_main.clear().rows.add(vat_reg_main_datas).draw();
            //dt_vat_registration_main.draw();
            
            $('[data-vat_reg_main_id="'+ vat_reg_main_id +'"]').attr('data-excise_duty', !excise_dutyValue);

            $("#navs-pills-top-vatregistrations .sk-bounce").hide();
            $("#vat-reg-lists").show();

            if(result.status == 200)
              // success sweetalert
              Swal.fire({
                icon: 'success',
                title: excise_dutyText + 'ed!',
                text: 'The excise duty for the VAT Registration has been '+ excise_dutyText +'ed!',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            else
              Swal.fire({
                title: 'Cancelled',
                text: result.message,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });  
          },
          error: function (xhr, status, error) {
            var err = JSON.parse(xhr.responseText);

            dt_vat_registration_main.draw();
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
        excise_dutyCheckbox.prop("checked", !excise_dutyValue);

        $("#navs-pills-top-vatregistrations .sk-bounce").hide();
        $("#vat-reg-lists").show();

        Swal.fire({
          title: 'Cancelled',
          text: 'The excise duty for the VAT Registration is not '+ excise_dutyText +'ed!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });
  // Excise Duty

  // Update Cash Account Statement
  $(document).on('click', '.form-check-input.chk_cash_account_statement', function () {
    
    var cashAccountStatementCheckbox = $(this);
    var cashAccountStatement = cashAccountStatementCheckbox.prop("checked");
    var cashAccountStatementText = cashAccountStatement ? "Check" : "Uncheck";

    var data = cashAccountStatementCheckbox.data();
    var vat_reg_main_id = data['id'];
    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ cashAccountStatementText +" the cash account statement for the VAT Registration!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ cashAccountStatementText +' it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $("#navs-pills-top-vatregistrations .sk-bounce").show();
        $("#vat-reg-lists").hide();

        //update the status
        $.ajax({
          type: 'PUT',
          data: {cash_account_statement: cashAccountStatement, cash_account_statement_text: cashAccountStatementText},  
          url: `${baseUrl}vat-registration-main/${vat_reg_main_id}/updatecashaccountstatement`,
          dataType: 'json',
          success: function (result) {
            vat_reg_main_datas = drawDtTable(result, 'vatregmain');
            dt_vat_registration_main.clear().rows.add(vat_reg_main_datas).draw();
            //dt_vat_registration_main.draw();
            
            $('[data-vat_reg_main_id="'+ vat_reg_main_id +'"]').attr('data-cash_account_statement', !cashAccountStatement);

            $("#navs-pills-top-vatregistrations .sk-bounce").hide();
            $("#vat-reg-lists").show();

            if(result.status == 200)
              // success sweetalert
              Swal.fire({
                icon: 'success',
                title: cashAccountStatementText + 'ed!',
                text: 'The cash account statement for the VAT Registration has been '+ cashAccountStatementText +'ed!',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            else
              Swal.fire({
                title: 'Cancelled',
                text: result.message,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });  
          },
          error: function (xhr, status, error) {
            var err = JSON.parse(xhr.responseText);

            dt_vat_registration_main.draw();
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
        cashAccountStatementCheckbox.prop("checked", !cashAccountStatement);

        $("#navs-pills-top-vatregistrations .sk-bounce").hide();
        $("#vat-reg-lists").show();

        Swal.fire({
          title: 'Cancelled',
          text: 'The cash account statement for the VAT Registration is not '+ cashAccountStatementText +'ed!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Update Duty Deferment Account
  $(document).on('click', '.form-check-input.chk_duty_deferment_account', function () {
    
    var dutyDefermentAccountCheckbox = $(this);
    var dutyDefermentAccount = dutyDefermentAccountCheckbox.prop("checked");
    var dutyDefermentAccountText = dutyDefermentAccount ? "Check" : "Uncheck";

    var data = dutyDefermentAccountCheckbox.data();
    var vat_reg_main_id = data['id'];
    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to "+ dutyDefermentAccountText +" the duty deferment account for the VAT Registration!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ dutyDefermentAccountText +' it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $("#navs-pills-top-vatregistrations .sk-bounce").show();
        $("#vat-reg-lists").hide();
        
        //update the status
        $.ajax({
          type: 'PUT',
          data: {duty_deferment_account: dutyDefermentAccount, duty_deferment_account_text: dutyDefermentAccountText},  
          url: `${baseUrl}vat-registration-main/${vat_reg_main_id}/updatedutydefermentaccount`,
          dataType: 'json',
          success: function (result) {
            vat_reg_main_datas = drawDtTable(result, 'vatregmain');
            dt_vat_registration_main.clear().rows.add(vat_reg_main_datas).draw();
            //dt_vat_registration_main.draw();
            
            $('[data-vat_reg_main_id="'+ vat_reg_main_id +'"]').attr('data-duty_deferment_account', !dutyDefermentAccount);

            $("#navs-pills-top-vatregistrations .sk-bounce").hide();
            $("#vat-reg-lists").show();

            if(result.status == 200)
              // success sweetalert
              Swal.fire({
                icon: 'success',
                title: dutyDefermentAccountText + 'ed!',
                text: 'The duty deferment account for the VAT Registration has been '+ dutyDefermentAccountText +'ed!',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            else
              Swal.fire({
                title: 'Cancelled',
                text: result.message,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });  
          },
          error: function (xhr, status, error) {
            var err = JSON.parse(xhr.responseText);

            dt_vat_registration_main.draw();
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
        dutyDefermentAccountCheckbox.prop("checked", !dutyDefermentAccount);

        $("#navs-pills-top-vatregistrations .sk-bounce").hide();
        $("#vat-reg-lists").show();

        Swal.fire({
          title: 'Cancelled',
          text: 'The duty deferment account for the VAT Registration is not '+ dutyDefermentAccountText +'ed!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  $(document).on('click', '#account_nos', function () {  
    $('#selectAccountNosForm').find('ul').empty();
  });

  // Select the Account No's
  $(document).on('click', '.select-accountnos', function () {    
          
    var selected_acc_no = [];
  
    $("#selected_account_nos").html("");

    $('li input:checked').each(function() {         
      var acc_no_data = $(this).data();
      var account_name = acc_no_data['acc_name'];
      var account_type = acc_no_data['acc_type'];

      var acc_no_reverse = $(this).closest("li").find("input.accoutno_reverse:checked");
console.log(acc_no_reverse);
      selected_acc_no.push({
        'vat_account_no' : $(this).val(),
        'vat_account_no_reverse' : acc_no_reverse.val(),
        'vat_account_name' : account_name,
        'vat_account_type' : account_type
      });        
    });
 
    var acc_div = ""; 
    var selected_acc_details="";
    $.each(selected_acc_no, function(key,value) {   
      acc_div += '<li class="d-inline-flex mb-3 w-50">' +                                     
                    '<div class="justify-content-between flex-grow-1">' +
                      '<div class="me-2">' +
                        '<p class="mb-0">'+ value['vat_account_no'] + ((value['vat_account_no_reverse']) ? '<u>Reverse:</u><i class="bx bx-check"/>' : '') +'</p>' +
                        '<p class="mb-0 text-muted">'+ value['vat_account_name'] +'</p>' +
                      '</div>' +
                    '</div>' +                    
                  '</li>';  
      selected_acc_details +=  value['vat_account_no'] + ',' + value['vat_account_name'] + ',' + value['vat_account_type'] + ',' + value['vat_account_no_reverse'] + '***';
    });       
    
    $("input[name=selected_acc_nos]").val(selected_acc_details);
    $('#selected_account_nos').html(acc_div);  
    $('#selected_account_nos').show();  
    $('#account_nos').prop('checked', true); 
    $('#selectAccountNos').modal('hide');                                           
  });
  // End Select the Account No's

  // Delete Record  
  $(document).on('click', '.delete-vatregmain', function () {     
    var vatregistration_id = $(this).data('id'),
      client_id = $(this).data('client_id'),
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
        $('#vat-reg-lists').block({
          message:
            '<div class="d-flex justify-content-center"><p class="mb-0">Deleting...</p> <div class="sk-wave m-0"><div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div> <div class="sk-rect sk-wave-rect"></div></div> </div>',
          //timeout: 1000,
          css: {
            backgroundColor: 'transparent',
            color: '#fff',
            border: '0'
          },
          overlayCSS: {
            opacity: 0.5
          }
        });

        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}vat-registration-main/${vatregistration_id}`,
          success: function (result) {
           
            vat_reg_main_datas = drawDtTable(result, 'vatregmain');            
            dt_vat_registration_main.clear().rows.add(vat_reg_main_datas).draw();

            //if(vat_reg_main_datas.length > 0)
            //{
              //var client_id = vat_reg_main_datas[0]['client_id'];
              loadVatReturnsTab(client_id);
              loadImportReconciliationTab(client_id);
            //}
            //dt_vat_registration_main.draw();

            $('#vat-reg-lists').unblock();

            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The VAT registration has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (error) {
            $('#vat-reg-lists').unblock();  
            console.log(error);
          }
        });

      } else if (result.dismiss === Swal.DismissReason.cancel) {       
        Swal.fire({
          title: 'Cancelled',
          text: 'The VAT registration is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });
  
  $(document).on('click', '.add-new', function () {    
    var client_id = $(this).data('client_id');
    window.location.href = `${baseUrl}vat-registration-main/${client_id}/create`; 
  });
   
  $(document).on('click', '.edit-vatregmain', function () {    
    var vat_registration_main_id = $(this).data('id');
    window.location.href = `${baseUrl}vat-registration-main/${vat_registration_main_id}/edit`; 
  });
 
  if(window.location.pathname.replace(/\d+/g, '').indexOf('/vat-registration-main//create') != -1)
  {
    //GB
    $("#for_GB").hide();
    $("#for_gateway").hide();

    //NO, SE
    $("#for_NO").hide();

    //CH
    $("#for_CH").hide();

    //DE
    $("#for_DE").hide();

    //DK
    $("#for_DK").hide();

    //NL
    $("#for_NL").hide();

    //PL
    $("#for_PL").hide();

    //FI
    $("#for_FI").hide();

    //FR
    $("#for_FR").hide();

    //ES
    $("#for_ES").hide();

    //PT
    $("#for_PT").hide();
  }

  $(document).on('change', '#formVatRegistrationMain #country', function () {      
    $('#general_periods option').removeAttr('selected');

    //GB
    $("#for_GB").hide();
    $("#for_gateway").hide();
    $("#cash_account_statement").hide();
    //$("#parent_gb_vat").hide();
    $("#parent_eori_no").hide();
    $("#parent_cash_account_no").hide();

    //NO, SE
    $("#for_NO").hide();
    $("#duty_deferment_account").hide();
    $("#parent_mva_no").hide();
    $("#parent_org_no").hide();

    //CH
    $("#for_CH").hide();  
    $("#parent_zaz_no").hide();

    //DE
    $("#for_DE").hide();  
    $("#parent_steuer_no").hide();

    //DK
    $("#for_DK").hide();  
    $("#parent_cvr_no").hide();
    $("#parent_excise_duty").hide();

    //NL
    $("#for_NL").hide();  
    $("#parent_omz_no").hide();

    //PL
    $("#for_PL").hide();  
    $("#parent_nip_no").hide();

    //FI
    $("#for_FI").hide();  
    $("#parent_fo_no").hide();

    //FR
    $("#for_FR").hide();  
    $("#parent_siret_no").hide();

    //ES
    $("#for_ES").hide();  
    $("#parent_nif_no").hide();

    //PT
    $("#for_PT").hide();  
    $("#parent_nipc_no").hide();
    
    if($(this).val() == "GB") 
    {   
      $('#general_periods option[value=quarterly]').attr('selected','selected');
      $("#cash_account_statement").show();

      $("#for_GB").show();
      $("#for_gateway").show();
      //$("#parent_gb_vat").show();
      $("#parent_eori_no").show();
      $("#parent_cash_account_no").show();
    }
    else if($(this).val() == "NO")    
    {
      $('#general_periods option[value=bi-monthly]').attr('selected','selected');

      $("#for_NO").show();
      $("#duty_deferment_account").show();
      $("#parent_mva_no").show();
      $("#parent_org_no").show();
    }
    else if($(this).val() == "CH")    
    {    
      $("#for_CH").show();      
      $("#parent_zaz_no").show();
    }
    else if($(this).val() == "DE")    
    {    
      $("#for_DE").show();      
      $("#parent_steuer_no").show();
    }
    else if($(this).val() == "DK")    
    {    
      $("#for_DK").show();      
      $("#parent_cvr_no").show();
      $("#parent_excise_duty").show();
    }
    else if($(this).val() == "SE")    
    {    
      $("#for_NO").show();
      $("#duty_deferment_account").hide();
      $("#parent_mva_no").hide();
      $("#parent_org_no").show();
    }
    else if($(this).val() == "NL")    
    {    
      $("#for_NL").show();      
      $("#parent_omz_no").show();
    }
    else if($(this).val() == "PL")    
    {    
      $("#for_PL").show();      
      $("#parent_nip_no").show();
    }
    else if($(this).val() == "FI")    
    {    
      $("#for_FI").show();      
      $("#parent_fo_no").show();
    }
    else if($(this).val() == "FR")    
    {    
      $("#for_FR").show();      
      $("#parent_siret_no").show();
    }
    else if($(this).val() == "ES")    
    {    
      $("#for_ES").show();      
      $("#parent_nif_no").show();
    }
    else if($(this).val() == "PT")    
    {    
      $("#for_PT").show();      
      $("#parent_nipc_no").show();
    }
    else
      $('#general_periods option').removeAttr('selected').filter('[value=""]').attr('selected', true);

    generalPeriodsChange();
  });

  $(document).on('change', '#formVatRegistrationMain #general_periods', function () { 
    generalPeriodsChange();
  }); 

  function generalPeriodsChange() {
    //$("#product_type_vat_return").removeAttr("disabled"); 
    //$("#product_type_voec_vat_return").removeAttr("disabled");

    //$(".product_type_vat_return").removeClass("disabled"); 
    //$(".product_type_voec_vat_return").removeClass("disabled");

    if($('#country').val() == "NO")
    {      
      $(".product_type_vat_return span.custom-option-title").text(' NUF VAT Return ');
      $(".product_type_vat_return small").text(' NUF VAT Return ');

      if($('#general_periods').val() == "bi-monthly")
      {          
        $(".product_type_vat_return").show();  
        $(".product_type_voec_vat_return").hide();

        $("#product_type_vat_return").prop("checked", true);      
      }
      else if($('#general_periods').val() == "quarterly")
      {           
        $(".product_type_vat_return").hide();
        $(".product_type_voec_vat_return").show();
       
        $("#product_type_voec_vat_return").prop("checked", true);
      }
      else
      {
        $(".product_type_vat_return").show();
        $(".product_type_voec_vat_return").show();

        $("#product_type_vat_return").removeAttr("checked");
        $("#product_type_voec_vat_return").removeAttr("checked");
      }  
    }
    else
    {
      $(".product_type_vat_return").show();
      $(".product_type_voec_vat_return").hide();

      $("#product_type_vat_return").removeAttr("checked");
      $("#product_type_voec_vat_return").removeAttr("checked");

      $(".product_type_vat_return span.custom-option-title").text(' VAT Return ');
      $(".product_type_vat_return small").text(' VAT Return ');
    }

    // if($('#country').val() == "NO" && $('#general_periods').val() == "bi-monthly")
    // {            
    //   $(".product_type_voec_vat_return").addClass("disabled");

    //   $("#product_type_voec_vat_return").attr("disabled", "disabled");  
    //   $("#product_type_vat_return").prop("checked", true);      
    // }
    // else if($('#country').val() == "NO" && $('#general_periods').val() == "quarterly")
    // {           
    //   $(".product_type_vat_return").addClass("disabled");

    //   $("#product_type_vat_return").attr("disabled", "disabled");  
    //   $("#product_type_voec_vat_return").prop("checked", true);
    // }   
    // else
    // {        
    //   if($('#general_periods').val() == "bi-monthly") 
    //   {    
    //     $(".product_type_voec_vat_return").addClass("disabled");

    //     $("#product_type_voec_vat_return").attr("disabled", "disabled");     
    //     $("#product_type_vat_return").prop("checked", true);       
    //   }
    //   else if($('#general_periods').val() == "quarterly")
    //   {     
    //     $(".product_type_vat_return").addClass("disabled");
        
    //     $("#product_type_vat_return").attr("disabled", "disabled");   
    //     $("#product_type_voec_vat_return").prop("checked", true);     
    //   }
    //   else
    //   {
    //     $("#product_type_vat_return").removeAttr("checked");
    //     $("#product_type_voec_vat_return").removeAttr("checked"); 
    //   }
    // }
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  // validating form and updating user's data
  const formVatRegistrationMain = document.getElementById('formVatRegistrationMain');

  if(formVatRegistrationMain != null)
  {
    // user form validation
    const fv = FormValidation.formValidation(formVatRegistrationMain, {
      fields: {
        country: {
          validators: {
            notEmpty: {
              message: 'Please select country'
            }
          }
        },
        service_start: {
          validators: {
            notEmpty: {
              message: 'Please enter start month and year'
            }
          }
        },
        // turnover_date: {
        //   validators: {
        //     notEmpty: {
        //       message: 'Please enter Turnover Date'
        //     }
        //   }
        // },
        general_periods: {
          validators: {
            notEmpty: {
              message: 'Please select Periods'
            }
          }
        },
        'product_type[]': {
          validators: {
            notEmpty: {            
              message: 'Please select product type'
            }
          }
        }
        // vat_reg_type: {
        //   validators: {
        //     notEmpty: {
        //       message: 'Please select VAT reg. type'
        //     }
        //   }
        // }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: '',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            switch (field) {
              case 'product_type[]':
                return '.mb-3';                
              default:
                return '.form-floating';
            }            
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      var btn_submit = $('#formVatRegistrationMain').find("button#btn-save");
      btn_submit.attr("disabled", "disabled");
      btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Saving...');

      // adding or updating user when form successfully validate
      $.ajax({
        data: $('#formVatRegistrationMain').serialize(),
        url: `${baseUrl}vat-registration-main`,
        type: 'POST',
        success: function (status) {   
          btn_submit.html("Saved");

          var client_id = $("#client_id").val();

          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${status}!`,
            text: `VAT ${status} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then(function() {            
            window.location.href = `${baseUrl}company/${client_id}`;     
          });
        },
        error: function (xhr, status, error) {
          var err = JSON.parse(xhr.responseText);
          
          btn_submit.removeAttr("disabled");
          btn_submit.html('Save');

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
  }  

  // Bootstrap Datepicker
  // --------------------------------------------------------------------
  var bsDatepickerServiceStart = $('#bs-datepicker-service_start'),
      bsDatepickerTurnoverDate = $('#bs-datepicker-turnover_date');

  // Basic
  if (bsDatepickerServiceStart.length) {
    bsDatepickerServiceStart.datepicker({
      format: "mm/yyyy",
      startView: "year", 
      minViewMode: "months",
      autoclose: true,
    });
  }

  // Basic
  if (bsDatepickerTurnoverDate.length) {
    bsDatepickerTurnoverDate.datepicker({
      format: "mm/dd/yyyy",
      todayHighlight: true,
      autoclose: true,
      orientation: isRtl ? 'auto right' : 'auto left'     
    });
  }

});