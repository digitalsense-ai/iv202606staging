/**
 * Page Documents List
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var historyUrl = baseUrl + 'history/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });   

  // //Load Timeline
  // window.loadHistory = function loadHistory(vat_reg_id)
  // {
  //   $.ajax({      
  //     url: `${baseUrl}history/${vat_reg_id}`,
  //     type: 'GET',
  //     success: function (result) { 
  //       $("#navs-vatreturns-timeline-"+vat_reg_id).html("");  

  //       if(result['view'] != "")    
  //       {                   
  //         $("#navs-vatreturns-timeline-"+vat_reg_id).append(result['view']);
  //         importVatCommentEditor(vat_reg_id);  
  //       }
  //     },
  //     error: function (err) {
        
  //     }
  //   });
  // }

  //Download Files
  $(document).on('click', '.btn-download-files', function () {
      var btn_download_files = $(this);
      var data = btn_download_files.data();

      var file_id = data['fileid'];
          
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center position-absolute" style="left: 0; right: 0; top: 0; bottom: 0;">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>'; 
      
      btn_download_files.parent(".card-body").append(loadertext);
      
      $.ajax({      
        url: `${historyUrl}${file_id}/download`,
        type: 'GET',       
        success: function (data) {
                    
          btn_download_files.parent(".card-body").find(".sk-bounce").remove();
          
          window.open(data, '_blank');          
        },
        error: function (err) {
          console.log(err);     
        }
      });
  });
  
});