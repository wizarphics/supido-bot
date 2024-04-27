<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{

    /**
     * The name of the Telegram command.
     * Ex: help - Whenever the user sends /help, this would be resolved.
     */
    protected string $name = 'start';

    /** @var string[] Command Aliases - Helpful when you want to trigger command with more than one name. */
    protected array $aliases = ['join'];
    /** @var string The Telegram command description. */
    protected string $description = 'Start Command to get you started';

    /** @var array Holds parsed command arguments */
    protected array $arguments = [];

    /** @var string Command Argument Pattern */
    protected string $pattern = '{username}';
    /** @var array Details of the current entity this command is responding to - offset, length, type etc */
    protected array $entity = [];

    public function handle()
    {
        $update =  $this->getUpdate();
        # first_name from Update object to be used as fallback.
        $telegram_id = $update->getMessage()->from->first_name;

        # Get the username argument if the user provides
        $userName = $this->argument('username');

        if (!$userName) {
            $this->replyWithMessage([
                'text' => "Please provide your twitter username\! Ex: /start jason"
            ]);
        } else {

            $response = Http::withHeader('adminsecretkey', env('WEB_ADMIN_SECRET', 'JHASADKsadfas123456'))
                ->get(env('WEB_API', "https://stg-api.supido.xyz/auth/getUserData"), compact('userName'));
            $link = env('WEB_URL', 'https://turbo-buzz-web-react.pages.dev/');

            if ($response->notFound()) {
                $k = Keyboard::make()
                    ->setIsPersistent(true)
                    ->inlineButton([
                        'text' => 'Visit SUPIDO Website',
                        'url' => $link,
                    ]);

                $this->replyWithMessage([
                    'text' => "Your \$SUPIDO account was not found, you probably forgot to link your twitter account\. You can visit [SUPIDO Website]($link) to link your account and try again\.",
                    'parse_mode' => 'MarkdownV2',
                    'reply_markup' => $k
                ]);
            } else

            if (!$response->ok()) {
                $this->replyWithMessage([
                    'text' => 'Something went wrong\! Please try again later'
                ]);
            } else {
                # This will update the chat status to "typing..."
                $this->replyWithChatAction(['action' => Actions::TYPING]);

                $user = User::firstOrCreate([
                    'username' => $userName,
                    'telegram_id' => $telegram_id,
                    'chat_id' => $update->getChat()->get('id'),
                    'referral_link' => $response->object()->userDetails->referralLink
                ]);

                $k = Keyboard::make()
                    ->setIsPersistent(true)
                    ->inline()
                    ->row([
                        Keyboard::inlineButton([
                            'text' => 'Link to SUPIDO Website',
                            'url' => $link
                        ])
                    ]);

                $this->replyWithMessage([
                    'text' => "Welcome, $telegram_id 🌟" . PHP_EOL . PHP_EOL . "Congratulations on stepping into the future of digital creativity and rewards with SUPIDO\! Your journey starts here: [SUPIDO]($link)" . PHP_EOL . PHP_EOL . "SUPIDO emerges as a platform for creators, bounty hunters, and brands that is designed to redefine the parameters of social media engagement\. SUPIDO offers a novel approach to online communities — rewarding every interaction and fostering a genuinely participatory ecosystem\." . PHP_EOL . PHP_EOL . "Our bot is here to navigate you through maximizing your rewards and diving deeper into our community engagement and innovative creator tools\." . PHP_EOL . PHP_EOL . "\$SUPIDO TOKEN LAUNCH" . PHP_EOL . PHP_EOL . "To fuel our vibrant ecosystem, we're introducing the \$SUPIDO token\. Seize your early bird advantage for allocations and substantial airdrops by:" . PHP_EOL . PHP_EOL . "1️⃣ Engaging with Our Socials ❤️" . PHP_EOL . "2️⃣ Bringing Friends Onboard 🤗" . PHP_EOL . "3️⃣ Participating on Our Platform \(launching soon\) 🚀" . PHP_EOL . PHP_EOL . "STAY UPDATED & LEARN COMMANDS" . PHP_EOL . "Keep an eye on [SUPIDO website link]($link) for the latest tasks and more ways to earn\! 💰" . PHP_EOL . "To check your current status, use /status" . PHP_EOL . "For your personalized referral link, use /referral" . PHP_EOL . "Let's reach new heights together\! 🌈",
                    'parse_mode' => 'MarkdownV2',
                    'reply_markup' => $k
                ]);
            }
        }
    }
}
