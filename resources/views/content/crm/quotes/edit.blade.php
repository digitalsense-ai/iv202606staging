@extends('layouts/layoutMaster')

@section('title', 'CRM - Leads')

@section('page-style')

@endsection

@section('content')

<div class="card">
  <div class="card-header">
    Quote Versions
  </div>

  <div class="card-body">
    @foreach($quote->versions as $version)
      <div>
        Version {{ $version->version }}
        <a href="{{ route('quotes.edit',$version->id) }}">Open</a>
      </div>
    @endforeach

    <form method="POST" action="{{ route('quotes.duplicate',$quote->id) }}">
      @csrf
      <button class="btn btn-primary">Create Negotiation Version</button>
    </form>
  </div>
</div>
@endsection