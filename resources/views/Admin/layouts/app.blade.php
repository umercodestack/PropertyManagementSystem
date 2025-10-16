<!DOCTYPE html>
<html lang="zxx" class="js">

<head>
    <base href="../../">
    <meta charset="utf-8">
    <meta name="author" content="Abdulla Ismayilov">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description"
        content="A powerful and conceptual apps base dashboard template that especially build for developers and programmers.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    <!-- Page Title  -->
    <title>Livedin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- StyleSheets  -->
    <link rel="stylesheet" href="{{ asset('assets/assets/css/dashlite.css?ver=3.2.3') }}">
    <link id="skin-default" rel="stylesheet" href="{{ asset('assets/assets/css/select2.min.css') }}">
    <link id="skin-default" rel="stylesheet" href="{{ asset('assets/assets/css/theme.css?ver=3.2.3') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/assets/css/libs/fontawesome-icons.css') }}">
     
    
    <!--Chat CSS-->
    <link rel="stylesheet" href="{{asset('assets/assets/css/chat.css')}}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css">

    <style>

.nk-notification-title {
    font-size: 15px;
    color: #2e7d32; /* Deep green */
}

.nk-notification-item {
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 6px;
    transition: background-color 0.3s, opacity 0.3s;
    position: relative;
}

.nk-notification-item.unseen {
    background-color: #e8f5e9; /* Light green for new/unseen */
    cursor: pointer;
}

.nk-notification-item.seen {
    background-color: #f3f4f6; /* Subtle grey for seen */
    opacity: 0.85;
}

.nk-notification-status-icon {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 16px;
    color: #888;
}

.nk-notification-item.unseen .nk-notification-status-icon {
    color: #43a047; /* green for unseen */
}

.nk-notification-item.seen .nk-notification-status-icon {
    color: #9e9e9e; /* grey for seen */
}

    
    /* Image Box Fix */
    .image-box {
        width: 180px; /* Fix width */
        height: 180px; /* Fix height */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden; /* Prevent stretching */
        border-radius: 10px; /* Optional: Rounded corners */
        background: #f0f0f0; /* Optional: Background color */
    }

    .fixed-image {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Maintain aspect ratio and cover box */
        border-radius: 10px;
    }

    .swiper {
        padding: 25px; /* Add space around slider */
    }
    
        .select2-container--default .select2-selection--single {
            height: 35px !important;
        }

        .select2-search.select2-search--inline {
            display: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: unset;
        }
           
.vacant_wrapper{
  width: 100%;
  height: 190px!important;
overflow-y: auto;
}


/* Custom Scrollbar */
.vacant_wrapper::-webkit-scrollbar {
  width: 5px; /* Adjust scrollbar width */
}

.vacant_wrapper::-webkit-scrollbar-track {
  background: #f1f1f1; /* Track background */
}

.vacant_wrapper::-webkit-scrollbar-thumb {
  background-color: #203247; /* Scrollbar color */
  border-radius: 6px;  /* Round scrollbar edges */
}

.vacant_wrapper::-webkit-scrollbar-thumb:hover {
  background-color: #1b2c3e; /* Darker shade on hover */
}

.cleaning_wrapper {
  width: 100%;
  height: 190px;
  overflow-x: auto;  
  overflow-y: auto; 
}

.cleaning_wrapper::-webkit-scrollbar {
  height: 5px;  
  width: 5px;   
}

.cleaning_wrapper::-webkit-scrollbar-track {
  background: #f1f1f1;  
}

.cleaning_wrapper::-webkit-scrollbar-thumb {
  background-color: #203247; 
  border-radius: 6px;  
}

.cleaning_wrapper::-webkit-scrollbar-thumb:hover {
  background-color: #1b2c3e;  
}
    
    </style>
    @if(request()->is('admin/*'))
    <link rel="preload" as="style" href="https://admin.livedin.co/public/build/assets/app-BByOe2x2.css" /><link rel="preload" as="style" href="https://admin.livedin.co/public/build/assets/app-CZ5hoL2l.css" /><link rel="modulepreload" href="https://admin.livedin.co/public/build/assets/app-BT4wwogc.js" /><link rel="stylesheet" href="https://admin.livedin.co/public/build/assets/app-BByOe2x2.css" /><link rel="stylesheet" href="https://admin.livedin.co/public/build/assets/app-CZ5hoL2l.css" /><script type="module" src="https://admin.livedin.co/public/build/assets/app-BT4wwogc.js"></script>
    @endif
    
    
    
