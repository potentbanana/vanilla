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
    private $tableName;

    /**
     * @var String
     */
    private $uniqueValue;

    /**
     * @var String
     */
    private $secondValue;

    /**
     * @var MockRelatedModel
     */
    private $mockRelatedModel;

    /**
     * @return MockRelatedModel
     */
    public function getMockRelatedModel()
    {
        return $this->mockRelatedModel;
    }

    /**
     * @param MockRelatedModel $mockRelatedModel
     * @return MockModel
     */
    public function setMockRelatedModel($mockRelatedModel)
    {
        $this->mockRelatedModel = $mockRelatedModel;
        return $this;
    }

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

    /**
     * @return String
     */
    public function getSecondValue()
    {
        return $this->secondValue;
    }

    /**
     * @param String $secondValue
     * @return MockModel
     */
    public function setSecondValue($secondValue)
    {
        $this->secondValue = $secondValue;
        return $this;
    }

    public function tableName()
    {
        return "tbl_mockmodel";
    }

    public function listModels()
    {
        return [
            "MockRelatedModel" => "fkId"
        ];
    }

}