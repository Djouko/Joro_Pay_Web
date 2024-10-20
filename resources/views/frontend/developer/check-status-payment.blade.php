@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Check Payment Status") }}</h1>
        <p>{{ __("Checks the status of a payment.") }}</p>
        <pre class="prettyprint mt-0" style="white-space: normal;">
            <span class="code-show-list">
                <br>**Response: SUCCESS (200 OK)**
                <br>{
                <br>&nbsp;"message": {
                <br>&nbsp;"code": 200,
                <br>&nbsp;"success": [
                <br>&nbsp;&nbsp;"SUCCESS"
                <br>&nbsp;]
                <br>},
                <br>"data": {
                <br>&nbsp;"token": "2zMRmT3KeYT2BWMAyGhqEfuw4tOYOfGXKeyKqehZ8mF1E35hMwE69gPpyo3e",
                <br>&nbsp;"trx_id": "BP2c7sAvw75MTlrP",
                <br>&nbsp;"payer": {
                <br>&nbsp;&nbsp;"username": "testuser",
                <br>&nbsp;&nbsp;"email": "user@appdevs.net"
                <br>&nbsp;},
                <br>&nbsp;"custom": "123456789ABCD"
                <br>},
                <br>"type": "success"
                <br>}
            </span>
        </pre>
    </div>
    <div class="page-change-area">

        <div class="navigation-wrapper">
            <a href="{{ setRoute("developer.initiate.payment") }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Initiate Payment") }}</a>
            <a href="{{ setRoute("developer.response.code") }}" class="right">{{ __("Response Codes") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>

    </div>
</div>
@endsection


@push("script")
<script>
    prettyPrint();
</script>
@endpush