</head>


<body class="nk-body bg-lighter npc-general has-sidebar ">
    <div class="nk-app-root">
        <!-- main @s -->
        <div id="vue-app" class="nk-main">
            <!-- sidebar @s -->
            @include('Admin.layouts.side-bar')
            <!-- sidebar @e -->
            <!-- wrap @s -->
            <div class="nk-wrap ">
                <!-- main header @s -->
                @include('Admin.layouts.header')
                <div class="nk-content" style="margin-top: 0">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
                <!-- footer @s -->
                @include('Admin.layouts.footer')
                <!-- footer @e -->
            </div>
            <!-- wrap @e -->
        </div>
        <!-- main @e -->
    </div>
    {{--
    <div class="js-preloader">
        <div class="loading-animation tri-ring"></div>
    </div> --}}
    <script src="{{ asset('assets/js/bundle.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/assets/js/charts/chart-hotel.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/assets/js/select2.min.js') }}"></script>

    <script src="{{ asset('assets/assets/js/main.js') }}"></script>

    <script src="{{ asset('assets/assets/js/libs/datatable-btns.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/assets/js/apps/messages.js?ver=3.2.3') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.full.min.js"></script>
<script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('.select2').select2();

            $('.datetimepic').datetimepicker({
        format: 'Y-m-d H:i', // Example: 12/10/2024 03:15 PM
        step: 15, // Interval of 15 minutes
        datepicker: true, // Enable date picker
        timepicker: true, // Enable time picker
        allowInput: false // Allow clearing manually
    });
        });
        
        
           $(document).ready(function () {
            $("#country").addClass('select2')

            $("#state").addClass('select2')
            $("#city").addClass('select2')
        })

        $(document).ready(function() {
            let page = 1; 

            function formatDate(dateString) {
                const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', options);
            }

            // function loadNotifications() {
            //     $.ajax({
            //         url: '/notifications/?page=' + page,
            //         method: 'GET',
            //         success: function(data) {
            //             console.log(data);
            //             let adminNotifications = [];
            //             $.ajax({
            //                 url: '/admin-notifications',
            //                 method: 'GET',
            //                 success: function(response) {
            //                     adminNotifications = response;
            //                     adminNotifications.forEach(function(notification) {

            //                         let message = '';
            //                         $('#notification-dropdown-body').append(`
            //                             <div class="nk-notification-item">
            //                                 <div class="nk-notification-icon">
            //                                     <em class="fas fa-solid fa-message"></em>
            //                                     </div>
            //                                 <div class="nk-notification-content">
            //                                     <div class="nk-notification-text">${notification?.message}</div>
            //                                     <div class="nk-notification-time">${formatDate(notification?.created_at)}</div>
            //                                 </div>
            //                             </div>
            //                             <hr class="py-0 mx-0">
            //                         `);
            //                     });

                                
            //             const notifications = data.data;
                        
            //             notifications.forEach(function(notification) {
            //                 const detail = JSON.parse(notification.notification_detail);

            //                 let message = '';

            //                 if (detail.event === 'booking_new') {
            //                     message = 'You have a new booking for property ID ' + detail.property_id;
            //                 }

            //                 $('#notification-dropdown-body').append(`
            //                     <div class="nk-notification-item">
            //                         <div class="nk-notification-icon"><em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em></div>
            //                         <div class="nk-notification-content">
            //                             <div class="nk-notification-text">${notification?.message}</div>
            //                             <div class="nk-notification-time">${formatDate(detail?.timestamp)}</div>
            //                         </div>
            //                     </div>
            //                     <hr class="py-0 mx-0">
            //                 `);
            //             });

            //             if (data?.links?.next) {
            //                 page++;
            //             } else {
            //                 $('#load-more-notifications').hide(); // Hide "View More" if no more pages
            //             }
            //                 }
            //             });


            //         },
            //         error: function(xhr, status, error) {
            //             console.error('Error fetching notifications:', error);
            //         }
            //     });
            // }

            // function updateNotificationCount() {
            //     $.ajax({
            //         url: '/notifications/count', // Your API route
            //         method: 'GET',
            //         success: function(data) {
            //             //Update the notification counter div with the new count
            //             //console.log(data.length);
            //             $('#notification-count').text(data.count);
            //             //$('#notification-count').text(data.length);
            //         },
            //         error: function(xhr, status, error) {
            //             console.error('Error fetching notification count:', error);
            //         }
            //     });
            // }


         
 function loadNotifications(reset = false) {
    if (reset) {
        page = 1;
        console.log("Resetting notification dropdown");
        $('#notification-dropdown-body').empty();
        $('#load-more-notifications').show();
    }

    $.ajax({
        url: '/notifications/response?page=' + page,
        method: 'GET',
        success: function (data) {
            let adminNotifications = [];

            // Fetch Admin Notifications First
            $.ajax({
                url: '/admin-notifications',
                method: 'GET',
                success: function (response) {
                    adminNotifications = response;

                    console.log(adminNotifications);
                    // Append Admin Notifications
                    adminNotifications.forEach(function (notification) {
                        $('#notification-dropdown-body').append(`
                            <div class="nk-notification-item seen">
                                <div class="nk-notification-icon">
                                    <em class="fas fa-message"></em>
                                </div>
                                <div class="nk-notification-content">
                                    <div class="nk-notification-title font-weight-bold mb-1">
                                        ${notification?.title || 'Admin'}
                                    </div>
                                    <div class="nk-notification-text">${notification?.message}</div>
                                    <div class="nk-notification-time text-muted">${formatDate(notification?.created_at)}</div>
                                </div>
                            </div>
                            <hr class="py-0 mx-0">
                        `);
                    });

                    // Now load user notifications
                    const notifications = data.data;
                    const csrfToken = $('meta[name="csrf-token"]').attr('content');

                    notifications.forEach(function (notification) {
                        const seenClass = notification.is_seen_by_all ? 'seen' : 'unseen';
                        const item = $(`
                            <div class="nk-notification-item ${seenClass}" data-id="${notification.id}">
    <div class="nk-notification-icon">
        <em class="fas fa-bell"></em>
    </div>
    <div class="nk-notification-content">
        <div class="nk-notification-title font-weight-bold mb-1">
            ${notification.title || 'Untitled'}
        </div>
        <div class="nk-notification-text">
            <a href="${notification.url}" class="notif-link" target="_blank">${notification.message}</a>

            ${notification.source === 'BookingCom' && !notification.status ? `
    <div class="mt-2 bookingcom-actions">
        <button class="btn btn-sm btn-success mr-2 btn-update-booking-status" 
            data-booking-id="${notification.module_id}" 
            data-status="confirmed">
            Payment Received 
        </button>
        <button class="btn btn-sm btn-danger btn-update-booking-status" 
            data-booking-id="${notification.module_id}" 
            data-status="cancelled">
            Cancel Booking
        </button>
    </div>
` : notification.source === 'BookingCom' && notification.status ? `
    <div class="mt-2">
        <span class="badge badge-pill ${notification.status === 'confirmed' ? 'badge-success' : 'badge-danger'}">
            ${notification.status === 'confirmed' ? 'Payment Received' : 'Booking Cancelled'}
        </span>
    </div>
` : ''}
        </div>
        <div class="nk-notification-time text-muted">${formatDate(notification.created_at)}</div>
    </div>
    <div class="nk-notification-status-icon" title="Mark as seen" style="cursor:pointer;">
        <i class="fas ${notification.is_seen_by_all ? 'fa-eye' : 'fa-eye-slash'}"></i>
    </div>
</div>
<hr class="py-0 mx-0">

                        `);

                        function markAsSeen() {
                            const notifId = item.data('id');
                            $.post({
                                url: `/notifications/${notifId}/seen`,
                                headers: { 'X-CSRF-TOKEN': csrfToken },
                                success: function (res) {
                                    if (res.success) {
                                        item.removeClass('unseen').addClass('seen');
                                        item.find('.nk-notification-status-icon i').removeClass('fa-eye-slash').addClass('fa-eye');
                                    }
                                }
                            });
                        }

                        // Trigger markAsSeen on double click
                        item.on('dblclick', markAsSeen);

                        // Trigger on eye icon click
                        item.find('.nk-notification-status-icon').on('click', function (e) {
                            e.preventDefault();
                            markAsSeen();
                        });

                        // Trigger on message link click
                        item.find('.notif-link').on('click', function () {
                            markAsSeen();
                        });

                        $('#notification-dropdown-body').append(item);
                    });

                    
                    if (data?.links?.next) {
                        page++;
                        $('#load-more-notifications').show();
                    } else {
                        $('#load-more-notifications').hide();
                    }
                },
                error: function (err) {
                    console.error('Error loading admin notifications:', err);
                }
            });
        },
        error: function (xhr, status, error) {
            console.error('Error fetching notifications:', error);
        }
    });
}


$(document).on('click', '.btn-update-booking-status', function () {
    const bookingId = $(this).data('booking-id');
    //alert(bookingId);
    const status = $(this).data('status');

    let confirmMessage = status === 'confirmed' 
        ? 'Are you sure you want to mark this booking as payment received?' 
        : 'Are you sure you want to cancel this booking?';

    if (confirm(confirmMessage)) {
        $.ajax({
            url: `/notifications/${bookingId}/update-status`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { status },
            success: function (response) {
                alert('notification status updated successfully.');
                // loadNotifications(false);
            },
            error: function (err) {
                console.error(err);
                alert('Failed to update booking status.');
            }
        });
    }
});







            function updateNotificationCount() {
                $.ajax({
                    url: '/notifications/counts', // Your API route
                    method: 'GET',
                    success: function(data) {
                        //Update the notification counter div with the new count
                        //console.log(data.length);
                        //$('#notification-count').text(data.count);
                        $('#notification-count').text(data.length);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching notification count:', error);
                    }
                });
            }


            updateNotificationCount();
            setInterval(updateNotificationCount, 20000);
            

            $('.notification-dropdown').on('show.bs.dropdown', function() {
                // $('#notification-dropdown-body').empty(); 
                page = 1; 
                loadNotifications(true); 
            });

            $('#load-more-notifications').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); 
                loadNotifications(true);
            });

            $('#load-more-notifications').on('click', function(e) {
                e.preventDefault();
            });

        });



        document.addEventListener('DOMContentLoaded', function() {
            console.log("WINDOW ECHO BLADE", window.Echo)
            if (window.Echo) {
                // Echo.private('test-channel')
                //     .listen('TestEvent', (e) => {
                //         alert(e.message);
                //     });
                window.Echo.private('test-channel.' + 43)
                    .listen('TestEvent', (e) => {
                        alert(200)
                        console.log(e.message);
                    });


            }
        });
        // window.Echo.channel('test-channel')
        //     .listen('TestEvent', (e) => {
        //         alert(e.message)
        //     });
    </script>
    
    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('.select2').select2();
        });
        
        document.addEventListener("DOMContentLoaded", function() {
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 4, // Desktop: 4 images
            spaceBetween: 5,  // Less spacing
            loop: true,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                640: { slidesPerView: 2 }, // Mobile: 2 images
                768: { slidesPerView: 3 }, // Tablet: 3 images
                1024: { slidesPerView: 4 } // Desktop: 4 images
            }
        });

        // Initialize Fancybox
        Fancybox.bind("[data-fancybox]", {
            Toolbar: true, 
            Thumbs: false 
        });
    });
    </script>
    
    
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Fancybox JS -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

