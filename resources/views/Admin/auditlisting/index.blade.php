@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Activation Listing Creation</h3>
            <div class="nk-block-des text-soft">
                <p>You have total {{ count($auditListings) }} audit listings.</p>
            </div>
        </div>
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                    <em class="icon ni ni-menu-alt-r"></em>
                </a>
                
            </div>
        </div>
    </div>
</div>

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

<div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Host ID</th>
                    <th>Properties Count</th>
            <th>Audit Listing Count</th>
                    <th>Property ID</th>
                    <th>Title</th>
                    <th>Airbnb</th>
                    <th>Booking.com</th>
                    <th>Vrbo</th>
                    <th>Agoda</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Updated By</th>
                    <th>Updated At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditListings as $key => $listing)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            @if($listing->hostaboard)
                                <a href="{{ route('hostaboard.edit', $listing->hostaboard->id) }}">
                                    {{ $listing->hostaboard->host_id }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                         <td>{{ $propertyCounts[$listing->hostaboard->host_id] ?? 0 }}</td>
                <td>{{ $auditCounts[$listing->hostaboard->host_id] ?? 0 }}</td>
                        <td>{{ $listing->hostaboard->property_id }}</td>
                        <td>{{ $listing->hostaboard->title }}</td>
                        <td>{{ $listing->airbnb }}</td>
                        <td>{{ $listing->booking_com }}</td>
                        <td>{{ $listing->vrbo }}</td>
                        <td>{{ $listing->agoda }}</td>
                        <td>{{ $listing->status }}</td>
                        <td>{{ $listing->remarks }}</td>
                        <td>{{ $listing->updatedBy?->name ?? 'N/A' }}</td>
                        <td>{{ $listing->updated_at ? $listing->updated_at->format('d-M-Y H:i') : 'N/A' }}</td>
                        <td>
                            <a href="{{ route('listing-audit.edit', $listing->id) }}" class="btn btn-primary btn-sm">
                                <em class="icon ni ni-pen"></em>
                            </a>
                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
