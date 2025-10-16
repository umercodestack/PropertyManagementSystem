@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Audit</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($audit) }} Audit.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{ route('audit-management.create') }}" class="btn btn-icon btn-primary"><em
                                            class="icon ni ni-plus"></em></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init-export nowrap table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Key Code</th>
                        <th>Apartment</th>
                        <th>Type</th>
                        <th>Unit Type</th>
                        <th>Floor</th>
                        <th>Unit Number</th>
                       
                        <th>Assigned To</th>
                        <th>Audit Date</th>
                        
                        <th>Status</th>
                        <th>Create At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($audit as $key => $item)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $item->key_code }}</td>
                            <td>
                                @if ($item->listing != null)
                                    @php
                                        $lising_name = json_decode($item->listing->listing_json);
                                    @endphp
                                    {{ $lising_name->title }}
                                @else
                                    {{ $item->listing_title }}
                                @endif

                            </td>
                            
                            
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->unit_type }}</td>
                            <td>{{ $item->floor }}</td>
                            <td>{{ $item->unit_number }}</td>
                            
                            
                            <td>{{ $item->assignToUser->name ?? NULL }} {{ $item->assignToUser->surname ?? NULL }}</td>
                            <td>{{ $item->audit_date }}</td>
                          
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->created_at->format('d-M-Y') }}</td>
                            <td>
                                <a href="{{ route('audit-management.edit', $item->id) }}" class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-pen"></em>
                                </a>
                                <a title="Create Deep Cleaning"
                                    href="{{ route('deep-cleaning-management.create') }}?audit_id={{ $item->id }}"
                                    class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-plus"></em>
                                </a>
                                <a href="#" class="btn btn-danger btn-sm">
                                    <em class="icon ni ni-trash"></em>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
