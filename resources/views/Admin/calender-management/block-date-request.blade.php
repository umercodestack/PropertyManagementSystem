@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Block Date Request</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($block_date_request)}} requests.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('group-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                    <th>Host</th>
                    <th>Listing</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Request for</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($block_date_request as $key=>$item)
                    @php
                        $listing_details = json_decode($item->listing->listing_json);
                        // dd($item);
                    @endphp
                        <tr>
                            <td>{{++$key}}</td>
                            <td>{{isset($item->user->name) ? $item->user->name : ''}} {{isset($item->user->surname) ? $item->user->surname : ''}}</td>
                            <td>{{ $listing_details->title }}</td>
                            <td>{{ $item->start_date }}</td>
                            <td>{{ $item->end_date }}</td>
                            <td>{{ $item->availability == 0 ? 'Block' : 'Unblock' }}</td>
                            <td>{{ $item->status}}</td>
                            <td>{{ $item->created_at->format('d-M-Y') }}</td>
                            <td>
                                <a href="{{route('block.date.request.accept', $item->id)}}" class="btn btn-primary btn-sm">
                                    Accept
                                </a>
                                <a href="{{route('block.date.request.decline', $item->id)}}" class="btn btn-danger btn-sm">
                                    Decline
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection