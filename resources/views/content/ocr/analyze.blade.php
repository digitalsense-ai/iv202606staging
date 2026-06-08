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
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
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

<script src="{{asset('assets/vendor/libs/dropzone/dropzone.min.js')}}"></script>

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
@endsection

@section('page-script')
<script type="text/javascript">
    window.EchoConfig = {
        pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
        pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    };
</script>

<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.analyzepdf .sk-bounce").show();
    $(".card.analyzepdf .card-datatable").hide(); 

    window.analyzepdf_completed_datas = [];   
    window.analyzepdf_processing_datas = [];
    window.analyzepdf_error_datas = [];
    window.analyzepdf_deleted_datas = [];
    
    var result = { 'analyzepdfs': {!! json_encode($analyzepdfs) !!}, 'vatregmains': {!! json_encode($vatregmains) !!} };    
    var analyzepdf_datas = drawDtTable(result, 'analyzepdf');  
});
</script>
<script>
    /*
document.getElementById('upload-form').addEventListener('submit', async function (e) {console.log("submitted");
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

    const response = await fetch("{{ url('/analyzepdf') }}", {        
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'  // <-- ADD THIS
        },
        body: formData
    });

    const contentType = response.headers.get('content-type');

    if (!response.ok) {
        const errorText = await response.text();
        console.error('Upload failed:', errorText);
        text.innerText = '❌ Upload failed';
        return;
    }

    if (!contentType || !contentType.includes('application/json')) {
        const raw = await response.text();
        console.error('Expected JSON, got:', raw);
        text.innerText = '❌ Server error';
        return;
    }


    const data = await response.json();
    const batchId = data.batch_id;

    pollProgress(batchId);
});
*/
async function pollProgress(batchId) {
    const bar = document.getElementById('progress-bar');
    const text = document.getElementById('progress-text');

    try {      
        const res = await fetch(`/analyzepdf/batch/${batchId}/progress`);
        const data = await res.json();

        // If batch has errors, show first error message
        if (data.error_docs && data.error_docs.length > 0) {
            var analyzepdf_datas = drawDtTable(data, 'analyzepdf');
            console.log(analyzepdf_datas);
            reloadAnalyzedPdf(analyzepdf_datas);

            bar.style.width = '100%';
            bar.innerText = 'Error';
            text.innerText = `Error: ${data.error_docs[0].error}`;
            bar.classList.remove('progress-bar-animated');
            bar.classList.add('bg-danger');
            return;
        }

        bar.style.width = data.percent + '%';
        bar.innerText = data.percent + '%';
        text.innerText = `${data.completed} / ${data.total} documents processed`;

        if (data.percent < 100) {
            setTimeout(() => pollProgress(batchId), 3000);
        } else {
            bar.classList.remove('progress-bar-animated');
            bar.classList.add('bg-success');
            text.innerText = '✅ Batch processing completed';

            var analyzepdf_datas = drawDtTable(data, 'analyzepdf');
            console.log(analyzepdf_datas);
            reloadAnalyzedPdf(analyzepdf_datas);
             
//console.log($('.datatables-analyzepdf').length);
            // var dt_analyzepdf_completed = $('.datatables-completed-analyzepdf').DataTable();console.log(dt_analyzepdf_completed.length);
            // dt_analyzepdf_completed.clear().rows.add(analyzepdf_datas).draw();

            // var dt_analyzepdf_processing = $('.datatables-processing-analyzepdf').DataTable();
            // dt_analyzepdf_processing.clear().rows.add(analyzepdf_datas).draw();

            // var dt_analyzepdf_error = $('.datatables-error-analyzepdf').DataTable();
            // dt_analyzepdf_error.clear().rows.add(analyzepdf_datas).draw();
        
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

window.reloadAnalyzedPdf = function reloadAnalyzedPdf(analyzepdf_datas) { 
// function reloadAnalyzedPdf(analyzepdf_datas) 
// {  
    var dt_analyzepdf_tables = $('.datatables-analyzepdf');
    for (var i = 0; i < dt_analyzepdf_tables.length; i++) 
    {      
        var analyzepdf_name = '';        
        if(i === 0) analyzepdf_name = 'completed';          
        else if(i === 1) analyzepdf_name = 'processing';          
        else if(i === 2) analyzepdf_name = 'error';  
        else if(i === 3) analyzepdf_name = 'deleted';     

        var tabSelector = "#navs-analyzepdf-" + analyzepdf_name;
        var tableSelector = ".datatables-"+ analyzepdf_name +"-analyzepdf";

        if($(tableSelector).length > 0)
        {
            if ($.fn.DataTable.isDataTable(tableSelector))
            {
                var dt_analyzepdf = $(tableSelector).DataTable();
                var rowsData = analyzepdf_datas['analyzepdf_'+ analyzepdf_name +'_datas'];

                dt_analyzepdf.clear().rows.add(rowsData).draw();
                $("#btn-analyzepdf-"+ analyzepdf_name +" span").html(rowsData.length);

                // Enable/disable tab based on data
                if (rowsData.length > 0) {
                    $(tabSelector).css({
                        'pointer-events': 'auto',
                        'opacity': '1',
                        'cursor': 'pointer'
                    });
                } else {
                    $(tabSelector).css({
                        'pointer-events': 'none',
                        'opacity': '0.5',
                        'cursor': 'not-allowed'
                    });
                }
            }
        }
    }
}

// function reloadAnalyzedPdf(analyzepdf_datas) 
// {  
//     var dt_analyzepdf_tables = $('.datatables-analyzepdf');
//     for (var i = 0; i < dt_analyzepdf_tables.length; i++) 
//     {      
//         var analyzepdf_name = '';        
//         if(i === 0)        
//           analyzepdf_name = 'completed';          
//         else if(i === 1)        
//           analyzepdf_name = 'processing';          
//         else if(i === 2)       
//           analyzepdf_name = 'error';     

//         if($('.datatables-'+ analyzepdf_name +'-analyzepdf').length > 0)
//         {
//             if ($.fn.DataTable.isDataTable('.datatables-'+ analyzepdf_name +'-analyzepdf'))
//             {
//                 $("#navs-analyzepdf-" + analyzepdf_name).css({
//                   'pointer-events': 'none',
//                   'opacity': '0.5',
//                   'cursor': 'not-allowed'
//                 });

//                 var dt_analyzepdf = $('.datatables-'+ analyzepdf_name +'-analyzepdf').DataTable(); // safely get it
//                 if (dt_analyzepdf.rows().any())
//                 {
//                     dt_analyzepdf.clear().rows.add(analyzepdf_datas['analyzepdf_'+ analyzepdf_name +'_datas']).draw();
//                     $("#btn-analyzepdf-"+ analyzepdf_name +" span").html(analyzepdf_datas['analyzepdf_'+ analyzepdf_name +'_datas'].length);
//                 }
//             }
//         }
//     }
// }

async function fetchInboxAndTrackProgress() {
    const progressCard = document.getElementById('batch-progress');
    const bar = document.getElementById('progress-bar');
    const text = document.getElementById('progress-text');

    // Call fetchInbox endpoint
    const response = await fetch("/analyzepdf/fetchinbox", {
        method: "GET",
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    const data = await response.json();
    const total = data.total || 0;

    if (total === 0) {
        text.innerText = "No emails to process";
        progressCard.classList.add('d-none'); // Keep hidden
        return;
    }

    // Only show progress card if there are emails
    progressCard.classList.remove('d-none');
    bar.style.width = '0%';
    bar.innerText = '0%';
    text.innerText = `Queuing ${total} emails…`;

    let completed = 0;

    // Poll backend for progress every 3 seconds
    const poll = setInterval(async () => {
        const res = await fetch(`/analyzepdf/progress`);
        const progressData = await res.json();

        completed = progressData.completed || 0;
        //const percent = Math.round((completed / total) * 100);

        const percent = Math.min(100, Math.round((completed / total) * 100));

        bar.style.width = percent + '%';
        bar.innerText = percent + '%';
        text.innerText = `${completed} / ${total} emails processed`;

        if (completed >= total) {
            clearInterval(poll);
            bar.classList.remove('progress-bar-animated');
            bar.classList.add('bg-success');
            text.innerText = `All emails processed`;

            var analyzepdf_datas = drawDtTable(progressData, 'analyzepdf');            
            reloadAnalyzedPdf(analyzepdf_datas);
        }
    }, 3000);
}

// Call this when user clicks "Fetch Email PDFs"
//fetchInboxAndTrackProgress();
</script>

<script src="{{ asset('js/pdf.min.js') }}"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
</script>

<script src="{{asset('js/dv-analyze-pdf.js')}}"></script>
@endsection

@section('content')
	
	@if(session('result'))
        <div class="alert alert-success">
            <h5>Analysis Result:</h5>
            <pre>{{ json_encode(session('result'), JSON_PRETTY_PRINT) }}</pre>            
        </div>
    @endif
       
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif   

    <div class="row">
        <div class="col-12">
            @if(strtolower(env('APP_URL')) === "http://localhost:8000" || strtolower(config('app.url')) === "http://localhost:8000")
                <a class="btn btn-primary" href="{{ route('analyze.pdf.validate', 'all') }}" target="_blank">Validate</a>
            @else
                <a href="javascript:fetchInboxAndTrackProgress()" class="btn btn-dark float-end">Fetch Email PDFs</a>
            @endif

            <div class="btn-group float-end mx-2">
                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Sync Now</button>
                <ul class="dropdown-menu">                    
                    @foreach ($syncclients as $syncclient)
                        <li><a class="dropdown-item" href="{{ route('analyze.pdf.sync', ['client_id' => $syncclient->id]) }}" target="_blank">{{ $syncclient->client_name }}</a></li>
                    @endforeach                  
                </ul>
            </div>         
        </div>
    </div>

    
    <div class="card my-4 card-ocr-bulk-upload">              
        <div class="card-body">
            <form method="post" action="{{ url('analyzepdf/bulk-upload') }}" enctype="multipart/form-data" class="dropzone needsclick dropzone-ocr-bulk-upload" id="dropzone-ocr-bulk-upload"> 
                <input type="hidden" id="bulk_total_uploads" name="bulk_total_uploads">
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
    
    {{-- Upload Form --}} 
    {{--   
    <form id="upload-form" enctype="multipart/form-data">    
        @csrf
        <div class="row">
            <div class="col-2">
                <div class="mb-3">
                    <label for="pdf_invoice_type" class="form-label">Invoice Type</label>
                    <select id="pdf_invoice_type" class="form-select" name="pdf_invoice_type" required>
                        <option value="">Select</option> 
                        <option value="com">Commercial Invoice</option>             
                        <option value="multi-invoices">Multi invoices in single PDF</option>
                        <option value="sales">Sales Invoice</option>
                    </select> 
                </div>
            </div>
            <!-- <div class="col-10">
                <div class="mb-3">
                    <label for="file" class="form-label">Choose PDF File(s)</label>
                    <input type="file" class="form-control" name="pdfs[]" id="pdfs" multiple accept="application/pdf" required>
                </div>
            </div> -->            
        </div>
        <div class="row" id="single-invoice">            
            <div class="col-10">
                <div class="mb-3">
                    <label for="file" class="form-label">Upload PDF(s)</label>
                    <input type="file" class="form-control" name="pdfs[]" id="pdfs" multiple accept="application/pdf">
                </div>
            </div>
        </div>

        <div class="row" id="multi-invoice" style="display: none;">        
            <div class="col-10">
                <div class="mb-3">
                    <label for="file" class="form-label">Upload PDF (to split and analyse)</label>
                    <input type="file" class="form-control" name="pdf_file" id="pdf_file" accept="application/pdf">
                </div>
            </div>          
            <div class="col-10">
                <div class="mb-3">
                    <label for="page_ranges" class="form-label">Enter page ranges (e.g., 1-2,3,4-5):</label>
                    <input type="text" class="form-control" name="page_ranges" placeholder="1-2,3,4-5">               
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Analyze PDF</button>
    </form>
    --}}

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
    @if($analyzepdfs)
    <!-- Ajax Sourced Server-side -->
    <div class="card analyzepdfs mt-4">

      <!-- Bounce -->
      <div class="sk-bounce sk-primary sk-center">
        <div class="sk-bounce-dot"></div>
        <div class="sk-bounce-dot"></div>
      </div>

      <h5 class="m-0 p-3">Extracted Data's</h5>

      <div class="card-header p-0">    
        <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">         
          <div class="col-md-8">
            <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">

              @for ($i = 0; $i < 4; $i++)
                @php
                    $tab_div_name = '';
                    $tab_span_class_name = '';
                    if ($i === 0)
                    {
                        $tab_div_name = 'completed';
                        $tab_span_class_name = 'primary';
                    }
                    elseif ($i === 1)
                    {
                        $tab_div_name = 'processing';
                        $tab_span_class_name = 'warning';
                    }
                    elseif ($i === 2)
                    {
                        $tab_div_name = 'error';
                        $tab_span_class_name = 'danger';
                    }
                    elseif ($i === 3)
                    {
                        $tab_div_name = 'deleted';
                        $tab_span_class_name = 'danger';
                    }
                @endphp

                <li class="nav-item">
                  <button type="button" id="btn-analyzepdf-{{ $tab_div_name }}" class="nav-link {{ ($i === 0) ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-analyzepdf-{{ $tab_div_name }}" aria-controls="navs-analyzepdf-{{ $tab_div_name }}" aria-selected="{{ ($i === 0) ? 'true' : 'false' }}"> {{ ucfirst($tab_div_name) }}<span class="alert-{{ $tab_span_class_name }} text-end fs-tiny p-1 mx-2"></span></button>                 
                </li>
              @endfor                
            </ul>
          </div>  
         
          <div class="col-md-4 dt-analyzepdf-export text-end">
            @for ($i = 0; $i < 4; $i++)
                @php
                  $tab_div_name = '';            
                  if ($i === 0)
                    $tab_div_name = 'completed';
                  elseif ($i === 1)
                    $tab_div_name = 'processing';
                  elseif ($i === 2)
                    $tab_div_name = 'error';
                elseif ($i === 3)
                    $tab_div_name = 'deleted';
                @endphp

                <div class="{{ $tab_div_name }}-analyzepdf-export {{ ($i === 0) ? '' : 'd-none' }}">
                  <!-- <button type="button" id="btn-refresh-analyzepdf" class="btn btn-primary">Refresh Data</button> -->
                </div>
            @endfor
          </div> 
         
        </div>
       
        <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
          <div class="card shadow-none px-0">
            
            <div class="card-header border-bottom p-2">        
              <div class="dt-search-filter text-end align-middle">
                <div class="dt-dropdown-filter w-auto d-inline-block">
                    <div class="w-auto d-inline-block me-1 client_name"></div>
                    <div class="w-auto d-inline-block me-1 invoice_type"></div>
                    <div class="w-auto d-inline-block me-2 invoice_status"></div>
                </div>
              </div>
              

              <!-- <div class="dt-dropdown-filter my-1">
                 <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <div class="row"> 
                            <div class="col-md-auto"><label class="my-2">Filter: </label></div>
                            <div class="col-md-auto invoice_type"></div>
                            <div class="col-md-auto invoice_client"></div>
                            <div class="col-md-auto invoice_status"></div>
                        </div>
                    </div>                    
                </div>
              </div> -->
            </div>

            <div class="tab-content p-0 pb-4">

              @for ($i = 0; $i < 4; $i++)
                @php
                  $tab_div_name = '';            
                  if ($i === 0)
                    $tab_div_name = 'completed';
                  elseif ($i === 1)
                    $tab_div_name = 'processing';
                  elseif ($i === 2)
                    $tab_div_name = 'error';
                  elseif ($i === 3)
                    $tab_div_name = 'deleted';  
                @endphp
                <div class="tab-pane fade {{ ($i === 0) ? 'show active' : '' }}" id="navs-analyzepdf-{{ $tab_div_name }}" role="tabpanel">
                  <table class="datatables-analyzepdf datatables-{{ $tab_div_name }}-analyzepdf table" data-analyzepdf_name="{{ $tab_div_name }}">                         
                    <thead class="bg-label-primary">
                        <tr>
                            <th></th>
                            <th>Sl. No.</th>           
                            <th>Document Type</th>
                            <th>Client Name</th>
                            <th>Invoice No's</th>
                            <th>File Name</th>
                            <th>Last Modified/<br>Created</th>
                            <th>Reason</th>     
                            <th>Status</th>  
                            <th>Actions</th>
                        </tr>
                    </thead>                   
                  </table>    
                </div><!--/ navs-analyzepdf-{{ $tab_div_name }}--> 
              @endfor
            </div>
             
          </div>
        </div>
      </div>

    </div>
    @endif

@include('_partials/_offcanvas/offcanvas-analyzepdf-form')
@include('_partials/_offcanvas/offcanvas-analyzepdf-filter')

@include('_partials/_modals/modal-analyzepdf-delete')

@endsection