@extends('layouts/layoutMaster')

@section('title', 'Declaration - New (OCR)')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/spinkit/spinkit.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/katex.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}">
@endsection

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/quill/katex.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/quill/quill.js') }}"></script>
<script src="{{ asset('assets/js/xlsx.min.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('js/dv-common.js') }}"></script>
<script>
  $(function () {
    window.declarationpage = 'declaration-new-ocr';
    
    window.declaration_first_datas = [];
    window.declaration_second_datas = [];
    window.declaration_third_datas = [];

    const declarationResult = window.drawDtTable(
      { declarations: @json($declarations) },
      'declaration-new-ocr'
    );
    window.declaration_datas = declarationResult.declaration_datas;
    window.declaration_first_datas = declarationResult.declaration_datas;

    window.clientId = @json($client_id);    
    window.vatRegId = @json($vat_reg_id); 
  });
</script>
<script src="{{ asset('js/dv-declarations.js') }}"></script>
<script src="{{ asset('js/dv-declarations-new-ocr.js') }}"></script>
<script src="{{ asset('js/dv-declaration-comment.js') }}"></script>
@endsection

@section('content')
@php
  $client = $declarations->client;
  $vatregmain = $declarations->vatregmain;
  $orgNo = $vatregmain->country === 'NO'
    ? $vatregmain->org_no
    : str_replace(['.', '-'], '', $vatregmain->vat_no);
  $periodStart = \Carbon\Carbon::parse($declarations->service_start);
  $periodEnd = $periodStart->copy()->addMonths(max(0, $declarations->frequency - 1))->endOfMonth();
@endphp

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">
    <a href="{{ url('company/' . $client->client_id) }}">{{ $client->client_name }}</a> /
  </span>
  {{ $periodStart->format('F') }}-{{ $periodEnd->format('F Y') }} {{ $declarations->country }} {{ $declarations->general_periods }}
</h4>

<!-- Toast with Placements -->
<div class="bs-toast toast toast-placement m-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
  <div class="toast-header">
    <img src="{{asset('assets/img/avatars/1.png')}}" class="d-block w-px-20 h-auto rounded me-2" alt="" />
    <div class="me-auto fw-medium toast-header-title">Refresh Data</div>
    <small>11 mins ago</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
    Hello, world! This is a toast message.
  </div>
</div>
<!-- Toast with Placements -->

<div class="card declarations-new-ocr">
  <div class="card-header border-bottom">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h5 class="mb-1">Declaration period</h5>
        <span class="text-muted">{{ $periodStart->format('d-m-Y') }} – {{ $periodEnd->format('d-m-Y') }} · {{ $orgNo }}</span>
      </div>
      <div class="d-flex align-items-center gap-2 dt-declaration-export-new-ocr">
        <button type="button" class="btn btn-primary js-refresh-declarations"><i class="bx bx-refresh me-2"></i>Refresh Data</button>
        <div class="dropdown">
          <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="bx bx-export me-2"></i>Export</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item js-export-table" data-export="print"><i class="bx bx-printer me-2"></i>Print</button></li>
            <li><button class="dropdown-item js-export-table" data-export="csv"><i class="bx bx-file me-2"></i>Csv</button></li>
            <li><button class="dropdown-item js-export-table" data-export-method="exportToExcelPeriodOverviewNew"><i class="bx bxs-file-export me-2"></i>Excel</button></li>
            <li><button class="dropdown-item js-export-table" data-export="pdf"><i class="bx bxs-file-pdf me-2"></i>Pdf</button></li>
            <li><button class="dropdown-item js-export-table" data-export="copy"><i class="bx bx-copy me-2"></i>Copy</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item js-export-table" data-export-method="exportToExcelMissingFilesNew"><i class="bx bxs-file-export me-2"></i>Missing Files</button></li>
            <li><button class="dropdown-item js-export-table" data-export-method="exportToExcelOnlyDeclarationsNew"><i class="bx bxs-file-export me-2"></i>Declarations</button></li>
            <li><button class="dropdown-item js-export-table" data-export-method="exportToExcelOnlyComInvoicesNew"><i class="bx bxs-file-export me-2"></i>Com. Invoices</button></li>
            <li><button class="dropdown-item js-export-table" data-export-method="exportToExcelOnlySalesInvoicesNew"><i class="bx bxs-file-export me-2"></i>Invoices</button></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div>

  <div class="card-datatable table-responsive" style="display: none">
    <table
      class="datatables-declarations-new-ocr table accordion"
      data-period-count="{{ $declarations->frequency }}"
      style="width: 100%"
    >
      <thead>
        <tr>
          <th></th>
          <th>Date</th>
          <th>Declaration</th>
          <th class="text-end">Statistical value</th>
          <th class="text-end">Net amount</th>
          <th class="text-end">Import VAT</th>
          <th class="text-end">Duties</th>
          <th class="text-end">VAT on duties</th>
          <th class="text-end">Adjustment</th>
          <th class="text-end">VAT on adjustment</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<input type="hidden" id="vat_reg_id" value="{{ $declarations->id }}">
<input type="hidden" id="org_no" value="{{ $orgNo }}">
<input type="hidden" id="client_name" value="{{ $client->client_name }}">
<input type="hidden" id="declaration_first_monthyear" value="{{ $periodStart->format('m-Y') }}">

@include('_partials/_modals/modal-declaration-invoice-disregard')
@include('_partials/_modals/modal-declaration-move-invoice-file')
@include('_partials/_modals/modal-declaration-cominvoice-rematch')
@include('_partials/_modals/modal-declaration-salesinvoice-move')
@include('_partials/_modals/modal-declaration-ftp-salesinvoice-edit')
@include('_partials/_offcanvas/offcanvas-declaration-filter')
@endsection