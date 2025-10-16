@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head">
    <div class="nk-block-head-content">
        <h4 class="nk-block-title">Edit Activation Maintenance & Inventory</h4>
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

        {{-- Host ID + Photos --}}
        <div class="row mb-4">
            

            <div class="col-lg-6 ">
                    <div class="form-group">
                        <label class="form-label">Host Id</label>
                        <input type="text" value="{{ $audit->hostaboard->host_id ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>

          


            <div class="col-lg-6 ">
                    <div class="form-group">
                        <label class="form-label">Property Id</label>
                        <input type="text" value="{{ $audit->hostaboard->property_id ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" value="{{ $audit->hostaboard->title ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>

        </div>

        {{-- Form Start --}}
        <form method="POST" action="{{ route('sales-activation-audit.update', $audit->id) }}">
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
                <label>is Required ? </label>
                <select name="is_required" class="form-select" disabled>
                    <option value="1" {{ $audit->is_required ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$audit->is_required ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="form-group">
                <label>Minor / Major</label>
                <select name="minor_major" class="form-select" disabled>
                    <option value="0" {{ !$audit->minor_major ? 'selected' : '' }}>Minor</option>
                    <option value="1" {{ $audit->minor_major ? 'selected' : '' }}>Major</option>
                </select>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $audit->amount) }}" disabled>
            </div>

            <div class="form-group">
                <label>Task Type</label>
                <input type="text" name="task_type" class="form-control" value="{{ old('task_type', $audit->task_type) }}" disabled>
            </div>

            <div class="form-group">
                <label>Task Remarks</label>
                <textarea name="task_remarks" class="form-control" rows="2" disabled>{{ old('task_remarks', $audit->task_remarks) }}</textarea>
            </div>

            <div class="form-group mt-4">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('sales-activation-audit.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
