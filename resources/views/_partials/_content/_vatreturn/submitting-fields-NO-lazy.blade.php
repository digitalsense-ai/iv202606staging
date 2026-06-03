<!-- Submitting Fields --> 
@php
	/*
	$pivsmonthtotal = 0; 
	foreach ($pivs_files as $key => $pivs_file)
	{
		if($pivs_file->folder_id != NULL)
			$pivsmonthtotal += $pivs_file->month_total; 
	}	
	*/

	if($import_vat_files)
	{
		$box_3 = 0;
        $box_31 = 0;
        $box_33 = 0;
        $box_5 = 0;
        $box_6 = 0;

        $box_52 = 0;

        $box_1 = 0;
        $box_11 = 0;
        $box_13 = 0;

        $box_32 = 0;
        $box_12 = 0;

        $box_51 = 0;
        $box_91 = 0;
        $box_92 = 0;
        
        $box_86 = 0;
        $box_87 = 0;
        $box_88 = 0;
        $box_89 = 0;

        $box_81 = 0;  
        $box_14 = 0;
        $box_82 = 0;
        $box_15 = 0;
        $box_83 = 0;
        $box_84 = 0;
        $box_85 = 0;	

        $total_statistical_amount = 0;
        $box_81_percentage = 1;				
		$box_83_percentage = 0;
		$box_85_percentage = 0;
	
		foreach ($import_vat_files as $key => $import_vat_file)
		{
			$fee_number = $import_vat_file->fee_number;
			$statistical_number = $import_vat_file->statistical_number;

			//$box_81 += ($fee_number + $statistical_number);
			
			$total_statistical_amount += ($fee_number + $statistical_number);			
					
			/*			
			if($salestotalvat > 0)
			{				
				$box_81_percentage = ($sales_standard_totalvat/$salestotalvat) * 100;				
				$box_83_percentage = ($sales_medium_totalvat/$salestotalvat) * 100;
				$box_85_percentage = ($sales_zero_totalvat/$salestotalvat) * 100;
			}
			*/

			if($salestotalnet > 0)
			{				
				$box_81_percentage = ($sales_standard_totalnet/$salestotalnet);			
				$box_83_percentage = ($sales_medium_totalnet/$salestotalnet);
				$box_85_percentage = ($sales_zero_totalnet/$salestotalnet);
			}
			
			$e_fee_number = $import_vat_file->e_fee_number;
			$e_statistical_number = $import_vat_file->e_statistical_number;
			$box_85 += $import_vat_file->box_85 + $e_fee_number;

			if($import_vat_file->box_85 == 0)
				$box_52 += ($e_fee_number + $e_statistical_number);
			else
				$box_52 += $e_statistical_number;	
		}

		if($total_statistical_amount > 0)
		{
			$box_81 = ($total_statistical_amount * $box_81_percentage);			
			$box_83 = ($total_statistical_amount * $box_83_percentage);
			//$box_85 = ($total_statistical_amount * $box_85_percentage);
		}

		if($submitting_fields)
		{
			$box_3 = $submitting_fields->box_3;
            $box_31 = $submitting_fields->box_31;
            $box_33 = $submitting_fields->box_33;
            $box_5 = $submitting_fields->box_5;
            $box_6 = $submitting_fields->box_6;

            $box_52 = $submitting_fields->box_52;

            $box_1 = $submitting_fields->box_1;
            $box_11 = $submitting_fields->box_11;
            $box_13 = $submitting_fields->box_13;

            $box_32 = $submitting_fields->box_32;
            $box_12 = $submitting_fields->box_12;

            $box_51 = $submitting_fields->box_51;
            $box_91 = $submitting_fields->box_91;
            $box_92 = $submitting_fields->box_92;
            
            $box_86 = $submitting_fields->box_86;
            $box_87 = $submitting_fields->box_87;
            $box_88 = $submitting_fields->box_88;
            $box_89 = $submitting_fields->box_89;

            $box_81 = $submitting_fields->box_81;  
            $box_14 = $submitting_fields->box_14;
            $box_82 = $submitting_fields->box_82;
            $box_15 = $submitting_fields->box_15;
            $box_83 = $submitting_fields->box_83;
            $box_84 = $submitting_fields->box_84;
            $box_85 = $submitting_fields->box_85;			
		}
		
	}	
