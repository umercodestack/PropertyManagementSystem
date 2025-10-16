@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Tasks Invoices</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($taskInvoices)}} Invoices.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('task-invoice-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                    <th>Task ID</th>
                    <th>Task Title</th>
                    <th>Host ID</th>
                    <th>Host Name</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Create At</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($taskInvoices as $key=>$item)
                        <tr>
                            <td>{{++$key}}</td>
                            <td>{{$item->task->id}}</td>
                            <td>{{$item->task->task_title}}</td>
                            <td>{{$item->user->id}}</td>
                            <td>{{$item->user->name}} {{$item->user->surname}}</td>
                            <td>{{$item->amount}}</td>
                            <td>{{$item->currency}}</td>
                            <td>{{$item->description}}</td>
                            <td>{{$item->status}}</td>
                            <td>{{ $item->created_at->format('d-M-Y') }}</td>
                            <td>
                                <a href="{{route('task-invoice-management.edit', $item->id)}}" class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-pen"></em>
                                </a>
                                <a href="{{route('task-invoice-print', $item->id)}}" class="btn btn-secondary btn-sm" target="_blank">
                                    <em class="icon ni ni-printer"></em>
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
