<?php
class Ping extends DiscordCommand {
	private $pingHandler = [];
	
	public function getCommand(){
		return '!ping';
	}
	
	public function onInit(){
		
	}
	
	public function run($DiscordPHP, $args, $event){
		$receivedPing = round(microtime(true) * 1000);
		
		$message = $DiscordPHP->createMessage('Pong!', $event['channel_id']);
		
		$pingAPI = round(microtime(true) * 1000);
		
		$this->pingHandler[$message['id']] = ['timingOnSend' => $receivedPing, 'TimingAPI' => $pingAPI];
	}
	
	public function MESSAGE_CREATE($DiscordPHP, $event){
		if(array_key_exists($event['id'], $this->pingHandler)){
			$ping = round(microtime(true) * 1000) - $this->pingHandler[$event['id']]['timingOnSend'];
			$pingAPI = $this->pingHandler[$event['id']]['TimingAPI'] - $this->pingHandler[$event['id']]['timingOnSend'];
			
			$DiscordPHP->editMessage($event['id'], "Pong! A Latência é {$ping}ms. A Latência da API é {$pingAPI}ms.", $event['channel_id']);
			
			unset($this->pingHandler[$event['id']]);
		}
	}
}