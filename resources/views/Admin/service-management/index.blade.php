@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Services</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($services)}} Services.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('service-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                <th>Service Name</th>
                <th>Service Title</th>
                <th>Service Category</th>
                <th>Description</th>
                <th>Create At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($services as $key=>$item)
                <tr>
                    <td>{{++$key}}</td>
                    <td>{{$item->service_name}}</td>
                    <td>{{$item->title}}</td>
                    <td>{{$item->ServiceCategories->category_name}}</td>
                    <td>{{$item->description}}</td>
                    <td>{{$item->created_at}}</td>
                    <td>
                        <a href="{{route('service-management.edit', $item->id)}}" class="btn btn-primary btn-sm">
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
