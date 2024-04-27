<?php

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
// bv4OtPWUxWe0

Artisan::command('bot:start', function () {
    Telegram::removeWebhook();
    $r = Telegram::setWebhook([
        'url' => 'https://sandbox-bot.wizarphics.com/<token>/webhook'
        //'url' => 'https://25v3gqkw-8000.uks1.devtunnels.ms/<token>/webhook'
        // https://25v3gqkw-8000.uks1.devtunnels.ms/
    ]);

    dd($r);
})->purpose('Sets telegram webhook url');

Artisan::command('token:generate', function () {
    $key = 'bot:' . base64_encode(
        Encrypter::generateKey('AES-256-CBC')
    );
    $this->comment("Key generated!:\t$key\n");
});
