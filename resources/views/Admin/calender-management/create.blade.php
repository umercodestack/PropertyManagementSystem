@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Apartment</h3>

                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6>1. Apartment Details</h6>
                    <hr>
                    <form action="{{route('apartment-management.store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="user_id" id="user_id" data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach($hosts as $items)
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
                                    <label class="form-label" for="apartment_type">Apartment Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="apartment_type" id="apartment_type" data-placeholder="Apartment Type">
                                            <option value="" selected disabled>Select Apartment Type</option>
                                            <option value="house">House</option>
                                            <option value="apartment">Apartment</option>
                                            <option value="cabin">Cabin</option>
                                            <option value="tent">Tent</option>
                                            <option value="farm">Farm</option>
                                        </select>
                                        @error('apartment_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="rental_type">Who might be staying</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="rental_type" id="rental_type" data-placeholder="Who might be staying">
                                            <option value="" selected disabled>Who might be staying</option>
                                            <option value="you">You</option>
                                            <option value="family">Family</option>
                                            <option value="friends">Friends</option>
                                        </select>
                                        @error('rental_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Apartment Title</label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Apartment Title">
                                    @error('title')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="max_guests">Max Guests</label>
                                    <input type="number" class="form-control" id="max_guests" name="max_guests" placeholder="Max Guests">
                                    @error('max_guests')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="bedrooms">Bedrooms</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" placeholder="Bedrooms">
                                    @error('bedrooms')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="beds">Beds</label>
                                    <input type="number" class="form-control" id="beds" name="beds" placeholder="Beds">
                                    @error('beds')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="bathrooms">Bathrooms</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" placeholder="Bathrooms">
                                    @error('bathrooms')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="description">Description</label>
                                    <input type="text" class="form-control" id="description" name="description" placeholder="Description">
                                    @error('description')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amenities">Amenities</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="amenities[]" id="amenities" multiple="multiple" data-placeholder="Amenities">
                                            <option value="house">House</option>
                                            <option value="apartment">Apartment</option>
                                            <option value="cabin">Cabin</option>
                                            <option value="tent">Tent</option>
                                            <option value="farm">Farm</option>
                                        </select>
                                        @error('amenities')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="any_of_these">Any of these</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="any_of_these[]" id="any_of_these" multiple="multiple" data-placeholder="Any of these">
                                            <option value="security-camera">Security Camera</option>
                                            <option value="weapon">Weapon</option>
                                            <option value="dangerous-animal">Dangerous Animal</option>
                                        </select>
                                        @error('any_of_these')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="unique_attr">Unique Attributes</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="unique_attr[]" id="unique_attr" multiple="multiple" data-placeholder="Unique Attributes">
                                            <option value="peace">Peace</option>
                                            <option value="Bedroom">Bedroom</option>
                                            <option value="relaxed">Relaxed</option>
                                            <option value="unique">Unique</option>
                                            <option value="friendly">Friendly</option>
                                            <option value="spacious">Spacious</option>
                                        </select>
                                        @error('unique_attr')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="js_id">Journey Specialist</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="js_id" id="js_id" data-placeholder="Select Journey Specialist">
                                            <option value="" selected disabled>Select Journey Specialist</option>
                                            @foreach($journeySpecialist as $items)
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
                                    <label class="form-label" for="host_type_id">Host Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="host_type_id" id="host_type_id" data-placeholder="Select Host Type">
                                            <option value="" selected disabled>Select Host Type</option>
                                            @foreach($hostTypes as $items)
                                                <option value="{{$items->id}}">{{$items->module_name}}</option>
                                            @endforeach
                                        </select>
                                        @error('host_type_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="door_lock">Door Lock</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="door_lock" id="door_lock" data-placeholder="Door Lock">
                                            <option value="" selected disabled>Select Door Lock</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                        @error('door_lock')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <h6>2. Apartment Address</h6>
                            <hr>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="country">Country</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="country" id="country" data-placeholder="Select Country">
                                            <option value="" selected disabled>Select Country</option>
                                            <option value="Pakistan">Pakistan</option>
                                            <option value="Saudi">Saudi Arabia</option>
                                            <option value="Dubai">Dubai</option>
                                            <option value="Bangladesh">Bangladesh</option>
                                            <option value="India">India</option>
                                            <option value="USA">USA</option>
                                        </select>
                                        @error('country')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="province">State</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="province" id="province" data-placeholder="Select State">
                                            <option value="" selected disabled>Select State</option>
                                            <option value="sindh">Sindh</option>
                                            <option value="punjab">Punjab</option>
                                            <option value="balochistan">Balochistan</option>
                                            <option value="kpk">KPK</option>
                                        </select>
                                        @error('province')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="city">State</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="city" id="city" data-placeholder="Select City">
                                            <option value="" selected disabled>Select City</option>
                                            <option value="karachi">Karachi</option>
                                            <option value="hyderabad">Hyderabad</option>
                                            <option value="umerkot">Umerkot</option>
                                            <option value="sukhar">Sukhar</option>
                                        </select>
                                        @error('city')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="address_line">Completed Address</label>
                                    <input type="text" class="form-control" id="address_line" name="address_line" placeholder="Completed Address">
                                    @error('address_line')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Longitude</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" placeholder="Zip Longitude">
                                    @error('longitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="latitude">Latitude</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" placeholder="Zip Latitude">
                                    @error('latitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="postal">Postal Code</label>
                                    <input type="number" class="form-control" id="postal" name="postal" placeholder="Postal Code">
                                    @error('postal')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <h6>3. Apartment Price</h6>
                            <hr>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="discount_id">Select Discount Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="discount_id" id="discount_id" data-placeholder="Select Discount Type">
                                            <option value="" selected disabled>Select Discount Type</option>
                                            @foreach($discounts as $items)
                                                <option value="{{$items->id}}">{{$items->discount_title}}</option>
                                            @endforeach
                                        </select>
                                        @error('discount_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="price">Price</label>
                                    <input type="number" class="form-control" id="price" name="price" placeholder="Price">
                                    @error('Price')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <h6>4. Apartment Photos</h6>
                            <hr>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="price">Apartment Photos</label>
                                    <input type="file" class="form-control" name="apartment_image[]" multiple>
                                    @error('Price')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
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
