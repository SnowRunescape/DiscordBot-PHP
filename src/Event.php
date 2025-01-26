<?php

namespace DiscordPHP;

use DiscordPHP\Interfaces\DiscordCommandInterface;
use DiscordPHP\Interfaces\DiscordEventHandlerInterface;
use DiscordPHP\Logging\Logger;
use Exception;
use Throwable;

class Event
{
    const OP = [
        "DISPATCH" => 0,
        "HEARTBEAT" => 1,
        "IDENTIFY" => 2,
        "RESUME" => 6,
        "DISCONNECT" => 9,
        "AUTHENTICATION" => 10,
        "HEARTBEAT_ACK" => 11
    ];

    private array $commandsHandler = [];
    private array $commandsEventsHandler = [];
    private array $eventsHandler = [];

    const EVENTS_HANDLER = [
        "CHANNEL_CREATE", "CHANNEL_UPDATE", "CHANNEL_DELETE", "CHANNEL_PINS_UPDATE", "GUILD_CREATE", "GUILD_UPDATE",
        "GUILD_DELETE", "GUILD_BAN_ADD", "GUILD_BAN_REMOVE", "GUILD_EMOJIS_UPDATE", "GUILD_INTEGRATIONS_UPDATE",
        "GUILD_MEMBER_ADD", "GUILD_MEMBER_REMOVE", "GUILD_MEMBER_UPDATE", "GUILD_MEMBERS_CHUNK", "GUILD_ROLE_CREATE",
        "GUILD_ROLE_UPDATE", "GUILD_ROLE_DELETE", "MESSAGE_CREATE", "MESSAGE_UPDATE", "MESSAGE_DELETE",
        "MESSAGE_DELETE_BULK", "MESSAGE_REACTION_ADD", "MESSAGE_REACTION_REMOVE", "MESSAGE_REACTION_REMOVE_ALL",
        "PRESENCE_UPDATE", "READY", "RESUMED", "TYPING_START", "USER_UPDATE", "VOICE_STATE_UPDATE",
        "VOICE_SERVER_UPDATE", "WEBHOOKS_UPDATE"
    ];

    private Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;

        $this->loadEvents();
    }

    public function eventHandler(array $event)
    {
        switch ($event["op"]) {
            case self::OP["DISPATCH"]:
                $this->executeCommand($event);
                $this->executeEvent($event);
                break;
            case self::OP["AUTHENTICATION"]:
                $this->discord->botConnected ?
                    $this->reconnect() :
                    $this->authentication();
                break;
            case self::OP["DISCONNECT"]:
                $this->disconnect();
                break;
        }
    }

    private function executeCommand(array $event)
    {
        if ($event["t"] != "MESSAGE_CREATE") {
            return;
        }

        $args = explode(" ", $event["d"]["content"]);

        $command = strtolower($args[0]);

        if (array_key_exists($command, $this->commandsHandler)) {
            Logger::Info("Command {$command} has been Detected!");

            try {
                $this->commandsHandler[$command]->run($event["d"], $args);
            } catch (Throwable $e) {
                $className = get_class($this->commandsHandler[$command]);

                Logger::Warning("Error when executing the class {$className}!");
                Logger::Warning($e->getMessage());
            }
        }
    }

    public function executeEvent(array $event)
    {
        if (!$this->discord->botConnected && $event["t"] != "READY") {
            return;
        }

        if (
            array_key_exists($event["t"], $this->eventsHandler) ||
            array_key_exists($event["t"], $this->commandsEventsHandler)
        ) {
            if ($event["t"] != "ON_TICK") {
                Logger::Info("Event {$event["t"]} has been received!");
            }

            if (array_key_exists($event["t"], $this->eventsHandler)) {
                try {
                    $this->eventsHandler[$event["t"]]->run($event["d"]);
                } catch (Exception $e) {
                    $className = get_class($this->eventsHandler[$event["t"]]);

                    Logger::Warning("Error when executing the class {$className}!");
                    Logger::Warning($e->getMessage());
                }
            }

            if (array_key_exists($event["t"], $this->commandsEventsHandler)) {
                try {
                    foreach ($this->commandsEventsHandler[$event["t"]] as $tempEvent) {
                        if ($event["t"] == "ON_TICK") {
                            $tempEvent->{$event["t"]}();
                        } else {
                            $tempEvent->{$event["t"]}($event["d"]);
                        }
                    }
                } catch (Exception $e) {
                    /* MUDAR A MENSAGEM DE ERRO AQUI... */
                    $className = get_class($this->commandsEventsHandler[$event["t"]]);

                    Logger::Warning("Error when executing the class {$className}!");
                    Logger::Warning($e->getMessage());
                }
            }
        }
    }

    private function loadEvents()
    {
        try {
            foreach (glob(__DIR__ . "/Events/*.php", GLOB_BRACE) as $file) {
                $className = basename($file, ".php");
                $className = "\\DiscordPHP\\Events\\{$className}";

                $class = new $className($this->discord);
                $this->registerEventHandler($class);
            }
        } catch (Throwable $th) {
            echo $th;
        }
    }

    private function authentication()
    {
        Logger::Info("Authenticating bot to discord...");

        $this->discord->socket->send([
            "op" => self::OP["IDENTIFY"],
            "d" => [
                "token" => $this->discord->getBotToken(),
                "intents" => 65535,
                "properties" => [
                    "\$os" => "windows",
                    "\$browser" => "SnowDev",
                    "\$device" => "SnowDev"
                ],
                "compress" => false,
                "large_threshold" => 250,
                "shard" => [0, 3],
                "presence" => [
                    "game" => [],
                    "status" => "online",
                    "since" => 91879201,
                    "afk" => false
                ]
            ]
        ]);
    }

    private function reconnect()
    {
        Logger::Warning("Connection lost, trying to reconnect...");

        $this->discord->socket->send([
            "op" => self::OP["RESUME"],
            "d" => [
                "token" => $this->discord->getBotToken(),
                "session_id" => $this->discord->botSessionId,
                "seq" => 1337
            ]
        ]);
    }

    public function disconnect()
    {
        Logger::Warning("Failed resume session, restarting!");

        $this->discord->socket->close();
        $this->discord->botConnected = false;
    }

    public function registerEventHandler(DiscordEventHandlerInterface $class)
    {
        try {
            $classArray = explode("\\", get_class($class));
            $className = end($classArray);

            if (in_array($className, self::EVENTS_HANDLER) || ($className == "ON_TICK")) {
                if (!array_key_exists($className, $this->eventsHandler)) {
                    $this->eventsHandler[$className] = $class;

                    Logger::Info("Event {$className} has been registered!");
                } else {
                    Logger::Warning("Event {$className} has already been registered!");
                }
            } else {
                Logger::Warning("Event {$className} does not exist!");
            }
        } catch (Exception $e) {
            Logger::Warning("Failed register event on class NULLED!");
        }
    }

    public function registerCommand(DiscordCommandInterface $class)
    {
        $command = strtolower($class->getCommand());

        if (array_key_exists($command, $this->commandsHandler)) {
            Logger::Warning("Command {$command} has already been registered!");
            return;
        }

        $this->commandsHandler[$command] = $class;
        $this->commandsHandler[$command]->onInit();

        Logger::Info("Command {$command} has been registered!");

        $classMethods = get_class_methods($class);

        foreach ($classMethods as $classMethod) {
            if (in_array($classMethod, self::EVENTS_HANDLER) || ($classMethod == "ON_TICK")) {
                $this->commandsEventsHandler[$classMethod][$command] = $class;

                Logger::Info("Event {$classMethod} of the command {$command} has been registered!");
            }
        }
    }
}
