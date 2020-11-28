<?php

declare(strict_types=1);

$getterConverter = new \SimpleAsFuck\Orm\Generator\Renderer\GetterReadValueConverter();
$typeGlobalConverter = new \SimpleAsFuck\Orm\Generator\Renderer\TypeGlobalNameConverter();

/**
 * @var string $modelNamespace
 * @var \SimpleAsFuck\Orm\Generator\Abstracts\ModelStructure $modelStructure
 * @var string $repositoryNamespace
 * @var bool $stupidDeveloper
 */

echo "<?php\n";

?>

declare(strict_types=1);

namespace <?= $repositoryNamespace ?>;

use <?= $modelNamespace.'\\'.$modelStructure->getName() ?>;
use <?= $modelNamespace.'\\Generated\\'.$modelStructure->getName() ?>Result;
use SimpleAsFuck\Orm\Database\Abstracts\Connection;
use SimpleAsFuck\Orm\Database\Abstracts\Result;

<?php

if ($stupidDeveloper) {
    echo
"/**\n".
" * Generated abstract class is only for simple class generation not use this abstraction in your code!\n".
" */\n"
    ;
}

?>
abstract class <?= $modelStructure->getName() ?>Repository
{
    private Connection $connection;

    final public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

<?php
    $primaryKeyParams = [];
    $wherePrimaryKeys = [];
    $selectProperties = [];
    foreach ($modelStructure->getPrimaryKeys() as $primaryKey) {
        $primaryKeyParams[] = $typeGlobalConverter->convert($primaryKey->getType()).' $'.$primaryKey->getName();
        $wherePrimaryKeys[] = '`'.$primaryKey->getName().'` = ?';
        $selectProperties[] = '$'.$primaryKey->renderReadCode($primaryKey->getName());
    }
?>
    final public function selectByPrimaryKey(<?= implode(', ', $primaryKeyParams) ?>): ?<?= $modelStructure->getName()."\n" ?>
    {
        $result = $this->rawQuery('select * from `<?= $modelStructure->getName() ?>` where <?= implode(' and ', $wherePrimaryKeys) ?>', [<?= implode(', ', $selectProperties) ?>]);
        $result = new <?= $modelStructure->getName() ?>Result($result);
        return $result->fetch();
    }
<?php
    foreach ($modelStructure->getAdditionalKeys() as $additionalKey) {
        echo
"\n".
'    final public function selectBy'.ucfirst($additionalKey->getName()).'('.($additionalKey->isNullable() ? '?' : '').$typeGlobalConverter->convert($additionalKey->getType()).' $'.$additionalKey->getName().'): '.$modelStructure->getName()."Result\n".
"    {\n"
        ;
        if ($additionalKey->isNullable()) {
            echo
'        if ($'.$additionalKey->getName()." === null) {\n".
"            \$result = \$this->rawQuery('select * from `".$modelStructure->getName()."` where `".$additionalKey->getName()."` is null', []);\n".
"        } else {\n".
"            \$result = \$this->rawQuery('select * from `".$modelStructure->getName()."` where `".$additionalKey->getName()."` = ?', [\$" . $additionalKey->renderReadCode($additionalKey->getName())."]);\n".
"        }\n"
            ;
        } else {
            echo
"        \$result = \$this->rawQuery('select * from `".$modelStructure->getName()."` where `".$additionalKey->getName()."` = ?', [\$" . $additionalKey->renderReadCode($additionalKey->getName())."]);\n"
            ;
        }
        echo
'        return new '.$modelStructure->getName()."Result(\$result);\n".
"    }\n"
        ;
    }

    $insertedColumns = [];
    $insertedValues = [];
    $unAssignableProperty = null;

    foreach ($modelStructure->getProperties() as $modelProperty) {
        if (! $modelProperty->isAssignable()) {
            $unAssignableProperty = $modelProperty;
            continue;
        }

        $insertedColumns[] = '`'.$modelProperty->getName().'`';
        $insertedValues[] = '?';
    }
?>

    final public function insert(<?= $modelStructure->getName() ?> <?= $unAssignableProperty ? '&' : '' ?>$model): void
    {
        $models = [$model];
        $this->multiInsert($models);
<?php
    if ($unAssignableProperty) {
        echo
"        \$model = \$models[array_key_first(\$models)];\n"
        ;
    }
?>
    }

