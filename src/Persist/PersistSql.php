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
        return [ "data" => $handler->fetchAll(\PDO::FETCH_ASSOC), "insertId" => $insertId];
    }

    /**
     * @param $model
     * @param $field
     * @return mixed
     */
    public function loadBy($model, $field, $returnArray = false)
    {
        $models = [];
        $sql = "SELECT * FROM " . $model->tableName() . " WHERE ";
        $params = [];
        if (is_array($field)) {
            foreach ($field as $f) {
                $methodName = $model->hasModelMap($f) ? $model->useModelMap("get", $f) : "get" . ucfirst($f);
                $paramName = ":{$f}";
                $sql .= "{$f} = :{$f} AND ";
                $params[$paramName] = $model->$methodName();
            }
            $sql = rtrim($sql, "AND ");
        } else {
            $methodName = $model->hasModelMap($field) ? $model->useModelMap("get", $field) : "get" . ucfirst($field);
            $paramName = ":{$field}";
            $params = [
                $paramName => $model->$methodName()
            ];
            $sql .= "{$field} = {$paramName}";
        }
        $results = $this->query($sql, $params);
        $data = $results["data"];
        if (count($data) == 1 && $returnArray !== true) {
            foreach ($data[0] as $key => $value) {
                $setMethod = $model->hasModelMap($key) ? $model->useModelMap("set", $key) : "set" . ucfirst($key);
                $model->$setMethod($value);
            }
        } else {
            foreach($data as $record) {
                $namespaceAndClass = "\\" . get_class($model);
                $newModel = new $namespaceAndClass();
                foreach($record as $key => $value) {
                    $setMethod = $newModel->hasModelMap($key) ? $newModel->useModelMap("set", $key) : "set" . ucfirst($key);
                    $newModel->$setMethod($value);
                }
                $models[] = clone($newModel);
                unset($newModel);
            }
        }
        if(!empty($model->listModels())) {
            $namespaceModel = get_class($model);
            $namespace = substr($namespaceModel, 0, strripos($namespaceModel, "\\"));
            foreach ($model->listModels() as $class => $key) {
                $namespaceAndModel = "\\{$namespace}\\{$class}";
                $newModel = new $namespaceAndModel();
                $setMethod = $newModel->hasModelMap($key) ? $newModel->useModelMap("set", $key) : "set" . ucfirst($key);
                $newModel->$setMethod($model->getId());
                $setArrayMethod = $model->hasModelMap($class) ? $model->useModelMap("set", $class) : "set" . ucfirst($class);
                $model->$setArrayMethod($this->loadBy($newModel, $key, true));
                unset($newModel);
            }
        }
        return empty($models) ? $model : $models;
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

        $model->setId($insertId);
    }

    /**
     * @param $table
     * @param $params
     * @return string
     */
    private function createInsertStatement($table, $params)
    {
        $parameterKeys = [];
        foreach (array_keys($params) as $key) {
            $parameterKeys[] = substr($key, 1);
        }
        $sql  = "INSERT INTO {$table} (" . join(',', $parameterKeys) . ") VALUES (" . join(',', array_keys($params)) . ")";

        return $sql;
    }

    /**
     * @param $queries
     * @param int $insertId
     */
    private function insertWithId($queries, $insertId = 0)
    {
        list("paramList" => $params) = $queries;

        if ($insertId > 0) {
            if (array_key_exists(".foreignKeys", $params)) {
                foreach ($params[".foreignKeys"] as $foreignModel => $foreignField) {
                    $params[':' . $foreignField] = $insertId;
                }
                unset($params[".foreignKeys"]);
            }
        } else {
            if (array_key_exists(".foreignKeys", $params)) {
                unset($params['.foreignKeys']);
            }
        }
        $sql = $this->createInsertStatement($queries[".table"], $params);


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
        if (!empty($model->foreignKeys())) {
            $params[".foreignKeys"] = $model->foreignKeys();
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