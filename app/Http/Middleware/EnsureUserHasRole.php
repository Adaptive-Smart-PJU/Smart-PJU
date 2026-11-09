<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $userRole = $user->role->value;

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'Akses ditolak untuk peran Anda.');
        }

        return $next($request);
    }
}
