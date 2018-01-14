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


    /**
     * @param array $options
     * @return Persist|PersistBase|PersistSql
     */
    public static function getInstance($options = [], $useBase = false)
    {
        if (!is_null(self::$handler)) {
            return self::$handler;
        }
        $driver = getenv("SQL_DRIVER");
        $host = getenv("SQL_HOST");
        $port = getenv("SQL_PORT");
        $dbname = getenv("SQL_DB");
        $password = getenv("SQL_PASSWORD");
        $user = getenv("SQL_USER");
        $dsn = "$driver:dbname=$dbname;host=$host;port=$port";
        if (($dsn && $user && $password) && !$useBase) {
            self::$handler = new PersistSql($dsn, $user, $password);
        } else {
            self::$handler = new PersistBase();
        }

        return self::$handler;
    }

    public static function clearInstance()
    {
        self::$handler = null;
    }
}