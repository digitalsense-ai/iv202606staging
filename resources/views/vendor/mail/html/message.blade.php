<x-mail::layout :lang="$lang ?? 'en'">
{{-- Header --}}
<x-slot:header>
<x-mail::header :subject="$subject ?? ''">
{{ $subject ?? '' }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer :url="config('app.url')" :lang="$lang ?? 'en'">

</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
