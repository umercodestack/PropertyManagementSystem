@extends('Admin.layouts.app')
@section('content')

<style>
    @font-face {
        font-family: 'Airbnb Cereal VF';
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_W_Wght.2d9d32865ef1262644c455b3ead871e9.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_W_Wght.2d9d32865ef1262644c455b3ead871e9.woff2') format('woff2-variations');
        font-style: normal;
        unicode-range: U+0000-03FF, U+0500-058F, U+0700-074F, U+0780-FAFF, U+FE00-FE6F, U+FF00-EFFFF, U+FFFFE-10FFFF;
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Italics_W_Wght.4d5968bfe066c741d708adc61baed262.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Italics_W_Wght.4d5968bfe066c741d708adc61baed262.woff2') format('woff2-variations');
        font-style: italic;
        unicode-range: U+0000-03FF, U+0500-058F, U+0700-074F, U+0780-FAFF, U+FE00-FE6F, U+FF00-EFFFF, U+FFFFE-10FFFF;
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        unicode-range: U+0600-06FF, U+0750-077F;
        font-style: normal;
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Arabic_W_Wght.d9f154d65b7b534fa988aba51062c8df.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Arabic_W_Wght.d9f154d65b7b534fa988aba51062c8df.woff2') format('woff2-variations');
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        unicode-range: U+0400-04FF;
        font-style: normal;
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Cyril_W_Wght.26f0964acf4eb88cb3589d38a3182964.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Cyril_W_Wght.26f0964acf4eb88cb3589d38a3182964.woff2') format('woff2-variations');
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        unicode-range: U+0590-05FF, U+FB00-FB4F;
        font-style: normal;
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Hebrew_W_Wght.33e5e2c8babc146eff37ebcaa12cb8bb.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Hebrew_W_Wght.33e5e2c8babc146eff37ebcaa12cb8bb.woff2') format('woff2-variations');
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_W_Wght.2d9d32865ef1262644c455b3ead871e9.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_W_Wght.2d9d32865ef1262644c455b3ead871e9.woff2') format('woff2-variations');
        font-style: normal;
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        unicode-range: U+0900-097F;
        font-style: normal;
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Deva_W_Wght.11994dc426c38d93ebc5f3cc27490d30.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Deva_W_Wght.11994dc426c38d93ebc5f3cc27490d30.woff2') format('woff2-variations');
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        unicode-range: U+0E00-0E7F;
        font-style: normal;
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_ThaiLp_W_Wght.61a8da9355421c30286deb6d36aa3fb0.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_ThaiLp_W_Wght.61a8da9355421c30286deb6d36aa3fb0.woff2') format('woff2-variations');
        font-display: swap;
    }

    @font-face {
        font-family: 'Airbnb Cereal VF';
        unicode-range: U+0370-03FF;
        font-style: normal;
        src: url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Greek_W_Wght.7f56bb1b50c0f29a313a57f55e55be5f.woff2') format('woff2 supports variations'), url('https://a0.muscache.com/airbnb/static/airbnb-dls-web/build/fonts/cereal-variable/AirbnbCerealVF_Greek_W_Wght.7f56bb1b50c0f29a313a57f55e55be5f.woff2') format('woff2-variations');
        font-display: swap;
    }

    .chat_wrapper {
        font-family: Airbnb Cereal VF, Circular, BlinkMacSystemFont, Roboto, Helvetica Neue, sans-serif !important;
    }

    .chat_wrapper .nk-reply-entry .sender-property,
    .chat_wrapper .nk-reply-entry .sender-user {
        font-size: 16px;
    }


    .chat_wrapper .all-messages {
        height: 77vh !important;
        overflow-y: auto !important;
        max-height: 77vh !important;
        transition: all 1s ease;
    }

    .chat_wrapper .nk-reply-form {
        margin: 0.5rem 2rem .0rem 2rem;
    }

    .chat_wrapper .thread_loader {
        z-index: 0 !important;

    }

    .nk-reply-form {
        position: sticky !important;
        bottom: 0px !important;
        z-index: 5 !important;
        background: white !important;
    }


    .nk-msg-head {
        padding: 1rem 2.5rem !important;
    }

    .search-user {
        position: relative !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        border-radius: 25px !important;
    }

    .search-user input {
        padding: 10px 15px !important;
        border-radius: 25px !important;
        border: 2px solid #949494 !important;
        outline: none !important;
        width: 100% !important;
    }

    .search-user span i {
        position: absolute;
        top: 10px;
        right: 15px;
    }
    .sender-request{
        justify-content: center !important;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .sender-request .sender-user{
        background-color: #8080800f !important;
        padding: 1px 10px !important;
        /* color: white !important; */
        border: 2px solid #8094ae;
        color: #8094ae;
        font-size: 14px !important;
    }

    .sender-request .sender-user::before{
        display: none !important;
    }
    .center-date{
        font-size: 12px !important;
        padding: 2px 3px !important;
    }
    .nk-reply-body .guest-msg{
        justify-content: start;
    }
    .nk-reply-body .property-msg{
        justify-content: end;
    }

    @media (min-width: 992px) {
        .nk-msg-head {
            padding: 1rem 2.5rem !important;
        }
    }

    @media (min-width: 992px) {
        .nk-msg-head .title {
            margin-bottom: 0px !important
        }
    }
</style>


<input type="hidden" id="hdnthreadid" value="">
<input type="hidden" id="hdntitle" value="">
<input type="hidden" id="hdnlive_feed_event_id" value="">
<input type="hidden" id="hdnthread_type" value="">
<input type="hidden" id="hdnbooking_json" value="">
<input type="hidden" id="hdnitemName" value="">
<input type="hidden" id="hdnlistingName" value="">


<div class=" chat_wrapper">
    <div class="nk-content-inner chat_wrapper">
        <div class="nk-content-body">
            <div class="nk-msg">
                <div class="nk-msg-aside">
                    <div class="nk-msg-nav">
                        <!-- <h5 class="pt-1 ps-1 text-left">Threads</h5> -->
                        <div class="mt-30"
                            style="position: relative; display: flex; justify-content: center; align-items: center; border-radius: 25px;">
                            <input id="search-input" type="text" placeholder="Search..." name="search"
                                class="thread-search"
                                style="padding: 2px 15px; border-radius: 25px; border: 2px solid #fff; outline: none; width: 100%; color: #000;     background-color: #fff;">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-search" viewBox="0 0 16 16"
                                    style="position: absolute; top: 0px; right: 15px; color:#203247 ">
                                    <path
                                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                                </svg>
                                <!-- <i class="bi bi-search" style="position: absolute; top: 10px; right: 15px;"></i> -->
                                <!-- <i class="fa-solid fa-magnifying-glass" ></i> -->
                            </span>
                        </div>

                        <div class="search-wrap" data-search="search">
                            <div class="search-content">
                                <a href="#" class="search-back btn btn-icon toggle-search" data-target="search"><em
                                        class="icon ni ni-arrow-left"></em></a>
                                <input type="text" class="form-control border-transparent form-focus-none"
                                    placeholder="Search by user or message">
                                <button class="search-submit btn btn-icon"><em class="icon ni ni-search"></em></button>
                            </div>
                        </div><!-- .search-wrap -->
                    </div><!-- .nk-msg-nav -->
                    <div class="nk-msg-list" data-simplebar="init">
                        <div class="simplebar-wrapper" style="margin: 0px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="" tabindex="0" role="region" aria-label="scrollable content"
                                        style="height: auto;">
                                        <div class="thread_loader" id="thread_loader">

                                        </div>
                                        <div class="simplebar-content all-threads" id="thread-content"
                                            style="padding: 0px;">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 1401px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar"
                                style="height: 25px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
                        </div>
                    </div><!-- .nk-msg-list -->
                </div><!-- .nk-msg-aside -->
                <div class="nk-msg-body bg-white">
                    <div class="nk-msg-head">
                        <h4 class="title d-none d-lg-block" id="listing_name"><!-- Messagner name --> </h4>
                        <div class="nk-msg-head-meta">
                            <div class="d-none d-lg-block">
                            </div>
                        </div>
                        <a href="#" class="nk-msg-profile-toggle profile-toggle"><em
                                class="icon ni ni-arrow-left"></em></a>
                    </div><!-- .nk-msg-head -->
                    <div class="nk-msg-reply nk-reply" data-simplebar="init">
                        <div class="simplebar-wrapper" style="margin: 0px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                        aria-label="scrollable content" style="height: auto; overflow: hidden scroll;">
                                        <div class="simplebar-content all-messages" style="padding: 0px;"
                                            id="allMessages">
                                            <div id="message_div" style="display:flex; flex-direction: column;">

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 1374px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar"
                                style="height: 25px; transform: translate3d(0px, -17px, 0px); display: block;"></div>
                        </div>
                    </div><!-- .nk-reply -->
                    <div class="nk-msg-profile" data-simplebar="init">
                        <div class="simplebar-wrapper" style="margin: 0px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                        aria-label="scrollable content" style="height: 100%; overflow: hidden scroll;">
                                        <div class="simplebar-content" style="padding: 0px;">
                                            <div class="card">
                                                <div class="card-inner-group">
                                                    <div class="card-inner-disabled">
                                                        <div class="user-card user-card-s2">
                                                            <div class="user-avatar md bg-primary">
                                                                <span>AB</span>
                                                            </div>
                                                            <div class="user-info">
                                                                <h5 id="cust_title"></h5>
                                                                <!-- <span class="sub-text">Customer</span> -->
                                                                <p class="listing_names"></p>
                                                                <p class="current_status"></p>
                                                            </div>

                                                            {{-- <div class="user-card-menu dropdown"> --}}
                                                                {{-- <a href="#"
                                                                    class="btn btn-icon btn-sm btn-trigger dropdown-toggle"
                                                                    data-bs-toggle="dropdown"><em
                                                                        class="icon ni ni-more-h"></em></a> --}}
                                                                {{-- <div class="dropdown-menu dropdown-menu-end"> --}}
                                                                    {{-- <ul class="link-list-opt no-bdr"> --}}
                                                                        {{-- <li><a href="#"><em
                                                                                    class="icon ni ni-eye"></em><span>View
                                                                                    Profile</span></a></li> --}}
                                                                        {{-- <li><a href="#"><em
                                                                                    class="icon ni ni-na"></em><span>Ban
                                                                                    From System</span></a></li> --}}
                                                                        {{-- <li><a href="#"><em
                                                                                    class="icon ni ni-repeat"></em><span>View
                                                                                    Orders</span></a></li> --}}
                                                                        {{-- </ul> --}}
                                                                    {{-- </div> --}}
                                                                {{-- </div> --}}
                                                        </div>
                                                        {{-- <div class="row text-center g-1"> --}}
                                                            {{-- <div class="col-4"> --}}
                                                                {{-- <div class="profile-stats"> --}}
                                                                    {{-- <span class="amount">23</span> --}}
                                                                    {{-- <span class="sub-text">Total Order</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- </div> --}}
                                                            {{-- <div class="col-4"> --}}
                                                                {{-- <div class="profile-stats"> --}}
                                                                    {{-- <span class="amount">20</span> --}}
                                                                    {{-- <span class="sub-text">Complete</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- </div> --}}
                                                            {{-- <div class="col-4"> --}}
                                                                {{-- <div class="profile-stats"> --}}
                                                                    {{-- <span class="amount">3</span> --}}
                                                                    {{-- <span class="sub-text">Progress</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- </div> --}}
                                                            {{-- </div> --}}
                                                    </div><!-- .card-inner -->

                                                    <div class="card-inner">
                                                        <div class="property-details" id="propertyDetails">
                                                            <h4 id="guestDetailHeading">Guest Details</h4>
                                                            <!-- <span class="mb-1" id="apartmentHeading"><strong>Apartment:
                                                                </strong><span id="apartment_title"></span></span>
                                                            <br> -->
                                                            <span class="mt-5" id="guestNameHeading"><strong>Guest Name:
                                                                </strong><span id="guest_name"></span></span>
                                                            <br>
                                                            <div id="booking_details_div" class="mt-0">
                                                                <h4>Booking Details</h4>
                                                            </div>
                                                        </div>

                                                        <div class="row" id="inquiry_offer_card">
                                                            <div class="col-md-6">
                                                                <button class="btn btn-primary btn-sm"
                                                                    style="border-color: #203247 !important; font-size: 11px !important;"
                                                                    id="approve_btn" data-id=""
                                                                    onclick="approveOrRejectInquiry(this, 'approve')">Pre-Approve</button>
                                                            </div>
                                                            {{-- <div class="col-md-6"> --}}
                                                                {{-- <button class="btn btn-primary btn-sm">Special
                                                                    Offer</button> --}}
                                                                {{-- </div> --}}
                                                            <div class="col-md-6">
                                                                <button class="btn btn-primary btn-sm"
                                                                    style="border-color: #203247 !important; font-size: 11px !important; "
                                                                    id="special_offer" data-id=""
                                                                    onclick="openSpecialOfferDiv(this)">Special
                                                                    Offer</button>
                                                            </div>
                                                            <div class="col-md-12" id="special_offer_div">

                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <p id="inquiry_text"
                                                                    style="background: #798bff; border-radius: 5px; padding: 5px; color: white; display: none">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        {{-- <div class="aside-wg"> --}}
                                                            {{-- <h6 class="overline-title-alt mb-2">User Information
                                                            </h6> --}}
                                                            {{-- <ul class="user-contacts"> --}}
                                                                {{-- <li> --}}
                                                                    {{-- <em
                                                                        class="icon ni ni-mail"></em><span>info@softnio.com</span>
                                                                    --}}
                                                                    {{-- </li> --}}
                                                                {{-- <li> --}}
                                                                    {{-- <em
                                                                        class="icon ni ni-call"></em><span>+938392939</span>
                                                                    --}}
                                                                    {{-- </li> --}}
                                                                {{-- <li> --}}
                                                                    {{-- <em class="icon ni ni-map-pin"></em><span>1134
                                                                        Ridder Park Road <br>San Fransisco, CA
                                                                        94851</span> --}}
                                                                    {{-- </li> --}}
                                                                {{-- </ul> --}}
                                                            {{-- </div> --}}
                                                        {{-- <div class="aside-wg"> --}}
                                                            {{-- <h6 class="overline-title-alt mb-2">Additional</h6>
                                                            --}}
                                                            {{-- <div class="row gx-1 gy-3"> --}}
                                                                {{-- <div class="col-6"> --}}
                                                                    {{-- <span class="sub-text">Ref ID: </span> --}}
                                                                    {{-- <span>TID-049583</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- <div class="col-6"> --}}
                                                                    {{-- <span class="sub-text">Requested:</span> --}}
                                                                    {{-- <span>Abu Bin Ishtiak</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- <div class="col-6"> --}}
                                                                    {{-- <span class="sub-text">Status:</span> --}}
                                                                    {{-- <span
                                                                        class="lead-text text-success">Open</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- <div class="col-6"> --}}
                                                                    {{-- <span class="sub-text">Last Reply:</span> --}}
                                                                    {{-- <span>Abu Bin Ishtiak</span> --}}
                                                                    {{-- </div> --}}
                                                                {{-- </div> --}}
                                                            {{-- </div> --}}
                                                        {{-- <div class="aside-wg"> --}}
                                                            {{-- <h6 class="overline-title-alt mb-2">Assigned Account
                                                            </h6> --}}
                                                            {{-- <ul class="align-center g-2"> --}}
                                                                {{-- <li> --}}
                                                                    {{-- <div class="user-avatar bg-purple"> --}}
                                                                        {{-- <span>IH</span> --}}
                                                                        {{-- </div> --}}
                                                                    {{-- </li> --}}
                                                                {{-- <li> --}}
                                                                    {{-- <div class="user-avatar bg-pink"> --}}
                                                                        {{-- <span>ST</span> --}}
                                                                        {{-- </div> --}}
                                                                    {{-- </li> --}}
                                                                {{-- <li> --}}
                                                                    {{-- <div class="user-avatar bg-gray"> --}}
                                                                        {{-- <span>SI</span> --}}
                                                                        {{-- </div> --}}
                                                                    {{-- </li> --}}
                                                                {{-- </ul> --}}
                                                            {{-- </div> --}}
                                                    </div><!-- .card-inner -->


                                                    <!-- PAYMENT CARD -->
                                                     <div class="card-inner">
                                                        <div class="property-details mb-3" id="propertyDetails">
                                                            <h4 id="paymentDetailHeading">Payment Details</h4>
                                                            <span class="mb-1" id="perNight_heading"><strong>Per Night Charges:
                                                                </strong><span id="perNight_title"></span></span>
                                                            <br>
                                                            <span class="mt-5" id="discount_heading"><strong>Discount:
                                                                </strong><span id="discount_title"></span></span>
                                                            <br>
                                                            <span class="mt-5" id="cleaningFee_heading"><strong>Cleaning Fee:
                                                                </strong><span id="cleaningFee_title"></span></span>
                                                            <br>
                                                            <!-- <span class="mt-5" id="serviceFee_heading"><strong>Service Fee:
                                                                </strong><span id="serviceFee_title"></span></span>
                                                            <br> -->
                                                            <span class="mt-5" id="promotion_heading"><strong>Promotion:
                                                                </strong><span id="promotion_title"></span></span>
                                                            <br>
                                                            <span class="mt-5" id="otaCommission_heading"><strong>Ota Commission:
                                                                </strong><span id="otaCommission_title"></span></span>
                                                            <br>
                                                            <span class="mt-5" id="totalCharges_heading"><strong>Total Charges:
                                                                </strong><span id="totalCharges_title"></span></span>
                                                            <br>
                                                        </div>
                                                    </div>
                                                    <!-- PAYMENT CARD -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 718px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar"
                                style="height: 25px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
                        </div>
                    </div><!-- .nk-msg-profile -->
                </div><!-- .nk-msg-body -->
            </div><!-- .nk-msg -->
        </div>
    </div>
</div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var limit = 10;
    var countOffset = 0;
    var searchThreads = "";
    window.onload = function () {

        var messageDiv = document.getElementById("message_div");
        messageDiv.scrollTop = messageDiv.scrollHeight;
    };
    let old_threads = [];
    let new_threads = [];

    function openSpecialOfferDiv(button) {
        $('#special_offer_div').html('');
        let dataId = $(button).attr('data-id');
        let html = `<div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                            <label for="">Amount</label>
                            <input type="number" class="form-control" name="special_offer" id="special_offer_input">
                            </div>
                        </div>
                         <div class="col-md-6 ">
                            <button class="btn btn-primary btn-sm mt-4 offer_btn" style="background-color: #203247 !important; border: none !important" data-id = "` + dataId + `"  onclick="approveOrRejectInquiry(this, 'special_offer')">Submit</button>
                        </div>
                    </div>
                   `;
        $('#special_offer_div').append(html);
    }

    function approveOrRejectInquiry(button, status) {

        let special_offer_amount = null;

        if (status === 'special_offer') {
            if ($('#special_offer_input').val() === null || $('#special_offer_input').val() === '') {
                alert('Please enter the valid amount of special offer');
                return false
            }
            special_offer_amount = $('#special_offer_input').val();
        }
        let dataId = $(button).attr('data-id');

        $.ajax({
            url: "{{ route('approveOrRejectInquiry') }}",
            type: "POST",
            data: {
                live_feed_event_id: dataId,
                amount: special_offer_amount,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response) {
                    if (status == 'approve') {
                        $('#inquiry_offer_card').css('display', 'none');
                        $('#inquiry_text').css('display', 'block');
                        $('#inquiry_text').text(`Book Now offer has been sent to user`);
                    } else {
                        $('#inquiry_offer_card').css('display', 'none');
                        $('#inquiry_text').css('display', 'block');
                        $('#inquiry_text').text(`The Special Offer has Been sent of price ${special_offer_amount}`);
                    }
                }
            },
            error: function (error) {
                console.error("Error during form submission:", error);
                // Handle errors as needed
            }
        });
    }

    function passJson(obj) {
        return obj;
    }
    function getThread(offsets, searchThreads) {

        const urlParams = new URLSearchParams(window.location.search);
        const thIds = urlParams.get('thread_id') || '';

        let thread_loader = $('#thread_loader');
        thread_loader.css('display', 'flex');
        thread_loader.html("<div class='spinner-border' style='width: 3rem; height: 3rem;' role='status'><span class='sr-only'>Loading...</span></div>");


        $.ajax({
            url: "{{ route('fetchThreadsAdmin') }}",
            type: "get",
            data: {
                system: 'Admin',
                offset: countOffset,
                limit: limit,
                search: searchThreads,
                thid : thIds
                

            },
            success: function (response) {
                let thread_type = "'" + response[0]?.thread_type + "'";
                if (response?.length === 0 && $('.nk-msg-item')?.length === 0) {
                    $('#thread-content').append(`<h4 style="text-align:center; margin:auto 0;"> No Thread Found</h4>`);
                    fetchThreadByID(null);

                }
                thread_loader.css('display', 'none');
                thread_loader[0]?.style?.setProperty('z-index', '1', 'important');

                response[0]?.name ? $('#cust_title').append(response[0]?.name) : $('#cust_title').append("")

                response?.map((item, index) => {

                    old_threads.push(item?.id);
                    // old_threads.push(Number(item.id));
                    let date = item?.message_date.split("T")[0]; // "2024-04-21"
                    if (index === 0) {




                        // fetchThreadByID(
                        //     item?.id,
                        //     response[0]?.name,
                        //     response[0]?.live_feed_event_id,
                        //     thread_type,
                        //     encodeURIComponent(JSON.stringify(response[0].booking_info_json)),
                        //     item?.name,
                        //     item?.listing_name
                        // );
                    }

                    // $.ajax({
                    //     url: "",
                    //     type: "get",
                    //     data: {thread_id: item.id},
                    //     success: function (response) {
                    //         $('#last_message_'+item.id).text(response.messages[0].message)
                    //     },
                    //     error: function(jqXHR, textStatus, errorThrown) {
                    //         console.log(textStatus, errorThrown);
                    //     }
                    // });
                    // if (index === 0 ) {
                    //     fetchThreadByID(item.id, response[0].name, response[0].live_feed_event_id, item.name);
                    //     // return false;
                    // }

                    // console.log(item.title);

                    let title = "'" + item?.name + "'"
                    let live_feed_event_id = "'" + item.live_feed_event_id + "'"
                    $('#approve_btn').attr('data-id', response[0].live_feed_event_id);
                    $('#special_offer').attr('data-id', response[0].live_feed_event_id);
                    let itemName = "'" + item?.name + "'";

                    let logoHtml = '';
                    if (item.connection_type == "airbnb" || item.connection_type == null) {
                        logoHtml = `
            <span>
                <img src="{{ asset('assets/images/logo/airbnb.png') }}"
                     srcset="{{ asset('assets/images/logo/airbnb.png') }}"
                     alt="logo"
                     style="width:20px; max-width:20px; margin-top:7px" />
            </span>`;
                    }

                    if (item.connection_type == "bcom" || item.connection_type == "BCom") {
                        logoHtml = `
            <span>
                <img src="{{ asset('assets/images/logo/bookingcom-1.svg') }}"
                     srcset="{{ asset('assets/images/logo/bookingcom-1.svg') }}"
                     alt="logo"
                     style="width:20px; max-width:20px; margin-top:7px" />
            </span>`;
                    }
                    if (item.connection_type == "vrbo") {
                        logoHtml = `
            <span>
                <img src="{{ asset('assets/images/logo/vrbo.png') }}"
                     srcset="{{ asset('assets/images/logo/vrbo.png') }}"
                     alt="logo"
                     style="width:20px; max-width:20px; margin-top:7px" />
            </span>`;
                    }

                    let bookingInfoJson = item?.booking_info_json || item?.booking_request_json;


                    let newMessage = `
    <div class="nk-msg-item" id="nk-msg-item-${item?.id}" data-msg-id="` + index + `" 
    onclick="fetchThreadByID('${item?.id}' , '${item?.name}', '${item?.live_feed_event_id}', '${item?.thread_type}', '${encodeURIComponent(JSON.stringify(item?.booking_info_json || item?.booking_request_json))}', '${item?.name}', '${item?.listing_name}','${item?.status}')">
                        <div style="display:flex; flex-direction:column;     align-items: center;">
                            <div class="nk-msg-media user-avatar" >
                                <span>` + item?.name.charAt(0) + `</span>
                               
                            </div>
                           

                             </div>
                            <div class="nk-msg-info">
                                <div class="nk-msg-from">
                                    <div class="nk-msg-sender">
                                               <div class="name"> ${item?.name?.length > 8 ? item?.name?.substring(0, 8) + "..." : item?.name}
                                               <span class="seen-msg seen-msg${item?.id}" style="color:#e85347; font-size: 9px; margin-left:5px;">${item?.is_read == 0 ? "unread" : ""} </span></div>
                                    </div>
                                    <div class="nk-msg-meta">
                                        <div class="date" style="width: 127px;">` + date + `</div>
                                        
                                    </div>
                                </div>
                                <div class="nk-msg-context">
                                    <div class="nk-msg-text">
            <p title="${item?.last_message}" data-full-text="${item?.last_message}">
                <span style="font-weight:700">${item?.last_message_sender}</span> : ${item?.last_message?.length > 26 ? item?.last_message?.substring(0, 26) + "..." : item?.last_message}</p>
                
  ${logoHtml} 
  
        </div>
                                </div>
                            </div>
                        </div>
                        `;
                    $('#thread-content').append(newMessage);
                    if ($(`#nk-msg-item-${item?.id}`) && index == 0 && countOffset == 0) {
                        // Safely encode all parameters

                        fetchThreadByID(item?.id, item?.name, item?.live_feed_event_id, item?.thread_type, encodeURIComponent(JSON.stringify(item?.booking_info_json || item?.booking_request_json)), item?.name, item?.listing_name,item?.status);


                    }

                })
                // if(response) {
                //     // console.log(response)
                //     response.map((item, index) => {
                //         console.log(item)
                //         const option = $('<option></option>')
                //             .attr('value', item.id) // set the value attribute to the item ID
                //             .text(item.title); // set the text content to the item name
                //         $('#listing_id').append(option);
                //     })
                // }
                scrollToBottom();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // console.log(textStatus, errorThrown);
            }
        });
    }




    setTimeout(function () {
        main();
    }, 500);



    // getNewThreads()
    // console.log(old_threads);
    // console.log(new_threads);
    // let old_threads1 = ['thread1', 'thread2', 'thread3'];
    // let new_threads1 = ['thread2', 'thread3', 'thread4', 'thread5'];
    // function arrayDifference(arr1, arr2) {
    //     const set1 = new Set(arr1);
    //     const set2 = new Set(arr2);
    //     const diff1 = [...set1].filter(x => !set2.has(x));
    //     const diff2 = [...set2].filter(x => !set1.has(x));
    //     return [...diff1, ...diff2];
    // }
    // let differences = arrayDifference(old_threads1, new_threads1);
    // console.log(differences)
    // function checkForNewThread() {
    //
    // }

    function areYouSure(thread_id, action_type){
        
        var labl = "";
        if(action_type == "accept_request"){
            var labl = "Accept";
        }
        
        if(action_type == "decline_request"){
            var labl = "Decline";
        }
        
        let userConsent = confirm("Do you want to "+labl+" the request?");

        if (userConsent) {
            acceptOrDeclineBookingRequest(thread_id, action_type);
        }
    }
    
    function acceptOrDeclineBookingRequest(thread_id, action_type){
        
        let bookingRequestSubmit = window.location.origin + '/booking-request-submit';
        
        if (typeof thread_id !== 'undefined' && thread_id !== null && typeof action_type !== 'undefined' && action_type !== null) {
            $.ajax({
                url: bookingRequestSubmit,
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    thread_id: thread_id,
                    action_type: action_type,
                },
    
                success: function (response) {
                    console.log(response);
                    alert('Successfully submitted');
                    
                    location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    
                    alert('Something went wrong');
                    
                    console.log(jqXHR, textStatus, errorThrown, "jqXHR");
                }
            });
        }
    }
    
    function fetchThreadByID(thread_id = null, title = null, live_feed_event_id = null, thread_type = null, booking_json = null, itemName = null, listingName = "-" , status = "" ) {
        // console.log(decodeURIComponent(JSON.parse(booking_json)),"booking_json")
        // console.log(booking_json,"booking_json");
        $("#hdnthreadid").val(thread_id);
        $("#hdntitle").val(title);
        $("#hdnlive_feed_event_id").val(live_feed_event_id);
        $("#hdnthread_type").val(thread_type);
        $("#hdnbooking_json").val(booking_json);
        $("#hdnitemName").val(itemName);
        $("#hdnlistingName").val(listingName);
        $('#apartment_title').html('')
        $('#guest_name').html('')
        $('#guestDetailHeading').html('');
        // $('#apartmentHeading').html('');
        $('#guestNameHeading').html('');
        $('#booking_details_div').html('');

        $('#perNight_title').html('');
        $('#discount_title').html('');
        $('#cleaningFee_title').html('');
        // $('#serviceFee_title').html('');
        $('#promotion_title').html('');
        $('#otaCommission_title').html('');
        $('#totalCharges_title').html('');

        let updateUnReadThread = window.location.origin + '/api/is_read/' +
            thread_id;
        $.ajax({
            url: updateUnReadThread,

            type: "PUT",
            data: {
                _token: '{{ csrf_token() }}'
            },

            success: function (response) {
                $(`.seen-msg${response?.id}`).css('display', 'none');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(`.seen-msg${response?.id}`).css('display', 'inline-block');
                console.log(jqXHR, textStatus, errorThrown, "jqXHR");

            }
        });

        if (booking_json != null && booking_json !== '' && booking_json != "null" && booking_json != undefined) {
            let booking_info = decodeURIComponent(booking_json)
            let booking_info_detail = JSON.parse(booking_info)
            let booking_info_details = JSON.parse(booking_info_detail)
            // console.log(booking_info_details?.payload?.bms?.rooms[0]?.amount
            // ,"booking_info_details");
            $('#guestDetailHeading').text('Guest Details');
            // $('#apartmentHeading').html(`<strong>Apartment: </strong><span id="apartment_title"></span>`);
            $('#guestNameHeading').html(`<strong>Guest Name: </strong><span id="guest_name">${itemName ?? ""}</span>`);
            // let booking_info_details = JSON.parse(booking_json);

            console.log(booking_info_details, "booking_info_details");

            var nights = booking_info_details?.nights ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.nights;

            var base_p = booking_info_details?.listing_base_price_accurate ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.listing_base_price_accurate;

            var per_night_price = parseFloat(base_p) / parseInt(nights);

            var cleaning_fee =  booking_info_details?.raw_message?.reservation?.standard_fees_details[0]?.amount_native ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.standard_fees_details[0]?.amount_native;

            var ota_commission = booking_info_details?.ota_commission ?? booking_info_details?.payload?.bms?.ota_commission;
            
            var total = booking_info_details?.listing_base_price_accurate ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.listing_base_price_accurate;

            // Discount
            var discount = 0;
            if (booking_info_details?.raw_message?.reservation && booking_info_details?.raw_message?.reservation?.pricing_rule_details) {
                booking_info_details?.raw_message?.reservation?.pricing_rule_details.forEach((items) => {
                    pricing_rules += -items.amount_native;
                    // booking_info_details?.raw_message.reservation.listing_base_price_accurate += -items.amount_native;
                    discount += -items.amount_native;
                });
            }

            // Promotion
            var promotion = 0;
            if (booking_info_details?.raw_message?.reservation && booking_info_details?.raw_message?.reservation?.promotion_details) {
                booking_info_details?.raw_message?.reservation?.promotion_details.forEach((items) => {
                    // booking_info_details?.raw_message.reservation.listing_base_price_accurate += -items.amount_native;
                    promotion += -items.amount_native;
                });
            }

            $('#perNight_title').text(per_night_price);
            $('#discount_title').text(discount);
            $('#cleaningFee_title').text(cleaning_fee);
            // $('#serviceFee_title').text(booking_info_details?.);
            $('#promotion_title').text(promotion);
            $('#otaCommission_title').text(ota_commission);
            $('#totalCharges_title').text(total);

            $('#apartment_title').text(booking_info_details?.listing_name ?? listingName)
            $('#guest_name').text(booking_info_details?.name)
            $('#booking_details_div').html(`
            <span class="mb-3"><strong>Checkin Date: </strong><span>${booking_info_details?.checkin_date ??
             booking_info_details?.payload?.bms?.rooms[0]?.checkin_date  
             }</span></span> <br>



                            <span class="mb-3"><strong>Checkout Date: </strong><span id="apartment_title">${booking_info_details?.checkout_date ??
                            booking_info_details?.payload?.bms?.rooms[0]?.checkout_date  
                            }</span></span><br>


                            <span class="mb-3"><strong>Nights: </strong><span id="apartment_title">${booking_info_details?.nights ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.nights  }</span></span><br>


                            <span class="mb-3"><strong>Expected Amount: </strong><span id="apartment_title">${booking_info_details?.expected_payout_amount_accurate  
                            ?? booking_info_details?.payload?.bms?.rooms[0]?.amount  }</span></span> <br>


                            <span class="mb-3"><strong>No of Adults: </strong><span id="apartment_title">${booking_info_details?.number_of_adults ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.guest_details?.number_of_adults  }</span></span><br>



                            <span class="mb-3"><strong>No of Children: </strong><span id="apartment_title">${booking_info_details?.number_of_children ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.guest_details?.number_of_children }</span></span><br>


                            <span class="mb-3"><strong>No of Guest: </strong><span id="apartment_title">${booking_info_details?.number_of_guests ?? (booking_info_details?.payload?.bms?.raw_message?.reservation?.guest_details?.number_of_adults + booking_info_details?.payload?.bms?.raw_message?.reservation?.guest_details?.number_of_children) }</span></span>

                            `);
        }


        // <span class="mb-3"><strong>No of Infants: </strong><span id="apartment_title">booking_info_details?.number_of_infants ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.guest_details?.number_of_infants </span></span><br>


        // <span class="mb-3"><strong>No of Pets: </strong><span id="apartment_title">booking_info_details?.number_of_pets ?? booking_info_details?.payload?.bms?.raw_message?.reservation?.guest_details?.number_of_pets  </span></span><br>




        // $('#message_div').html('')
        $('#cust_title').html('')
        $('#cust_title').append(title)
        $('#approve_btn').attr('data-id', live_feed_event_id);
        $('#special_offer').attr('data-id', live_feed_event_id);
        $('#inquiry_text').css('display', 'none');
        if (thread_id == null) {
            $('#cust_title').append("")
            $('#message_div').empty();
            $('.all-messages').css('display', 'flex');
            $('.all-messages').css('align-item', 'center');

            $('#message_div').append(`<h3 style='text-align:center;padding-top:80px;'>No Message</h3>`);
            return
        }
        // console.log('TTYpe: '+thread_type);
        if (thread_type == 'inquiry') {
            $('#inquiry_offer_card').css('display', 'flex');
        }
        else {
            $('#inquiry_offer_card').css('display', 'none');
        }
        let allMessages = $('.all-messages');
        allMessages.css('display', 'flex');
        allMessages.css('justify-content', 'center');
        allMessages.css('align-items', 'center');
        $('.nk-msg-item').css('background-color', '');
        $(`.nk-msg-item`).css('border-left', '0px solid transparent');

        $(`#nk-msg-item-${thread_id}`).css('background-color', '#8094ae26');
        $(`#nk-msg-item-${thread_id}`).css('border-left', '5px solid #203247');

        $('#message_div').html("<div class='spinner-border' id='chatLoader' style='width: 3rem; height: 3rem;' role='status'><span class='sr-only'>Loading...</span></div>");


        $('.listing_names').html(listingName);
        $('.current_status').html(status);
        


        $('#listing_name').html(itemName);

        // checkForBookingInquiryDetails
        let checkForBookingInquiryDetailsUrl = window.location.origin + '/checkForBookingInquiryDetails/' +
            live_feed_event_id;
        $.ajax({
            url: checkForBookingInquiryDetailsUrl,
            type: "get",
            success: function (response) {

                // $('#message_div').html("");
                $('#chatLoader').css('display', 'none');
                allMessages.css('display', 'block');
                // response.messages.map((item, index) => {
                if (response.booking_details) {
                    let total_price = Number(response.total_price) / 100

                    $('#inquiry_offer_card').css('display', 'none');
                    $('#inquiry_text').css('display', 'block');
                    $('#inquiry_text').text(`The Special Offer has Been sent of price ${total_price}`);
                }
                // inquiry_offer_card
                // inquiry_text

            },
            error: function (jqXHR, textStatus, errorThrown) {
                allMessages.css('display', 'block');
                // $('#message_div').html("");
                console.log(textStatus, errorThrown);
            }
        });


        
        

        let url = window.location.origin + '/fetchThreadByIDAdmin/' + thread_id;
        $.ajax({
            url: url,
            type: "get",
            data: {
                thread_id: thread_id
            },
            success: function (response) {
                // response.messages.map((item, index) => {
                // console.log(response)
                // const sortedMessages = response.message_content.sort((a, b) => new Date(a.updated_at) - new Date(b.updated_at));

                response.map((item, index) => {


                    let alignStyle = item.sender === "property" ? 'style="align-self: end !important; display:flex; flex-direction: column; align-items:end; width:100%"' : "";
                    let headerStyle = 
    item.sender === "property"
        ? 'style="display: flex; flex-direction: row-reverse; width: 100%; justify-content: space-between;"' 
        : item.sender === "channel"
        ? 'style="display: none;"' 
        : "";


                    let userCardStyle = item.sender === "property"
                        ? 'style="display: flex; flex-direction: row-reverse;"'
                        : "";
                    let chatBgColor = item.sender === "property"
                        ? 'style="background: #f6f6f6; padding:7px 10px; border-radius:10px;"' : 'style="background: #e3effd; padding:7px 10px; border-radius:10px;"';
                    let senderClass = item.sender === "property" ? "sender-property" :  "sender-user";
                    let checkUser = item.sender === "property" ? 'end'  : item.sender === "channel" ? 'center' : 'start';
                    let messages = `
    <div class="nk-reply-item" ${alignStyle}>
            <div class="nk-reply-header" ${headerStyle}>
            <div class="user-card" ${userCardStyle}>
                <div class="user-avatar sm bg-blue">
       <span title="${item.sender === 'guest' ? "Guest" : 'Property'}">
  ${item?.sender === 'guest' ? itemName?.slice(0, 1) : 'M'}
</span>
                </div>
                <div class="user-name px-2" title="${item.sender === 'guest' ? "Guest" : 'Property'}">` + (item.sender === "guest" ? itemName : "Me") + `</div>
            </div>
        </div>
            <div class="nk-reply-body">
            <div class="nk-reply-entry entry ${item?.sender == 'channel' ? 'sender-request' : item?.sender == 'guest' ? 'guest-msg' : 'property-msg' }" >
            ${item?.attachment_url ? `
            <div style="width: 250px; min-height:140px;  border-radius: 10px;">
        <img src="${item?.attachment_url }" alt="Image" style="width: 250px; margin-top: 10px; border-radius: 10px;" />
        </div>
    ` : `
        <p class="${senderClass}" ${chatBgColor}>
            ${item.message_content}
        </p>
        
        ${item.message_type == 'booking_request' && item.is_booking_action_submitted == 0 ? '<p><button class="btn btn-primary btn-sm" style="background-color: #203247 !important;border: none !important;border-color: #203247 !important; font-size: 11px !important;border-radius: 5px;" onclick="areYouSure('+thread_id+','+"'accept_request'"+')">Accept</button> <button class="btn btn-primary btn-sm" style="background-color: #203247 !important;border: none !important;border-color: #203247 !important; font-size: 11px !important;border-radius: 5px;margin-left: 15px;" onclick="areYouSure('+thread_id+','+"'decline_request'"+')">Decline</button><p>':''}
        
    `}

            </div>
            <div class="date-time  ${item?.sender == 'channel' ? 'center-date' : '' }"" style="text-align: ${checkUser}; padding: 5px 3px; color: #8094ae;">` + item?.message_date + `</div>
        </div>
    </div>`;

                    $('#message_div').append(messages);

                });

                let messageReply = `<div class="nk-reply-form" style="border-radius:10px;">
                        <div class="nk-reply-form-header" style="border-top-right-radius:10px; border-top-left-radius:10px;">
                            <ul class="nav nav-tabs-s2 nav-tabs nav-tabs-sm" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#reply-form" aria-selected="true" role="tab">Reply</a>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="reply-form" role="tabpanel">
                            <form>
                                <div class="nk-reply-form-editor">
                                    <div class="nk-reply-form-field">
                                        <textarea class="form-control form-control-simple no-resize" id="message" placeholder="Type Your Message here....." required></textarea>
                                    </div>
                                    <div class="nk-reply-form-tools">

                                        <ul class="nk-reply-form-actions g-1">
                               <li style="display:flex;">
  <label
    for="upload-attachment"
    class="btn btn-icon btn-sm chat-attach-btn"
    data-bs-toggle="tooltip"
    data-bs-placement="top"
    aria-label="Upload Attachment"
    data-bs-original-title="Upload Attachment"
  >
    <em class="icon ni ni-clip-v"></em>Attach File
  </label>
  <input
    type="file"
    id="upload-attachment"
    name="image"
    accept="image/jpeg, image/png, image/gif, image/tiff"
    style="display: none;"
  />
  <span id="file-info" style="display: none; font-size: 14px; color: #555; margin-left: 10px;"></span>

  <div class="" style="position:relative; width: 40px; height: 40px;">
  <img
    id="file-preview"
    src=""
    alt="Preview"
    style="width: 40px; height: 40px;  border: 1px solid #ccc; object-fit: cover; margin-left: 10px; border-radius:10px;display: none; position:absolute; z-index:1;"
  />
  <span class="cut_btn" id="cutBtn" style="position:absolute; display: none; z-index:2; top: 2px;
    right: -9px;
    background-color: #ffffffc4;
    border-radius: 50%;" ><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg></span>
  </div>
</li>

                                            <input type="hidden" name="thread_id" id="thread_id" value="` + thread_id +
                    `">
                                            <li class="">
                                                <button class="btn btn-primary send-btn" id="replySubmit" onclick="submitReply(` +
                    thread_id + `)" type="button">Send <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill ms-1" viewBox="0 0 16 16">
  <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
</svg></button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>`
                scrollToBottom();
                

                // $(document).ready(function () {
                //     var ul = $('#message_div');
                //     ul.scrollTop(ul.prop("scrollHeight"));
                // });
                $('#message_div').append(messageReply)

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);

            }
        });
        
        
        
        $(this).off('click');

    }


   function fetchthreadmsgspecial()
   {
       let thread_id = $('#hdnthreadid').val();
       
       let title = $('#hdntitle').val();
       let live_feed_event_id = $('#hdnlive_feed_event_id').val();
       let thread_type = $('#hdnthread_type').val();
       let booking_json = $('#hdnbooking_json').val(); 
       let itemName = $('#hdnitemName').val();
       let listingName = $('#hdnlistingName').val();
       let status = "";
    
     let decoded_booking_json = decodeURIComponent(booking_json); 
    let parsed_booking_json = JSON.parse(decoded_booking_json); 

    let encoded_booking_json = encodeURIComponent(JSON.stringify(parsed_booking_json)); 

      fetchThreadByID(thread_id, title, live_feed_event_id, thread_type,encodeURIComponent(JSON.stringify(parsed_booking_json)) , itemName, listingName,status);
       
       scrollToBottom();
   }

   //setInterval(fetchthreadmsgspecial, 70000);


    function submitReply(thread_id) {

        const fileInput = document.getElementById("upload-attachment");
        const message = $('#message').val();
        // console.log('hi');
        // if (!message) {
        //     alert('Message cannot be null');
        //     return;
        // }

        // Check if file is selected    
        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            console.log(fileInput?.files[0], "fileInput?.files[0]")
            console.log(file, "file");
            console.log(file?.name, "file name");
            console.log(file?.type, "file type");
            // Convert file to Base64
            const fileToBase64 = (file) => {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result.split(",")[1]); // Base64 without prefix
                    reader.onerror = (error) => reject(error);
                    reader.readAsDataURL(file);
                });
            };

            fileToBase64(file).then((base64String) => {
                // Send Base64 file with message via AJAX
                sendMessageWithAttachment(message, thread_id, base64String, file?.name, file?.type);
            }).catch((error) => {
                console.error("Error converting file to Base64:", error);
            });
        } else {
            // Send message without file
            // alert("some thing went wrong")
            sendMessageWithAttachment(message, thread_id, null);
        }
    }

    function sendMessageWithAttachment(message, thread_id, base64File, fileName, filetype) {
        const filePreview = document.getElementById('file-preview');
        const cutBtn = document.getElementById('cutBtn');
        const fileInput = document.getElementById("upload-attachment");
        const replySubmit = document.getElementById("replySubmit");

        replySubmit.innerHTML = `<div class='spinner-border' style='width: 1.3rem; height: 1.3rem;' role='status'><span class='sr-only'>Loading...</span></div>`;
        replySubmit.disabled = true;
        replySubmit.style.cursor = "not-allowed";

        $.ajax({
            url: "{{ route('sendmessageadminandattachment') }}",
            type: "POST",
            data: {
                thread_id: thread_id,
                ...(base64File && { file: base64File }),
                ...(fileName && { file_name: fileName }),
                ...(filetype && { file_type: filetype }),
                ...(message && { message: message }),
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                $("#message").val('');
                replySubmit.innerHTML = `Send <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill ms-1" viewBox="0 0 16 16">
  <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
</svg>`;
                replySubmit.disabled = false;
                replySubmit.style.cursor = "pointer";

                if (response) {
                    const messages =
                        (message != null && message !== '') && `
                   <div class="nk-reply-item" style="align-self: end !important; display:flex; flex-direction: column; align-items:end; width:100%">
        <div class="nk-reply-header" style="display: flex; flex-direction: row-reverse; width: 100%; justify-content: space-between;">
            <div class="user-card" style="display: flex; flex-direction: row-reverse;">
                <div class="user-avatar sm bg-blue">
       <span title="Property">
  M
</span>

                </div>
                <div class="user-name px-2" title="Property">Me</div>
            </div>
            </div>
            <div class="nk-reply-body">
            <div class="nk-reply-entry entry">
            <p class="sender-property" style="background: #f6f6f6; padding:7px 10px; border-radius:10px;">` + message + `</p>
            </div>
            <div class="date-time" style="text-align: end; padding: 5px 3px; color: #8094ae;">2024-12-24 07:53:51</div>
        </div>
    </div>`;

                    if (message) {
                        const parentDiv = $('#message_div');
                        parentDiv.prepend(messages);
                        $('.nk-reply-form').before(messages);
                    }

                    const fileAttachment =
                        (base64File != null && base64File !== '') && `
                   <div class="nk-reply-item" style="align-self: end !important; display:flex; flex-direction: column; align-items:end; width:100%">
        <div class="nk-reply-header" style="display: flex; flex-direction: row-reverse; width: 100%; justify-content: space-between;">
            <div class="user-card" style="display: flex; flex-direction: row-reverse;">
                <div class="user-avatar sm bg-blue">
       <span title="Property">
  M
</span>

                </div>
                <div class="user-name px-2" title="Property">Me</div>
            </div>
            </div>
            <div class="nk-reply-body">
            <div class="nk-reply-entry entry">
              <img src="data:${filetype};base64,${base64File}" alt="Image" style="max-width: 300px; margin-top: 20px; border-radius: 10px;" />

            </div>
            <div class="date-time" style="text-align: end; padding: 5px 3px; color: #8094ae;">2024-12-24 07:53:51</div>
        </div>
    </div>`;

                    if (base64File) {

                        const forAttachment = $('#message_div');
                        forAttachment.prepend(fileAttachment);
                        $('.nk-reply-form').before(fileAttachment);
                    }
                    scrollToBottom();
                }
                fileInput.value = '';
                cutBtn.style.display = "none";
                filePreview.style.display = "none";
                filePreview.textContent = "";
            },
            error: function (error) {
                alert("Only upload files in JPEG, PNG, GIF, or TIFF formats.");
                console.error("Error during form submission:", error);
                const fileInput = document.getElementById("upload-attachment");
                fileInput.value = '';
                cutBtn.style.display = "none";
                filePreview.style.display = "none";
                filePreview.textContent = "";
                replySubmit.innerHTML = `Send <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill ms-1" viewBox="0 0 16 16">
  <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
</svg>`;
                replySubmit.disabled = false;
                replySubmit.style.cursor = "not-allowed";



            }
        });
    }

    function getNewThreads() {
        const urlParams = new URLSearchParams(window.location.search);
        const thIds = urlParams.get('thread_id') || '';

       
        $.ajax({
            url: "{{ route('fetchThreadsAdmin') }}",
            type: "get",
            data: {
                system: 'Admin',
                 thid: thIds
            },
            success: function (response) {
                new_threads = []
                response.map((item, index) => {
                    if (!old_threads.includes(item.id)) {


                        let date = item.message_date.split("T")[0];

                        let title = "'" + item.name + "'"
                        let live_feed_event_id = "'" + item.live_feed_event_id + "'"
                        old_threads.push(item.id)


                        let logoHtml = '';
                        if (item.connection_type == "airbnb" || item.connection_type == null) {
                            logoHtml = `
            <span>
                <img src="{{ asset('assets/images/logo/airbnb.png') }}"
                     srcset="{{ asset('assets/images/logo/airbnb.png') }}"
                     alt="logo"
                     style="width:20px; max-width:20px; margin-top:7px" />
            </span>`;
                        }

                        if (item.connection_type == "bcom" || item.connection_type == "BCom") {
                            logoHtml = `
            <span>
                <img src="{{ asset('assets/images/logo/bookingcom-1.svg') }}"
                     srcset="{{ asset('assets/images/logo/bookingcom-1.svg') }}"
                     alt="logo"
                     style="width:20px; max-width:20px; margin-top:7px" />
            </span>`;
                        }
                        if (item.connection_type == "vrbo") {
                            logoHtml = `
            <span>
                <img src="{{ asset('assets/images/logo/vrbo.png') }}"
                     srcset="{{ asset('assets/images/logo/vrbo.png') }}"
                     alt="logo"
                     style="width:20px; max-width:20px; margin-top:7px" />
            </span>`;
                        }

                        let newMessage = `
                        <div class="nk-msg-item" id="nk-msg-item-${item?.id}" data-msg-id="` + index + `"  onclick="fetchThreadByID('${item?.id}' , '${item?.name}', '${item?.live_feed_event_id}', '${item?.thread_type}', '${encodeURIComponent(JSON.stringify(item?.booking_info_json || item?.booking_request_json))}', '${item?.name}', '${item?.listing_name}')">
                             <div style="display:flex; flex-direction:column;     align-items: center;">
                            <div class="nk-msg-media user-avatar">
                                <span>` + item?.name.charAt(0) + `</span>
                               
                            </div>
                         
                             </div>
                           <div class="nk-msg-info">
                                <div class="nk-msg-from">
                                   <div class="nk-msg-sender">
                                               <div class="name">${item?.name?.length > 8 ? item?.name?.substring(0, 8) + "..." : item?.name} 
                                               <span class="seen-msg seen-msg${item?.id}" style="color:#e85347; font-size: 9px; margin-left:5px;">${item?.is_read == 0 ? "unread" : ""} </span></div>
                                    </div>

                                    
                                    <div class="nk-msg-meta">
                                        <div class="date" style="width: 127px;">` + date + `</div>
                                        
                                    </div>
                                </div>
                              <div class="nk-msg-context">
                                    <div class="nk-msg-text">
                                    <h6 class="title"></h6>
                                        <p title="${item?.last_message}" data-full-text="${item?.last_message}">
                <span style="font-weight:700">${item?.last_message_sender}</span> : ${item?.last_message?.length > 26 ? item?.last_message?.substring(0, 26) + "..." : item?.last_message}</p>
                                        ${logoHtml} 
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        `;
                        $('#thread-content').prepend(newMessage);
                     }
                    new_threads.push(item.id);
        

                })
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);

            }
        });
    }



    // setInterval(
    //     function () {
    //         getNewThreads()
    //     },
    //     6000
    // );


    async function main() {
        const threadData = await getThread();

        setInterval(async () => {
            await getNewThreads();
        }, 6000);
    }




    $(document).ready(function () {
        $('#thread-content').on('scroll', function () {
            var contentHeight = $(this)[0].scrollHeight;
            var scrollPosition = $(this).scrollTop() + $(this).outerHeight();

            if (scrollPosition >= contentHeight - 1) {
                countOffset += 10;
                getThread(countOffset, searchThreads);
            }
        });


        $(document).ready(function () {
            let debounceTimer;

            $('#search-input').on('input', function () {
                $('#thread-content').empty();
                searchThreads = $(this).val();

                countOffset = 0;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    getThread(countOffset, searchThreads);
                }, 700);
            });
        });


    });

    function scrollToBottom() {
        if ($(".all-messages").length) {
            $(".all-messages").animate(
                { scrollTop: $(".all-messages")[0].scrollHeight+1 },
                100 // Smooth animation
            );
        }
    }
    

    $(window).on("load", function () {
        scrollToBottom();
       
    });

    //     window.onload = function () {
    //     const messagesDiv = document.getElementById('allMessages');
    //     messagesDiv.scrollTop = messagesDiv.scrollHeight;
    //   };






    document.addEventListener("DOMContentLoaded", function () {
        document.body.addEventListener('change', function (event) {
            if (event.target && event.target.id === 'upload-attachment') {
                const file = event.target.files[0];


                const fileInfo = document.getElementById('file-info');
                const filePreview = document.getElementById('file-preview');
                const cutBtn = document.getElementById('cutBtn');


                if (file) {
                    const fileType = file.type;
                    const reader = new FileReader();


                    filePreview.style.display = "none";
                    filePreview.src = "";
                    fileInfo.style.display = "none";
                    fileInfo.textContent = "";
                    cutBtn.style.display = "block";

                    if (fileType.startsWith("image/")) {

                        reader.onload = function (e) {
                            filePreview.src = e.target.result;
                            filePreview.style.display = "block";
                            cutBtn.style.display = "block";

                        };
                        reader.readAsDataURL(file);
                    } else {

                        fileInfo.textContent = file.name;
                        fileInfo.style.display = "block";
                    }
                }
            }
        });
    });

</script>