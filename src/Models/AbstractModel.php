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
    /**
     * @var array
     */
    protected $modelMap = [];

    /**
     * @var Integer
     */
    protected $id;

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function foreignKeys()
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

    public function listModels() {
        return [];
    }

    public function hasModelMap($property, $valueAsIndex=false)
    {
        $map = $valueAsIndex === true ? array_flip($this->modelMap) : $this->modelMap;
        return in_array($property, array_keys($map));
    }

    public function useModelMap($prefix, $property, $valueAsIndex=false)
    {
        $map = $valueAsIndex === true ? array_flip($this->modelMap) : $this->modelMap;
        return $this->hasModelMap($property, $valueAsIndex) ?
            $prefix . $map[$property] :
            $prefix . ucfirst($property);
    }
}