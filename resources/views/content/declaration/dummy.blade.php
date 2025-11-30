@extends('layouts/layoutMaster')

@section('title', 'Declaration - NO')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />

<!-- <link rel="stylesheet" href="{{asset('assets/css/scroller.dataTables.min.css')}}" /> -->

<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<!-- Flat Picker -->
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<!-- <script type="text/javascript" language="javascript" src="{{asset('assets/js/dataTables.scroller.min.js')}}"></script> -->
<script src="{{asset('assets/js/xlsx.min.js')}}"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> -->

<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script type="text/javascript">
$(function () {  
    $(".card.declarations .sk-bounce").show();
    $(".card.declarations .card-datatable").hide(); 

    window.declaration_first_datas = [];   
    window.declaration_second_datas = [];
       
    declaration_first_datas = [
      {
        "id": 1,
        "pdf": "PDF",
        "declaration_no": "2400245070",
        "duties": 597,
        "net_amount": 4989.85,
        "adjustment": 583.44,
        "statistical_value": 5573.29,
        "import_vat": ((597 + 5573.29) * 0.25),//((duties + statistical_value) * 0.25)
        "vat_on_duties": (597 * 0.25),//(duties * 0.25)
        "vat_on_adjustment": (583.44 * 0.25),//(adjustment * 0.25)
        "net_amount_commercial_invoice": 5573.29,
        "net_amount_sales_invoice": 5573.29,
        "vat_amount_sales_invoice": 1393.32,
        "sales_vat_vs_import_vat": (1393.32 - (4989.85 * 0.25)),//(vat_amount_sales_invoice - (net_amount * 0.25))         
        "co_invoices": [
          {
            "id": 1,
            "pdf": "PDF",
            "co_invoice_no": "PROF02449",
            "net_amount_co_invoice": 5573.29,
            "import_vat": (5573.29 * 0.25),//(net_amount_co_invoice * 0.25)
            "currency": "NOK",
            "invoices": [
              {
                "id": 1,
                "pdf": "PDF",
                "invoice_no": 9008235,
                "invoice_date": "01-05-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "NOK",
              },
              {
                "id": 2,
                "pdf": "PDF",
                "invoice_no": 9008235,
                "invoice_date": "29-05-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "NOK",
              },
              {
                "id": 3,
                "pdf": "PDF",
                "invoice_no": 9008236,
                "invoice_date": "30-05-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "EUR",
              }
            ]
          },
          {
            "id": 2,
            "pdf": "PDF",
            "co_invoice_no": "PROF02448",
            "net_amount_co_invoice": 5573.29,
            "import_vat": (5573.29 * 0.25),//(net_amount_co_invoice * 0.25)
            "currency": "NOK",
            "invoices": [
              {
                "id": 1,
                "pdf": "PDF",
                "invoice_no": 9008240,
                "invoice_date": "27-05-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "NOK",
              }
            ]
          }
        ]
      }
    ];  

    declaration_second_datas = [      
      {
        "id": 2,
        "pdf": "PDF",
        "declaration_no": "",
        "duties": 597,
        "net_amount": 4989.85,
        "adjustment": 583.44,
        "statistical_value": 5573.29,
        "import_vat": ((597 + 5573.29) * 0.25),//((duties + statistical_value) * 0.25)
        "vat_on_duties": (597 * 0.25),//(duties * 0.25)
        "vat_on_adjustment": (583.44 * 0.25),//(adjustment * 0.25)
        "net_amount_commercial_invoice": 5573.29,
        "net_amount_sales_invoice": 5573.29,
        "vat_amount_sales_invoice": 1393.32,
        "sales_vat_vs_import_vat": (1393.32 - (4989.85 * 0.25)),//(vat_amount_sales_invoice - (net_amount * 0.25))   
        // "duties": "",
        // "net_amount": "",
        // "adjustment": "",
        // "statistical_value": "",
        // "import_vat": "",//((duties + statistical_value) * 0.25)
        // "vat_on_duties": "",//(duties * 0.25)
        // "vat_on_adjustment": "",//(adjustment * 0.25)
        // "net_amount_commercial_invoice": "",
        // "net_amount_sales_invoice": "",
        // "vat_amount_sales_invoice": "",
        // "sales_vat_vs_import_vat": "",//(vat_amount_sales_invoice - (net_amount * 0.25))         
        "co_invoices": [
          {
            "id": 1,
            "pdf": "PDF",
            "co_invoice_no": "PROF02450",
            "net_amount_co_invoice": 5573.29,
            "import_vat": (5573.29 * 0.25),//(net_amount_co_invoice * 0.25)
            "currency": "NOK",
            "invoices": [
              {
                "id": 1,
                "pdf": "PDF",
                "invoice_no": 9008237,
                "invoice_date": "05-06-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "NOK",
              },
              {
                "id": 2,
                "pdf": "PDF",
                "invoice_no": 9008238,
                "invoice_date": "08-06-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "NOK",
              },
              {
                "id": 3,
                "pdf": "PDF",
                "invoice_no": 9008239,
                "invoice_date": "10-06-2024",
                "net_amount": 0,
                "vat_amount": 0,
                "vat_check_25": 0,
                "currency": "EUR",
              }
            ]
          }
        ]
      }         
    ];

    var declaration_datas = {
      'declaration_first_datas' : declaration_first_datas, 
      'declaration_second_datas' : declaration_second_datas
    };
   
});
</script>
<script src="{{asset('js/dv-declarations.js')}}"></script>
<script src="{{asset('js/dv-declaration-comment.js')}}"></script>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light"><a href="#">{{ 'Designbysi ApS' }}</a>/</span> {{ 'May 2024 NO bi-monthly' }}
</h4>

