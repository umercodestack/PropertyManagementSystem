@extends('Admin.layouts.app')
@section('content')

<head>
    <style>
        .select2-selection__rendered 
        {
            display: flex !important;
        }
        .select2-container--default .select2-selection--multiple
        {
            height:auto !important;
        }
    </style>
</head>   
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Activation Form</h3>
                </div>
            </div>
        </div>
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('hostaboard.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4">


                        <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Host Info</label>


                        <div class="col-md-6">

                             <div class="form-group">
                                <label class="form-label" for="property_manager" > Sales Manager <span class="text-danger">*</span></label>
                                <select name="property_manager" id="property_manager" class="form-control select2"  data-placeholder="Select Sales Manager" required>
           
                                <option value="" disabled selected>Select Sales Manager</option> <!-- Placeholder Option -->
                                    @foreach($propertyManagers as $manager)
                                    <option value="{{ $manager->id }}" {{ $manager->id == 0 ? 'selected' : '' }}>
                                        {{ $manager->name }}
                                        {{ $manager->surname }}
                                </option>
                        @endforeach


                    </select>
                    @error('property_manager')
                        <span class="invalid">{{ $message }}</span>
                    @enderror
                </div>
    
                         </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="host_id">Host ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="host_id" name="host_id" value="{{ old('host_id') }}"  maxlength="10" placeholder="eg: KSA-103" required>
                                    @error('host_id')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_id">Property ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="property_id" name="property_id" value="{{ old('property_id') }}" maxlength="10" placeholder="LP-XXX (e.g. LP-101) | Add 'H' for cohosted (e.g. LP-101H)" required>
                                    @error('property_id')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="title" required>
                                    @error('title')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                             <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="owner_name">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" placeholder="eg: john" required>
                                    @error('owner_name')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="eg: smith" required>
                                    @error('last_name')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                              <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="host_number">Contact Number <span class="text-danger">*</span></label>
        <input type="tel"
               class="form-control"
               id="host_number"
               name="host_number"
               maxlength="13"
               pattern="^\+966\d{9}$"
               placeholder="e.g. +966512345678"
               title="Must start with +966 followed by 9 digits"
               value="{{ old('host_number') }}" required>
        @error('host_number')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

                           <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="host_email">Email <span class="text-danger">*</span></label>
        <input type="email"
               class="form-control"
               id="host_email"
               name="host_email"
               placeholder="e.g. name@example.com"
               title="Must be a valid email with @"
               value="{{ old('host_email') }}">
        <small id="emailError" style="color:red; display:none;">Invalid email format</small>
        @error('host_email')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

    
<div class="col-md-6">
        <div class="form-group">
                <label class="form-label" for="date_of_birth">Date Of Birth 

                <span class="text-danger d-none" id="date_of_birth_asterisk">*</span>
                </label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" >
                    @error('date_of_birth')
                        <span class="invalid">{{ $message }}</span>
                    @enderror
                </div>
</div>



                          

                            
    <hr>
   
    <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Host Bank Details</label>

    <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="host_bank_detail">Account Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="host_bank_detail" name="host_bank_detail" value="{{ old('host_bank_detail') }}" required placeholder="Bank Account Title">
                                    @error('host_bank_detail')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="bank_name">Bank Name <span class="text-danger">*</span></label>
                                     <select class="form-control select2" id="bank_name" name="bank_name" required placeholder="Select Bank">
                                        <option value="" disabled selected>Select Bank </option>
                                        <option value="Al Rajhi Bank" {{ old('bank_name') == 'Al Rajhi Bank' ? 'selected' : '' }}>Al Rajhi Bank</option>
                                        <option value="Albilad Bank" {{ old('bank_name') == 'Albilad Bank' ? 'selected' : '' }}>Albilad Bank</option>
                                        <option value="The Saudi National Bank" {{ old('bank_name') == 'The Saudi National Bank' ? 'selected' : '' }}>The Saudi National Bank</option>
                                        <option value="Bank AlJazira" {{ old('bank_name') == 'Bank AlJazira' ? 'selected' : '' }}>Bank AlJazira</option>

                                        <option value="Alinma Bank" {{ old('bank_name') == 'Alinma Bank' ? 'selected' : '' }}>Alinma Bank</option>
                                        <option value="Riyadh Bank" {{ old('bank_name') == 'Riyadh Bank' ? 'selected' : '' }}>Riyadh Bank</option>
                                        <option value="Banque Saudi Fransi" {{ old('bank_name') == 'Banque Saudi Fransi' ? 'selected' : '' }}>Banque Saudi Fransi</option>
                                        <option value="The Saudi Investment Bank" {{ old('bank_name') == 'The Saudi Investment Bank' ? 'selected' : '' }}>The Saudi Investment Bank</option>

                                        <option value="Saudi Awwal Bank (SAB)" {{ old('bank_name') == 'Saudi Awwal Bank (SAB)' ? 'selected' : '' }}>Saudi Awwal Bank (SAB)</option>
                                        <option value="Gulf International Bank (MEB)" {{ old('bank_name') == 'Gulf International Bank (MEB)' ? 'selected' : '' }}>Gulf International Bank (MEB)</option>
                                    </select>

                                    @error('bank_name')
                                        <span class="invalid">{{ $message }}</span>
                                         @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="iban_no">IBAN No <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control"
               id="iban_no"
               name="iban_no"
               maxlength="24"
               minlength="24"
               pattern="^SA\d{22}$"
               required
               placeholder="e.g. SA1234567890123456789012"
               title="IBAN must start with 'SA' and be exactly 24 characters"
               value="{{ old('iban_no') }}">
        <small id="ibanError" style="color:red; display:none;">IBAN must start with 'SA' and be 24 characters long.</small>
        @error('iban_no')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="swift_code">Swift Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="swift_code" name="swift_code" value="{{ old('swift_code') }}" required placeholder="Must be a valid SWIFT/BIC format">
                                    @error('swift_code')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

 <hr>

  <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Property Details</label>


  <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
        <select class="form-control select2" id="type" name="type" required placeholder="Select Property Type">
            <option value="" disabled selected>Select Property Type *</option>
            <option value="Apartment" {{ old('type') == 'Apartment' ? 'selected' : '' }}>Apartment</option>
            <option value="Land" {{ old('type') == 'Land' ? 'selected' : '' }}>Land</option>
            <option value="Villa" {{ old('type') == 'Villa' ? 'selected' : '' }}>Villa</option>
            <option value="Floor" {{ old('type') == 'Floor' ? 'selected' : '' }}>Floor</option>
            <option value="Building" {{ old('type') == 'Building' ? 'selected' : '' }}>Building</option>
            <option value="Studio" {{ old('type') == 'Studio' ? 'selected' : '' }}>Studio</option>
            <option value="Tower" {{ old('type') == 'Tower' ? 'selected' : '' }}>Tower</option>
            <option value="Hotel" {{ old('type') == 'Hotel' ? 'selected' : '' }}>Hotel</option>
            <!-- Add more options as needed -->
        </select>
        @error('type')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="unit_type" >Unit Type <span class="text-danger">*</span></label>
        <select class="form-control select2" id="unit_type" name="unit_type" required>
            <option value="" disabled selected>Select Unit Type</option>
            <option value="1BR" {{ old('unit_type') == '1BR' ? 'selected' : '' }}>1BR</option>
            <option value="2BR" {{ old('unit_type') == '2BR' ? 'selected' : '' }}>2BR</option>
            <option value="3BR" {{ old('unit_type') == '3BR' ? 'selected' : '' }}>3BR</option>
            <option value="Studio" {{ old('unit_type') == 'Studio' ? 'selected' : '' }}>Studio</option>
            <option value="Chalet" {{ old('unit_type') == 'Chalet' ? 'selected' : '' }}>Chalet</option>
           
            <!-- Add more options as needed -->
        </select>
        @error('unit_type')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>



  <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="unit_number">Unit Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="unit_number" name="unit_number" value="{{ old('unit_number') }}" maxlength="10" required placeholder="Unit Number">
                                    @error('unit_number')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="floor">Floor</label>
                                    

                                    <select class="form-control select2" id="floor" name="floor" required>
                                        <option value="" disabled selected>Select Floor</option>
                                        <option value="Basement">Basement</option>
                                        <option value="Ground Floor">Ground Floor</option>
                                        <option value="First Floor">First Floor</option>
                                        <option value="Second Floor">Second Floor</option>
                                        <option value="Third Floor">Third Floor</option>
                                        <option value="Fourth Floor">Fourth Floor</option>
                                        <option value="Fifth Floor">Fifth Floor</option>
                                        <option value="Sixth Floor">Sixth Floor</option>
                                        <option value="Seventh Floor">Seventh Floor</option>
                                        <option value="Eighth Floor">Eighth Floor</option>
                                        <option value="Ninth Floor">Ninth Floor</option>
                                        <option value="Tenth Floor">Tenth Floor</option>
                                        <option value="Eleventh Floor">Eleventh Floor</option>
                                        <option value="Twelfth Floor">Twelfth Floor</option>
                                        <option value="Thirteenth Floor">Thirteenth Floor</option>
                                        <option value="Fourteenth Floor">Fourteenth Floor</option>
                                        <option value="Fifteenth Floor">Fifteenth Floor</option>
                                        <option value="Sixteenth Floor">Sixteenth Floor</option>
                                        <option value="Seventeenth Floor">Seventeenth Floor</option>
                                        <option value="Eighteenth Floor">Eighteenth Floor</option>
                                        <option value="Nineteenth Floor">Nineteenth Floor</option>
                                        <option value="Twentieth Floor">Twentieth Floor</option>

                                        
                                    </select>

                                    @error('floor')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_area">Property Area

                                    <span class="text-danger d-none" id="property_area_asterisk">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="property_area" name="property_area" value="{{ old('property_area') }}" maxlength="10"  placeholder="Property Area">
                                    @error('property_area')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_services">Property Services <span class="text-danger d-none" id="property_services_asterisk">*</span></label>
                                    <input type="text" class="form-control" id="property_services" name="property_services" value="{{ old('property_services') }}"  placeholder="Property Services">
                                    @error('property_services')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_age">Property Age
                                        <span class="text-danger d-none" id="property_age_asterisk">*</span>

                                    </label>
                                    <input type="number" class="form-control" id="property_age" name="property_age" value="{{ old('property_age') }}" maxlength="10"  placeholder="Property Age">
                                    @error('property_age')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="room_number">Room Number
                                        <span class="text-danger d-none" id="room_number_asterisk">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="room_number" name="room_number" value="{{ old('room_number') }}" maxlength="10"  placeholder="Room Number">
                                    @error('room_number')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="street">Street <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="street" name="street" value="{{ old('street') }}" placeholder="Street Name / Number" required>
                                    @error('street')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            


<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="district">District <span class="text-danger">*</span></label>
        <select class="form-control select2 @error('district') is-invalid @enderror" id="district" name="district" required>
            <option value="">Select District</option>
            @php
                $districts = [
    'Al Amal', 'Al Aqiq', 'Al Arid', 'Al Arid', 'Al Ashbiliyah', 'Al Badiah',
    'Al Dhubbat', 'Al Diriyah', 'Al Faisaliyah', 'Al Falah', 'Al Ghadir',
    'Al Izdihar', 'Al Janadriyah', 'Al Jazirah', 'Al Khalidiyah', 'Al Malaz',
    'Al Malqa', 'Al Manar', 'Al Mansoura', 'Al Maruj', 'Al Marwah', 'Al Maseef',
    'Al Mughrizat', 'Al Munsiya', 'Al Muraba’a', 'Al Muruj', 'Al Nadheem',
    'Al Nafal', 'Al Narjis', 'Al Naseem', 'Al Nuzha', 'Al Olaya', 'Al Rabwah',
    'Al Rawdah', 'Al Rayyan', 'Al Rehab', 'Al Rimal', 'Al Salam', 'Al Salamah',
    'Al Shifa', 'Al Shuhada', 'Al Sulay', 'Al Sulimaniyah', 'Al Taawun',
    'Al Uraija', 'Al Uyaynah', 'Al Wadi', 'Al Wurud', 'Al Yamamah', 'Al Yasmeen',
    'An Namudhajiyah', 'Az Zahra', 'Dar Al Baida', 'Deerah', 'Diplomatic Quarter',
    'Dirab', 'Ghubairah', 'Hittin', 'Irqah', 'Ishbiliyah', 'King Fahd District',
    'King Khalid International Airport Area', 'Manfuhah', 'Qurtubah',
    'Salahuddin', 'Shubra', 'Tuwaiq', 'Umm Al Hamam', 'Wadi Laban', 'Yarmuk','AL Qudus','AL Hamra'
];

            @endphp
            @foreach ($districts as $district)
                <option value="{{ $district }}" {{ old('district') == $district ? 'selected' : '' }}>{{ $district }}</option>
            @endforeach
        </select>
        @error('district')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>
   
                            
                            
                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="city_name">City Name <span class="text-danger">*</span></label>
        <select class="form-control select2" id="city_name" name="city_name" required>
            <option value="">-- Select City --</option>
            <option value="Riyadh" {{ old('city_name') == 'Riyadh' ? 'selected' : '' }}>Riyadh</option>
            <option value="Jeddah" {{ old('city_name') == 'Jeddah' ? 'selected' : '' }}>Jeddah</option>
            <option value="Makkah" {{ old('city_name') == 'Makkah' ? 'selected' : '' }}>Makkah</option>
            <option value="Medinah" {{ old('city_name') == 'Medinah' ? 'selected' : '' }}>Medinah</option>
            <option value="Khobar" {{ old('city_name') == 'Khobar' ? 'selected' : '' }}>Khobar</option>
            <option value="Dammam" {{ old('city_name') == 'Dammam' ? 'selected' : '' }}>Dammam</option>
        </select>
        @error('city_name')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_address">Postal Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="property_address" name="property_address" placeholder="E.g RFYB3686, 3686 Muhammad Ali Junah, 7437, Al Yarmuk, Riyadh 13251, Saudi Arabia" required maxlength="250">{{ old('property_address') }}</textarea>
                                    @error('property_address')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="postal_code">Postal Code <span class="text-danger">*</span></label>
                                   
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" maxlength="5" required placeholder="eg. 74200" >
                                    @error('postal_code')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="building_Caretaker_name">Building Caretaker Name</label>
                                    <input type="text" class="form-control" id="building_Caretaker_name" name="building_Caretaker_name" value="{{ old('building_Caretaker_name') }}" placeholder="Building Caretaker Name">
                                    @error('building_Caretaker_name')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                           <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="building_Caretaker_Number">Building Caretaker Number</label>
        <input type="tel"
               class="form-control"
               id="building_Caretaker_Number"
               name="building_Caretaker_Number"
               maxlength="13"
               pattern="^\+966\d{9}$"
               placeholder="e.g. +966512345678"
               title="Must start with +966 and be followed by 9 digits"
               value="{{ old('building_Caretaker_Number') }}">
        <small id="caretakerError" style="color:red; display:none;">Number must start with +966 and be 9 digits long after that.</small>
        @error('building_Caretaker_Number')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="bedrooms">Number of Bedrooms <span class="text-danger">*</span></label>
                                  

                                    <select class="form-control select2" id="bedrooms" name="bedrooms" required placeholder="Select Bedrooms">
                                        <option value="" disabled selected>Select Number Of Bedrooms</option>
                                        <option value="1" {{ old('bedrooms') == '1' ? 'selected' : '' }}>1</option>
                                        <option value="2" {{ old('bedrooms') == '2' ? 'selected' : '' }}>2</option>
                                        <option value="3" {{ old('bedrooms') == '3' ? 'selected' : '' }}>3</option>
                                        <option value="4" {{ old('bedrooms') == '4' ? 'selected' : '' }}>4</option>
                                    </select>    

                                    @error('bedrooms')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror


                                </div>
                            </div>

                            


<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="beds">Number of Beds <span class="text-danger">*</span></label>
        <select class="form-control select2" id="beds" name="beds" required>
            <option value="">Select Number of Beds</option>
            @for ($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" {{ old('beds') == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
        @error('beds')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="bathrooms">Number of Bathrooms <span class="text-danger">*</span></label>
        <select class="form-control select2" id="bathrooms" name="bathrooms" required>
            <option value="">Select Number of Bathrooms</option>
            @for ($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" {{ old('bathrooms') == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
        @error('bathrooms')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>



<div class="row mt-4">
    {{-- Living Room --}}
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label d-block">
                Living Room <span class="text-danger">*</span>
              
            </label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="living_room_yes" name="living_room" value="1"
                       {{ old('living_room') == '1' ? 'checked' : '' }} required>
                <label class="form-check-label" for="living_room_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="living_room_no" name="living_room" value="0"
                       {{ old('living_room') == '0' ? 'checked' : '' }} required>
                <label class="form-check-label" for="living_room_no">No</label>
            </div>
        </div>
    </div>

    {{-- Kitchen --}}
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label d-block">
                Kitchen <span class="text-danger">*</span>
               
            </label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="kitchen_yes" name="kitchen" value="1"
                       {{ old('kitchen') == '1' ? 'checked' : '' }} required>
                <label class="form-check-label" for="kitchen_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="kitchen_no" name="kitchen" value="0"
                       {{ old('kitchen') == '0' ? 'checked' : '' }} required>
                <label class="form-check-label" for="kitchen_no">No</label>
            </div>
        </div>
    </div>

    {{-- Outdoor Area --}}
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label d-block">
                Outdoor Area
               
            </label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="outdoor_area_yes" name="outdoor_area" value="1"
                       {{ old('outdoor_area') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="outdoor_area_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="outdoor_area_no" name="outdoor_area" value="0"
                       {{ old('outdoor_area') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="outdoor_area_no">No</label>
            </div>
        </div>
    </div>

    {{-- Laundry Area --}}
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label d-block">
                Laundry Area
            
            </label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="laundry_area_yes" name="laundry_area" value="1"
                       {{ old('laundry_area') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="laundry_area_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="laundry_area_no" name="laundry_area" value="0"
                       {{ old('laundry_area') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="laundry_area_no">No</label>
            </div>
        </div>
    </div>

    {{-- Corridor --}}
    <div class="col-md-2">
        <div class="form-group">
            <label class="form-label d-block">
                Corridor
                
            </label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="corridor_yes" name="corridor" value="1"
                       {{ old('corridor') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="corridor_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="corridor_no" name="corridor" value="0"
                       {{ old('corridor') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="corridor_no">No</label>
            </div>
        </div>
    </div>
</div>



                            <hr>

  <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Booking Engine</label>

                           
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="be_listing_name">BE Name</label>
                                    <input type="text" class="form-control" id="be_listing_name" name="be_listing_name" value="{{ old('be_listing_name') }}">
                                    @error('be_listing_name')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_about">About Property</label>
                                    <textarea class="form-control" id="property_about" name="property_about">{{ old('property_about') }}</textarea>
                                    @error('property_about')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                           
                   <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="discounts">Discount</label>
                                    <input type="number" class="form-control" id="discounts" name="discounts" value="{{ old('discounts') }}">
                                    @error('discounts')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                    </div>      

                    <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="tax">Tax</label>
                                    <input type="number" class="form-control" id="tax" name="tax" value="{{ old('tax') }}">
                                    @error('tax')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                    </div>

                    

                            

<hr>

  <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Tech Details</label>

  
  <div class="row mt-4">
    {{-- Door Lock Mechanism --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label" for="door_locks_mechanism">
                Door Locks Mechanism <span class="text-danger">*</span>
                <small class="d-block text-muted">Needed for check-in automation</small>
            </label>
            <select class="form-control select2" id="door_locks_mechanism" name="door_locks_mechanism" required>
                <option value="" disabled selected>Select Door Lock Mechanism</option>
                <option value="Auto" {{ old('door_locks_mechanism') == 'Auto' ? 'selected' : '' }}>Auto</option>
                <option value="Manual" {{ old('door_locks_mechanism') == 'Manual' ? 'selected' : '' }}>Manual</option>
            </select>
            @error('door_locks_mechanism')
                <span class="invalid">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Door Lock Code --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label" for="door_lock_code">
                Door Lock Code <span class="text-danger" id="lock_code_required_star">*</span>
                <small class="d-block text-muted">TT Lock Host Credentials (Only if Auto)</small>
            </label>
            <input type="text"
                   class="form-control"
                   id="door_lock_code"
                   name="door_lock_code"
                   placeholder="Enter lock code if Auto"
                   value="{{ old('door_lock_code') }}">
            @error('door_lock_code')
                <span class="invalid">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>




                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="wi_fi_password">
            Wi-Fi Password <span class="text-danger" id="wifi_required_star" style="display: none;">*</span>
            <small class="d-block text-muted">Required if Wi-Fi is selected</small>
        </label>
        <input type="text"
               class="form-control"
               id="wi_fi_password"
               name="wi_fi_password"
               value="{{ old('wi_fi_password') }}"
               placeholder="Wi-Fi Password">
        @error('wi_fi_password')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>



                          
<hr>

  <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">OTA Info</label>





                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="existing_ota_links">Existing OTA Links</label>
                                    <textarea class="form-control" id="existing_ota_links" name="existing_ota_links" placeholder="https://www.airbnb.com/rooms/1173067429189103741?guests=1&adults=1&s=67&unique_share_id=da6130c9-0bd8-44be-8c14-4fcf8325cb72 , https://gathern.co/view/87048">{{ old('existing_ota_links') }}</textarea>
                                    @error('existing_ota_links')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="property_google_map_link">
            Property Google Map Link <span class="text-danger">*</span>
            <small class="d-block text-muted">Must be a valid Google Maps URL</small>
        </label>
        <input type="url"
               class="form-control"
               id="property_google_map_link"
               name="property_google_map_link"
               value="{{ old('property_google_map_link') }}"
               placeholder="https://www.google.com/maps/place/..."
               title="Link must be a valid Google Maps URL"
               pattern="https?://(www\.)?google\.com/maps(/.*)?"
               required>
        @error('property_google_map_link')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-6 mt-5">
    <label class="form-label d-block">Is Photo Exists?</label>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="is_photo_exists"
               id="is_photo_exists_yes"
               value="1"
               {{ old('is_photo_exists') == '1' ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="is_photo_exists_yes">Yes</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="is_photo_exists"
               id="is_photo_exists_no"
               value="0"
               {{ old('is_photo_exists') == '0' ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="is_photo_exists_no">No</label>
    </div>
</div>

<div class="col-md-6" id="property_images_container">
    <div class="form-group">
        <label class="form-label" for="property_images_link">
            Property Images Link <span class="text-danger d-none" id="property_images_link_asterisk">*</span>
            <small class="d-block text-muted">
                Upload link must include images of:
                Door Lock, Building, Bedroom, Bathroom, Kitchen, Lounge, Laundry Area
            </small>
        </label>
        <input type="url"
               class="form-control"
               id="property_images_link"
               name="property_images_link"
               placeholder="e.g. https://drive.google.com/..."
               title="Must include images of: Door Lock, Building, Bedroom, Bathroom, Kitchen, Lounge, Laundry Area"
               value="{{ old('property_images_link') }}">
        <span class="text-danger d-none" id="property_link_error">Property Images Link is required when photo exists.</span>
        @error('property_images_link')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>




<hr>

  <label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Finance</label>

<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="cleaning_fee">Cleaning Fee <span class="text-danger">*</span></label>
        <select class="form-control select2" id="cleaning_fee" name="cleaning_fee" required>
            <option value="" disabled selected>Select Cleaning Fee</option>
            @foreach([60, 90, 120, 150] as $fee)
                <option value="{{ $fee }}" {{ old('cleaning_fee') == $fee ? 'selected' : '' }}>{{ $fee }} SAR</option>
            @endforeach
        </select>
        @error('cleaning_fee')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<hr>

<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Legal</label>


  <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="contract_file">Contract File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="contract_file" name="contract_file" required accept="application/pdf">
                                    @error('contract_file')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                              <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="utiltiy_bills">Utiltiy Bills<span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="utiltiy_bills" name="utiltiy_bills" required accept="application/pdf">
                                    @error('utiltiy_bills')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="licence_doc">Licence Doc <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="licence_doc" name="licence_doc" required accept="application/pdf">
                                    @error('licence_doc')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                             <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="ownership_documents">Ownership Documents <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="ownership_documents[]" name="ownership_documents[]" multiple required accept="application/pdf"> 
                                    
                                    @error('ownership_documents')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="owner_document">Owner National ID/Iqama Picture/Photocopy (Front & Back) <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="owner_document[]" name="owner_document[]" multiple required accept="application/pdf">
                                    @error('owner_document')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="spl_national_address">SPL National Address <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="spl_national_address" name="spl_national_address" required accept="application/pdf">
                                    @error('spl_national_address')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="host_rental_lease">Host Rental Lease Reminder <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="host_rental_lease" name="host_rental_lease" required>
                                    @error('host_rental_lease')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                           


<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Optional Services</label>

{{-- Allow Pets --}}
<div class="col-md-3 mt-5" >
    <label class="form-label d-block">Allow Pets </label>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="is_allow_pets"
               id="allow_pets_yes"
               value="1"
               {{ old('is_allow_pets') == '1' ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="allow_pets_yes">Yes</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="is_allow_pets"
               id="allow_pets_no"
               value="0"
               {{ old('is_allow_pets') == '0' ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="allow_pets_no">No</label>
    </div>
</div>

{{-- Self Check-In --}}
<div class="col-md-3 mt-5" >
    <label class="form-label d-block">Self Check-In </label>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="is_self_check_in"
               id="self_checkin_yes"
               value="1"
               {{ old('is_self_check_in') == '1' ? 'checked' : '' }}
               >
        <label class="form-check-label" for="self_checkin_yes">Yes</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="is_self_check_in"
               id="self_checkin_no"
               value="0"
               {{ old('is_self_check_in') == '0' ? 'checked' : '' }}
               >
        <label class="form-check-label" for="self_checkin_no">No</label>
    </div>
</div>


                

       

<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Operational Info</label>

<div class="col-md-3">
    <div class="form-group">
        
    <label class="form-label" for="block_dates_via_host_app">Block Dates via Host App</label>
        <select class="form-control select2" id="block_dates_via_host_app" name="block_dates_via_host_app">
            <option value="" disabled selected>Select Block Dates via Host App</option>
            <option value="1" {{ old('block_dates_via_host_app') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('block_dates_via_host_app') == '0 Key' ? 'selected' : '' }}>No</option>
            
           
        </select>
        @error('block_dates_via_host_app')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
    
       <label class="form-label" for="ota_charges_on_direct_booking">OTA Charges on Direct Booking</label>

        <select class="form-control select2" id="ota_charges_on_direct_booking" name="ota_charges_on_direct_booking">
            <option value="" disabled selected>Select OTA Charges on Direct Booking</option>
            <option value="1" {{ old('ota_charges_on_direct_booking') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('ota_charges_on_direct_booking') == '0 Key' ? 'selected' : '' }}>No</option>
            
           
        </select>
        @error('ota_charges_on_direct_booking')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        
    <label class="form-label" for="cleaning_fee_on_direct_booking">Cleaning Fee on Direct Booking
    </label>
        <select class="form-control select2" id="cleaning_fee_on_direct_booking" name="cleaning_fee_on_direct_booking">
            <option value="" disabled selected>Select Cleaning Fee on Direct Booking</option>
            <option value="1" {{ old('cleaning_fee_on_direct_booking') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('cleaning_fee_on_direct_booking') == '0 Key' ? 'selected' : '' }}>No</option>
            
           
        </select>
        @error('cleaning_fee_on_direct_booking')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        
    <label class="form-label" for="fixed_cleaning_fee">Fixed Cleaning Fee</label>
        <select class="form-control select2" id="fixed_cleaning_fee" name="fixed_cleaning_fee">
            <option value="" disabled selected>Select Fixed Cleaning Fee</option>
            <option value="1" {{ old('fixed_cleaning_fee') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('fixed_cleaning_fee') == '0 Key' ? 'selected' : '' }}>No</option>
            
           
        </select>
        @error('fixed_cleaning_fee')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
     <div class="form-group">
     <label class="form-label" for="fixed_cleaning_fee_amount">Fixed Cleaning Fee Amount</label>
     <input type="number" class="form-control" id="fixed_cleaning_fee_amount" name="fixed_cleaning_fee_amount" placeholder="Enter Fixed Cleaning Fee Amount" value="{{ old('fixed_cleaning_fee_amount') }}">
      @error('fixed_cleaning_fee_amount')
      <span class="invalid">{{ $message }}</span>
           @enderror
       </div>
</div>


{{-- Deep Cleaning Required? --}}
<div class="col-md-3 " >
    <label class="form-label d-block">Deep Cleaning Required ? <span class="text-danger">*</span> </label>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="deep_cleaning_required"
               id="deep_cleaning_required_yes"
               value="1"
               {{ old('deep_cleaning_required') == '1' ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="deep_cleaning_required">Yes</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input"
               type="radio"
               name="deep_cleaning_required"
               id="deep_cleaning_required_no"
               value="0"
               {{ old('deep_cleaning_required') == '0' ? 'checked' : '' }}
               required>
        <label class="form-check-label" for="deep_cleaning_required">No</label>
    </div>
</div>


<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">
    Hosting Terms
</label>


    {{-- Co Hosting Account --}}
    <div class="col-md-3">
        <div class="form-group">
            <label class="form-label" for="co_hosting_account">Co-Hosting Account <span class="text-danger">*</span></label>
           <select class="form-control select2" id="co_hosting_account" name="co_hosting_account" required>
    <option value="" disabled {{ old('co_hosting_account') === null ? 'selected' : '' }}>Select Co Hosting Account</option>
    <option value="1" {{ old('co_hosting_account') == '1' ? 'selected' : '' }}>Yes</option>
    <option value="0" {{ old('co_hosting_account') == '0' ? 'selected' : '' }}>No</option>
</select>
            @error('co_hosting_account')
                <span class="invalid">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Airbnb Email --}}
    <div class="col-md-3">
        <div class="form-group">
            <label class="form-label" for="airbnb_email">Airbnb Credentials - Email</label>
            <input type="email" class="form-control" id="airbnb_email" name="airbnb_email"
                   placeholder="e.g. host@example.com"
                   value="{{ old('airbnb_email') }}">
            @error('airbnb_email')
                <span class="invalid">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Airbnb Password --}}
    <div class="col-md-3">
        <div class="form-group">
            <label class="form-label" for="airbnb_password">
    Airbnb Credentials - Password <span id="password_required_star" class="text-danger" style="display: none;">*</span>
</label>

            <input type="text" class="form-control" id="airbnb_password" name="airbnb_password"
                   placeholder="Enter password"
                   value="{{ old('airbnb_password') }}">
            @error('airbnb_password')
                <span class="invalid">{{ $message }}</span>
            @enderror
        </div>
    </div>




<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Rental Terms</label>

<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="length_type">Lenght Type</label>
        <select class="form-control select2" id="length_type" name="length_type"  >
            <option value="" disabled selected>Select Length Type</option>
            <option value="Short Term" {{ old('length_type') == 'Short Term' ? 'selected' : '' }}>Short Term</option>
            <option value="Long Term" {{ old('length_type') == 'Long Term' ? 'selected' : '' }}>Long Term</option>
        </select>
        @error('length_type')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

  <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="lenght_type_document">Length Type Document </label>
                                    <input type="file" class="form-control" id="lenght_type_document" name="lenght_type_document"  accept="application/pdf">
                                    @error('lenght_type_document')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

<div class="col-md-3">
     <div class="form-group">
     <label class="form-label" for="min_days_for_ltr">Min Number of Days for LTR</label>
     <input type="number" class="form-control" id="min_days_for_ltr" name="min_days_for_ltr" placeholder="Enter Min Days Ltr" value="{{ old('min_days_for_ltr') }}">
      @error('min_days_for_ltr')
      <span class="invalid">{{ $message }}</span>
           @enderror
       </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="type_of_ownership_document">Type of Ownership Document <span class="text-danger d-none" id="type_of_ownership_document_asterisk">*</span></label>
        <select class="form-control select2" id="type_of_ownership_document" name="type_of_ownership_document[]" multiple>
            <option value="Paper Title Deed" {{ in_array('Paper Title Deed', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Paper Title Deed</option>
            <option value="Electronic Lease Contract" {{ in_array('Electronic Lease Contract', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Electronic Lease Contract</option>
            <option value="Electronic Title Deed" {{ in_array('Electronic Title Deed', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Electronic Title Deed</option>
            <option value="Commercial Registration Title" {{ in_array('Commercial Registration Title', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Commercial Registration Title</option>
            <option value="Court Ruling of Ownership (Istihkam)" {{ in_array('Court Ruling of Ownership (Istihkam)', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Court Ruling of Ownership (Istihkam)</option>
            <option value="Inheritance Title" {{ in_array('Inheritance Title', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Inheritance Title</option>
            <option value="Funding Title" {{ in_array('Funding Title', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Funding Title</option>
            <option value="Off-Plan (WAFI) Contract" {{ in_array('Off-Plan (WAFI) Contract', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Off-Plan (WAFI) Contract</option>
            <option value="Contractual Status on Ejar Platform" {{ in_array('Contractual Status on Ejar Platform', old('type_of_ownership_document', [])) ? 'selected' : '' }}>Contractual Status on Ejar Platform</option>
        </select>
        @error('type_of_ownership_document')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="existing_property_obligations">Existing Property Obligations (Insurance)

        <span class="text-danger d-none" id="existing_property_obligations_asterisk">*</span>
        </label>
        
        <input type="file" class="form-control" id="existing_property_obligations" name="existing_property_obligations" accept="application/pdf">
        @error('existing_property_obligations')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        <label class="form-label" for="ownership_document_number_and_type">
            Ownership Document Number & Type 

            <span class="text-danger d-none" id="ownership_document_number_and_type_asterisk">*</span>
        </label>
        <input type="text" 
               class="form-control @error('ownership_document_number_and_type') is-invalid @enderror" 
               id="ownership_document_number_and_type" 
               name="ownership_document_number_and_type" 
               value="{{ old('ownership_document_number_and_type') }}" 
               pattern="[A-Za-z0-9\s\-\/]+" 
               title="Only alphanumeric characters, spaces, dashes, and slashes are allowed" >
        
        @error('ownership_document_number_and_type')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">Finance/Rev Share</label>


<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="services">Services</label>
        <select class="form-control select2" id="services" name="services">
            <option value="" disabled selected>Select Services</option>
            <option value="Guest management only" {{ old('services') == 'Guest management only Term' ? 'selected' : '' }}>Guest management only</option>
            <option value="Revene Management Only" {{ old('services') == 'Guest management only' ? 'selected' : '' }}>Revene Management Only</option>
            <option value="Cleaning & Laundry Services Only" {{ old('services') == 'Cleaning & Laundry Services Only' ? 'selected' : '' }}>Cleaning & Laundry Services Only</option>
        </select>
        @error('services')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="livedin_share_after_discount">Livedin Share After Discount</label>
        <select class="form-control select2" id="livedin_share_after_discount" name="livedin_share_after_discount">
            <option value="" disabled selected>Select LivedIn Share After Discount</option>
            <option value="1" {{ old('livedin_share_after_discount') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('livedin_share_after_discount') == '0' ? 'selected' : '' }}>No</option>
        </select>
        @error('livedin_share_after_discount')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="share_percentage">LivedIn Share Percentage <span class="text-danger">*</span></label>
         <input type="number" class="form-control" id="share_percentage" name="share_percentage" placeholder="Enter Percentage" value="{{ old('share_percentage') }}" required>
        @error('share_percentage')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="host_exclusivity">Host Exclusivity <span class="text-danger">*</span></label>
        <select class="form-control select2" id="host_exclusivity" name="host_exclusivity" required>
            <option value="" disabled selected>Select Services</option>
            <option value="Exclusive" {{ old('host_exclusivity') == 'Exclusive management only Term' ? 'selected' : '' }}>Exclusive</option>
            <option value="Non Exclusive" {{ old('host_exclusivity') == 'Non Exclusive' ? 'selected' : '' }}>Non Exclusive</option>
           
        </select>
        @error('host_exclusivity')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="cleaning_done_by_livedin">Cleaning Done By LivedIn <span class="text-danger">*</span></label>
        <select class="form-control select2" id="cleaning_done_by_livedin" name="cleaning_done_by_livedin" required>
            <option value="" disabled selected>Cleaning Done By LivedIn</option>
            <option value="1" {{ old('cleaning_done_by_livedin') == '1' ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ old('cleaning_done_by_livedin') == '0' ? 'selected' : '' }}>No</option>
        </select>
        @error('cleaning_done_by_livedin')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">
    Property Features
</label>

{{-- Building Type --}}
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="building_type">Building Type <span class="text-danger">*</span></label>
        <select class="form-control select2" id="building_type" name="building_type" required>
            <option value="" disabled selected>Select Building Type</option>
            <option value="Apartment in Villa" {{ old('building_type') == 'Apartment in Villa' ? 'selected' : '' }}>Apartment in Villa</option>
            <option value="Apartment in Building" {{ old('building_type') == 'Apartment in Building' ? 'selected' : '' }}>Apartment in Building</option>
        </select>
        @error('building_type')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Floor Number --}}
<div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="floor_number">Floor Number <span class="text-danger">*</span></label>
        <select class="form-control select2" id="floor_number" name="floor_number" required>
            <option value="" disabled selected>Select Floor</option>
            @for ($i = 1; $i <= 50; $i++)
                <option value="{{ $i }}" {{ old('floor_number') == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
        @error('floor_number')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="building_number">Building Number 

    <span class="text-danger d-none" id="building_number_asterisk">*</span>
    </label>
         <input type="text" class="form-control" id="building_number" name="building_number" placeholder="Enter Building Number" value="{{ old('building_number') }}" >
        @error('building_number')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<div class="col-md-3">
    <div class="form-group">

    <label class="form-label" for="additional_number">Additional Number

    <span class="text-danger d-none" id="additional_number_asterisk">*</span>

    </label>
         <input type="text" class="form-control" id="additional_number" name="additional_number" placeholder="Enter Additional Number" value="{{ old('additional_number') }}" >
        @error('additional_number')
            <span class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


<hr>
<label class="form-label" style="display: inline-block; background-color: #f0f0f0; font-weight: bold; font-size: 16px; padding: 6px 12px; border-radius: 4px;">
    Amenities
</label>
<div class="row gy-4">
    <div class="col-md-12">
        <div class="form-group">
            <label class="form-label" for="amenities">Amenities <span class="text-danger">*</span></label>
            <div class="row">

                @php
                    $amenities = [
    'Air conditioner',
    'Basic tableware for number of guests (Plates/Bowls & Cutlery)',
    'Basic utensils (Spatula | Serving spoon)',
    'Blackout curtains/curtain',
    'Coffee Maker',
    'Cutting board + kitchen knife',
    'Dedicated work space',
    'Diffuser',
    'Dish soap | sponge | drying rack or towel',
    'Door mats',
    'Essential',
    'Fire Extinguisher',
    'First Aid Kit',
    'Fridge',
    'Full Length mirror',
    'Geyser',
    'Hair Dryer',
    'Hangers',
    'Hanging space',
    'Heater',
    'Hotplate',
    'Iron',
    'Kettle',
    'Kitchen',
    'Kitchen essentials',
    'Microwave',
    'Minimal cookware (1-2 pots, 1 frying pan)',
    'Multi plug/extension wire',
    'Netflix subscription',
    'Other',
    'Outdoor Dining Area',
    'Pool',
    'Private Entrance',
    'Self Check-in',
    'Shampoo',
    'Shoerack',
    'Sink',
    'Smoke Alarm',
    'T.V',
    'Towels',
    'Trash bin & bags',
    'Washer Machine',
    'Washing machine',
    'Wifi',
    'Free Parking Premises'
];

                @endphp

                @foreach($amenities as $index => $amenity)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="amenity_{{ $index }}"
                                   name="amenities[]"
                                   value="{{ $amenity }}">
                            <label class="form-check-label" for="amenity_{{ $index }}">{{ $amenity }}</label>
                        </div>
                    </div>
                @endforeach

            </div>

            @error('amenities')
                <span class="invalid">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>




                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<script>
    
document.getElementById('owner_name').addEventListener('input', function (e) {
    this.value = this.value.replace(/[^A-Za-z]/g, '');
});
document.getElementById('last_name').addEventListener('input', function (e) {
    this.value = this.value.replace(/[^A-Za-z]/g, '');
});


document.addEventListener('DOMContentLoaded', function () {
    var emailInput = document.getElementById('host_email');
    var errorText = document.getElementById('emailError');

    emailInput.addEventListener('input', function () {
        var email = emailInput.value.trim();
        var isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (email && !isValid) {
            errorText.style.display = 'inline';
            emailInput.classList.add('is-invalid');
        } else {
            errorText.style.display = 'none';
            emailInput.classList.remove('is-invalid');
        }
    });


    const ibanInput = document.getElementById('iban_no');
    const ibanError = document.getElementById('ibanError');

    ibanInput.addEventListener('input', function () {
        const iban = ibanInput.value.toUpperCase().trim();
        const isValid = /^SA\d{22}$/.test(iban);

        // Force uppercase
        ibanInput.value = iban;

        if (iban.length === 24 && !isValid) {
            ibanError.style.display = 'inline';
            ibanInput.classList.add('is-invalid');
        } else {
            ibanError.style.display = 'none';
            ibanInput.classList.remove('is-invalid');
        }
    });


    const caretakerInput = document.getElementById('building_Caretaker_Number');
    const caretakerError = document.getElementById('caretakerError');

    caretakerInput.addEventListener('input', function () {
        const value = caretakerInput.value.trim();
        const isValid = value === '' || /^\+966\d{9}$/.test(value); // allow empty (optional)

        if (!isValid) {
            caretakerError.style.display = 'inline';
            caretakerInput.classList.add('is-invalid');
        } else {
            caretakerError.style.display = 'none';
            caretakerInput.classList.remove('is-invalid');
        }
    });


    const mechanism = document.getElementById('door_locks_mechanism');
    const lockCode = document.getElementById('door_lock_code');
    const star = document.getElementById('lock_code_required_star');

    function toggleLockCodeRequired() {
        if (mechanism.value === 'Auto') {
            lockCode.setAttribute('required', 'required');
            star.style.display = 'inline';
        } else {
            lockCode.removeAttribute('required');
            star.style.display = 'none';
        }
    }

    mechanism.addEventListener('change', toggleLockCodeRequired);
    toggleLockCodeRequired(); // initial on load



    const wifiPassword = document.getElementById('wi_fi_password');
    const wifiStar = document.getElementById('wifi_required_star');

    function checkWifiSelected() {
        const checkboxes = document.querySelectorAll('input[name="amenities[]"]:checked');
        let wifiSelected = false;

        checkboxes.forEach(function (cb) {
            if (cb.value.trim().toLowerCase() === 'wifi') {
                wifiSelected = true;
            }
        });

        if (wifiSelected) {
            wifiPassword.setAttribute('required', 'required');
            wifiStar.style.display = 'inline';
        } else {
            wifiPassword.removeAttribute('required');
            wifiStar.style.display = 'none';
        }
    }

    checkWifiSelected();

    document.querySelectorAll('input[name="amenities[]"]').forEach(function (cb) {
        cb.addEventListener('change', checkWifiSelected);
    });


    const mapInput = document.getElementById('property_google_map_link');

    mapInput.addEventListener('input', function () {
        const pattern = /^https?:\/\/(www\.)?google\.com\/maps(\/.*)?$/;
        if (this.value && !pattern.test(this.value)) {
            this.setCustomValidity('Please enter a valid Google Maps link');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });


    const coHostSelect = document.getElementById('co_hosting_account');
    const airbnbPassword = document.getElementById('airbnb_password');
    const passwordStar = document.getElementById('password_required_star');

    function togglePasswordRequired() {
        const value = coHostSelect.value;
        if (value === "1") {
            airbnbPassword.setAttribute('required', 'required');
            passwordStar.style.display = 'inline';
        } else {
            airbnbPassword.removeAttribute('required');
            passwordStar.style.display = 'none';
        }
    }

    coHostSelect.addEventListener('change', togglePasswordRequired);

    // Also run it on page load *after select has been populated*
    setTimeout(togglePasswordRequired, 100);



        const yesRadio = document.getElementById('is_photo_exists_yes');
        const noRadio = document.getElementById('is_photo_exists_no');
        const linkInput = document.getElementById('property_images_link');
        const asterisk = document.getElementById('property_images_link_asterisk');

        function toggleRequired() {
            if (yesRadio.checked) {
                linkInput.setAttribute('required', 'required');
                asterisk.classList.remove('d-none');
            } else {
                linkInput.removeAttribute('required');
                asterisk.classList.add('d-none');
            }
        }

        yesRadio.addEventListener('change', toggleRequired);
        noRadio.addEventListener('change', toggleRequired);

        // On page load
        toggleRequired();

});



</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



<script>
$(document).ready(function () {
    // list of field IDs to toggle (match these IDs in your HTML)
    const fields = [
        "type_of_ownership_document",                // select (multi)
        "ownership_document_number_and_type",        // input
        "date_of_birth",
        "building_number",
        "additional_number",
        "property_area",
        "property_services",
        "property_age",
        "room_number",
        "existing_property_obligations"              // file
    ];

    function setFieldRequired($el, isRequired) {
        // toggle attribute on the actual input/select
        if (isRequired) {
            $el.attr('required', 'required');
        } else {
            $el.removeAttr('required');
        }

        // For Select2, the underlying <select> receives the attribute above
        // For visual asterisk:
        const $ast = $('#' + $el.attr('id') + '_asterisk');
        if ($ast.length) {
            if (isRequired) $ast.removeClass('d-none');
            else $ast.addClass('d-none');
        }
    }

    function toggleRequiredFields() {
        const val = $('#length_type').val();
        const isLong = val === 'Long Term';

        fields.forEach(function (fieldId) {
            const $field = $('#' + fieldId);
            if ($field.length) {
                setFieldRequired($field, isLong);
            }
        });
    }

    // Run on init in case old value is present (edit page)
    toggleRequiredFields();

    // Listen for normal change and Select2 events (cover both cases)
    $('#length_type').on('change select2:select select2:unselect', toggleRequiredFields);

    // If you re-init Select2 later, re-run toggle
    // Example re-init hook:
    // $('#length_type').on('select2:open', toggleRequiredFields);
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        var yyyy = today.getFullYear();

        today = yyyy + '-' + mm + '-' + dd;
        document.getElementById('date_of_birth').setAttribute("max", today);
    });
</script>
    
@endsection
