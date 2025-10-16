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
            <div class="card card-bordered mt-3">
                <div class="card-inner">
                    <h5 class="mb-1">Listing</h5>
                    <hr>
                    <form action="{{ route('update.listing.details.update', $listing->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-center">

                            <div class="col-md-12 mt-3 ">
                                <div class="form-group text-start float-end">
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
@endsection
