<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/12/2018
 * Time: 10:54 PM
 */

namespace Vanilla\Test\Models;

use Vanilla\Models\AbstractModel;

class MockModel extends AbstractModel
{
    const INITIALID = 1;

    /**
     * @var String
     */
    private $id;

    /**
     * @var String
     */
    private $uniqueValue;

    /**
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param String $id
     * @return MockModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return String
     */
    public function getUniqueValue()
    {
        return $this->uniqueValue;
    }

    /**
     * @param String $uniqueValue
     * @return MockModel
     */
    public function setUniqueValue($uniqueValue)
    {
        $this->uniqueValue = $uniqueValue;
        return $this;
    }


}