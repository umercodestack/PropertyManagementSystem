@extends('Admin.layouts.app')
@section('content')
    <style>
        .fc_confirmed_booking {
            background-color: #86c56aff !important;
            border: 2px solid #86c56aff;
        }

        .fc_cancelled_booking {
            background-color: #c73c3c !important;
            border: 2px solid #c73c3c;
        }

        .fc_modified_booking {
            background-color: #dd974cff !important;
            border: 2px solid #dd974cff;
        }

        .fc_available {
            background-color: #4675c7ff !important;
            border: 2px solid #4675c7ff;
        }

        .fc_unavailable {
            background-color: #999999 !important;
            border: 2px solid #999999;
        }

        .fc_purple {
            background-color: #bd65ff !important;
            border: 1px solid #bd65ff;
        }

        .fc-event-title {
            color: white !important;
            font-weight: 700 !important;
            font-size: 13px !important;
        }

        .avail-lock {
            background: #00000069 !important;
            border: none !important;
        }

        /* Accordion Styling */
        .accordion-button {
            background-color: #f1f3f5;
            color: #212529;
            border-radius: 8px !important;
            padding: 0.75rem 1rem;
            box-shadow: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .accordion-button:hover {
            background-color: #e2e6ea;
        }

        /* .accordion-button::after {
                    content: "";
                    font-weight: bold;
                    font-size: 1.2rem;
                    transition: transform 0.3s ease;
                } */

        /* .accordion-button:not(.collapsed)::after {
                    content: "";
                } */

        .accordion-item {
            background-color: transparent;
            border: none;
            margin-bottom: 10px;
        }

        .accordion-body {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 5px;
        }

        input[type="text"],
        input[type="number"] {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 0.5rem;
            font-size: 0.95rem;
        }

        label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-check-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        @media (max-width: 576px) {
            .offcanvas {
                width: 100% !important;
            }
        }
    </style>
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Calendar</h3>
            </div><!-- .nk-block-head-content -->
            @if (isset($_GET['listing_id']))
                <div class="nk-block-head-content">
                    <!-- <a class="btn btn-primary" data-bs-toggle="modal" href="#addEventPopup"><em
                                    class="icon ni ni-plus"></em><span>Bulk Rate Update</span></a> -->
                    @isset($_GET['listing_id'])
                        <!-- <a class="btn btn-primary" href="{{ route('calender.index.sync', $_GET['listing_id']) }}">Calender
                                            Sync</a>
                                        <a class="btn btn-primary" href="{{ route('calender.syncGathern', $_GET['listing_id']) }}">Sync
                                            Gathern</a> -->
                        @php
                            $churned = \App\Models\ChurnedProperty::where('listing_id', $_GET['listing_id'])->first();
                        @endphp
                        <a class="btn btn-primary" href="{{ route('calender.churnedListing', $_GET['listing_id']) }}">Churned |
                            {{ isset($churned) ? 'Yes' : 'No' }}</a>
                    @endisset
                </div><!-- .nk-block-head-content -->
            @endif
        </div><!-- .nk-block-between -->
    </div><!-- .nk-block-head -->


    <form action="{{ route('calender.index') }}" method="GET">
        <div class="row mb-3">
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label for="channel_id">Channels</label>
                    <select id="channel_id" name="channel_id" class="form-control" onchange="getListings(this.value)"
                        required>
                        <option value="" selected disabled>Select Channel</option>
                        @foreach ($channels as $item)
                            <option value="{{ $item->ch_channel_id }}"
                                {{ isset($_GET['channel_id']) && $_GET['channel_id'] === $item->ch_channel_id ? 'selected' : '' }}>
                                {{ $item->user->name }} {{ $item->user->surname }}</option>
                        @endforeach
                        <option value="3962b987-4bea-44e8-9655-479609bb934c"
                            {{ isset($_GET['channel_id']) && $_GET['channel_id'] === '3962b987-4bea-44e8-9655-479609bb934c' ? 'selected' : '' }}>
                            Livenin Me 2</option>
                    </select>
                </div>
            </div> --}}
            <div class="col-md-6">
                <div class="form-group">
                    <label for="listing_id">Listings</label>
                    <select name="listing_id" class="form-control select2" required>
                        <option value="" selected disabled>Select Listing</option>
                        @foreach ($listings as $item)
                            @php
                                $listing_json = json_decode($item->listing_json);
                            @endphp
                            <option value="{{ $item->listing_id }}"
                                {{ isset($_GET['listing_id']) && $_GET['listing_id'] == $item->listing_id ? 'selected' : '' }}>
                                {{ $listing_json->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <div class="col-md-6 mt-2">
                <div class="row">
                    <div class="col-md-2 mt-2">
                        <button class="btn btn-secondary" type="submit">Submit</button>
                    </div>

                    <div class="col-md-2 mt-2">
                        <button class="btn btn-secondary" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#rightSidebar">
                            Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @if (isset($rate_plan))
        <div class="offcanvas offcanvas-end" tabindex="-1" id="rightSidebar" aria-labelledby="rightSidebarLabel"
            data-bs-backdrop="false" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="rightSidebarLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">

                <div class="col-md-12">
                    <form id="invoice-form">
                        <div class="mb-3">
                            <label class="form-check-label" for="daterange">Select Date Range</label>
                            <input type="text" class="form-control" name="daterange" id="gbvdaterange"
                                value="01/01/2018 - 01/15/2018" />
                        </div>

                        <div class="mb-3 d-flex justify-content-evenly mb-1">
                            <button class="btn btn-secondary" type="submit" name="download_invoice">
                                Download Invoice
                            </button>
                        </div>
                    </form>

                    <div class="mb-3 d-flex justify-content-evenly mb-1">
                        <label><b>Base Price:</b> <span id="slide_base_price">0</span></label>
                    </div>

                    <div class="mb-3">

                        <div class="d-flex justify-content-between mb-1 w-100">
                            <div>
                                <span><b>GBV:</b> <span id="slide_gbv">0</span></span>
                            </div>
                            <div>
                                <span><b>ADR:</b> <span id="slide_adr">0</span></span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-1 w-100">
                            <div>
                                <span><b>NBV:</b> <span id="slide_nbv">0</span></span>
                            </div>
                            <div>
                                <span><b>Occupancy %:</b> <span id="slide_occupancy">0</span></span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <span><b>Cancellations:</b> <span id="slide_cancellations">0</span></span>
                            </div>
                            <div>
                                <span><b>Discounts:</b> <span id="slide_discounts">0</span></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="accordion" id="dateRangeAccordion">
                    <!-- Availability Form -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSelectDateRange">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSelectAvailability" aria-expanded="false"
                                aria-controls="collapseSelectAvailability">
                                Availability
                            </button>
                        </h2>
                        <div id="collapseSelectAvailability" class="accordion-collapse collapse"
                            aria-labelledby="headingSelectDateRange" data-bs-parent="#dateRangeAccordion">
                            <div class="accordion-body">
                                <form action="{{ route('slideBarUpdateAvailability') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="listing_id" value="{{ $_GET['listing_id'] }}">
                                    <div class="mb-3">
                                        <label class="form-check-label" for="daterange">Select Date Range</label>
                                        <input type="text" class="form-control" name="daterange" id="daterange"
                                            value="01/01/2018 - 01/15/2018" required />
                                    </div>
                                    <!-- <div class="mb-3">
                                            <label for="min_stay" class="form-label">Min Stay</label>
                                            <input type="number" name="min_stay" class="form-control" id="min_stay" value="1" required/>
                                        </div>

                                        <div class="mb-3">
                                            <label for="max_stay" class="form-label">Max Stay</label>
                                            <input type="number" name="max_stay" class="form-control" id="max_stay" required/>
                                        </div> -->

                                    <div class="mb-3">
                                        <label for="availability" class="form-label">Availability</label>
                                        <input type="number" name="availability" class="form-control" id="availability"
                                            min="0" max="1" step="1" required />
                                    </div>

                                    <div class="mb-3">
                                        <div id="reason_div">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <button class="btn btn-secondary" type="submit" data-bs-target="#rightSidebar">
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Pricing Form -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSelectDateRange">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSelectDateRange" aria-expanded="false"
                                aria-controls="collapseSelectDateRange">
                                Date Range Pricing
                            </button>
                        </h2>
                        <div id="collapseSelectDateRange" class="accordion-collapse collapse"
                            aria-labelledby="headingSelectDateRange" data-bs-parent="#dateRangeAccordion">
                            <div class="accordion-body">
                                <form action="{{ route('slideBarUpdatePrice') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="listing_id" value="{{ $_GET['listing_id'] }}">
                                    <div class="mb-3">
                                        <label class="form-check-label" for="daterange">Select Date Range</label>
                                        <input type="text" class="form-control" name="daterange" id="daterange"
                                            value="01/01/2018 - 01/15/2018" required />
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label">Airbnb Per Night Charges</label>
                                        <input type="number" name="price" class="form-control" id="price"
                                            oninput="validateInput(this)" required />
                                    </div>

                                    <div class="mb-3">
                                        <label for="bcom_rm" class="form-label">Booking.com (Rate Multiplier)</label>
                                        <input type="text" class="form-control" id="bcom_rm" name="bcom_rm"
                                            oninput="validateInput(this)">
                                    </div>

                                    <div class="mb-3">
                                        <label for="almosafer_rm" class="form-label">Almosafer (Rate Multiplier)</label>
                                        <input type="text" class="form-control" id="almosafer_rm" name="almosafer_rm"
                                            oninput="validateInput(this)">
                                    </div>

                                    <button class="btn btn-secondary" type="submit" data-bs-target="#rightSidebar">
                                        Update
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Pricing Form -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCustomDateRange">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseCustomDateRange" aria-expanded="false"
                                aria-controls="collapseCustomDateRange">
                                Custom Pricing
                            </button>
                        </h2>
                        <div id="collapseCustomDateRange" class="accordion-collapse collapse"
                            aria-labelledby="headingCustomDateRange" data-bs-parent="#dateRangeAccordion">
                            <div class="accordion-body">
                                <form action="{{ route('slideBarUpdateCustomPrice') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="listing_id" value="{{ $_GET['listing_id'] }}">
                                    <div class="mb-3">
                                        <label for="weekday_pricing" class="form-label">Airbnb Weekday Pricing</label>
                                        <input type="number" name="weekday_pricing" class="form-control"
                                            id="weekday_pricing" oninput="validateInput(this)" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="weekend_pricing" class="form-label">Airbnb Weekend Pricing</label>
                                        <input type="number" name="weekend_pricing" class="form-control"
                                            id="weekend_pricing" oninput="validateInput(this)" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="bcom_rm" class="form-label">Booking.com (Rate Multiplier)</label>
                                        <input type="number" name="bcom_rm" class="form-control" id="bcom_rm"
                                            oninput="validateInput(this)">
                                    </div>
                                    <div class="mb-3">
                                        <label for="almosafer_rm" class="form-label">Almosafer (Rate Multiplier)</label>
                                        <input type="text" class="form-control" id="almosafer_rm" name="almosafer_rm"
                                            oninput="validateInput(this)">
                                    </div>
                                    <button class="btn btn-secondary" type="submit" data-bs-target="#rightSidebar">
                                        Update
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
    @if (isset($calender) && $calender)
        <div class="nk-block mt-4">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div id="calendar" class="nk-calendar"></div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($_GET['listing_id']))
        <div class="modal fade" id="addEventPopup">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Rate Update</h5>
                        <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <em class="icon ni ni-cross"></em>
                        </a>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('updateListingCalenderAdmin') }}" id="addEventForm"
                            class="form-validate is-alter" method="POST">
                            @csrf
                            <div class="row gx-4 gy-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label" for="event-title">Rate</label>
                                        <div class="form-control-wrap">
                                            @php
                                                // Get today's date
