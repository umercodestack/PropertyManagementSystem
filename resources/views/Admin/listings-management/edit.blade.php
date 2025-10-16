@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Listing</h3>
                </div>
            </div>
        </div>
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h5 class="mb-1">Listing Details</h5>
                    <hr>
                    <form action="{{ route('listing.commissionUpdate', $listing->id) }}" method="POST">
                        @csrf
                        @php
                            $listing_details = json_decode($listing->listing_json);
                            //
                        @endphp
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="listing_id">Listing Id</label>
                                    <input type="text" class="form-control" id="listing_id" name="listing_id"
                                        value="{{ $listing_details->id }}" readonly>
                                    @error('listing_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="type">Listing Type</label>
                                    <input type="text" class="form-control" id="type" name="type"
                                        value="{{ $listing_details->type }}" readonly>
                                    @error('type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Listing Title</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="{{ $listing_details->title }}">
                                    @error('title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Apartment #</label>
                                    <input type="text" class="form-control" id="apartment_num" name="apartment_num"
                                        value="{{ $listing->apartment_num }}">
                                    @error('apartment_num')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Is Sync</label>
                                    <input type="text" class="form-control" id="is_sync" name="is_sync"
                                        value="{{ $listing->is_sync === null ? '-' : $listing->is_sync }}" readonly>
                                    @error('title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount_type">Amount Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="commission_type" id="amount_type"
                                            data-placeholder="Amount Type">
                                            <option value="" selected disabled>Amount Type</option>
                                            <option value="percentage"
                                                {{ $listing->commission_type == 'percentage' ? 'selected' : '' }}>Percentage
                                            </option>
                                            <option value="fixed"
                                                {{ $listing->commission_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                        </select>
                                        @error('amount_type')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount">Amount</label>
                                    <input type="number" class="form-control" id="amount" step="any" name="commission_value"
                                        value="{{ $listing->commission_value }}" onblur="checkPercentageValue(this.value)"
                                        placeholder="Amount">
                                    @error('amount')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="is_churned">Churned</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="is_churned" id="is_churned"
                                            data-placeholder="Amount Type">
                                            <option value="" selected disabled>Amount Type</option>
                                            <option value="0"
                                                {{ $listing->is_churned == 0 ? 'selected' : '' }}>No
                                            </option>
                                            <option value="1"
                                                {{ $listing->is_churned == 1 ? 'selected' : '' }}>Yes</option>
                                        </select>
                                        @error('is_churned')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="google_map">Google Map Link</label>
                                    <input type="text" class="form-control" id="google_map" name="google_map"
                                        value="{{ $listing->google_map }}"
                                        placeholder="Google Map Link">
                                    @error('google_map')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="title"> Livedin Fee Applicable on Direct Booking</label>
                                    <input type="checkbox"
                                    style="width: 20px; height: 20px; margin-top:10px"
                                    id="ota_fee_direct_booking"
                                    name="ota_fee_direct_booking"
                                    value="1"
                                    {{ $listing->ota_fee_direct_booking ? 'checked' : '' }}>
                                    @error('ota_fee_direct_booking')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="title">Cleaning Fee Applicable on Direct Booking </label>
                                    <input type="checkbox"
                                    style="width: 20px; height: 20px; margin-top:10px"
                                    id="cleaning_fee_direct_booking"
                                    name="cleaning_fee_direct_booking"
                                    value="1"
                                    {{ $listing->cleaning_fee_direct_booking ? 'checked' : '' }}>
                                    @error('cleaning_fee_direct_booking')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="title"> Cleaning Fee Applicable</label>
                                    <input type="checkbox"
                                    style="width: 20px; height: 20px; margin-top:10px"
                                    id="is_cleaning_fee"
                                    name="is_cleaning_fee"
                                    value="1"
                                    {{ $listing->is_cleaning_fee ? 'checked' : '' }}>
                                    @error('is_cleaning_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="is_co_host">Co-host Account</label>
                                    <input type="checkbox"
                                    style="width: 20px; height: 20px; margin-top:10px"
                                    id="is_co_host"
                                    name="is_co_host"
                                    value="1"
                                    {{ $listing->is_co_host ? 'checked' : '' }}>
                                    @error('is_co_host')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="pre_discount">Livedin Share After Discount </label>
                                    <input type="checkbox"
                                    style="width: 20px; height: 20px; margin-top:10px"
                                    id="pre_discount"
                                    name="pre_discount"
                                    value="1"
                                    {{ $listing->pre_discount ? 'checked' : '' }}>
                                    @error('pre_discount')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                          
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="title">Cleaning Fee Per Cycle Value</label>
                                    <input type="text" class="form-control" id="cleaning_fee_per_cycle" name="cleaning_fee_per_cycle"
                                        value="{{ $listing->cleaning_fee_per_cycle }}">
                                    @error('title')
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
            
            <div class="card card-bordered">
                <div class="card-inner">
                    <h5 class="mb-1">Listing Addtional Detail</h5>
                    <hr>
                    <form action="#" method="POST">
                        @csrf
                        @php
                            $listing_details = json_decode($listing->listing_json);
                            //
                        @endphp

                        <div>
                        <div class="col-md-6 mb-5">
                              
                                <select name="hostaboard_id" id="hostaboard_id" class="form-control select2">
                                    <option value="" selected disabled>Select Activation Code</option>
                                    @foreach ($hostaboards as $item)
                                        <option value="{{ $item->id }}">{{ $item->ActivationCode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>



                        <div class="row gy-4">

                        
                        


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="be_listing_name">Booking Engine Name</label>
                                    <input type="text" class="form-control" id="be_listing_name" name="be_listing_name"
                                        value="{{ $listing->be_listing_name }}" >
                                    @error('be_listing_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_about">About Property</label>
                                    <textarea class="form-control" id="property_about" name="property_about">{{$listing->property_about }}</textarea>
                                    @error('property_about')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="bedrooms">Bedrooms</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="{{ old('bedrooms', $listing->bedrooms) }}">
                                    @error('bedrooms')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="beds">Beds</label>
                                    <input type="number" class="form-control" id="beds" name="beds" value="{{ old('beds', $listing->beds) }}">
                                    @error('beds')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="bathrooms">Bathrooms</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="{{ old('bathrooms', $listing->bathrooms) }}">
                                    @error('bathrooms')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="district">District</label>
                                    <input type="text" class="form-control" id="district" name="district" value="{{ old('district', $listing->district) }}">
                                    @error('district')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="street">Street</label>
                                    <input type="text" class="form-control" id="street" name="street" value="{{ old('street', $listing->street) }}">
                                    @error('street')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="city_name">City Name</label>
                                    <input type="text" class="form-control" id="city_name" name="city_name" value="{{ old('city_name', $listing->city_name) }}">
                                    @error('city_name')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="activation_google_map">Google Map Link</label>
                                    <input type="text" class="form-control" id="activation_google_map" name="activation_google_map"
                                        value="{{ $listing->google_map }}"
                                        placeholder="Google Map Link">
                                    @error('google_map')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                         <div class="form-group">
                     <label class="form-label" for="property_type">Property Type</label>
      

                                     <input type="text" class="form-control" id="property_type" name="property_type"
                                        value="{{ $listing->property_type }}"
                                        placeholder="Property Type">
                            @error('property_type')
                                    <span class="invalid">{{ $message }}</span>
                            @enderror
                        </div>
                                </div>        


<div class="col-md-2 mt-5">
    
<div class="form-check">
        <input type="checkbox" class="form-check-input" id="is_allow_pets" name="is_allow_pets" value="1"
            {{ old('is_allow_pets', $listing->is_allow_pets) == 1 ? 'checked' : '' }}>
        <label class="form-check-label" for="is_allow_pets">Allow Pets</label>
    </div>

    <div class="form-check">
        
        <input type="checkbox" class="form-check-input" id="is_self_check_in" name="is_self_check_in" value="1"
            {{ old('is_self_check_in', $listing->is_self_check_in) == 1 ? 'checked' : '' }}>
        <label class="form-check-label" for="is_self_check_in">Self Check-In</label>
    </div>

    <div class="form-check">
    
        <input type="checkbox" class="form-check-input" id="living_room" name="living_room" value="1"
        {{ old('living_room', $listing->living_room) == 1 ? 'checked' : '' }}>
        <label class="form-check-label" for="living_room">Living Room</label>
    
    </div>


</div>

                <div class="col-md-2 mt-5">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="laundry_area" name="laundry_area" value="1"
                                    {{ old('laundry_area', $listing->laundry_area) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="laundry_area">Laundry Area</label>
                                </div>


                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="corridor" name="corridor" value="1"
                                    {{ old('corridor', $listing->corridor) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="corridor">Corridor</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="outdoor_area" name="outdoor_area" value="1"
                                    {{ old('outdoor_area', $listing->outdoor_area) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="outdoor_area">Outdoor Area</label>
                                </div>
                    </div>

                <div class="col-md-2 mt-5">

                    <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="kitchen" name="kitchen" value="1"
                            {{ old('kitchen', $listing->kitchen) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="kitchen">Kitchen</label>
                    </div>
                    
                </div>


<div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="discounts">Discount</label>
                                    <input type="number" class="form-control" id="discounts" name="discounts" value="{{ old('discounts', $listing->discounts) }}">
                                    @error('discounts')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                    </div>

                    <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="tax">Tax</label>
                                    <input type="number" class="form-control" id="tax" name="tax" value="{{ old('tax', $listing->tax) }}">
                                    @error('tax')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                    </div>

                    <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="cleaning_fee">Cleaning Fee</label>
                                    <input type="number" class="form-control" id="cleaning_fee" name="cleaning_fee" value="{{ old('cleaning_fee', $listing->cleaning_fee) }}">
                                    @error('cleaning_fee')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                    </div>

                            

                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="button" class="btn btn-primary" id="btnupdateaddtionalfields">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-bordered mt-3">
                <div class="card-inner">
                    <h5 class="mb-1">Gathern Ical Link</h5>

                    <form action="{{ route('listing.storeListingIcal', $listing_settings['listing_id']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="url">Url</label>
                                    <input type="text" class="form-control" id="url"
                                        name="url" value="{{ isset($listingIcalLink->url) ? $listingIcalLink->url : '' }}"
                                        placeholder="Guest Included">
                                    @error('url')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="active">Status</label>
                                    <select name="active" id="active" class="form-control">
                                        <option value="1" {{ isset($listingIcalLink->active) && $listingIcalLink->active == '1' ? 'selected': '' }} selected>Yes</option>
                                        <option value="0"  {{ isset($listingIcalLink->active) && $listingIcalLink->active == '0' ? 'selected': '' }}>No</option>
                                    </select>
                                    @error('active')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                @enderror
                                </div>
                            </div>
                            
                            
                            <div class="col-md-12 mt-3 ">
                                <div class="form-group text-start float-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="col-md-3">
                        @if (isset($listingIcalLink->url))
                        {{-- {{ dd($listingIcalLink->token) }} --}}
                            <button class="btn btn-primary mt-4" onclick="copyToClipboard('{{ url('/ical/' . $listingIcalLink->token . '.ics') }}')">
                                Copy Livedin iCal Link
                            </button>
                    
                            <script>
                                function copyToClipboard(text) {
                                    navigator.clipboard.writeText(text).then(function() {
                                        alert('iCal link copied to clipboard!');
                                    }, function(err) {
                                        alert('Failed to copy the link');
                                        console.error('Copy failed: ', err);
                                    });
                                }
                            </script>
                        @endif
                    </div>
                    
                </div>
            </div>

            <div class="card card-bordered mt-3">
                <div class="card-inner">
                    <h5 class="mb-1">Listing Settings</h5>
                    <hr>
                    <form action="{{ route('listing.settings.update', $listing_settings['id']) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="instant_booking">Instant Booking</label>
                                <select name="instant_booking" id="instant_booking" class="form-control">
                                    <option value="" selected disabled>Select Instant Booking Option</option>
                                    <option value="off"
                                        {{ $listing_settings['instant_booking'] == 'off' ? 'selected' : '' }}>Off</option>
                                    <option value="everyone"
                                        {{ $listing_settings['instant_booking'] == 'everyone' ? 'selected' : '' }}>everyone
                                    </option>
                                    <option value="well_reviewed_guests"
                                        {{ $listing_settings['instant_booking'] == 'well_reviewed_guests' ? 'selected' : '' }}>
                                        Well reviewed guests</option>
                                    <option value="guests_with_verified_identity"
                                        {{ $listing_settings['instant_booking'] == 'guests_with_verified_identity' ? 'selected' : '' }}>
                                        Guests with verified identity</option>
                                    <option value="well_reviewed_guests_with_verified_identity"
                                        {{ $listing_settings['instant_booking'] == 'well_reviewed_guests_with_verified_identity' ? 'selected' : '' }}>
                                        Guests with verified identity</option>
                                </select>
                            </div>
                            {{-- {{ dd($listing_settings) }} --}}
                            @php $standard_fees = '' @endphp
                            @foreach ($listing_settings['ch_pricing_settings'] as $key=>$item)
                            {{-- {{ dd($listing, $key) }} --}}
                            @php

                                if(is_array($item)) {
                                    if($key == 'default_pricing_rules' || $key == 'pass_through_taxes') {
                                        continue;
                                    }
                                    if(isset($key) && $key == 'standard_fees') {

                                        foreach ($item as $index => $value) {
                                            // dd($value,$index);
                                            $standard_fees .= '<div class="col-md-4 mt-3">
                                                                    <div class="form-group">
                                                                        <label class="form-label" for="' . $value["fee_type"] . '">' . $value["fee_type"] . '</label>
                                                                        <input type="text" class="form-control" id="' . $value["fee_type"] . '"
                                                                            name="standard_fees['.$value["fee_type"].']" value="' . $value["amount"] . '"
                                                                            placeholder="' . $value["fee_type"] . '">

                                                                    </div>
                                                                </div>';
                                        }
                                        // dd($standard_fees);

                                        // $standard_fees =
                                    }
                                    // dd($item, $key);
                                    // continue;
                                }
                            @endphp
                            @php
                                if(!is_array($item)) {

                            @endphp
                                <div class="col-md-4 mt-3">
                                    <div class="form-group">
                                        <label class="form-label" for="{{ $key }}">{{ $key  }}</label>
                                        <input type="text" class="form-control" id="{{ $key }}"
                                            name="{{ $key }}" value="{{ $item}}"
                                            placeholder="{{ $key }}">
                                        @error($key)
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @php

                            }
                            @endphp

                            @endforeach
                            {{-- {{ dd($standard_fees) }} --}}
                            {!! $standard_fees !!}
                            @php
                                // foreach ($standard_fees as $key => $standard_fees) {
                                //     // dd($standard_fees);
                                // }
                            @endphp
                            {{-- {{ dd($standard_fees) }} --}}
                            {{-- <div class="col-md-4">
                                <label for="listing_currency">Listing Currency</label>
                                <select name="listing_currency" id="listing_currency" class="form-control">
                                    <option value="" selected disabled>Select Currency</option>
                                    <option value="EUR"
                                        {{ $listing_settings->listing_currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="PKR"
                                        {{ $listing_settings->listing_currency == 'PKR' ? 'selected' : '' }}>PKR</option>
                                    <option value="USD"
                                        {{ $listing_settings->listing_currency == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="SAR"
                                        {{ $listing_settings->listing_currency == 'SAR' ? 'selected' : '' }}>SAR</option>
                                </select>
                            </div> --}}
                            <input type="hidden" name="rate_plan_id" value="{{ $rate_plan }}">

                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="default_daily_price">Default Daily Price</label>
                                    <input type="number" class="form-control" id="default_daily_price"
                                        name="default_daily_price" value="{{ $listing_settings->default_daily_price }}"
                                        placeholder="Default Daily Price">
                                    @error('default_daily_price')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}
                            {{-- <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="guests_included">Guest Included</label>
                                    <input type="number" class="form-control" id="guests_included"
                                        name="guests_included" value="{{ $listing_settings->guests_included }}"
                                        placeholder="Guest Included">
                                    @error('guests_included')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="weekend_price">Weekend Price</label>
                                    <input type="number" class="form-control" id="weekend_price" name="weekend_price"
                                        value="{{ $listing_settings->weekend_price }}" placeholder="Weekend Price">
                                    @error('weekend_price')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="weekly_price_factor">Weekly Price Factor</label>
                                    <input type="number" class="form-control" id="weekly_price_factor"
                                        name="weekly_price_factor" value="{{ $listing_settings->weekly_price_factor }}"
                                        placeholder="Weekly price factor">
                                    @error('weekly_price_factor')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="monthly_price_factor">Monthly Price Factor</label>
                                    <input type="number" class="form-control" id="monthly_price_factor"
                                        name="monthly_price_factor" value="{{ $listing_settings->monthly_price_factor }}"
                                        placeholder="Monthly Price Factor">
                                    @error('monthly_price_factor')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_linen_fee">Pass through linen fee</label>
                                    <input type="number" class="form-control" id="pass_through_linen_fee"
                                        name="pass_through_linen_fee"
                                        value="{{ $listing_settings->pass_through_linen_fee }}"
                                        placeholder="Pass through linen fee">
                                    @error('pass_through_linen_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_security_deposit">Pass Through Security
                                        Deposit</label>
                                    <input type="number" class="form-control" id="pass_through_security_deposit"
                                        name="pass_through_security_deposit"
                                        value="{{ $listing_settings->pass_through_security_deposit }}"
                                        placeholder="Pass Through Security Deposit">
                                    @error('pass_through_security_deposit')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_resort_fee">Pass Through Resort
                                        Fee</label>
                                    <input type="number" class="form-control" id="pass_through_resort_fee"
                                        name="pass_through_resort_fee"
                                        value="{{ $listing_settings->pass_through_resort_fee }}"
                                        placeholder="Pass Through Resort Fee">
                                    @error('pass_through_resort_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_community_fee">Pass Through Community
                                        Fee</label>
                                    <input type="number" class="form-control" id="pass_through_community_fee"
                                        name="pass_through_community_fee"
                                        value="{{ $listing_settings->pass_through_community_fee }}"
                                        placeholder="Pass Through Community Fee">
                                    @error('pass_through_community_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_pet_fee">Pass Through Pet Fee</label>
                                    <input type="number" class="form-control" id="pass_through_pet_fee"
                                        name="pass_through_pet_fee" value="{{ $listing_settings->pass_through_pet_fee }}"
                                        placeholder="Pass Through Pet Fee">
                                    @error('pass_through_pet_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_cleaning_fee">Pass Through Cleaning
                                        Fee</label>
                                    <input type="number" class="form-control" id="pass_through_cleaning_fee"
                                        name="pass_through_cleaning_fee"
                                        value="{{ $listing_settings->pass_through_cleaning_fee }}"
                                        placeholder="Pass Through Cleaning Fee">
                                    @error('pass_through_cleaning_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="price_per_extra_person">Price Per Extra Person</label>
                                    <input type="number" class="form-control" id="price_per_extra_person"
                                        name="price_per_extra_person"
                                        value="{{ $listing_settings->price_per_extra_person }}"
                                        placeholder="Price Per Extra Person">
                                    @error('price_per_extra_person')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="pass_through_short_term_cleaning_fee">Pass Through
                                        Short Term Cleaning Fee</label>
                                    <input type="number" class="form-control" id="pass_through_short_term_cleaning_fee"
                                        name="pass_through_short_term_cleaning_fee"
                                        value="{{ $listing_settings->pass_through_short_term_cleaning_fee }}"
                                        placeholder="Pass Through Short Term Cleaning Fee">
                                    @error('pass_through_short_term_cleaning_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-group">
                                    <label class="form-label" for="cleaning_fee">Cleaning Fee</label>
                                    <input type="number" class="form-control" id="cleaning_fee" name="cleaning_fee"
                                        value="{{ $listing_settings->cleaning_fee }}" placeholder="Cleaning Fee">
                                    @error('cleaning_fee')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}

                            <div class="col-md-12 mt-3 ">
                                <div class="form-group text-start float-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card card-bordered mt-3">
                <div class="card-inner">
                    <h5 class="mb-1">Listing Discount Settings</h5>
                    <hr>
                    <form action="{{ route('listing.discounts.update') }}" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <!-- Early Bird Section -->
                            <div class="col-md-6">
                                <input type="hidden" value="{{ $listing['listing_id'] }}" name="listing_id" />
                                <h6>Early Bird Discount</h6>
                                <div class="form-group mt-3">
                                    <label for="early_bird_price_change" class="form-label">Price Change (%)</label>
                                    <input type="number" id="early_bird_price_change" name="early_bird_price_change" value="{{ !empty($listing_discount['early_bird']['price_change']) ? $listing_discount['early_bird']['price_change'] : 0 }}" class="form-control" placeholder="Enter price change" required>
                                    
                                </div>
                                <div class="form-group mt-3">
                                    <label for="early_bird_threshold_one" class="form-label">Days before arrival</label>
                                    <select id="early_bird_threshold_one" name="early_bird_threshold_one" class="form-control" required>
                                        <option value="" disabled selected>Select number of days</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i * 30 }}" 
                                                {{ (!empty($listing_discount['early_bird']['threshold_one']) && $listing_discount['early_bird']['threshold_one'] == $i * 30) ? 'selected' : '' }}>
                                                {{ $i * 30 }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>                                
                            </div>
            
                            <!-- Last Minute Section -->
                            <div class="col-md-6">
                                <h6>Last Minute Discount</h6>
                                <div class="form-group mt-3">
                                    <label for="last_minute_price_change" class="form-label">Price Change (%)</label>
                                    <input type="number" id="last_minute_price_change" name="last_minute_price_change" class="form-control" 
                                    value="{{ !empty($listing_discount['last_minute']['price_change']) ? $listing_discount['last_minute']['price_change'] : 0 }}"placeholder="Enter price change" required>
                                </div>
                                <div class="form-group mt-3">
                                    <label for="last_minute_threshold_one" class="form-label">Days before arrival</label>
                                    <input type="number" id="last_minute_threshold_one" name="last_minute_threshold_one" class="form-control" 
                                    value="{{ !empty($listing_discount['last_minute']['threshold_one']) ? $listing_discount['last_minute']['threshold_one'] : 0 }}"
                                    placeholder="Enter threshold value" required>
                                </div>
                            </div>
                        </div>
            
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>            

            <div class="card card-bordered mt-3">
                <div class="card-inner">
                   
                   
                    <div class="row">
                         <div class="col-6">
                         <h5 class="mb-1">Assign Host</h5>
                         <hr>
                         <form action="{{ route('listing.update', $listing->id) }}" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="host_id">Select Host</label>
                                <select name="host_id" id="host_id" class="form-control select2">
                                    <option value="" selected disabled>Select Host</option>
                                    @foreach ($users as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} {{ $item->surname }} {{ $item->host_key }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4  mt-3">
                                <div class="form-group text-start">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                         </div>
                         <div class="col-6">
                         <h5 class="mb-1">Assign Experience Managers</h5>
                         <hr>
                         <form action="{{ route('listing.updatemanager', $listing->id) }}" method="POST">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="exp_manager_id">Select Exp Managers</label>
                                <select name="exp_manager_id" id="exp_manager_id" class="form-control select2">
                                    <option value="" selected disabled>Select Experience Managers</option>
                                    @foreach ($experiencemanagers as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} {{ $item->surname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4  mt-3">
                                <div class="form-group text-start">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                         </div>
                    </div>   
                   
                </div>
            </div>

<div class="card card-bordered mt-3">
             <div class="row">
            <div class="col-6">
                <div class="card-inner">
                    <h5 class="mb-1">Listing Hosts</h5>
                    <div class="row ml-3" style="margin-left: 0">
                        @php
                            $listing_host = json_decode($listing->user_id);
                            //                           dd($listing_host);
                        @endphp
                        @foreach ($listing_host as $item)
                            @php
                                $user = \App\Models\User::where('id', $item)->first();
                            @endphp
                             @if ($user)
                            <div class="col-md-2 mt-2"
                                style="position:relative;background: green; color: white; border-radius: 5px; padding: 10px;">
                                {{ $user->name }} {{ $user->surname }}
                                <form action="{{ route('listing.destroy', $listing->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <button type="submit"
                                        style="border: 0;color:white;position: absolute; background: red; top: 0; right: 0; width: 20px; height: 20px; text-align: center; border-radius: 15px;">
                                        X
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-1"></div>
                             @else
        <div class="col-md-2 mt-2" style="background: gray; color: white; padding: 10px;">
            User Not Found
        </div>
    @endif
                            
                        @endforeach
                    </div>
                </div>


            </div>

             <div class="col-6">
                <div class="card-inner">
                    <h5 class="mb-1">Experience Managers</h5>
                    <div class="row ml-3" style="margin-left: 0">
                    @php
                $listing_manager = json_decode($listing->exp_managers, true); // Decode as associative array
                 @endphp

                @if (!empty($listing_manager) && is_array($listing_manager))
                @foreach ($listing_manager as $item)
                @php
                     $user = \App\Models\User::where('id', $item)->first();
                @endphp

                @if ($user)
                    <div class="col-md-2 mt-2"
                        style="position:relative;background: green; color: white; border-radius: 5px; padding: 10px;">
                    {{ $user->name }} {{ $user->surname }}
                    <form action="{{ route('listing.expmanagerdelete', $listing->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="exp_manager_id" value="{{ $user->id }}">
                    <button type="submit"
                        style="border: 0;color:white;position: absolute; background: red; top: 0; right: 0; width: 20px; height: 20px; text-align: center; border-radius: 15px;">
                        X
                    </button>
                </form>
            </div>
            <div class="col-md-1"></div>
                 @endif
                    @endforeach
                @else
                    <p>No managers found.</p>
                @endif

</div>
            
        </div>
    </div>

    <script>
        let amount_type = document.getElementById('amount_type');
        let amount = document.getElementById('amount');

        function checkPercentageValue(e) {
            // alert(amount_type.value);
            if (amount_type.value === 'percentage' && e > 100) {
                // alert("In")
                alert("percentage Value Can Not Be Greater Than 100 !!!");
                amount.value = 0;
            }
        }
    </script>
    
    
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>


$(document).ready(function () {
    
    $('#hostaboard_id').change(function () {
        var selectedId = $(this).val(); // Get the selected ID

        if (selectedId) {
            // Send the AJAX request to get data
            $.ajax({
                url: "{{ route('activation.getactivationdetail') }}",
                type: "POST",
                data: {
                    
                    id: selectedId  
                },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        // Populate the fields with the returned data
                        $('#be_listing_name').val(response.data.be_listing_name);
                        $('#property_about').val(response.data.property_about);
                        $('#bedrooms').val(response.data.bedrooms);
                        $('#beds').val(response.data.beds);
                        $('#bathrooms').val(response.data.bathrooms);
                        $('#city_name').val(response.data.city_name);
                        $('#district').val(response.data.district);
                        $('#street').val(response.data.street);
                        $('#property_type').val(response.data.type);
                        
                        $('#is_allow_pets').prop('checked', response.data.is_allow_pets == 1);
                        $('#is_self_check_in').prop('checked', response.data.is_self_check_in == 1);

                        $('#living_room').prop('checked', response.data.living_room == 1);
                        $('#laundry_area').prop('checked', response.data.laundry_area == 1);

                        $('#corridor').prop('checked', response.data.corridor == 1);
                        $('#outdoor_area').prop('checked', response.data.outdoor_area == 1);
                        $('#kitchen').prop('checked', response.data.kitchen == 1);


                        $('#cleaning_fee').val(response.data.cleaning_fee);
                        $('#discounts').val(response.data.discounts);
                        $('#tax').val(response.data.tax);
                        $('#activation_google_map').val(response.data.property_google_map_link);
                    } else {
                        alert('No data found');
                    }
                },
                error: function () {
                    alert('Failed to fetch data');
                }
            });
        } else {
            alert('Please select a valid ID');
        }
    });


    $('#btnupdateaddtionalfields').on('click', function() {
    
    var listingId = $('#listing_id').val();

    var hostaboard_id 
    var propertyAbout = $('#property_about').val();
    var beListingName = $('#be_listing_name').val();
    var bedrooms = $('#bedrooms').val();
    var beds = $('#beds').val();
    var bathrooms = $('#bathrooms').val();
    var cityName = $('#city_name').val();
    var district = $('#district').val();
    var street = $('#street').val();
    var propertyType = $('#property_type').val();
    
    var isAllowPets = $('#is_allow_pets').prop('checked') ? 1 : 0;
    var isSelfCheckIn = $('#is_self_check_in').prop('checked') ? 1 : 0;
    
    var living_room = $('#living_room').prop('checked') ? 1 : 0;
    
    var laundry_area = $('#laundry_area').prop('checked') ? 1 : 0;
    var corridor = $('#corridor').prop('checked') ? 1 : 0;
    var outdoor_area = $('#outdoor_area').prop('checked') ? 1 : 0;
    var kitchen = $('#kitchen').prop('checked') ? 1 : 0;
    //alert(kitchen);
    
    var cleaningFee = $('#cleaning_fee').val();
    var discounts = $('#discounts').val();
    var tax = $('#tax').val();
    var googleMap = $('#google_map').val();
    var activationid = $('#hostaboard_id :selected').val();

    // Prepare the data object
    var data = {
        listingId: listingId,
        property_about: propertyAbout,
        be_listing_name: beListingName,
        bedrooms: bedrooms,
        beds: beds,
        bathrooms: bathrooms,
        city_name: cityName,
        district: district,
        street: street,
        property_type: propertyType,
        is_allow_pets: isAllowPets,
        is_self_check_in: isSelfCheckIn,
        
         

        cleaning_fee: cleaningFee,
        discounts: discounts,
        tax: tax,
        google_map: googleMap,
        activationid: activationid,
        
        living_room : living_room,
        laundry_area : laundry_area,
        corridor : corridor,
        outdoor_area : outdoor_area,
        kitchen : kitchen
    };

        $.ajax({
                url: "{{ route('activation.updateactivationdetail') }}",
                type: "POST",
                data: data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        
                        //alert(response.message);
                        location.reload();  

                    }
                    else{

                    } 
                },
                error: function () {
                    alert('Failed to update data');
                }
            });



    });




});





</script>
@endsection
