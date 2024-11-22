<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class EncryptResponse
{
    public function handle(Request $request, \Closure $next) // Use \Closure directly
    {
        // Get the response from the next middleware
        $response = $next($request);

        // Check if the request is an API call for 'api/lms' (or other routes if needed)
        if ($request->is('api/lms')) {
            return $response; 
        }

        // Ensure the response is an instance of Response and has a JSON content type
        if ($response instanceof Response && $response->headers->get('Content-Type') === 'application/json') {
            // Decrypt or encode the response content if needed before sending back
            $data = json_decode($response->getContent(), true);

            // Encrypt the response data
            $encryptedData = Crypt::encryptString(json_encode($data));

            // Set the encrypted data as the response content
            $response->setContent($encryptedData);

            // Optionally, you can change the content type or add a custom header
            // $response->headers->set('Content-Type', 'application/encrypted-json'); // if you want a custom content type

            return $response;
        }

        return $response;
    }
}
