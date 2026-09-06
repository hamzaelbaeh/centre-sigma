<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $lang): RedirectResponse
    {
        if (! in_array($lang, ['fr', 'ar'], true)) {
            $lang = 'fr';
        }
        $request->session()->put('locale', $lang);

        return redirect()->back(fallback: url('/'));
    }
}
