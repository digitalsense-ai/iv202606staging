/**
 * Assign Client
 */

'use strict';
$(function () {
  const select2TeamUser = $('.team-user-select');

  var assignVATReg = document.getElementById('assignVATReg');
  assignVATReg.addEventListener('show.bs.modal', function (event) {  

    var team_user_id = event.relatedTarget.dataset.id;
  
    // do something...
    if (select2TeamUser.length) {
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
      select2TeamUser.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: assignVATReg,
        templateResult: renderAvatar,
        templateSelection: renderAvatar,
        placeholder: 'Choose Team User',
        escapeMarkup: function (es) {
          return es;
        }
      })
      //.val(team_user_id).trigger("change");
      .val(team_user_id).change();
    }
  });

  // Checkbox
  $(document).on('click', '.chk-vatreg', function () {
    //var numberChecked = $('input.chk-vatreg:checked').length;
    //$(".selected-no").text("Selected " + numberChecked);

    selectedLength('.chk-vatreg');
  });

  // window.selectedLength = function selectedLength(elementName)
  // {
  //   //var numberChecked = $('input.chk-vatreg:checked').length;
  //   var numberChecked = $('input'+elementName+':checked').length;
  //   $(".selected-no").text("Selected " + numberChecked);
  // }

  function select2TeamUserChange(element)
  {
    var team_user_id = element.find('option:selected').data("id");
    
    if($("#assignVATRegForm #loader").length == 0)
    {
      var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertBefore("#assignVATRegForm #vat-reg-list");
    } 
    $("#assignVATRegForm #vat-reg-list").hide();

    $.ajax({      
      url: `${baseUrl}dv-user/assigned/${team_user_id}`,
      type: 'GET',
      success: function (result) { 
        //$(".selected-no").text("Selected " + result.length);

        $(".chk-vatreg").prop('checked', false);
        $.each(result['vat_regs'], function(key,value) {          
          $(".chk-vatreg[value='"+ value["vat_reg_main_id"] +"']").prop('checked', true);
        });

        $.each(result['vat_regs_main'], function(key,value) {          
          $(".chk-vatreg[value='"+ value["vat_reg_main_id"] +"']").prop('checked', true);
        });
        
        selectedLength('.chk-vatreg');

        $("#assignVATRegForm #loader").remove();
        $("#assignVATRegForm #vat-reg-list").show();
      },
      error: function (err) {
        console.log(err);
      }
    });
  }

   select2TeamUser.on('change', function (e) {  
      select2TeamUserChange($(this));
//     var team_user_id = $(this).find('option:selected').data("id");
// console.log(team_user_id);
//     if($("#assignVATRegForm #loader").length == 0)
//     {
//       var loadertext = '<div class="sk-bounce sk-primary sk-center" id="loader">' +
//               '<div class="sk-bounce-dot"></div>' +
//               '<div class="sk-bounce-dot"></div>' +
//             '</div>';        
//       $(loadertext).insertBefore("#assignVATRegForm #vat-reg-list");
//     } 
//     $("#assignVATRegForm #vat-reg-list").hide();

//     $.ajax({      
//       url: `${baseUrl}dv-user/assigned/${team_user_id}`,
//       type: 'GET',
//       success: function (result) { 
//         //$(".selected-no").text("Selected " + result.length);

//         $(".chk-vatreg").prop('checked', false);
//         $.each(result, function(key,value) {          
//           $(".chk-vatreg[value='"+ value["vat_reg_main_id"] +"']").prop('checked', true);
//         });
//         selectedLength('.chk-vatreg');

//         $("#assignVATRegForm #loader").remove();
//         $("#assignVATRegForm #vat-reg-list").show();
//       },
//       error: function (err) {
//         console.log(err);
//       }
//     });
    
   });  
});
