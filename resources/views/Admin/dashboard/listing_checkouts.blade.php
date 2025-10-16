@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <a class="btn btn-primary mb-2" href="{{ route('dashboard') }}">back</a>
                <h3 class="nk-block-title page-title">Apartment Checkouts</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($chechoutsOta) + count($chechoutsLived) }} Checkouts.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-inner card-inner-md">
        @if (count($chechoutsOta) === 0 && count($chechoutsLived) === 0)
            <span>No Checkins Today</span>
        @endif
        <div class="row">
            <div class="col-md-6">
                <h4>Ota Booking</h4>

                @foreach ($chechoutsOta as $item)
                    @php
                        $listing = \App\Models\Listings::where('listing_id', $item->listing_id)->first();
                        $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;

                        $booking_json = json_decode($item->booking_otas_json_details);
                        // dd($booking_json->attributes->customer->name);
                    @endphp
                    <a href="{{ route('booking.editOtaBooking', $item->id) }}">
                        <div class="user-card">

                            <div class="user-info">
                                <span class="lead-text">{{ $listing_json->title ?? '' }}</span>
                                <span class="sub-text"><strong>Name</strong>:
                                    {{ $booking_json->attributes->customer->name }} ||
                                    &nbsp;<strong>Date</strong>: {{ $item->departure_date }}</span>
                            </div>

                        </div>
                    </a>

                    <hr class="m-0 mt-2 p-1">
                @endforeach
            </div>
            <div class="col-md-6">
                <h4>Livedin Booking</h4>

                @foreach ($chechoutsLived as $item)
                    @php
                        $listing = \App\Models\Listings::where('id', $item->listing_id)->first();
                        $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;

                        $booking_json = json_decode($item->booking_otas_json_details);
                        // dd($booking_json->attributes->customer->name);
                    @endphp
                    <a href="{{ route('booking-management.edit', $item->id) }}" target="_blank">
                        <div class="user-card">
                            <div class="user-info">
                                <span class="lead-text"> {{ $listing_json->title ?? '' }}</span>
                                <span class="sub-text"><strong>Name</strong>: {{ $item->name }} ||
                                    &nbsp;<strong>Date</strong>:
                                    {{ $item->booking_date_end }}</span>
                            </div>
                        </div>
                    </a>
                    <hr class="m-0 mt-2 p-1">
                @endforeach
            </div>
        </div>
    </div>
    </div>
@endsection
