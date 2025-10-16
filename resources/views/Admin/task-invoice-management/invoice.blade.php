
<!DOCTYPE html>
<html lang="zxx" class="js">

<head>
    <base href="../../">
    <meta charset="utf-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Invocie">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="{{asset('assets/images/favicon.png')}}">
    <!-- Page Title  -->
    <title>Invoice Print | LivedIn</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="{{asset('assets/assets/css/dashlite.css?ver=3.2.3')}}">
    <link id="skin-default" rel="stylesheet" href="{{asset('assets/assets/css/theme.css?ver=3.2.3')}}">
</head>

<body class="bg-white" onload="printPromot()">
<div class="nk-block">
    <div class="invoice invoice-print">
        <div class="invoice-wrap">
            <div class="invoice-brand text-center">
                <img src="{{asset('assets/images/logo.png')}}" srcset="{{asset('assets/images/logo.png')}}" alt="">
            </div>
            <div class="invoice-head">
                <div class="invoice-contact">
                    <span class="overline-title">Invoice To</span>
                    <div class="invoice-contact-info">
                        <h4 class="title">{{$user->name}} {{$user->surname}}</h4>
                        <ul class="list-plain">
                            <li><em class="icon ni ni-map-pin-fill fs-18px"></em><span>House #65, 4328 Marion Street<br>{{$user->country}} {{$user->city}}</span></li>
                            <li><em class="icon ni ni-call-fill fs-14px"></em><span>+{{$user->phone}}</span></li>
                        </ul>
                    </div>
                </div>
                <div class="invoice-desc">
                    <h3 class="title">Invoice</h3>
                    <ul class="list-plain">
                        <li class="invoice-id"><span>Invoice ID</span>:<span>00000{{$taskInvoice->id}}</span></li>
                        <li class="invoice-date"><span>Date</span>:<span>{{$taskInvoice->created_at->format('d-M-Y') }}</span></li>
                    </ul>
                </div>
            </div><!-- .invoice-head -->
            <div class="invoice-bills">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th class="w-150px">Item ID</th>
                            <th class="w-60">Description</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>00000{{$taskInvoice->id}}</td>
                            <td>{{$taskInvoice->description}}</td>
                            <td>{{$taskInvoice->status}}</td>
                            <td>{{$taskInvoice->currency}}</td>
                            <td>{{$taskInvoice->amount}}</td>
                        </tr>

                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">Subtotal</td>
                            <td>{{$taskInvoice->amount}}</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">Processing fee</td>
                            <td>0.00</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">TAX</td>
                            <td>0.00</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">Grand Total</td>
                            <td>{{$taskInvoice->amount}}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div><!-- .invoice-bills -->
        </div><!-- .invoice-wrap -->
    </div><!-- .invoice -->
</div><!-- .nk-block -->
<script>
    function printPromot() {
        window.print();
    }
</script>
</body>

</html>
