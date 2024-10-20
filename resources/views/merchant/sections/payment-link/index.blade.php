@extends('merchant.layouts.master')
@section('breadcrumb')
    @include('merchant.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("merchant.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection
@section('content')
<div class="body-wrapper">
    <div class="table-area">
        <div class="dashboard-header-wrapper">
            <h4 class="title">{{ $page_title }}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn">
                    <a href="{{ setRoute('merchant.payment-link.create') }}" class="btn--base"><i class="las la-plus me-1"></i> {{ __('Create Link') }}</a>
                </div>
            </div>
        </div>
        <div class="table-wrapper">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("titleS") }}</th>
                            <th>{{ __('type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment_links as $item)
                            <tr>
                                <td>{{ $item->title }}</td>
                                <td>{{ $item->linkType }}</td>
                                <td>{{ $item->amountCalculation }}</td>
                                <td><span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span></td>
                                <td>{{ dateFormat('d M Y , h:i:s A', $item->created_at) }}</td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" onclick="copyToClipBoard('copy-share-link-{{ $item->id }}')" class="btn--base btn"><i class="las la-clipboard"></i></button>
                                        <input type="hidden" id="copy-share-link-{{ $item->id }}" value="{{ setRoute('payment-link.share', $item->token) }}">
                                        <div class="action-btn ms-1">
                                            <button type="button" class="btn--base btn"><i class="las la-ellipsis-v"></i></button>
                                            <ul class="action-list">
                                                @if ($item->status == 1)
                                                    <li><a href="{{ setRoute('merchant.payment-link.edit', $item->id) }}">{{ __("editS") }}</a></li>
                                                @endif
                                                @if ($item->status == 1)
                                                    <li><a href="" class="status_change" data-target="{{ $item->id }}" data-type="{{ __("Make Close") }}">{{ __("closeS") }}</a></li>
                                                @else
                                                    <li><a href="" class="status_change" data-target="{{ $item->id }}" data-type="{{ __("Make Active") }}">{{ __("active") }}</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty2',['colspan' => 7])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <nav>
           {{ $payment_links->links() }}
        </nav>
    </div>
</div>
@endsection

@push('script')
<script>
    function copyToClipBoard(element) {
        var copyText = document.getElementById(element);
        copyText.select();
        navigator.clipboard.writeText(copyText.value);
        var message = '{{ __("URL Copied To Clipboard!") }}'
        notification('success', message);
    }
    $(".status_change").click(function(e){
        e.preventDefault();
        var target  = $(this).data('target');
        var actionRoute = "{{ route('merchant.payment-link.status') }}";
        var firstText = '{{ __("Are you sure to change") }}';
        var status = "{{ __('Status') }}";
        var message = `${firstText} <strong>${status}</strong>?`;
        var type = $(this).data('type');
        openAlertModal(actionRoute,target,message,type,"POST");
    });
</script>
@endpush
