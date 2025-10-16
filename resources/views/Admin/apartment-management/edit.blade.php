@extends('Admin.layouts.app')
@section('content')
<head>
<style>
 
 /* Image Box with Progress */
.uploading-image-wrapper {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 10px;
    border: 2px solid #ccc;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: inline-block;
}
#loadingSpinner img {
    display: block;
    margin: 0 auto;
}

.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.loader-gif {
    width: 60px;
}


/* Progress Bar inside image */
.uploading-image-wrapper .progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background-color: #4caf50;
    border-radius: 3px 3px 0 0;
}

/* Image itself */
.uploading-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Button and other styles */
#uploadImageForm {
    margin-top: 10px;
    padding: 8px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#uploadImageForm:hover {
    background-color: #0056b3;
}

</style>    
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
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
                    <form action="{{route('apartment-management.update', $apartment->id)}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="apartment_type">Apartment Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="apartment_type" id="apartment_type" data-placeholder="Apartment Type">
                                            <option value="" disabled>Select Apartment Type</option>
                                            <option value="house" {{ $apartment->apartment_type == 'house' ? 'selected' : '' }}>House</option>
                                            <option value="apartment" {{ $apartment->apartment_type == 'apartment' ? 'selected' : '' }}>Apartment</option>
                                            <option value="cabin" {{ $apartment->apartment_type == 'cabin' ? 'selected' : '' }}>Cabin</option>
                                            <option value="tent" {{ $apartment->apartment_type == 'tent' ? 'selected' : '' }}>Tent</option>
                                            <option value="farm" {{ $apartment->apartment_type == 'farm' ? 'selected' : '' }}>Farm</option>
                                        </select>
                                        @error('apartment_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Apartment Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $apartment->title) }}" placeholder="Apartment Title">
                                    @error('title')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="apartment_num">Apartment #</label>
                                    <input type="text" class="form-control" id="apartment_num" name="apartment_num" value="{{ old('apartment_num', $apartment->apartment_num) }}">
                                    @error('apartment_num')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount_type">Commission Amount Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="commission_type" id="amount_type" data-placeholder="Amount Type">
                                            <option value="" disabled>Amount Type</option>
                                            <option value="percentage" {{ $apartment->commission_type == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                            <option value="fixed" {{ $apartment->commission_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                        </select>
                                        @error('commission_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount">Commission</label>
                                    <input type="text" class="form-control" id="amount" name="commission_value" value="{{ old('commission_value', $apartment->commission_value) }}" placeholder="Amount">
                                    @error('commission_value')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="is_churned">Churned</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="is_churned" id="is_churned" data-placeholder="Churned">
                                            <option value="" disabled>Churned</option>
                                            <option value="0" {{ $apartment->is_churned == 0 ? 'selected' : '' }}>Live</option>
                                            <option value="1" {{ $apartment->is_churned == 1 ? 'selected' : '' }}>Churned</option>
                                        </select>
                                        @error('is_churned')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="google_map">Google Map Link</label>
                                    <input type="text" class="form-control" id="google_map" name="google_map" value="{{ old('google_map', $apartment->google_map) }}" placeholder="Google Map Link">
                                    @error('google_map')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            

                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="district">District</label>
        <select class="form-control" id="district" name="district">
            <option value="">-- Select District --</option>
            <option value="Al Aj" {{ old('district', $apartment->district) == 'Al Aj' ? 'selected' : '' }}>Al Aj</option>
            <option value="Al Aqiq" {{ old('district', $apartment->district) == 'Al Aqiq' ? 'selected' : '' }}>Al Aqiq</option>
            <option value="Al Arid" {{ old('district', $apartment->district) == 'Al Arid' ? 'selected' : '' }}>Al Arid</option>
            <option value="Al Falah" {{ old('district', $apartment->district) == 'Al Falah' ? 'selected' : '' }}>Al Falah</option>
            <option value="Al Malqa" {{ old('district', $apartment->district) == 'Al Malqa' ? 'selected' : '' }}>Al Malqa</option>
            <option value="Al Murooj" {{ old('district', $apartment->district) == 'Al Murooj' ? 'selected' : '' }}>Al Murooj</option>
            <option value="Al Olaya" {{ old('district', $apartment->district) == 'Al Olaya' ? 'selected' : '' }}>Al Olaya</option>
            <option value="Al Qirawan" {{ old('district', $apartment->district) == 'Al Qirawan' ? 'selected' : '' }}>Al Qirawan</option>
            <option value="Al Rabi" {{ old('district', $apartment->district) == 'Al Rabi' ? 'selected' : '' }}>Al Rabi</option>
            <option value="Al Rawdah" {{ old('district', $apartment->district) == 'Al Rawdah' ? 'selected' : '' }}>Al Rawdah</option>
            <option value="Al Rimal" {{ old('district', $apartment->district) == 'Al Rimal' ? 'selected' : '' }}>Al Rimal</option>
            <option value="Al Sahafa" {{ old('district', $apartment->district) == 'Al Sahafa' ? 'selected' : '' }}>Al Sahafa</option>
            <option value="Al Yasmin" {{ old('district', $apartment->district) == 'Al Yasmin' ? 'selected' : '' }}>Al Yasmin</option>
            <option value="Al-Aqiq District" {{ old('district', $apartment->district) == 'Al-Aqiq District' ? 'selected' : '' }}>Al-Aqiq District</option>
            <option value="Al-Nargis" {{ old('district', $apartment->district) == 'Al-Nargis' ? 'selected' : '' }}>Al-Nargis</option>
            <option value="Al-Narjis" {{ old('district', $apartment->district) == 'Al-Narjis' ? 'selected' : '' }}>Al-Narjis</option>
            <option value="Al-Olaya, King Fahad District" {{ old('district', $apartment->district) == 'Al-Olaya, King Fahad District' ? 'selected' : '' }}>Al-Olaya, King Fahad District</option>
            <option value="Almalqa" {{ old('district', $apartment->district) == 'Almalqa' ? 'selected' : '' }}>Almalqa</option>
            <option value="Alnarjes" {{ old('district', $apartment->district) == 'Alnarjes' ? 'selected' : '' }}>Alnarjes</option>
            <option value="An Narjis" {{ old('district', $apartment->district) == 'An Narjis' ? 'selected' : '' }}>An Narjis</option>
            <option value="Aqiq" {{ old('district', $apartment->district) == 'Aqiq' ? 'selected' : '' }}>Aqiq</option>
            <option value="As Sulimaniyah" {{ old('district', $apartment->district) == 'As Sulimaniyah' ? 'selected' : '' }}>As Sulimaniyah</option>
            <option value="Banban" {{ old('district', $apartment->district) == 'Banban' ? 'selected' : '' }}>Banban</option>
            <option value="Hittin" {{ old('district', $apartment->district) == 'Hittin' ? 'selected' : '' }}>Hittin</option>
            <option value="Narjis" {{ old('district', $apartment->district) == 'Narjis' ? 'selected' : '' }}>Narjis</option>
            <option value="Qurtubah" {{ old('district', $apartment->district) == 'Qurtubah' ? 'selected' : '' }}>Qurtubah</option>
            <option value="Sulaymania" {{ old('district', $apartment->district) == 'Sulaymania' ? 'selected' : '' }}>Sulaymania</option>
            <option value="Suleimaniyah" {{ old('district', $apartment->district) == 'Suleimaniyah' ? 'selected' : '' }}>Suleimaniyah</option>
            <option value="Yarmukh" {{ old('district', $apartment->district) == 'Yarmukh' ? 'selected' : '' }}>Yarmukh</option>
        </select>
        @error('district')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>



                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="street">Street</label>
                                    <input type="text" class="form-control" id="street" name="street" value="{{ old('street', $apartment->street) }}">
                                    @error('street')
                                    <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="city_name">City Name</label>
        <select class="form-control" id="city_name" name="city_name">
            <option value="">-- Select City --</option>
            <option value="Riyadh" {{ old('city_name', $apartment->city_name) == 'Riyadh' ? 'selected' : '' }}>Riyadh</option>
        </select>
        @error('city_name')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="address_line">Completed Address</label>
                                    <input type="text" class="form-control" id="address_line" name="address_line" value="{{ old('address_line', $apartment->address_line) }}" placeholder="Completed Address">
                                    @error('address_line')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $apartment->longitude) }}" placeholder="Zip Longitude">
                                    @error('longitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="latitude">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $apartment->latitude) }}" placeholder="Zip Latitude">
                                    @error('latitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="postal">Postal Code</label>
                                    <input type="number" class="form-control" id="postal" name="postal" value="{{ old('postal', $apartment->postal) }}" placeholder="Postal Code">
                                    @error('postal')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="be_listing_name">Booking Engine Name</label>
                                    <input type="text" class="form-control" id="be_listing_name" name="be_listing_name" value="{{ old('be_listing_name', $apartment->be_listing_name) }}">
                                    @error('be_listing_name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_about">About Property</label>
                                    <textarea class="form-control" id="property_about" name="property_about">{{ old('property_about', $apartment->property_about) }}</textarea>
                                    @error('property_about')
                                    <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="max_guests">Max Guests</label>
                                    <input type="number" class="form-control" id="max_guests" name="max_guests" value="{{ old('max_guests', $apartment->max_guests) }}" placeholder="Max Guests">
                                    @error('max_guests')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="bedrooms">Bedrooms</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="{{ old('bedrooms', $apartment->bedrooms) }}" placeholder="Bedrooms">
                                    @error('bedrooms')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="beds">Beds</label>
                                    <input type="number" class="form-control" id="beds" name="beds" value="{{ old('beds', $apartment->beds) }}" placeholder="Beds">
                                    @error('beds')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="bathrooms">Bathrooms</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="{{ old('bathrooms', $apartment->bathrooms) }}" placeholder="Bathrooms">
                                    @error('bathrooms')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2 mt-5">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_allow_pets" name="is_allow_pets" value="1" {{ $apartment->is_allow_pets ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_allow_pets">Allow Pets</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_self_check_in" name="is_self_check_in" value="1" {{ $apartment->is_self_check_in ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_self_check_in">Self Check-In</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="living_room" name="living_room" value="1" {{ $apartment->living_room ? 'checked' : '' }}>
                                    <label class="form-check-label" for="living_room">Living Room</label>
                                </div>
                            </div>
                            <div class="col-md-2 mt-5">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="laundry_area" name="laundry_area" value="1" {{ $apartment->laundry_area ? 'checked' : '' }}>
                                    <label class="form-check-label" for="laundry_area">Laundry Area</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="corridor" name="corridor" value="1" {{ $apartment->corridor ? 'checked' : '' }}>
                                    <label class="form-check-label" for="corridor">Corridor</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="outdoor_area" name="outdoor_area" value="1" {{ $apartment->outdoor_area ? 'checked' : '' }}>
                                    <label class="form-check-label" for="outdoor_area">Outdoor Area</label>
                                </div>
                            </div>
                            <div class="col-md-2 mt-5">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="kitchen" name="kitchen" value="1" {{ $apartment->kitchen ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kitchen">Kitchen</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="amenities">Amenities</label>
                                    <div class="row">
                                        @php
                                            $amenities = [
                                                'Air conditioner', 'Wifi', 'T.V', 'Heater', 'Kitchen', 'Microwave', 'Fridge',
                                                'Kettle', 'Coffee Maker', 'Washer Machine', 'Hair Dryer', 'Iron', 'Essential',
                                                'Shampoo', 'Smoke Alarm', 'Fire Extinguisher', 'First Aid Kit', 'Outdoor Dining Area',
                                                'Pool', 'Private Entrance', 'Self Check-in', 'Free Parking Premises', 'Sink', 'Hotplate', 'Other'
                                            ];
                                            $selectedAmenities = $apartment->amenities ? json_decode($apartment->amenities, true) : [];
                                        @endphp
                                        @foreach($amenities as $amenity)
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="amenity_{{ $loop->index }}" name="amenities[]" value="{{ $amenity }}" {{ in_array($amenity, $selectedAmenities) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="amenity_{{ $loop->index }}">{{ $amenity }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('amenities')
                                    <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                          
                            
                            
                           
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="price">Per Night Price</label>
                                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $apartment->price) }}" placeholder="Price">
                                    @error('price')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="cleaning_fee">Cleaning Fee</label>
                                    <input type="number" class="form-control" id="cleaning_fee" name="cleaning_fee" value="{{ old('cleaning_fee', $apartment->cleaning_fee) }}">
                                    @error('cleaning_fee')
                                    <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="discounts">Discount</label>
                                    <input type="number" class="form-control" id="discounts" name="discounts" value="{{ old('discounts', $apartment->discounts) }}">
                                    @error('discounts')
                                    <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="tax">Tax</label>
                                    <input type="number" class="form-control" id="tax" name="tax" value="{{ old('tax', $apartment->tax) }}">
                                    @error('tax')
                                    <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="checkin_time">Check-in Time</label>
        <input type="time" class="form-control" id="checkin_time" name="checkin_time" value="{{ old('checkin_time', $apartment->checkin_time) }}">
        @error('checkin_time')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="checkout_time">Check-out Time</label>
        <input type="time" class="form-control" id="checkout_time" name="checkout_time" value="{{ old('checkout_time', $apartment->checkout_time) }}">
        @error('checkout_time')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="minimum_days_stay">Minimum days stay</label>
        <select class="form-control select2" id="minimum_days_stay" name="minimum_days_stay">
            <option value="">-- Select Minimum Days Stay --</option>
            @for($i = 1; $i <= 31; $i++)
                <option value="{{ $i }}" {{ (old('minimum_days_stay', $apartment->minimum_days_stay ?? '') == $i) ? 'selected' : '' }}>
                    {{ $i }}
                </option>
            @endfor
        </select>
        @error('minimum_days_stay')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="is_long_term">Stay Type</label>
        <select class="form-control select2" id="is_long_term" name="is_long_term">
            <option value="">-- Select District --</option>
            <option value="0" {{ (old('is_long_term', $apartment->is_long_term ?? '') == "0") ? 'selected' : '' }}>Short Term</option>
            <option value="1" {{ (old('is_long_term', $apartment->is_long_term ?? '') == "1") ? 'selected' : '' }}>Long Term</option>
        </select>
        @error('is_long_term')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


                            <div class="card card-bordered mt-3">
                                <div class="card-inner">
                                    <div class="row">
                                        <div class="col-6">
                                            <h5 class="mb-1">Assign Host</h5>
                                            <hr>
                                            @csrf
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <label for="host_id">Select Host</label>
                                                    <select name="host_id" id="host_id" class="form-control select2">
                                                        <option value="" disabled>Select Host</option>
                                                        @foreach ($users as $item)
                                                            <option value="{{ $item->id }}" data-name="{{ $item->name }} {{ $item->surname }}">{{ $item->name }} {{ $item->surname }} {{ $item->host_key }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mt-3">
                                                    <div class="form-group text-start">
                                                        <button type="button" class="btn btn-primary"  onclick="addHost()">Add Host</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="mb-1">Assign Experience Managers</h5>
                                            <hr>
                                            @csrf
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <label for="exp_manager_id">Select Exp Managers</label>
                                                    <select name="exp_manager_id" id="exp_manager_id" class="form-control select2">
                                                        <option value="" disabled>Select Experience Managers</option>
                                                        @foreach ($experiencemanagers as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }} {{ $item->surname }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mt-3">
                                                    <div class="form-group text-start">
                                                        <button type="button" class="btn btn-primary" onclick="addManager()">Add Managers</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-bordered mt-3 p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card-inner">
                                            <h5 class="mb-3">Selected Hosts</h5>
                                            <div class="row g-2" id="selected-hosts" style="min-height: 50px; border: 1px solid #e5e5e5; padding: 10px; border-radius: 5px;">
                                            @foreach($users->whereIn('id', $apartment->user_id) as $host)
    <div class="col-md-3 mt-2" id="host-{{ $host->id }}" style="position:relative;background: green; color: white; border-radius: 5px; padding: 10px; margin-left: 25px;">
        {{ $host->name }} {{ $host->surname }}
        <input type="hidden" name="hosts[]" value="{{ $host->id }}">
        <button type="button" onclick="removeHost('{{ $host->id }}')" style="position:absolute; top:0; right:0; background:red; border-radius:50%; border: none; color:white; width:20px; height:20px;">X</button>
    </div>
@endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-inner">
                                            <h5 class="mb-3">Selected Experience Managers</h5>
                                            <div class="row g-2" id="selected-managers" style="min-height: 50px; border: 1px solid #e5e5e5; padding: 10px; border-radius: 5px;">
                                               @foreach($experiencemanagers->whereIn('id', $apartment->exp_managers) as $manager)
                                                
                                                    <div class="col-md-3 mt-2" id="manager-{{ $manager->id }}" style="position:relative;background: green; color: white; border-radius: 5px; padding: 10px; margin-left: 25px;">
                                                        {{ $manager->name }} {{ $manager->surname }}
                                                        <input type="hidden" name="exp_managers[]" value="{{ $manager->id }}">
                                                        <button type="button" onclick="removeManager('{{ $manager->id }}')" style="position:absolute; top:0; right:0; background:red; border-radius:50%; border: none; color:white; width:20px; height:20px;">X</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            
<div class="col-md-12" id="images_section">
    <div class="form-group">
        <label class="form-label" for="cover_image">Cover Image</label>
        <input type="file" class="form-control" name="cover_image" id="cover_image" accept="image/*">
        @error('cover_image')
        <span class="invalid" style="color:red;">{{ $message }}</span>
        @enderror

        <!-- Loader -->
        <div id="imgloader" style="display:none;">
    <div class="loader-overlay">
        <img src="https://i.gifer.com/ZZ5H.gif" class="loader-gif" alt="Loading...">
    </div>
</div>

       
    </div>
</div>

                            

@php
    $rooms = ['Bedroom 1', 'Bedroom 2', 'Bedroom 3', 'Bathroom 1', 'Bathroom 2', 'Bathroom 3', 'Living Room', 'Kitchen'];
@endphp

@foreach($rooms as $index => $room)
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label">{{ $room }}</label>
        <input type="file" 
               class="form-control room-image-upload" 
               name="room_images[{{ $room }}][]" 
               data-room="{{ $room }}" 
               multiple 
               accept="image/*">

        <!-- Optional preview container -->
        <div id="preview-{{ Str::slug($room) }}" style="margin-top: 10px;"></div>

        @error('room_images.' . $room)
        <span class="invalid" style="color: red;">{{ $message }}</span>
        @enderror
    </div>
</div>
@endforeach



<div class="col-md-12">
    <!-- File Upload Section -->
    <div class="form-group" style="margin-top: 20px;">
        <label class="form-label" for="apartment_image">Other Images</label>
        <input type="file" class="form-control" name="apartment_image[]" id="apartment_image" multiple accept="image/*">
        
        @error('apartment_image')
            <span class="invalid" style="color: red;">{{ $message }}</span>
        @enderror
    </div>

    <!-- Response Message (Success or Error) -->
    <div id="responseMessage"></div>

    <div id="loadingSpinner" style="display: none;">
        <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading..." style="width: 50px; height: 50px; margin-top: 20px;">
    </div>

    <!-- Display uploaded images -->
    <div id="uploadedImages" style="margin-top: 30px;"></div>
</div>



                            <h6>Apartment Images</h6>
                            
    <div class="swiper mySwiper">
        <div class="swiper-wrapper"  tabindex="-1">
        @if($apartmentImages && $apartmentImages->isNotEmpty())    
            @foreach($apartmentImages as $image)
                <div class="swiper-slide image-box position-relative">
                    <a href="{{ $image->url }}" data-fancybox="apartment-gallery">
                        <img class="fixed-image" src="{{$image->url}}" alt="Apartment Image">
                    </a>

                    <!-- Display Category -->
                    <div class="image-category" style="position: absolute; bottom: 5px; left: 5px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 5px; border-radius: 5px;">
                        {{ $image->category }}
                    </div>

                    <!-- Delete Button -->
                    <a href="{{ route('apartment-image-delete', $image->id) }}" 
                       onclick="return confirm('Delete this image?')" 
                       class="btn btn-danger btn-sm" 
                       style="position: absolute; top: 5px; right: 5px; border-radius: 50%;">
                        &times;
                    </a>
                </div>
            @endforeach
        @else
        
              <!-- <p class="mt-5">No apartment images available.</p>-->
        @endif

        </div>

        <!-- Swiper Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>



                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary" id="bntupdate">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
<script>
    let selectedHosts = [];

    function addHost() {
        const select = document.getElementById('host_id');
        const selectedOption = select.options[select.selectedIndex];
        const hostId = selectedOption.value;
        const hostName = selectedOption.getAttribute('data-name');

        // Prevent duplicates
        if (selectedHosts.includes(hostId)) {
            alert('Host already added!');
            return;
        }

        selectedHosts.push(hostId);

        const hostHtml = `
            <div class="col-md-3 mt-2" id="host-${hostId}" style="position:relative;background: green; color: white; border-radius: 5px; padding: 10px;     margin-left: 25px;">
                ${hostName}
                <input type="hidden" name="hosts[]" value="${hostId}">
                <button type="button" onclick="removeHost('${hostId}')" style="position:absolute; top:0; right:0; background:red; border-radius:50%; border: none; color:white; width:20px; height:20px;">X</button>
            </div>
        `;

        document.getElementById('selected-hosts').insertAdjacentHTML('beforeend', hostHtml);
    }

    function removeHost(id) {
        document.getElementById(`host-${id}`).remove();
        selectedHosts = selectedHosts.filter(i => i !== id);
    }


    let selectedManagers = [];

function addManager() {
    const select = document.getElementById('exp_manager_id');
    const selectedOption = select.options[select.selectedIndex];
    const managerId = selectedOption.value;
    const managerName = selectedOption.text;

    // Prevent duplicates
    if (selectedManagers.includes(managerId)) {
        alert('Manager already added!');
        return;
    }

    selectedManagers.push(managerId);

    const managerHtml = `
        <div class="col-md-3 mt-2" id="manager-${managerId}" style="position:relative;background: green; color: white; border-radius: 5px; padding: 10px; margin-left: 25px;">
            ${managerName}
            <input type="hidden" name="exp_managers[]" value="${managerId}">
            <button type="button" onclick="removeManager('${managerId}')" style="position:absolute; top:0; right:0; background:red; border-radius:50%; border: none; color:white; width:20px; height:20px;">X</button>
        </div>
    `;

    document.getElementById('selected-managers').insertAdjacentHTML('beforeend', managerHtml);
}

function removeManager(id) {
    document.getElementById(`manager-${id}`).remove();
    selectedManagers = selectedManagers.filter(i => i !== id);
}


</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
   
   const listingId = {{ $apartment->id }};
   const csrf = '{{ csrf_token() }}';

   $(document).ready(function () {
    



    $('#cover_image').on('change', function () {
        const file = this.files[0];
        if (!file) return;

       
        $('#imgloader').show();
        $('#coverPreview').html('');
        $('#bntupdate').hide();

        // const reader = new FileReader();
        // const img = $('<img style="width:100px; height:100px; object-fit:cover;">');
        // reader.onload = function (e) {
        //     img.attr('src', e.target.result);
        // };
        // reader.readAsDataURL(file);

        // $('#coverPreview').append(img);

        const formData = new FormData();
        formData.append('apartment_image', file);
        formData.append('category', 'Cover Image');
        formData.append('listing_id', listingId);
        formData.append('_token', csrf);

       
        $.ajax({
            url: "{{ route('image.upload.ajax') }}",
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
            if (res.image_url) {
                // img.attr('src', res.image_url); 
                var slide = `
        <div class="swiper-slide image-box position-relative" style="width: 271.5px; margin-right: 5px;">
            <a href="${res.image_url}" data-fancybox="apartment-gallery">
                <img class="fixed-image" src="${res.image_url}" alt="Apartment Image">
            </a>
            <div class="image-category" style="position: absolute; bottom: 5px; left: 5px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 5px; border-radius: 5px;">
                ${res.category}
            </div>
            <a href="apartment-image-delete/${res.id}" 
               onclick="return confirm('Delete this image?')" 
               class="btn btn-danger btn-sm" 
               style="position: absolute; top: 5px; right: 5px; border-radius: 50%;">
                &times;
            </a>
        </div>
        `;
        $('.swiper-wrapper').append(slide);
        $('.swiper-wrapper').attr('tabindex', -1).focus();

        if (window.swiperInstance) {
            window.swiperInstance.update();
        }
            }
            $('#imgloader').hide();
            $('#bntupdate').show();
            $('input[type="file"]').val('');

            if (window.mySwiper) {
                    window.mySwiper.update();
            } else {
            window.mySwiper = new Swiper(".mySwiper", {
                slidesPerView: 4,
                spaceBetween: 15,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        }
        },
        error: function () {
            $('#imgloader').hide();
            $('#coverPreview').append('<div class="text-danger">Upload failed.</div>');
             }
         });
    });


    $('.room-image-upload').on('change', function () {
        const files = this.files;
        const room = $(this).data('room');
        const previewContainer = $('#preview-' + room.replace(/\s+/g, '-').toLowerCase());

        if (!files.length) return;

        previewContainer.html(''); // Clear old previews

        $('#imgloader').show(); // Optional shared loader
        $('#bntupdate').hide();

        uploadRoomImage(files, 0, files.length, room, previewContainer);
    });


function uploadRoomImage(files, index, total, room, container) {
    const file = files[index];
    const formData = new FormData();
    formData.append('apartment_image', file);
    formData.append('category', room);
    formData.append('listing_id', {{ $apartment->id }});
    formData.append('_token', '{{ csrf_token() }}');

    // const wrapper = $('<div style="display:inline-block; position:relative; margin:5px;"></div>');
    // const previewImg = $('<img style="width:100px; height:100px; object-fit:cover; border:1px solid #ccc;">');
     const progressBar = $('<div style="position:absolute; bottom:0; left:0; height:4px; background:#28a745; width:0%;"></div>');
    
    // wrapper.append(previewImg).append(progressBar);
    // container.append(wrapper);

    // // Show local preview
    // const reader = new FileReader();
    // reader.onload = function (e) {
    //     previewImg.attr('src', e.target.result);
    // };
    // reader.readAsDataURL(file);

    $.ajax({
        url: "{{ route('image.upload.ajax') }}",
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        xhr: function () {
            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressBar.css('width', percent + '%');
                }
            });
            return xhr;
        },
        success: function (res) {
            // if (res.image_url) {
            //     previewImg.attr('src', res.image_url); 
            // }
            // progressBar.remove();


            var slide = `
        <div class="swiper-slide image-box position-relative" style="width: 271.5px; margin-right: 5px;">
            <a href="${res.image_url}" data-fancybox="apartment-gallery">
                <img class="fixed-image" src="${res.image_url}" alt="Apartment Image">
            </a>
            <div class="image-category" style="position: absolute; bottom: 5px; left: 5px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 5px; border-radius: 5px;">
                ${res.category}
            </div>
            <a href="apartment-image-delete/${res.id}" 
               onclick="return confirm('Delete this image?')" 
               class="btn btn-danger btn-sm" 
               style="position: absolute; top: 5px; right: 5px; border-radius: 50%;">
                &times;
            </a>
        </div>
        `;
        $('.swiper-wrapper').append(slide);
        $('.swiper-wrapper').attr('tabindex', -1).focus();

        if (window.swiperInstance) {
        window.swiperInstance.update();
        }

            // Upload next image
            if (index + 1 < total) {
                uploadRoomImage(files, index + 1, total, room, container);
            } else {
                $('#imgloader').hide();
                $('#bntupdate').show();
                $('input[type="file"]').val('');

                if (window.mySwiper) {
        window.mySwiper.update();
    } else {
        window.mySwiper = new Swiper(".mySwiper", {
            slidesPerView: 4,
            spaceBetween: 15,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    }
            }
        },
        error: function () {
            progressBar.remove();
            container.append('<div class="text-danger">Upload failed</div>');
            $('#imgloader').hide();
        }
    });
}
    
    
    
    $('#apartment_image').on('change', function () {
        var files = $('#apartment_image')[0].files;
        var totalFiles = files.length;

        if (totalFiles === 0) {
            $('#responseMessage').html('<div class="alert alert-warning">Please select images to upload.</div>');
            return;
        }

        // Clear previous images and hide any response message
        $('#imgloader').show();
        $('#uploadedImages').html('');
        $('#responseMessage').html('');
        $('#bntupdate').hide();

        // Function to upload images one by one
        uploadImage(files, 0, totalFiles);
    });

    // Upload image recursively with progress
    function uploadImage(files, index, totalFiles) {
        var file = files[index];
        var formData = new FormData();
        formData.append('apartment_image', file);
        formData.append('category', 'Other Images');
        formData.append('listing_id', {{ $apartment->id }});
        formData.append('_token', '{{ csrf_token() }}'); 


        // var imageWrapper = $('<div class="uploading-image-wrapper"></div>');
         var progressBar = $('<div class="progress-bar"></div>');
        // var imgElement = $('<img src="" alt="Uploading Image">');
        

        // var reader = new FileReader();
        // reader.onload = function (e) {
        //     imgElement.attr('src', e.target.result);
        // };
        // reader.readAsDataURL(file);

        $.ajax({
            url: "{{ route('image.upload.ajax') }}",  
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function () {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        var percent = (e.loaded / e.total) * 100;
                        progressBar.css('width', percent + '%');
                    }
                });
                return xhr;
            },
            success: function (response) {
                
                // imgElement.attr('src', response.image_url);
                progressBar.remove(); 
                

                var slide = `
        <div class="swiper-slide image-box position-relative" style="width: 271.5px; margin-right: 5px;">
            <a href="${response.image_url}" data-fancybox="apartment-gallery">
                <img class="fixed-image" src="${response.image_url}" alt="Apartment Image">
            </a>
            <div class="image-category" style="position: absolute; bottom: 5px; left: 5px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 5px; border-radius: 5px;">
                ${response.category}
            </div>
            <a href="apartment-image-delete/${response.id}" 
               onclick="return confirm('Delete this image?')" 
               class="btn btn-danger btn-sm" 
               style="position: absolute; top: 5px; right: 5px; border-radius: 50%;">
                &times;
            </a>
        </div>
        `;
        $('.swiper-wrapper').append(slide);
        $('.swiper-wrapper').attr('tabindex', -1).focus();

        if (window.swiperInstance) {
        window.swiperInstance.update();
        }

                
                if (index + 1 < totalFiles) {
                    uploadImage(files, index + 1, totalFiles);  
                } else {
                    $('#responseMessage').html('');
                    $('#imgloader').hide();
                    $('#bntupdate').show();
                    $('input[type="file"]').val('');

                    if (window.mySwiper) {
        window.mySwiper.update();
        } else {
        window.mySwiper = new Swiper(".mySwiper", {
            slidesPerView: 4,
            spaceBetween: 15,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
        }
                }
            },
            error: function (xhr) {
                $('#responseMessage').html('<div class="alert alert-danger">Error uploading image.</div>');
                progressBar.remove(); 
                $('#imgloader').hide();
            }
        });
    }
});



</script>

@if(session('success'))
    <script>
        window.onload = function () {
            const section = document.getElementById('images_section');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
@endif



@endsection