@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Manage Permissions</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    
                    <form action="{{route('user-management.update-permissions', $user->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Permission Modules</label>
                                    @foreach($permissionModules as $module)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" id="permission_{{ $module->id }}" value="{{ $module->id }}"
                                            {{ in_array($module->id, $userPermissions->pluck('id')->toArray()) ? 'checked' : '' }}
                                            >
                                            <label class="form-label" for="permission_{{ $module->id }}">
                                                {{ $module->module_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @error('permissions')
                                    <span id="fva-permissions-error" class="invalid">{{ $message }}</span>
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
