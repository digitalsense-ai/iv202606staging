@extends('layouts/layoutMaster')

@section('title', 'Table convert')

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
@endsection

@section('content')
<form method="POST" action="{{ route('hmrc.validate') }}">
    @csrf   
    <button type="submit">Validate HMRC</button>
</form>

@endsection
