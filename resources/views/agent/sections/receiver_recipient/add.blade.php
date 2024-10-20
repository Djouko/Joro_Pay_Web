@extends('agent.layouts.master')

@push('css')

@endpush
@php
    $token = (object)session()->get('receiver_remittance_token');
@endphp

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row justify-content-center mb-30-none">
        <div class="col-xl-12 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ @$page_title }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group transaction-type">
                                    <label>{{ __("Transaction Type") }}<span>*</span></label>
                                    <select  name="transaction_type" required  class="form--control select2-auto-tokenize method_code trx-type-select" data-minimum-results-for-search="Infinity">
                                            <option disabled selected value="">{{ __("Select Transaction Type") }}</option>
                                            <option value="bank-transfer" {{ @$token->transacion_type == 'bank-transfer' ? 'selected':''}} data-name="Bank Transfer">{{__("bank-transfer")}}</option>
                                        <option value="wallet-to-wallet-transfer" {{ @$token->transacion_type == 'wallet-to-wallet-transfer' ? 'selected':''}} data-name="wallet-to-wallet-transfer">{{ @$basic_settings->site_name }} {{__("rWallet")}}</option>
                                        <option value="cash-pickup" {{ @$token->transacion_type ==  'cash-pickup' ? 'selected':''}} data-name="Cash Pickup">{{__("rcash-pickup")}}</option>
                                    </select>
                                </div>


                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading transfer">{{ __("Add Recipient") }} <i class="fas fa-plus-circle ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