$today = date('m/d/Y');

// Get the date after 500 days
$dateAfter500Days = date('m/d/Y', strtotime('+500 days'));

                                            @endphp
                                            <input type="number" name="price" class="form-control" id="event-title"
                                                required>
                                            {{-- <input type="hidden" name="channel_id" value="{{ $_GET['channel_id'] }}"
                                                class="form-control" id="event-title" required> --}}
                                            <input type="hidden" name="listing_id" value="{{ $_GET['listing_id'] }}"
                                                class="form-control" id="event-title" required>
                                            <div class="form-group form-check p-0">
                                                <input type="hidden" class="form-control" name="dateRangeBulk"
                                                    value="{{ $today }} - {{ $dateAfter500Days }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <ul class="d-flex justify-content-between gx-4 mt-1">
                                        <li>
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </li>
                                        <li>
                                            <button id="resetEvent" data-bs-dismiss="modal"
                                                class="btn btn-danger btn-dim">Discard</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{--    <div class="modal fade" id="editEventPopup"> --}}
    {{--        <div class="modal-dialog modal-md" role="document"> --}}
    {{--            <div class="modal-content"> --}}
    {{--                <div class="modal-header"> --}}
    {{--                    <h5 class="modal-title">Edit Event</h5> --}}
    {{--                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close"> --}}
    {{--                        <em class="icon ni ni-cross"></em> --}}
    {{--                    </a> --}}
    {{--                </div> --}}
    {{--                <div class="modal-body"> --}}
    {{--                    <form action="#" id="editEventForm" class="form-validate is-alter" method="POST"> --}}
    {{--                        <div class="row"> --}}
    {{--                            <div class="col-6"> --}}
    {{--                                <div class="form-group"> --}}
    {{--                                    <div class="col-12"> --}}
    {{--                                        <div class="form-group"> --}}
    {{--                                            <label class="form-label" for="edit-event-title">Availability</label> --}}
    {{--                                            <div class="form-control-wrap"> --}}
    {{--                                                <select id="edit-event-title" name="availability" class="form-control"> --}}
    {{--                                                    <option value="available">available</option> --}}
    {{--                                                    <option value="unavailable">unavailable</option> --}}
    {{--                                                </select> --}}
    {{--                                            </div> --}}
    {{--                                        </div> --}}
    {{--                                    </div> --}}
    {{--                                </div> --}}
    {{--                            </div> --}}
    {{--                            <div class="col-6"> --}}
    {{--                                <div class="form-group"> --}}
    {{--                                    <label class="form-label" for="edit-event-end-date">Date</label> --}}
    {{--                                    <input type="text" id="edit-event-end-date" name="date" class="form-control date-picker" data-date-format="yyyy-mm-dd" readonly> --}}
    {{--                                    <input type="hidden" name="listing_id" id="listing_id" value="{{isset($_GET['listing_id']) && $_GET['listing_id'] ? $_GET['listing_id'] : ''}}" readonly> --}}
    {{--                                </div> --}}
    {{--                            </div> --}}
    {{--                            <div class="col-12 text-end mt-3"> --}}
    {{--                                <button type="submit" class="btn btn-primary">Update</button> --}}
    {{--                            </div> --}}
    {{--                        </div> --}}
    {{--                    </form> --}}
    {{--                </div> --}}
    {{--            </div> --}}
    {{--        </div> --}}
    {{--    </div> --}}
    <div class="modal fade" id="previewEventPopup">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div id="preview-event-header" class="modal-header">
                    <h5 id="preview-event-title" class="modal-title">Placeholder Title</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="row gy-3 py-1">
                        <div class="col-sm-6">
                            <h6 class="overline-title">Start Time</h6>
                            <p id="preview-event-start"></p>
                        </div>
                        <div class="col-sm-6" id="preview-event-end-check">
                            <h6 class="overline-title">End Time</h6>
                            <p id="preview-event-end"></p>
                        </div>
                        <div class="col-sm-10" id="preview-event-description-check">
                            <h6 class="overline-title">Price</h6>
                            <p id="preview-event-description"></p>
                        </div>
                    </div>
                    <ul class="d-flex justify-content-between gx-4 mt-3">
                        <li>
                            <button data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editEventPopup"
                                class="btn btn-primary">Edit Event</button>
                        </li>
                        <li>
                            <button data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deleteEventPopup"
                                class="btn btn-danger btn-dim">Delete</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteEventPopup">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body modal-body-lg text-center">
                    <div class="nk-modal py-4">
                        <em class="nk-modal-icon icon icon-circle icon-circle-xxl ni ni-cross bg-danger"></em>
                        <h4 class="nk-modal-title">Are You Sure ?</h4>
                        <div class="nk-modal-text mt-n2">
                            <p class="text-soft">This event data will be removed permanently.</p>
                        </div>
                        <ul class="d-flex justify-content-center gx-4 mt-4">
                            <li>
                                <button data-bs-dismiss="modal" id="deleteEvent" class="btn btn-success">Yes, Delete
                                    it</button>
                            </li>
                            <li>
                                <button data-bs-dismiss="modal" data-toggle="modal" data-target="#editEventPopup"
                                    class="btn btn-danger btn-dim">Cancel</button>
                            </li>
                        </ul>
                    </div>
                </div><!-- .modal-body -->
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/bundle.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/js/fullcalendar.js_ver=3.2.3') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    {{--    <script src="{{asset('assets/js/calendar.js')}}"></script> --}}
    <script>
        document.getElementById('invoice-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const daterange = this.daterange.value;
            const listingId = `{{ $_GET['listing_id'] ?? '' }}`; // or pass via JS variable

            try {
                // 1. Fetch booking IDs
                const response = await fetch(`/invoices/list?daterange=${daterange}&listing_id=${listingId}`);
                const ids = await response.json();

                if (!ids.length) {
                    alert('No bookings found.');
                    return;
                }

                // 2. Create hidden links and open tabs *synchronously*
                for (let i = 0; i < ids.length; i++) {
                    const url = `/invoice/download/${ids[i]}`;
                    const newWindow = window.open(url, '_blank');

                    // If popup is blocked
                    if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                        alert('Popup blocked. Please allow popups for this site.');
                        break;
                    }
                }

            } catch (error) {
                console.error('Error loading invoices:', error);
            }
        });



        const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get the CSRF token from the meta tag
        var start = moment().subtract(29, 'days');
        var end = moment();


        document.getElementById("availability").addEventListener("input", function(e) {
            const value = e.target.value;
            if (value !== "0" && value !== "1") {
                e.target.value = "";
            }
        });


        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
                'YYYY-MM-DD'));
        });

        function validateInput(input) {
            let value = input.value;

            // Allow only digits and decimal point
            if (!/^\d*\.?\d*$/.test(value)) {
                input.value = value.slice(0, -1);
                return;
            }

            // Prevent leading zero without decimal (e.g., "0", "00", etc.)
            if (value === "0" || /^0\d+/.test(value)) {
                input.value = "";
                return;
            }

            // Optional: limit to 1 decimal point
            const parts = value.split(".");
            if (parts.length > 2) {
                input.value = parts[0] + "." + parts[1];
            }
        }

        function disableRatePlan(val, rate_plan_input) {
            val.value = val.checked ? 1 : 0;
            // alert(val.value);
            if (val.value == 1) {
                $('#' + rate_plan_input).prop('disabled', false);
                // $(rate_plan_input).disabled()
            } else {
                $('#' + rate_plan_input).prop('disabled', true);
            }
        }

        function disableStayInput(val, stay_type) {
            val.value = val.checked ? 1 : 0;
            // alert(val.value);
            if (val.value == 1) {
                $('#' + stay_type).prop('disabled', false);
                // $(rate_plan_input).disabled()
            } else {
                $('#' + stay_type).prop('disabled', true);
            }
        }

        $('#availability').on('keyup', function() {

            const value = event.target.value;
            if (/[a-zA-Z]/.test(value) || /[^0-9]/.test(value)) {
                $('#reason_div').html('');
                $('#availability').val("");
                return false;
            }

            let selectedValue = $(this).val();

            if (selectedValue.length > 1) {
                // $('#availability').val(selectedValue[0]);
                $('#reason_div').html('');
                $('#availability').val("");
                return false;
            }

            // console.log('selected val: ',selectedValue.length);


            if (selectedValue == 0 && selectedValue != null && selectedValue != '') {
                $('#reason_div').empty().append(
                    `<label class="form-check-label" for="block_reason">Block Reason</label>
                                <textarea class="form-control" name="block_reason" id="block_reason" rows="5" required></textarea>`
                )
            } else {
                $('#reason_div').html('');
            }
        });


        // @if (isset($_GET['channel_id']) && $_GET['channel_id'])
        //     getListings('{{ $_GET['channel_id'] }}')
        // @endif
        // function getListings(val) {
        //     $('#listing_id').html('');
        //     let listing_id = {{ isset($_GET['listing_id']) && $_GET['listing_id'] ? $_GET['listing_id'] : 0 }}
        //     console.log(listing_id);
        //     $.ajax({
        //         url: "{{ route('fetchListingsByChannelIdAdmin') }}",
        //         type: "post",
        //         data: {
        //             channel_id: val,
        //             is_sync: 'sync_all',
        //             _token: csrfToken // Include the CSRF token in the data
        //         },
        //         success: function(response) {
        //             // console.log(response)
        //             if (response) {
        //                 // console.log(response)
        //                 response.map((item, index) => {
        //                     // console.log(item)
        //                     const option = $('<option></option>')
        //                         .attr('value', item.id) // set the value attribute to the item ID
        //                         .text(item.title); // set the text content to the item name
        //                     if (item.id == listing_id) {
        //                         option.attr('selected', 'selected');
        //                     }
        //                     $('#listing_id').append(option);
        //                 })
        //             }
        //         },
        //         error: function(jqXHR, textStatus, errorThrown) {
        //             console.log(textStatus, errorThrown);
        //         }
        //     });

        //     function fetchUpdateForm(val) {
        //         {{-- $.ajax({ --}}
        //         {{--    url: "{{route('calender.fetch')}}", --}}
        //         {{--    type: "get", --}}
        //         {{--    data: { --}}
        //         {{--        channel_id: val, --}}
        //         {{--        is_sync: 'sync_all', --}}
        //         {{--        _token: csrfToken --}}
        //         {{--    } , --}}
        //         {{--    success: function (response) { --}}
        //         {{--        console.log(response) --}}
        //         {{--        if(response) { --}}
        //         {{--            // console.log(response) --}}
        //         {{--            response.map((item) => { --}}
        //         {{--                console.log(item) --}}
        //         {{--            }) --}}
        //         {{--        } --}}
        //         {{--    }, --}}
        //         {{--    error: function(jqXHR, textStatus, errorThrown) { --}}
        //         {{--        console.log(textStatus, errorThrown); --}}
        //         {{--    } --}}
        //         {{-- }); --}}
        //     }
        // }
        {{-- function calenderDateUpdate(val) { --}}
        {{--    $.ajax({ --}}
        {{--        url: "{{route('calender.index')}}", --}}
        {{--        type: "get", --}}
        {{--        data: { --}}
        {{--            channel_id: val, --}}
        {{--            is_sync: 'sync_all', --}}
        {{--            _token: csrfToken --}}
        {{--        } , --}}
        {{--        success: function (response) { --}}
        {{--            console.log(response) --}}
        {{--            if(response) { --}}
        {{--                // console.log(response) --}}
        {{--                response.map((item) => { --}}
        {{--                    console.log(item) --}}
        {{--                }) --}}
        {{--            } --}}
        {{--        }, --}}
        {{--        error: function(jqXHR, textStatus, errorThrown) { --}}
        {{--            console.log(textStatus, errorThrown); --}}
        {{--        } --}}
        {{--    }); --}}
        {{-- } --}}
        $('#editEventForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Get the data-id attribute from the form
            {{-- let channel_id = '{{$_GET['channel_id']}}' --}}
            {{-- let listing_id = '{{$_GET['listing_id']}}' --}}
            let availability = $('#edit-event-title').val();
            let eventDate = $('#edit-event-end-date').val();

            // console.log(availability)
            // console.log(eventDate)
            // Get the form data to send in the AJAX request
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route('updateListingCalender') }}', // Replace with your desired endpoint
                type: 'POST',
                data: {
                    channel_id: channel_id,
                    listing_id: listing_id,
                    availability: availability,
                    eventDate: eventDate,
                },
                success: function(response) {
                    // Handle success response
                    console.log('Successfully submitted:', response);
                    // Add any additional code for success handling
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Handle error response
                    console.error('Error submitting:', textStatus, errorThrown);
                    // Add any additional code for error handling
                }
            });
        });

        !(function(NioApp, $) {
            "use strict";

            // Variable
            var $win = $(window),
                $body = $('body'),
                breaks = NioApp.Break;

            NioApp.Calendar = function() {

                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();



                var tomorrow = new Date(today);
                tomorrow.setDate(today.getDate() + 1);
                var t_dd = String(tomorrow.getDate()).padStart(2, '0');
                var t_mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
                var t_yyyy = tomorrow.getFullYear();

                var yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                var y_dd = String(yesterday.getDate()).padStart(2, '0');
                var y_mm = String(yesterday.getMonth() + 1).padStart(2, '0');
                var y_yyyy = yesterday.getFullYear();

                var YM = yyyy + '-' + mm;
                var YESTERDAY = y_yyyy + '-' + y_mm + '-' + y_dd;
                var TODAY = yyyy + '-' + mm + '-' + dd;
                var TOMORROW = t_yyyy + '-' + t_mm + '-' + t_dd;

                console.log(YM)
                console.log(YESTERDAY)
                console.log(TODAY)
                console.log(TOMORROW)

                var month = ["January", "February", "March", "April", "May", "June", "July", "August", "September",
                    "October", "November", "December"
                ];

                var calendarEl = document.getElementById('calendar');
                var eventsEl = document.getElementById('externalEvents');

                var removeEvent = document.getElementById('removeEvent');

                var addEventBtn = $('#addEvent');
                var addEventForm = $('#addEventForm');
                var addEventPopup = $('#addEventPopup');

                var updateEventBtn = $('#updateEvent');
                var editEventForm = $('#editEventForm');
                var editEventPopup = $('#editEventPopup');

                let previewEventPopup = $('#previewEventPopup');

                var deleteEventBtn = $('#deleteEvent');

                var mobileView = (NioApp.Win.width < NioApp.Break.md) ? true : false;

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    timeZone: 'UTC',
                    initialView: mobileView ? 'listWeek' : 'dayGridMonth',
                    themeSystem: 'bootstrap5',
                    headerToolbar: {
                        left: 'title prev,next',
                        center: null,
                        right: 'today dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },

                    height: 800,
                    contentHeight: 780,
                    aspectRatio: 3,

                    editable: true,
                    droppable: true,
                    views: {
                        dayGridMonth: {
                            dayMaxEventRows: 5,
                        }
                    },
                    direction: NioApp.State.isRTL ? "rtl" : "ltr",

                    nowIndicator: true,
                    now: TODAY + 'T09:25:00',
                    eventMouseEnter: function(info) {
                        let elm = info.el,
                            title = info.event._def.title,
                            content = info.event._def.extendedProps.description;
                        if (content) {
                            const fcPopover = new bootstrap.Popover(elm, {
                                template: '<div class="popover event-popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
                                title: title,
                                content: content ? content : '',
                                placement: 'top',
                            })
                            fcPopover.show();
                        }
                    },
                    eventMouseLeave: function() {
                        removePopover();
                    },
                    eventDragStart: function() {
                        removePopover();
                    },
                    eventClick: function(info) {
                        // console.log(info.event._def.publicId)

                        // console.log('start vtype')
                        // console.log(info.event._def.extendedProps)
                        // console.log('end vtype')

                        if (info.event._def.extendedProps.bktype === 'livedIn_booking') {
                            let base_url = window.location.origin;
                            let url = base_url + '/booking-management/' + info.event._def.publicId +
                                '/edit';
                            window.open(url, '_blank');
                        }

                        if (info.event._def.extendedProps.bktype === 'ota_booking') {
                            let base_url = window.location.origin;
                            let url = base_url + '/edit/ota/booking/' + info.event._def.publicId;
                            window.open(url, '_blank');
                        }

                        // console.log('Value: ',info.event._def.extendedProps.description);

                        var title = info.event._def.title;
                        var description = info.event._def.extendedProps.description;
                        var start = info.event._instance.range.start;
                        var startDate = start.getFullYear() + '-' + String(start.getMonth() + 1)
                            .padStart(2, '0') + '-' + String(start.getDate()).padStart(2, '0');
                        var startTime = start.toUTCString().split(' ');
                        startTime = startTime[startTime.length - 2];
                        startTime = (startTime == '00:00:00') ? '' : startTime;
                        var end = info.event._instance.range.end;
                        var endDate = start.getFullYear() + '-' + String(start.getMonth() + 1).padStart(
                            2, '0') + '-' + String(start.getDate()).padStart(2, '0');;
                        // var endDate = end.getFullYear() + '-' + String(end.getMonth() + 1).padStart(2, '0') + '-' + String(end.getDate()).padStart(2, '0');
                        var endTime = end.toUTCString().split(' ');
                        endTime = endTime[endTime.length - 2];
                        endTime = (endTime == '00:00:00') ? '' : endTime;
                        var className = info.event._def.ui.classNames[0].slice(3);
                        var eventId = info.event._def.publicId;

                        //Set data in eidt form
                        $('#edit-event-title').val(title);
                        $('#edit-event-start-date').val(startDate).datepicker('update');
                        $('#edit-event-end-date').val(endDate).datepicker('update');
                        $('#edit-event-start-time').val(startTime);
                        $('#edit-event-end-time').val(endTime);
                        $('#edit-event-description').val(description);
                        $('#edit-event-theme').val(className);
                        $('#edit-event-theme').trigger('change.select2');
                        editEventForm.attr('data-id', eventId);

                        // Set data in preview
                        var previewStart = String(start.getDate()).padStart(2, '0') + ' ' + month[start
                            .getMonth()] + ' ' + start.getFullYear() + (startTime ? ' - ' + to12(
                            startTime) : '');
                        var previewEnd = String(end.getDate()).padStart(2, '0') + ' ' + month[end
                            .getMonth()] + ' ' + end.getFullYear() + (endTime ? ' - ' + to12(
                            endTime) : '');
                        $('#preview-event-title').text(title);
                        $('#preview-event-header').addClass('fc-' + className);
                        $('#preview-event-start').text(previewStart);
                        $('#preview-event-end').text(previewEnd);
                        $('#preview-event-description').text(description);
                        !description ? $('#preview-event-description-check').css('display', 'none') :
                            null;

                        removePopover();

                        let fcMorePopover = document.querySelectorAll('.fc-more-popover');
                        fcMorePopover && fcMorePopover.forEach(elm => {
                            elm.remove();
                        })
                        jQuery('#editEventPopup').modal('show');
                    },
                    // showNonCurrentDates: false,
                    eventDidMount: function(info) {
                        const otaImg = info.event.extendedProps.otaImg;

                        if (otaImg) {
                            const img = document.createElement('img');
                            img.src = otaImg;
                            img.style.width = '20px';
                            img.style.marginRight = '5px';
                            img.style.borderRadius = '4px';

                            const titleEl = info.el.querySelector('.fc-event-title');
                            if (titleEl) {
                                titleEl.prepend(img);
                            }
                        }
                    },

                    // eventWillMount: function(event, el) {
                    //     console.log('event...', event)
                    //     if (event.el.className.includes('event-type-calendar')) {
                    //         const updatedTitle = event.event.title.split('--')
                    //         // event.el.innerText = event.event.title
                    //         event.el.innerHTML = '<div class="fc-event-title d-flex justify-content-between"><span><em class="icon ni ni-sign-dollar"></em>'+updatedTitle[0]+'</span><span><em class="icon ni ni-check-round-fill"></em>'+updatedTitle[1]+'</span></div>'
                    //     }
                    // },
                    events: [
                        @isset($calender)
                            @foreach ($calender as $key => $item)
                                @php
                                    $avail_lock = '';
                                    $item['availability'] == 0 ? ($avail_lock = 'fc_unavailable') : ($avail_lock = 'fc_available');
                                @endphp {
                                    id: '{{ $key }}',
                                    title: '{{ $item['rate'] }} -- ({{ $item['availability'] }})',
                                    start: '{{ $key }}',
                                    end: '{{ $key }}',
                                    className: "{{ $avail_lock }}",
                                    // description: 'Avail - {{ $item['availability'] }}  -- max_stay - {{ $item['max_stay'] }} -- min_stay - {{ $item['min_stay_through'] }}',
                                    description: `Availability - {{ $item['availability'] }}  {{ isset($item['updated_by']) && $item['updated_by'] != null ? ' -- UB ' . $item['updated_by'] : '' }} {{ !empty($item['reason']) ? ' --- Block Reason: ' . $item['reason'] : '' }}`,
                                },
                            @endforeach
                        @endisset
                        @isset($bookings)

                            @php

                                $img_arr = [
                                    'airbnb' => 'assets/images/logo/airbnb.svg',
                                    'bookingcom' => 'assets/images/logo/bookingcom-1.svg',
                                    'vrbo' => 'assets/images/logo/vrbo.png',
                                    'host_booking' => 'assets/images/logo/host_booking.png',
                                    'gathern' => 'assets/images/logo/gathern.png',
                                    'livedin_booking' => 'assets/images/logo/livedin_booking.svg',
                                    'almosafer' => 'assets/images/logo/almosafer.png',
                                ];

                            @endphp

                            @foreach ($bookings as $key => $item)

                                @php
                                    $ota_img = !empty($img_arr[$item['ota_name']]) ? $img_arr[$item['ota_name']] : '';

                                    if ($item['ota_name'] == 'livedin_booking') {
                                        if ($item['booking_sources'] == 'gathern') {
                                            $ota_img = 'assets/images/logo/gathern.png';
                                        } elseif ($item['booking_sources'] == 'host_booking' || $item['booking_sources'] == 'host') {
                                            $ota_img = 'assets/images/logo/host_booking.png';
                                        } else {
                                            $ota_img = 'assets/images/logo/other_sources.png';
                                        }
                                    }

                                    $class_name = 'fc_confirmed_booking';
                                    if (!empty($item['status'])) {
                                        if ($item['status'] == 'cancelled') {
                                            $class_name = 'fc_cancelled_booking';
                                        }

                                        if ($item['status'] == 'modified') {
                                            $class_name = 'fc_modified_booking';
                                        }
                                    }

                                @endphp {
                                    id: '{{ $item['id'] }}',
                                    title: '{{ $item['name'] }}',
                                    start: '{{ $item['booking_date_start'] }}',
                                    className: '{{ $class_name }}',
                                    end: '{{ $item['booking_date_end'] }}',
                                    {{--                                className: "{{$item['availability'] == 'available' ? 'fc-event-primary' : 'fc-event-danger'}}", --}}
                                    description: '{{ 'Created: ' . $item['created_at'] }}' +
                                        ' {{ isset($item['status']) && strtolower($item['status']) == 'cancelled' ? ' Cancelled - Reason: ' . $item['reason'] : '' }}',
                                    extendedProps: {
                                        otaImg: '{{ asset($ota_img) }}',
                                        bktype: '{{ $item['type'] }}'
                                    },
                                },
                            @endforeach
                        @endisset
                    ],
                });


                // Date Range Selection
                var selectedDateRanges = [];
                calendar.setOption('selectable', true);

                calendar.setOption('select', function(info) {
                    var startDate = info.startStr;
                    var endDate = new Date(info.end);
                    endDate.setDate(endDate.getDate() - 1);

                    var overlaps = selectedDateRanges.some(function(range) {
                        return !(new Date(range.end) < new Date(startDate) || new Date(range
                            .start) > endDate);
                    });

                    if (!overlaps) {
                        var dateObj = new Date(endDate);

                        var endDate = dateObj.getFullYear() + '-' +
                            String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
                            String(dateObj.getDate()).padStart(2, '0');

                        if (startDate && endDate && startDate != endDate) {
                            startDate = moment(startDate).format('MM/DD/YYYY');
                            endDate = moment(endDate).format('MM/DD/YYYY');
                            $('input[name="daterange"]').daterangepicker({
                                timePicker: true,
                                startDate,
                                endDate
                            });
                            $('html, body').animate({
                                scrollTop: 0
                            }, 'slow');
                            $('.redirect-booking').remove();

                            var url =
                                `{{ route('booking-management.create') }}?startDate=${startDate}&endDate=${endDate}`;

                            var buttonHtml = `
                                <div class="col-md-2 redirect-booking">
                                    <div class="col-md-12 mt-4">
                                            <a href="${url}" class="btn btn-secondary">Go To Booking</a>
                                        </div>
                                </div>
                            `;

                            $('#filter-container').append(buttonHtml);
                        }
                    } else {
                        console.log("Selected range overlaps with existing data.");
                    }

                });
                calendar.render();
                //Add event
                addEventBtn.on("click", function(e) {
                    e.preventDefault();
                    var eventTitle = $('#event-title').val();
                    var eventStartDate = $('#event-start-date').val();
                    var eventEndDate = $('#event-end-date').val();
                    var eventStartTime = $('#event-start-time').val();
                    var eventEndTime = $('#event-end-time').val();
                    var eventDescription = $('#event-description').val();
                    var eventTheme = $('#event-theme').val();
                    var eventStartTimeCheck = eventStartTime ? 'T' + eventStartTime + 'Z' : '';
                    var eventEndTimeCheck = eventEndTime ? 'T' + eventEndTime + 'Z' : '';

                    calendar.addEvent({
                        id: 'added-event-id-' + Math.floor(Math.random() * 9999999),
                        title: eventTitle,
                        start: eventStartDate + eventStartTimeCheck,
                        end: eventEndDate + eventEndTimeCheck,
                        className: "fc-" + eventTheme,
                        description: eventDescription,
                    });
                    addEventPopup.modal('hide');
                });



                updateEventBtn.on("click", function(e) {

                    e.preventDefault();
                    var eventTitle = $('#edit-event-title').val();
                    var eventStartDate = $('#edit-event-start-date').val();
                    var eventEndDate = $('#edit-event-end-date').val();
                    var eventStartTime = $('#edit-event-start-time').val();
                    var eventEndTime = $('#edit-event-end-time').val();
                    var eventDescription = $('#edit-event-description').val();
                    var eventTheme = $('#edit-event-theme').val();
                    var eventStartTimeCheck = eventStartTime ? 'T' + eventStartTime + 'Z' : '';
                    var eventEndTimeCheck = eventEndTime ? 'T' + eventEndTime + 'Z' : '';

                    var selectEvent = calendar.getEventById(editEventForm[0].dataset.id);
                    selectEvent.remove();
                    calendar.addEvent({
                        id: editEventForm[0].dataset.id,
                        title: eventTitle,
                        start: eventStartDate + eventStartTimeCheck,
                        end: eventEndDate + eventEndTimeCheck,
                        className: "fc-" + eventTheme,
                        description: eventDescription,
                    });
                    editEventPopup.modal('hide');
                });

                deleteEventBtn.on("click", function(e) {
                    e.preventDefault();
                    var selectEvent = calendar.getEventById(editEventForm[0].dataset.id);
                    selectEvent.remove();
                });

                function removePopover() {
                    let fcPopover = document.querySelectorAll('.event-popover');
                    fcPopover.forEach(elm => {
                        elm.remove();
                    })
                }

                function to12(time) {
                    time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

                    if (time.length > 1) {
                        time = time.slice(1);
                        time.pop();
                        time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
                        time[0] = +time[0] % 12 || 12;
                    }
                    time = time.join('');
                    return time;
                }

                function customCalSelect(cat) {
                    if (!cat.id) {
                        return cat.text;
                    }
                    var $cat = $('<span class="fc-' + cat.element.value + '"> <span class="dot"></span>' + cat
                        .text + '</span>');
                    return $cat;
                };

                function removePopover() {
                    let fcPopover = document.querySelectorAll('.event-popover');
                    fcPopover.forEach(elm => {
                        elm.remove();
                    })
                }

                NioApp.Select2('.select-calendar-theme', {
                    templateResult: customCalSelect
                });

                addEventPopup.on('hidden.bs.modal', function(e) {
                    setTimeout(function() {
                        $('#addEventForm input,#addEventForm textarea').val('');
                        $('#event-theme').val('event-primary');
                        $('#event-theme').trigger('change.select2');
                    }, 1000);
                });

                previewEventPopup.on('hidden.bs.modal', function(e) {
                    $('#preview-event-header').removeClass().addClass('modal-header');
                });

            };

            NioApp.coms.docReady.push(NioApp.Calendar);

        })(NioApp, jQuery);


        $('#gbvdaterange').on('apply.daterangepicker', function(ev, picker) {
            fetchGBVDetails(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
        });


        function fetchGBVDetails(startDate, endDate) {
            $.ajax({
                url: '{{ route('get-gbv-details') }}',
                method: 'GET',
                data: {
                    listing_id: "{{ $_GET['listing_id'] ?? '' }}",
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {

                    console.log("Data: ", response);
                    console.log("startDate", startDate, "endDate", endDate);

                    $('#slide_base_price').text(response.base_price + ' SAR');
                    $('#slide_gbv').text(response.gbv + ' SAR');
                    $('#slide_adr').text(response.adr + ' SAR');
                    $('#slide_nbv').text(response.nbv + ' SAR');
                    $('#slide_occupancy').text(response.occupancy);
                    $('#slide_cancellations').text(response.cancellations);
                    $('#slide_discounts').text(response.discounts + ' SAR');
                }
            });
        }


        function updateDatePicker() {
            let start = moment().startOf('month');
            let end = moment().endOf('month');

            $('#gbvdaterange').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
            fetchGBVDetails(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        }

        $(document).ready(function() {
            updateDatePicker();
        });
    </script>
@endsection