<script>
        $(document).ready(function (e) {
            $.ajax({
                url: '/api/countries',
                method: 'GET',
                success: function (data) {
                    $('#country').empty().append("<option value=''>Select Country</option>")
                    data.forEach(country => {
                        $('#country').append(
                            `<option value='${country.id}'>${country.name}</option>`)
                    })
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching notification count:', error);
                }
            });

        })

        $("#country").on('change', function (e) {

            // alert("loading", e.target.value)
            const stateId = e.target.value

            $.ajax({
                url: `/api/cities?country_id=${stateId}`,
                method: 'GET',
                success: function (data) {
                    $('#city').empty().append("<option value=''>Select State</option>")
                    data.forEach(state => {
                        $('#city').append(
                            `<option value='${state.id}'>${state.name}</option>`)
                    })
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching notification count:', error);
                }
            });
        });

        // $("#state").on('change', function(e) {
        //     // alert("loading", e.target.value)
        //     const stateId = e.target.value
        //     $.ajax({
        //         url: `/api/getCities?state_id=${stateId}`,
        //         method: 'GET',
        //         success: function(data) {
        //             $('#city').empty().append("<option value=''>Select City</option>")
        //             data.forEach(country => {
        //                 $('#city').append(
        //                     `<option value='${country.id}'>${country.name}</option>`)
        //             })
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Error fetching notification count:', error);
        //         }
        //     });
        // });


        
    </script>




</body>

</html>
