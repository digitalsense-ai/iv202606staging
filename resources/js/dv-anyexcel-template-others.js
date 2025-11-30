/**
 * Any Excel Template OTHERS
 */

'use strict';

$(function () {  
  
  // Variable declaration
  var anyexcelTemplateUrl = baseUrl + 'anyexcel-template/'; 

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 

  //VAT REG. MAIN - ANYEXCEL TEMPLATE CREATION
  var //dt_anyexcel_template_table = $('.datatables-anyexceltemplate'),
      previousoption = $("#anyexcel_template option:selected").val();
  
  if (window.location.hash) {
      var hash = window.location.hash; 
      var accordionItem = $(hash.replace('#', '#btn-accordion-'));
      var vat_reg_id = hash.replace('#', ''); 

      // Check if it exists and contains a collapsible element
      var collapseEl = accordionItem.closest('.accordion-item').find('.accordion-collapse');

      if (collapseEl.length) {
          // Open the accordion item
          collapseEl.collapse('show');

          // Wait for accordion to expand before switching tab
          collapseEl.on('shown.bs.collapse', function () {              
            var tabSelector = '#vat-returns-main-' + vat_reg_id + ' #btn-documents-' + vat_reg_id;
            $(tabSelector).tab('show');  

            // After tab is shown, remove the hash from URL
            $(tabSelector).on('shown.bs.tab', function () {
                history.replaceState(null, null, window.location.pathname);
            });           
          });

          // Optional: scroll to the opened section
          $('html, body').animate({
              scrollTop: accordionItem.offset().top
          }, 500);
      }
  }

  $('.datatables-anyexceltemplate').each(function(index, dt_anyexcel_template_table) {
  // Excel Column templates datatable
  //if (dt_anyexcel_template_table.length) {    
    let $table = $(dt_anyexcel_template_table);

    let file_type = $table.data('file_type');
    let vat_reg_id = $table.data('vat_reg_id');

    //var dt_anyexcel_template = dt_anyexcel_template_table.DataTable({
    var dt_anyexcel_template = $table.DataTable({  
      data: anyexcel_template_datas,        
      processing: true,  
      autoWidth: false,   
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'name' },
        //{ data: 'version' },
        { data: 'columns' },
        //{ data: 'action' }      
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
          visible: false,
          //responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },        
        {
          // Title
          targets: 1,          
          width: "40%",   
          render: function (data, type, full, meta) {
            var $title = full['name'];
            
            //return '<span>' + $title + '</span>';   

             var buttons = "";
           
            //var vat_reg_id = (full['vatreg'].length > 0) ? full['vatreg'][0].id : 0;
            //var vat_reg_id = dt_anyexcel_template_table.data('vat_reg_id');
            //var file_type = dt_anyexcel_template_table.data('file_type');

            // let rowNode = dt_anyexcel_template.row(meta.row).node();
            // let vat_reg_id = $(rowNode).data('vat_reg_id');
            // let file_type = $(rowNode).data('file_type');

            buttons = `<div class="switches-stacked">
                  <label class="switch">
                      <input type="radio" class="switch-input" name="anyexcel_template_selection_` + file_type + `_` + vat_reg_id + `" value="`+full['id']+`" />
                      <span class="switch-toggle-slider">
                        <span class="switch-on"></span>
                        <span class="switch-off"></span>
                      </span>
                      <span class="switch-label">`+ $title +`</span>
                    </label></div>`;

            return buttons;         
          }
        },          
        {
          // Columns
          targets: 2,          
          width: "60%",   
          render: function (data, type, full, meta) {
            var $files = full['columns'];
            
            var parsed_files = JSON.parse($files);
          
            var htmlColumns = '<table class="table">';

            $.each(parsed_files, function (idxfile, sheet) {              
              var sheet_index = idxfile + 1;

              if(sheet.data_index)
              {
                htmlColumns += '<tr class="sheet-'+sheet_index+'-header">' +
                                  '<td colspan="2" class="border-0">' + 
                                    '<h5 class="m-0 mx-n3">Sheet ' + sheet_index + ':<br>' + 
                                      '<span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Sheet Name: </span>' +  
                                      '<span class="fs-6 fw-normal">' + sheet.sheet_name + '</span><br>' +   
                                      '<span class="ms-3 w-px-100 d-inline-block fs-6 text-decoration-underline fw-normal">Data Row: </span>' +  
                                      '<span class="fs-6 fw-normal">' + sheet.data_index + '</span><br>' +                                          
                                    ' </h5>' +
                                    '<span class="show-mapped-columns text-decoration-underline text-primary mx-n3" data-sheet_index="'+ sheet_index +'">Show</span>' +
                                    '</td>' +
                                '</tr>';

                var parsed_columns = sheet['columns'];
                $.each(parsed_columns, function (idx, item) { 

                  if(item.mapped_column)
                  {
                    var column_mapping = item.mapped_column.split(':');
                   
                    var reverse_rows = '';                
                    if(item.reverse)                 
                      reverse_rows = '<br><u>Reverse:</u><i class="bx bx-check"/>';

                    var formula_rows = '';                
                    if(item.formula)                 
                      formula_rows = '<br><u>Formula:</u> ' + item.formula;
                    
                    htmlColumns += '<tr class="sheet-'+sheet_index+' d-none"><td>' + item.column + ':</td><td>' + column_mapping[1] + reverse_rows + formula_rows + '</td></tr>';                                                
                  }
                }); 
              }            
            });
           
            return htmlColumns + '</table>';
          }
        }       
        // {
        //   // Actions
        //   targets: -1,        
        //   width: "15%",
        //   title: 'Actions',
        //   searchable: false,
        //   orderable: false,
        //   render: function (data, type, full, meta) {
        //     var buttons = "";

        //     var vatreg = full['vatreg'].length;
        //     var vat_reg_id = vatreg.id;

        //     // if(vatreg == 0)
        //     // {
        //     //   buttons +=  //'<button class="btn btn-sm btn-icon edit-anyexcel-template" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#excelColumnTemplateModal" title="Edit"><i class="bx bx-edit"></i></button>' +
        //     //               '<button class="btn btn-sm btn-icon delete-anyexcel-template" data-id="'+full['id']+'" title="Delete"><i class="bx bx-trash"></i></button>';
        //     // }
        //     // //else
        //     //   //buttons +=  '<button class="btn btn-sm btn-icon edit-anyexcel-template" data-id="'+full['id']+'" data-bs-toggle="modal" data-bs-target="#excelColumnTemplateModal" title="Clone"><i class="bx bx-copy-alt"></i></button>';                          
        //     buttons = `<div class="switches-stacked">
        //           <label class="switch">
        //               <input type="radio" class="switch-input" name="anyexcel_template_selection_vatreturn_` + vat_reg_id + `" value="`+full['id']+`" />
        //               <span class="switch-toggle-slider">
        //                 <span class="switch-on"></span>
        //                 <span class="switch-off"></span>
        //               </span>
        //               <span class="switch-label">`+full['name']+`</span>
        //             </label></div>`;

        //     return (             
        //       buttons              
        //     );
        //   }
        // }
      ],
      order: [[0, 'desc']],
      dom:
        '<"row mx-2"' +
        '<"col-md-2"<"me-3">>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"f>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6">' +
        '>',
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      },
      // // For responsive popup
      // responsive: {
      //   details: {
      //     display: $.fn.dataTable.Responsive.display.modal({
      //       header: function (row) {
      //         var data = row.data();
      //         return 'Details of ' + data['title'];
      //       }
      //     }),
      //     type: 'column',
      //     renderer: function (api, rowIdx, columns) {
      //       var data = $.map(columns, function (col, i) {
      //         return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
      //           ? '<tr data-dt-row="' +
      //               col.rowIndex +
      //               '" data-dt-column="' +
      //               col.columnIndex +
      //               '">' +
      //               '<td>' +
      //               col.title +
      //               ':' +
      //               '</td> ' +
      //               '<td>' +
      //               col.data +
      //               '</td>' +
      //               '</tr>'
      //           : '';
      //       }).join('');

      //       return data ? $('<table class="table"/><tbody />').append(data) : false;
      //     }
      //   }
      // },
      initComplete: function () {
        
        $(".sk-bounce").hide();
        $("#anyexceltemplate-card").show();        
      }
    });
  //}  
  });

  $('#vatreg_period').on('change', function() { 
    var vat_reg_id = $(this).val();

    if(vat_reg_id == '')
    {
      $("#formMailboxAssignAnyExcelTemplate input:radio.switch-input[name^='anyexcel_template_selection_vatreturn_']").attr('name', 'anyexcel_template_selection_vatreturn_0');
      $("#formMailboxAssignAnyExcelTemplate #anyexcel-template-selection").parent().hide();
    }
    else
    {
      $("#formMailboxAssignAnyExcelTemplate input:radio.switch-input[name^='anyexcel_template_selection_vatreturn_']").attr('name', 'anyexcel_template_selection_vatreturn_' + vat_reg_id);

      $("#formMailboxAssignAnyExcelTemplate #anyexcel-template-selection").parent().show();
    }
  });

  $('.show-mapped-columns').on('click', function() {
    var data = $(this).data();
    var sheet_index = data.sheet_index;

    if($(this).html() == 'Show')
    {
      $(this).closest('tbody').find('tr.sheet-' + sheet_index).removeClass('d-none');
      $(this).html('Hide');
    }
    else if($(this).html() == 'Hide')
    {
      $(this).closest('tbody').find('tr.sheet-'+ sheet_index +':not(.sheet-'+ sheet_index +'-header)').addClass('d-none');
      $(this).html('Show');
    }
  });

  $('#anyexcel_template, .form-select.anyexcel-template').on('focus', function () {
    previousoption = $(this).val();
  });

  $('#anyexcel_template, .form-select.anyexcel-template').on('change', function() { 
    var select_box = $(this); 
    
    if(select_box.val() == '')
    {
      // if(select_box.attr('id') == "excel_column_template")
      // {
      //   select_box.find('option[value=""]').removeAttr("selected");
      //   select_box.val(previousoption);
        
      //   window.location.href = `${anyexcelTemplateUrl}create`;  
      //   // var excelColumnTemplateModal = $('#excelColumnTemplateModal');
      //   // if (excelColumnTemplateModal.length)
      //   // {
      //   //   excelColumnTemplateModal.modal('show');

      //   //   clearItems();

      //   //   $("#column_mapping .sk-bounce").hide();
      //   //   $("#column_mapping .card-column-mapping").show();
      //   // }      
      // }
      // else
      // {  
        select_box.attr('disabled', 'disabled');

        Swal.fire({
          title: 'Are you sure?',
          text: "Creating a new template will redirect you to another page.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, continue!',
          customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {
          if (result.value) {
            window.location.href = `${anyexcelTemplateUrl}create`;  
          }
          else if (result.dismiss === Swal.DismissReason.cancel) {
            select_box.removeAttr('disabled');
            select_box.find('option[value=""]').removeAttr("selected");
            select_box.val(previousoption);

            Swal.fire({
              title: 'Cancelled',
              text: 'Template creation has been cancelled.',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });
      //} //COMPANY VIEW - ANYEXCEL TEMPLATE SELECTION
    }
    else if(select_box.val() == 'any-excel')
    {
      var data = select_box.data();
      var file_type = data.file_type;
      var vat_reg_id = data.vat_reg_id;

      var anyexcelTemplateSelectionModal = $('#anyexcelTemplateSelectionModal-' + file_type + '-' + vat_reg_id);
           
      if (anyexcelTemplateSelectionModal.length) 
        anyexcelTemplateSelectionModal.modal('show');
    }
  });
  //end VAT REG. MAIN - ANYEXCEL TEMPLATE CREATION

  //COMPANY VIEW - ANYEXCEL TEMPLATE SELECTION
  function enableSelectButton()
  {
    if($('#vatreg_period').length == 0)
    {
      if($("#anyexcel-template-selection .switch-input:checked").length == 0)
      {
        $(".btn-anyexcel-template-select").addClass('disabled');
        $(".btn-anyexcel-template-select").addClass('btn-danger');
        $(".btn-anyexcel-template-select").removeClass('btn-success');
        $(".btn-anyexcel-template-select").attr("disabled", "disabled"); 

        //$("button.btn-anyexcel-template-select:not(.vatcheck)").removeAttr('data-id');
      }
      else
      {
        $(".btn-anyexcel-template-select").removeClass('disabled');
        $(".btn-anyexcel-template-select").removeClass('btn-danger');
        $(".btn-anyexcel-template-select").addClass('btn-success');
        $(".btn-anyexcel-template-select").removeAttr("disabled", "disabled"); 

        //$("button.btn-anyexcel-template-select:not(.vatcheck)").attr('data-id', $("#anyexcel-template-selection .switch-input:checked").val());
      } 
    }  
    else
    {
      if($("#anyexcel-template-selection .switch-input:checked").length == 0)
      {
        $(".btn-mailbox-assign-anyexcel-template").addClass('disabled');
        $(".btn-mailbox-assign-anyexcel-template").addClass('btn-danger');
        $(".btn-mailbox-assign-anyexcel-template").removeClass('btn-success');
        $(".btn-mailbox-assign-anyexcel-template").attr("disabled", "disabled");        
      }
      else
      {
        $(".btn-mailbox-assign-anyexcel-template").removeClass('disabled');
        $(".btn-mailbox-assign-anyexcel-template").removeClass('btn-danger');
        $(".btn-mailbox-assign-anyexcel-template").addClass('btn-success');
        $(".btn-mailbox-assign-anyexcel-template").removeAttr("disabled", "disabled"); 
      } 
    }
  }
  enableSelectButton();  

  $(document).on('change', '#anyexcel-template-selection .switch-input', function () {     
    enableSelectButton();
  });

  $(document).on('click', 'button.btn-anyexcel-template-select:not(.vatcheck)', function () {

    var data = $(this).data();
    var vat_reg_id = data['vat_reg_id'];
    var file_type = data['file_type'];

    var template_id = $('input[name="anyexcel_template_selection_'+ file_type + '_' + vat_reg_id + '"]:checked').val();
   
    if(template_id != "")     
    {   
      var btn_anyexcel_template_select = $(this);
      btn_anyexcel_template_select.attr("disabled", "disabled");
      btn_anyexcel_template_select.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Selecting...');

      $("#btn-upload-"+ file_type +"-" + vat_reg_id).attr('disabled', 'disabled');
      $.ajax({
        type: 'PUT',       
        url: `${anyexcelTemplateUrl}${template_id}`,  
        data: {vat_reg_id: vat_reg_id, file_type: file_type},
        success: function(data){    console.log(data);
          var nav_id = "vatreturns";
          var tab_id = "documents";
          if(file_type == 'iranyexcel')
            nav_id = "importreconciliation";
          else if(file_type == 'vatcontrol')
            tab_id = "control";
          else if(file_type == 'ircontrol')
          {
            nav_id = "importreconciliation";
            tab_id = "control";
          }

          var anyexceltemplate = data['anyexceltemplate'];
          
          $(".navs-"+ nav_id +"-"+ tab_id +" .dropzone-file[data-vat_reg_id='"+ vat_reg_id +"'][data-file_type='"+ file_type +"']").each(function () {
            var original_file = $(this).find("input[name='original_file']");   

            if(template_id == 0)         
              original_file.val(0);
            else
              original_file.val(1);

           
            $('#anyexcel-template-' + file_type + '-' + vat_reg_id + ' optgroup[label="-- Select Template --"] option').each(function (i) {               
              if($(this).val() > 0)
                $(this).remove();
            });
            $('#anyexcel-template-' + file_type + '-' + vat_reg_id + ' optgroup[label="-- Select Template --"]').append('<option value="'+ anyexceltemplate.id +'" selected>'+ anyexceltemplate.name +'</option>');

            btn_anyexcel_template_select.html('<span class="align-middle">Select');  
            btn_anyexcel_template_select.removeAttr('disabled');  

            var anyexcelTemplateSelectionModal = $('#anyexcelTemplateSelectionModal-' + file_type + '-' + vat_reg_id);
           
            if (anyexcelTemplateSelectionModal.length) {
              anyexcelTemplateSelectionModal.modal('hide');
            }
          }); 
          $("#btn-upload-"+ file_type +"-" + vat_reg_id).removeAttr('disabled');         
        },
        error: function(jqXHR, textStatus, errorThrown){
          console.log('error: ' + textStatus);
        }
      });
    }
  });
  //end COMPANY VIEW - ANYEXCEL TEMPLATE SELECTION

  // //VAT REG. MAIN - ANYEXCEL TEMPLATE CREATION
  // var previousoption = $("#anyexcel_template option:selected").val();
  
  // $('#anyexcel_template').on('change', function() {  

  //   if($(this).val() == '')
  //   {      
  //     $(this).find('option[value=""]').removeAttr("selected");
  //     $(this).val(previousoption);
      
  //     var anyexcelTemplateModal = $('#anyexcelTemplateModal');
  //     if (anyexcelTemplateModal.length)
  //     {
  //       anyexcelTemplateModal.modal('show');

  //       clearItems();

  //       $("#column_mapping .sk-bounce").hide();
  //       $("#column_mapping .card-column-mapping").show();
  //     }
  //   }
  // });
  // //end VAT REG. MAIN - EXCEL TEMPLATE CREATION
});//();  