@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Activation Maintenance & Inventory</h3>
            <div class="nk-block-des text-soft">
                <p>You have total {{ count($audits) }} audit entries.</p>
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

@if (session('warnings'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach (session('warnings') as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
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
                    <th>Owner Name</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Is Required ?</th>
                    <th>Minor / Major</th>
                    <th>Amount</th>
                    <th>Task Type</th>
                    <th>Task Remarks</th>
                    <th>Updated By</th>
                    <th>Updated At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $key => $audit)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            @if($audit->hostaboard)
                                <a href="{{ route('hostaboard.edit', $audit->hostaboard->id) }}">
                                    {{ $audit->hostaboard->host_id }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $audit->hostaboard->property_id ?? 'N/A' }}</td>
                        <td>{{ $audit->hostaboard->owner_name ?? 'N/A' }}</td>
                      
                        <td>{{ $audit->status }}</td>
                        <td>{{ $audit->remarks }}</td>
                        <td>{{ $audit->is_required ? 'Yes' : 'No' }}</td>
                        <td>{{ $audit->minor_major ? 'Major' : 'Minor' }}</td>
                        <td>{{ $audit->amount }}</td>
                        <td>{{ $audit->task_type }}</td>
                        <td>{{ $audit->task_remarks }}</td>
                        <td>{{ $audit->updatedBy->name ?? 'N/A' }}</td>
                        <td>{{ date('d-M-Y H:i', strtotime($audit->updated_at)) }}</td>
                        <td>
                            <a href="{{ route('sales-activation-audit.edit', $audit->id) }}" class="btn btn-primary btn-sm">
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
