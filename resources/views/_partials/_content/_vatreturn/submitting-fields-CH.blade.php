<!-- Submitting Fields --> 
@php	  
	//if($importreconciliationcominvoices)
	//{
		$box_200 = $salestotalnet;

		/*
		foreach ($importreconciliationcominvoices as $key => $importreconciliationcominvoice)
        {        	        	
        	if($importreconciliationcominvoice->currency_code == 'CHF')
        		$box_200 += $importreconciliationcominvoice->net_amount;
			else        		
        	{
        		if($importreconciliationcominvoice->convert_net_amount)
        			$box_200 += $importreconciliationcominvoice->convert_net_amount;
        	}
        }
        */

        $box_205 = 0;

        $box_220 = 0;
        $box_221 = 0;
        $box_225 = 0;
        $box_230 = 0;
        $box_235 = 0;
        $box_280 = 0;
        $box_289 = 0;

        $box_299 = 0;//$box_200;

        $box_303 = $sales_standard_totalnet;//$box_299;
        $box_303_1 = $sales_standard_totalvat;//$box_303 * 0.081;
        $box_313 = $sales_reduced_totalnet;
        $box_313_1 = $sales_reduced_totalvat;
        $box_343 = 0;
        $box_343_1 = 0;

        $box_379 = 0;

        $box_383 = 0;
        $box_383_1 = 0;

        $box_399 = 0;//$box_303;

        $box_400 = $purchasetotalvat;//$import_vat_total + $purchasetotalvat;
        $box_405 = 0;
        $box_410 = 0;
        $box_415 = 0;
        $box_420 = 0;
        $box_479 = 0;
        $box_500 = 0;
        $box_510 = 0;

		if($submitting_fields)
		{
			$box_200 = $submitting_fields->box_200;
            $box_205 = $submitting_fields->box_205;

            $box_220 = $submitting_fields->box_220;
            $box_221 = $submitting_fields->box_221;
            $box_225 = $submitting_fields->box_225;
            $box_230 = $submitting_fields->box_230;
            $box_235 = $submitting_fields->box_235;
            $box_280 = $submitting_fields->box_280;
            $box_289 = $submitting_fields->box_289;

            $box_299 = $submitting_fields->box_299;

            $box_303 = $submitting_fields->box_303;            
            $box_303_1 = $submitting_fields->box_303_1;
            $box_313 = $submitting_fields->box_313;
            $box_313_1 = $submitting_fields->box_313_1;            
            $box_343 = $submitting_fields->box_343;
            $box_343_1 = $submitting_fields->box_343_1;

            $box_379 = $submitting_fields->box_379;

            $box_383 = $submitting_fields->box_383;
            $box_383_1 = $submitting_fields->box_383_1;  

            $box_399 = $submitting_fields->box_399;

            $box_400 = $submitting_fields->box_400;
            $box_405 = $submitting_fields->box_405;
            $box_410 = $submitting_fields->box_410;
            $box_415 = $submitting_fields->box_415;

            $box_420 = $submitting_fields->box_420;
            $box_479 = $submitting_fields->box_479;

            $box_500 = $submitting_fields->box_500;
            $box_510 = $submitting_fields->box_510;			
		}			
	//}	

