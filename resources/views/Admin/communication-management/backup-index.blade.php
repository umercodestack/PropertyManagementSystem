@extends('Admin.layouts.app')
@section('content')
    <style>
        #message_div {
            height: 700px !important;
            overflow-y: scroll !important;
            /* border: 1px solid #000 !important; */
            padding: 0 !important;
        }

        .nk-reply-form {
            position: sticky !important;
            bottom: 0 !important;
            z-index: 5 !important;
            background: white !important;
        }


        .nk-msg-head {
            padding: 1rem 2.5rem !important;
        }

        @media (min-width: 992px) {
            .nk-msg-head {
                padding: 1rem 2.5rem !important;
            }
        }

        @media (min-width: 992px) {
            .nk-msg-head .title {
                margin-bottom: 0px !important
            }
        }
    </style>
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-msg">
                <div class="nk-msg-aside">
                    <div class="nk-msg-nav">
                        <h6 class="pt-3">Threads</h6>
                        {{--                        <ul class="nk-msg-menu"> --}}
                        {{--                            <li class="nk-msg-menu-item active"><a href="">Active</a></li> --}}
                        {{--                            <li class="nk-msg-menu-item"><a href="">Closed</a></li> --}}
                        {{--                            <li class="nk-msg-menu-item"><a href="">Stared</a></li> --}}
                        {{--                            <li class="nk-msg-menu-item"><a href="">All</a></li> --}}
                        {{--                            <li class="nk-msg-menu-item ms-auto"><a href="" class="search-toggle toggle-search" data-target="search"><em class="icon ni ni-search"></em></a></li> --}}
                        {{--                        </ul><!-- .nk-msg-menu --> --}}
                        <div class="search-wrap" data-search="search">
                            <div class="search-content">
                                <a href="#" class="search-back btn btn-icon toggle-search" data-target="search"><em
                                        class="icon ni ni-arrow-left"></em></a>
                                <input type="text" class="form-control border-transparent form-focus-none"
                                    placeholder="Search by user or message">
                                <button class="search-submit btn btn-icon"><em class="icon ni ni-search"></em></button>
                            </div>
                        </div><!-- .search-wrap -->
                    </div><!-- .nk-msg-nav -->
                    <div class="nk-msg-list" data-simplebar="init">
                        <div class="simplebar-wrapper" style="margin: 0px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                        aria-label="scrollable content" style="height: auto; overflow: hidden scroll;">
                                        <div class="simplebar-content" id="thread-content" style="padding: 0px;">
                                            {{--                                            <div class="nk-msg-item current" data-msg-id="1"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar"> --}}
                                            {{--                                                    <span>AB</span> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Abu Bin Ishtiyak</div> --}}
                                            {{--                                                            <div class="lable-tag dot bg-pink"></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="attchment"><em class="icon ni ni-clip-h"></em></div> --}}
                                            {{--                                                            <div class="date">12 Jan</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Unable to select currency when order.</h6> --}}
                                            {{--                                                            <p>Hello team, I am facing problem as i can not select currency on buy order page.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="2"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar"> --}}
                                            {{--                                                    <img src="./images/avatar/b-sm.jpg" alt=""> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Jackelyn Dugas</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">15 Jan</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Have not received bitcoin yet.</h6> --}}
                                            {{--                                                            <p>Hey! I recently bitcoin from you. But still have not received yet.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a class="active" href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item is-unread" data-msg-id="3"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar bg-purple"> --}}
                                            {{--                                                    <span>MJ</span> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Mayme Johnston</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">11 Jan</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">I can not submit kyc application</h6> --}}
                                            {{--                                                            <p>Hello support! I can not upload my documents on kyc application.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="unread"><span class="badge bg-primary">2</span></div> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="133"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar"> --}}
                                            {{--                                                    <img src="./images/avatar/c-sm.jpg" alt=""> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Jake Smityh</div> --}}
                                            {{--                                                            <div class="lable-tag dot bg-pink"></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">30 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Have not received bitcoin yet.</h6> --}}
                                            {{--                                                            <p>Hey! I recently bitcoin from you. But still have not received yet.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="12"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar"> --}}
                                            {{--                                                    <img src="./images/avatar/d-sm.jpg" alt=""> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Amanda Moore</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">28 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Wallet needs to verify.</h6> --}}
                                            {{--                                                            <p>Hello, I already varified my Wallet but it still showing needs to verify alert.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="1"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar bg-blue"> --}}
                                            {{--                                                    <span>RV</span> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Rebecca Valdez</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">26 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">I want my money back.</h6> --}}
                                            {{--                                                            <p>Hey! I don't want to stay as your subscriber any more, Also i want my mony back.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="1"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar bg-orange"> --}}
                                            {{--                                                    <span>CG</span> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Charles Greene</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">21 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Have not received bitcoin yet.</h6> --}}
                                            {{--                                                            <p>Hey! I recently bitcoin from you. But still have not received yet.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="1"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar bg-success"> --}}
                                            {{--                                                    <span>EA</span> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Ethan Anderson</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">16 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Unable to select currency when order.</h6> --}}
                                            {{--                                                            <p>Hello team, I am facing problem as i can not select currency on buy order page.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="1"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar"> --}}
                                            {{--                                                    <img src="./images/avatar/c-sm.jpg" alt=""> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Jose Peterson</div> --}}
                                            {{--                                                            <div class="lable-tag dot bg-pink"></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">14 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Have not received bitcoin yet.</h6> --}}
                                            {{--                                                            <p>Hey! I recently bitcoin from you. But still have not received yet.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="12"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar"> --}}
                                            {{--                                                    <img src="./images/avatar/d-sm.jpg" alt=""> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Amanda Moore</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">12 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">Wallet needs to verify.</h6> --}}
                                            {{--                                                            <p>Hello, I already varified my Wallet but it still showing needs to verify alert.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                            {{--                                            <div class="nk-msg-item" data-msg-id="3"> --}}
                                            {{--                                                <div class="nk-msg-media user-avatar bg-purple"> --}}
                                            {{--                                                    <span>MJ</span> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="nk-msg-info"> --}}
                                            {{--                                                    <div class="nk-msg-from"> --}}
                                            {{--                                                        <div class="nk-msg-sender"> --}}
                                            {{--                                                            <div class="name">Mayme Johnston</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-meta"> --}}
                                            {{--                                                            <div class="date">09 Dec, 2019</div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="nk-msg-context"> --}}
                                            {{--                                                        <div class="nk-msg-text"> --}}
                                            {{--                                                            <h6 class="title">I can not submit kyc application</h6> --}}
                                            {{--                                                            <p>Hello support! I can not upload my documents on kyc application.</p> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                        <div class="nk-msg-lables"> --}}
                                            {{--                                                            <div class="asterisk"><a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a></div> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-msg-item --> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 1401px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar"
                                style="height: 25px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
                        </div>
                    </div><!-- .nk-msg-list -->
                </div><!-- .nk-msg-aside -->
                <div class="nk-msg-body bg-white">
                    <div class="nk-msg-head">
                        <h4 class="title d-none d-lg-block" id="listing_name">Messages</h4>
                        <div class="nk-msg-head-meta">
                            <div class="d-none d-lg-block">
                                {{--                                <ul class="nk-msg-tags"> --}}
                                {{--                                    <li><span class="label-tag"><em class="icon ni ni-flag-fill"></em> <span>Technical Problem</span></span></li> --}}
                                {{--                                </ul> --}}
                            </div>
                            {{--                            <div class="d-lg-none"><a href="#" class="btn btn-icon btn-trigger nk-msg-hide ms-n1"><em class="icon ni ni-arrow-left"></em></a></div> --}}
                            {{--                            <ul class="nk-msg-actions"> --}}
                            {{--                                <li><a href="#" class="btn btn-dim btn-sm btn-outline-light"><em class="icon ni ni-check"></em><span>Mark as Closed</span></a></li> --}}
                            {{--                                <li class="d-lg-none"><a href="#" class="btn btn-icon btn-sm btn-white btn-light profile-toggle"><em class="icon ni ni-info-i"></em></a></li> --}}
                            {{--                                <li class="dropdown"> --}}
                            {{--                                    <a href="#" class="btn btn-icon btn-sm btn-white btn-light dropdown-toggle" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a> --}}
                            {{--                                    <div class="dropdown-menu dropdown-menu-end"> --}}
                            {{--                                        <ul class="link-list-opt no-bdr"> --}}
                            {{--                                            <li><a href="#"><em class="icon ni ni-user-add"></em><span>Assign To Member</span></a></li> --}}
                            {{--                                            <li><a href="#"><em class="icon ni ni-archive"></em><span>Move to Archive</span></a></li> --}}
                            {{--                                            <li><a href="#"><em class="icon ni ni-done"></em><span>Mark as Close</span></a></li> --}}
                            {{--                                        </ul> --}}
                            {{--                                    </div> --}}
                            {{--                                </li> --}}
                            {{--                            </ul> --}}
                        </div>
                        <a href="#" class="nk-msg-profile-toggle profile-toggle"><em
                                class="icon ni ni-arrow-left"></em></a>
                    </div><!-- .nk-msg-head -->
                    <div class="nk-msg-reply nk-reply" data-simplebar="init">
                        <div class="simplebar-wrapper" style="margin: 0px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                        aria-label="scrollable content" style="height: auto; overflow: hidden scroll;">
                                        <div class="simplebar-content" style="padding: 0px;">
                                            <div id="message_div">

                                            </div>
                                            {{--                                            <div class="nk-reply-form"> --}}
                                            {{--                                                <div class="nk-reply-form-header"> --}}
                                            {{--                                                    <ul class="nav nav-tabs-s2 nav-tabs nav-tabs-sm" role="tablist"> --}}
                                            {{--                                                        <li class="nav-item" role="presentation"> --}}
                                            {{--                                                            <a class="nav-link active" data-bs-toggle="tab" href="#reply-form" aria-selected="true" role="tab">Reply</a> --}}
                                            {{--                                                        </li> --}}
                                            {{--                                                        <li class="nav-item" role="presentation"> --}}
                                            {{--                                                            <a class="nav-link" data-bs-toggle="tab" href="#note-form" aria-selected="false" tabindex="-1" role="tab">Private Note</a> --}}
                                            {{--                                                        </li> --}}
                                            {{--                                                    </ul> --}}
                                            {{--                                                    <div class="nk-reply-form-title"> --}}
                                            {{--                                                        <div class="title">Reply as:</div> --}}
                                            {{--                                                        <div class="user-avatar xs bg-purple"> --}}
                                            {{--                                                            <span>IH</span> --}}
                                            {{--                                                        </div> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                                <div class="tab-content"> --}}
                                            {{--                                                    <div class="tab-pane active" id="reply-form" role="tabpanel"> --}}
                                            {{--                                                        <div class="nk-reply-form-editor"> --}}
                                            {{--                                                            <div class="nk-reply-form-field"> --}}
                                            {{--                                                                <textarea class="form-control form-control-simple no-resize" placeholder="Hello"></textarea> --}}
                                            {{--                                                            </div> --}}
                                            {{--                                                            <div class="nk-reply-form-tools"> --}}
                                            {{--                                                                <ul class="nk-reply-form-actions g-1"> --}}
                                            {{--                                                                    <li class="me-2"><button class="btn btn-primary" type="submit">Reply</button></li> --}}
                                            {{--                                                                    <li> --}}
                                            {{--                                                                        <div class="dropdown"> --}}
                                            {{--                                                                            <a class="btn btn-icon btn-sm" data-bs-toggle="dropdown" href="#"><em class="icon ni ni-hash" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Template" data-bs-original-title="Template"></em></a> --}}
                                            {{--                                                                            <div class="dropdown-menu"> --}}
                                            {{--                                                                                <ul class="link-list-opt no-bdr link-list-template"> --}}
                                            {{--                                                                                    <li class="opt-head"><span>Quick Insert</span></li> --}}
                                            {{--                                                                                    <li><a href="#"><span>Thank you message</span></a></li> --}}
                                            {{--                                                                                    <li><a href="#"><span>Your issues solved</span></a></li> --}}
                                            {{--                                                                                    <li><a href="#"><span>Thank you message</span></a></li> --}}
                                            {{--                                                                                    <li class="divider"> --}}
                                            {{--                                                                                    </li><li><a href="#"><em class="icon ni ni-file-plus"></em><span>Save as Template</span></a></li> --}}
                                            {{--                                                                                    <li><a href="#"><em class="icon ni ni-notes-alt"></em><span>Manage Template</span></a></li> --}}
                                            {{--                                                                                </ul> --}}
                                            {{--                                                                            </div> --}}
                                            {{--                                                                        </div> --}}
                                            {{--                                                                    </li> --}}
                                            {{--                                                                    <li> --}}
                                            {{--                                                                        <a class="btn btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" href="#" aria-label="Upload Attachment" data-bs-original-title="Upload Attachment"><em class="icon ni ni-clip-v"></em></a> --}}
                                            {{--                                                                    </li> --}}
                                            {{--                                                                    <li> --}}
                                            {{--                                                                        <a class="btn btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" href="#" aria-label="Insert Emoji" data-bs-original-title="Insert Emoji"><em class="icon ni ni-happy"></em></a> --}}
                                            {{--                                                                    </li> --}}
                                            {{--                                                                    <li> --}}
                                            {{--                                                                        <a class="btn btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" href="#" aria-label="Upload Images" data-bs-original-title="Upload Images"><em class="icon ni ni-img"></em></a> --}}
                                            {{--                                                                    </li> --}}
                                            {{--                                                                </ul> --}}
                                            {{--                                                                <div class="dropdown"> --}}
                                            {{--                                                                    <a href="#" class="dropdown-toggle btn-trigger btn btn-icon me-n2" data-bs-toggle="dropdown"><em class="icon ni ni-more-v"></em></a> --}}
                                            {{--                                                                    <div class="dropdown-menu dropdown-menu-end"> --}}
                                            {{--                                                                        <ul class="link-list-opt no-bdr"> --}}
                                            {{--                                                                            <li><a href="#"><span>Another Option</span></a></li> --}}
                                            {{--                                                                            <li><a href="#"><span>More Option</span></a></li> --}}
                                            {{--                                                                        </ul> --}}
                                            {{--                                                                    </div> --}}
                                            {{--                                                                </div> --}}
                                            {{--                                                            </div><!-- .nk-reply-form-tools --> --}}
                                            {{--                                                        </div><!-- .nk-reply-form-editor --> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                    <div class="tab-pane" id="note-form" role="tabpanel"> --}}
                                            {{--                                                        <div class="nk-reply-form-editor"> --}}
                                            {{--                                                            <div class="nk-reply-form-field"> --}}
                                            {{--                                                                <textarea class="form-control form-control-simple no-resize" placeholder="Type your private note, that only visible to internal team."></textarea> --}}
                                            {{--                                                            </div> --}}
                                            {{--                                                            <div class="nk-reply-form-tools"> --}}
                                            {{--                                                                <ul class="nk-reply-form-actions g-1"> --}}
                                            {{--                                                                    <li class="me-2"><button class="btn btn-primary" type="submit">Add Note</button></li> --}}
                                            {{--                                                                    <li> --}}
                                            {{--                                                                        <a class="btn btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" href="#" aria-label="Upload Attachment" data-bs-original-title="Upload Attachment"><em class="icon ni ni-clip-v"></em></a> --}}
                                            {{--                                                                    </li> --}}
                                            {{--                                                                </ul> --}}
                                            {{--                                                                <div class="dropdown"> --}}
                                            {{--                                                                    <a href="#" class="dropdown-toggle btn-trigger btn btn-icon me-n2" data-bs-toggle="dropdown"><em class="icon ni ni-more-v"></em></a> --}}
                                            {{--                                                                    <div class="dropdown-menu dropdown-menu-end"> --}}
                                            {{--                                                                        <ul class="link-list-opt no-bdr"> --}}
                                            {{--                                                                            <li><a href="#"><span>Another Option</span></a></li> --}}
                                            {{--                                                                            <li><a href="#"><span>More Option</span></a></li> --}}
                                            {{--                                                                        </ul> --}}
                                            {{--                                                                    </div> --}}
                                            {{--                                                                </div> --}}
                                            {{--                                                            </div><!-- .nk-reply-form-tools --> --}}
                                            {{--                                                        </div><!-- .nk-reply-form-editor --> --}}
                                            {{--                                                    </div> --}}
                                            {{--                                                </div> --}}
                                            {{--                                            </div><!-- .nk-reply-form --> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 1374px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar"
                                style="height: 25px; transform: translate3d(0px, -17px, 0px); display: block;"></div>
                        </div>
                    </div><!-- .nk-reply -->
                    <div class="nk-msg-profile" data-simplebar="init">
                        <div class="simplebar-wrapper" style="margin: 0px;">
                            <div class="simplebar-height-auto-observer-wrapper">
                                <div class="simplebar-height-auto-observer"></div>
                            </div>
                            <div class="simplebar-mask">
                                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                    <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                        aria-label="scrollable content" style="height: 100%; overflow: hidden scroll;">
                                        <div class="simplebar-content" style="padding: 0px;">
                                            <div class="card">
                                                <div class="card-inner-group">
                                                    <div class="card-inner">
                                                        <div class="user-card user-card-s2 mb-2">
                                                            <div class="user-avatar md bg-primary">
                                                                <span>AB</span>
                                                            </div>
                                                            <div class="user-info">
                                                                <h5 id="cust_title"></h5>
                                                                <span class="sub-text">Customer</span>
                                                            </div>

                                                            {{--                                                            <div class="user-card-menu dropdown"> --}}
                                                            {{--                                                                <a href="#" class="btn btn-icon btn-sm btn-trigger dropdown-toggle" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a> --}}
                                                            {{--                                                                <div class="dropdown-menu dropdown-menu-end"> --}}
                                                            {{--                                                                    <ul class="link-list-opt no-bdr"> --}}
                                                            {{--                                                                        <li><a href="#"><em class="icon ni ni-eye"></em><span>View Profile</span></a></li> --}}
                                                            {{--                                                                        <li><a href="#"><em class="icon ni ni-na"></em><span>Ban From System</span></a></li> --}}
                                                            {{--                                                                        <li><a href="#"><em class="icon ni ni-repeat"></em><span>View Orders</span></a></li> --}}
                                                            {{--                                                                    </ul> --}}
                                                            {{--                                                                </div> --}}
                                                            {{--                                                            </div> --}}
                                                        </div>
                                                        {{--                                                        <div class="row text-center g-1"> --}}
                                                        {{--                                                            <div class="col-4"> --}}
                                                        {{--                                                                <div class="profile-stats"> --}}
                                                        {{--                                                                    <span class="amount">23</span> --}}
                                                        {{--                                                                    <span class="sub-text">Total Order</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                            <div class="col-4"> --}}
                                                        {{--                                                                <div class="profile-stats"> --}}
                                                        {{--                                                                    <span class="amount">20</span> --}}
                                                        {{--                                                                    <span class="sub-text">Complete</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                            <div class="col-4"> --}}
                                                        {{--                                                                <div class="profile-stats"> --}}
                                                        {{--                                                                    <span class="amount">3</span> --}}
                                                        {{--                                                                    <span class="sub-text">Progress</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                        </div> --}}
                                                    </div><!-- .card-inner -->

                                                    <div class="card-inner">
                                                        <div class="property-details mb-3">
                                                            <h4>Guest Details</h4>
                                                            <span class="mb-3"><strong>Apartment: </strong><span id="apartment_title"></span></span>
                                                            <br>
                                                            <br>
                                                            <span class="mt-5"><strong>Guest Name: </strong><span id="guest_name"></span></span>
                                                            <br>
                                                            <div id="booking_details_div" class="mt-3">
                                                                <h4>Booking Details</h4>
                                                            </div>
                                                        </div>

                                                        <div class="row" id="inquiry_offer_card">
                                                            <div class="col-md-6">
                                                                <button class="btn btn-primary btn-sm" id="approve_btn"
                                                                    data-id = ""
                                                                    onclick="approveOrRejectInquiry(this, 'approve')">Pre-Approve</button>
                                                            </div>
                                                            {{--                                                            <div class="col-md-6"> --}}
                                                            {{--                                                                <button class="btn btn-primary btn-sm">Special Offer</button> --}}
                                                            {{--                                                            </div> --}}
                                                            <div class="col-md-6">
                                                                <button class="btn btn-primary btn-sm" id="special_offer"
                                                                    data-id = ""
                                                                    onclick="openSpecialOfferDiv(this)">Special
                                                                    Offer</button>
                                                            </div>
                                                            <div class="col-md-12" id="special_offer_div">

                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <p id="inquiry_text"
                                                                    style="background: #798bff; border-radius: 5px; padding: 5px; color: white; display: none">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        {{--                                                        <div class="aside-wg"> --}}
                                                        {{--                                                            <h6 class="overline-title-alt mb-2">User Information</h6> --}}
                                                        {{--                                                            <ul class="user-contacts"> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <em class="icon ni ni-mail"></em><span>info@softnio.com</span> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <em class="icon ni ni-call"></em><span>+938392939</span> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <em class="icon ni ni-map-pin"></em><span>1134 Ridder Park Road <br>San Fransisco, CA 94851</span> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                            </ul> --}}
                                                        {{--                                                        </div> --}}
                                                        {{--                                                        <div class="aside-wg"> --}}
                                                        {{--                                                            <h6 class="overline-title-alt mb-2">Additional</h6> --}}
                                                        {{--                                                            <div class="row gx-1 gy-3"> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Ref ID: </span> --}}
                                                        {{--                                                                    <span>TID-049583</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Requested:</span> --}}
                                                        {{--                                                                    <span>Abu Bin Ishtiak</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Status:</span> --}}
                                                        {{--                                                                    <span class="lead-text text-success">Open</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                                <div class="col-6"> --}}
                                                        {{--                                                                    <span class="sub-text">Last Reply:</span> --}}
                                                        {{--                                                                    <span>Abu Bin Ishtiak</span> --}}
                                                        {{--                                                                </div> --}}
                                                        {{--                                                            </div> --}}
                                                        {{--                                                        </div> --}}
                                                        {{--                                                        <div class="aside-wg"> --}}
                                                        {{--                                                            <h6 class="overline-title-alt mb-2">Assigned Account</h6> --}}
                                                        {{--                                                            <ul class="align-center g-2"> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <div class="user-avatar bg-purple"> --}}
                                                        {{--                                                                        <span>IH</span> --}}
                                                        {{--                                                                    </div> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <div class="user-avatar bg-pink"> --}}
                                                        {{--                                                                        <span>ST</span> --}}
                                                        {{--                                                                    </div> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                                <li> --}}
                                                        {{--                                                                    <div class="user-avatar bg-gray"> --}}
                                                        {{--                                                                        <span>SI</span> --}}
                                                        {{--                                                                    </div> --}}
                                                        {{--                                                                </li> --}}
                                                        {{--                                                            </ul> --}}
                                                        {{--                                                        </div> --}}
                                                    </div><!-- .card-inner -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="simplebar-placeholder" style="width: auto; height: 718px;"></div>
                        </div>
                        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                        </div>
                        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                            <div class="simplebar-scrollbar"
                                style="height: 25px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
                        </div>
                    </div><!-- .nk-msg-profile -->
                </div><!-- .nk-msg-body -->
            </div><!-- .nk-msg -->
        </div>
    </div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    window.onload = function() {
        var messageDiv = document.getElementById("message_div");
        messageDiv.scrollTop = messageDiv.scrollHeight;
    };
    let old_threads = [];
    let new_threads = [];



    function openSpecialOfferDiv(button) {
        $('#special_offer_div').html('');
        let dataId = $(button).attr('data-id');
        let html = `<div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                            <label for="">Amount</label>
                            <input type="number" class="form-control" name="special_offer" id="special_offer_input">
                            </div>
                        </div>
                         <div class="col-md-6 ">
                            <button class="btn btn-primary btn-sm mt-4" data-id = "` + dataId + `"  onclick="approveOrRejectInquiry(this, 'special_offer')">Submit</button>
                        </div>
                    </div>
                   `;
        // console.log(html)
        $('#special_offer_div').append(html);
    }

    function approveOrRejectInquiry(button, status) {
        let special_offer_amount = null;

        if (status === 'special_offer') {
            if ($('#special_offer_input').val() === null || $('#special_offer_input').val() === '') {
                alert('Please enter the valid amount of special offer');
                return false
            }
            special_offer_amount = $('#special_offer_input').val();
        }
        let dataId = $(button).attr('data-id');

        $.ajax({
            url: "{{ route('approveOrRejectInquiry') }}",
            type: "POST",
            data: {
                live_feed_event_id: dataId,
                amount: special_offer_amount,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response) {
                    if(status == 'approve') {
                        $('#inquiry_offer_card').css('display', 'none');
                        $('#inquiry_text').css('display', 'block');
                        $('#inquiry_text').text(`Book Now offer has been sent to user`);
                    }else {
                        $('#inquiry_offer_card').css('display', 'none');
                        $('#inquiry_text').css('display', 'block');
                        $('#inquiry_text').text(`The Special Offer has Been sent of price ${special_offer_amount}`);
                    }

                }
            },
            error: function(error) {
                console.error("Error during form submission:", error);
                // Handle errors as needed
            }
        });
    }

    function passJson(obj) {
        return obj;
    }
    function getThread() {
        let thread_content = $('#thread-content');
        thread_content.html('')
        $.ajax({
            url: "{{ route('fetchThreadsAdmin') }}",
            type: "get",
            data: {
                system: 'Admin'
            },
            success: function(response) {

                // console.log(response)
                $('#cust_title').append(response[0].name)
                response.map((item, index) => {
                    old_threads.push(item.id);
                    // old_threads.push(Number(item.id));
                    // console.log(item)
                    let date = item.message_date.split("T")[0]; // "2024-04-21"
                    // console.log(date)


                    // $.ajax({
                    //     url: "",
                    //     type: "get",
                    //     data: {thread_id: item.id},
                    //     success: function (response) {
                    //         $('#last_message_'+item.id).text(response.messages[0].message)
                    //     },
                    //     error: function(jqXHR, textStatus, errorThrown) {
                    //         console.log(textStatus, errorThrown);
                    //     }
                    // });
                    if (index === 0) {
                        let thread_type = "'" + response[0].thread_type + "'"
                        console.log('it',JSON.parse(item.booking_info_json))
                        fetchThreadByID(item.id, response[0].name, response[0].live_feed_event_id,thread_type,encodeURIComponent(JSON.stringify(response[0].booking_info_json)));
                    }
                      let thread_type = "'" + item.thread_type + "'"
                    let title = "'" + item.name + "'"
                    let live_feed_event_id = "'" + item.live_feed_event_id + "'"
                    $('#approve_btn').attr('data-id', response[0].live_feed_event_id);
                    $('#special_offer').attr('data-id', response[0].live_feed_event_id);



                    let newMessage = `
                        <div class="nk-msg-item" data-msg-id="` + index + `" onclick="fetchThreadByID(` + item.id +
                        `, ` + title + `, ` + live_feed_event_id + `,`+ thread_type +`, '`+ encodeURIComponent(JSON.stringify(item.booking_info_json)) +`')">
                            <div class="nk-msg-media user-avatar">
                                <span>` + item.name.charAt(0) + `</span>
                            </div>
                            <div class="nk-msg-info">
                                <div class="nk-msg-from">
                                    <div class="nk-msg-sender">
                                        <div class="name">` + item.name + `</div>
                                    </div>
                                    <div class="nk-msg-meta">
                                        <div class="attchment"><em class="icon ni ni-clip-h"></em></div>
                                        <div class="date">` + date + `</div>
                                    </div>
                                </div>
                                <div class="nk-msg-context">
                                    <div class="nk-msg-text">
                                        <h6 class="title"></h6>
                                        <p>` + item.last_message + `</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        `;
                    $('#thread-content').append(newMessage);
                })
                // if(response) {
                //     // console.log(response)
                //     response.map((item, index) => {
                //         console.log(item)
                //         const option = $('<option></option>')
                //             .attr('value', item.id) // set the value attribute to the item ID
                //             .text(item.title); // set the text content to the item name
                //         $('#listing_id').append(option);
                //     })
                // }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // console.log(textStatus, errorThrown);
            }
        });
    }
    setTimeout(function() {
        getThread();
        console.log(new_threads);
    }, 2000);



    // getNewThreads()
    // console.log(old_threads);
    // console.log(new_threads);
    // let old_threads1 = ['thread1', 'thread2', 'thread3'];
    // let new_threads1 = ['thread2', 'thread3', 'thread4', 'thread5'];
    // function arrayDifference(arr1, arr2) {
    //     const set1 = new Set(arr1);
    //     const set2 = new Set(arr2);
    //     const diff1 = [...set1].filter(x => !set2.has(x));
    //     const diff2 = [...set2].filter(x => !set1.has(x));
    //     return [...diff1, ...diff2];
    // }
    // let differences = arrayDifference(old_threads1, new_threads1);
    // console.log(differences)
    // function checkForNewThread() {
    //
    // }

    function fetchThreadByID(thread_id, title, live_feed_event_id,thread_type, booking_json) {
        console.log('ht',booking_json)
        $('#apartment_title').html('')
        $('#guest_name').html('')
        $('#booking_details_div').html('')
        console.log('asd',booking_json)
                        if(booking_json !== null || booking_json !== '' || booking_json !== 'null') {
                            console.log('booking_json', booking_json)
                            let booking_info = decodeURIComponent(booking_json)
                            let booking_info_detail = JSON.parse(booking_info)
                            let booking_info_details = JSON.parse(booking_info_detail)
                            console.log('tt',booking_info_details);
                            // let booking_info_details = JSON.parse(booking_json);
                            $('#apartment_title').text(booking_info_details?.listing_name)
                            $('#guest_name').text(booking_info_details?.name)
                            $('#booking_details_div').html(`
                            <span class="mb-3"><strong>Checkin Date: </strong><span>`+booking_info_details?.checkin_date+`</span></span> <br>
                            <span class="mb-3"><strong>Checkout Date: </strong><span id="apartment_title">`+booking_info_details?.checkout_date+`</span></span><br>
                            <span class="mb-3"><strong>Nights: </strong><span id="apartment_title">`+booking_info_details?.nights+`</span></span><br>
                            <span class="mb-3"><strong>Expected Amount: </strong><span id="apartment_title">`+booking_info_details?.expected_payout_amount_accurate+` `+booking_info_details?.currency+`</span></span>
                            <span class="mb-3"><strong>No of Adults: </strong><span id="apartment_title">`+booking_info_details?.number_of_adults+`</span></span><br>
                            <span class="mb-3"><strong>No of Children: </strong><span id="apartment_title">`+booking_info_details?.number_of_children+`</span></span><br>
                            <span class="mb-3"><strong>No of Guest: </strong><span id="apartment_title">`+booking_info_details?.number_of_guests+`</span></span><br>
                            <span class="mb-3"><strong>No of Infants: </strong><span id="apartment_title">`+booking_info_details?.number_of_infants+`</span></span><br>
                            <span class="mb-3"><strong>No of Pets: </strong><span id="apartment_title">`+booking_info_details?.number_of_pets+`</span></span><br>
                            `);
                     }


        $('#message_div').html('')
        $('#cust_title').html('')
        $('#cust_title').append(title)
        $('#approve_btn').attr('data-id', live_feed_event_id);
        $('#special_offer').attr('data-id', live_feed_event_id);
        $('#inquiry_text').css('display', 'none');
        if(thread_type=='inquiry') {
            $('#inquiry_offer_card').css('display', 'flex');
        }
        else {
            $('#inquiry_offer_card').css('display', 'none');
        }

        // checkForBookingInquiryDetails
        let checkForBookingInquiryDetailsUrl = window.location.origin + '/checkForBookingInquiryDetails/' +
            live_feed_event_id;
        $.ajax({
            url: checkForBookingInquiryDetailsUrl,
            type: "get",
            success: function(response) {
                // response.messages.map((item, index) => {
                // console.log(response.booking_details)
                if (response.booking_details) {
                    let total_price = Number(response.total_price) / 100

                    $('#inquiry_offer_card').css('display', 'none');
                    $('#inquiry_text').css('display', 'block');
                    $('#inquiry_text').text(`The Special Offer has Been sent of price ${total_price}`);
                }
                // inquiry_offer_card
                // inquiry_text

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

        // console.log(window.location.origin)
        let url = window.location.origin + '/fetchThreadByIDAdmin/' + thread_id;
        $.ajax({
            url: url,
            type: "get",
            data: {
                thread_id: thread_id
            },
            success: function(response) {
                // response.messages.map((item, index) => {
                // console.log(response)
                // const sortedMessages = response.message_content.sort((a, b) => new Date(a.updated_at) - new Date(b.updated_at));
                console.log('check', response)
                response.map((item, index) => {
                    const date = new Date(item.created_at);
                    const formattedDate = date.toLocaleDateString();

                    let messages = `
                    <div class="nk-reply-item">
                        <div class="nk-reply-header">
                            <div class="user-card">
                                <div class="user-avatar sm bg-blue">
                                    <span>LIn</span>
                                </div>
                                <div class="user-name">` + item.sender + `</div>
                            </div>
                            <div class="date-time">` + formattedDate + `</div>
                        </div>
                        <div class="nk-reply-body">
                            <div class="nk-reply-entry entry">
                                <p>` + item.message_content + `</p>
                            </div>
                        </div>
                    </div>`;
                    $('#message_div').append(messages)
                });

                let messageReply = `<div class="nk-reply-form">
                        <div class="nk-reply-form-header">
                            <ul class="nav nav-tabs-s2 nav-tabs nav-tabs-sm" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#reply-form" aria-selected="true" role="tab">Reply</a>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="reply-form" role="tabpanel">
                            <form>
                                <div class="nk-reply-form-editor">
                                    <div class="nk-reply-form-field">
                                        <textarea class="form-control form-control-simple no-resize" id="message" placeholder="Type Your Message here....." required></textarea>
                                    </div>
                                    <div class="nk-reply-form-tools">

                                        <ul class="nk-reply-form-actions g-1">
                                            <li>
                                                 <label for="upload-attachment" class="btn btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Upload Attachment" data-bs-original-title="Upload Attachment">
                                                   <em class="icon ni ni-clip-v"></em>
                                                 </label>
                                                 <input type="file" id="upload-attachment" name="image" accept="image/jpeg" style="display: none;">
                                            </li>
                                            <input type="hidden" name="thread_id" id="thread_id" value="` + thread_id +
                    `">
                                            <li class="me-2">
                                                <button class="btn btn-primary" id="resplySubmit" onclick="submitReply(` +
                    thread_id + `)" type="button">Reply</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>`
                $(document).ready(function() {
                    var ul = $('#message_div');
                    ul.scrollTop(ul.prop("scrollHeight"));
                });
                $('#message_div').append(messageReply)

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }

    function submitReply(thread_id) {

        let message = $('#message').val();
        // let thread_id = $('#thread_id').val();
        if (message === '' || message === null) {
            alert('Message can not be null')
        } else {
            $.ajax({
                url: "{{ route('sendMessageAdmin') }}",
                type: "POST",
                data: {
                    message: message,
                    thread_id: thread_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $("#message").val('');
                    if (response) {
                        let messages = `
                    <div class="nk-reply-item">
                        <div class="nk-reply-header">
                            <div class="user-card">
                                <div class="user-avatar sm bg-blue">
                                    <span>AB</span>
                                </div>
                                <div class="user-name">property</div>
                            </div>
                            <div class="date-time">05 Jul, 2024</div>
                        </div>
                        <div class="nk-reply-body">
                            <div class="nk-reply-entry entry">
                                <p>` + message + `</p>
                            </div>

                        </div>
                    </div>`;
                        let parentDiv = $('#message_div');
                        parentDiv.prepend(messages);
                        $('.nk-reply-form').before(messages);
                    }
                },
                error: function(error) {
                    console.error("Error during form submission:", error);
                    // Handle errors as needed
                }
            });
        }
    }

    function getNewThreads() {
        $.ajax({
            url: "{{ route('fetchThreadsAdmin') }}",
            type: "get",
            data: {
                system: 'Admin'
            },
            success: function(response) {
                new_threads = []
                // console.log(response)
                response.map((item, index) => {
                    if (!old_threads.includes(item.id)) {

                        let date = item.message_date.split("T")[0]; // "2024-04-21"

                        let title = "'" + item.name + "'"
                        let live_feed_event_id = "'" + item.live_feed_event_id + "'"
                        old_threads.push(item.id)
                        let newMessage = `
                        <div class="nk-msg-item" data-msg-id="` + index + `" onclick="fetchThreadByID(` + item.id +
                            `, ` + title + `, ` + live_feed_event_id + `)">
                            <div class="nk-msg-media user-avatar">
                                <span>` + item.name.charAt(0) + `</span>
                            </div>
                            <div class="nk-msg-info">
                                <div class="nk-msg-from">
                                    <div class="nk-msg-sender">
                                        <div class="name">` + item.name + `</div>
                                    </div>
                                    <div class="nk-msg-meta">
                                        <div class="date">` + date + `</div>
                                    </div>
                                </div>
                                <div class="nk-msg-context">
                                    <div class="nk-msg-text">
                                        <h6 class="title"></h6>
                                        <p>` + item.last_message + `</p>
                                    </div>
                                    <div class="nk-msg-lables">
                                        <div class="asterisk">
                                            <a href="#"><em class="asterisk-off icon ni ni-star"></em><em class="asterisk-on icon ni ni-star-fill"></em></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                        $('#thread-content').prepend(newMessage);
                    }
                    new_threads.push(item.id)
                })
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // console.log(textStatus, errorThrown);
            }
        });
    }



    setInterval(
        function() {
            getNewThreads()
            console.log(new_threads)
        },
        3000
    );
</script>
