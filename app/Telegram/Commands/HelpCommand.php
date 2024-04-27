<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
final class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'help';

    /**
     * @var array Command Aliases
     */
    protected array $aliases = ['listcommands', 'showcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Get a list of available commands';

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $commands = $this->getTelegram()->getCommandBus()->getCommands();

        $text = 'Available commands:'.PHP_EOL.PHP_EOL;
        foreach ($commands as $name => $handler) {
            $text .= sprintf('/%s - %s'.PHP_EOL, $name, $handler->getDescription());
        }
        $text .= PHP_EOL . "Stuck or curious for more? Simply visit our platform for guidance.";

        $this->replyWithMessage(['text' => $text]);
    }
}
