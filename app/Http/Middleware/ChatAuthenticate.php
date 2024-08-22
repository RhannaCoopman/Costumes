<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ChatAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $intake = $request->route('intake');

        $patient = Patient::where('user_id', Auth::user()->id)->first();

        if (!$intake || !$patient ||
            ($intake->patient_id && $patient->id != $intake->patient_id) ||
            (!$intake->patient_id && $patient->user->email != $intake->email)
        ) {
            abort(403);
        }

        return $next($request);
    }
}
