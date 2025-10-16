@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Module</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('permission-module-management.update', $module->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="module_name">Module Title</label>
                                    <input type="text" class="form-control" id="module_name" name="module_name" value="{{ $module->module_name }}" placeholder="Module Title">
                                    @error('module_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="permission">Permission</label>
                                    <input type="text" class="form-control" id="permission" name="permission" value="{{ $module->permission }}" placeholder="Permission">
                                    @error('permission')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="module_icon">Module Icon</label>
                                    <input type="text" class="form-control" id="module_icon" name="module_icon" value="{{ $module->module_icon }}" placeholder="Module Icon">
                                    @error('module_icon')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="module_route">Module Route</label>
                                    <input type="text" class="form-control" id="module_route" name="module_route" value="{{ $module->module_route }}" placeholder="Module Route">
                                    @error('module_route')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="position">Position</label>
                                    <input type="text" class="form-control" id="position" name="position" value="{{ $module->position }}" placeholder="Module Route">
                                    @error('position')
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
