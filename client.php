<?php

require_once "vendor/autoload.php";

use DiscordPHP\Discord;

$discord = new Discord("YOU_DISCORD_BOT_TOKEN");
$discord->run();
