@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Edit Activation Co Host Account</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route('backend-ops.index') }}" class="btn btn-outline-light bg-white d-none d-sm-inline-flex">
                <em class="icon ni ni-arrow-left"></em><span>Back</span>
            </a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-bordered">
    <div class="card-inner">
        <form method="POST" action="{{ route('backend-ops.update', $op->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Host ID</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->host_id ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Property ID</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->property_id ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->title ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->type ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Unit Type</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->unit_type ?? 'N/A' }}" disabled>
                    </div>
                </div>

                 <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Unit Number</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->unit_number ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->floor ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Google Map</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->property_google_map_link ?? 'N/A' }}" disabled>
                    </div>
                </div>


                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Account Title</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->host_bank_detail ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->bank_name ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">IBAN No</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->iban_no ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Swift Code</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->swift_code ?? 'N/A' }}" disabled>
                    </div>
                </div>



                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->airbnb_email ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="text" class="form-control" value="{{ $op->hostaboard->airbnb_password ?? 'N/A' }}" disabled>
                    </div>
                </div>


                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Select Status</option>
                            <option value="pending" {{ old('status', $op->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('status', $op->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="declined" {{ old('status', $op->status) == 'declined' ? 'selected' : '' }}>Declined</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $op->remarks) }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <em class="icon ni ni-save"></em><span>Update</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
