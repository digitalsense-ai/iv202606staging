<!DOCTYPE html>
<html>
<head>
    <title>Laravel Dompdf Add Custom Font Family Example - intravat.com</title>
    <style>
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
      .inner-tbl th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; font-weight: bold; }
      .inner-tbl th,
      .inner-tbl td  { padding: 0.625rem 1.5rem; border-bottom: 0.2px solid #d4d8dd; }
      .inner-tbl td h6 { text-transform: none; font-weight: bold; }

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
      /*.mb-2-only { margin-bottom: 2rem !important; }*/

      .pb-0 { padding-bottom: 0 !important; }
      
      .d-none { display: none; } 

      .border-1 { border: 0.2px solid #d4d8dd !important; }    
      .border-start { border-left: 0.2px solid #d4d8dd !important; }    
      .border-top { border-top: 0.2px solid #d4d8dd !important; }    
      .border-bottom { border-bottom: 0.2px solid #d4d8dd !important; }    
      .border-end { border-right: 0.2px solid #d4d8dd !important; }  

      tr.odd-row td { background-color: #f8f9fa; color: #677788; }  
       /* @font-face {
            font-family: 'Rubik';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            src: url({{ storage_path('fonts/Rubik/Rubik-VariableFont_wght.ttf') }}) format('truetype');
        }

        body {
            font-family: 'Rubik', sans-serif;
        }

        @font-face {
            font-family: 'Croissant One';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            src: url({{ storage_path('fonts/Croissant_One/CroissantOne-Regular.ttf') }}) format('truetype');
        }

        body {
            font-family: 'Croissant One', sans-serif;
        }
       
      @page { margin: 0in; }  
      html, body { margin: 0; padding: 0; }      
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
      .rest-page table.fixed { padding: 2.28rem 1.1rem !important; }

      .logo { margin: 1rem 0; width: 200px; }

      .inner-tbl { padding: 1rem 0 !important; }
      .inner-tbl th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; font-weight: bold; }
      .inner-tbl th,
      .inner-tbl td  { padding: 0.625rem 1.5rem; border-bottom: 0.2px solid #d4d8dd; }
      .inner-tbl td h6 { text-transform: none; font-weight: bold; }

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
      .pe-4 { padding-right: 1.5rem !important; }
      .mb-2 { margin-bottom: 0.5rem !important; }
      .fw-bold { font-weight: bold !important; }
      .v-middle { vertical-align: middle !important; }

      .d-none { display: none; } 

      .border-1 { border: 0.2px solid #d4d8dd !important; }    
      .border-start { border-left: 0.2px solid #d4d8dd !important; }    
      .border-top { border-top: 0.2px solid #d4d8dd !important; }    
      .border-bottom { border-bottom: 0.2px solid #d4d8dd !important; }    
      .border-end { border-right: 0.2px solid #d4d8dd !important; }  
      */  
    </style>
</head>
<body>
    <p>Dear Boss,
        <br/><br/>
        I am writing to formally resign from my position as [Your Position] at [IT Company Name], with my last working day being [Last Working Day], in accordance with the notice period stipulated in my employment contract.
        <br/><br/>
        I want to express my sincere gratitude for the opportunities and experiences I've had during my time at [IT Company Name]. It has been an incredible journey, and I have had the privilege of working alongside an outstanding team of professionals.
        <br/><br/>
        After careful consideration, I have decided to take a new direction in my career. This decision wasn't easy, as I have cherished my time here and the projects we've accomplished together. I am immensely proud of our collective achievements.
        <br/><br/>
        During my notice period, I am committed to ensuring a seamless transition. I am more than willing to assist in the transfer of my responsibilities, provide training to my successor, and complete any pending projects. Please let me know how I can best contribute to this process.
        <br/><br/>
        Sincerely,<br/>
        Hardik Savani
    </p>

    <!-- Cover Page -->
  <div class="full-width p-1 cover-page" style="page-break-before: always;">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none">         
      <thead> 
        <tr>
          <th align="center" valign="middle">  
            <h6>Import Reconciliation</h6>
          </th>
        </tr>

        <tr>
          <th align="right" valign="middle">  
            
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
            <h2>ALÛSTRE P/S</h2>
            <h2>Cvr. no.42801763</h2>
            <h2>Apr 24 - Jun 24</h2>  
          </th>          
        </tr>    
      </tbody>
    </table>
  </div>
  <!--/ Cover Page -->

  <!-- Content Page -->
  <div class="full-width p-1 rest-page" style="position: relative;">    
    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed"> <!-- style="min-height: 94%;" -->
      <thead> 
        <tr>
          <th width="50%" align="left" valign="top">  
            <h5>ALÛSTRE P/S</h5>
            <h5>Cvr. no.42801763</h5>
            <h5>Apr 24 - Jun 24</h5>
          </th>
          <th width="50%" align="right" valign="top">  
            <img src="<?php echo $logo ?>" width="25%" class="logo">
          </th>        
        </tr>    
      </thead>      
      <tbody>
        {!! $overviewContent !!}
      </tbody>
    </table>
    
    <div class="w-100" style="position: absolute; bottom: 0; left: 0;">
      <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="border-none fixed"> <!-- min-height: 2%;   --> 
        <thead>
          <tr>
            <th align="left" valign="middle">
              <h5>IntraVAT ApS</h5>
              <h5>Torvet 9, 1.  DK-Køge 4600</h5>
              <h5>Tel: +45 88 63 22 99 Mail: <a href="mailto:info@intravat.com" class="text-decoration-none">info@intravat.com</a></h5>
            </th>
            <th align="right" valign="middle">
              <h5 class="pe-4">&nbsp;</h5>
              <h5 class="pe-4">&nbsp;</h5>
              <h5 class="pe-4">Page 1</h5> 
            </th>          
          </tr>    
        </thead> 
      </table>
    </div>
  </div>
  <!--/ Content Page -->


</body>
</html>