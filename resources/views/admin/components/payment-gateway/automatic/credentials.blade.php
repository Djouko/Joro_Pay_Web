@isset($gateway)
    <div class="gateway-content">
        <h3 class="title">{{ $gateway->title }}</h3>
        <p>{{ __("Global Setting for") }} {{ $gateway->alias }} {{ __("in bellow") }}</p>
    </div>
    @php
        $webhook = url('/').'/flutterwave/withdraw_webhooks';
    @endphp
    @foreach ($gateway->credentials as $item)

        @if($gateway->name == "Flutterwave")
            @php
                if ($item->name === "callback-url") {
                    // Update the value
                    $item->value = $webhook;
                }
            @endphp
        @endif

        <div class="form-group">
            <label>{{ $item->label }}</label>
            <div class="input-group">
                <input type="text" class="form--control" id="referralURL" placeholder="{{ $item->placeholder }}" name="{{ $item->name }}" value="{{ $item->value }}"
                {{ $item->name === "callback-url" ? "readonly":'' }}
                >
                @if($item->name === "callback-url")
                    <span class="input-group-text copytext" id="copyBoard" data-value="{{ $webhook }}">
                        <i class="las la-copy"></i>
                    </span>
                @endif
            </div>
        </div>
    @endforeach
@endisset
@push('script')
    <script>
        $('.copytext').on('click', function(){
            var copyText = $(this).data('value');
            var input = document.createElement('input');
            input.setAttribute('value', copyText);
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            throwMessage('success',['{{ __("link Copied Successfully") }}']);
        });
    </script>
@endpush
