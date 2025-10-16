@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Dashboard Overview</h3>
                <div class="nk-block-des text-soft">
                    <!-- <p>Welcome to DashLite Dashboard Template.</p> -->
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-more-v"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li>
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle btn btn-white btn-dim btn-outline-light"
                                        data-bs-toggle="dropdown"><em
                                            class="d-none d-sm-inline icon ni ni-calender-date"></em><span><span
                                                class="d-none d-md-inline">
                                                {{-- {{ isset($_GET['day']) && $_GET['day'] === 'today' ? 'Today' : ' Select Days' }} --}}
                                                @if ((isset($_GET['day']) && $_GET['day'] === 'today') || (isset($_GET['day']) && $_GET['day'] === 'yesterday'))
                                                    {{ $_GET['day'] }}
                                                @else
                                                    Select Days
                                                @endif

                                                {{-- Select Days --}}
                                            </span></span><em class="dd-indc icon ni ni-chevron-right"></em></a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <ul class="link-list-opt no-bdr">
                                            <li><a href="{{ route('dashboard') }}"><span>All</span></a></li>
                                            <li><a href="{{ route('dashboard') }}?day=today"><span>Today</span></a></li>
                                            <li><a href="{{ route('dashboard') }}?day=yesterday"><span>Yesterday</span></a>
                                            </li>
                                            {{-- <li><a href="#"><span>Last 1 Years</span></a></li> --}}
                                        </ul>
                                    </div>
                                </div>
                            </li>


                            <li class="nk-block-tools-opt">
                                {{-- <a href="{{ route('listing.occupancyData') }}"
                                    class="btn btn-primary"><em class="icon ni ni-reports"></em><span>Reports</span></a> --}}
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalDefault"><em
                                        class="icon ni ni-reports"></em><span>Reports</span></button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div><!-- .nk-block-head-content -->
        </div><!-- .nk-block-between -->
    </div><!-- .nk-block-head -->
    <div class="nk-block">
        <div class="row g-gs">
            <div class="col-md-3">
                <div class="card card-bordered card-full">
                    <div class="card-inner">
                        <div class="card-title-group align-start mb-0">
                            <div class="card-title">
                                <h6 class="title">Total Booking</h6>
                            </div>
                            <div class="card-tools">
                                <em class="card-hint icon ni ni-help-fill" data-bs-toggle="tooltip" data-bs-placement="left"
                                    title="Total Booking"></em>
                            </div>
                        </div>
                        <div class="card-amount">
                            <span class="amount"> {{ count($OtaBookings) + count($bookings) }} </span>
                            {{-- <span class="change down text-danger"><em class="icon ni ni-arrow-long-down"></em>1.93%</span> --}}
                        </div>
                        <div class="invest-data">
                            <div class="invest-data-amount g-2">
                                <div class="invest-data-history">
                                    <div class="title">OTA Booking</div>
                                    <div class="amount">{{ count($OtaBookings) }}</div>
                                </div>
                                <div class="invest-data-history">
                                    <div class="title">LivedIn Bookings</div>
                                    <div class="amount">{{ count($bookings) }}</div>
                                </div>
                            </div>
                            {{-- <div class="invest-data-ck">
                                <canvas class="iv-data-chart" id="totalBooking"></canvas>
                            </div> --}}
                        </div>
                    </div>
                </div><!-- .card -->
            </div><!-- .col -->
            <div class="col-md-3">
                <div class="card card-bordered card-full">
                    <div class="card-inner">
                        <div class="card-title-group align-start mb-0">
                            <div class="card-title">
                                <h6 class="title">Rooms Available</h6>
                            </div>
                            <div class="card-tools">
                                <em class="card-hint icon ni ni-help-fill" data-bs-toggle="tooltip" data-bs-placement="left"
                                    title="Total Room"></em>
                            </div>
                        </div>
                        <div class="card-amount">
                            <span class="amount"> {{ count($listings) - count($occupiedRooms) }}</span>
                        </div>
                        <div class="invest-data">
                            <div class="invest-data-amount g-2">
                                <div class="invest-data-history">
                                    <div class="title">Total Rooms</div>
                                    <div class="amount">{{ count($listings) }}</div>
                                </div>
                                <div class="invest-data-history">
                                    <div class="title">Booked</div>
                                    <div class="amount">{{ count($occupiedRooms) }}</div>
                                </div>
                            </div>
                            {{-- <div class="invest-data-ck">
                                <canvas class="iv-data-chart" id="totalRoom"></canvas>
                            </div> --}}
                        </div>
                    </div>
                </div><!-- .card -->
            </div><!-- .col -->
            <div class="col-md-6">
                <div class="card card-bordered  card-full">
                    <div class="card-inner">
                        <div class="card-title-group align-start mb-0">
                            <div class="card-title">
                                <h6 class="title">Revenue</h6>
                            </div>
                            <div class="card-tools">
                                <em class="card-hint icon ni ni-help-fill" data-bs-toggle="tooltip" data-bs-placement="left"
                                    title="Total Expenses"></em>
                            </div>
                        </div>
                        <div class="card-amount">
                            <span class="amount"> {{ $performance['revenue'] }}<span class="currency currency-usd">
                                    SAR</span>
                            </span>
                        </div>
                        <div class="invest-data">
                            <div class="invest-data-amount g-2">
                                <div class="invest-data-history">
                                    <div class="title">Host Earnings</div>
                                    <div class="amount">{{ $performance['my_earning'] }} <span
                                            class="currency currency-usd"> SAR</span></div>
                                </div>
                                <div class="invest-data-history">
                                    <div class="title">Livedin Earnings</div>
                                    <div class="amount">{{ $performance['livedIn'] }} <span class="currency currency-usd">
                                            SAR</span></div>
                                </div>
                                <div class="invest-data-history">

                                    <div class="title">ADR</div>
                                    <div class="amount">
                                        @if (count($occupiedRooms) > 0)
                                            {{ round($performance['revenue'] / count($occupiedRooms), 2) }}
                                        @else
                                            0.00
                                        @endif
                                        <span class="currency currency-usd">SAR</span>
                                    </div>
                                </div>

                                <div class="invest-data-history">
                                    <div class="title">Occupancy</div>
                                    <div class="amount">{{ $performance['occupancy'] }} <span
                                            class="currency currency-usd">%</span></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div><!-- .card -->
            </div><!-- .col -->
            <div class="col-md-6 col-xxl-3">
                <div class="card card-bordered card-full">
                    <div class="card-inner-group">
                        <div class="card-inner">
                            <div class="card-title-group">
                                <div class="card-title">
                                    <h6 class="title">Today Checkins</h6>
                                </div>
                                <div class="card-tools">
                                    <a href="{{ route('listing.listingCheckins') }}" class="link">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-inner card-inner-md">
                            @if (count($chechinsOta) === 0 && count($chechinsLived) === 0)
                                <span>No Checkins Today</span>
                            @endif
                            @foreach ($chechinsOta as $item)
                                @php
                                    $listing = \App\Models\Listings::where('listing_id', $item->listing_id)->first();
                                    if (is_null($listing)) {
                                        continue;
                                    }
                                    $listing_json = json_decode($listing->listing_json);

                                    $booking_json = json_decode($item->booking_otas_json_details);
                                    // dd($booking_json->attributes->customer->name);
                                @endphp
                                <div class="user-card">

                                    <div class="user-info">
                                        <span class="lead-text">{{ substr($listing_json->title, 0, 40) }}...</span>
                                        <span class="sub-text">{{ $booking_json->attributes->customer->name ?? ''}}</span>
                                    </div>

                                </div>
                                <hr class="m-0">
                            @endforeach

                            @foreach ($chechinsLived as $item)
                                @php
                                    $listing = \App\Models\Listings::where('id', $item->listing_id)->first();
                                    $listing_json = json_decode($listing?->listing_json);

                                    $booking_json = json_decode($item->booking_otas_json_details);
                                    // dd($booking_json->attributes->customer->name);
                                @endphp
                                <div class="user-card">
                                    <div class="user-info">
                                        <span class="lead-text"> {{ substr($listing_json?->title, 0, 40) }}...</span>
                                        <span class="sub-text">{{ $item->name }}</span>
                                    </div>
                                    <div class="user-action">
                                        <div class="drodown">
                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger me-n1"
                                                data-bs-toggle="dropdown" aria-expanded="false"><em
                                                    class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <ul class="link-list-opt no-bdr">
                                                    <li><a href="#"><em class="icon ni ni-setting"></em><span>Action
                                                                Settings</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-notify"></em><span>Push
                                                                Notification</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div><!-- .card -->
            </div>
            <div class="col-md-6 col-xxl-3">
                <div class="card card-bordered card-full">
                    <div class="card-inner-group">
                        <div class="card-inner">
                            <div class="card-title-group">
                                <div class="card-title">
                                    <h6 class="title">Today Checkouts</h6>
                                </div>
                                <div class="card-tools">
                                    <a href="{{ route('listing.listingCheckouts') }}" class="link">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-inner card-inner-md">
                            @if (count($chechoutsOta) === 0 && count($chechoutsLived) === 0)
                                <span>No Checkins Today</span>
                            @endif
                            @foreach ($chechoutsOta as $item)
                                @php
                                    $listing = \App\Models\Listings::where('listing_id', $item->listing_id)->first();
                                    if (is_null($listing)) {
                                        continue;
                                    }
                                    $listing_json = json_decode($listing->listing_json);

                                    $booking_json = json_decode($item->booking_otas_json_details);
                                    // dd($booking_json->attributes->customer->name);
                                @endphp
                                <div class="user-card">

                                    <div class="user-info">
                                        <span class="lead-text">{{ substr($listing_json->title, 0, 40) }}...</span>
                                        <span class="sub-text">{{ $booking_json->attributes->customer->name ?? '' }}</span>
                                    </div>
                                </div>
                                <hr class="m-0">
                            @endforeach
                            @foreach ($chechoutsLived as $item)
                                @php
                                    $listing = \App\Models\Listings::where('id', $item->listing_id)->first();
                                    $listing_json = json_decode($listing->listing_json);

                                    $booking_json = json_decode($item->booking_otas_json_details);
                                    // dd($booking_json->attributes->customer->name);
                                @endphp
                                <div class="user-card">

                                    <div class="user-info">
                                        <span class="lead-text"> {{ substr($listing_json->title, 0, 40) }}...</span>
                                        <span class="sub-text">{{ $item->name }}</span>
                                    </div>
                                    <div class="user-action">
                                        <div class="drodown">
                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger me-n1"
                                                data-bs-toggle="dropdown" aria-expanded="false"><em
                                                    class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <ul class="link-list-opt no-bdr">
                                                    <li><a href="#"><em class="icon ni ni-setting"></em><span>Action
                                                                Settings</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-notify"></em><span>Push
                                                                Notification</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach


                        </div>
                    </div>
                </div><!-- .card -->
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="card card-bordered card-full ">
                    <div class="card-inner-group">
                        <div class="card-inner">
                            <div class="card-title-group">
                                <div class="card-title">
                                    <h6 class="title">Vacant Listing</h6>
                                </div>
                                <div class="card-tools">
                                    <a href="{{ route('listing.listingVacant') }}" class="link">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-inner card-inner-md vacant_wrapper">
                            @if (count($calender) === 0 && count($calender) === 0)
                                <span>No Vacant Today</span>
                            @endif
                            @foreach ($calender as $item)
                                @php
                                    $listing = \App\Models\Listings::where('id', $item->listing_id)
                                        ->orWhere('listing_id', $item->listing_id)
                                        ->first();
                                    if (is_null($listing)) {
                                        continue;
                                    }
                                    $listing_json = json_decode($listing->listing_json);

                                @endphp
                                <div class="user-card">

                                    <div class="user-info">
                                        <span class="lead-text">{{ substr($listing_json->title, 0, 40) }}...</span>

                                    </div>

                                </div>
                                <hr class="m-0">
                            @endforeach


                        </div>


                    </div>
                </div><!-- .card -->
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="card card-bordered card-full">
                    <div class="card-inner-group">
                        <div class="card-inner">
                            <div class="card-title-group">
                                <div class="card-title">
                                    <h6 class="title">Today's Cleaning's</h6>
                                </div>
                                <div class="card-tools">
                                    <a href="{{ route('listing.todaycleaning') }}" class="link">View All</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-inner card-inner-md cleaning_wrapper">
                            @if (count($todayscleanings) === 0 && count($todayscleanings) === 0)
                                <span>No Cleaning Today</span>
                            @endif

                            @php

                                $randomCleanings = collect($todayscleanings)->shuffle()->take(50);
                                $sortedCleanings = $randomCleanings->sortByDesc(function ($item) {
                                    return $item['status'] === 'pending' ? 1 : 0;
                                });

                            @endphp

                            @foreach ($sortedCleanings as $item)
                                @if ($item['status'] == 'pending')
                                    <div class="user-card badge badge-dim bg-warning"
                                        style="border-radius: 50px;
    margin: 5px 0px;">
                                        <div class="user-info">
                                            <span class="lead-text">
                                                {{ substr($item['listing_title'], 0, 40) }}
                                                <strong style="margin-left: 5px">Status:</strong> {{ $item['status'] }}
                                                ...
                                            </span>
                                        </div>
                                    </div>
                                    <hr class="m-0">
                                @else
                                    <div class="user-card badge badge-dim bg-success"
                                        style="border-radius: 50px;
    margin: 5px 0px;">
                                        <div class="user-info">
                                            <span class="lead-text">
                                                {{ substr($item['listing_title'], 0, 40) }}
                                                <strong style="margin-left: 5px">Status:</strong> {{ $item['status'] }}
                                                (Processed)
                                            </span>
                                        </div>
                                    </div>
                                    <hr class="m-0">
                                @endif
                            @endforeach


                        </div>


                    </div>
                </div><!-- .card -->
            </div>



            <!-- <div class="col-xxl-6" >
                                                                                <div class="card card-bordered card-full">
                                                                                    <div class="card-inner d-flex flex-column h-100">
                                                                                        <div class="card-title-group mb-3">
                                                                                            <div class="card-title me-1">
                                                                                                <h6 class="title">Apartment Occupancies</h6>
                                                                                                <p>This is the overall occupancies.</p>
                                                                                            </div>
                                                                                            <div class="card-tools">
                                                                                                <a href="{{ route('listing.listingOccupancie') }}" class="link">View All</a>
                                                                                            </div>

                                                                                        </div>
                                                                                        <div class="progress-list gy-3">
                                                                                            @foreach ($occupancies as $key => $items)
    {{-- {{ dd($items['title']) }} --}}
                                                                                                <div class="progress-wrap">
                                                                                                    <div class="progress-text">
                                                                                                        <div class="progress-label">{{ substr($items['title'], 0, 100) }}</div>
                                                                                                        <div class="progress-amount">{{ $items['occupancy'] }}</div>
                                                                                                    </div>
                                                                                                    <div class="progress progress-md">
                                                                                                        <div class="progress-bar bg-teal" data-progress="{{ $items['occupancy'] }}">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
    @endforeach
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div> .col -->
        </div><!-- .row -->
    </div><!-- .nk-block -->
    <!-- Modal Content Code -->
    <div class="modal fade" tabindex="-1" id="modalDefault">
        <div class="modal-dialog modal-dialog-top" role="document">
            <div class="modal-content">
                <form action="{{ route('listing.occupancyData') }}" method="GET">
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                    <div class="modal-header">
                        <h5 class="modal-title">Revenue Report</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="month">Report Month</label>
                                    <input type="month" class="form-control" name="month" id="month" required>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="report_type">Report Type</label>
                                <select name="report_type" id="report_type" class="form-control" required>
                                    <option value="" disabled selected>Select Report Type</option>
                                    <option value="gross_revenue">Gross Revenue</option>
                                    <option value="net_revenue">Net Revenue</option>
                                    <option value="occupancy">Occupancy</option>
                                    <option value="cancelled_bookings">Cancelled Bookings</option>
                                    <option value="cancelled_bookings_new_bookings">Cancelled Bookings + New Bookings
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">

                        <a id="downloadReportLink" target="_blank"
                            href="{{ route('listing.otaOccupancyData', ['month' => '2024-11']) }}"
                            class="btn btn-success text-black">
                            OTA Report
                        </a> &nbsp;&nbsp;&nbsp;&nbsp;

                        <button type="submit" class="btn btn-success text-black">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const downloadReportLink = document.getElementById('downloadReportLink');
        const month = document.getElementById('month');

        month.addEventListener('change', function() {
            // Get the selected month value
            const selectedMonth = month.value;

            // Update the href of the link with the new month
            downloadReportLink.href = '{{ route('listing.otaOccupancyData', ['month' => '__month__']) }}'.replace(
                '__month__', selectedMonth);
        });
    </script>
@endsection
