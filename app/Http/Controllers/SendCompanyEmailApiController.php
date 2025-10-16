<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyContact;
use App\Jobs\SendCompanyContactEmail;
use Illuminate\Support\Facades\Log;


class SendCompanyEmailApiController extends Controller
{
    public function sendToAll()
    {
        // Get all contacts from the table
        $contacts = CompanyContact::all();

        // Check if empty
        if ($contacts->isEmpty()) {
            return response()->json([
                'message' => 'No contacts found.'
            ], 404);
        }

        // Loop through contacts and dispatch email job
        foreach ($contacts as $contact) {
            Log::info('Dispatching job for: ' . $contact->email_address);
            dispatch((new SendCompanyContactEmail($contact))->onQueue('company_emails'));
        }

        return response()->json([
            'message' => 'Emails queued successfully.',
            'count' => $contacts->count(),
        ]);
    }
}
