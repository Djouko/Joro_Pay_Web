@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $contact_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CONTACT_SECTION);
    $contact = App\Models\Admin\SiteSections::getData( $contact_slug)->first();
@endphp

@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Contact
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="contact-section ptb-150">
    <div class="container">
        <div class="row justify-content-center mb-30-none">
            <div class="col-xl-5 col-lg-5 mb-30">
                <div class="contact-widget wow fadeInLeft" data-wow-duration="1s" data-wow-delay=".4s">
                    <div class="contact-form-header">
                        <h2 class="title">{{ __(@$contact->value->language->$lang->heading) }}</h2>
                        <p>{{ __(@$contact->value->language->$lang->sub_heading) }}</p>
                    </div>
                    <ul class="contact-item-list">
                        <li>
                            <a href="#0">
                                <div class="contact-item-icon">
                                    <i class="las la-map-marked-alt"></i>
                                </div>
                                <div class="contact-item-content">
                                    <h5 class="title">{{ __("Our Location") }}</h5>
                                    <span class="sub-title">{{ __(@$contact->value->language->$lang->location) }}</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#0">
                                <div class="contact-item-icon tow">
                                    <i class="las la-phone-volume"></i>
                                </div>
                                <div class="contact-item-content">
                                    <h5 class="title">{{ __("Call Us At") }}: +{{ __(@$contact->value->language->$lang->mobile) }}</h5>
                                    <span class="sub-title">{{ __(@$contact->value->language->$lang->office_hours) }}</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#0">
                                <div class="contact-item-icon three">
                                    <i class="las la-envelope"></i>
                                </div>
                                <div class="contact-item-content">
                                    <h5 class="title">{{ __("Email Us Directly") }}</h5>
                                    <span class="sub-title">{{ __(@$contact->value->language->$lang->email) }}</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-xl-7 col-lg-7 mb-30">
                <div class="contact-form-inner wow fadeInRight" data-wow-duration="1s" data-wow-delay=".4s">
                    <div class="contact-form-area">
                        <form class="contact-form" action="{{ setRoute('contact.store') }}"  method="POST" id="contact-form">
                            @csrf
                            <div class="row justify-content-center mb-10-none">
                                <div class="col-xl-6 col-lg-6 col-md-6 form-group">
                                    <label>{{ __("name") }}<span>*</span></label>
                                    <input type="text" name="name" class="form--control" placeholder="{{ __("Enter Name") }}" required>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 form-group">
                                    <label>{{ __("Email") }}<span>*</span></label>
                                    <input type="email" name="email" class="form--control" placeholder="{{ __("enter Email Address") }}" required>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 form-group">
                                    <label>{{ __("Phone") }}<span>*</span></label>
                                    <input type="text" name="mobile" class="form--control number-input" placeholder="{{ __("enter Phone") }}" required>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 form-group">
                                    <label>{{ __("Subject") }}<span>*</span></label>
                                    <input type="text" name="subject" class="form--control" placeholder="{{ __("Enter Subject") }}" required>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Message") }}<span>*</span></label>
                                    <textarea class="form--control" name="message" placeholder="{{ __("Write Here..") }}." required></textarea>
                                </div>

                                <div class="col-lg-12 form-group">
                                    <button type="submit" class="btn--base mt-10 contact-btn">{{ __("Send Message") }} <i class="las la-angle-right"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Contact
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection


@push("script")

@endpush
