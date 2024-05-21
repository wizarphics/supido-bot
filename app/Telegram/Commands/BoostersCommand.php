<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;

class BoostersCommand extends Command
{

    /**
     * The name of the Telegram command.
     * Ex: help - Whenever the user sends /help, this would be resolved.
     */
    protected string $name = 'boosters';

    /** @var string[] Command Aliases - Helpful when you want to trigger command with more than one name. */
    protected array $aliases = ['allboosters'];
    /** @var string The Telegram command description. */
    protected string $description = 'No of active boosters (when boosters are inactive)';

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


        $user = User::firstWhere('telegram_id', $telegram_id);

        if (!$user) {
            $this->replyWithMessage([
                'text' => "Please provide your twitter username! Ex: /start jason"
            ]);
        } else {
            $userName = $user->username;
            $response = Http::withHeader('adminsecretkey', env('WEB_ADMIN_SECRET', 'JHASADKsadfas123456'))
                ->get(env('WEB_API', "https://stg-api.supido.xyz/auth/getUserData"), compact('userName'));
            $this->replyWithChatAction(['action' => Actions::TYPING]);
            if ($response->ok()) {
                $msg = "*Boosters Status:*" . PHP_EOL . PHP_EOL;
                $boosters = $response->json('boosters');

                if (count($boosters) > 0) {
                    foreach ($boosters as $booster) {
                        $msg .= "*{$booster['type']}:* {$booster['quantity']}" . PHP_EOL;
                    }
                } else {
                    $msg .= "No active boosters found\!\." . PHP_EOL;
                }
                $msg .= PHP_EOL . "Stuck or curious for more? Simply use /help or visit our platform for guidance\.";

                $this->replyWithMessage([
                    'text' => $msg,
                    'parse_mode' => 'MarkdownV2',
                ]);
            } else {
                $this->replyWithMessage(['text' => 'Sorry, An error occurred unable to process your request at the moment!']);
            }
        }
    }
}
