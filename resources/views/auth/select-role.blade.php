@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Sign in :: Slect Role')

@section('vendor-style')

@endsection

@section('page-style')

@endsection

@section('vendor-script')

@endsection

@section('page-script')

@endsection

@section('content')
<!-- Modal -->
<div class="modal show d-block" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('set.role') }}">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Your Role</h5>
            </div>
            <div class="modal-body">
                <select name="role_id" class="form-select" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucwords(str_replace('-', ' ', $role->name)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>
        </div>
    </form>
  </div>
</div>
@endsection