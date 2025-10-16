@extends('Admin.authentication.layouts.app')
@section('content')
    <div class="nk-split nk-split-page nk-split-lg">
        <div class="nk-split-content nk-block-area nk-block-area-column nk-auth-container bg-white">
            <div class="absolute-top-right d-lg-none p-3 p-sm-5">
                <a href="#" class="toggle btn-white btn btn-icon btn-light" data-target="athPromo"><em
                        class="icon ni ni-info"></em></a>
            </div>
            <div class="nk-block nk-block-middle nk-auth-body">
                <div class="brand-logo pb-5">
                    <a href="{{ route('login') }}" class="logo-link">
                        <img class="logo-light logo-img logo-img-lg" {{ asset('src="assets/images/logo.png') }}"
                            srcset="{{ asset('assets/images/logo.png') }}" alt="logo">
                        <img class="logo-dark logo-img logo-img-lg" src="{{ asset('assets/images/logo-dark') }}"
                            srcset="{{ asset('assets/images/logo.png') }}" alt="logo-dark">
                    </a>
                </div>
                <div class="nk-block-head">
                    <div class="nk-block-head-content">
                        <h5 class="nk-block-title">Password Reset</h5>

                    </div>
                </div><!-- .nk-block-head -->
                <form action="{{ route('forgetPassword') }}" class="form-validate is-alter" method="POST">
                    @csrf
                    <div class="form-group">
                        <div class="form-label-group">
                            <label class="form-label" for="password">Password</label>
                        </div>
                        <div class="form-control-wrap">
                            <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg"
                                data-target="password">
                                <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                            </a>
                            <input type="password" class="form-control form-control-lg" id="password" name="password"
                                placeholder="Enter your passcode">
                            @error('password')
                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                            @enderror
                        </div>
                    </div><!-- .form-group -->
                    <div class="form-group">
                        <div class="form-label-group">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                        </div>
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <div class="form-control-wrap">
                            <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg"
                                data-target="password_confirmation">
                                <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                            </a>
                            <input type="password" class="form-control form-control-lg" id="password_confirmation"
                                name="password_confirmation" placeholder="Confirm Password">
                            @error('password_confirmation')
                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                            @enderror
                        </div>
                    </div><!-- .form-group -->
                    <div class="form-group">
                        <button class="btn btn-lg btn-primary btn-block">Submit</button>
                    </div>
                </form><!-- form -->
                <div class="form-note-s2 pt-4">
                </div>
                {{--            <div class="text-center pt-4 pb-3"> --}}
                {{--                <h6 class="overline-title overline-title-sap"><span>OR</span></h6> --}}
                {{--            </div> --}}
                {{--            <ul class="nav justify-center gx-4"> --}}
                {{--                <li class="nav-item"><a class="link link-primary fw-normal py-2 px-3" href="#">Facebook</a></li> --}}
                {{--                <li class="nav-item"><a class="link link-primary fw-normal py-2 px-3" href="#">Google</a></li> --}}
                {{--            </ul> --}}
                {{--            <div class="text-center mt-5"> --}}
                {{--                <span class="fw-500">I don't have an account? <a href="#">Try 15 days free</a></span> --}}
                {{--            </div> --}}
            </div><!-- .nk-block -->
            <div class="nk-block nk-auth-footer">
                <div class="nk-block-between">
                    <ul class="nav nav-sm">
                        <li class="nav-item">
                            <a class="link link-primary fw-normal py-2 px-3" href="#">Terms & Condition</a>
                        </li>
                        <li class="nav-item">
                            <a class="link link-primary fw-normal py-2 px-3" href="#">Privacy Policy</a>
                        </li>
                        <li class="nav-item">
                            <a class="link link-primary fw-normal py-2 px-3" href="#">Help</a>
                        </li>
                        <li class="nav-item dropup">
                            <a class="dropdown-toggle dropdown-indicator has-indicator link link-primary fw-normal py-2 px-3"
                                data-bs-toggle="dropdown" data-offset="0,10"><small>English</small></a>
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                <ul class="language-list">
                                    <li>
                                        <a href="#" class="language-item">
                                            <img src="{{ asset('assets/images/flags/english.png') }}" alt=""
                                                class="language-flag">
                                            <span class="language-name">English</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="language-item">
                                            <img src="{{ asset('assets/images/flags/spanish.png') }}" alt=""
                                                class="language-flag">
                                            <span class="language-name">Español</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="language-item">
                                            <img src="{{ asset('assets/images/flags/french.png') }}" alt=""
                                                class="language-flag">
                                            <span class="language-name">Français</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="language-item">
                                            <img src="{{ asset('assets/images/flags/turkey.png') }}" alt=""
                                                class="language-flag">
                                            <span class="language-name">Türkçe</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul><!-- .nav -->
                </div>
                <div class="mt-3">
                    <p>&copy; 2024 LivedIn. All Rights Reserved.</p>
                </div>
            </div><!-- .nk-block -->
        </div><!-- .nk-split-content -->
        <div class="nk-split-content nk-split-stretch bg-lighter d-flex toggle-break-lg toggle-slide toggle-slide-right"
            data-toggle-body="true" data-content="athPromo" data-toggle-screen="lg" data-toggle-overlay="true">
            <div class="slider-wrap w-100 w-max-550px p-3 p-sm-5 m-auto">
                <div class="slider-init" data-slick='{"dots":true, "arrows":false}'>
                    <div class="slider-item">
                        <div class="nk-feature nk-feature-center">
                            <div class="nk-feature-img">
                                <img class="round" src="{{ asset('assets/images/slides/promo-a.png') }}"
                                    srcset="{{ asset('assets/images/slides/promo-a2x.png') }} 2x" alt="">
                            </div>
                            <div class="nk-feature-content py-4 p-sm-5">
                                <h4>Livedin</h4>
                                <p>You can start to create your products easily with its user-friendly design & most
                                    completed responsive layout.</p>
                            </div>
                        </div>
                    </div><!-- .slider-item -->
                    <div class="slider-item">
                        <div class="nk-feature nk-feature-center">
                            <div class="nk-feature-img">
                                <img class="round" src="{{ asset('assets/images/slides/promo-b.png') }}"
                                    srcset="{{ asset('assets/images/slides/promo-b2x.png') }} 2x" alt="">
                            </div>
                            <div class="nk-feature-content py-4 p-sm-5">
                                <h4>Livedin</h4>
                                <p>You can start to create your products easily with its user-friendly design & most
                                    completed responsive layout.</p>
                            </div>
                        </div>
                    </div><!-- .slider-item -->
                    <div class="slider-item">
                        <div class="nk-feature nk-feature-center">
                            <div class="nk-feature-img">
                                <img class="round" src="{{ asset('assets/images/slides/promo-c.png') }}"
                                    srcset="{{ asset('assets/images/slides/promo-c2x.png') }} 2x" alt="">
                            </div>
                            <div class="nk-feature-content py-4 p-sm-5">
                                <h4>LivedIn</h4>
                                <p>You can start to create your products easily with its user-friendly design & most
                                    completed responsive layout.</p>
                            </div>
                        </div>
                    </div><!-- .slider-item -->
                </div><!-- .slider-init -->
                <div class="slider-dots"></div>
                <div class="slider-arrows"></div>
            </div><!-- .slider-wrap -->
        </div><!-- .nk-split-content -->
    </div><!-- .nk-split -->
@endsection