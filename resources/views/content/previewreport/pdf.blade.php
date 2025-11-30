<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Preview Report - {{ $vatreg->country . ' ' . \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                  \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</title> 
       
    <style type="text/css">   
      @page { margin: 0in; }  
      html, body { margin: 0; padding: 0; }  

      @font-face {
          font-family: 'Rubik';
          font-weight: normal;
          font-style: normal;
          font-variant: normal;
          src: url({{ storage_path('fonts/Rubik/Rubik-VariableFont_wght.ttf') }}) format('truetype');
      }

      body { font-family: 'Rubik', sans-serif; color: #516377; line-height: 1.1; font-size: 0.9375rem; font-weight: normal; }  
      table { border-collapse: collapse; margin: 0; padding: 0; }

      .cover-page { background-color: #003056; color: #fff; min-height: 100%; }
      .rest-page { background-color: #ffffff; color: #677788; min-height: 100%; }
      .full-width { width: 100%; }
    
      .gap-1 { height: 300px; }
      .fw-normal { font-weight: normal; }

      table { padding: 1%; }      
      h1 { font-size: 4rem; }
      h2 { font-size: 2rem; padding: 0; margin: 0 0 1rem; }
      h5, h6 { padding: 0; margin: 0; }
      h5 { font-size: 0.9375rem; font-weight: normal; }
      
      .border-none {  border: none !important; }
      .position-absolute { position: absolute !important; left: 0 !important; } 
      .w-100 { width: 100% !important; }
      .m-0 { margin: 0 !important; }
      .p-0 { padding: 0 !important; }      

      .rest-page h6 { color: #516377;  font-size: 0.9375rem;  font-weight: normal; }
      .rest-page table.fixed { /*padding: 2.28rem 1.1rem !important;*/ padding: 2.375rem !important; }

      .logo { margin: 1rem 0; width: 160px; }

      .inner-tbl { padding: 1rem 0 !important; }
      tr.th,
      .inner-tbl th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; font-weight: bold; }
      .inner-tbl th,
      .inner-tbl td  { padding: 0.625rem 1.5rem; border-bottom: 0.2px solid #d4d8dd; }
      .inner-tbl td h6 { text-transform: none; font-weight: bold; }

      tr.th.reduce-fs {  font-size: 0.60rem; letter-spacing: normal; }

      .page-break { page-break-before: always; }  

      .border-primary { border: 2px solid #5a8dee !important; box-shadow: 0 2px 14px rgba(38, 60, 85, 0.16); 
        border-radius: 0.3125rem;  }
      .card-body { padding: 1.375rem; }
      .text-uppercase { text-transform: uppercase !important; }
      .bg-primary { background-color: #5a8dee !important; }
      .rounded-pill { border-radius: 50rem !important; }
      .text-white { color: #fff; }
      .text-start { text-align: left; }
      .text-end { text-align: right; }
      .py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
      .px-2 { padding-right: 0.5rem !important; padding-left: 0.5rem !important; }
      .p-2 { padding: 0.5rem !important; }
      .pt-4 { padding-top: 1.5rem !important; }
      .pb-4 { padding-bottom: 1.5rem !important; }
      .pe-4 { padding-right: 1.5rem !important; }
      .mb-2 { margin-bottom: 0.5rem !important; }
      .fw-bold { font-weight: bold !important; }
      .v-middle { vertical-align: middle !important; }
      .my-1 { margin-top: 1rem !important; margin-bottom: 1rem !important; }
      .my-2 { margin-top: 2rem !important; margin-bottom: 2rem !important; }
      .mt-2-only { margin-top: 2rem !important; }
      .mb-2-only { margin-bottom: 2rem !important; }

      .pb-0 { padding-bottom: 0 !important; }
      
      .d-none { display: none; } 

      .border-1 { border: 0.2px solid #d4d8dd !important; }    
      .border-start { border-left: 0.2px solid #d4d8dd !important; }    
      .border-top { border-top: 0.2px solid #d4d8dd !important; }    
      .border-bottom { border-bottom: 0.2px solid #d4d8dd !important; }    
      .border-end { border-right: 0.2px solid #d4d8dd !important; }  

      tr.odd-row td { background-color: #f8f9fa; color: #677788; }  

    </style>     
</head>
<body>
  <!-- Cover Page -->
  <div class="full-width p-1 cover-page">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none">         
      <thead> 
        <tr>
          <th align="center" valign="middle">  
            <h6>Import Reconciliation</h6>
          </th>
        </tr>

        <tr>
          <th align="right" valign="middle">  
            <img src="<?php echo $logo_white ?>" class="logo">            
          </th>
        </tr>        
      </thead>
      <tbody> 
        <tr>
          <th align="center" valign="middle" class="gap-1"></th>          
        </tr>
        <tr>
          <th align="center" valign="middle">
            <h1>Import Reconciliation</h1>
            <h2>For</h2>
            <h2>{{ $client->client_name }}</h2>
            <h2>Cvr. no.{{ $client->vatno }}</h2>
            <h2>{{ \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
                \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}
            </h2>  
          </th>          
        </tr>    
      </tbody>
    </table>
  </div>
  <!--/ Cover Page -->

  <!-- Content Page -->
  <div class="full-width p-1 rest-page page-break" style="position: relative;">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
      @include('_partials/_content/_previewreport/header-pdf') 
      <tbody>
        {!! $declarationContent !!}
      </tbody>
    </table>
        
    @php
      $page_no = 1;
    @endphp
    @include('_partials/_content/_previewreport/footer-pdf') 
  </div>
  <!--/ Content Page -->

  <!-- Content Page -->
  @php
    $all_importreconciliationcominvoices = $vatreg->importreconciliationcominvoices;
    $row_per_page = 22;
  @endphp
  <div class="full-width p-1 rest-page page-break" style="position: relative;">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
      @include('_partials/_content/_previewreport/header-pdf')       
      <tbody>
        {!! $comInvoiceContent !!}
      </tbody>
    </table>
        
    @php
      $page_no = ceil(count($all_importreconciliationcominvoices)/$row_per_page) + 1;
    @endphp
    @include('_partials/_content/_previewreport/footer-pdf') 
  </div>
  <!--/ Content Page -->

  <!-- Content Page -->
  <div class="full-width p-1 rest-page page-break" style="position: relative;">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed">
      @include('_partials/_content/_previewreport/header-pdf')      
      <tbody>
        {!! $overviewContent !!}        
      </tbody>
    </table>    

    @php     
      $vatreturns = $vatreg->vatreturns;      
      $page_no = isset($page_no) ? ($page_no + 1) : ((count($vatreturns) > 2) ? 4 : 3);
    @endphp
    @include('_partials/_content/_previewreport/footer-pdf') 
  </div>
  <!--/ Content Page -->

  <!-- Back Page -->
  <div class="full-width p-1 cover-page page-break">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none">         
      <thead> 
        <tr>
          <th align="center" valign="middle">  
            <h6>Import Reconciliation</h6>
          </th>
        </tr>

        <tr>
          <th align="right" valign="middle">  
            <img src="<?php echo $logo_white ?>" width="25%" class="logo">
          </th>
        </tr>        
      </thead>
      <tbody> 
        <tr>
          <th align="center" valign="middle" class="gap-1"></th>          
        </tr>        
        <tr>
          <th align="center" valign="middle">            
            <h2 class="fw-normal">~~~~~ End ~~~~~</h2>           
          </th>          
        </tr>    
      </tbody>
    </table>
  </div> 
  <!--/ Back Page --> 
</body>
</html>