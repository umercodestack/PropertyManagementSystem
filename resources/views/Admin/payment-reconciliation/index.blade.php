@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Payment Reconciliation</h3>
                <div class="nk-block-des text-soft">
                    <!--<p>All Bookings</p>-->
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner table-responsive">
            <table class="datatable-init-export table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Action</th>
                        <th>IBFT</th>
                        <th>Reservation Code</th>
                        <th>Confirmation Code</th>
                        <th>Booking ID</th>
                        <th>OTA Booking ID</th>
                        <th>Created On</th>
                        <th>Guest</th>
                        <th>Apartment</th>
                        <th>Phone</th>
                        <th>Source</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Payment Method</th>
                        <th>OTA</th>
                        <th>Total</th>
                        <th>Discount</th>
                        <th>Service Fee</th>
                        <th>Ota Commission</th>
                        <th>Cleaning Fee</th>
                        <th>Per Night</th>
                        <th>Booking Notes</th>
                        
                        <th>Payment Received from OTA</th>
                        <th>Payment Received Date</th>
                        <th>Bank Charges</th>
                        <th>Checked Bank Statement</th>
                        <th>Remarks</th>
                        
                    </tr>
                </thead>
                <tbody>

                    @php $key_counter = 0; @endphp
                    @foreach ($data['bookings'] as $itembk)
                    
                    @php
                    
                    $ibft_screenshot = !is_null($itembk->payment_reconciliation) ? $itembk->payment_reconciliation->ibft_screenshot : '';
                    $payment_recevied_ota = !is_null($itembk->payment_reconciliation) ? $itembk->payment_reconciliation->payment_recevied_ota : '';
                    $payment_received_date = !is_null($itembk->payment_reconciliation) ? $itembk->payment_reconciliation->payment_received_date : '';
                    $bank_charges = !is_null($itembk->payment_reconciliation) ? $itembk->payment_reconciliation->bank_charges : '';
                    $bank_statement = !is_null($itembk->payment_reconciliation) ? $itembk->payment_reconciliation->bank_statement : '';
                    $remarks = !is_null($itembk->payment_reconciliation) ? $itembk->payment_reconciliation->remarks : '';
                    
                    @endphp
                    
                        <tr>
                            <td>{{ ++$key_counter }}</td>
                            
                            <td>
                                <!--data-bs-toggle="modal" data-bs-target="#modalDefault"-->
                                <button type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="addPayment({{$itembk['id']}}, 'bookings')">
                                    <em class="icon ni ni-link"></em>
                                </button>
                            </td>
                            
                            <td>
                                @if(!empty($ibft_screenshot))
                                <a target="_blank" href="{{$ibft_screenshot}}" class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-tag"></em>
                                </a>
                                @else
                                    @if($itembk->images()->exists())
                                        @foreach($itembk->images()->get() as $bkimg)
                                        <a target="_blank" href="{{ asset('storage/' . $bkimg->image) }}" class="btn btn-primary btn-sm">
                                            <em class="icon ni ni-tag"></em>
                                        </a>
                                        @endforeach
                                    @endif
                                @endif
                            </td>
                            
                            <td>
                                {{$itembk->reservation_code}}
                            </td>
                            
                            <td>
                                ---
                            </td>
                            
                            <td>
                                <a target="_blank" href="{{ route('booking-management.edit', $itembk['id']) }}" style="background: #6576ff; color: white; padding: 5px; border-radius: 3px; ">{{ $itembk->id }}</a>
                            </td>
                            
                            <td>
                                ---
                            </td>
                            <td>{{ $itembk->created_at->format('Y-m-d h:i:s A') }}</td>
                            @php
                                $guest = \App\Models\Guests::where('id', $itembk->guest_id)->first();
                            @endphp
                            <td>{{ !empty($guest) ? ($guest->name . ' ' . $guest->surname) : '-' }}</td>
                            <td>
                                @php
                                    $listing = \App\Models\Listing::where('id', $itembk->listing_id)->first();
                                    // dd($listing);
                                    if (isset($listing->listing_json)) {
                                        $listing_json = json_decode($listing->listing_json);
                                    } else {
                                        $listing_json = [];
                                    }
                                    // $listing_json = json_decode($listing->listing_json);
                                @endphp

                                {{ isset($listing_json->title) ? $listing_json->title : '' }}
                            </td>
                            <td>{{ $itembk->phone }}</td>
                            <td>{{ $itembk->booking_sources }}</td>
                            <td>{{ $itembk->booking_date_start }}</td>
                            <td>{{ $itembk->booking_date_end }}</td>
                            <td>{{ $itembk->payment_method ?? '-' }}</td>
                            <td>{{ $itembk->ota_name }}</td>
                            <td>{{ $itembk->total_price }}</td>
                            <td>{{ $itembk->custom_discount }}</td>
                            <td>{{ $itembk->service_fee }}</td>
                            <td>{{ $itembk->ota_commission }}</td>
                            <td>{{ $itembk->cleaning_fee }}</td>
                            <td>{{ $itembk->per_night_price }}</td>
                            <td>{{ $itembk->booking_notes }}</td>
                            
                            <td>
                                <span id="bk_payment_rec_ota_{{$itembk['id']}}">
                                    {{ $payment_recevied_ota }}
                                </span>
                            </td>
                            <td>
                                <span id="bk_payment_rec_date_{{$itembk['id']}}">
                                    {{ $payment_received_date }}
                                </span>
                            </td>
                            <td>
                                <span id="bk_bank_charges_{{$itembk['id']}}">
                                    {{ $bank_charges }}
                                </span>
                            </td>
                            <td>
                                <span id="bk_bank_statement_{{$itembk['id']}}">
                                    {{ $bank_statement }}
                                </span>
                            </td>
                            <td>
                                <span id="bk_remarks_{{$itembk['id']}}">
                                    {{ $remarks }}
                                </span>
                            </td>
                        </tr>
                    @endforeach

                    @foreach ($data['ota_bookings'] as $item)
                        @php
                            $booking_details = json_decode($item->booking_otas_json_details);

                            $raw_message = json_decode($booking_details->attributes->raw_message);
                            $iserted_at = $booking_details->attributes->inserted_at;

                            $pricing_rules = 0;
                            $discount = 0;
                            $promotion = 0;
                            if (isset($raw_message->reservation->pricing_rule_details)) {
                                foreach ($raw_message->reservation->pricing_rule_details as $items) {
                                    $pricing_rules += -$items->amount_native;
                                    $discount += -$items->amount_native;
                                }
                            }
                            if (isset($raw_message->reservation->promotion_details)) {
                                foreach ($raw_message->reservation->promotion_details as $items) {
                                    $promotion += -$items->amount_native;
                                }
                            }

                            // dd($raw_message->reservation->listing_base_price_accurate + -($raw_message->reservation->promotion_details[0]->amount_native));
                            $raw_message->reservation->listing_base_price_accurate =
                                $raw_message->reservation->listing_base_price_accurate +
                                -(isset($raw_message->reservation->promotion_details[0]->amount_native)
                                    ? $raw_message->reservation->promotion_details[0]->amount_native
                                    : -0) +
                                $pricing_rules;
                            // dd($raw_message->reservation->listing_base_price_accurate);
                            // dd($raw_message);
                            // $booking_details->attributes->raw_message = json_encode($raw_message);
                            // $booking->booking_otas_json_details = json_encode($booking_details);
                            // $booking['booking_status'] = 'confirmed';


                            $cleaning_fee = !empty($booking_details->attributes->rooms[0]->services[0]->total_price) ? (int) $booking_details->attributes->rooms[0]->services[0]->total_price : 0;


                            $start_date = $item->arrival_date;
                            $end_date = $item->departure_date;


                            $total_nights = $start_date == $end_date ? 1 : \Carbon\Carbon::parse($start_date)->diffInDays(\Carbon\Carbon::parse($end_date));

                            $total = !empty($raw_message->reservation->listing_base_price_accurate) ? $raw_message->reservation->listing_base_price_accurate : 0;

                            $per_night_price = !empty($total) && !empty($total_nights) ? $total / $total_nights : 0;
                            
                            $confirmation_code = !empty($raw_message->reservation->confirmation_code) ? $raw_message->reservation->confirmation_code :'';
                            
                    
                            $ibft_screenshot = !is_null($item->payment_reconciliation) ? $item->payment_reconciliation->ibft_screenshot : '';
                            $payment_recevied_ota = !is_null($item->payment_reconciliation) ? $item->payment_reconciliation->payment_recevied_ota : '';
                            $payment_received_date = !is_null($item->payment_reconciliation) ? $item->payment_reconciliation->payment_received_date : '';
                            $bank_charges = !is_null($item->payment_reconciliation) ? $item->payment_reconciliation->bank_charges : '';
                            $bank_statement = !is_null($item->payment_reconciliation) ? $item->payment_reconciliation->bank_statement : '';
                            $remarks = !is_null($item->payment_reconciliation) ? $item->payment_reconciliation->remarks : '';

                        @endphp
                        <tr>
                            <td>{{ ++$key_counter }}</td>
                            
                            <td>
                                <!--data-bs-toggle="modal" data-bs-target="#modalDefault"-->
                                <button type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="addPayment({{$item['id']}}, 'ota_bookings')">
                                    <em class="icon ni ni-link"></em>
                                </button>
                            </td>
                            
                            <td>
                                @if(!empty($ibft_screenshot))
                                <a target="_blank" href="{{$ibft_screenshot}}" class="btn btn-primary btn-sm">
                                    <em class="icon ni ni-tag"></em>
                                </a>
                                @else
                                    ---
                                @endif
                            </td>
                            
                            <td>
                                ---
                            </td>
                            
                            <td>
                                {{$confirmation_code}}
                            </td>
                            

                            <td>
                                ---
                            </td>
                            
                            <td>
                                <a target="_blank" href="{{ route('booking.editOtaBooking', $item['id']) }}" style="background: #6576ff; color: white; padding: 5px; border-radius: 3px; ">{{ $item['booking_id'] }}</a>
                            </td>

                            <td>{{ \Carbon\Carbon::parse($iserted_at)->format('Y-m-d h:i:s A') }}</td>

                            <td>{{ $booking_details->attributes->customer->name }}</td>
                            <td>
                                @php
                                    $listing = \App\Models\Listing::where('listing_id', $item->listing_id)->first();
                                    // dd($listing);
                                    if (isset($listing->listing_json)) {
                                        $listing_json = json_decode($listing->listing_json);
                                    } else {
                                        $listing_json = [];
                                    }
                                    // $listing_json = json_decode($listing->listing_json);
                                @endphp

                                {{ isset($listing_json->title) ? $listing_json->title : '' }}
                            </td>

                            <td>{{ !empty($booking_details->attributes->customer->phone) ? $booking_details->attributes->customer->phone : '' }}</td>

                            <td>{{ !empty($booking_details->attributes->ota_name) ? $booking_details->attributes->ota_name : '' }}</td>

                            <td>{{ $item->arrival_date }}</td>
                            <td>{{ $item->departure_date }}</td>

                            <td>---</td>
                            <td>{{ !empty($booking_details->attributes->ota_name) ? $booking_details->attributes->ota_name : '' }}</td>

                            <td>{{ $raw_message->reservation->listing_base_price_accurate }}</td>

                            <td>{{ $discount }}</td>

                            <td>0</td>
                            
                            <td>{{ $booking_details->attributes->ota_commission }}</td>

                            <td>{{ $cleaning_fee }}</td>
                            
                            <td>{{ round((float) $per_night_price, 2) }}</td>
                            <td>{{ !empty($booking_details->attributes->notes) ? $booking_details->attributes->notes : '' }}</td>
                            
                            
                            <td>
                                <span id="ota_bk_payment_rec_ota_{{$item['id']}}">
                                    {{ $payment_recevied_ota }}
                                </span>
                            </td>
                            <td>
                                <span id="ota_bk_payment_rec_date_{{$item['id']}}">
                                    {{ $payment_received_date }}
                                </span>
                            </td>
                            <td>
                                <span id="ota_bk_bank_charges_{{$item['id']}}">
                                    {{ $bank_charges }}
                                </span>
                            </td>
                            <td>
                                <span id="ota_bk_bank_statement_{{$item['id']}}">
                                    {{ $bank_statement }}
                                </span>
                            </td>
                            <td>
                                <span id="ota_bk_remarks_{{$item['id']}}">
                                    {{ $remarks }}
                                </span>
                            </td>
                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="oldmodalDefault">
        <form action="{{ route('booking.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Bookings</h5>
                        <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <em class="icon ni ni-cross"></em>
                        </a>
                    </div>
                    <div class="modal-body">
                        <input type="file" class="form-control" name="file" accept=".xlsx, .xls" required>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="text-end">
                            <a href="{{ asset('excel_template/booking_template.xlsx') }}" download>Download Sample File</a>
                        </div>
                        <button class="btn btn-danger">Cancel</button>
                        <button class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="modal fade" tabindex="-1" id="modalDefault">
        <form id="paymentReconciliationForm" action="{{ route('payment-reconciliation.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="booking_id" name="booking_id" value="">
            <input type="hidden" id="booking_type" name="booking_type" value="">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment Reconciliation</h5>
                        <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <em class="icon ni ni-cross"></em>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>IBFT Screenshot</label>
                            <input type="file" class="form-control" name="ibft_screenshot">
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Received from OTA</label>
                            <select name="payment_recevied_ota" id="payment_recevied_ota" class="form-control">
                                <option value="" selected>Please select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Received Date</label>
                            <input type="date" class="form-control" name="payment_received_date">
                        </div>
                        
                        <div class="form-group">
                            <label>Bank Charges</label>
                            <input type="text" class="form-control" name="bank_charges">
                        </div>
                        
                        <div class="form-group">
                            <label>Checked Bank Statement</label>
                            <input type="radio" id="bank_statement_yes" name="bank_statement" value="Yes">
                            <label for="bank_statement_yes">Yes</label> &nbsp;&nbsp; 
                            <input type="radio" id="bank_statement_no" name="bank_statement" value="No">
                            <label for="bank_statement_no">No</label><br>
                        </div>
                        
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control"></textarea>
                        </div>
                        
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    
        function addPayment(booking_id, type){
            
            $('#booking_id').val(booking_id);
            $('#booking_type').val(type);
            
            $('#modalDefault').modal('show');
        }
        
        $(document).ready(function() {
            
            $('#paymentReconciliationForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                var booking_id = formData.get('booking_id');
                var booking_type = formData.get('booking_type');
                
                console.log(booking_id, booking_type);
    
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                        if(response.success) {
                            
                            if(booking_type == "bookings"){
                                
                                $('#bk_payment_rec_ota_'+booking_id).text(response?.data?.payment_recevied_ota);
                                $('li').find('#bk_payment_rec_ota_'+booking_id).text(response?.data?.payment_recevied_ota);
                                
                                $('#bk_payment_rec_date_'+booking_id).text(response?.data?.payment_received_date);
                                $('li').find('#bk_payment_rec_date_'+booking_id).text(response?.data?.payment_received_date);
                                
                                $('#bk_bank_charges_'+booking_id).text(response?.data?.bank_charges);
                                $('li').find('#bk_bank_charges_'+booking_id).text(response?.data?.bank_charges);
                                
                                $('#bk_bank_statement_'+booking_id).text(response?.data?.bank_statement);
                                $('li').find('#bk_bank_statement_'+booking_id).text(response?.data?.bank_statement);
                                
                                $('#bk_remarks_'+booking_id).text(response?.data?.remarks);
                                $('li').find('#bk_remarks_'+booking_id).text(response?.data?.remarks);
                                
                            }
                            
                            if(booking_type == "ota_bookings"){
                                
                                $('#ota_bk_payment_rec_ota_'+booking_id).text(response?.data?.payment_recevied_ota);
                                $('li').find('#ota_bk_payment_rec_ota_'+booking_id).text(response?.data?.payment_recevied_ota);
                                
                                $('#ota_bk_payment_rec_date_'+booking_id).text(response?.data?.payment_received_date);
                                $('li').find('#ota_bk_payment_rec_date_'+booking_id).text(response?.data?.payment_received_date);
                                
                                $('#ota_bk_bank_charges_'+booking_id).text(response?.data?.bank_charges);
                                $('li').find('#ota_bk_bank_charges_'+booking_id).text(response?.data?.bank_charges);
                                
                                $('#ota_bk_bank_statement_'+booking_id).text(response?.data?.bank_statement);
                                $('li').find('#ota_bk_bank_statement_'+booking_id).text(response?.data?.bank_statement);
                                
                                $('#ota_bk_remarks_'+booking_id).text(response?.data?.remarks);
                                $('li').find('#ota_bk_remarks_'+booking_id).text(response?.data?.remarks);
                                
                            }

                            $('#paymentReconciliationForm')[0].reset();
                            $('#modalDefault').modal('hide');
                            alert('Form submitted successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('There was an error processing the request: ' + error);
                    }
                });
            });
        });
    </script>
@endsection
