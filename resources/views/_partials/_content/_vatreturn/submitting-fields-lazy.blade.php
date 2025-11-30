<!-- Submitting Fields --> 
@php
	$pivsmonthtotal = 0; 
	foreach ($pivs_files as $pivs_file)
	{
		if($pivs_file->folder_id != NULL)
			$pivsmonthtotal += $pivs_file->month_total; 
	}	

	$c79numbers = 0; 
	foreach ($c79_documents as $c79_document)
	{
		if($c79_document->folder_id != NULL)
			$c79numbers += $c79_document->doc_numbers; 
	}

	if($submitting_fields)
	{
		$box1 = $submitting_fields->box_1;
		$box2 = $submitting_fields->box_2;
		$box3 = $submitting_fields->box_3;
		$box4 = $submitting_fields->box_4;
		$box5 = $submitting_fields->box_5;
		$box6 = $submitting_fields->box_6;
		$box7 = $submitting_fields->box_7;
		$box8 = $submitting_fields->box_8;
		$box9 = $submitting_fields->box_9;
	}
	else
	{
		$box1 = $pivsmonthtotal + $salestotalvat;
		$box2 = 0;
		$box3 = $box1 + $box2;
		$box4 = $c79numbers + $pivsmonthtotal + $purchasetotalvat;
		$box5 = $box3 - $box4;
		$box6 = $salestotalnet;
		$box7 = (($c79numbers/0.2) + ($pivsmonthtotal/0.2) + $purchasetotalnet);
		$box8 = 0;
		$box9 = 0;
	}

	$readonly = 'readonly';
	if($box1 == 0 && $box2 == 0 && $box3 == 0 && $box4 == 0 && $box5 == 0 && $box6 == 0 && $box7 == 0 && $box8 == 0 && $box9 == 0)
		$readonly = '';

	$purchasenetamount = 0;//Delete once when the PIVS reload tasks completed
@endphp	
<form id="formSubmittingFields-{{ $vat_reg_id }}" class="needs-validation formSubmittingFields" novalidate data-vatid="{{ $vat_reg_id }}" data-country="GB">
@csrf
		
	<input type="hidden" id="pivsmonthtotal-{{ $vat_reg_id }}" value="{{ $pivsmonthtotal }}" required>
	<input type="hidden" id="c79numbers-{{ $vat_reg_id }}" value="{{ $c79numbers }}" required>

	<div class="row g-3">
		<div class="col-12">	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 1</strong></p>
			      <span>VAT due in the period on sales and other outputs</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box1, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-1" name="submittingfields_box_1" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 2</strong></p>
			      <span>VAT due in the period on acquisitions of goods made in Northern Ireland from EU Member States</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box2, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-2" name="submittingfields_box_2" {{ $readonly }} required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 3</strong></p>
			      <span>Total VAT due (this is the total of box 1 and 2)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box3, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-3" name="submittingfields_box_3" onkeypress="return isDecimal(event, this)" required {{ $readonly }} />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 4</strong></p>
			      <span>VAT reclaimed in the period on purchases, c79 and other inputs (including acquisitions in Northern Ireland from EU member states)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box4, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-4" name="submittingfields_box_4" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 5</strong></p>
			      <span>Net VAT to pay to HMRC or reclaim (this is the difference between box 3 and 4)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box5, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-5" name="submittingfields_box_5" onkeypress="return isDecimal(event, this)" required {{ $readonly }} />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 6</strong></p>
			      <span>Total value of sales and other supplies, excluding VAT</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box6, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-6" name="submittingfields_box_6" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 7</strong></p>
			      <span>Total value of purchases and all other inputs excluding any VAT</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box7, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-7" name="submittingfields_box_7" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 8</strong></p>
			      <span>Total value of dispatches of goods and related costs (excluding VAT) from Northern Ireland to EU Member States</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box8, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-8" name="submittingfields_box_8" {{ $readonly }} required />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 9</strong></p>
			      <span>Total value of acquisitions of goods and related costs (excluding VAT) made in Northern Ireland from EU Member States</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ number_format($box9, 2, '.', '') }}" id="submittingfields-box-{{ $vat_reg_id }}-9" name="submittingfields_box_9" {{ $readonly }} required />
			    </div>
			  </li>
			</ul>
		</div>
		<div class="col-12">
			<button type="submit" class="btn btn-primary float-end submittingfields-save" {{ (!$vatregmain_status || $vatreg_is_disregard) ? 'disabled=disabled' : '' }}>Save</button> 
		</div>

		@php
		/* DON'T DELETE	
		@if($authUser->role == 'team-user' || $authUser->role == 'client-user')	
		<div class="col-12">
			<div class="form-check">
				<input class="form-check-input submittingfields-declaration" type="checkbox" value="" id="submittingfields-declaration-{{ $vat_reg_id }}" name="submittingfields_declaration" checked="" data-vatid="{{ $vat_reg_id }}">
				<label class="form-check-label" for="submittingfields-declaration-{{ $vat_reg_id }}">
					@if($authUser->role == 'client-user')
						When you submit this VAT information you are making a legal declaration that the information is true and complete. A false declaration can result in prosecution.
					@elseif($authUser->role == 'team-user')	
						I confirm that my client has received a copy of the information contained in this return and approved the information as being correct and complete to the best of their knowledge and belief.
					@endif
				</label>
			</div>
		</div>

		<div class="col-12">
			<button type="submit" class="btn btn-primary me-sm-3 me-1 submittingfields" disabled>Submit</button> 
		</div>
		@endif
		*/
		@endphp
	</div>
</form>
<!--/ Submitting Fields -->  