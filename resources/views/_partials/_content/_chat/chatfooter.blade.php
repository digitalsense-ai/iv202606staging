<!-- Chat message form -->
<div class="chat-history-footer shadow-sm">
  <form class="form-send-message d-flex justify-content-between align-items-center" id="sendChatMessageForm" method="post"  enctype="multipart/form-data">
    @csrf  
    <input class="form-control message-input border-0 me-3 shadow-none" placeholder="Type your message here" name="message-data">
    <input type="hidden" name="_id" value="{{$user->user_id}}">
    <div class="message-actions d-flex align-items-center">
      <!-- <i class="speech-to-text bx bx-microphone bx-sm cursor-pointer"></i> -->
      <label for="attach-doc" class="form-label mb-0">
        <i class="bx bx-paperclip bx-sm cursor-pointer mx-3"></i>
        <input type="file" id="attach-doc" name="attach-doc" hidden>
      </label>
      <button type="submit" class="btn btn-primary d-flex send-msg-btn">
        <i class="bx bx-paper-plane me-md-1 me-0"></i>
        <span class="align-middle d-md-inline-block d-none">Send</span>
      </button>
    </div>
  </form>
</div>