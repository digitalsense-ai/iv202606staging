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
    {{--<span id="manualInputCounter" class="badge bg-label-primary fs-6">0 / 0</span>--}}
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
</div>
@endsection