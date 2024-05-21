<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandInterface;

class AirdropStatusCommand extends Command
{

    /**
     * The name of the Telegram command.
     * Ex: help - Whenever the user sends /help, this would be resolved.
     */
    protected string $name = 'airdrop-status';

    /** @var string[] Command Aliases - Helpful when you want to trigger command with more than one name. */
    protected array $aliases = ['status'];
    /** @var string The Telegram command description. */
    protected string $description = 'Command to display information about your current airdrop status i.e points earned and no of pending tasks';

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
                $response = $response->json();
                $tUnCompletedTasks = count($response['unCompletedTasks']);
                $tCompletedTasks = count($response['completedTasks']);
                $this->replyWithMessage([
                    'text' => "*Airdrop Status:*" . PHP_EOL . PHP_EOL . "*Total Referrals:* {$response['totalRefferalsCount']}" . PHP_EOL . "*Total Points Earned:* {$response['totalNumberOfPointsEarned']}" . PHP_EOL . PHP_EOL . "*Tasks:*" . PHP_EOL . PHP_EOL . "*Completed Tasks:* {$tCompletedTasks}" . PHP_EOL . "Uncompleted Tasks: {$tUnCompletedTasks}" . PHP_EOL . PHP_EOL . "Stuck or curious for more? Simply use /help or visit our platform for guidance\.",
                    'parse_mode' => 'MarkdownV2',
                ]);
            } else {
                $this->replyWithMessage(['text' => 'Sorry, An error occurred unable to process your request at the moment!']);
            }
        }
    }
}
