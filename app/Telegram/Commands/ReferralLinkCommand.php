<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;
use Telegram\Bot\Keyboard\Keyboard;

class ReferralLinkCommand extends Command
{

    /**
     * The name of the Telegram command.
     * Ex: help - Whenever the user sends /help, this would be resolved.
     */
    protected string $name = 'referral';

    /** @var string[] Command Aliases - Helpful when you want to trigger command with more than one name. */
    protected array $aliases = ['referral_link'];
    /** @var string The Telegram command description. */
    protected string $description = 'Command to display your referral link';

    /** @var array Holds parsed command arguments */
    protected array $arguments = [];

    /** @var string Command Argument Pattern */
    protected string $pattern = '';
    /** @var array Details of the current entity this command is responding to - offset, length, type etc */
    protected array $entity = [];

    public function handle()
    {
        $update =  $this->getUpdate();

        # firstname from Update object to be used as fallback.
        $telegram_id = $update->getMessage()->from->first_name;


        $user = User::where('telegram_id', $telegram_id)
            ->orWhere('chat_id', $update->getChat()->get('id'))
            ->first();


        if (!$user) {
            $this->replyWithMessage([
                'text' => "Please provide your twitter username! Ex: /start jason"
            ]);
        } else {
            $userName = $user->username;

            $response = Http::withHeader('adminsecretkey', env('WEB_ADMIN_SECRET', 'JHASADKsadfas123456'))
                ->get(env('WEB_API', "https://stg-api.supido.xyz/auth/getUserData"), compact('userName'));

            $user->referral_link = $response->object()->userDetails->referralLink;
            $user->save();

            $this->replyWithChatAction(['action' => Actions::TYPING]);
            $this->replyWithMessage([
                'text' => "Your Referral link is:".PHP_EOL.$user->referral_link,
            ]);
        }
    }
}
