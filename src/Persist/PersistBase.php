<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/12/2018
 * Time: 3:24 PM
 */

namespace Vanilla\Persist;

use Vanilla\Models\ModelInterface;

/**
 * Class PersistBase
 * @package Vanilla\Persist
 */
class PersistBase extends AbstractPersist implements PersistBaseInterface
{
    private $data = [];

    private $index = [];

    public function __construct()
    {
        // TODO: Add the actual persistence to a file.
    }

    public function load(ModelInterface $model, $id)
    {
        $index = $id - 1;
        $table = $model->tableName();
        $data = $this->data[$table][$index];
        foreach ($data as $key => $value) {
            $method = "set" . ucfirst($key);
            $model->$method($value);
        }
        $model->setId($index + 1);
    }


    public function save(ModelInterface $model)
    {
        $this->insertWithId($this->prepare($model), 0);
    }

    public function insertWithId($nextModel, $index)
    {
        list("table" => $table, "data" => $data, "related" => $related) = $nextModel;
        if ($index > 0) {
            if (array_key_exists(".foreignKey", $data)) {
                $data[$data[".foreignKey"]] = $index;
                unset($data[".foreignKey"]);
            }
        }

        if(!array_key_exists($table, $this->data)) {
            $this->data[$table] = [];
            $this->index[$table] = 0;
        } else {
            $this->index[$table] += 1;
        }
        $this->data[$table][] = $data;
        $newIndex = $this->index[$table];

        if (!empty($related)) {
            foreach ($related as $nextData) {
                $this->insertWithId($nextData, $newIndex);
            }
        }
    }

    /**
     * @param $model
     * @return array
     */
    public function prepare($model)
    {
        $queries = [];
        $params = [];
        foreach (get_class_methods($model) as $method) {
            if (!empty(preg_match("/^(get)+/", $method))) {
                $key = ucfirst(substr($method, 3));
                $value = $model->$method();
                if (is_object($value)) {
                    $queries[] = $this->prepare($value);
                } else if (is_array($value)) {
                    // Check back
                    foreach($value as $k => $v) {
                        if (is_object($v)) {
                            $queries[] = $this->prepare($v);
                        }
                    }
                } else {
                    if (!empty($value)) {
                        $params[lcfirst($key)] = $value;
                    }
                }
            }
        }
        if (!is_null($model->foreignKey())) {
            $params[".foreignKey"] = $model->foreignKey();
        }
        $table = $model->tableName();

        return [
            "table" => $table,
            "data" => $params,
            "related" => $queries
        ];
    }
}