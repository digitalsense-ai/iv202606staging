/**
 * Assign Team User
 */

'use strict';
$(function () {
  const select2Company = $('.company-select');

  var assignTeamUser = document.getElementById('assignTeamUser');
  assignTeamUser.addEventListener('show.bs.modal', function (event) {  

    var company_id = event.relatedTarget.dataset.id;    
    
    // do something...
    if (select2Company.length) {
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
      select2Company.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: assignTeamUser,
        templateResult: renderAvatar,
        templateSelection: renderAvatar,
        placeholder: 'Choose Company',
        escapeMarkup: function (es) {
          return es;
        }
      })
      //.val(company_id).trigger("change");    
      .val(company_id).change();    
    }
  });

  // Checkbox
  $(document).on('click', '.chk-team-user', function () {
    //var numberChecked = $('input.chk-team-user:checked').length;
    //$(".selected-no").text("Selected " + numberChecked);

    selectedLength('.chk-team-user');
  });

  function select2CompanyChange(element)
  {
    var company_id = element.find('option:selected').data("id");

    if($("#assignTeamUserForm #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertBefore("#assignTeamUserForm #team-user-list");
    } 
    $("#assignTeamUserForm #team-user-list").hide();

    $.ajax({      
      url: `${baseUrl}company/assigned/${company_id}`,
      type: 'GET',
      success: function (result) {      
        //$(".selected-no").text("Selected " + result.length);

        $(".chk-team-user").prop('checked', false);
        $.each(result, function(key,value) {
          $(".chk-team-user[value='"+ value["team_user_id"] +"']").prop('checked', true);
        });
        selectedLength('.chk-team-user');

        $("#assignTeamUserForm #loader").remove();
        $("#assignTeamUserForm #team-user-list").show();
      },
      error: function (err) {
        console.log(err);
      }
    });
  }

   select2Company.on('change', function (e) {   
    select2CompanyChange($(this));
    // var company_id = $(this).find('option:selected').data("id");

    // if($("#assignTeamUserForm #loader").length == 0)
    // {
    //   var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
    //           '<div class="sk-bounce-dot"></div>' +
    //           '<div class="sk-bounce-dot"></div>' +
    //         '</div>';        
    //   $(loadertext).insertBefore("#assignTeamUserForm #team-user-list");
    // } 
    // $("#assignTeamUserForm #team-user-list").hide();

    // $.ajax({      
    //   url: `${baseUrl}company/assigned/${company_id}`,
    //   type: 'GET',
    //   success: function (result) {      
    //     //$(".selected-no").text("Selected " + result.length);

    //     $(".chk-team-user").prop('checked', false);
    //     $.each(result, function(key,value) {
    //       $(".chk-team-user[value='"+ value["team_user_id"] +"']").prop('checked', true);
    //     });
    //     selectedLength('.chk-team-user');

    //     $("#assignTeamUserForm #loader").remove();
    //     $("#assignTeamUserForm #team-user-list").show();
    //   },
    //   error: function (err) {
    //     console.log(err);
    //   }
    // });
    
   });  
});
