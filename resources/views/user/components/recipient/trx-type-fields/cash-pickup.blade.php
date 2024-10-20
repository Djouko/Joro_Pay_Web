@php
    $token = (object)session()->get('remittance_token');
    $country= App\Models\Admin\ReceiverCounty::where('id',@$token->receiver_country)->first();
@endphp
<div class="trx-input" style="display: none;">
    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
                'name'          => "firstname",
                'label'         => __("first Name"),
                'label_after'   => "<span>*</span>",
               'placeholder'         => __("first Name"),
            ])
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
                 'label'         => __("last Name"),
                'label_after'   => "<span>*</span>",
                'name'          => "lastname",
                 'placeholder'         => __("last Name"),
            ])
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            <label>{{ __("country") }}<span>*</span></label>
            <select name="country" class="form--control country-select select2-basic " id="cash-pickup">
                <option selected disabled>{{ __("select Country") }}</option>
                @foreach ($countries as $item)
                    <option value="{{ $item->id }}"  {{   @$country->id == $item->id?'selected':'' }} data-country-code="{{ $item->code }}" data-mobile-code="{{ $item->mobile_code }}"  data-id="{{ $item->id }}">{{ $item->country }} ({{ $item->code }})</option>
                @endforeach
            </select>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
              'label'         => __("address"),
                'label_after'   => "<span>*</span>",
                'name'          => "address",
                'type'          => "text",
                 'placeholder'         => __("enter Address"),
                'required'      => true,
                'attribute'     => "id=place-input autocomplete=none",
            ])
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
               'label'         => __("state"),
                'name'          => "state",
                'type'          => "text",
                 'placeholder'         => __("enter State"),
            ])
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
              'label'         => __("city"),
                'label_after'   => "<span>*</span>",
                'name'          => "city",
                'type'          => "text",
                'placeholder'         => __("enter City"),
            ])
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 form-group">
            @include('admin.components.form.input',[
                'label'         => __("zip Code"),
                'label_after'   => "<span>*</span>",
                'name'          => "zip",
                'type'          => "text",
                'placeholder'         => __("zip Code"),
            ])
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 form-group">
        <label>{{ __("phone Number") }}<span>*</span></label>
          <div class="input-group">
            <div class="input-group-text phone-code">+{{ @$country->mobile_code }}</div>
            <input class="phone-code" type="hidden" name="mobile_code"  value="{{  @$country->mobile_code }}"/>
            <input type="text" class="form--control" placeholder="{{ __("enter Mobile Number") }}" name="mobile">
          </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 form-group">
        <label>{{ __("email Address") }}<span>*</span></label>
          <div class="input-group">
            <input type="email" class="form--control" placeholder="{{ __('enter Email Address') }}" name="email">
          </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 form-group">
            <label>{{ __("Pickup Point") }} <span>*</span></label>
            <select name="cash_pickup" class="form--control select2-basic" data-placeholder="Select Pickup Point">
                <option selected disabled>{{ __("Select Pickup Point") }}</option>
                @foreach ($pickup_points as $item)
                    <option value="{{ $item->alias }}">{{  $item->name  }}</option>
                @endforeach
            </select>
        </div>

    </div>
</div>
