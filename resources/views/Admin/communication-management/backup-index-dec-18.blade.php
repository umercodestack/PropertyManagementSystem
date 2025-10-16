@extends('Admin.layouts.app')
@section('content')
<style>
    

    .nk-reply-form {
        position: sticky !important;
        bottom: -15px !important;
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
<div class=" chat_wrapper">
<div class="nk-content-inner chat_wrapper">
    <div class="nk-content-body">
        <div class="nk-msg">
            <div class="nk-msg-aside">
                <div class="nk-msg-nav">
                    <!-- <h5 class="pt-1 ps-1 text-left">Threads</h5> -->
                    <div class="mt-30"
                        style="position: relative; display: flex; justify-content: center; align-items: center; border-radius: 25px;">
                        <input id="search-input" type="text" placeholder="Search..." name="search" class="thread-search"
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
                    <a href="#" class="nk-msg-profile-toggle profile-toggle"><em class="icon ni ni-arrow-left"></em></a>
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
                                    <div class="simplebar-content all-messages" style="padding: 0px;" id="allMessages">
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
                                                    <div class="card-inner">
                                                        <div class="user-card user-card-s2 mb-2">
                                                            <div class="user-avatar md bg-primary">
                                                                <span>AB</span>
                                                            </div>
                                                            <div class="user-info">
                                                                <h5 id="cust_title"></h5>
                                                                <span class="sub-text">Customer</span>
                                                                <p class="listing_names"></p>
                                                            </div>

                                                            {{--                                                            <div class="user-card-menu dropdown"> --}}
                                                            {{--                                                                <a href="#" class="btn btn-icon btn-sm btn-trigger dropdown-toggle" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a> --}}
                                                            {{--                                                                <div class="dropdown-menu dropdown-menu-end"> --}}
                                                            {{--                                                                    <ul class="link-list-opt no-bdr"> --}}
                                                            {{--                                                                        <li><a href="#"><em class="icon ni ni-eye"></em><span>View Profile</span></a></li> --}}
                                                            {{--                                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Ban From System</span></a></li> --}}
                                                            {{--                                                                        <li><a href="#"><em class="icon ni ni-repeat"></em><span>View Orders</span></a></li> --}}
                                                            {{--                                                                    </ul> --}}
                                                            {{--                                                                </div> --}}
                                                            {{--                                                            </div> --}}
                                                        </div>
                                                        {{--                                                        <div class="row text-center g-1"> --}}
                                                        {{--                                                            <div class="col-4"> --}}
                                                        {{--                                                                <div class="profile-stats"> --}}
                                                        {{--                                                                    <span class="amount">23</span> --}}
                                                        {{--                                                                    <span class="sub-text">Total Order</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                            <div class="col-4"> --}}
                                                        {{--                                                                <div class="profile-stats"> --}}
                                                        {{--                                                                    <span class="amount">20</span> --}}
                                                        {{--                                                                    <span class="sub-text">Complete</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                            <div class="col-4"> --}}
                                                        {{--                                                                <div class="profile-stats"> --}}
                                                        {{--                                                                    <span class="amount">3</span> --}}
                                                        {{--                                                                    <span class="sub-text">Progress</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                        </div> --}}
                                                    </div><!-- .card-inner -->

                                                    <div class="card-inner">
                                                        <div class="property-details mb-3">
                                                            <h4>Guest Details</h4>
                                                            <span class="mb-3"><strong>Apartment: </strong><span id="apartment_title"></span></span>
                                                            <br>
                                                            <br>
                                                            <span class="mt-5"><strong>Guest Name: </strong><span id="guest_name"></span></span>
                                                            <br>
                                                            <div id="booking_details_div" class="mt-3">
                                                                <h4>Booking Details</h4>
                                                            </div>
                                                        </div>

                                                        <div class="row" id="inquiry_offer_card">
                                                            <div class="col-md-6">
                                                                <button class="btn btn-primary btn-sm"
                                                                style="border-color: #203247 !important; font-size: 11px !important;"
                                                                 id="approve_btn"
                                                                    data-id = ""
                                                                    onclick="approveOrRejectInquiry(this, 'approve')">Pre-Approve</button>
                                                            </div>
                                                            {{--                                                            <div class="col-md-6"> --}}
                                                            {{--                                                                <button class="btn btn-primary btn-sm">Special Offer</button> --}}
                                                            {{--                                                            </div> --}}
                                                            <div class="col-md-6">
                                                                <button class="btn btn-primary btn-sm" 
                                                                style="border-color: #203247 !important; font-size: 11px !important; "id="special_offer"
                                                                    data-id = ""
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
                                                        {{--                                                        <div class="aside-wg"> --}}
                                                        {{--                                                            <h6 class="overline-title-alt mb-2">User Information</h6> --}}
                                                        {{--                                                            <ul class="user-contacts"> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <em class="icon ni ni-mail"></em><span>info@softnio.com</span> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <em class="icon ni ni-call"></em><span>+938392939</span> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <em class="icon ni ni-map-pin"></em><span>1134 Ridder Park Road <br>San Fransisco, CA 94851</span> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                            </ul> --}}
                                                        {{--                                                        </div> --}}
                                                        {{--                                                        <div class="aside-wg"> --}}
                                                        {{--                                                            <h6 class="overline-title-alt mb-2">Additional</h6> --}}
                                                        {{--                                                            <div class="row gx-1 gy-3"> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Ref ID: </span> --}}
                                                        {{--                                                                    <span>TID-049583</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Requested:</span> --}}
                                                        {{--                                                                    <span>Abu Bin Ishtiak</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Status:</span> --}}
                                                        {{--                                                                    <span class="lead-text text-success">Open</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Last Reply:</span> --}}
                                                        {{--                                                                    <span>Abu Bin Ishtiak</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                        </div> --}}
                                                        {{--                                                        <div class="aside-wg"> --}}
                                                        {{--                                                            <h6 class="overline-title-alt mb-2">Assigned Account</h6> --}}
                                                        {{--                                                            <ul class="align-center g-2"> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <div class="user-avatar bg-purple"> --}}
                                                        {{--                                                                        <span>IH</span> --}}
                                                        {{--                                                                    </div> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <div class="user-avatar bg-pink"> --}}
                                                        {{--                                                                        <span>ST</span> --}}
                                                        {{--                                                                    </div> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <div class="user-avatar bg-gray"> --}}
                                                        {{--                                                                        <span>SI</span> --}}
                                                        {{--                                                                    </div> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                            </ul> --}}
                                                        {{--                                                        </div> --}}
                                                    </div><!-- .card-inner -->
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
        // console.log(html)
        $('#special_offer_div').append(html);
    }

    function approveOrRejectInquiry(button, status) {
        console.log(button,status,"approveOrRejectInquiry")

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
                console.log(status,"status")
                if (response) {
                    if(status == 'approve') {
                        $('#inquiry_offer_card').css('display', 'none');
                        $('#inquiry_text').css('display', 'block');
                        $('#inquiry_text').text(`Book Now offer has been sent to user`);
                    }else {
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
                search: searchThreads

            },
            success: function (response) {
                let thread_type = "'" + response[0].thread_type + "'";
                if (response.length === 0 && $('.nk-msg-item').length === 0) {
                    $('#thread-content').append(`<h4 style="text-align:center; margin:auto 0;"> No Thread Found</h4>`);
                    fetchThreadByID(null);

                }
                thread_loader.css('display', 'none');
                response[0]?.name ? $('#cust_title').append(response[0].name) : $('#cust_title').append("")

                response?.map((item, index) => {
                    old_threads.push(item.id);
                    // old_threads.push(Number(item.id));
                    let date = item.message_date.split("T")[0]; // "2024-04-21"

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

                    let bookingInfoJson = item?.booking_info_json;



                    let newMessage = `
    <div class="nk-msg-item" id="nk-msg-item-${item.id}" data-msg-id="` + index + `" 
    onclick="fetchThreadByID('${item?.id}' , '${item?.name}', '${item?.live_feed_event_id}', '${response[0].thread_type}', '${encodeURIComponent(JSON.stringify(response[0].booking_info_json))}', '${item?.name}', '${item?.listing_name}')">
                        <div style="display:flex; flex-direction:column;     align-items: center;">
                            <div class="nk-msg-media user-avatar" >
                                <span>` + item.name.charAt(0) + `</span>
                               
                            </div>
                           
                             </div>
                            <div class="nk-msg-info">
                                <div class="nk-msg-from">
                                    <div class="nk-msg-sender">
                                               <div class="name">` + item.name + ` 
                                               <span class="seen-msg" style="color:#e85347; font-size: 10px; margin-left:5px;">${item?.is_read == 0 ? "unread": ""} </span></div>
                                    </div>
                                    <div class="nk-msg-meta">
                                        <div class="date">` + date + `</div>
                                        
                                    </div>
                                </div>
                                <div class="nk-msg-context">
                                    <div class="nk-msg-text">
            <p title="${item?.last_message}" data-full-text="${item?.last_message}">
                <span style="font-weight:800">${item?.last_message_sender}</span> : ${item?.last_message?.length > 32 ? item?.last_message?.substring(0, 32) + "..." : item?.last_message}</p>
                
  ${logoHtml} 
  
        </div>
                                </div>
                            </div>
                        </div>
                        `;
                    $('#thread-content').append(newMessage);
                    if ($(`#nk-msg-item-${item.id}`) && index === 0 && countOffset == 0) {
                       // Safely encode all parameters

fetchThreadByID(item?.id, item?.name, item?.live_feed_event_id,response[0].thread_type,  encodeURIComponent(JSON.stringify(response[0].booking_info_json)),item?.name, item?.listing_name);


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
        getThread();
    }, 2000);



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

    function fetchThreadByID(thread_id = null, title = null, live_feed_event_id = null, thread_type = null, booking_json = null, itemName = null, listingName ="-") {
        console.log(thread_id, title,live_feed_event_id,thread_type,booking_json,itemName,listingName, "listingName")
        $('#apartment_title').html('')
        $('#guest_name').html('')
        $('#booking_details_div').html('')
        let updateUnReadThread = window.location.origin + '/api/is_read/' +
        thread_id;
        $.ajax({
            url: updateUnReadThread,

                type: "PUT",
                success: function (response) {
console.log(response,"response");
$('.seen-msg').css('display', 'none');
                 },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR,textStatus, errorThrown,"jqXHR");
$('.seen-msg').css('display', 'inline-block');

            }
        });

        if(booking_json !== null || booking_json !== '' || booking_json !== 'null') {
                            console.log('booking_json', booking_json)
                            let booking_info = decodeURIComponent(booking_json)
                            let booking_info_detail = JSON.parse(booking_info)
                            let booking_info_details = JSON.parse(booking_info_detail)
                            console.log('tt',booking_info_details);
                            // let booking_info_details = JSON.parse(booking_json);
                            $('#apartment_title').text(booking_info_details?.listing_name)
                            $('#guest_name').text(booking_info_details?.name)
                            $('#booking_details_div').html(`
                            <span class="mb-3"><strong>Checkin Date: </strong><span>`+booking_info_details?.checkin_date+`</span></span> <br>
                            <span class="mb-3"><strong>Checkout Date: </strong><span id="apartment_title">`+booking_info_details?.checkout_date+`</span></span><br>
                            <span class="mb-3"><strong>Nights: </strong><span id="apartment_title">`+booking_info_details?.nights+`</span></span><br>
                            <span class="mb-3"><strong>Expected Amount: </strong><span id="apartment_title">`+booking_info_details?.expected_payout_amount_accurate+` `+booking_info_details?.currency+`</span></span>
                            <span class="mb-3"><strong>No of Adults: </strong><span id="apartment_title">`+booking_info_details?.number_of_adults+`</span></span><br>
                            <span class="mb-3"><strong>No of Children: </strong><span id="apartment_title">`+booking_info_details?.number_of_children+`</span></span><br>
                            <span class="mb-3"><strong>No of Guest: </strong><span id="apartment_title">`+booking_info_details?.number_of_guests+`</span></span><br>
                            <span class="mb-3"><strong>No of Infants: </strong><span id="apartment_title">`+booking_info_details?.number_of_infants+`</span></span><br>
                            <span class="mb-3"><strong>No of Pets: </strong><span id="apartment_title">`+booking_info_details?.number_of_pets+`</span></span><br>
                            `);
                     }


        $('#message_div').html('')
        $('#cust_title').html('')
        $('#cust_title').append(title)
        $('#approve_btn').attr('data-id', live_feed_event_id);
        $('#special_offer').attr('data-id', live_feed_event_id);
        $('#inquiry_text').css('display', 'none');
console.log(thread_type,"thread_type")
        if (thread_id == null) {
            $('#cust_title').append("")
            $('#message_div').empty();
            $('.all-messages').css('display', 'flex');
            $('.all-messages').css('align-item', 'center');

            $('#message_div').append(`<h3 style='text-align:center;padding-top:80px;'>No Message</h3>`);
            return 
        }
        if(thread_type =='inquiry') {
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

        $('#message_div').html("<div class='spinner-border' style='width: 3rem; height: 3rem;' role='status'><span class='sr-only'>Loading...</span></div>");

      
        $('.listing_names').html(listingName)

       
        $('#listing_name').html(itemName);

        // checkForBookingInquiryDetails
        let checkForBookingInquiryDetailsUrl = window.location.origin + '/checkForBookingInquiryDetails/' +
            live_feed_event_id;
        $.ajax({
            url: checkForBookingInquiryDetailsUrl,
            type: "get",
            success: function (response) {
                
                allMessages.css('display', 'block');
                $('#message_div').html("");
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
                $('#message_div').html("");
                console.log(textStatus, errorThrown);
            }
        });

        // console.log(window.location.origin)
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
    const [date, time] = item?.message_date.split(" ");
    let [hours, minutes] = time.split(":");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = (hours % 12 || 12);
    const formattedDateTime = `${date} ${hours}:${minutes} ${ampm}`;

                    let alignStyle = item.sender === "property" ? 'style="align-self: end !important; display:flex; flex-direction: column; align-items:end; width:100%"' : "";
                    let headerStyle = item.sender === "property" ? 'style="display: flex; flex-direction: row-reverse; width: 100%; justify-content: space-between;"' : "";
                    let userCardStyle = item.sender === "property"
                        ? 'style="display: flex; flex-direction: row-reverse;"'
                        : "";
                    let chatBgColor = item.sender === "property"
                        ? 'style="background: #f6f6f6; padding:7px 10px; border-radius:10px;"' : 'style="background: #e3effd; padding:7px 10px; border-radius:10px;"';
                    let senderClass = item.sender === "property" ? "sender-property" : "sender-user";
                    let checkUser = item.sender === "property" ? true : false;
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
            <div class="nk-reply-entry entry">
            <p class="${senderClass}" ${chatBgColor}>` + item.message_content + `</p>
            </div>
            <div class="date-time" style="text-align: ${checkUser ? 'end' : 'start'}; padding: 5px 3px; color: #8094ae;">` + formattedDateTime + `</div>
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
                                            <li>
                                                 <label for="upload-attachment" class="btn btn-icon btn-sm chat-attach-btn" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Upload Attachment" data-bs-original-title="Upload Attachment">
                                                   <em class="icon ni ni-clip-v"></em>Attach File
                                                 </label>
                                                 <input type="file" id="upload-attachment" name="image" accept="image/jpeg" style="display: none;">
                                            </li>
                                            <input type="hidden" name="thread_id" id="thread_id" value="` + thread_id +
                    `">
                                            <li class="">
                                                <button class="btn btn-primary send-btn" id="resplySubmit" onclick="submitReply(` +
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

    function submitReply(thread_id) {

        let message = $('#message').val();
        // let thread_id = $('#thread_id').val();
        if (message === '' || message === null) {
            alert('Message can not be null')
        } else {
            $.ajax({
                url: "{{ route('sendMessageAdmin') }}",
                type: "POST",
                data: {
                    message: message,
                    thread_id: thread_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $("#message").val('');
                    if (response) {
                        let messages = `
                    <div class="nk-reply-item">
                        <div class="nk-reply-header">
                            <div class="user-card">
                                <div class="user-avatar sm bg-blue">
                                    <span>AB</span>
                                </div>
                                <div class="user-name">property</div>
                            </div>
                            <div class="date-time">05 Jul, 2024</div>
                        </div>
                        <div class="nk-reply-body">
                            <div class="nk-reply-entry entry">
                                <p>` + message + `</p>
                            </div>

                        </div>
                    </div>`;
                        let parentDiv = $('#message_div');
                        parentDiv.prepend(messages);
                        $('.nk-reply-form').before(messages);
                    }
                },
                error: function (error) {
                    console.error("Error during form submission:", error);
                    // Handle errors as needed
                }
            });
        }
    }

    function getNewThreads() {
        $.ajax({
            url: "{{ route('fetchThreadsAdmin') }}",
            type: "get",
            data: {
                system: 'Admin'
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
                        <div class="nk-msg-item" id="nk-msg-item-${item.id}" data-msg-id="` + index + `"  onclick="fetchThreadByID('${item?.id}' , '${item?.name}', '${item?.live_feed_event_id}', '${response[0].thread_type}', '${encodeURIComponent(JSON.stringify(response[0].booking_info_json))}', '${item?.name}', '${item?.listing_name}')">
                             <div style="display:flex; flex-direction:column;     align-items: center;">
                            <div class="nk-msg-media user-avatar">
                                <span>` + item.name.charAt(0) + `</span>
                               
                            </div>
                         
                             </div>
                            <div class="nk-msg-info">
                                <div class="nk-msg-from">
                                    <div class="nk-msg-sender">
                                        <div class="name">` + item.name + `</div>
                                    </div>
                                    <div class="nk-msg-meta">
                                        <div class="date">` + date + `</div>
                                        
                                    </div>
                                </div>
                                <div class="nk-msg-context">
                                    <div class="nk-msg-text">
                                    <h6 class="title"></h6>
                                         <p title="${item.last_message}" data-full-text="${item.last_message}">${item.last_message.length > 32 ? item.last_message.substring(0, 32) + "..." : item.last_message}</p>
                                        ${logoHtml} 
                                    </div>
                                    <div class="nk-msg-lables">
                                        <div class="asterisk">
                                            <a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a>
                                        </div>
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
                // console.log(textStatus, errorThrown);
            }
        });
    }



    setInterval(
        function () {
            getNewThreads()
        },
        3000
    );

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
            { scrollTop: $(".all-messages")[0].scrollHeight },
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

</script>