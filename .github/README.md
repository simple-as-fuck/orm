# Simple as fuck / orm

Orm layer easy to use with customizable class generation. 

## Installation

```bash
composer require simple-as-fuck/orm
```

### Usage generator with mysql as composer command

Add configurations into your composer.json extra (https://getcomposer.org/doc/04-schema.md#extra).

All configuration keys are commented in [Defaults\Config](../src/Config/Defaults/Config.php) and can be change in composer extra.

```json
{
    "extra": {
        "orm-generator-config": {
            "mysql-host": "127.0.0.1",
            "mysql-port": 3306,
            "mysql-username": "root",
            "mysql-password": "",
            "mysql-database-name": "database"
        }
    }
}
```

Add commands into your composer.json scripts (https://getcomposer.org/doc/articles/scripts.md#writing-custom-commands).

```json
{
    "scripts": {
        "orm:generate": "SimpleAsFuck\\Orm\\Generator\\Command\\Composer::mysqlGenerate",
        "orm:check": "SimpleAsFuck\\Orm\\Generator\\Command\\Composer::mysqlCheck"
    }
}
```

After prepared composer you can run commands:

```bash
composer orm:generate
```

Command generate for all database tables with primary key Model and Repository classes.
By default, models are in App\Model\Database and repository in App\Service\Database namespace.

```bash
composer orm:check
```

If you add all generated classes into repository, you can check generated content if is up-to-date with database structure.  

```bash
composer orm:generate --no-interaction --no-dev
```

Is recommended avoid adding generated code in repository and run orm generation like this for production deploy after composer install.
**--no-dev** option in default class templates remove some comments from generated code and some additional checks with exception,
this can make orm gently faster.

```bash
composer orm:generate -- --i-am-not-stupid
```

**-- --i-am-not-stupid** and **--no-dev** are synonyms because if you write production deploy script,
is good to know what you doing and not be stupid :-).

### Usage default generated classes

Nice after successful class generation with default templates, you can write code like this.

```php
/**
 * you can put some implementation of abstract connection in your DI container
 * than all repository can be loaded from DI
 *
 * @var \SimpleAsFuck\Orm\Database\Abstracts\Connection $connection 
 */
$repository = new \App\Service\Database\SomethingRepository($connection);

$model = new \App\Model\Database\Something('example', 5);

$repository->insert($model);

$autoIncrementPrimaryKey = 1;
$insertedModel = new \App\Model\Database\Something('update_example', 5, $autoIncrementPrimaryKey);

$repository->update($insertedModel);

$insertedModel = $repository->selectByPrimaryKey($autoIncrementPrimaryKey);

$repository->delete($insertedModel);

```

## Customization

### Usage generator with mysql as php class

If you want write in application your command for class generation,
mainly for configuration sharing with generator and app,
use class **\SimpleAsFuck\Orm\Generator\Generator**.

```php

/**
 * abstraction with configuration you can create some adapter for config from your app
 *
 * @var \SimpleAsFuck\Orm\Config\Abstracts\Config $config
 */

/**
 * you can inject custom connection into mysql database
 *
 * @var \SimpleAsFuck\Orm\Database\Mysql\Connection|null $connection
 */

/**
 * you can inject custom directory generator and change what classes will generated
 * by default models and repositories are generated with model-*, repository-* config.
 *
 * @var \SimpleAsFuck\Orm\Generator\Abstracts\DirectoryGenerator|null $directoryGenerator
 */

$generator = \SimpleAsFuck\Orm\Generator\Generator::createMysql($config, $connection, $directoryGenerator);

// same as composer cmd "composer orm:generate"
$generator->generate();
// same as composer cmd "composer orm:check"
$generator->check();
// same as composer cmd "composer orm:generate -- --i-am-not-stupid"
$generator->generate(false);

```

### Usage generator with different database type

Generator can create classes from various sources,
because use abstract **SimpleAsFuck\Orm\Generator\Abstracts\StructureLoader**.
You can load models structure from any source and create any classes.

```php

/**
 * you must inject some structure loader and provide ModelStructure array,
 * for every instance of ModelStructure should be created some class or classes in $directoryGenerator
 * $generator instance will handle file system synchronization 
 *
 * @var \SimpleAsFuck\Orm\Generator\Abstracts\StructureLoader $structureLoader
 */

/**
 * you must inject directory generator with definition
 * which classes will generated based on ModelStructure array
 *
 * @var \SimpleAsFuck\Orm\Generator\Abstracts\DirectoryGenerator $directoryGenerator
 */

$generator = new \SimpleAsFuck\Orm\Generator\Generator($structureLoader, $directoryGenerator);
```
