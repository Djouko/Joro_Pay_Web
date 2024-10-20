<table class="custom-table newletter-search-table">
    <thead>
        <tr>
            <th>{{ __("name") }}</th>
            <th>{{ __("Email") }}</th>
            <th>{{__("action")}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data ?? [] as $item)
            <tr data-item="{{ $item->editData }}">
                <td>{{ $item->fullname }}
                <td>{{ $item->email }}</td>
                <td>
                    @include('admin.components.link.delete-default',[
                        'href'          => "javascript:void(0)",
                        'class'         => "delete-modal-button",
                        'permission'    => "admin.newsletter.delete",
                    ])
                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 7])
        @endforelse
    </tbody>
</table>


