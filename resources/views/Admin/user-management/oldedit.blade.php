@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit User</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('user-management.update', $user->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="role_id">Role</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="role_id" id="role_id" data-placeholder="Select Role">
                                            <option value="" selected disabled>Select Role</option>
                                            @foreach($roles as $items)
                                                <option value="{{$items->id}}" {{$user->role_id == $items->id ? 'selected' : ''}}>{{$items->role_name}}</option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">First Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$user->name}}" placeholder="First Name">
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">Last Name</label>
                                    <input type="text" class="form-control" id="surname" name="surname" value="{{$user->surname}}" placeholder="Last Name">
                                    @error('surname')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">User ID</label>
                                    <input type="text" class="form-control" id="host_key" name="host_key" value="{{$user->host_key}}" placeholder="User ID">
                                    @error('host_key')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{$user->email}}" placeholder="Email">
                                    @error('email')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{$user->phone}}" placeholder="Phone">
                                    @error('phone')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="form-label" for="dob">Date Of Birth</label>--}}
{{--                                    <input type="date" class="form-control" id="dob" name="dob" value="{{$user->dob}}" placeholder="Date Of Birth">--}}
{{--                                    @error('dob')--}}
{{--                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="gender">Gender</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="gender" id="gender" data-placeholder="Select Gender">
                                            <option value="" selected disabled>Select Gender</option>
                                            <option value="male" {{$user->gender == 'male' ? 'selected' : ''}}>Male</option>
                                            <option value="female" {{$user->gender == 'female' ? 'selected' : ''}}>Female</option>
                                        </select>
                                        @error('gender')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="plan_verified">Status</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="plan_verified" id="plan_verified" data-placeholder="Select Status">
                                            <option value="" selected disabled>Select Status</option>
                                            <option value="1" {{$user->plan_verified == 1 ? 'selected' : ''}}>Activate</option>
                                            <option value="0" {{$user->plan_verified == 0 ? 'selected' : ''}}>Deactivate</option>
                                        </select>
                                        @error('gender')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="form-label" for="country">Country</label>--}}
{{--                                    <div class="form-control-wrap">--}}
{{--                                        <select class="form-select" name="country" id="country" data-placeholder="Select Country">--}}
{{--                                            <option value="" selected disabled>Select Country</option>--}}
{{--                                            <option value="PK">Pakistan</option>--}}
{{--                                            <option value="SA">Saudi Arabia</option>--}}
{{--                                            <option value="DB">Dubai</option>--}}
{{--                                            <option value="IR">Iran</option>--}}
{{--                                        </select>--}}
{{--                                        @error('country')--}}
{{--                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>--}}
{{--                                        @enderror--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="form-label" for="city">City</label>--}}
{{--                                    <div class="form-control-wrap">--}}
{{--                                        <select class="form-select" name="city" id="city" data-placeholder="Select City">--}}
{{--                                            <option value="" selected disabled>Select City</option>--}}
{{--                                            <option value="Karachi">Karachi</option>--}}
{{--                                            <option value="Peshawar">Peshawar</option>--}}
{{--                                            <option value="Lahore">Lahore</option>--}}
{{--                                            <option value="Quetta">Quetta</option>--}}
{{--                                        </select>--}}
{{--                                        @error('city')--}}
{{--                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>--}}
{{--                                        @enderror--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="form-label" for="longitude">Password</label>--}}
{{--                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">--}}
{{--                                    @error('password')--}}
{{--                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                            </div>--}}

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
