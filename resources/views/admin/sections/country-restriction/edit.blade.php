@extends('admin.layouts.master')

@push('css')
@php
    $rejectedCountries = $data->data ?? []; // Rejected countries
    $allCountries = get_all_countries() ?? []; // All available countries
@endphp
@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __($page_title)])
@endsection

@section('content')

<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __($page_title) }}</h6>
        <div class="table-btn-area">
            @include('admin.components.link.custom',[
                'href'          => route('admin.country.restriction.index'),
                'class'         => "btn btn--base",
                'text'          =>__("Back"),
                'permission'    => "admin.country.restriction.index",
            ])
        </div>

    </div>
    <div class="card-body">
        <form action="{{ setRoute('admin.country.restriction.update',$data->slug) }}" method="POST">
            @csrf
            @method("PUT")
            <div class="custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 form-group mt-2">
                            <label for="selectRole">{{ __("Remove Any Country") }}</label>
                            <select name="countries[]" id="selectTitle" class="select2-auto-tokenize form--control" data-placeholder="{{ __("select Country") }}" multiple>
                                {{-- Loop through rejected countries --}}
                                @foreach ($rejectedCountries as $countryName)
                                    <option value="{{ $countryName }}">{{ $countryName }}</option>
                                @endforeach
                                {{-- Loop through all countries --}}
                                @foreach ($allCountries as $country)
                                    {{-- Check if the country is not in the rejected countries --}}
                                    @if (!in_array($country->name, $rejectedCountries))
                                        {{-- Check if the country is selected --}}
                                        <option value="{{ $country->name }}" selected>{{ $country->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.button.form-btn',[
                                'class'         => "w-100 btn-loading",
                                'text'          => __("Save & Change"),
                                'permission'    => "admin.country.restriction.update",
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')

@endpush
