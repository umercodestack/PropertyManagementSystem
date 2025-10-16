@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Listings</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{ count($listings) }} Listings.</p>
                </div>
            </div>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <form action="{{ route('sync.listing') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="channel_id"
                                            value="{{ isset($_GET['channel_id']) ? $_GET['channel_id'] : '' }}">
                                        <button class="btn btn-icon btn-primary p-1" type="Submit">Sync Listings</button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init-export nowrap table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Id</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Rate Plan Enabled</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listings as $key => $item)
                        @php

                            $listing_json = json_decode($item->listing_json);
                            //                    dd($listing_json);
                            $channel = \App\Models\Channels::where('id', $_GET['channel_id'])->first();
                            $listingRelation = \App\Models\ListingRelation::where(
                                'listing_id_other_ota',
                                $item->id,
                            )->first();
                        @endphp
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $item->id }}</td>
                            <td>{{ $channel->connection_type != null ? $channel->connection_type : 'Airbnb' }}</td>
                            <td>{{ $listing_json->title ?? '' }}</td>
                            <td>

                                {{ $channel->connection_type == ''
                                    ? ($item->is_sync === 'sync_all'
                                        ? 'Yes'
                                        : 'No')
                                    : (isset($listingRelation)
                                        ? 'Yes'
                                        : 'No') }}
                            </td>
                            <td>

                                {{-- <form action="{{ route('pullFutureReservation') }}" method="POST" style="display:contents">
                                    @csrf
                                    <input type="hidden" name="listing_id" value="{{ $item['listing_id'] }}">
                                    <input type="hidden" name="ch_channel_id" value="{{ $channel['ch_channel_id'] }}">
                                    <button type="submit" title="Load Future Reservation" class="btn btn-primary btn-sm">
                                        <em class="icon ni ni-edit"></em>
                                    </button>
                                </form> --}}
                                @php
                                    $user = json_decode($item['user_id'], true);
                                    // dd($user[0]);
                                @endphp
                                <button type="button" class="btn btn-primary btn-sm ajax-map-listing-btn"
                                    data-user-id="{{ $user[0] ?? 0 }}" data-listing-id="{{ $item['listing_id'] ?? 0 }}"
                                    data-route="{{ route('mapListing', $user[0] ?? 0) }}" title="Map Listing">
                                    @if ($item['is_sync'] === 'sync_all')
                                        <em class="icon ni ni-link-alt"></em>
                                    @else
                                        <em class="icon ni ni-unlink-alt"></em>
                                    @endif
                                </button>
                                </form>

                                <a title="Edit Listing" href="{{ route('listing.pricing.edit', $item['id']) }}"
                                    class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-setting"></em>
                                </a>
                                <button type="button" class="btn btn-primary btn-sm" data-id="{{ $item->id }}"
                                    id="map" data-bs-toggle="modal" data-bs-target="#modalDefault">
                                    <em class="icon ni ni-book-read"></em>
                                </button>
                                @if ($item->status == 0)
                                    <a id="createAlmosfarProperty"
                                        href="{{ route('create.almosafer.property', $item['id']) }}"
                                        class="btn btn-danger btn-sm createAlmosfarProperty"
                                        onclick="disableButton(this, event)">
                                        Create on Almosafer
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
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
                <form action="{{ route('listing.mapBookingListing') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="airbnb_listing_id">Airbnb Listing</label>
                            <select class="form-control select2" name="airbnb_listing_id" id="airbnb_listing_id" required>
                                <option value="">Select Listing to sync</option>
                                @foreach ($airbnb_listings as $item)
                                    @php
                                        $listing_json = json_decode($item->listing_json);
                                        if ($item->is_manual == 1) {
                                            continue;
                                        }
                                        $channel = \App\Models\Channels::where('id', $item->channel_id)->first();
                                        if (isset($channel->connection_type) && $channel->connection_type != null) {
                                            continue;
                                        }

                                    @endphp
                                    <option value="{{ $item->id }}">{{ $listing_json->title ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="listing_id" id="listing_id" value="" />
                        <div class="form-group">
                            <label for="rate_multiplier">Multiply Rate</label>
                            <input type="number" class="form-control" name="rate_multiplier" id="rate_multiplier"
                                placeholder="Rate Multiplier" required />
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function disableButton(el, event) {
            $('.createAlmosfarProperty')
                .addClass('disabled')
                .css('pointer-events', 'none');

            el.innerText = 'Please wait...';
        }

        $(document).on("click", "#map", function() {
            var listing_id = $(this).data('id');
            $("#listing_id").val(listing_id);
            $('.select2').select2({
                dropdownParent: $('#modalDefault')
            });
            // As pointed out in comments,
            // it is unnecessary to have to manually call the modal.
            // $('#addBookDialog').modal('show');
        });

        $(document).on('click', '.ajax-map-listing-btn', function() {
            let button = $(this);
            let route = button.data('route');
            let listingId = button.data('listing-id');

            let isMapped = button.find('em').hasClass('ni-link-alt');
            let confirmText = isMapped ?
                'Do you want to unmap this property?' :
                'Do you want to map this property?';

            if (!confirm(confirmText)) {
                return;
            }

            $.ajax({
                url: route,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    listing_id: listingId
                },
                beforeSend: function() {
                    button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.message === 'listing unmapped successfully') {
                        // Unmapped: change to unlink icon
                        button.find('em').removeClass('ni-link-alt').addClass('ni-unlink-alt');

                        // Update Rate Plan to "No"
                        $('.rate-status-cell[data-listing-id="' + listingId + '"]').text('No');
                    } else {
                        // Mapped: change to link icon
                        button.find('em').removeClass('ni-unlink-alt').addClass('ni-link-alt');

                        // Update Rate Plan to "Yes"
                        $('.rate-status-cell[data-listing-id="' + listingId + '"]').text('Yes');
                    }

                    alert(response.message); // Or use toast
                },
                error: function() {
                    alert('Mapping failed.');
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });
    </script>
@endsection
