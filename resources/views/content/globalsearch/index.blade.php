@extends('layouts/layoutMaster')

@section('title', 'Global Search - View')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('page-style')

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/dv-global-search.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4"><span class="text-muted fw-light">Global Search & FTP/</span> Full refresh</h4>

<input type="hidden" name="pending_batches" id="pending_batches" value="{{ count($batchIds) }}">
<input type="hidden" name="has_pending" id="has_pending" value="">
<div class="col-3">
  <label for="client" class="form-label">Choose Client and Click Refresh</label>        
  <select id="client" class="form-select form-select-lg" data-allow-clear="true" name="client">
  	<option value="">--Select Company--</option>
    @foreach($clients as $key=>$client)
      @foreach($client->vatregmain as $vrmain) 
        <option value="{{ $client->id }}">{{ $client->client_name . ' - ' . $vrmain->country }}</option>   	
		    {{--<option value="{{ $client->id }}">{{ $client->client_name . ' - ' . (($client->vatregmain) ? (($client->vatregmain->first()) ? $client->vatregmain->first()->country : '') : '') }}</option>--}}
      @endforeach  	
	  @endforeach 
  </select>
  <h4 class="my-2">(OR)</h4>
</div>

<button type="button" class="btn btn-primary btn-refresh-global-search" disabled="disabled">Refresh</button>

<div class="progress my-2" style="display: none;">
    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" 
         aria-valuemin="0" aria-valuemax="100" id="progressBar" style="width: 0%;">
        <span class="sr-only">0%</span>
    </div>
</div>
@endsection
