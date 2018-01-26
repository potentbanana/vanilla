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
        $this->tableName = "tbl_" . uniqid();
        $this->tableRelatedName = "tbl_" . uniqid();
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

    public function testStoreData()
    {
        $persistHandler = PersistFactory::getInstance();

        $mockModel = new MockModel();
        $mockModel->setTableName($this->tableName);
        $uniqId = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $persistHandler->save($mockModel);

        $mockModelNotLoading = new MockModel();
        $mockModelNotLoading->setTableName($this->tableName);
        $mockModelNotLoading->setUniqueValue(uniqid());
        $persistHandler->save($mockModelNotLoading);
        unset($persistHandler);

        // Clear the handler so we are sure we're instantiating a new persistence handler.
        PersistFactory::clearInstance();
        $persistHandlerLoad = PersistFactory::getInstance();
        $mockModel2 = new MockModel();
        $mockModel2->setTableName($this->tableName);
        $mockModel2->setUniqueValue($uniqId);
        $persistHandlerLoad->loadBy($mockModel2, "uniqueValue");

        $this->assertEquals($mockModel->getUniqueValue(), $mockModel2->getUniqueValue());
    }

    public function testForeignKey()
    {
        $persistHandler = PersistFactory::getInstance();

        $mockRelatedModel = new MockRelatedModel();
        $mockRelatedModel->setTableName($this->tableRelatedName);
        $mockRelatedModel->setSomeValue("a test");

        $mockRelatedModel2 = new MockRelatedModel();
        $mockRelatedModel2->setTableName($this->tableRelatedName);
        $mockRelatedModel2->setSomeValue("a second test");

        $mockModel = new MockModel();
        $mockModel->setTableName($this->tableName);
        $mockModel->setMockRelatedModel([$mockRelatedModel, $mockRelatedModel2]);
        $uniqId = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $persistHandler->save($mockModel);
        unset($persistHandler);

        PersistFactory::clearInstance();
        $persistHandler = PersistFactory::getInstance();
        $mockModelInbound = new MockModel();
        $mockModelInbound->setTableName($this->tableName);
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
        $mockModel->setTableName($this->tableName);
        $persistHandler->save($mockModel);

        // unset $persistHandler
        unset($persistHandler);
        PersistFactory::clearInstance();
        $persistHandler = PersistFactory::getInstance();
        $mockModelInbound = new MockModel();
        $mockModelInbound->setTableName($this->tableName);
        $mockModelInbound->setUniqueValue($uniqId);
        $mockModelInbound->setSecondValue($secondValue);
        $persistHandler->loadBy($mockModelInbound, ["uniqueValue", "secondValue"]);

        $this->assertEquals($mockModelInbound->getId(), 1);
    }
}
