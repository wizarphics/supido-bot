<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class UpdateController extends Controller
{

    public function __construct(protected Telegram $telegram, protected Request $request)
    {
    }


    public function register(string $token, Request $request): void
    {
        $data = $request->validate([
            'telegramFirstName' => 'required|string',
            'telegramId' => 'required|string',
            // 'telegramPhoto' => 'nullable|string',
            // 'telegramUser' => 'nullable|string',
            'TwitterUsername' => 'required|string',
            'walletAddress' => 'nullable|string'
        ]);

        $user = User::updateOrCreate(
            [
                'username' => $data['twitterUsername']
            ],
            [
                'telegram_id' => $data['telegramFirstName'],
                'chat_id' => $data['telegramId'],
                'referral_link' => ''
            ]
        );

        $link = env('WEB_URL', 'https://turbo-buzz-web-react.pages.dev/');


        $k = Keyboard::make()
            ->inlineButton([
                'text' => 'Link to SUPIDO Website',
                'url' => $link
            ]);

        $this->telegram->sendMessage([
            'chat_id' => $user->chat_id,
            'text' => "Welcome, {$user->telegram_id} 🌟" . PHP_EOL . PHP_EOL . "Congratulations on stepping into the future of digital creativity and rewards with SUPIDO! Your journey starts here: [SUPIDO]($link)" . PHP_EOL . PHP_EOL . "SUPIDO emerges as a platform for creators, bounty hunters, and brands that is designed to redefine the parameters of social media engagement\. SUPIDO offers a novel approach to online communities — rewarding every interaction and fostering a genuinely participatory ecosystem\." . PHP_EOL . PHP_EOL . "Our bot is here to navigate you through maximizing your rewards and diving deeper into our community engagement and innovative creator tools\." . PHP_EOL . PHP_EOL . "\$SUPIDO TOKEN LAUNCH" . PHP_EOL . PHP_EOL . "To fuel our vibrant ecosystem, we're introducing the \$SUPIDO token\. Seize your early bird advantage for allocations and substantial airdrops by:" . PHP_EOL . PHP_EOL . ">Engaging with Our Socials ❤️" . PHP_EOL . ">Bringing Friends Onboard 🤗" . PHP_EOL . ">Participating on Our Platform (launching soon) 🚀**" . PHP_EOL . "STAY UPDATED & LEARN COMMANDS" . PHP_EOL . "Keep an eye on [SUPIDO website link]($link) for the latest tasks and more ways to earn! 💰" . PHP_EOL . "To check your current status, use /status" . PHP_EOL . "For your personalized referral link, use /referral" . PHP_EOL . "Let's reach new heights together! 🌈",
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => $k
        ]);
    }

    public function newTask(string $token)
    {
        info("New task:" . PHP_EOL . json_encode($this->request->all()));
        Artisan::call('reminders:send');
        // Queue::before()
        return response([
            'error' => false,
            'message' => 'Ok'
        ]);
    }

    public function taskCompleted(string $token)
    {
        info("Task completed:" . PHP_EOL . json_encode($this->request->all()));

        $user = User::firstWhere([
            'username' => $this->request->twitterScreenName,
        ]);

        if ($user) {
            $res = $this->telegram->sendMessage([
                'chat_id' => $user->chat_id,
                'text' => "🌈 Task Completed: {$this->request->questDetails}!" . PHP_EOL . "🏆 You've just gained {$this->request->rewardPoints} points, increasing your tally to {$this->request->totalPoints} points\. Continue your engagement for even more exciting rewards! 🌟" . PHP_EOL . PHP_EOL . "Spread the word by sharing your referral link\. Earn points for every friend who joins the SUPIDO journey\! 🌍" . PHP_EOL . "[Your SUPIDO Referral Link]($user->referral_link)",
                'parse_mode' => 'MarkdownV2',
            ]);

            return response(['error' => false, 'message' => 'Message sent!']);
        }

        return response(['error' => true, 'message' => 'User hasn\'t starred a conversation with the SUPIDO Bot'], 404);
    }
}
