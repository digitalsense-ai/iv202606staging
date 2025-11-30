<thead> 
  <tr>
    <th width="50%" align="left" valign="top">  
      <h5>{{ $client->client_name }}</h5>
      <h5>Cvr. no.{{ $client->vatno }}</h5>
      <h5>{{ \Carbon\Carbon::parse($vatreg->service_start)->format('M y') . ' - ' . 
          \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y') }}</h5>
    </th>
    <th width="50%" align="right" valign="top">  
      <img src="<?php echo $logo ?>" width="25%" class="logo">
    </th>        
  </tr>    
</thead>