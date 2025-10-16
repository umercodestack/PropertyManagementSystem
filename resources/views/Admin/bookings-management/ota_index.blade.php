@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Otas Booking</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($bookings) }} Booking.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{ route('booking-management.index') }}" class="btn btn-secondary">LivedIn
                                        Bookings</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Status Filter -->
    <div class="card card-bordered mb-3">
        <form action="{{ route('bookings.ota') }}" method="GET">
            <div class="card-inner">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Filter by Status</label>
                            <select name="system_status" class="form-select">
                                <option value="">All</option>
                                <option value="confirmed"
                                    {{ isset($_GET['system_status']) && $_GET['system_status'] == 'confirmed' ? 'selected' : '' }}>
                                    Confirmed</option>
                                <option value="checkedin"
                                    {{ isset($_GET['system_status']) && $_GET['system_status'] == 'checkedin' ? 'selected' : '' }}>
                                    Checkedin</option>
                                <option value="checkedout"
                                    {{ isset($_GET['system_status']) && $_GET['system_status'] == 'checkedout' ? 'selected' : '' }}>
                                    Checkedout</option>
                                <option value="upcoming"
                                    {{ isset($_GET['system_status']) && $_GET['system_status'] == 'upcoming' ? 'selected' : '' }}>
                                    Upcoming</option>
                                <option value="cancelled"
                                    {{ isset($_GET['system_status']) && $_GET['system_status'] == 'cancelled' ? 'selected' : '' }}>
                                    Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mt-4">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init-export table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Created On</th>
                        <th>Booking ID</th>
                        <th>Source</th>
                        <th>Confirmation Code</th>
                        <th>Guest</th>
                        <th>Apartment</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Cleaning Fee</th>
                        <th>Discount</th>
                        <th>Promotion</th>
                        <th>Ota Commission</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $key => $item)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d h:i:s A') }}</td>
                            <td><a href="{{ route('booking.editOtaBooking', $item->id) }}"
                                    style="background: #6576ff; color: white; padding: 5px; border-radius: 3px; ">{{ $item['id'] }}</a>
                            </td>
                            <td>{{ $item->ota_name }} </td>
                            <td>{{ $item->unique_id }}</td>
                            <td>{{ $item->guest_name }}</td>
                            <td>
                                @php
                                    $title = '';
                                    if (
                                        $listing = \App\Models\Listing::where(
                                            $item->ota_name == 'Almosafer' ? 'id' : 'listing_id',
                                            $item->listing_id
                                        )->first()
                                    ) {
                                        if (
                                            $listingRelation = \App\Models\ListingRelation::where(
                                                'listing_id_other_ota',
                                                $listing->id,
                                            )->first()
                                        ) {
                                            $listing = \App\Models\Listing::find($listingRelation->listing_id_airbnb);
                                        }
                                        $title =
                                            $listing && $listing->listing_json
                                                ? json_decode($listing->listing_json, true)['title'] ?? ''
                                                : '';
                                    }
                                    $short_title = $title
                                        ? implode(' - ', array_slice(explode('-', $title, 3), 0, 2))
                                        : '';
                                @endphp
                                {{ $short_title }}
                            </td>
                            <td>{{ $item->guest_email }}</td>
                            <td>{{ $item->guest_phone }}</td>
                            <td>{{ $item->cleaning_fee }}</td>
                            <td>{{ $item->discount }}</td>
                            <td>{{ $item->promotion }}</td>
                            <td>{{ $item->ota_commission }}</td>
                            <td>{{ $item->amount }}</td>
                            <td>{{ $item->arrival_date }}</td>
                            <td>{{ $item->departure_date }}</td>
                            <td>{{ $item->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
