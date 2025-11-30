/**
 * Page Import VAT Tasks List
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var importVatUrl = baseUrl + 'import-vat/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  //Submit
  $(document).on("submit", ".formImportVat", function(event)
  {
      event.preventDefault();

      var data = $(this).data();  
      var vat_reg_id = data['vat_reg_id']; 
      var client_id = data['client_id']; 
      var import_vat_file_id = data['import_vat_file_id']; 

      var btn_submit = $(this).find("button.importvat-save");
      btn_submit.attr("disabled", "disabled");
      btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
          'Saving...');
      
      $.ajax({
        data: $('#formImportVat-'+vat_reg_id+'-'+import_vat_file_id).serialize(),
        url: `${importVatUrl}${import_vat_file_id}`,
        type: 'POST',
        success: function (response) {

          if(response.status >= 200 && response.status <= 299)   
          {   
            btn_submit.html("Saved");  

            //loadVATReturnsImportVATFiles(client_id, vat_reg_id);
            loadVATReturnsFileDocs('ivf', 'Import VAT', client_id, vat_reg_id); 
            //loadVATReturnsSubmittingFields(vat_reg_id);

            // sweetalert
            Swal.fire({
              icon: 'success',
              title: `Import VAT saved!`,
              text: `Import VAT was successfully saved.`,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });            
          }
          else if(response.status >= 400 && response.status <= 599)  
          {            
            Swal.fire({
              title: 'Error!',
              text: response.message.message,
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });  
          }  
          
          $('#formImportVat-'+vat_reg_id+'-'+import_vat_file_id).trigger('reset');
          
        },
        error: function (xhr, status, error) {
          var err = JSON.parse(xhr.responseText);

          btn_submit.html("Save");      
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

  //Import VAT Change
  $(document).on('change keyup paste', '.formImportVat input[type="text"]', function () {       
      //Save Button
      var btn_save = $(".formImportVat button.importvat-save");
      btn_save.removeAttr("disabled");
      btn_save.html('Save');
  });
});