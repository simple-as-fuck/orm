<?php

declare(strict_types=1);

require_once __DIR__.'/../../../vendor/autoload.php';

$connection = new \SimpleAsFuck\Orm\Database\Mysql\Connection('database', 3306, 'root', 'password', 'database');

$connection->query('create table TestIgnoredTable (id int unsigned primary key)', []);

$connection->query('
    create table TestAutoIncrement(
        id int unsigned not null auto_increment,
        `int` int not null,
        `string` varchar(255) not null,
        createdAt datetime not null,
        timeAt datetime default null,
        deletedAt varchar(255) default null,
        `float` float not null default 0.0,
        primary key (id),
        index timeAtIndex (timeAt)
    )
', []);

$connection->query('
    create table TestMap(
        someId int unsigned not null ,
        anotherId int unsigned not null ,
        primary key (someId, anotherId) 
    )
', []);

$connection->query("
    create table TestMultiplePrimaryKey(
        id int unsigned not null auto_increment,
        `date` date not null,
        customType varchar(255) not null,
        anotherId int unsigned default null,
        deletedAt varchar(255) default null,
        `value` decimal(10,2) not null default '0.0',
        primary key (id, date),
        index anotherIdIndex (anotherId)
    )
", []);
