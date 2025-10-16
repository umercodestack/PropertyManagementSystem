<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lease Reminder</title>
</head>
<body>
    <h1>Hello {{ $activation->title }}</h1>

    <p>This is a reminder that your lease will expire soon.</p>

    <p><strong>Lease Date:</strong> {{ \Carbon\Carbon::parse($activation->host_rental_lease)->format('d M Y') }}</p>
    <p><strong>Reminder Date:</strong> {{ \Carbon\Carbon::parse($activation->host_rental_lease)->subDays(60)->format('d M Y') }}</p>

    <p>Please click the button below to review:</p>
    
    <a href="{{ url('/host-rental-lease/'.$activation->lease_task_id.'/edit') }}" style="padding:10px 20px;background:#3490dc;color:white;text-decoration:none;">
        Review Lease
    </a>
    
</body>
</html>
