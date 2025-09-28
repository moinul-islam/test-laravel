<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        // Multiple roles handle করার জন্য
        $allowedRoles = explode('|', $roles);
        $userRole = auth()->user()->role;
        
        // Check if user's role is in allowed roles
        if (!in_array($userRole, $allowedRoles)) {
            abort(404);
        }
        
        return $next($request);
    }
}