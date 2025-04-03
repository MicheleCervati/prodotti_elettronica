<?php

require_once 'functions.php';

class DBconn
{
    private static PDO $db;


    public static function getDB(array $config): PDO
    {

        if (!isset(self::$db)) {
            try {
                self::$db = new PDO($config['dsn'], $config['username'], $config['passwd'], $config['options']);
            } catch (PDOException $e) {
                logError($e);
            }
        }
        return self::$db;
    }
}