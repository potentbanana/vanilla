<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/12/2018
 * Time: 10:41 PM
 */

namespace Vanilla\Test\Persist;

use Vanilla\Test\Models\MockModel;
use Vanilla\Persist\PersistBase;
use Vanilla\Persist\PersistFactory;

class PersistBaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var String
     */
    private $uniqueValue;
    private $uniqueValue2;

    public function setUp()
    {

        $this->persistBase = PersistFactory::getInstance([], true);
        $model = new MockModel();
        $this->uniqueValue = uniqid();
        $model->setUniqueValue($this->uniqueValue);
        $this->persistBase->save($model);

        // Store model 2
        $model2 = new MockModel();
        $this->uniqueValue2 = uniqid();
        $model2->setUniqueValue($this->uniqueValue2);
        $this->persistBase->save($model2);
    }

    public function tearDown()
    {
        $this->uniqueValue = $this->uniqueValue2 = null;

        // We are trying multiple tests so we need to clear the static instance handler each time.
        PersistFactory::clearInstance();
    }

    public function testLoadBaseSingle()
    {
        $ident = MockModel::INITIALID;
        $mockModel = new MockModel();
        $mockModel->setId($ident);
        $this->persistBase->load($mockModel, $ident);
        $this->assertEquals($this->uniqueValue, $mockModel->getUniqueValue());
    }

    public function testLoadBaseMultiple()
    {
        $mockModel = new MockModel();
        $this->persistBase->load($mockModel, MockModel::INITIALID);
        $this->assertEquals($this->uniqueValue, $mockModel->getUniqueValue());
        $this->assertEquals(1, $mockModel->getId());
        unset($mockModel);

        $mockModel = new MockModel();
        $this->persistBase->load($mockModel, MockModel::INITIALID + 1);
        $this->assertEquals($this->uniqueValue2, $mockModel->getUniqueValue());
        $this->assertEquals(2, $mockModel->getId());
    }

}
