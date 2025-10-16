@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Apartment</h3>

                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6>1. Apartment Details</h6>
                    <hr>
                    <form action="{{route('apartment-management.update', $apartment->id)}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="user_id" id="user_id" data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach($hosts as $items)
                                                <option value="{{$items->id}}" {{$apartment->user_id == $items->id ? 'selected' : ''}}>{{$items->name}} {{$items->surname}}</option>
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
                                            <option value="house" {{$apartment->apartment_type === 'house' ? 'selected' : ''}}>House</option>
                                            <option value="apartment" {{$apartment->apartment_type === 'apartment' ? 'selected' : ''}}>Apartment</option>
                                            <option value="cabin" {{$apartment->apartment_type === 'cabin' ? 'selected' : ''}}>Cabin</option>
                                            <option value="tent" {{$apartment->apartment_type === 'tent' ? 'selected' : ''}}>Tent</option>
                                            <option value="farm" {{$apartment->apartment_type === 'farm' ? 'selected' : ''}}>Farm</option>
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
                                            <option value="you" {{$apartment->rental_type === 'you' ? 'selected' : ''}}>You</option>
                                            <option value="family" {{$apartment->rental_type === 'family' ? 'selected' : ''}}>Family</option>
                                            <option value="friends" {{$apartment->rental_type === 'friends' ? 'selected' : ''}}>Friends</option>
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
                                    <input type="text" class="form-control" id="title" name="title" value="{{$apartment->title}}" placeholder="Apartment Title">
                                    @error('title')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="max_guests">Max Guests</label>
                                    <input type="number" class="form-control" id="max_guests" name="max_guests" value="{{$apartment->max_guests}}" placeholder="Max Guests">
                                    @error('max_guests')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="bedrooms">Bedrooms</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="{{$apartment->bedrooms}}" placeholder="Bedrooms">
                                    @error('bedrooms')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="beds">Beds</label>
                                    <input type="number" class="form-control" id="beds" name="beds" value="{{$apartment->beds}}" placeholder="Beds">
                                    @error('beds')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="bathrooms">Bathrooms</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="{{$apartment->bathrooms}}" placeholder="Bathrooms">
                                    @error('bathrooms')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="description">Description</label>
                                    <input type="text" class="form-control" id="description" name="description" value="{{$apartment->description}}" placeholder="Description">
                                    @error('description')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amenities">Amenities</label>
                                    <div class="form-control-wrap">
                                        @php
                                            $amenities = (json_decode($apartment->amenities));
                                            $any_of_these = (json_decode($apartment->any_of_these));
                                            $unique_attr = (json_decode($apartment->unique_attr));
                                        @endphp
                                        <select class="form-select select2" name="amenities[]" id="amenities" multiple="multiple" data-placeholder="Amenities">
                                            <option value="house" {{ in_array("house", $amenities) ? 'selected' : '' }}>House</option>
                                            <option value="apartment" {{ in_array("apartment", $amenities) ? 'selected' : '' }}>Apartment</option>
                                            <option value="cabin" {{ in_array("cabin", $amenities) ? 'selected' : '' }}>Cabin</option>
                                            <option value="tent" {{ in_array("tent", $amenities) ? 'selected' : '' }}>Tent</option>
                                            <option value="farm" {{ in_array("farm", $amenities) ? 'selected' : '' }}>Farm</option>
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
                                            <option value="security-camera" {{ in_array("security-camera", $any_of_these) ? 'selected' : '' }}>Security Camera</option>
                                            <option value="weapon" {{ in_array("weapon", $any_of_these) ? 'selected' : '' }}>Weapon</option>
                                            <option value="dangerous-animal" {{ in_array("dangerous-animal", $any_of_these) ? 'selected' : '' }}>Dangerous Animal</option>
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
                                            <option value="peace" {{ in_array("peace", $unique_attr) ? 'selected' : '' }}>Peace</option>
                                            <option value="Bedroom" {{ in_array("Bedroom", $unique_attr) ? 'selected' : '' }}>Bedroom</option>
                                            <option value="relaxed" {{ in_array("relaxed", $unique_attr) ? 'selected' : '' }}>Relaxed</option>
                                            <option value="unique" {{ in_array("unique", $unique_attr) ? 'selected' : '' }}>Unique</option>
                                            <option value="friendly" {{ in_array("friendly", $unique_attr) ? 'selected' : '' }}>Friendly</option>
                                            <option value="spacious" {{ in_array("spacious", $unique_attr) ? 'selected' : '' }}>Spacious</option>
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
                                                <option value="{{$items->id}}" {{$apartment->js_id == $items->id ? 'selected' : ''}}>{{$items->name}} {{$items->surname}}</option>
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
                                                <option value="{{$items->id}}" {{$apartment->host_type_id == $items->id ? 'selected' : ''}}>{{$items->module_name}}</option>
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
                                            <option value="yes" {{$apartment->door_lock === 1 ? 'selected' : ''}}>Yes</option>
                                            <option value="no" {{$apartment->door_lock === 0 ? 'selected' : ''}}>No</option>
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
                                            <option value="Pakistan" {{$apartmentAddress->country == 'Pakistan' ? 'selected' : ''}}>Pakistan</option>
                                            <option value="Saudi" {{$apartmentAddress->country == 'Saudi' ? 'selected' : ''}}>Saudi Arabia</option>
                                            <option value="Dubai" {{$apartmentAddress->country == 'Dubai' ? 'selected' : ''}}>Dubai</option>
                                            <option value="Bangladesh" {{$apartmentAddress->country == 'Bangladesh' ? 'selected' : ''}}>Bangladesh</option>
                                            <option value="India" {{$apartmentAddress->country == 'India' ? 'selected' : ''}}>India</option>
                                            <option value="USA" {{$apartmentAddress->country == 'USA' ? 'selected' : ''}}>USA</option>
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
                                            <option value="sindh" {{$apartmentAddress->province == 'sindh' ? 'selected' : ''}}>Sindh</option>
                                            <option value="punjab" {{$apartmentAddress->province == 'punjab' ? 'selected' : ''}}>Punjab</option>
                                            <option value="balochistan" {{$apartmentAddress->province == 'balochistan' ? 'selected' : ''}}>Balochistan</option>
                                            <option value="kpk" {{$apartmentAddress->province == 'kpk' ? 'selected' : ''}}>KPK</option>
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
                                            <option value="karachi" {{$apartmentAddress->city == 'karachi' ? 'selected' : ''}}>Karachi</option>
                                            <option value="hyderabad" {{$apartmentAddress->city == 'hyderabad' ? 'selected' : ''}}>Hyderabad</option>
                                            <option value="umerkot" {{$apartmentAddress->city == 'umerkot' ? 'selected' : ''}}>Umerkot</option>
                                            <option value="sukhar" {{$apartmentAddress->city == 'sukhar' ? 'selected' : ''}}>Sukhar</option>
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
                                    <input type="text" class="form-control" id="address_line" name="address_line" value="{{$apartmentAddress->address_line}}" placeholder="Completed Address">
                                    @error('address_line')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Longitude</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" value="{{$apartmentAddress->longitude}}" placeholder="Zip Longitude">
                                    @error('longitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="latitude">Latitude</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" value="{{$apartmentAddress->latitude}}" placeholder="Zip Latitude">
                                    @error('latitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="postal">Postal Code</label>
                                    <input type="number" class="form-control" id="postal" name="postal" value="{{$apartmentAddress->postal}}" placeholder="Postal Code">
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
                                                <option value="{{$items->id}}" {{$apartmentPrice->discount_id == $items->id ? 'selected' : ''}}>{{$items->discount_title}}</option>
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
                                    <input type="number" class="form-control" id="price" name="price" value="{{$apartmentPrice->price}}" placeholder="Price">
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

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amenities">Assign Listing</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="amenities[]" id="amenities" multiple="multiple" data-placeholder="Assign Listing">
                                            <option value="d8962365">d8962365</option>
                                        </select>
                                        @error('amenities')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @foreach($apartmentImages as $item)
                                <div class="col-md-4 mb-4" style="border: 1px solid #cbcbcb;padding: 15px;border-radius: 5px;">
                                    <img src="{{ asset('storage/'.$item->apartment_image) }}" alt="" style="width: 100%; height: 100%">
                                    <div class="text-end">
                                        <a href="{{ asset('storage/'.$item->apartment_image) }}" download class="btn btn-success">Download</a>
                                        <a href="{{route('apartment-image-delete', $item->id)}}" class="btn btn-danger">Delete</a>
                                    </div>
                                </div>
                            @endforeach
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
