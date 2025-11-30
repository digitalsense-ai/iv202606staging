@php
$configData = Helper::appClasses();
@endphp

@foreach($client->clientqa as $clientqakey => $clientqa)
<div class="row mt-4">
  <!-- Country -->
  <div class="alert alert-primary text-center text-uppercase" role="alert">
    {{ isset($clientqa) ? $clientqa->country : ''}}
  </div>
  <!--/ Country -->
  <!-- Navigation -->
  <div class="col-lg-3 col-md-4 col-12 mb-md-0 mb-3">
    <div class="d-flex justify-content-between flex-column mb-2 mb-md-0">
      <ul class="nav nav-align-left nav-pills flex-column">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#est">
            <i class="bx bx-credit-card faq-nav-icon me-1"></i>
            <span class="align-middle">Establishment In The Country</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gs">
            <i class='bx bx-shopping-bag faq-nav-icon me-1'></i>
            <span class="align-middle">Goods/Services</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#eu">
            <i class='bx bx-rotate-left faq-nav-icon me-1'></i>
            <span class="align-middle">Intra-EU Acquisitions And Sales</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ie">
            <i class='bx bx-cube faq-nav-icon me-1'></i>
            <span class="align-middle">Import/Export</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#about">
            <i class='bx bx-cog faq-nav-icon me-1'></i>
            <span class="align-middle">About You</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#director">
            <i class='bx bx-cog faq-nav-icon me-1'></i>
            <span class="align-middle">Your Director</span>
          </button>
        </li>
      </ul>
      {{--<div class="d-none d-md-block">
        <div class="mt-5">
          <img src="{{asset('assets/img/illustrations/boy-working-'.$configData['style'].'.png')}}" class="img-fluid scaleX-n1" alt="FAQ Image" data-app-light-img="illustrations/boy-working-light.png" data-app-dark-img="illustrations/boy-working-dark.png">
        </div>
      </div>--}}
    </div>
  </div>
  <!-- /Navigation -->

  <!-- FAQ's -->
  <div class="col-lg-9 col-md-8 col-12">
    <div class="tab-content py-0">
      <!-- Establishment In The Country -->
      <div class="tab-pane fade show active" id="est" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">Establishment In The Country</span>
            </h5>
            <span>Get help with 'Establishment In The Country'</span>
          </div>
        </div>
        <div id="accordionEst" class="accordion accordion-header-primary">
          <div class="card accordion-item active">
            <h2 class="accordion-header">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#accordionEst-date-{{ $clientqakey }}" aria-controls="accordionEst-date-{{ $clientqakey }}">
                On which date would you like your VAT registration to be effective from?
              </button>
            </h2>

            <div id="accordionEst-date-{{ $clientqakey }}" class="accordion-collapse collapse show">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->est_date : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-name-{{ $clientqakey }}" aria-controls="accordionEst-name-{{ $clientqakey }}">
                Under which name would you like to be registered?
                <small>(NB: Must appear on your Company registgration extract as a primary or secondary name)</small>
              </button>
            </h2>
            <div id="accordionEst-name-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->est_name : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-warehouse-{{ $clientqakey }}" aria-controls="accordionEst-warehouse-{{ $clientqakey }}">
                Do you have a warehouse address in the country?
              </button>
            </h2>
            <div id="accordionEst-warehouse-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, provide the address.</p>
                  {{ isset($clientqa) ? $clientqa->est_warehouse_address : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o  Is it your own facility, a client’s warehouse, or e.g. an Amazon facility?</p>
                  {{ isset($clientqa) ? $clientqa->est_warehouse : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-new_warehouse-{{ $clientqakey }}" aria-controls="accordionEst-new_warehouse-{{ $clientqakey }}">
                Do you intend to establish a warehouse in the country?
              </button>
            </h2>
            <div id="accordionEst-new_warehouse-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->est_new_warehouse : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-showroom-{{ $clientqakey }}" aria-controls="accordionEst-showroom-{{ $clientqakey }}">
                Do you have a showroom in the country?
              </button>
            </h2>
            <div id="accordionEst-showroom-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o  If yes, provide the address.</p>
                  {{ isset($clientqa) ? $clientqa->est_showroom : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-branch-{{ $clientqakey }}" aria-controls="accordionEst-branch-{{ $clientqakey }}">
                Do you have a shop or branch in the country?
              </button>
            </h2>
            <div id="accordionEst-branch-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o  If yes, provide the address.</p>
                  {{ isset($clientqa) ? $clientqa->est_branch : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-office-{{ $clientqakey }}" aria-controls="accordionEst-office-{{ $clientqakey }}">
                Do you have an office in the country?
              </button>
            </h2>
            <div id="accordionEst-office-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, provide the address.</p>
                  {{ isset($clientqa) ? $clientqa->est_office : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o  If yes, are there local employees (see next question)?</p>
                  {{ isset($clientqa) ? $clientqa->est_office_employee : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-emp-{{ $clientqakey }}" aria-controls="accordionEst-emp-{{ $clientqakey }}">
                Do you have employees in the country?
              </button>
            </h2>
            <div id="accordionEst-emp-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <p class="mb-1 text-primary">o  If yes:</p>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">Do they have decision-making authority (i.e. can they enter into binding agreements)?</p>
                  {{ isset($clientqa) ? $clientqa->est_emp_authority : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">What are their roles?</p>
                  {{ isset($clientqa) ? $clientqa->est_emp_role : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">Are they permanent or project-based employees?</p>
                  {{ isset($clientqa) ? $clientqa->est_emp_type : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">For how long will they stay in the country?</p>
                  {{ isset($clientqa) ? $clientqa->est_emp_stay : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-agent-{{ $clientqakey }}" aria-controls="accordionEst-agent-{{ $clientqakey }}">
                Do you have an agent or representative in the country who is authorised to enter into binding agreements on your behalf?
              </button>
            </h2>
            <div id="accordionEst-agent-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->est_agent : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-invoice-{{ $clientqakey }}" aria-controls="accordionEst-invoice-{{ $clientqakey }}">
                Do you work with partners in the country (e.g. your goods are held in consignment by an agent like Amazon or Zalando)?
              </button>
            </h2>
            <div id="accordionEst-invoice-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, is the invoice to the end-customer in your name or the agent’s?</p>
                  {{ isset($clientqa) ? $clientqa->est_invoice : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-subcontractor-{{ $clientqakey }}" aria-controls="accordionEst-subcontractor-{{ $clientqakey }}">
                If you carry out construction/project work in the country, do you use subcontractors?
              </button>
            </h2>
            <div id="accordionEst-subcontractor-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, are they Danish or local subcontractors?</p>
                  {{ isset($clientqa) ? $clientqa->est_subcontractor : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-goods_value-{{ $clientqakey }}" aria-controls="accordionEst-goods_value-{{ $clientqakey }}">
                Do you expect to receive/purchase goods in the country (e.g. to support your new VAT registration or by transferring your own goods from your home country)?
              </button>
            </h2>
            <div id="accordionEst-goods_value-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what value do you expect in the first year?</p>
                  {{ isset($clientqa) ? $clientqa->est_goods_value : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-services_value-{{ $clientqakey }}" aria-controls="accordionEst-services_value-{{ $clientqakey }}">
                Do you expect to receive/purchase services in the country for your new VAT registration?
              </button>
            </h2>
            <div id="accordionEst-services_value-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what value do you expect in the first year?</p>
                  {{ isset($clientqa) ? $clientqa->est_services_value : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-industry_regulation-{{ $clientqakey }}" aria-controls="accordionEst-industry_regulation-{{ $clientqakey }}">
                Are there any specific industry regulations that apply to your business in the country?
              </button>
            </h2>
            <div id="accordionEst-industry_regulation-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->est_industry_regulation : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEst-cost_element-{{ $clientqakey }}" aria-controls="accordionEst-cost_element-{{ $clientqakey }}">
                What are your major cost elements?
              </button>
            </h2>
            <div id="accordionEst-cost_element-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->est_cost_element : ''}}
              </div>
            </div>
          </div>

        </div>
      </div>
      <!--/ Establishment In The Country -->

      <!-- Goods/Services -->
      <div class="tab-pane fade" id="gs" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">Goods/Services</span>
            </h5>
            <span>Get help with 'Goods/Services'</span>
          </div>
        </div>
        <div id="accordionGs" class="accordion accordion-header-primary">
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-desc-{{ $clientqakey }}" aria-controls="accordionGs-desc-{{ $clientqakey }}">
                Provide a comprehensive description of your business activity and commercial scope in the country, including goods flow and product types
              </button>
            </h2>

            <div id="accordionGs-desc-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->gs_desc : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-value-{{ $clientqakey }}" aria-controls="accordionGs-value-{{ $clientqakey }}">
                What is the expected value (in local currency) of goods to be imported/purchased during the first financial period in the country?
              </button>
            </h2>
            <div id="accordionGs-value-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->gs_value : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-annual_turnover-{{ $clientqakey }}" aria-controls="accordionGs-annual_turnover-{{ $clientqakey }}">
                What is the expected total annual turnover (in local currency) from sales of goods/services to customers in the country for the coming year?
              </button>
            </h2>
            <div id="accordionGs-annual_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->gs_annual_turnover : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-internal_consumption-{{ $clientqakey }}" aria-controls="accordionGs-internal_consumption-{{ $clientqakey }}">
                Do you use your goods/services for internal consumption in the country?
              </button>
            </h2>
            <div id="accordionGs-internal_consumption-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->gs_internal_consumption : ''}}
              </div>
            </div>
          </div>
          
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-sell-{{ $clientqakey }}" aria-controls="accordionGs-sell-{{ $clientqakey }}">
                If you sell goods:
              </button>
            </h2>
            <div id="accordionGs-sell-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">                
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o What goods do you sell?</p>
                  {{ isset($clientqa) ? $clientqa->gs_sell : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o What is the value of your goods (in local currency)?</p>
                  {{ isset($clientqa) ? $clientqa->gs_sell_value : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Do you provide free samples to customers/sellers?</p>
                  {{ isset($clientqa) ? $clientqa->gs_free_sample : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Do you use influencers or similar individuals who receive free products from you?</p>
                  {{ isset($clientqa) ? $clientqa->gs_influencer : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-vat_exempt-{{ $clientqakey }}" aria-controls="accordionGs-vat_exempt-{{ $clientqakey }}">
                Do you sell VAT-exempt goods?
              </button>
            </h2>
            <div id="accordionGs-vat_exempt-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <p class="mb-1">o If yes:</p>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">Which goods are exempt?</p>
                  {{ isset($clientqa) ? $clientqa->gs_vat_exempt : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">What is the expected turnover for this in the first year?</p>
                  {{ isset($clientqa) ? $clientqa->gs_vat_exempt_turnover : ''}}
                </div>                
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-service-{{ $clientqakey }}" aria-controls="accordionGs-service-{{ $clientqakey }}">
                If you sell services:
              </button>
            </h2>
            <div id="accordionGs-service-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">                
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Do you sell construction services (e.g. building/repairing fixed property)?</p>
                  {{ isset($clientqa) ? $clientqa->gs_service : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o What is the value of your construction services?</p>
                  {{ isset($clientqa) ? $clientqa->gs_service_value : ''}}
                </div>  
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Do you host events? For a fee? (e.g. conferences, training, etc.)</p>
                  {{ isset($clientqa) ? $clientqa->gs_event : ''}}
                </div>                
              </div>
            </div>
          </div>
          
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-market-{{ $clientqakey }}" aria-controls="accordionGs-market-{{ $clientqakey }}">
                Describe how you market your goods and/or services (e.g. via newsletters, website, social media) in the country.
              </button>
            </h2>
            <div id="accordionGs-market-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->gs_market : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionGs-real_estate-{{ $clientqakey }}" aria-controls="accordionGs-real_estate-{{ $clientqakey }}">
                Are you or will you be involved in real estate transactions in the country?
              </button>
            </h2>
            <div id="accordionGs-real_estate-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->gs_real_estate : ''}}
              </div>
            </div>
          </div>

        </div>
      </div>
      <!--/ Goods/Services -->

      <!-- Intra-EU Acquisitions And Sales -->
      <div class="tab-pane fade" id="eu" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">Intra-EU Acquisitions And Sales</span>
            </h5>
            <span>(Only to be completed if you are registering for VAT in an EU country)</span>
          </div>
        </div>
        <div id="accordionEu" class="accordion accordion-header-primary">
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEu-acquisition_turnover-{{ $clientqakey }}" aria-controls="accordionEu-acquisition_turnover-{{ $clientqakey }}">
                Do you expect to acquire/purchase/import goods from other EU countries (intra-EU supplies)?
              </button>
            </h2>

            <div id="accordionEu-acquisition_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what is the expected turnover from such acquisitions in the first year?</p>
                  {{ isset($clientqa) ? $clientqa->eu_acquisition_turnover : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEu-reg_export_turnover-{{ $clientqakey }}" aria-controls="accordionEu-reg_export_turnover-{{ $clientqakey }}">
                Do you expect to export goods from the EU country in which you are registering to other EU countries?
              </button>
            </h2>

            <div id="accordionEu-reg_export_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what is the expected turnover from such exports in the first year?</p>
                  {{ isset($clientqa) ? $clientqa->eu_reg_export_turnover : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEu-import_turnover-{{ $clientqakey }}" aria-controls="accordionEu-import_turnover-{{ $clientqakey }}">
                Do you expect to import goods from Third Countries into the EU country?
              </button>
            </h2>

            <div id="accordionEu-import_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what is the expected import turnover for the first year?</p>
                  {{ isset($clientqa) ? $clientqa->eu_import_turnover : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEu-export_turnover-{{ $clientqakey }}" aria-controls="accordionEu-export_turnover-{{ $clientqakey }}">
                Do you expect to export goods from the EU country to Third Countries?
              </button>
            </h2>

            <div id="accordionEu-export_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what is the expected export turnover for the first year?</p>
                  {{ isset($clientqa) ? $clientqa->eu_export_turnover : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionEu-export_owner-{{ $clientqakey }}" aria-controls="accordionEu-export_owner-{{ $clientqakey }}">
                (Only if your country of origin is NOT an EU member state) Will you take ownership of the goods in the EU country before selling them to customers?
              </button>
            </h2>

            <div id="accordionEu-export_owner-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o (e.g. transferring goods from your home VAT registration to the new EU VAT registration before delivery (DDP), or delivering to the border and letting the customer act as importer (DAP))</p>
                  {{ isset($clientqa) ? $clientqa->eu_export_owner : ''}}
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <!--/ Intra-EU Acquisitions And Sales -->

      <!-- Import/Export -->
      <div class="tab-pane fade" id="ie" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">Import/Export</span>
            </h5>
            <span>(Only to be completed if you are registering for VAT in a third country)</span>
          </div>
        </div>
        <div id="accordionIe" class="accordion accordion-header-primary">
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionIe-import_turnover-{{ $clientqakey }}" aria-controls="accordionIe-import_turnover-{{ $clientqakey }}">
                Do you expect to import goods from other countries?
              </button>
            </h2>

            <div id="accordionIe-import_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what is the expected import turnover for the first year?</p>
                  {{ isset($clientqa) ? $clientqa->ie_import_turnover : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionIe-export_turnover-{{ $clientqakey }}" aria-controls="accordionIe-export_turnover-{{ $clientqakey }}">
                Do you expect to export goods from the VAT registration country to other countries?
              </button>
            </h2>

            <div id="accordionIe-export_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, what is the expected export turnover for the first year?</p>
                  {{ isset($clientqa) ? $clientqa->ie_export_turnover : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionIe-export_owner-{{ $clientqakey }}" aria-controls="accordionIe-export_owner-{{ $clientqakey }}">
                Will you take ownership of the goods in the third country before selling them to customers?
              </button>
            </h2>

            <div id="accordionIe-export_owner-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o (e.g. transferring goods from your home VAT registration to the new VAT registration in the third country before delivery (DDP), or delivering to the border and letting the customer act as importer (DAP))</p>
                  {{ isset($clientqa) ? $clientqa->ie_export_owner : ''}}
                </div>
              </div>
            </div>
          </div>          

        </div>
      </div>
      <!--/ Import/Export -->

      <!-- About You -->
      <div class="tab-pane fade" id="about" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">About You</span>
            </h5>
            <span>(Only to be completed if you are registering for VAT in the United Kingdom)</span>
          </div>
        </div>
        <div id="accordionAbout" class="accordion accordion-header-primary">
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-vat_countries-{{ $clientqakey }}" aria-controls="accordionAbout-vat_countries-{{ $clientqakey }}">
                In which countries are you VAT-registered?
              </button>
            </h2>

            <div id="accordionAbout-vat_countries-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_vat_countries : ''}}
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-warehouse_countries-{{ $clientqakey }}" aria-controls="accordionAbout-warehouse_countries-{{ $clientqakey }}">
                In which countries do you have warehouses?
              </button>
            </h2>

            <div id="accordionAbout-warehouse_countries-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o List addresses.</p>
                  {{ isset($clientqa) ? $clientqa->about_warehouse_countries : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-sell_countries-{{ $clientqakey }}" aria-controls="accordionAbout-sell_countries-{{ $clientqakey }}">
                Which countries do you generally sell to?
              </button>
            </h2>

            <div id="accordionAbout-sell_countries-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_sell_countries : ''}}               
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-sell_goods-{{ $clientqakey }}" aria-controls="accordionAbout-sell_goods-{{ $clientqakey }}">
                If you sell goods:
              </button>
            </h2>

            <div id="accordionAbout-sell_goods-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o From which countries do your goods originate/where are they purchased?</p>
                  {{ isset($clientqa) ? $clientqa->about_originate_countries : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Who are your key suppliers?</p>
                  {{ isset($clientqa) ? $clientqa->about_suppliers : ''}}
                </div>
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Who is your main freight forwarder?</p>
                  {{ isset($clientqa) ? $clientqa->about_freight : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-bank_details-{{ $clientqakey }}" aria-controls="accordionAbout-bank_details-{{ $clientqakey }}">
                Provide your bank details including:
              </button>
            </h2>

            <div id="accordionAbout-bank_details-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Bank name, address, IBAN, SWIFT, and account number.</p>
                  {{ isset($clientqa) ? $clientqa->about_bank_details : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-erp-{{ $clientqakey }}" aria-controls="accordionAbout-erp-{{ $clientqakey }}">
                Which finance/ERP system do you use?
              </button>
            </h2>

            <div id="accordionAbout-erp-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_erp : ''}}               
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-erp_contact-{{ $clientqakey }}" aria-controls="accordionAbout-erp_contact-{{ $clientqakey }}">
                Provide contact details for your ERP contact person (name, phone number, and email):
              </button>
            </h2>

            <div id="accordionAbout-erp_contact-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_erp_contact : ''}}               
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-main_contact-{{ $clientqakey }}" aria-controls="accordionAbout-main_contact-{{ $clientqakey }}">
                Who will be our main contact at your company? (name, phone number, and email):
              </button>
            </h2>

            <div id="accordionAbout-main_contact-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_main_contact : ''}}               
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-cvr_contact-{{ $clientqakey }}" aria-controls="accordionAbout-cvr_contact-{{ $clientqakey }}">
                Can you confirm that the information on the attached CVR extract is up to date and complete?
              </button>
            </h2>

            <div id="accordionAbout-cvr_contact-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_cvr_contact : ''}}               
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-invoice_email-{{ $clientqakey }}" aria-controls="accordionAbout-invoice_email-{{ $clientqakey }}">
                Which email address should we send invoices relating to our services to?
              </button>
            </h2>

            <div id="accordionAbout-invoice_email-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                {{ isset($clientqa) ? $clientqa->about_invoice_email : ''}}               
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-invoice_contact-{{ $clientqakey }}" aria-controls="accordionAbout-invoice_contact-{{ $clientqakey }}">
                Which email address should we use for requests for data and materials relating to VAT returns?
              </button>
            </h2>

            <div id="accordionAbout-invoice_contact-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Provide the name, email address, and phone number of the responsible person.</p>
                  {{ isset($clientqa) ? $clientqa->about_invoice_contact : ''}}
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionAbout-scan_contact-{{ $clientqakey }}" aria-controls="accordionAbout-scan_contact-{{ $clientqakey }}">
                Which email address should we use for forwarding scanned copies of your physical post received at IntraVAT’s address?
              </button>
            </h2>

            <div id="accordionAbout-scan_contact-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o Provide the name, email address, and phone number of the responsible person.</p>
                  {{ isset($clientqa) ? $clientqa->about_scan_contact : ''}}
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <!--/ About You -->

      <!-- Your Director -->
      <div class="tab-pane fade" id="director" role="tabpanel">
        <div class="d-flex mb-3 gap-3">
          <div>
            <span class="badge bg-label-primary rounded-2 p-2">
              <i class="bx bx-credit-card fs-3 lh-1"></i>
            </span>
          </div>
          <div>
            <h5 class="mb-0">
              <span class="align-middle">Your Director</span>
            </h5>
            <span>(Only to be completed if you are registering for VAT in the United Kingdom)</span>
          </div>
        </div>
        <div id="accordionDirector" class="accordion accordion-header-primary">
          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionDirector-import_turnover-{{ $clientqakey }}" aria-controls="accordionDirector-import_turnover-{{ $clientqakey }}">
                Has your director changed their name during their lifetime?
              </button>
            </h2>

            <div id="accordionDirector-import_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, please obtain documentation from Borger.dk (if Danish) or other government web portal.</p>
                  @if($clientqa->clientqafiles)
                    @php
                      $clientqafilekey = 1;
                    @endphp
                    @foreach($clientqa->clientqafiles as $clientqafile)
                      @if($clientqafile->file_type == 'name')                      
                        <li>{{ $clientqafilekey . '. ' . $clientqafile->o_file_name }}</li>
                        @php
                          $clientqafilekey++;
                        @endphp
                      @endif
                    @endforeach
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="card accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionDirector-export_turnover-{{ $clientqakey }}" aria-controls="accordionDirector-export_turnover-{{ $clientqakey }}">
                Has your director changed their address within the past three years?
              </button>
            </h2>

            <div id="accordionDirector-export_turnover-{{ $clientqakey }}" class="accordion-collapse collapse">
              <div class="accordion-body">
                <div class="ms-4 mb-3">
                  <p class="mb-1 text-primary">o If yes, please obtain documentation from Borger.dk(if Danish) or other government web portal.</p>
                  @if($clientqa->clientqafiles)
                    @php
                      $clientqafilekey = 1;
                    @endphp
                    @foreach($clientqa->clientqafiles as $clientqafile)
                      @if($clientqafile->file_type == 'address')                      
                        <li>{{ $clientqafilekey . '. ' . $clientqafile->o_file_name }}</li>
                        @php
                          $clientqafilekey++;
                        @endphp
                      @endif
                    @endforeach
                  @endif  
                </div>
              </div>
            </div>
          </div>                  

        </div>
      </div>
      <!--/ Your Director -->
    </div>
  </div>
  <!-- /FAQ's -->
</div>
@endforeach