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
        size: A2 landscape;
        margin: 0;
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
<body onload="window.print()">
    <button class="no-print" class="" onclick="window.print()">Print</button>

    <table style="margin-bottom:10px;margin-top:10px;" id="logoTable">
        <tbody>
            <tr>
                <td colspan=15 style="display: flex;align-items: center;">
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
                <td colspan="15" style="background-color: #e1e1e1">
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
        </tbody>
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
                <td colspan="15" style="background-color: #e1e1e1">
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
    </form>

</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    let hostTotalAfterSalesTax = $('#hostTotalAfterSalesTax').text();
    let hostTotalAfterSalesTaxTwo = $('#hostTotalAfterSalesTaxTwo').text();
    let hostTotalAfterSalesTaxTotal = $('#hostTotalAfterSalesTaxTotal').text();
    let hostTotalAfterSalesTaxTotalTwo = $('#hostTotalAfterSalesTaxTotalTwo').text();
    function minusSalesTax(sales_tax) {
        $('#hostTotalAfterSalesTax').text(Number(hostTotalAfterSalesTax)+ Number(sales_tax))
        $('#hostTotalAfterSalesTaxNumber').val(Number(hostTotalAfterSalesTax)+ Number(sales_tax));
        $('#hostTotalAfterSalesTaxAA').text(Number(hostTotalAfterSalesTax)+ Number(sales_tax))
        $('#hostTotalAfterSalesTaxTotal').text(Number(hostTotalAfterSalesTax)+ Number(sales_tax))
        hostTotalAfterSalesTaxTotal =  $('#hostTotalAfterSalesTaxTotal').text()
        $('#hostTotalAfterSalesTaxTotalNumber').val($('#hostTotalAfterSalesTaxTotal').text())
        minusExtraValues()
        total()
    }

    function minusSalesTaxTwo(sales_tax) {
        $('#hostTotalAfterSalesTaxTwo').text(Number(hostTotalAfterSalesTaxTwo)+ Number(sales_tax))
        // $('#hostTotalAfterSalesTaxAA').text(Number(hostTotalAfterSalesTaxTwo)+ Number(sales_tax))
        $('#hostTotalAfterSalesTaxTotalTwo').text(Number(hostTotalAfterSalesTaxTwo)+ Number(sales_tax))
        hostTotalAfterSalesTaxTotalTwo =  $('#hostTotalAfterSalesTaxTotalTwo').text()
        $('#hostTotalAfterSalesTaxTwoNumber').val($('#hostTotalAfterSalesTaxTotalTwo').text())
        $('#hostTotalAfterSalesTaxTotalTwoNumber').val($('#hostTotalAfterSalesTaxTotalTwo').text())
        total()
        // minusExtraValuesTwo()
    }


    function minusExtraValues() {
        let initialValue = 0;
    $('.calculation').each(function () {
        let val = parseFloat($(this).val()) || 0;
        initialValue += val;
    });
    $('#hostTotalAfterSalesTaxTotal').text(Number(hostTotalAfterSalesTaxTotal)+Number(initialValue));
    $('#hostTotalAfterSalesTaxTotalNumber').val(Number(hostTotalAfterSalesTaxTotal)+Number(initialValue))

    total()
    }

    function minusExtraValuesTwo() {
        let initialValue = 0;
    $('.calculationTwo').each(function () {
        let val = parseFloat($(this).val()) || 0;
        initialValue += val;
    });

    $('#hostTotalAfterSalesTaxTotalTwo').text(Number(hostTotalAfterSalesTaxTotalTwo)+Number(initialValue));
    $('#hostTotalAfterSalesTaxTotalTwoNumber').val(Number(hostTotalAfterSalesTaxTotalTwo)+Number(initialValue));
    total()
    }

    function total() {
        let total = $('#total').text($('#hostTotalAfterSalesTaxTotal').text() -  $('#hostTotalAfterSalesTaxTotalTwo').text());
        $('#final_amount_paid_to_host').val($('#hostTotalAfterSalesTaxTotal').text() -  $('#hostTotalAfterSalesTaxTotalTwo').text())
        // total =
    }

//     function addTr(table_body) {
//     // Select the table body
//     let t_body = $('#' + table_body);

//     // Get the last row's <td> value (e.g., "B", "C", etc.)
//     let lastRow = t_body.find('tr:last');
//     let lastValue = lastRow.find('td:nth-child(8) input').val(); // Get the value from the 8th <td> (which is now an input field)

//     // Initialize the value for the new row
//     let newValue = '';

