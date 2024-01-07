<?php

namespace DiscordPHP;

use DiscordPHP\Logging\Logger;
use WebSocket\Client;

class Socket
{
    private Client $socket;

    public function __construct(string $wss)
    {
        Logger::Info("Starting WebSocket...");

        $this->socket = new Client($wss);
    }

    public function isConnected()
    {
        return $this->socket->isConnected();
    }

    public function send($message)
    {
        if (is_array($message)) {
            $message = json_encode($message, true);
        }

        $this->socket->send($message);
    }

    public function receive()
    {
        $data = $this->socket->receive();

        if (!$data) {
            return [];
        }

        return json_decode($data, true);
    }

    public function close()
    {
        $this->socket->close();
    }
}
