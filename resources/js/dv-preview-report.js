/**
 * Page Preview Report
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var previewReportUrl = baseUrl + 'preview-report/';
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  //Export to PDF  
  $(document).on('click', '.btn-export-pdf-previewreport', function () {
      var btn_export_pdf_previewreport = $(this);
      btn_export_pdf_previewreport.attr('disabled', 'disabled');
      btn_export_pdf_previewreport.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Exporting...');

      var vat_reg_id = btn_export_pdf_previewreport.data('vat_reg_id'); 
     
      $.ajax({        
        //data: {box1: box1, box2: box2, box3: box3, box4: box4, box5: box5, box6: box6, box7: box7, box8: box8, box9: box9},  
        url: `${previewReportUrl}${vat_reg_id}/export`,
        type: 'POST',
        xhrFields: {
          responseType: 'blob'      
        },
        success: function (data) {
          btn_export_pdf_previewreport.removeAttr('disabled'); 
          btn_export_pdf_previewreport.html('<i class="bx bx-up-arrow-circle me-1"></i>' +
                                  '<span class="align-middle">Export to PDF</span>');

          var blob=new Blob([data]);      
          var link=document.createElement('a');
          link.href=window.URL.createObjectURL(blob);
          link.download="previewreport.pdf";
          link.click();
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });

  $("#confirm-vatreturns-footer div.card").clone().appendTo('#load-previewreport-vatreturns-footer');  
  $("#load-previewreport-vatreturns-footer div.card").addClass('w-75 float-end');  
});