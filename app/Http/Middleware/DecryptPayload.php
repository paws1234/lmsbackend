<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DecryptPayload
{
    public function handle(Request $request, Closure $next)
    {
        // If it's a GET request, skip the decryption process
        if ($request->isMethod('get')) {
            return $next($request);
        }

        // Log the raw payload received in the request
        Log::info('Received payload: ', ['payload' => $request->getContent()]);

        // Retrieve the raw request body
        $payload = $request->getContent();

        try {
            // First, check if the payload is Base64 encoded
            if ($this->isBase64($payload)) {
                // If Base64 encoded, decode it
                $decodedData = base64_decode($payload, true);
                if ($decodedData === false) {
                    throw new \Exception("Base64 decoding failed.");
                }

                // Log the decoded Base64 data
                Log::info('Decoded Base64 data: ', ['decoded_data' => $decodedData]);

                // Decode the JSON payload after decoding the Base64 string
                $data = json_decode($decodedData, true);
                if (is_null($data)) {
                    throw new \Exception("Failed to decode JSON data after Base64 decoding.");
                }

                // Log the decoded JSON data
                Log::info('Decoded JSON data: ', ['data' => $data]);

            } else {
                // If not Base64, assume it's a plain JSON payload or some other format
                $data = json_decode($payload, true);
                if (is_null($data)) {
                    // Handle other cases like plain text or other formats if needed
                    throw new \Exception("Failed to decode plain JSON or unsupported format.");
                }

                // Log the decoded JSON data (or plain data if it's valid JSON)
                Log::info('Decoded plain JSON data: ', ['data' => $data]);
            }

            // Merge the decoded data into the request (so it can be used by controllers)
            $request->merge($data);

        } catch (\Exception $e) {
            // Log the error details in case of failure
            Log::error('Decoding failed: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Decoding failed: ' . $e->getMessage()], 400);
        }

        // Proceed with the next middleware
        return $next($request);
    }

    /**
     * Check if the given string is Base64 encoded.
     *
     * @param  string $data
     * @return bool
     */
    private function isBase64($data)
    {
        // Check if the string is a valid Base64 encoded string
        return base64_encode(base64_decode($data, true)) === $data;
    }
}
