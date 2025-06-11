<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $allowed_origins = [
            'https://react.gwenell.com',
            'https://gsb.gwenell.com',
            'http://localhost:8097'
        ];
        
        $origin = $request->header('Origin');
        
        if (in_array($origin, $allowed_origins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            // Default allowed origin when no origin or not in the allowed list
            $response->headers->set('Access-Control-Allow-Origin', 'https://gsb.gwenell.com');
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        return $response;
    }
} 