@extends('layouts/layoutMaster')

@section('title', 'Email List')

@section('vendor-style')

@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
  <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}" />
@endsection

@section('vendor-script')

@endsection

@section('page-script')
<script src="{{asset('js/dv-common.js')}}"></script>
<script src="{{asset('js/dv-analyze-pdf.js')}}"></script>
@endsection

@section('content')

<h2>Inbox for {{ env('MS_MAILBOX') }}</h2>

@foreach($emails as $email)
    <div style="margin-bottom: 20px;">
        <strong>Subject:</strong> {{ $email['subject'] ?? '' }}<br>
        <strong>From:</strong> {{ $email['from']['emailAddress']['address'] ?? '' }}<br>

        @if(!empty($email['attachments']))
            <strong>Attachments:</strong>
            <ul>
                @foreach($email['attachments'] as $file)
                    <li>{{ $file }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach

@endsection