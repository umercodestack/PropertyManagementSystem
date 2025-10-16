@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create User</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('user-management.store')}}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            
                            
                                             <div class="form-group" style="display:none">
        <label for="hostactivation_id">Host Activation ID</label>
        <input type="text" class="form-control" id="hostactivation_id" name="hostactivation_id" 
               value="{{ old('hostactivation_id', $hostactivationId) }}">
    </div>
                            
                              <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="role_id">Role</label>
        <div class="form-control-wrap">
            <select class="form-select" name="role_id" id="role_id" data-placeholder="Select Role">
                <option value="" selected disabled>Select Role</option>
                @foreach($roles as $items)
                    <option value="{{ $items->id }}" 
                            {{ (old('role_id') ?? $defaultRole) === $items->role_name ? 'selected' : '' }}>
                        {{ $items->role_name }}
                    </option>
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
                                    <label class="form-label" for="parent_user">Parent User</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="parent_user" id="parent_user" data-placeholder="Select Parent User">
                                            <option value="" selected disabled>Select Parent User</option>
                                            @foreach($users as $item)
                                                <option value="{{$item->id}}">{{$item->name}} {{$item->surname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="host_type_id">Host Type</label>
                                    <div class="form-control-wrap">
                                            <select class="form-select" name="host_type_id" id="host_type_id" data-placeholder="Select Host Type">
                                            <option value="" selected disabled>Select Host Type</option>
                                            @foreach($host_type as $item)
                                                <option value="{{$item->id}}">{{$item->module_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">First Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="First Name" value="{{ old('owner_name', $ownerName) }}">
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">Last Name</label>
                                    <input type="text" class="form-control" id="surname" name="surname" placeholder="Last Name" value="{{ old('last_name', $last_name) }}">
                                    @error('surname')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">User ID</label>
                                    <input type="text" class="form-control" id="host_key" name="host_key" value="" placeholder="User ID">
                                    @error('host_key')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('host_email', $host_email) }}">
                                    @error('email')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" value="{{ old('host_number', $hostnumber) }}">
                                    @error('phone')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="dob">Date Of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" placeholder="Date Of Birth">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="country">Country</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="country" id="country" data-placeholder="Select Country">
                                            <option value="" selected disabled>Select Country</option>
                                            <option value="PK">Pakistan</option>
                                            <option value="SA">Saudi Arabia</option>
                                            <option value="DB">Dubai</option>
                                            <option value="IR">Iran</option>
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
                                            <option value="Karachi">Karachi</option>
                                            <option value="Peshawar">Peshawar</option>
                                            <option value="Lahore">Lahore</option>
                                            <option value="Quetta">Quetta</option>
                                        </select>
                                        @error('city')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                                    @error('password')
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
