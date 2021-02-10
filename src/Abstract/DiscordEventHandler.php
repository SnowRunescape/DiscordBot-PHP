<?php
abstract class DiscordEventHandler {
	abstract public function onInit();
	
    abstract public function run($DiscordPHP, $event);
}