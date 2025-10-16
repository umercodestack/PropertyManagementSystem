@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Vendor</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('vendor-management.store')}}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Vendor Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Vendor Title">
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="location">Address</label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="Address">
                                    @error('location')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="occupation">Occupation</label>
                                    <input type="text" class="form-control" id="occupation" name="occupation" placeholder="Occupation">
                                    @error('occupation')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="availability">Availability</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="availability" id="availability" data-placeholder="Select Availability">
                                            <option value="" selected disabled>Select Currency</option>
                                            <option value="yes">Available</option>
                                            <option value="no">Not Available</option>
                                        </select>
                                        @error('availability')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="last_hired">Last Hired</label>
                                    <input type="date" class="form-control" id="last_hired" name="last_hired" placeholder="Last Hired">
                                    @error('last_hired')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="time_duration">Time Duration</label>
                                    <input type="time" class="form-control" id="time_duration" name="time_duration" placeholder="Time Duration">
                                    @error('time_duration')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="service_id">Service</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="service_id" id="service_id" data-placeholder="Select Service">
                                            <option value="" selected disabled>Select Service</option>
                                            @foreach($services as $item)
                                                <option value="{{$item->id}}">{{$item->service_name}}</option>
                                            @endforeach
                                        </select>
                                        @error('service_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount">
                                    @error('amount')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="currency">Currency</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="currency" id="currency" data-placeholder="Select Currency">
                                            <option value="" selected disabled>Select Currency</option>
                                            <option value="gbp">GBP</option>
                                            <option value="pkr">PKR</option>
                                        </select>
                                        @error('currency')
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
