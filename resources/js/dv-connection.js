/**
 * Page Client List
 */

'use strict';

// Datatable (jquery)
$(function () {  
  $(".sk-bounce").show();
  
  // Variable declaration for table
  var dt_client_connection_table = $('.datatables-client-connection'),    
    clientConnectionUrl = baseUrl,   
    offCanvasForm = $('#connectionModal'),
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

  // Client Connections datatable
  if (dt_client_connection_table.length) {    
    
    var dt_connection = dt_client_connection_table.DataTable({
      data: connection_datas, 
      processing: true,
      //serverSide: true,
      autoWidth : false,
      // ajax: {
      //   url: baseUrl + 'client'
      // },
      columns: [
        // columns according to JSON      
        { data: 'id' },
        { data: 'connection_name' },
        { data: 'connection_status' },       
        { data: 'connection_remarks' }        
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
        
       {
          // Connection Name
          targets: 1,          
          width: "10%",   
          render: function (data, type, full, meta) {
            //var $connection_name_value = ""; 
           // var $vatregmain_datas = full['vatregmain'];
            var $connection_name = full['connection_name'];
            //console.log(full['vatregmain_datas']);
            var $row_output = "";  

             // $.each($vatregmain_datas, function (idx, vatregmain) {
             //  var $altconnection_name = vatregmain['vatregmain'];
             //    if(full['connection_name'] != null)
             //    {
             //      $connection_name_value = full['connection_name'];
             //    }
             //    else
             //    {
             //      $connection_name_value = full['client_name'] + '-' + $altconnection_name;
             //    }
             //     $row_output +=
             //    '<span>' +
             //    $connection_name_value +
             //    '</span>';
             // });
            
            //var $connection_name = full['connection_name'];
            
            //return $row_output;
            return '<span>' + $connection_name + '</span>';
          }
        },
        {
          // Connection status
          targets: 2,          
          width: "10%",   
          render: function (data, type, full, meta) {
             var $connection_status_value = ""; 

            if(full['connection_status'] != null)
            {
              $connection_status_value = ((full['connection_status']) == 1 ? 'Active' : 'Inactive');

            }
            else
            {
              $connection_status_value = ((full['status']) == 1 ? 'Active' : 'Inactive');
            }
            
            if($connection_status_value == "Active")
            {
              return '<span class="badge badge-dot bg-success me-1"></span><span>' + $connection_status_value + '</span>';
            }
            else
            {
              return '<span class="badge badge-dot bg-danger me-1"></span><span>' + $connection_status_value + '</span>';
            }
          }
        },      
        {
          // Connection Remarks
          targets: 3,          
          width: "10%",   
          render: function (data, type, full, meta) {
            var $connection_remarks_value = ""; 

            if(full['connection_remarks'] != null)
            {
              $connection_remarks_value = full['connection_remarks'];
            }
            else
            {
              $connection_remarks_value = "Connected via VAT Reg"
            }            
            
            return '<span>' + $connection_remarks_value + '</span>';
          }
        },    
      ],
      order: [[1, 'asc']],
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
          text: '<i class="bx bx-plus me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add New Connection</span>',
          className: 'add-new-connection btn btn-primary mx-3',  
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#connectionModal'
          }        
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['connection_name'];
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
        $("#client-connection-page").show();
        dt_client_connection_table.show();    
      },      
      drawCallback: function(settings){ 
       
      }
    });
    
  }

  // Add New Record
  $('.add-new-connection').on('click', function () {
    clearConnectionForm();
  });
   function clearConnectionForm()
  {
    $('#modalLabel').html('Create Connection');

    $('#connection_id').val("");    
    $('#connection_name').val("");

    $('#erp_options').val("");  
    $('#api_client_id').val("");    
    $('#api_tenant_id').val("");  
    $('#api_secret_key').val("");    
    $('#sales_invoice_url').val("");   
    $('#purchase_invoice_url').val("");    
    $('#select2Client').val("");     
  }

   // validating form and updating user's data
  const formNewConnection = document.getElementById('formNewConnection');

  if(formNewConnection != null)
  {
    //console.log('dfd');
    // user form validation
    const fv = FormValidation.formValidation(formNewConnection, {
      fields: {
        connection_name: {
          validators: {
            notEmpty: {
              message: 'Please enter connection name'
            }
          }
        },
        
        erp_options: {
          validators: {
            notEmpty: {
              message: 'Please select connection type'
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
            return '.form-floating';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () { 
      // adding or updating user when form successfully validate
      $.ajax({
        data: $('#formNewConnection').serialize(),
        // url: `${baseUrl}/`,clientConnectionUrl
        url: `${clientConnectionUrl}`,
        type: 'POST',
        success: function (result) { 
        //console.log(result);        
          //var client_id = $("#client_id").val();
          
           var errstatus = result['status'];
           var errmessage = "";
           var errtitle = "";
           var icontext = "";
            if(errstatus == 200)   
            {
              errtitle = "Connection saved!";
              errmessage = "Connection Established.";
              icontext = "success";
            }   
            else if(errstatus == 401)
            {
              errtitle = "Connection not saved!";
              errmessage = "Connection Already Established.";
              icontext = "warning";
            }
            else if(errstatus == 402)
            {
              errtitle = "Connection Failed!";
              errmessage = result['message'];
              icontext = "error";
            }
          if(result)    
          {             
                //var status = result['message'];
                connection_datas = drawDtTable(result, 'clientconnection');
                dt_connection.clear().rows.add(connection_datas).draw();
                     // console.log(connection_datas);
              // offCanvasForm.offcanvas('hide');
              var modalId = "connectionModal";
                $('#'+ modalId).modal('hide');

                clearConnectionForm();
                  
                                
                Swal.fire({
                  icon: icontext,
                  //title: `Connection saved!`,
                  title: errtitle,
                  //text: `Connection Success.`,
                  //text: result['message'],
                  text: errmessage,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });

              }
        },
       error: function (result) {
             
        var err = result['message'];
        connection_datas = drawDtTable(result, 'clientconnection');
        dt_connection.clear().rows.add(connection_datas).draw();
        
        //offCanvasForm.offcanvas('hide');
         var modalId = "connectionModal";
                $('#'+ modalId).modal('hide');

            var errmessage = "";
            var icontext = "";
            if(errstatus == 401)
            {
              errmessage = "Connection Already Established.";
              icontext = "warning";
            }
            else if(errstatus == 402)
            {
              errmessage = result['message'];
              icontext = "error";
            }

             Swal.fire({
              //icon: 'success',
              icon: icontext,
              title: `Connection Failed!`,
              //text: `Connection Success.`,
              text: errmessage,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
      }
      });
    });
   
  }  

  
});
