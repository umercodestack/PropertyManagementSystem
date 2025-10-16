@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Guest</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('guest-management.update', $guest->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">First Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$guest->name}}" placeholder="First Name">
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">Last Name</label>
                                    <input type="text" class="form-control" id="surname" name="surname" value="{{$guest->surname}}" placeholder="Last Name">
                                    @error('surname')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{$guest->email}}" placeholder="Email">
                                    @error('email')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="number" class="form-control" id="phone" name="phone" value="{{$guest->phone}}" placeholder="Phone">
                                    @error('phone')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="dob">Date Of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" value="{{$guest->dob}}" placeholder="Date Of Birth">
                                    @error('dob')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="gender">Gender</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="gender" id="gender" data-placeholder="Select Gender">
                                            <option value="" selected disabled>Select Gender</option>
                                            <option value="Male" {{$guest->gender === 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{$guest->gender === 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Email" {{$guest->gender === 'Email' ? 'selected' : '' }}>Email</option>
                                        </select>
                                        @error('gender')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="country">Country</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="country" id="country" data-placeholder="Select Country">
                                            <option value="" selected disabled>Select Country</option>
                                            <option value="PK"  {{$guest->country === 'PK' ? 'selected' : '' }}>Pakistan</option>
                                            <option value="SA"  {{$guest->country === 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                                            <option value="DB"  {{$guest->country === 'DB' ? 'selected' : '' }}>Dubai</option>
                                            <option value="IR"  {{$guest->country === 'IR' ? 'selected' : '' }}>Iran</option>
                                        </select>
                                        @error('country')
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
                                            <option value="Karachi"  {{$guest->city === 'Karachi' ? 'selected' : '' }}>Karachi</option>
                                            <option value="Peshawar"  {{$guest->city === 'Peshawar' ? 'selected' : '' }}>Peshawar</option>
                                            <option value="Lahore"  {{$guest->city === 'Lahore' ? 'selected' : '' }}>Lahore</option>
                                            <option value="Quetta"  {{$guest->city === 'Quetta' ? 'selected' : '' }}>Quetta</option>
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
@endsection
