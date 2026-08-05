<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED_LOCALES, true), Response::HTTP_NOT_FOUND);

        session(['locale' => $locale]);

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
