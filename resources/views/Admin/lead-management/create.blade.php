@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Booking Lead</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <form action="{{route('lead-management.store')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <h6>Booking Lead Details</h6>
                                <hr>
                                <div class="row gy-4">
{{--                                    <div class="col-md-4">--}}
{{--                                        <div class="form-group">--}}
{{--                                            <label class="form-label" for="host_id">Host</label>--}}
{{--                                            <div class="form-control-wrap">--}}
{{--                                                <select class="form-select" name="host_id" id="host_id" data-placeholder="Select Host">--}}
{{--                                                    <option value="" selected disabled>Select Host</option>--}}
{{--                                                    @foreach($users as $items)--}}
{{--                                                        <option value="{{$items->id}}">{{$items->name}} {{$items->surname}}</option>--}}
{{--                                                    @endforeach--}}
{{--                                                </select>--}}
{{--                                                @error('user_id')--}}
{{--                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>--}}
{{--                                                @enderror--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="apartment_id">Apartment</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="apartment_id" id="apartment_id" data-placeholder="Select Apartment" onchange="fetchListingInfo(this.value);listingNameAppend(this.options[this.selectedIndex].getAttribute('data-title'))">
                                                    <option value="" selected disabled>Select Apartment</option>
                                                    @foreach($listings as $items)
                                                        @php
                                                            $listing_details = json_decode($items->listing_json);
                                                        @endphp
                                                        <option value="{{$items->id}}" data-title="{{$listing_details->title}}">{{$listing_details->title}} -- {{$listing_details->id}}</option>
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
                                            <input type="text" class="form-control"  name="daterange" id="daterange"  value="01/01/2018 - 01/15/2018" onchange="changeTotalPriceByDate(this.value);addValueToTotal(this.value)"/>
                                            @error('booking_date_start')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="adult">Adult</label>
                                            <input type="number" class="form-control" id="adult" name="adult" onkeyup="changeAdultChildRoolText(this.value, 'adult')" placeholder="Adult">
                                            @error('adult')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="children">Children</label>
                                            <input type="number" class="form-control" id="children" name="children" onkeyup="changeAdultChildRoolText(this.value, 'children')" placeholder="Children">
                                            @error('children')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="rooms">Rooms</label>
                                            <input type="number" class="form-control" id="rooms" name="rooms" onkeyup="changeAdultChildRoolText(this.value, 'rooms')" placeholder="Rooms">
                                            @error('rooms')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone</label>
                                            <input type="number" class="form-control" id="phone" name="phone" onkeyup="fetchGuestData(this.value)" placeholder="Phone">
                                            @error('phone')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="name">First Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="First Name">
                                            @error('name')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="surname">Last Name</label>
                                            <input type="text" class="form-control" id="surname" name="surname" placeholder="Last Name">
                                            @error('surname')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address">
                                            @error('email')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="country">Country</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="country" id="country" data-placeholder="Select Country" onchange="fetchCities(this.value)">
                                                    <option value="" selected disabled>Select Country</option>
                                                    @foreach($countries as $item)
                                                        <option value="{{$item->id}}">{{$item->name}}</option>
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
                                                <select class="form-select" name="city" id="city" data-placeholder="Select City">
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
                                                <select class="form-select" name="rating" id="rating" data-placeholder="Select Rating" onchange="disableDropdown(this.value, 'rating');appendValuesInDropdown(this.value, 'rating')">
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
                                                <select class="form-select" name="purpose_of_call" id="purpose_of_call" data-placeholder="Select Purpose of call" onchange="disableDropdown(this.value, 'purpose_of_call')">
                                                    <option value="" selected disabled>Select Purpose Of Call</option>
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
                                                <select class="form-select" name="reason" id="reason" data-placeholder="Select Reason For Not Booking">
                                                    <option value="" selected disabled>Select Reason For Not Booking</option>
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
                                            <input type="text" class="form-control" id="booking_notes" name="booking_notes" placeholder="Booking Notes">
                                            @error('booking_notes')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="cnic_passport">CNIC / Passport</label>
                                            <input type="text" class="form-control" id="cnic_passport" name="cnic_passport" placeholder="CNIC / Passport">
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
                                                <select class="form-select" name="payment_method" id="payment_method" data-placeholder="Payment Method">
                                                    <option value="" selected disabled>Select Payment Method</option>
                                                    <option value="ibft">IBFT</option>
                                                    <option value="bank">Bank</option>
                                                    <option value="cod">COD</option>
                                                    <option value="airbnb">PAID - Airbnb</option>
                                                    <option value="booking">PAID - Booking.com</option>
                                                    <option value="gathering">PAID - Gathering</option>
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
                                            <label class="form-label" for="booking_sources">Booking Source</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select" name="booking_sources" id="booking_sources" data-placeholder="Booking Source">
                                                    <option value="" selected disabled>Booking Source</option>
                                                    <option value="call">Call</option>
                                                    <option value="fnf">FNF</option>
                                                    <option value="pr">PR</option>
                                                    <option value="website">Website</option>
                                                    <option value="corporate">Corporate</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="whatsapp">Whatsapp</option>
                                                    <option value="booking_com">Booking.com</option>
                                                    <option value="hotel">Hotel</option>
                                                    <option value="walk-in">Walk-in</option>
                                                    <option value="gozayaan">Gozayaan</option>
                                                    <option value="airbnb">AirBNB</option>
                                                    <option value="others">Other</option>
                                                </select>
                                                @error('booking_sources')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="image">References</label>
                                            <div class="form-control-wrap">
                                                <input type="file" class="form-control" id="image" name="image[]" multiple>
                                                @error('image')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group text-end">
                                            <button type="submit" class="btn btn-primary">Submit</button>
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
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Per Night Charges</Strong>: <input type="number" class="form-control w-50" name="per_night_price" id="pnChargeDis" readonly></p>
                                    </div>
                                    <input type="hidden" class="form-control w-50" name="day_difference"  id="day_difference"/>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Discount</Strong>: <input type="number" class="form-control w-50" name="custom_discount" id="pnDiscountDis" onkeyup="addValueToTotal(this.value)"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Cleaning Fee</Strong>: <input type="number" class="form-control w-50" name="cleaning_fee" id="pnCleaningDis" onkeyup="addValueToTotal(this.value)"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Service Fee</Strong>: <input type="number" class="form-control w-50" name="service_fee" id="pnServiceDis" onkeyup="addValueToTotal(this.value)"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Total Charges</Strong>: <input type="number" class="form-control w-50" name="total_price" id="pnTotalDis" readonly></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>

        var start = moment().subtract(1, 'days');
        var end = moment();


        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });


        function fetchCities(val) {
            $('#city').html('');
            $('#city').append($('<option>', {
                value: '',
                text: 'Select City',
                disabled: true,
                selected: true
            }));

            $.ajax({
                url: "{{route('cities')}}",
                type: "get",
                data: {country_id: val} ,
                success: function (response) {
                    if(response){
                        response.map((item, index) => {
                            $('#city').append($('<option>', {
                                value: item.id,
                                text : item.name
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
            if(prefix === 'rating') {
                if(val === '2' || val === 'irrelevant') {
                    $('#purpose_of_call').prop("disabled", false);
                }
                else {
                    $('#purpose_of_call').prop("disabled", true);
                }
                if(val === '1') {
                    $('#reason').prop("disabled", false);
                }else {
                    $('#reason').prop("disabled", true);
                }
            }
            else if(prefix === 'purpose_of_call') {

                if(val === 'enquiries' || val === 'future_enquiries' || val === 'corporate_rates') {
                    $('#reason').prop("disabled", false);
                }else {
                    $('#reason').prop("disabled", true);
                }
            }
        }
        function appendValuesInDropdown(val, prefix) {
            if(prefix === 'rating') {
                $('#purpose_of_call').html('')
                $('#purpose_of_call').append($('<option>', {
                    value: '',
                    text: 'Select Purpose of call',
                    disabled: true,
                    selected: true
                }));

                if(val === '2' ) {

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
                }
                else if(val === 'irrelevant' ) {

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
                }
                else {
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

        function listingNameAppend(listing_name){
            $('#listingDis').text(listing_name)
        }

        function changeAdultChildRoolText(value, prefix) {
            if(prefix === 'adult') {
                $('#adultDis').text(value)
            }
            else if(prefix === 'rooms') {
                $('#adultDis').text(value)
            }
            else if(prefix === 'children') {
                $('#childrenDis').text(value)
            }
            else {
                console.log('no data found !!!')
            }
        }


        function fetchListingInfo(listing_id) {
            $("#pnChargeDis").val(0)
            $("#pnTotalDis").val(0)
            $.ajax({
                url: "{{route('fetchListingInfo')}}",
                type: "get",
                data: {id: listing_id} ,
                success: function (response) {
                    // console.log(response.id)
                    if(response){
                        $("#pnChargeDis").val(response)
                        $("#pnTotalDis").val(response)
                        addValueToTotal()
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }

        function addValueToTotal() {
            let per_day_cahrge = $("#pnChargeDis").val();
            let pnDiscountDis = $("#pnDiscountDis").val();
            let pnCleaningDis = $("#pnCleaningDis").val();
            let pnServiceDis = $("#pnServiceDis").val();
            let dayDifference = $("#day_difference").val();
            let total = (Number(per_day_cahrge) * Number(dayDifference)) +Number(pnDiscountDis)+Number(pnCleaningDis)+Number(pnServiceDis);
            $("#pnTotalDis").val(total)
        }

        function fetchGuestData(val) {
            $.ajax({
                url: "{{route('fetchGuestByGuestId')}}",
                type: "post",
                data: {phone: val, _token: '{{ csrf_token() }}'} ,
                success: function (response) {
                    // console.log(response.id)
                    if(response){
                        $("#guest_id").val(response.id)
                        $("#name").val(response.name)
                        $("#surname").val(response.surname)
                        $("#email").val(response.email)
                        $("#dob").val(response.dob)
                        $("#gender").val(response.gender)
                        $("#country").val(response.country)
                        $("#city").val(response.city)
                    }
                    else {
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
