/**
 * Assign Client User
 */

'use strict';
$(function () {

  // Checkbox
  $(document).on('click', '.chk-client-user', function () {
    //var numberChecked = $('input.chk-client-user:checked').length;
    //$(".selected-no").text("Selected " + numberChecked);

    selectedLength('.chk-client-user');
  });

});
