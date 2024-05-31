<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::macro('everyThreeDays', function () {
            return $this->spliceIntoPosition(5, 1, '*/3');
        });
        \Illuminate\Support\Str::macro(
            'escapeMarkdownV2',
            function ($text) {
                $escapeChars = ['\\', '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
                $escapedText = str_replace($escapeChars, array_map(function ($char) {
                    return '\\' . $char;
                }, $escapeChars), $text);
                return $escapedText;
            }
        );
    }
}
