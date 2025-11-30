/**
 * Page Milbox File List
 */

'use strict';

// Datatable (jquery)
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
  
  // Variable declaration for table
  //var userView = baseUrl + 'dv-user/';
  var mailboxFileUrl = baseUrl + 'mail-box-files/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  
  
  let dt_new_mailboxfiles = null;
  var dt_new_mailboxfiles_table = $('.datatables-new-mailboxfiles');
  if (dt_new_mailboxfiles_table.length) {
    
    dt_new_mailboxfiles = dt_new_mailboxfiles_table.DataTable({  
        data: mailboxfile_new_datas,              
        // scrollCollapse: false,       
        // scrollX: true,      
        fixedHeader: true,        
        searching: true,       
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        pageLength: 100,     
        autoWidth: false,                 
        columns: [           
          { data: 'id' },        
          { data: 'client_name' },
          { data: 'email_datetime' },   
          { data: 'email_id' },       
          { data: 'email_subject' },
          { data: 'o_file_name' },                        
          { data: 'preview' },
          { data: 'action' },
          { data: 'vatreg', visible: false }         
        ],         
        columnDefs: [
          {
            // For Checkboxes
            targets: 0,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) {                
              return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'">';
            },
            checkboxes: {
              selectRow: true,
              selectAllRender: '<input type="checkbox" class="form-check-input">'
            }
          },
          { // Preview
            targets: 6,           
            render: function (data, type, full, meta) { 
              var buttons = "";
              buttons = '<button class="btn btn-sm btn-icon mailbox-file-preview" title="Preview" data-id="'+full['id']+'" data-file_id="'+ full['file_id'] +'" data-file_type="mailbox"><span class="tf-icons bx bxs-download"></span></button>';
              return (             
                buttons              
              );
             }          
          },
          {
            // For Action
            targets: 7,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) {  

              if (/\.(xls|xlsx)$/i.test(full['o_file_name'])) 
              {           
                var periods = '';
                $.each(full['vatreg'], function (idx, vatreg) {
                  if(periods == '')
                    periods = vatreg.id + "%%%" + vatreg.service_start;
                  else
                    periods += '***' + vatreg.id + "%%%" + vatreg.service_start;

                  //template_options += '<option value="'+ vatreg.id +'">'+ moment(vatreg.service_start).format('M Y') +'</option>';
                });

                return `<div class="d-inline-block">
                        <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end m-0">
                          <li>
                            <a href="javascript:;" class="dropdown-item btn-assign-anyexcel-template" title="Choose period and assign template" data-mailbox_id="`+ full['id'] +`" data-periods="`+ periods +`">
                              <span><i class="bx bx-book-open me-2"></i>Assign Template</span>
                            </a>                                    
                          </li>
                          <div class="dropdown-divider"></div>
                          <li>
                            <a href="javascript:;" class="dropdown-item btn-dismiss-mailbox-file" title="Dismiss File" data-mailbox_id="`+ full['id'] +`">
                              <span><i class="bx bx-x me-2"></i>Dismiss File</span>
                            </a>                                     
                          </li>
                        </ul>
                      </div>`;
              }

              return `<div class="d-inline-block">
                        <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end m-0">                          
                          <li>
                            <a href="javascript:;" class="dropdown-item btn-dismiss-mailbox-file" title="Dismiss File" data-mailbox_id="`+ full['id'] +`">
                              <span><i class="bx bx-x me-2"></i>Dismiss File</span>
                            </a>                                     
                          </li>
                        </ul>
                      </div>`;
            }
          }
        ],
        processing: true, 
        order: [[2, 'desc']],             
        dom:     
          '<"row mx-0 new-search-filter"' +              
          '<"col-md-12"lfB>' +
          '>r' +
          '<"row mx-0"' +
          '<"col-sm-12 p-0"t' +                    
          '>>' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        select: {            
          style: 'multi'
        },
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
        buttons: [                   
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle ml-3',
          text: '<i class="bx bx-export me-2"></i>Export',
          autoClose: true,
          buttons: [
            {
              extend: 'print',
              title: 'Mail Box Files',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible',               
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', config.colors.headingColor)
                  .css('border-color', config.colors.borderColor)
                  .css('background-color', config.colors.body);
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
              title: 'Mail Box Files',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },           
            {
              extend: 'excel',
              title: 'Mail Box Files',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',              
              //action: exportToExcelNewMailboxfiles, 
              exportOptions: {
                columns: ':visible',      
              }
            },
            {
              extend: 'pdf',
              orientation: 'landscape',
              pageSize: 'LEGAL',
              title: 'Mail Box Files',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Mail Box Files',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            }
          ]
        }            
      ],
      initComplete: function (settings, json) {          
                    
        $(".new-search-filter").appendTo('.dt-search-filter');

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMailboxfileSetting" aria-controls="offcanvasMailboxfileSetting">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.new-search-filter .dataTables_filter');

        $(".new-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-mailboxfile-export .new-mailboxfile-export');

        var mailboxfile_total = this.api().data().length;
        $("#btn-mailboxfile-new span").html(mailboxfile_total);

        $(".card.mailboxfiles .sk-bounce").hide();
        $(".card.mailboxfiles .card-datatable").show();           
      }      
    });

  }//DATATABLE

  let dt_active_mailboxfiles = null;
  var dt_active_mailboxfiles_table = $('.datatables-active-mailboxfiles');
  if (dt_active_mailboxfiles_table.length) {
    
    dt_active_mailboxfiles = dt_active_mailboxfiles_table.DataTable({  
        data: mailboxfile_active_datas,              
        // scrollCollapse: false,       
        // scrollX: true,      
        fixedHeader: true,        
        searching: true,       
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        pageLength: 100,     
        autoWidth: false,                 
        columns: [           
          { data: 'id' },        
          { data: 'client_name' },
          { data: 'email_datetime' },   
          { data: 'email_id' },       
          { data: 'email_subject' },
          { data: 'o_file_name' },                        
          { data: 'preview' },
          { data: 'action' }        
        ],         
        columnDefs: [
          {
            // For Checkboxes
            targets: 0,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) {                
              return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'">';
            },
            checkboxes: {
              selectRow: true,
              selectAllRender: '<input type="checkbox" class="form-check-input">'
            }
          },
          { // Preview
            targets: 6,           
            render: function (data, type, full, meta) { 
              var buttons = "";
              buttons = '<button class="btn btn-sm btn-icon mailbox-file-preview"  title="Preview" data-id="'+full['id']+'" data-file_id="'+ full['file_id'] +'" data-file_type="mailbox"><span class="tf-icons bx bxs-download"></span></button>';
              return (             
                buttons              
              );
             }          
          },
          {
            // For Action
            targets: 7,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) {                
              return '';
            }
          }
        ],
        processing: true, 
        order: [[2, 'desc']],             
        dom:     
          '<"row mx-0 active-search-filter d-none"' +              
          '<"col-md-12"lfB>' +
          '>r' +
          '<"row mx-0"' +
          '<"col-sm-12 p-0"t' +                    
          '>>' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        select: {            
          style: 'multi'
        },
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
        buttons: [                   
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle ml-3',
          text: '<i class="bx bx-export me-2"></i>Export',
          autoClose: true,
          buttons: [
            {
              extend: 'print',
              title: 'Mail Box Files',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible',               
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', config.colors.headingColor)
                  .css('border-color', config.colors.borderColor)
                  .css('background-color', config.colors.body);
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
              title: 'Mail Box Files',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },           
            {
              extend: 'excel',
              title: 'Mail Box Files',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',              
              //action: exportToExcelActiveMailboxfiles, 
              exportOptions: {
                columns: ':visible',      
              }
            },
            {
              extend: 'pdf',
              orientation: 'landscape',
              pageSize: 'LEGAL',
              title: 'Mail Box Files',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Mail Box Files',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            }
          ]
        }            
      ],
      initComplete: function (settings, json) {          
                    
        $(".active-search-filter").appendTo('.dt-search-filter');

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMailboxfileSetting" aria-controls="offcanvasMailboxfileSetting">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.active-search-filter .dataTables_filter');

        $(".active-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-mailboxfile-export .active-mailboxfile-export');

        var mailboxfile_total = this.api().data().length;
        $("#btn-mailboxfile-active span").html(mailboxfile_total);

        $(".card.mailboxfiles .sk-bounce").hide();
        $(".card.mailboxfiles .card-datatable").show();           
      }      
    });

  }//DATATABLE

  let dt_dismissed_mailboxfiles = null;
  var dt_dismissed_mailboxfiles_table = $('.datatables-dismissed-mailboxfiles');
  if (dt_dismissed_mailboxfiles_table.length) {
    
    dt_dismissed_mailboxfiles = dt_dismissed_mailboxfiles_table.DataTable({  
        data: mailboxfile_dismissed_datas,              
        // scrollCollapse: false,       
        // scrollX: true,      
        fixedHeader: true,        
        searching: true,       
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        pageLength: 100,     
        autoWidth: false,                 
        columns: [           
          { data: 'id' },        
          { data: 'client_name' },
          { data: 'email_datetime' },   
          { data: 'email_id' },       
          { data: 'email_subject' },
          { data: 'o_file_name' },                        
          { data: 'preview' },
          { data: 'action' }       
        ],         
        columnDefs: [
          {
            // For Checkboxes
            targets: 0,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) {                
              return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'">';
            },
            checkboxes: {
              selectRow: true,
              selectAllRender: '<input type="checkbox" class="form-check-input">'
            }
          },
          { // Preview
            targets: 6,           
            render: function (data, type, full, meta) { 
              var buttons = "";
              buttons = '<button class="btn btn-sm btn-icon mailbox-file-preview"  title="Preview" data-id="'+full['id']+'" data-file_id="'+ full['file_id'] +'" data-file_type="mailbox"><span class="tf-icons bx bxs-download"></span></button>';
              return (             
                buttons              
              );
             }          
          },
          {
            // For Action
            targets: 7,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) {                
              return '';
            }
          }
        ],
        processing: true, 
        order: [[2, 'desc']],             
        dom:     
          '<"row mx-0 dismissed-search-filter d-none"' +              
          '<"col-md-12"lfB>' +
          '>r' +
          '<"row mx-0"' +
          '<"col-sm-12 p-0"t' +                    
          '>>' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        select: {            
          style: 'multi'
        },
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
        buttons: [                   
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle ml-3',
          text: '<i class="bx bx-export me-2"></i>Export',
          autoClose: true,
          buttons: [
            {
              extend: 'print',
              title: 'Mail Box Files',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible',               
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', config.colors.headingColor)
                  .css('border-color', config.colors.borderColor)
                  .css('background-color', config.colors.body);
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
              title: 'Mail Box Files',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },           
            {
              extend: 'excel',
              title: 'Mail Box Files',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',              
              //action: exportToExcelDismissedMailboxfiles, 
              exportOptions: {
                columns: ':visible',      
              }
            },
            {
              extend: 'pdf',
              orientation: 'landscape',
              pageSize: 'LEGAL',
              title: 'Mail Box Files',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Mail Box Files',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            }
          ]
        }            
      ],
      initComplete: function (settings, json) {          
                    
        $(".dismissed-search-filter").appendTo('.dt-search-filter');

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMailboxfileSetting" aria-controls="offcanvasMailboxfileSetting">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.dismissed-search-filter .dataTables_filter');

        $(".dismissed-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-mailboxfile-export .dismissed-mailboxfile-export');

        var mailboxfile_total = this.api().data().length;
        $("#btn-mailboxfile-dismissed span").html(mailboxfile_total);

        $(".card.mailboxfiles .sk-bounce").hide();
        $(".card.mailboxfiles .card-datatable").show();           
      }      
    });

  }//DATATABLE

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);   
 
  // PDF Click    
  $(document).on('click', '.mailbox-file-preview', function () {        
    var btn_preview_file = $(this);
    var data = btn_preview_file.data();    
    btn_preview_file.html('<!-- Bounce -->' +
      '<div class="sk-bounce sk-primary sk-center">' +
        '<div class="sk-bounce-dot"></div>' +
        '<div class="sk-bounce-dot"></div>' +
      '</div>');

    var file_id = data['id'];
    var file_type = data['file_type'];   
    $.ajax({      
      url: `${baseUrl}file/${file_id}/download`,
      data: {file_type : file_type},
      type: 'GET',     
      success: function (data) {
        btn_preview_file.html('<span class="tf-icons bx bxs-download"></span>');            
        window.open(data, '_blank');  
      },
      error: function (err) {
        console.log(err);     
      }
    });
  });

  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    var id = $(e.target).attr("id") // activated tab
   
    if(id == 'btn-mailboxfile-new')
    {      
      $(".dt-mailboxfile-export .active-mailboxfile-export").addClass('d-none');
      $(".dt-mailboxfile-export .dismissed-mailboxfile-export").addClass('d-none');
      $(".dt-mailboxfile-export .new-mailboxfile-export").removeClass('d-none');      

      $(".dt-search-filter .active-search-filter").addClass('d-none');
      $(".dt-search-filter .dismissed-search-filter").addClass('d-none');
      $(".dt-search-filter .new-search-filter").removeClass('d-none');      
    }
    else if(id == 'btn-mailboxfile-active')
    {     
      $(".dt-mailboxfile-export .new-mailboxfile-export").addClass('d-none');
      $(".dt-mailboxfile-export .dismissed-mailboxfile-export").addClass('d-none');
      $(".dt-mailboxfile-export .active-mailboxfile-export").removeClass('d-none');

      $(".dt-search-filter .new-search-filter").addClass('d-none');
      $(".dt-search-filter .dismissed-search-filter").addClass('d-none');
      $(".dt-search-filter .active-search-filter").removeClass('d-none');      
    }  
    else if(id == 'btn-mailboxfile-dismissed')
    {     
      $(".dt-mailboxfile-export .new-mailboxfile-export").addClass('d-none');
      $(".dt-mailboxfile-export .active-mailboxfile-export").addClass('d-none');
      $(".dt-mailboxfile-export .dismissed-mailboxfile-export").removeClass('d-none');

      $(".dt-search-filter .new-search-filter").addClass('d-none');
      $(".dt-search-filter .active-search-filter").addClass('d-none');
      $(".dt-search-filter .dismissed-search-filter").removeClass('d-none');
    }  
  });

  //Open Assign Modal
  $(document).on('click', '.btn-assign-anyexcel-template', function () { 
    var data = $(this).data();
    var periods = data.periods;
    var mailbox_file_id = data.mailbox_id;

    var vatreg_periods = periods.split("***");
    var period_options = '<option value="">--Select Period--</option>';
    $.each(vatreg_periods, function(index, value) {
      var vatreg_id_date = value.split("%%%");
      //compare_month_year = moment(declarations['service_start']).add(i, 'month').format('MM-Y');
      period_options += '<option value="'+ vatreg_id_date[0] +'">'+ moment(vatreg_id_date[1]).format('MMM Y') +'</option>';
    });
    $('#vatreg_period').html('');
    $('#vatreg_period').html(period_options);

    $('#mailbox_file_id').val(mailbox_file_id);
    //$('#mailboxAssignAnyExcelTemplateModal .onboarding-title').html('Create FTP Sales Invoice');
                
    //$('#mailboxAssignAnyExcelTemplateModal #mailbox_assign_anyexcel_template').hide();        
    //$('#mailboxAssignAnyExcelTemplateModal #mailbox_assign_anyexcel_template').html('');
    //$("#mailboxAssignAnyExcelTemplateModal .sk-bounce").show();
    $('#mailboxAssignAnyExcelTemplateModal').modal('show');
    
    //$("#mailboxAssignAnyExcelTemplateModal .sk-bounce").hide();
    //$('#mailboxAssignAnyExcelTemplateModal #mailbox_assign_anyexcel_template').html(result.sales_invoice_datas);
    //$('#mailboxAssignAnyExcelTemplateModal #mailbox_assign_anyexcel_template').show();
  });
  
  //Assign AnyExcel Template for a VAT reg. period
  $(document).on('click', '.btn-mailbox-assign-anyexcel-template', function () { 
    var btn_mailbox_assign_anyexcel_template = $(this);

    btn_mailbox_assign_anyexcel_template.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Assigning...');
    btn_mailbox_assign_anyexcel_template.attr('disabled', 'disabled');

    var vat_reg_id = $("#vatreg_period").val();
    var template_id = $('input[name="anyexcel_template_selection_vatreturn_' + vat_reg_id + '"]:checked').val();
    var mailbox_file_id = $("#mailbox_file_id").val();

    $.ajax({      
      url: `${mailboxFileUrl}assign`,
      data: {mailbox_file_id : mailbox_file_id, vat_reg_id : vat_reg_id, template_id : template_id},
      type: 'POST',     
      success: function (result) {
        btn_mailbox_assign_anyexcel_template.html('Assign');
        btn_mailbox_assign_anyexcel_template.removeAttr('disabled', 'disabled');

        let mailboxfile_datas = drawDtTable(result, 'mailbox');
        
        $("#btn-mailboxfile-new span").html(mailboxfile_datas['mailboxfile_new_datas'].length);
        dt_new_mailboxfiles.clear().rows.add(mailboxfile_datas['mailboxfile_new_datas']).draw();

        $("#btn-mailboxfile-active span").html(mailboxfile_datas['mailboxfile_active_datas'].length);
        dt_active_mailboxfiles.clear().rows.add(mailboxfile_datas['mailboxfile_active_datas']).draw();

        $("#btn-mailboxfile-dismissed span").html(mailboxfile_datas['mailboxfile_dismissed_datas'].length);
        dt_dismissed_mailboxfiles.clear().rows.add(mailboxfile_datas['mailboxfile_dismissed_datas']).draw();

        $('#vatreg_period').val('');
        $('#mailbox_file_id').val('');
       
        $('#mailboxAssignAnyExcelTemplateModal').modal('hide');
      },
      error: function (err) {
        console.log(err);     
      }
    });
  });

  //Dismiss File
  $(document).on('click', '.btn-dismiss-mailbox-file', function () { 
    var data = $(this).data();
    var mailbox_file_id = data['mailbox_id'];

    $.ajax({      
      url: `${mailboxFileUrl}dismiss`,
      data: {mailbox_file_id : mailbox_file_id},
      type: 'DELETE',     
      success: function (result) {

        let mailboxfile_datas = drawDtTable(result, 'mailbox');
      
        $("#btn-mailboxfile-new span").html(mailboxfile_datas['mailboxfile_new_datas'].length);
        dt_new_mailboxfiles.clear().rows.add(mailboxfile_datas['mailboxfile_new_datas']).draw();

        $("#btn-mailboxfile-active span").html(mailboxfile_datas['mailboxfile_active_datas'].length);
        dt_active_mailboxfiles.clear().rows.add(mailboxfile_datas['mailboxfile_active_datas']).draw();

        $("#btn-mailboxfile-dismissed span").html(mailboxfile_datas['mailboxfile_dismissed_datas'].length);
        dt_dismissed_mailboxfiles.clear().rows.add(mailboxfile_datas['mailboxfile_dismissed_datas']).draw();  
      },
      error: function (err) {
        console.log(err);     
      }
    });
  });
});
