@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Channel Connection</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('connect-link-management.store')}}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="user_id" id="user_id" data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach($users as $items)
                                                <option value="{{$items->id}}">{{$items->name}} {{$items->surname}}</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="min_stay_type">Minimum Stay Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="min_stay_type" id="min_stay_type" data-placeholder="Select Minimum Stay Type">
                                            <option value="" selected disabled>Select Minimum Stay Type</option>
                                            <option value="Arrival">Arrival</option>
                                            <option value="Through">Through</option>
                                        </select>
                                        @error('min_stay_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
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
