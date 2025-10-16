@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Reviews</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($reviews)}} Reviews.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table" data-export-title="Export">
            <thead>
            <tr>
                <th>S.No</th>
                <th>Guest Name</th>
                <th>Listing</th>
                <th>Booking ID</th>
                <th>Code</th>
                <th>OTA</th>
                <th>Overall Score</th>
                <th>Clean</th>
                <th>Accuracy</th>
                <th>Checkin</th>
                <th>Communication</th>
                <th>Location</th>
                <th>Value</th>
                <th>Content</th>
                <th>Create At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($reviews as $key=>$item)
            {{-- {{ dd($item) }} --}}
            @php
                if($item->ota_name == 'BookingCom') {
                    continue;
                }
                $review_json = json_decode($item->review_json);
                $listing_id = $review_json->meta->listing_id;
                $listing = \App\Models\Listings::where('listing_id',$listing_id)->first();
                $booking = \App\Models\BookingOtasDetails::where('listing_id',$listing_id)->first();
                $ota_key = '';
                if(isset($booking->booking_otas_json_details)) {
                    $booking_json = json_decode($booking->booking_otas_json_details);
                    $ota_key = $booking_json->attributes->ota_reservation_code;
                }
                if($listing == null) {
                    continue;
                }
                $clean = 0;
                $accuracy = 0;
                $checkin = 0;
                $communication = 0;
                $location = 0;
                $value = 0;
                $listing_json = json_decode($listing->listing_json);
                $guest_name = $review_json->guest_name;
                if(!is_null($review_json->scores) && $review_json->scores !=[]) {
                    $clean = $review_json->scores[0]->score / 2;
                    $accuracy = $review_json->scores[1]->score/ 2;
                    $checkin = $review_json->scores[2]->score/ 2;
                    $communication = $review_json->scores[3]->score/ 2;
                    $location = $review_json->scores[4]->score/ 2;
                    $value = $review_json->scores[5]->score/ 2;
                }
            @endphp
                <tr>
                    <td>{{++$key}}</td>
                    <td>{{ $guest_name }}</td>
                    <td>{{$listing_json->title}}</td>
                    <td>{{isset($booking->id) ? $booking->id : ''}}</td>
                    <td>{{$ota_key}}</td>
                    <td>{{$review_json->ota}}</td>
                    <td>{{$review_json->overall_score / 2}}</td>
                    <td>{{ $clean }}</td>
                    <td>{{ $accuracy }}</td>
                    <td>{{ $checkin }}</td>
                    <td>{{ $communication }}</td>
                    <td>{{ $location }}</td>
                    <td>{{ $value }}</td>
                    <td>{{$review_json->content}}</td>
                    <td>{{$review_json->received_at}}</td>
                    <td>
                        <a href="{{route('review-management.edit', $item->id)}}" class="btn btn-primary btn-sm">
                            <em class="icon ni ni-pen"></em>
                        </a>

                        <a href="{{route('guestReview.create', $item->id)}}" class="btn btn-primary btn-sm">
                            <em class="icon ni ni-user"></em>
                        </a>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
