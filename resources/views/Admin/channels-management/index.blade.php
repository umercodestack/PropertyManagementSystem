@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Channels</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($channels) }} Channels.</p>
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{ route('listing.createBookingComConnection') }}"
                                        class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                        <th>Title</th>
                        <th>Channel type</th>
                        <th>Create At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($channels as $key => $item)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $item->user->name }}
                                {{ $item->user->surname }}-{{ $item->connection_type == '' ? 'airbnb' : $item->connection_type }}
                            </td>
                            <td>{{ $item->connection_type == '' ? 'airbnb' : $item->connection_type }}</td>
                            <td>{{ $item['created_at'] }}</td>
                            <td>
                                <a title="Activate Channel" href="{{ route('activate-channel', $item->id) }}"
                                    class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-edit"></em>
                                </a>
                                <a title="Listings"
                                    href="{{ route('listing-management.index') }}?channel_id={{ $item->id }}"
                                    class="btn btn-secondary btn-sm">
                                    <em class="icon ni ni-article"></em>
                                </a>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
