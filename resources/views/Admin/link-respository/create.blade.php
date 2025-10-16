@extends('Admin.layouts.app')
@section('content')

<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Link Repository</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('linkrepository.store') }}" method="POST">
                        @csrf
                        
                        
                         @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                        <div class="row gy-4">


                        <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="host_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id ="host_id" name="host_id"
                                            data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ isset($linkrepository) && $linkrepository->host_id === $items->id ? 'selected' : '' }}>
                                                    {{ $items->name }}
                                                    {{ $items->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('host_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>



                            <div class="col-md-6" >
                                <div class="form-group">
                                    <label class="form-label" for="listing_id">Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="listing_id" id = "listing_id"
                                            data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach ($listings as $items)
                                                @php
                                                    $listing_json = json_decode($items->listing_json);
                                                @endphp
                                                <option value="{{ $items->id }}">{{ $listing_json->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('listing_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="airbnb">Airbnb</label>
                                    <input type="url" class="form-control" id="airbnb" name="airbnb" value="{{ old('airbnb') }}">
                                    @error('airbnb')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="gathern">Gathern</label>
                                    <input type="url" class="form-control" id="gathern" name="gathern" value="{{ old('gathern') }}">
                                    @error('gathern')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="booking">Booking.com</label>
                                    <input type="url" class="form-control" id="booking" name="booking" value="{{ old('booking') }}">
                                    @error('booking')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="vrbo">Vrbo</label>
                                    <input type="url" class="form-control" id="vrbo" name="vrbo" value="{{ old('vrbo') }}">
                                    @error('vrbo')
                                        <span class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
    <div class="form-group">
        <label class="form-label" for="status">Status</label>
        <select class="form-control" id="status" name="status">
            <option value="" disabled selected>Select Status</option>
            <option value="Pending" {{ old('Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Published" {{ old('Published') == 'Published' ? 'selected' : '' }}>Published</option>
         
            <!-- Add more options as needed -->
        </select>
        @error('status')
            <span class="invalid">{{ $message }}</span>
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


    <!-- <script>
   $(document).ready(function() {
    $('#listing_id').prop('disabled', true); 

    $('#host_id').change(function() {
        let hostId = $(this).val();
        if (hostId) {
            $('#listing_id').prop('disabled', true);

            $.ajax({
                url: '/get-listings-by-host/' + hostId,
                type: 'GET',
                success: function(data) {
                    $('#listing_id').empty();
                    $('#listing_id').append('<option value="" selected disabled>Select Apartment</option>');
                    $.each(data, function(key, value) {
                        $('#listing_id').append('<option value="' + value.id + '">' + value.title + '</option>');
                    });
                    $('#listing_id').prop('disabled', false);
                },
                error: function() {
                    alert('Failed to fetch listings');
                    $('#listing_id').prop('disabled', true);
                }
            });
        } else {
            $('#listing_id').prop('disabled', true);
        }
    });
});

</script>     -->
@endsection
