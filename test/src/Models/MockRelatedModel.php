<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/14/2018
 * Time: 3:55 PM
 */

namespace Vanilla\Test\Models;

use Vanilla\Models\AbstractModel;

class MockRelatedModel extends AbstractModel
{
    private $id;
    private $someValue;
    private $tableName;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return MockRelatedModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSomeValue()
    {
        return $this->someValue;
    }

    /**
     * @param mixed $someValue
     * @return MockRelatedModel
     */
    public function setSomeValue($someValue)
    {
        $this->someValue = $someValue;
        return $this;
    }

    public function foreignKey()
    {
        return "fkId";
    }

    public function setTableName($tableName=null)
    {
        $this->tableName = $tableName;
    }

    public function tableName()
    {
        return $this->tableName;
    }
}

