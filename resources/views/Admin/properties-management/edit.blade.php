@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Property</h3>
                </div>
            </div>
        </div>
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('property-management.update', $property->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Property Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{$property->title}}" placeholder="Property Title">
                                    @error('title')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="user_id[]" id="user_id" data-placeholder="Select Host" multiple>
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach($users as $items)
                                                <option value="{{$items->id}}" {{$property->user_id === $items->id ? 'selected' : '' }} >{{$items->name}} {{$items->surname}}</option>
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
                                        <select class="form-select" name="group_id" id="group_id" data-placeholder="Select Group">
                                            <option value="" selected disabled>Select Group</option>
                                            @foreach($groups as $items)
                                                <option value="{{$items->id}}" {{$property->group_id === $items->id ? 'selected' : '' }}>{{$items->group_name}}</option>
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
                                            <option value="AED" {{$property->currency === 'AED' ? 'selected' : '' }}>AED</option>
                                            <option value="USD" {{$property->currency === 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="GBP" {{$property->currency === 'GBP' ? 'selected' : '' }}>GBP</option>
                                            <option value="PKR" {{$property->currency === 'PKR' ? 'selected' : '' }}>PKR</option>
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
                                    <input type="email" class="form-control" id="email" name="email" value="{{$property->email}}" placeholder="Email">
                                    @error('email')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="number" class="form-control" id="phone" name="phone" value="{{$property->phone}}" placeholder="Phone">
                                    @error('phone')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="zip_code">Zip Code</label>
                                    <input type="number" class="form-control" id="zip_code" name="zip_code" value="{{$property->zip_code}}" placeholder="Zip Code">
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
                                            <option value="PK" {{$property->country === 'PK' ? 'selected' : '' }}>Pakistan</option>
                                            <option value="SA" {{$property->country === 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                                            <option value="DB" {{$property->country === 'DB' ? 'selected' : '' }}>Dubai</option>
                                            <option value="IR" {{$property->country === 'IR' ? 'selected' : '' }}>Iran</option>
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
                                            <option value="Sindh" {{$property->state === 'Sindh' ? 'selected' : '' }}>Sindh</option>
                                            <option value="Punjab" {{$property->state === 'Punjab' ? 'selected' : '' }}>Punjab</option>
                                            <option value="Keyber" {{$property->state === 'Keyber' ? 'selected' : '' }}>Keyber</option>
                                            <option value="Balochistan" {{$property->state === 'Balochistan' ? 'selected' : '' }}>Balochistan</option>
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
                                            <option value="Karachi" {{$property->city === 'Karachi' ? 'selected' : '' }}>Karachi</option>
                                            <option value="Peshawar" {{$property->city === 'Peshawar' ? 'selected' : '' }}>Peshawar</option>
                                            <option value="Lahore" {{$property->city === 'Lahore' ? 'selected' : '' }}>Lahore</option>
                                            <option value="Quetta" {{$property->city === 'Quetta' ? 'selected' : '' }}>Quetta</option>
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
                                    <input type="text" class="form-control" id="address" name="address" value="{{$property->address}}" placeholder="Zip Address">
                                    @error('address')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Longitude</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" value="{{$property->longitude}}" placeholder="Zip Longitude">
                                    @error('longitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="latitude">Latitude</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" value="{{$property->latitude}}" placeholder="Zip Latitude">
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
                                            <option value="Europe/London" {{$property->timezone === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
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
                                            <option value="Apartment" {{$property->property_type === 'Apartment' ? 'selected' : '' }}>Apartment</option>
                                            <option value="Flat" {{$property->property_type === 'Flat' ? 'selected' : '' }}>Flat</option>
                                            <option value="Hotel" {{$property->property_type === 'Hotel' ? 'selected' : '' }}>Hotel</option>
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
