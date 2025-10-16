@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Booking</h3>
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
                                    <a href="{{ route('bookings.ota') }}" class="btn btn-secondary">OTA Bookings</a>
                                    <a href="{{ route('booking-management.create') }}" class="btn btn-icon btn-primary"><em
                                            class="icon ni ni-plus"></em></a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalDefault">Bulk Bookings Import</button>
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
        <form action="{{ route('booking-management.index') }}" method="GET">
            <div class="card-inner">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Filter by Status</label>
                            <select name="booking_status" class="form-select">
                                <option value="">All</option>
                                <option value="confirmed"
                                    {{ isset($_GET['booking_status']) && $_GET['booking_status'] == 'confirmed' ? 'selected' : '' }}>
                                    Confirmed</option>
                                <option value="checkedin"
                                    {{ isset($_GET['booking_status']) && $_GET['booking_status'] == 'checkedin' ? 'selected' : '' }}>
                                    Checkedin</option>
                                <option value="checkedout"
                                    {{ isset($_GET['booking_status']) && $_GET['booking_status'] == 'checkedout' ? 'selected' : '' }}>
                                    Checkedout</option>
                                <option value="upcoming"
                                    {{ isset($_GET['booking_status']) && $_GET['booking_status'] == 'upcoming' ? 'selected' : '' }}>
                                    Upcoming</option>
                                <option value="cancelled"
                                    {{ isset($_GET['booking_status']) && $_GET['booking_status'] == 'cancelled' ? 'selected' : '' }}>
                                    Cancelled</option>

                                <!-- Add more status options as needed -->
                                <!--                                    confirmed-->
                                <!--checkedin-->
                                <!--checkedout-->
                                <!--upcoming-->
                                <!--cancelled-->
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
        <div class="card-inner table-responsive">
            <table class="datatable-init-export table" data-export-title="Export">
                {{-- {{ dd($bookings) }} --}}
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Booking ID</th>
                        <th>Created On</th>
                        <th>Guest</th>
                        <th>Apartment</th>
                        <th>Phone</th>
                        <th>Source</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Payment Method</th>
                        <th>OTA</th>
                        <th>Total</th>
                        <th>Discount</th>
                        <th>Service Fee</th>
                        <th>Ota Commission</th>
                        <th>Cleaning Fee</th>
                        <th>Per Night</th>
                        <th>Status</th>
                        {{-- <th>Action</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $key => $item)
                        @php
                            $listing_json = $item->listing?->listing_json
                                ? json_decode($item->listing->listing_json)
                                : null;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('booking-management.edit', $item->id) }}"
                                    style="background: #6576ff; color: white; padding: 5px; border-radius: 3px;">
                                    {{ $item->id }}
                                </a>
                            </td>
                            <td>{{ $item->created_at->format('Y-m-d h:i:s A') }}</td>
                            <td>{{ $item->guest?->name }} {{ $item->guest?->surname }}</td>
                            <td>{{ $listing_json->title ?? '' }}</td>
                            <td>{{ $item->phone }}</td>
                            <td>{{ $item->booking_sources }}</td>
                            <td>{{ $item->booking_date_start }}</td>
                            <td>{{ $item->booking_date_end }}</td>
                            <td>{{ $item->payment_method === 'cod' ? 'Paid to Host' : $item->payment_method }}</td>
                            <td>{{ $item->ota_name }}</td>
                            <td>{{ $item->total_price }}</td>
                            <td>{{ $item->custom_discount }}</td>
                            <td>{{ $item->service_fee }}</td>
                            <td>{{ $item->ota_commission }}</td>
                            <td>{{ $item->cleaning_fee }}</td>
                            <td>{{ $item->per_night_price }}</td>
                            <td>{{ $item->booking_status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="modalDefault">
        <form action="{{ route('booking.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Bookings</h5>
                        <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <em class="icon ni ni-cross"></em>
                        </a>
                    </div>
                    <div class="modal-body">
                        <input type="file" class="form-control" name="file" accept=".xlsx, .xls" required>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="text-end">
                            <a href="{{ asset('excel_template/booking_template.xlsx') }}" download>Download Sample File</a>
                        </div>
                        <button class="btn btn-danger">Cancel</button>
                        <button class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
