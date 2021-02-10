<?php
require 'Logging/Logger.class.php';

require 'Resources/Discord.php';
require 'Resources/MessageEmbed.php';
require 'Resources/Utils.php';
require 'Resources/websocket/Client.php';

require 'Abstract/DiscordEventHandler.php';
require 'Abstract/DiscordCommand.php';

class DiscordPHP extends Discord {
	private $tokenBot;
	private $sessionBotID;
	
	private $tryReconection = 0;
	private $lastSendKeepAlive = 0;
	private $lastReadSocketEmpty = 0;
	
	private $commandsHandler = [];
	private $commandsEventsHandler = [];
	private $eventsHandler = [];
	
	private static $defaultEventsHandler = [
		'CHANNEL_CREATE', 'CHANNEL_UPDATE', 'CHANNEL_DELETE', 'CHANNEL_PINS_UPDATE', 'GUILD_CREATE', 'GUILD_UPDATE',
		'GUILD_DELETE', 'GUILD_BAN_ADD', 'GUILD_BAN_REMOVE', 'GUILD_EMOJIS_UPDATE', 'GUILD_INTEGRATIONS_UPDATE',
		'GUILD_MEMBER_ADD', 'GUILD_MEMBER_REMOVE', 'GUILD_MEMBER_UPDATE', 'GUILD_MEMBERS_CHUNK', 'GUILD_ROLE_CREATE',
		'GUILD_ROLE_UPDATE', 'GUILD_ROLE_DELETE', 'MESSAGE_CREATE', 'MESSAGE_UPDATE', 'MESSAGE_DELETE',
		'MESSAGE_DELETE_BULK', 'MESSAGE_REACTION_ADD', 'MESSAGE_REACTION_REMOVE', 'MESSAGE_REACTION_REMOVE_ALL',
		'PRESENCE_UPDATE', 'TYPING_START', 'USER_UPDATE', 'VOICE_STATE_UPDATE', 'VOICE_SERVER_UPDATE', 'WEBHOOKS_UPDATE'
	];
	
	private $botConnected = false;
	private $forceStop = false;
	
	private $socket;
	
	public function __construct($tokenBot){
		Logger::Info('Starting DiscordPHP v2.0.0');
		
		$this->tokenBot = $tokenBot;
	}
	
	public function getBotToken(){
		return $this->tokenBot;
	}
	
	public function init(){
		if($this->socket == null){
			while(!$this->forceStop){
				if($this->tryReconection > 3) $this->tryReconection = 0;
				
				try {
					$this->socket = new Client('wss://gateway.discord.gg/?v=6&encoding=json');

					Logger::Info('Starting WebSocket...');
					
					$this->process(0);
					
					while(($this->botConnected) && (!$this->forceStop) && ($this->socket)){
						$this->process();
					}
				} catch(Exception $e){
					if($this->botConnected) $this->tryReconection++;
					
					$this->socket = null;
					
					Logger::Warning('Connection lost, attempting to reconnect in 10 seconds...');

					sleep(10);
				}
			}
		} else {
			Logger::Warning('The websocket is already running!');
		}
	}
	
	public function includePlugins(){
		foreach(glob(__DIR__ . '/../plugin/{commands,events}/*.php', GLOB_BRACE) as $file){
			$classes = get_declared_classes();
			
			include $file;
			
			$className = array_values(array_diff_key(get_declared_classes(), $classes));
			
			if($className[0]){
				if(is_subclass_of($className[0], 'DiscordCommand')) $this->registerCommand(new $className[0]());
				if(is_subclass_of($className[0], 'DiscordEventHandler')) $this->registerEventHandler(new $className[0]());
			}
		}
	}
	
	public function registerCommand($class){
		if(is_subclass_of($class, 'DiscordCommand')){
			$command = strtolower($class->getCommand());
			
			if(!array_key_exists($command, $this->commandsHandler)){
				$this->commandsHandler[$command] = $class;
				
				$this->commandsHandler[$command]->onInit();
				
				Logger::Info("Command {$command} has ben Registred!");
				
				$class_methods = get_class_methods($class);

				foreach($class_methods as $method_name){
					if((in_array($method_name, self::$defaultEventsHandler)) || ($method_name == 'ON_TICK')){
						$this->commandsEventsHandler[$method_name][$command] = $class;
						
						Logger::Info("Event {$method_name} of the command {$command} has ben Registred!");
					}
				}
			} else {
				Logger::Warning("Command {$command} has already been registered!");
			}
		} else {
			$className = get_class($class);
			
			Logger::Warning("Failed register command on class {$className}!");
		}
	}
	
