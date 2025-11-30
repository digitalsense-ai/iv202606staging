/**
 *  Page auth register multi-steps
 */

'use strict';

// Select2 (jquery)
$(function () {
  var select2 = $('.select2');

  // select2
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>');
      $this.select2({
        placeholder: 'Select an country',
        dropdownParent: $this.parent()
      });
    });
  }
});

// // Bootstrap Datepicker
// // --------------------------------------------------------------------
// var bsDatepickerStartDate = $('#multiStepsStartDate'),
//     bsDatepickerEndDate = $('#multiStepsEndDate');

//     // Basic
// if (bsDatepickerStartDate.length) {
//   bsDatepickerStartDate.datepicker({
//     format: "dd-mm-yyyy",
//     todayHighlight: true,
//     autoclose: true,
//     orientation: 'auto left'     
//   });
// }

// // Basic
// if (bsDatepickerEndDate.length) {
//   bsDatepickerEndDate.datepicker({
//     format: "dd-mm-yyyy",
//     todayHighlight: true,
//     autoclose: true,
//     orientation: 'auto left'     
//   });
// }  

// Multi Steps Validation
// --------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {

    var clientUrl = baseUrl + 'register';  
    let isFormValid = false;
    const stepsValidation = document.querySelector('#multiStepsValidation');
    if (typeof stepsValidation !== undefined && stepsValidation !== null) {

      // Multi Steps form
      const stepsValidationForm = stepsValidation.querySelector('#multiStepsRegForm');

      // Form steps
      const stepsValidationFormStep1 = stepsValidationForm.querySelector('#userDetailsValidation');
      const stepsValidationFormStep2 = stepsValidationForm.querySelector('#companyInfoValidation');
      const stepsValidationFormStep3 = stepsValidationForm.querySelector('#otherDetailsValidation');

      // Multi steps next prev button
      const stepsValidationNext = [].slice.call(stepsValidationForm.querySelectorAll('.btn-next'));
      const stepsValidationPrev = [].slice.call(stepsValidationForm.querySelectorAll('.btn-prev'));
      
      let validationStepper = new Stepper(stepsValidation, {
        linear: true
      });
      
      // User details
      const multiSteps1 = FormValidation.formValidation(stepsValidationFormStep1, {
        fields: {
          multiStepsFirstname: {
            validators: {
              notEmpty: {
                message: 'Please enter firstname'
              }
              // stringLength: {
              //   min: 6,
              //   max: 30,
              //   message: 'The name must be more than 6 and less than 30 characters long'
              // },
              // regexp: {
              //   regexp: /^[a-zA-Z0-9 ]+$/,
              //   message: 'The name can only consist of alphabetical, number and space'
              // }
            }
          },
          multiStepsLastname: {
            validators: {
              notEmpty: {
                message: 'Please enter lastname'
              }
              // stringLength: {
              //   min: 6,
              //   max: 30,
              //   message: 'The name must be more than 6 and less than 30 characters long'
              // },
              // regexp: {
              //   regexp: /^[a-zA-Z0-9 ]+$/,
              //   message: 'The name can only consist of alphabetical, number and space'
              // }
            }
          },
          multiStepsUserEmail: {
            validators: {
              notEmpty: {
                message: 'Please enter email address'
              },
              emailAddress: {
                message: 'The value is not a valid email address'
              }
            }
          },
          multiStepsPass: {
            validators: {
              notEmpty: {
                message: 'Please enter password'
              }
            }
          },
          multiStepsConfirmPass: {
            validators: {
              notEmpty: {
                message: 'Confirm Password is required'
              },
              identical: {
                compare: function () {
                  return stepsValidationFormStep1.querySelector('[name="multiStepsPass"]').value;
                },
                message: 'The password and its confirm are not the same'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            // Use this for enabling/changing valid/invalid class
            // eleInvalidClass: '',
            eleValidClass: '',
            rowSelector: '.col-sm-6'
          }),
          autoFocus: new FormValidation.plugins.AutoFocus(),
          submitButton: new FormValidation.plugins.SubmitButton()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      }).on('core.form.valid', function () {console.log("step 1 - valid");
        // Jump to the next step when all fields in the current step are valid
        validationStepper.next();        
      }).on('core.form.invalid', function () {
        // if fields are invalid
        isFormValid = false;
      });
      
      // Company info
      const multiSteps2 = FormValidation.formValidation(stepsValidationFormStep2, {
        fields: {  
          multiStepsVatNo: {
            validators: {
              notEmpty: {
                message: 'Please enter company registration number'
              }
            }
          },       
          multiStepsCompanyName: {
            validators: {
              notEmpty: {
                message: 'Please enter company name'
              }
            }
          },           
          multiStepsAddress: {
            validators: {
              notEmpty: {
                message: 'Please enter address'
              }
            }
          },         
          multiStepsZipcode: {
            validators: {
              notEmpty: {
                message: 'Please enter zipcode'
              }
            }
          },
          multiStepsCity: {
            validators: {
              notEmpty: {
                message: 'Please enter city'
              }
            }
          },
          multiStepsCompEmail: {
            validators: {
              notEmpty: {
                message: 'Please enter email address'
              },
              emailAddress: {
                message: 'The value is not a valid email address'
              }
            }
          }          
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            // Use this for enabling/changing valid/invalid class
            // eleInvalidClass: '',
            eleValidClass: '',
            rowSelector: function (field, ele) {
              // field is the field name
              //return '.form-floating';              
              switch (field) {
                case 'multiStepsVatNo':
                  return '.input-group';                
                default:
                  return '.form-floating';
              }
            }
          }),
          autoFocus: new FormValidation.plugins.AutoFocus(),
          submitButton: new FormValidation.plugins.SubmitButton()
        }
      }).on('core.form.valid', function () {console.log("step 2 - valid");
        // Jump to the next step when all fields in the current step are valid
        validationStepper.next();        
      }).on('core.form.invalid', function () {
        // if fields are invalid
        isFormValid = false;
      });
     
      // Legal Rep
      const multiSteps3 = FormValidation.formValidation(stepsValidationFormStep3, {
        fields: {
          multiStepsRepFirstName: {
            validators: {
              notEmpty: {
                message: 'Please enter first name'
              }
            }
          },
           multiStepsSurname: {
            validators: {
              notEmpty: {
                message: 'Please enter surname'
              }
            }
          },
           multiStepsRepAddress: {
            validators: {
              notEmpty: {
                message: 'Please enter address'
              }
            }
          },
           multiStepsRepZipcode: {
            validators: {
              notEmpty: {
                message: 'Please enter zipcode'
              }
            }
          },
           multiStepsRepCity: {
            validators: {
              notEmpty: {
                message: 'Please enter city'
              }
            }
          },
           multiStepsRiskAssessment: {
            validators: {
              notEmpty: {
                message: 'Please select value'
              }
            }
          },
           multiStepsUseTrademark: {
            validators: {
              notEmpty: {
                message: 'Please select value'
              }
            }
          }
        }, 
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            // Use this for enabling/changing valid/invalid class
            // eleInvalidClass: '',
            eleValidClass: '',
            rowSelector: function (field, ele) {
              // field is the field name              
              return '.col-sm-6';
            }
          }),
          autoFocus: new FormValidation.plugins.AutoFocus(),
          submitButton: new FormValidation.plugins.SubmitButton()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      }).on('core.form.valid', function () {console.log("step 3 - valid");      
        // You can submit the form
        // stepsValidationForm.submit()
        // or send the form data to server via an Ajax request
        // To make the demo simple, I just placed an alert
        //alert('Submitted..!!');  
        var btn_submit = $("#multiStepsRegForm").find('button.btn-submit');
        btn_submit.attr('disabled', 'disabled');
        btn_submit.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Submitting...');        

        // adding user when form successfully validate
         $.ajax({
          data: $('#multiStepsRegForm').serialize(),
          url: `${baseUrl}register`,
          type: 'POST',
          success: function (result) {                    
            var myDropzone = Dropzone.forElement(".dropzone");
           
            if(myDropzone.files.length > 0)
            {              
              var client_id = result.client_id;
              $("#dropzone-multi-register-0").attr('data-clientid', client_id);
              $("#dropzone-multi-register-0").attr('action', `${clientUrl}/files/` + client_id);
                              
              myDropzone.processQueue();
            }
            else
            {                    
              btn_submit.html('Submitted');    

               var status = result.message;
              // sweetalert
              Swal.fire({
                icon: 'success',
                title: `Successfully ${status}!`,
                text: `User and company ${status} Successfully.`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              }).then(function() { 
               window.location.href =  `${baseUrl}signin`;
              });
            }
          },
          error: function (xhr, status, error) {
           var err = JSON.parse(xhr.responseText);        
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
      }).on('core.form.invalid', function () {
        // if fields are invalid
        isFormValid = false;
      });
      
      stepsValidationNext.forEach(item => {
        item.addEventListener('click', event => {
          // When click the Next button, we will validate the current step         
          switch (validationStepper._currentIndex) {
            case 0:
              multiSteps1.validate();
              break;

            case 1:
              multiSteps2.validate();
              break;

            case 2:
              multiSteps3.validate();
              break;

            default:
              break;
          }
        });
      });

      stepsValidationPrev.forEach(item => {
        item.addEventListener('click', event => {         
          switch (validationStepper._currentIndex) {
            case 2:
              validationStepper.previous();
              break;

            case 1:
              validationStepper.previous();
              break;

            case 0:

            default:
              break;
          }
        });
      });
    }
  })();

  let iti;
  if(iti != null){
    iti.destroy();
  }

  function initializeTel() {
    var input = document.getElementById("multiStepsUserTelephone");
    var input1 = document.getElementById("multiStepsTelephone");
    if(input)
    {
      iti = intlTelInput(input, {
          initialCountry: $("#multiStepsState").val(),
          preferredCountries: ["dk"],
          separateDialCode: true,
          utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"    
      });
    }
    
    if(input1)
      {
      iti = intlTelInput(input1, {
          initialCountry: $("#multiStepsState").val(),
          preferredCountries: ["dk"],
          separateDialCode: true,
          utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"    
      });
    }
  }

  initializeTel();
});
