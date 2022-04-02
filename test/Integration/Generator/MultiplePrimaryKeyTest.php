<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator;

use App\Model\Database\TestMultiplePrimaryKey;
use App\Service\Database\TestMultiplePrimaryKeyRepository;
use PHPUnit\Framework\TestCase;
use SimpleAsFuck\Orm\Database\Abstracts\Transaction;
use SimpleAsFuck\Orm\Database\Mysql\Connection;

/**
 * @covers \SimpleAsFuck\Orm\Generator\Generator
 * @covers \App\Model\Database\TestMultiplePrimaryKey
 * @covers \App\Service\Database\TestMultiplePrimaryKeyRepository
 */
final class MultiplePrimaryKeyTest extends TestCase
{
    private Connection $connection;
    private Transaction $transaction;
    private TestMultiplePrimaryKeyRepository $repository;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__.'/../../../vendor/autoload.php';
    }

    public function setUp(): void
    {
        $this->connection = new Connection('database', 3306, 'root', 'password', 'database');
        $this->transaction = $this->connection->beginTransaction();

        $this->repository = new TestMultiplePrimaryKeyRepository($this->connection);
    }

    public function tearDown(): void
    {
        $this->transaction->rollback();
    }

    public function testInsert(): void
    {
        $model = new TestMultiplePrimaryKey(new \DateTimeImmutable('2000-10-07'), null, new \DateTimeImmutable('2000-10-07'));

        $this->repository->insert($model);

        $insertedModel = $this->repository->fetchByPrimaryKey($model->getDate(), $model->getId());

        self::assertEquals($model, $insertedModel);
    }

    public function testUpdate(): void
    {
        $id = (int) $this->connection->query("
            insert into TestMultiplePrimaryKey 
                (date, customType, anotherId)
            values
                ('1999-08-15', '1999-08-15 10:10:10', null)
        ", [])->lastInsertedId();

        $date = new \DateTimeImmutable('1999-08-15');
        $model = new TestMultiplePrimaryKey($date, 1, new \DateTimeImmutable('1999-08-15 17:11:11'), new \DateTimeImmutable('1589-10-25 17:11:11'), '10.00', $id);

        $this->repository->update($model);

        $updatedModel = $this->repository->fetchByPrimaryKey($date, $id);

        self::assertEquals($model, $updatedModel);
        self::assertNotSame($model, $updatedModel);
    }

    public function testDelete(): void
    {
        $this->connection->query("insert into TestMultiplePrimaryKey (id, date, customType) values (1, '1999-08-15', '1999-08-15 10:10:10')", []);

        $date = new \DateTimeImmutable('1999-08-15');
        $model = new TestMultiplePrimaryKey($date, null, new \DateTimeImmutable('1999-08-15 10:10:10'), null, '0.00', 1);

        $this->repository->delete($model);

        $deletedModel = $this->repository->fetchByPrimaryKey($date, 1);

        self::assertNull($deletedModel);
    }

    public function testSelect(): void
    {
        $query = $this->connection->prepare("insert into TestMultiplePrimaryKey (date, customType, anotherId) values ('1999-08-15', '1999-08-15 10:10:11', ?)");

        $query->execute([2]);
        $query->execute([null]);
        $query->execute([null]);
        $expectedId = (int) $query->execute([1])->lastInsertedId();
        $query->execute([3]);

        $models = $this->repository->selectByAnotherId(null)->fetchAll();
        $foundModel = $this->repository->selectByAnotherId(1)->fetch();

        self::assertCount(2, $models);
        self::assertSame($expectedId, $foundModel ? $foundModel->getId() : null);
    }
}
