<?php
require '../vendor/autoload.php';

require 'src/DiscordPHP.php';

$DiscordPHP = new DiscordPHP('YOU_DISCORD_BOT_TOKEN');
$DiscordPHP->includePlugins();
$DiscordPHP->init();