@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Revenue Triggers Logs</h3>
                <div class="nk-block-des text-soft">
                    <!--<p>You have total 0 Revenue.</p>-->
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table" data-export-title="Export">
            <thead>
            <tr>
                <th>Listing Name</th>
                <th>Trigger Type</th>
                <th>Status</th>
                <th>Log</th>
                <th>User Name</th>
            </tr>
            </thead>
            <tbody>
                @if(!empty($triggers_histories))
                @foreach($triggers_histories as $th)
                <tr>
                
                    <td>{{$th->listing_name}}</td>
                    <td>{{$th->trigger_type}}</td>
                    <td>{{$th->status}}</td>
                    <td>{{$th->trigger_log}}</td>
                    <td>{{$th->user_name}}</td>
                </tr>
                @endforeach
                @endif
            
            
            
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
