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
        $driver = !empty($options) && in_array("driver", $options) ? $options['driver'] : getenv("SQL_DRIVER");
        $host = !empty($options) && in_array("host", $options) ? $options["host"] : getenv("SQL_HOST");
        $port = !empty($options) && in_array("port", $options) ? $options["port"] : getenv("SQL_PORT");
        $dbname = !empty($options) && in_array("dbname", $options) ? $options["dbname"] : getenv("SQL_DB");
        $password = !empty($options) && in_array("password", $options) ? $options["password"] : getenv("SQL_PASSWORD");
        $user = !empty($options) && in_array("user", $options) ? $options["user"] : getenv("SQL_USER");
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