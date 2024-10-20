<table class="custom-table bank-search-table">
    <thead>
        <tr>

            <th>{{ __("bank Name") }}</th>
            <th></th>
            <th>{{ __("Created Time") }}</th>
            <th></th>
            <th>{{__("Status") }}</th>
            <th></th>
            <th>{{__("action")}}</th>
        </tr>
    </thead>
    <tbody>

        @forelse ($banks ?? [] as $item)
            <tr data-item="{{ $item->editData }}">
                <td>{{ $item->name }}</td>
                <td></td>
                <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                <td></td>

                <td>
                    @include('admin.components.form.switcher',[
                        'name'          => 'bank_status',
                        'value'         => $item->status,
                        'options'       => [__("Enable") => 1,__("Disable") => 0],
                        'onload'        => true,
                        'data_target'   => $item->id,
                        'permission'    => "admin.remitance.bank.deposit.status.update",
                    ])
                </td>
                <td></td>

                <td>
                    @include('admin.components.link.edit-default',[
                        'href'          => "javascript:void(0)",
                        'class'         => "edit-modal-button",
                        'permission'    => "admin.remitance.bank.deposit.update",
                    ])

                    @include('admin.components.link.delete-default',[
                        'href'          => "javascript:void(0)",
                        'class'         => "delete-modal-button",
                        'permission'    => "admin.remitance.bank.deposit.delete",
                    ])

                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 7])
        @endforelse
    </tbody>
</table>

@push("script")
    <script>
        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.remitance.bank.deposit.status.update') }}");
        })
    </script>
@endpush
