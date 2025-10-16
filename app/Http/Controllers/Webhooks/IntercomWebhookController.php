<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntercomWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $tokenFromHeader = $request->header('X-Hub-Signature');
        $expectedToken   = env('INTERCOM_TOKEN');

        // Check token
        if ($tokenFromHeader !== $expectedToken) {
            Log::warning('🚫 Intercom webhook: invalid token.', ['received' => $tokenFromHeader]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $parts = $request->input('data.item.conversation_parts.conversation_parts', []);

        foreach ($parts as $part) {
            if ($part['part_type'] === 'comment' && $part['author']['type'] === 'admin') {
                $html = $part['body'];
                $plain = strip_tags($html);

                Log::info('✅ Admin Message Received', ['message' => $plain]);

                // Optional: save to DB or queue it
                return response()->json([
                    'status' => 'received',
                    'message' => $plain
                ]);
            }
        }

        return response()->json(['status' => 'no admin reply']);
    }
}
