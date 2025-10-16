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
                    <form action="{{ route('user-management.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="role_id">Role</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="role_id" id="role_id"
                                            data-placeholder="Select Role">
                                            <option value="" selected disabled>Select Role</option>
                                            @foreach ($roles as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ $user->role_id == $items->id ? 'selected' : '' }}>
                                                    {{ $items->role_name }}</option>
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
                                        <select class="form-select" name="parent_user" id="parent_user"
                                            data-placeholder="Select Parent User">
                                            <option value="" selected disabled>Select Parent User</option>
                                            @foreach ($users as $item)
                                                <option {{ $user->parent_user_id == $item->id ? 'selected' : '' }}
                                                    value="{{ $item->id }}">
                                                    {{ $item->name }} {{ $item->surname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="host_type_id">Host Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="host_type_id" id="host_type_id"
                                            data-placeholder="Select Host Type">
                                            <option value="" selected disabled>Select Host Type</option>
                                            @foreach ($host_type as $item)
                                                <option {{ $user->host_type_id == $item->id ? 'selected' : '' }}
                                                    value="{{ $item->id }}">{{ $item->module_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">First Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ $user->name }}" placeholder="First Name">
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">Last Name</label>
                                    <input type="text" class="form-control" id="surname" name="surname"
                                        value="{{ $user->surname }}" placeholder="Last Name">
                                    @error('surname')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="surname">User ID</label>
                                    <input type="text" class="form-control" id="host_key" name="host_key"
                                        value="{{ $user->host_key }}" placeholder="User ID">
                                    @error('host_key')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $user->email }}" placeholder="Email">
                                    @error('email')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        value="{{ $user->phone }}" placeholder="Phone">
                                    @error('phone')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="dob">Date Of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob"
                                        value="{{ $user->dob }}" placeholder="Date Of Birth">
                                    @error('dob')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="gender">Gender</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="gender" id="gender"
                                            data-placeholder="Select Gender">
                                            <option value="" selected disabled>Select Gender</option>
                                            <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>
                                                Male
                                            </option>
                                            <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>
                                                Female
                                            </option>
                                            <option value="Others" {{ $user->gender == 'Others' ? 'selected' : '' }}>
                                                Others
                                            </option>
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
                                        <select class="form-select select2" name="country" id="country"
                                            data-placeholder="Select Country">
                                            <option value="" selected disabled>Select Country</option>
                                            @foreach ($countries as $country)
                                                <option {{ $user->country == $country->id ? 'selected' : '' }}
                                                    value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('country')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- for city id --}}
                            <input hidden type="text" id="SelectedCity"
                                @if (is_numeric($user->city)) value="{{ $user->city }}" @else value="lunc" @endif />


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="city">City</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="city" id="city"
                                            data-placeholder="Select City">
                                            {{-- <option value="" selected disabled>Select City</option> --}}
                                            {{-- <option value="Karachi">Karachi</option>
                                            <option value="Peshawar">Peshawar</option>
                                            <option value="Lahore">Lahore</option>
                                            <option value="Quetta">Quetta</option> --}}
                                        </select>
                                        @error('city')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="plan_verified">Status</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="plan_verified" id="plan_verified"
                                            data-placeholder="Select Status">
                                            <option value="" selected disabled>Select Status</option>
                                            <option value="1" {{ $user->plan_verified == 1 ? 'selected' : '' }}>
                                                Activate</option>
                                            <option value="0" {{ $user->plan_verified == 0 ? 'selected' : '' }}>
                                                Deactivate</option>
                                        </select>
                                        @error('plan_verified')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            {{--                            <div class="col-md-6"> --}}
                            {{--                                <div class="form-group"> --}}
                            {{--                                    <label class="form-label" for="country">Country</label> --}}
                            {{--                                    <div class="form-control-wrap"> --}}
                            {{--                                        <select class="form-select" name="country" id="country" data-placeholder="Select Country"> --}}
                            {{--                                            <option value="" selected disabled>Select Country</option> --}}
                            {{--                                            <option value="PK">Pakistan</option> --}}
                            {{--                                            <option value="SA">Saudi Arabia</option> --}}
                            {{--                                            <option value="DB">Dubai</option> --}}
                            {{--                                            <option value="IR">Iran</option> --}}
                            {{--                                        </select> --}}
                            {{--                                        @error('country') --}}
                            {{--                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span> --}}
                            {{--                                        @enderror --}}
                            {{--                                    </div> --}}
                            {{--                                </div> --}}
                            {{--                            </div> --}}
                            {{--                            <div class="col-md-6"> --}}
                            {{--                                <div class="form-group"> --}}
                            {{--                                    <label class="form-label" for="city">City</label> --}}
                            {{--                                    <div class="form-control-wrap"> --}}
                            {{--                                        <select class="form-select" name="city" id="city" data-placeholder="Select City"> --}}
                            {{--                                            <option value="" selected disabled>Select City</option> --}}
                            {{--                                            <option value="Karachi">Karachi</option> --}}
                            {{--                                            <option value="Peshawar">Peshawar</option> --}}
                            {{--                                            <option value="Lahore">Lahore</option> --}}
                            {{--                                            <option value="Quetta">Quetta</option> --}}
                            {{--                                        </select> --}}
                            {{--                                        @error('city') --}}
                            {{--                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span> --}}
                            {{--                                        @enderror --}}
                            {{--                                    </div> --}}
                            {{--                                </div> --}}
                            {{--                            </div> --}}

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="longitude">Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Password">
                                    @error('password')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="able_to_block_calender">Block Date Permission</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="able_to_block_calender"
                                            id="able_to_block_calender" data-placeholder="Select Status">
                                            <option value="" selected disabled>Block Date Permission</option>
                                            <option value="1"
                                                {{ $user->able_to_block_calender == 1 ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0"
                                                {{ $user->able_to_block_calender == 0 ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                        @error('able_to_block_calender')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @if ($user->role_id == 2)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="cleaning_per_cycle">Cleaning Per Cycle</label>
                                        <input type="text" class="form-control" id="cleaning_per_cycle"
                                            name="cleaning_per_cycle" placeholder="Cleaning Per Cycle"
                                            value="{{ $user->cleaning_per_cycle }}">
                                        @error('cleaning_per_cycle')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif
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

{{-- 
<script>
    window.onload = (event) => {
        const countryId = document.getElementById('country').value
        const url = `${window.location.origin}/api/cities?country_id=${countryId}`;
        document.getElementById('city').innerHTML = "<option value=''>Select City</option>"
        const selectedCity = document.getElementById('SelectedCity').value
        if (countryId)
            fetch(url)
            .then(r => r.json())
            .then(cities => {
                cities.slice(0, cities.length > 1000 ? 1000 : cities.length).forEach(city => {
                    document.getElementById('city').innerHTML +=
                        `<option value='${city.id}'>${city.name}</option>`;
                })
                document.getElementById('city').value = selectedCity
            });


    };

</script>  --}}
