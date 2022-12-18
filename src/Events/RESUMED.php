<?php

namespace DiscordPHP\Events;

use DiscordPHP\Abstracts\DiscordEventHandler;
use DiscordPHP\Logging\Logger;

class RESUMED extends DiscordEventHandler
{
    public function onInit() {}

    public function run($event)
    {
        Logger::Info("Your connection to the discord has been resumed!");
    }
}
