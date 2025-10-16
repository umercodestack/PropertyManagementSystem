@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Cleaning Management</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($cleanings) }} Cleanings.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <form action="{{ route('cleaning-management.index') }}" method="GET">
                <div class="row justify-content-center align-items-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Select Type</label>
                            <select class="form-control" name="type" id="type" required>
                                <option value="" selected disabled>Select Type</option>
                                <option value="checkin"
                                    {{ isset($_GET['type']) && $_GET['type'] == 'checkin' ? 'selected' : '' }}>Checkin
                                </option>
                                <option value="checkout"
                                    {{ isset($_GET['type']) && $_GET['type'] == 'checkout' ? 'selected' : '' }}>
                                    Checkout</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-check-label" for="daterange">Select Date Range</label>
                        <div class="form-group form-check p-0">
                            <input type="text" class="form-control" required name="daterange" id="daterange"
                                value="{{ isset($_GET['daterange']) ? $_GET['daterange'] : '01/01/2018 - 01/15/2018' }}" />
                        </div>
                    </div>
                    <div class="col-md-4 justify-content-center align-items-center mt-3">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init-export nowrap table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Apartment</th>
                        <th>Cleaner Name</th>
                        <th>Guest Name</th>
                        <th>Guest Phone</th>
                        <th>Keycode</th>
                        <th>Checkin Date</th>
                        <th>Checkout Date</th>
                        <th>Checkout time</th>
                        <th>Next Guest Checkin time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cleanings as $key => $item)
                        <tr>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ ++$key }}</td>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['listing_title'] }}</td>
                                
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['cleaner_Name'] }}</td>
                                
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['guest']->name . ' ' . $item['guest']->surname }}</td>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ isset($item['guest']->phone) ? $item['guest']->phone : '' }}</td>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['key_code'] }}</td>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['checkin'] }}</td>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['checkout'] }}</td>
                            
                              <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['checkouttime'] }}</td>
                                <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['checkintime'] }}</td>    
                                
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                {{ $item['status'] }}
                            </td>
                            <td style="{{ $item['has_checkin'] == 1 ? 'background: green; color: white' : '' }}">
                                <a href="{{ route('cleaning-management.editData') }}?booking_id={{ $item['booking_id'] }}&checkout={{ $item['checkout'] }}&type={{ $item['type'] }}"
                                    class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-pen"></em>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
