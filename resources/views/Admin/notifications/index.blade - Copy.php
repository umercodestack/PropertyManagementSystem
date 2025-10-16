@extends('Admin.layouts.app')

@section('content')

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Role-Based Notifications</h3>
        </div>
        <div class="nk-block-head-content">
            
        </div>
    </div>
</div>

<div class="card card-bordered mt-3">
    <div class="card-inner">
        <form method="GET" action="" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="perPage" class="form-select" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ request('perPage', 10) == $size ? 'selected' : '' }}>{{ $size }} per page</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search Notifications" value="{{ request('search', '') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3">
                <p>Total Notifications: {{ $totalRecords }}</p>
                </div>    
            </div>
        </form>

        @if ($notifications->count())
            

            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notifications as $index => $notification)
                        <tr>
                            <td>{{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}</td>
                            <td>{{ $notification->message }}</td>
                            <td>
                                @if ($notification->is_seen_by_all)
                                    <span class="badge bg-success">Seen</span>
                                @else
                                    <span class="badge bg-warning">Unseen</span>
                                @endif
                            </td>
                            <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                {{ $notifications->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @else
            <p>No notifications found.</p>
        @endif
    </div>
</div>

@endsection
