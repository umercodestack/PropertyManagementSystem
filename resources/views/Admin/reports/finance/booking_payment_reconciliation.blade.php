@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Payment Reconciliation</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ $bookings->count() }} Booking.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <form action="{{ route('finance.booking.payment.reconciliation') }}" method="GET">
                <div class="row justify-content-center align-items-center">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">Select Type</label>
                            <select class="form-control" name="type" id="type">
                                <option value="" selected disabled>Select Type</option>
                                <option value="created_at" {{ $filters['type'] == 'created_at' ? 'selected' : '' }}>
                                    Created At
                                </option>
                                <option value="checkin" {{ $filters['type'] == 'checkin' ? 'selected' : '' }}>
                                    Checkin
                                </option>
                                <option value="checkout" {{ $filters['type'] == 'checkout' ? 'selected' : '' }}>
                                    Checkout
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-check-label" for="daterange">Select Date Range</label>
                        <div class="form-group form-check p-0">
                            <input type="text" class="form-control" required name="daterange" id="daterange"
                                value="{{ $filters['daterange'] ?? '01/01/2018 - 01/15/2018' }}" />
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="payment_status">Payment Status</label>
                            <select class="form-select" name="payment_status" id="payment_status">
                                <option value="" {{ empty($filters['payment_status']) ? 'selected' : '' }}>
                                    All Payment Status
                                </option>
                                <option value="payment_unverified"
                                    {{ $filters['payment_status'] == 'payment_unverified' ? 'selected' : '' }}>
                                    Payment Unverified
                                </option>
                                <option value="payment_partial"
                                    {{ $filters['payment_status'] == 'payment_partial' ? 'selected' : '' }}>
                                    Partial Payment
                                </option>
                                <option value="payment_complete"
                                    {{ $filters['payment_status'] == 'payment_complete' ? 'selected' : '' }}>
                                    Payment Complete
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="booking_source">Booking Source</label>
                            <select class="form-control" name="booking_source" id="booking_source">
                                <option value="">All Sources</option>
                                @foreach ($allBookingSources as $source)
                                    <option value="{{ $source }}"
                                        {{ $filters['booking_source'] == $source ? 'selected' : '' }}>
                                        {{ ucwords($source) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 justify-content-center align-items-center mt-3">
                        <button class="btn btn-primary btn-block">Filter Results</button>
                        <a href="{{ route('finance.booking.payment.reconciliation') }}"
                            class="btn btn-secondary btn-block mt-1">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards - More Efficient Layout -->
    <div class="row mt-4">
        @php
            $statusConfig = [
                'payment_unverified' => ['class' => 'bg-danger', 'icon' => 'alert-circle'],
                'payment_partial' => ['class' => 'bg-warning', 'icon' => 'clock'],
                'payment_complete' => ['class' => 'bg-success', 'icon' => 'check-circle'],
            ];
        @endphp

        @foreach ($paymentStatusCounts as $status => $count)
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="mb-1">
                            <span
                                class="badge {{ $statusConfig[$status]['class'] ?? 'bg-secondary' }} fs-4">{{ $count }}</span>
                        </h3>
                        <p class="text-muted mb-0">{{ ucwords(str_replace('_', ' ', $status)) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Financial Summary -->
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <h5 class="mb-4">Financial Summary</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($totals['total_amount'], 2) }}</h4>
                            <p class="mb-0">Total Amount</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($totals['total_amount_to_be_received'], 2) }}</h4>
                            <p class="mb-0">To Be Received</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($totals['total_amount_received'], 2) }}</h4>
                            <p class="mb-0">Amount Received</p>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ number_format($totals['total_discount'], 2) }}</h4>
                            <p class="mb-0">Total Discount</p>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Optimized Data Table -->
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <div class="table-responsive">
                <table class="datatable-init-export table table-striped" id="bookingsTable">
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
                            <th>Total</th>
                            <th>Amount To Be Received</th>
                            <th>Amount Received</th>
                            <th>Forex Adjustment</th>
                            <th>Payment Status</th>
                            <th>Discount</th>
                            <th>OTA Commission</th>
                            <th>Cleaning Fee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $item)
                            <tr>
                                <td>{{ $item['s_no'] }}</td>
                                <td>
                                    <a href="{{ $item['route'] }}" class="badge bg-primary">
                                        {{ $item['booking_id'] }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item['created_on'])->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $item['guest'] }}</td>
                                <td>{{ $item['listing_title'] }}</td>
                                <td>{{ $item['phone'] }}</td>
                                <td>{{ ucfirst($item['source']) }}</td>
                                <td>{{ $item['start_date'] }}</td>
                                <td>{{ $item['end_date'] }}</td>
                                <td>{{ strtolower($item['payment_method']) }}</td>
                                <td>{{ number_format($item['amount'], 2) }}</td>
                                <td>{{ number_format($item['amount_to_be_received'], 2) }}</td>
                                <td>{{ number_format($item['amount_received'], 2) }}</td>
                                <td>{{ number_format($item['forex_adjustement'], 2) }}</td>
                                <td>
                                    <span
                                        class="badge {{ $statusConfig[$item['payment_status']]['class'] ?? 'bg-secondary' }}">
                                        {{ ucwords(str_replace('_', ' ', $item['payment_status'])) }}
                                    </span>
                                </td>
                                <td>{{ number_format($item['discount'], 2) }}</td>
                                <td>{{ number_format($item['ota_commission'], 2) }}</td>
                                <td>{{ number_format($item['cleaning_fee'], 2) }}</td>
                                <td>{{ ucfirst($item['status']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/bundle.js?ver=3.2.3') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get the CSRF token from the meta tag

        let daterange = '{{ isset($_GET['daterange']) && $_GET['daterange'] ? $_GET['daterange'] : 0 }}';
        console.log(daterange);
        let startDate = null
        let endDate = null
        if (daterange && daterange !== "0") {
            let dates = daterange.split(' - '); // Split the string by ' - '
            startDate = dates[0]; // Start date is the first part
            endDate = dates[1]; // End date is the second part

            // console.log("Start Date:", startDate);
            // console.log("End Date:", endDate);
        } else {
            console.log("Date range is not provided or is invalid.");
        }
        console.log("Start Date:", startDate);
        console.log("End Date:", endDate);
        var start = startDate != null ? startDate : moment().subtract(29, 'days');
        var end = endDate != null ? endDate : moment();

        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
                'YYYY-MM-DD'));
        });
    </script>
@endsection
