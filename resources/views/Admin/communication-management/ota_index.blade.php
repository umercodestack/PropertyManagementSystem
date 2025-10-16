@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Otas Booking</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($bookings)}} Booking.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('booking-management.index')}}" class="btn btn-secondary">Lokal Bookings</a>
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
                <th>ID</th>
                <th>OTA Name</th>
                <th>Guest Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bookings as $key=>$item)
                <tr>
                    <td>{{++$key}}</td>
                    <td>{{$item['id']}}</td>
                    <td>{{$item['attributes']['ota_name']}}</td>
                    <td>{{$item['attributes']['customer']['name']}}</td>
                    <td>{{$item['attributes']['customer']['mail']}}</td>
                    <td>{{$item['attributes']['customer']['phone']}}</td>
                    <td>{{$item['attributes']['customer']['address']}} {{$item['attributes']['customer']['country']}} {{$item['attributes']['customer']['city']}}</td>
                    <td>{{$item['attributes']['arrival_date']}}</td>
                    <td>{{$item['attributes']['departure_date']}}</td>

                    {{--                    <td>--}}
{{--                        <a title="Activate Channel" href="{{route('activate-channel', $item['id'])}}" class="btn btn-primary btn-sm">--}}
{{--                            <em class="icon ni ni-edit"></em>--}}
{{--                        </a>--}}
{{--                    </td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection
