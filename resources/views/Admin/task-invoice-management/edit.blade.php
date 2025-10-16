@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Task Invoice</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('task-invoice-management.update', $task_invoices->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="task_id">Task</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="task_id" id="task_id" data-placeholder="Select Task">
                                            <option value="" selected disabled>Select Task</option>
                                            @foreach($task as $items)
                                                <option value="{{$items->id}}" {{$task_invoices->task_id == $items->id ? 'selected' : ''}}>{{$items->task_title}}</option>
                                            @endforeach
                                        </select>
                                        @error('task_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="user_id" id="user_id" data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Vendor</option>
                                            @foreach($users as $items)
                                                <option value="{{$items->id}}" {{$task_invoices->user_id == $items->id ? 'selected' : ''}}>{{$items->name}} {{$items->surname}}</option>
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
                                    <label class="form-label" for="amount">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount" value="{{$task_invoices->amount}}" placeholder="Time Duration">
                                    @error('amount')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="currency">Currency</label>
                                    <input type="text" class="form-control" id="currency" name="currency" value="{{$task_invoices->currency}}" placeholder="Time Currency">
                                    @error('currency')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" placeholder="Description">{{$task_invoices->description}}</textarea>
                                    @error('description')
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
