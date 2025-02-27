<?php

namespace DiscordPHP\Logging;

class Logger
{
    private static $saveLog = true;
    private static $fpLoghasOpened = false;

    private static $fpLog;

    const LOGGER_ID_0 = "INFO";
    const LOGGER_ID_1 = "WARNING";

    private static function console(string $text, string $type)
    {
        $date = date("d-m-Y H:i:s");
        $type = constant("self::LOGGER_ID_{$type}");

        $t = "{$date} [SwBot] [{$type}] {$text}\n";

        echo $t;

        if (self::$saveLog) {
            if (!self::$fpLoghasOpened) {
                self::$fpLoghasOpened = true;

                self::$fpLog = @fopen(__DIR__ . "/../../CONSOLE_LOG.txt", "a");
            }

            if (self::$fpLog !== false) {
                fwrite(self::$fpLog, $t);
            }
        }
    }

    public static function Info($text)
    {
        self::console($text, 0);
    }

    public static function Warning($text)
    {
        self::console($text, 1);
    }
}
