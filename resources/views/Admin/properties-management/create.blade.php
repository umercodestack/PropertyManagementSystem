@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Property</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('property-management.store')}}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Property Title</label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Property Title">
                                    @error('title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
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

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="group_id">Group</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="group_id" id="group_id" data-placeholder="Select Group">
                                            <option value="" selected disabled>Select Group</option>
                                            @foreach($groups as $items)
                                                <option value="{{$items->id}}">{{$items->group_name}}</option>
                                            @endforeach
                                        </select>
                                        @error('group_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="currency">Currency</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="currency" id="currency" data-placeholder="Select Currency">
                                            <option value="" selected disabled>Select Currency</option>
                                            <option value="AED">AED</option>
                                            <option value="USD">USD</option>
                                            <option value="GBP">GBP</option>
                                            <option value="PKR">PKR</option>
                                        </select>
                                        @error('currency')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                                    @error('email')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="number" class="form-control" id="phone" name="phone" placeholder="Phone">
                                    @error('phone')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="zip_code">Zip Code</label>
                                    <input type="number" class="form-control" id="zip_code" name="zip_code" placeholder="Zip Code">
                                    @error('zip_code')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="country">Country</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="country" id="country" data-placeholder="Select Country">
                                            <option value="" selected disabled>Select Country</option>
                                            <option value="PK">Pakistan</option>
                                            <option value="SA">Saudi Arabia</option>
                                            <option value="DB">Dubai</option>
                                            <option value="IR">Iran</option>
                                        </select>
                                        @error('country')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="state">State</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="state" id="state" data-placeholder="Select State">
                                            <option value="" selected disabled>Select State</option>
                                            <option value="Sindh">Sindh</option>
                                            <option value="Punjab">Punjab</option>
                                            <option value="Keyber">Keyber</option>
                                            <option value="Balochistan">Balochistan</option>
                                        </select>
                                        @error('state')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="city">City</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="city" id="city" data-placeholder="Select City">
                                            <option value="" selected disabled>Select City</option>
                                            <option value="Karachi">Karachi</option>
                                            <option value="Peshawar">Peshawar</option>
                                            <option value="Lahore">Lahore</option>
                                            <option value="Quetta">Quetta</option>
                                        </select>
                                        @error('city')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="address">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Zip Address">
                                    @error('address')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Longitude</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" placeholder="Zip Longitude">
                                    @error('longitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="latitude">Latitude</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" placeholder="Zip Latitude">
                                    @error('latitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="timezone">Time Zone</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="timezone" id="timezone" data-placeholder="Select Time Zone">
                                            <option value="" selected disabled>Select Time Zone</option>
                                            <option value="Europe/London">Europe/London</option>
                                        </select>
                                        @error('timezone')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_type">Property Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="property_type" id="property_type" data-placeholder="Select Property Type">
                                            <option value="" selected disabled>Select Property Type</option>
                                            <option value="Apartment">Apartment</option>
                                            <option value="Flat">Flat</option>
                                            <option value="Hotel">Hotel</option>
                                        </select>
                                        @error('property_type')
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
