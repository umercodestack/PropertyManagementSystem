@extends('Admin.layouts.app')
@section('content')

    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Vouchers</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($vouchers)}} vouchers</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('voucher-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                <th>Voucher Code</th>
                <th>Discount Type</th>
                <th>Discount</th>
                <th>Max Discount Amount</th>
                <th>Voucher Usage Limit</th>
                <th>Active</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($vouchers as $key=>$voucher)
                <tr>
                    <td>{{++$key}}</td>
                    <td>{{$voucher->voucher_code}}</td>
                    <td>{{$voucher->discount_type}}</td>
                    <td>{{$voucher->discount}}</td>
                    <td>{{$voucher->max_discount_amount}}</td>
                    <td>{{$voucher->voucher_usage_limit}}</td>
                    <td>{!! $voucher->is_enabled == 1 ? '<label class="alert alert-success" role="alert">Yes</label>' : '<label class="alert alert-danger" role="alert">No</label>' !!}</td>
                    
                    
                    

                    <td>
                        <a href="{{route('voucher-management.edit', $voucher->id)}}" class="btn btn-primary btn-sm">
                            <em class="icon ni ni-pen"></em>
                        </a>
                        <!--<a href="#" class="btn btn-danger btn-sm">-->
                        <!--    <em class="icon ni ni-trash"></em>-->
                        <!--</a>-->
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