@endphp	
<form id="formSubmittingFields-{{ $vat_reg_id }}" class="needs-validation formSubmittingFields" novalidate data-vatid="{{ $vat_reg_id }}" data-country="CH">
@csrf
<fieldset {{ ($vatreg_status == 6) ? 'disabled' : '' }}>
	<div class="row g-3">
		<div class="col-12">
			<h5 class="my-4">I. Turnover</h5>
			<h6 class="my-4">Consideration</h6>	
			<ul class="list-group">
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 200</strong></p>
			      <span>Total amount of agreed or collected consideration incl. from supplies opted for taxation, transfer of supplies acc. to the notification procedure and supplies provided abroad (worldwide turnover)</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_200) ? number_format($box_200, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-200" name="submittingfields_box_200" onkeypress="return isDecimal(event, this)" required />
			    </div>
			  </li>
			  
			  <li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
			    <div class="offer">
			      <p class="mb-0"><strong>BOX 205</strong></p>
			      <span>Consideration reported in box 200 from supplies exempt from the tax without credit (art. 21) where the option for their taxation according to art. 22 has been exercised</span>
			    </div>
			    <div class="apply mt-3 mt-sm-0">			    	
			    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_205) ? number_format($box_205, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-205" name="submittingfields_box_205" required />
			    </div>
			  </li>			  
			</ul>

			<h6 class="my-4">Deductions</h6>
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 220</strong></p>
				      <span>Supplies exempt from the tax (e.g. export, art. 23) and supplies provided to institutional and individual beneficiaries that are exempt from liability for tax (art. 107 para. 1 lit. a)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_220) ? number_format($box_220, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-220" name="submittingfields_box_220" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 221</strong></p>
				      <span>Supplies provided abroad (place of supply is abroad)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_221) ? number_format($box_221, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-221" name="submittingfields_box_221" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>
				  
				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 225</strong></p>
				      <span>Transfer according to the notification procedure (art. 38, please submit Form. 764)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_225) ? number_format($box_225, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-225" name="submittingfields_box_225" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 230</strong></p>
				      <span>Supplies provided on Swiss territory exempt from the tax without credit (art. 21) and where the option for their taxation according to art. 22 has not been exercised</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_230) ? number_format($box_230, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-230" name="submittingfields_box_230" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 235</strong></p>
				      <span>Reduction of consideration (discounts, rebates etc.)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_235) ? number_format($box_235, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-235" name="submittingfields_box_235" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 280</strong></p>
				      <span>Miscellaneous (e. g. land value, purchase prices in case of margir taxation)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_280) ? number_format($box_280, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-280" name="submittingfields_box_280" onkeypress="return isDecimal(event, this)" required />

				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_289) ? number_format($box_289, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-289" name="submittingfields_box_289" onkeypress="return isDecimal(event, this)" required readonly />
				    </div>
				</li>		
			</ul>	

			<h6 class="my-4">Taxable turnover</h6>
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 299</strong></p>
				      <span>(Ref. 200 minus Ref. 289)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_299) ? number_format($box_299, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-299" name="submittingfields_box_299" onkeypress="return isDecimal(event, this)" required readonly />
				    </div>
				</li>		
			</ul>
		</div>
		
		<div class="col-12">
			<h5 class="my-4">II. Tax calculation</h5>
			<h6 class="my-4">Tax rate</h6>	
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 303</strong></p>
				      <span>Standard rate supplies</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_303) ? number_format($box_303, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-303" name="submittingfields_box_303" onkeypress="return isDecimal(event, this)" required />

				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_303_1) ? number_format($box_303_1, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-303-1" name="submittingfields_box_303_1" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 313</strong></p>
				      <span>Reduced rate supplies</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_313) ? number_format($box_313, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-313" name="submittingfields_box_313" onkeypress="return isDecimal(event, this)" required />

				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_313_1) ? number_format($box_313_1, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-313-1" name="submittingfields_box_313_1" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 343</strong></p>
				      <span>Accommodation</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_343) ? number_format($box_343, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-343" name="submittingfields_box_343" onkeypress="return isDecimal(event, this)" required />

				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_343_1) ? number_format($box_343_1, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-343-1" name="submittingfields_box_343_1" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>
			</ul>

			<h6 class="my-4">Taxable turnover (As in Ref. 299)</h6>	
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 379</strong></p>
				      <span>Taxable turnover (As in Ref. 299)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_379) ? number_format($box_379, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-379" name="submittingfields_box_379" onkeypress="return isDecimal(event, this)" required readonly />				    	
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 383</strong></p>
				      <span>Acquisition tax (net, exklusive VAT)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_383) ? number_format($box_383, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-383" name="submittingfields_box_383" onkeypress="return isDecimal(event, this)" required />

				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_383_1) ? number_format($box_383_1, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-383-1" name="submittingfields_box_383_1" onkeypress="return isDecimal(event, this)" required />
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 399</strong></p>
				      <span>Total amount of tax due (Ref. 303 to 383)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_399) ? number_format($box_399, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-399" name="submittingfields_box_399" onkeypress="return isDecimal(event, this)" required readonly />				    	
				    </div>
				</li>
			</ul>

			<h6 class="my-4">Input tax</h6>	
			<ul class="list-group">
				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 400</strong></p>
				      <span>Input tax on cost of materials and supplies of services</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_400) ? number_format($box_400, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-400" name="submittingfields_box_400" onkeypress="return isDecimal(event, this)" required />				    	
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 405</strong></p>
				      <span>Input tax on investments and other operating costs</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_405) ? number_format($box_405, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-405" name="submittingfields_box_405" onkeypress="return isDecimal(event, this)" required />				    
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 410</strong></p>
				      <span>De-taxation (art. 32, please enclose a detailed list) and corrections following a change from the net tax method or flat-rate to the effective method.</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_410) ? number_format($box_410, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-410" name="submittingfields_box_410" onkeypress="return isDecimal(event, this)" required />				    	
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 415</strong></p>
				      <span>Correction of the input tax deduction: mixed use (art. 30), own use (art. 31) and corrections following a change from the effective method to the net tax or flat-rate method.</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_415) ? number_format($box_415, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-415" name="submittingfields_box_415" onkeypress="return isDecimal(event, this)" required />				    	
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 420</strong></p>
				      <span>Reduction of the input tax deduction: Flow of funds, which are not deemed to be consideration, such as subsidies, tourist charges (art. 33 para. 2)</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_420) ? number_format($box_420, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-420" name="submittingfields_box_420" onkeypress="return isDecimal(event, this)" required />	

				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_479) ? number_format($box_479, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-479" name="submittingfields_box_479" onkeypress="return isDecimal(event, this)" required readonly />				    	
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 500</strong></p>
				      <span>Amount payable</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_500) ? number_format($box_500, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-500" name="submittingfields_box_500" onkeypress="return isDecimal(event, this)" required readonly />				    	
				    </div>
				</li>

				<li class="list-group-item d-flex justify-content-between flex-column flex-sm-row">
				    <div class="offer">
				      <p class="mb-0"><strong>BOX 510</strong></p>
				      <span>Credit in favour of the taxable person</span>
				    </div>
				    <div class="apply mt-3 mt-sm-0">			    	
				    	<input class="form-control btn btn-outline-primary w-px-150 text-end" type="text" value="{{ isset($box_510) ? number_format($box_510, 0, '', '') : 0 }}" id="submittingfields-box-{{ $vat_reg_id }}-510" name="submittingfields_box_510" onkeypress="return isDecimal(event, this)" required readonly />				    	
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