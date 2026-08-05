<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED_LOCALES = ['en', 'hy', 'fa'];

    public const RTL_LOCALES = ['fa'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        session(['locale' => $locale]);

        App::setLocale($locale);
        Carbon::setLocale($locale);

        View::share('htmlDir', in_array($locale, self::RTL_LOCALES, true) ? 'rtl' : 'ltr');

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        $sessionLocale = session('locale');

        if (in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
            return $sessionLocale;
        }

        $user = $request->user();

        if ($user && $user->locale && in_array($user->locale, self::SUPPORTED_LOCALES, true)) {
            return $user->locale;
        }

        return config('app.locale');
    }
}
