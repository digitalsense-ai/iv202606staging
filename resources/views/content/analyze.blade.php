@extends('layouts/layoutMaster')

@section('title', 'Analyze PDF')

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
@endsection

@section('content')
	
	@if(session('result'))
        <div class="alert alert-success">
            <h5>Analysis Result:</h5>
            <pre>{{ json_encode(session('result'), JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/analyze-pdf') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Choose PDF File</label>
            <input type="file" class="form-control" name="file" id="file" accept="application/pdf" required>
        </div>
        <button type="submit" class="btn btn-primary">Analyze PDF</button>
    </form>

@endsection
