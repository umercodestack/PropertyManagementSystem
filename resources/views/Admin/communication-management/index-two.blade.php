@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-chat">
                <div class="nk-chat-aside">
                    <div class="nk-chat-aside-head">
                        <div class="nk-chat-aside-user">
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle dropdown-indicator" data-bs-toggle="dropdown">
                                    <div class="title">Chats</div>
                                </a>
                            </div>
                        </div><!-- .nk-chat-aside-user -->

                    </div><!-- .nk-chat-aside-head -->
                    <div class="nk-chat-aside-body" data-simplebar>
                        <div class="nk-chat-aside-search">
                            <div class="form-group">
                                <div class="form-control-wrap">
                                    <div class="form-icon form-icon-left">
                                        <em class="icon ni ni-search"></em>
                                    </div>
                                    <input type="text" class="form-control form-round" id="default-03"
                                        placeholder="Search by name">
                                </div>
                            </div>
                        </div><!-- .nk-chat-aside-search -->

                        <div class="nk-chat-list">
                            <h6 class="title overline-title-alt">Messages</h6>
                            <ul class="chat-list" id= 'chat-list'>

                            </ul><!-- .chat-list -->
                        </div><!-- .nk-chat-list -->
                    </div>
                </div><!-- .nk-chat-aside -->
                <div class="nk-chat-body profile-shown">
                    <div class="nk-chat-head">
                        <ul class="nk-chat-head-info">
                            <li class="nk-chat-body-close">
                                <a href="#" class="btn btn-icon btn-trigger nk-chat-hide ms-n1"><em
                                        class="icon ni ni-arrow-left"></em></a>
                            </li>
                            <li class="nk-chat-head-user">
                                <div class="user-card">
                                    <div class="user-avatar bg-purple">
                                        <span id="header_short_name"></span>
                                    </div>
                                    <div class="user-info">
                                        <div class="lead-text" id="header_name"></div>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <div class="nk-chat-head-search">
                            <div class="form-group">
                                <div class="form-control-wrap">
                                    <div class="form-icon form-icon-left">
                                        <em class="icon ni ni-search"></em>
                                    </div>
                                    <input type="text" class="form-control form-round" id="chat-search"
                                        placeholder="Search in Conversation">
                                </div>
                            </div>
                        </div><!-- .nk-chat-head-search -->
                    </div><!-- .nk-chat-head -->
                    <div class="nk-chat-panel" id="user-chat-content">
                    </div><!-- .nk-chat-panel -->
                    <div class="nk-chat-editor" id="messageReply">

                    </div><!-- .nk-chat-editor -->
                    <div class="nk-chat-profile visible" data-simplebar>
                        <div class="user-card user-card-s2 my-4">
                            <div class="user-avatar md bg-purple">
                                <span>IH</span>
                            </div>
                            <div class="user-info">
                                <h5 id="sidebar_name">Iliash Hossain</h5>
                            </div>
                        </div>
                        <div class="chat-profile">
                            <div class="chat-profile-group">
                                <a href="#" class="chat-profile-head" data-bs-toggle="collapse"
                                    data-bs-target="#chat-options">
                                    <h6 class="title overline-title">Options</h6>
                                    <span class="indicator-icon"><em class="icon ni ni-chevron-down"></em></span>
                                </a>
                                <div class="chat-profile-body collapse show" id="chat-options">
                                    <div class="chat-profile-body-inner">
                                        <ul class="chat-profile-options">
                                            <li><a class="chat-option-link" href="#"><em
                                                        class="icon icon-circle bg-light ni ni-edit-alt"></em><span
                                                        class="lead-text">Nickname</span></a></li>
                                            <li><a class="chat-option-link chat-search-toggle" href="#"><em
                                                        class="icon icon-circle bg-light ni ni-search"></em><span
                                                        class="lead-text">Search In Conversation</span></a></li>
                                            <li><a class="chat-option-link" href="#"><em
                                                        class="icon icon-circle bg-light ni ni-circle-fill"></em><span
                                                        class="lead-text">Change Theme</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div><!-- .chat-profile-group -->
                        </div><!-- .chat-profile-group -->
                    </div> <!-- .chat-profile -->
                </div><!-- .nk-chat-profile -->
            </div><!-- .nk-chat-body -->
        </div><!-- .nk-chat -->
    </div>
    </div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
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
                if (response) {}
            },
            error: function(error) {
                console.error("Error during form submission:", error);
                // Handle errors as needed
            }
        });
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
                        fetchThreadByID(item.id, response[0].name, response[0].live_feed_event_id);
                        // return false;
                    }
                    // console.log(item.title);

                    let title = "'" + item.name + "'"
                    let live_feed_event_id = "'" + item.live_feed_event_id + "'"
                    $('#approve_btn').attr('data-id', response[0].live_feed_event_id);
                    $('#special_offer').attr('data-id', response[0].live_feed_event_id);

                    let newMessage = `
                    <li class="chat-item" data-msg-id="` + index + `" onclick="fetchThreadByID(` + item.id +
                        `, ` + title + `, ` + live_feed_event_id + `)">
                                    <span class="chat-link chat-open">
                                        <div class="chat-media user-avatar bg-purple">
                                            <span>` + item.name.charAt(0) + `</span>
                                            <span class="status dot dot-lg dot-gray"></span>
                                        </div>
                                        <div class="chat-info">
                                            <div class="chat-from">
                                                <div class="name">` + item.name + `</div>
                                                <span class="time">` + date + `</span>

                                            </div>
                                            <div class="chat-context">
                                                <div class="text">
                                                    <p class='m-0'>` + item.last_message + `</p>
                                                    <span>` + item.listing_name.substring(0, 20) + `</span>
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                </li>
                    `;

                    $('#chat-list').append(newMessage);
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

    function fetchThreadByID(thread_id, title, live_feed_event_id) {



        $('#user-chat-content').html('')
        $('#header_name').html('')
        $('#messageReply').html('')
        $('#sidebar_name').html('')
        $('#sidebar_name').append(title)
        $('#header_name').append(title)
        $('#header_short_name').html('')
        $('#header_short_name').append(title.charAt(0))
        $('#approve_btn').attr('data-id', live_feed_event_id);
        $('#special_offer').attr('data-id', live_feed_event_id);
        $('#inquiry_text').css('display', 'none');
        $('#inquiry_offer_card').css('display', 'flex');

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

                response.map((item, index) => {
                    const date = new Date(item.created_at);
                    const formattedDate = date.toLocaleDateString();

                    let user_type = item.sender == 'guest' ? 'is-you' : 'is-me'

                    let messages = `
                        <div class="chat ` + user_type + `">
                            <div class="chat-avatar">
                                <div class="user-avatar bg-purple">
                                    <span>IH</span>
                                </div>
                            </div>
                            <div class="chat-content">
                                <div class="chat-bubbles">
                                    <div class="chat-bubble">
                                        <div class="chat-msg"> ` + item.message_content + ` </div>
                                    </div>
                                </div>
                                <ul class="chat-meta">
                                    <li>` + item.sender + `</li>
                                    <li>` + formattedDate + `</li>
                                </ul>
                            </div>
                        </div>
                    `

                    // let messages = `
                    // <div class="nk-reply-item">
                    //     <div class="nk-reply-header">
                    //         <div class="user-card">
                    //             <div class="user-avatar sm bg-blue">
                    //                 <span>LIn</span>
                    //             </div>
                    //             <div class="user-name">` + item.sender + `</div>
                    //         </div>
                    //         <div class="date-time">` + formattedDate + `</div>
                    //     </div>
                    //     <div class="nk-reply-body">
                    //         <div class="nk-reply-entry entry">
                    //             <p>` + item.message_content + `</p>
                    //         </div>
                    //     </div>
                    // </div>`;
                    $('#user-chat-content').append(messages)
                    $(document).ready(function() {
                        var ul = $('#user-chat-content');
                        ul.scrollTop(ul.prop("scrollHeight"));
                    });
                });

                let messageReply =
                    `
                <div class="nk-chat-editor-upload  ms-n1">
                            <a href="#" class="btn btn-sm btn-icon btn-trigger text-primary toggle-opt"
                                data-target="chat-upload"><em class="icon ni ni-plus-circle-fill"></em></a>
                            <div class="chat-upload-option" data-content="chat-upload">
                                <ul class="">
                                    <li><a href="#"><em class="icon ni ni-img-fill"></em></a></li>

                                </ul>
                            </div>
                        </div>
                        <div class="nk-chat-editor-form">
                            <div class="form-control-wrap">
                                <textarea class="form-control form-control-simple no-resize" rows="1" id="default-textarea"
                                    placeholder="Type your message..."></textarea>
                            </div>
                        </div>
                         <input type="hidden" name="thread_id" id="thread_id" value="` + thread_id +
                    `">
                        <ul class="nk-chat-editor-tools g-2">
                            <li>
                                <button class="btn btn-round btn-primary btn-icon" id="resplySubmit" onclick="submitReply(` +
                    thread_id + `)" type="button"><em
                                        class="icon ni ni-send-alt"></em></button>
                            </li>
                        </ul>
                `


                $('#messageReply').append(messageReply)

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
                    <li class="chat-item" data-msg-id="` + index + `" onclick="fetchThreadByID(` + item.id +
                            `, ` + title + `, ` + live_feed_event_id + `)">
                                    <span class="chat-link chat-open">
                                        <div class="chat-media user-avatar bg-purple">
                                            <span>` + item.name.charAt(0) + `</span>
                                            <span class="status dot dot-lg dot-gray"></span>
                                        </div>
                                        <div class="chat-info">
                                            <div class="chat-from">
                                                <div class="name">` + item.name + `</div>
                                                <span class="time">` + date + `</span>
                                            </div>
                                            <div class="chat-context">
                                                <div class="text">
                                                    <p>` + item.last_message + `</p>
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                </li>
                    `;

                        $('#chat-list').prepend(newMessage);
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
