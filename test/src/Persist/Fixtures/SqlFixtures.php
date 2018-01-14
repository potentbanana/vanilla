<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/13/2018
 * Time: 7:52 PM
 */

namespace Vanilla\Test\Persist\Fixtures;


use Vanilla\Test\Models\MockModel;

class SqlFixtures
{
    private $pdo;
    private $tableName;

    public function __construct()
    {
        $driver = getenv("SQL_DRIVER");
        $host = getenv("SQL_HOST");
        $port = getenv("SQL_PORT");
        $dbname = getenv("SQL_DB");
        $password = getenv("SQL_PASSWORD");
        $user = getenv("SQL_USER");
        $dsn = "$driver:dbname=$dbname;host=$host;port=$port";
        if (!$dsn || !$user || !$password) {
            throw new \Exception("Unable to determine SQL connection; exiting test.");
        }
        $this->pdo = new \PDO($dsn, $user, $password);

    }

    public function setUp()
    {
        $sql = <<<SQL
        CREATE TABLE {$this->tableName} (id SERIAL, uniqueValue TEXT);
SQL;

        $preparedStatement = $this->pdo->prepare($sql);
        try {
            $preparedStatement->execute();
        } catch (\Exception $exception) {
            echo $exception->getMessage() . "\n";
            $this->pdo->rollBack();
        }

        // Now create 10 models with unique values
        for ($i = 0; $i < 10; $i++) {
            $uniqId = uniqid();
            $sql = "INSERT INTO {$this->tableName} (uniqueValue) VALUES (\"$uniqId\");";
            $pdoStatement = $this->pdo->prepare($sql);
            $pdoStatement->execute();
        }
    }

    public function tearDown()
    {
        $preparedStatement = $this->pdo->prepare("DROP TABLE {$this->tableName};");
        $preparedStatement->execute();
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
    public function getTableName()
    {
        return $this->tableName;
    }
}