<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
	body { color:#000 !important; }
@media only screen and (max-width: 600px) {
	.draft-email .content { width: 80% !important; }
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}
</style>
</head>
<body>

<table class="wrapper draft-email" width="100%" cellpadding="0" cellspacing="0" role="presentation">
	<tr>
		<td align="center">
			<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
				{{ $header ?? '' }}

				<!-- Email Body -->
				<tr>
					<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
						<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
						<!-- Body content -->
						<tr>
							<td class="content-cell" style="color: #000 !important;">
							{{ Illuminate\Mail\Markdown::parse($slot) }}

							{{ $subcopy ?? '' }}
							</td>
						</tr>
						{{ $footer ?? '' }}
						</table>
					</td>
				</tr>

				<tr>
					<td class="sub-footer">
						<p>{{ trans('You are receiving this email because you are registered with IntraVAT. This email is not a marketing email or a promotional email. That is why it does not contain an unsubscribe link. You are receiving this email even if you have unsubscribed from IntraVAT marketing emails.', [], $lang) }}</p>										
					</td>
				</tr>

			</table>
		</td>
	</tr>
</table>
</body>
</html>