@endphp	
<form id="formSubmittingFields-{{ $vat_reg_id }}" class="needs-validation formSubmittingFields" novalidate data-vatid="{{ $vat_reg_id }}" data-country="NO">
@csrf
<fieldset {{ ($vatreg_status == 6) ? 'disabled' : '' }}>
	<div class="row g-3">
		<div class="col-12">
			<h5 class="my-4">Sales of goods and services in Norway</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 3</strong></p>
			      <span>Sales and withdrawals of goods and services (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($sales_standard_totalnet) ? number_format($sales_standard_totalnet, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-3" name="submittingfields_box_3" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 31</strong></p>
			      <span>Sales and withdrawals of goods and services (medium rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($sales_medium_totalnet) ? number_format($sales_medium_totalnet, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-31" name="submittingfields_box_31" required readonly />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 33</strong></p>
			      <span>Sales and withdrawals of goods and services (low rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($sales_low_totalnet) ? number_format($sales_low_totalnet, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-33" name="submittingfields_box_33" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 5</strong></p>
			      <span>Sales and withdrawals of goods and services exempt from value added tax (zero-rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($sales_zero_totalnet) ? number_format($sales_zero_totalnet, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-5" name="submittingfields_box_5" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 6</strong></p>
			      <span>Sales and withdrawals of goods and services outside the scope of the Value Added Tax Act</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_6) ? number_format($box_6, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-6" name="submittingfields_box_6" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>
			</ul>
		</div>
		
		<div class="col-12">
			<h5 class="my-4">Sales of goods and services to other countries (exports)</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 52</strong></p>
			      <span>Sales of goods and services exempt from value added tax to other countries (zero-rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_52) ? number_format($box_52, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-52" name="submittingfields_box_52" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>			 			 
			</ul>
		</div>	

		<div class="col-12">
			<h5 class="my-4">Purchases of goods and services in Norway</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 1</strong></p>
			      <span>Purchases of goods and services with deductions (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($purchases_standard_totalvat) ? ((str_starts_with($purchases_standard_totalvat, '-')) ? number_format((-1 * $purchases_standard_totalvat), 0, '', '') : number_format($purchases_standard_totalvat, 0, '', '')) : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-1" name="submittingfields_box_1" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 11</strong></p>
			      <span>Purchases of goods and services with deductions (medium rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($purchases_medium_totalvat) ? ((str_starts_with($purchases_medium_totalvat, '-')) ? number_format((-1 * $purchases_medium_totalvat), 0, '', '') :number_format($purchases_medium_totalvat, 0, '', '')) : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-11" name="submittingfields_box_11" required readonly />
			    </div>
			  </li>

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 13</strong></p>
			      <span>Purchases of goods and services with deductions (low rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($purchases_low_totalvat) ? ((str_starts_with($purchases_low_totalvat, '-')) ? number_format((-1 * $purchases_low_totalvat), 0, '', '') :number_format($purchases_low_totalvat, 0, '', '')) : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-13" name="submittingfields_box_13" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>
			</ul>
		</div>

		<div class="col-12">
			<h5 class="my-4">Fish etc.</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 32</strong></p>
			      <span>Sales of fish and other marine wildilfe resources (11,11 %)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($sales_fish_totalnet) ? number_format($sales_fish_totalnet, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-32" name="submittingfields_box_32" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 12</strong></p>
			      <span>Purchases of fish and other marine wildilfe resources (11,11 %)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($purchases_fish_totalvat) ? number_format($purchases_fish_totalvat, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-12" name="submittingfields_box_12" required readonly />
			    </div>
			  </li>			  
			</ul>
		</div>

		<div class="col-12">
			<h5 class="my-4">Emission allowances and gold</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 51</strong></p>
			      <span>Sales of emission allowances and gold to businesses/self-employed persons</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_51) ? number_format($box_51, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-51" name="submittingfields_box_51" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 91</strong></p>
			      <span>Purchases of emission allowances and gold with deductions (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_91) ? number_format($box_91, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-91" name="submittingfields_box_91" required />
			    </div>
			  </li>		

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 92</strong></p>
			      <span>Purchases of emission allowances and gold without deductions entitlement (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_92) ? number_format($box_92, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-92" name="submittingfields_box_92" required />
			    </div>
			  </li>		  
			</ul>
		</div>

		<div class="col-12">
			<h5 class="my-4">Purchases of services from abroad (import)</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 86</strong></p>
			      <span>Purchases of services from abroad with deductions (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_86) ? number_format($box_86, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-86" name="submittingfields_box_86" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 87</strong></p>
			      <span>Purchases of services from abroad without deductions entitlement (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_87) ? number_format($box_87, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-87" name="submittingfields_box_87" required />
			    </div>
			  </li>		

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 88</strong></p>
			      <span>Purchases of services from abroad with deductions (low rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_88) ? number_format($box_88, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-88" name="submittingfields_box_88" required />
			    </div>
			  </li>	

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 89</strong></p>
			      <span>Purchases of services from abroad without deductions entitlement (low rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_89) ? number_format($box_89, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-89" name="submittingfields_box_89" required />
			    </div>
			  </li>	  
			</ul>
		</div>

		<div class="col-12">
			<h5 class="my-4">Purchases of goods from abroad (import)</h5>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 81</strong></p>
			      <span>Purchases of goods from abroad with deductions (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_81) ? number_format($box_81, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-81" name="submittingfields_box_81" onkeypress="return isDecimal(event, this)" required readonly />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 14</strong></p>
			      <span>Deductions on purchases of goods from abroad, value added tax paid upon import (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_14) ? number_format($box_14, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-14" name="submittingfields_box_14" required />
			    </div>
			  </li>		

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 82</strong></p>
			      <span>Purchases of goods from abroad without deduction entitlement (standard rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_82) ? number_format($box_82, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-82" name="submittingfields_box_82" required />
			    </div>
			  </li>	

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 15</strong></p>
			      <span>Deductions on purchases of goods from abroad, value added tax paid upon import (medium rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_15) ? number_format($box_15, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-15" name="submittingfields_box_15" required />
			    </div>
			  </li>	  

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 83</strong></p>
			      <span>Purchases of goods from abroad with deductions (medium rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_83) ? number_format($box_83, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-83" name="submittingfields_box_83" required />
			    </div>
			  </li>		

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 84</strong></p>
			      <span>Purchases of goods from abroad without deduction entitlement (medium rate)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_84) ? number_format($box_84, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-84" name="submittingfields_box_84" required />
			    </div>
			  </li>	

			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 85</strong></p>
			      <span>Purchases of goods from abroad with a zero-rate</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_85) ? number_format($box_85, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-85" name="submittingfields_box_85" required />
			    </div>
			  </li>	 
			</ul>
		</div>

		<div class="col-12">
			<button type="submit" class="btn btn-primary float-end submittingfields-save" {{ (!$vatregmain_status || $vatreg_is_disregard) ? 'disabled=disabled' : '' }}>Save</button> 
		</div>
	</div>
</fieldset>
</form>
<!--/ Submitting Fields -->  