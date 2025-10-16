<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Calender Update</title>
</head>
<body>
    <h4>Dates are {{ $mailData['status'] }}</h4>
    <p>{{ $mailData['host_name'] }} {{ $mailData['status'] }} calender from {{ $mailData['booking_date_start'] }} to {{ $mailData['booking_date_end'] }} for {{ $mailData['listing_title'] }} on the host app</p>
</body>
</html>
