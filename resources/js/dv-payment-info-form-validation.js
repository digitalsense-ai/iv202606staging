/**
 * Page Payment Info List
 */

'use strict';

$(function () {  

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const bsValidationForms = document.querySelectorAll('.needs-validation');

  // Loop over them and prevent submission
  Array.prototype.slice.call(bsValidationForms).forEach(function (form) {
    
    form.addEventListener(
      'submit',
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        } else {
          event.preventDefault();
          
          var frmId = form.getAttribute("id")
          // Submit your form         
          // adding or updating user when form successfully validate
          $.ajax({
            data: $('#'+frmId).serialize(),
            url: `${baseUrl}payment-info`,
            type: 'POST',
            success: function (status) {              
              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `Payment Info ${status} Successfully.`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              }).then(function() {
                  // Redirect the user
                  window.location.href = `${baseUrl}payment-info`;                
              });
            },
            error: function (data, textStatus, errorThrown) {              
              Swal.fire({
                title: 'Error in Entry!',               
                text: textStatus,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          });
        }

        form.classList.add('was-validated');
      },
      false
    );
  });
  
});
