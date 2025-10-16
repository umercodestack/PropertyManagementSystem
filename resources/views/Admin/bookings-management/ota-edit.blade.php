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
            <form action="{{ route('booking.updateOtaBooking', $booking->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <h6>Ota Booking Details</h6>

                                @php
                                    use Carbon\Carbon;

                                    if ($booking->ota_name == 'Almosafer') {
                                        $arrival = Carbon::parse($booking->arrival_date);
                                        $departure = Carbon::parse($booking->departure_date);
                                        $total_nights = $arrival->diffInDays($departure);
                                    } else {
                                        $booking_details = json_decode($booking->booking_otas_json_details);
                                        $raw_message = json_decode($booking_details->attributes->raw_message);
                                        $total_nights = !empty($raw_message->reservation->nights)
                                            ? $raw_message->reservation->nights
                                            : 1;
                                    }
                                @endphp
                                <hr>
                                <div class="row gy-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="apartment_id">Apartment</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="apartment_id" id="apartment_id"
                                                    data-placeholder="Select Apartment">
                                                    <option value="" selected disabled>Select Apartment</option>
                                                    @foreach ($listings as $items)
                                                        @php
                                                            if ($items->is_manual == 1) {
                                                                continue;
                                                            }
                                                            $listing_details = json_decode($items->listing_json);

                                                            $selected_value = false;
                                                            if ($booking->ota_name == 'Almosafer') {
                                                                if ($almosafer_airbnb_listing_id == $items->id) {
                                                                    $selected_value = true;
                                                                }
                                                            } elseif ($booking->listing_id == $items->listing_id) {
                                                                $selected_value = true;
                                                            }

                                                        @endphp
                                                        <option value="{{ $items->listing_id }}"
                                                            {{ $selected_value ? 'selected' : '' }}
                                                            data-title="{{ isset($listing_details->title) ? $listing_details->title : '' }}">
                                                            {{ isset($listing_details->title) ? $listing_details->title : '' }}
                                                            -- {{ isset($listing_details->id) ? $listing_details->id : '' }}
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
                                                value="01/01/2018 - 01/15/2018" />
                                            @error('booking_date_start')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="adult">Adult</label>
                                            <input type="number" class="form-control" id="adult" name="adult"
                                                onkeyup="changeAdultChildRoolText(this.value, 'adult')"
                                                value="{{ !empty($bk_json->attributes->occupancy->adults) ? $bk_json->attributes->occupancy->adults : $booking->adults }}"
                                                placeholder="Adult" disabled>
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
                                                value="{{ !empty($bk_json->attributes->occupancy->children) ? $bk_json->attributes->occupancy->children : $booking->children }}"
                                                placeholder="Children" disabled>
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
                                                value="{{ !empty($bk_json->attributes->rooms) ? count($bk_json->attributes->rooms) : $booking->rooms }}"
                                                placeholder="Rooms" disabled>
                                            @error('rooms')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone</label>
                                            <input type="text" class="form-control" name="phone"
                                                value="{{ $booking->guest_phone }}" placeholder="Phone" disabled>
                                            @error('phone')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="{{ $booking->guest_name }}" placeholder="First Name" disabled>
                                            @error('name')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="form-label" for="booking_notes">Booking Notes</label>
                                            <input type="text" class="form-control" id="booking_notes"
                                                name="booking_notes"
                                                value="{{ !empty($bk_json->attributes->notes) ? $bk_json->attributes->notes : $booking->booking_notes }}"
                                                placeholder="Booking Notes" disabled>
                                            @error('booking_notes')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label" for="city">City</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="city" id="city"
                                                    data-placeholder="Select City" disabled>
                                                    <option value="">Select City</option>
                                                    <option value="{{ $listing->city_name }}" selected disabled>
                                                        {{ $listing->city_name ?? '' }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="discount">Discount</label>
                                            <input type="text" class="form-control" name="discount" id="discount"
                                                value="{{ $booking->discount }}" />
                                            @error('discount')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="promotion">Promotion</label>
                                            <input type="text" class="form-control" name="promotion" id="promotion"
                                                value="{{ $booking->promotion }}" />
                                            @error('promotion')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="ota_commission">Ota Commission</label>
                                            <input type="text" class="form-control" name="ota_commission"
                                                id="ota_commission" value="{{ $booking->ota_commission }}" />
                                            @error('ota_commission')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="cleaning_fee">Cleaning Fee</label>
                                            <input type="text" class="form-control" name="cleaning_fee"
                                                id="cleaning_fee" value="{{ $booking->cleaning_fee }}" />
                                            @error('cleaning_fee')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="promotion">Total</label>
                                            <input type="text" class="form-control" name="total" id="total"
                                                value="{{ $booking->amount }}" />
                                            @error('total')
                                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label" for="status">Status</label>
                                            <div class="form-control-wrap">
                                                <select class="form-select select2" name="status" id="status"
                                                    data-placeholder="Select Status" required>
                                                    <option value="" selected disabled>Select Status</option>
                                                    <option value="New"
                                                        {{ isset($booking->status) && $booking->status == 'New' ? 'selected' : '' }}>
                                                        New</option>
                                                    <option value="confirmed"
                                                        {{ isset($booking->status) && $booking->status == 'confirmed' ? 'selected' : '' }}>
                                                        Confirmed</option>
                                                    <option value="modified"
                                                        {{ isset($booking->status) && $booking->status == 'modified' ? 'selected' : '' }}>
                                                        Modified</option>
                                                    <option value="cancelled"
                                                        {{ isset($booking->status) && $booking->status == 'cancelled' ? 'selected' : '' }}>
                                                        Cancelled</option>
                                                </select>
                                                @error('status')
                                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">

                                        <div class="form-group">
                                            <label class="form-label">Order Status</label>
                                            <select name="system_status" class="form-select">
                                                <option value="">All</option>
                                                <option value="confirmed"
                                                    {{ isset($booking->system_status) && $booking->system_status == 'confirmed' ? 'selected' : '' }}>
                                                    Confirmed</option>
                                                <option value="checkedin"
                                                    {{ isset($booking->system_status) && $booking->system_status == 'checkedin' ? 'selected' : '' }}>
                                                    Checkedin</option>
                                                <option value="checkedout"
                                                    {{ isset($booking->system_status) && $booking->system_status == 'checkedout' ? 'selected' : '' }}>
                                                    Checkedout</option>
                                                <option value="upcoming"
                                                    {{ isset($booking->system_status) && $booking->system_status == 'upcoming' ? 'selected' : '' }}>
                                                    Upcoming</option>
                                                <option value="cancelled"
                                                    {{ isset($booking->system_status) && $booking->system_status == 'cancelled' ? 'selected' : '' }}>
                                                    Cancelled</option>
                                            </select>
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

                                    @if ($booking->payment_status != 'payment_complete')
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label" for="payment_references">Payment
                                                    References</label>
                                                <div class="form-control-wrap">
                                                    <input type="file" class="form-control" id="payment_references"
                                                        name="payment_references[]" multiple>
                                                    @error('payment_references')
                                                        <span id="fva-full-name-error"
                                                            class="invalid">{{ $message }}</span>
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
                                                    <div
                                                        class="col-md-6 mb-3 position-relative border p-3 rounded bg-light">
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


                                    <div class="col-md-12">
                                        <div class="form-group text-end">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>

            </form>

            @if (!empty($booking->proof_of_payment))
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="proof_of_payment_lbl">Iqama ID:</label>
                        <a href="{{ asset('storage/' . $booking->proof_of_payment) }}" target="_blank"
                            class="btn btn-primary">
                            View File
                        </a>
                        <form action="{{ route('booking.deleteFile', $booking->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete File</button>
                        </form>
                    </div>
                </div>
            @endif



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
                        <p><Strong>Apartment</Strong>: <span id="listingDis">{{ $listing_name }}</span></p>
                        <p><Strong>Check In</Strong>: <span id="checkInDis">{{ $booking->arrival_date }}</span></p>
                        <p><Strong>Check Out</Strong>: <span id="checkOutDis">{{ $booking->departure_date }}</span></p>
                        <p><Strong>Adults</Strong>: <span
                                id="adultDis">{{ !empty($bk_json->attributes->occupancy->adults) ? $bk_json->attributes->occupancy->adults : $booking->adults }}</span>
                        </p>
                        <p><Strong>Children</Strong>: <span
                                id="childrenDis">{{ !empty($bk_json->attributes->occupancy->children) ? $bk_json->attributes->occupancy->children : $booking->children }}</span>
                        </p>
                        <p><Strong>Nights</Strong>: <span id="NightDis">{{ $total_nights }}</span></p>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <p class="d-flex align-items-center justify-content-between"><Strong>Per Night
                                Charges</Strong>: <input type="number" step="0.01" class="form-control w-50"
                                name="per_night_price" id="pnChargeDis" onkeyup="addValueToTotal(this.value)"
                                value="{{ isset($booking->amount) ? $booking->amount / $total_nights : 0 }}" disabled></p>
                    </div>
                    <input type="hidden" class="form-control w-50" name="day_difference" id="day_difference" />
                    <hr>
                    <div class="col-md-12">
                        <p class="d-flex align-items-center justify-content-between">
                            <Strong>Discount</Strong>: <input type="number" step="0.01" class="form-control w-50"
                                name="custom_discount" id="pnDiscountDis" onkeyup="addValueToTotal(this.value)"
                                value="{{ $booking->discount }}" disabled>
                        </p>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <p class="d-flex align-items-center justify-content-between"><Strong>Cleaning
                                Fee</Strong>: <input type="number" step="0.01" class="form-control w-50"
                                name="cleaning_fee" id="pnCleaningDis" onkeyup="addValueToTotal(this.value)"
                                value="{{ $booking->cleaning_fee }}" disabled></p>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <p class="d-flex align-items-center justify-content-between"><Strong>Service
                                Fee</Strong>: <input type="number" step="0.01" class="form-control w-50"
                                name="service_fee" id="pnServiceDis" onkeyup="addValueToTotal(this.value)"></p>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <p class="d-flex align-items-center justify-content-between"><Strong>Ota Commission
                                Charges</Strong>: <input type="number" step="0.01" class="form-control w-50"
                                name="ota_commission" value="{{ $booking->ota_commission }}" disabled></p>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <p class="d-flex align-items-center justify-content-between"><Strong>Total
                                Charges</Strong>: <input readonly type="number" step="0.01" class="form-control w-50"
                                name="total_price" id="pnTotalDis" value="{{ $booking->amount }}" disabled>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
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
                    <form action="{{ route('booking.updateOtaBooking', $booking->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            {{-- <label class="form-label">Add Booking References</label> --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="amount_to_be_received">Amount To Be Received
                                        (SAR)</label>
                                    <div class="form-control-wrap">
                                        {{-- {{ dd($booking) }} --}}
                                        <input type="number" class="form-control" id="amount_to_be_received"
                                            onkeyup="calculateForex(this.value)" name="amount_to_be_received"
                                            value="{{ $booking->ota_name == 'BookingCom' ? $booking->amount - $booking->discount - $booking->promotion : $booking->amount + $booking->cleaning_fee - $booking->ota_commission - $booking->discount - $booking->promotion }}"
                                            disabled placeholder="Enter Amount Received">
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
                                            value="{{ $booking->amount_received ?? '' }}"
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
                                    <label class="form-label" for="forex_adjustement">Forex Adjustment (SAR)</label>
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
                                        <select class="form-select select2" name="payment_status" id="payment_status"
                                            data-placeholder="Booking Status">
                                            <option value="payment_unverified" selected
                                                {{ $booking->payment_status == 'payment_unverified' ? 'selected' : '' }}>
                                                Payment Unverified
                                            </option>
                                            <option value="payment_partial"
                                                {{ $booking->payment_status == 'payment_partial' ? 'selected' : '' }}>
                                                Partial Payment
                                            </option>
                                            <option value="payment_complete"
                                                {{ $booking->payment_status == 'payment_complete' ? 'selected' : '' }}>
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
                                                    <input type="text" name="reference_number[]"
                                                        class="form-control mb-2" placeholder="e.g. TXN1234">
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

    </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        var start = moment('{{ $booking->arrival_date }}');
        var end = moment('{{ $booking->departure_date }}');


        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
                'YYYY-MM-DD'));
        });
        let refIndex = 1;

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
