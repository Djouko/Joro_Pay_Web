@extends('merchant.layouts.master')

@push('css')
    <style>
        .copy-button {
            cursor: pointer;
        }
    </style>
@endpush

@section('breadcrumb')
    @include('merchant.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("merchant.dashboard"),
        ]
    ], 'active' => __($page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row mb-20-none">
        <div class="col-xl-12 col-lg-12 mb-20">
            <div class="custom-card mt-10">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __("developer API") }}</h5>
                </div>
                @if (auth()->user()->developerApi)
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="dash-payment-item-wrapper">
                            <div class="dash-payment-item active">
                                <div class="dash-payment-title-area justify-content-end align-items-center d-sm-flex d-block">
                                    <button type="button" class="btn--base mt-3 mt-sm-0 api-kys-btn">{{ __("Create Api Keys") }}</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __("name") }}</th>
                                                <th>{{ __('Client ID') }}</th>
                                                <th>{{ __('Secret ID') }}</th>
                                                <th>{{ __('Mode') }}</th>
                                                <th>{{ __('Created At') }}</th>
                                                <th>{{ __('action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($apis ?? [] as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>
                                                        <div class="secret-key mt-3">
                                                            <span class="fw-bold">{{ textLength($item->client_id, 20) }}</span>
                                                            <div class="copy-text copy-btn copytext" data-copy-value="{{ $item->client_id }}"><i class="las la-copy"></i></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="secret-key mt-3">
                                                            <span class="fw-bold">{{ textLength($item->client_secret, 20) }}</span>
                                                            <div class="copy-text copy-btn copytext" data-copy-value="{{ $item->client_secret }}"><i class="las la-copy"></i></div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $item->mode }}</td>
                                                    <td>{{ dateFormat('d M Y , h:i:s A', $item->created_at) }}</td>
                                                    <td>
                                                        <button type="button" class="btn--base btn text-light active-deactive-btn" data-id="{{ $item->id }}"><i class="las la-check-circle"></i></button>
                                                        <button type="button" class="btn--danger btn text-light delete-btn" data-id="{{ $item->id }}"><i class="las la-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @empty
                                                @include('admin.components.alerts.empty2',['colspan' => 6])
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <nav>
                                    {{ $apis->links() }}
                                 </nav>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start fund virtual card modal
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="modal fade" id="apiKeysModal" tabindex="-1" aria-labelledby="apiKyesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content overflow-hidden">
        <div class="modal-header">
        <h5 class="modal-title" id="apiKyesModalLabel">{{ __('Create New Api Keys') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active mb-0 rounded-0">
                    <div class="row mt-20">
                        <form class="card-form" action="{{ setRoute('merchant.developer.api.generate.keys') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id">
                            <div class="col-xl-12 col-lg-12 form-group">
                                <label>{{ __("name") }} <span class="text--base">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control" required placeholder="{{ __("Enter Name") }}" name="name" value="{{ old("name") }}">
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group">
                                <button type="submit" class="btn--base w-100 btn-loading">{{__("Create")}}<i class="las la-plus-circle ms-1"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End fund virtual card modal
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection

@push('script')
    <script>
        $(".active-deactive-btn").click(function(){
            var actionRoute =  "{{ setRoute('merchant.developer.api.mode.update') }}";
            var target      = $(this).data('id');
            var btnText     = "{{ __('Change') }}";
            var message     = "{{ __('Are you sure to change mode') }}"+"?";
            openAlertModal(actionRoute,target,message,btnText,"POST");
        });
        $(".delete-btn").click(function(){
            var actionRoute =  "{{ setRoute('merchant.developer.api.delete.keys') }}";
            var target      = $(this).data('id');
            var btnText     = "{{ __('Delete') }}";
            var message     = "{{ __('delete_text') }}"+"?";
            openAlertModal(actionRoute,target,message,btnText,"POST");
        });

        //copy keys
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.copy-btn').forEach(function (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var copyValue = this.getAttribute('data-copy-value');
                    var tempInput = document.createElement('input');
                    tempInput.value = copyValue;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    tempInput.setSelectionRange(0, 99999); // For mobile devices
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    var message     = "{{ __('Copied Successful') }}";
                    throwMessage('success',[message]);
                });
            });
        });

        $('.api-kys-btn').on('click', function () {
            var modal = $('#apiKeysModal');
            modal.modal('show');
        });
    </script>
@endpush
