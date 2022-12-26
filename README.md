# DiscordBot-PHP
DiscordBot-PHP is a powerful [PHP](https://github.com/php) module that allows you to easily interact with the [Discord API](https://discordapp.com/developers/docs/intro).

![Console](https://i.imgur.com/2Svi59h.png)

# Installation
Preferred way to install is with [Composer](https://getcomposer.org/).

```
composer require snowrunescape/discord-bot-php
```

PHP 7.4 or newer is required.

# Example usage
You can see an example of how to use it by [clicking here](https://github.com/SnowRunescape/DiscordBot-PHP-Example).

```PHP
require_once "vendor/autoload.php";

use DiscordPHP\Discord;

$discord = new Discord("YOU_DISCORD_BOT_TOKEN");
$discord->run();
```

Register commands and events before triggering the `$discord->run();`

To register a command use the code
```PHP
$discord->event->registerCommand(new Ping($discord));
```

To register events use the code
```PHP
$discord->event->registerEventHandler(new MESSAGE_CREATE($discord));
```

### Example Command

```PHP
class Ping extends DiscordCommand
{
    public function getCommand()
    {
        return "!ping";
    }

    public function onInit()
    {
        Logger::Info("Starting command...");
    }

    public function run(array $event, array $args)
    {
        $this->discord->discordAPI->createMessage("Pong!", $event["channel_id"]);
    }
}
```

Events can be created inside commands, to keep the code organized

```PHP
public function MESSAGE_CREATE($event)
{
    Logger::Info("This event handler has been called!");
}
```

### Example eventHandler

```PHP
class MESSAGE_CREATE extends DiscordEventHandler
{
    public function onInit() {
        Logger::Info("Starting eventHandler...");
    }

    public function run(array $event)
    {
        Logger::Info("This event handler has been called!");
    }
}
```

# Credits
* Textalk | [Github](https://github.com/Textalk) | [Website](https://www.textalk.se/) 

# Help
If you don't understand something in the documentation, you are experiencing problems, or you just need a gentle nudge in the right direction, please don't hesitate to join our official [DiscordBot-PHP Server](https://discord.snowdev.com.br).