    /**
     * @param <?= $modelStructure->getName() ?>[] $models
     */
    final public function multiInsert(array <?= $unAssignableProperty ? '&' : '' ?>$models): void
    {
        $transaction = $this->connection->beginTransaction();

        try {
            $query = $this->connection->prepare('insert into `<?= $modelStructure->getName() ?>` (<?= implode(', ', $insertedColumns) ?>) values (<?= implode(', ', $insertedValues) ?>)');

            foreach ($models as <?= $unAssignableProperty ? '$key => ' : '' ?>$model) {
<?php
    if ($stupidDeveloper) {
        echo
'                if (get_class($model) !== \\'.$modelNamespace.'\\'.$modelStructure->getName()."::class) {\n".
"                    throw new \LogicException('Inserted model must by instance of: ".$modelNamespace.'\\'.$modelStructure->getName()."');\n".
"                }\n\n"
        ;
    }
?>
                <?= $unAssignableProperty ? '$result = ' : '' ?>$query->execute([
<?php
    foreach ($modelStructure->getProperties() as $modelProperty) {
        if (! $modelProperty->isAssignable()) {
            continue;
        }

        if ($modelProperty->isNullable()) {
            echo
'                    $model->'.$getterConverter->convert($modelProperty->getName()).' === null ? null : '.$modelProperty->renderReadCode('$model->' . $getterConverter->convert($modelProperty->getName())).",\n"
            ;
            continue;
        }

        echo
'                    '.$modelProperty->renderReadCode('$model->' . $getterConverter->convert($modelProperty->getName())).",\n"
        ;
    }
?>
                ]);
<?php
    if ($unAssignableProperty) {
        echo
'                $models[$key] = new '.$modelStructure->getName()."(\n"
        ;
        foreach ($modelStructure->getProperties() as $modelProperty) {
            if (! $modelProperty->isAssignable()) {
                continue;
            }

            echo
'                    $model->'.$getterConverter->convert($modelProperty->getName()).",\n"
            ;
        }
        echo
'                    ('.$unAssignableProperty->getType().") \$result->lastInsertedId()\n".
"                );\n"
        ;
    }

    $setValues = [];
    foreach (array_merge($modelStructure->getAdditionalKeys(), $modelStructure->getSimpleParams()) as $modelProperty) {
        $setValues[] = '`' . $modelProperty->getName() . '` = ?';
    }
?>
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollback();
            throw $exception;
        }
    }
<?php
    if (count($modelStructure->getAdditionalKeys()) || count($modelStructure->getSimpleParams())) {
        ?>

    final public function update(<?= $modelStructure->getName() ?> $model): void
    {
        $this->multiUpdate([$model]);
    }

    /**
     * @param <?= $modelStructure->getName() ?>[] $models
     */
    final public function multiUpdate(array $models): void
    {
        $transaction = $this->connection->beginTransaction();

        try {
            $query = $this->connection->prepare('update `<?= $modelStructure->getName() ?>` set <?= implode(', ', $setValues) ?> where <?= implode(' and ', $wherePrimaryKeys) ?>');

            foreach ($models as $model) {
<?php
    if ($stupidDeveloper) {
        echo
'                if (get_class($model) !== \\'.$modelNamespace.'\\'.$modelStructure->getName()."::class) {\n".
"                    throw new \LogicException('Updated model must by instance of: ".$modelNamespace.'\\'.$modelStructure->getName()."');\n".
"                }\n\n"
        ;
    } ?>
                $query->execute([
<?php
foreach (array_merge($modelStructure->getAdditionalKeys(), $modelStructure->getSimpleParams()) as $modelProperty) {
        if ($modelProperty->isNullable()) {
            echo
'                    $model->'.$getterConverter->convert($modelProperty->getName()).' === null ? null : '.$modelProperty->renderReadCode('$model->' . $getterConverter->convert($modelProperty->getName())).",\n"
        ;
            continue;
        }

        echo
'                    '.$modelProperty->renderReadCode('$model->'.$getterConverter->convert($modelProperty->getName())).",\n"
    ;
    }

        foreach ($modelStructure->getPrimaryKeys() as $primaryKey) {
            echo
'                    '.$primaryKey->renderReadCode('$model->'.$getterConverter->convert($primaryKey->getName())).",\n"
    ;
        } ?>
                ]);
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollback();
            throw $exception;
        }
    }
<?php
    }
?>

    final public function delete(<?= $modelStructure->getName() ?> $model): void
    {
        $this->multiDelete([$model]);
    }

    /**
     * @param <?= $modelStructure->getName() ?>[] $models
     */
    public function multiDelete(array $models): void
    {
        $transaction = $this->connection->beginTransaction();

        try {
            $query = $this->connection->prepare('delete from <?= $modelStructure->getName() ?> where <?= implode(' and ', $wherePrimaryKeys) ?>');

            foreach ($models as $model) {
<?php
    if ($stupidDeveloper) {
        echo
'                if (get_class($model) !== \\'.$modelNamespace.'\\'.$modelStructure->getName()."::class) {\n".
"                    throw new \LogicException('Deleted model must by instance of: ".$modelNamespace.'\\'.$modelStructure->getName()."');\n".
"                }\n\n"
        ;
    }
?>
                $query->execute([
<?php
    foreach ($modelStructure->getPrimaryKeys() as $primaryKey) {
        echo
'                    '.$primaryKey->renderReadCode('$model->'.$getterConverter->convert($primaryKey->getName())) . ",\n"
        ;
    }
?>
                ]);
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollback();
            throw $exception;
        }
    }

    /**
     * @param string[]|int[]|float[]|null[] $inputParameters
     */
    final protected function select(string $statement, array $inputParameters): <?= $modelStructure->getName() ?>Result
    {
<?php
    if ($stupidDeveloper) {
        echo
"        if (! preg_match('/^\\s*select\\s+".$modelStructure->getName()."\\.\\*\\s+from\\s+".$modelStructure->getName()."/ui', \$statement)) {\n".
"            throw new \\LogicException('Statement must start with: \"select ".$modelStructure->getName().".* from ".$modelStructure->getName()."\"');\n".
"        }\n\n"
        ;
    }
?>
        $result = $this->rawQuery($statement, $inputParameters);
        return new <?= $modelStructure->getName() ?>Result($result);
    }

    /**
     * @param string[]|int[]|float[]|null[] $inputParameters
     */
    final protected function rawQuery(string $statement, array $inputParameters): Result
    {
<?php
if ($stupidDeveloper) {
    echo
"        if (preg_match('/^\\s*select\\s+".$modelStructure->getName()."\\.\\*\\s+from\\s+".$modelStructure->getName()."/ui', \$statement)) {\n".
"            throw new \\LogicException('For statement: '.\$statement.' selecting full instance is dedicated method: Repository::select');\n".
"        }\n\n"
    ;
}
?>
        return $this->connection->query($statement, $inputParameters);
    }
}
