<?php

namespace DiscordPHP;

use DiscordPHP\Logging\Logger;
use Exception;

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
        "PRESENCE_UPDATE", "READY", "TYPING_START", "USER_UPDATE", "VOICE_STATE_UPDATE", "VOICE_SERVER_UPDATE",
        "WEBHOOKS_UPDATE"
    ];

    private Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;

        $this->loadEvents();
    }

    public function executeEvent(array $event)
    {
/*         if (!$this->discord->botConnected) {
            return;
        } */

        if (
            (array_key_exists($event["t"], $this->eventsHandler)) ||
            (array_key_exists($event["t"], $this->commandsEventsHandler))
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
                    foreach($this->commandsEventsHandler[$event["t"]] as $tempEvent){
                        if($event["t"] == "ON_TICK"){
                            $tempEvent->{$event["t"]}($this);
                        } else {
                            $tempEvent->{$event["t"]}($this, $event["d"]);
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

    public function executeCommand(array $event)
    {

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

    public function loadEvents()
    {
        $this->loadEventsInternal();
        //$this->loadEventsExtras();
    }

    private function loadEventsInternal()
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

    private function loadEventsExtras()
    {
        foreach (glob(__DIR__ . "/../plugin/{commands,events}/*.php", GLOB_BRACE) as $file) {
            $classes = get_declared_classes();

            include $file;

            $className = array_values(array_diff_key(get_declared_classes(), $classes));

            if ($className[0]) {
                if (is_subclass_of($className[0], "DiscordCommand")) {
                    //$this->registerCommand(new $className[0]());
                } else if (is_subclass_of($className[0], "DiscordEventHandler")) {
                    $this->registerEventHandler(new $className[0]($this->discord));
                }
            }
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

    public function registerEventHandler($class)
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
}
