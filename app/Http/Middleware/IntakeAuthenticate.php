<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Models\Medic;
use App\Models\MedicAssistant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IntakeAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $intake = $request->route('intake');

        if (!$intake) {
            return $next($request);
        } 
    
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return $next($request);
        }

        $medic = Medic::where('user_id', $user->id)->first();
        if ($medic && (!$intake || $intake->medic_id == $medic->id)) {
            return $next($request);
        }

        $medicAssistant = MedicAssistant::where('assistant_id', $user->id)
            ->where('medic_id', $intake->medic_id)
            ->where('institution_id', $intake->institution_id)
            ->first();
           
        if ($medicAssistant) {
            return $next($request);
        }
        
        abort(403);
    }
}

