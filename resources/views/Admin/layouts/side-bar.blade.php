<div class="nk-sidebar nk-sidebar-fixed is-dark " data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-menu-trigger">
            <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em
                    class="icon ni ni-arrow-left"></em></a>
            <a href="#" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex"
                data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
        </div>
        <div class="nk-sidebar-brand">
            <a href="{{ route('dashboard') }}" class="logo-link nk-sidebar-logo">
                <img class="logo-light logo-img" src="{{ asset('assets/images/logo-dark.png') }}"
                    srcset="{{ asset('assets/images/logo-dark.png') }}" alt="logo">
                <img class="logo-dark logo-img" src="{{ asset('assets/images/logo-dark.png') }}"
                    srcset="{{ asset('assets/images/logo-dark.png') }}" alt="logo-dark">
            </a>
        </div>
    </div><!-- .nk-sidebar-element -->
    <div class="nk-sidebar-element nk-sidebar-body">
        <div class="nk-sidebar-content">
            <div class="nk-sidebar-menu" data-simplebar>

                @if (auth()->user()->role->role_name == 'Super Admin' || auth()->user()->role->role_name == 'Admin' || count(auth()->user()->modules) > 0)
                <ul class="nk-menu">
                    @foreach (auth()->user()->role->role_name == 'Super Admin' || auth()->user()->role->role_name == 'Admin' ? \App\Models\PermissionModule::where('is_parent', 1)->orderBy('position', 'asc')->get() : auth()->user()->modules as $module)
                    
                    @if ($module->module_route && $module->module_route != 'listing-management.index')
                                <li class="nk-menu-item">
                                    @if (Route::has($module->module_route) && !$module->is_vue)
                                        <a href="{{ route($module->module_route) }}" class="nk-menu-link"
                                            {{ $module->module_route }}
                                            @if ($module->module_route == 'calender.multi') target="_blank" @endif>
                                            <span class="nk-menu-icon"><em
                                                    class="{{ $module->module_icon }}"></em></span>
                                            <span class="nk-menu-text">{{ $module->module_name }}</span>
                                        </a>
                                    @elseif ($module->is_vue)
                                        <a href="{{ $module->module_route }}" class="nk-menu-link"
                                            {{ $module->module_route }}
                                            @if ($module->module_route == 'calender.multi') target="_blank" @endif>
                                            <span class="nk-menu-icon"><em
                                                    class="{{ $module->module_icon }}"></em></span>
                                            <span class="nk-menu-text">{{ $module->module_name }}</span>
                                        </a>
                                    @endif
                                </li>
                            @else
                            @if (count($module->childModulesForUser) > 0)
                            <li class="nk-menu-item has-sub">
                                <a href="#" class="nk-menu-link nk-menu-toggle">
                                    <span class="nk-menu-icon"><em class="{{ $module->module_icon }}"></em></span>
                                    <span class="nk-menu-text">{{ $module->module_name }}</span>
                                </a>
                                <ul class="nk-menu-sub">
                                   @foreach ($module->childModulesForUser as $childModule)
                                    <li class="nk-menu-item">
                                            <a href="{{ route($childModule->module_route) }}" class="nk-menu-link">
                                                <span class="nk-menu-icon"><em class="{{ $childModule->module_icon }}"></em></span>
                                                <span class="nk-menu-text">{{ $childModule->module_name }}</span>
                                            </a>
                                        </li>
                                   @endforeach
                                </ul>
                            </li>
                            @endif
                            @endif
                    @endforeach
                </ul>
                @endif
                
                
                   @if (auth()->user()->role->role_name == 'Super Admin' ||
                        auth()->user()->role->role_name == 'Admin' )   
                <ul class="nk-menu">
                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle">
                            <span class="nk-menu-icon"><em class="icon ni ni-link-group"></em></span>
                            <span class="nk-menu-text">Channel Management</span>
                        </a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="{{ route('property-management.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-bag-fill"></em></span>
                                    <span class="nk-menu-text">Properties</span>
                                </a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="{{ route('group-management.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                                    <span class="nk-menu-text">Groups</span>
                                </a>
                            </li>
                        </ul><!-- .nk-menu-sub -->
                    </li>

                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle">
                            <span class="nk-menu-icon"><em class="icon ni ni-link-group"></em></span>
                            <span class="nk-menu-text">Roles & Permissions</span>
                        </a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item">
                                <a href="{{ route('role-management.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                                    <span class="nk-menu-text">Roles</span>
                                </a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="{{ route('permission-module-management.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                                    <span class="nk-menu-text">Permissions</span>
                                </a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="{{ route('assign.permissions') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                                    <span class="nk-menu-text">Assign Permission</span>
                                </a>
                            </li>
                            
                        </ul><!-- .nk-menu-sub -->
                    </li>
                    <!-- .nk-menu-item -->

                    <!-- .nk-menu-item -->
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="{{route('groups')}}" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-calendar-booking-fill"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Bookings</span> --}}
                    {{--                        </a> --}}
                    {{--                        <!-- <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="bookings.html" class="nk-menu-link"><span class="nk-menu-text">All Bookings</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="booking-add.html" class="nk-menu-link"><span class="nk-menu-text">Add Booking</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="all_guests.html" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon fas fa-bed"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Guests</span> --}}
                    {{--                        </a> --}}
                    {{--                        <!-- <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="all_guests.html" class="nk-menu-link"><span class="nk-menu-text">All guests</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">Document verification</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item has-sub"> --}}
                    {{--                        <a href="#" class="nk-menu-link nk-menu-toggle"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon fas fa-house-user"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Property Owners</span> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="all_property_owners.html" class="nk-menu-link"><span class="nk-menu-text">All property owners</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">Document verification</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <?php--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    // if(in_array("apartment_view", $_SESSION["roles_ui"]["roles"])){--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //     echo <<<EOT--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //             <li class="nk-menu-item">--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //             <a href="all_apartments.html" class="nk-menu-link">--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //                 <span class="nk-menu-icon"><em class="icon ni ni-home-fill"></em></span>--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //                 <span class="nk-menu-text">Apartments</span>--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //             </a></li>--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    //         EOT;--}}
                    
                    
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    // }--}}
                                                                                                                                                                                                                                                                                                                                                                                                                                                        {{--                    ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> --}}
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="all_apartments.html" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-home-fill"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Apartments</span> --}}
                    {{--                        </a> --}}
                    {{--                        <!-- <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-list.html" class="nk-menu-link"><span class="nk-menu-text">All apartments</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item has-sub"> --}}
                    {{--                        <a href="#" class="nk-menu-link nk-menu-toggle"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-note-add-fill-c"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Listings</span> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-list.html" class="nk-menu-link"><span class="nk-menu-text">Listings</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">Amenities</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">User provided listing URLs</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">Discounts</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="all_offered_services.html" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon fas fa-concierge-bell"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Offered Services</span> --}}
                    {{--                        </a> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="all_service_providers.html" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon fas fa-toolbox"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Service Providers</span> --}}
                    {{--                        </a> --}}
                    {{--                        <!-- <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-list.html" class="nk-menu-link"><span class="nk-menu-text">All service providers</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">Provided services</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item has-sub"> --}}
                    {{--                        <a href="#" class="nk-menu-link nk-menu-toggle"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon fas fa-tasks"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Tasks</span> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="all_tasks.html" class="nk-menu-link"><span class="nk-menu-text">All Tasks</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="task_executions.html" class="nk-menu-link"><span class="nk-menu-text">Task executions</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="room-type.html" class="nk-menu-link"><span class="nk-menu-text">Task checklists</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}


                    {{--                    <li class="nk-menu-item has-sub"> --}}
                    {{--                        <a href="#" class="nk-menu-link nk-menu-toggle"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-coins"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Financials</span> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="invoices_list.html" class="nk-menu-link"><span class="nk-menu-text">Invoices List</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <!-- <li class="nk-menu-item"> --}}
                    {{--                                <a href="invoice-details.html" class="nk-menu-link"><span class="nk-menu-text">Invoice Details</span></a> --}}
                    {{--                            </li> --> --}}
                    {{--                        </ul> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="communication.html" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-chat-circle-fill"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Communication</span> --}}
                    {{--                        </a> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item"> --}}
                    {{--                        <a href="settings.html" class="nk-menu-link"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-setting-alt-fill"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Settings</span> --}}
                    {{--                        </a> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-heading"> --}}
                    {{--                        <h6 class="overline-title text-primary-alt">Admin functionalities</h6> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                    {{--                    <li class="nk-menu-item has-sub"> --}}
                    {{--                        <a href="#" class="nk-menu-link nk-menu-toggle"> --}}
                    {{--                            <span class="nk-menu-icon"><em class="icon ni ni-coins"></em></span> --}}
                    {{--                            <span class="nk-menu-text">Admin</span> --}}
                    {{--                        </a> --}}
                    {{--                        <ul class="nk-menu-sub"> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="users.html" class="nk-menu-link"><span class="nk-menu-text">Users</span></a> --}}
                    {{--                            </li> --}}
                    {{--                            <li class="nk-menu-item"> --}}
                    {{--                                <a href="roles.html" class="nk-menu-link"><span class="nk-menu-text">Roles</span></a> --}}
                    {{--                            </li> --}}
                    {{--                        </ul> --}}
                    {{--                    </li><!-- .nk-menu-item --> --}}
                </ul><!-- .nk-menu -->
                
                 @endif
            </div><!-- .nk-sidebar-menu -->
        </div><!-- .nk-sidebar-content -->
    </div><!-- .nk-sidebar-element -->
</div>
