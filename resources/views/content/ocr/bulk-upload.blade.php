@extends('layouts/layoutMaster')

@section('title', 'Bulk Upload')

@section('vendor-style')
<!-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" /> -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/css/scroller.dataTables.min.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />

<!-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" /> -->
@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
  <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<!-- <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<!-- Flat Picker -->
<!-- <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script type="text/javascript" language="javascript" src="{{asset('assets/js/dataTables.scroller.min.js')}}"></script>
<script src="{{asset('assets/js/xlsx.min.js')}}"></script> -->

<!-- <script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script> -->

<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>

<!-- <script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script> -->
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>
<script type="text/javascript">
$(function () {
  window.analyzepdf_type = 'analyzepdf_bulkupload';
});
</script>
<script src="{{asset('js/dv-common.js')}}"></script>

<script src="{{asset('js/dv-analyze-pdf.js')}}"></script>
<!-- <script src="{{asset('js/dv-analyze-pdf-manual-input.js')}}"></script> -->
@endsection

@section('content')

    <h4 class="py-3 breadcrumb-wrapper mb-4 d-flex align-items-center gap-2">
        <span class="text-muted fw-light"><a href="{{ route('analyze.pdf.index')}}">{{ __('Document Flow') }}</a>/{{ __('Bulk Upload') }}</span>
    </h4>

    @if($environment === "local" || $environment === "live") 
    <div class="card my-4 card-ocr-bulk-upload">              
        <div class="card-body">
            <form method="post" action="{{ url('analyzepdf/bulk-upload') }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-ocr-bulk-upload" id="dropzone-ocr-bulk-upload"> 
                <!-- <input type="hidden" id="bulk_total_uploads" name="bulk_total_uploads"> -->
                <div class="col-md-2">
                    <div class="mb-3">                       
                      <label for="bulk_pdf_invoice_type" class="form-label">Invoice Type</label>
                      <select id="bulk_pdf_invoice_type" class="form-select" name="bulk_pdf_invoice_type" required>
                          <option value="">Select</option> 
                          <option value="com">Commercial Invoice</option>             
                          <option value="multi-invoices">Multi invoices in single PDF</option>
                          <option value="sales">Sales Invoice</option>
                      </select> 
                    </div>
                </div>

                <div class="dz-message needsclick">                    
                    Drop files here or click to upload
                    <span class="note needsclick">(The uploaded files are stored in <strong>One-Drive</strong>.)</span>
                </div>
            </form>
        </div>
    </div>
    @else
        <span class="text-danger">This feature is only available in the LIVE environment.</span>
    @endif

    {{-- Batch Progress UI --}}
    <div id="batch-progress" class="card mt-4 d-none">
        <div class="card-body">
            <h5 class="mb-3">Processing Documents</h5>

            <div class="progress" style="height: 24px;">
                <div
                    id="progress-bar"
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    style="width: 0%"
                >
                    0%
                </div>
            </div>

            <p id="progress-text" class="mt-2 mb-0 text-muted">
                Initializing…
            </p>
        </div>
    </div>

@endsection