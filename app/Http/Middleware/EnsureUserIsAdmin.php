<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts portfolio administration to accounts explicitly flagged as admins.
 *
 * Guests are redirected to the login screen (so the visitor is not told whether the
 * area exists). Authenticated non-admins receive 403, which is the honest answer:
 * they are known, and they are simply not permitted.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $request->expectsJson()
                ? abort(401)
                : redirect()->guest(route('login'));
        }

        abort_unless($user->isAdmin(), 403);

        return $next($request);
    }
}
