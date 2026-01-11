<?php
class Database {
    private static ?PDO $db = null;

    public static function get(): PDO {
        if (!self::$db) {
            $config = require __DIR__ . '/../../config/config.php';
            self::$db = new PDO('sqlite:' . $config['db_path']);
        }
        return self::$db;
    }
}
