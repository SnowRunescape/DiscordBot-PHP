<?php

namespace DiscordPHP\Interfaces;

use DiscordPHP\Discord;

interface DiscordEventHandlerInterface
{
    public function __construct(Discord $discord);
    public function onInit();
    public function run(array $event);
}
