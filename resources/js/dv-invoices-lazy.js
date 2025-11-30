/**
 * Page Invoices List
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
  var userView = baseUrl + 'dv-user/';
  var invoiceUrl = baseUrl + 'invoices/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  //Switch
  /*
  $(document).on('click', '.switch-input.search', function () {    
    $(".dataTables_filter label:not(.switch)").toggle();      
  });
  */
  var cardInvoiceBlockCustom = $('.btn-card-block-custom'),
      cardInvoiceSection = $('#card-invoice-block');

  // Custom Message
  //if (cardInvoiceBlockCustom.length && cardInvoiceSection.length) {
    //cardInvoiceBlockCustom.on('click', function () {
    //$(window).on("load", function(){
  if($("#invoice_refresh").val() == "1") 
  {     
      cardInvoiceSection.block({
        message:
          '<div class="d-flex justify-content-center flex-column align-items-center">' +
            '<p class="mb-0">' +
              'Something went wrong. Please click <a href="javascript:;" class="btn-refresh bg-label-danger" data-vat_reg_id="'+ $("#vat_reg_id").val() +'" title="Refresh">Refresh</a> to fetch the invoices' +
            '</p>' +
            '<div class="sk-wave m-0" style="display: none;">' +
              '<div class="sk-rect sk-wave-rect"></div> ' +
              '<div class="sk-rect sk-wave-rect"></div> ' +
              '<div class="sk-rect sk-wave-rect"></div> ' +
              '<div class="sk-rect sk-wave-rect"></div> ' +
              '<div class="sk-rect sk-wave-rect"></div>' +
            '</div>' +
            
          '</div>',
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
    //});
  }
  
  let refreshTimer = setInterval(() => {
      fetch('/invoices/'+ $('#vat_reg_id').val() +'/current')
          .then(res => res.json())
          .then(result => {console.log(result);
              const currentCount = result.count;
              //const newData = result.data;

              // Update table
              var invoice_datas = drawDtTable(result, 'invoice');

              invoice_correct_datas = invoice_datas['invoice_correct_datas'];
              dt_correct_invoices.clear().rows.add(invoice_correct_datas).draw();
              $("#btn-invoice-correct span").html(invoice_correct_datas.length);
            
              invoice_wrong_datas = invoice_datas['invoice_wrong_datas']; 
              dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();
              $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);
            
              invoice_managed_datas = invoice_datas['invoice_managed_datas'];
              dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw(); 
              $("#btn-invoice-managed span").html(invoice_managed_datas.length);
              
              // Update progress text
              // document.getElementById('invoice-count').textContent =
              //     `Inserted ${currentCount}/${result.totalExpected}`;

              $('#invoice-loading').removeClass('d-none');

              // Stop refreshing once all inserted
              if (currentCount >= result.totalExpected) {
                  clearInterval(refreshTimer);
                  console.log('✅ All invoices inserted. Auto-refresh stopped.');

                  $('#invoice-loading').addClass('d-none');
              }
          });
  }, 30000);


  // Refresh
  $(document).on('click', '.btn-refresh', function () {
    var vat_reg_id = $(this).data('vat_reg_id');

    var btn_refresh = $(this);
    btn_refresh.attr('disabled', 'disabled');
    // btn_refresh.html(
    //   //'<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
    //     'Refreshing...');
    $(".blockUI p").html('Will take time to fetch invoices. Please be patient. <span class="bg-label-danger">Refreshing...</span>');

    $(".blockUI .sk-wave").show();

    //loadOverviewTabLazy(vat_reg_id, true);
    refreshInvoices(vat_reg_id);
  });

  window.refreshInvoices = function refreshInvoices(vat_reg_id)
  {      
    console.log("start Time : " + vat_reg_id + " -- " + moment().format("DD-MM-YYYY h:m:s A"));    
    
    $.ajax({              
      url: `${invoiceUrl}${vat_reg_id}/refresh`,      
      type: 'GET',
      success: function (result) {   
        
        console.log("dfdssfdsfdsf");

        setTimeout(function() {
            location.reload();
        }, 10000);

        console.log("END Time : " + vat_reg_id + " -- " + moment().format("DD-MM-YYYY h:m:s A"));  

                
      },
      error: function (err) {
        console.log(err);        
      }
    });
  }

  var dt_correct_invoices_table = $('.datatables-correct-invoices');
  if (dt_correct_invoices_table.length) {
    
    var dt_correct_invoices = dt_correct_invoices_table.DataTable({  
        data: invoice_correct_datas,              
        scrollCollapse: false,
        // scroller: true,
        scrollX: true,
        // scrollY: '60vh',
        fixedHeader: true,        
        searching: true,
        // lengthMenu: [
        //     [10, 25, 50, 100, -1],
        //     [10, 25, 50, 100, 'All']
        // ],
        // pageLength: -1, 
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        pageLength: 100,     
        autoWidth: false,                 
        columns: [           
          { data: 'id' },
          { data: 'fake_id' },
          { data: 'invoice_type' },       
          { data: 'tax_code' },
          { data: 'invoice_date' },
          { data: 'acc_no' },    
          { data: 'invoice_no' },
          { data: 'currency_code' },
          { data: 'total_net', className: "text-end" },   
          { data: 'vat_rate', className: "text-end" },   
          { data: 'total_vat', className: "text-end" },     
          { data: 'total_gross', className: "text-end" },
          { data: 'local_currency_code' },
          { data: 'exchange_rate', className: "text-end" },
          { data: 'local_total_net', className: "text-end" },
          { data: 'local_total_vat', className: "text-end" },
          { data: 'local_total_gross', className: "text-end" },
          { data: 'n' },
          { data: 'o' },
          { data: 'p' },
          { data: 'q' },
          { data: 'c_name' },
          { data: 'c_vat_no' },
          { data: 'c_street' },
          { data: 'c_house_no' },
          { data: 'c_city' },
          { data: 'c_postcode' },
          { data: 'c_country' },
          { data: 'pdf' },
          { data: 'disregard_invoice', visible: false },
          { data: 'disregard_comment', visible: false }
        ],   
        createdRow: function(row, data, dataIndex) {          
          if (data['disregard_invoice'] == 1)
          {      
            $(row).addClass("disabled");
            $(row).attr('data-disregard_invoice', data['disregard_invoice']);
            
            $('td', row).each(function(index) {               
                $(this).attr('data-bs-toggle', 'tooltip');
                $(this).attr('data-bs-offset', '0,4');
                $(this).attr('data-bs-placement', 'top');
                $(this).attr('data-bs-html', 'true');
                $(this).attr('title', data['disregard_comment']);
            });
          }
        },      
        columnDefs: [
            {
              // For Checkboxes
              targets: 0,              
              searchable: false,
              orderable: false,              
              render: function (data, type, full, meta) {
                if(full.disregard_invoice)                
                  return '';
                else
                  return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'" data-invoice_no="'+ full.invoice_no +'" data-invoice_date="'+ full.invoice_date +'" data-local_currency_code="'+ full.local_currency_code +'">';
              },
              checkboxes: {
                selectRow: true,
                selectAllRender: '<input type="checkbox" class="form-check-input">'
              }
            },            
            {
                target: 2,
                visible: false
            },
    //         {
    //             target: 7,
    //             render: function (data, type, full, meta) {
    //               var total_net = full['total_net'];
    //               var invoice_currency = (full['local_currency_code'] == null || full['local_currency_code'] == "") ? full['currency_code'] : full['local_currency_code'];
                  
    //               var currency_locale = 'en-US';
    //               var currency_style = invoice_currency;
    //               if(invoice_currency == "DKK" || invoice_currency == "NOK")           
    //                   currency_locale = 'da-DK';                 
    //               else if(invoice_currency == "SEK")       
    //                   currency_locale = 'sv-SE';      
    //               else if(invoice_currency == "GBP")     
    //                   currency_locale = 'en-GB';      
    //               else if(invoice_currency == "INR")        
    //                   currency_locale = 'en-IN';
    //               else if(invoice_currency == "EUR")        
    //                   currency_locale = 'fr-FR';
    //               else if(invoice_currency == "CHF")        
    //                   currency_locale = 'fr-FR';  

    //               var total_net_format = new Intl.NumberFormat(currency_locale, {
    // style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2}).format(total_net);

    //               return total_net_format;
    //             }
    //         }      
        ],
        processing: true, 
        order: [[1, 'desc']],       
        // dom:     
        //   '<"row mx-0 correct-search-filter"' +    
        //   '<"col-md-3"<"me-3"l>>' +      
        //   '<"col-md-9"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        //   '>r' +
        //   '<"row mx-0"' +
        //   '<"col-sm-12 p-0 custom-tab"t' +                    
        //   '>>' +
        //   '<"row mx-2"' +
        //   '<"col-sm-12 col-md-6"i>' +
        //   '<"col-sm-12 col-md-6"p>' +
        //   '>',
        dom:     
          '<"row mx-0 correct-search-filter"' +              
          //'<"col-md-12"lfB>' +
          '<"col-sm-12 col-md-6 sub-btns text-start my-auto">' +
          '<"col-sm-12 col-md-6"lfB>' +
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
              title: 'Invoices',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible',
                // prevent avatar to be print
                // format: {
                //   body: function (inner, coldex, rowdex) {
                //     if (inner.length <= 0) return inner;
                //     var el = $.parseHTML(inner);
                //     var result = '';
                //     $.each(el, function (index, item) {
                //       if (item.classList !== undefined && item.classList.contains('client-name')) {
                //         result = result + item.lastChild.textContent;
                //       } else result = result + item.innerText;
                //     });
                //     return result;
                //   }
                // }
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
              title: 'Invoices',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            // {
            //   extend: 'excel',
            //   title: 'Invoices',
            //   text: '<i class="bx bxs-file-export me-2"></i>Excel',
            //   className: 'dropdown-item',                
            //   exportOptions: {               
            //     columns: ':visible'
            //   }
            // },
            {
              extend: 'excel',
              title: 'Invoices',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',
              // customize: function(xlsx) {
              //     var sheet = xlsx.xl.worksheets['sheet1.xml'];
                  
              //     // Set the format of all cells to text
              //     //$('row c', sheet).attr('s', '2'); // '2' typically corresponds to Text format in Excel
              //     $('row c', sheet).attr('s', '65'); // '2' typically corresponds to Text format in Excel
              //     // // Apply general format to all cells
              //     // $(sheet).find('row c[r*="G"]').each(function() {
              //     //   //console.log($(this));
              //     //   //console.log($(this).text() + " -- " + $(this).attr('s') + " == " + $(this).text().replace(/[^\d.,-]/g, ''));
              //     //     // Apply general format style (style index 0 is general)
              //     //     //$(this).attr('s', '65'); // Set the cell style to general
              //     //     //var cellvalue = $(this).text().replace(/[^\d.,-]/g, '');
              //     //     //$(this).text(cellvalue);
              //     //     $(this).attr('s', '2'); // Set the cell style to TEXT
              //     //     console.log($(this).text() + " -- " + $(this).attr('s'));
              //     // });
              //     // // // Use the following line to format cells with prices
              //     // // $(sheet).find('row g[r*="2"]') // Modify as needed to target specific cells
              //     // //     .attr('s', '22'); // '22' is the style index for currency formatting in Excel
              // },
              action: exportToExcelCorrectInvoices, 
              exportOptions: {
                columns: ':visible',
      //           format: {
      //               body: function (data, row, column, node) {
      //                 var currency_code = $("#currency_code").val();
      //                 console.log(typeof data + ' -- ' + column + ' -- ' + row + ' -- ' + data);
      //                   // Check if the data is in the format you want to preserve
      //                   if (typeof data === 'string') {
                          
      //                     if(column == 5)
      //                       currency_code = data;
      //                       // If the string contains a comma and a period, replace accordingly
      //                       // Ensure it keeps the original format
      //                       if(column == 6 || column == 8 || column == 9 || 
      //                         column == 11 || column == 12 || column == 13 || column == 14)
      //                       {
      //                         console.log(currency_code);
      //                         if(currency_code == 'DKK' || currency_code == 'NOK')
      //                         {
      //                           // Normalize the string to a number (replace period and comma)
      //                             let number = parseFloat(data.replace('.', '').replace(',', '.'));

      //                         var amount_format = new Intl.NumberFormat('en-US', {
      // style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2}).format(number);
      //                         console.log(amount_format);
      //                         return amount_format;
      //                         }
      //                         else
      //                           return data;
      //                       }
      //                       else
      //                         return data;//.replace(/\./g, ''); // Remove thousand separators
      //                   }
      //                   return data; // Return original data if no changes are needed
      //               }
      //           }
                // format: {
                //     body: function(data, row, column, node) {
                //       // // if(String(data).indexOf('.') != -1 && data.indexOf(',') != -1)
                //       // //   // Convert to string to preserve the format
                //       // //   return data;//return String(data);//.replace('.', ',');
                //       // // else if(String(data).indexOf(',') != -1)
                //       // //   return String(data).replace(',', '.');
                //       // // else if(String(data).indexOf('.') != -1)
                //       // //   return String(data).replace('.', ',');
                //       // // else
                //       // //   return data;
                //       // console.log(column);
                //       // console.log(String(data));
                //       // return String(data).replace(/[^\d.,]/g, '');

                //       // Convert to string and format for DKK locale
                //       if (typeof data === 'number') {
                //           // Format number for DKK locale
                //           return data.toLocaleString('da-DK', { 
                //               minimumFractionDigits: 2, 
                //               maximumFractionDigits: 2 
                //           });
                //       }
                //       // If it's a string, return as is
                //       return String(data);
                //     }
                // }
              }
            },
            {
              extend: 'pdf',
              orientation: 'landscape',
              pageSize: 'LEGAL',
              title: 'Invoices',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Invoices',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            },
            {
                extend: 'excelHtml5',
                title: 'SAF-T',
                text: '<i class="bx bx-file me-2" ></i>SAF-T',
                action: saftexportaction                    
            }
            
          ]
        }            
      ],
      initComplete: function (settings, json) {   
       
        // Enable TFOOT scoll bars
        $('#navs-invoice-correct .dataTables_scrollBody').css('overflow', 'auto');
        
        $('#navs-invoice-correct .dataTables_scrollHead').css('overflow', 'auto');
        
        //var lastScrollLeft = 0;
        // Sync TFOOT scrolling with TBODY
        $('#navs-invoice-correct .dataTables_scrollBody').on('scroll', function () {
          //console.log("correct bottom scroll");
            // var documentScrollLeft = $(document).scrollLeft();
            // if (lastScrollLeft != documentScrollLeft)
            // {
            //   lastScrollLeft = documentScrollLeft;
              $('#navs-invoice-correct .dataTables_scrollHead').scrollLeft($(this).scrollLeft());
            //}
        });      
        
        $('#navs-invoice-correct .dataTables_scrollHead').on('scroll', function () {
          //console.log("correct top scroll");
            // var documentScrollLeft = $(document).scrollLeft();
            // if (lastScrollLeft != documentScrollLeft)
            // {
            //   lastScrollLeft = documentScrollLeft;
              $('#navs-invoice-correct .dataTables_scrollBody').scrollLeft($(this).scrollLeft());
            //}
        });

        this.api()       
        .columns(2)
        .every(function () {
          var column = this;
         
          var select = $(                  
          '<div class="form-check form-switch m-0">' +
          '<input class="form-check-input" type="checkbox" id="flexSwitchCheckCorrectInvoiceType">' +
          '<label class="form-check-label" for="flexSwitchCheckCorrectInvoiceType" id="flexSwitchCheckCorrectInvoiceTypeLabel">Sales + Purchase</label>' +
          '</div>'
          )
          .appendTo('.dt-invoice-type .correct-invoice-type')
          .on('change', function () {   
            
            var val = $("#flexSwitchCheckCorrectInvoiceType").prop('checked') ? 'Sales' : 'Purchase';
            $("#flexSwitchCheckCorrectInvoiceTypeLabel").text(val);

            var search_val = (val == 'Sales') ? 'sale' : 'purchase';
            column.search(search_val ? search_val : '', true, false).draw();             
          });
        }); 
                
        $(".correct-search-filter").appendTo('.dt-search-filter');

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceColumnSetting" aria-controls="offcanvasInvoiceColumnSetting">' +
                              '<i class="bx bx-columns"></i>' +
                            '</label>' + 

                            '<label class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceFilter" aria-controls="offcanvasInvoiceFilter">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.correct-search-filter .dataTables_filter');

        var btn_disregard_invoice =  '<button type="button" id="btn_correct_disregard_invoice" title="Disregard Invoice" class="btn-disregard-invoice badge rounded-pill bg-label-secondary border-0 text-capitalize" disabled="disabled" data-is_disregard="1" data-tab_name="correct">' +                                     
                                      '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>' +
                                    '</button>';
        $(btn_disregard_invoice).appendTo('.correct-search-filter .sub-btns');

        $(".correct-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-invoice-export .correct-invoice-export');

        var invoice_total = this.api().data().length;
        $("#btn-invoice-correct span").html(invoice_total);

        $(".card.invoices .sk-bounce").hide();
        $(".card.invoices .card-datatable").show();           
      },  
      rowCallback: function( row, data, index ) {
        if(data.local_currency_code != "")
          $('td:eq(0)', row).addClass("alert-warning");
      }   
    });

  }//DATATABLE

  var dt_wrong_invoices_table = $('.datatables-wrong-invoices');
  if (dt_wrong_invoices_table.length) {
    
    var dt_wrong_invoices = dt_wrong_invoices_table.DataTable({  
        data: invoice_wrong_datas,              
        scrollCollapse: false,
        // scroller: true,
        scrollX: true,
        // scrollY: '60vh',   
        fixedHeader: true,     
        searching: true,
        // lengthMenu: [
        //     [10, 25, 50, 100, -1],
        //     [10, 25, 50, 100, 'All']
        // ],
        // pageLength: -1,      
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],
        pageLength: 100,
        autoWidth: false,                 
        columns: [           
          { data: 'id' },
          { data: 'fake_id' },
          { data: 'invoice_type' },       
          { data: 'tax_code' },
          { data: 'invoice_date' },
          { data: 'acc_no' },                            
          { data: 'invoice_no' },
          { data: 'currency_code' },
          { data: 'total_net', className: "text-end" },   
          { data: 'vat_rate', className: "text-end" },   
          { data: 'total_vat', className: "text-end" },     
          { data: 'total_gross', className: "text-end" },
          { data: 'local_currency_code' },
          { data: 'exchange_rate', className: "text-end" },
          { data: 'local_total_net', className: "text-end" },
          { data: 'local_total_vat', className: "text-end" },
          { data: 'local_total_gross', className: "text-end" },
          { data: 'n' },
          { data: 'o' },
          { data: 'p' },
          { data: 'q' },
          { data: 'c_name' },
          { data: 'c_vat_no' },
          { data: 'c_street' },
          { data: 'c_house_no' },
          { data: 'c_city' },
          { data: 'c_postcode' },
          { data: 'c_country' },
          { data: 'pdf' },
          { data: 'from_currency' },
          { data: 'disregard_invoice', visible: false },
          { data: 'disregard_comment', visible: false }
        ],   
        createdRow: function(row, data, dataIndex) {          
          if (data['disregard_invoice'] == 1)
          {      
            $(row).addClass("disabled");
            $(row).attr('data-disregard_invoice', data['disregard_invoice']);
            
            $('td', row).each(function(index) {               
                $(this).attr('data-bs-toggle', 'tooltip');
                $(this).attr('data-bs-offset', '0,4');
                $(this).attr('data-bs-placement', 'top');
                $(this).attr('data-bs-html', 'true');
                $(this).attr('title', data['disregard_comment']);
            });
          }
        },    
        columnDefs: [
            {
              // For Checkboxes
              targets: 0,              
              searchable: false,
              orderable: false,
              render: function (data, type, full, meta) {
                if(full.disregard_invoice)                
                  return '';
                else
                  return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'" data-invoice_no="'+ full.invoice_no +'" data-invoice_date="'+ full.invoice_date +'" data-from_currency="'+ full.from_currency +'">';
              },
              checkboxes: {
                selectRow: true,
                selectAllRender: '<input type="checkbox" class="form-check-input">'
              }
            },            
            {
                target: 2,
                visible: false
            },
            {
                target: 26,//25,
                visible: false
            }         
        ],
        processing: true, 
        order: [[1, 'desc']],       
        // dom:     
        //   '<"row mx-0 wrong-search-filter d-none"' +    
        //   '<"col-md-3"<"me-3"l>>' +      
        //   '<"col-md-9"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        //   '>r' +
        //   '<"row mx-0"' +
        //   '<"col-sm-12 p-0 custom-tab"t' +                    
        //   '>>' +
        //   '<"row mx-2"' +
        //   '<"col-sm-12 col-md-6"i>' +
        //   '<"col-sm-12 col-md-6"p>' +
        //   '>',
        dom:     
          '<"row mx-0 wrong-search-filter d-none"' +                        
          //'<"col-md-2 d-flex align-items-center selectedInvoiceCount">' +
          //'<"col-md-10"lfB>' +
          '<"col-sm-12 col-md-2 sub-btns text-start my-auto">' +
          '<"col-sm-12 col-md-2 d-flex align-items-center selectedInvoiceCount">' +          
          '<"col-sm-12 col-md-8"lfB>' +

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
              title: 'Invoices',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible',
                // // prevent avatar to be print
                // format: {
                //   body: function (inner, coldex, rowdex) {
                //     if (inner.length <= 0) return inner;
                //     var el = $.parseHTML(inner);
                //     var result = '';
                //     $.each(el, function (index, item) {
                //       if (item.classList !== undefined && item.classList.contains('client-name')) {
                //         result = result + item.lastChild.textContent;
                //       } else result = result + item.innerText;
                //     });
                //     return result;
                //   }
                // }
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
              title: 'Invoices',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'excel',
              title: 'Invoices',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',    
              action: exportToExcelWrongInvoices,             
              exportOptions: {               
                columns: ':visible'
              }
            },
            {
              extend: 'pdf',
              orientation: 'landscape',
              pageSize: 'LEGAL',
              title: 'Invoices',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Invoices',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            },
            {
                extend: 'excelHtml5',
                title: 'SAF-T',
                text: '<i class="bx bx-file me-2" ></i>SAF-T',
                action: saftexportaction                    
            }
            
          ]
        }            
      ],
      initComplete: function (settings, json) {   
        // Enable TFOOT scoll bars
        $('#navs-invoice-wrong .dataTables_scrollBody').css('overflow', 'auto');
        
        $('#navs-invoice-wrong .dataTables_scrollHead').css('overflow', 'auto');
        
        //var lastScrollLeft = 0;
        // Sync TFOOT scrolling with TBODY
        $('#navs-invoice-wrong .dataTables_scrollBody').on('scroll', function () {
          //console.log("wrong bottom scroll");
            // var documentScrollLeft = $(document).scrollLeft();
            // if (lastScrollLeft != documentScrollLeft)
            // {
            //   lastScrollLeft = documentScrollLeft;
              $('#navs-invoice-wrong .dataTables_scrollHead').scrollLeft($(this).scrollLeft());
            //}
        });      
        
        $('#navs-invoice-wrong .dataTables_scrollHead').on('scroll', function () {
          //console.log("wrong top scroll");
            // var documentScrollLeft = $(document).scrollLeft();
            // if (lastScrollLeft != documentScrollLeft)
            // {
            //   lastScrollLeft = documentScrollLeft;
              $('#navs-invoice-wrong .dataTables_scrollBody').scrollLeft($(this).scrollLeft());
            //}
        });

        this.api()       
        .columns(2)
        .every(function () {
          var column = this;
         
          var select = $(                  
          '<div class="form-check form-switch m-0">' +
          '<input class="form-check-input" type="checkbox" id="flexSwitchCheckWrongInvoiceType">' +
          '<label class="form-check-label" for="flexSwitchCheckWrongInvoiceType" id="flexSwitchCheckWrongInvoiceTypeLabel">Sales + Purchase</label>' +
          '</div>'
          )
          .appendTo('.dt-invoice-type .wrong-invoice-type')
          .on('change', function () {   
            
            var val = $("#flexSwitchCheckWrongInvoiceType").prop('checked') ? 'Sales' : 'Purchase';
            $("#flexSwitchCheckWrongInvoiceTypeLabel").text(val);

            var search_val = (val == 'Sales') ? 'sale' : 'purchase';
            column.search(search_val ? search_val : '', true, false).draw();             
          });
        }); 
                       
        $(".wrong-search-filter").appendTo('.dt-search-filter');

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceColumnSetting" aria-controls="offcanvasInvoiceColumnSetting">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>' +

                            '<label class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceFilter" aria-controls="offcanvasInvoiceFilter">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.wrong-search-filter .dataTables_filter');

        var btn_disregard_invoice =  '<button type="button" id="btn_wrong_disregard_invoice" title="Disregard Invoice" class="btn-disregard-invoice badge rounded-pill bg-label-secondary border-0 text-capitalize" disabled="disabled" data-is_disregard="1" data-tab_name="wrong">' +                                     
                                      '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>' +
                                    '</button>';
        $(btn_disregard_invoice).appendTo('.wrong-search-filter .sub-btns');

        $(".wrong-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-invoice-export .wrong-invoice-export');

        var invoice_total = this.api().data().length;
        $("#btn-invoice-wrong span").html(invoice_total);
       
        $(".card.invoices .sk-bounce").hide();
        $(".card.invoices .card-datatable").show();           
      },      
    });

  }//DATATABLE

  var dt_managed_invoices_table = $('.datatables-managed-invoices');
  if (dt_managed_invoices_table.length) {
    
    var dt_managed_invoices = dt_managed_invoices_table.DataTable({  
        data: invoice_managed_datas,              
        scrollCollapse: false,      
        scrollX: true,       
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
          { data: 'fake_id' },
          { data: 'invoice_type' },       
          { data: 'tax_code' },
          { data: 'invoice_date' },
          { data: 'acc_no' },                            
          { data: 'invoice_no' },
          { data: 'currency_code' },
          { data: 'total_net', className: "text-end" },   
          { data: 'vat_rate', className: "text-end" },   
          { data: 'total_vat', className: "text-end" },     
          { data: 'total_gross', className: "text-end" },
          { data: 'local_currency_code' },
          { data: 'exchange_rate', className: "text-end" },
          { data: 'local_total_net', className: "text-end" },
          { data: 'local_total_vat', className: "text-end" },
          { data: 'local_total_gross', className: "text-end" },
          { data: 'n' },
          { data: 'o' },
          { data: 'p' },
          { data: 'q' },
          { data: 'c_name' },
          { data: 'c_vat_no' },
          { data: 'c_street' },
          { data: 'c_house_no' },
          { data: 'c_city' },
          { data: 'c_postcode' },
          { data: 'c_country' },
          { data: 'pdf' },
          { data: 'from_currency' },
          { data: 'disregard_invoice', visible: false },
          { data: 'disregard_comment', visible: false }
        ],   
        createdRow: function(row, data, dataIndex) {          
          if (data['disregard_invoice'] == 1)
          {      
            $(row).addClass("disabled");
            $(row).attr('data-disregard_invoice', data['disregard_invoice']);
            
            $('td', row).each(function(index) {               
                $(this).attr('data-bs-toggle', 'tooltip');
                $(this).attr('data-bs-offset', '0,4');
                $(this).attr('data-bs-placement', 'top');
                $(this).attr('data-bs-html', 'true');
                $(this).attr('title', data['disregard_comment']);
            });
          }
        },    
        columnDefs: [
            {
              // For Checkboxes
              targets: 0,              
              searchable: false,
              orderable: false,
              render: function (data, type, full, meta) {
                if(full.disregard_invoice)                
                  return '';
                else
                  return '<input type="checkbox" class="dt-checkboxes form-check-input" value="'+ full.id +'" data-invoice_no="'+ full.invoice_no +'" data-invoice_date="'+ full.invoice_date +'">';
              },
              checkboxes: {
                selectRow: true,
                selectAllRender: '<input type="checkbox" class="form-check-input">'
              }
            },            
            {
                target: 2,
                visible: false
            },
            {
                target: 26,//25,
                visible: false
            }         
        ],
        processing: true, 
        order: [[1, 'desc']],              
        dom:     
          '<"row mx-0 managed-search-filter d-none"' +                        
          //'<"col-md-2 d-flex align-items-center selectedInvoiceCount">' +
          //'<"col-md-10"lfB>' +
          '<"col-sm-12 col-md-2 sub-btns text-start my-auto">' +
          '<"col-sm-12 col-md-2 d-flex align-items-center selectedInvoiceCount">' +
          '<"col-sm-12 col-md-8"lfB>' +
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
              title: 'Invoices',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible',
                // // prevent avatar to be print
                // format: {
                //   body: function (inner, coldex, rowdex) {
                //     if (inner.length <= 0) return inner;
                //     var el = $.parseHTML(inner);
                //     var result = '';
                //     $.each(el, function (index, item) {
                //       if (item.classList !== undefined && item.classList.contains('client-name')) {
                //         result = result + item.lastChild.textContent;
                //       } else result = result + item.innerText;
                //     });
                //     return result;
                //   }
                // }
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
              title: 'Invoices',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'excel',
              title: 'Invoices',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',   
              action: exportToExcelManagedInvoices,              
              exportOptions: {               
                columns: ':visible'
              }
            },
            {
              extend: 'pdf',
              title: 'Invoices',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Invoices',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {               
                columns: ':visible'
              }
            },
            {
                extend: 'excelHtml5',
                title: 'SAF-T',
                text: '<i class="bx bx-file me-2" ></i>SAF-T',
                action: saftexportaction                    
            }
            
          ]
        }            
      ],
      initComplete: function (settings, json) {   
        // Enable TFOOT scoll bars
        $('#navs-invoice-managed .dataTables_scrollBody').css('overflow', 'auto');
        
        $('#navs-invoice-managed .dataTables_scrollHead').css('overflow', 'auto');
           
        // Sync TFOOT scrolling with TBODY
        $('#navs-invoice-managed .dataTables_scrollBody').on('scroll', function () {
          $('#navs-invoice-managed .dataTables_scrollHead').scrollLeft($(this).scrollLeft());         
        });      
        
        $('#navs-invoice-managed .dataTables_scrollHead').on('scroll', function () {          
          $('#navs-invoice-managed .dataTables_scrollBody').scrollLeft($(this).scrollLeft());          
        });

        this.api()       
        .columns(2)
        .every(function () {
          var column = this;
         
          var select = $(                  
          '<div class="form-check form-switch m-0">' +
          '<input class="form-check-input" type="checkbox" id="flexSwitchCheckManagedInvoiceType">' +
          '<label class="form-check-label" for="flexSwitchCheckManagedInvoiceType" id="flexSwitchCheckManagedInvoiceTypeLabel">Sales + Purchase</label>' +
          '</div>'
          )
          .appendTo('.dt-invoice-type .managed-invoice-type')
          .on('change', function () {   
            
            var val = $("#flexSwitchCheckManagedInvoiceType").prop('checked') ? 'Sales' : 'Purchase';
            $("#flexSwitchCheckManagedInvoiceTypeLabel").text(val);

            var search_val = (val == 'Sales') ? 'sale' : 'purchase';
            column.search(search_val ? search_val : '', true, false).draw();             
          });
        }); 
                       
        $(".managed-search-filter").appendTo('.dt-search-filter');

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceColumnSetting" aria-controls="offcanvasInvoiceColumnSetting">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>' + 

                            '<label class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceFilter" aria-controls="offcanvasInvoiceFilter">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.managed-search-filter .dataTables_filter');

        var btn_disregard_invoice =  '<button type="button" id="btn_managed_disregard_invoice" title="Disregard Invoice" class="btn-disregard-invoice badge rounded-pill bg-label-secondary border-0 text-capitalize" disabled="disabled" data-is_disregard="1" data-tab_name="managed">' +                                     
                                      '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>' +
                                    '</button>';
        $(btn_disregard_invoice).appendTo('.managed-search-filter .sub-btns');

        $(".managed-search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-invoice-export .managed-invoice-export');

        var invoice_total = this.api().data().length;
        $("#btn-invoice-managed span").html(invoice_total);
       
        $(".card.invoices .sk-bounce").hide();
        $(".card.invoices .card-datatable").show();           
      },      
    });

  }//DATATABLE

  //Filter    
  $(document).on('click', '#chk-invoice-filter-show-disregarded-invoices', function() { 
    var which_tab = $('li.nav-item .nav-link.active').attr('id').replace('btn-invoice-', '');

    filterShowDisregardedInvoices(which_tab);
  });

  function filterShowDisregardedInvoices(which_tab)
  {    
    if($('#chk-invoice-filter-show-disregarded-invoices').prop('checked'))       
      $('.datatables-'+ which_tab +'-invoices tbody tr[data-disregard_invoice="1"]').removeClass('hidden-disregard-row');      
    else      
      $('.datatables-'+ which_tab +'-invoices tbody tr[data-disregard_invoice="1"]').addClass('hidden-disregard-row');      
  }

  $("table.table.dataTable").each(function () {
    var which_tab = $(this).closest('.tab-pane').attr('id').replace('navs-invoice-', '');

    filterShowDisregardedInvoices(which_tab);
  });

  function reInitializeTooltips()  
  {
    // Initialize tooltips for all elements with the data-bs-toggle attribute
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

    if (tooltipTriggerList.length > 0) 
    {
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
          var tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl); // Get the tooltip instance
          if (tooltip) {
              tooltip.dispose(); // Destroy the tooltip
          }
      });
      
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {      
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });    
    }
  }

  reInitializeTooltips();

  //Checkbox Select    
  // $(document).on('click', 'th.dt-checkboxes-select-all', function() {
  //   var chk_all = $(this).find(".form-check-input");
  //   var dt = $(this).closest(".table.dataTable");
  //   console.log(dt);
  //   console.log(chk_all.prop('checked'));
  //   dt.find(".dt-checkboxes").prop('checked', chk_all.prop('checked')); 
    
  //   // var table = $(this).closest("table"); 
  //   var rows = dt.find('tbody tr');    
  //   if(chk_all.prop('checked'))   
  //     rows.addClass('selected');      
  //   else    
  //     rows.removeClass('selected');          

  //   checkSelectAll(dt);
  // });

  $(document).on('click', '.table.dataTable tbody tr:not(".disabled") td:first-child', function (e) {
    const $td = $(this);
    const $target = $(e.target);
   
    var dt = $td.closest(".table.dataTable");console.log(dt);
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

      $(".btn-disregard-invoice").attr('disabled', 'disabled');
      $(".btn-disregard-invoice").addClass('bg-label-secondary');
      $(".btn-disregard-invoice").removeClass('bg-label-primary');
    }
    else
    {
      if(total_chk.length == remaining_chk.length)
      {
        chk_all.removeClass('indeterminate');
        $(".btn-disregard-invoice").removeAttr('disabled');
        $(".btn-disregard-invoice").addClass('bg-label-primary');
        $(".btn-disregard-invoice").removeClass('bg-label-secondary');
      }
      else
      {  
        chk_all.addClass('indeterminate');      

        $(".btn-disregard-invoice").removeAttr('disabled');
        $(".btn-disregard-invoice").addClass('bg-label-primary');
        $(".btn-disregard-invoice").removeClass('bg-label-secondary');
      }
    }
  }

  // Disregard invoice
  $(document).on('click', '.btn-disregard-invoice', function () {    
    var btn_disregard_invoice = $(this);
    var data = btn_disregard_invoice.data();

    var disregard_invoice_text = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'disregard' : 'enable';
    var disregard_invoice_suffix = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'ed' : 'd';
    var disregard_invoice_text_capitalize = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'Disregard' : 'Enable';
    var disregard_invoice_text_after = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'Enable' : 'Disregard';
    var disregard_invoice_text_loading = (btn_disregard_invoice.attr('title') == 'Disregard Invoice') ? 'Disregarding' : 'Enabling';
   
    var vat_reg_id = $("#vat_reg_id").val();    
    
    var selected_invoices_id = $.map($('#navs-invoice-'+ data['tab_name'] +' .form-check-input.dt-checkboxes:checked'), function(c){
                                  return c.value; 
                              });
    var selected_invoices = $.map($('#navs-invoice-'+ data['tab_name'] +' .form-check-input.dt-checkboxes:checked'), function(c){                                  
                              return $(c).data('invoice_no');       
                            });

    Swal.fire({
      title: 'Are you sure?',     
      text: "You want to "+ disregard_invoice_text +" the selected invoices!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, '+ disregard_invoice_text_capitalize +'!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
    
      if (result.value) {
        
        btn_disregard_invoice.attr('disabled', 'disabled');
        btn_disregard_invoice.html(
            '<span><i class="bx bx-list-minus"></i> ' +
            disregard_invoice_text_loading + '...</span>');
        
        var filldata = {         
          "invoice_id": selected_invoices_id,
          "invoice_no": selected_invoices,
          //"invoice_date": selected_invoices[0]['invoice_date'],         
          //"invoice_name": data['invoice_name'],
          "tab_name": data['tab_name'],
          "disregard": data['is_disregard']
        };
        fillDisregardModal(filldata);        
            
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled ' + disregard_invoice_text_capitalize + ' Invoice :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
     
    }); 
  });

  function fillDisregardModal(data)
  {       
    $("#invoice_vat_reg_id").val($("#vat_reg_id").val());
    $("#invoice_id").val(data['invoice_id']);
    $("#invoice_no").val(data['invoice_no']);    
    $("#tab_name").val(data['tab_name']);
    $("#is_disregard").val(data['disregard']);

    $("#invoice-disregard-reason").val('');
    $("#invoice-disregard-editor .ql-editor").html('');  

    $('#modalInvoiceDisregard').modal('show');
  }

  // Disregard invoice MODAL - close
  $(document).on("hide.bs.modal", "#modalInvoiceDisregard", function(event) {
    console.log("modal close");

    var which_tab = $(this).find('#tab_name').val();
    $('#btn_'+ which_tab +'_disregard_invoice').html(            
      '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>'
    );
  });

  // Disregard invoice - Save  
  $(document).on("submit", ".frm-invoice-disregard", function(event) {  
    event.preventDefault();

    var selected_reason = $("#invoice-disregard-reason").val();

    if($(this).find(".ql-editor").html().replace( /(<([^>]+)>)/ig, '') == "")
    {      
      Swal.fire({
        title: 'Error',
        text: 'Please type reason for disregarding invoice',
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
      
      var invoice_id = (String($('#invoice_id').val()).indexOf(',') != -1) ? 0 : $('#invoice_id').val();      
       
      $("#invoice-disregard-quill").val($(this).find(".ql-editor").html());

      var formData = new FormData(this);         
                   
      var btn_comment_save = $("#" + formId + " #btn-invoice-disregard-save");
      btn_comment_save.attr('disabled', 'disabled');
      btn_comment_save.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');
      
      $.ajax({
        url: `${invoiceUrl}${invoice_id}/disregard`,
        type: 'POST',
        dataType: "JSON",
        data: formData,
        processData: false,
        contentType: false,
        success: function (result) {
         
          if(result)    
          {                 
            btn_comment_save.removeAttr('disabled');
            btn_comment_save.html('Saved');
            btn_comment_save.removeClass('disabled');

            var which_tab = result['tab_name'];
            $("#btn_"+ which_tab +"_disregard_invoice").html();

            if(invoice_id == 0)
              $('#btn_'+ which_tab +'_disregard_invoice').html(
              '<span><i class="bx bx-list-minus"></i> Disregard invoice</span>');
              
            var invoice_datas = drawDtTable(result, 'invoice');            
            
            if(which_tab == 'correct')
            {
              invoice_correct_datas = invoice_datas['invoice_correct_datas'];
              dt_correct_invoices.clear().rows.add(invoice_correct_datas).draw();

              $("#btn-invoice-correct span").html(invoice_correct_datas.length);
            }
            else if(which_tab == 'wrong')
            {
              invoice_wrong_datas = invoice_datas['invoice_wrong_datas']; 
              dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();

              $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);
            }
            else if(which_tab == 'managed')
            {
              invoice_managed_datas = invoice_datas['invoice_managed_datas'];
              dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw(); 
              $("#btn-invoice-managed span").html(invoice_managed_datas.length);
            }
            filterShowDisregardedInvoices(which_tab);
            reInitializeTooltips();

            var swal_title = 'Disregarded and reason saved';  
            var swal_text = 'Invoice has been disregarded and the reason ';         
                       
            //Clear Modal Values
            $("#invoice-disregard-reason").val('');
            $("#invoice-disregard-editor").find(".ql-editor").html("");
            
            $('#modalInvoiceDisregard').modal('hide');

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

  window.invoiceDisregardEditor = function invoiceDisregardEditor(data = null) {      
    const invoiceDisregardEditors = document.querySelector('#invoice-disregard-editor');   
    // Initialize Quill Editor
    // ------------------------------

    if (invoiceDisregardEditors) {
      new Quill('#invoice-disregard-editor', {
        modules: {
          toolbar: '#invoice-disregard-editor-toolbar'
        },
        placeholder: 'Write your message... ',
        theme: 'snow'
      });
    }  
  } 

  invoiceDisregardEditor(); 

/*
  var dt_all_invoices_table = $('.datatables-all-invoices');
  if (dt_all_invoices_table.length) {
    
    var dt_all_invoices = dt_all_invoices_table.DataTable({  
        data: invoice_datas,              
        scrollCollapse: false,
        scroller: true,
        scrollX: true,
        scrollY: '48vh',        
        searching: true,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'All']
        ],
        pageLength: -1,
        //fixedColumns: true,  
        autoWidth: false,          
        //autoWidth: true,    
        //fixedHeader: false,      
        columns: [           
          { data: 'id' },
          { data: 'fake_id' },
          { data: 'invoice_type' },       
          { data: 'tax_code' },
          { data: 'invoice_date' },                        
          { data: 'invoice_no' },
          { data: 'currency_code' },
          { data: 'total_net' },   
          { data: 'vat_rate' },   
          { data: 'total_vat' },     
          { data: 'total_gross' },
          { data: 'local_currency_code' },
          { data: 'exchange_rate' },
          { data: 'local_total_net' },
          { data: 'local_total_vat' },
          { data: 'local_total_gross' },
          { data: 'n' },
          { data: 'o' },
          { data: 'p' },
          { data: 'q' },
          { data: 'c_name' },
          { data: 'c_vat_no' },
          { data: 'c_street' },
          { data: 'c_house_no' },
          { data: 'c_city' },
          { data: 'c_postcode' },
          { data: 'c_country' },
          { data: 'pdf' }
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
            {
                target: 2,
                visible: false
            }           
            {
                target: 1,
                //width: "5%",
            },
            
            {
                target: 2,
                //width: "10%",
            },
            {
                target: 3,
                //width: "15%",
            },
            {
                target: 4,
                //width: "10%",
            },
            {
                target: 5,
                //width: "10%",
            },
            {
                target: 6,
                //width: "5%",
            },
            {
                target: 7,
                //width: "10%",
            },
            {
                target: 8,
                //width: "10%",
            },
            {
                target: 9,
                //width: "10%",
            },
            {
                target: 10,
                //width: "10%",
            },
            {
                target: 11,
                //width: "5%",
            }
            ,
            {
                target: 12,
                //visible: false
            },
            {
                target: 13,
                //visible: false
            },
            {
                target: 14,
                //visible: false
            },
            {
                target: 15,
                //visible: false
            },
            {
                target: 16,
                //visible: false
            },
            {
                target: 17,
                //visible: false
            },
            {
                target: 18,
                //visible: false
            },
            {
                target: 19,
                //visible: false
            },
            {
                target: 20,
                //visible: false
            },
            {
                target: 21,
                //visible: false
            },
            {
                target: 22,
                //visible: false
            },
            {
                target: 23,
                //visible: false
            },
            {
                target: 24,
                //visible: false
            },
            {
                target: 25,
                //visible: false
            },
            {
                target: 26,
                //visible: false
            },
            {
                target: 27,
                //visible: false
            }           
        ],
        processing: true, 
        order: [[1, 'desc']],
        // dom:     
        //   '<"row mx-0 border-bottom"' +
        //   '<"col-md-2"<"me-3"l>>' +
        //   '<"col-md-10 my-1"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        //   '>rt' +
        //   '<"row mx-2"' +
        //   '<"col-sm-12 col-md-6"i>' +
        //   '<"col-sm-12 col-md-6"p>' +
        //   '>',
        dom:     
          '<"row mx-0 border-bottom"' +    
          '<"col-md-9"<"me-3"l>>' +      
          '<"col-md-3 my-1"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +          
          //'>rt' +
          '>r' +
          '<"row mx-0"' +
          '<"col-sm-12 p-0 custom-tab"t' +                    
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
              title: 'Invoices',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                //columns: [1, 2, 3, 4, 5, 6, 7, 8, 9,10],
                //columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26],
                columns: ':visible',
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('client-name')) {
                        result = result + item.lastChild.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
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
              title: 'Invoices',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                //columns: [1, 2, 3, 4, 5, 6, 7, 8, 9,10]            
                //columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26]
                columns: ':visible'
              }
            },
            {
              extend: 'excel',
              title: 'Invoices',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',                
              exportOptions: {
                //columns: [1, 2, 3, 4, 5, 6, 7, 8, 9,10]              
                //columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26]
                columns: ':visible'
              }
            },
            {
              extend: 'pdf',
              title: 'Invoices',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {
                //columns: [1, 2, 3, 4, 5, 6, 7, 8, 9,10]  
                //columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26]           
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Invoices',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {
                //columns: [1, 2, 3, 4, 5, 6, 7, 8, 9,10]              
                //columns: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26]
                columns: ':visible'
              }
            },
            {
                extend: 'excelHtml5',
                title: 'SAF-T',
                text: '<i class="bx bx-file me-2" ></i>SAF-T',
                action: saftexportaction                    
            }
            
          ]
        }            
      ],
      initComplete: function (settings, json) {   

        this.api()
        //.columns(12)
        .columns(2)
        .every(function () {
          var column = this;
         
          var select = $(                  
          '<div class="form-check form-switch mb-2">' +
          '<input class="form-check-input" type="checkbox" id="flexSwitchCheckInvoiceType">' +
          '<label class="form-check-label" for="flexSwitchCheckInvoiceType" id="flexSwitchCheckInvoiceTypeLabel">Sales + Purchase</label>' +
          '</div>'
          )
          .appendTo('.invoice_type')
          .on('change', function () {   
            
            var val = $("#flexSwitchCheckInvoiceType").prop('checked') ? 'Sales' : 'Purchase';
            $("#flexSwitchCheckInvoiceTypeLabel").text(val);

            var search_val = (val == 'Sales') ? 'sale' : 'purchase';
            column.search(search_val ? search_val : '', true, false).draw();             
          });
        }); 
        

        $(".dt-buttons.btn-group.flex-wrap").appendTo('.invoice_export');

        // var switchsearch = '<label class="switch mx-3">' +
        //                     '<input type="checkbox" class="switch-input search" />' +
        //                     '<span class="switch-toggle-slider">' +
        //                       '<span class="switch-on"></span>' +
        //                       '<span class="switch-off"></span>' +
        //                     '</span>' +
        //                     '<span class="switch-label">Search</span>' +
        //                   '</label>';
        // $(switchsearch).appendTo('.dataTables_filter');
        // $(".dataTables_filter label:not(.switch").hide();

        var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasInvoiceColumnSetting" aria-controls="offcanvasInvoiceColumnSetting">' +
                              '<i class="bx bx-slider"></i>' +
                            '</label>';
        $(sliderfilter).appendTo('.dataTables_filter');

        var invoice_total = this.api().data().length;
        var customtab = '<div class="card shadow-none">' + 
                          '<div class="card-header border-bottom">' +
                              '<ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">' +
                                  '<li class="nav-item">' +
                                    '<button type="button" id="btn-invoice-correct" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-correct" aria-controls="navs-invoice-correct" aria-selected="true">Invoices</button>' +
                                  '</li>' +

                                  '<li class="nav-item">' +
                                    '<button type="button" id="btn-invoice-wrong" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoice-wrong" aria-controls="navs-invoice-wrong" aria-selected="true">Mismatched Invoices</button>' +
                                  '</li>' +
                              '</ul>' +
                          '</div>' +    
                          '<div class="tab-content px-0">' +      
                              '<div class="tab-pane fade" id="navs-invoice-correct" role="tabpanel">' +
                                
                              '</div>' +
                              '<div class="tab-pane fade" id="navs-invoice-wrong" role="tabpanel">' +
                                
                              '</div>' +
                          '</div>' +
                        '</div>';

        $(customtab).prependTo('.custom-tab');       

        if(search_currency == '')
        {
          $(".dataTables_scroll").appendTo('#navs-invoice-correct');
          //$(".dataTables_scroll").clone().appendTo('#navs-invoice-wrong');             

          // $.fn.dataTable.ext.search.push(
          //   function(settings, data, dataIndex) {
          //       //return $(dt_all_invoices.row(dataIndex).node()).find('.selectRow').prop("checked") == true;
          //        return $(this.row(dataIndex).node()).val() == $('#currency_code').val();
          //     }
          // );
          // this.draw();

          $("#btn-invoice-correct").html('Invoices <span class="bg-primary text-white text-end fs-tiny p-1 mx-2">'+ invoice_total +'</span>');
          $("#btn-invoice-wrong").html('Mismatched Invoices <span class="bg-danger text-white text-end fs-tiny p-1 mx-2">'+ invoice_total +'</span>');      

          $("#navs-invoice-wrong").removeClass('show active');
          $("#navs-invoice-correct").addClass('show active');

          $("#btn-invoice-wrong").removeClass('active');
          $("#btn-invoice-correct").addClass('active');
        }
        else
        {
          if(search_currency == $('#currency_code').val())
          {
            $(".dataTables_scroll").appendTo('#navs-invoice-correct');

            $("#btn-invoice-correct").html('Invoices <span class="bg-primary text-white text-end fs-tiny p-1 mx-2">'+ invoice_total +'</span>');
            $("#btn-invoice-wrong").html('Mismatched Invoices <span class="bg-danger text-white text-end fs-tiny p-1 mx-2">0</span>');

            $("#navs-invoice-wrong").removeClass('show active');
            $("#navs-invoice-correct").addClass('show active');

            $("#btn-invoice-wrong").removeClass('active');
            $("#btn-invoice-correct").addClass('active');
          }
          else         
          {      
            $(".dataTables_scroll").appendTo('#navs-invoice-wrong');

            $("#btn-invoice-wrong").html('Mismatched Invoices <span class="bg-danger text-white text-end fs-tiny p-1 mx-2">'+ invoice_total +'</span>');
            $("#btn-invoice-correct").html('Invoices <span class="bg-primary text-white text-end fs-tiny p-1 mx-2">0</span>');

            $("#navs-invoice-correct").removeClass('show active');
            $("#navs-invoice-wrong").addClass('show active');

            $("#btn-invoice-correct").removeClass('active');
            $("#btn-invoice-wrong").addClass('active');
          }
        }
      
        $(".card.invoices .sk-bounce").hide();
        $(".card.invoices .card-datatable").show();           
      }, 
      // rowCallback: function( row, data, index ) {

      //     if (data[5] != $("#currency_code").val()) //{
      //         $(row).hide();
      //         // $("#navs-invoice-correct .dataTables_scrollBody tbody").find(row).show();
      //         // $("#navs-invoice-wrong .dataTables_scrollBody tbody").find(row).hide();
      //     // }
      //     // else
      //     // {
      //     //   $("#navs-invoice-correct .dataTables_scrollBody tbody").find(row).hide();
      //     //   $("#navs-invoice-wrong .dataTables_scrollBody tbody").find(row).show();
      //     // }
      // },
    });

  }//DATATABLE
    */
  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300); 

  $("#offcanvasInvoiceColumnSetting .form-check-input").each(function () {
    //toggleDtTableColumn(dt_all_invoices, $(this));
    
    if($("#offcanvasInvoiceColumnSetting .form-check-input:not('#chk_invoice_column_check_all'):checked").length == 25)
    {
      $("#offcanvasInvoiceColumnSetting .form-check-input#chk_invoice_column_check_all").prop("indeterminate", false); 
      $("#offcanvasInvoiceColumnSetting .form-check-input#chk_invoice_column_check_all").prop("checked", true); 
    }
    else    
      $("#offcanvasInvoiceColumnSetting .form-check-input#chk_invoice_column_check_all").prop("indeterminate", true); 

    toggleDtTableColumn([dt_correct_invoices, dt_wrong_invoices, dt_managed_invoices], $(this));
  });

  // Datatable Column Visible Checkbox
  $(document).on('click', '#offcanvasInvoiceColumnSetting .form-check-input', function () { 
    if ($(this).attr("id") == "chk_invoice_column_check_all") 
    {
      if ($(this).is(":checked"))
        document.querySelectorAll("#offcanvasInvoiceColumnSetting .form-check-input").forEach(c => (c.checked = 1));
      else
        document.querySelectorAll("#offcanvasInvoiceColumnSetting .form-check-input").forEach(c => (c.checked = 0));
    }
    else    
      $("#offcanvasInvoiceColumnSetting .form-check-input#chk_invoice_column_check_all").prop("indeterminate", true);
    
    toggleDtTableColumn([dt_correct_invoices, dt_wrong_invoices, dt_managed_invoices], $(this));
  });

  // Datatable Checkbox Convert
  /*
  $(document).on('click', '#navs-invoice-wrong .form-check-input.dt-checkboxes, #navs-invoice-wrong td', function () {  
    //var selectedInvoices = $('#navs-invoice-wrong .form-check-input.dt-checkboxes:checked').length;  
    var selectedInvoices = $.map($('#navs-invoice-wrong .form-check-input.dt-checkboxes:checked'), function(c){return c.value; });
//     var selectedInvoices = $.map($('#navs-invoice-wrong .form-check-input.dt-checkboxes:checked'), function(c){            
//       return {id: c.value, invoice_date: $(c).data('invoice_date')};      
//     });
// console.log(selectedInvoices);
    //var data = $(this).data();
    //var vat_reg_id = $("#vat_reg_id").val();
    //var from_currency = data["from_currency"];

    //$("#selectedInvoiceCount-" + vat_reg_id + "-" + from_currency).html('('+selectedInvoices.length+')');
    
    $(".selectedInvoiceCount").html('('+selectedInvoices.length+')');
    $(".wrong-search-filter .selectedInvoiceCount").html(selectedInvoices.length+' rows selected');

    if(selectedInvoices.length == 0)
    {     
      $(".wrong-search-filter .selectedInvoiceCount").html('');

      $('#btn-convert-currency').prop('disabled', 'disabled');  
    }
    else    
      $('#btn-convert-currency').removeAttr('disabled');    
  });
*/
  // $(document).on('click', '.dt-checkboxes-select-all', function () {
  //   alert("clicked");
  // });

  dt_wrong_invoices.on('select', function (e, dt, type, indexes) {
    selectDeselectRows(e, dt, type, indexes);
  });

  dt_wrong_invoices.on('deselect', function (e, dt, type, indexes) {
    selectDeselectRows(e, dt, type, indexes);
  });

  function selectDeselectRows(e, dt, type, indexes)
  {
    
    if (type === 'row') 
    {
        var data = dt_wrong_invoices
            .rows({ page: 'current', selected: true, indexes: indexes })
            .data()
            .pluck('from_currency')
            ;
      
        var array = $.map(data, function(value, index){
          return [value];
        });

        if(array.length == 0)  
        {
          $('#btn-convert-currency').prop('disabled', 'disabled'); 

          $(".wrong-search-filter .selectedInvoiceCount").html(''); 
        }
        else
        {
          $('#btn-convert-currency').removeAttr('disabled');

          $(".wrong-search-filter .selectedInvoiceCount").html(array.length+' rows selected');
        }
      
        const groupSimilar = arr => {
           return arr.reduce((acc, val) => {
              const { data, map } = acc;
              const ind = map.get(val);
              if(map.has(val)){
                 data[ind][1]++;
              } else {
                 map.set(val, data.push([val, 1])-1);
              }
              return { data, map };
           }, {
              data: [],
              map: new Map()
           }).data;
        };
 
        var groupCurrencies = groupSimilar(array);

        $(".formCurrencyConvert .accordion .accordion-item").each(function(index) {
          
          $(this).next('.divider').hide();
          $(this).hide();  
          $(this).find(".selectedInvoiceCount").html('(0)'); 
        });

        $.each( groupCurrencies, function( key, value ) {
          
          if(value[1] == 0)
          {
            $("#" + value[0]).next('.divider').hide();
            $("#" + value[0]).hide();             
          }
          else
          {
            $("#" + value[0]).next('.divider').show();
            $("#" + value[0]).show();              
          }
          $("#" + value[0] + " .selectedInvoiceCount").html('('+value[1]+')');
        });

    }  
  }
  
  //Invoice column settings
  $(document).on("submit", ".form-invoice-column-settings", function(event)
  {
    event.preventDefault();

    var form = $(this);        
    var user_id = form.find('#user-id').val();

    var btn_save_invoice_setting = form.find("button.btn-save-invoice-column-setting");
    btn_save_invoice_setting.attr('disabled', 'disabled');
    btn_save_invoice_setting.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Saving...');

    $.ajax({
      //url: `${userView}invoice-column-settings/${user_id}`,
      url: `${invoiceUrl}invoice-column-settings`,
      type: 'POST',     
      data: form.serialize(),     
      success: function (status) {       
        $("#offcanvasInvoiceColumnSetting").offcanvas('hide');
        
        btn_save_invoice_setting.removeAttr('disabled');               
        btn_save_invoice_setting.html("Save");            
      },
      error: function (error) {
        console.log(error);
      }
    }); 
  });  

  //Switch Currency convert options
  $(".switch-input.currency-convert-date").each(function () {
    switchCurrenctRateTypes($(this));   
  });

  $(document).on('click', '.switch-input.currency-convert-date', function () { 
    switchCurrenctRateTypes($(this));   
    /*
    var formId = $(this).closest('.formCurrencyConvert').attr('id');    
          
    if(!$(this).prop('checked'))   
    {
      $("#" + formId + " .btn-convert").addClass('disabled');                     
      $("#" + formId + " .btn-convert").removeClass('btn-success');                
      $("#" + formId + " .btn-convert").addClass('btn-danger'); 
    }
    else     
    {
      $("#" + formId + " .btn-convert").removeClass('disabled');        
      $("#" + formId + " .btn-convert").addClass('btn-success');                
      $("#" + formId + " .btn-convert").removeClass('btn-danger');  
    }
    */
  });

  function switchCurrenctRateTypes(element)
  {
    var formId = element.closest('.formCurrencyConvert').attr('id');    
          
    if(!element.prop('checked'))   
    {
      $("#" + formId + " .btn-convert").addClass('disabled');                     
      $("#" + formId + " .btn-convert").removeClass('btn-success');                
      $("#" + formId + " .btn-convert").addClass('btn-danger'); 
    }
    else     
    {
      $("#" + formId + " .btn-convert").removeClass('disabled');        
      $("#" + formId + " .btn-convert").addClass('btn-success');                
      $("#" + formId + " .btn-convert").removeClass('btn-danger');  
    }
  }

  //Convert currency 
  $(document).on('click', '.btn-convert', function () {
    console.log("conversion start Time : -- " + moment().format("DD-MM-YYYY h:m:s A"));   

    var formId = $(this).closest('.formCurrencyConvert').attr('id');    
    var modalId = $(this).closest('.modal-file').attr('id'); 
    
    var btn_convert = $(this);   

    var data = btn_convert.data();
   
    var vat_reg_id = data['vat_reg_id'];  
   
    var selected_invoices_id = $.map($('#navs-invoice-wrong .form-check-input.dt-checkboxes:checked'), function(c){return c.value; });
    var selected_invoices = $.map($('#navs-invoice-wrong .form-check-input.dt-checkboxes:checked'), function(c){
      return {id: c.value, invoice_no: $(c).data('invoice_no'), invoice_date: $(c).data('invoice_date')}; 
      //return { c.value: $(c).data('invoice_date')}; 
    });

    Swal.fire({
      title: 'Are you sure?',
      text: "You want to convert the currency for the selected invoices!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Convert!',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {        
      if (result.value) {
        btn_convert.attr('disabled', 'disabled');
        btn_convert.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
            'Converting...');
        
        $.ajax({
          data: $('#' + formId).serialize() + '&selected_invoices=' + JSON.stringify(selected_invoices),
          type: 'POST',
          url: `${invoiceUrl}${vat_reg_id}/convert`,
          success: function (result) {
            console.log(result);
            
            console.log("conversion middle Time : -- " + moment().format("DD-MM-YYYY h:m:s A"));   
            btn_convert.removeAttr('disabled');               
            btn_convert.html("Convert");

            var invoice_correct_count = invoice_correct_datas.length;
            var invoice_wrong_count = invoice_wrong_datas.length;

            //console.log(invoice_wrong_datas); 
            var delete_from_wrong = invoice_wrong_datas.filter((obj) => {
              return $.inArray(obj.id.toString(), selected_invoices_id) != -1;
            });console.log(delete_from_wrong); 

            var invoice_datas = drawDtTable(result, 'invoice');//console.log(invoice_datas);
            
            console.log(invoice_correct_count); 
            console.log(invoice_datas); 

            if(invoice_correct_count == invoice_datas['invoice_correct_datas'].length)
            {
              if(search_type != '' && search_percentage != '' && search_currency != '')
              {                
                var invoice_merged_datas = invoice_managed_datas.concat(delete_from_wrong);
                invoice_managed_datas = invoice_merged_datas;

                dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw();                
                $("#btn-invoice-managed span").html(invoice_managed_datas.length);

                var remove_from_wrong = invoice_wrong_datas.filter((obj) => {
                  return $.inArray(obj.id.toString(), selected_invoices_id) == -1;
                });console.log(remove_from_wrong);

                invoice_wrong_datas = remove_from_wrong;
                dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();
                $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);

                /*
                // var delete_from_wrong = invoice_wrong_datas.filter((obj) => {
                //   return $.inArray(obj.id.toString(), selected_invoices_id) == -1;
                // });console.log(delete_from_wrong);
                // invoice_wrong_datas = delete_from_wrong;
                dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();
                $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);

                //include in managed invoices                            
                invoice_managed_datas = delete_from_wrong;              
                dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw();                
                $("#btn-invoice-managed span").html(invoice_managed_datas.length);
                */
              }
              else
              {
                //console.log(invoice_correct_datas);
                var move_to_correct = invoice_wrong_datas.filter((obj) => {
                  return $.inArray(obj.id.toString(), selected_invoices_id) != -1;
                });
                //console.log(move_to_correct);               
                //invoice_correct_datas.push(move_to_correct);
                //var arr3 = $.merge(invoice_correct_datas, move_to_correct)
                var invoice_merged_datas = invoice_correct_datas.concat(move_to_correct);
                //$.merge( $.merge( [], invoice_correct_datas ), invoice_correct_datas );
                //console.log(invoice_merged_datas);
                invoice_correct_datas = invoice_merged_datas;    
                dt_correct_invoices.clear().rows.add(invoice_correct_datas).draw();
                $("#btn-invoice-correct span").html(invoice_correct_datas.length);

                var delete_from_wrong = invoice_wrong_datas.filter((obj) => {
                  return $.inArray(obj.id.toString(), selected_invoices_id) == -1;
                });  
                invoice_wrong_datas = delete_from_wrong;              
                dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();
                $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);


  //               //remove only the selected rows and draw              
  //               var wrong_rows = dt_wrong_invoices.rows('.selected');              
  //               //dt_correct_invoices.rows('.selected').remove().draw();
  // console.log(wrong_rows);
  //               dt_correct_invoices.row.add(wrong_rows).draw(false);
  //               wrong_rows.remove().draw();

  //               $("#btn-invoice-correct span").html(invoice_correct_count + selected_invoices.length);
  //               $("#btn-invoice-wrong span").html(invoice_wrong_count - selected_invoices.length);
              }
            }
            else
            {              
              invoice_correct_datas = invoice_datas['invoice_correct_datas']; 
              invoice_wrong_datas = invoice_datas['invoice_wrong_datas']; 
              invoice_managed_datas = invoice_datas['invoice_managed_datas']; 
              dt_correct_invoices.clear().rows.add(invoice_correct_datas).draw();           
              dt_wrong_invoices.clear().rows.add(invoice_wrong_datas).draw();
              dt_managed_invoices.clear().rows.add(invoice_managed_datas).draw();

              $("#btn-invoice-correct span").html(invoice_correct_datas.length);
              $("#btn-invoice-wrong span").html(invoice_wrong_datas.length);
              $("#btn-invoice-managed span").html(invoice_managed_datas.length);
            }

            $("#"+ modalId).modal('hide');

            if(result['message'] == 'success')    
              Swal.fire({
                icon: 'success',
                title: `Successfully converted!`,
                text: `Currency converted Successfully.`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            else
              Swal.fire({
                icon: 'error',
                title: `Error in conversion!`,
                text: `Error while converting`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
console.log("conversion end Time : -- " + moment().format("DD-MM-YYYY h:m:s A"));   
            
          },
          error: function (error) {
            console.log(error);
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled currency conversion :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });    
  });

  //SAFT Export    
  function saftexportaction(e, dt, button, config) {
      
      Swal.fire({
        icon: 'warning',
        title: 'Contact Admin!',
        text: 'SAFT API',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      
      
      // //LIVE - SAFT - dont delete
      // $(".content-wrapper .sk-bounce").show();
      // $(".content-wrapper .sk-bounce").addClass("invoice-center");
     
      // var vat_id = $('#vat_id').val();
      // var invoice_period = $('#invoice_period').val();
      // var invoice_year = $('#invoice_year').val();

      // $.ajax({        
      //   data: {invoice_period: invoice_period, invoice_year: invoice_year},     
      //   url: `${baseUrl}invoices/${vat_id}/downloadsaft`,
      //   type: 'POST',
      //   xhrFields: {
      //     responseType: 'blob'      
      //   },
      //   success: function (data) {
      //     $(".content-wrapper .sk-bounce").removeClass("invoice-center");
      //     $(".content-wrapper .sk-bounce").hide();

      //     var blob=new Blob([data]);      
      //     var link=document.createElement('a');
      //     link.href=window.URL.createObjectURL(blob);
      //     link.download="saft.xls";
      //     link.click();
      //   },
      //   error: function (err) {
      //     console.log(err);     
      //   }
      // });
      
  }

  //PDF Click    
  $(document).on('click', 'span.pdf', function () {    
    var data = $(this).data();    

    var spanpdf = $(this);
    spanpdf.html('<!-- Bounce -->' +
          '<div class="sk-bounce sk-primary sk-center">' +
            '<div class="sk-bounce-dot"></div>' +
            '<div class="sk-bounce-dot"></div>' +
          '</div>');

    /*
    var apiType = '';
    if(data['api_name'] == "Dynamics 365")
      apiType = 'dynamics';
    else if(data['api_name'] == "E-conomic")
      apiType = 'economic';

    $.ajax({
      data: data,
      url: `${baseUrl}invoices/${apiType}/pdf`,
      type: 'POST',
      xhrFields: {
        responseType: 'blob'
      },
      success: function (data) {
        spanpdf.html('<i class="fa fa-download"></i>');

        var blob=new Blob([data]);
        var link=document.createElement('a');
        link.href=window.URL.createObjectURL(blob);
        link.download="invoice.pdf";
        link.click();
      },
      error: function (err) {
        console.log(err);     
      }
    });
    */

    var vat_reg_id = data['vat_reg_id'];
    var invoice_id = data['invoice_id'];
    var invoice_type = data['invoice_type'];
    $.ajax({      
      url: `${baseUrl}invoice/download/${vat_reg_id}`,
      data: {invoice_id: invoice_id, invoice_type: invoice_type},
      type: 'POST',
      xhrFields: {
        responseType: 'blob'
      },
      success: function (data) {
        spanpdf.html('<i class="fa fa-download"></i>');

        var blob=new Blob([data]);
        var link=document.createElement('a');
        link.href=window.URL.createObjectURL(blob);
        link.download="invoice.pdf";
        link.click();
      },
      error: function (err) {
        //console.log(err);     

        spanpdf.html('<i class="fa fa-download"></i>');
        Swal.fire({
          icon: 'error',
          title: `PDF not found!`,
          text: `No PDF found for this Invoice No.!`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }); 
      }
    });

  });

  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    var id = $(e.target).attr("id") // activated tab
   
    if(id == 'btn-invoice-correct')
    {
      $(".dt-invoice-type .wrong-invoice-type").addClass('d-none');
      $(".dt-invoice-type .managed-invoice-type").addClass('d-none');
      $(".dt-invoice-type .correct-invoice-type").removeClass('d-none');      

      $(".dt-invoice-export .wrong-invoice-export").addClass('d-none');
      $(".dt-invoice-export .managed-invoice-export").addClass('d-none');
      $(".dt-invoice-export .correct-invoice-export").removeClass('d-none');      

      $(".dt-search-filter .wrong-search-filter").addClass('d-none');
      $(".dt-search-filter .managed-search-filter").addClass('d-none');
      $(".dt-search-filter .correct-search-filter").removeClass('d-none');      
    }
    else if(id == 'btn-invoice-wrong')
    {
      $(".dt-invoice-type .correct-invoice-type").addClass('d-none');
      $(".dt-invoice-type .managed-invoice-type").addClass('d-none');
      $(".dt-invoice-type .wrong-invoice-type").removeClass('d-none');

      $(".dt-invoice-export .correct-invoice-export").addClass('d-none');
      $(".dt-invoice-export .managed-invoice-export").addClass('d-none');
      $(".dt-invoice-export .wrong-invoice-export").removeClass('d-none');

      $(".dt-search-filter .correct-search-filter").addClass('d-none');
      $(".dt-search-filter .managed-search-filter").addClass('d-none');
      $(".dt-search-filter .wrong-search-filter").removeClass('d-none');      
    }  
    else if(id == 'btn-invoice-managed')
    {
      $(".dt-invoice-type .correct-invoice-type").addClass('d-none');
      $(".dt-invoice-type .wrong-invoice-type").addClass('d-none');
      $(".dt-invoice-type .managed-invoice-type").removeClass('d-none');

      $(".dt-invoice-export .correct-invoice-export").addClass('d-none');
      $(".dt-invoice-export .wrong-invoice-export").addClass('d-none');
      $(".dt-invoice-export .managed-invoice-export").removeClass('d-none');

      $(".dt-search-filter .correct-search-filter").addClass('d-none');
      $(".dt-search-filter .wrong-search-filter").addClass('d-none');
      $(".dt-search-filter .managed-search-filter").removeClass('d-none');      
    }  
  });

  function exportToExcelCorrectInvoices() {
    
      let workbook = XLSX.utils.book_new();
      let sheetData = [];

      // Define headers     
      let headers = [];
      let visibleColumnIndexes = [];        

      dt_correct_invoices.columns().every(function(index) {        
          if (this.visible()) {             
              let columnData = this.dataSrc(); // Get the data property name       
              if(columnData != 'id')
              {
                if(columnData == 'fake_id')
                  headers.push('No.'); // Get header text       
                else
                  headers.push(this.header().innerText); // Get header text       

                visibleColumnIndexes.push(columnData); // Store the data property name
              }
          }
      });

      // Loop through the main DataTable
      let allData = [];
      dt_correct_invoices.rows().every(function(rowIdx) {
          var rowData = this.data();
         
          let rowInfo = [];
          var currency_code = '';
          visibleColumnIndexes.forEach(function(colName) {                        
              if(colName == 'pdf')
                rowInfo.push('-'); // Push the value into the row array
              else
              {                
                if(colName == 'currency_code')
                {
                  currency_code = rowData[colName];
                  rowInfo.push(currency_code); // Push the value into the row array
                }
                else if(colName == 'total_net' || colName == 'total_vat' || colName == 'total_gross'
                   || colName == 'exchange_rate' || colName == 'local_total_net' || colName == 'local_total_vat'
                    || colName == 'local_total_gross')
                {
                  let value = rowData[colName];
                  if (typeof value === "number") 
                    rowInfo.push(value); // Directly push the number
                  else {
                    
                    let parsed_value =  parseAmountValue(value, currency_code);
                    rowInfo.push(parsed_value); // Push the number or an empty string

                    /*
                      var currency_locale = 'en-US';
                      if(currency_code == "DKK" || currency_code == "NOK")           
                          currency_locale = 'da-DK';                  
                      else if(currency_code == "SEK")       
                          currency_locale = 'sv-SE';      
                      else if(currency_code == "GBP")     
                          currency_locale = 'en-GB';      
                      else if(currency_code == "INR")        
                          currency_locale = 'en-IN';
                      else if(currency_code == "EUR")        
                          currency_locale = 'fr-FR';
                      else if(currency_code == "CHF")        
                          currency_locale = 'fr-FR'; 

                      if(currency_code == 'DKK' || currency_code == 'NOK')
                      {  
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\./g, '') // Remove thousand separators
                            .replace(',', '.'); // Replace decimal comma with decimal point

                        let parsedValue = parseFloat(sanitizedValue);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      else if(currency_code == 'SEK' || currency_code == 'EUR' || currency_code == 'CHF')
                      {  
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\s/g, '') // Remove thousand separators
                            .replace(',', '.'); // Replace decimal comma with decimal point

                        let parsedValue = parseFloat(sanitizedValue);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      else
                      {
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\s/g, '') // Remove thousand separators
                            .replace(/\,/g, ''); // Remove thousand separators

                        let parsedValue = parseFloat(sanitizedValue);    
                        //let parsedValue = parseFloat(value);                        
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      */
                  }                  
                }
                else
                  rowInfo.push(rowData[colName]); // Push the value into the row array  
              }
          });
          
          // Push main row data         
          allData.push(rowInfo); // Push the rowInfo object to the array   
      });
      console.log(allData);
     
      // Include the headers
      //let headers = visibleColumnIndexes; // Assuming headers match the data property names
      allData.unshift(headers); // Add headers as the first row

      // Create the worksheet      
      let worksheet = XLSX.utils.aoa_to_sheet(allData);

      XLSX.utils.book_append_sheet(workbook, worksheet, "Invoices");

      // Export the workbook
      XLSX.writeFile(workbook, 'Invoices.xlsx');
  }

  function exportToExcelWrongInvoices() {
    
      let workbook = XLSX.utils.book_new();
      let sheetData = [];

      // Define headers     
      let headers = [];
      let visibleColumnIndexes = [];        

      dt_wrong_invoices.columns().every(function(index) {        
          if (this.visible()) {             
              let columnData = this.dataSrc(); // Get the data property name       
              if(columnData != 'id')
              {
                if(columnData == 'fake_id')
                  headers.push('No.'); // Get header text       
                else
                  headers.push(this.header().innerText); // Get header text       

                visibleColumnIndexes.push(columnData); // Store the data property name
              }
          }
      });

      // Loop through the main DataTable
      let allData = [];
      dt_wrong_invoices.rows().every(function(rowIdx) {
          var rowData = this.data();
         
          let rowInfo = [];
          var currency_code = '';
          visibleColumnIndexes.forEach(function(colName) {                        
              if(colName == 'pdf')
                rowInfo.push('-'); // Push the value into the row array
              else
              {                
                if(colName == 'currency_code')
                {
                  currency_code = rowData[colName];
                  rowInfo.push(currency_code); // Push the value into the row array
                }
                else if(colName == 'total_net' || colName == 'total_vat' || colName == 'total_gross'
                   || colName == 'exchange_rate' || colName == 'local_total_net' || colName == 'local_total_vat'
                    || colName == 'local_total_gross')
                {
                  let value = rowData[colName];
                  if (typeof value === "number") 
                    rowInfo.push(value); // Directly push the number
                  else {
                    let parsed_value =  parseAmountValue(value, currency_code);
                    rowInfo.push(parsed_value); // Push the number or an empty string

                    /*
                      var currency_locale = 'en-US';
                      if(currency_code == "DKK" || currency_code == "NOK")           
                          currency_locale = 'da-DK';                  
                      else if(currency_code == "SEK")       
                          currency_locale = 'sv-SE';      
                      else if(currency_code == "GBP")     
                          currency_locale = 'en-GB';      
                      else if(currency_code == "INR")        
                          currency_locale = 'en-IN';
                      else if(currency_code == "EUR")        
                          currency_locale = 'fr-FR';
                      else if(currency_code == "CHF")        
                          currency_locale = 'fr-FR'; 

                      if(currency_code == 'DKK' || currency_code == 'NOK')
                      {  
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\./g, '') // Remove thousand separators
                            .replace(',', '.'); // Replace decimal comma with decimal point

                        let parsedValue = parseFloat(sanitizedValue);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      else if(currency_code == 'SEK' || currency_code == 'EUR' || currency_code == 'CHF')
                      {  
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\s/g, '') // Remove thousand separators
                            .replace(',', '.'); // Replace decimal comma with decimal point

                        let parsedValue = parseFloat(sanitizedValue);                       
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      else
                      {
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\s/g, '') // Remove thousand separators
                            .replace(/\,/g, ''); // Remove thousand separators

                        let parsedValue = parseFloat(sanitizedValue);  

                        //let parsedValue = parseFloat(value);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      */
                  }                  
                }
                else
                  rowInfo.push(rowData[colName]); // Push the value into the row array  
              }
          });
          
          // Push main row data         
          allData.push(rowInfo); // Push the rowInfo object to the array   
      });
      console.log(allData);
     
      // Include the headers
      //let headers = visibleColumnIndexes; // Assuming headers match the data property names
      allData.unshift(headers); // Add headers as the first row

      // Create the worksheet      
      let worksheet = XLSX.utils.aoa_to_sheet(allData);

      XLSX.utils.book_append_sheet(workbook, worksheet, "Invoices");

      // Export the workbook
      XLSX.writeFile(workbook, 'Invoices.xlsx');
  }

  function exportToExcelManagedInvoices() {
    
      let workbook = XLSX.utils.book_new();
      let sheetData = [];

      // Define headers     
      let headers = [];
      let visibleColumnIndexes = [];        

      dt_managed_invoices.columns().every(function(index) {        
          if (this.visible()) {             
              let columnData = this.dataSrc(); // Get the data property name       
              if(columnData != 'id')
              {
                if(columnData == 'fake_id')
                  headers.push('No.'); // Get header text       
                else
                  headers.push(this.header().innerText); // Get header text       

                visibleColumnIndexes.push(columnData); // Store the data property name
              }
          }
      });

      // Loop through the main DataTable
      let allData = [];
      dt_managed_invoices.rows().every(function(rowIdx) {
          var rowData = this.data();
         
          let rowInfo = [];
          var currency_code = '';
          visibleColumnIndexes.forEach(function(colName) {                        
              if(colName == 'pdf')
                rowInfo.push('-'); // Push the value into the row array
              else
              {                
                if(colName == 'currency_code')
                {
                  currency_code = rowData[colName];
                  rowInfo.push(currency_code); // Push the value into the row array
                }
                else if(colName == 'total_net' || colName == 'total_vat' || colName == 'total_gross'
                   || colName == 'exchange_rate' || colName == 'local_total_net' || colName == 'local_total_vat'
                    || colName == 'local_total_gross')
                {
                  let value = rowData[colName];
                  if (typeof value === "number") 
                    rowInfo.push(value); // Directly push the number
                  else {

                    let parsed_value =  parseAmountValue(value, currency_code);
                    rowInfo.push(parsed_value); // Push the number or an empty string
                    
                    /*
                      var currency_locale = 'en-US';
                      if(currency_code == "DKK" || currency_code == "NOK")           
                          currency_locale = 'da-DK';                  
                      else if(currency_code == "SEK")       
                          currency_locale = 'sv-SE';      
                      else if(currency_code == "GBP")     
                          currency_locale = 'en-GB';      
                      else if(currency_code == "INR")        
                          currency_locale = 'en-IN';
                      else if(currency_code == "EUR")        
                          currency_locale = 'fr-FR';
                      else if(currency_code == "CHF")        
                          currency_locale = 'fr-FR'; 

                      if(currency_code == 'DKK' || currency_code == 'NOK')
                      {  
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\./g, '') // Remove thousand separators
                            .replace(',', '.'); // Replace decimal comma with decimal point

                        let parsedValue = parseFloat(sanitizedValue);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      else if(currency_code == 'SEK' || currency_code == 'EUR' || currency_code == 'CHF')
                      {  
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\s/g, '') // Remove thousand separators
                            .replace(',', '.'); // Replace decimal comma with decimal point

                        let parsedValue = parseFloat(sanitizedValue);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      else
                      {
                        // Convert Danish format to a valid number
                        let sanitizedValue = value
                            .replace(/\−/g, '-')
                            .replace(/\s/g, '') // Remove thousand separators
                            .replace(/\,/g, ''); // Remove thousand separators

                        let parsedValue = parseFloat(sanitizedValue);  

                        //let parsedValue = parseFloat(value);
                        rowInfo.push(isNaN(parsedValue) ? "" : parsedValue); // Push the number or an empty string
                      }
                      */
                  }                  
                }
                else
                  rowInfo.push(rowData[colName]); // Push the value into the row array  
              }
          });
          
          // Push main row data         
          allData.push(rowInfo); // Push the rowInfo object to the array   
      });
      console.log(allData);
     
      // Include the headers
      //let headers = visibleColumnIndexes; // Assuming headers match the data property names
      allData.unshift(headers); // Add headers as the first row

      // Create the worksheet      
      let worksheet = XLSX.utils.aoa_to_sheet(allData);

      XLSX.utils.book_append_sheet(workbook, worksheet, "Invoices");

      // Export the workbook
      XLSX.writeFile(workbook, 'Invoices.xlsx');
  }
});
