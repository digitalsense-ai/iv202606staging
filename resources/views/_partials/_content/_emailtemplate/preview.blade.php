<div class="row">
	<div class="col-md-12 mb-4 email-template-preview" id="email-template-preview-{{ $key }}">
		{{--@php   		
			$arrKeys = ["draft", "newuser", "pivs", "documents", "c79", "comments", "importvatfile", "cashaccountstatement", "dutydefermentaccount", "lock"];
		@endphp
		@if(in_array($key, $arrKeys))   
    	<span class="btn btn-label-primary float-end mb-3 email-template-edit" data-id="{{ $key }}"><i class='bx bx-edit-alt'></i>Edit</span>
    	@endif
    	src="{{ url('email-preview/'.$key) }}"   
    	--}}
    	<span class="btn btn-label-primary float-end mb-3 email-template-edit" data-id="{{ $key }}"><i class='bx bx-edit-alt'></i>Edit</span>
    	<iframe id="iframe-email-template-{{ $key }}" title="email-template" width="100%" style="height: 100vh;"></iframe>  	
    </div>
    {{--@if(in_array($key, $arrKeys))--}}
    <div class="col-md-12 mb-4 full-editor" style="display: none;" id="full-editor-{{ $key }}">
    	<form id="formEmailTemplate-{{ $key }}" class="card-body needs-validation" novalidate data-id="{{ $key }}">
        @csrf
        	<textarea name="text_quill" style="display: none;" id="text-quill-{{ $key }}"></textarea>
			<!-- Full Editor -->	  
		    <div id="full-editor-content-{{ $key }}">
		    	
		    </div>	  
			<!-- /Full Editor -->  	
			<button type="submit" class="btn btn-primary float-end mt-3 email-template-update">Update</button>
			<!-- <a href="{{ url('email-templates') }}" class="btn btn-label-secondary float-end mt-3 mx-2">Cancel</a> -->
			<span class="btn btn-label-secondary float-end mt-3 mx-2 email-template-cancel" data-id="{{ $key }}">Cancel</span>
		</form>
	</div>
	{{--@endif--}}  
</div>       