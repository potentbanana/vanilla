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
    private $tableRelatedName;

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

    public function setUpMainTable()
    {
        $sql = <<<SQL
        CREATE TABLE {$this->tableName} 
        (
          id SERIAL PRIMARY KEY, 
          uniqueValue TEXT,
          secondValue TEXT
        );

SQL;

        $preparedStatement = $this->pdo->prepare($sql);
        try {
            $preparedStatement->execute();
        } catch (\Exception $exception) {
            echo $exception->getMessage() . "\n";
            $this->pdo->rollBack();
        }
    }

    public function setUpRelatedTable()
    {
        $sql = <<<SQL
        CREATE TABLE {$this->tableRelatedName} 
        (
          id SERIAL, 
          someValue TEXT, 
          fkId integer references {$this->tableName}(id)
        );
SQL;

        $preparedStatement = $this->pdo->prepare($sql);
        try {
            $preparedStatement->execute();
        } catch (\Exception $exception) {
            echo $exception->getMessage() . "\n";
            $this->pdo->rollBack();
        }
    }

    public function setUp()
    {
        $this->setUpMainTable();
        $this->setUpRelatedTable();
    }

    public function tearDown()
    {
        $preparedStatement = $this->pdo->prepare("DROP TABLE {$this->tableName} CASCADE;");
        $preparedStatement->execute();

        $preparedStatement2 = $this->pdo->prepare("DROP TABLE {$this->tableRelatedName} CASCADE;");
        $preparedStatement2->execute();
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function setTableRelatedName($tableRelatedName)
    {
        $this->tableRelatedName = $tableRelatedName;
    }

    public function getTableName()
    {
        return $this->tableName;
    }
}