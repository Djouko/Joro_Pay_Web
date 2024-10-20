@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp
@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <div class="row mb-30-none">
            <div class="col-lg-6 mb-30">
                <h1 class="heading-title mb-20">{{ __("Initiate Payment") }}</h1>
                <p>{{ __("Initiates a new payment transaction.") }}</p>
                <div class="mb-10">
                    <strong>{{ __("Endpoint") }}:</strong> <span class="badge rounded-pill bg-primary">POST</span> <code class="fw-bold fs-6" style="color: #EE8D1D;"><code>&#123;&#123;base_url&#125;&#125;</code>/payment/create</code>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                            <th scope="col">{{ __("Parameter") }}</th>
                            <th scope="col">{{ __("type") }}</th>
                            <th scope="col">{{ __("Details") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <th scope="row">amount</th>
                            <td>decimal</td>
                            <td>{{ __("Your Amount , Must be rounded at 2 precision.") }}</td>
                            </tr>
                            <tr>
                            <th scope="row">currency</th>
                            <td>string</td>
                            <td>{{ __("Currency Code, Must be in Upper Case (Alpha-3 code)") }}</td>
                            </tr>
                            <tr>
                            <th scope="row">return_url:</th>
                            <td>string</td>
                            <td>{{ __("Enter your return or success URL") }}</td>
                            </tr>
                            <tr>
                            <th scope="row">cancel_url:</th>
                            <td>string (optional)</td>
                            <td>{{ __("Enter your cancel or failed URL") }}</td>
                            </tr>
                            <tr>
                            <th scope="row">custom:</th>
                            <td>string (optional)</td>
                            <td>{{ __("Transaction id which can be used your project transaction") }}</td>
                            </tr>
                        </tbody>
                        </table>
                </div>
            </div>
            <div class="col-lg-6 mb-30">
                <pre class="prettyprint mt-0" style="white-space: normal;">
                    <span class="code-show-list">
                        <span>Request Example (guzzle)</span>
                        <br>
                        <span>
                            <br>&lt;?php
                            <br> require_once('vendor/autoload.php');
                            <br> $client = new \GuzzleHttp\Client();
                            <br> $response = $client->request('POST', '&#123;&#123;base_url&#125;&#125;/payment/create', [
                            <br>'json' => [
                            <br>&nbsp;&nbsp;'amount' => '100.00',
                            <br>&nbsp;&nbsp;'currency' => 'USD',
                            <br>&nbsp;&nbsp;'return_url' => 'www.example.com/success',
                            <br>&nbsp;&nbsp;'cancel_url' => 'www.example.com/cancel',
                            <br>&nbsp;&nbsp;'custom' => '123456789ABCD',
                            <br>&nbsp;],

                            <br>'headers' => [
                            <br>&nbsp;&nbsp;'Authorization' => 'Bearer &#123;&#123;access_token&#125;&#125;',
                            <br>&nbsp;&nbsp;'accept' => 'application/json',
                            <br>&nbsp;&nbsp;'content-type' => 'application/json',
                            <br>&nbsp;],
                            <br>]);
                            <br>echo $response->getBody();
                        </span>
                    </span>
                </pre>
                <pre class="prettyprint mt-0" style="white-space: normal;">
                    <span class="code-show-list">
                        <br>**Response: SUCCESS (200 OK)**
                        <br>{
                        <br>&nbsp;"message": {
                        <br>&nbsp;"code": 200,
                        <br>&nbsp;"success": [
                        <br>&nbsp;&nbsp;"CREATED"
                        <br>&nbsp;]
                        <br>},
                        <br>"data": {
                        <br>&nbsp;"token": "2zMRmT3KeYT2BWMAyGhqEfuw4tOYOfGXKeyKqehZ8mF1E35hMwE69gPpyo3e",
                        <br>&nbsp;"payment_url": "www.example.com/pay/sandbox/v1/user/authentication/form/2zMRmT3KeYT2BWMAyGhqEfuw4tOYOfGXKeyKqehZ8mF1E35hMwE69gPpyo3e",
                        <br>},
                        <br>"type": "success"
                        <br>}
                    </span>
                </pre>
                <pre class="prettyprint mt-0" style="white-space: normal;">
                    <span class="code-show-list">
                        <br>**Response: ERROR (403 FAILED)**
                        <br>{
                        <br>&nbsp;"message": {
                        <br>&nbsp;"code": 403,
                        <br>&nbsp;"error": [
                        <br>&nbsp;&nbsp;"Requested with invalid token!"
                        <br>&nbsp;]
                        <br>},
                        <br>"data": [],
                        <br>"type": "error"
                        <br>}
                    </span>
                </pre>
            </div>
        </div>
    </div>
    <div class="page-change-area">

        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.access.token') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Access Token") }}</a>
            <a href="{{ setRoute('developer.check.status.payment') }}" class="right">{{ __("Check Payment Status") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>

    </div>
</div>
@endsection

@push("script")
<script>
    prettyPrint();
</script>
@endpush
