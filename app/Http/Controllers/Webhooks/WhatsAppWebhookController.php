<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            
            $from = $request->input('From');        
            $to = $request->input('To');           
            $body = $request->input('Body');         
            $messageSid = $request->input('MessageSid'); 
            $messageStatus = $request->input('MessageStatus') ?? null;

            
            DB::table('whatsapp_messages')->insert([
                'direction' => 'inbound',
                'from' => $from,
                'to' => $to,
                'body' => $body,
                'message_sid' => $messageSid,
                'status' => $messageStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Twilio expects a valid response quickly
            return response('Message received', 200);

        } catch (\Exception $e) {
            // Log error message with stack trace
            Log::error('WhatsApp webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            // Respond with 500 error to indicate failure
            return response('Server Error', 500);
        }
    }
}
