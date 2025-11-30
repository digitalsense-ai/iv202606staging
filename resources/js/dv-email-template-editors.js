/**
 * Form Editors
 */

'use strict';

(function () {

  // Full Toolbar
  // --------------------------------------------------------------------
  const fullToolbar = [
    [
      {
        font: []
      },
      {
        size: []
      }
    ],
    ['bold', 'italic', 'underline', 'strike'],
    [
      {
        color: []
      },
      {
        background: []
      }
    ],
    [
      {
        script: 'super'
      },
      {
        script: 'sub'
      }
    ],
    [
      {
        header: '1'
      },
      {
        header: '2'
      },
      'blockquote',
      'code-block'
    ],
    [
      {
        list: 'ordered'
      },
      {
        list: 'bullet'
      },
      {
        indent: '-1'
      },
      {
        indent: '+1'
      }
    ],
    // [{ direction: 'rtl' }],
    // ['link', 'image', 'video', 'formula'],
    // ['clean']
  ];

  $(document).on("shown.bs.collapse", "#accordionStyleEmailTemplate.accordion", function(event) {    
    var active_accordion = $(this).find('.accordion-item.active');   
    var btn_accordion = active_accordion.find('button.accordion-button');

    var email_type = btn_accordion.data('email_type');
    var iframe = $("#iframe-email-template-"+email_type);
    var attr = iframe.attr('src');
    if (typeof attr === 'undefined' || attr === false)
    {        
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
            '<div class="sk-bounce-dot"></div>' +
            '<div class="sk-bounce-dot"></div>' +
          '</div>';      
      $(loadertext).insertAfter('#email-template-preview-' + email_type  + ' .email-template-edit');  

      var iframesrc = `${baseUrl}email-preview/${email_type}`;
      iframe.attr("src", iframesrc);
     
      const iframeload = document.getElementById(iframe.attr('id'));
      iframeload.addEventListener("load", function() {
          console.log("Finish");
          $('#loader').remove();  
      });
    }    
  });


  //Email template Edit Click    
  $(document).on('click', 'span.email-template-edit', function () {  
    var data = $(this).data();
    var emailType = data['id'];

    $("#email-template-preview-"+emailType).hide();
    
    var fullEditorId = "#full-editor-content-"+emailType;
    
    $.ajax({      
      url: `${baseUrl}email-preview/${emailType}`,
      data: {'type': 'edit'},
      type: 'GET',      
      success: function (data) {
        
        $(fullEditorId).html(data);

        var fullEditor = new Quill(fullEditorId, {
          bounds: fullEditorId,
          placeholder: 'Type Something...',
          modules: {
            formula: true,
            toolbar: fullToolbar
          },
          theme: 'snow'
        });

        $("#full-editor-"+emailType).show();  
      },
      error: function (err) {
        console.log(err);     
      }
    });
        
  });

  //Email template Cancel Click    
  $(document).on('click', 'span.email-template-cancel', function () {  
    var data = $(this).data();
    var emailType = data['id'];
        
    $("#full-editor-"+emailType + " .ql-toolbar").remove();
    $("#full-editor-content-"+emailType).html('');
    
    $("#full-editor-"+emailType).hide();  
    $("#email-template-preview-"+emailType).show();
  });

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
          
          var emailType = form.id.replace('formEmailTemplate-', '');
          console.log(emailType);

          var btn_update = $(this).find(".email-template-update");          
          btn_update.attr('disabled', 'disabled');
          btn_update.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
              'Updating...');

          var fullEditor = $("#full-editor-content-"+emailType);  

          $("#text-quill-"+emailType).val(fullEditor.find(".ql-editor").html());

            $.ajax({
              data: $('#'+form.id).serialize(),
              url: `${baseUrl}email-template/${emailType}`,
              type: 'PUT',
              success: function (status) {
                
                // sweetalert
                Swal.fire({
                  icon: 'success',
                  title: `Successfully ${status}!`,
                  text: `Email Template ${status} Successfully.`,
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                }).then((result) => {
                  btn_update.removeAttr('disabled');               
                  btn_update.html("Update");
                  // Reload the Page
                  location.reload();    
                });
              },
              error: function (err) {
                    
              }
            });
        }

        form.classList.add('was-validated');
      },
      false
    );
  });

})();
