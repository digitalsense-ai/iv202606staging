/**
 * Page OCR Invoice PDF File List
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
  var ocrInvoicePdfFileUrl = baseUrl + 'analysepdf/';
  var ocrInvoicePdfPostUrl = baseUrl + 'analyze-invoice-pdf/';  

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  
  
  //let dt_ocrinvoicepdf_files = null;
  var dt_ocrinvoicepdffiles_table = $('.datatables-ocrinvoicepdffiles');
  if (dt_ocrinvoicepdffiles_table.length) {
    
    dt_ocrinvoicepdf_files = dt_ocrinvoicepdffiles_table.DataTable({  
        data: ocrinvoicepdffile_datas,              
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
          { data: 'fake_id' },    
          { data: 'invoice_type_name' },    
          { data: 'client_name' },
          { data: 'file_name' },
          { data: 'datetime' },  
          { data: 'status' },          
          { data: 'action' }   
        ],         
        columnDefs: [
          // {
          //   // For Checkboxes
          //   targets: 0,              
          //   searchable: false,
          //   orderable: false,              
          //   // render: function (data, type, full, meta) {                
          //   //   return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'">';
          //   // },
          //   // checkboxes: {
          //   //   selectRow: true,
          //   //   selectAllRender: '<input type="checkbox" class="form-check-input">'
          //   // }
          // },          
          {
            // For Action
            targets: 6,              
            searchable: false,
            orderable: false,              
            render: function (data, type, full, meta) { 

              return `<div class="d-inline-block">
                        <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end m-0">                          
                          <li>
                            <a href="javascript:;" class="dropdown-item btn-show-data" id="show-invoice-ocr-pdf-data" title="Show Data" data-invoice_ocr_pdf_id="`+ full['id'] +`" data-bs-toggle="offcanvas" data-bs-target="#offcanvasOcrInvoicePdfData">
                              <span><i class="bx bx-show me-2"></i>Show Data</span>
                            </a>                                     
                          </li>
                        </ul>
                      </div>`;
            }
          }
        ],
        processing: true, 
        order: [[0, 'asc']],             
        dom:     
          '<"row mx-0 new-search-filter"' +              
          '<"col-md-12"f>' +
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
        /*        
        buttons: [                   
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle ml-3',
          text: '<i class="bx bx-export me-2"></i>Export',
          autoClose: true,
          buttons: [
            {
              extend: 'print',
              title: 'OCR Invoice PDF Files',
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
              title: 'OCR Invoice PDF Files',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },           
            {
              extend: 'excel',
              title: 'OCR Invoice PDF Files',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',                            
              exportOptions: {
                columns: ':visible',      
              }
            },
            {
              extend: 'pdf',
              orientation: 'landscape',
              pageSize: 'LEGAL',
              title: 'OCR Invoice PDF Files',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'OCR Invoice PDF Files',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            }
          ]
        }            
      ],
      */
      initComplete: function (settings, json) {          
                    
        // $(".new-search-filter").appendTo('.dt-search-filter');

        // var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMailboxfileSetting" aria-controls="offcanvasMailboxfileSetting">' +
        //                       '<i class="bx bx-slider"></i>' +
        //                     '</label>';
        // $(sliderfilter).appendTo('.new-search-filter .dataTables_filter');

        // $(".new-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-mailboxfile-export .new-mailboxfile-export');

        // var mailboxfile_total = this.api().data().length;
        // $("#btn-mailboxfile-new span").html(mailboxfile_total);

        //$(".card.mailboxfiles .sk-bounce").hide();
        //$(".card.mailboxfiles .card-datatable").show();           
      }      
    });

  }//DATATABLE

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);     

  // var $repeater = $('.form-salesinvoice-repeater');
  // $repeater.repeater({
  //     show: function () {
  //         $(this).slideDown();
  //     },
  //     hide: function (deleteElement) {
  //         $(this).slideUp(deleteElement);
  //     }
  // });

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

  // edit record
  $(document).on('click', '#show-invoice-ocr-pdf-data', function () {
    var invoice_ocr_pdf_id = $(this).data('invoice_ocr_pdf_id'),    
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
      $(loadertext).insertBefore(".offcanvas-body #addOcrInvoicePdfForm");  
    }     
    $("#addOcrInvoicePdfForm").hide();
    $("#offcanvasOcrInvoicePdfData #docViewer").hide();

    // get data
    var filter_invoice_ocr_pdf_data = ocrinvoicepdffile_datas.filter(function(ocrinvoicepdffile_data) {                                        
        return (ocrinvoicepdffile_data.id === invoice_ocr_pdf_id);
    });
    
    if(filter_invoice_ocr_pdf_data.length > 0)
    {
      var invoice_ocr_pdf_data = filter_invoice_ocr_pdf_data[0];      
      //var parsed_extracted_data = JSON.parse(invoice_ocr_pdf_data.extracted_data);
      let parsed_extracted_data = invoice_ocr_pdf_data.extracted_data
                                ? (typeof invoice_ocr_pdf_data.extracted_data === 'string'
                                    ? JSON.parse(invoice_ocr_pdf_data.extracted_data)
                                    : invoice_ocr_pdf_data.extracted_data)
                                : null;

      var invoice_type = '';
      if(parsed_extracted_data.supplier)
      {
        invoice_type = 'sales';

        $('#invoice_type').val(invoice_type);

        var org_no = parsed_extracted_data.supplier.cvr_number.replace(/\D/g, '');
        $('#client_no').val(org_no);
        $('#client_name').val(parsed_extracted_data.supplier.name);

        $('#credit_note').parent('div').removeClass('d-none');
        $('#net_amount').parent('div').removeClass('d-none');
        $('#vat_rate').parent('div').removeClass('d-none');
        $('#vat_amount').parent('div').removeClass('d-none');

        //var $repeater = $('.form-salesinvoice-repeater');
        $repeater.find('[data-repeater-item]').slice(1).remove();
        $repeater.find('[data-repeater-item]')
          .first()
          .find('.sales-invoice-ref-no')
          .val('');
        $repeater.parent('div').addClass('d-none');

        $('#credit_note').attr('checked', parsed_extracted_data.credit_note);
        $('#net_amount').val(parsed_extracted_data.net_amount);
        $('#vat_rate').val(parsed_extracted_data.vat_rate);
        $('#vat_amount').val(parsed_extracted_data.vat_amount);
        $('#total_amount').val(parsed_extracted_data.total_amount);        
      }
      else if(parsed_extracted_data.recipient)
      {
        invoice_type = 'com';

        $('#invoice_type').val(invoice_type);

        var org_no = parsed_extracted_data.recipient.vat_number.replace(/\D/g, '');
        $('#client_no').val(org_no);
        $('#client_name').val(parsed_extracted_data.recipient.name);

        $('#total_amount').val(parsed_extracted_data.total_amount);

        $('#credit_note').parent('div').addClass('d-none');
        $('#net_amount').parent('div').addClass('d-none');
        $('#vat_rate').parent('div').addClass('d-none');
        $('#vat_amount').parent('div').addClass('d-none');

        

        var sales_invoices = parsed_extracted_data.related_sales_invoices || [];
        // Filter out empty / invalid values first
        sales_invoices = sales_invoices.filter(function (val) {
            return val !== null && val !== undefined && String(val).trim() !== '';
        });

        if (sales_invoices.length === 0) {
            // No values → keep repeater hidden
            return;
        }
        
        $repeater.find('[data-repeater-item]').slice(1).remove();
        $repeater.find('[data-repeater-item]')
          .first()
          .find('.sales-invoice-ref-no')
          .val('');

        $repeater.parent('div').removeClass('d-none');

        // Set first value and create the rest
        $.each(sales_invoices, function (index, sales_invoice) {

            if (index === 0) {
                // First item already exists
                $repeater.parent('div').removeClass('d-none');
                $repeater
                    .find('[data-repeater-item]')
                    .first()
                    .find('.sales-invoice-ref-no')
                    .val(sales_invoice);
            } else {
                // Create new repeater item
                $repeater.find('[data-repeater-create]').click();

                // Set value on the newly created item
                $repeater
                    .find('[data-repeater-item]')
                    .last()
                    .find('.sales-invoice-ref-no')
                    .val(sales_invoice);
            }
        });

      }

      $('#offcanvasOcrInvoicePdfData #docViewer').attr('src', '/storage/ocr/'+ invoice_type +'/' + invoice_ocr_pdf_data.file_name);
      
      $('#invoice_date').val(parsed_extracted_data.invoice_date);
      $('#invoice_no').val(parsed_extracted_data.invoice_number);
      $('#currency').val(parsed_extracted_data.currency);

      $("#offcanvasOcrInvoicePdfData #loader").remove();
      $("#addOcrInvoicePdfForm").show();
      $("#offcanvasOcrInvoicePdfData #docViewer").show();
    }

  });
});
