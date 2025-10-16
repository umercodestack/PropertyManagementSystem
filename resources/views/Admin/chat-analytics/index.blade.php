@extends('Admin.layouts.app')
@section('content')


<div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Customer Service Report</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($results) }} threads in the report.</p>
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
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-bordered text-center">
            <div class="card-inner">
                <h6>Total Threads</h6>
                <h4>{{ $totalThreads }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-bordered text-center">
            <div class="card-inner">
                <h6>Total Inquiries</h6>
                <h4>{{ $totalInquiries }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-bordered text-center">
            <div class="card-inner">
                <h6>Total Confirm Bookings</h6>
                <h4>{{ $totalConfirmedBookings }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-bordered text-center">
            <div class="card-inner">
                <h6>Total No of Nights</h6>
                <h4>{{ $totalNightsSum }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-bordered text-center">
            <div class="card-inner">
                <h6>Average First Response Time</h6>
                <h4>{{ $avgFirstResponseTime }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-bordered text-center">
            <div class="card-inner">
                <h6>Average Total Handling Time</h6>
                <h4>{{ $avgTotalHandlingTime }}</h4>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Date Filter Form --}}
<div class="card card-bordered card-preview">
<div class="card card-bordered mb-4">
    <div class="card-inner">
        <form method="GET" action="{{ route('chatanalytics.index') }}" class="row g-3">
            <div class="col-md-6">
                <label for="agent" class="form-label">Agent</label>
        <select name="agent" id="agent" class="form-control select2">
            <option value="">All Agents</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('agent') == $user->name ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
            </div>
            <div class="col-md-6">
                 <label for="listing" class="form-label">Listing</label>
        <select name="listing" id="listing" class="form-control select2">
            <option value="">All Listings</option>
            @foreach($listings as $listing)
                <option value="{{ $listing->listing_id }}" {{ request('listing') == $listing->title ? 'selected' : '' }}>
                    {{ $listing->title }}
                </option>
            @endforeach
        </select>
            </div>
            

            <div class="col-md-3 ">
                    <div class="form-group">
                        <label class="form-label" for="chatdaterange">Date</label>
                        <input type="text" class="form-control" name="daterange" id="daterange" value="01/01/2018 - 01/15/2018"  autocomplete="off" required />
                        
                    
                        
                        @error('chatdaterange')
                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                          @enderror
                            <p id="error-msg" class="text-danger mt-2"></p>
                            <p id="error-msgdt" class="text-danger mt-2"></p>
                            
                    </div>
            </div>

                 <input type="hidden" name="from_date" id="from_date" value="{{ $fromDate }}">
                         <input type="hidden" name="to_date" id="to_date" value="{{ $toDate }}">

            <div class="col-md-3 d-flex align-items-end mb-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('chatanalytics.index') }}" class="btn btn-light ms-2">Reset</a>
            </div>
        </form>
    </div>
</div>
</div>
<div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table" data-export-title="Export">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Thread ID</th>
                    <th>LP Code</th>
                    <th>Thread Name</th>
                    <th>Status</th>
                    <th>System Status</th>
                    <th>Agent Name</th>
                    <th>Guest First Message</th>
                    <th>Property First Response</th>
                    <th>Response Seconds</th>
                    <th>Response Time (HH:MM:SS)</th>
                    <th>Total Handling Seconds</th>
                    <th>Total Handling Time (HH:MM:SS)</th>
                    <th>Arrival Date</th>
                    <th>Departure Date</th>
                    <th>Total Nights</th>
                    <th>Response Time Category</th>
                    <th>Title</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $key => $row)
                    <tr>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ ++$key }}</td>
                       <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white;' : '' }}">
    <a href="{{ route('communication-management.index', ['thread_id' => $row->thread_id]) }}" 
       style="background: #007bff; color: white; padding: 5px 10px; border-radius: 4px; display: inline-block; text-decoration: none;"
       target="_blank" 
       rel="noopener noreferrer">
        {{ $row->thread_id }}
    </a>
</td>

                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{$row->lp_code}}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->thread_name }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->status }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->system_status }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->agent_name }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->guest_first_message }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->property_first_response }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->response_seconds }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->response_time_hh_mm_ss }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->total_handling_seconds }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->total_handling_time_hh_mm_ss }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->arrival_date }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->departure_date }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->total_nights }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->response_time_category }}</td>
                        <td style="{{ strtolower(trim($row->status)) == 'booking confirm' ? 'background: green; color: white' : '' }}">{{ $row->title }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>


var start = moment("{{ $fromDate }}");
var end = moment("{{ $toDate }}");

function updateHiddenFields(start, end) {
    $('#from_date').val(start.format('YYYY-MM-DD'));
    $('#to_date').val(end.format('YYYY-MM-DD'));
    $('input[name="daterange"]').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
}


        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
        
        }, function(start, end) {
            updateHiddenFields(start, end);
        });



updateHiddenFields(start, end);


</script>    
 

@endsection
