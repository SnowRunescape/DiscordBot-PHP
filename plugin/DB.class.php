<?php
class DB {
	private static $connection;
	
	private static $host;
	private static $username;
	private static $password;
	private static $db;
	
	public function __construct($host, $username, $password, $db){
		self::$host = $host;
		self::$username = $username;
		self::$password = $password;
		self::$db = $db;
	}
	
	public static function conn(){
        if(self::$connection == null) {
            self::init();
        }
		
        try {
            self::$connection->query("SELECT 1");
        } catch(PDOException $e){
			Logger::Warning('Database connection failed, reinitializing...');
			
            self::init();
        }
		
		return self::$connection;
	}
	
	private static function init(){
		Logger::Info('Initializing connection to database...');
		
		$start = round(microtime(true) * 1000);
		
		$host = self::$host;
		$username = self::$username;
		$password = self::$password;
		$db = self::$db;
		
		try {
			self::$connection = new PDO("mysql:host={$host};dbname={$db}", $username, $password, [PDO::ATTR_PERSISTENT => true]);
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$end = round(microtime(true) * 1000);
			
			$tt = $end - $start;
			
			Logger::Info('Database connection established successfully!');
			Logger::Info("Conexão estabelecida em {$tt}ms");
		} catch(PDOException $e){
			Logger::Warning('Error connecting to database!');
			Logger::Warning($e->getMessage());
			
			exit;
		}
	}
}