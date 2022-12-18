<?php

use DiscordPHP\Abstracts\DiscordCommand;

class Ping extends DiscordCommand
{
    private $pingHandler = [];

    public function getCommand()
    {
        return "!ping";
    }

    public function onInit() {}

    public function run(array $event, array $args)
    {
        $receivedPing = round(microtime(true) * 1000);

        $message = $this->discord->discordAPI->createMessage("Pong!", $event["channel_id"]);

        $pingAPI = round(microtime(true) * 1000);

        $this->pingHandler[$message["id"]] = [
            "timingOnSend" => $receivedPing,
            "timingAPI" => $pingAPI
        ];
    }

    public function MESSAGE_CREATE(array $event)
    {
        if (array_key_exists($event["id"], $this->pingHandler)) {
            $ping = round(microtime(true) * 1000) - $this->pingHandler[$event["id"]]["timingOnSend"];
            $pingAPI = $this->pingHandler[$event["id"]]["timingAPI"] - $this->pingHandler[$event["id"]]["timingOnSend"];

            $this->discord->discordAPI->editMessage(
                $event["id"],
                "Pong! A Latência é {$ping}ms. A Latência da API é {$pingAPI}ms.",
                $event["channel_id"]
            );

            unset($this->pingHandler[$event["id"]]);
        }
    }
}
