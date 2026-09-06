<?php

namespace App\Providers;

use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\TimetableSlot;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Carbon::setLocale(config('app.locale', 'fr'));
        \Illuminate\Support\Facades\App::setLocale(config('app.locale', 'fr'));
        Paginator::defaultView('vendor.pagination.simple');
        Paginator::defaultSimpleView('vendor.pagination.simple');

        Route::bind('parent', fn ($v) => ParentGuardian::findOrFail($v));
        Route::bind('class', fn ($v) => SchoolClass::findOrFail($v));
        Route::bind('timetable', fn ($v) => TimetableSlot::findOrFail($v));

        View::composer('layouts.app', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $view->with('appSettings', Setting::current());
                }
                if (Schema::hasTable('school_years')) {
                    $view->with('activeYear', SchoolYear::active());
                }
            } catch (\Throwable $e) {
                $view->with('appSettings', null)->with('activeYear', null);
            }
        });
    }
}
