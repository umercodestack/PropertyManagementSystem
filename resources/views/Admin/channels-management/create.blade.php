@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Channel</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('channel-management.store')}}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="user_id" id="user_id" data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach($users as $items)
                                                <option value="{{$items->id}}">{{$items->name}} {{$items->surname}}</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="ch_channel_id">Channel ID</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" name="ch_channel_id">
                                        @error('ch_channel_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="connection_type">Channel Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="connection_type" id="connection_type" data-placeholder="Select Connection Type">
                                            <option value="" selected disabled>Select Channel Type</option>
                                            <option value="Airbnb">Airbnb</option>
                                            <option value="BCom">Booking.com</option>
                                            <option value="Vrbo">Vrbo</option>
                                        </select>
                                        @error('user_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
