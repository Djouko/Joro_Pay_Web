<!-- favicon -->
<link rel="shortcut icon" href="{{ get_fav($basic_settings) }}" type="image/x-icon">
<!-- fontawesome css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/fontawesome-all.min.css">
<!-- line-awesome-icon css -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/line-awesome.min.css">
<!-- bootstrap css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/bootstrap.min.css">
<!-- swipper css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/swiper.min.css">

<!-- animate css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/animate.css">

<link rel="stylesheet" href="{{ asset('public/backend/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/backend/library/popup/magnific-popup.css') }}">
<!-- nice-select css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/nice-select.css">
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/virtual-card.css">
<!-- Fileholder CSS CDN -->
<link rel="stylesheet" href="https://appdevs.cloud/cdn/fileholder/v1.0/css/fileholder-style.css" type="text/css">
<!-- main style css link -->
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/style.css">
@php
    $color = @$basic_settings->base_color ?? '#000000';

@endphp

<style>
    :root {
        --primary-color: {{$color}};
    }

</style>
