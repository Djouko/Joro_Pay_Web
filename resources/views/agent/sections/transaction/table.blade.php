<table class="custom-table">
    <thead>
        <tr>
            <th>{{ __("web_trx_id") }}</th>
            <th>{{ __("Transaction Type") }}</th>
            <th>{{ __("Transaction Amount") }}</th>
            <th>{{ __("Profit Amount") }}</th>
            <th>{{ __("Time") }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($profits?? [] as $item)
            <tr>
                <td>{{ $item->transactions->trx_id }}</td>
                <td>{{$item->transactions->type }}</td>
                <td>{{ get_amount($item->transactions->details->charges->conversion_amount??$item->transactions->details->charges->sender_amount,$item->transactions->details->charges->wallet_currencyy??get_default_currency_code())}}</td>
                <td>{{ get_amount($item->total_charge,$item->transactions->details->charges->wallet_currency??get_default_currency_code(),4)}}</td>
                <td>{{ $item->created_at->format("Y-m-d h:i A") }}</td>
            </tr>
        @empty
        @include('admin.components.alerts.empty',['colspan' => 5])
        @endforelse
    </tbody>
</table>
@push('script')

@endpush
