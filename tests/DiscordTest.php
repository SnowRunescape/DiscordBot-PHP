<?php

declare(strict_types=1);

namespace Tests;

use DiscordPHP\Discord;
use PHPUnit\Framework\TestCase;

class DiscordTest extends TestCase
{
    public function testGetBotToken()
    {
        $token = "abc123";

        $discord = new Discord($token);
        $this->assertEquals($token, $discord->getBotToken());
    }
}
