<?php

namespace DiscordPHP;

use DiscordPHP\Logging\Logger;
use Exception;

class Discord
{
    const DISCORD_WSS = "wss://gateway.discord.gg/?v=10&encoding=json";

    private string $token;

    public DiscordAPI $discordAPI;
    public Event $event;
    public Socket $socket;

    public bool $keepAliveSent = false;
    public int $lastReadSocketEmpty = 0;

    public bool $botConnected = false;
    public bool $botForceStop = false;
    public string $botSessionId;

    public function __construct(string $token)
    {
        Logger::Info("Starting DiscordPHP 3.1.0");

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
        while (!$this->botForceStop) {
            try {
                $this->processSocket(0);

                while (
                    $this->botConnected &&
                    !$this->botForceStop &&
                    $this->socket
                ) {
                    $this->processSocket();
                }
            } catch (Exception $e) {
                Logger::Warning("Connection lost, attempting to reconnect in 10 seconds...");

                sleep(10);
            }
        }
    }

    private function processSocket(int $stop = Event::OP["HEARTBEAT_ACK"])
    {
        $this->keepAliveSent = false;
        $op = "";

        while ($op !== $stop) {
            $this->event->executeEvent([
                "t" => "ON_TICK",
                "d" => []
            ]);

            $receive = null;

            try {
                $receive = $this->socket->receive();

                if (is_null($receive)) {
                    throw new Exception("Nulled");
                }

                $op = $receive["op"];

                $this->event->eventHandler($receive);
            } catch (Exception $e) {
                if (is_null($receive) && !$this->botConnected) {
                    $this->botForceStop = true;

                    Logger::Warning("Failed to authenticate TOKEN to discord!");
                    return;
                }

                if (
                    $this->lastReadSocketEmpty == time() || (
                        $this->keepAliveSent &&
                        $op != Event::OP["AUTHENTICATION"]
                    )
                ) {
                    throw new Exception($e);
                }

                $this->lastReadSocketEmpty = time();
            }
        }
    }
}
