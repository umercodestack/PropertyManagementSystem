@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <a class="btn btn-primary mb-2" href="{{ route('dashboard') }}">back</a>

                <h3 class="nk-block-title page-title">Today's Cleanings</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($todayscleanings)}} Today's Cleanings.</p>
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
    @if (count($todayscleanings) === 0 && count($todayscleanings) === 0)
            <span>No Cleaning Today</span>
        @endif 
       
        <div class="row">

        <div class="col-md-12">
                
                <hr>
                @foreach ($todayscleanings as $item)
                   
                    <a href="{{ route('cleaning-management.editData') }}?booking_id={{ $item['booking_id'] }}&checkout={{ $item['checkout'] }}&type={{ $item['type'] }}">
                        <div class="user-card">

                            <div class="user-info">
                                <span class="lead-text">{{ $item['listing_title'] }}</span>
                             
                                <span class="sub-text"><strong>Status</strong>:
                                {{ $item['status'] }}</span>

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
