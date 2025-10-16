@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Edit  Listing Mapping</h3>
        </div>
        <div class="nk-block-head-content">
            <a href="{{ route('listing-audit.index') }}" class="btn btn-outline-light bg-white d-none d-sm-inline-flex">
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
        <form method="POST" action="{{ route('listing-audit-mapping.update', $auditMapping->id) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Host Id</label>
                        <input type="text" value="{{ $auditMapping->hostaboard->host_id }}" class="form-control" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Property Id</label>
                        <input type="text" value="{{ $auditMapping->hostaboard->property_id ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" value="{{ $auditMapping->hostaboard->title ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>


                
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <input type="text" class="form-control" value="{{ $auditMapping->hostaboard->type ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Unit Type</label>
                        <input type="text" class="form-control" value="{{ $auditMapping->hostaboard->unit_type ?? 'N/A' }}" disabled>
                    </div>
                </div>

                 <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Unit Number</label>
                        <input type="text" class="form-control" value="{{ $auditMapping->hostaboard->unit_number ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" value="{{ $auditMapping->hostaboard->floor ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Google Map</label>
                        <input type="text" class="form-control" value="{{ $auditMapping->hostaboard->property_google_map_link ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">LivedIn Share Percentage</label>
                        <input type="text" value="{{ $auditMapping->hostaboard->share_percentage }}" class="form-control" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Host Exclusivity</label>
                        <input type="text" value="{{ $auditMapping->hostaboard->host_exclusivity }}" class="form-control" disabled>
                    </div>
                </div>


               

                @php
                    $fields = [
                        'airbnb' => 'Airbnb',
                        'booking_com' => 'Booking.com',
                        'vrbo' => 'Vrbo',
                        'al_mosafer' => 'Al Mosafer',
                        'agoda' => 'Agoda',
                        'golden_host' => 'Golden Host',
                        'aqar' => 'Aqar',
                        'bayut' => 'Bayut',
                        'google_hotels' => 'Google Hotels',
                        'gathen' => 'Gathen',
                        'darent' => 'Darent',
                    ];
                @endphp

                @foreach ($fields as $key => $label)
                    <div class="col-lg-6">
    <div class="form-group">
        <label class="form-label">
            {{ $label }} URL 
            @if($key === 'airbnb') <span class="text-danger">*</span> @endif
        </label>
        <input type="url" 
               name="{{ $key }}" 
               class="form-control"
               value="{{ old($key, $auditMapping->$key) }}" 
               disabled
               @if($key === 'airbnb') required @endif disabled>
    </div>
</div>


                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ $label }} Status</label>
                            <select name="{{ $key . '_status' }}" class="form-control select2">
                                <option value="" disabled {{ old($key . '_status', $auditMapping[$key . '_status'] ?? '') == '' ? 'selected' : '' }}>
                                    Select Status
                                </option>
                                <option value="To be mapped" disabled
                                    {{ old($key . '_status', $auditMapping[$key . '_status'] ?? '') == 'To be mapped' ? 'selected' : '' }}>
                                    To be mapped
                                </option>
                                <option value="Mapped"
                                    {{ old($key . '_status', $auditMapping[$key . '_status']) == 'Mapped' ? 'selected' : '' }}>
                                    Mapped
                                </option>
                            </select>
                        </div>
                    </div>
                @endforeach

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Overall Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control select2" required>
                            <option value="">Select Status</option>
                            <option value="Pending" {{ old('status', $auditMapping->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approve" {{ old('status', $auditMapping->status) == 'Approve' ? 'selected' : '' }}>Approve</option>
                            <option value="Declined" {{ old('status', $auditMapping->status) == 'Declined' ? 'selected' : '' }}>Declined</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" required>{{ old('remarks', $auditMapping->remarks) }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <em class="icon ni ni-save"></em><span>Update Listing Mapping</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
