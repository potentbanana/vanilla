<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/13/2018
 * Time: 7:49 PM
 */

namespace Vanilla\Test\Persist;

use Vanilla\Persist\PersistSql;
use Vanilla\Persist\PersistFactory;
use Vanilla\Test\Models\MockModel;
use Vanilla\Test\Models\MockRelatedModel;
use Vanilla\Test\Persist\Fixtures\SqlFixtures;
use PHPUnit\Framework\TestCase;

class PersistSqlTest extends TestCase
{
    private $tableName;
    private $tableRelatedName;
    private $sqlFixture;

    public function setUp()
    {
        $this->tableName = "tbl_mockmodel";
        $this->tableRelatedName = "tbl_mockrelatedmodel";
        $this->sqlFixture = new SqlFixtures();
        $this->sqlFixture->setTableName($this->tableName);
        $this->sqlFixture->setTableRelatedName($this->tableRelatedName);
        $this->sqlFixture->setUp();
    }

    public function tearDown()
    {
        PersistFactory::clearInstance();
        $this->sqlFixture->tearDown();
    }

    public function testSaveReturnsInsertId()
    {
        // tear down the sql fixture
        $persistHandler = PersistFactory::getInstance();
        $mockModel = new MockModel();
        $mockModel->setUniqueValue("doesn't matter");
        $persistHandler->save($mockModel);

        $this->assertEquals(1, $mockModel->getId());
    }

    public function testStoreData()
    {
        $persistHandler = PersistFactory::getInstance();

        $mockModel = new MockModel();
        $uniqId = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $persistHandler->save($mockModel);

        $mockModelNotLoading = new MockModel();
        $mockModelNotLoading->setUniqueValue(uniqid());
        $persistHandler->save($mockModelNotLoading);
        unset($persistHandler);

        // Clear the handler so we are sure we're instantiating a new persistence handler.
        PersistFactory::clearInstance();
        $persistHandlerLoad = PersistFactory::getInstance();
        $mockModel2 = new MockModel();
        $mockModel2->setUniqueValue($uniqId);
        $persistHandlerLoad->loadBy($mockModel2, "uniqueValue");

        $this->assertEquals($mockModel->getUniqueValue(), $mockModel2->getUniqueValue());
    }

    public function testForeignKey()
    {
        $persistHandler = PersistFactory::getInstance();

        $mockRelatedModel = new MockRelatedModel();
        $mockRelatedModel->setSomeValue("a test");

        $mockRelatedModel2 = new MockRelatedModel();
        $mockRelatedModel2->setSomeValue("a second test");

        $mockModel = new MockModel();
        $mockModel->setMockRelatedModel([$mockRelatedModel, $mockRelatedModel2]);
        $uniqId = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $persistHandler->save($mockModel);
        unset($persistHandler);

        PersistFactory::clearInstance();
        $persistHandler = PersistFactory::getInstance();
        $mockModelInbound = new MockModel();
        $mockModelInbound->setUniqueValue($uniqId);
        $persistHandler->loadBy($mockModelInbound, "uniqueValue");

        $this->assertEquals($uniqId, $mockModelInbound->getUniqueValue());

    }

    public function testLoadByMultiple()
    {
        $persistHandler = PersistFactory::GetInstance();

        $mockModel = new MockModel();
        $uniqId = uniqid();
        $secondValue = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $mockModel->setSecondValue($secondValue);
        $persistHandler->save($mockModel);

        // unset $persistHandler
        unset($persistHandler);
        PersistFactory::clearInstance();
        $persistHandler = PersistFactory::getInstance();
        $mockModelInbound = new MockModel();
        $mockModelInbound->setUniqueValue($uniqId);
        $mockModelInbound->setSecondValue($secondValue);
        $persistHandler->loadBy($mockModelInbound, ["uniqueValue", "secondValue"]);

        $this->assertEquals($mockModelInbound->getId(), 1);
    }

    public function testLoadRelatedModels()
    {
        $persistHandler = PersistFactory::GetInstance();

        $mockModel = new MockModel();
        $uniqId = uniqid();
        $secondValue = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $mockModel->setSecondValue($secondValue);

        $mockRelatedModel = new MockRelatedModel();
        $mockRelatedModel->setSomeValue("a first test");

        $mockRelatedModel2 = new MockRelatedModel();
        $mockRelatedModel2->setSomeValue("a second test");
        $mockModel->setMockRelatedModel([$mockRelatedModel, $mockRelatedModel2]);
        $persistHandler->save($mockModel);

        unset($persistHandler);
        PersistFactory::clearInstance();

        $loadHandler = PersistFactory::getInstance();
        $loadModel = new MockModel();
        $loadModel->setUniqueValue($uniqId);
        $loadHandler->loadBy($loadModel, "uniqueValue");
        $this->assertNotEmpty($loadModel->getMockRelatedModel());
        $this->assertInstanceOf("\\Vanilla\\Test\\Models\\MockRelatedModel", $loadModel->getMockRelatedModel()[0]);
        $this->assertInstanceOf("\\Vanilla\\Test\\Models\\MockRelatedModel", $loadModel->getMockRelatedModel()[1]);
        $this->assertNotEquals($loadModel->getMockRelatedModel()[0], $loadModel->getMockRelatedModel()[1]);
    }
}