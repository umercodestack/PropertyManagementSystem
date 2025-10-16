@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Booking</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <form action="{{ route('booking-management.update', $booking->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <h6>Booking Details</h6>
                                <hr>
                                <div class="row gy-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="reservation_code">Reservation Code</label>
                                            <input type="text" class="form-control" name="reservation_code"
                                                id="reservation_code" value="{{ $booking->reservation_code }}"
                                                placeholder="Reservation code" />
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
                                                        <option value="{{ $items->id }}" {{ $booking->listing_id === $items->id ? 'selected' : '' }} data-title="{{ $listing_details->title }}">
                                                            {{ $listing_details->title }} -- {{ $listing_details->id }}
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
                                                onchange="changeTotalPriceByDate(this.value);" onkeydown="return false;"
                                                autocomplete="off" required />
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
                                                onkeyup="changeAdultChildRoolText(this.value, 'adult')"
                                                value="{{ $booking->adult }}" placeholder="Adult">
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
                                                value="{{ $booking->children }}" placeholder="Children">
                                            @error('children')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="rooms">Rooms</label>
                                            <input type="number" class="form-control" id="rooms" name="rooms"
                                                onkeyup="changeAdultChildRoolText(this.value, 'rooms')"
                                                value="{{ $booking->rooms }}" placeholder="Rooms">
                                            @error('rooms')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone</label>
                                            <input type="number" class="form-control" id="phone" name="phone"
                                                onkeyup="fetchGuestData(this.value)" value="{{ $guest->phone ?? '' }}"
                                                placeholder="Phone">
                                            @error('phone')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="name">First Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="{{ $guest->name ?? '' }}" placeholder="First Name">
                                            @error('name')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="surname">Last Name</label>
                                            <input type="text" class="form-control" id="surname" name="surname"
                                                value="{{ $guest->surname ?? '' }}" placeholder="Last Name">
                                            @error('surname')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ $guest->email ?? '' }}" placeholder="Email Address">
                                            @error('email')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="country">Country</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="country" id="country"
                                                    data-placeholder="Select Country" onchange="fetchCities(this.value)">
                                                    <option value="" selected disabled>Select Country</option>
                                                    @foreach ($countries as $item)
                                                        <option value="{{ $item->id }}" {{ isset($guest->country) && $guest->country == $item->id ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
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
                                                <select class="form-select select2" name="city" id="city"
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
                                                <select class="form-select select2" name="rating" id="rating"
                                                    data-placeholder="Select Rating"
                                                    onchange="disableDropdown(this.value, 'rating');appendValuesInDropdown(this.value, 'rating')">
                                                    <option value="" selected disabled>Select Rating</option>
                                                    <option value="1" {{ $booking->rating === '1' ? 'selected' : '' }}>1
                                                    </option>
                                                    <option value="2" {{ $booking->rating === '2' ? 'selected' : '' }}>2
                                                    </option>
                                                    <option value="irrelevant" {{ $booking->rating === 'irrelevant' ? 'selected' : '' }}>
                                                        Irrelevant</option>
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
                                                <select class="form-select select2" name="purpose_of_call"
                                                    id="purpose_of_call" data-placeholder="Select Purpose of call"
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
                                                <select class="form-select select2" name="reason" id="reason"
                                                    data-placeholder="Select Reason For Not Booking">
                                                    <option value="" selected disabled>Select Reason For Not Booking
                                                    </option>
                                                    <option value="competition" {{ $booking->reason == 'competition' ? 'selected' : '' }}>
                                                        Competition</option>
                                                    <option value="price_too_high" {{ $booking->reason == 'price_too_high' ? 'selected' : '' }}>Price
                                                        Too High</option>
                                                    <option value="price_too_low" {{ $booking->reason == 'price_too_low' ? 'selected' : '' }}>Price
                                                        Too Low</option>
                                                    <option value="location" {{ $booking->reason == 'location' ? 'selected' : '' }}>Location
                                                    </option>
                                                    <option value="room_unavailable" {{ $booking->reason == 'room_unavailable' ? 'selected' : '' }}>Room
                                                        Unavailable</option>
                                                    <option value="did_not_like_product" {{ $booking->reason == 'did_not_like_product' ? 'selected' : '' }}>
                                                        Did Not Like Product</option>
                                                    <option value="follow_up" {{ $booking->reason == 'follow_up' ? 'selected' : '' }}>Follow-up
                                                    </option>
                                                    <option value="not_responding" {{ $booking->reason == 'not_responding' ? 'selected' : '' }}>Not
                                                        Responding</option>
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
                                            <input type="text" class="form-control" id="booking_notes" name="booking_notes"
                                                value="{{ $booking->booking_notes }}" placeholder="Booking Notes">
                                            @error('booking_notes')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="cnic_passport">CNIC / Passport</label>
                                            <input type="text" class="form-control" id="cnic_passport" name="cnic_passport"
                                                value="{{ $booking->cnic_passport }}" placeholder="CNIC / Passport">
                                            @error('cnic_passport')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <input type="hidden" name="guest_id" id="guest_id" value="{{ $booking->guest_id }}">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="payment_method">Payment Method</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="payment_method"
                                                    id="payment_method" data-placeholder="Payment Method">
                                                    <option value="" selected disabled>Select Payment Method</option>
                                                    <option value="ibft" {{ $booking->payment_method == 'ibft' ? 'selected' : '' }}>IBFT
                                                    </option>
                                                    <option value="ibft-cc" {{ $booking->payment_method == 'ibft_cc' ? 'selected' : '' }}>IBFT
                                                        Cash Collection
                                                    </option>
                                                    <option value="paid_to_darent" {{ $booking->booking_sources == 'paid_to_darent' ? 'selected' : '' }}>
                                                        Paid to Darent
                                                    </option>
                                                    <option value="paid_to_goldenhost" {{ $booking->booking_sources == 'paid_to_goldenhost' ? 'selected' : '' }}>
                                                        Paid to Goldenhost
                                                    </option>
                                                    <option value="paid_to_vrbo" {{ $booking->booking_sources == 'paid_to_vrbo' ? 'selected' : '' }}>
                                                        Paid To Vrbo
                                                    </option>
                                                    <option value="bank" {{ $booking->payment_method == 'bank' ? 'selected' : '' }}>Bank
                                                    </option>
                                                    <option value="cod" {{ $booking->payment_method == 'cod' ? 'selected' : '' }}>Paid to
                                                        Host
                                                    </option>
                                                    <option value="livedin" {{ $booking->payment_method == 'livedin' ? 'selected' : '' }}>
                                                        Livedin
                                                    </option>
                                                    <option value="airbnb" {{ $booking->payment_method == 'airbnb' ? 'selected' : '' }}>PAID -
                                                        Airbnb</option>
                                                    <option value="booking" {{ $booking->payment_method == 'booking' ? 'selected' : '' }}>PAID
                                                        - Booking.com</option>
                                                    <option value="gathern" {{ $booking->payment_method == 'gathern' ? 'selected' : '' }}>
                                                        PAID - Gathern</option>
                                                    <option value="online" {{ $booking->payment_method == 'online' ? 'selected' : '' }}>Online
                                                    </option>
                                                    <option value="paid_booking_engine_credit_card" {{ $booking->payment_method == 'paid_booking_engine_credit_card' ? 'selected' : '' }}>
                                                        Paid - BookingEngine Credit Card
                                                    </option>
                                                    <option value="paid_booking_engine_stc_pay" {{ $booking->payment_method == 'paid_booking_engine_stc_pay' ? 'selected' : '' }}>
                                                        Paid - BookingEngine STC Pay
                                                    </option>
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
                                            <div class="form-control-wrap" data-old="{{ $booking->booking_type }}">
                                                <select class="form-select" name="booking_type" id="booking_type"
                                                    data-placeholder="Booking Type" required>
                                                    <option value="" selected disabled>Booking Type</option>
                                                    <option value="ota" {{ $booking->booking_type == 'ota' ? 'selected' : '' }}>OTA</option>
                                                    <option value="ota_livedin" {{ $booking->booking_type == 'ota_livedin' ? 'selected' : '' }}>OTA-Livedin (Direct Booking)</option>
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
                                                    data-placeholder="Booking Source" data-old="{{ $booking->booking_sources }}" required>
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
                                                    <option value="0" {{ $booking->include_cleaning == 0 || is_null($booking->include_cleaning) ? 'selected' : '' }}>
                                                        Yes
                                                    </option>
                                                    <option value="1" {{ $booking->include_cleaning === 1 ? 'selected' : '' }}>
                                                        No
                                                    </option>
                                                </select>
                                                @error('include_cleaning')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="booking_status">Booking Status</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="booking_status"
                                                    id="booking_status" data-placeholder="Booking Status">
                                                    <option value="" selected disabled>Booking Status</option>
                                                    <option value="confirmed" {{ $booking->booking_status == 'confirmed' ? 'selected' : '' }}>
                                                        Confirmed
                                                    </option>
                                                    <option value="checkedin" {{ $booking->booking_status == 'checkedin' ? 'selected' : '' }}>
                                                        Checkedin
                                                    </option>
                                                    <option value="checkedout" {{ $booking->booking_status == 'checkedout' ? 'selected' : '' }}>
                                                        Checkedout
                                                    </option>
                                                    <option value="upcoming" {{ $booking->booking_status == 'upcoming' ? 'selected' : '' }}>
                                                        Upcoming
                                                    </option>
                                                    <option value="cancelled" {{ $booking->booking_status == 'cancelled' ? 'selected' : '' }}>
                                                        Cancelled
                                                    </option>

                                                </select>
                                                @error('booking_status')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">Created By</label>
                                            @php
                                                $created_by = '';
                                                if (isset($booking->created_by)) {
                                                    $user = \App\Models\User::where(
                                                        'id',
                                                        $booking->created_by,
                                                    )->first();
                                                    $created_by = $user->name . ' ' . $user->surname;
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $created_by }}"
                                                placeholder="Created By" disabled>
                                            @error('booking_notes')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">Updated By</label>
                                            @php
                                                $updated_by = '';
                                                if (isset($booking->updated_by)) {
                                                    $user = \App\Models\User::where(
                                                        'id',
                                                        $booking->updated_by,
                                                    )->first();
                                                    $updated_by = $user->name . ' ' . $user->surname;
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $updated_by }}"
                                                placeholder="Updated By" disabled>
                                            @error('booking_notes')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="image">Iqama ID
                                            </label>
                                            <div class="form-control-wrap">
                                                <input type="file" class="form-control" id="image" name="image[]" multiple>
                                                @error('image')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    @if ($booking->payment_status != 'payment_complete')
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label" for="payment_references">Payment
                                                    References</label>
                                                <div class="form-control-wrap">
                                                    <input type="file" class="form-control" id="payment_references"
                                                        name="payment_references[]" multiple>
                                                    @error('payment_references')
                                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (isset($booking_images) && $booking_images->count() > 0)
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label" for="booking_sources">Iqama IDs</label>
                                                <div class="form-control-wrap">
                                                    <div class="row">
                                                        @foreach ($booking_images as $item)
                                                            <div class="col-md-4" style="position: relative">
                                                                <img src="{{ asset('storage/' . $item->image) }}"
                                                                    alt="{{ $item->id }}" class="w-100 img-fluid">
                                                                <a href="{{ route('booking-image-delete', $item->id) }}"
                                                                    style="position: absolute; top: -10px; right: 10px; border: 0; width: 25px; height: 25px; border-radius: 12.5px; background: red; color: white;text-align:center">X</a>

                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if (isset($booking_payment_images) && $booking_payment_images->count() > 0)
                                        <div class="col-md-12 mt-4">
                                            <label class="form-label">Booking References</label>
                                            <div class="row">
                                                @foreach ($booking_payment_images as $item)
                                                    <div class="col-md-6 mb-3 position-relative border p-3 rounded bg-light">
                                                        {{-- Display Image --}}
                                                        <img src="{{ asset('storage/' . $item->image) }}"
                                                            class="w-100 mb-2 rounded shadow-sm" alt="Reference Image">
                                                        {{-- Delete Button --}}
                                                        <a href="{{ route('reference-image-delete', $item->id) }}"
                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                                            onclick="return confirm('Are you sure you want to delete this reference image?')">
                                                            &times;
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
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
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="me-2">Per Night Charges:</strong>
                                            <div class="position-relative" style="width: 200px;">
                                                <input type="number" class="form-control pe-5" name="per_night_price"
                                                    id="pnChargeDis" step="0.01" value="{{ $booking->per_night_price }}"
                                                    onkeyup="addValueToTotal(this.value)" @if (
                                                        auth()->user()->role_id == 14 &&
                                                        ($booking->booking_sources == 'host_booking' || $booking->booking_sources == 'host')
                                                    ) readonly @endif>

                                                @if (
                                                        auth()->user()->role_id == 14 &&
                                                        ($booking->booking_sources == 'host_booking' || $booking->booking_sources == 'host')
                                                    )
                                                    <button type="button" onclick="resetPerNightCharge()"
                                                        class="btn btn-sm btn-outline-info position-absolute top-50 end-0 translate-middle-y me-1"
                                                        style="z-index: 10; padding: 0 6px; height: 70%;"
                                                        title="Set Price to 0">
                                                        Set Price to 0
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" class="form-control w-50" name="day_difference"
                                        id="day_difference" />
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between">
                                            <Strong>Discount</Strong>: <input type="number" class="form-control w-50"
                                                name="custom_discount" id="pnDiscountDis" step="0.01"
                                                value="{{ $booking->custom_discount }}"
                                                onkeyup="addValueToTotal(this.value)">
                                        </p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Cleaning
                                                Fee</Strong>: <input type="number" class="form-control w-50"
                                                name="cleaning_fee" id="pnCleaningDis" step="0.01"
                                                value="{{ $booking->cleaning_fee }}"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Service
                                                Fee</Strong>: <input type="number" class="form-control w-50"
                                                name="service_fee" id="pnServiceDis" step="0.01"
                                                value="{{ $booking->service_fee }}" onkeyup="addValueToTotal(this.value)">
                                        </p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Ota Commission
                                                Charges</Strong>: <input type="number" id=ota_commission
                                                value="{{ $booking->ota_commission }}" step="0.01" class="form-control w-50"
                                                name="ota_commission"></p>
                                    </div>
                                    <hr>
                                    <div class="col-md-12">
                                        <p class="d-flex align-items-center justify-content-between"><Strong>Total
                                                Charges</Strong>: <input readonly type="number" class="form-control w-50"
                                                name="total_price" step="0.01" value="{{ $booking->total_price }}"
                                                id="pnTotalDis">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-bordered">
                            @php
                                $updated_by = '';
                                if (isset($booking->updated_by)) {
                                    $user = \App\Models\User::where('id', $booking->updated_by)->first();
                                    $updated_by = $user->name . ' ' . $user->surname;
                                }
                            @endphp
                            <div class="card-inner">
                                <h6>Logs</h6>
                                <hr>
                                @if (
                                        auth()->user()->role_id == 14 &&
                                        ($booking->booking_sources == 'host_booking' || $booking->booking_sources == 'host')
                                    )
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <p><Strong>Set price timestamp</Strong> :
                                                <span>{{ $booking->updated_at->format('d-M-Y h:m a') }}</span>
                                            </p>
                                            <p><Strong>Agent ID</Strong> : <span>{{ $updated_by }}</span></p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
        @if ($role == 1 || $role == 4 || $role == 15)
            <div class="col-md-8 mt-3">
                <div class="card card-bordered">
                    @php
                        $updated_by = '';
                        if (isset($booking->updated_by)) {
                            $user = \App\Models\User::where('id', $booking->updated_by)->first();
                            $updated_by = $user->name . ' ' . $user->surname;
                        }
                    @endphp
                    <div class="card-inner">
                        <h6>Booking Payment Reconciliation</h6>
                        <hr>
                        <div class="col-md-12">
                            <form action="{{ route('booking-management.update', $booking->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    {{-- <label class="form-label">Add Booking References</label> --}}
                                    @php
                                        $amount_tbr = 0;
                                        if (
                                            $booking->booking_sources == 'airbnb' ||
                                            $booking->booking_sources == 'gathern'
                                        ) {
                                            $amount_tbr =
                                                $booking->total_price +
                                                $booking->cleaning_fee -
                                                $booking->ota_commission -
                                                $booking->custom_discount;
                                        } elseif ($booking->booking_sources == 'booking_com') {
                                            $amount_tbr = $booking->total_price - $booking->custom_discount;
                                        } elseif ($booking->booking_sources == 'host') {
                                            if ($booking->payment_method == 'cod') {
                                                $amount_tbr = 0;
                                            } else {
                                                $amount_tbr = $booking->total_price - $booking->custom_discount;
                                            }
                                        } else {
                                            $amount_tbr =
                                                $booking->total_price +
                                                $booking->cleaning_fee -
                                                $booking->custom_discount;
                                        }
                                        // dd($amount_tbr);
                                    @endphp
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="amount_to_be_received">Amount To Be Received
                                                (SAR)</label>
                                            <div class="form-control-wrap">
                                                <input type="number" class="form-control" id="amount_to_be_received"
                                                    onkeyup="calculateForex(this.value)" name="amount_to_be_received"
                                                    value="{{ $amount_tbr ?? '' }}" disabled
                                                    placeholder="Enter Amount Received">
                                                @error('amount_to_be_received')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="amount_received">Amount Received (SAR)</label>
                                            <div class="form-control-wrap">
                                                <input type="number" class="form-control" id="amount_received"
                                                    onkeyup="calculateForex(this.value)" name="amount_received"
                                                    value="{{ $booking->amount_received ?? 0 }}"
                                                    placeholder="Enter Amount Received">
                                                @error('amount_received')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <br>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="forex_adjustement">Forex Adjustment
                                                (SAR)</label>
                                            <div class="form-control-wrap">
                                                <input type="number" class="form-control" id="forex_adjustement" readonly
                                                    name="forex_adjustement" value="{{ $booking->forex_adjustement ?? '' }}"
                                                    placeholder="Forex Adjustment">
                                                @error('forex_adjustement')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="reference_numbers">Reference Numbers</label>
                                            <div class="form-control-wrap">
                                                <input type="text" class="form-control" id="reference_numbers"
                                                    name="reference_numbers" value="{{ $booking->reference_numbers ?? '' }}"
                                                    placeholder="123, 456, 789">
                                                @error('reference_numbers')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="payment_status">Payment Status</label>
                                            <div class="form-control-wrap">
                                                <p>Current Status: {{ $booking->payment_status }}</p>

                                                <select class="form-select select2" name="payment_status" id="payment_status"
                                                    data-placeholder="Booking Status">
                                                    <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>
                                                        Pending
                                                    </option>
                                                    <option value="in_review" {{ $booking->payment_status == 'in_review' ? 'selected' : '' }}>
                                                        In Review
                                                    </option>
                                                    <option value="rejected" {{ $booking->payment_status == 'rejected' ? 'selected' : '' }}>
                                                        Rejected
                                                    </option>
                                                    <option value="payment_partial" {{ $booking->payment_status == 'payment_partial' ? 'selected' : '' }}>
                                                        Partial Payment
                                                    </option>
                                                    <option value="payment_complete" {{ $booking->payment_status == 'payment_complete' ? 'selected' : '' }}>
                                                        Payment Complete
                                                    </option>

                                                </select>
                                                @error('payment_status')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group text-end">
                                            <button type="submit" class="btn btn-sm btn-primary mt-2">Submit</button>
                                        </div>
                                    </div>
                                    <br>
                                    {{-- @if ($role == 1 || $role == 4 || $role == 14 || $role == 15)
                                    <div id="reference-upload-group">
                                        <div class="reference-item border p-3 mb-3 rounded bg-light">
                                            <label>Image</label>
                                            <input type="file" name="image[]" class="form-control mb-2">

                                            <div class="row">
                                                <div class="col-md-8">
                                                    <label>Reference Number</label>
                                                    <input type="text" name="reference_number[]" class="form-control mb-2"
                                                        placeholder="e.g. TXN1234">
                                                </div>
                                                <div class="col-md-4">
                                                    <label>Reference Status</label>
                                                    <select name="reference_status[]" class="form-control mb-2">
                                                        <option value="verified">Verified</option>
                                                        <option value="pending" selected>Pending</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                            onclick="addMoreReference()">+ Add More</button>
                                        <button type="submit" class="btn btn-sm btn-outline-primary mt-2">Submit</button>
                                    </div>
                                    @endif --}}
                                </div>


                            </form>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js?v=1.5"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js?v=1.5"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js?v=1.5"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css?v=1.5" />
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

            const oldBookingType = bookingType.dataset.old || bookingType.value;
            const oldBookingSource = bookingSources.dataset.old || bookingSources.value;

            if (oldBookingType) {
                populateBookingSources(oldBookingType, oldBookingSource);
                handleCommissionCalculation();
            }

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

            function populateBookingSources(type, selected = '') {
                bookingSources.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.disabled = true;
                placeholder.text = 'Booking Source';

                if (!selected) {
                    placeholder.selected = true;
                }

                bookingSources.appendChild(placeholder);

                const optionsToAdd = type === 'ota' ? otaOptions : type === 'ota_livedin' ? livedinOptions : [];

                optionsToAdd.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.text = opt.text;

                    if (selected && selected === opt.value) {
                        option.selected = true;
                    }

                    bookingSources.appendChild(option);
                });
            }

            function handleCommissionCalculation() {
                const selectedSource = bookingSources.value;

                if (otaValues.includes(selectedSource)) {
                    otaCommission.readOnly = false;
                    otaCommission.value = '';
                } else if (selectedSource !== '') {
                    const amount = parseFloat(per_day_charge.value || 0);
                    const commission = (amount * 0.075).toFixed(2);
                    otaCommission.value = commission;
                    otaCommission.readOnly = true;
                } else {
                    resetCommissionField();
                }

                otaCommission.required = true;
            }

            function resetCommissionField() {
                otaCommission.value = '';
                otaCommission.readOnly = false;
            }
        });

        var start = moment('{{ $booking->booking_date_start }}');
        var end = moment('{{ $booking->booking_date_end }}');


        // $('input[name="daterange"]').daterangepicker({
        //     opens: 'left',
        //     startDate: start,
        //     endDate: end,
        // }, function(start, end, label) {
        //     console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
        //         'YYYY-MM-DD'));
        // });


        const apartmentDropdown = $('#apartment_id');
        const datepicker = $('input[name="daterange"]');

        // datepicker.prop('disabled', !apartmentDropdown.val());

        // $('#error-msgdt').text("Please select an apartment first.");

        $('form').on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13 || e.keyCode === 108) {
                e.preventDefault();
                return false;
            }
        });


        // $('input[name="daterange"]').daterangepicker({
        //     // minDate: moment().startOf('day'),
        //     startDate: start,
        //     endDate: end,
        //     locale: {
        //         format: 'MM/DD/YYYY'
        //     },
        //     // isInvalidDate: function(date) {
        //     //     return blockedDates.includes(date.format("YYYY-MM-DD"));
        //     // }
        // });

        // Super admin & admin can select previous dates
        if ({{ auth()->user()->role_id }} == 1 || {{ auth()->user()->role_id }} == 4) {
            $('input[name="daterange"]').daterangepicker({
                // minDate: moment().startOf('day'),
                startDate: start,
                endDate: end,
                locale: {
                    format: 'MM/DD/YYYY'
                },
                // isInvalidDate: function(date) {
                //     return blockedDates.includes(date.format("YYYY-MM-DD"));
                // }
            });
        } else {
            $('input[name="daterange"]').daterangepicker({
                minDate: moment("{{ $booking_date_start }}").startOf(
                    'day'), // moment().subtract(26, 'hours').startOf('day'),
                startDate: start,
                endDate: end,
                locale: {
                    format: 'MM/DD/YYYY'
                },
                // isInvalidDate: function(date) {
                //     return blockedDates.includes(date.format("YYYY-MM-DD"));
                // }
            });
        }


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
                success: function (response) {
                    if (response) {
                        response.map((item, index) => {
                            let isSelected = false
                            let fetchCity = '{{ $guest->city ?? '' }}'
                            if (item.id == fetchCity) {
                                isSelected = true
                            }
                            console.log(isSelected);
                            $('#city').append($('<option>', {
                                value: item.id,
                                text: item.name,
                                selected: isSelected
                            }));
                        })
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }

        fetchCities(`{{ $guest->country ?? '' }}`);

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

                    let fetchPurposeOfCall = `{{ $booking->purpose_of_call }}`;
                    let isSelected = false;
                    Object.entries(values).forEach(([key, value]) => {
                        if (fetchPurposeOfCall == key) {
                            isSelected = true;
                        }

                        $('#purpose_of_call').append($('<option>', {
                            value: key,
                            text: value,
                            selected: isSelected
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

        disableDropdown(`{{ $booking->rating }}`, 'rating');
        disableDropdown(`{{ $booking->purpose_of_call }}`, 'purpose_of_call');
        appendValuesInDropdown(`{{ $booking->rating }}`, 'rating')

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
        listingNameAppend($("#apartment_id :selected").text())

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

        changeAdultChildRoolText(`{{ $booking->rooms }}`, 'rooms')
        changeAdultChildRoolText(`{{ $booking->adult }}`, 'adult')
        changeAdultChildRoolText(`{{ $booking->children }}`, 'children')


        function fetchListingInfo(listing_id) {

            const apartmentDropdown = $('#apartment_id');
            const datepicker = $('input[name="daterange"]');

            apartmentDropdown.on('select2:select', function (e) {
                console.log('vll', apartmentDropdown.val());
                if (apartmentDropdown.val() === "") {
                    datepicker.prop('disabled', true);
                    $('#error-msgdt').text("Please select an apartment first.");
                } else {
                    datepicker.prop('disabled', false);
                    $('#error-msgdt').text("");
                }
            });

            let daterange = $('#daterange').val();
            // $("#pnChargeDis").val(0)
            $("#pnTotalDis").val(0)
            $.ajax({
                url: "{{ route('fetchListingInfo') }}",
                type: "get",
                data: {
                    id: listing_id,
                    daterange: daterange
                },
                success: function (response) {


                    if (response.calenderBlockDates) {

                        // console.log('dt: ',response.calenderBlockDates);

                        let blockedDates = response?.calenderBlockDates?.map(date => moment(date, "YYYY/MM/DD")
                            .format("YYYY-MM-DD"));

                        // blockedDates = ['2029-02-01'];

                        blockDatePicker(blockedDates, listing_id);
                    }

                    // console.log(response.id)
                    if (response.rate) {
                        // $("#pnChargeDis").val(response.rate)
                        $("#pnTotalDis").val(response.rate)
                        addValueToTotal()
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }

        // fetchListingInfo(`{{ $booking->listing_id }}`);



        $(document).ready(function () {

            var dropdown = document.getElementById('apartment_id');

            var primary_listing_id = dropdown.value;

            fetchListingInfo(primary_listing_id);

            // console.log('Fn: ', primary_listing_id);

            $('#apartment_id').on('change', function () {
                var primary_listing_id = $(this).val();
                //alert(primary_listing_id);
                fetchListingInfo(primary_listing_id);
            });

        });

        var dtpck = $('input[name="daterange"]');

        function blockDatePicker(blockedDates, listing_id = 0) {

            // console.log('booking_date_start', "{{ $booking_date_start }}");

            // console.log('final',blockedDates);
            // var gg = moment('2025-02-15').subtract(1, 'days');
            // console.log('fdtes', gg);

            // var previousDate = moment('2025-01-31').subtract(1, 'days');

            // console.log('new dates', previousDate.startOf('day'));

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
                    minDate: moment("{{ $booking_date_start }}").startOf('day'),
                    locale: {
                        format: 'MM/DD/YYYY'
                    },
                    // isInvalidDate: function(date) {
                    //     return blockedDates.includes(date.format("YYYY-MM-DD"));
                    // }
                });
            }

            // alert({{ auth()->user()->role_id }});

            dtpck.on('apply.daterangepicker', function (ev, picker) {
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
                        endDate: endDate.format("YYYY-MM-DD"),
                        type: 'edit'
                    },
                    success: function (response) {

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
                    error: function (jqXHR, textStatus, errorThrown) {
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
        }

        function resetPerNightCharge() {
            const input = document.getElementById('pnChargeDis');
            input.value = 0;
            addValueToTotal(); // No parameter needed
        }

        function addValueToTotal() {
            let per_day_charge = parseFloat($("#pnChargeDis").val()) || 0;
            let pnDiscountDis = parseFloat($("#pnDiscountDis").val()) || 0;
            let pnCleaningDis = parseFloat($("#pnCleaningDis").val()) || 0;
            let pnServiceDis = parseFloat($("#pnServiceDis").val()) || 0;
            let dayDifference = parseFloat($("#day_difference").val()) || 0;
            let ota_commission = parseFloat($("#ota_commission").val()) || 0;

            let base_total = per_day_charge * dayDifference;

            let total = base_total + pnServiceDis - pnDiscountDis; // + ota_commission;

            $("#pnTotalDis").val(total.toFixed(2));

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
                otaCommission.required = true;
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
                success: function (response) {
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
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }



        function calculateForex(changedValue) {
            // Get the values from both input fields
            const amountToBeReceived = parseFloat(document.getElementById('amount_to_be_received').value) || 0;
            const amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;

            // Calculate forex adjustment: Amount To Be Received - Amount Received
            const forexAdjustment = amountToBeReceived - amountReceived;

            // Update the forex adjustment field
            document.getElementById('forex_adjustement').value = forexAdjustment.toFixed(2);
        }

        // Alternative function if you want to trigger calculation on both fields
        function calculateForexAdjustment() {
            const amountToBeReceived = parseFloat(document.getElementById('amount_to_be_received').value) || 0;
            const amountReceived = parseFloat(document.getElementById('amount_received').value) || 0;

            const forexAdjustment = amountToBeReceived - amountReceived;

            document.getElementById('forex_adjustement').value = forexAdjustment.toFixed(2);
        }
    </script>
@endsection