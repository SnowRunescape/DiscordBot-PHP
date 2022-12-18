<?php

namespace DiscordPHP;

use DiscordPHP\Logging\Logger;
use Exception;

class Discord
{
    const DISCORD_WSS = "wss://gateway.discord.gg/?v=6&encoding=json";

    private string $token;

    public DiscordAPI $discordAPI;
    public Event $event;
    public Socket $socket;

    public int $lastReadSocketEmpty = 0;

    public bool $botConnected = false;
    public bool $botForceStop = false;
    public string $botSessionId;

    public function __construct(string $token)
    {
        Logger::Info("Starting DiscordPHP v3.0.0");

        $this->token = $token;

        $this->discordAPI = new DiscordAPI($this);
        $this->event = new Event($this);
        $this->socket = new Socket(self::DISCORD_WSS);
    }

    public function getBotToken()
    {
        return $this->token;
    }

    public function run()
    {
        $this->processSocket(0);

        while (
            $this->botConnected &&
            !$this->botForceStop &&
            $this->socket
        ) {
            $this->processSocket();
        }
    }

    private function processSocket($stop = 11)
    {
        $keepAliveSent = false;
        $op = "";

        while ($op !== $stop && $this->socket != null) {
            $this->event->executeEvent([
                "t" => "ON_TICK"
            ]);

            try {
                $receive = $this->socket->receive();

                $op = $receive["op"];

                if (($op == 11) && $keepAliveSent) {
                    $keepAliveSent = false;
                }

                $this->event->eventHandler($receive);
            } catch(Exception $e) {
                if ($this->lastReadSocketEmpty == time() || $keepAliveSent) {
                    throw new Exception($e);
                }

                $this->lastReadSocketEmpty = time();
            }
        }
    }
}
