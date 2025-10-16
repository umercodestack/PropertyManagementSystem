@extends('Admin.layouts.app')

@section('content')

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Operations Overview</h3>
            <div class="nk-block-des text-soft">
                <p>Here is a snapshot of the activation and listing processes.</p>
            </div>
        </div>
    </div>
</div>

<div class="card card-bordered card-preview">
    <div class="row mb-4 p-4">
        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Activation Form</h6>
                    <h4>{{ $data['total_activations_without_mapping'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Photography</h6>
                    <h4>{{ $data['photography_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Photography Review</h6>
                    <h4>{{ $data['photography_in_review'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Activation Audit</h6>
                    <h4>{{ $data['activation_audit_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Inventory/Maintenance</h6>
                    <h4>{{ $data['inventory_maintenance_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Deep Cleaning</h6>
                    <h4>{{ $data['deep_cleaning_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Cohosting Account</h6>
                    <h4>{{ $data['cohosting_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Listing Creation</h6>
                    <h4>{{ $data['listing_creation_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Listing Mapping</h6>
                    <h4>{{ $data['listing_mapping_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-bordered text-center">
                <div class="card-inner">
                    <h6>Live</h6>
                    <h4>{{ $data['listing_mapping_completed'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