	public function registerEventHandler($class){
		try {
			$className = get_class($class);
			
			if(is_subclass_of($class, 'DiscordEventHandler')){
				if((in_array($className, self::$defaultEventsHandler)) || ($className == 'ON_TICK')){
					if(!array_key_exists($className, $this->eventsHandler)){
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
		} catch(Exception $e){
			Logger::Warning("Failed register event on class NULLED!");
		}
	}
	
	public function setPresence($name, $type, $status, $channel){
		if(($type == 0) || ($type == 1) || ($type == 2)){
			if(($status == 'online') || ($status == 'dnd') || ($status == 'idle') || ($status == 'invisible') || ($status == 'offline')){
				Logger::Info('RichPresence was changed.');
				
				$this->socket->send('{ "op": 3, "d": { "since": 91879201, "game": { "name": "'.$name.'", "type": '.$type.' }, "status": "'.$status.'", "afk": false } }');
			}
		}
	}
	
	private function process($stop = 11){
		$keepAliveSent = false;
		$op = '';

		while($op !== $stop and $this->socket != null){
			$this->executeEvent(['t' => 'ON_TICK']);
			
			try {
				if(time() >= ($this->lastSendKeepAlive + 30)){
					$this->lastSendKeepAlive = time();
					
					$keepAliveSent = true;
					
					$this->socket->send('{"op": 1, "d": 251}');
				}
				
				$receive = json_decode($this->socket->receive(), true);
								
				$op = $receive['op'];
				
				if(($op == 11) && ($keepAliveSent)){
					$keepAliveSent = false;
				}
				
				$this->eventHandler($receive);
			} catch(Exception $e){
				if($this->lastReadSocketEmpty == time()) throw new Exception($e);
				if($keepAliveSent) throw new Exception($e);
				
				$this->lastReadSocketEmpty = time();
			}
		}
	}
	
	private function eventHandler($event){
		$op = $event['op'];
		
		$this->isReady($event);
		
		if($op == 0){
			$this->executeCommand($event);
			$this->executeEvent($event);
		} else if($op == 9){
			$this->socket = null;
			$this->botConnected = false;
			
			Logger::Warning('Failed resume session, restarting!');
		} else if($op == 10){
			if(!$this->botConnected){
				Logger::Info('Authenticating bot to discord...');
				
				$this->socket->send('{ "op": 2, "d": { "token": "'.$this->tokenBot.'", "properties": { "$os": "windows", "$browser": "SnowDev", "$device": "SnowDev" }, "compress": false, "large_threshold": 250, "shard": [0,3], "presence": { "game": {}, "status": "online", "since": 91879201, "afk": false } } }');
			} else {
				Logger::Warning('Connection lost, trying to reconnect...');
				
				$this->socket->send('{ "op": 6, "d": { "token": "'.$this->tokenBot.'", "session_id": "'.$this->sessionBotID.'", "seq": 1337 } }');
			}
		}
	}
	
	private function executeCommand($event){
		if($event['t'] == 'MESSAGE_CREATE'){
			$args = explode(' ', $event['d']['content']);
			
			$command = strtolower($args[0]);
			
			if(array_key_exists($command, $this->commandsHandler)){
				Logger::Info("Command {$command} has ben Detected!");
				
				try {
					$this->commandsHandler[$command]->run($this, $args, $event['d']);
				} catch(Exception $e){
					$className = get_class($this->commandsHandler[$command]);
					
					Logger::Warning("Error when executing the class {$className}!");
					Logger::Warning($e->getMessage());
				}
			}
		}
	}
	
	private function executeEvent($event){
		if(!$this->botConnected) return;
		
		if((array_key_exists($event['t'], $this->eventsHandler)) || (array_key_exists($event['t'], $this->commandsEventsHandler))){
			if($event['t'] != 'ON_TICK') Logger::Info("Event {$event['t']} has ben received!");
			
			if(array_key_exists($event['t'], $this->eventsHandler)){
				try {
					$this->eventsHandler[$event['t']]->run($this, $event['d']);
				} catch(Exception $e){
					$className = get_class($this->eventsHandler[$event['t']]);
					
					Logger::Warning("Error when executing the class {$className}!");
					Logger::Warning($e->getMessage());
				}
			}
			
			if(array_key_exists($event['t'], $this->commandsEventsHandler)){
				try {
					foreach($this->commandsEventsHandler[$event['t']] as $tempEvent){
						if($event['t'] == 'ON_TICK'){
							$tempEvent->{$event['t']}($this);
						} else {
							$tempEvent->{$event['t']}($this, $event['d']);
						}
					}
				} catch(Exception $e){
					/* MUDAR A MENSAGEM DE ERRO AQUI... */
					$className = get_class($this->commandsEventsHandler[$event['t']]);
					
					Logger::Warning("Error when executing the class {$className}!");
					Logger::Warning($e->getMessage());
				}
			}
		}
	}
	
	private function isReady($event){
		switch($event['t']){
			case 'READY':
				if(!$this->botConnected){
					$this->botConnected = true;
					
					$this->sessionBotID = $event['d']['session_id'];
					
					Logger::Info('Bot has been authenticated successfully!');
					Logger::Info("SessionID of the current connection is {$this->sessionBotID}");
					
					$this->createMessage('BOT INICIADO!', '678214248492695552');
				}
				
				break;
			case 'RESUMED':
				Logger::Info("Your connection to the discord has been resumed!");
				
				break;
			default:
				if(($event == null) && (!$this->botConnected)){
					$this->socket = null;
					
					$this->botConnected = false;
					$this->forceStop = true;
					
					Logger::Warning('Failed to authenticate TOKEN to discord!');
				}
		}
	}
}