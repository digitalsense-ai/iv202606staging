@extends('layouts/layoutMaster')

@section('title', 'Table convert')

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
@endsection

@section('content')
<form method="POST" action="{{ route('convert.table') }}">
    @csrf
    <textarea name="table" style="width:100%;height:200px;" placeholder="Paste table here..."></textarea>
    <br><br>
    <button type="submit">Convert to Excel</button>
</form>

@endsection
