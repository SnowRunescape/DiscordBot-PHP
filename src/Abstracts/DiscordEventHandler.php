<?php

namespace DiscordPHP\Abstracts;

use DiscordPHP\Discord;
use DiscordPHP\Interfaces\DiscordEventHandlerInterface;

abstract class DiscordEventHandler implements DiscordEventHandlerInterface
{
    protected Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    public function onInit() {}
}
