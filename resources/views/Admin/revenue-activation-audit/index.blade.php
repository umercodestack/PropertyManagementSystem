@extends('Admin.layouts.app')

@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Activation Photography</h3>
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
                    <th>Photo Exists</th>
                    <th>Photos</th>
                    <th>Status</th>
                    <th>Task Status</th>
                    <th>Remarks</th>
                    
                    <th>Updated By</th>
                    <th>Updated At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $key => $audit)
                    <tr>
                        <td>{{ $key + 1 }}</td>

                        {{-- Host ID - clickable --}}
                        <td>
                            @if($audit->hostaboard)
                                <a href="{{ route('hostaboard.edit', $audit->hostaboard->id) }}">
                                    {{ $audit->hostaboard->host_id }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>

                        {{-- Property ID - clickable --}}
                        <td>
                            @if($audit->hostaboard)
                                
                                    {{ $audit->hostaboard->property_id }}
                               
                            @else
                                N/A
                            @endif
                        </td>

                        {{-- Owner Name - clickable --}}
                        <td>
                            @if($audit->hostaboard)
                              
                                    {{ $audit->hostaboard->owner_name }}
                               
                            @else
                                N/A
                            @endif
                        </td>

                        {{-- Photo Exists - Boolean --}}
                        <td>
                            @if(isset($audit->hostaboard->is_photo_exists))
                                {!! $audit->hostaboard->is_photo_exists ? '<span class="text-success">✔️</span>' : '<span class="text-danger">❌</span>' !!}
                            @else
                                N/A
                            @endif
                        </td>

                        {{-- Property Images Link - Clickable --}}
                        <td>
                            @if($audit->hostaboard && $audit->hostaboard->property_images_link)
                                <a href="{{ $audit->hostaboard->property_images_link }}" target="_blank">View</a>
                            @else
                                N/A
                            @endif
                        </td>

                        <td>{{ $audit->status }}</td>
                       
                        <td>{{ $audit->remarks }}</td>
                        <td>{{ $audit->task_remarks }}</td>
                        <td>{{ $audit->updatedBy->name ?? 'N/A' }}</td>
                        <td>{{ date('d-M-Y H:i', strtotime($audit->updated_at)) }}</td>
                        <td>
                            <a href="{{ route('revenue-activation-audit.edit', $audit->id) }}" class="btn btn-primary btn-sm">
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
