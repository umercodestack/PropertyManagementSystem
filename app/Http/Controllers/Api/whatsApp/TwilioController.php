<?php

namespace App\Http\Controllers\Api\whatsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\DB;

class TwilioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

public function store(Request $request)
{
    $request->validate([
        'to' => 'required|string',      // e.g. +923001234567
        'message' => 'required|string'
    ]);

    try {
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_AUTH_TOKEN');
        $from   = env('TWILIO_WHATSAPP_FROM'); // e.g. whatsapp:+14155238886

        $client = new Client($sid, $token);

        $message = $client->messages->create(
            "whatsapp:" . $request->to,
            [
                'from' => $from,
                'body' => $request->message
            ]
        );

        // Save outbound message to DB
        DB::table('whatsapp_messages')->insert([
            'direction' => 'outbound',
            'from' => $from,
            'to' => "whatsapp:" . $request->to,
            'body' => $request->message,
            'message_sid' => $message->sid,
            'status' => $message->status ?? 'sent', // initial status from API
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'WhatsApp message sent successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
