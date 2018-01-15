<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/13/2018
 * Time: 4:08 PM
 */

namespace Vanilla\Persist;


class PersistSql extends AbstractPersist
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var Integer
     */
    private $lastInsertId;

    /**
     * Persistence constructor.
     * @param $dsn
     * @param $user
     * @param $password
     */
    public function  __construct($dsn, $user, $password)
    {
        $this->pdo= new \PDO($dsn, $user, $password);
    }

    /**
     * Query the database
     * @param $sql
     * @param $values
     * @return array
     */
    public function query($sql, $values)
    {
        try {
            $this->pdo->beginTransaction();
            $handler = $this->pdo->prepare($sql);
            $handler->execute($values);
            $insertId = $this->pdo->lastInsertId();
            $this->pdo->commit();
        } catch (\Exception $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
        if ($handler->errorCode()) {
            $this->setLastError($handler->errorInfo());
        }
        return [ "data" => $handler->fetchAll(), "insertId" => $insertId];
    }

    /**
     * @param $model
     * @param $field
     * @return mixed
     */
    public function loadBy($model, $field)
    {
        $methodName = "get" . ucfirst($field);
        $sql = "SELECT * FROM " . $model->tableName() . " WHERE {$field} = :{$field}";
        $params = [
            $field => $model->$methodName()
        ];
        $results = $this->query($sql, $params);
        $data = $results["data"];
        foreach ($data[0] as $key => $value) {
            if (is_numeric($key) || $key == "id") {
                continue;
            }
            $setMethod = "set" . ucfirst($key);
            $model->$setMethod($value);
        }

        return $model;
    }

    /**
     * @param $model
     */
    public function save($model)
    {
        $data = $this->prepareModel($model);
        $queryResults = $this->query($data["sql"], $data["paramList"]);
        $insertId = $queryResults["insertId"];
        if (!empty($data["related"])) {
            // Get last id
            foreach ($data["related"] as $query) {
                $this->insertWithId($query, $insertId);
            }
        }
    }

    private function insertWithId($queries, $insertId = 0)
    {
        list("paramList" => $params, "sql" => $sql) = $queries;

        if ($insertId > 0) {
            if (array_key_exists(".foreignKey", $params)) {
                $params[':' . $params[".foreignKey"]] = $insertId;
                unset($params[".foreignKey"]);
            }
            $parameterKeys = [];
            foreach (array_keys($params) as $key) {
                $parameterKeys[] = substr($key, 1);
            }
            $sql  = "INSERT INTO {$queries[".table"]} (" . join(',', $parameterKeys) . ") VALUES (" . join(',', array_keys($params)) . ")";
        }


        if (!empty($queries["paramList"])) {
            list("data" => $results, "insertId" => $newInsertId) = $this->query($sql, $params);
        }

        if (!empty($queries["related"])) {
            foreach ($queries["related"] as $query) {
                $this->insertWithId($query, $newInsertId);
            }
        }
    }

    /**
     * @param $model
     * @return array
     */
    public function prepareModel($model)
    {
        $queries = [];
        $params = [];
        $parameterKeys = [];

        // Class methods prepended with get are table column names
        foreach (get_class_methods($model) as $method) {
            if (!empty(preg_match("/^(get)+/", $method))) {
                $key = ucfirst(substr($method, 3));
                $value = $model->$method();
                if (is_object($value)) {
                    $queries[] = $this->prepareModel($value);
                } else if (is_array($value)) {
                    // Check back
                    foreach($value as $k => $v) {
                        if (is_object($v)) {
                            $queries[] = $this->prepareModel($v);
                        }
                    }
                } else {
                    if (!empty($value)) {
                        $params[':' . lcfirst($key)] = $value;
                        $parameterKeys[] = lcfirst($key);
                    }
                }
            }
        }
        if (!is_null($model->foreignKey())) {
            $params[".foreignKey"] = $model->foreignKey();
        }
        if (!$model->tableName()) {
            $class = new \ReflectionClass($model);
            $table = strtolower($class->getShortName());
        } else {
            $table = $model->tableName();
        }
        return [
            ".table" => $table,
            "sql" => "INSERT INTO {$table} (" . join(',', $parameterKeys ) . ") VALUES (" . join(',', array_keys($params)) . ")",
            "paramList" => $params,
            "related" => $queries
        ];
    }
}