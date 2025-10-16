@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Verify Code</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('authenticateVrboVerifyCode')}}" method="POST">
                        @csrf
                        {{-- {{ dd($request) }} --}}
                        <div class="row gy-4">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="code">Code</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" name="code">
                                        @error('code')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="ch_channel_id" value="{{ $request['ch_channel_id'] }}">
                            <input type="hidden" name="user_id" value="{{ $request['user_id'] }}">
                            <input type="hidden" name="connection_type" value="{{ $request['connection_type'] }}">
                            <input type="hidden" name="token" value="{{ json_encode($token) }}">

                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
