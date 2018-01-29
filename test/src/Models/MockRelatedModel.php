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
    private $fkId;

    /**
     * @return mixed
     */
    public function getFkId()
    {
        return $this->fkId;
    }

    /**
     * @param mixed $fkId
     * @return MockRelatedModel
     */
    public function setFkId($fkId)
    {
        $this->fkId = $fkId;
        return $this;
    }



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

    public function foreignKeys()
    {
        return [
            "MockModel" => "fkId"
        ];
    }

    public function tableName()
    {
        return "tbl_mockrelatedmodel";
    }
}

