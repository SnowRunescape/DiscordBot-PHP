<?php

namespace DiscordPHP\Events;

use DiscordPHP\Abstracts\DiscordEventHandler;
use DiscordPHP\Logging\Logger;

class READY extends DiscordEventHandler
{
    public function onInit() {}

    public function run($event)
    {
        if (!$this->discord->botConnected) {
            $this->discord->botConnected = true;

            $this->discord->botSessionId = $event["session_id"];

            Logger::Info("Bot has been authenticated successfully!");
            Logger::Info("SessionID of the current connection is {$this->discord->botSessionId}");
        }
    }
}
