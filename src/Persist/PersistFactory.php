<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/5/2018
 * Time: 12:50 AM
 */

namespace Vanilla\Persist;


class PersistFactory
{
    const MYSQL = "mysql";
    const POSTGRES = "postgres";

    /**
     * @var Persist
     */
    private static $handler = null;



    public static function getInstance($persistType = null, $options = [])
    {
        if (!is_null(self::$handler)) {
            return self::$handler;
        }
        $persistType = strtolower($persistType);
        if (!empty($options)) {
            list("dbname" => $dbname,
                "host" => $host,
                "port" => $port,
                "user" => $user,
                "password" => $password) = $options;
            if ($persistType === self::MYSQL) {
                $dsn = "mysql:dbname={$dbname};host={$host};port={$port}";
                self::$handller = new PersistMysql($dsn, $user, $password);
            } else {
                if ($persistType === self::POSTGRES) {
                    $dsn = "postgres:dbname={$dbname};host={$host};port={$port}";
                    self::$handler = new PersistPostgres($dsn, $user, $password);
                }
            }
        }

        self::$handler = new PersistBase();

        return self::$handler;
    }

    public static function clearInstance()
    {
        self::$handler = null;
    }
}