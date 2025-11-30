/**
 * Select Account No.'s
 */

'use strict';
$(function () {
  
  var selectClientVatNos = document.getElementById('selectClientVatNos');
  selectClientVatNos.addEventListener('show.bs.modal', function (event) {
    $('#cvr_nos').prop('checked', false);
    $(".selected-no").text("0");
    
    $("#selectClientVatNosForm .sk-bounce").show();

    $('#selectClientVatNosForm').find('h4').hide();
    $('#selectClientVatNosForm').find('ul').hide();

    var client_id = event.relatedTarget.dataset.client_id;
    
    $.ajax({      
      url: `${baseUrl}vatnos`,
      type: 'GET',
      data: {},
      success: function (result) { 
        console.log(result);
        $('#selectClientVatNosForm').find('h4').text(result.length + " VAT No.'s");

        var li = ''; 
        $('input.chk-vattno:checkbox').prop('checked', false);  

        $.each(result, function(key,value) {
         
          li += '<li class="d-inline-flex mb-3 w-50">' +   
                    '<div class="align-items-center">' +
                      '<div class="form-check form-check-inline">' +                       
                        '<input  name="chk_vatno[]" class="form-check-input chk-vattno" type="checkbox"  value="'+ value['id'] +'" data-vat_no="'+ value['vatno'] +'" data-client_name="'+ value['client_name'] +'"  />' +
                      '</div>' +
                    '</div>' +                 
                    '<div class="justify-content-between flex-grow-1">' +
                      '<div class="me-2">' +
                        '<p class="mb-0">'+ value['client_name']+'</p>' +
                        '<p class="mb-0 text-muted">'+ value['vatno'] +'</p>' +
                      '</div>' +
                    '</div>' +                    
                  '</li>';
        });

        $('#selectClientVatNosForm').find('ul').empty();
        $('#selectClientVatNosForm').find('ul').append(li);

        $('#selectClientVatNosForm').find('h4').show();
        $('#selectClientVatNosForm').find('ul').show();
        $("#selectClientVatNosForm .sk-bounce").hide();
      },
      error: function (err) {
        console.log(err);
      }
    });
  }); 
  
  $("#cvr_nos").click(function () {
    $(".chk-vattno").prop('checked', $(this).prop('checked'));
    selectedLength('.chk-vattno');
  });
  
  // Checkbox
  $(document).on('click', '.chk-vattno', function () {   
    selectedLength('.chk-vattno');
  });

});
