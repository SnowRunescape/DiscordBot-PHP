# DiscordBot-PHP
DiscordBot-PHP is a powerful [PHP](https://github.com/php) module that allows you to easily interact with the [Discord API](https://discordapp.com/developers/docs/intro).

# Installation
PHP 5.6 or newer is required.

# Example usage
```PHP
require 'vendor\autoload.php';

require 'src/DiscordPHP.php';

$DiscordPHP = new DiscordPHP('YOU_DISCORD_BOT_TOKEN');

$DiscordPHP->includePlugins();

$DiscordPHP->init();
```

``$DiscordPHP->includePlugins();`` includes automatically all commands and events found in the ``plugins`` folder

### Example Command

```PHP
class Usage extends DiscordCommand {
    public function getCommand(){
        return '!hello';
    }
  
    public function onInit(){
        
    }
	
    public function run($DiscordPHP, $args, $event){
        $DiscordPHP->createMessage('Hey, Hello World :D', $event['channel_id']);
    }
}
```

# Help
If you don't understand something in the documentation, you are experiencing problems, or you just need a gentle nudge in the right direction, please don't hesitate to join our official [DiscordBot-PHP Server](https://discord.snowdev.com.br).
