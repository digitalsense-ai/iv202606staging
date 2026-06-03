@extends('layouts/layoutMaster')

@section('title', 'Split PDF')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<link rel="stylesheet" href="{{asset('assets/css/scroller.dataTables.min.css')}}" />
@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
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
<script src="{{asset('js/dv-common.js')}}"></script>
<script src="{{asset('js/dv-analyze-pdf.js')}}"></script>
@endsection

@section('content')
	
<form action="{{ route('split.pdf.post') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">        
        <div class="col-10">
            <div class="mb-3">
                <label for="file" class="form-label">Upload PDF</label>
                <input type="file" class="form-control" name="pdf_file" accept="application/pdf" required>
            </div>
        </div>
      </div>
      <div class="row">  
        <div class="col-10">
            <div class="mb-3">
                <label for="page_ranges" class="form-label">Enter page ranges (e.g., 1-2,3,4-5):</label>
                <input type="text" class="form-control" name="page_ranges" placeholder="1-2,3,4-5" required>               
            </div>
        </div>
    </div>

    <!-- <label>Upload PDF:</label>
    <input type="file" name="pdf_file" required><br><br>

    <label></label>
    <input type="text" name="page_ranges" placeholder="1-2,3,4-5" required><br><br> -->

    <button type="submit" class="btn btn-primary">Split PDF</button>
</form>

@endsection