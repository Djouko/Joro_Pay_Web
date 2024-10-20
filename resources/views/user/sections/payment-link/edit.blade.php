@extends('user.layouts.master')
@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection
@section('content')
<div class="body-wrapper payment-body-wrapper">
    <div class="custom-card payment-card">
        <div class="payment-type-wrapper">
            <div class="payment-header">
                <h3 class="title">{{ __('Select Type') }}</h3>
            </div>
            <form action="{{ setRoute('user.payment-link.update') }}" class="payment-form" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="currency_symbol" value="{{ $payment_link->currency_symbol ?? '' }}">
                <input type="hidden" name="currency_name" value="{{ $payment_link->currency_name ?? '' }}">
                <input type="hidden" name="target" value="{{ $payment_link->id}}">

                <div class="payment-select-wrapper">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-group">
                                <select class="nice-select payment-select" name="type">
                                    <option {{ $payment_link->type == payment_gateway_const()::LINK_TYPE_PAY ? 'selected' : '' }} value="{{ payment_gateway_const()::LINK_TYPE_PAY }}" selected>{{ __('Customers Choose What To Pay') }}</option>
                                    <option {{ $payment_link->type == payment_gateway_const()::LINK_TYPE_SUB ? 'selected' : '' }} value="{{ payment_gateway_const()::LINK_TYPE_SUB }}">{{ __('Products Or Subscriptions') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="payment-box payment-box-area" id="pay-view">
                    <div class="payment-inner-header">
                        <h6 class="inner-title">{{ __('Payment Page') }}</h6>
                    </div>
                    <div class="payment-form-wrapper">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group">
                                    <label>{{ __("titleS") }}</label>
                                    <input type="text" class="form--control link_title" name="title" placeholder="Name of cause or service" value="{{ old('title', $payment_link->title ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Description') }} <span>{{ __('Optional') }}</span></label>
                                    <textarea class="form--control" name="details" placeholder="Give customers more detail about what they're paying for." >{{ old('details', $payment_link->details ?? '')  }}</textarea>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-group">
                                    @include('admin.components.form.input-file',[
                                        'label'         =>  __('Image').' '.__('Optional'),
                                        'class'         => "file-holder m-auto payment-link-image",
                                        'name'          => "image",
                                        'old_files_path'    => files_asset_path('payment-link-image'),
                                        'old_files'         => $payment_link->image,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="payment-select-wrapper">
                        <div class="row">
                            <div class="col-xl-5">
                                <div class="form-group">
                                    <label>{{ __("currency") }}</label>
                                    <select class="select2-auto-tokenize currency_link" name="currency">
                                        <option value="" disabled>{{ __('Select One') }}</option>
                                        @foreach ($currency_data as $item)
                                            <option value="{{ $item->code }}" data-country="{{ $item->name }}" data-currency_name="{{ $item->name }}" data-code="{{ $item->code }}" data-currency_symbol="{{ $item->symbol }}" {{ $payment_link->currency == $item->code ? 'selected' : '' }}>{{ $item->code. ' ('. $item->name.')' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="payment-check-group">
                        <div class="custom-check-group">
                            <input type="checkbox" id="level-1" class="dependency-checkbox" data-target="payment-check-form" name="limit">
                            <label for="level-1">{{ __("Set limits") }}</label>
                        </div>
                        <div class="payment-check-form" style="display: none;">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        <label>{{ __("Minimum amount") }}</label>
                                        <div class="input-group">
                                            <div class="input-group-text prepend currency_link_symbol">£</div>
                                            <input type="text" class="form--control number-input" placeholder="0.3" min="0.1" name="min_amount" value="{{ old('min_amount', $payment_link->min_amount ? get_amount($payment_link->min_amount) : '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group">
                                        <label>{{ __("Maximum amount") }}</label>
                                        <div class="input-group">
                                            <div class="input-group-text prepend currency_link_symbol">£</div>
                                            <input type="text" class="form--control number-input" placeholder="10,000" min="0.2" name="max_amount" value="{{ old('max_amount', $payment_link->max_amount ? get_amount($payment_link->max_amount) : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="payment-product-box payment-box-area" id="sub-view">
                    <div class="payment-product-form">
                        <div class="row">
                            <div class="col-xl-12 form-group">
                                <label>{{ __("titleS") }}*</label>
                                <input type="text" class="form--control link_title" name="sub_title" placeholder="Collecting Payment Platform" value="{{ old('title', $payment_link->title ?? '') }}">
                            </div>
                            <div class="col-xl-12 form-group">
                                <label>{{ __("currency") }}</label>
                                <select class="select2-auto-tokenize currency_link_sub" name="sub_currency">
                                    <option value="" disabled>{{ __('Select One') }}</option>
                                    @foreach ($currency_data as $item)
                                        <option value="{{ $item->code }}" data-country="{{ $item->name }}" data-currency_name="{{ $item->name }}" data-code="{{ $item->code }}" data-currency_symbol="{{ $item->symbol }}" {{ $payment_link->currency == $item->code ? 'selected' : '' }}>{{ $item->code. ' ('. $item->name.')' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("price") }}*</label>
                                <div class="input-group">
                                    <div class="input-group-text prepend currency_link_symbol">{{ $payment_link->currency_symbol }}</div>
                                    <input type="text" class="form--control sub_price number-input" placeholder="0.00" name="price" value="{{ old('sub_price', get_amount(@$payment_link->price)) }}">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("quantity") }}*</label>
                                <input type="text" class="form--control qty_change number-input" min="1" name="qty" value="{{ old('qty', $payment_link->qty ?? '1') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn--base mt-20 w-100 btn-loading">{{ __('update Link') }}</button>
            </form>
        </div>
        <div class="payment-preview-wrapper">
            <div class="payment-header">
                <h3 class="title">{{ __('Preview') }}</h3>
                <div class="payment-tab">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link" id="mobile-tab" data-bs-toggle="tab" data-bs-target="#mobile" type="button" role="tab" aria-controls="mobile" aria-selected="false"><i class="las la-mobile-alt"></i></button>
                            <button class="nav-link active" id="web-tab" data-bs-toggle="tab" data-bs-target="#web" type="button" role="tab" aria-controls="web" aria-selected="true"><i class="las la-tv"></i></button>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade" id="mobile" role="tabpanel" aria-labelledby="mobile-tab">
                    <div class="payment-preview-mockup">
                        <img src="{{ asset('public/frontend/') }}/images/element/mockup.png" alt="element">
                        <div class="payment-preview-box two">
                            <div class="payment-preview-box-left">
                                <form class="payment-preview-box-left-form">
                                    <div class="form-group">
                                        <label>{{ __('Amount') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-text prepend currency_link_symbol">{{ $payment_link->currency_symbol }}</div>
                                            <input type="text" class="form--control paylink_amount" value="{{ $payment_link->price * $payment_link->qty }}" min="0.1" readonly>
                                        </div>
                                        <span class="limit-show">{{ get_amount($payment_link->min_amount) }} {{ $payment_link->currency }} - {{ get_amount($payment_link->max_amount) }} {{ $payment_link->currency }}</span>
                                    </div>
                                </form>
                                <div class="payment-preview-thumb">
                                    <img src="{{ get_fav($basic_settings) }}" alt="logo">
                                </div>
                            </div>
                            <div class="payment-preview-box-right">
                                <form class="payment-preview-box-right-form">
                                    <div class="row">
                                        <div class="col-xl-12 form-group">
                                            <div class="or-area">
                                                <span class="or-line"></span>
                                                <span class="or-title">{{ __('Pay with Debit & Credit Card') }}</span>
                                                <span class="or-line"></span>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <label>{{ __("Email") }}</label>
                                            <input type="email" class="form--control" readonly placeholder="{{ __("Email") }}">
                                        </div>
                                        <div class="col-xl-12">
                                            <label>{{ __("card Information") }}</label>
                                            <input type="text" class="form--control number-input" placeholder="1234 1234 1234 1234" readonly>
                                            <div class="row mt-10">
                                                <div class="col-xl-6 form-group">
                                                    <input type="date" class="form--control" readonly>
                                                </div>
                                                <div class="col-xl-6 form-group">
                                                    <input type="text" class="form--control number-input" placeholder="{{ __("cvc") }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <label>{{ __("Name on card") }}</label>
                                            <input type="text" class="form--control" readonly placeholder="{{ __("Name on card") }}">
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <div class="preview-check-group">
                                                <div class="custom-check-group">
                                                    <input type="checkbox" id="level-2" checked>
                                                    <label for="level-2">{{ __('Securely save my information  for 1-click checkout') }} <span>{{ __("Pay faster on") }} {{ Auth::user()->address->company_name ?? '' }} {{ __("and everywhere Link is accepted") }}</span></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 form-group pt-10">
                                            <button type="submit" class="btn--base disabled w-100 btn-loading" disabled>{{ __('Pay') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade show active" id="web" role="tabpanel" aria-labelledby="web-tab">
                    <div class="payment-preview-box">
                        <div class="payment-preview-box-left">
                            <form class="payment-preview-box-left-form">
                                <div class="form-group">
                                    <label>{{ __('Amount') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-text prepend currency_link_symbol">{{ $payment_link->currency_symbol }}</div>
                                        <input type="text" class="form--control paylink_amount" placeholder="0.00" min="0.1" readonly value="{{ $payment_link->price * $payment_link->qty }}">
                                    </div>
                                    <span class="limit-show">{{ __("limit") }}: {{ get_amount($payment_link->min_amount) }} {{ $payment_link->currency }} - {{ get_amount($payment_link->max_amount) }} {{ $payment_link->currency }}</span>
                                </div>
                            </form>
                            <div class="payment-preview-thumb">
                                <img src="{{ get_fav($basic_settings) }}" alt="logo">
                            </div>
                        </div>
                        <div class="payment-preview-box-right">
                            <form class="payment-preview-box-right-form">
                                <div class="row">
                                    <div class="col-xl-12 form-group">
                                        <div class="or-area">
                                            <span class="or-line"></span>
                                            <span class="or-title">{{ __('Pay with Debit & Credit Card') }}</span>
                                            <span class="or-line"></span>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 form-group">
                                        <label>{{ __("Email") }}</label>
                                        <input type="email" class="form--control" readonly placeholder="{{ __("Email") }}">
                                    </div>
                                    <div class="col-xl-12 form-group">
                                        <label>{{ __("card Information") }}</label>
                                        <input type="text" class="form--control number-input" placeholder="1234 1234 1234 1234" readonly>
                                        <div class="row mt-10">
                                            <div class="col-xl-6 form-group">
                                                <input type="date" class="form--control" readonly>
                                            </div>
                                            <div class="col-xl-6 form-group">
                                                <input type="text" class="form--control number-input" placeholder="{{ __("cvc") }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 form-group">
                                        <label>{{ __("Name on card") }}</label>
                                        <input type="text" class="form--control" readonly placeholder="{{ __("Name on card") }}">
                                    </div>
                                    <div class="col-xl-12 form-group">
                                        <div class="preview-check-group">
                                            <div class="custom-check-group">
                                                <input type="checkbox" id="level-3" checked>
                                                <label for="level-3">{{ __('Securely save my information  for 1-click checkout') }} <span>{{ __("Pay faster on") }} {{ Auth::user()->address->company_name ?? '' }} and everywhere Link is accepted</span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 form-group pt-10">
                                        <button type="submit" class="btn--base disabled  w-100" disabled>{{ __('Pay') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

<script>
    var defualCurrency = "{{ get_default_currency_code() }}";

    $(document).ready(function () {
        var old_image = "{{ $payment_link->image }}";
        var old_image_path = "{{ files_asset_path('payment-link-image') }}";
        var payment_limit = "{{ $payment_link->limit }}";
        var type = '{{ $payment_link->type }}';

        if(old_image != '' && old_image_path != ''){
            $('.payment-preview-thumb img').attr('src', old_image_path+'/'+old_image);
        }


        $('.link_title').on('keyup', function(){
            let title = $(this).val();
            $('.link-sub-title').text(title);
        });

        if(payment_limit == 1){
            $('.dependency-checkbox').attr('checked', true);
        }

        $(".payment-link-image").on('change', function () {
            paymentLinkImagePreview(this);
        });

        $(document).on('change', '.currency_link_sub', function () {
            previewShow('.currency_link_sub');
            subTotalPaymentCal();
        });

        $('.currency_link').on('change', function(){
            previewShow('.currency_link');
        });
    });

    function paymentLinkImagePreview(input){
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.payment-preview-thumb img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>


<script>
    makePayment('.payment-select');
    function makePayment(element) {
        $(element).change(function(){
            if($(element).val() == 'sub'){
                $('.limit-show').addClass('d-none');
                $('.paylink_qty').removeClass('d-none');
                $('.paylink_qty').text('(1)');
            }else{
                $('.paylink_amount').val('');
                $('.paylink_qty').addClass('d-none');
                if($('.dependency-checkbox').is(':checked')){
                    $('.paylink_amount').removeAttr('readonly');
                    $('.limit-show').removeClass('d-none');
                }
            }
            showHidePaymentSection($(this));
        });

        $(document).ready(function(){
            showHidePaymentSection(element);
        });

        function showHidePaymentSection(element) {
            $(".payment-box-area").hide();
            $("#"+$(element).val()+"-view").show();
        }
    }


    $('.qty_change').on('change, keyup', function(){
        subTotalPaymentCal();
    });

    $('.sub_price').on('keyup', function(){
        subTotalPaymentCal();
    });

    function subTotalPaymentCal(){
        let price = $('.sub_price').val();
        let qty = $('.qty_change').val();
        let currency_code = acceptVar('.currency_link_sub').currencyCode;
        currency_code = currency_code == undefined ? 'USD' : currency_code;
        let total_price = price * qty;

        $('.paylink_qty').text(qty);
        $('.paylink_amount').val(total_price);
    }
</script>


<script>

    $(document).on("change",".dependency-checkbox",function() {
        dependencyCheckboxHandle($(this));
    });

    $(document).ready(function() {
        let dependencyCheckbox = $(".dependency-checkbox");
        $.each(dependencyCheckbox, function(index,item) {
            dependencyCheckboxHandle($(item));
        });
    });

    function acceptVar(element) {
        var selectedVal = $(element+" :selected");
        var currencyCode = $(element+" :selected").attr("data-currency_code");
        var currencySymbol = $(element+" :selected").attr("data-currency_symbol");
        var currencyName = $(element+" :selected").attr("data-currency_name");
        return {
            currencyCode:currencyCode,
            selectedVal:selectedVal,
            currencySymbol:currencySymbol,
            currencyName:currencyName,
        };
    }

    function dependencyCheckboxHandle(targetCheckbox) {
        let target = $(targetCheckbox).attr("data-target");
        if($(targetCheckbox).is(":checked")) {
            $("." + target).slideDown(300);
            $('.limit-show').removeClass('d-none');
            previewShow('.currency_link');
        }else {
            $("." + target).slideUp(300);
            $('.limit-show').addClass('d-none');
        }
    }


    // limit calcualtion
    function previewShow(element){
        let currency_code = acceptVar(element).currencyCode;
        let currency_symbol = acceptVar(element).currencySymbol;
        let currency_name = acceptVar(element).currencyName;

        $('input[name="currency_name"]').val(currency_name);
        $('input[name="currency_symbol"]').val(currency_symbol);

        $('.currency_link_symbol').text(currency_symbol)

        limitShowPreveiw(currency_code);
    }

    function limitShowPreveiw(currency_code){
        $(document).on('keyup', 'input[name="min_amount"], input[name="max_amount"]', function(){
            let min_limit = $('input[name="min_amount"]').val();
            let max_limit = $('input[name="max_amount"]').val();

            $('.limit-show').text(parseFloat(min_limit).toFixed(2)+' '+currency_code+' - '+parseFloat(max_limit).toFixed(2)+' '+currency_code+'');
    });
    }
</script>
@endpush
