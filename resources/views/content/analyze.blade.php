@extends('layouts/layoutMaster')

@section('title', 'Analyze PDF')

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
<script type="text/javascript">
$(function () {      
    window.dt_ocrinvoicepdf_files = null;
    window.ocrinvoicepdffile_datas = [];   
   
    var result = { 'ocrpdfs': {!! json_encode($ocrpdfs) !!} };    
    var ocrinvoicepdffile_datas = drawDtTable(result, 'ocrinvoicepdf');    
});
</script>
<script>
document.getElementById('upload-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const progressCard = document.getElementById('batch-progress');
    const bar = document.getElementById('progress-bar');
    const text = document.getElementById('progress-text');

    progressCard.classList.remove('d-none');
    bar.style.width = '0%';
    bar.innerText = '0%';
    text.innerText = 'Uploading…';

    const response = await fetch("{{ url('/analyze-invoice-pdf') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    });

    const data = await response.json();
    const batchId = data.batch_id;

    pollProgress(batchId);
});

async function pollProgress(batchId) {
    const bar = document.getElementById('progress-bar');
    const text = document.getElementById('progress-text');

    try {
        const res = await fetch(`/api/batches/${batchId}/progress`);
        const data = await res.json();

        bar.style.width = data.percent + '%';
        bar.innerText = data.percent + '%';
        text.innerText = `${data.completed} / ${data.total} documents processed`;

        if (data.percent < 100) {
            setTimeout(() => pollProgress(batchId), 3000);
        } else {
            bar.classList.remove('progress-bar-animated');
            bar.classList.add('bg-success');
            text.innerText = '✅ Batch processing completed';

            ocrinvoicepdffile_datas = drawDtTable(data, 'ocrinvoicepdf');
            dt_ocrinvoicepdf_files.clear().rows.add(ocrinvoicepdffile_datas).draw();

            $('#batch-progress').addClass('d-none');
            bar.style.width = data.percent + '%';
            bar.innerText = data.percent + '%';
            text.innerText = `Initializing…`;

            $('#pdf_invoice_type').val('sales');
            $('#pdfs').val('');
        }
    } catch (err) {
        console.error(err);
    }
}
</script>

<script src="{{asset('js/dv-ocr-invoice-pdfs.js')}}"></script>
@endsection

@section('content')
	
	@if(session('result'))
        <div class="alert alert-success">
            <h5>Analysis Result:</h5>
            <pre>{{ json_encode(session('result'), JSON_PRETTY_PRINT) }}</pre>            
        </div>
    @endif
   
    {{--@if($invoice)        
        <pre style="white-space: pre-wrap; font-family: inherit;">{{ $invoice }}</pre>
    @endif--}}

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif   

    {{-- Upload Form --}}
    {{--<form action="{{ url('/analyze-invoice-pdf') }}" method="POST" enctype="multipart/form-data">--}}
    <form id="upload-form" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-2">
                <div class="mb-3">
                    <label for="pdf_invoice_type" class="form-label">Invoice Type</label>
                    <select id="pdf_invoice_type" class="form-select" name="pdf_invoice_type" required>             
                      <!-- <option value="multiple-invoices-single-pdf">Multi invoices in single PDF</option> -->
                      <option value="sales">Sales Invoice</option> 
                      <option value="com">Commercial Invoice</option>          
                    </select> 
                </div>
            </div>
            <div class="col-10">
                <div class="mb-3">
                    <label for="file" class="form-label">Choose PDF File(s)</label>
                    <input type="file" class="form-control" name="pdfs[]" id="pdfs" multiple accept="application/pdf" required>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Analyze PDF</button>
    </form>

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

    {{-- Extracted Data's --}}
    @if($ocrpdfs)
    <div id="rxtracted-datas" class="card mt-4">
        <div class="card-body p-0 pb-3">
            <h5 class="m-0 p-3">Extracted Data's</h5>

            <table class="datatables-ocrinvoicepdffiles table">     
                <thead class="bg-label-primary">
                    <tr>
                        <th>Sl. No.</th>           
                        <th>Document Type</th>
                        <th>Client Name</th>
                        <th>File Name</th>         
                        <th>Date and Time</th>
                        <th>Status</th>     
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @endif

@include('_partials/_offcanvas/offcanvas-invoice-ocr-pdf-form')

@endsection