@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Finance SOA</h3>
                <div class="nk-block-des text-soft">

                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">

                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <form action="{{ route('reports.finance.soa.print') }}" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="listing_id">Select Host</label>
                            <select class="form-control select2" name="user_id" id="user_id" onchange="fetchListings(this.value);fetchSoasByHostId(this.value)" required>
                                <option value="" selected disabled>Select Host</option>
                                <option value="">All</option>
                                @foreach ($hosts as $items)
                                    <option value="{{ $items->id }}">
                                        {{ $items->name }} {{ $items->surname }} {{ $items->host_key }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="daterange">Booking Date</label>
                            <input type="text" class="form-control" name="daterange" id="daterange" required
                                 value="01/01/2018 - 01/15/2018" />
                            @error('daterange')
                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label" for="booking_sources">Apartments</label>
                            <div class="form-control-wrap">
                                <select class="form-select" name="listings[]" multiple="multiple" required
                                     id="listings" data-placeholder="Apartments">
                                </select>
                                @error('listings')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 mt-4">
                        <div class="form-group text-end">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner" style="position: relative!important; overflow: auto!important; width: 100%!important;">
            <table class="datatable-init-export  table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Host Name</th>
                        <th>Booking Dates</th>
                        <th>Publish Date</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="t_body">

                </tbody>
            </table>
        </div>
    </div>
    
    <div class="modal fade" tabindex="-1" id="modalDefault">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Map Listing</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <form method="POST" action="{{ route('reports.finance.soa.upload.pop') }}" enctype="multipart/form-data" style="padding: 20px; width:100%">
            @csrf
            <label>
                <span style="margin-top: 5px;">Upload Proof of Payment:</span>
                <input
                    type="file"
                    name="pop"
                    style="border: 1px solid black; padding: 5px;"
                    accept=".pdf,.jpg,.jpeg"
                    required
                >
                <input type="hidden" name="soa_id" id = 'soa_id'>

                <button type="submit" style="padding: 6px; border: 0; background: #a6a6ff; color: black; border-radius: 5px;float:inline-end;margin-top:10px">Submit</button>
            </label>
        </form>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
 var start = moment().subtract(2, 'days');
        var end = moment();
        var start = moment().subtract(2, 'days');
        var end = moment();

        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
            // minDate: moment(), // Disable dates before today
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
                'YYYY-MM-DD'));
        });
$(document).on("click", "#soaPop", function () {
        var soa_id = $(this).data('id');
        $("#soa_id").val( soa_id );
        // As pointed out in comments,
        // it is unnecessary to have to manually call the modal.
        // $('#addBookDialog').modal('show');
});
        function fetchListings(user_id) {
            $('#listings').html('')
            const $listings = $('#listings');
            $.ajax({
                url: `{{ url('report/finance/soa/host/listings') }}/${user_id}`,
                type: "GET",
                data: {
                    user_id: user_id,
                },
                success: function(response) {
                    if (response) {
                        console.log(response);
                        $listings.empty();
                        response.forEach((apartment) => {
                            let listingJson = JSON.parse(apartment.listing_json);
                            // console.log()
                            $listings.append(
                                $('<option>', {
                                    value: apartment.id,
                                    text: listingJson.title
                                })
                            );
                        });
                        $listings.trigger('change'); // If select2 is already initialized

                    }
                },
                error: function(error) {
                    console.error("Error during form submission:", error);
                }
            });
        }
        
        function fetchSoasByHostId(host_id) {
            $('#t_body').html('');
            let special_offer_amount = null;
            $.ajax({
                url: "{{ route('reports.finance.soa.fetch.soa.by.host') }}",
                type: "GET",
                data: {
                    user_id: host_id,
                },
                success: function(response) {
                    if (response) {
                        response.map((item, index) => {
                            console.log('soas',item)

                        //     <th>S.No</th>
                        // <th>Host Name</th>
                        // <th>Booking Dates</th>
                        // <th>Publish Date</th>
                        // <th>Total</th>
                        // <th>Action</th>
                            $('#t_body').append(`
                            <tr>
                                <td>` + ++index + `</td>
                                <td>` + item.user.name + `</td>
                                <td>` + item.booking_dates + `</td>
                                <td>` + item.publish_date + `</td>
                                <td>` + item.total + `</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-id="`+item.id+`" id="soaPop" data-bs-toggle="modal" data-bs-target="#modalDefault">
                                    <em class="icon ni ni-link"></em>
                                </button>
                                </td>
                            </tr>
                    `);
                        });
                    }
                },
                error: function(error) {
                    console.error("Error during form submission:", error);
                    // Handle errors as needed
                }
            });
        }
        fetchSoasByHostId(null);
    </script>
@endsection
