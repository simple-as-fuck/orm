<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator;

use App\Model\Database\TestMap;
use App\Service\Database\TestMapRepository;
use PHPUnit\Framework\TestCase;
use SimpleAsFuck\Orm\Database\Abstracts\Transaction;
use SimpleAsFuck\Orm\Database\Mysql\Connection;

/**
 * @covers \SimpleAsFuck\Orm\Generator\Generator
 * @covers \App\Model\Database\TestMap
 * @covers \App\Service\Database\TestMapRepository
 */
final class MapTest extends TestCase
{
    private Connection $connection;
    private Transaction $transaction;
    private TestMapRepository $repository;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__.'/../../../vendor/autoload.php';
    }

    public function setUp(): void
    {
        $this->connection = new Connection('database', 3306, 'root', 'password', 'database');
        $this->transaction = $this->connection->beginTransaction();

        $this->repository = new TestMapRepository($this->connection);
    }

    public function tearDown(): void
    {
        $this->transaction->rollback();
    }

    public function testInsert(): void
    {
        $model = new TestMap(2, 1);

        $this->repository->insert($model);

        $notFound = $this->repository->fetchByPrimaryKey(3, 1);
        $insertedModel = $this->repository->fetchByPrimaryKey(2, 1);

        self::assertNull($notFound);
        self::assertNotSame($model, $insertedModel);
        self::assertEquals($model, $insertedModel);
        self::assertSame('2-1', $insertedModel ? $insertedModel->convertPrimaryKeyToIndex() : null);
    }

    public function testNotGeneratedUpdate(): void
    {
        self::assertFalse(method_exists($this->repository, 'update'));
        self::assertFalse(method_exists($this->repository, 'multiUpdate'));
    }

    public function testDelete(): void
    {
        $this->connection->query('insert into TestMap (someId, anotherId) VALUES (3, 1)', []);

        $model = new TestMap(3, 1);
        $this->repository->delete($model);

        $deletedModel = $this->repository->fetchByPrimaryKey(3, 1);

        self::assertNull($deletedModel);
    }
}
