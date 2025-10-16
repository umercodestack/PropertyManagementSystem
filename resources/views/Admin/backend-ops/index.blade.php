@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Activation Co Host Account</h3>
            <div class="nk-block-des text-soft">
                <p>You have total {{ count($ops) }} audit backend op records.</p>
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
                    <th>Property ID</th>
                    <th>Title</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Updated By</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ops as $key => $op)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $op->hostaboard->host_id }}</td>
                    <td>{{ $op->hostaboard->property_id }}</td>
                    <td>{{ $op->hostaboard->title }}</td>
                    <td>{{ $op->hostaboard->airbnb_email }}</td>
                    <td>{{ $op->hostaboard->airbnb_password }}</td>
                    
                    
                
                    <td>{{ $op->status ?? 'N/A' }}</td>
                    <td>{{ $op->remarks ?? '-' }}</td>
                    <td>{{ $op->updatedBy?->name ?? 'N/A' }}</td>
                    <td>{{ $op->created_at ? $op->created_at->format('d-M-Y H:i') : 'N/A' }}</td>
                    <td>{{ $op->updated_at ? $op->updated_at->format('d-M-Y H:i') : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('backend-ops.edit', $op->id) }}" class="btn btn-sm btn-warning">
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
