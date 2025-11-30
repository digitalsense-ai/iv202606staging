@props(['url', 'lang'])
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">

<a href="{{ $url }}" style="display: inline-block;">
<img src="https://app.intravat.cloud/assets/img/logo/intravat-logo-white.png" class="logo" alt="Logo">
</a><br>
{{--{{ trans('Questions tel.', [], $lang) }}--}} +45 88 63 22 99<br>
IntraVAT ApS<br>
Torvet 9, 1.<br>
Køge 4600<br>
Denmark
</td>
</tr>
</table>
</td>
</tr>
<script>
  let list = document.getElementsByTagName("p");
  for (let i = 0; i < list.length; i++) {  	
    if (list[i].textContent.startsWith("Sort code:") || 
    	list[i].textContent.startsWith("Account number:") ||
    	list[i].textContent.startsWith("Account name:") ||
    	list[i].textContent.startsWith("Payment reference:") ||
    	list[i].textContent.startsWith("Bank identifier code (BIC):") ||
    	list[i].textContent.startsWith("Account number (IBAN):") ||    	    
    	list[i].textContent.startsWith("[bankname]") ||
    	list[i].textContent.startsWith("[address]") ||
    	list[i].textContent.startsWith("[city]") ||
    	list[i].textContent.startsWith("[country]") ||
    	list[i].textContent.startsWith("[postcode]") ||
    	list[i].textContent.startsWith("Sorteringskode:") || 
    	list[i].textContent.startsWith("Kontonummer:") ||
    	list[i].textContent.startsWith("Kontonavn:") ||
    	list[i].textContent.startsWith("Betalings reference:") ||
    	list[i].textContent.startsWith("Bankidentifikationskode (BIC):") ||
    	list[i].textContent.startsWith("Kontonummer (IBAN):")    	
    ) 
    {
      // list[i].style.margin = "0px";
      // list[i].style.fontSize = "14px";
      // list[i].style.fontWeight = "normal";
      list[i].style = "margin: 0px; font-size: 14px; font-weight: normal;";
    }

    if (list[i].textContent.startsWith("From UK local bank account:") || 
    	list[i].textContent.startsWith("From a foreign bank account:") ||
    	list[i].textContent.startsWith("HMRCs bank adresse:") ||
    	list[i].textContent.startsWith("Fra UK lokal bankkonto:") || 
    	list[i].textContent.startsWith("Fra en udenlandsk bankkonto:") ||
    	list[i].textContent.startsWith("HMRCs bank adresse:")
    ) 
    {
      //list[i].style.margin = "10px 0px";      
      list[i].style = "margin: 10px 0px;";
    }
  }
</script>