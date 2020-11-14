<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Defaults;

final class Config extends \SimpleAsFuck\Orm\Config\Abstracts\Config
{
    private const DEFAULT_VALUES = [
        /* configuration for models generation */
        // filesystem path for directory were models will be generated, in application absolut path is recommended
        'model-output-path' => './src/Model/Database',
        'model-namespace' => 'App\\Model\\Database',
        // absolut path for templates used for model class rendering
        // if you do not like generated classes, you can change them because of your code style or whatever
        'model-template-path' => __DIR__.'/../../Generator/Template/Model.php',
        'model-generated-template-path' => __DIR__.'/../../Generator/Template/ModelGenerated.php',
        'model-result-template-path' => __DIR__.'/../../Generator/Template/ModelResult.php',
        /* configuration for repositories generation */
        // filesystem path for directory were repositories will be generated, in application absolut path is recommended
        'repository-output-path' => './src/Service/Database',
        'repository-namespace' => 'App\\Service\\Database',
        // absolut path for templates used for repository class rendering
        // if you do not like generated classes, you can change them because of your code style or whatever
        'repository-template-path' => __DIR__.'/../../Generator/Template/Repository.php',
        'repository-generated-template-path' => __DIR__.'/../../Generator/Template/RepositoryGenerated.php',
        /* configuration for structure loading from database */
        // if database type is not found in any map (type, column, table) string type is used as default
        // this map define how database types in all tables are converted into php types
        // key is database type, value is php type
        'database-type-map' => [
            'tinyint' => 'int',
            'smallint' => 'int',
            'mediumint' => 'int',
            'integer' => 'int',
            'int' => 'int',
            'bigint' => 'int',
            'decimal' => 'string',
            'float' => 'float',
            'double' => 'float',
            'char' => 'string',
            'varchar' => 'string',
            'binary' => 'string',
            'varbinary' => 'string',
            'blob' => 'string',
            'tinytext' => 'string',
            'text' => 'string',
            'mediumtext' => 'string',
            'longtext' => 'string',
            'datetime' => \DateTimeImmutable::class,
            'timestamp' => \DateTimeImmutable::class,
            'date' => \DateTimeImmutable::class,
            'time' => \DateTimeImmutable::class,
        ],
        // this map define how column name in all tables is converted into php type
        // key is database column name, value is php type
        'database-column-map' => [],
        // this map define how column name in specific tables is converted into php type
        // key is database table name and column name like this 'table.column', value is php type
        'database-table-map' => [],
        // array of table names ignored for class generation
        'database-ignored-tables' => [],
        /* configuration for default mysql connection used in SimpleAsFuck\Orm\Generator::createMysql */
        // user must have access into information_schema database for class generation
        'mysql-host' => '127.0.0.1',
        'mysql-port' => 3306,
        'mysql-username' => 'root',
        'mysql-password' => '',
        // configuration from witch database are class generated
        'mysql-database-name' => 'database',
    ];

    /**
     * @return mixed
     */
    protected function getValue(string $key)
    {
        if (array_key_exists($key, static::DEFAULT_VALUES)) {
            return static::DEFAULT_VALUES[$key];
        }

        throw new \RuntimeException('Config key: "'.$key.'" not exists');
    }
}
