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
use Vanilla\Test\Persist\Fixtures\SqlFixtures;
use PHPUnit\Framework\TestCase;

class PersistSqlTest extends TestCase
{
    private static $tableName;

    public static function setUpBeforeClass()
    {
        self::$tableName = "super_test_table";
        $sqlFixture = new SqlFixtures();
        $sqlFixture->setTableName(self::$tableName);
        $sqlFixture->setUp();
    }

    public static function tearDownAfterClass()
    {
        $sqlFixture = new SqlFixtures();
        $sqlFixture->setTableName(self::$tableName);
        $sqlFixture->tearDown();
    }

    public function tearDown()
    {
        PersistFactory::clearInstance();
    }

    public function testStoreData()
    {
        $persistHandler = PersistFactory::getInstance();

        $mockModel = new MockModel();
        $mockModel->setTableName(self::$tableName);
        $uniqId = uniqid();
        $mockModel->setUniqueValue($uniqId);
        $persistHandler->save($mockModel);

        $mockModelNotLoading = new MockModel();
        $mockModelNotLoading->setTableName(self::$tableName);
        $mockModelNotLoading->setUniqueValue(uniqid());
        $persistHandler->save($mockModelNotLoading);
        unset($persistHandler);

        // Clear the handler so we are sure we're instantiating a new persistence handler.
        PersistFactory::clearInstance();
        $persistHandlerLoad = PersistFactory::getInstance();
        $mockModel2 = new MockModel();
        $mockModel2->setTableName(self::$tableName);
        $mockModel2->setUniqueValue($uniqId);
        $persistHandlerLoad->loadBy($mockModel2, "uniqueValue");

        $this->assertEquals($mockModel->getId(), $mockModel2->getId());
    }
}
