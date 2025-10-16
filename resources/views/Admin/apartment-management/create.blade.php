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
                    
                    <form action="{{route('apartment-management.store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4">
                            

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
                                    <label class="form-label" for="title">Apartment Title</label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Apartment Title">
                                    @error('title')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Apartment #</label>
                                    <input type="text" class="form-control" id="apartment_num" name="apartment_num">
                                    @error('apartment_num')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount_type">Commission Amount Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="commission_type" id="amount_type"
                                            data-placeholder="Amount Type">
                                            <option value="" selected disabled>Amount Type</option>
                                            <option value="percentage">Percentage</option>
                                            <option value="fixed">Fixed</option>
                                        </select>
                                        @error('amount_type')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount">Commission </label>
                                    <input type="text" class="form-control" id="amount" name="commission_value"
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
                                            data-placeholder="Churned">
                                           
                                            <option value="0">Live</option>
                                            <option value="1">Churned</option>
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
                                    <input type="text" class="form-control" id="google_map" name="google_map"
                                        
                                        placeholder="Google Map Link">
                                    @error('google_map')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            

<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="district">District</label>
        <select class="form-control select2" id="district" name="district" >
            <option value="">-- Select District --</option>
            <option value="Al Aj">Al Aj</option>
            <option value="Al Aqiq">Al Aqiq</option>
            <option value="Al Arid">Al Arid</option>
            <option value="Al Falah">Al Falah</option>
            <option value="Al Malqa">Al Malqa</option>
            <option value="Al Murooj">Al Murooj</option>
            <option value="Al Olaya">Al Olaya</option>
            <option value="Al Qirawan">Al Qirawan</option>
            <option value="Al Rabi">Al Rabi</option>
            <option value="Al Rawdah">Al Rawdah</option>
            <option value="Al Rimal">Al Rimal</option>
            <option value="Al Sahafa">Al Sahafa</option>
            <option value="Al Yasmin">Al Yasmin</option>
            <option value="Al-Aqiq District">Al-Aqiq District</option>
            <option value="Al-Nargis">Al-Nargis</option>
            <option value="Al-Narjis">Al-Narjis</option>
            <option value="Al-Olaya, King Fahad District">Al-Olaya, King Fahad District</option>
            <option value="Almalqa">Almalqa</option>
            <option value="Alnarjes">Alnarjes</option>
            <option value="An Narjis">An Narjis</option>
            <option value="Aqiq">Aqiq</option>
            <option value="As Sulimaniyah">As Sulimaniyah</option>
            <option value="Banban">Banban</option>
            <option value="Hittin">Hittin</option>
            <option value="Narjis">Narjis</option>
            <option value="Qurtubah">Qurtubah</option>
            <option value="Sulaymania">Sulaymania</option>
            <option value="Suleimaniyah">Suleimaniyah</option>
            <option value="Yarmukh">Yarmukh</option>
        </select>
        @error('district')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="street">Street</label>
                                    <input type="text" class="form-control" id="street" name="street" >
                                    @error('street')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="city_name">City Name</label>
        <select class="form-control" id="city_name" name="city_name">
            
            <option value="Riyadh">Riyadh</option>
        </select>
        @error('city_name')
            <span class="invalid">{{ $message }}</span>
        @enderror
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
                                    <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Zip Longitude">
                                    @error('longitude')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="latitude">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Zip Latitude">
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

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="be_listing_name">Booking Engine Name</label>
                                    <input type="text" class="form-control" id="be_listing_name" name="be_listing_name"
                                       >
                                    @error('be_listing_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_about">About Property</label>
                                    <textarea class="form-control" id="property_about" name="property_about"></textarea>
                                    @error('property_about')
                                        <span class="invalid">{{ $message }}</span>
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

                            

                            <div class="col-md-2 mt-5">
    
                            
<div class="form-check">
        <input type="checkbox" class="form-check-input" id="is_allow_pets" name="is_allow_pets" value="1">
        <label class="form-check-label" for="is_allow_pets">Allow Pets</label>
    </div>

    <div class="form-check">
        
        <input type="checkbox" class="form-check-input" id="is_self_check_in" name="is_self_check_in" value="1">
        <label class="form-check-label" for="is_self_check_in">Self Check-In</label>
    </div>

    <div class="form-check">
    
        <input type="checkbox" class="form-check-input" id="living_room" name="living_room" value="1">
        <label class="form-check-label" for="living_room">Living Room</label>
    
    </div>


</div>

                <div class="col-md-2 mt-5">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="laundry_area" name="laundry_area" value="1">
                                    <label class="form-check-label" for="laundry_area">Laundry Area</label>
                                </div>


                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="corridor" name="corridor" value="1">
                                    <label class="form-check-label" for="corridor">Corridor</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="outdoor_area" name="outdoor_area" value="1">
                                    <label class="form-check-label" for="outdoor_area">Outdoor Area</label>
                                </div>
                    </div>

                <div class="col-md-2 mt-5">

                    <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="kitchen" name="kitchen" value="1">
                            <label class="form-check-label" for="kitchen">Kitchen</label>
                    </div>
                    
</div>


<div class="row gy-4">
                            <!-- Other fields here -->
<div class="col-md-12">
    <div class="form-group">
        <label class="form-label" for="amenities">Amenities</label>
        <div class="row">
        @php
    $amenities = [
        'Air conditioner', 'Wifi', 'T.V', 'Heater', 'Kitchen', 'Microwave', 'Fridge',
        'Kettle', 'Coffee Maker', 'Washer Machine', 'Hair Dryer', 'Iron', 'Essential',
        'Shampoo', 'Smoke Alarm', 'Fire Extinguisher', 'First Aid Kit', 'Outdoor Dining Area',
        'Pool', 'Private Entrance', 'Self Check-in', 'Free Parking Premises','Sink','Hotplate', 'Other'
    ];
@endphp

@foreach($amenities as $amenity)
    <div class="col-md-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="amenity_{{ $loop->index }}" name="amenities[]" value="{{ $amenity }}">
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
                                    <input type="number" class="form-control" id="price" name="price" placeholder="Price">
                                    @error('Price')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="cleaning_fee">Cleaning Fee</label>
                                    <input type="number" class="form-control" id="cleaning_fee" name="cleaning_fee" >
                                    @error('cleaning_fee')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="discounts">Discount</label>
                                    <input type="number" class="form-control" id="discounts" name="discounts" >
                                    @error('discounts')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="tax">Tax</label>
                                    <input type="number" class="form-control" id="tax" name="tax" >
                                    @error('tax')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="checkin_time">Check-in Time</label>
        <input type="time" class="form-control" id="checkin_time" name="checkin_time">
        @error('checkin_time')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="checkout_time">Check-out Time</label>
        <input type="time" class="form-control" id="checkout_time" name="checkout_time">
        @error('checkout_time')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="minimum_days_stay">Minimum days stay</label>
        <select class="form-control select2" id="minimum_days_stay" name="minimum_days_stay" >
           
            <option value="1" selected>1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option> 
            <option value="5">5</option>
    <option value="6">6</option>
<option value="7">7</option>
<option value="8">8</option>
<option value="9">9</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
<option value="24">24</option>
<option value="25">25</option>
<option value="26">26</option>
<option value="27">27</option>
<option value="28">28</option>
<option value="29">29</option>
<option value="30">30</option>
<option value="31">31</option>      


        </select>
        @error('minimum_days_stay')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="is_long_term">Stay Type</label>
        <select class="form-control select2" id="is_long_term" name="is_long_term" >
    
            <option value="0" selected>Short Term</option>
            <option value="1">Long Term</option>
            
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
                                    <option value="" selected disabled>Select Host</option>
                                    @foreach ($users as $item)
                                        <option value="{{ $item->id }}" data-name="{{ $item->name }} {{ $item->surname }}">{{ $item->name }} {{ $item->surname }} {{ $item->host_key }}
                                        </option>
                                    @endforeach
                                </select>
                                 
                           

                               

                            </div>
                            <div class="col-md-4  mt-3">
                                <div class="form-group text-start">
                                    
                                    <button type="button" class="btn btn-primary" onclick="addHost()">Add Host</button>
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
                                    <option value="" selected disabled>Select Experience Managers</option>
                                    @foreach ($experiencemanagers as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} {{ $item->surname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4  mt-3">
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

        {{-- Hosts Column --}}
        <div class="col-md-6">
            <div class="card-inner">
                <h5 class="mb-3">Selected Hosts</h5>
                <div class="row g-2" id="selected-hosts" style="min-height: 50px; border: 1px solid #e5e5e5; padding: 10px; border-radius: 5px;"></div>
            </div>
        </div>

        {{-- Managers Column --}}
        <div class="col-md-6">
            <div class="card-inner">
                <h5 class="mb-3">Selected Experience Managers</h5>
                <div class="row g-2" id="selected-managers" style="min-height: 50px; border: 1px solid #e5e5e5; padding: 10px; border-radius: 5px;"></div>
            </div>
        </div>

    </div>
</div>


<div class="col-md-12" style="display:none">
    <div class="form-group">
        <label class="form-label" for="cover_image">Cover Image</label>
        <input type="file" class="form-control" name="cover_image" accept="image/*">
        @error('cover_image')
        <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

@php
    $rooms = ['Bedroom 1', 'Bedroom 2', 'Bedroom 3', 'Bathroom 1', 'Bathroom 2', 'Bathroom 3', 'Living Room', 'Kitchen'];
@endphp

@foreach($rooms as $index => $room)
<div class="col-md-6" style="display:none">
    <div class="form-group">
        <label class="form-label">{{ $room }}</label>
        <input type="file" class="form-control" name="room_images[{{ $room }}][]" multiple accept="image/*">
        @error('room_images.' . $room)
        <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>
@endforeach

<div class="col-md-12" style="display:none">
    <div class="form-group">
        <label class="form-label" for="apartment_image">Other Images</label>
        <input type="file" class="form-control" name="apartment_image[]" multiple accept="image/*">
        @error('apartment_image')
        <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>




                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Next</button>
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

@endsection
