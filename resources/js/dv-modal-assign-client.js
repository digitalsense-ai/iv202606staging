/**
 * Assign Client
 */

'use strict';
$(function () {
  const select2ClientUser = $('.client-user-select');

  var assignClient = document.getElementById('assignClient');
  assignClient.addEventListener('show.bs.modal', function (event) {  

    var client_user_id = event.relatedTarget.dataset.id;
   
    // do something...
    if (select2ClientUser.length) {
      function renderAvatar(option) {
        if (!option.id) {
          return option.text;
        }
        var optionEle =
          '<div class="d-flex align-items-center">' +
          '<div class="avatar avatar-xs me-2 d-flex">' +
          '<span class="avatar-initial rounded-circle bg-label-">'+ $(option.element).data('image') +'</span>' +          
          '</div>' +
          '<div class="name">' +
          $(option.element).data('name') +
          '</div>' +
          '</div>';
        return optionEle;
      }      
      select2ClientUser.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: assignClient,
        templateResult: renderAvatar,
        templateSelection: renderAvatar,
        placeholder: 'Choose Client User',
        escapeMarkup: function (es) {
          return es;
        }
      })
      //.val(client_user_id).trigger("change");
      .val(client_user_id).change();
    }
  });

  // Checkbox
  $(document).on('click', '.chk-client', function () {
    //var numberChecked = $('input.chk-client:checked').length;
    //$(".selected-no").text("Selected " + numberChecked);

    selectedLength('.chk-client');
  });

  function select2ClientUserChange(element)
  {
    var client_user_id = element.find('option:selected').data("id");

    if($("#assignClientForm #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertBefore("#assignClientForm #client-list");
    } 
    $("#assignClientForm #client-list").hide();

    $.ajax({      
      url: `${baseUrl}client/assigned/${client_user_id}`,
      type: 'GET',
      success: function (result) { 
        //$(".selected-no").text("Selected " + result.length);

        $(".chk-client").prop('checked', false);
        $.each(result, function(key,value) {
          $(".chk-client[value='"+ value["client_id"] +"']").prop('checked', true);
        });
        selectedLength('.chk-client');

        $("#assignClientForm #loader").remove();
        $("#assignClientForm #client-list").show();
      },
      error: function (err) {
        console.log(err);
      }
    });
  }

   select2ClientUser.on('change', function (e) { 
    select2ClientUserChange($(this));  
    // var client_user_id = $(this).find('option:selected').data("id");

    // if($("#assignClientForm #loader").length == 0)
    // {
    //   var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
    //           '<div class="sk-bounce-dot"></div>' +
    //           '<div class="sk-bounce-dot"></div>' +
    //         '</div>';        
    //   $(loadertext).insertBefore("#assignClientForm #client-list");
    // } 
    // $("#assignClientForm #client-list").hide();

    // $.ajax({      
    //   url: `${baseUrl}client/assigned/${client_user_id}`,
    //   type: 'GET',
    //   success: function (result) { 
    //     //$(".selected-no").text("Selected " + result.length);

    //     $(".chk-client").prop('checked', false);
    //     $.each(result, function(key,value) {
    //       $(".chk-client[value='"+ value["client_id"] +"']").prop('checked', true);
    //     });
    //     selectedLength('.chk-client');

    //     $("#assignClientForm #loader").remove();
    //     $("#assignClientForm #client-list").show();
    //   },
    //   error: function (err) {
    //     console.log(err);
    //   }
    // });
    
   });  
});
