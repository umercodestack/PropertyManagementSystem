@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Revenue Triggers</h3>
                <div class="nk-block-des text-soft">
                    <!--<p>You have total 0 Revenue.</p>-->
                </div>
            </div>
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                </div>
            </div>
        </div>
    </div>
    
    <form action="{{ route('revenue-triggers.index') }}" method="GET">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="listing_id">Listings</label>
                    <select name="listing_id" class="form-control select2">
                        <option value="" selected disabled>Select Listing</option>
                        @if(!empty($listings))
                        @foreach ($listings as $item)
                            @php
                                $listing_json = json_decode($item->listing_json);
                            @endphp
                            <option value="{{ $item->listing_id }}"
                                {{ isset($_GET['listing_id']) && $_GET['listing_id'] == $item->listing_id ? 'selected' : '' }}>
                                {{ $listing_json->title }}
                            </option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="form-group">
                    <label for="trigger">Triggers</label>
                    <select name="trigger" class="form-control select2">
                        <option value="" selected disabled>Select Triggers</option>
                        @if(!empty($triggers))
                        @foreach ($triggers as $trigger_key => $trigger)
                            <option value="{{ $trigger_key }}"
                                {{ isset($_GET['trigger']) && $_GET['trigger'] == $trigger_key ? 'selected' : '' }}>
                                {{ $trigger }}
                            </option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>
            
            <div class="col-md-1 mt-3">
                <button class="btn btn-secondary" style="margin-top: 6px;" type="submit">Submit</button>
            </div>
            <div class="col-md-1 mt-3">
                <a href="{{ route('revenue-triggers.index') }}" style="margin-top: 6px;" class="btn btn-secondary">Clear</a>
            </div>
            
            <div class="col-md-2 mt-3">
                <a target="_blank" href="{{ route('revenue-triggers.auto-revenue') }}" style="margin-top: 6px;" class="btn btn-primary">Set Auto Triggers</a>
            </div>
            
            @if(!empty($_GET['listing_id']))
            <div class="col-md-1 mt-1">
                <a href="{{ url('/revenue-triggers/view-logs/'.$_GET['listing_id']) }}" class="btn btn-secondary">All Logs</a>
            </div>
            @endif
            
        </div>
    </form>
    
    <div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table" data-export-title="Export">
            <thead>
            <tr>
                <th>S.No</th>
                <th>Listing</th>
                <th>Type</th>
                <th>Trigger</th>
                <th>Log</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @php $sr = 1; @endphp
            
            <!-- DAILY TRIGGER WEEKDAYS ONLY -->
            @if(!empty($data['listings_without_booking_on_weekdays_three_pm']))
                @foreach($data['listings_without_booking_on_weekdays_three_pm'] as $lwbowsevenp)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$lwbowsevenp->jsn_listing_name}}</td>
                        <td>Daily Trigger</td>
                        <td>Property unsold until 3PM KSA. Reduce Pricing by 5%</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$lwbowsevenp->listing_id, 'trigger_type'=>$lwbowsevenp->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$lwbowsevenp->listing_id}}', '{{$lwbowsevenp->price_type}}', '{{$lwbowsevenp->days_type}}', {{$lwbowsevenp->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $lwbowsevenp->listing_id, 'days_type' => $lwbowsevenp->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            <!-- DAILY TRIGGER WEEKENDS ONLY STARTS -->
            
            @if(!empty($data['listings_without_booking_on_weekends_eight_pm']))
                @foreach($data['listings_without_booking_on_weekends_eight_pm'] as $lwbowsevenp)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$lwbowsevenp->jsn_listing_name}}</td>
                        <td>Daily Trigger</td>
                        <td>Property unsold until 8PM KSA. Reduce Pricing by 15%</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$lwbowsevenp->listing_id, 'trigger_type'=>$lwbowsevenp->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$lwbowsevenp->listing_id}}', '{{$lwbowsevenp->price_type}}', '{{$lwbowsevenp->days_type}}', {{$lwbowsevenp->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $lwbowsevenp->listing_id, 'days_type' => $lwbowsevenp->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            @if(!empty($data['listings_without_booking_on_weekends_six_pm']))
                @foreach($data['listings_without_booking_on_weekends_six_pm'] as $lwbowsixp)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$lwbowsixp->jsn_listing_name}}</td>
                        <td>Daily Trigger</td>
                        <td>Property unsold until 6 PM KSA. Reduce Pricing by 10%</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$lwbowsixp->listing_id, 'trigger_type'=>$lwbowsixp->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$lwbowsixp->listing_id}}', '{{$lwbowsixp->price_type}}', '{{$lwbowsixp->days_type}}', {{$lwbowsixp->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $lwbowsixp->listing_id, 'days_type' => $lwbowsixp->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            <!-- DAILY TRIGGER WEEKENDS ONLY ENDS -->
            
            
            <!-- NEXT MONTH TRIGGER STARTS -->
            
            @if(!empty($data['monthly']['thirty_percent']))
                @foreach($data['monthly']['thirty_percent'] as $mthirtypercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$mthirtypercent->jsn_listing_name}}</td>
                        <td>Next Month</td>
                        <td>Next Month Occupancy > 30%. Increase Pricing by 10%. for the month</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$mthirtypercent->listing_id, 'trigger_type'=>$mthirtypercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$mthirtypercent->listing_id}}', '{{$mthirtypercent->price_type}}', '{{$mthirtypercent->days_type}}', {{$mthirtypercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $mthirtypercent->listing_id, 'days_type' => $mthirtypercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            
            @if(!empty($data['monthly']['fivety_percent']))
                @foreach($data['monthly']['fivety_percent'] as $mfivetypercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$mfivetypercent->jsn_listing_name}}</td>
                        <td>Next Month</td>
                        <td>Next Month Occupancy >50%. Increase Pricing by 30% for the month</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$mfivetypercent->listing_id, 'trigger_type'=>$mfivetypercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$mfivetypercent->listing_id}}', '{{$mfivetypercent->price_type}}', '{{$mfivetypercent->days_type}}', {{$mfivetypercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $mfivetypercent->listing_id, 'days_type' => $mfivetypercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            
            @if(!empty($data['monthly']['seventy_percent']))
                @foreach($data['monthly']['seventy_percent'] as $mseventypercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$mseventypercent->jsn_listing_name}}</td>
                        <td>Next Month</td>
                        <td>Next Month Occupancy >70%. Increase Pricing by 100% for the month</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$mseventypercent->listing_id, 'trigger_type'=>$mseventypercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$mseventypercent->listing_id}}', '{{$mseventypercent->price_type}}', '{{$mseventypercent->days_type}}', {{$mseventypercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $mseventypercent->listing_id, 'days_type' => $mseventypercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            <!-- NEXT MONTH TRIGGER ENDS -->
            
            <!-- NEXT WEEK TRIGGER STARTS -->
            
            @if(!empty($data['weekly']['twenty_percent']))
                @foreach($data['weekly']['twenty_percent'] as $wtwentypercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$wtwentypercent->jsn_listing_name}}</td>
                        <td>Next Week</td>
                        <td>Next week Occupancy < 10%. Reduce Pricing by 20% for the week</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$wtwentypercent->listing_id, 'trigger_type'=>$wtwentypercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$wtwentypercent->listing_id}}', '{{$wtwentypercent->price_type}}', '{{$wtwentypercent->days_type}}', {{$wtwentypercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $wtwentypercent->listing_id, 'days_type' => $wtwentypercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            
            @if(!empty($data['weekly']['ten_percent']))
                @foreach($data['weekly']['ten_percent'] as $wtenpercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$wtenpercent->jsn_listing_name}}</td>
                        <td>Next Week</td>
                        <td>Next week Occupancy < 30%. Reduce Pricing by 10% for the week</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$wtenpercent->listing_id, 'trigger_type'=>$wtenpercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$wtenpercent->listing_id}}', '{{$wtenpercent->price_type}}', '{{$wtenpercent->days_type}}', {{$wtenpercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $wtenpercent->listing_id, 'days_type' => $wtenpercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            
            @if(!empty($data['weekly']['fifteen_percent']))
                @foreach($data['weekly']['fifteen_percent'] as $wfifteenpercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$wfifteenpercent->jsn_listing_name}}</td>
                        <td>Next Week</td>
                        <td>Next week Occupancy > 50%. Increase Pricing by 15% for the week</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$wfifteenpercent->listing_id, 'trigger_type'=>$wfifteenpercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$wfifteenpercent->listing_id}}', '{{$wfifteenpercent->price_type}}', '{{$wfifteenpercent->days_type}}', {{$wfifteenpercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $wfifteenpercent->listing_id, 'days_type' => $wfifteenpercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            
            @if(!empty($data['weekly']['thirty_percent']))
                @foreach($data['weekly']['thirty_percent'] as $wthirtypercent)
                    
                    <tr>
                        <td>{{$sr}}</td>
                        <td>{{$wthirtypercent->jsn_listing_name}}</td>
                        <td>Next Week</td>
                        <td>Next week Occupancy > 70%. Increase Pricing by 30% for the week</td>
                        
                        @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$wthirtypercent->listing_id, 'trigger_type'=>$wthirtypercent->days_type])->first(); @endphp
                        <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                        <td>
                            <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$wthirtypercent->listing_id}}', '{{$wthirtypercent->price_type}}', '{{$wthirtypercent->days_type}}', {{$wthirtypercent->percent}})">
                                <em class="icon ni ni-link"></em>
                            </button>
                            
                            @php
                            $tprice = route('revenue-triggers.revert_price', ['listing_id' => $wthirtypercent->listing_id, 'days_type' => $wthirtypercent->days_type]);
                            @endphp
                            <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                <em class="icon ni ni-undo"></em>
                            </button>
                            
                        </td>
                    </tr>
                    
                    @php ++$sr; @endphp
                @endforeach
            @endif
            
            <!-- NEXT WEEK TRIGGER ENDS -->


            <!-- NO BOOKINGS TRIGGER STARTS -->
            
            @if(!empty($data['no_bookings_last_four_days']))
                @foreach($data['no_bookings_last_four_days'] as $nblfourdays)
                    @if(!empty($nblfourdays->jsn_listing_name))
                        <tr>
                            <td>{{$sr}}</td>
                            <td>{{$nblfourdays->jsn_listing_name}}</td>
                            <td>No booking last 4 days</td>
                            <td>No bookings for the last 4 days. Reduce pricing by 20% until booking is received</td>
                            
                            @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$nblfourdays->listing_id, 'trigger_type'=>$nblfourdays->days_type])->first(); @endphp
                            <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                        
                            <td>
                                <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$nblfourdays->listing_id}}', '{{$nblfourdays->price_type}}', '{{$nblfourdays->days_type}}', {{$nblfourdays->percent}})">
                                    <em class="icon ni ni-link"></em>
                                </button>
                                
                                @php
                                $tprice = route('revenue-triggers.revert_price', ['listing_id' => $nblfourdays->listing_id, 'days_type' => $nblfourdays->days_type]);
                                @endphp
                                <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                    <em class="icon ni ni-undo"></em>
                                </button>
                            
                            </td>
                        </tr>
                        
                    @php ++$sr; @endphp
                    @endif
                @endforeach
            @endif
            
            
            @if(!empty($data['no_bookings_last_three_days']))
                @foreach($data['no_bookings_last_three_days'] as $nblthreedays)
                    @if(!empty($nblthreedays->jsn_listing_name))
                        <tr>
                            <td>{{$sr}}</td>
                            <td>{{$nblthreedays->jsn_listing_name}}</td>
                            <td>No booking last 3 days</td>
                            <td>No bookings for the last 3 days. Reduce Pricing by 15% until booking is received</td>
                            
                            @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$nblthreedays->listing_id, 'trigger_type'=>$nblthreedays->days_type])->first(); @endphp
                            <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                            
                            <td>
                                <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$nblthreedays->listing_id}}', '{{$nblthreedays->price_type}}', '{{$nblthreedays->days_type}}', {{$nblthreedays->percent}})">
                                    <em class="icon ni ni-link"></em>
                                </button>
                                
                                @php
                                $tprice = route('revenue-triggers.revert_price', ['listing_id' => $nblthreedays->listing_id, 'days_type' => $nblthreedays->days_type]);
                                @endphp
                                <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                    <em class="icon ni ni-undo"></em>
                                </button>
                                
                            </td>
                        </tr>
                        
                    @php ++$sr; @endphp
                    @endif
                @endforeach
            @endif
            
            
            @if(!empty($data['no_bookings_last_two_days']))
                @foreach($data['no_bookings_last_two_days'] as $nbltwodays)
                    @if(!empty($nbltwodays->jsn_listing_name))
                        <tr>
                            <td>{{$sr}}</td>
                            <td>{{$nbltwodays->jsn_listing_name}}</td>
                            <td>No booking last 2 days</td>
                            <td>No booking for the last 2 days. Reduce Pricing by 10% until booking is received</td>
                            
                            @php $trlog = Illuminate\Support\Facades\DB::table('triggers_prices')->where(['listing_id'=>$nbltwodays->listing_id, 'trigger_type'=>$nbltwodays->days_type])->first(); @endphp
                            <td>{{!is_null($trlog) ? $trlog->trigger_logs : ''}}</td>
                            
                            <td>
                                <button title="Apply" type="button" class="btn btn-primary btn-sm" data-id="1" id="map" onclick="areYouSure('{{$nbltwodays->listing_id}}', '{{$nbltwodays->price_type}}', '{{$nbltwodays->days_type}}', {{$nbltwodays->percent}})">
                                    <em class="icon ni ni-link"></em>
                                </button>
                                
                                @php
                                $tprice = route('revenue-triggers.revert_price', ['listing_id' => $nbltwodays->listing_id, 'days_type' => $nbltwodays->days_type]);
                                @endphp
                                <button title="Revert" type="button" class="btn btn-danger btn-sm" data-id="1" id="map" onclick="revertPrice('{{$tprice}}')">
                                    <em class="icon ni ni-undo"></em>
                                </button>
                                
                            </td>
                        </tr>
                        
                    @php ++$sr; @endphp
                    @endif
                @endforeach
            @endif
            
            
            <!-- NO BOOKINGS TRIGGER ENDS -->
            
            
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    function revertPrice(url){
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log(response);
                
                if(response.success == 1){
                    alert('Price successfully reverted');
                    location.reload();
                }
                
                if(response.success == 0){
                    alert(response.error);
                }
            },
            error: function(xhr, status, error) {
                alert('There was an error processing the request: ' + error);
            }
        });
        
    }

    function updatePrice(listing_id, price_type, days_type, percent){
        $.ajax({
            url: "{{ route('revenue-triggers.store') }}",
            type: 'POST',
            data: {
                "listing_id": listing_id,
                "price_type": price_type,
                "days_type": days_type,
                "percent": percent
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log(response);
                
                if(response.success == 1){
                    alert('Price successfully updated');
                    location.reload();
                }
                
                if(response.success == 0){
                    alert(response.error);
                }
            },
            error: function(xhr, status, error) {
                alert('There was an error processing the request: ' + error);
            }
        });
    }
    
    function areYouSure(listing_id, price_type, days_type, percent){
        
        let userConsent = confirm("Do you want to update the price?");

        if (userConsent) {
            updatePrice(listing_id, price_type, days_type, percent);
        }
    }

</script>
@endsection
