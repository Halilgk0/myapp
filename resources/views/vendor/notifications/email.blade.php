@component('mail::message')
# {{ $greeting ?? 'Merhaba!' }}

{{ $introLines[0] ?? '' }}

@if (isset($actionText))
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endif

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
Saygılarımızla,<br>
{{ config('app.name') }} Ekibi
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@component('mail::subcopy')
Eğer "{{ $actionText }}" butonuna tıklamakta sorun yaşıyorsanız, aşağıdaki URL'yi kopyalayıp tarayıcınıza yapıştırın:

[{{ $displayableActionUrl }}]({{ $actionUrl }})
@endcomponent
@endslot
@endisset
@endcomponent
