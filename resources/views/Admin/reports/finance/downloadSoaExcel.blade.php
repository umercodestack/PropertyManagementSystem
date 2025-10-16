<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Statement of Account</title>
    <style>
         @media print {
      /* Hide the print button in the print output */
      .no-print {
        display: none;
      }

      /* Set print orientation to landscape */
      @page {
        size: landscape;
      }
    }

    body {
      font-family: Arial, sans-serif;
    }
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        thead th, tfoot td {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: left;
        }
        th, td {
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
        table thead th{
            background: gray;
            color: white!important
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
    ]
@endphp
<body >

    <table style="margin-bottom:10px;margin-top:10px;" id="logoTable">
        <tbody>
            <tr>
                <td colspan=11 style="display: flex;align-items: center;">
                    <img src="{{ public_path('assets/images/livedinlogoexcel.png') }}" style="width: 10%" alt="">
                </td>
                <td colspan="4"><span style="font-size: 25px;font-weight: 600;">LIVEDIN HOLDINGS LIMITED</span></td>
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
                <td>Email:</td>
                <td>operations@livedin.co</td>
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
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <br>
    {{-- {{ dd($bookings, $bookingsCod) }} --}}
    {{-- {{ dd($soaDetails) }} --}}
    <table id="statementDetails">
        <caption>Statement of Account</caption>
        <tbody>
            <tr>
                <td>Statement #:</td>
                <td>{{ isset($soa) && $soa->publish_date ? str_replace('-', '',\Carbon\Carbon::parse($soa->publish_date)->format('dmY')).'-'.$host->host_key : \Carbon\Carbon::now()->format('dmY').'-'.$host->host_key }}</td>
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
                <td>{{ isset($soa) && $soa->publish_date ? \Carbon\Carbon::parse($soa->publish_date)->format('F d, Y') : \Carbon\Carbon::now()->format('F d, Y') }}</td>
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


    {{-- {{ dd($bookings) }} --}}

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
                <th>OTA FEE (SAR)</th>
                <th>Livedin Share (SAR)</th>
                <th>Host Share (SAR)</th>
            </tr>
        </thead>
        <tbody>

            @php
                $totalRevLived = 0;
                $discLived = 0;
                $otaCommissionLived = 0;
                $livedinCommissionLived = 0;
                $hostCommissionLived = 0;
            @endphp
            <tr>
                <td colspan="15">
                    <h3>Payment Collected by Livedin</h3>
                </td>
            </tr>
            @foreach ($bookings as $booking)
            @php
                $totalRevLived +=  $booking['total'];
                $discLived +=  $booking['discount'];
                $otaCommissionLived +=  $booking['ota_commission'];
                $livedinCommissionLived +=  $booking['livedin_commission'];
                $hostCommissionLived +=  $booking['host_commission'];
                $listing = \App\Models\Listings::where('listing_id', $booking['listing_id'])->first();
                // dd($listing);
            @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($booking['start_date'])->format('d-M-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking['end_date'])->format('d-M-y') }}</td>
                    <td>{{ $booking['nights'] }}</td>
                    <td>{{ $listing->apartment_num }}</td>
                    <td>{{ $booking['status'] == 'cancelled' ? 'Cancelled' : "Checked-out" }}</td>
                    <td>Livedin</td>
                    <td>{{ $booking['type'] == '' ? 'AirBnb' : $booking['type']}}</td>
                    <td>{{ $booking['booking_id'] }}</td>
                    <td>{{ $booking['name'] }}</td>
                    <td>{{ $booking['night_rate'] }}</td>
                    <td>{{ $booking['total'] }}</td>
                    <td>{{ $booking['discount'] }}</td>
                    <td>{{ $booking['ota_commission'] }}</td>
                    <td>{{ $booking['livedin_commission'] }}</td>
                    <td>{{ $booking['host_commission'] }}</td>
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
                <td>{{ $otaCommissionLived }}</td>
                <td>{{ $livedinCommissionLived }}</td>
                <td><span>{{ $hostCommissionLived }}</span></td>
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
                <td>
                    <span>
                        {{ isset($soaDetails[0]['value']) ? $soaDetails[0]['value']: 0 }}
                    </span>
                </td>
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
                <td>
                    <input type="hidden" id="hostTotalAfterSalesTaxNumber" name="details[livedin][total_rev_sales]" value="{{ isset($soaDetails[1]['value']) ? $soaDetails[1]['value']: $hostCommissionLived }}">
                    <span id="hostTotalAfterSalesTax">{{ isset($soaDetails[1]['value']) ? $soaDetails[1]['value']: $hostCommissionLived }}</span>
                </td>
            </tr>
        </tbody>
        <tfoot>
            @php
                 $alphas = ['A','B','C','D','E','F','G','H'];
                 $i=2;
                 $j=0;
            @endphp
            @foreach ( $extraFields as  $key=>$extraField )
            @if ($soaDetails[$i]['value'] != 0)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $alphas[$j++] }}</td>
                <td>{{ $extraField }} <input type="hidden" name="details[livedin][{{ $key }}][head_type]" value="{{ isset($soaDetails[$i]['head_type']) ? $soaDetails[$i]['head_type']: $extraField  }}"></td>
                <td>
                    {{ isset($soaDetails[$i]['comment']) ? $soaDetails[$i]['comment']: '' }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    {{ isset($soaDetails[$i]['value']) ? $soaDetails[$i]['value']: 0 }}
                </td>
            </tr>

            @endif
            @php $i++; @endphp
            @endforeach
            {{-- {{ dd($j) }} --}}
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    @php
                        $finalAlphas = array_slice($alphas, 0, $j);
                    @endphp
                    H={{ implode('-', $finalAlphas) }}
                </td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <span id="hostTotalAfterSalesTaxTotal">{{ $hostCommissionLived }}</span>
                    <input type="hidden" id="hostTotalAfterSalesTaxTotalNumber" name="details[livedin][total_livedIn]" value="{{ $hostCommissionLived }}">
                </td>
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
                <td>
                    <input type="hidden" id="final_amount_paid_to_host" name="details[final_amount_paid_to_host]" value="{{ $hostCommissionLived -  0 }}">

                    <span id="total">
                    {{ $hostCommissionLived -  0 }}
                </span></td>
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
                <th>OTA FEE (SAR)</th>
                <th>Livedin Share (SAR)</th>
                <th>Host Share (SAR)</th>
            </tr>
        </thead>
        <tbody id="payedToHostTBody">
            <tr>
                <td colspan="15">
                    <h3>Payment Collected by Host</h3>
                </td>
            </tr>
            @php
                $totalRevHost = 0;
                $discHost = 0;
                $otaCommissionHost = 0;
                $livedinCommissionHost = 0;
                $hostCommissionHost = 0;
            @endphp
            @foreach ($bookingsCod as $booking)
                @php
                    $totalRevHost +=  $booking['total'];
                    $discHost +=  $booking['discount'];
                    $otaCommissionHost +=  $booking['ota_commission'];
                    $livedinCommissionHost +=  $booking['livedin_commission'];
                    $hostCommissionHost +=  $booking['host_commission'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($booking['start_date'])->format('d-M-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking['end_date'])->format('d-M-y') }}</td>
                    <td>{{ $booking['nights'] }}</td>
                    <td>{{ $listing->apartment_num }}</td>
                    <td>{{ $booking['status'] == 'cancelled' ? 'Cancelled' : "Checked-out" }}</td>
                    <td>Livedin</td>
                    <td>{{ $booking['type'] == '' ? 'AirBnb' : $booking['type']}}</td>
                    <td>{{ $booking['booking_id'] }}</td>
                    <td>{{ $booking['name'] }}</td>
                    <td>{{ $booking['night_rate'] }}</td>
                    <td>{{ $booking['total'] }}</td>
                    <td>{{ $booking['discount'] }}</td>
                    <td>{{ $booking['ota_commission'] }}</td>
                    <td>{{ $booking['livedin_commission'] }}</td>
                    <td>{{ $booking['host_commission'] }}</td>
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
                    <td>{{ $otaCommissionHost }}</td>
                    <td>{{ $livedinCommissionHost }}</td>
                    <td>{{ $hostCommissionHost }}</td>
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
                    <td>
                        {{ isset($soaDetails[10]['value']) ? $soaDetails[10]['value']: 0 }}
                    </td>
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
                    <td>{{ $otaCommissionHost }}</td>
                    <td>
                        <input type="hidden" id="hostTotalAfterSalesTaxTwoNumber" name="details[host][total_rev_sales]" value="{{ isset($soaDetails[11]['value']) ? $soaDetails[11]['value']: $livedinCommissionHost }}">

                        <span id="hostTotalAfterSalesTaxTwo">{{ isset($soaDetails[11]['value']) ? $soaDetails[11]['value']: $livedinCommissionHost }}</span>
                    </td>
                    <td><span >{{ $hostCommissionHost }}</span></td>
                </tr>
        </tbody>

        <tfoot>
            @php
                $i=12;
                $alphasHost = ['S','T','U','V','W','X','Y','Z'];
                 $i=2;
                 $k=0;
            @endphp
            @foreach ( $extraFields as  $key=>$extraField )
            @if ($soaDetails[$i]['value'] != 0)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $alphasHost[$k++] }}</td>
                <td>{{ $extraField }} <input type="hidden" name="details[host][{{ $key }}][head_type]" value="{{ isset($soaDetails[$i]['head_type']) ? $soaDetails[$i]['head_type']: $extraField }}"></td>
                <td>
                    {{ isset($soaDetails[$i]['comment']) ? $soaDetails[$i]['comment']: '' }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    {{ isset($soaDetails[$i]['value']) ? $soaDetails[$i]['value']: 0 }}
                </td>
            </tr>
            @endif
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
                <td>
                    @php
                    $finalAlphas = array_slice($alphasHost, 0, $j);
                @endphp
                    M={{ implode('-', $finalAlphas) }}
                </td>
                <td><strong> Total Revenue (Incl. Sales Tax)-(SAR) </strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <input type="hidden" id="hostTotalAfterSalesTaxTotalTwoNumber" name="details[host][total_livedIn]" value="{{ isset($soaDetails[19]['value']) ? $soaDetails[19]['value']: $livedinCommissionHost }}">
                    <span id="hostTotalAfterSalesTaxTotalTwo">{{ isset($soaDetails[19]['value']) ? $soaDetails[19]['value']: $livedinCommissionHost }}</span>
                </td>
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
                <td>
                    <input type="hidden" id="final_amount_paid_to_host" name="details[final_amount_paid_to_host]" value="{{ $hostCommissionLived - $livedinCommissionHost }}">

                    <span id="total">
                    {{ $hostCommissionLived - $livedinCommissionHost }}
                </span></td>
            </tr>
        </tfoot>
        {{-- <tfoot>
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
                <td>{{ $extraField }}</td>
                <td><input type="text" name="comment"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="number" name="calculation" onkeyup="minusSalesTax(this.value)"></td>
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
                <td>{{ $totalRevHost }}</td>
                <td>{{ $discHost }}</td>
                <td>{{ $otaCommissionHost }}</td>
                <td>{{ $livedinCommissionHost }}</td>
                <td><span id="hostTotalAfterSalesTax">{{ $hostCommissionHost }}</span></td>
            </tr>
        </tfoot> --}}
    </table>
    @php
    }
    @endphp

</body>

</html>
