@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Groups</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($groups)}} groups.</p>
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
                    <th>Group Name</th>
                    <th>Host ID</th>
                    <th>Host Name</th>
                    <th>Create At</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($groups as $key=>$item)
                        <tr>
                            <td>{{++$key}}</td>
                            <td>{{$item->group_name}}</td>
                             <td>{{isset($item->user->id) ? $item->user->id : ''}}</td>
                            <td>{{isset($item->user->name) ? $item->user->name : ''}} {{isset($item->user->surname) ? $item->user->surname : ''}}</td>
                            <td>{{ $item->created_at->format('d-M-Y') }}</td>
                            <td>
                                <a href="{{route('group-management.edit', $item->id)}}" class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-pen"></em>
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
