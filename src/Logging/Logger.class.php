<?php
class Logger {
	private static $SaveLog = true;
	private static $fpLoghasOpened = false;
	
	private static $fpLog;
	
	const LOGGER_ID_0 = "INFO";
	const LOGGER_ID_1 = "WARNING";
	
	private static function console($text, $type){
		$date = date("d-m-Y H:i:s");
		$type = constant("self::LOGGER_ID_{$type}");
		
		$t = "{$date} [SwBot] [{$type}] {$text}\n";
		
		echo $t;
		
		if(self::$SaveLog){
			if(!self::$fpLoghasOpened){
				self::$fpLoghasOpened = true;
				
				self::$fpLog = fopen(__DIR__ . '/../../log/CONSOLE_LOG.txt', 'a');
			}
			
			fwrite(self::$fpLog, $t);
		}
	}
	
	public static function Info($text){
		self::console($text, 0);
	}
	
	public static function Warning($text){
		self::console($text, 1);
	}
}