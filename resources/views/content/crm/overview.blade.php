@extends('layouts/layoutMaster')

@section('title', 'CRM - Overview')

@section('page-style')
 
@endsection

@section('content')

<!-- Cards with few info -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <a href="{{ route('leads.index') }}" class="text-decoration-none text-reset">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-primary"><i class='bx bx-purchase-tag fs-4'></i></span>
              </div>
              <div class="card-info">
                <h5 class="card-title mb-0 me-2">{{ $lead_total }}</h5>
                <small class="text-muted">Leads</small>
              </div>
            </div>
            <div id="conversationChart"></div>
          </div>
        </a>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <a href="{{ route('quotes.index') }}" class="text-decoration-none text-reset">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-success"><i class='bx bx-dollar fs-4'></i></span>
              </div>
              <div class="card-info">
                <h5 class="card-title mb-0 me-2">{{ $active_quote_total }}</h5>
                <small class="text-muted">Active Quotes</small>
              </div>
            </div>
            <div id="incomeChart"></div>
          </div>
        </a>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <a href="{{ route('quotes.approved') }}" class="text-decoration-none text-reset">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-warning"><i class='bx bx-wallet fs-4'></i></span>
              </div>
              <div class="card-info">
                <h5 class="card-title mb-0 me-2">{{ $approved_quote_total }}</h5>
                <small class="text-muted">Approved Quotes</small>
              </div>
            </div>
            <div id="profitChart"></div>
          </div>
        </a>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body">
        <a href="{{ route('quotes.rejected') }}" class="text-decoration-none text-reset">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-danger"><i class='bx bx-x fs-4'></i></span>
              </div>
              <div class="card-info">
                <h5 class="card-title mb-0 me-2">{{ $rejected_quote_total }}</h5>
                <small class="text-muted">Rejected Quotes</small>
              </div>
            </div>
            <div id="expensesLineChart"></div>
          </div>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row">

    @foreach(['active','approved','rejected'] as $status)

        <!-- Earning Reports -->
        <div class="col-md-6 col-lg-4 col-xl-4 mb-4">
            <div class="card h-100">
              <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                  <h5 class="m-0 me-2">{{ ucfirst($status) }} Reports</h5>
                  <small class="text-muted">{{ ucfirst($status) }} Quotes Overview</small>
                </div>
                <!-- <div class="dropdown">
                  <button class="btn p-0" type="button" id="earningReports" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReports">
                    <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                    <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                    <a class="dropdown-item" href="javascript:void(0);">Share</a>
                  </div>
                </div> -->
              </div>
              <div class="card-body pb-0">
                <ul class="p-0 m-0">
                  <li class="d-flex mb-4 pb-1">
                    <div class="avatar flex-shrink-0 me-3">
                      <span class="avatar-initial rounded bg-label-primary"><i class='bx bx-trending-up'></i></span>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                      <div class="me-2">
                        <h6 class="mb-0">Registration fees</h6>
                        <small class="text-muted">$ {{ $data[$status]->registration_total ?? 0 }}</small>
                      </div>
                      <!-- <div class="user-progress">
                        <small class="fw-medium">$1,619</small><i class='bx bx-chevron-up text-success ms-1'></i> <small class="text-muted">18.6%</small>
                      </div> -->
                    </div>
                  </li>
                  <li class="d-flex mb-4 pb-1">
                    <div class="avatar flex-shrink-0 me-3">
                      <span class="avatar-initial rounded bg-label-success"><i class='bx bx-dollar'></i></span>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                      <div class="me-2">
                        <h6 class="mb-0">Monthly hosting</h6>
                        <small class="text-muted">$ {{ $data[$status]->package_total ?? 0 }}</small>
                      </div>
                      <!-- <div class="user-progress">
                        <small class="fw-medium">$3,571</small><i class='bx bx-chevron-up text-success ms-1'></i> <small class="text-muted">39.6%</small>
                      </div> -->
                    </div>
                  </li>
                  <li class="d-flex mb-4 pb-1">
                    <div class="avatar flex-shrink-0 me-3">
                      <span class="avatar-initial rounded bg-label-secondary"><i class='bx bx-credit-card'></i></span>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                      <div class="me-2">
                        <h6 class="mb-0">Yearly hosting</h6>
                        <small class="text-muted">$ {{ ($data[$status]->package_total ?? 0) * 12 }}</small>
                      </div>
                      <!-- <div class="user-progress">
                        <small class="fw-medium">$430</small><i class='bx bx-chevron-up text-success ms-1'></i> <small class="text-muted">52.8%</small>
                      </div> -->
                    </div>
                  </li>
                </ul>
                <div id="reportBarChart"></div>
              </div>
            </div>
        </div>
        <!--/ {{ ucfirst($status) }} Reports -->
    @endforeach

</div>

@endsection