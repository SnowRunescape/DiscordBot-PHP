<?php

namespace DiscordPHP\Events;

use DiscordPHP\Abstracts\DiscordEventHandler;
use DiscordPHP\Event;

class ON_TICK extends DiscordEventHandler
{
    private int $lastSendKeepAlive = 0;

    public function run($event)
    {
        if (time() >= ($this->lastSendKeepAlive + 30)) {
            $this->lastSendKeepAlive = time();

            $this->discord->socket->send([
                "op" => Event::OP["HEARTBEAT"],
                "d" => 251
            ]);
        }
    }
}
