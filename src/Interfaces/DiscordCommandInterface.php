<?php

namespace DiscordPHP\Interfaces;

use DiscordPHP\Discord;

interface DiscordCommandInterface
{
    public function __construct(Discord $discord);
    public function getCommand();
    public function onInit();
    public function run(array $event, array $args);
}
