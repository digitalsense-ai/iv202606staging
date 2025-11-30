<!-- Assign VAT Reg. Modal -->
<div class="modal fade" id="selectVATAccountNos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      
      <div class="modal-body px-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-start">
          <h3 class="mb-2">Select VAT Account No.'s</h3>
          <p>Select VAT Account No.'s for search of Invoices</p>
        </div>
      </div>
      <form class="assign-new-vat-reg pt-0" id="selectVATAccountNosForm"> 
        <!-- Bounce -->
        <div class="sk-bounce sk-primary sk-center">
          <div class="sk-bounce-dot"></div>
          <div class="sk-bounce-dot"></div>
        </div> 
                 
        <h4 class="mb-4 pb-2">{{ count($accountnos) }} VAT Account No.'s</h4>      
        <ul class="p-0 m-0">
                 
        </ul> 
      </form>     
      <div class="d-flex align-items-center mt-4">
        <i class="bx bx-user me-2"></i>
        <div class="d-flex justify-content-between flex-grow-1 align-items-center">
          <h6 class="mb-0 selected-no">Selected 0</h6>
          <button class="btn btn-primary assign-vatreg">Select</button>
        </div>
      </div>      
    </div>
  </div>
</div>
<!--/ Assign VAT Reg. Modal -->
