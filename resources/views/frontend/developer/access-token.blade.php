@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')

<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <div class="row mb-30-none">
            <div class="col-lg-6 mb-30">
                <h1 class="heading-title mb-20">{{ __("Get Access Token") }}</h1>
                <p>{{ __("Get access token to initiates payment transaction.") }}</p>
                <div class="mb-10">
                    <strong>{{ __("Endpoint") }}:</strong> <span class="badge rounded-pill bg-primary">{{ __("POST") }}</span> <code class="fw-bold fs-6" style="color: #EE8D1D;"><code>&#123;&#123;base_url&#125;&#125;</code>/authentication/token</code>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                          <tr>
                            <th scope="col">{{ __("Parameter") }}</th>
                            <th scope="col">{{ __("type") }}</th>
                            <th scope="col">{{ __("Comments") }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <th scope="row">client_id</th>
                            <td>string</td>
                            <td>{{ __("Enter merchant API client/primary key") }}</td>
                          </tr>
                          <tr>
                            <th scope="row">secret_id</th>
                            <td>string</td>
                            <td>{{ __("Enter merchant API secret key") }}</td>
                          </tr>
                        </tbody>
                      </table>
                </div>
            </div>
            <div class="col-lg-6 mb-30">
                <span class="mb-10">{{ __("Just request to that endpoint with all parameter listed below") }}:</span>
                <pre class="prettyprint mt-0" style="white-space: normal;">
                    <span class="code-show-list">
                        <span>Request Example (guzzle)</span>
                        <br>
                        <span>
                            <br>&lt;?php
                            <br> require_once('vendor/autoload.php');
                            <br> $client = new \GuzzleHttp\Client();
                            <br> $response = $client->request('POST', '&#123;&#123;base_url&#125;&#125;/authentication/token', [
                            <br>'json' => [
                            <br>&nbsp;&nbsp;'client_id' => 'tRCDXCuztQzRYThPwlh1KXAYm4bG3rwWjbxM2R63kTefrGD2B9jNn6JnarDf7ycxdzfnaroxcyr5cnduY6AqpulRSebwHwRmGerA',
                            <br>&nbsp;&nbsp;'secret_id' => 'oZouVmqHCbyg6ad7iMnrwq3d8wy9Kr4bo6VpQnsX6zAOoEs4oxHPjttpun36JhGxDl7AUMz3ShUqVyPmxh4oPk3TQmDF7YvHN5M3',
                            <br>&nbsp;],
                            <br>'headers' => [
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
                        <br>&nbsp;&nbsp;"SUCCESS"
                        <br>&nbsp;]
                        <br>},
                        <br>"data": {
                        <br>&nbsp;"access_token":"nyXPO8Re5SXP1c5gMqHbW6DQ5BfQdbYGpuWVjEQAP76SUT7YfdngoFzDGSNHTvmzq8AjPRrCyzxzukrJvOlSSwtAPAqjvAQJdse4YOnlHasD3vg6EYg6qyKxSiHeXBoRluD2NbZzxN3sAYVqd9q1XCAl7oaW3BbJl2ktEQWBUuNYMZPQaDyNEGwxoY389TCNJvxVcroveYxPJkYANvnaxOy16aE9Qp6EBClSjvK17WR3cJupTXlUhgw9ddpv1gDSlbDJvzKutrQX7XJqwk1GW1Dm6aK4PTn1D4mvMVqiOqQKigTzcEi2KPQnkoM86ONw3X8SxttFOfesdSwxKJMXuQpdnFHOjo",
                        <br>&nbsp;"expire_time": 600
                        <br>},
                        <br>"type": "success"
                        <br>}
                    </span>
                </pre>
                <pre class="prettyprint mt-0" style="white-space: normal;">
                    <span class="code-show-list">
                        <br>**Response: ERROR (400 FAILED)**
                        <br>{
                        <br>&nbsp;"message": {
                        <br>&nbsp;"code": 400,
                        <br>&nbsp;"error": [
                        <br>&nbsp;&nbsp;"Invalid secret ID"
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
            <a href="{{ setRoute('developer.base.url') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __(" Base URL") }}</a>
            <a href="{{ setRoute('developer.initiate.payment') }}" class="right"> {{ __("Initiate Payment ") }}<i class="las la-arrow-right ms-1"></i></a>
        </div>

    </div>
</div>
@endsection


@push("script")
<script>
    prettyPrint();
</script>
@endpush
