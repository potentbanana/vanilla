<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/12/2018
 * Time: 11:04 PM
 */

namespace Vanilla\Models;


abstract class AbstractModel implements ModelInterface
{
    public function foreignKey()
    {
        return null;
    }

    public function toArray()
    {
        $objArray = [];
        foreach(get_class_methods($this) as $method) {
            if (!empty(preg_match("/^(get)+/", $method))) {
                $key = ucfirst(substr($method, 3));
                $value = $this->$method();
                if ($value instanceof AbstractModel) {
                    $objArray[$key] = $value->toArray();
                } else {
                    $objArray[$key] = $value;
                }
            }
        }
        return $objArray;
    }

    public function tableName()
    {
        $class = new \ReflectionClass($this);
        return strtolower($class->getShortName());
    }
}