@extends('Admin.layouts.app')

@section('content')


<div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Assign Permissions</h3>
                
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('role-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row gy-4">
        <div class="col-md-6">
         <div class="form-group">
         <label for="role">Select Role:</label>
            <form method="GET" id="roleForm" action="{{ route('fetch.permissions') }}">
   
       
        <select name="role_id" id="role" class="form-control" onchange="this.form.submit()">
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                   <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->role_name }}
                </option>
                @endforeach
            </select>
   
</form>
        </div>
      </div>
    </div>
 <br/>

    <div class="card card-bordered card-preview">
       <div class="card-inner">
       @if(!empty($rights))
    <form method="POST" action="{{ route('save.permissions') }}">
        @csrf
        <input type="hidden" name="role_id" value="{{ $selectedRoleId }}">

         <table class="datatable-init-export nowrap table" data-export-title="Export">
         <thead>
         <tr>
                    <th>Module Name</th>
                    <th>View</th>
                <th>Create</th>
                <th>Edit</th>

                   
                    </th>
                </tr>
         </thead>
         <tbody>
            @php
                // Group permissions by module_name
                $groupedPermissions = $rights->groupBy('module_name');
            @endphp

            @foreach($groupedPermissions as $moduleName => $permissions)
                <tr>
                    <td class="align-middle">{{ $moduleName }}</td>

                    {{-- View --}}
                    <td>
                        @php
                            $viewPermission = $permissions->first(function($p) {
                                return stripos($p->permission, 'view') !== false;
                            });
                        @endphp
                        @if($viewPermission)
                        <input type="checkbox" class="form-check-input" name="permissions[]" 
                            value="{{ $viewPermission ? $viewPermission->id : '' }}"
                            {{ $viewPermission && $viewPermission->PermissionAssignedId ? 'checked' : '' }}>
                            @else
        --
    @endif
                    </td>

                    {{-- Create --}}
                    <td>
    @php
        $createPermission = $permissions->first(function($p) {
            return stripos($p->permission, 'create') !== false;
        });
    @endphp
    @if($createPermission)
        <input type="checkbox" class="form-check-input" name="permissions[]" 
            value="{{ $createPermission->id }}"
            {{ $createPermission->PermissionAssignedId ? 'checked' : '' }}>
    @else
        --
    @endif
</td>

                    {{-- Edit --}}
                    <td>
    @php
        $editPermission = $permissions->first(function($p) {
            return stripos($p->permission, 'edit') !== false;
        });
    @endphp
    @if($editPermission)
        <input type="checkbox" class="form-check-input" name="permissions[]" 
            value="{{ $editPermission->id }}"
            {{ $editPermission->PermissionAssignedId ? 'checked' : '' }}>
    @else
        --
    @endif
</td>
                </tr>
            @endforeach
        </tbody>
         </table>
         <button type="submit" class="btn btn-primary" style="color:white; float:inline-end;">Save Permissions</button>
    </form>
@endif
       </div>
    </div>


        


</div>


<script>
function updateActionAndSubmit() {
    const form = document.getElementById('roleForm');
    const roleId = document.getElementById('role').value;

    if (roleId) {
        
        form.action = "{{ url('/fetch-permissions') }}/" + roleId;
        form.submit();
    } else {
        alert('Please select a role.');
    }
}

function toggleAllPermissions(source) {
        let checkboxes = document.querySelectorAll('.permissionCheckbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = source.checked;
        });
    }
</script>
@endsection
