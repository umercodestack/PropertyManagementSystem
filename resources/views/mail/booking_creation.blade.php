<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>New Booking</title>
</head>
<body>
    <h4>New Booking Created</h4>
    <p>{{ $mailData['host_name'] }} had created a direct booking from host app for aparment {{ $mailData['listing_title'] }} from {{ $mailData['booking_date_start'] }} to {{ $mailData['booking_date_end'] }}, booking id is {{ $mailData['id'] }} and the total price is {{ $mailData['total_price'] }}</p>
</body>
</html>
