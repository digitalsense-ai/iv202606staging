@if($tab_name == 'overview' && !isset($overview_partial))
<div class="table-responsive">
	<table class="table m-0 {{ ($vatreg->is_disregard) ? 'disabled' : '' }}">
		<thead>
			<tr>
				<th>Item</th>
				<th>Description</th>
				<th class="text-center">Invoices</th>
				<th class="text-end">% VAT</th>		          
				<th class="text-end">NET</th>
				<th class="text-end">VAT</th>
			</tr>
		</thead>
	  	<tbody>
@endif	  	

@if($tab_name == 'previewreport')
			<tr>
				<td align="left" class="p-2">Sales</td>
				<td align="left" class="p-2">Sale Invoice</td>
				<td align="center" class="p-2">
					<a class="cursor-pointer text-decoration-none"  target="_blank">0</a>
				</td>
				<td align="center" class="p-2">-</td>	
				<td align="right" class="p-2">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>
				<td align="right" class="p-2">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>	
		    </tr>
		    <tr><td colspan="6" class="border-none pt-4"></td></tr>
		    <tr>                    
				<td align="right" colspan="5" class="border-none p-2 pb-0">Subtotal:</td>
				<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>                   
			</tr>
			<tr>                    
				<td align="right" colspan="5" class="border-none p-2 pb-0">VAT:</td>
				<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>                   
			</tr>
			<tr>                    
				<td align="right" colspan="5" class="border-none p-2 pb-0">Total:</td>
				<td align="right" class="border-none p-2 pb-0 fw-bold">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>                   
			</tr> 
			<tr><td colspan="6" class="border-none pb-4"></td></tr>			
@else
			<tr>
				<td class="text-nowrap">Sales</td>
				<td class="text-nowrap">Sale Invoice</td>
				<td class="text-center">			          	
					<a class="cursor-pointer text-decoration-none"  target="_blank">0</a>
				</td>
				<td class="text-end">-</td>	
				<td class="text-end">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>
				<td class="text-end">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</td>	
		    </tr>
		    <tr>
				<td colspan="4" class="align-top px-4 py-5 border-bottom-0"></td>
				<td class="text-end px-4 py-5 border-bottom-0">
					<p class="mb-2">Subtotal:</p>			                
					<p class="mb-2">Tax:</p>
					<p class="mb-0">Total:</p>
				</td>
				<td class="px-4 py-5 border-bottom-0">
					<p class="fw-semibold mb-2 text-end">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</p>
					<p class="fw-semibold mb-2 text-end" id="total-tax-{{ $vat_reg_id }}">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</p>
					<p class="fw-semibold mb-0 text-end">{{ $currencyFormatter->format(0) . ' ' . (($currencySymbol) ? $currencySymbol : $currencycode) }}</p>
				</td>		         
		    </tr>
@endif		    

@if($tab_name == 'overview' && !isset($overview_partial))
		</tbody>
	</table>
</div>
@endif