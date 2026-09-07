<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale', 'fr'));
        if (! in_array($locale, ['fr', 'ar'], true)) {
            $locale = 'fr';
        }
        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
