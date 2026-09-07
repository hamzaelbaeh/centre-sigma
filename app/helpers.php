<?php

if (! function_exists('latin_digits')) {
    /**
     * Map Eastern Arabic / Persian digits to Western 0-9.
     */
    function latin_digits(?string $s): string
    {
        if ($s === null || $s === '') {
            return '';
        }

        return strtr($s, [
            // Eastern Arabic-Indic (U+0660–U+0669)
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            // Persian / Urdu (U+06F0–U+06F9)
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }
}

if (! function_exists('format_date_latin')) {
    /**
     * Format a date with translated month/weekday names but always Western digits.
     */
    function format_date_latin($date = null, string $format = 'l d/m/Y'): string
    {
        if ($date instanceof \Carbon\CarbonInterface) {
            $c = $date;
        } elseif ($date) {
            $c = \Carbon\Carbon::parse($date);
        } else {
            $c = now();
        }

        return latin_digits($c->translatedFormat($format));
    }
}

if (! function_exists('money_ltr')) {
    /**
     * Format an amount as French-style money inside an LTR-isolated span (safe HTML).
     */
    function money_ltr($n, int $decimals = 2, string $suffix = ' DH'): string
    {
        $text = number_format((float) $n, $decimals, ',', ' ').$suffix;

        return '<span class="ltr-num">'.e($text).'</span>';
    }
}
