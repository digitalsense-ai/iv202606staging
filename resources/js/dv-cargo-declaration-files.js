/**
 * Page Cargo Declaration File List
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
  var cargoDeclarationFileUrl = baseUrl + 'cargo-declaration-files/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });  
  
  var dt_cargodeclarationfiles_table = $('.datatables-cargodeclarationfiles');
  if (dt_cargodeclarationfiles_table.length) {
    
    var dt_cargodeclarationfiles = dt_cargodeclarationfiles_table.DataTable({  
        data: cargodeclarationfile_datas,          
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
          { data: 'lope_no' },
          { data: 'cargo_date' },
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
            targets: 8,           
            render: function (data, type, full, meta) { 
              var buttons = "";
              buttons = '<button class="btn btn-sm btn-icon cargo-declaration-file-preview"  title="Preview" data-id="'+full['id']+'" data-file_id="'+ full['file_id'] +'" data-file_type="cargo_mailbox"><span class="tf-icons bx bxs-download"></span></button>';
              return (             
                buttons              
              );
             }          
          },
          {
            // For Action
            targets: 9,              
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
          '<"row mx-0 search-filter"' +              
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
              title: 'Cargo Declaration Files',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },           
            {
              extend: 'excel',
              title: 'Cargo Declaration Files',
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
              title: 'Cargo Declaration Files',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {                
                columns: ':visible'
              }
            },
            {
              extend: 'copy',
              title: 'Cargo Declaration Files',
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
                    
        $(".search-filter").appendTo('.dt-search-filter');

        // var sliderfilter =  '<label class="mx-3 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMailboxfileSetting" aria-controls="offcanvasMailboxfileSetting">' +
        //                       '<i class="bx bx-slider"></i>' +
        //                     '</label>';
        // $(sliderfilter).appendTo('.new-search-filter .dataTables_filter');

        $(".search-filter .dt-buttons.btn-group.flex-wrap").prependTo('.dt-cargodeclarationfiles-export .cargodeclarationfiles-export');

        //var mailboxfile_total = this.api().data().length;
        //$("#btn-mailboxfile-new span").html(mailboxfile_total);

        $(".card.cargodeclarationfiles .sk-bounce").hide();
        $(".card.cargodeclarationfiles .card-datatable").show();           
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
  $(document).on('click', '.cargo-declaration-file-preview', function () {        
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
  
});
