@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Reply Review</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                        @php
                            $review_json = json_decode($review->review_json);
                            // dd($review_json);
                            $listing_id = $review_json->meta->listing_id;
                            $listing = \App\Models\Listings::where('listing_id',$listing_id)->first();
                            $booking = \App\Models\BookingOtasDetails::where('listing_id',$listing_id)->first();
                            $listing_json = json_decode($listing->listing_json);
                            $guest_name = $review_json->guest_name;
                            // dd($review_json,$guest_name)
                        @endphp
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="name">Guest Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$guest_name}}" placeholder="Guest Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="name">Apartment Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$listing_json->title}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">Booking ID</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$booking->id}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">OTA</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->ota}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">Overall Score</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->overall_score}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">Received At</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->received_at}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-label" for="name">Content</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->content}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="card card-bordered mt-3">
            <div class="card-inner">
                <ul>
                    @foreach ($replies as $item)
                    @php
                        $user = \App\Models\User::whereId($item->reply_by)->first();
                    @endphp
                        <li>
                            <div class="row">
                                <div class="col-md-4">
                                    <span>{{ $item->content }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="line-height-0">UpdatedBy: {{ $user->name}}</span>
                                    <span class="line-height-0">Reply At{{ $item->created_at }}</span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <form action="{{route('review-management.update', $review->id)}}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <textarea name="content" class="form-control" name="content" id="" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Guest Review --}}

    </div>
@endsection
