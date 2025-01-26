<?php

namespace DiscordPHP\Abstracts;

use DiscordPHP\Discord;
use DiscordPHP\Interfaces\DiscordCommandInterface;

abstract class DiscordCommand implements DiscordCommandInterface
{
    protected Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    public function onInit() {}
}
