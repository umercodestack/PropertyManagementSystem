@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Booking</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->


        <div class="nk-block">
            <form action="{{ route('booking-management.store') }}" method="POST" enctype="multipart/form-data"
                id = 'booking_form'>
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <h6>Booking Details</h6>
                                <hr>
                                <div class="row gy-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="reservation_code">Reservation Code</label>
                                            <input type="text" class="form-control" name="reservation_code"
                                                id="reservation_code" value="" placeholder="Reservation code" />
                                            @error('reservation_code')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="apartment_id">Apartment</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="apartment_id" id="apartment_id"
                                                    data-placeholder="Select Apartment"
                                                    onchange="fetchListingInfo(this.value);listingNameAppend(this.options[this.selectedIndex].getAttribute('data-title'))">
                                                    <option value="" selected disabled>Select Apartment</option>

                                                    @foreach ($listings as $items)
                                                        @php
                                                            $listing_details = json_decode($items->listing_json);
                                                        @endphp
                                                        <option value="{{ $items->id }}"
                                                            data-title="{{ $listing_details->title ?? '' }}">
                                                            {{ $listing_details->title ?? '' }} --
                                                            {{ $listing_details->id ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('apartment_id')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="booking_date_start">Booking Date</label>
                                            <input type="text" class="form-control" name="daterange" id="daterange"
                                                value="01/01/2018 - 01/15/2018"
                                                onchange="changeTotalPriceByDate(this.value);addValueToTotal(this.value);"
                                                onkeydown="return false;" autocomplete="off" required />
                                            @error('booking_date_start')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror

                                            <p id="error-msg" class="text-danger mt-2"></p>
                                            <p id="error-msgdt" class="text-danger mt-2"></p>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="adult">Adult</label>
                                            <input type="number" class="form-control" id="adult" name="adult"
                                                onkeyup="changeAdultChildRoolText(this.value, 'adult')" placeholder="Adult">
                                            @error('adult')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="children">Children</label>
                                            <input type="number" class="form-control" id="children" name="children"
                                                onkeyup="changeAdultChildRoolText(this.value, 'children')"
                                                placeholder="Children">
                                            @error('children')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="rooms">Rooms</label>
                                            <input type="number" class="form-control" id="rooms" name="rooms"
                                                onkeyup="changeAdultChildRoolText(this.value, 'rooms')" placeholder="Rooms">
                                            @error('rooms')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone</label>
                                            <input type="number" class="form-control" id="phone" name="phone"
                                                onkeyup="fetchGuestData(this.value)" placeholder="Phone">
                                            @error('phone')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="name">First Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="First Name">
                                            @error('name')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="surname">Last Name</label>
                                            <input type="text" class="form-control" id="surname" name="surname"
                                                placeholder="Last Name">
                                            @error('surname')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Email Address">
                                            @error('email')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="country">Country</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="country" id="country"
                                                    data-placeholder="Select Country" onchange="fetchCities(this.value)">
                                                    <option value="" selected disabled>Select Country</option>
                                                    @foreach ($countries as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('country')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="city">City</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="city" id="city"
                                                    data-placeholder="Select City">
                                                    <option value="" selected disabled>Select City</option>
                                                </select>
                                                @error('city')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="rating">Rating</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="rating" id="rating"
                                                    data-placeholder="Select Rating"
                                                    onchange="disableDropdown(this.value, 'rating');appendValuesInDropdown(this.value, 'rating')">
                                                    <option value="" selected disabled>Select Rating</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="irrelevant">Irrelevant</option>
                                                </select>
                                                @error('rating')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="purpose_of_call">Purpose of call</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="purpose_of_call" id="purpose_of_call"
                                                    data-placeholder="Select Purpose of call"
                                                    onchange="disableDropdown(this.value, 'purpose_of_call')">
                                                    <option value="" selected disabled>Select Purpose Of Call
                                                    </option>
                                                </select>
                                                @error('purpose_of_call')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="reason">Select Reason For Not Booking</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="reason" id="reason"
                                                    data-placeholder="Select Reason For Not Booking">
                                                    <option value="" selected disabled>Select Reason For Not Booking
                                                    </option>
                                                    <option value="competition">Competition</option>
                                                    <option value="price_too_high">Price Too High</option>
                                                    <option value="price_too_low">Price Too Low</option>
                                                    <option value="location">Location</option>
                                                    <option value="room_unavailable">Room Unavailable</option>
                                                    <option value="did_not_like_product">Did Not Like Product</option>
                                                    <option value="follow_up">Follow-up</option>
                                                    <option value="not_responding">Not Responding</option>
                                                </select>
                                                @error('reason')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="form-label" for="booking_notes">Booking Notes</label>
                                            <input type="text" class="form-control" id="booking_notes"
                                                name="booking_notes" placeholder="Booking Notes">
                                            @error('booking_notes')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="cnic_passport">CNIC / Passport</label>
                                            <input type="text" class="form-control" id="cnic_passport"
                                                name="cnic_passport" placeholder="CNIC / Passport">
                                            @error('cnic_passport')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <input type="hidden" name="guest_id" id="guest_id">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="payment_method">Payment Method</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="payment_method" id="payment_method"
                                                    data-placeholder="Payment Method">
                                                    <option value="" selected disabled>Select Payment Method</option>
                                                    <option value="ibft">IBFT</option>
                                                    <option value="bank">Bank</option>
                                                    <option value="cod">Paid to Host</option>
                                                    <option value="paid_to_vrbo">Paid To Vrbo</option>
                                                    <option value="paid_to_darent">Paid To Darent</option>
                                                    <option value="paid_to_goldenhost">Paid To Goldenhost</option>
                                                    <option value="livedin">Livedin</option>
                                                    <option value="airbnb">PAID - Airbnb</option>
                                                    <option value="booking">PAID - Booking.com</option>
                                                    <option value="gathern">PAID - Gathern</option>
                                                    <option value="online">Online</option>
                                                </select>
                                                @error('city')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="booking_type">Booking Type</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="booking_type" id="booking_type"
                                                    data-placeholder="Booking Type" required>
                                                    <option value="" selected disabled>Booking Type</option>
                                                    <option value="ota">OTA</option>
                                                    <option value="ota_livedin">OTA-Livedin (Direct Booking)</option>
                                                </select>
                                                @error('booking_sources')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="booking_sources">Booking Source</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="booking_sources" id="booking_sources"
                                                    data-placeholder="Booking Source" required>
                                                    <option value="" selected disabled>Booking Source</option>
                                                </select>
                                                @error('booking_sources')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="include_cleaning">Guest Extended</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="include_cleaning" id="include_cleaning"
                                                    data-placeholder="Include Cleaning">
                                                    <option value="0">Yes</option>
                                                    <option value="1" selected>No</option>

                                                </select>
                                                @error('include_cleaning')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="image">Iqama ID</label>
                                            <div class="form-control-wrap">
                                                <input type="file" class="form-control" id="image" name="image[]"
                                                    multiple>
                                                @error('image')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="payment_references">Payment References</label>
                                            <div class="form-control-wrap">
                                                <input type="file" class="form-control" id="payment_references"
                                                    name="payment_references[]" multiple>
                                                @error('payment_references')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group text-end">
                                            <button type="submit" class="btn btn-primary"
                                                id = 'submitButton'>Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <h6>Order Details</h6>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <p><Strong>Apartment</Strong>: <span id="listingDis">--</span></p>
                                        <p><Strong>Check In</Strong>: <span id="checkInDis">0</span></p>
                                        <p><Strong>Check Out</Strong>: <span id="checkOutDis">0</span></p>
                                        <p><Strong>Adults</Strong>: <span id="adultDis">0</span></p>
                                        <p><Strong>Children</Strong>: <span id="childrenDis">0</span></p>
                                        <p><Strong>Nights</Strong>: <span id="NightDis">0</span></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Per Night
                                                Charges</Strong>: <input type="number" step="0.01"
                                                class="form-control w-50" name="per_night_price" id="pnChargeDis"
                                                onkeyup="addValueToTotal(this.value)"></p>
                                    </div>
                                    <input type="hidden" class="form-control w-50" name="day_difference"
                                        id="day_difference" />
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between">
                                            <Strong>Discount</Strong>: <input type="number" step="0.01"
                                                class="form-control w-50" name="custom_discount" id="pnDiscountDis"
                                                onkeyup="addValueToTotal(this.value)">
                                        </p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Cleaning
                                                Fee</Strong>: <input type="number" step="0.01"
                                                class="form-control w-50" name="cleaning_fee" id="pnCleaningDis"
                                                onkeyup="addValueToTotal(this.value)"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Service
                                                Fee</Strong>: <input type="number" step="0.01"
                                                class="form-control w-50" name="service_fee" id="pnServiceDis"
                                                onkeyup="addValueToTotal(this.value)"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Ota Commission
                                                Charges</Strong>: <input type="number" step="0.01"
                                                class="form-control w-50" name="ota_commission" required></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Total
                                                Charges</Strong>: <input readonly type="number" step="0.01"
                                                class="form-control w-50" name="total_price" id="pnTotalDis">
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js?v=1.5"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js?v=1.5"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js?v=1.5"></script>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css?v=1.5" />

    <script>

        document.addEventListener("DOMContentLoaded", function () {
        const bookingType = document.getElementById('booking_type');
        const bookingSources = document.getElementById('booking_sources');
        const otaCommission = document.querySelector('input[name="ota_commission"]');
        const totalAmount = document.getElementById('pnTotalDis');
        const per_day_charge = document.getElementById('pnChargeDis');

        const otaValues = [
            'airbnb',
            'almosafer',
            'booking_com',
            'darent',
            'goldenhost',
            'vrbo',
            'host_booking',
            'expedia',
            'gathern',
            'host',
            'gozayaan',
            'ltr_host',
            'other'
        ];

        const livedinOptions = [
            { value: 'booking_engine', text: 'Booking Engine' },
            { value: 'walk-in', text: 'Walk-in' },
            { value: 'call', text: 'Call' },
            { value: 'fnf', text: 'FNF' },
            { value: 'pr', text: 'PR' },
            { value: 'website', text: 'Website' },
            { value: 'corporate', text: 'Corporate' },
            { value: 'instagram', text: 'Instagram' },
            { value: 'facebook', text: 'Facebook' },
            { value: 'whatsapp', text: 'Whatsapp' },
            { value: 'ltr_direct', text: 'LTR Direct' },
            // { value: 'darent', text: 'Darent' },
            // { value: 'goldenhost', text: 'Goldenhost' },
            // { value: 'vrbo', text: 'Vrbo' },
            // { value: 'host_booking', text: 'Host Booking' },
            // { value: 'expedia', text: 'Expedia' },
            // { value: 'gathern', text: 'Gathern' },
            // { value: 'host', text: 'Host' },
            // { value: 'gozayaan', text: 'Gozayaan' },
            // { value: 'ltr_host', text: 'LTR Host' },
            // { value: 'others', text: 'Other' },
        ];

        const otaOptions = [
            { value: 'airbnb', text: 'AirBNB' },
            { value: 'almosafer', text: 'Almosafer' },
            { value: 'booking_com', text: 'Booking.com' },
            { value: 'darent', text: 'Darent' },
            { value: 'goldenhost', text: 'Goldenhost' },
            { value: 'vrbo', text: 'Vrbo' },
            { value: 'host_booking', text: 'Host Booking' },
            { value: 'expedia', text: 'Expedia' },
            { value: 'gathern', text: 'Gathern' },
            { value: 'host', text: 'Host' },
            { value: 'gozayaan', text: 'Gozayaan' },
            { value: 'ltr_host', text: 'LTR-Host' },
            { value: 'other', text: 'Other' },
        ];

        let userHasInteracted = false;

        bookingType.addEventListener('change', function () {
            userHasInteracted = true;
            populateBookingSources(this.value);
            resetCommissionField();
        });

        bookingSources.addEventListener('change', function () {
            userHasInteracted = true;
            handleCommissionCalculation();
        });

        per_day_charge.addEventListener('input', function () {
            if (!otaCommission.readOnly || !userHasInteracted) return;
            handleCommissionCalculation();
        });

        function populateBookingSources(type) {
            bookingSources.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.disabled = true;
            placeholder.selected = true;
            placeholder.text = 'Booking Source';
            bookingSources.appendChild(placeholder);

            const optionsToAdd = type === 'ota' ? otaOptions : type === 'ota_livedin' ? livedinOptions : [];

            optionsToAdd.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.text = opt.text;
                bookingSources.appendChild(option);
            });
        }

        function handleCommissionCalculation() {
            const selectedSource = bookingSources.value;

            if (otaValues.includes(selectedSource)) {
                // OTA selected → manual entry
                otaCommission.readOnly = false;
                otaCommission.value = '';
            } else if (selectedSource !== '') {
                // OTA Livedin selected → auto calculate 7.5% of total_amount
                const amount = parseFloat(per_day_charge.value || 0);
                const commission = (amount * 0.075).toFixed(2);
                otaCommission.value = commission;
                otaCommission.readOnly = true;
            } else {
                resetCommissionField();
            }
        }

        function resetCommissionField() {
            otaCommission.value = '';
            otaCommission.readOnly = false;
        }
    });

        document.getElementById('booking_form').addEventListener('submit', function(event) {
            var button = document.getElementById('submitButton');
            button.disabled = true;
            button.innerHTML = 'Submitting...';
        });

        var start = moment().subtract(2, 'days');
        var end = moment();
        var start = moment().subtract(2, 'days');
        var end = moment();

        // $('input[name="daterange"]').daterangepicker({
        //     opens: 'left',
        //     startDate: start,
        //     endDate: end,
        //     // minDate: moment(), // Disable dates before today
        // }, function(start, end, label) {
        //     console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
        //         'YYYY-MM-DD'));
        // });

        const apartmentDropdown = $('#apartment_id');
        const datepicker = $('input[name="daterange"]');

        datepicker.prop('disabled', !apartmentDropdown.val());

        $('#error-msgdt').text("Please select an apartment first.");

        $('form').on('keydown', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13 || e.keyCode === 108) {
                e.preventDefault();
                return false;
            }
        });

        let currentDate = moment().subtract(26, 'hours').startOf('day'); //moment().startOf('day');
        let endDate = currentDate.clone().add(1, 'day');

        $('input[name="daterange"]').daterangepicker({
            minDate: moment().subtract(26, 'hours').startOf('day'),
            startDate: currentDate,
            endDate: endDate,
            locale: {
                format: 'MM/DD/YYYY'
            },
            // isInvalidDate: function(date) {
            //     return blockedDates.includes(date.format("YYYY-MM-DD"));
            // }
        });

        // let blockedDates = ["26/02/2029", "28/02/2029"].map(date => moment(date, "DD/MM/YYYY").format("YYYY-MM-DD"));



        // Set Date From URL
        const urlParams = new URLSearchParams(window.location.search);
        const startDateParam = urlParams.get('startDate');
        const endDateParam = urlParams.get('endDate');

        // Initialize the date range picker
        // if (startDateParam && endDateParam) {
        //     $('#daterange').daterangepicker({
        //         timePicker: true,
        //         locale: {
        //             format: 'MM/DD/YYYY'
        //         },
        //         startDate: startDateParam ? moment(startDateParam, 'MM/DD/YYYY') : moment(),
        //         endDate: endDateParam ? moment(endDateParam, 'MM/DD/YYYY') : moment().add(7, 'days')
        //     });
        // }

        function fetchCities(val) {
            $('#city').html('');
            $('#city').append($('<option>', {
                value: '',
                text: 'Select City',
                disabled: true,
                selected: true
            }));

            $.ajax({
                url: "{{ route('cities') }}",
                type: "get",
                data: {
                    country_id: val
                },
                success: function(response) {
                    if (response) {
                        response.map((item, index) => {
                            $('#city').append($('<option>', {
                                value: item.id,
                                text: item.name
                            }));
                        })
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
        $('#purpose_of_call').prop("disabled", true);
        $('#reason').prop("disabled", true);

        function disableDropdown(val, prefix) {
            if (prefix === 'rating') {
                if (val === '2' || val === 'irrelevant') {
                    $('#purpose_of_call').prop("disabled", false);
                } else {
                    $('#purpose_of_call').prop("disabled", true);
                }
                if (val === '1') {
                    $('#reason').prop("disabled", false);
                } else {
                    $('#reason').prop("disabled", true);
                }
            } else if (prefix === 'purpose_of_call') {

                if (val === 'enquiries' || val === 'future_enquiries' || val === 'corporate_rates') {
                    $('#reason').prop("disabled", false);
                } else {
                    $('#reason').prop("disabled", true);
                }
            }
        }

        function appendValuesInDropdown(val, prefix) {
            if (prefix === 'rating') {
                $('#purpose_of_call').html('')
                $('#purpose_of_call').append($('<option>', {
                    value: '',
                    text: 'Select Purpose of call',
                    disabled: true,
                    selected: true
                }));

                if (val === '2') {

                    let values = {
                        'enquiries': 'Enquiries',
                        'future_enquiries': 'Future Enquiries',
                        'booking': 'Booking',
                        'corporate_rates': 'Corporate Rates'
                    };

                    Object.entries(values).forEach(([key, value]) => {
                        console.log(key);
                        console.log(value);

                        $('#purpose_of_call').append($('<option>', {
                            value: key,
                            text: value
                        }));
                    });
                } else if (val === 'irrelevant') {

                    let values = {
                        'supply': 'Supply',
                        'existing_customer': 'Existing Customer',
                        'airline': 'Airline',
                        'bus_tickets': 'Bus Tickets',
                        'rental_cars': 'Rental Cars',
                        'banquets': 'Banquets',
                        'event_management': 'Event Management',
                        'real_state': 'Real State',
                        'food_related_enquiries': 'Food Related Enquiries',
                        'travel_agent': 'Travel Agent',
                        'corporate': 'Corporate',
                        'others': 'Others'
                    };

                    Object.entries(values).forEach(([key, value]) => {
                        console.log(key);
                        console.log(value);

                        $('#purpose_of_call').append($('<option>', {
                            value: key,
                            text: value
                        }));
                    });
                } else {
                    $('#purpose_of_call').append($('<option>', {
                        value: '',
                        text: 'No data Available'
                    }));
                }

            }
        }

        function changeTotalPriceByDate(dateRange) {
            const [start, end] = dateRange.split(' - ');
            const startDate = new Date(start);
            const endDate = new Date(end);
            $('#checkInDis').text(start)
            $('#checkOutDis').text(end)
            const timeDifference = endDate.getTime() - startDate.getTime();
            const dayDifference = timeDifference / (1000 * 3600 * 24);
            console.log(dayDifference);
            $("#day_difference").val(dayDifference)
            $("#NightDis").text(dayDifference)
        }

        function listingNameAppend(listing_name) {
            $('#listingDis').text(listing_name)
        }

        function changeAdultChildRoolText(value, prefix) {
            if (prefix === 'adult') {
                $('#adultDis').text(value)
            } else if (prefix === 'rooms') {
                $('#adultDis').text(value)
            } else if (prefix === 'children') {
                $('#childrenDis').text(value)
            } else {
                console.log('no data found !!!')
            }
        }


        function fetchListingInfo(listing_id) {

            const apartmentDropdown = $('#apartment_id');
            const datepicker = $('input[name="daterange"]');

            apartmentDropdown.on('select2:select', function(e) {
                console.log('vll', apartmentDropdown.val());
                if (apartmentDropdown.val() === "") {
                    datepicker.prop('disabled', true);
                    $('#error-msgdt').text("Please select an apartment first.");
                } else {
                    datepicker.prop('disabled', false);
                    $('#error-msgdt').text("");
                }
            });

            $("#pnChargeDis").val(0)
            $("#pnTotalDis").val(0)
            let daterange = $("#daterange").val();
            $.ajax({
                url: "{{ route('fetchListingInfo') }}",
                type: "get",
                data: {
                    id: listing_id,
                    daterange: daterange
                },
                success: function(response) {

                    if (response.calenderBlockDates) {

                        // console.log('dt: ',response.calenderBlockDates);

                        let blockedDates = response?.calenderBlockDates?.map(date => moment(date, "YYYY/MM/DD")
                            .format("YYYY-MM-DD"));

                        // blockedDates = ['2029-02-01'];

                        blockDatePicker(blockedDates, listing_id);
                    }

                    if (response.rate) {
                        $("#pnChargeDis").val(response.rate)
                        $("#pnTotalDis").val(response.rate)
                        addValueToTotal()
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }

        var dtpck = $('input[name="daterange"]');

        function blockDatePicker(blockedDates, listing_id = 0) {

            // console.log('final',blockedDates);

            // Super admin & admin can select previous dates
            if ({{ auth()->user()->role_id }} == 1 || {{ auth()->user()->role_id }} == 4) {
                dtpck.daterangepicker({
                    // minDate: moment().startOf('day'),
                    locale: {
                        format: 'MM/DD/YYYY'
                    },
                    // isInvalidDate: function(date) {
                    //     return blockedDates.includes(date.format("YYYY-MM-DD"));
                    // }
                });
            } else {
                dtpck.daterangepicker({
                    minDate: moment().subtract(26, 'hours').startOf('day'),
                    locale: {
                        format: 'MM/DD/YYYY'
                    },
                    // isInvalidDate: function(date) {
                    //     return blockedDates.includes(date.format("YYYY-MM-DD"));
                    // }
                });
            }

            // alert({{ auth()->user()->role_id }});

            dtpck.on('apply.daterangepicker', function(ev, picker) {
                let startDate = picker.startDate.clone();
                let endDate = picker.endDate.clone();

                if (startDate.isSame(endDate, 'day')) {
                    endDate = endDate.add(1, 'day');
                    picker.setEndDate(endDate);
                }

                // console.log('startDate', startDate.format("YYYY-MM-DD"));
                // console.log('endDate', endDate.format("YYYY-MM-DD"));

                $.ajax({
                    url: "{{ route('fetchCheckInOutBlocked') }}",
                    type: "get",
                    data: {
                        id: listing_id,
                        startDate: startDate.format("YYYY-MM-DD"),
                        endDate: endDate.format("YYYY-MM-DD")
                    },
                    success: function(response) {

                        console.log(response);

                        if (response.success == 0) {

                            $('#error-msg').text(
                                "Selected range includes blocked dates. Please select a different range."
                            );
                            $('#daterange').val('');
                            $('#submitButton').hide();

                            return;
                        }
                        console.log('Final dt: ', response.success);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });



                // while (startDate.isSameOrBefore(endDate)) {
                //     if (blockedDates.includes(startDate.format("YYYY-MM-DD"))) {

                //         let blockedIndex = blockedDates.indexOf(endDate.format("YYYY-MM-DD"));

                //         // endDate = endDate.add(1, 'day');
                //         // picker.setEndDate(endDate);
                //         // break;


                //         let blockedDatesLength = blockedDates.length - 1;

                //         // console.log('last bindex', blockedIndex);
                //         // console.log('last blockedDatesLength', blockedDatesLength);

                //         // console.log('chekc strt dt ',startDate.format("YYYY-MM-DD"));

                //         if(blockedIndex == blockedDatesLength){
                //           //
                //         } else{
                //              $('#error-msg').text("Selected range includes blocked dates. Please select a different range.");
                //             $('#daterange').val('');
                //             $('#submitButton').hide();

                //             return;
                //         }

                //     }
                //     startDate.add(1, 'day');
                // }
                $('#submitButton').show();
                $('#error-msg').text('');
            });

            dtpck.val("");
        }


        function addValueToTotal() {
            let per_day_charge = $("#pnChargeDis").val();
            let pnDiscountDis = $("#pnDiscountDis").val();
            let pnCleaningDis = $("#pnCleaningDis").val();
            let pnServiceDis = $("#pnServiceDis").val();
            let dayDifference = $("#day_difference").val();
            let total = (Number(per_day_charge) * Number(dayDifference));
            $("#pnTotalDis").val(total);

            const bookingType = document.getElementById('booking_type');
            const bookingSources = document.getElementById('booking_sources');
            const otaCommission = document.querySelector('input[name="ota_commission"]');
            const totalAmount = document.getElementById('pnTotalDis');

            if (
                bookingType &&
                bookingType.value === 'ota_livedin' &&
                bookingSources &&
                bookingSources.value !== '' &&
                totalAmount
            ) {
                const amount = parseFloat(per_day_charge || 0);
                const commission = (amount * 0.075).toFixed(2);
                otaCommission.value = commission;
                otaCommission.readOnly = true;
            }
        }

        function fetchGuestData(val) {
            $.ajax({
                url: "{{ route('fetchGuestByGuestId') }}",
                type: "post",
                data: {
                    phone: val,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // console.log(response.id)
                    if (response) {
                        $("#guest_id").val(response.id)
                        $("#name").val(response.name)
                        $("#surname").val(response.surname)
                        $("#email").val(response.email)
                        $("#dob").val(response.dob)
                        $("#gender").val(response.gender)
                        $("#country").val(response.country)
                        $("#city").val(response.city)
                    } else {
                        $("#guest_id").val('')
                        $("#name").val('')
                        $("#surname").val('')
                        $("#email").val('')
                        $("#dob").val('')
                        $("#gender").val('')
                        $("#country").val('')
                        $("#city").val('')
                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
    </script>
@endsection
