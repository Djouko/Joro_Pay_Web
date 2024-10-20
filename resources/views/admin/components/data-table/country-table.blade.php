<table class="custom-table country-search-table">
    <thead>
        <tr>
            <th>{{ __("Flag") }}</th>
            <th></th>
            <th>{{ __("Name | Code") }}</th>
            <th>{{ __("Rate") }}</th>
            <th>{{ __("Symbol") }}</th>
            <th>{{__("Status") }}</th>
            <th>{{__("action")}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($allCountries ?? [] as $item)
            <tr data-item="{{ $item->editData }}">
                <td>
                    <ul class="user-list">
                        <li><img src="{{ get_image($item->flag,'country-flag') }}" alt="flag"></li>
                    </ul>
                </td>
                <td></td>
                <td>{{ $item->name }}
                    @if ($item->receiver)
                        <span class="badge badge--success ms-1">{{ __("Receiver") }}</span>
                    @endif
                    <br> <span>{{ $item->code }}</span></td>
                    <td><span class="text--info">{{ $item->type }}</span> <br> 1 {{ get_default_currency_code($default_currency) }} = {{ get_amount($item->rate,$item->code) }}</td>
                <td>{{ $item->symbol }}</td>


                <td>
                    @include('admin.components.form.switcher',[
                        'name'          => 'country_status',
                        'value'         => $item->status,
                        'options'       => [__("Enable") => 1,__("Disable") => 0],
                        'onload'        => true,
                        'data_target'   => $item->code,
                        'permission'    => "admin.remitance.country.status.update",
                    ])
                </td>
                <td>
                    @include('admin.components.link.edit-default',[
                        'href'          => "javascript:void(0)",
                        'class'         => "edit-modal-button",
                        'permission'    => "admin.remitance.country.update",
                    ])

                    @include('admin.components.link.delete-default',[
                        'href'          => "javascript:void(0)",
                        'class'         => "delete-modal-button",
                        'permission'    => "admin.remitance.country.delete",
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
            switcherAjax("{{ setRoute('admin.remitance.country.status.update') }}");
        })
    </script>
@endpush
