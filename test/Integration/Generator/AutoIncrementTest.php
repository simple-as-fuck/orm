<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator;

use App\Model\Database\TestAutoIncrement;
use App\Service\Database\TestAutoIncrementRepository;
use PHPUnit\Framework\TestCase;
use SimpleAsFuck\Orm\Database\Abstracts\Transaction;
use SimpleAsFuck\Orm\Database\Mysql\Connection;

/**
 * @covers \SimpleAsFuck\Orm\Generator\Generator
 * @covers \App\Model\Database\TestAutoIncrement
 * @covers \App\Service\Database\TestAutoIncrementRepository
 */
final class AutoIncrementTest extends TestCase
{
    private Connection $connection;
    private Transaction $transaction;
    private TestAutoIncrementRepository $repository;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__.'/../../../vendor/autoload.php';
    }

    public function setUp(): void
    {
        $this->connection = new Connection('database', 3306, 'root', 'password', 'database');
        $this->transaction = $this->connection->beginTransaction();

        $this->repository = new TestAutoIncrementRepository($this->connection);
    }

    public function tearDown(): void
    {
        $this->transaction->rollback();
    }

    public function testInsert(): void
    {
        $model1 = new TestAutoIncrement(null, 6, 'test', new \DateTimeImmutable('200-11-11 10:11:11'));
        $model2 = new TestAutoIncrement(new \DateTimeImmutable('2022-10-10 10:10:11'), 6, 'test', new \DateTimeImmutable(), new \DateTimeImmutable('2222-11-11 00:01:22'), 6.7, 1);

        $this->repository->insert($model1);
        $this->repository->insert($model2);

        static::assertNotSame(1, $model2->getId());
        static::assertNotSame('1', $model2->getPrimaryKey());
        static::assertTrue($model2->getId() > $model1->getId());

        static::assertSame(null, $model1->getTimeAt());
        static::assertSame(6, $model1->getInt());
        static::assertSame('test', $model1->getString());
        static::assertEquals(new \DateTimeImmutable('200-11-11 10:11:11'), $model1->getCreatedAt());
        static::assertSame(null, $model1->getDeletedAt());
        static::assertSame(0.0, $model1->getFloat());
        static::assertIsString($model1->getPrimaryKey());

        static::assertEquals(new \DateTimeImmutable('2022-10-10 10:10:11'), $model2->getTimeAt());
        static::assertEquals(new \DateTimeImmutable('2222-11-11 00:01:22'), $model2->getDeletedAt());
        static::assertSame(6.7, $model2->getFloat());
    }

    public function testMultiInsert(): void
    {
        $models = [];
        $models[] = new TestAutoIncrement(null, 6, 'test', new \DateTimeImmutable('200-11-11 10:11:11'));
        $models[] = new TestAutoIncrement(new \DateTimeImmutable('2022-10-10 10:10:11'), 6, 'test', new \DateTimeImmutable(), new \DateTimeImmutable('2222-11-11 00:01:22'), 6.7, 1);

        $this->repository->multiInsert($models);

        static::assertNotSame(1, $models[1]->getId());
        static::assertNotSame('1', $models[1]->getPrimaryKey());
        static::assertTrue($models[1]->getId() > $models[0]->getId());
    }

    public function testUpdate(): void
    {
        $id = (int) $this->connection->query("
            insert into TestAutoIncrement 
                (`int`, string, createdAt, timeAt, deletedAt, `float`)
            values
                (5, 'test', '2000-12-02 10:00:00', null, null, 9.9)
        ", [])->lastInsertedId();

        $model = new TestAutoIncrement(null, 7, 'test1', new \DateTimeImmutable('2000-12-02 10:00:00'), null, 2.2, $id);

        $this->repository->update($model);

        $selectedModel = $this->repository->selectByPrimaryKey($id);

        static::assertEquals($model, $selectedModel);
        static::assertNotSame($model, $selectedModel);
    }

    public function testDelete(): void
    {
        $id = (int) $this->connection->query("
            insert into TestAutoIncrement
                (`int`, string, createdAt)
            values
                (4, 'test', '2000-12-02 10:00:00')
        ", [])->lastInsertedId();

        $model = $this->repository->selectByPrimaryKey($id);
        if ($model === null) {
            throw new \LogicException();
        }

        $this->repository->delete($model);

        $result = $this->connection->query('select id from TestAutoIncrement where id = ?', [$id])->fetch();
        static::assertNull($result);
    }

    public function testSelect(): void
    {
        $query = $this->connection->prepare('insert into TestAutoIncrement (`int`, string, createdAt, timeAt) values (?, ?, ?, ?)');
        $query->execute([7, 'test1', '2011-01-01 10:10:11', '2000-11-11 09:09:11']);
        $query->execute([7, 'test2', '2011-01-01 10:10:11', null]);
        $query->execute([7, 'test3', '2011-01-01 10:10:11', '2011-11-11 09:09:11']);
        $query->execute([7, 'test4', '2011-01-01 10:10:11', '2012-11-11 09:09:11']);

        $modelByNull = $this->repository->selectByTimeAt(null)->fetch();
        $modelByValue = $this->repository->selectByTimeAt(new \DateTimeImmutable('2011-11-11 09:09:11'))->fetch();
        $modelsNotFound = $this->repository->selectByTimeAt(new \DateTimeImmutable('2013-11-11 09:09:11'))->fetchAll();

        static::assertSame('test2', $modelByNull ? $modelByNull->getString() : null);
        static::assertSame('test3', $modelByValue ? $modelByValue->getString() : null);
        static::assertCount(0, $modelsNotFound);
    }
}
