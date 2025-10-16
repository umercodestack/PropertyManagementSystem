@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head">
    <div class="nk-block-head-content">
        <h4 class="nk-block-title">Edit Activation Photography</h4>
    </div>
</div>






<div class="card card-bordered">
    <div class="card-inner">


    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

        {{-- Host ID + Photos Display --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Host ID:</label>
                <div class="mt-1">
                    @if($audit->hostaboard)
                        <a href="{{ route('hostaboard.edit', $audit->hostaboard->id) }}" target="_blank" class="badge bg-info text-white p-2">
                            <em class="icon ni ni-user-alt"></em>
                              {{ $audit->hostaboard->host_id }}
                        </a>
                    @else
                        <span class="badge bg-light text-muted">N/A</span>
                    @endif
                </div>
            </div>

            
                

            <div class="col-md-6">
                <label class="form-label fw-bold">Property Photos:</label>
                <div class="mt-1">
                    @if($audit->hostaboard && $audit->hostaboard->property_images_link)
                        <a href="{{ $audit->hostaboard->property_images_link }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <em class="icon ni ni-camera-fill"></em> View Photos
                        </a>
                    @else
                        <span class="badge bg-light text-muted">N/A</span>
                    @endif
                </div>
            </div>


            <div class="col-lg-6 mt-5">
                    <div class="form-group">
                        <label class="form-label">Property Id</label>
                        <input type="text" value="{{ $audit->hostaboard->property_id ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>

                <div class="col-lg-6 mt-5">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" value="{{ $audit->hostaboard->title ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>
        </div>

        {{-- Form Start --}}
        <form method="POST" action="{{ route('revenue-activation-audit.update', $audit->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-select" required>
                     <option value="">--Select Status--</option> 
                    <option value="approved" {{ $audit->status == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="declined" {{ $audit->status == 'declined' ? 'selected' : '' }}>Declined</option>
                    <option value="pending" {{ $audit->status == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $audit->remarks) }}</textarea>
            </div>

            <div class="form-group">
                <label>Task Status</label>
                <!-- <select name="task_status" class="form-select" disabled>
                    <option value="">Select</option>
                    <option value="generated" {{ $audit->task_status == 'generated' ? 'selected' : '' }}>Generated</option>
                    <option value="in progress" {{ $audit->task_status == 'in progress' ? 'selected' : '' }}>In progress</option>
                    <option value="no required" {{ $audit->task_status == 'no required' ? 'selected' : '' }}>No Required</option>
                     <option value="completed" {{ $audit->task_status == 'completed' ? 'selected' : '' }}>Completed</option>
                </select> -->

                <input type="text" class="form-control" id="task_status" name="task_status" value="{{$audit->task_status}}" readonly disabled >

            </div>

            <div class="form-group" style="display:none">
                <label>Task Remarks</label>
                <textarea name="task_remarks" class="form-control" rows="2" disabled>{{ old('task_remarks', $audit->task_remarks) }}</textarea>
            </div>

            <div class="form-group mt-4">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('revenue-activation-audit.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
