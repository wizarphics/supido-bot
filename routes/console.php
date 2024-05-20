<?php

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Console\ClosureCommand;
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
    /** @var ClosureCommand $this */
    try {
        $url = env('BOT_WEBHOOK_URL');
        Telegram::removeWebhook();
        $r = Telegram::setWebhook(compact('url'));
        if ($r) {
            return $this->alert("Bot webhook updated successfully!\r\nWebhook url:{$url}");
        } else {
            return $this->warn("Error updating bot webhook url: {$url}");
        }
    } catch (Throwable|Error $t) {
        $this->error("Error updating bot webhook url {$t->getMessage()}");
    }
    exit(-1);
})->purpose('Sets telegram webhook url');

Artisan::command('token:generate', function () {
    $key = 'bot:' . base64_encode(
        Encrypter::generateKey('AES-256-CBC')
    );
    $this->comment("Key generated!:\t$key\n");
});
