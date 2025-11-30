<h4 class="text-start mb-1">QUESTIONS & RESPONSES</h4>
<p class="text-start text-body">Describe your customers in the country where VAT registration is required (B2B, B2C, or both):</p>

<!-- Establishment In The Country -->
<div class="col-lg-12 mb-4">  
  <h5 class="text-dark fw-medium m-0 text-decoration-underline text-uppercase">Establishment In The Country</h5>
  <div class="demo-inline-spacing mt-3">
    <div class="list-group">
      <div class="pb-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">On which date would you like your VAT registration to be effective from?</h6>          
        </div>
        <input type="date" id="est-date" name="est_date" class="form-control w-px-150" placeholder="DD-MM-YYYY" value="{{ isset($clientqa) ? $clientqa->est_date : ''}}" />       
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">Under which name would you like to be registered?</h6>
        </div>
        <p class="mb-1">(NB: Must appear on your Company registgration extract as a primary or secondary name)</p>
        <input type="text" id="est-name" name="est_name" class="form-control" placeholder="johndoe" value="{{ isset($clientqa) ? $clientqa->est_name : ''}}" />
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you have a warehouse address in the country?</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o If yes, provide the address.</p>
          <textarea class="form-control" rows="3" id="est-warehouse-address" name="est_warehouse_address" placeholder="Address">{{ isset($clientqa) ? $clientqa->est_warehouse_address : ''}}</textarea>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o  Is it your own facility, a client’s warehouse, or e.g. an Amazon facility?</p>
          <input type="text" id="est-warehouse" name="est_warehouse" class="form-control" placeholder="Amazon facility" value="{{ isset($clientqa) ? $clientqa->est_warehouse : ''}}" />
        </div>
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">Do you intend to establish a warehouse in the country?</h6>
        </div>        
        <input type="text" id="est-new-warehouse" name="est_new_warehouse" class="form-control" placeholder="Yes" value="{{ isset($clientqa) ? $clientqa->est_new_warehouse : ''}}" />
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you have a showroom in the country?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o  If yes, provide the address.</p>
          <textarea class="form-control" rows="3" id="est-showroom" name="est_showroom" placeholder="Address">{{ isset($clientqa) ? $clientqa->est_showroom : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you have a shop or branch in the country?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o  If yes, provide the address.</p>
          <textarea class="form-control" rows="3" id="est-branch" name="est_branch" placeholder="Address">{{ isset($clientqa) ? $clientqa->est_branch : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you have an office in the country?</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o If yes, provide the address.</p>
          <textarea class="form-control" rows="3" id="est-office" name="est_office" placeholder="Address">{{ isset($clientqa) ? $clientqa->est_office : ''}}</textarea>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o  If yes, are there local employees (see next question)?</p>
          <textarea class="form-control" rows="3" id="est-office-employee" name="est_office_employee" placeholder="">{{ isset($clientqa) ? $clientqa->est_office_employee : ''}}</textarea>
        </div>
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you have employees in the country?</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o  If yes:</p>
          <div class="ms-4 mb-3">
            <p class="mb-1">Do they have decision-making authority (i.e. can they enter into binding agreements)?</p>
            <textarea class="form-control" rows="3" id="est-emp-authority" name="est_emp_authority" placeholder="">{{ isset($clientqa) ? $clientqa->est_emp_authority : ''}}</textarea>
          </div>
          
          <div class="ms-4 mb-3">
            <p class="mb-1">What are their roles?</p>
            <textarea class="form-control" rows="3" id="est-emp-role" name="est_emp_role" placeholder="">{{ isset($clientqa) ? $clientqa->est_emp_role : ''}}</textarea>
          </div>

          <div class="ms-4 mb-3">
            <p class="mb-1">Are they permanent or project-based employees?</p>
            <textarea class="form-control" rows="3" id="est-emp-type" name="est_emp_type" placeholder="">{{ isset($clientqa) ? $clientqa->est_emp_type : ''}}</textarea>
          </div>

          <div class="ms-4">
            <p class="mb-1">For how long will they stay in the country?</p>
            <textarea class="form-control" rows="3" id="est-emp-stay" name="est_emp_stay" placeholder="">{{ isset($clientqa) ? $clientqa->est_name : ''}}</textarea>
          </div>
        </div>               
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">Do you have an agent or representative in the country who is authorised to enter into binding agreements on your behalf?</h6>
        </div>        
        <input type="text" id="est-agent" name="est_agent" class="form-control" placeholder="johndoe" value="{{ isset($clientqa) ? $clientqa->est_agent : ''}}" />
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you work with partners in the country (e.g. your goods are held in consignment by an agent like Amazon or Zalando)?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, is the invoice to the end-customer in your name or the agent’s?</p>
          <textarea class="form-control" rows="3" id="est-invoice" name="est_invoice" placeholder="">{{ isset($clientqa) ? $clientqa->est_invoice : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">If you carry out construction/project work in the country, do you use subcontractors?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, are they Danish or local subcontractors?</p>
          <textarea class="form-control" rows="3" id="est-subcontractor" name="est_subcontractor" placeholder="">{{ isset($clientqa) ? $clientqa->est_subcontractor : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to receive/purchase goods in the country (e.g. to support your new VAT registration or by transferring your own goods from your home country)?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what value do you expect in the first year?</p>
          <textarea class="form-control" rows="3" id="est-goods-value" name="est_goods_value" placeholder="">{{ isset($clientqa) ? $clientqa->est_goods_value : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to receive/purchase services in the country for your new VAT registration?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what value do you expect in the first year?</p>
          <textarea class="form-control" rows="3" id="est-services-value" name="est_services_value" placeholder="">{{ isset($clientqa) ? $clientqa->est_services_value : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">Are there any specific industry regulations that apply to your business in the country?</h6>
        </div>        
        <textarea class="form-control" rows="3" id="est-industry-regulation" name="est_industry_regulation" placeholder="">{{ isset($clientqa) ? $clientqa->est_industry_regulation : ''}}</textarea>
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">What are your major cost elements?</h6>
        </div>        
        <textarea class="form-control" rows="3" id="est-cost-element" name="est_cost_element" placeholder="">{{ isset($clientqa) ? $clientqa->est_cost_element : ''}}</textarea>
      </div>

    </div>
  </div>
</div>
<!--/ Establishment In The Country -->

<!-- Goods/Services -->
<div class="col-lg-12 mb-4"> 
  <h5 class="text-dark fw-medium m-0 text-decoration-underline text-uppercase">Goods/Services</h5>
  <div class="demo-inline-spacing mt-3">
    <div class="list-group">
      <div class="pb-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Provide a comprehensive description of your business activity and commercial scope in the country, including goods flow and product types</h6>          
        </div>
        <textarea class="form-control" rows="3" id="gs-desc" name="gs_desc">{{ isset($clientqa) ? $clientqa->gs_desc : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">What is the expected value (in local currency) of goods to be imported/purchased during the first financial period in the country?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="gs-value" name="gs_value">{{ isset($clientqa) ? $clientqa->gs_value : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">What is the expected total annual turnover (in local currency) from sales of goods/services to customers in the country for the coming year?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="gs-annual-turnover" name="gs_annual_turnover">{{ isset($clientqa) ? $clientqa->gs_annual_turnover : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Do you use your goods/services for internal consumption in the country?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="gs-internal-consumption" name="gs_internal_consumption">{{ isset($clientqa) ? $clientqa->gs_internal_consumption : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">If you sell goods:</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o What goods do you sell?</p>
          <textarea class="form-control" rows="3" id="gs-sell" name="gs_sell">{{ isset($clientqa) ? $clientqa->gs_sell : ''}}</textarea>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o What is the value of your goods (in local currency)?</p>
          <textarea class="form-control" rows="3" id="gs-sell-value" name="gs_sell_value">{{ isset($clientqa) ? $clientqa->gs_sell_value : ''}}</textarea>
        </div>
        <div class="ms-4 mb-3">
          <p class="mb-1">o Do you provide free samples to customers/sellers?</p>
          <textarea class="form-control" rows="3" id="gs-free-sample" name="gs_free_sample">{{ isset($clientqa) ? $clientqa->gs_free_sample : ''}}</textarea>
        </div>
        <div class="ms-4">
          <p class="mb-1">o Do you use influencers or similar individuals who receive free products from you?</p>
          <textarea class="form-control" rows="3" id="gs-influencer" name="gs_influencer">{{ isset($clientqa) ? $clientqa->gs_influencer : ''}}</textarea>
        </div>
      </div>
      
      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you sell VAT-exempt goods?</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o If yes:</p>
          <div class="ms-4 mb-3">
            <p class="mb-1">Which goods are exempt?</p>
            <textarea class="form-control" rows="3" id="gs-vat-exempt" name="gs_vat_exempt">{{ isset($clientqa) ? $clientqa->gs_vat_exempt : ''}}</textarea>
          </div>
          
          <div class="ms-4">
            <p class="mb-1">What is the expected turnover for this in the first year?</p>
            <textarea class="form-control" rows="3" id="gs-vat-exempt-turnover" name="gs_vat_exempt_turnover">{{ isset($clientqa) ? $clientqa->gs_vat_exempt_turnover : ''}}</textarea>
          </div>          
        </div>               
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">If you sell services:</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o Do you sell construction services (e.g. building/repairing fixed property)?</p>
          <textarea class="form-control" rows="3" id="gs-service" name="gs_service">{{ isset($clientqa) ? $clientqa->gs_service : ''}}</textarea>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o What is the value of your construction services?</p>
          <textarea class="form-control" rows="3" id="gs-service-value" name="gs_service_value">{{ isset($clientqa) ? $clientqa->gs_service_value : ''}}</textarea>
        </div>
        <div class="ms-4">
          <p class="mb-1">o Do you host events? For a fee? (e.g. conferences, training, etc.)</p>
          <textarea class="form-control" rows="3" id="gs-event" name="gs_event">{{ isset($clientqa) ? $clientqa->gs_event : ''}}</textarea>
        </div>        
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">Describe how you market your goods and/or services (e.g. via newsletters, website, social media) in the country.</h6>
        </div>        
        <textarea class="form-control" rows="3" id="gs-market" name="gs_market">{{ isset($clientqa) ? $clientqa->gs_market : ''}}</textarea>
      </div>
     
      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">          
          <h6 class="mb-2">Are you or will you be involved in real estate transactions in the country?</h6>
        </div>        
        <textarea class="form-control" rows="3" id="gs-real-estate" name="gs_real_estate">{{ isset($clientqa) ? $clientqa->gs_real_estate : ''}}</textarea>
      </div>

    </div>
  </div>
</div>
<!--/ Goods/Services -->

<!-- Intra-EU Acquisitions And Sales -->
<div class="col-lg-12 mb-4"> 
  <h5 class="text-dark fw-medium m-0 text-decoration-underline text-uppercase">Intra-EU Acquisitions And Sales</h5>
  <p class="text-start text-body">(Only to be completed if you are registering for VAT in an EU country)</p>
  <div class="demo-inline-spacing mt-3">
    <div class="list-group">      
      <div class="pb-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to acquire/purchase/import goods from other EU countries (intra-EU supplies)?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what is the expected turnover from such acquisitions in the first year?</p>
          <textarea class="form-control" rows="3" id="eu-acquisition-turnover" name="eu_acquisition_turnover">{{ isset($clientqa) ? $clientqa->eu_acquisition_turnover : ''}}</textarea>
        </div>                
      </div>
      
      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to export goods from the EU country in which you are registering to other EU countries?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what is the expected turnover from such exports in the first year?</p>
          <textarea class="form-control" rows="3" id="eu-reg-export-turnover" name="eu_reg_export_turnover">{{ isset($clientqa) ? $clientqa->eu_reg_export_turnover : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to import goods from Third Countries into the EU country?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what is the expected import turnover for the first year?</p>
          <textarea class="form-control" rows="3" id="eu-import-turnover" name="eu_import_turnover">{{ isset($clientqa) ? $clientqa->eu_import_turnover : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to export goods from the EU country to Third Countries?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what is the expected export turnover for the first year?</p>
          <textarea class="form-control" rows="3" id="eu-export-turnover" name="eu_export_turnover">{{ isset($clientqa) ? $clientqa->eu_export_turnover : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">(Only if your country of origin is NOT an EU member state) Will you take ownership of the goods in the EU country before selling them to customers?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o (e.g. transferring goods from your home VAT registration to the new EU VAT registration before delivery (DDP), or delivering to the border and letting the customer act as importer (DAP))</p>
          <textarea class="form-control" rows="3" id="eu-export-owner" name="eu_export_owner">{{ isset($clientqa) ? $clientqa->eu_export_owner : ''}}</textarea>
        </div>                
      </div>

    </div>
  </div>
</div>
<!--/ Intra-EU Acquisitions And Sales -->

<!-- Import/Export -->
<div class="col-lg-12 mb-4"> 
  <h5 class="text-dark fw-medium m-0 text-decoration-underline text-uppercase">Import/Export</h5>
  <p class="text-start text-body">(Only to be completed if you are registering for VAT in a third country)</p>
  <div class="demo-inline-spacing mt-3">
    <div class="list-group">      
      <div class="pb-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to import goods from other countries?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what is the expected import turnover for the first year?</p>
          <textarea class="form-control" rows="3" id="ie-import-turnover" name="ie_import_turnover">{{ isset($clientqa) ? $clientqa->ie_import_turnover : ''}}</textarea>
        </div>                
      </div>
      
      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Do you expect to export goods from the VAT registration country to other countries?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, what is the expected export turnover for the first year?</p>
          <textarea class="form-control" rows="3" id="ie-export-turnover" name="ie_export_turnover">{{ isset($clientqa) ? $clientqa->ie_export_turnover : ''}}</textarea>
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Will you take ownership of the goods in the third country before selling them to customers?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o (e.g. transferring goods from your home VAT registration to the new VAT registration in the third country before delivery (DDP), or delivering to the border and letting the customer act as importer (DAP))</p>
          <textarea class="form-control" rows="3" id="ie-export-owner" name="ie_export_owner">{{ isset($clientqa) ? $clientqa->ie_export_owner : ''}}</textarea>
        </div>                
      </div>
      
    </div>
  </div>
</div>
<!--/ Import/Export -->

<!-- About You -->
<div class="col-lg-12 mb-4"> 
  <h5 class="text-dark fw-medium m-0 text-decoration-underline text-uppercase">About You</h5>
  <p class="text-start text-body">(Only to be completed if you are registering for VAT in the United Kingdom)</p>
  <div class="demo-inline-spacing mt-3">
    <div class="list-group">   
      <div class="pb-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">In which countries are you VAT-registered?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="about-vat-countries" name="about_vat_countries">{{ isset($clientqa) ? $clientqa->about_vat_countries : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">In which countries do you have warehouses?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o List addresses.</p>
          <textarea class="form-control" rows="3" id="about-warehouse-countries" name="about_warehouse_countries">{{ isset($clientqa) ? $clientqa->about_warehouse_countries : ''}}</textarea>         
        </div>                
      </div>
      
      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Which countries do you generally sell to?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="about-sell-countries" name="about_sell_countries">{{ isset($clientqa) ? $clientqa->about_sell_countries : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">If you sell goods:</h6>
        </div>        
        <div class="ms-4 mb-3">
          <p class="mb-1">o From which countries do your goods originate/where are they purchased?</p>
          <textarea class="form-control" rows="3" id="about-originate-countries" name="about_originate_countries">{{ isset($clientqa) ? $clientqa->about_originate_countries : ''}}</textarea>         
        </div> 
        <div class="ms-4 mb-3">
          <p class="mb-1">o Who are your key suppliers?</p>
          <textarea class="form-control" rows="3" id="about-suppliers" name="about_suppliers">{{ isset($clientqa) ? $clientqa->about_suppliers : ''}}</textarea>         
        </div>
        <div class="ms-4">
          <p class="mb-1">o Who is your main freight forwarder?</p>
          <textarea class="form-control" rows="3" id="about-freight" name="about_freight">{{ isset($clientqa) ? $clientqa->about_freight : ''}}</textarea>         
        </div>               
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Provide your bank details including:</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o Bank name, address, IBAN, SWIFT, and account number.</p>
          <textarea class="form-control" rows="3" id="about-bank-details" name="about_bank_details">{{ isset($clientqa) ? $clientqa->about_bank_details : ''}}</textarea>         
        </div>                
      </div>
      
      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Which finance/ERP system do you use?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="about-erp" name="about_erp">{{ isset($clientqa) ? $clientqa->about_erp : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Provide contact details for your ERP contact person (name, phone number, and email):</h6>          
        </div>
        <textarea class="form-control" rows="3" id="about-erp-contact" name="about_erp_contact">{{ isset($clientqa) ? $clientqa->about_erp_contact : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Who will be our main contact at your company? (name, phone number, and email):</h6>          
        </div>
        <textarea class="form-control" rows="3" id="about-main-contact" name="about_main_contact">{{ isset($clientqa) ? $clientqa->about_main_contact : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Can you confirm that the information on the attached CVR extract is up to date and complete?</h6>          
        </div>
        <textarea class="form-control" rows="3" id="about-cvr-contact" name="about_cvr_contact">{{ isset($clientqa) ? $clientqa->about_cvr_contact : ''}}</textarea>   
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">        
          <h6 class="mb-2">Which email address should we send invoices relating to our services to?</h6>          
        </div>        
        <input type="email" id="about-invoice-email" name="about_invoice_email" class="form-control" placeholder="johndoe@gmail.com" value="{{ isset($clientqa) ? $clientqa->about_invoice_email : ''}}" />
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Which email address should we use for requests for data and materials relating to VAT returns?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o Provide the name, email address, and phone number of the responsible person.</p>
          <textarea class="form-control" rows="3" id="about-invoice-contact" name="about_invoice_contact">{{ isset($clientqa) ? $clientqa->about_invoice_contact : ''}}</textarea>         
        </div>                
      </div>

      <div class="py-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Which email address should we use for forwarding scanned copies of your physical post received at IntraVAT’s address?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o Provide the name, email address, and phone number of the responsible person.</p>
          <textarea class="form-control" rows="3" id="about-scan-contact" name="about_scan_contact">{{ isset($clientqa) ? $clientqa->about_scan_contact : ''}}</textarea>         
        </div>                
      </div>

    </div>
  </div>
</div>
<!--/ About You -->

<!-- Your Director -->
<div class="col-lg-12 mb-4"> 
  <h5 class="text-dark fw-medium m-0 text-decoration-underline text-uppercase">Your Director</h5>
  <p class="text-start text-body">(Only to be completed if you are registering for VAT in the United Kingdom)</p>
  <div class="demo-inline-spacing mt-3">
    <div class="list-group">      
      <div class="pb-3 border-bottom">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Has your director changed their name during their lifetime?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, please obtain documentation from Borger.dk (if Danish) or other government web portal.</p>
          <input class="form-control" type="file" id="director-file-name" name="director_file_name" multiple>          
        </div> 

        @if(isset($clientqa))
          @if($clientqa->clientqafiles)
            <ul class="mt-4 mb-0 ms-4">
            @php
              $clientqafilekey = 1;
            @endphp
            @foreach($clientqa->clientqafiles as $clientqafile)
              @if($clientqafile->file_type == 'name')                      
                <li>{{ $clientqafile->o_file_name }}
                  <button type="button" class="btn btn-label-danger m-2 btn-delete-qa-file" title="Delete" data-client_id="{{ $client_id }}" data-file_id="{{ $clientqafile->id }}" data-file_type="{{ $clientqafile->file_type }}" data-file_type_title="QA Files">
                    <i class="bx bx-x me-1"></i>
                    <span class="align-middle">Delete</span>
                  </button>
                </li>
                @php
                  $clientqafilekey++;
                @endphp
              @endif
            @endforeach
            </ul>
          @endif  
        @endif               
      </div>
      
      <div class="py-3">
        <div class="d-flex justify-content-between w-100">
          <h6 class="mb-2">Has your director changed their address within the past three years?</h6>
        </div>        
        <div class="ms-4">
          <p class="mb-1">o If yes, please obtain documentation from Borger.dk(if Danish) or other government web portal.</p>
          <input class="form-control" type="file" id="director-file-address" name="director_file_address" multiple>
        </div>

        @if(isset($clientqa))
          @if($clientqa->clientqafiles)
            <ul class="mt-4 mb-0 ms-4">
            @php
              $clientqafilekey = 1;
            @endphp
            @foreach($clientqa->clientqafiles as $clientqafile)
              @if($clientqafile->file_type == 'address')                      
                <li>{{ $clientqafile->o_file_name }}
                  <button type="button" class="btn btn-label-danger m-2 btn-delete-qa-file" title="Delete" data-client_id="{{ $client_id }}" data-file_id="{{ $clientqafile->id }}" data-file_type="{{ $clientqafile->file_type }}" data-file_type_title="QA Files">
                    <i class="bx bx-x me-1"></i>
                    <span class="align-middle">Delete</span>
                  </button>
                </li>
                @php
                  $clientqafilekey++;
                @endphp
              @endif
            @endforeach
            </ul>
          @endif
        @endif                  
      </div>      
    </div>
  </div>
</div>
<!--/ Your Director -->