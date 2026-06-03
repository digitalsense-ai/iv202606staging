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

                @foreach($quotes as $quote)

<tr class="table-primary">

    <td>
        <strong>#{{ $quote->id }}</strong>
    </td>

    <td>
        {{ $quote->lead->company_name }}
    </td>

    <td>
        ORIGINAL
    </td>

    <td>
        V{{ $quote->version }}
    </td>

    <td>
        {{ ucfirst($quote->status) }}
    </td>

    <td>
        <a href="{{ route('quotes.show',$quote->id) }}"
           class="btn btn-sm btn-primary">

           Open

        </a>
    </td>

</tr>

{{-- CHILD NEGOTIATION VERSIONS --}}
@foreach($quote->children as $child)

<tr>

    <td class="ps-5">
        └── #{{ $child->id }}
    </td>

    <td>
        Negotiation Copy
    </td>

    <td>
        LINKED
    </td>

    <td>
        V{{ $child->version }}
    </td>

    <td>

        @if($child->status == 'negotiation')
            <span class="badge bg-warning">
                Negotiation
            </span>
        @endif

        @if($child->status == 'approved')
            <span class="badge bg-success">
                Approved
            </span>
        @endif

        @if($child->status == 'rejected')
            <span class="badge bg-danger">
                Rejected
            </span>
        @endif

    </td>

    <td>

        <a href="{{ route('quotes.show',$child->id) }}"
           class="btn btn-sm btn-outline-secondary">

           Open

        </a>

    </td>

</tr>

@endforeach

@endforeach

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