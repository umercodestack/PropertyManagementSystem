@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Booking</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6>Booking Details</h6>
                    <hr>
                    <form action="{{route('booking-management.store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="apartment_id">Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="apartment_id" id="apartment_id" data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach($apartments as $items)
                                                <option value="{{$items->id}}">{{$items->title}}</option>
                                            @endforeach
                                        </select>
                                        @error('apartment_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="booking_date_start">Booking Start Date</label>
                                    <input type="date" class="form-control" id="booking_date_start" name="booking_date_start" placeholder="Booking Start Date">
                                    @error('booking_date_start')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="booking_date_end">Booking End Date</label>
                                    <input type="date" class="form-control" id="booking_date_end" name="booking_date_end" placeholder="Booking End Date">
                                    @error('booking_date_end')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="number" class="form-control" id="phone" name="phone" onkeyup="fetchGuestData(this.value)" placeholder="Phone">
                                    @error('phone')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="name">First Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="First Name">
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="surname">Last Name</label>
                                    <input type="text" class="form-control" id="surname" name="surname" placeholder="Last Name">
                                    @error('surname')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address">
                                    @error('email')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="dob">Date Of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" placeholder="Date Of Birth">
                                    @error('dob')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="gender">Gender</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="gender" id="gender" data-placeholder="Select Gender">
                                            <option value="" selected disabled>Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Others">Others</option>
                                        </select>
                                        @error('gender')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="city">City</label>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="cnic_passport">CNIC / Passport</label>
                                    <input type="text" class="form-control" id="cnic_passport" name="cnic_passport" placeholder="CNIC / Passport">
                                    @error('cnic_passport')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="adult">Adult</label>
                                    <input type="number" class="form-control" id="adult" name="adult" placeholder="Adult">
                                    @error('adult')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="children">Children</label>
                                    <input type="number" class="form-control" id="children" name="children" placeholder="Children">
                                    @error('children')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="rooms">Rooms</label>
                                    <input type="number" class="form-control" id="rooms" name="rooms" placeholder="Rooms">
                                    @error('rooms')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="custom_discount">Custom Discount</label>
                                    <input type="number" class="form-control" id="custom_discount" name="custom_discount" placeholder="Rooms">
                                    @error('custom_discount')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="guest_id" id="guest_id">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="payment_method">Payment Method</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="payment_method" id="payment_method" data-placeholder="Payment Method">
                                            <option value="" selected disabled>Select Payment Method</option>
                                            <option value="ibft">IBFT</option>
                                            <option value="bank">Bank</option>
                                            <option value="cod">COD</option>
                                            <option value="online">Online</option>
                                        </select>
                                        @error('city')
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

    <script>
        function fetchGuestData(val) {
            $.ajax({
                url: "{{route('fetchGuestByGuestId')}}",
                type: "post",
                data: {phone: val} ,
                success: function (response) {
                    // console.log(response.id)
                    if(response){
                        $("#guest_id").val(response.id)
                        $("#name").val(response.name)
                        $("#surname").val(response.surname)
                        $("#email").val(response.email)
                        $("#dob").val(response.dob)
                        $("#gender").val(response.gender)
                        $("#country").val(response.country)
                        $("#city").val(response.city)
                    }
                    else {
                        $("#guest_id").val('')
                        $("#name").val('')
                        $("#surname").val('')
                        $("#email").val('')
                        $("#dob").val('')
                        $("#gender").val('')
                        $("#country").val('')
                        $("#city").val('')
                    }

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
    </script>
@endsection
