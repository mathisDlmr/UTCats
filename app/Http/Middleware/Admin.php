<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Assos;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $assos = json_decode(Auth::user()->assos, true);

        $authorized = false;
        if (is_array($assos)) {
            foreach ($assos as $asso) {
                if (
                    (isset($asso['login'], $asso['role']) && $asso['login'] === 'simde' && $asso['role'] === 'team_payutc') ||
                    (isset($asso['login']) && $asso['login'] === 'bde')
                ) {
                    $authorized = true;
                    break;
                }
            }
        }

        if ($authorized) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}