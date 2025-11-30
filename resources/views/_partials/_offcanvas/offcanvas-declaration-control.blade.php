<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDeclarationControl" aria-labelledby="offcanvasDeclarationControlLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasDeclarationControlLabel" class="offcanvas-title">Declaration Control</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body my-auto mx-0 flex-grow-0 py-0">
    <form method="post" class="form-declaration-control">  
      @csrf
      <input type="hidden" id="user-id" name="user_id" value="{{ $authUser->user_id }}">
      <div class="row">
        <div class="col-md-12 declaration-control">
        {{--                    
          @foreach ($declarations as $declaration_key => $declaration_all)         
            <ul class="p-0 m-0 {{ ($declaration_key == 'declaration_first_datas') ? 'first' : 'second' }}">
            @foreach ($declaration_all as $key => $declaration)
              @if($key == "net_amount_commercial_invoice" || $key == "net_amount_sales_invoice" || $key == "vat_amount_sales_invoice" || $key == "sales_vat_vs_import_vat")

                @php
                  $label_name = "";
                  $icon_class_name = "bx-chevron-up text-success";
                @endphp
                @if($key == "net_amount_commercial_invoice")
                  @php
                    $label_name = "Net Amount Commercial Invoice";
                  @endphp
                @elseif($key == "net_amount_sales_invoice")
                  @php
                    $label_name = "Net Amount Sales Invoice";
                  @endphp 
                @elseif($key == "vat_amount_sales_invoice")
                  @php
                    $label_name = "VAT Amount Sales Invoice";
                  @endphp
                @elseif($key == "sales_vat_vs_import_vat")
                  @php
                    $label_name = "Sales VAT vs Import VAT";
                    $icon_class_name = "bx-chevron-down text-danger";
                  @endphp      
                @endif

                <li class="d-flex mb-4 pb-1">                    
                  <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                      <h6 class="mb-0">{{ $label_name }}</h6>                            
                    </div>
                    <div class="user-progress">
                      <small class="fw-medium">{{ $declaration }}</small><i class='bx {{ $icon_class_name }} ms-1'></i>
                    </div>
                  </div>
                </li>
              @endif  
            @endforeach
            </ul>
          @endforeach                               
          --}}
        </div><!--/ col -->
      </div>
    
      <button type="submit" class="btn btn-primary mb-2 w-100 btn-save-declaration-control disabled" disabled="disabled">Save</button>
      <button type="button" class="btn btn-label-secondary d-grid w-100" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>