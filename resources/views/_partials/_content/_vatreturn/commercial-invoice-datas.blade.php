{{--
@php
	$missing_commercial_invoices = '';

	foreach ($commercial_invoices_files as $key => $commercial_invoices_file)
	{
		if($commercial_invoices_file->sale_invoice_nos != '')
		{			
			$sale_invoice_nos = explode(',', $commercial_invoices_file->sale_invoice_nos);

			if(count($invoices) == 0)
			{
				if($missing_commercial_invoices == '')
					$missing_commercial_invoices = $commercial_invoices_file->sale_invoice_nos;
				else
					$missing_commercial_invoices .= ', ' . $commercial_invoices_file->sale_invoice_nos;			
			}
			else				
			{
				$filtered_invoices = $invoices->filter(function ($invoice, $key) use($sale_invoice_nos) { 			
			        return (in_array($invoice->invoice_no, $sale_invoice_nos)) ? null : $invoice->invoice_no; 
			    }); 
				dd($filtered_invoices);
			}							
		}
	}
@endphp
--}}

@if($missing_commercial_invoices != '')
	@php
		//$missing_commercial_invoice_count = count(explode(', ', $missing_commercial_invoices));
		$missing_commercial_invoice_arr = explode(',', $missing_commercial_invoices);
		$missing_commercial_invoice_arr = array_map('trim', $missing_commercial_invoice_arr);
		$missing_commercial_invoice_count = count($missing_commercial_invoice_arr);
	@endphp		
	<u>Missing Invoices:</u><span class="alert-danger text-end fs-tiny p-1 mx-2" data-vat_reg_id="{{ $vat_reg_id }}">{{ $missing_commercial_invoice_count }}</span><br>{{ $missing_commercial_invoices }}
@endif