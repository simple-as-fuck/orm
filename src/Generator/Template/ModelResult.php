<?php

declare(strict_types=1);

/**
 * @var \SimpleAsFuck\Orm\Generator\Abstracts\ModelStructure $modelStructure
 * @var string $resultNamespace
 * @var string $modelNamespace
 * @var bool $stupidDeveloper
 */

echo "<?php\n";

?>

declare(strict_types=1);

namespace <?= $resultNamespace ?>;

use <?= $modelNamespace.'\\'.$modelStructure->getName() ?>;
use SimpleAsFuck\Orm\Database\Abstracts\Result;

final class <?= $modelStructure->getName()."Result\n" ?>
{
    private Result $dbResult;

    public function __construct(Result $dbResult)
    {
        $this->dbResult = $dbResult;
    }

    public function fetch(): ?<?= $modelStructure->getName()."\n" ?>
    {
        $object = $this->dbResult->fetch();
        if (! $object) {
            return null;
        }

        return new <?= $modelStructure->getName() ?>(
<?php
    $writeConstructorParams = [];
    $unAssignableConstructParams = [];

    foreach ($modelStructure->getProperties() as $modelProperty) {
        $constructorParam = '';
        if ($modelProperty->isNullable()) {
            $constructorParam = '$object->'.$modelProperty->getName().' === null ? null : ';
        }
        $constructorParam = '            '.$constructorParam.$modelProperty->renderWriteCode('$object->' . $modelProperty->getName());

        $modelProperty->isAssignable() ? $writeConstructorParams[] = $constructorParam : $unAssignableConstructParams[] = $constructorParam;
    }

    echo implode(",\n", array_merge($writeConstructorParams, $unAssignableConstructParams))."\n";
?>
        );
    }

    /**
     * @return <?= $modelStructure->getName() ?>[] indexed by primary key value
     */
    public function fetchAll(): array
    {
        $models = [];
        while (true) {
            $model = $this->fetch();
            if (! $model) {
                break;
            }

            $models[$model->getPrimaryKey()] = $model;
        }

        return $models;
    }
}
