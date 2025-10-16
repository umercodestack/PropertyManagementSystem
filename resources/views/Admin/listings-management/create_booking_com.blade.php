@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Connect OTAS</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('channel-management.store') }}" method="POST">
                        @csrf
                        <div class="row gy-4 align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="title">Title</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" name="title" id="title"
                                            placeholder="Enter Room ID">
                                        @error('title')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="listing_id">Select Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="listing_id" id="listing_id"
                                            data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach ($listings as $items)
                                                <option value="{{ $items->id }}">{{ $items->title }}</option>
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
                                    <label class="form-label" for="hotel_id">Enter Hotel ID</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" name="hotel_id" id="hotel_id"
                                            placeholder="Enter Hotel ID">
                                        @error('hotel_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mt-4">
                                    <span id="connection-status" class="ms-2 "></span>
                                    {{-- <label class="form-label" for="room_id">Enter Room ID</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" name="room_id" id="room_id"
                                            placeholder="Enter Room ID">
                                        @error('room_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div> --}}
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="button" class="btn btn-secondary" id="test-connection-btn">Test
                                        Connection</button>

                                    <button type="submit" id="submit-btn" class="btn btn-primary" disabled>Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('test-connection-btn').addEventListener('click', function() {
            console.log('Testing')
            const hotelId = document.getElementById('hotel_id').value;
            const statusElement = document.getElementById('connection-status');
            const submitBtn = document.getElementById('submit-btn');

            // Disable submit until successful test
            submitBtn.disabled = true;

            if (!hotelId) {
                statusElement.innerHTML = '<span class="text-danger">Please enter a Hotel ID</span>';
                return;
            }

            statusElement.innerHTML = '<span class="text-info">Testing connection...</span>';

            fetch('{{ route('listing.testBookingComConnection') }}?hotel_id=' + encodeURIComponent(hotelId), {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusElement.innerHTML = '<span class="text-success">' + data.message +
                            '</span>';
                        submitBtn.disabled = false; // ✅ Enable submit
                    } else {
                        statusElement.innerHTML = '<span class="text-danger">' + data.message +
                            ' Extranet is not added or wrong hotel ID</span>';
                        submitBtn.disabled = true; // ✅ Keep disabled
                    }
                })
                .catch(error => {
                    statusElement.innerHTML = '<span class="text-danger">Error: ' + error.message + '</span>';
                    submitBtn.disabled = true; // ✅ Keep disabled
                });
        });
    </script>
@endsection
