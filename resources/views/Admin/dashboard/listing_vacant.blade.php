@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <a class="btn btn-primary mb-2" href="{{ route('dashboard') }}">back</a>

                <h3 class="nk-block-title page-title">Vacant Listing</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($calender) }} Vacant.</p>
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
        @if (count($calender) === 0 && count($calender) === 0)
            <span>No Vacant Today</span>
        @endif

        <div class="row">

            <div class="col-md-12">

                <hr>
                @foreach ($calender as $item)
                    @php
                        $listing = \App\Models\Listings::where('listing_id', $item->listing_id)->first();
                        if (is_null($listing)) {
                            continue;
                        }

                        $listing_json = json_decode($listing->listing_json);

                    @endphp
                    <a href="{{ route('booking-management.create', ['listing_id' => $listing->id]) }}">
                        <div class="user-card">

                            <div class="user-info">
                                <span class="lead-text">{{ $listing_json->title }}</span>


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
