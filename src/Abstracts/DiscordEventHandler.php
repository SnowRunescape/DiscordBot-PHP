<?php

namespace DiscordPHP\Abstracts;

use DiscordPHP\Discord;

abstract class DiscordEventHandler
{
    protected Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    abstract public function onInit();
    abstract public function run(array $event);
}
