<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\StructureLoader;

use SimpleAsFuck\Orm\Config\Abstracts\Config;
use SimpleAsFuck\Orm\Database\Abstracts\Connection;
use SimpleAsFuck\Orm\Generator\Abstracts\ModelStructure;
use SimpleAsFuck\Orm\Generator\Abstracts\Renderer;
use SimpleAsFuck\Orm\Generator\Abstracts\StructureLoader;

final class Mysql extends StructureLoader
{
    private Connection $connection;
    private Config $config;

    public function __construct(Connection $connection, Config $config, Renderer $renderer)
    {
        parent::__construct($config, $renderer);

        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * method load structure for all models from MySql
     *
     * @return ModelStructure[]
     */
    public function loadStructure(): array
    {
        $ignoredTables = $this->config->getArrayOfString('database-ignored-tables');

        $result = $this->connection->query("
            select * from
                information_schema.TABLES
            where
                TABLE_SCHEMA = :databaseName
                and
                TABLE_TYPE = 'BASE TABLE'
                and
                TABLE_NAME not in (:ignoredTables)
        ", [
            ':databaseName' => $this->config->getString('mysql-database-name'),
            ':ignoredTables' => implode(',', $ignoredTables),
        ]);

        $modelsStructure = [];

        while (true) {
            $tableStructure = $result->fetch();
            if (!$tableStructure) {
                break;
            }

            $columns = $this->connection->query("
                select * from
                    information_schema.COLUMNS
                where
                    TABLE_SCHEMA = :databaseName
                    and
                    TABLE_NAME = :tableName
                order by
                    ORDINAL_POSITION
            ", [
                ':databaseName' => $this->config->getString('mysql-database-name'),
                ':tableName' => $tableStructure->TABLE_NAME,
            ])->fetchAll();

            $primaryKeys = [];
            $additionalKeys = [];
            $simpleParams = [];

            foreach ($columns as $column) {
                $propertyType = $this->convertType($tableStructure->TABLE_NAME, $column->COLUMN_NAME, $column->DATA_TYPE);

                if ($column->COLUMN_KEY === 'PRI') {
                    $primaryKeys[] = $this->makeModelProperty(
                        $column->COLUMN_NAME,
                        $column->COLUMN_COMMENT,
                        false,
                        strpos($column->EXTRA, 'auto_increment') === false,
                        $propertyType,
                        strpos($column->EXTRA, 'auto_increment') === false ? null : 'null'
                    );

                    continue;
                }

                $property = $this->makeModelProperty(
                    $column->COLUMN_NAME,
                    $column->COLUMN_COMMENT,
                    $column->IS_NULLABLE === 'YES',
                    true,
                    $propertyType,
                    $this->convertDefaultValue($propertyType, $column->COLUMN_DEFAULT, $column->IS_NULLABLE === 'YES')
                );

                if ($column->COLUMN_KEY !== '') {
                    $additionalKeys[] = $property;
                    continue;
                }

                $simpleParams[] = $property;
            }

            if (count($primaryKeys) === 0) {
                continue;
            }

            $modelsStructure[] = new ModelStructure($tableStructure->TABLE_NAME, $tableStructure->TABLE_COMMENT, $primaryKeys, $additionalKeys, $simpleParams);
        }

        return $modelsStructure;
    }

    private function convertDefaultValue(string $type, ?string $value, bool $isNullable): ?string
    {
        if ($value === null) {
            if ($isNullable) {
                return 'null';
            }

            return null;
        }

        if (preg_match('/^null$/i', $value)) {
            return strtolower($value);
        }

        if (preg_match('/^\s*[a-zA-z0-9_]+\(.*\)\s*$/', $value)) {
            return null;
        }

        if ($type === 'string' && ! preg_match('/^\'.*\'$/', $value)) {
            $value = '\''.$value.'\'';
        }

        if ($type === 'float' && strpos($value, '.') === false) {
            $value .= '.0';
        }

        return $value;
    }
}
