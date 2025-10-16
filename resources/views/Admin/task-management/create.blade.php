@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Task</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('task-management.store')}}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="task_title">Task Title</label>
                                    <input type="text" class="form-control" id="task_title" name="task_title" placeholder="Task Title">
                                    @error('task_title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="category_id">Category</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="category_id" id="category_id" data-placeholder="Select Category ID">
                                            <option value="" selected disabled>Select Category ID</option>
                                            @foreach($category as $items)
                                                <option value="{{$items->id}}">{{$items->category_title}}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="vendor_id">Vendor</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="vendor_id" id="vendor_id" data-placeholder="Select Vendor">
                                            <option value="" selected disabled>Select Vendor</option>
                                            @foreach($vendor as $items)
                                                <option value="{{$items->id}}">{{$items->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('vendor_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="apartment_id">Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="apartment_id" id="apartment_id" data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach($apartment as $items)
                                                <option value="{{$items->id}}">{{$items->title}}</option>
                                            @endforeach
                                        </select>
                                        @error('apartment_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="frequency">Frequency</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="frequency" id="frequency" data-placeholder="Select Frequency">
                                            <option value="" selected disabled>Select Frequency</option>
                                            <option value="onetime">Onetime</option>
                                            <option value="repeat">Repeat</option>
                                        </select>
                                        @error('frequency')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="time_duration">Time Duration</label>
                                    <input type="time" class="form-control" id="time_duration" name="time_duration" placeholder="Time Duration">
                                    @error('time_duration')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="date_duration">Date Duration</label>
                                    <input type="date" class="form-control" id="date_duration" name="date_duration" placeholder="Date Duration">
                                    @error('date_duration')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="completion_time">Completion Time</label>
                                    <input type="time" class="form-control" id="completion_time" name="completion_time" placeholder="Completion Time">
                                    @error('completion_time')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
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