<!-- Ajax Sourced Server-side -->
<div class="card declarations">

  <!-- Bounce -->
  <div class="sk-bounce sk-primary sk-center">
    <div class="sk-bounce-dot"></div>
    <div class="sk-bounce-dot"></div>
  </div> 
  
  <div class="card-header p-0">    
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0 border-bottom">         
      <div class="col-md-8">
        <ul class="nav nav-tabs card-header-tabs mx-0" role="tablist">
          <li class="nav-item">
            <button type="button" id="btn-declaration-first" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-declaration-first" aria-controls="navs-declaration-first" aria-selected="true">May 2400245070 <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
            <input type="hidden" name="declaration_first_monthyear" id="declaration_first_monthyear" value="May-2024" />
          </li>

          <li class="nav-item">
            <button type="button" id="btn-declaration-second" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-declaration-second" aria-controls="navs-declaration-second" aria-selected="false">Jun 2400245071 <span class="alert-primary text-end fs-tiny p-1 mx-2"></span></button>
            <input type="hidden" name="declaration_second_monthyear" id="declaration_second_monthyear" value="June-2024" />
          </li>          
        </ul>
      </div>       
      <div class="col-md-4 dt-declaration-export text-end">
        <div class="first-declaration-export">
          <button type="button" id="btn-control" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDeclarationControl">Control</button>
        </div>       
        <div class="second-declaration-export d-none">
          <button type="button" id="btn-control" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDeclarationControl">Control</button>
        </div>
      </div>      
    </div>
   
    <div class="d-flex justify-content-between align-items-center row gap-3 gap-md-0 m-0">
      <div class="card shadow-none px-0">
        
        <div class="card-header border-bottom p-2">        
          <div class="dt-search-filter text-end align-middle">
          </div>
        </div>

        <div class="tab-content px-0">
          <div class="tab-pane fade show active" id="navs-declaration-first" role="tabpanel">
            <table class="datatables-declarations datatables-first-declarations table accordion">     
              <thead>
                <tr class="detail-control">
                  <th class="declaration-th-w20"></th> 
                  <th class="declaration-th-w150">Declarations</th>  
                  <th class="declaration-th-w150">Statistical value</th>
                  <th class="declaration-th-w150">Net Amount</th>
                  <th class="declaration-th-w150">Import VAT</th>
                  <th class="declaration-th-w150">Duties</th>
                  <th class="declaration-th-w150">VAT on duties</th>
                  <th class="declaration-th-w150">Adjustment</th>                  
                  <th class="declaration-th-w150">VAT on adjustment</th>
                  <th class="">Action</th>

                  <!-- <th class="">Declarations</th>                  
                  <th class="">Duties</th>
                  <th class="">Net Amount</th>
                  <th class="">Adjustment</th>
                  <th class="">Statistical value</th>
                  <th class="">Import VAT</th>
                  <th class="">VAT on duties</th>
                  <th class="">VAT on adjustment</th>
                  <th class="">Action</th> -->
                  <!-- <th class="declaration-th-w20"></th>                  
                  <th class="invoice-th-w150">Declarations</th>                  
                  <th class="invoice-th-w150">Duties</th>
                  <th class="invoice-th-w150">Net Amount</th>
                  <th class="invoice-th-w150">Adjustment</th>
                  <th class="invoice-th-w150">Statistical value</th>
                  <th class="invoice-th-w150">Import VAT</th>
                  <th class="invoice-th-w150">VAT on duties</th>
                  <th class="invoice-th-w150">VAT on adjustment</th>
                  <th class="invoice-th-w200">Net amount commercial invoice</th>
                  <th class="invoice-th-w200">Net amount sales invoice</th>
                  <th class="invoice-th-w200">VAT amount sales invoice</th>
                  <th class="invoice-th-w200">Sales VAT vs import VAT</th> -->
                </tr>
              </thead>
            </table>
          </div><!--/ navs-declaration-first-->
          <div class="tab-pane fade" id="navs-declaration-second" role="tabpanel">
            <table class="datatables-declarations datatables-second-declarations table accordion">     
              <thead>
                <tr class="detail-control">
                  <th class="declaration-th-w20"></th>                  
                  <th class="declaration-th-w150">Declarations</th>                  
                  <th class="declaration-th-w150">Statistical value</th>
                  <th class="declaration-th-w150">Net Amount</th>
                  <th class="declaration-th-w150">Import VAT</th>
                  <th class="declaration-th-w150">Duties</th>
                  <th class="declaration-th-w150">VAT on duties</th>
                  <th class="declaration-th-w150">Adjustment</th>                  
                  <th class="declaration-th-w150">VAT on adjustment</th>                
                  <th class="">Action</th>
                </tr>
              </thead>
            </table>    
          </div><!--/ navs-declaration-second-->            
        </div>
         
      </div>
    </div>
  </div>

</div>
{{--
@include('_partials/_modals/modal-declaration-comment')
@include('_partials/_modals/modal-declaration-com-invoice-comment')
@include('_partials/_modals/modal-declaration-invoice-comment')
--}}

@include('_partials/_offcanvas/offcanvas-declaration-control')
@include('_partials/_offcanvas/offcanvas-declaration-filter')
{{--

<button id="exportBtn">Export to Excel</button>
<script>
    document.getElementById("exportBtn").addEventListener("click", function() {
        const data = [
            ["Header1", "Header2", "Header3"], // Header row
            [1, 2, 3],
            [4, 5, 6]
        ];

        const ws = XLSX.utils.aoa_to_sheet(data);

        // Optionally format the header row, but note that this may not work as intended
        for (let i = 0; i < data[0].length; i++) {
            const cellAddress = XLSX.utils.encode_cell({ r: 0, c: i });
            ws[cellAddress].s = {
                fill: { fgColor: { rgb: "FFFF00" } }, // Yellow background
                font: { bold: true, color: { rgb: "000000" } } // Black text
            };
        }

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, "data.xlsx");
    });
</script>
--}}
@endsection
