/**
 * Page Chat Talk
 */

'use strict';
// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  var chatTalkUrl = baseUrl + 'chattalk/'
    ;
   
  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
  
  var chatHistoryBody = document.querySelector('.chat-history-body');

  // Scroll to bottom function
  function scrollToBottom() {
    chatHistoryBody.scrollTo(0, chatHistoryBody.scrollHeight);
  }

  //Load Conversation
  function loadConversations(user_id)
  {        
    if($("#loader").length == 0)
    {
      var loadertext = '<!-- Bounce -->' +
            '<div class="sk-bounce sk-primary sk-center" id="loader">' +
              '<div class="sk-bounce-dot"></div>' +
              '<div class="sk-bounce-dot"></div>' +
            '</div>';        
      $(loadertext).insertAfter(".chat-history-wrapper");        
    }    

    $.ajax({
      data: {user_id: user_id},  
      url: `${chatTalkUrl}${user_id}`,
      type: 'GET',
      success: function (result) { 
        $("#loader").remove();    
        
        $(".chat-history-header").remove();        
        $(result['chat_header']).insertBefore(".chat-history-body");

        $(".chat-history-body ul.chat-history").html("");
        if(result['chat_body'] != "")                        
          $(".chat-history-body ul.chat-history").append(result['chat_body']);  

        $(".chat-history-footer").remove();        
        $(result['chat_footer']).insertAfter(".chat-history-body");

        $("#app-chat-sidebar-right").html("");        
        $("#app-chat-sidebar-right").append(result['right_sidebar']); 

        scrollToBottom();
      },
      error: function(jqXHR, textStatus, errorThrown){
        console.log('error: ' + textStatus);
      }
    });
  }


  //Click Contact list
  $(document).on('click', '.chat-list-item', function () {
      var data = $(this).data();     
      var user_id = data['user_id']; 

      loadConversations(user_id);      
  });
 
  $(document).on("click",".chat-history-header img.rightsidebar",function(ev){
      ev.preventDefault();
      var modalToOpen = $(this).attr("data-target");
      $(modalToOpen).addClass('show');
  });

  $(document).on("click","#app-chat-sidebar-right .close-sidebar",function(ev){
      ev.preventDefault();
      var modalToOpen = $(this).attr("data-target");
      $(modalToOpen).removeClass('show');
  });

  //File Attachment
  $(document).on("change","#attach-doc",function(ev){
      $('.form-send-message').submit();
  });

  $(document).on("submit", ".form-send-message", function(event)
  {
      event.preventDefault();

      var formId = $("#sendChatMessageForm");
      var formData = new FormData(this);

      $.ajax({
        url: `${baseUrl}ajax/chattalk/send`,
        type: 'POST',
        dataType: "JSON",
        data: formData,
        processData: false,
        contentType: false,
        success: function (result) {
          if (result.status == 'success') 
          {
            $('ul.chat-history').append(result.html);  

            formId[0].reset();

            scrollToBottom();      
          }
          
        },
        error: function (error) {
          console.log(error);
        }
      }); 
  });


});