//     // Check if the last value is a valid single letter, then increment
//     if (lastValue && lastValue.length === 1 && /^[A-Za-z]$/.test(lastValue)) {
//         newValue = String.fromCharCode(lastValue.charCodeAt(0) + 1); // Increment character (e.g., "B" -> "C")
//     } else {
//         newValue = 'B'; // Start with 'B' if no valid value exists or if it's the first row
//     }

//     // Get the hostTotal value from the last row
//     let lastHostTotal = lastRow.find('span[id^="hostTotalAfterSalesTax"]').text().trim();

//     // Append the new row with incremented value and copied data
//     t_body.append(`
//         <tr>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td><input type="text" name="row_id" value="${newValue}"></td> <!-- Incremented value as input -->
//             <td><input type="text" name="payable_to_host" value="Payable to Host- (SAR)"></td> <!-- Input box -->
//             <td></td>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td></td>
//             <td>
//                 <span id="hostTotalAfterSalesTax${newValue}">${lastHostTotal}</span>
//                 <span>
//                     <button class="plus_btn" onclick="addTr('${table_body}')">+</button>
//                     <button class="minus_btn" onclick="removeTr(this)" class="minus_btn">−</button> <!-- Minus button -->
//                 </span>
//             </td>
//         </tr>
//     `);
// }

// // Function to remove the row
// function removeTr(button) {
//     $(button).closest('tr').remove(); // Find the closest <tr> and remove it
// }

  async function exportToPDFSinglePage() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: "landscape", // For wide tables
            unit: "pt",               // Units in points
            format: "a4"              // A4 paper size
        });

        let currentY = 40; // Starting Y position for content

        // Add title for the document
        pdf.setFontSize(16);
        pdf.text("Statement of Account", 40, currentY);
        currentY += 20; // Adjust spacing after title

        // Add logo and details table
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
                fillColor: [240, 240, 240], // Light gray header
                textColor: [0, 0, 0]        // Black text
            },
        });
        currentY = pdf.lastAutoTable.finalY + 10; // Update Y position

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

        // Add Livedin Statement
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
                fillColor: [0, 102, 204], // Blue header
                textColor: [255, 255, 255] // White text
            },
        });
        currentY = pdf.lastAutoTable.finalY + 20;

        // Add Host Statement
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

        // Save the PDF
        pdf.save("Statement_of_Account.pdf");
    }

    function exportToExcel() {
    const workbook = XLSX.utils.book_new(); // Create a new workbook
    const combinedData = []; // Array to hold combined table data

    // Add data from logo table
    const logoTable = document.getElementById("logoTable");
    const logoData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(logoTable), { header: 1 });
    combinedData.push(...logoData, []); // Add data and a blank row

    // Add data from statement details table
    const statementDetailsTable = document.getElementById("statementDetails");
    const statementDetailsData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(statementDetailsTable), { header: 1 });
    combinedData.push(...statementDetailsData, []); // Add data and a blank row

    // Add data from Livedin statement table
    const livedinTable = document.getElementById("statementTableLivedin");
    const livedinData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(livedinTable), { header: 1 });
    combinedData.push(["Livedin Statement"]); // Add section title
    combinedData.push(...livedinData, []); // Add data and a blank row

    // Add data from Host statement table
    const hostTable = document.getElementById("statementTableHost");
    const hostData = XLSX.utils.sheet_to_json(XLSX.utils.table_to_sheet(hostTable), { header: 1 });
    combinedData.push(["Host Statement"]); // Add section title
    combinedData.push(...hostData, []); // Add data and a blank row

    // Create a sheet from the combined data
    const sheet = XLSX.utils.aoa_to_sheet(combinedData);

    // Apply borders and styles to all cells
    const range = XLSX.utils.decode_range(sheet['!ref']); // Get the range of the sheet
    for (let R = range.s.r; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
            if (!sheet[cellAddress]) sheet[cellAddress] = { v: "" }; // Initialize empty cells
            if (!sheet[cellAddress].s) sheet[cellAddress].s = {}; // Initialize styles if not present

            // Add black borders to all sides
            sheet[cellAddress].s.border = {
                top: { style: "thin", color: { rgb: "000000" } },
                bottom: { style: "thin", color: { rgb: "000000" } },
                left: { style: "thin", color: { rgb: "000000" } },
                right: { style: "thin", color: { rgb: "000000" } },
            };

            // Optional: Set alignment
            sheet[cellAddress].s.alignment = { horizontal: "center", vertical: "center" };
        }
    }

    // Append the sheet to the workbook
    XLSX.utils.book_append_sheet(workbook, sheet, "Statement of Account");

    // Trigger download
    XLSX.writeFile(workbook, "Statement_of_Account.xlsx");
}

</script>
</html>
