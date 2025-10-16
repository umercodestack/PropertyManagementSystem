@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Booking Lead</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($bookings)}} Booking Lead.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('lead-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                <th>Guest</th>
                <th>Apartment</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bookings as $key=>$item)
                <tr>
                    <td>{{++$key}}</td>
                    @php
                        $guest = \App\Models\Guests::where('id', $item->guest_id)->first();
                    @endphp
                    <td>{{$guest->name}} {{$guest->surname}}</td>
                    <td>{{isset($item->apartment->title) ? $item->apartment->title : ''}}</td>
                    <td>{{$item->phone}}</td>
                    <td>{{$item->email}}</td>
                    <td>{{$item->booking_date_start}}</td>
                    <td>{{$item->booking_date_end}}</td>
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
