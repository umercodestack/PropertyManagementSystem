@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Apartment Occupancies</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($occupancies) }} Apartment.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>

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
                        <th>Apartment</th>
                        <th>Occupancy</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($occupancies as $key => $item)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $item['title'] }}</td>
                            <td>{{ $item['occupancy'] }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
