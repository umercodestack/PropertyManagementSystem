@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Users</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($users)}} users.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('user-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                <th>User ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Host Type</th>
                <th>Email</th>
                <th>Phone</th>
{{--                <th>DOB</th>--}}
{{--                <th>Gender</th>--}}
{{--                <th>Country</th>--}}
                <th>Create At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $key=>$item)
                <tr>
                    <td>{{++$key}}</td>
                    <td>{{$item->host_key}}</td>
                    <td>{{$item->name}} {{$item->surname}}</td>
                    <td>
                        @php
                            $role = \App\Models\Roles::where('id', $item->role_id)->first();
                        @endphp
                        {{$role->role_name}}
                    </td>
                    <td>
                        @php
                            $hostType = \App\Models\HostType::where('id', $item->host_type_id)->first();
                        @endphp
                        {{isset($hostType->module_name) ? $hostType->module_name : ''}}
                    </td>
                    <td>{{$item->email}}</td>
                    <td>{{$item->phone}}</td>
{{--                    <td>{{$item->dob}}</td>--}}
{{--                    <td>{{$item->gender}}</td>--}}
{{--                    <td>{{$item->country}}</td>--}}
                    <td>{{date("d-M-Y",strtotime($item->created_at))}}</td>

                    <td>
                        <a href="{{route('user-management.edit', $item->id)}}" class="btn btn-primary btn-sm">
                            <em class="icon ni ni-pen"></em>
                        </a>
                        <a href="#" class="btn btn-danger btn-sm">
                            <em class="icon ni ni-trash"></em>
                        </a>
                        <a href="{{route('user-management.manage-permissions', $item->id)}}" class="btn btn-warning btn-sm">
                            <em class="icon ni ni-unlock-fill"></em>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
