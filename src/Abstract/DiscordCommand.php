<?php
abstract class DiscordCommand {
    abstract public function getCommand();
	
    abstract public function onInit();
	
    abstract public function run($DiscordPHP, $args, $event);
}