/**
 * Page OCR Invoice PDF File List
 */

'use strict';
Dropzone.autoDiscover = false;

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Datatable (jquery)
$(function () {
  window.Pusher = Pusher;
  window.Echo = new Echo({
      broadcaster: 'pusher',      
      key: window.EchoConfig.pusherKey,
      cluster: window.EchoConfig.pusherCluster,      
      forceTLS: true
  });

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
  var analyzePdfUrl = baseUrl + 'analyzepdf/';

  var statusObj = {      
      0: { title: 'Not Sync', class: 'bg-danger' },
      1: { title: 'Synced', class: 'bg-success' }      
    };

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  
  
  window.Echo.channel('ocr-sync-invoices-channel').listen('.OcrInvoicesSyncEvent', (event) => {
    //console.log(event);
    console.log('OCR Sync Invoices Event:', event);
     // console.log(event.message);
     // console.log(event.client_id);
    // Handle the event
    var client_id = event.client_id;
    //console.log(client_id);
    
    $.ajax({              
      url: `${analyzePdfUrl}progress`,     
      type: 'GET',
      success: function (result) {   console.log(result);
        //const progressData = result.json();

        var analyzepdf_datas = drawDtTable(result, 'analyzepdf');            
        reloadAnalyzedPdf(analyzepdf_datas);
      },
      error: function (err) {
        console.log(err);        
      }
    });
  });

  window.analyzepdfDeleteCommentEditor = function analyzepdfDeleteCommentEditor(data = null) {      
    const analyzepdfDeleteCommentEditors = document.querySelector('#analyzepdf-delete-reason-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (analyzepdfDeleteCommentEditors) {
      new Quill('#analyzepdf-delete-reason-editor', {
        modules: {
          toolbar: '#analyzepdf-delete-reason-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  }  

  analyzepdfDeleteCommentEditor();

  var dt_analyzepdf_tables = $('.datatables-analyzepdf');

  for (var i = 0; i < dt_analyzepdf_tables.length; i++) {
   
    var dt_analyzepdf_table = $(dt_analyzepdf_tables[i]); // This is a DOM element, not a jQuery object

    if (dt_analyzepdf_table) 
    {
      let columntargets = [7, 8];
      
      var analyzepdf_filter_class = 'd-none';
      let analyzepdf_name = '';
      var analyzepdf_datas = [];
      if(i === 0)
      {
        analyzepdf_filter_class = '';
        analyzepdf_name = 'completed';
        analyzepdf_datas = analyzepdf_completed_datas;

        columntargets = [7];
      }
      else if(i === 1)
      {
        analyzepdf_name = 'processing';
        analyzepdf_datas = analyzepdf_processing_datas;
      }
      else if(i === 2)
      {
        analyzepdf_name = 'error';
        analyzepdf_datas = analyzepdf_error_datas;
      }
      else if(i === 3)
      {
        analyzepdf_name = 'deleted';
        analyzepdf_datas = analyzepdf_deleted_datas;
        columntargets = [];

        columntargets = [8];
      }

      let columns = [
          { data: 'id' },
          { data: 'fake_id' },    
          { data: 'invoice_type_name' },    
          { data: 'client_name' },
          { data: 'invoice_no' },
          { data: 'file_name' },
          { data: 'created_at', className: 'w-px-200' },  
          { data: 'deleted_reason' },
          { data: 'sync_status' },
          { data: 'action' }  
      ];      

      var dt_analyzepdf = dt_analyzepdf_table.DataTable({  
          data: analyzepdf_datas,              
          scrollCollapse: false,              
          searching: true,    
          lengthMenu: [
              [10, 25, 50, 100],
              [10, 25, 50, 100]
          ],
          pageLength: 100,     
          autoWidth: false, 
          ordering: true,                
          columns: columns,          
          columnDefs: [
            {
              // For Checkboxes
              targets: 0,              
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) {
                if(full.is_deleted)                
                  return '';
                else
                  return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'" data-invoice_no="'+ full.invoice_no +'">';
              },
              checkboxes: {
                selectRow: true,
                selectAllRender: '<input type="checkbox" class="form-check-input">'
              }
            },
            {
              // For Uparrow Icons
              targets:  columntargets,         
              searchable: false,
              orderable: false,
              visible: false,
              render: function (data, type, full, meta) {                
                return '';
              }
            },   
            {
              // For Client Name and No.
              targets:  3,         
              searchable: true,
              orderable: true,              
              render: function (data, type, full, meta) {                
                return full.client_name + '<br><span class="fs-tiny">' + full.client_no + '</span>';
              }
            }, 
            {
              // For Uparrow Icons
              targets:  5,         
              searchable: true,
              orderable: true,              
              className: 'text-break',
              render: function (data, type, full, meta) { 
                var error_msg = '';

                if (full.status === 'failed') {
                    error_msg =
                        '<br>' +
                        (full.error || '')
                            //.split('\\n')
                            .split(/\r?\n|\\n/)
                            .filter(err => err.trim() !== '')
                            .map(err => `<span class="badge bg-label-danger text-capitalize me-1 mb-1">${err}</span>`)
                            .join('<br>');
                }
                                
                return `${full.file_name} ${error_msg}`;
              }
            },
            {
              // For Uparrow Icons
              targets:  6,         
              searchable: false,
              orderable: true,              
              render: function (data, type, full, meta) { 
                if (type === 'sort') {
                    return moment(full.created_at, 'DD-MM-YYYY').format('YYYYMMDD');
                }
                else
                {
                  if(full.created_at != '-' && full.updated_at != '-')
                  {
                    if(full.created_at == full.updated_at)
                      return full.created_at;                  
                  }
                  return ((full.updated_at != '-') ? ('<span class="fw-semibold">' + full.updated_at + '</span><br>') : '') + full.created_at;
                  //(full.updated_at != '-') ? full.updated_at : full.created_at;
                }
              }
            },
            {
              // For Uparrow Icons
              targets:  7,         
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) { 
                if(full.duplicate_message)
                  return full.duplicate_message;                  
                else 
                  return full.deleted_reason;
              }
            },
            {
              // For Uparrow Icons
              targets:  8,         
              searchable: true,
              orderable: true,
              visible: true,
              render: function (data, type, full, meta) {
                return '<span class="badge '+ statusObj[full.sync_status].class +'">'+ statusObj[full.sync_status].title +'</span>';

                // if(full.sync_status)           
                //   return '<span class="badge bg-success">Synced</span>';
                // else
                //   return '<span class="badge bg-danger">Not Sync</span>';
              }
            },            
            {
              // For Action
              targets: 9,              
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) { 

                let btn_delete_analyzepdf = (!full.is_deleted) ? `<div class="dropdown-divider"></div>
                                      <li>
                                        <a href="javascript:;" class="dropdown-item text-danger btn-delete-analyzepdf" title="Delete Analyze PDF" data-analyzepdf_id="`+ full['id'] +`" data-tab_name="`+ analyzepdf_name +`" data-invoice_no="`+ full['invoice_no'] +`">
                                          <span class="text-danger"><i class="bx bx-x"></i> Delete</span>
                                        </a>                                      
                                      </li>`
                                      : '';

                let btn_recapture_analyzepdf = `<div class="dropdown-divider"></div>
                                      <li>
                                        <a href="javascript:;" class="dropdown-item btn-recapture" id="recapture-analyzepdf-data" title="Recapture Data" data-analyzepdf_id="`+ full['id'] +`" data-tab_name="`+ analyzepdf_name +`" data-invoice_no="`+ full['invoice_no'] +`">
                                          <span><i class="bx bx-refresh me-2"></i>Recapture</span>
                                        </a>                                     
                                      </li>`
                                      ;

                let btn_validate_analyzepdf = `<div class="dropdown-divider"></div>
                                      <li>
                                        <a href="javascript:;" class="dropdown-item btn-validate" id="validate-analyzepdf-data" title="Validate Data" data-analyzepdf_id="`+ full['id'] +`" data-tab_name="`+ analyzepdf_name +`" data-invoice_no="`+ full['invoice_no'] +`">
                                          <span><i class="bx bx-check me-2"></i>Validate</span>
                                        </a>                                     
                                      </li>`
                                      ;

                return `<div class="d-inline-block">
                          <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
                          <ul class="dropdown-menu dropdown-menu-end m-0">` +                            
                            `<li>
                              <a href="javascript:;" class="dropdown-item btn-show-data" id="show-analyzepdf-data" title="Show Data" data-analyzepdf_id="`+ full['id'] +`" data-analyzepdf_status="`+ full['status'] +`" data-tab_name="`+ analyzepdf_name +`" data-invoice_no="`+ full['invoice_no'] +`" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfData">
                                <span><i class="bx bx-show me-2"></i>Show Data</span>
                              </a>                                     
                            </li>` +
                            btn_recapture_analyzepdf +
                            btn_validate_analyzepdf +
                            btn_delete_analyzepdf +
                          `</ul>
                        </div>`;
              }
            }                     
          ],
          processing: true, 
          order: [[1, 'asc']],
          dom:     
            '<"row mx-0 '+ analyzepdf_name +'-search-filter '+ analyzepdf_filter_class +'"' +                      
            '<"col-sm-12 col-md-3 sub-btns text-start my-auto">' +
            '<"col-sm-12 col-md-9"lfB>' +            
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
                title: 'Analyze PDF',
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
                title: 'Analyze PDF',
                text: '<i class="bx bx-file me-2" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: ':visible'
                }
              },           
              {
                extend: 'excel',
                title: 'Analyze PDF',
                text: '<i class="bx bxs-file-export me-2"></i>Excel',
                className: 'dropdown-item',              
                exportOptions: {
                  columns: ':visible'
                },             
                action: function (e, dt, node, config) {                  
                    //exportToExcelPeriodOverviewNew(dt, declaration_name);
                }
              },
              {
                extend: 'pdf',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                title: 'Analyze PDF',
                text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: {                
                  columns: ':visible'
                }
              },
              {
                extend: 'copy',
                title: 'Analyze PDF',
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

          $("."+ analyzepdf_name +"-search-filter").appendTo('.dt-search-filter');

          // Adding Document Tyoe filter once table initialized
          if(analyzepdf_name === 'completed')          
          {          
            this.api()
              .columns(2)
              .every(function () {
                var column = this;
                var select = $(
                  '<select id="FilterInvoiceType" class="form-select text-capitalize"><option value=""> Select Invoice Type </option></select>'
                )
                  .appendTo('.invoice_type')
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

              this.api()
              .columns(3)
              .every(function () {
                var column = this;
                var select = $(
                  '<select id="FilterClientName" class="form-select w-px-200 text-capitalize"><option value=""> Select Client Name </option></select>'
                )
                  .appendTo('.client_name')
                  .on('change', function () {                
                    var val = $(this).val().replace(/-/g, " ");
                    column.search(val ? val : '', true, false).draw();
                  });

                column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                    var selected = (j === 0) ? 'selected' : '';
                    if(d)
                      select.append('<option value="' + d + '" ' + selected + '>' + d.replace(/-/g, " ") + '</option>');
                  });

                // manually trigger filter
                select.trigger('change');  
              });
            
              this.api()
              .columns(8)
              .every(function () {
                var column = this;
                var select = $(
                  '<select id="FilterStatus" class="form-select text-capitalize"><option value=""> Select Status </option></select>'
                )
                  .appendTo('.invoice_status')
                  .on('change', function () {                
                    var val = $(this).val().replace(/-/g, " ");
                    column.search(val ? val : '', true, false).draw();
                  });

                column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                    var selected = (d === 0) ? 'selected' : '';
                    select.append('<option value="' + statusObj[d].title.replace(/-/g, " ") + '" ' + selected + '>' + statusObj[d].title.replace(/-/g, " ") + '</option>');
                  });

                // manually trigger filter
                select.trigger('change');  
              });
              
             
              $(".dt-dropdown-filter").prependTo('.dt-search-filter .completed-search-filter #DataTables_Table_0_filter'); 
          }

          var btn_recapture_invoice =  '<button type="button" id="btn_'+ analyzepdf_name +'_recapture_invoice" title="Recapture Invoice" class="btn-recapture me-2 my-2 badge rounded-pill bg-label-primary border-0 text-capitalize disabled-opacity" disabled="disabled" data-is_recapture="1" data-tab_name="'+ analyzepdf_name +'">' +                                     
                                      '<span><i class="bx bx-refresh"></i> Recapture</span>' +
                                    '</button>';
          $(btn_recapture_invoice).appendTo('.'+ analyzepdf_name +'-search-filter .sub-btns');

          var btn_validate_invoice =  '<button type="button" id="btn_'+ analyzepdf_name +'_validate_invoice" title="Validate Invoice" class="btn-validate me-2 my-2 badge rounded-pill bg-label-warning border-0 text-capitalize disabled-opacity" disabled="disabled" data-is_validate="1" data-tab_name="'+ analyzepdf_name +'">' +                                     
                                      '<span><i class="bx bx-check"></i> Validate</span>' +
                                    '</button>';
          $(btn_validate_invoice).appendTo('.'+ analyzepdf_name +'-search-filter .sub-btns');

          if(analyzepdf_name != 'deleted')          
          {          
            var btn_delete_invoice =  '<button type="button" id="btn_'+ analyzepdf_name +'_delete_invoice" title="Delete Invoice" class="btn-delete-analyzepdf my-2 badge rounded-pill bg-label-danger border-0 text-capitalize disabled-opacity" disabled="disabled" data-is_delete="1" data-tab_name="'+ analyzepdf_name +'">' +                                     
                                        '<span><i class="bx bx-x"></i> Delete</span>' +
                                      '</button>';
            $(btn_delete_invoice).appendTo('.'+ analyzepdf_name +'-search-filter .sub-btns');
          }

          var sliderfilter =  '<label class="mx-2 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAnalyzePdfFilter" aria-controls="offcanvasAnalyzePdfFilter">' +
                                '<i class="bx bx-slider"></i>' +
                              '</label>';
          $(sliderfilter).appendTo('.'+ analyzepdf_name +'-search-filter .dataTables_filter');
         
          $("."+ analyzepdf_name +"-search-filter .dt-buttons.btn-group.flex-wrap").appendTo('.dt-analyzepdf-export .'+ analyzepdf_name +'-analyzepdf-export');

          var analyzepdf_total = this.api().data().length;
          $("#btn-analyzepdf-"+ analyzepdf_name +" span").html(analyzepdf_total);

          $(".card.analyzepdfs .sk-bounce").hide();
          $(".card.analyzepdfs .card-datatable").show();          
        }
      });          
    } //if dt exist
  } //for loop dt

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);     

  // Invoice Type
  $(document).on('change', '#pdf_invoice_type', function () {
    if($(this).val() == 'multi-invoices')
    {
      $("#pdfs").removeAttr('required');
      $("#single-invoice").hide();

      $("#pdf_file").attr('required', 'required');
      $("#page_ranges").attr('required', 'required');
      $("#multi-invoice").show();
    }
    else
    {
      $("#pdf_file").removeAttr('required');
      $("#page_ranges").removeAttr('required');
      $("#multi-invoice").hide();

      $("#pdfs").attr('required', 'required');
      $("#single-invoice").show();
    }
  }); 

  var $repeater = $('.form-salesinvoice-repeater');

  if (!$repeater.data('repeater-initialized')) {
      $repeater.repeater({
          show: function () {
              $(this).slideDown();
          },
          hide: function (deleteElement) {
              $(this).slideUp(deleteElement);
          }
      });

      $repeater.data('repeater-initialized', true);
  }

  function clearFormItems() {
    $("#offcanvasAnalyzePdfData #docViewer").removeAttr('src');

    $("#analyzepdf_id").val('');
    $("#analyzepdf_status").val('');

    $("#invoice_type").val('');
    $("#client_no").val('');
    $("#client_name").val('');
    $("#invoice_date").val('');
    $("#invoice_no").val('');
    $("#currency").val('');
    $("#credit_note").val('');
    $("#net_amount").val('');
    $("#vat_rate").val('');
    $("#vat_amount").val('');
    $("#total_amount").val('');
    $("#exchange_currency").val('');
    $("#exchange_rate").val('');
    $("#exchange_net_amount").val('');
    $("#exchange_vat_amount").val('');    
    $("#exchange_total_amount").val('');
  }

  // edit record
  $(document).on('click', '#show-analyzepdf-data', function () {
    clearFormItems();

    var analyzepdf_id = $(this).data('analyzepdf_id'),
      invoice_no = $(this).data('invoice_no'),
      tab_name = $(this).data('tab_name'),
      dtrModal = $('.offcanvas.show');
   
    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }
    
    if($(".offcanvas-body #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertBefore(".offcanvas-body #addAnalyzePdfForm");  
    }     
    $("#addAnalyzePdfForm").hide();
    $("#offcanvasAnalyzePdfData #docViewer").hide();

    let analyzepdf_datas;
    if(tab_name === 'completed')
      analyzepdf_datas = analyzepdf_completed_datas;
    else if(tab_name === 'processing')
      analyzepdf_datas = analyzepdf_processing_datas;
    else if(tab_name === 'error')
      analyzepdf_datas = analyzepdf_error_datas;
    else if(tab_name === 'commercial-invoice')
      analyzepdf_datas = analyzepdf_commercial_invoice_datas;
    else if(tab_name === 'sales-invoice')
      analyzepdf_datas = analyzepdf_sales_invoice_datas;
    else if(tab_name === 'declaration')
      analyzepdf_datas = analyzepdf_declaration_datas;
    else
      return; // unknown tab
console.log(analyzepdf_datas);

    // get data
    var filter_analyzepdf_data = analyzepdf_datas.filter(function(analyzepdf_data) {  
      if(tab_name === 'processing')            
        return (analyzepdf_data.id === analyzepdf_id && (analyzepdf_data.status == 'processing' || analyzepdf_data.status == 'queued'));
      else if(tab_name === 'commercial-invoice' || tab_name === 'sales-invoice' || tab_name === 'declaration' || tab_name === 'error')
        return (analyzepdf_data.id === analyzepdf_id);      
      else                          
        return (analyzepdf_data.id === analyzepdf_id && analyzepdf_data.status == tab_name);
    });
console.log(filter_analyzepdf_data);

    if(filter_analyzepdf_data)
    {
    // if(tab_name === 'commercial-invoice' || tab_name === 'sales-invoice' || tab_name === 'declaration')
    // {
      var analyzepdf_data = filter_analyzepdf_data[0]; 

      $('#analyzepdf_id').val(analyzepdf_data.id);      
      $('#analyzepdf_status').val(analyzepdf_data.status);     

      $('#client_no').val(analyzepdf_data.client_no);       
      $('#client_name').val(analyzepdf_data.client_name);

      $('#invoice_type').val(analyzepdf_data.invoice_type);
      $('#invoice_no').val(analyzepdf_data.invoice_no);
      $('#invoice_date').val(analyzepdf_data.invoice_date);

      $('#currency').val(analyzepdf_data.currency);
      $('#net_amount').val(analyzepdf_data.net_amount);

      $('#exchange_currency').val(analyzepdf_data.exchange_currency);        
      $('#exchange_net_amount').val(analyzepdf_data.exchange_net_amount);

      if(analyzepdf_data.invoice_type == 'sales' || analyzepdf_data.invoice_type == 'multi-invoices')
      {
        $('#credit_note').removeAttr('disabled');
        $('#credit_note').parent('div').removeClass('d-none');

        $('#net_amount').removeAttr('disabled');
        $('#net_amount').parent('div').removeClass('d-none');

        $('#vat_rate').removeAttr('disabled');
        $('#vat_rate').parent('div').removeClass('d-none');

        $('#vat_amount').removeAttr('disabled');
        $('#vat_amount').parent('div').removeClass('d-none');

        $('#total_amount').removeAttr('disabled');
        $('#total_amount').parent('div').removeClass('d-none');

        $('#exchange_currency').removeAttr('disabled');
        $('#exchange_currency').parent('div').removeClass('d-none');

        $('#exchange_rate').removeAttr('disabled');
        $('#exchange_rate').parent('div').removeClass('d-none');

        $('#exchange_net_amount').removeAttr('disabled');
        $('#exchange_net_amount').parent('div').removeClass('d-none');

        $('#exchange_vat_amount').removeAttr('disabled');
        $('#exchange_vat_amount').parent('div').removeClass('d-none');

        $('#exchange_total_amount').removeAttr('disabled');
        $('#exchange_total_amount').parent('div').removeClass('d-none');
     
        $repeater.find('[data-repeater-item]').slice(1).remove();
        $repeater.find('[data-repeater-item]')
          .first()
          .find('.sales-invoice-ref-no')
          .val('');
        $repeater
          .find('[data-repeater-item]')
          .first()
          .find('.sales-invoice-ref-no')
          .attr('disabled', 'disabled');
        $repeater.parent('div').addClass('d-none');

        var credit_note = (analyzepdf_data.credit_note) ? true : false;
        $('#credit_note').attr('checked', credit_note);

        $('#vat_rate').val(analyzepdf_data.vat_rate);
        $('#vat_amount').val(analyzepdf_data.vat_amount);
        $('#total_amount').val(analyzepdf_data.total_amount);

        //$('#exchange_currency').val(analyzepdf_data.exchange_currency);
        $('#exchange_rate').val(analyzepdf_data.exchange_rate);
        //$('#exchange_net_amount').val(analyzepdf_data.exchange_net_amount);
        $('#exchange_vat_amount').val(analyzepdf_data.exchange_vat_amount);
        $('#exchange_total_amount').val(analyzepdf_data.exchange_total_amount);
      } //sales
      else if(analyzepdf_data.invoice_type === 'com')
      {
        $('#credit_note').attr('disabled', 'disabled');
        $('#credit_note').parent('div').addClass('d-none');
        
        $('#vat_rate').attr('disabled', 'disabled');
        $('#vat_rate').parent('div').addClass('d-none');

        $('#vat_amount').attr('disabled', 'disabled');
        $('#vat_amount').parent('div').addClass('d-none');

        $('#total_amount').attr('disabled', 'disabled');
        $('#total_amount').parent('div').addClass('d-none');

        // $('#exchange_currency').attr('disabled', 'disabled');
        // $('#exchange_currency').parent('div').addClass('d-none');

        $('#exchange_rate').attr('disabled', 'disabled');
        $('#exchange_rate').parent('div').addClass('d-none');

        // $('#exchange_net_amount').attr('disabled', 'disabled');
        // $('#exchange_net_amount').parent('div').addClass('d-none'); 

        $('#exchange_vat_amount').attr('disabled', 'disabled');       
        $('#exchange_vat_amount').parent('div').addClass('d-none'); 

        $('#exchange_total_amount').attr('disabled', 'disabled');
        $('#exchange_total_amount').parent('div').addClass('d-none');        

        let invoiceArray = analyzepdf_data.related_sales_invoices;

        // Clear existing repeater items except first
        $repeater.find('[data-repeater-item]').slice(1).remove();

        var $firstItem = $repeater.find('[data-repeater-item]').first();
        $firstItem.find('.sales-invoice-ref-no').val('');
      
        // Show container
        $repeater.parent('div').removeClass('d-none');

        if (invoiceArray && invoiceArray.length > 0) 
        {
          invoiceArray.forEach(function(invoiceValue, index) {
            if (index === 0) {
              $firstItem.find('.sales-invoice-ref-no')
                  .removeAttr('disabled')
                  .val(invoiceValue);
            } else {
              // Create new repeater row using plugin
              $repeater.find('[data-repeater-create]').click();

              // Set value in last created item
              $repeater.find('[data-repeater-item]').last()
                  .find('.sales-invoice-ref-no')
                  .removeAttr('disabled')
                  .val(invoiceValue);
            }
          });
        } //related sales invocies
      }//com
    }//if value exist
       
      if(analyzepdf_data.azure_url)
      {
        //Azure Storage Path
        $.get(analyzePdfUrl + analyzepdf_data.id + '/sas-url', function(response) {
            if (response.azure_signed_url) {
                //var pdfUrl = response.azure_signed_url + '#page=' + (response.start_pageno || 1);
                var pdfUrl = response.azure_signed_url + '#page=1';
                $('#offcanvasAnalyzePdfData #docViewer').attr('src', pdfUrl);
            } else {
                console.log('PDF not available.');
            }
        }).fail(function() {
            console.log('Failed to fetch PDF.');
        });
        //Azure Storage Path
      }
      else
      {
        //Local Storage Path
        var pdfUrl = '/storage/ocr/'+ invoice_type +'/' + analyzepdf_data.file_name + '#page=1';
        // var pdfUrl = '/storage/ocr/'+ invoice_type +'/' + analyzepdf_data.file_name + '#page=' + 
        //               (analyzepdf_data.start_pageno ? analyzepdf_data.start_pageno : 1);
        // if(invoice_type == 'multi-invoices')
        // {        
        //   var cleaned = analyzepdf_data.file_name.replace(/_\d+(?=\.pdf$)/i, '');
        //   pdfUrl = '/storage/ocr/'+ invoice_type +'/' + cleaned + '#page=' + 
        //               (analyzepdf_data.start_pageno ? analyzepdf_data.start_pageno : 1);
        // }      
        $('#offcanvasAnalyzePdfData #docViewer').attr('src', pdfUrl);
        //Local Storage Path
      }

    /*} //search
    else
    {      
      if(filter_analyzepdf_data.length > 0)
      {
        var analyzepdf_data = filter_analyzepdf_data[0]; 
     
        var parsed_extracted_data = JSON.parse(analyzepdf_data.extracted_data);
        
        $('#analyzepdf_id').val(analyzepdf_id);

        var page_no = 1;
        var invoice_type = analyzepdf_data.invoice_type;
       
        if(invoice_type == 'sales' || invoice_type == 'multi-invoices')
        {        
          $('#invoice_type').val(invoice_type);
                  
          let org_no = null;
          const getNumeric = str => str ? str.replace(/\D/g, '') : '';

          if(parsed_extracted_data)
          {
            let vat_numeric = getNumeric( (parsed_extracted_data.supplier.org_number) ? parsed_extracted_data.supplier.org_number.replace(/[a-zA-Z\s]+/g, '') :
                          ((parsed_extracted_data.supplier.cvr_number) ?  parsed_extracted_data.supplier.cvr_number.replace(/[a-zA-Z\s]+/g, '') : '')
                         );
            
            if (vat_numeric.length == 17) {
                org_no = vat_numeric.substring(0, 9);
            }
            else
            {
              if (vat_numeric.length >= 9)
                org_no = vat_numeric;      
            }
          }

          $('#client_no').val(org_no);      
          $('#client_name').val(analyzepdf_data.client_name);

          $('#credit_note').removeAttr('disabled');
          $('#credit_note').parent('div').removeClass('d-none');

          $('#net_amount').removeAttr('disabled');
          $('#net_amount').parent('div').removeClass('d-none');

          $('#vat_rate').removeAttr('disabled');
          $('#vat_rate').parent('div').removeClass('d-none');

          $('#vat_amount').removeAttr('disabled');
          $('#vat_amount').parent('div').removeClass('d-none');

          $('#total_amount').removeAttr('disabled');
          $('#total_amount').parent('div').removeClass('d-none');

          $('#exchange_currency').removeAttr('disabled');
          $('#exchange_currency').parent('div').removeClass('d-none');

          $('#exchange_net_amount').removeAttr('disabled');
          $('#exchange_net_amount').parent('div').removeClass('d-none');

          $('#exchange_vat_amount').removeAttr('disabled');
          $('#exchange_vat_amount').parent('div').removeClass('d-none');
       
          $repeater.find('[data-repeater-item]').slice(1).remove();
          $repeater.find('[data-repeater-item]')
            .first()
            .find('.sales-invoice-ref-no')
            .val('');
          $repeater
            .find('[data-repeater-item]')
            .first()
            .find('.sales-invoice-ref-no')
            .attr('disabled', 'disabled');
          $repeater.parent('div').addClass('d-none');

          if(parsed_extracted_data)
          {
            var credit_note = (parsed_extracted_data.credit_note) ? true : false;
            $('#credit_note').attr('checked', credit_note);

            let net_amount = parsed_extracted_data.net_amount ? parsed_extracted_data.net_amount.replace(/[a-zA-Z\s]+/g, '') : '';
            let vat_amount = parsed_extracted_data.vat_amount ? parsed_extracted_data.vat_amount.replace(/[a-zA-Z\s]+/g, '') : '';
            let total_amount = parsed_extracted_data.total_amount ? parsed_extracted_data.total_amount.replace(/[a-zA-Z\s]+/g, '') : ''; 
            let discount_amount = parsed_extracted_data.discount_amount ? parsed_extracted_data.discount_amount.replace(/[a-zA-Z\s]+/g, '') : '';       
            
            if (discount_amount && /^\d$/.test(discount_amount))
              discount_amount = '';

    console.log("vat rate: " + parsed_extracted_data.vat_rate);
    console.log("vat amount: " + vat_amount);
    console.log("net amount: " + net_amount);
    console.log("discount amount: " + discount_amount);

            //calculate vat rate from amount
            let pass_currency = 'NOK';
            if(parsed_extracted_data.currency)
              pass_currency = parsed_extracted_data.currency.trim().replace(/[^\w\s]/g, "");

            if(pass_currency == 'kr')
              pass_currency = 'DKK';

            let parse_net_amount = parseAmountValue(net_amount);
            let parse_vat_amount = parseAmountValue(vat_amount);
            let parse_total_amount = parseAmountValue(total_amount);
            let parse_discount_amount = parseAmountValue(discount_amount);
    // console.log("PARSED net amount: " + parse_net_amount);
    // console.log("PARSED vat amount: " + parse_vat_amount);
    // console.log("PARSED total amount: " + parse_total_amount);
    // console.log("PARSED discount amount: " + parse_discount_amount);        
            if(/,(\d{1,2})$/.test(net_amount))
            {
              parse_net_amount = parseAmountValue(net_amount, 'NOK');
              parse_vat_amount = parseAmountValue(vat_amount, 'NOK');
              parse_total_amount = parseAmountValue(total_amount, 'NOK');
              parse_discount_amount = parseAmountValue(discount_amount, 'NOK');
            }       

    console.log("PARSED net amount: " + parse_net_amount);
    console.log("PARSED vat amount: " + parse_vat_amount);
    console.log("PARSED total amount: " + parse_total_amount);
    console.log("PARSED discount amount: " + parse_discount_amount);

            if(parse_discount_amount)
            {
              console.log("has discount_amount");

              let parse_sub_discount_amount = parse_net_amount - parse_discount_amount;
              parse_net_amount = parse_sub_discount_amount;

              console.log(parse_net_amount);

              // let sub_discount_amount = net_amount - discount_amount;
              net_amount = parse_sub_discount_amount.toLocaleString('en-IN');
              
              console.log(net_amount);          
            }

            if(parse_net_amount > parse_total_amount)
            {
              if(parsed_extracted_data.credit_note)
              {
                let formatted_net_amount = parseDenmarkFormat(net_amount);        
                $('#net_amount').val(formatted_net_amount);

                let formatted_total_amount = parseDenmarkFormat(total_amount);        
                $('#total_amount').val(formatted_total_amount);
              }
              else
              {
                let formatted_net_amount = parseDenmarkFormat(total_amount);        
                $('#net_amount').val(formatted_net_amount);

                let formatted_total_amount = parseDenmarkFormat(net_amount);        
                $('#total_amount').val(formatted_total_amount);
              }          
            }
            else
            {
              let formatted_net_amount = parseDenmarkFormat(net_amount);        
              $('#net_amount').val(formatted_net_amount);

              let formatted_total_amount = parseDenmarkFormat(total_amount);        
              $('#total_amount').val(formatted_total_amount);
            }

            let formatted_vat_amount = parseDenmarkFormat(vat_amount);        
            $('#vat_amount').val(formatted_vat_amount);            

    console.log("parse vat amount: " + parse_vat_amount);
    console.log("parse net amount: " + parse_net_amount);         
            var calculated_vat_rate = (parse_net_amount == 0) ? 0 : ((parse_vat_amount / parse_net_amount) * 100);
            //$('#vat_rate').val(vat_rate);
    console.log("CALCULATED vat rate: " + calculated_vat_rate + " -- " + parsed_extracted_data.currency + " -- " + pass_currency);
            if(parsed_extracted_data.vat_rate)
            {          
              var vat_rate = parseVatRate(parsed_extracted_data.vat_rate);
    console.log("vat rate: " + vat_rate);
              if(parsed_extracted_data.vat_rate == calculated_vat_rate)
                $('#vat_rate').val(vat_rate);
              else if(calculated_vat_rate > 25)
              {
                $('#vat_rate').val(vat_rate);
              }
              else
              {
                let calculated_vat_rate_result = null;
                if (calculated_vat_rate >= 8 && calculated_vat_rate < 9)
                  calculated_vat_rate_result = "8,1";
                else
                  calculated_vat_rate_result = Math.round(calculated_vat_rate).toString();

                $('#vat_rate').val(calculated_vat_rate_result);
              }
            }
            else
            {
              let calculated_vat_rate_result = null;
              if (calculated_vat_rate >= 8 && calculated_vat_rate < 9)
                calculated_vat_rate_result = "8,1";
              else
                calculated_vat_rate_result = Math.round(calculated_vat_rate).toString();

              $('#vat_rate').val(calculated_vat_rate_result);
            }
            
            $('#invoice_date').val(parsed_extracted_data.invoice_date);

            $('#invoice_no').val(parsed_extracted_data.invoice_number);

            if(parsed_extracted_data.currency)
              //$('#currency').val(parsed_extracted_data.currency.trim().substring(0, 3)); 
              $('#currency').val(pass_currency.trim().substring(0, 3)); 

            if(parsed_extracted_data.exchange_currency)
              $('#exchange_currency').val(parsed_extracted_data.exchange_currency.trim().replace(/[^\w\s]/g, "").substring(0, 3));

            let exchange_net_amount = parsed_extracted_data.exchange_net_amount ? parsed_extracted_data.exchange_net_amount.replace(/[a-zA-Z\s]+/g, '') : '';       
            let formatted_exchange_net_amount = parseDenmarkFormat(exchange_net_amount);       
            $('#exchange_net_amount').val(formatted_exchange_net_amount);

            let exchange_vat_amount = parsed_extracted_data.exchange_vat_amount ? parsed_extracted_data.exchange_vat_amount.replace(/[a-zA-Z\s]+/g, '') : '';       
            let formatted_exchange_vat_amount = parseDenmarkFormat(exchange_vat_amount);       
            $('#exchange_vat_amount').val(formatted_exchange_vat_amount); 
          }      
        }    
        else if(invoice_type == 'com')
        {        
          $('#invoice_type').val(invoice_type);

          let org_no = null;
          const getNumeric = str => str ? str.replace(/\D/g, '') : '';

          if(parsed_extracted_data)
          {
            let vat_numeric = getNumeric( (parsed_extracted_data.recipient.org_number) ? parsed_extracted_data.recipient.org_number.replace(/[a-zA-Z\s]+/g, '') : '');
            
            if (vat_numeric && vat_numeric.length == 17) {
                org_no = vat_numeric.substring(0, 9);
            }
            else
            {
              if (vat_numeric && vat_numeric.length >= 9)
                org_no = vat_numeric;      
            }
          }

          $('#client_no').val(org_no);       
          $('#client_name').val(analyzepdf_data.client_name);

          if(parsed_extracted_data)
          {
            let net_amount = parsed_extracted_data.net_amount ? parsed_extracted_data.net_amount.replace(/[a-zA-Z\s]+/g, '') : '';       
            let formatted_net_amount = parseDenmarkFormat(net_amount);
            $('#net_amount').val(formatted_net_amount);
          }

          $('#credit_note').attr('disabled', 'disabled');
          $('#credit_note').parent('div').addClass('d-none');
          
          $('#vat_rate').attr('disabled', 'disabled');
          $('#vat_rate').parent('div').addClass('d-none');

          $('#vat_amount').attr('disabled', 'disabled');
          $('#vat_amount').parent('div').addClass('d-none');

          $('#total_amount').attr('disabled', 'disabled');
          $('#total_amount').parent('div').addClass('d-none');

          $('#exchange_currency').attr('disabled', 'disabled');
          $('#exchange_currency').parent('div').addClass('d-none');

          $('#exchange_net_amount').attr('disabled', 'disabled');
          $('#exchange_net_amount').parent('div').addClass('d-none'); 

          $('#exchange_vat_amount').attr('disabled', 'disabled');       
          $('#exchange_vat_amount').parent('div').addClass('d-none');    
          
          var sales_invoices_raw = (parsed_extracted_data) ? parsed_extracted_data.related_sales_invoices : null;

          if (sales_invoices_raw) 
          {
              if (!Array.isArray(sales_invoices_raw)) {
                  sales_invoices_raw = [sales_invoices_raw];
              }

              var invoiceValues = new Set();

              sales_invoices_raw.forEach(function(val) {
                  if (!val) return;

                  // Split by commas first
                  var commaParts = String(val).split(',');

                  commaParts.forEach(function(part) {                    
                      part = part.trim().replace(/[.,;]+$/, '');console.log("PART: " + part);
                      if (!part) return;

                      // Match alphanumeric or numeric range first (with optional spaces around dash)
                      var rangeMatch = part.match(/^([A-Za-z]*)(\d+)\s*-\s*([A-Za-z]*)(\d+)$/);

                      if (rangeMatch) {
                          var prefixStart = rangeMatch[1];
                          var startNum = parseInt(rangeMatch[2], 10);
                          var prefixEnd = rangeMatch[3];
                          var endNum = parseInt(rangeMatch[4], 10);

                          if (prefixStart === prefixEnd && startNum <= endNum) {
                              for (var i = startNum; i <= endNum; i++) {
                                  invoiceValues.add(
                                      prefixStart + i.toString().padStart(rangeMatch[2].length, '0')
                                  );
                              }
                          }
                      } else {
                          // Not a range: split by spaces (for "123 124 125" or "NO123 NO124")
                          part.split(/\s+/).forEach(function(p) {
                              if (p) invoiceValues.add(p);
                          });
                      }
                  });
              });
            
              // Optional: convert to array and sort numerically/alphabetically
              var invoiceArray = Array.from(invoiceValues).sort((a,b) => {
                  var numA = parseInt(a.replace(/\D+/g,''), 10);
                  var numB = parseInt(b.replace(/\D+/g,''), 10);
                  return (numA && numB) ? numA - numB : a.localeCompare(b);
              });

              console.log(invoiceArray);
          }                    
                
          // Clear existing repeater items except first
          $repeater.find('[data-repeater-item]').slice(1).remove();

          var $firstItem = $repeater.find('[data-repeater-item]').first();
          $firstItem.find('.sales-invoice-ref-no').val('');

          // Show container
          $repeater.parent('div').removeClass('d-none');

          if (invoiceArray && invoiceArray.length > 0) {

              invoiceArray.forEach(function(invoiceValue, index) {

                  if (index === 0) {

                      $firstItem.find('.sales-invoice-ref-no')
                          .removeAttr('disabled')
                          .val(invoiceValue);

                  } else {

                      // Create new repeater row using plugin
                      $repeater.find('[data-repeater-create]').click();

                      // Set value in last created item
                      $repeater.find('[data-repeater-item]').last()
                          .find('.sales-invoice-ref-no')
                          .removeAttr('disabled')
                          .val(invoiceValue);
                  }

              });
          }
         
          if(parsed_extracted_data)
          {
            $('#invoice_date').val(parsed_extracted_data.invoice_date);
            $('#invoice_no').val(parsed_extracted_data.invoice_number);

            if(parsed_extracted_data.currency)
              $('#currency').val(parsed_extracted_data.currency.trim().replace(/[^\w\s]/g, "").substring(0, 3));
          }
        } //com   
      
      
        if(analyzepdf_data.azure_url)
        {
          //Azure Storage Path
          $.get(analyzePdfUrl + analyzepdf_data.id + '/sas-url', function(response) {
              if (response.azure_signed_url) {
                  var pdfUrl = response.azure_signed_url + '#page=' + (response.start_pageno || 1);
                  $('#offcanvasAnalyzePdfData #docViewer').attr('src', pdfUrl);
              } else {
                  console.log('PDF not available.');
              }
          }).fail(function() {
              console.log('Failed to fetch PDF.');
          });
          //Azure Storage Path
        }
        else
        {
          //Local Storage Path
          var pdfUrl = '/storage/ocr/'+ invoice_type +'/' + analyzepdf_data.file_name + '#page=' + 
                        (analyzepdf_data.start_pageno ? analyzepdf_data.start_pageno : 1);
          if(invoice_type == 'multi-invoices')
          {        
            var cleaned = analyzepdf_data.file_name.replace(/_\d+(?=\.pdf$)/i, '');
            pdfUrl = '/storage/ocr/'+ invoice_type +'/' + cleaned + '#page=' + 
                        (analyzepdf_data.start_pageno ? analyzepdf_data.start_pageno : 1);
          }      
          $('#offcanvasAnalyzePdfData #docViewer').attr('src', pdfUrl);
          //Local Storage Path
        }
      }
    }//capture page 
    */  
        $("#offcanvasAnalyzePdfData #loader").remove();
        $("#addAnalyzePdfForm").show();
        $("#offcanvasAnalyzePdfData #docViewer").show();
       
  });

  // inovice type change
  $(document).on('change', '#invoice_type', function () {
    $repeater.find('[data-repeater-item]').slice(1).remove();
    $repeater.find('[data-repeater-item]')
      .first()
      .find('.sales-invoice-ref-no')
      .val('');

    if($(this).val() == 'com')
    {
      $('#credit_note').parent('div').addClass('d-none');
      $('#net_amount').parent('div').addClass('d-none');
      $('#vat_rate').parent('div').addClass('d-none');
      $('#vat_amount').parent('div').addClass('d-none');
      $('#exchange_currency').parent('div').addClass('d-none');
      $('#exchange_rate').parent('div').addClass('d-none');
      $('#exchange_net_amount').parent('div').addClass('d-none');        
      $('#exchange_vat_amount').parent('div').addClass('d-none');   
      $('#exchange_total_amount').parent('div').addClass('d-none'); 
     
      $repeater.parent('div').removeClass('d-none');
    }
    else
    {
      $('#credit_note').parent('div').removeClass('d-none');
      $('#net_amount').parent('div').removeClass('d-none');
      $('#vat_rate').parent('div').removeClass('d-none');
      $('#vat_amount').parent('div').removeClass('d-none');
      $('#exchange_currency').parent('div').removeClass('d-none');
      $('#exchange_rate').parent('div').removeClass('d-none');
      $('#exchange_net_amount').parent('div').removeClass('d-none');
      $('#exchange_vat_amount').parent('div').removeClass('d-none');
      $('#exchange_total_amount').parent('div').removeClass('d-none');
           
      $repeater.parent('div').addClass('d-none');
    }
  });

  //Filter    
  $(document).on('click', '.btn-analyzepdf-filter', function() {    
    let activeTab = $('.nav-tabs .nav-item .nav-link.active').attr('id').replace('btn-analyzepdf-', '')
                      .replace('btn-analyzepdfsearch-', '');
    console.log("activeTab :" + activeTab);
    filterAnalyzePdf(activeTab);
  });   

  //Filter    
  $(document).on('click', '.btn-analyzepdf-clear-filter', function() {  
    clearFilterItems();  
    let activeTab = $('.nav-tabs .nav-item .nav-link.active').attr('id').replace('btn-analyzepdf-', '')
                      .replace('btn-analyzepdfsearch-', '');
    console.log("activeTab :" + activeTab);
    filterAnalyzePdf(activeTab);
  });    

  function clearFilterItems() {        
    $("#filter_invoice_type").val('');
    $("#filter_client_no").val('');
    $("#filter_client_name").val('');
    $("#filter_invoice_date").val('');
    $("#filter_invoice_no").val('');
    $("#filter_currency").val('');
    $("#filter_credit_note").val('');
    $("#filter_net_amount").val('');   
    $("#filter_vat_amount").val('');
    $("#filter_total_amount").val('');    
  }

  function filterAnalyzePdf(tab_name)
  {  
    let docType   = $('#filter_invoice_type').val();
    let clientNo  = $('#filter_client_no').val().trim().toLowerCase();
    let clientName = $('#filter_client_name').val().trim().toLowerCase();
    let invoiceDate  = $('#filter_invoice_date').val().trim().toLowerCase();
    let invoiceNo  = $('#filter_invoice_no').val().trim().toLowerCase();
    let currency  = $('#filter_currency').val().trim().toLowerCase();
    let creditNote  = $('#filter_credit_note').is(':checked');
    
    let netAmount  = normalizeAmountForFilter($('#filter_net_amount').val().trim());
    let vatAmount  = normalizeAmountForFilter($('#filter_vat_amount').val().trim());
    let totalAmount  = normalizeAmountForFilter($('#filter_total_amount').val().trim());

    let noFiltersApplied =
        !docType &&
        !clientNo &&
        !clientName &&
        !invoiceDate &&
        !invoiceNo &&
        !currency &&
        !creditNote &&
        (netAmount == null || netAmount === 0) &&
        (vatAmount == null || vatAmount === 0) &&
        (totalAmount == null || totalAmount === 0);       
  
    // Select dataset & table based on tab_name
    let analyzepdf_datas, table;
    if(tab_name === 'completed')
      analyzepdf_datas = analyzepdf_completed_datas;
    else if(tab_name === 'processing')
      analyzepdf_datas = analyzepdf_processing_datas;
    else if(tab_name === 'error')
      analyzepdf_datas = analyzepdf_error_datas;
    else if(tab_name === 'deleted')
      analyzepdf_datas = analyzepdf_deleted_datas;
    else if(tab_name === 'commercial-invoice')
      analyzepdf_datas = analyzepdf_commercial_invoice_datas;
    else if(tab_name === 'sales-invoice')
      analyzepdf_datas = analyzepdf_sales_invoice_datas;
    else
      return; // unknown tab
   console.log(analyzepdf_datas);
    // Filter data
    let filteredData = analyzepdf_datas.filter(item => {

        // Document type filter
        if (docType && item.invoice_type !== docType) return false;

        // Extracted data parsing
        let extracted = [];
        if (item.extracted_data) {
          try {           
            // let parsed = typeof item.extracted_data === 'string'
            //     ? JSON.parse(item.extracted_data)
            //     : item.extracted_data;

            let parsed = item.extracted_data
                          ? (typeof item.extracted_data === 'string'
                              ? JSON.parse(item.extracted_data)
                              : item.extracted_data)
                          : null;

            extracted = Array.isArray(parsed) ? parsed : [parsed];
          } catch (e) {
            extracted = [];
          }
        }
       
        let match = extracted.some(invoice => { 

          let extractedClientNo = null;
          let extractedClientName = null;
          if(item.invoice_type == 'com')
          {
            let recipient = invoice.recipient ?? {};
           
            extractedClientNo = (recipient.org_number) ? recipient.org_number.replace(/[a-zA-Z\s]+/g, '') :
                        ((recipient.cvr_number) ?  recipient.cvr_number.replace(/[a-zA-Z\s]+/g, '') : '');

            extractedClientName = (recipient.name ?? '').toLowerCase();
          }
          else
          {            
            let supplier = invoice.supplier ?? {};
            
            extractedClientNo = (supplier.org_number) ? supplier.org_number.replace(/[a-zA-Z\s]+/g, '') :
                        ((supplier.cvr_number) ?  supplier.cvr_number.replace(/[a-zA-Z\s]+/g, '') : '');    

            extractedClientName = (supplier.name ?? '').toLowerCase();
          }          
      
          if (clientNo && !String(extractedClientNo).includes(clientNo)) return false;
          if (clientName && !String(extractedClientName).includes(clientName)) return false;

          if (invoiceDate && !String(invoice.invoice_date).toLowerCase().includes(invoiceDate)) return false;
          //if (invoiceNo && !String(invoice.invoice_number).toLowerCase().includes(invoiceNo)) return false;
          if (
            invoiceNo &&
            ![
              invoice.invoice_number,
              invoice.no_invoice_number,
              invoice.order_number
            ]
              .filter(Boolean)
              .some(value =>
                String(value).toLowerCase().includes(invoiceNo.toLowerCase())
              )
          ) {
            return false;
          }
          if (currency && !String(invoice.currency).toLowerCase().includes(currency)) return false;

          if (creditNote && invoice.credit_note !== true) return false;

          let invoiceNet   = normalizeAmountForFilter(invoice.net_amount);
          let invoiceVat   = normalizeAmountForFilter(invoice.vat_amount);
          let invoiceTotal = normalizeAmountForFilter(invoice.total_amount);

          if (netAmount !== null && invoiceNet !== netAmount) return false;
          if (vatAmount !== null && invoiceVat !== vatAmount) return false;
          if (totalAmount !== null && invoiceTotal !== totalAmount) return false;     

          return true;
        });
       
        return match;
    });

    // Reload corresponding DataTable  
    if(tab_name === 'commercial-invoice' || tab_name === 'sales-invoice')  
      table = $('.datatables-'+ tab_name +'-analyzepdfsearch').DataTable();
    else
      table = $('.datatables-'+ tab_name +'-analyzepdf').DataTable();
    
    var item_count = 0;  
    if (noFiltersApplied)
    {
      item_count = analyzepdf_datas.length;
      table.clear().rows.add(analyzepdf_datas).draw();
    }
    else
    {
      item_count = filteredData.length;
      table.clear().rows.add(filteredData).draw();   
    }

    if(tab_name === 'commercial-invoice' || tab_name === 'sales-invoice') 
      $("#btn-analyzepdfsearch-"+ tab_name +" span").html(item_count);
    else
      $("#btn-analyzepdf-"+ tab_name +" span").html(item_count);
  }

  //Update      
  $(document).on("submit", "#addAnalyzePdfForm", function(event)
  {
    event.preventDefault();

    var form = $(this);        
    var analyze_id = $('#analyze_id').val();

    var btn_save_form = form.find("button.btn-save-analyze-data");
    btn_save_form.attr('disabled', 'disabled');
    btn_save_form.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');

    $.ajax({      
      url: `${analyzePdfUrl}` + analyze_id,
      type: 'PUT',     
      data: form.serialize(),     
      success: function (data) {       
        $("#offcanvasAnalyzePdfData").offcanvas('hide');

        btn_save_form.removeAttr('disabled');
        btn_save_form.html("Save");   

        var analyzepdf_datas = drawDtTable(data, 'analyzepdf');       
        reloadAnalyzedPdf(analyzepdf_datas);         
      },
      error: function (error) {
        console.log(error);
      }
    }); 
  });  

  $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {  
    var id = $(e.target).attr("id") // activated tab

   console.log(id);
    if(id == 'btn-analyzepdf-completed')
    {            
      $(".dt-analyzepdf-export .completed-analyzepdf-export").removeClass('d-none');  
      $(".dt-analyzepdf-export .processing-analyzepdf-export").addClass('d-none');     
      $(".dt-analyzepdf-export .error-analyzepdf-export").addClass('d-none');
      $(".dt-analyzepdf-export .deleted-analyzepdf-export").addClass('d-none');

      $(".dt-search-filter .completed-search-filter").removeClass('d-none');    
      $(".dt-search-filter .processing-search-filter").addClass('d-none');    
      $(".dt-search-filter .error-search-filter").addClass('d-none');
      $(".dt-search-filter .deleted-search-filter").addClass('d-none');          
    }
    else if(id == 'btn-analyzepdf-processing')
    {            
      $(".dt-analyzepdf-export .completed-analyzepdf-export").addClass('d-none');  
      $(".dt-analyzepdf-export .processing-analyzepdf-export").removeClass('d-none');     
      $(".dt-analyzepdf-export .error-analyzepdf-export").addClass('d-none');
      $(".dt-analyzepdf-export .deleted-analyzepdf-export").addClass('d-none');

      $(".dt-search-filter .completed-search-filter").addClass('d-none');    
      $(".dt-search-filter .processing-search-filter").removeClass('d-none');    
      $(".dt-search-filter .error-search-filter").addClass('d-none');       
      $(".dt-search-filter .deleted-search-filter").addClass('d-none');   
    } 
    else if(id == 'btn-analyzepdf-error')
    {      
      $(".dt-analyzepdf-export .completed-analyzepdf-export").addClass('d-none');  
      $(".dt-analyzepdf-export .processing-analyzepdf-export").addClass('d-none');     
      $(".dt-analyzepdf-export .error-analyzepdf-export").removeClass('d-none');
      $(".dt-analyzepdf-export .deleted-analyzepdf-export").addClass('d-none');

      $(".dt-search-filter .completed-search-filter").addClass('d-none');    
      $(".dt-search-filter .processing-search-filter").addClass('d-none');    
      $(".dt-search-filter .error-search-filter").removeClass('d-none');      
      $(".dt-search-filter .deleted-search-filter").addClass('d-none');      
    }
    else if(id == 'btn-analyzepdf-deleted')
    {      
      $(".dt-analyzepdf-export .completed-analyzepdf-export").addClass('d-none');  
      $(".dt-analyzepdf-export .processing-analyzepdf-export").addClass('d-none');     
      $(".dt-analyzepdf-export .error-analyzepdf-export").addClass('d-none');
      $(".dt-analyzepdf-export .deleted-analyzepdf-export").removeClass('d-none');

      $(".dt-search-filter .completed-search-filter").addClass('d-none');    
      $(".dt-search-filter .processing-search-filter").addClass('d-none');    
      $(".dt-search-filter .error-search-filter").addClass('d-none');      
      $(".dt-search-filter .deleted-search-filter").removeClass('d-none');      
    }
  });

  // Analyze PDF - Delete
  $(document).on('click', '.btn-delete-analyzepdf', function () {    
    var btn_delete_analyzepdf = $(this);
    var data = btn_delete_analyzepdf.data();

    var analyzepdf_id = data['analyzepdf_id'];
    var invoice_no = data['invoice_no'];
    //var invoice_name = data['invoice_name'];
    var which_tab = data['tab_name'];

    var selected_analyzepdf_id = $.map($('#navs-analyzepdf-'+ which_tab +' .form-check-input.dt-checkboxes:checked'), function(c){
                                  return c.value; 
                              });
    var selected_analyzepdfs = $.map($('#navs-analyzepdf-'+ which_tab +' .form-check-input.dt-checkboxes:checked'), function(c){                                  
                              return $(c).data('invoice_no');       
                            });

    btn_delete_analyzepdf.attr('disabled', 'disabled');
    btn_delete_analyzepdf.addClass('disabled-opacity');    
    btn_delete_analyzepdf.html('<span><i class="bx bx-x me-2"></i>Deleting...</span>');

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to delete the analyse pdf!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        //btn_delete_analyzepdf.attr('disabled', 'disabled');
        //btn_delete_analyzepdf.html('<i class="spinner-border me-1"></i>');

        // $("#analyzepdf_delete_id").val(analyzepdf_id);    
        // $("#analyzepdf_delete_invoice_no").val(invoice_no);

        $("#analyzepdf_delete_id").val(((selected_analyzepdf_id.length > 0) ? selected_analyzepdf_id : analyzepdf_id));    
        $("#analyzepdf_delete_invoice_no").val(((selected_analyzepdfs.length > 0) ? selected_analyzepdfs : invoice_no));

        $("#analyzepdf_delete_tab_name").val(which_tab);

        $('#modalAnalyzePdfDelete').modal('show');       

      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled delete :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }

      btn_delete_analyzepdf.removeAttr('disabled');               
      btn_delete_analyzepdf.removeClass('disabled-opacity');  
      btn_delete_analyzepdf.html('<span><i class="bx bx-x me-2"></i>Delete</span>');
    });   
  });

  // Analyze PDF - Delete - Save reason
  $(document).on("submit", ".frm-analyzepdf-delete", function(event) {  
    event.preventDefault();

    //var selected_reason = $("#invoice-disregard-reason").val();

    if($(this).find(".ql-editor").html().replace( /(<([^>]+)>)/ig, '') == "")
    {      
      Swal.fire({
        title: 'Error',
        text: 'Please type reason for deleteing the pdf',
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
      var formId = $(this).attr('id');
      
      var analyzepdf_id = (String($('#analyzepdf_delete_id').val()).indexOf(',') != -1) ? 0 : $('#analyzepdf_delete_id').val();
      
      $("#analyzepdf-delete-reason-quill").val($(this).find(".ql-editor").html());

      var formData = new FormData(this);         
                   
      var btn_analyzepdf_delete_reason_save = $("#" + formId + " #btn-analyzepdf-delete-reason-save");
      btn_analyzepdf_delete_reason_save.attr('disabled', 'disabled');
      btn_analyzepdf_delete_reason_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Deleting...');
     
      $.ajax({
        url: `${analyzePdfUrl}${analyzepdf_id}/delete`,
        type: 'POST',
        dataType: "JSON",
        data: formData,
        processData: false,
        contentType: false,
        success: function (result) {
         
          if(result)    
          {           
            btn_analyzepdf_delete_reason_save.removeAttr('disabled');
            btn_analyzepdf_delete_reason_save.html('Deleted');
            btn_analyzepdf_delete_reason_save.removeClass('disabled');

            // var which_tab = result['tab_name'];
            // $('#btn_'+ which_tab +'_disregard_invoice').html(
            //   '<span><i class="bx bx-x"></i> Delete</span>');
                          
            var analyzepdf_datas = drawDtTable(result, 'analyzepdf');            
            reloadAnalyzedPdf(analyzepdf_datas);

            // if(which_tab == 'correct')
            // {
            //   analyzepdf_completed_datas = analyzepdf_datas['analyzepdf_completed_datas'];
            //   dt_correct_invoices.clear().rows.add(analyzepdf_completed_datas).draw();

            //   $("#btn-invoice-correct span").html(invoice_correct_datas.length);
            // }
            // else if(which_tab == 'wrong')
            // {
            //   invoice_wrong_datas = analyzepdf_datas['invoice_wrong_datas']; 
            //   dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();

            //   $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);
            // }
            // else if(which_tab == 'managed')
            // {
            //   invoice_managed_datas = analyzepdf_datas['invoice_managed_datas'];
            //   dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw(); 
            //   $("#btn-invoice-managed span").html(invoice_managed_datas.length);
            // }
            // else if(which_tab == 'deleted')
            // {
            //   invoice_managed_datas = analyzepdf_datas['invoice_managed_datas'];
            //   dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw(); 
            //   $("#btn-invoice-managed span").html(invoice_managed_datas.length);
            // }
            //filterShowDisregardedInvoices(which_tab);
            //reInitializeTooltips();

            var swal_title = 'Deleted and reason saved';  
            var swal_text = 'Analyze PDF has been deleted and the reason ';         
                       
            //Clear Modal Values
            $("#analyzepdf_delete_id").val('');    
            $("#analyzepdf_delete_invoice_no").val('');
            $("#analyzepdf_delete_tab_name").val('');
            $("#analyzepdf-delete-reason-editor").find(".ql-editor").html("");
            
            $('#modalAnalyzePdfDelete').modal('hide');

            Swal.fire({
              icon: 'success',
              title: swal_title +'!',
              text: swal_text + ' has been saved.',
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
  });

  // recapture
  $(document).on('click', '.btn-recapture', function () {
    var btn_recapture = $(this);   

    var analyzepdf_id = $(this).data('analyzepdf_id'),
      invoice_no = $(this).data('invoice_no'),
      tab_name = $(this).data('tab_name');
    
    var selected_analyzepdf_id = $.map($('#navs-analyzepdf-'+ tab_name +' .form-check-input.dt-checkboxes:checked'), function(c){
                                  return c.value; 
                              });
    
    // var selected_analyzepdfs = $.map($('#navs-analyzepdf-'+ tab_name +' .form-check-input.dt-checkboxes:checked'), function(c){                                  
    //                           return $(c).data('invoice_no');       
    //                         });

    analyzepdf_id = (selected_analyzepdf_id.length > 0) ? 0 : analyzepdf_id;
   
    btn_recapture.attr('disabled', 'disabled');
    btn_recapture.addClass('disabled-opacity');    
    btn_recapture.html('<span><i class="bx bx-refresh me-2"></i>Recapturing...</span>');

    $.ajax({      
      url: `${analyzePdfUrl}${analyzepdf_id}/recapture`,
      type: 'GET',     
      //data: form.serialize(),   
      data: 'selected_analyzepdf_id=' + selected_analyzepdf_id,  
      success: function (response) {  
        const progressCard = document.getElementById('batch-progress');
        const bar = document.getElementById('progress-bar');
        const text = document.getElementById('progress-text');

        const data = response;
        const total = parseInt(data.total || 0);

        if (total === 0) {
            text.innerText = "No recapture to process";
            progressCard.classList.add('d-none'); // Keep hidden
            return;
        }
       
        // Only show progress card if there are emails
        progressCard.classList.remove('d-none');
        bar.style.width = '0%';
        bar.innerText = '0%';        
        text.innerText = `Queuing ${total} recapture…`;

        // =========================================
        // REPLACE OLD setInterval BLOCK WITH THIS
        // =========================================

        let recapturePoll = null;
        let pollingStopped = false;

        async function pollProgress() {

            if (pollingStopped) {
                return;
            }

            try {

                const res = await fetch(`/analyzepdf/progress`);
                const progressData = await res.json();

                const completed =
                    progressData.completed || 0;

                const percent = Math.min(
                    100,
                    Math.round((completed / total) * 100)
                );

                console.log("COMPLETED:", completed);

                bar.style.width = percent + '%';
                bar.innerText = percent + '%';

                text.innerText =
                    `${completed} / ${total} recapture processed`;

                if (completed >= total && total > 0) {

                    pollingStopped = true;

                    clearTimeout(recapturePoll);

                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');

                    text.innerText =
                        `All recapture processed`;

                    btn_recapture.removeAttr('disabled');

                    btn_recapture.removeClass(
                        'disabled-opacity'
                    );

                    btn_recapture.html(
                        '<span><i class="bx bx-refresh me-2"></i>Recapture</span>'
                    );

                    console.log("LOADING DONE");

                    var analyzepdf_datas =
                        drawDtTable(progressData, 'analyzepdf');

                    reloadAnalyzedPdf(analyzepdf_datas);

                    return;
                }

                recapturePoll =
                    setTimeout(pollProgress, 3000);

            }
            catch (e) {

                console.log(e);

                recapturePoll =
                    setTimeout(pollProgress, 3000);
            }
        }

        pollProgress();

        /*
        let completed = 0;
console.log("TOTAL--------: " + total);
        // Poll backend for progress every 3 seconds
        const poll = setInterval(async () => {
            const res = await fetch(`/analyzepdf/progress`);
            const progressData = await res.json();

            completed = progressData.completed || 0;
            //const percent = Math.round((completed / total) * 100);
console.log("COMPLETED--------: " + completed);
            const percent = Math.min(100, Math.round((completed / total) * 100));
console.log("PERCENT--------: " + percent);
            bar.style.width = percent + '%';
            bar.innerText = percent + '%';
            text.innerText = `${completed} / ${total} recapture processed`;

            if (completed >= total) {
                clearInterval(poll);
                bar.classList.remove('progress-bar-animated');
                bar.classList.add('bg-success');
                text.innerText = `All recapture processed`;                
                
                btn_recapture.removeAttr('disabled');               
                btn_recapture.removeClass('disabled-opacity');  
                btn_recapture.html('<span><i class="bx bx-refresh me-2"></i>Recapture</span>');
console.log("LOADING--------: " + total);
                var analyzepdf_datas = drawDtTable(progressData, 'analyzepdf');            
                reloadAnalyzedPdf(analyzepdf_datas);
            }
        }, 3000);   
        */            
      },
      error: function (error) {
        console.log(error);
      }
    });

  });

  $(document).on('change', 'th.dt-checkboxes-select-all input.form-check-input', function () {console.log("checkbox change");
      const $td = $(this);
      
      var dt = $td.closest(".table.dataTable");
      checkSelectAll(dt);
  });

  $(document).on('click', '.table.dataTable tbody tr:not(".disabled") td:first-child', function (e) {
    const $td = $(this);
    const $target = $(e.target);
   
    var dt = $td.closest(".table.dataTable");
    checkSelectAll(dt);    
  });

  function checkSelectAll(dt)
  {
    var total_chk = dt.find(".dt-checkboxes");

    var chk_all = dt.find("th.dt-checkboxes-select-all .form-check-input");  
    var remaining_chk = dt.find(".dt-checkboxes:checked");

    if(remaining_chk.length == 0)
    {
      chk_all.removeClass('indeterminate');
      chk_all.prop('checked', false);   

      $(".btn-delete-analyzepdf").attr('disabled', 'disabled');
      $(".btn-delete-analyzepdf").addClass('disabled-opacity');
      
      $(".btn-recapture").attr('disabled', 'disabled');
      $(".btn-recapture").addClass('disabled-opacity');

      $(".btn-validate").attr('disabled', 'disabled');
      $(".btn-validate").addClass('disabled-opacity');
    }
    else
    {
      if(total_chk.length == remaining_chk.length)
      {
        chk_all.removeClass('indeterminate');
        $(".btn-delete-analyzepdf").removeAttr('disabled');        
        $(".btn-delete-analyzepdf").removeClass('disabled-opacity');

        $(".btn-recapture").removeAttr('disabled');        
        $(".btn-recapture").removeClass('disabled-opacity');

        $(".btn-validate").removeAttr('disabled');        
        $(".btn-validate").removeClass('disabled-opacity');
      }
      else
      {  
        chk_all.addClass('indeterminate');      

        $(".btn-delete-analyzepdf").removeAttr('disabled');        
        $(".btn-delete-analyzepdf").removeClass('disabled-opacity');

        $(".btn-recapture").removeAttr('disabled');        
        $(".btn-recapture").removeClass('disabled-opacity');

        $(".btn-validate").removeAttr('disabled');        
        $(".btn-validate").removeClass('disabled-opacity');
      }
    }
  }  

  //Load Dropzone
  window.loadOcrBulkUploadDropzone = function loadOcrBulkUploadDropzone(element)
  {
    const previewTemplate = `<div class="dz-preview dz-file-preview">
    <div class="dz-details">
      <div class="dz-thumbnail">
        <img data-dz-thumbnail>
        <span class="dz-nopreview">No preview</span>
        <div class="dz-success-mark"></div>
        <div class="dz-error-mark"></div>
        <div class="dz-error-message"><span data-dz-errormessage></span></div>
        <div class="progress">
          <div class="progress-bar progress-bar-primary" role="progressbar" data-dz-uploadprogress></div>
        </div>
      </div>
      <div class="dz-filename" data-dz-name></div>
      <div class="dz-size" data-dz-size></div>
    </div>
    </div>`;
    
    var accepted_files = ".pdf";
   
    $(element).dropzone(    
    {
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },        
        parallelUploads:1,        
        uploadMultiple:true,        
        acceptedFiles: accepted_files,        
        timeout: 180000,       
        init : function() {
          
          var myDropzone = this;
          var dropzoneId = myDropzone.element.getAttribute('id');              

          const progressCard = document.getElementById('batch-progress');
          const bar = document.getElementById('progress-bar');
          const text = document.getElementById('progress-text');

          let total = 0;
          let completed = 0;

          myDropzone.on("addedfiles", function (file) {              
            $("#ocr-bulk-upload .card-bulk-upload").hide();
            var total_files = myDropzone.files.length;   
            console.log(total_files);

            $("#bulk_total_uploads").val(total_files);
            
            total = total_files || 0;

            // Only show progress card if there are emails
            progressCard.classList.remove('d-none');
            bar.style.width = '0%';
            bar.innerText = '0%';
            text.innerText = `Queuing ${total} bulk files…`;
          });

          myDropzone.on("complete", function(file) {            
            completed++;
            console.log("Bulk COMPLETED--------: " + completed);

            if (total === 0) {
                text.innerText = "No bulk file to process";
                progressCard.classList.add('d-none'); // Keep hidden
                return;
            }

            // =========================================
            // REPLACE OLD setInterval BLOCK WITH THIS
            // =========================================

            let recapturePoll = null;
            let pollingStopped = false;

            async function pollProgress() {

                if (pollingStopped) {
                    return;
                }

                try {

                    const res = await fetch(`/analyzepdf/progress`);
                    const progressData = await res.json();

                    //const 
                    completed =
                        progressData.completed || 0;

                    const percent = Math.min(
                        100,
                        Math.round((completed / total) * 100)
                    );

                    console.log("BULK COMPLETED:", completed);

                    bar.style.width = percent + '%';
                    bar.innerText = percent + '%';

                    text.innerText =
                        `${completed} / ${total} bulk files processed`;

                    if (completed >= total && total > 0) {

                        pollingStopped = true;

                        clearTimeout(recapturePoll);

                        bar.classList.remove('progress-bar-animated');
                        bar.classList.add('bg-success');

                        text.innerText =
                            `All bulk files are processed`;                        

                        console.log("BULK LOADING DONE");

                        var analyzepdf_datas =
                            drawDtTable(progressData, 'analyzepdf');

                        reloadAnalyzedPdf(analyzepdf_datas);

                        completed = 0;
                        total = 0;

                        myDropzone.removeAllFiles(true);

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        return;
                    }

                    recapturePoll =
                        setTimeout(pollProgress, 3000);

                }
                catch (e) {

                    console.log(e);

                    recapturePoll =
                        setTimeout(pollProgress, 3000);
                }
            }

            pollProgress();

            /*
            $.ajax({      
              url: `${analyzePdfUrl}progress`,
              type: 'GET',                   
              success: function (data) {       
                const progressData = data;

                const percent = Math.min(100, Math.round((completed / total) * 100));
    
                bar.style.width = percent + '%';
                bar.innerText = percent + '%';
                text.innerText = `${completed} / ${total} bulk files processed`;

                if (completed >= total) {             
                  bar.classList.remove('progress-bar-animated');
                  bar.classList.add('bg-success');
                  text.innerText = `All bulk files are processed`;                
                                      
    console.log("Bulk LOADING--------: " + total);
                  var analyzepdf_datas = drawDtTable(progressData, 'analyzepdf');            
                  reloadAnalyzedPdf(analyzepdf_datas);

                  completed = 0;
                  total = 0;

                  myDropzone.removeAllFiles(true);

                  $('html, body').animate({
                      scrollTop: 0
                  }, 500);
                  // Clear preview HTML
                  //document.querySelector("#dropzone-ocr-bulk-upload").innerHTML = "";
                }
              },
              error: function (error) {
                console.log(error);
              }
            });
            */             
          });

          myDropzone.on("successmultiple", function (file, response) {
            console.log("successssssssssssssss");   
          });

          myDropzone.on("error", function (file, errorMessage, xhr) {
            console.log(errorMessage);
            // Swal.fire({
            //   title: 'Error',
            //   text: errorMessage['message'],
            //   icon: 'error',
            //   customClass: {
            //     confirmButton: 'btn btn-success'
            //   }
            // });

            
            //  let response = xhr.response;             
            //  let parseresponse = JSON.parse(response, (key, value)=>{
            //     return value;
            //  });
          });   
        },
        addRemoveLinks: true,
        removedfile: function(file) {                    
         var _ref;
          return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
       }
    });
  }

  loadOcrBulkUploadDropzone($("#dropzone-ocr-bulk-upload"));

  // validate
  $(document).on('click', '.btn-validate', function () {
    var btn_validate = $(this);   

    var analyzepdf_id = $(this).data('analyzepdf_id'),
      invoice_no = $(this).data('invoice_no'),
      tab_name = $(this).data('tab_name');
    
    var selected_analyzepdf_id = $.map($('#navs-analyzepdf-'+ tab_name +' .form-check-input.dt-checkboxes:checked'), function(c){
                                  return c.value; 
                              });
        
    analyzepdf_id = (selected_analyzepdf_id.length > 0) ? 0 : analyzepdf_id;
   
    btn_validate.attr('disabled', 'disabled');
    btn_validate.addClass('disabled-opacity');    
    btn_validate.html('<span><i class="bx bx-refresh me-2"></i>Validating...</span>');

    $.ajax({      
      url: `${analyzePdfUrl}${analyzepdf_id}/validate`,
      type: 'GET',          
      data: 'selected_analyzepdf_id=' + selected_analyzepdf_id,  
      success: function (response) {  
        const progressCard = document.getElementById('batch-progress');
        const bar = document.getElementById('progress-bar');
        const text = document.getElementById('progress-text');

        const data = response;
        const total = parseInt(data.total || 0);

        if (total === 0) {
            text.innerText = "No validate to process";
            progressCard.classList.add('d-none'); // Keep hidden
            return;
        }
       
        // Only show progress card if there are emails
        progressCard.classList.remove('d-none');
        bar.style.width = '0%';
        bar.innerText = '0%';        
        text.innerText = `Queuing ${total} validate…`;
        
        let validatePoll = null;
        let pollingStopped = false;

        async function pollProgress() {

            if (pollingStopped) {
                return;
            }

            try {

                const res = await fetch(`/analyzepdf/progress`);
                const progressData = await res.json();

                const completed =
                    progressData.completed || 0;

                const percent = Math.min(
                    100,
                    Math.round((completed / total) * 100)
                );

                console.log("validate COMPLETED:", completed);

                bar.style.width = percent + '%';
                bar.innerText = percent + '%';

                text.innerText =
                    `${completed} / ${total} validate processed`;

                if (completed >= total && total > 0) {

                    pollingStopped = true;

                    clearTimeout(validatePoll);

                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');

                    text.innerText =
                        `All validate processed`;

                    btn_validate.removeAttr('disabled');

                    btn_validate.removeClass(
                        'disabled-opacity'
                    );

                    btn_validate.html(
                        '<span><i class="bx bx-check me-2"></i>Validate</span>'
                    );

                    console.log("validate LOADING DONE");

                    var analyzepdf_datas =
                        drawDtTable(progressData, 'analyzepdf');

                    reloadAnalyzedPdf(analyzepdf_datas);

                    return;
                }

                validatePoll =
                    setTimeout(pollProgress, 3000);

            }
            catch (e) {

                console.log(e);

                validatePoll =
                    setTimeout(pollProgress, 3000);
            }
        }

        pollProgress();            
      },
      error: function (error) {
        console.log(error);
      }
    });

  });

});
