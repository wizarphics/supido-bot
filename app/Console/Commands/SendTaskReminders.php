<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to users with uncompleted tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending reminders...');
        $this->newLine();
        $this->withProgressBar(User::all(), function (User $user) {
            $uncompletedTasks = $this->getUncompletedTasks($user);
            if (count($uncompletedTasks) > 20) {
                $this->sendReminder($user, $uncompletedTasks);
            }
        });
        $this->newLine();
        $this->info('Done!');
    }

    private function getUncompletedTasks(User $user)
    {
        $userName = $user->username;
        // Make request to task management system to fetch tasks for user
        $response = Http::withHeader('adminsecretkey', env('WEB_ADMIN_SECRET'))
            ->get(env('WEB_API', "https://api.supido.xyz/auth/getUserData"), compact('userName'))->object();
        $user->referral_link = $response->userDetails->referralLink;
        $user->save();

        return $response->unCompletedTasks ?? [];
    }

    private function sendReminder(User $user, array $uncompletedTasks)
    {
        $t = count($uncompletedTasks);
        $k = Keyboard::make()
            ->inlineButton([
                'text' => 'Your SUPIDO Referral Link Here',
                'url' => $user->referral_link
            ]);

        Telegram::sendMessage([
            'chat_id' => $user->chat_id,
            'text' => Str::escapeMarkdownV2("⏰ SUPIDO Wake-Up Call! 🌠" . PHP_EOL . PHP_EOL . "Hello there!" . PHP_EOL . "It seems you've got {$t} tasks waiting for your magic touch. Let's dive back in and unlock those rewards awaiting you!" . PHP_EOL . PHP_EOL . "🚀 Ready to Jump Back In?" . PHP_EOL . PHP_EOL . "1) Tap the button below." . PHP_EOL . "2) Share your special referral link." . PHP_EOL . "3) Encourage friends to explore SUPIDO with you." . PHP_EOL . PHP_EOL . "Each task you complete not only brings you points but also closer to exclusive perks and a vibrant position in the SUPIDO universe." . PHP_EOL . PHP_EOL . "🔗 Your Referral Link: {$user->referral_link}" . PHP_EOL . PHP_EOL . "Stuck or curious for more? Simply use /help or visit our platform for guidance." . PHP_EOL . PHP_EOL . "Let's make waves together in the SUPIDO community! 🌊✨" . PHP_EOL . PHP_EOL . "Cheers," . PHP_EOL . "The SUPIDO Team."),
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => $k
        ]);
    }
}
