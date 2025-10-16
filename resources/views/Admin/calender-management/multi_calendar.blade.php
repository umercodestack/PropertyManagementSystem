<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Calendar</title>

    <link rel="stylesheet" href="{{asset('assets/assets/css/styles.css?date=2024-12-13')}}">
    <link rel="stylesheet" href="{{asset('assets/assets/css/custom.css?date=2024-12-13')}}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

</head>

<body>


    <div class="calendar-container">

        <!-- --------------------  Property List Start    ------------------------ -->
        <button class="menu-btn">☰ </button>
        <div class="property-list">
            <button class="close-btn">✕</button> 
            <div class="list_heading">
                <span id="total_property">

                </span>
            </div>

            <div class="search-container">
                <i class="bi bi-search search_icon"></i>
                <input type="text" placeholder="Search listings..." class="search-box" id="searchInput" autocomplete="off">
                <button id="filter_button" class="filter_button">Go</button>
                <!-- <i class="bi bi-sliders2-vertical filter_icon"></i> -->
            </div>


            <ul id="property-listings">
                <!-- <span class="property_loader" id="property_loader"></span> -->

                <!-- <li><a href="">lokal room</a></li> -->
            </ul>
            <div class="pagination_container" id="pagination_container">
                <!-- <ol class="pagination">
                    <span><a href="#">«</a></span>
                  </ol> -->
            </div>
        </div>
        <!-- --------------------  Property List End     ------------------------------------ -->

        <div class="calendar_wrapper">

            <!-------------------------------------- Header  start ------------------------------------------->
            <div class="calendar-header">
                <div class="selection_wrapper">
                    <select class="month_selection" id="monthSelect">
                        <!-- <option value="0">Select Month</option> -->
                    </select>
                </div>
                <div class="button_wrapper">
                    <!-- <button>Available Opportunities</button> -->

                    <!-- <select class="layer_selection">
                        <option value="0">Layer</option> </select> -->
                </div>
            </div>
            <!-------------------------------------- Header  End ------------------------------------------->


            <!-------------------------------------- Calendar  start ------------------------------------------->

              <span class="calendar_loader" id="calendar_loader"></span>
              <span class="message_for_empty_calendar" id="message_for_empty_calendar">Data Not Found</span>

            <div class="calendar-content">
                <div class="calendar-header-dates">
                    <!-- <div>August</div> -->
                    <div></div>
                </div>
                <div class="calendar_col">
                    <!-- <div class="calendar-dates">
                </div>
                <div class="calendar-grid">
                </div>
                <div class="calendar-grid1">
                </div>
                <div class="calendar-grid2">
                </div> -->
                </div>
                <!-------------------------------------- Calendar  End ------------------------------------------->
            </div>

        </div>
    </div>


    <!-- Sidebar -->
    <div class="sidebar">
        <div class="cut-button" id="cut-button">&#10005;</div>

        <h2>Selected Dates</h2>
        <div class="date-range">
            <input type="text" id="dateRange" class="dateInput" name="daterange" />
        </div>
        <h3 class="listing" id="total_property_sideBar"></h3>
        <hr>
        <h3>Availability</h3>
        <label class="radio_label">Available
            <input type="radio" name="radio" class="inputRadio" value="true" checked="checked">
            <span class="checkmark"></span>
        </label>
        <label class="radio_label">Unavailable
            <input type="radio" name="radio" class="inputRadio" value="false">
            <span class="checkmark"></span>
        </label>
        <hr>
         <div id="reason_wrapper">

            <h3>Block Reason</h3>
            <textarea name="" id="reason_text" class="reason_text"></textarea>
            <hr>
        </div>

     

        <div class="stay_wrapper">

            <h3 class="stay_label" >Min Stay
            </h3>
            <h3 class="stay_label">Max Stay
            </h3>
        </div>
        <input type="number" class="min_stay_input" id="min_stay_input" placeholder="Minimum Stay">
        <input type="number" class="max_stay_input" id="max_stay_input" placeholder="Maximum Stay">
       
        <hr>
        <h3>Price</h3>
        <input type="number" class="price_input" id="input_price" placeholder="Enter price">
        <div class="price_wrapper">
            <em class="price_label">1. If you enter an empty price, It won’t reflect</em>
            <em class="price_label">2. Price cannot be 0, It can be empty or greater than 0</em>
        </div>
        <hr>
        <div class="switch_wrapper">
        <h3>Lock</h3>
        <label class="switch">
            <input  type="checkbox" id="checkLock">
            <div class="slider">
                <div class="circle">
                    <svg class="cross" xml:space="preserve" style="enable-background:new 0 0 512 512" viewBox="0 0 365.696 365.696" y="0" x="0" height="6" width="6" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <path data-original="#000000" fill="currentColor" d="M243.188 182.86 356.32 69.726c12.5-12.5 12.5-32.766 0-45.247L341.238 9.398c-12.504-12.503-32.77-12.503-45.25 0L182.86 122.528 69.727 9.374c-12.5-12.5-32.766-12.5-45.247 0L9.375 24.457c-12.5 12.504-12.5 32.77 0 45.25l113.152 113.152L9.398 295.99c-12.503 12.503-12.503 32.769 0 45.25L24.48 356.32c12.5 12.5 32.766 12.5 45.247 0l113.132-113.132L295.99 356.32c12.503 12.5 32.769 12.5 45.25 0l15.081-15.082c12.5-12.504 12.5-32.77 0-45.25zm0 0"></path>
                        </g>
                    </svg>
                    <svg class="checkmark" xml:space="preserve" style="enable-background:new 0 0 512 512" viewBox="0 0 24 24" y="0" x="0" height="10" width="10" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <path class="" data-original="#000000" fill="currentColor" d="M9.707 19.121a.997.997 0 0 1-1.414 0l-5.646-5.647a1.5 1.5 0 0 1 0-2.121l.707-.707a1.5 1.5 0 0 1 2.121 0L9 14.171l9.525-9.525a1.5 1.5 0 0 1 2.121 0l.707.707a1.5 1.5 0 0 1 0 2.121z"></path>
                        </g>
                    </svg>
                </div>
            </div>
        </label>
    </div>
        <!-- <h3>Rule-sets</h3>
    <select>
        <option value="">Select rule-set</option>
        <option value="1">Rule-set 1</option>
        <option value="2">Rule-set 2</option>
    </select> -->
        <span id="response_update_api" class="response"></span>
        <button id="saveMultiCalendarData">Save</button>
    </div>

    <script src="{{ asset('assets/assets/js/calendar_script.js') }}?v={{ time() }}"></script>

    <!-- <script src="pagination.js"></script> -->

    <script>
        // let dateRanger = document.getElementById("dateRange").disabled = true;
        $(document).ready(function () {
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                autoApply: false,
                locale: {
                    format: 'MM/DD/YYYY'
                }
            });
        });

        // $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        //     bridgeFunction
        //     console.log('Manual selection:', picker.startDate.format('MM/DD/YYYY'), picker.endDate.format('MM/DD/YYYY'));
        // });
    </script>

</body>

</html>