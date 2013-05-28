<?php
/**
 * Mysql
 */

// setup
$GLOBALS['Aura\Sql\Setup\MysqlSetup']['connection_params'] = [
    'dsn' => [
        'host' => '127.0.0.1',
    ],
    'username' => 'root',
    'password' => '',
    'options' => [],
];

// test
$GLOBALS['Aura\Sql\MysqlConnectionTest']['expect_dsn_string'] = 'mysql:host=127.0.0.1';

/**
 * Pgsql
 */

// setup
$GLOBALS['Aura\Sql\Setup\PgsqlSetup']['connection_params'] = [
    'dsn' => [
        'host' => '127.0.0.1',
        'dbname' => 'test',
    ],
    'username' => 'postgres',
    'password' => '',
    'options' => [],
];

// test
$GLOBALS['Aura\Sql\PgsqlConnectionTest']['expect_dsn_string'] = 'pgsql:host=127.0.0.1;dbname=test';

/**
 * Sqlite
 */

// setup
$GLOBALS['Aura\Sql\Setup\SqliteSetup']['connection_params'] = [
    'dsn' => ':memory:',
];

// test
$GLOBALS['Aura\Sql\SqliteConnectionTest']['expect_dsn_string'] = 'sqlite::memory:';
