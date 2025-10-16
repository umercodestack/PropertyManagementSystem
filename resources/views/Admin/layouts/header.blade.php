<div class="nk-header nk-header-fixed is-light position-static">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-menu-trigger d-xl-none ms-n1">
                <a href="#" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em
                        class="icon ni ni-menu"></em></a>
            </div>
            <div class="nk-header-brand d-xl-none">
                <a href="html/index.html" class="logo-link">
                    <img class="logo-light logo-img" src="{{ asset('assets/images/logo-dark.png') }}"
                        srcset="{{ asset('assets/images/logo-dark.png') }}" alt="logo">
                    <img class="logo-dark logo-img" src="{{ asset('assets/images/logo-dark.png') }}"
                        srcset="{{ asset('assets/images/logo-dark.png') }}" alt="logo-dark">
                </a>
            </div><!-- .nk-header-brand -->
            <!--<div class="nk-header-news d-none d-xl-block">
                <div class="nk-news-list">
                    <a class="nk-news-item" href="#">
                        <div class="nk-news-icon">
                            <em class="icon ni ni-card-view"></em>
                        </div>
                        <div class="nk-news-text">
                            <p>Do you know the latest update of 2022? <span> A overview of our is now available on YouTube</span></p>
                            <em class="icon ni ni-external"></em>
                        </div>
                    </a>
                </div>
            </div>--><!-- .nk-header-news -->
            <div class="nk-header-tools">
                <ul class="nk-quick-nav">
                    <li class="dropdown language-dropdown d-none d-sm-block me-n1">
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-s1">
                        </div>
                    </li><!-- .dropdown -->
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="user-toggle">
                                <div class="user-avatar sm">

                                    <div class="nk-activity-media user-avatar bg-success">
                                        {{-- <em class="icon ni ni-user-alt"></em>
                                        <img id="user-profile-pic1" src="" alt=""> --}}
                                        {{ Auth::user()->name[0] . Auth::user()->surname[0] }}
                                    </div>
                                </div>
                                <div class="user-info d-none d-md-block">
                                    <div class="user-status" id="profile-user-role">
                                        {{-- ROLE --}}
                                        {{ Auth::user()->role->role_name }}
                                    </div>
                                    <div class="user-name dropdown-indicator" id="profile-user-fullname">
                                        {{ Auth::user()->name . ' ' . Auth::user()->surname }}
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end dropdown-menu-s1">
                            <!-- <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                <div class="user-card">
                                    <div class="user-avatar">
                                        <span>AB</span>
                                    </div>
                                    <div class="user-info">
                                        <span class="lead-text" id="card-profile-user-fullname">Name Surname</span>
                                        <span class="sub-text" id="card-profile-user-email">email@test.com</span>
                                    </div>
                                </div>
                            </div> -->
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    {{-- <li><a href="profile.php"><em class="icon ni ni-user-alt"></em><span>View
                                                Profile</span></a></li> --}}
                                    <!-- <li><a href="settings.html"><em class="icon ni ni-setting-alt"></em><span>Account Setting</span></a></li>
                                    <li><a href="settings-activity-log.html"><em class="icon ni ni-activity-alt"></em><span>Login Activity</span></a></li> -->
                                    <li><a class="dark-switch" href="#"><em class="icon ni ni-moon"></em><span>Dark
                                                Mode</span></a></li>
                                </ul>
                            </div>
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li><a href="{{ route('logout') }}"><em class="icon ni ni-signout"></em><span>Sign
                                                out</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </li><!-- .dropdown -->
                    <li class="dropdown notification-dropdown me-n1">
                        <a href="#" class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                            <div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>
                            <span class="badge bg-danger rounded-pill" id="notification-count">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end dropdown-menu-s1">
                            <div class="dropdown-head">
                                <span class="sub-title nk-dropdown-title">Notifications</span>
                            </div>
                            <div class="dropdown-body">
                                <div class="nk-notification" id="notification-dropdown-body">
                                    {{-- <div class="nk-notification-item">
                                        <div class="nk-notification-content">
                                            <div class="nk-notification-text">Test Message</div>
                                            <div class="nk-notification-time">Hello</div>
                                        </div>
                                    </div>
                                    <hr class="py-0 mx-0"> --}}
                                </div><!-- .nk-notification -->
                            </div><!-- .nk-dropdown-body -->
                            <div class="dropdown-foot center">
                                <a href="#" id="load-more-notifications">View More</a>
                            </div>
                        </div>
                    </li>

                    <!-- .dropdown -->
                </ul><!-- .nk-quick-nav -->
            </div><!-- .nk-header-tools -->
        </div><!-- .nk-header-wrap -->
    </div><!-- .container-fliud -->
</div>