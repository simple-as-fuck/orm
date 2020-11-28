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

        $notFound = $this->repository->selectByPrimaryKey(3, 1);
        $insertedModel = $this->repository->selectByPrimaryKey(2, 1);

        static::assertNull($notFound);
        static::assertNotSame($model, $insertedModel);
        static::assertEquals($model, $insertedModel);
        static::assertSame('2-1', $insertedModel ? $insertedModel->getPrimaryKey() : null);
    }

    public function testNotGeneratedUpdate(): void
    {
        static::assertFalse(method_exists($this->repository, 'update'));
        static::assertFalse(method_exists($this->repository, 'multiUpdate'));
    }

    public function testDelete(): void
    {
        $this->connection->query('insert into TestMap (someId, anotherId) VALUES (3, 1)', []);

        $model = new TestMap(3, 1);
        $this->repository->delete($model);

        $deletedModel = $this->repository->selectByPrimaryKey(3, 1);

        static::assertNull($deletedModel);
    }
}