<script>
    $("select.trx-type-select").change(function() {
            getTrxFields($(this).val());
    });
    $(document).on("change",".country-select",function() {
        var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
        placePhoneCode(phoneCode);
    });

    $(document).ready(function(){
        var selectedVal = $("select[name=transaction_type] :selected").val();
        if(selectedVal != ''){
            getTrxFields(selectedVal);
        }

    });
    function getTrxFields(value) {
            var value = value;
            if(value == null || value == undefined || value == "") {
                return false;
            }
            var data = {
                _token: laravelCsrf(),
                data:value,
            };
            $.post("{{ setRoute('agent.receiver.recipient.create.get.input') }}",data,function() {
                // success
            }).done(function(response){
                $(".trx-input").remove();
                $(response).insertAfter($(".transaction-type"));
                $(".transaction-type").parent().find(".trx-input").slideDown(400);
                $("select[name=country]").select2();
                $("select[name=state]").select2({
                    tags:true,
                });
                $("select[name=city]").select2({
                    tags:true,
                });
                $("select[name=point]").select2();
            }).fail(function(response) {
                $(".trx-input").remove();
                var response = JSON.parse(response.responseText);
                throwMessage(response.type,response.message.error);
            });
    }
    $(document).on("focusout",".email",function(){
            getUser($(this).val(),"{{ setRoute('agent.get.user.info') }}",$(this));
        });
    function getUser(string,URL,errorPlace = null) {
            if(string.length < 3) {
                return false;
            }
            var CSRF = laravelCsrf();
            var data = {
                _token      : CSRF,
                text        : string,
            };
            $.post(URL,data,function() {
                // success
            }).done(function(response){

                if(response.data == null) {
                    if(errorPlace != null) {
                        // $(errorPlace).css('border','none');
                        // if($(errorPlace).parent().find(".get-user-error").length > 0) {
                        //     // $(errorPlace).parent().find(".get-user-error").text("User doesn't exists");
                        //     throwMessage('error',["User doesn't  exists."]);
                        // }else {
                        //     $(`<span class="text--danger get-user-error mt-2">User doesn't exists!</span>`).insertAfter($(errorPlace));
                        // }
                        $(errorPlace).parents("form").find("input[name=address]").val("");
                        $(errorPlace).parents("form").find("input[name=lastname]").val("");
                        $(errorPlace).parents("form").find("input[name=firstname]").val("");
                        $(errorPlace).parents("form").find("input[name=zip]").val("");
                        $(errorPlace).parents("form").find("input[name=mobile_code]").val("");
                        $(errorPlace).parents("form").find("input[name=mobile]").val("");
                        $(errorPlace).parents("form").find("input[name=state]").val("");
                        $(errorPlace).parents("form").find("input[name=city]").val("");
                        $(errorPlace).parents("form").find(".phone-code").text("");
                        $("select[name=country]").change(function(){
                            var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
                            placePhoneCode(phoneCode);
                        });
                        throwMessage('error',["Agent doesn't  exists."]);
                    }
                }else {
                    if(errorPlace != null) {
                        $(errorPlace).parent().find(".get-user-error").remove();
                        $(errorPlace).css('border','1px solid green');
                    }
                    var user = response.data;
                    if(user.address == null || user.address == "") {
                        user.address = {};
                    }
                    var user_infos = {
                        firstname: user.firstname,
                        lastname: user.lastname,
                        middlename: user.middlename,
                        mobile_code: user.mobile_code,
                        email: user.email,
                        mobile: user.mobile,
                        address: user.address.address ?? "",
                        city: user.address.city ?? "",
                        state: user.address.state ?? "",
                        zip: user.address.zip ?? "",
                    };
                    $.each(user_infos,function(index,item) {
                        if(item == "" || item == null || item == undefined) {
                            $(errorPlace).parents("form").find("input[name="+index+"],textarea[name="+index+"]").removeAttr("readonly");
                        }
                        $(errorPlace).parents("form").find("input[name="+index+"],textarea[name="+index+"]").val(item);
                    })
                    $(errorPlace).parents("form").find(".phone-code").text("+"+user.mobile_code);

                    if(user.address.country == undefined || user.address.country == "") {
                        // make select box for country
                        var country_select = `
                            <label>Country <span>*</span></label>
                            <select name="country" class="form--control country-select" data-placeholder="{{ __('select Country') }}" data-old="">
                                <option selected disabled>Select Country</option>
                            </select>
                        `;
                        $(".country-select-wrp").html(country_select);
                        $("select[name=country]").select2();
                        var state_select = `
                            <label>State <span>*</span></label>
                            <select name="state" class="form--control state-select" data-placeholder="Select State" data-old="">
                                <option selected disabled>Select State</option>
                            </select>
                        `;
                        $(".state-select-wrp").html(state_select);
                        var city_select = `
                            <label>City <span>*</span></label>
                            <select name="city" class="form--control city-select" data-placeholder="Select City" data-old="">
                                <option selected disabled>Select City</option>
                            </select>
                        `;
                        $(".city-select-wrp").html(city_select);
                        getAllCountries("{{ setRoute('global.countries') }}",$(".country-select"),$(".country-select"));
                        countrySelect(".country-select",$(".country-select"));
                        stateSelect(".state-select",$(".state-select"));
                        // $(errorPlace).parents("form").find("input[name=zip]").val("").removeAttr("readonly");
                        $(errorPlace).parents("form").find("input[name=zip]").val("").removeAttr("readonly");
                        $(errorPlace).parents("form").find("input[name=mobile_code]").val("").removeAttr("readonly");
                        $(errorPlace).parents("form").find("input[name=mobile]").val("").removeAttr("readonly");
                        $(errorPlace).parents("form").find("input[name=state]").val("").removeAttr("readonly");
                        $(errorPlace).parents("form").find("input[name=city]").val("").removeAttr("readonly");
                        $(errorPlace).parents("form").find(".phone-code").text("");
                        $("select[name=country]").change(function(){
                            var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
                            placePhoneCode(phoneCode);
                        });
                    }else {
                        $(errorPlace).parents("form").find("input[name=country]").val(user.address.country ?? "");
                        $(errorPlace).parents("form").find("input[name=state]").val(user.address.state ?? "");
                        $(errorPlace).parents("form").find("input[name=city]").val(user.address.city ?? "");
                        $(errorPlace).parents("form").find("input[name=zip]").val(user.address.zip ?? "");
                    }
                }
            }).fail(function(response) {
                var response = JSON.parse(response.responseText);
                throwMessage(response.type,response.message.error);
            });
    }
</script>
@endpush
