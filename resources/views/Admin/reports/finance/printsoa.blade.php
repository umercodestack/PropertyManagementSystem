<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Statement of Account</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        thead th,
        tfoot td {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: left;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        caption {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .main_btn {
            margin-right: 5px;
            background: #5dc35d;
            padding: 10px;
            border-radius: 25px;
            border: 0;
            cursor: pointer;
            font-size: 15px;
        }

        .plus_btn {
            margin-left: 10px;
            padding: 0px;
            border: 0;
            border-radius: 20px;
            width: 20px;
            height: 20px;
            background: green;
            color: white;
            font-size: 18px;
        }

        .minus_btn {
            margin-left: 10px;
            padding: 0px;
            border: 0;
            border-radius: 20px;
            width: 20px;
            height: 20px;
            background: red;
            color: white;
            font-size: 18px;
        }

        table thead th {
            background: gray;
            color: white !important;
        }
    </style>
</head>

@php
    $extraFields = [
        'A' => 'Deep Cleaning Cost',
        'B' => 'Normal Cleaning Cost',
        'C' => 'Maintenance Cost',
        'D' => 'Supplies',
        'E' => 'Adjustment',
        'F' => 'Item Purchased',
        'G' => 'Damage Adjustment',
    ];
    // Ensure listings is an array
    if (gettype($_GET['listings']) == 'string') {
        $_GET['listings'] = json_decode($_GET['listings'], true);
    }
    // Set default cleaning fee
    $cleaningFee = isset($bookings[0]['cleaning_fee_per_cycle']) ? $bookings[0]['cleaning_fee_per_cycle'] : 0;
@endphp

<body>
    <div style="width:100%;display: flex;text-align:end;flex-direction: row-reverse;margin-top:80px">
        <form method="GET" action="{{ route('reports.finance.soa.downloadSoa') }}" enctype="multipart/form-data">
            <label>
                <input type="hidden" name="user_id" value="{{ $_GET['user_id'] }}">
                <input type="hidden" name="daterange" value="{{ $_GET['daterange'] }}">
                <input type="hidden" name="listings" value="{{ json_encode($_GET['listings']) }}">
                <button type="submit" class="main_btn">Export To PDF</button>
            </label>
        </form>
        <form method="GET" action="{{ route('reports.finance.soa.print.excel') }}" enctype="multipart/form-data">
            <label>
                <input type="hidden" name="user_id" value="{{ $_GET['user_id'] }}">
                <input type="hidden" name="daterange" value="{{ $_GET['daterange'] }}">
                <input type="hidden" name="listings" value="{{ json_encode($_GET['listings']) }}">
                <button type="submit" class="main_btn">Export To Excel</button>
            </label>
        </form>
        <form action="{{ route('reports.finance.soa.resetSoaDetails') }}" method="POST">
            @csrf
            <label>
                <input type="hidden" name="user_id" value="{{ $_GET['user_id'] }}">
                <input type="hidden" name="daterange" value="{{ $_GET['daterange'] }}">
                <input type="hidden" name="listings" value="{{ json_encode($_GET['listings']) }}">
                <button type="submit" class="main_btn">Reset</button>
            </label>
        </form>
    </div>

    <div style="width:100%;display: flex;text-align:end;flex-direction: row-reverse;margin-top:20px">
        <form method="POST" action="{{ route('reports.finance.soa.publish') }}" enctype="multipart/form-data">
            @csrf
            <label>
                <span style="margin-top: 5px;">Publish SOA:</span>
                <input type="text" name="total" value="{{ isset($soa->total) ? $soa->total : '' }}" style="border: 1px solid black; padding: 5px;" required>
                <input
                    type="file"
                    name="soa_file"
                    style="border: 1px solid black; padding: 5px;"
                    accept=".pdf,.jpg,.jpeg"
                    required
                >
                <input type="hidden" name="user_id" value="{{ $_GET['user_id'] }}">
                <input type="hidden" name="daterange" value="{{ $_GET['daterange'] }}">
                <input type="hidden" name="listings" value="{{ json_encode($_GET['listings']) }}">
                <button type="submit"
                    style="padding: 6px; border: 0; background: #a6a6ff; color: black; border-radius: 5px;">Submit</button>
            </label>
        </form>
    </div>

    @isset($soa)
        <div style="width:100%;display: flex;text-align:end;flex-direction: row-reverse;margin-top:20px">
            <a href="{{ asset('storage/' . $soa->file_path) }}" download="download">Download SOA</a>
        </div>
        <div style="width:100%;display: flex;text-align:end;flex-direction: row-reverse;margin-top:20px">
            <form method="POST" action="{{ route('reports.finance.soa.upload.pop') }}" enctype="multipart/form-data">
                @csrf
                <label>
                    <span style="margin-top: 5px;">Upload Proof of Payment:</span>
                    <input type="file" name="pop" style="border: 1px solid black; padding: 5px;"
                        accept=".pdf,.jpg,.jpeg" required>
                    <input type="hidden" name="user_id" value="{{ $_GET['user_id'] }}">
                    <input type="hidden" name="daterange" value="{{ $_GET['daterange'] }}">
                    <input type="hidden" name="listings" value="{{ json_encode($_GET['listings']) }}">
                    <button type="submit"
                        style="padding: 6px; border: 0; background: #a6a6ff; color: black; border-radius: 5px;">Submit</button>
                </label>
            </form>
        </div>
        @php
            $soaPop = \App\Models\ReportFinanceSoaPop::where('soa_id', $soa->id)->first();
        @endphp
        @isset($soaPop)
            <div style="width:100%;display: flex;text-align:end;flex-direction: row-reverse;margin-top:20px">
                <a href="{{ asset('storage/' . $soaPop->file_path) }}" download="download">Download Pop</a>
            </div>
        @endisset
    @endisset

    <table style="margin-bottom:10px;margin-top:10px;" id="logoTable">
        <tbody>
            <tr>
                <td colspan="18" style="display: flex;align-items: center;">
                    <img src="{{ asset('assets/images/livedinlogoexcel.png') }}" style="width: 10%" alt="">
                    &nbsp;<span style="font-size: 18px;font-weight: 600;">LIVEDIN HOLDINGS LIMITED</span>
                </td>
            </tr>
        </tbody>
    </table>
    <table id="adressDetails">
        <tbody>
            <tr>
                <td>Address #:</td>
                <td>Prince Turki Bin Abdul Aziz</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Phone:</td>
                <td>+966115115798</td>
            </tr>
            <tr>
                <td></td>
                <td>Al Awal Road, Al Raeed Riyadh</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>12354, Saudi Arabia</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Email:</td>
                <td>operations@livedin.co</td>
            </tr>
        </tbody>
    </table>
    <br>
    <table id="statementDetails">
        <caption>Statement of Account</caption>
        <tbody>
            <tr>
                <td>Statement #:</td>
                <td>{{ isset($soa) && $soa->publish_date ? str_replace('-', '', \Carbon\Carbon::parse($soa->publish_date)->format('dmY')) . '-' . $host->host_key : \Carbon\Carbon::now()->format('dmY') . '-' . $host->host_key }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Host- {{ $host->name }} {{ $host->surname }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td>{{ isset($soa) && $soa->publish_date ? \Carbon\Carbon::parse($soa->publish_date)->format('F d, Y') : \Carbon\Carbon::now()->format('F d, Y') }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Riyadh</td>
            </tr>
            <tr>
                <td>Host ID:</td>
                <td>{{ $host->host_key }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Statement Period:</td>
                <td>{{ str_replace(['/', ' - '], ['-', ' to '], $_GET['daterange']) }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <form method="POST" action="{{ route('reports.finance.soa.saveSoaDetails') }}" enctype="multipart/form-data">
        @csrf

    @php
        if(isset($bookings) && count($bookings) > 0) {
    @endphp
    <table id="statementTableLivedin">
        <caption></caption>
        <thead>
            <tr>
                <th>CheckInDate</th>
                <th>Check Out Date</th>
                <th>No. of Nights</th>
                <th>Apartment No.</th>
                <th>Booking Status</th>
                <th>Payment Collected by</th>
                <th>Booking Source</th>
                <th>Booking ID</th>
                <th>Guest Name</th>
                <th>Night Rate</th>
                <th>Booking Amount (SAR)</th>
                <th>Discounts (SAR)</th>
                <th>Post Discounts Booking Amount (SAR)</th>
                <th>OTA FEE (SAR)</th>
                <th>Livedin Share (SAR)</th>
                <th>Host Share (SAR)</th>
                <th>Forex Adjustment (SAR)</th> <!-- New column -->
                <th>Post Forex Host Share (SAR)</th> <!-- New column -->
                <th>Cleaning</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRevLived = 0;
                $discLived = 0;
                $postDiscountLived = 0;
                $otaCommissionLived = 0;
                $livedinCommissionLived = 0;
                $hostCommissionLived = 0;
                $forexAdjustmentLived = 0; // New variable
                $postForexHostShareLived = 0; // New variable
            @endphp
            <tr>
                <td colspan="19" style="background-color: #e1e1e1">
                    <h3>Payment Collected by Livedin</h3>
                </td>
            </tr>
            @foreach ($bookings as $booking)
            @php
                $totalRevLived += $booking['total'];
                $discLived += $booking['discount'];
                $postDiscountLived += $booking['post_discount_booking_amount'];
                $otaCommissionLived += $booking['ota_commission'];
                $livedinCommissionLived += $booking['livedin_commission'];
                $hostCommissionLived += $booking['host_commission'];
                $forexAdjustmentLived += $booking['forex_adjustment']; // New sum
                $postForexHostShareLived += $booking['post_forex_host_share']; // New sum
                $listing = \App\Models\Listings::where('listing_id', $booking['listing_id'])->first();
            @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($booking['start_date'])->format('d-M-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking['end_date'])->format('d-M-y') }}</td>
                    <td>{{ $booking['nights'] }}</td>
                    <td>{{ $listing->apartment_num }}</td>
                    <td>{{ $booking['status'] }}</td>
                    <td>Livedin</td>
                    <td>{{ $booking['type'] == '' ? 'AirBnb' : $booking['type']}}</td>
                    <td>{{ $booking['booking_id'] }}</td>
                    <td>{{ $booking['name'] }}</td>
                    <td>{{ $booking['night_rate'] }}</td>
                    <td>{{ $booking['total'] }}</td>
                    <td>{{ $booking['discount'] }}</td>
                    <td>{{ $booking['post_discount_booking_amount'] }}</td>
                    <td>{{ $booking['ota_commission'] }}</td>
                    <td>{{ $booking['livedin_commission'] }}</td>
                    <td>{{ $booking['host_commission'] }}</td>
                    <td>{{ $booking['forex_adjustment'] }}</td> <!-- New column -->
                    <td>{{ $booking['post_forex_host_share'] }}</td> <!-- New column -->
                    <td>{{ $booking['total_cleaning'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td>{{ $totalRevLived }}</td>
                <td>{{ $discLived }}</td>
                <td>{{ $postDiscountLived }}</td>
                <td>{{ $otaCommissionLived }}</td>
                <td>{{ $livedinCommissionLived }}</td>
                <td>{{ $hostCommissionLived }}</td>
                <td>{{ $forexAdjustmentLived }}</td> <!-- New total -->
                <td>{{ $postForexHostShareLived }}</td> <!-- New total -->
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Sales Tax (SAR)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="number" name="details[livedin][sales_tax]" onkeyup="minusSalesTax(this.value)" value="{{ isset($soaDetails[0]['value']) ? $soaDetails[0]['value']: 0 }}"><span></span></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <input type="hidden" id="hostTotalAfterSalesTaxNumber" name="details[livedin][total_rev_sales]" value="{{ isset($soaDetails[1]['value']) ? $soaDetails[1]['value']: $hostCommissionLived }}">
                    <span id="hostTotalAfterSalesTax">{{ isset($soaDetails[1]['value']) ? $soaDetails[1]['value']: $hostCommissionLived }}</span>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        <tfoot>
            @php $i=2; @endphp
            @foreach ( $extraFields as  $key=>$extraField )
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $key }}</td>
                <td>{{ $extraField }} <input type="hidden" name="details[livedin][{{ $key }}][head_type]" value="{{ isset($soaDetails[$i]['head_type']) ? $soaDetails[$i]['head_type']: $extraField  }}"></td>
                <td><input type="text" name="details[livedin][{{ $key }}][comment]" value="{{ isset($soaDetails[$i]['comment']) ? $soaDetails[$i]['comment']: '' }}"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="number" name="details[livedin][{{ $key }}][value]" class="calculation" onkeyup="minusExtraValues(this.value)" value="{{ isset($soaDetails[$i]['value']) ? $soaDetails[$i]['value']: 0 }}"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @php $i++; @endphp
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>E=A-B-C-D-E-F-G-H-I-J-K-L</td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <span id="hostTotalAfterSalesTaxTotal">{{ $hostCommissionLived }}</span>
                    <input type="hidden" id="hostTotalAfterSalesTaxTotalNumber" name="details[livedin][total_livedIn]" value="{{ $hostCommissionLived }}">
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @if(count($bookingsCod) < 1)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>E=D-Z</td>
                <td><strong> Final amount to be paid to HOST </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <input type="hidden" id="final_amount_paid_to_host" name="details[final_amount_paid_to_host]" value="{{ $hostCommissionLived - 0 }}">
                    <span id="total">{{ $hostCommissionLived - 0 }}</span>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endif
        </tfoot>
    </table>
    @php
    }
    @endphp
    @php
        if(isset($bookingsCod) && count($bookingsCod) > 0) {
    @endphp
    <table id="statementTableHost">
        <caption>Statement of Account</caption>
        <thead>
            <tr>
                <th>CheckInDate</th>
                <th>Check Out Date</th>
                <th>No. of Nights</th>
                <th>Apartment No.</th>
                <th>Booking Status</th>
                <th>Payment Collected by</th>
                <th>Booking Source</th>
                <th>Booking ID</th>
                <th>Guest Name</th>
                <th>Night Rate</th>
                <th>Booking Amount (SAR)</th>
                <th>Discounts (SAR)</th>
                <th>Post Discounts Booking Amount (SAR)</th>
                <th>OTA FEE (SAR)</th>
                <th>Livedin Share (SAR)</th>
                <th>Host Share (SAR)</th>
                <th>Forex Adjustment (SAR)</th> <!-- New column -->
                <th>Post Forex Host Share (SAR)</th> <!-- New column -->
                <th>Cleaning</th>
            </tr>
        </thead>
        <tbody id="payedToHostTBody">
            <tr>
                <td colspan="19" style="background-color: #e1e1e1">
                    <h3>Payment Collected by Host</h3>
                </td>
            </tr>
            @php
                $totalRevHost = 0;
                $discHost = 0;
                $postDiscountHost = 0;
                $otaCommissionHost = 0;
                $livedinCommissionHost = 0;
                $hostCommissionHost = 0;
                $forexAdjustmentHost = 0; // New variable
                $postForexHostShareHost = 0; // New variable
            @endphp
            @foreach ($bookingsCod as $booking)
                @php
                    $totalRevHost += $booking['total'];
                    $discHost += $booking['discount'];
                    $postDiscountHost += $booking['post_discount_booking_amount'];
                    $otaCommissionHost += $booking['ota_commission'];
                    $livedinCommissionHost += $booking['livedin_commission'];
                    $hostCommissionHost += $booking['host_commission'];
                    $forexAdjustmentHost += $booking['forex_adjustment']; // New sum
                    $postForexHostShareHost += $booking['post_forex_host_share']; // New sum
                    $listing = \App\Models\Listings::where('listing_id', $booking['listing_id'])->first();
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($booking['start_date'])->format('d-M-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking['end_date'])->format('d-M-y') }}</td>
                    <td>{{ $booking['nights'] }}</td>
                    <td>{{ $listing->apartment_num }}</td>
                    <td>{{ $booking['status'] }}</td>
                    <td>Livedin</td>
                    <td>{{ $booking['type'] == '' ? 'AirBnb' : $booking['type']}}</td>
                    <td>{{ $booking['booking_id'] }}</td>
                    <td>{{ $booking['name'] }}</td>
                    <td>{{ $booking['night_rate'] }}</td>
                    <td>{{ $booking['total'] }}</td>
                    <td>{{ $booking['discount'] }}</td>
                    <td>{{ $booking['post_discount_booking_amount'] }}</td>
                    <td>{{ $booking['ota_commission'] }}</td>
                    <td>{{ $booking['livedin_commission'] }}</td>
                    <td>{{ $booking['host_commission'] }}</td>
                    <td>{{ $booking['forex_adjustment'] }}</td> <!-- New column -->
                    <td>{{ $booking['post_forex_host_share'] }}</td> <!-- New column -->
                    <td>{{ $booking['total_cleaning'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><strong>Total Revenue (SAR)</strong></td>
                <td></td>
                <td>{{ $totalRevHost }}</td>
                <td>{{ $discHost }}</td>
                <td>{{ $postDiscountHost }}</td>
                <td>{{ $otaCommissionHost }}</td>
                <td>{{ $livedinCommissionHost }}</td>
                <td>{{ $hostCommissionHost }}</td>
                <td>{{ $forexAdjustmentHost }}</td> <!-- New total -->
                <td>{{ $postForexHostShareHost }}</td> <!-- New total -->
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Sales Tax (SAR)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="number" name="details[host][sales_tax]" onkeyup="minusSalesTaxTwo(this.value)" value="{{ isset($soaDetails[10]['value']) ? $soaDetails[10]['value']: 0 }}"><span></span></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td>{{ $totalRevHost }}</td>
                <td>{{ $discHost }}</td>
                <td>{{ $postDiscountHost }}</td>
                <td>{{ $otaCommissionHost }}</td>
                <td>
                    <input type="hidden" id="hostTotalAfterSalesTaxTwoNumber" name="details[host][total_rev_sales]" value="{{ isset($soaDetails[11]['value']) ? $soaDetails[11]['value']: $livedinCommissionHost }}">
                    <span id="hostTotalAfterSalesTaxTwo">{{ isset($soaDetails[11]['value']) ? $soaDetails[11]['value']: $livedinCommissionHost }}</span>
                </td>
                <td><span>{{ $hostCommissionHost }}</span></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        <tfoot>
            @php $i=12; @endphp
            @foreach ( $extraFields as $key=>$extraField )
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $key }}</td>
                <td>{{ $extraField }} <input type="hidden" name="details[host][{{ $key }}][head_type]" value="{{ isset($soaDetails[$i]['head_type']) ? $soaDetails[$i]['head_type']: $extraField }}"></td>
                <td><input type="text" name="details[host][{{ $key }}][comment]" value="{{ isset($soaDetails[$i]['comment']) ? $soaDetails[$i]['comment']: '' }}"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="number" name="details[host][{{ $key }}][value]" class="calculationTwo" onkeyup="minusExtraValuesTwo(this.value)" value="{{ isset($soaDetails[$i]['value']) ? $soaDetails[$i]['value']: 0 }}"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @php $i++; @endphp
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>E=A-B-C-D-E-F-G-H-I-J-K-L</td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <input type="hidden" id="hostTotalAfterSalesTaxTotalTwoNumber" name="details[host][total_livedIn]" value="{{ isset($soaDetails[19]['value']) ? $soaDetails[19]['value']: $livedinCommissionHost }}">
                    <span id="hostTotalAfterSalesTaxTotalTwo">{{ isset($soaDetails[19]['value']) ? $soaDetails[19]['value']: $livedinCommissionHost }}</span>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>E=D-Z</td>
                <td><strong> Final amount to be paid to HOST </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <input type="hidden" id="final_amount_paid_to_host" name="details[final_amount_paid_to_host]" value="{{ isset($hostCommissionHost) ? $hostCommissionHost : 0 - $livedinCommissionHost }}">
                    <span id="total">{{ isset($hostCommissionHost) ? $hostCommissionHost : 0 - $livedinCommissionHost }}</span>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @php
    }
    @endphp

    <div style="width: 100%; text-align: end; margin-top: 10px;">
        <label>
            <input type="hidden" name="user_id" value="{{ $_GET['user_id'] }}">
            <input type="hidden" name="daterange" value="{{ $_GET['daterange'] }}">
            <input type="hidden" name="listings" value="{{ json_encode($_GET['listings']) }}">
            <button type="submit" class="main_btn">Publish</button>
        </label>
    </div>
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    // Set the initial cleaning value but allow it to be subtracted
    const cleaningPerCycle = {{ $cleaning_per_cycle ?? 0 }};
    document.querySelector('[name="details[livedin][B][value]"]').value = cleaningPerCycle;

    let hostTotalAfterSalesTax = $('#hostTotalAfterSalesTax').text();
    let hostTotalAfterSalesTaxTwo = $('#hostTotalAfterSalesTaxTwo').text();
    let hostTotalAfterSalesTaxTotal = $('#hostTotalAfterSalesTaxTotal').text();
    let hostTotalAfterSalesTaxTotalTwo = $('#hostTotalAfterSalesTaxTotalTwo').text();

    function minusSalesTax(sales_tax) {
        $('#hostTotalAfterSalesTax').text(Number(hostTotalAfterSalesTax) + Number(sales_tax));
        $('#hostTotalAfterSalesTaxNumber').val(Number(hostTotalAfterSalesTax) + Number(sales_tax));
        $('#hostTotalAfterSalesTaxAA').text(Number(hostTotalAfterSalesTax) + Number(sales_tax));
        $('#hostTotalAfterSalesTaxTotal').text(Number(hostTotalAfterSalesTax) + Number(sales_tax));
        hostTotalAfterSalesTaxTotal = $('#hostTotalAfterSalesTaxTotal').text();
        $('#hostTotalAfterSalesTaxTotalNumber').val($('#hostTotalAfterSalesTaxTotal').text());
        minusExtraValues();
        updateRowTotals();
    }

    function minusSalesTaxTwo(sales_tax) {
        $('#hostTotalAfterSalesTaxTwo').text(Number(hostTotalAfterSalesTaxTwo) + Number(sales_tax));
        $('#hostTotalAfterSalesTaxTotalTwo').text(Number(hostTotalAfterSalesTaxTwo) + Number(sales_tax));
        hostTotalAfterSalesTaxTotalTwo = $('#hostTotalAfterSalesTaxTotalTwo').text();
        $('#hostTotalAfterSalesTaxTwoNumber').val($('#hostTotalAfterSalesTaxTotalTwo').text());
        $('#hostTotalAfterSalesTaxTotalTwoNumber').val($('#hostTotalAfterSalesTaxTotalTwo').text());
        minusExtraValuesTwo();
        updateRowTotals();
    }

    function minusExtraValues() {
        let initialValue = 0;
        $('.calculation').each(function () {
            let val = parseFloat($(this).val()) || 0;
            initialValue += val;
        });
        // Subtract cleaningPerCycle from hostTotalAfterSalesTaxTotal
        let newTotal = Number(hostTotalAfterSalesTaxTotal) + Number(initialValue) - cleaningPerCycle;
        $('#hostTotalAfterSalesTaxTotal').text(newTotal);
        $('#hostTotalAfterSalesTaxTotalNumber').val(newTotal);
        updateRowTotals();
    }

    function minusExtraValuesTwo() {
        let initialValue = 0;
        $('.calculationTwo').each(function () {
            let val = parseFloat($(this).val()) || 0;
            initialValue += val;
        });
        $('#hostTotalAfterSalesTaxTotalTwo').text(Number(hostTotalAfterSalesTaxTotalTwo) + Number(initialValue));
        $('#hostTotalAfterSalesTaxTotalTwoNumber').val(Number(hostTotalAfterSalesTaxTotalTwo) + Number(initialValue));
        updateRowTotals();
    }

    function updateRowTotals() {
        // Update totals for Payment Collected by Livedin
        $('#statementTableLivedin tbody tr').each(function () {
            let $row = $(this);
            let hostCommission = parseFloat($row.find('td:eq(15)').text()) || 0; // Host Share (SAR) column
            let forexAdjustment = parseFloat($row.find('td:eq(16)').text()) || 0; // Forex Adjustment (SAR) column
            let livedinCommission = parseFloat($row.find('td:eq(14)').text()) || 0; // Livedin Share (SAR) column
            let extraValues = 0;
            $row.nextUntil('tr:has(td[colspan="19"])').find('.calculation').each(function () {
                extraValues += parseFloat($(this).val()) || 0;
            });
            let postForexHostShare = hostCommission - forexAdjustment; // Calculate Post Forex Host Share
            let finalAmount = postForexHostShare - livedinCommission - extraValues;
            if ($row.is(':last-child')) {
                $row.find('td:last').text(finalAmount.toFixed(2)); // Update Cleaning column
            } else {
                $row.find('td:eq(17)').text(postForexHostShare.toFixed(2)); // Update Post Forex Host Share column
            }
        });

        // Update totals for Payment Collected by Host
        $('#statementTableHost tbody tr').each(function () {
            let $row = $(this);
            let hostCommission = parseFloat($row.find('td:eq(15)').text()) || 0; // Host Share (SAR) column
            let forexAdjustment = parseFloat($row.find('td:eq(16)').text()) || 0; // Forex Adjustment (SAR) column
            let livedinCommission = parseFloat($row.find('td:eq(14)').text()) || 0; // Livedin Share (SAR) column
            let extraValues = 0;
            $row.nextUntil('tr:has(td[colspan="19"])').find('.calculationTwo').each(function () {
                extraValues += parseFloat($(this).val()) || 0;
            });
            let postForexHostShare = hostCommission - forexAdjustment; // Calculate Post Forex Host Share
            let finalAmount = postForexHostShare - livedinCommission - extraValues;
            if ($row.is(':last-child')) {
                $row.find('td:last').text(finalAmount.toFixed(2)); // Update Cleaning column
            } else {
                $row.find('td:eq(17)').text(postForexHostShare.toFixed(2)); // Update Post Forex Host Share column
            }
        });
    }

    function total() {
        let totalLivedin = parseFloat($('#hostTotalAfterSalesTaxTotal').text()) || 0;
        let totalHost = parseFloat($('#hostTotalAfterSalesTaxTotalTwo').text()) || 0;
        // Subtract cleaningPerCycle from the overall totalLivedin
        let finalTotal = (totalLivedin - totalHost) - cleaningPerCycle;
        $('#total').text(finalTotal.toFixed(2));
        $('#final_amount_paid_to_host').val(finalTotal.toFixed(2));
    }

    $(document).ready(function () {
        updateRowTotals(); // Initialize row totals on page load
        $('.calculation, .calculationTwo').on('keyup', updateRowTotals); // Update on input change
    });

    async function exportToPDFSinglePage() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: "landscape",
            unit: "pt",
            format: "a4"
        });

        let currentY = 40;

        pdf.setFontSize(16);
        pdf.text("Statement of Account", 40, currentY);
        currentY += 20;

        const logoTable = document.getElementById("logoTable");
        pdf.autoTable({
            html: logoTable,
            startY: currentY,
            theme: "grid",
            styles: {
                fontSize: 8,
                cellPadding: 4
            },
            headStyles: {
                fillColor: [240, 240, 240],
                textColor: [0, 0, 0]
            },
        });
        currentY = pdf.lastAutoTable.finalY + 10;

        const statementDetails = document.getElementById("statementDetails");
        pdf.autoTable({
            html: statementDetails,
            startY: currentY,
            theme: "grid",
            styles: {
                fontSize: 8,
                cellPadding: 4
            },
            headStyles: {
                fillColor: [240, 240, 240],
                textColor: [0, 0, 0]
            },
        });
        currentY = pdf.lastAutoTable.finalY + 20;

        pdf.setFontSize(14);
        pdf.text("Livedin Statement", 40, currentY);
        currentY += 10;
        const statementTableLivedin = document.getElementById("statementTableLivedin");
        pdf.autoTable({
            html: statementTableLivedin,
            startY: currentY,
            theme: "grid",
            styles: {
                fontSize: 8,
                cellPadding: 4
            },
            headStyles: {
                fillColor: [0, 102, 204],
                textColor: [255, 255, 255]
            },
        });
        currentY = pdf.lastAutoTable.finalY + 20;

        pdf.setFontSize(14);
        pdf.text("Host Statement", 40, currentY);
        currentY += 10;
        const statementTableHost = document.getElementById("statementTableHost");
        pdf.autoTable({
            html: statementTableHost,
            startY: currentY,
            theme: "grid",
            styles: {
                fontSize: 8,
                cellPadding: 4
            },
            headStyles: {
                fillColor: [0, 102, 204],
                textColor: [255, 255, 255]
            },
        });

        pdf.save("Statement_of_Account.pdf");
    }

    function exportToExcel() {
        const workbook = XLSX.utils.book_new();
        const combinedData = [];

        const logoTable = document.getElementById("logoTable");
        const logoData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(logoTable), { header: 1 });
        combinedData.push(...logoData, []);

        const statementDetailsTable = document.getElementById("statementDetails");
        const statementDetailsData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(statementDetailsTable), { header: 1 });
        combinedData.push(...statementDetailsData, []);

        const livedinTable = document.getElementById("statementTableLivedin");
        const livedinData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(livedinTable), { header: 1 });
        combinedData.push(["Livedin Statement"]);
        combinedData.push(...livedinData, []);

        const hostTable = document.getElementById("statementTableHost");
        const hostData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(hostTable), { header: 1 });
        combinedData.push(["Host Statement"]);
        combinedData.push(...hostData, []);

        const sheet = XLSX.utils.aoa_to_sheet(combinedData);

        const range = XLSX.utils.decode_range(sheet['!ref']);
        for (let R = range.s.r; R <= range.e.r; ++R) {
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
                if (!sheet[cellAddress]) sheet[cellAddress] = { v: "" };
                if (!sheet[cellAddress].s) sheet[cellAddress].s = {};

                sheet[cellAddress].s.border = {
                    top: { style: "thin", color: { rgb: "000000" } },
                    bottom: { style: "thin", color: { rgb: "000000" } },
                    left: { style: "thin", color: { rgb: "000000" } },
                    right: { style: "thin", color: { rgb: "000000" } },
                };

                sheet[cellAddress].s.alignment = { horizontal: "center", vertical: "center" };
            }
        }

        XLSX.utils.book_append_sheet(workbook, sheet, "Statement of Account");
        XLSX.writeFile(workbook, "Statement_of_Account.xlsx");
    }
</script>
</html>