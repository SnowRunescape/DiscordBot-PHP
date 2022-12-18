<?php

namespace DiscordPHP;

use DiscordPHP\Logging\Logger;
use Exception;
use Throwable;

class Event
{
    const OP = [
        "DISCONNECT" => 9,
        "AUTHENTICATION" => 10
    ];

    private array $commandsHandler = [];
    private array $commandsEventsHandler = [];
    private array $eventsHandler = [];

    private static $defaultEventsHandler = [
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
        if ($event["op"] == 0) {
            $this->executeCommand($event);
            $this->executeEvent($event);
        } else if ($event["op"] == self::OP["AUTHENTICATION"]) {
            $this->discord->botConnected ?
                $this->reconnect() :
                $this->authentication();
        } else if ($event["op"] == self::OP["DISCONNECT"]) {
            $this->disconnect();
        }
    }

    public function executeCommand(array $event)
    {
        if ($event["t"] == "MESSAGE_CREATE") {
            $args = explode(" ", $event["d"]["content"]);

            $command = strtolower($args[0]);

            if (array_key_exists($command, $this->commandsHandler)) {
                Logger::Info("Command {$command} has ben Detected!");

                try {
                    $this->commandsHandler[$command]->run($event["d"], $args);
                } catch (Throwable $e) {
                    $className = get_class($this->commandsHandler[$command]);

                    Logger::Warning("Error when executing the class {$className}!");
                    Logger::Warning($e->getMessage());
                }
            }
        }
    }

    public function executeEvent(array $event)
    {
/*         if (!$this->discord->botConnected) {
            return;
        } */

try {
    if (
        array_key_exists($event["t"], $this->eventsHandler) ||
        array_key_exists($event["t"], $this->commandsEventsHandler)
    ) {
        if ($event["t"] != "ON_TICK") {
            Logger::Info("Event {$event["t"]} has ben received!");
        }

        if (array_key_exists($event["t"], $this->eventsHandler)) {
            try {
                $this->eventsHandler[$event["t"]]->run($event);
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
} catch (\Throwable $th) {
    echo $th;
}
    }

    public function loadEvents()
    {
        try {
            foreach (glob(__DIR__ . "/Events/*.php", GLOB_BRACE) as $file) {
                $className = basename($file, ".php");
                $className = "\\DiscordPHP\\Events\\{$className}";

                $class = new $className($this->discord);
                $this->registerEventHandler($class);
            }
        } catch (\Throwable $th) {
            echo $th;
        }
    }

    private function authentication()
    {
        Logger::Info("Authenticating bot to discord...");

        $this->discord->socket->send([
            "op" => 2,
            "d" => [
                "token" => $this->discord->getBotToken(),
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
            "op" => 6,
            "d" => [
                "token" => $this->discord->getBotToken(),
                "session_id" => $this->sessionBotID,
                "seq" => 1337
            ]
        ]);
    }

    private function disconnect()
    {
        Logger::Warning("Failed resume session, restarting!");

        $this->socket = null;
        $this->botConnected = false;
    }

    public function registerEventHandler(object $class)
    {
        try {
            $className = max(explode("\\", get_class($class)));

            if (is_subclass_of($class, "\DiscordPHP\Abstracts\DiscordEventHandler")) {
                if ((in_array($className, self::$defaultEventsHandler)) || ($className == "ON_TICK")) {
                    if (!array_key_exists($className, $this->eventsHandler)) {
                        $this->eventsHandler[$className] = $class;

                        Logger::Info("Event {$className} has ben Registred!");
                    } else {
                        Logger::Warning("Event {$className} has already been registered!");
                    }
                } else {
                    Logger::Warning("Event {$className} does not exist!");
                }
            } else {
                Logger::Warning("Failed register event on class {$className}!");
            }
        } catch (Exception $e) {
            Logger::Warning("Failed register event on class NULLED!");
        }
    }

    public function registerCommand(object $class)
    {
        $command = strtolower($class->getCommand());

        if (array_key_exists($command, $this->commandsHandler)) {
            Logger::Warning("Command {$command} has already been registered!");
            return;

        }

        $this->commandsHandler[$command] = $class;
        $this->commandsHandler[$command]->onInit();

        Logger::Info("Command {$command} has ben Registred!");

        $classMethods = get_class_methods($class);

        foreach ($classMethods as $classMethod) {
            if ((in_array($classMethod, self::$defaultEventsHandler)) || ($classMethod == "ON_TICK")) {
                $this->commandsEventsHandler[$classMethod][$command] = $class;

                Logger::Info("Event {$classMethod} of the command {$command} has ben Registred!");
            }
        }
    }
}
