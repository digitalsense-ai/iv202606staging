@extends('layouts/layoutMaster')

@section('title', 'OCR Manual Input')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />
@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<!-- Flat Picker -->
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script type="text/javascript" language="javascript" src="{{asset('assets/js/dataTables.scroller.min.js')}}"></script>
<script src="{{asset('assets/js/xlsx.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>

<script src="{{asset('js/dv-common.js')}}"></script>

<script src="{{asset('js/dv-analyze-pdf-manual-input.js')}}"></script>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h4 class="mb-1">Manual Input</h4>
    <p class="text-muted mb-0">Handle OCR error items.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span id="manualInputCounter" class="badge bg-label-primary fs-6">0 / 0</span>
    <a href="{{ route('analyze.pdf.index') }}" class="btn btn-label-secondary">Back to Overview</a>
  </div>
</div>

<div class="row manual-input-shell g-3">
  <div class="col-auto manual-input-list"> <!-- col-12 col-xl-3-->
    <div class="card h-100">
      <!-- <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Error Queue</h5>
          <small class="text-muted">Manual correction workload</small>
        </div>
        <button id="btnRefreshQueue" type="button" class="btn btn-sm btn-label-primary">
          <i class="bx bx-refresh"></i>
        </button>
      </div>
      -->

      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Error Queue</h5>
            <small class="text-muted">Manual correction workload</small>
          </div>

          <button id="btnRefreshQueue" type="button" class="btn btn-sm btn-label-primary">
            <i class="bx bx-refresh"></i>
          </button>
        </div>

        <div class="mt-2">
          <input
            type="text"
            id="manualQueueSearch"
            class="form-control w-100"
            placeholder="Search by file name, invoice no..."
          >
        </div>
      </div>
      <div class="list-group list-group-flush manual-input-queue" id="manualInputQueue"></div> 

    </div>
  </div>

  @php
    $ismanual =  true;
  @endphp
  @include('_partials/_content/_ocr/analyzepdf-form')
{{--
  <div class="col"> <!-- col-12 col-xl-9-->
    <div class="card h-100">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 pb-2">
        <div>
          <h5 id="manualInputTitle" class="mb-0">Select an item</h5>
          <small id="manualInputSubtitle" class="text-danger">PDF and correction fields will appear here.</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <button id="btnDeleteItem" type="button" class="btn btn-label-danger me-xl-3" disabled>
            <i class="bx bx-trash"></i> Delete
          </button>
          <div class="btn-group" role="group" aria-label="Queue navigation">
            <button id="btnPreviousItem" type="button" class="btn btn-label-primary" disabled>
              <i class="bx bx-chevron-left"></i> Previous
            </button>
            <button id="btnNextItem" type="button" class="btn btn-label-primary" disabled>
              Next <i class="bx bx-chevron-right"></i>
            </button>
          </div>
          
        </div>
      </div>

      <div class="card-body">
        <div id="manualInputEmpty" class="manual-input-empty text-muted">
          Select an error item from the queue.
        </div>
       
        <div id="manualInputDetail" class="row g-3 d-none manual-input-detail">    
          <div class="col-12 col-lg-9">
            <iframe id="manualPdfViewer" class="manual-input-pdf-frame"></iframe>
          </div>

          <div class="col-12 col-lg-3">
            <form id="manualInputForm" class="manual-input-form">
              @csrf
              <input type="hidden" name="id" id="manual_invoice_id">

              <div class="mb-2">
                <label class="form-label" for="invoice_type">Document Type</label>
                <select id="invoice_type" class="form-select" name="invoice_type" required>
                  <option value="">Select</option>
                  <option value="com">Commercial Invoice</option>
                  <option value="sales">Sales Invoice</option>
                </select>
                <!-- <small class="text-muted">Multiple-invoices is intentionally hidden on manual input.</small> -->
              </div>

              <div class="row">
                <div class="col-6">
                  <label class="form-label" for="client_no">Client No.</label>
                  <input type="text" id="client_no" class="form-control" name="client_no" required>
                </div>

                <div class="col-6">
                  <label class="form-label" for="client_name">Client Name</label>
                  <input type="text" id="client_name" class="form-control" name="client_name" readonly required>                  
                </div>
              </div>

              <div class="row">
                <div class="col-12 mb-2 lh-1">
                  <small id="clientLookupStatus" class="text-muted">Client name is populated from the PDF<!--client database-->.</small>
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-2">
                  <label class="form-label" for="invoice_date">Invoice Date</label>
                  <input type="text" id="invoice_date" class="form-control" name="invoice_date" placeholder="YYYY-MM-DD" required>
                </div>

                <div class="col-6 mb-2">
                  <label class="form-label" for="invoice_no">Invoice No.</label>
                  <input type="text" id="invoice_no" class="form-control" name="invoice_no" required>
                </div>
              </div>

              <div class="form-check mb-2">
                <input type="checkbox" id="credit_note" class="form-check-input" name="credit_note" value="1">
                <label class="form-check-label" for="credit_note">Credit Note</label>
              </div>

              <div class="row">
                <div class="col-6 mb-2">
                  <label class="form-label" for="currency">Currency</label>
                  <select id="currency" class="form-select" name="currency" required>
                    <option value="">Select</option>
                    <option value="CHF">CHF</option>
                    <option value="DKK">DKK</option>
                    <option value="EUR">EUR</option>
                    <option value="NOK">NOK</option>
                    <option value="GBP">GBP</option>
                    <option value="PLN">PLN</option>
                    <option value="SEK">SEK</option>
                    <option value="USD">USD</option>
                  </select>
                </div>
                <div class="col-6 mb-2">
                  <label class="form-label" for="exchange_currency">Exchange Currency</label>
                  <select id="exchange_currency" class="form-select" name="exchange_currency">
                    <option value="">Select</option>
                    <option value="CHF">CHF</option>
                    <option value="DKK">DKK</option>
                    <option value="EUR">EUR</option>
                    <option value="NOK">NOK</option>
                    <option value="GBP">GBP</option>
                    <option value="PLN">PLN</option>
                    <option value="SEK">SEK</option>
                    <option value="USD">USD</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-2">
                  <label class="form-label" for="vat_rate">VAT %</label>
                  <input type="text" id="vat_rate" class="form-control" name="vat_rate">
                </div>
                <div class="col-6 mb-2">
                  <label class="form-label" for="exchange_rate">Exchange Rate</label>
                  <input type="text" id="exchange_rate" class="form-control" name="exchange_rate">
                </div>
              </div>

              <div class="row">
                <div class="col-6">
                  <label class="form-label" for="net_amount">Net Amount</label>
                  <input type="text" id="net_amount" class="form-control" name="net_amount">
                </div>
                <div class="col-6">
                  <label class="form-label" for="exchange_net_amount">Exchange Net</label>
                  <input type="text" id="exchange_net_amount" class="form-control" name="exchange_net_amount">
                </div>
              </div>
              <div class="row">
                <div class="col-12 mb-2 lh-1">
                  <small class="text-muted">Fill in the actual net amount from the PDF. Do not use the amount after any adjustments.</small>
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-2">
                  <label class="form-label" for="vat_amount">VAT Amount</label>
                  <input type="text" id="vat_amount" class="form-control" name="vat_amount">
                </div>
                <div class="col-6 mb-2">
                  <label class="form-label" for="exchange_vat_amount">Exchange VAT</label>
                  <input type="text" id="exchange_vat_amount" class="form-control" name="exchange_vat_amount">
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-2">
                  <label class="form-label" for="total_amount">Total Amount</label>
                  <input type="text" id="total_amount" class="form-control" name="total_amount">
                </div>
                <div class="col-6 mb-2">
                  <label class="form-label" for="exchange_total_amount">Exchange Total</label>
                  <input type="text" id="exchange_total_amount" class="form-control" name="exchange_total_amount">
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label" for="sales_invoice_ref_no">Sales Invoice Ref. No.</label>
                <div class="form-salesinvoice-repeater manual-input-salesinvoice-repeater">
                  <button type="button" class="btn btn-label-warning mb-2 py-0" data-repeater-create>+Add</button>
                  <div data-repeater-list="sales-invoice" class="h-px-180 overflow-scroll-y">
                    <div data-repeater-item>
                      <div class="row">
                        <div class="mb-2 col-8 mb-0">
                          <input type="text" name="number" class="form-control sales-invoice-ref-no" placeholder="123456" />
                        </div>
                        <div class="mb-2 col-4 d-flex align-items-center mb-0">
                          <button type="button" class="btn btn-label-danger px-2" data-repeater-delete>
                            <i class="bx bx-x me-1"></i>
                            <span class="align-middle">Delete</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label" for="note">Note</label>
                <textarea id="note" class="form-control manual-input-note" name="note" placeholder="Add internal correction note"></textarea>
              </div>

              <div class="d-flex justify-content-between align-items-center gap-2">
                <button id="btnForceSubmit" type="button" class="btn btn-warning" disabled>Input</button>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-label-secondary" onclick="window.location.href='{{ route('analyze.pdf.index') }}'">Cancel</button>
                  <button id="btnSaveManualInput" type="submit" class="btn btn-primary" disabled>Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>--}}
</div>
@endsection