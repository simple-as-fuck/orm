<?php

declare(strict_types=1);

$getterConverter = new \SimpleAsFuck\Orm\Generator\Renderer\GetterReadValueConverter();
$typeGlobalConverter = new \SimpleAsFuck\Orm\Generator\Renderer\TypeGlobalNameConverter();

/**
 * @var \SimpleAsFuck\Orm\Generator\Abstracts\ModelStructure $modelStructure
 * @var string $modelNamespace
 * @var bool $stupidDeveloper
 */

echo "<?php\n";

?>

declare(strict_types=1);

namespace <?= $modelNamespace ?>;

<?php

if ($stupidDeveloper) {
    echo
        "/**\n".
        " * Generated abstract class is only for simple class generation not use this abstraction in your code!\n".
        " */\n"
    ;
}

?>
abstract class <?= $modelStructure->getName()."\n" ?>
{
<?php
    $constructComments = 0;

    foreach ($modelStructure->getProperties() as $modelProperty) {
        if ($modelProperty->getComment() !== '') {
            $constructComments++;
        }

        echo "    private ".($modelProperty->isNullable() || ! $modelProperty->isAssignable() ? '?' : '').$typeGlobalConverter->convert($modelProperty->getType())." \$".$modelProperty->getName().";\n";
    }

    if ($constructComments) {
        echo "\n    /**\n";
        foreach ($modelStructure->getProperties() as $modelProperty) {
            if ($modelProperty->getComment() === '') {
                continue;
            }

            echo '     * @param '.$typeGlobalConverter->convert($modelProperty->getType()).($modelProperty->isNullable() || ! $modelProperty->isAssignable() ? '|null' : '').' $'.$modelProperty->getName().' '.$modelProperty->getComment()."\n";
        }
        echo "     */";
    }

    echo "\n    final public function __construct(";

    $constructParams = [];
    foreach ($modelStructure->getPrimaryKeys() as $primaryKey) {
        if (! $primaryKey->isAssignable()) {
            continue;
        }
        $constructParams[] = $typeGlobalConverter->convert($primaryKey->getType()).' $'.$primaryKey->getName();
    }

    foreach ($modelStructure->getAdditionalKeys() as $additionalKey) {
        $constructParams[] = ($additionalKey->isNullable() ? '?' : '').$typeGlobalConverter->convert($additionalKey->getType()).' $'.$additionalKey->getName();
    }

    foreach ($modelStructure->getSimpleParams() as $simpleParam) {
        $constructParams[] = ($simpleParam->isNullable() ? '?' : '').$typeGlobalConverter->convert($simpleParam->getType()).' $'.$simpleParam->getName().($simpleParam->getDefaultValue() !== null ? ' = '.$simpleParam->getDefaultValue() : '');
    }

    foreach ($modelStructure->getPrimaryKeys() as $primaryKey) {
        if ($primaryKey->isAssignable()) {
            continue;
        }
        $constructParams[] = '?'.$typeGlobalConverter->convert($primaryKey->getType()).' $'.$primaryKey->getName().' = null';
    }

    echo implode(', ', $constructParams);

    echo ")\n    {\n";
    foreach ($modelStructure->getProperties() as $property) {
        echo '        $this->'.$property->getName().' = $'.$property->getName().";\n";
    }
    echo "    }\n\n";

    foreach ($modelStructure->getProperties() as $key => $modelProperty) {
        if ($modelProperty->getComment() !== '') {
            echo
                "    /**\n".
                "     * ".$modelProperty->getComment()."\n".
                "     */\n"
            ;
        }

        echo
            "    final public function ".$getterConverter->convert($modelProperty->getName()).': '.($modelProperty->isNullable() ? '?' : '').$typeGlobalConverter->convert($modelProperty->getType())."\n".
            "    {\n";
        if (! $modelProperty->isAssignable()) {
            echo
                '        if ($this->'.$modelProperty->getName()." === null) {\n".
                '            throw new \LogicException(\'Primary key $'.$modelProperty->getName().' is not assigned'.($stupidDeveloper ? ', before read primary key $'.$modelProperty->getName().' model must be inserted or fetched from database' : '')."');\n".
                "        }\n"
            ;
        }
        echo
            "        return \$this->".$modelProperty->getName().";\n".
            "    }\n\n"
        ;
    }

    $primaryKeys = [];
    foreach ($modelStructure->getPrimaryKeys() as $primaryKey) {
        $primaryKeys[] = '(string) '.$primaryKey->renderReadCode('$this->get' . ucfirst($primaryKey->getName()).'()');
    }

    echo
        "    final public function getPrimaryKey(): string\n".
        "    {\n".
        '        return '.implode(" . '-' . ", $primaryKeys).";\n".
        "    }\n"
    ;
?>
}
