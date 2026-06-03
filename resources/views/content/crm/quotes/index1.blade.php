@extends('layouts/layoutMaster')

@section('title', 'CRM - Quotes')

@section('page-style')

@endsection

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="row mb-3">

        <div class="col-md-6">
            <h3 class="mb-0">Quotes Management</h3>
            <small class="text-muted">
                CRM / Quotes
            </small>
        </div>

        <div class="col-md-6 text-end">

            <a href="{{ route('quotes.index',['status'=>'active']) }}"
               class="btn {{ $status == 'active' ? 'btn-primary' : 'btn-light' }}">
                Active Quotes
            </a>

            <a href="{{ route('quotes.index',['status'=>'negotiation']) }}"
               class="btn {{ $status == 'negotiation' ? 'btn-warning' : 'btn-light' }}">
                Under Negotiation
            </a>

            <a href="{{ route('quotes.index',['status'=>'approved']) }}"
               class="btn {{ $status == 'approved' ? 'btn-success' : 'btn-light' }}">
                Approved
            </a>

            <a href="{{ route('quotes.index',['status'=>'rejected']) }}"
               class="btn {{ $status == 'rejected' ? 'btn-danger' : 'btn-light' }}">
                Rejected
            </a>

        </div>

    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- QUOTES TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="row">

                <div class="col-md-6">
                    <h5 class="mb-0">
                        {{ ucfirst($status) }} Quotes
                    </h5>
                </div>

                <div class="col-md-6 text-end">

                    <form method="GET"
                          action="{{ route('quotes.index') }}"
                          class="d-inline-flex">

                        <input type="hidden"
                               name="status"
                               value="{{ $status }}">

                        <input type="text"
                               name="search"
                               class="form-control form-control-sm me-2"
                               placeholder="Search company">

                        <button class="btn btn-sm btn-primary">
                            Search
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="card-body p-0">

            <table class="table table-hover mb-0 align-middle">

                <thead class="table-light">

                    <tr>
                        <th>#</th>
                        <th>Company</th>
                        <th>Package</th>
                        <th>Version</th>
                        <th>Monthly</th>
                        <th>Registration</th>
                        <th>Yearly</th>
                        <th>Status</th>
                        <th width="260">Actions</th>
                    </tr>

                </thead>

                <tbody>

                @forelse($quotes as $quote)

                    @php

                        $addonTotal = $quote->addons
                            ->where('enabled', true)
                            ->sum('price');

                        $monthly =
                            $quote->base_price + $addonTotal;

                        $yearly = $monthly * 12;

                    @endphp

                    <tr>

                        <td>
                            #{{ $quote->id }}
                        </td>

                        <td>
                            <strong>
                                {{ $quote->lead->company_name ?? '-' }}
                            </strong>

                            <br>

                            <small class="text-muted">
                                {{ $quote->lead->cvr_number ?? '' }}
                            </small>
                        </td>

                        <td>
                            <span class="badge bg-info">
                                {{ ucwords(str_replace('_',' ',$quote->package)) }}
                            </span>
                        </td>

                        <td>
                            V{{ $quote->version }}
                        </td>

                        <td>
                            {{ number_format($monthly,2) }}
                        </td>

                        <td>
                            {{ number_format($quote->registration_price,2) }}
                        </td>

                        <td>
                            {{ number_format($yearly,2) }}
                        </td>

                        <td>

                            @if($quote->status == 'active')
                                <span class="badge bg-primary">
                                    Active
                                </span>
                            @endif

                            @if($quote->status == 'negotiation')
                                <span class="badge bg-warning text-dark">
                                    Negotiation
                                </span>
                            @endif

                            @if($quote->status == 'approved')
                                <span class="badge bg-success">
                                    Approved
                                </span>
                            @endif

                            @if($quote->status == 'rejected')
                                <span class="badge bg-danger">
                                    Rejected
                                </span>
                            @endif

                        </td>

                        <td>

                            {{-- VIEW --}}
                            <a href="{{ route('quotes.show',$quote->id) }}"
                               class="btn btn-sm btn-outline-secondary">
                                View
                            </a>

                            {{-- EDIT --}}
                            <a href="{{ route('quotes.edit',$quote->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>

                            {{-- NEGOTIATION --}}
                            @if($quote->status != 'approved')

                            <form method="POST"
                                  action="{{ route('quotes.negotiate',$quote->id) }}"
                                  class="d-inline">

                                @csrf

                                <button class="btn btn-sm btn-outline-warning">
                                    Negotiate
                                </button>

                            </form>

                            @endif

                            {{-- APPROVE --}}
                            @if($quote->status != 'approved')

                            <form method="POST"
                                  action="{{ route('quotes.change-status',$quote->id) }}"
                                  class="d-inline">

                                @csrf

                                <input type="hidden"
                                       name="status"
                                       value="approved">

                                <button class="btn btn-sm btn-outline-success">
                                    Approve
                                </button>

                            </form>

                            @endif

                            {{-- REJECT --}}
                            @if($quote->status != 'rejected')

                            <button
                                class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal{{ $quote->id }}">
                                Reject
                            </button>

                            @endif

                        </td>

                    </tr>

                    {{-- REJECT MODAL --}}
                    <div class="modal fade"
                         id="rejectModal{{ $quote->id }}"
                         tabindex="-1">

                        <div class="modal-dialog">

                            <form method="POST"
                                  action="{{ route('quotes.change-status',$quote->id) }}">

                                @csrf

                                <input type="hidden"
                                       name="status"
                                       value="rejected">

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Reject Quote
                                        </h5>
                                    </div>

                                    <div class="modal-body">

                                        <label>
                                            Reminder Date
                                        </label>

                                        <input type="date"
                                               name="reminder_date"
                                               class="form-control mb-2">

                                        <label>
                                            Reminder Time
                                        </label>

                                        <input type="time"
                                               name="reminder_time"
                                               class="form-control mb-2">

                                        <label>
                                            Notes
                                        </label>

                                        <textarea name="notes"
                                                  class="form-control"></textarea>

                                    </div>

                                    <div class="modal-footer">

                                        <button class="btn btn-danger">
                                            Reject Quote
                                        </button>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                @empty

                    <tr>

                        <td colspan="9"
                            class="text-center py-5">

                            No quotes found

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="card-footer bg-white">
            {{ $quotes->links() }}
        </div>

    </div>

</div>
@endsection